<?php

$_db = NULL;


function db_connect() {
	global $_db;
	if (!$_db) {
		db_force_connect();
	}
}


function db_force_connect() {
	global $_db;
	global $SETTINGS;
	$s = $SETTINGS['database'];
	$host = $s['host'];
	$name = $s['name'];
	$dsn = "mysql:host=$host;dbname=$name;charset=UTF-8";
	$attributes = array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	);
	$_db = new PDO($dsn, $s['username'], $s['password'], $attributes);
	return $_db;
}


function db_load_players() {
	global $_db;
	db_connect();
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


// Map with values as sets (map with truth value)
$VALID_VALUES = array(
	'attachments' => array(
		'sans' => TRUE,
		'tips' => TRUE,
		'strongs' => TRUE,
		'halves' => TRUE
	),
	'point_rules' => array(
		'reallybad' => TRUE,
		'tiptricks' => TRUE,
		'solotricks' => TRUE
	)
);


/**
 * Creates a new game.
 *
 * @global DBO $_db The DBO connection
 * @param string $location
 * @param string $description
 * @param array#4(int) $player_ids
 * @param array(string) $attachments
 * @param array(string) $point_rules
 */
function db_create_game($location, $description, $player_ids, $attachments, $point_rules) {
	assert(count($player_ids) === 4);
	global $_db;
	db_beginTransaction();
	$sql = <<<EOS
INSERT INTO games
(location, description, attachments, point_rules, started_at, ended_at, updated_at)
VALUES
(?, ?, ?, ?, NOW(), NULL, NOW())
EOS;
	$params = array(
		$location,
		$description,
		db_set_string($attachments),
		db_set_string($point_rules)
	);
	db_prepare_execute($sql, $params);
	$game_id = $_db->lastInsertId();
	$sql = <<<EOS
INSERT INTO game_players
(game_id, player_position, player_id, total_points)
VALUES
(?, ?, ?, 0)
EOS;
	$stm = $_db->prepare($sql);
	foreach ($player_ids as $index => $player_id) {
		$params = array(
			$game_id,
			$index,
			$player_id
		);
		$result = $stm->execute($params);
	}
	db_commit();
	return $game_id;
}


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
	db_prepare_execute($sql, $params);
	$id = $_db->lastInsertId();
	return $id;
}


function db_create_normal_round($game_id, $bid_tricks, $bid_attachment, $bid_winner_position, $tips = NULL) {
	if ($bid_attachment === 'tips') {
		assert(is_int($tips) && $tips >= 1 && $tips <= 3);
	} else {
		assert(is_null($tips));
	}
	db_assert_player_position($bid_winner_position);
	db_beginTransaction();
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
	db_prepare_execute($sql, $params);
	db_commit();
	return $game_round_id;
}


function db_create_solo_round($game_id, $solo_type, $bid_winner_positions) {
	db_assert_player_positions($bid_winner_positions);
	assert(count($bid_winner_positions) > 0);
	global $_db;
	db_beginTransaction();
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
	db_prepare_execute($sql, $params);
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
	db_commit();
	return $game_round_id;
}


function db_end_round($id, $player_points) {
	assert(is_array($player_points));
	assert(count($player_points) === 4);
	global $_db;
	db_connect();
	// Round players table rows:
	$sql = <<<EOS
INSERT INTO game_round_players
(game_round_id, player_position, points)
VALUES(?, ?, ?)
EOS;
	$stm = $_db->prepare($sql);
	foreach ($player_points as $position => $points) {
		$params = array(
			$id,
			$position,
			$points
		);
		$stm->execute($params);
	}
	// Rounds table row:
	$sql = <<<EOS
UPDATE game_rounds
SET
ended_at = NOW(),
updated_at = NOW()
WHERE id = ?
EOS;
	$params = array($id);
	db_prepare_execute($sql, $params);
}


