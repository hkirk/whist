<?php

require("lib.php");

check_request_method("POST");

$game_id = check_get_uint($_GET, 'game_id');
check_input($game_id);

$game = db_get_game_type_with_active_round($game_id);
if($game===NULL) {
	// No active round!
	render_unexpected_input_page_and_exit("Game no started!");
}

$active_round = $game['active_round'];
if($active_round===NULL) {
	// No active round!
	render_unexpected_input_page_and_exit("There is no active round!");
}

$game_round_id = $active_round['id'];
$bid_type = $active_round['bid_type'];
if($bid_type==='solo') {
	db_delete_solo_round($game_round_id, $game_id);
} else {
	db_delete_normal_round($game_round_id, $game_id);
}

redirect_to_game($game_id);
