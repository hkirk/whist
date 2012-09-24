<?php

require("../lib/lib.php");

if (request_method() !== 'POST') {
	return;
}

$player_position_validator = function($value) {
			if ($value === "" || !ctype_digit($value)) {
				return FALSE;
			}
			return $value >= 0 && $value <= 3;
		};


// Basic input validation:
$game_id = check_get_uint($_POST, 'game_id');
$tricks = check_get_uint($_POST, 'tricks', TRUE);
$attachment_key = check_get_enum($_POST, 'attachment', $ATTACHMENTS, TRUE);
$solo_game_key = check_get_enum($_POST, 'solo', $SOLO_GAMES, TRUE);
$tips = check_get_uint($_POST, 'tips', TRUE);
$bid_winner_positions = check_get_multi_checkbox_array($_POST, 'bid_winner_positions', $player_position_validator);
check_input($game_id, $tricks, $attachment_key, $solo_game_key, $tips, $bid_winner_positions);

$n_bid_winner_positions = count($bid_winner_positions);
if ($n_bid_winner_positions > 4) {
	render_unexpected_input_page_and_exit("Too many bid winners!");
}
$used_bid_winner_position = array();
foreach ($bid_winner_positions as $bid_winner_position) {
	if (isset($used_bid_winner_position[$bid_winner_position])) {
		render_unexpected_input_page_and_exit("Bid winner position occurs twice");
	}
	$used_bid_winner_position[$bid_winner_position] = TRUE;
}


// Advanced input validation:

$data = array(
	'unknown_game' => FALSE,
	'has_active_round' => FALSE,
	'missing_bid_type' => FALSE,
	'multiple_bid_types' => FALSE,
	'missing_solo_bid_winners' => FALSE,
	'missing_attachment' => FALSE,
	'missing_tips' => FALSE,
	'tips_chosen' => FALSE,
	'missing_normal_bid_winner' => FALSE
);
$input_error = FALSE;


$active_round_bid_type = db_get_active_round_bid_type($game_id);

switch ($active_round_bid_type) {
	case NULL:
		// No such game
		$input_error = $data['unknown_game'] = TRUE;
		break;
	case 'none':
		// No active round - ok
		break;
	default:
		// Has an active round
		$input_error = $data['has_active_round'] = TRUE;
		break;
}



if ($tricks === '') {
	// Solo game...
	if ($solo_game_key === '') {
		$input_error = $data['missing_bid_type'] = TRUE;
	}
	if ($attachment_key !== '' || $tips !== '') {
		$input_error = $data['multiple_bid_types'] = TRUE;
	}
	if ($n_bid_winner_positions < 1) {
		$input_error = $data['missing_solo_bid_winners'] = TRUE;
	}
	$bid_type = 'solo';
} else {
	// Normal game...
	if ($solo_game_key !== '') {
		$input_error = $data['multiple_bid_types'] = TRUE;
	}
	if ($attachment_key === '') {
		$input_error = $data['missing_attachment'] = TRUE;
	}
	if ($attachment_key === TIPS) {
		if ($tips === '') {
			$input_error = $data['missing_tips'] = TRUE;
		}
	} else {
		if ($tips !== '') {
			$input_error = $data['tips_chosen'] = TRUE;
		}
	}
	if ($tricks < MIN_BID_TRICKS || $tricks > MAX_BID_TRICKS) {
		render_unexpected_input_page_and_exit("Invalid number of tricks!");
	}
	if ($tips < MIN_TIPS || $tips > MAX_TIPS) {
		render_unexpected_input_page_and_exit("Invalid number of tips!");
	}
	if ($n_bid_winner_positions !== 1) {
		$input_error = $data['missing_normal_bid_winner'] = TRUE;
	}
	$bid_type = 'normal';
}

if ($input_error) {
	render_page_and_exit("Input Error", "Input error", "begin_round_input_error", $data);
}
// End of validation


if ($bid_type == 'normal') {
	if ($tips === '') {
		$tips = NULL;
	}
	$id = db_create_normal_round($game_id, $tricks, $attachment_key, $bid_winner_positions[0], $tips);
} else {
	$id = db_create_solo_round($game_id, $solo_game_key, $bid_winner_positions);
}


redirect_path("game.php?id=" . $game_id);