function db_end_normal_round_get_data($id) {
	db_beginTransaction();
	$sql = <<<EOS
SELECT bid_winner_position, bid_tricks, bid_attachment, tips
FROM normal_game_rounds
WHERE game_round_id = ?
EOS;
	$params = array($id);
	$row = db_prepare_execute_fetch($sql, $params);
	if ($row === NULL) {
		error_log("Row not found for normal game round $id!");
		return NULL;
	}
	$bid_winner_position = $row['bid_winner_position'];
	$bid_tricks = $row['bid_tricks'];
	$bid_attachment = $row['bid_attachment'];
	$tips = $row['tips'];
	return $row;
}


function db_end_normal_round($id, $bid_winner_mate_position, $tricks, $player_points) {
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
		$id
	);
	db_prepare_execute($sql, $params);
	db_end_round($id, $player_points);
	db_commit();
}


function db_end_solo_round_get_data($id) {
	db_beginTransaction();
	$sql = <<<EOS
SELECT solo_type, player_position
FROM solo_game_rounds AS r
INNER JOIN solo_game_round_bid_winners
USING(game_round_id)
WHERE r.game_round_id = ?
EOS;
	$params = array($id);
	list(,, $rows) = db_prepare_execute_fetchAll($sql, $params);
	//assert(count($rows) === 4);
	$n_rows = count($rows);
	if ($n_rows < 1 || $n_rows > 4) {
		error_log("Invalid number of rows ($n_rows) for solo game round $id!");
		return NULL;
	}
	$solo_type = $rows[0]['solo_type'];
	$bid_winner_positions = array();
	foreach ($rows as $row) {
		$bid_winner_solo_type = $row['solo_type'];
		$bid_winner_position = $row['player_position'];
		assert($solo_type === $bid_winner_solo_type);
		$bid_winner_positions[] = $bid_winner_position;
	}
	return array($solo_type, $bid_winner_positions);
}


function db_end_solo_round($id, $bid_winner_tricks_by_position, $player_points) {
	global $_db;
	db_connect();
	$sql = <<<EOS
UPDATE solo_game_round_players
SET tricks = ?,
WHERE game_round_id = ? AND player_position = ?
EOS;
	$stm = $_db->prepare($sql);
	foreach ($bid_winner_tricks_by_position as $position => $tricks) {
		$params = array(
			$tricks,
			$id,
			$position
		);
		$stm->execute($params);
	}
	db_end_round($id, $player_points);
	db_commit();
}


