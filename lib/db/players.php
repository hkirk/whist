<?php


function db_load_players() {
	global $_db;
	_db_connect();
	$query = <<<EOS
SELECT *
FROM players
ORDER BY nickname
EOS;
	$stm = $_db->query($query);
	$players = [];
	foreach ($stm as $row) {
		$players[] = [
			'id' => $row['id'],
			'nickname' => $row['nickname'],
			'fullname' => $row['fullname']
		];
	}
	return $players;
}


function db_check_player_ids($player_ids) {
	$n_players = count($player_ids);
	if ($n_players === 0) {
		return TRUE;
	}
	$placeholders_list = _db_placeholders_list($player_ids);
	$sql = <<<EOS
SELECT COUNT(*) AS count
FROM players
WHERE id IN ($placeholders_list)
EOS;
	list(,, $row) = _db_prepare_execute_fetch($sql, $player_ids);
	var_dump($row);
	$count = $row['count'];
	echo "Count: ";
	echo $count;
	return (int) $count === count($player_ids);
}

function db_get_number_of_players($game_id) {
    _db_connect();

    $sql = <<<EOS
SELECT count(*) as count
FROM game_players WHERE game_id = ?
EOS;

    list(,,$row)  = _db_prepare_execute_fetch($sql, [$game_id]);
    return $row['count'];

}

