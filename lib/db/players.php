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
	$players = array();
	foreach ($stm as $row) {
		$players[] = array(
			'id' => $row['id'],
			'nickname' => $row['nickname'],
			'fullname' => $row['fullname']
		);
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