function db_get_game_rounds($game_id) {
	db_beginTransaction();
	// Round data:
	$sql = <<<EOS
SELECT 
	gr.*,
	ngr.bid_winner_position       AS normal_bid_winner_position,
	ngr.bid_winner_mate_position  AS normal_bid_winner_mate_position,
	ngr.bid_tricks                AS normal_bid_tricks,
	ngr.bid_attachment            AS normal_bid_attachment,
	ngr.tricks                    AS normal_tricks,
	ngr.tips                      AS normal_tips,
	sgr.solo_type                 AS solo_type,
	sgrbw.player_position         AS solo_player_position,
	sgrbw.tricks                  AS solo_tricks
FROM game_rounds AS gr
LEFT OUTER JOIN normal_game_rounds ngr ON ngr.game_round_id = gr.id
LEFT OUTER JOIN solo_game_rounds AS sgr ON sgr.game_round_id = gr.id
LEFT OUTER JOIN solo_game_round_bid_winners AS sgrbw ON sgrbw.game_round_id = sgr.game_round_id
WHERE gr.game_id = ?
ORDER BY gr.round ASC, sgrbw.player_position ASC
EOS;
	$params = array($game_id);
	list($stm, ) = db_prepare_execute($sql, $params);
	$rounds = array();
	$expected_round = 0;
	$round_data = NULL;
	$solo_data = NULL;
	$last_round = NULL;
	$last_bid_type = NULL;
	while ($row = $stm->fetch()) {
		$round = (int) $row['round'];
		$bid_type = $row['bid_type'];
		if ($last_round !== $round) {
			// New round
			$expected_round++;
			db_get_game_rounds_commit_solo_round($solo_data, $round_data, $rounds);
			$round_data = array(
				'round' => $round,
				'bid_type' => $bid_type,
				'started_at' => $row['started_at'],
				'ended_at' => $row['ended_at'],
				'updated_at' => $row['updated_at']
			);
			$solo_data = NULL;
		} else {
			assert($last_bid_type === $bid_type);
		}
		assert($round === $expected_round);
		if ($bid_type === "normal") {
			assert($round !== $last_round);
			$round_data['bid_data'] = array_filter_entries($row, 'normal_', array(
				'bid_winner_position',
				'bid_winner_mate_position',
				'bid_tricks',
				'bid_attachment',
				'tricks',
				'tips'));
			// Commit round data
			$rounds[] = $round_data;
		} else if ($bid_type === "solo") {
			if ($solo_data === NULL) {
				// First solo player
				assert($round !== $last_round);
				$solo_data = array(
					'type' => $row['solo_type'],
					'bid_winner_tricks_by_position' => array()
				);
			} else {
				assert($round === $last_round);
			}
			$player_position = $row['solo_player_position'];
			$tricks = $row['solo_tricks'];
			$solo_data['bid_winner_tricks_by_position'][$player_position] = $tricks;
		} else {
			assert(FALSE);
		}
		$last_round = $round;
		$last_bid_type = $bid_type;
	}
	db_get_game_rounds_commit_solo_round($solo_data, $round_data, $rounds);
	// Player points:
	$sql = <<<EOS
SELECT 
	gr.round             AS round,
	grp.game_round_id    AS player_game_round_id,
	grp.player_position  AS player_position,
	grp.points           AS player_points
FROM game_rounds AS gr
LEFT OUTER JOIN game_round_players AS grp ON grp.game_round_id = gr.id
WHERE gr.game_id = ?
ORDER BY gr.round, grp.player_position
EOS;
	$params = array($game_id);
	list($stm, ) = db_prepare_execute($sql, $params);
	$index = 0;
	$player_points = NULL;
	$last_round = NULL;
	while ($row = $stm->fetch()) {
		$round = (int) $row['round'];
		if ($row['player_game_round_id'] === NULL) {
			// Unfinished round
			assert($player_points === NULL); // Points are commited
			assert($round !== $last_round);
			$player_points = array_fill(0, 4, NULL);
			$commit = TRUE;
		} else {
			if ($player_points === NULL) {
				assert($round !== $last_round);
				$expected_player_position = 0;
				$player_points = array();
			} else {
				assert($round === $last_round);
			}
			$player_position = (int) $row['player_position'];
			assert($player_position === $expected_player_position);
			$player_points[] = $row['points'];
			$commit = $player_position === 3;
			$expected_player_position++;
		}
		if ($commit) {
			$round_data = &$rounds[$index];
			assert($round_data['round'] === $round);
			$round_data['player_points'] = $player_points;
			$player_points = NULL;
			$index++;
		}
		$last_round = $round;
	}
	db_commit();
	// Return
	return $rounds;
}


function db_get_game_rounds_commit_solo_round($solo_data, $round_data, &$rounds) {
	if ($solo_data !== NULL) {
		$round_data['bid_data'] = $solo_data;
		$rounds[] = $round_data;
	}
}


