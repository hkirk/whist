<?php

require("lib.php");

check_request_method("POST");

// Build valid bid input values
$VALID_BID_VALUES = array();
for ($tricks = MIN_BID_TRICKS; $tricks <= MAX_TRICKS; $tricks++) {
	$VALID_BID_VALUES[BID_PREFIX_NORMAL . $tricks] = TRUE;
}
foreach ($SOLO_GAME_KEY_ORDER as $solo_game_key) {
	$VALID_BID_VALUES[BID_PREFIX_SOLO . $solo_game_key] = TRUE;
}

// Build valid attachment input values
$VALID_ATTACHMENT_VALUES = $ATTACHMENTS;
unset($VALID_ATTACHMENT_VALUES[TIPS]);
for ($tips = MIN_TIPS; $tips <= MAX_TIPS; $tips++) {
	$VALID_ATTACHMENT_VALUES[TIPS . "-" . $tips] = TRUE;
}
$VALID_ATTACHMENT_VALUES[''] = TRUE; // The "null" / "solo" value


// Basic input validation:
$game_id = check_get_uint($_POST, 'game_id');
$input_bid = check_get_radio_enum($_POST, 'bid', $VALID_BID_VALUES);
$input_attachment = check_get_radio_enum($_POST, 'attachment', $VALID_ATTACHMENT_VALUES);
$bid_winner_positions = check_get_multi_checkbox_array($_POST, 'bid_winner_positions', $VALID_PLAYER_POSITIONS);
check_input($game_id, $input_bid, $input_attachment, $bid_winner_positions);

$n_bid_winner_positions = count($bid_winner_positions);
if ($n_bid_winner_positions > 4) {
	render_unexpected_input_page_and_exit("Too many bid winners!");
}
$used_bid_winner_position = array();
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


$data = array(
		'unknown_game' => FALSE,
		'has_active_round' => FALSE,
		'missing_bid' => FALSE,
		'solo_and_attachment' => FALSE,
		'missing_solo_bid_winners' => FALSE,
		'missing_normal_bid_winner' => FALSE,
		'missing_attachment' => FALSE,
		'illegal_attachment' => FALSE
);
$input_error = FALSE;

if ($input_bid === '') {
	$input_error = $data['missing_bid'] = TRUE;
	beginround_render_page_and_exit($data);
}


$game = db_get_game_type_with_active_round($game_id);

if ($game === NULL) {
	$input_error = $data['unknown_game'] = TRUE;
	beginround_render_page_and_exit($data);
}

if ($game['active_round'] !== NULL) {
	$input_error = $data['has_active_round'] = TRUE;
}
//var_dump($game);


$solo_bid = strpos($input_bid, 'solo-') !== FALSE;

if ($solo_bid) {
	// Solo game...
	$solo_game_key = substr($input_bid, strlen(BID_PREFIX_SOLO));
	if ($input_attachment !== '') {
		$input_error = $data['solo_and_attachment'] = TRUE;
	}
	if ($n_bid_winner_positions < 1) {
		$input_error = $data['missing_solo_bid_winners'] = TRUE;
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
		$input_error = $data['missing_normal_bid_winner'] = TRUE;
	}
}

if ($input_error) {
	beginround_render_page_and_exit($data);
}
// End of validation


if ($solo_bid) {
	$id = db_create_solo_round($game_id, $solo_game_key, $bid_winner_positions);
} else {
	$id = db_create_normal_round($game_id, $tricks, $attachment_key, $bid_winner_positions[0], $tips);
}


redirect_to_game($game_id);
