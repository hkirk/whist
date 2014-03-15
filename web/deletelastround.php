<?php

require("lib.php");

check_request_method("POST");

/* Basic input validation: */
$game_id = check_get_uint($_POST, 'game_id');
check_input($game_id);

// Get latest round

$sql = <<<EOS
    SELECT id, bid_type FROM game_rounds WHERE game_id = ? ORDER BY round DESC LIMIT 1
EOS;

list(,, $row) = _db_prepare_execute_fetch($sql, [$game_id]);

if ($row === false) {
	error_log("No round found for game $game_id");
} else {
	$game_round_id = $row["id"];
	$bid_type = $row['bid_type'];

	$number_of_players = db_get_number_of_players($game_id);

	if ($bid_type = "solo") {
		db_delete_solo_round($game_round_id, $game_id, $number_of_players);
	} else {
		db_delete_normal_round($game_round_id, $game_id, $number_of_players);
	}
}

header("Location: game.php?id=$game_id");