function db_get_game($game_id) {
	$sql = <<<EOS
SELECT 
	g.*,
	gp.player_position AS player_position,
	gp.total_points    AS player_total_points,
	p.nickname         AS player_nickname,
	p.fullname         AS player_fullname
FROM games AS g 
INNER JOIN game_players AS gp ON gp.game_id = g.id
LEFT OUTER JOIN players AS p ON p.id = gp.player_id 
WHERE g.id = ?
ORDER BY gp.player_position ASC
EOS;
	$params = array($game_id);
	list(,, $rows) = db_prepare_execute_fetchAll($sql, $params);
	$n_rows = count($rows);
	if ($n_rows !== 4) {
		error_log("Invalid number of rows $n_rows");
		return NULL;
	}
	$players = array();
	foreach ($rows as $index => $row) {
		printf("pos: %s", $row['player_position']);
		assert((string) $index === $row['player_position']);
		$player = array_filter_entries($row, 'player_', array('nickname', 'fullname'));
		$player['total_points'] = (int) $row['player_total_points'];
		$players[] = $player;
	}
	$game = array_filter_entries($rows[0], '', array('location', 'description', 'started_at', 'ended_at', 'updated_at'));
	$game['players'] = $players;
	return $game;
}


/**
 * 
 * @param type $game_id
 * @return null, 'none', 'solo', or 'normal'
 */
function db_get_active_round_bid_type($game_id) {
	$sql = <<<EOS
SELECT gr.bid_type AS bid_type, gr.ended_at AS ended_at
FROM games AS g 
LEFT OUTER JOIN game_rounds AS gr ON g.id = gr.game_id
WHERE gr.game_id = ?
ORDER BY gr.round DESC
LIMIT 1
EOS;
	$params = array($game_id);
	list(,, $row) = db_prepare_execute_fetch($sql, $params);
	var_dump($row);
	if ($row === NULL) {
		// No such game!
		return NULL;
	}
	if ($row['ended_at'] !== NULL) {
		// The latest round is ended
		return 'none';
	}
	return $row['bid_type'];
}


function db_check_player_ids($player_ids) {
	$n_players = count($player_ids);
	if ($n_players === 0) {
		return TRUE;
	}
	$placeholders_list = db_placeholders_list($player_ids);
	$sql = <<<EOS
SELECT COUNT(*) AS count
FROM players
WHERE id IN ($placeholders_list)
EOS;
	list(,, $row) = db_prepare_execute_fetch($sql, $player_ids);
	var_dump($row);
	$count = $row['count'];
	echo "Count: ";
	echo $count;
	return (int) $count === count($player_ids);
}


//
// Helpers:
//

function db_set_string($array) {
	return implode(",", $array);
}


function db_placeholders_list($array) {
	$length = count($array);
	if ($length === 0) {
		return "";
	} else {
		return "?" . str_repeat(", ?", $length - 1);
	}
}


function db_quoted_values_list($array) {
	global $_db;
	$quoted_array = array();
	foreach ($array as $value) {
		$quoted_array[] = $_db->quote($value);
	}
	return implode(", ", $quoted_array);
}


function db_assert_player_position($position) {
	assert(is_int($position) && $position >= 0 && $position <= 3);
}


function db_assert_player_positions($positions) {
	assert(is_array($positions));
	foreach ($positions as $position) {
		db_assert_player_position($position);
	}
}


function db_beginTransaction() {
	global $_db;
	db_connect();
	$_db->beginTransaction();
}


function db_commit() {
	global $_db;
	db_connect();
	$_db->commit();
}


function db_prepare_execute($sql, $params = array()) {
	global $_db;
	db_connect();
	$stm = $_db->prepare($sql);
	$result = $stm->execute($params);
	return array($stm, $result);
}


function db_prepare_execute_fetch($sql, $params = array()) {
	list($stm, $result) = db_prepare_execute($sql, $params);
	if ($result) {
		$row = $stm->fetch();
	} else {
		$row = NULL;
	}
	return array($stm, $result, $row);
}


function db_prepare_execute_fetchAll($sql, $params = array()) {
	list($stm, $result) = db_prepare_execute($sql, $params);
	if ($result) {
		$rows = $stm->fetchAll();
	} else {
		$rows = NULL;
	}
	return array($stm, $result, $rows);
}


