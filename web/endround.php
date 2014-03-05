<?php

require("lib.php");

check_request_method("POST");

$data = [
		'unknown_game' => FALSE,
		'no_active_round' => FALSE,
		'missing_tricks' => FALSE,
		'bad_tricks_sum' => FALSE,
		'missing_bid_winner_mate_position' => FALSE
];
$input_error = FALSE;


/* Basic input validation: */
$game_id = check_get_uint($_POST, 'game_id');
check_input($game_id);

$number_of_players = db_get_number_of_players($game_id);
if ($number_of_players < DEFAULT_PLAYERS) {
	$data['unknown_game'] = TRUE;
	endround_render_page_and_exit($data);
}

$max_player_position = $number_of_players - 1;
$VALID_PLAYER_POSITIONS = build_valid_player_position_input_values($number_of_players);

/* An array entry for each bid winner */
$tricks_array = check_get_array($_POST, 'tricks', NULL, $VALID_PLAYER_POSITIONS);
$bid_winner_mate_position = check_get_uint($_POST, 'bid_winner_mate_position', TRUE, MIN_PLAYER_POSITION, $max_player_position);
check_input($tricks_array, $bid_winner_mate_position);


function endround_render_page_and_exit($data) {
	render_page_and_exit("Input Error", "Input error", "end_round_input_error", $data);
}


$tricks_array_size = count($tricks_array);
if ($tricks_array_size < MIN_BID_WINNERS || $tricks_array_size > MAX_BID_WINNERS) {
	render_unexpected_input_page_and_exit("Invalid number of tricks entries");
}
foreach ($tricks_array as $index => $dummy) {
	$tricks = check_get_uint($tricks_array, $index, TRUE, MIN_TRICKS, MAX_TRICKS);
	if ($tricks === NULL) {
		render_unexpected_input_page_and_exit("Bad tricks");
	}
	if ($tricks === '') {
		$input_error = $data['missing_tricks'] = TRUE;
		endround_render_page_and_exit($data);
	}
	// Update with integer key and tricks value
	$tricks_array[(int) $index] = $tricks;
}


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
$player_data = $active_round['player_data'];
if ($bid_type === 'normal') {
	foreach ($player_data as $position => $pd) {
		if ($pd['is_bye'] && $position === $bid_winner_mate_position) {
			render_unexpected_input_page_and_exit("Bid winner position cannot be a bye player position!");
		}
	}
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
	$tricks_sum += $tricks;
}
if ($tricks_sum > MAX_TRICKS) {
	$input_error = $data['bad_tricks_sum'] = TRUE;
}


if ($input_error) {
	endround_render_page_and_exit($data);
}


// End of validation


function init_player_points($player_data, $value) {
	$player_points = [];
	foreach ($player_data as $pd) {
		$player_points[] = $pd['is_bye'] ? null : $value;
	}
	return $player_points;
}


$point_rules = $game_with_active_round['point_rules'];
$round_id = $active_round['id'];
if ($bid_type === 'normal') {
	$bid_tricks = $bid_data['bid_tricks'];
	$bid_attachment_key = $bid_data['bid_attachment'];
	$tricks = $tricks_array[$bid_winner_position];
	$tips = $bid_data['tips'];
	//printf("Bid tricks: %s, Bid att: %s, Tricks: %s, Tips: %s",$bid_tricks, $bid_attachment['name'], $tricks, $tips);
	$bidder_points = normal_game_points($point_rules, $bid_tricks, $bid_attachment_key, $tricks, $tips);
	// Initialize all player points to the negation of the bid winner points (opponents) for non-bye players:
	$player_points = init_player_points($player_data, -$bidder_points);
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
	// Initialize all player points to zero for non-bye players:
	$player_points = init_player_points($player_data, 0);
	$solo_game = $SOLO_GAMES[$bid_data['type']];
	foreach ($bid_winner_tricks_by_position as $position => $tricks) {
		//printf("Solo game: %s, tricks: %s", $solo_game['name'], $tricks);
		$bidder_points = solo_game_points($point_rules, $solo_game, $tricks);
		foreach ($player_data as $i => $pd) {
			if ($pd['is_bye']) {
				continue;
			}
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
redirect_to_game($game_id);
