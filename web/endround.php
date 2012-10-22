<?php

require("lib.php");

check_request_method("POST");

// Basic input validation:
$game_id = check_get_uint($_POST, 'game_id');
$tricks_array = check_get_array($_POST, 'tricks', NULL, $VALID_PLAYER_POSITIONS);
$bid_winner_mate_position = check_get_uint($_POST, 'bid_winner_mate_position', TRUE, MIN_PLAYER_POSITION, MAX_PLAYER_POSITION);
check_input($game_id, $tricks_array, $bid_winner_mate_position);

$tricks_array_size = count($tricks_array);
if ($tricks_array_size < 1 || $tricks_array_size > MAX_PLAYER_POSITION) {
	render_unexpected_input_page_and_exit("Bad number of tricks in array!");
}
foreach ($tricks_array as $index => $dummy) {
	$tricks = check_get_uint($tricks_array, $index, TRUE, MIN_TRICKS, MAX_TRICKS);
	if ($tricks === NULL) {
		render_unexpected_input_page_and_exit("Bad tricks");
	}
	// Update with integer key and value (or blank)
	$tricks_array[(int) $index] = $tricks;
}


function endround_render_page_and_exit($data) {
	render_page_and_exit("Input Error", "Input error", "end_round_input_error", $data);
}


$data = array(
	'unknown_game' => FALSE,
	'no_active_round' => FALSE,
	'missing_tricks' => FALSE,
	'bad_tricks_sum' => FALSE,
	'missing_bid_winner_mate_position' => FALSE
);
$input_error = FALSE;

$game_with_active_round = db_get_game_type_with_active_round($game_id);

if ($game_with_active_round === NULL) {
	$input_error = $data['unknown_game'] = TRUE;
	endround_render_page_and_exit($data);
}

$active_round = $game_with_active_round['active_round'];
if ($active_round === NULL) {
	$input_error = $data['no_active_round'] = TRUE;
	endround_render_page_and_exit($data);
}

$bid_type = $active_round['bid_type'];
$bid_data = $active_round['bid_data'];
if ($bid_type === 'normal') {
	if ($bid_winner_mate_position === '') {
		$input_error = $data['missing_bid_winner_mate_position'] = TRUE;
	}
	$bid_winner_position = $bid_data['bid_winner_position'];
	if (!isset($tricks_array[$bid_winner_position])) {
		render_unexpected_input_page_and_exit("Bid winner position index is not in tricks array!");
	}
	$n_bid_winners = 1;
} else {
	$bid_winner_positions = array_keys($bid_data['bid_winner_tricks_by_position']);
	if ($bid_winner_mate_position !== '') {
		render_unexpected_input_page_and_exit("Mate position is not blank!");
	}
	foreach ($bid_winner_positions as $bid_winner_position) {
		if (!isset($tricks_array[$bid_winner_position])) {
			render_unexpected_input_page_and_exit("Bid winner position index is not in tricks array!");
		}
	}
	$n_bid_winners = count($bid_winner_positions);
}

if (count($tricks_array) !== $n_bid_winners) {
	render_unexpected_input_page_and_exit("Unexpected number of bid winner tricks!");
}


$tricks_sum = 0;
foreach ($tricks_array as $tricks) {
	if ($tricks === '') {
		$input_error = $data['missing_tricks'] = TRUE;
	} else {
		$tricks_sum += $tricks;
	}
}
if ($tricks_sum > MAX_TRICKS) {
	$input_error = $data['bad_tricks_sum'] = TRUE;
}


if ($input_error) {
	endround_render_page_and_exit($data);
}
// End of validation


$round_id = $active_round['id'];
if ($bid_type === 'normal') {
	$tricks = $tricks_array[$bid_winner_position];
	// TODO extract parameters
	//$bidder_points = normal_game_points($point_rules, $bid_tricks, $bid_attachment, $tricks, $tips);
	$bidder_points = normal_game_points(array(), 8, $ATTACHMENTS[NONE], 8, NULL);
	// Initialize all player points to the negation of the bid winner points (opponents)
	$player_points = array_fill(0, 4, -$bidder_points);
	if ($bid_winner_mate_position === $bid_winner_position) {
		$player_points[$bid_winner_position] = $bidder_points * 3;
	} else {
		$player_points[$bid_winner_position] = $bidder_points;
		$player_points[$bid_winner_mate_position] = $bidder_points;
	}
	var_dump($player_points);
	db_end_normal_round($game_id, $round_id, $bid_winner_mate_position, $tricks, $player_points);
} else {
	$bid_winner_tricks_by_position = $tricks_array;
	// Initialize all player points to zero
	$player_points = array_fill(0, 4, 0);
	foreach ($bid_winner_tricks_by_position as $position => $tricks) {
		// TODO extract parameters
		//$bidder_points = solo_game_points($point_rules, $solo_game, $tricks);
		$bidder_points = solo_game_points(array(), $SOLO_GAMES['solo'], $tricks);
		for ($i = MIN_PLAYER_POSITION; $i <= MAX_PLAYER_POSITION; $i++) {
			if ($i === $position) {
				$player_points[$i] += $bidder_points * 3;
			} else {
				$player_points[$i] -= $bidder_points;
			}
		}
	}
	db_end_solo_round($game_id, $round_id, $bid_winner_tricks_by_position, $player_points);
}


// Redirect back to the game
redirect_path("/game.php?id=" . $game_id);
