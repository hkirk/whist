<?php


function db_get_game_list($offset, $limit) {
	$sql = <<<EOS
SELECT 
g.id AS id,
g.started_at AS started_at,
g.ended_at AS ended_at,
g.updated_at AS updated_at,
l.name AS location
FROM games AS g
LEFT OUTER JOIN locations l
ON g.location_id = l.id
ORDER BY g.started_at DESC

EOS;
	$params = array($offset, $limit);
	list(,, $rows) = _db_prepare_execute_fetchAll($sql, $params);
	return $rows;
}


/**
 * Creates a new game.
 *
 * @global DBO $_db The DBO connection
 * @param int $location_id
 * @param string $description
 * @param array#4(int) $player_ids
 * @param array(string) $attachments
 * @param array(string) $point_rules
 */
function db_create_game($location_id, $description, $player_ids, $attachments, $point_rules) {
	assert(count($player_ids) === 4);
	global $_db;
	_db_beginTransaction();
	$sql = <<<EOS
INSERT INTO games
(location_id, description, attachments, point_rules, started_at, ended_at, updated_at)
VALUES
(?, ?, ?, ?, NOW(), NULL, NOW())
EOS;
	$params = array(
		$location_id,
		$description,
		_db_set_string_from_array($attachments),
		_db_set_string_from_array($point_rules)
	);
	_db_prepare_execute($sql, $params);
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
	_db_commit();
	return $game_id;
}


$_DB_ROUND_TYPES_SELECT = <<<EOS
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
EOS;

$_DB_ROUND_TYPES_JOINS = <<<EOS
LEFT OUTER JOIN normal_game_rounds AS ngr ON ngr.game_round_id = gr.id
LEFT OUTER JOIN solo_game_rounds AS sgr ON sgr.game_round_id = gr.id
LEFT OUTER JOIN solo_game_round_bid_winners AS sgrbw ON sgrbw.game_round_id = sgr.game_round_id            
EOS;


function db_get_game_rounds($game_id) {
	global $_DB_ROUND_TYPES_SELECT;
	global $_DB_ROUND_TYPES_JOINS;
	_db_beginTransaction();
	// Round data:
	$sql = <<<EOS
SELECT
    $_DB_ROUND_TYPES_SELECT
FROM game_rounds AS gr
$_DB_ROUND_TYPES_JOINS
WHERE gr.game_id = ?
ORDER BY gr.round ASC, sgrbw.player_position ASC
EOS;
	$params = array($game_id);
	list($stm, ) = _db_prepare_execute($sql, $params);
	$rounds = _db_build_game_rounds_from_traversable($stm);
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
	list($stm, ) = _db_prepare_execute($sql, $params);
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
			$player_points[] = $row['player_points'];
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
	_db_commit();
	// Return
	return $rounds;
}


function db_get_game_with_players($game_id) {
	$sql = <<<EOS
SELECT 
	g.*,
	l.name             AS location,
	gp.player_position AS player_position,
	gp.total_points    AS player_total_points,
	p.nickname         AS player_nickname,
	p.fullname         AS player_fullname
FROM games AS g
LEFT OUTER JOIN locations AS l ON l.id = g.location_id
INNER JOIN game_players AS gp ON gp.game_id = g.id
LEFT OUTER JOIN players AS p ON p.id = gp.player_id 
WHERE g.id = ?
ORDER BY gp.player_position ASC
EOS;
	$params = array($game_id);
	list(,, $rows) = _db_prepare_execute_fetchAll($sql, $params);
	$n_rows = count($rows);
	if ($n_rows !== 4) {
		error_log("Invalid number of rows $n_rows");
		return NULL;
	}
	$players = array();
	foreach ($rows as $index => $row) {
		//printf("pos: %s", $row['player_position']);
		assert((string) $index === $row['player_position']);
		$player = array_filter_entries($row, 'player_', array('nickname', 'fullname'));
		$player['total_points'] = (int) $row['player_total_points'];
		$players[] = $player;
	}
	$first_row = $rows[0];
	$game = array_filter_entries($first_row, '', array('location', 'description', 'started_at', 'ended_at', 'updated_at'));
	$game['attachments'] = _db_set_array_from_string($first_row['attachments']);
	$game['point_rules'] = _db_set_array_from_string($first_row['point_rules']);
	$game['players'] = $players;
	return $game;
}


/**
 * 
 * @param type $game_id
 * @return Game array with keys 'attachments', 'point_rules', and 'active_round'. The latter is NULL, if there is no active game round.
 */
function db_get_game_type_with_active_round($game_id) {
	global $_DB_ROUND_TYPES_SELECT;
	global $_DB_ROUND_TYPES_JOINS;
	// The LIMIT is the maximum number of solo bid winner rows
	$sql = <<<EOS
SELECT 
	g.attachments AS attachments,
	g.point_rules AS point_rules,
	gr.id AS gr_id,
	$_DB_ROUND_TYPES_SELECT
FROM games AS g 
LEFT OUTER JOIN game_rounds AS gr ON g.id = gr.game_id
$_DB_ROUND_TYPES_JOINS
WHERE g.id = ?
ORDER BY gr.round DESC
LIMIT 4
EOS;
	$params = array($game_id);
	list(,, $rows) = _db_prepare_execute_fetchAll($sql, $params);
	if (count($rows) === 0) {
		// No such game
		return NULL;
	}
	$row = $rows[0];
	$game = array(
		'attachments' => _db_set_array_from_string($row['attachments']),
		'point_rules' => _db_set_array_from_string($row['point_rules']),
	);
	if ($rows[0]['gr_id'] === NULL) {
		// No rounds
		$game['active_round'] = NULL;
		return $game;
	}
	$rounds = _db_build_game_rounds_from_traversable($rows, NULL);
//    printf("Rounds: ");
//    var_dump($rounds);
//    printf("Rounds done");
	if (count($rounds) < 1) {
		// Hmmm, Invalid number of rounds
		assert(FALSE);
		return NULL;
	}
	$most_recent_round = $rounds[0];
	if ($most_recent_round['ended_at'] === NULL) {
		$game['active_round'] = $most_recent_round;
	} else {
		$game['active_round'] = NULL;
	}
	return $game;
}


function _db_build_game_rounds_from_traversable($traversable, $expected_round = 0) {
	$rounds = array();
	$round_data = NULL;
	$solo_data = NULL;
	$last_round = NULL;
	$last_bid_type = NULL;
	foreach ($traversable as $row) {
		$round = (int) $row['round'];
		$bid_type = $row['bid_type'];
		if ($last_round !== $round) {
			// New round
			if ($expected_round !== NULL) {
				$expected_round++;
			}
			_db_build_game_rounds_from_traversable_commit_solo_round($solo_data, $round_data, $rounds);
			$round_data = array(
				'id' => $row['id'],
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
		assert($expected_round === NULL || $round === $expected_round);
		if ($bid_type === "normal") {
			assert($round !== $last_round);
			$bid_data = array_filter_entries($row, 'normal_', array(
				'bid_winner_position',
				'bid_winner_mate_position',
				'bid_tricks',
				'bid_attachment',
				'tricks',
				'tips'));
			array_convert_numerics_to_ints($bid_data);
			$round_data['bid_data'] = $bid_data;
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
	_db_build_game_rounds_from_traversable_commit_solo_round($solo_data, $round_data, $rounds);
	return $rounds;
}


function _db_build_game_rounds_from_traversable_commit_solo_round($solo_data, $round_data, &$rounds) {
	if ($solo_data !== NULL) {
		$round_data['bid_data'] = $solo_data;
		$rounds[] = $round_data;
	}
}