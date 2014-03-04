<?php


function db_create_round($game_id, $is_bye_players, $dealer_position, $bid_type) {
	assert(count($is_bye_players) >= DEFAULT_PLAYERS);
	assert($dealer_position >= MIN_PLAYER_POSITION && $dealer_position < count($is_bye_players));
	global $_db;
	$sql = <<<EOS
INSERT INTO game_rounds
(game_id, round, dealer_position, bid_type, started_at, ended_at, updated_at)
SELECT ?, (
	SELECT IF(max IS NULL, 1, max+1)
	FROM (
		SELECT MAX(round) AS max
		FROM game_rounds
		WHERE game_id = ?
	) AS nested
),?, ?, NOW(), NULL, NOW()
EOS;
	$params = [
			$game_id,
			$game_id,
			$dealer_position,
			$bid_type
	];
	_db_prepare_execute($sql, $params);
	$id = $_db->lastInsertId();
	// Bye players:
	$sql_players = <<<EOS
INSERT INTO game_round_players
(game_round_id, player_position, bye, points)
VALUES(?, ?, ?, NULL)
EOS;
	$stm_players = $_db->prepare($sql_players);
	foreach ($is_bye_players as $player_position => $is_bye_player) {
		$params = [$id, $player_position, $is_bye_player];
		$stm_players->execute($params);
	}
	// Update the game row:
	_db_set_game_updated($game_id);
	return $id;
}


function db_create_normal_round($game_id, $is_bye_players, $dealer_position, $bid_tricks, $bid_attachment, $bid_winner_position, $tips = NULL) {
	if ($bid_attachment === 'tips') {
		assert(is_int($tips) && $tips >= 1 && $tips <= 3);
	} else {
		assert(is_null($tips));
	}
	_db_assert_player_position($bid_winner_position, count($is_bye_players));
	_db_beginTransaction();
	$game_round_id = db_create_round($game_id, $is_bye_players, $dealer_position, 'normal');
	$sql = <<<EOS
INSERT INTO normal_game_rounds
(game_round_id, bid_winner_position, bid_winner_mate_position, bid_tricks, bid_attachment, tricks, tips)
VALUES(?, ?, NULL, ?, ?, NULL, ?)
EOS;
	$params = [
			$game_round_id,
			$bid_winner_position,
			$bid_tricks,
			$bid_attachment,
			$tips
	];
	_db_prepare_execute($sql, $params);
	_db_commit();
	return $game_round_id;
}


function db_create_solo_round($game_id, $is_bye_players, $dealer_position, $solo_type, $bid_winner_positions) {
	_db_assert_player_positions($bid_winner_positions, count($is_bye_players));
	assert(count($bid_winner_positions) > 0);
	global $_db;
	_db_beginTransaction();
	// Round row:
	$game_round_id = db_create_round($game_id, $is_bye_players, $dealer_position, 'solo');
	// Solo round row:
	$sql = <<<EOS
INSERT INTO solo_game_rounds	
(game_round_id, solo_type)
VALUES(?, ?)
EOS;
	$params = [
			$game_round_id,
			$solo_type
	];
	_db_prepare_execute($sql, $params);
	// Solo round players row(s):
	$sql = <<<EOS
INSERT INTO solo_game_round_bid_winners
(game_round_id, player_position, tricks)
VALUES(?, ?, NULL)
EOS;
	$stm = $_db->prepare($sql);
	foreach ($bid_winner_positions as $bid_winner_position) {
		$params = [
				$game_round_id,
				$bid_winner_position
		];
		$stm->execute($params);
	}
	// Commit
	_db_commit();
	return $game_round_id;
}


