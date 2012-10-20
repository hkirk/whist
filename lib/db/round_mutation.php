<?php


function db_create_round($game_id, $bid_type) {
	global $_db;
	$sql = <<<EOS
INSERT INTO game_rounds
(game_id, round, bid_type, started_at, ended_at, updated_at)
SELECT ?, (
	SELECT IF(max IS NULL, 1, max+1)
	FROM (
		SELECT MAX(round) AS max
		FROM game_rounds
		WHERE game_id = ?
	) AS nested
),?, NOW(), NULL, NOW()
EOS;
	$params = array(
		$game_id,
		$game_id,
		$bid_type
	);
	_db_prepare_execute($sql, $params);
	$id = $_db->lastInsertId();
	return $id;
}


function db_create_normal_round($game_id, $bid_tricks, $bid_attachment, $bid_winner_position, $tips = NULL) {
	if ($bid_attachment === 'tips') {
		assert(is_int($tips) && $tips >= 1 && $tips <= 3);
	} else {
		assert(is_null($tips));
	}
	_db_assert_player_position($bid_winner_position);
	_db_beginTransaction();
	$game_round_id = db_create_round($game_id, 'normal');
	$sql = <<<EOS
INSERT INTO normal_game_rounds
(game_round_id, bid_winner_position, bid_winner_mate_position, bid_tricks, bid_attachment, tricks, tips)
VALUES(?, ?, NULL, ?, ?, NULL, ?)
EOS;
	$params = array(
		$game_round_id,
		$bid_winner_position,
		$bid_tricks,
		$bid_attachment,
		$tips
	);
	_db_prepare_execute($sql, $params);
	_db_commit();
	return $game_round_id;
}


function db_create_solo_round($game_id, $solo_type, $bid_winner_positions) {
	_db_assert_player_positions($bid_winner_positions);
	assert(count($bid_winner_positions) > 0);
	global $_db;
	_db_beginTransaction();
	// Round row:
	$game_round_id = db_create_round($game_id, 'solo');
	// Solo round row:
	$sql = <<<EOS
INSERT INTO solo_game_rounds	
(game_round_id, solo_type)
VALUES(?, ?)
EOS;
	$params = array(
		$game_round_id,
		$solo_type
	);
	_db_prepare_execute($sql, $params);
	// Solo round players row(s):
	$sql = <<<EOS
INSERT INTO solo_game_round_bid_winners
(game_round_id, player_position, tricks)
VALUES(?, ?, NULL)
EOS;
	$stm = $_db->prepare($sql);
	foreach ($bid_winner_positions as $bid_winner_position) {
		$params = array(
			$game_round_id,
			$bid_winner_position
		);
		$stm->execute($params);
	}
	// Commit
	_db_commit();
	return $game_round_id;
}


function db_end_round($game_id, $game_round_id, $player_points) {
	assert(is_array($player_points));
	assert(count($player_points) === 4);
	global $_db;
	_db_connect();
	// Round players table rows:
	$sql_round = <<<EOS
INSERT INTO game_round_players
(game_round_id, player_position, points)
VALUES(?, ?, ?)
EOS;
	$sql_game = <<<EOS
UPDATE game_players
SET total_points = total_points + ?
WHERE game_id = ? AND player_position = ?
EOS;
	$stm_round = $_db->prepare($sql_round);
	$stm_game = $_db->prepare($sql_game);
	foreach ($player_points as $position => $points) {
		// Insert round points
		$params = array(
			$game_round_id,
			$position,
			$points
		);
		$stm_round->execute($params);
		// Update game total points
		$params = array(
			$points,
			$game_id,
			$position
		);
		$stm_game->execute($params);
	}
	// Rounds table row:
	$sql = <<<EOS
UPDATE game_rounds
SET
ended_at = NOW(),
updated_at = NOW()
WHERE id = ?
EOS;
	$params = array($game_round_id);
	_db_prepare_execute($sql, $params);
}


function db_end_normal_round($game_id, $game_round_id, $bid_winner_mate_position, $tricks, $player_points) {
	_db_beginTransaction();
	$sql = <<<EOS
UPDATE normal_game_rounds
SET
bid_winner_mate_position = ?,
bid_tricks = ?
WHERE game_round_id = ?
EOS;
	$params = array(
		$bid_winner_mate_position,
		$tricks,
		$game_round_id
	);
	_db_prepare_execute($sql, $params);
	db_end_round($game_id, $game_round_id, $player_points);
	_db_commit();
}


function db_end_solo_round($game_id, $game_round_id, $bid_winner_tricks_by_position, $player_points) {
	_db_beginTransaction();
	global $_db;
	_db_connect();
	$sql = <<<EOS
UPDATE solo_game_round_bid_winners
SET tricks = ?
WHERE game_round_id = ? AND player_position = ?
EOS;
	$stm = $_db->prepare($sql);
	foreach ($bid_winner_tricks_by_position as $position => $tricks) {
		$params = array(
			$tricks,
			$game_round_id,
			$position
		);
		$stm->execute($params);
	}
	db_end_round($game_id, $game_round_id, $player_points);
	_db_commit();
}


