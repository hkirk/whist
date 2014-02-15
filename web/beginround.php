<?php

require("lib.php");

check_request_method("POST");

$data = [
		'unknown_game' => FALSE,
		'has_active_round' => FALSE,
		'missing_dealer' => FALSE,
		'missing_bid' => FALSE,
		'solo_and_attachment' => FALSE,
		'missing_attachment' => FALSE,
		'illegal_attachment' => FALSE,
		'illegal_solo_bid_winner_count' => FALSE,
		'illegal_normal_bid_winner_count' => FALSE,
		'illegal_bye_count' => FALSE,
		'multi_bye_position' => FALSE,
		'joint_bid_winner_bye' => FALSE,
		'joint_bye_dealer' => FALSE
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

/* Build valid bid input values */
$VALID_BID_VALUES = build_valid_bid_input_values();

/* Build valid attachment input values */
$VALID_ATTACHMENT_VALUES = build_valid_attachment_input_values();

/* Build valid player positions */
$VALID_PLAYER_POSITIONS = build_valid_player_position_input_values($number_of_players);

$max_player_position = $number_of_players - 1;

$input_bid = check_get_select_enum($_POST, 'bid', $VALID_BID_VALUES, TRUE);
$input_attachment = check_get_select_enum($_POST, 'attachment', $VALID_ATTACHMENT_VALUES, TRUE);
$bid_winner_positions = check_get_multi_input_array($_POST, 'bid_winner_positions', $VALID_PLAYER_POSITIONS);
$bye_positions = check_get_multi_input_array($_POST, 'bye_positions', $VALID_PLAYER_POSITIONS);
$dealer_position = check_get_uint($_POST, 'dealer_position', FALSE, MIN_PLAYER_POSITION, $max_player_position, '');
check_input($input_bid, $input_attachment, $bid_winner_positions, $bye_positions, $dealer_position);

$n_bid_winner_positions = count($bid_winner_positions);
if ($n_bid_winner_positions > $number_of_players) {
	render_unexpected_input_page_and_exit("Too many bid winners!");
}

$used_bid_winner_position = [];
foreach ($bid_winner_positions as $index => $bid_winner_position) {
	if (isset($used_bid_winner_position[$bid_winner_position])) {
		render_unexpected_input_page_and_exit("Bid winner position occurs twice");
	}
	$used_bid_winner_position[$bid_winner_position] = TRUE;
	// Convert position to an integer
	$bid_winner_positions[$index] = (int) $bid_winner_position;
}


// Advanced input validation:

function beginround_render_page_and_exit($data) {
	render_page_and_exit("Input Error", "Input error", "begin_round_input_error", $data);
}


$game = db_get_game_type_with_active_round($game_id);

if ($game === NULL) {
	$input_error = $data['unknown_game'] = TRUE;
	beginround_render_page_and_exit($data);
}

if ($game['active_round'] !== NULL) {
	$input_error = $data['has_active_round'] = TRUE;
}

$n_bye_players = count($bye_positions);
if ($number_of_players - $n_bye_players != DEFAULT_PLAYERS) {
	$input_error = $data['illegal_bye_count'] = TRUE;
}

$used_bye_position = [];
foreach ($bye_positions as $index => $bye_position) {
	if (isset($used_bye_position[$bye_position])) {
		$input_error = $data['multi_bye_position'] = TRUE;
	}
	$used_bye_position[$bye_position] = TRUE;
	// Convert position to an integer
	$bye_positions[$index] = (int) $bye_position;
}

if ($dealer_position === '') {
	$input_error = $data['missing_dealer'] = TRUE;
	beginround_render_page_and_exit($data);
}

$bid_winner_bye_intersection = array_intersect($bid_winner_positions, $bye_positions);
if (!empty($bid_winner_bye_intersection)) {
	$input_error = $data['joint_bid_winner_bye'] = TRUE;
}
if (in_array($dealer_position, $bye_positions)) {
	$input_error = $data['joint_bye_dealer'] = TRUE;
}

if ($input_bid === '') {
	$input_error = $data['missing_bid'] = TRUE;
	beginround_render_page_and_exit($data);
}


$solo_bid = strpos($input_bid, BID_PREFIX_SOLO) === 0;

if ($solo_bid) {
	// Solo game...
	$solo_game_key = substr($input_bid, strlen(BID_PREFIX_SOLO));
	if ($input_attachment !== '') {
		$input_error = $data['solo_and_attachment'] = TRUE;
	}
	if ($n_bid_winner_positions < 1 || $n_bid_winner_positions > DEFAULT_PLAYERS) {
		$input_error = $data['illegal_solo_bid_winner_count'] = TRUE;
	}
} else {
	// Normal game...
	$tricks = (int) substr($input_bid, strlen(BID_PREFIX_NORMAL));
	if ($input_attachment === '') {
		$input_error = $data['missing_attachment'] = TRUE;
	} else {
		$dash_pos = strpos($input_attachment, "-");
		if ($dash_pos === FALSE) {
			$attachment_key = $input_attachment;
			$tips = NULL;
			assert($attachment_key !== TIPS);
		} else {
			$attachment_key = substr($input_attachment, 0, $dash_pos);
			$tips = (int) substr($input_attachment, $dash_pos + 1);
			assert($attachment_key === TIPS);
		}
//		printf("Attachments: ");
//		var_dump($game['attachments']);
		if (!(in_array($attachment_key, $game['attachments']) || in_array($attachment_key, $REQUIRED_ATTACHMENT_KEYS_ORDER))) {
			$input_error = $data['illegal_attachment'] = TRUE;
		}
	}
	if ($n_bid_winner_positions !== 1) {
		$input_error = $data['illegal_normal_bid_winner_count'] = TRUE;
	}
}

if ($input_error) {
	beginround_render_page_and_exit($data);
}
// End of validation

$is_bye_players = array_fill(0, $number_of_players, FALSE);
foreach ($bye_positions as $bye_position) {
	$is_bye_players[$bye_position] = TRUE;
}
if ($solo_bid) {
	$game_round_id = db_create_solo_round($game_id, $is_bye_players, $dealer_position, $solo_game_key, $bid_winner_positions);
} else {
	$game_round_id = db_create_normal_round($game_id, $is_bye_players, $dealer_position, $tricks, $attachment_key, $bid_winner_positions[0], $tips);
}


redirect_to_game($game_id);