function db_delete_round($game_round_id, $game_id, $n_players) {
	_db_connect();
	$sql_round_players_select = <<<EOS
SELECT player_position, points
FROM game_round_players
WHERE game_round_id = ?
ORDER BY player_position
EOS;
	$sql_game_players = <<<EOS
UPDATE game_players AS gp
SET total_points = total_points - ?
WHERE game_id = ?
AND player_position = ?
EOS;
	$sql_round_players = <<<EOS
DELETE FROM game_round_players
WHERE game_round_id = ?
EOS;
	$sql_rounds = <<<EOS
DELETE FROM game_rounds
WHERE id = ?
EOS;
	$params = [$game_round_id];
	list(,, $rows) = _db_prepare_execute_fetchAll($sql_round_players_select, $params);
	$n_rows = count($rows);
	if ($n_rows == $n_players) {
		error_log("Removing points from users...");
		foreach ($rows as $row) {
			// Do not update points for bye players and unfinished rounds (SQL int - NULL = NULL ~ 0 total points)
			if ($row['points'] !== null) {
				$params_game_players = [$row['points'], $game_id, $row['player_position']];
				_db_prepare_execute($sql_game_players, $params_game_players);
			}
		}
	} else {
		error_log("Unexpected number of players for game round $game_round_id ($n_rows, expected $n_players)");
	}
	_db_prepare_execute($sql_round_players, $params);
	_db_prepare_execute($sql_rounds, $params);
}


function db_delete_normal_round($game_round_id, $game_id, $nplayers) {
	_db_beginTransaction();
	$sql = <<<EOS
DELETE FROM normal_game_rounds
WHERE game_round_id = ?
EOS;
	$params = [$game_round_id];
	_db_prepare_execute($sql, $params);
	db_delete_round($game_round_id, $game_id, $nplayers);
	_db_commit();
}


function db_delete_solo_round($game_round_id, $game_id, $nplayers) {
	_db_beginTransaction();
	$sql_round_bid_winners = <<<EOS
DELETE FROM solo_game_round_bid_winners
WHERE game_round_id = ?
EOS;
	$sql_rounds = <<<EOS
DELETE FROM solo_game_rounds
WHERE game_round_id = ?
EOS;
	$params = [$game_round_id];
	_db_prepare_execute($sql_round_bid_winners, $params);
	_db_prepare_execute($sql_rounds, $params);
	db_delete_round($game_round_id, $game_id, $nplayers);
	_db_commit();
}


function db_end_round($game_id, $game_round_id, $player_points) {
	assert(is_array($player_points));
	assert(count($player_points) >= DEFAULT_PLAYERS);
	global $_db;
	_db_connect();
	// Round players table rows:
	$sql_round = <<<EOS
UPDATE game_round_players
SET points = ?
WHERE game_round_id = ? AND player_position = ?
EOS;
	$sql_game = <<<EOS
UPDATE game_players
SET total_points = total_points + ?
WHERE game_id = ? AND player_position = ?
EOS;
	$stm_round = $_db->prepare($sql_round);
	$stm_game = $_db->prepare($sql_game);
	foreach ($player_points as $position => $points) {
		// Update round points
		$params = [
				$points,
				$game_round_id,
				$position
		];
		$stm_round->execute($params);
		// Update game total points
		if ($points !== null) {
			// Do not update points for bye players (SQL int + NULL = NULL ~ 0 total points)
			$params = [
					$points,
					$game_id,
					$position
			];
		}
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
	$params = [$game_round_id];
	_db_prepare_execute($sql, $params);
	// Update the game row:
	_db_set_game_updated($game_id);
}


function db_end_normal_round($game_id, $game_round_id, $bid_winner_mate_position, $tricks, $player_points) {
	_db_beginTransaction();
	$sql = <<<EOS
UPDATE normal_game_rounds
SET
bid_winner_mate_position = ?,
tricks = ?
WHERE game_round_id = ?
EOS;
	$params = [
			$bid_winner_mate_position,
			$tricks,
			$game_round_id
	];
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
		$params = [
				$tricks,
				$game_round_id,
				$position
		];
		$stm->execute($params);
	}
	db_end_round($game_id, $game_round_id, $player_points);
	_db_commit();
}


function _db_set_game_updated($game_id) {
	// Game table row:
	$sql = <<<SQL
UPDATE games
SET updated_at = NOW()
WHERE id = ?
SQL;
	$params = [$game_id];
	_db_prepare_execute($sql, $params);
}

