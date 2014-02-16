<?php


function db_get_game_list($offset, $limit) {
	$sql = <<<EOS
SELECT 
g.id AS id,
g.started_at AS started_at,
g.ended_at AS ended_at,
g.updated_at AS updated_at,
l.name AS location, (
	SELECT COUNT(*)
	FROM game_players AS gp
	WHERE gp.game_id = g.id
) AS n_players					
FROM games AS g
LEFT OUTER JOIN locations l
ON g.location_id = l.id
ORDER BY g.started_at DESC

EOS;
	$params = [$offset, $limit];
	list(,, $rows) = _db_prepare_execute_fetchAll($sql, $params);
	return $rows;
}


/**
 * TODO move out of games.php
 * @param $name The name of the location.
 */
function db_create_location($name) {
	_db_beginTransaction();
	$sql = "INSERT INTO locations (name) VALUES (?)";
	$params = [$name];
	_db_prepare_execute($sql, $params);
	_db_commit();
}


/**
 * TODO move out of games.php
 *
 * @param $name The full name.
 * @param $nickname The players nickname,
 */
function db_create_player($name, $nickname) {
	_db_beginTransaction();
	$sql = "INSERT INTO players (fullname, nickname) VALUES (?, ?)";
	$params = [$name, $nickname];
	_db_prepare_execute($sql, $params);
	_db_commit();
}


/**
 * Creates a new game.
 *
 * @global DBO $_db The DBO connection
 * @param int $location_id
 * @param string $description
 * @param array(int) $player_ids
 * @param array(string) $attachments
 * @param array(string) $point_rules
 */
function db_create_game($location_id, $description, $player_ids, $attachments, $point_rules) {
	assert(count($player_ids) >= DEFAULT_PLAYERS);
	global $_db;
	_db_beginTransaction();
	$sql = <<<EOS
INSERT INTO games
(location_id, description, attachments, point_rules, started_at, ended_at, updated_at)
VALUES (?, ?, ?, ?, NOW(), NULL, NOW())
EOS;
	$params = [
			$location_id,
			$description,
			_db_set_string_from_array($attachments),
			_db_set_string_from_array($point_rules)
	];
	_db_prepare_execute($sql, $params);
	$game_id = $_db->lastInsertId();
	$sql = <<<EOS
INSERT INTO game_players
(game_id, player_position, player_id, total_points)
VALUES (?, ?, ?, 0)
EOS;
	$stm = $_db->prepare($sql);
	foreach ($player_ids as $index => $player_id) {
		$params = [$game_id, $index, $player_id];
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
LEFT OUTER JOIN  normal_game_rounds          AS ngr    ON ngr.game_round_id = gr.id
LEFT OUTER JOIN  solo_game_rounds            AS sgr    ON sgr.game_round_id = gr.id
LEFT OUTER JOIN  solo_game_round_bid_winners AS sgrbw  ON sgrbw.game_round_id = sgr.game_round_id
EOS;


function db_get_game_rounds($game_id, $n_players) {
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
	$params = [$game_id];
	list($stm, ) = _db_prepare_execute($sql, $params);
	$rounds = _db_build_game_rounds_from_traversable($stm);

	// Player points:
	$sql = <<<EOS
SELECT 
	gr.round             AS round,
	gr.ended_at          AS ended_at,
	grp.game_round_id    AS player_game_round_id,
	grp.player_position  AS player_position,
	grp.bye              AS player_bye,
	grp.points           AS player_points
FROM             game_rounds        AS gr
LEFT OUTER JOIN  game_round_players AS grp  ON grp.game_round_id = gr.id
WHERE gr.game_id = ?
ORDER BY gr.round, grp.player_position
EOS;
	$params = [$game_id];
	list($stm, ) = _db_prepare_execute($sql, $params);
	$index = 0;
	$player_data = [];
	$n_non_bye_players = 0;
	$last_round = NULL;
	while ($row = $stm->fetch()) {
		$round = (int) $row['round'];
		if ($row['player_game_round_id'] === NULL) {
			// No player rows for the round!
			throw new WhistException("No player rows for round $round!");
		}
		$is_active_round = $row['ended_at'] === NULL;
		$is_bye = (bool) $row['player_bye'];
		$points = $row['player_points'];
		$player_position = (int) $row['player_position'];
		$expected_player_position = count($player_data);
		if ($is_active_round && $points !== NULL) {
			throw new WhistException("Non-null point for active round $round!");
		}
		if (!$is_active_round && $points === NULL && !$is_bye) {
			throw new WhistException("Null points for non-bye player in finished round $round!");
		}
		if ($is_bye && $points !== null) {
			throw new WhistException("Non-null points for bye player for round $round!");
		}
		if ($last_round != $round && $expected_player_position != 0) {
			throw new WhistException("Missing player rows for round $round!");
		}
		if ($last_round == $round && $expected_player_position == 0) {
			throw new WhistException("Too many player rows for round $round!");
		}
		if ($player_position != $expected_player_position) {
			throw new WhistException("Invalid player position $player_position (expected $expected_player_position) for round $round!");
		}

		if (!$is_bye) {
			$n_non_bye_players++;
		}

		$player_data[] = [
				'points' => $points, // TODO Is NULL for active round, which is fine, but is also for bye players. Consider this
				'is_bye' => $is_bye
		];

		if ($expected_player_position == $n_players - 1) {
			if ($n_non_bye_players != DEFAULT_PLAYERS) {
				throw new WhistException("Invalid number of non-bye players $n_non_bye_players for round $round!");
			}
			$round_data = &$rounds[$index];
			assert($round_data['round'] === $round);
			$round_data['player_data'] = $player_data;
			$player_data = [];
			$n_non_bye_players = 0;
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
FROM             games        AS g
LEFT OUTER JOIN  locations    AS l   ON l.id = g.location_id
INNER JOIN       game_players AS gp  ON gp.game_id = g.id
LEFT OUTER JOIN  players      AS p   ON p.id = gp.player_id
WHERE g.id = ?
ORDER BY gp.player_position ASC
EOS;
	$params = [$game_id];
	list(,, $rows) = _db_prepare_execute_fetchAll($sql, $params);
	$n_rows = count($rows);
	if ($n_rows < DEFAULT_PLAYERS) {
		error_log("Invalid number of rows $n_rows");
		return NULL;
	}
	$players = [];
	foreach ($rows as $index => $row) {
		//printf("pos: %s", $row['player_position']);
		assert((string) $index === $row['player_position']);
		$player = array_filter_entries($row, 'player_', ['nickname', 'fullname']);
		$player['total_points'] = (int) $row['player_total_points'];
		$players[] = $player;
	}
	$first_row = $rows[0];
	$game = array_filter_entries($first_row, '', ['location', 'description', 'started_at', 'ended_at', 'updated_at']);
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
	$limit = DEFAULT_PLAYERS + 1;
	// The LIMIT is the maximum number of solo bid winner rows + 1 to detect data inconsistency 
	// in _db_build_game_rounds_from_traversable()
	$sql = <<<EOS
SELECT 
	g.attachments AS attachments,
	g.point_rules AS point_rules,
	$_DB_ROUND_TYPES_SELECT
FROM             games       AS g 
LEFT OUTER JOIN  game_rounds AS gr  ON g.id = gr.game_id
$_DB_ROUND_TYPES_JOINS
WHERE g.id = ?
ORDER BY gr.round DESC
LIMIT $limit
EOS;
	$params = [$game_id];
	list(,, $rows) = _db_prepare_execute_fetchAll($sql, $params);
	if (count($rows) === 0) {
		// No such game
		return NULL;
	}
	$row = $rows[0];
	$game = [
			'attachments' => _db_set_array_from_string($row['attachments']),
			'point_rules' => _db_set_array_from_string($row['point_rules']),
	];
	if ($row['id'] === NULL) {
		// No rounds
		$game['active_round'] = NULL;
		return $game;
	}
	$rounds = _db_build_game_rounds_from_traversable($rows, NULL);
	if (count($rounds) < 1) {
		// Hmmm, Invalid number of rounds
		assert(FALSE);
		return NULL;
	}
	$most_recent_round = $rounds[0];
	if ($most_recent_round['ended_at'] === NULL) {
		$sql = <<<SQL
SELECT player_position, bye
FROM game_round_players
WHERE game_round_id = ?
ORDER BY player_position
SQL;
		$params = [$most_recent_round['id']];
		list($stm, ) = _db_prepare_execute($sql, $params);
		$expected_position = 0;
		$player_data = [];
		while ($row = $stm->fetch()) {
			assert($row['player_position'] == $expected_position);
			$player_data[] = [
					'is_bye' => (bool) $row['bye']
			];
			$expected_position++;
		}
		$most_recent_round['player_data'] = $player_data;
		$game['active_round'] = $most_recent_round;
	} else {
		$game['active_round'] = NULL;
	}
	return $game;
}


function _db_build_game_rounds_from_traversable($traversable, $expected_round = 0) {
	$rounds = [];
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
			$round_data = [
					'id' => $row['id'],
					'round' => $round,
					'dealer_position' => (int) $row['dealer_position'],
					'bid_type' => $bid_type,
					'started_at' => $row['started_at'],
					'ended_at' => $row['ended_at'],
					'updated_at' => $row['updated_at']
			];
			$solo_data = NULL;
		} else {
			assert($last_bid_type === $bid_type);
		}
		assert($expected_round === NULL || $round === $expected_round);
		if ($bid_type === "normal") {
			assert($round !== $last_round);
			$bid_data = array_filter_entries($row, 'normal_', [
					'bid_winner_position',
					'bid_winner_mate_position',
					'bid_tricks',
					'bid_attachment',
					'tricks',
					'tips']);
			array_convert_numerics_to_ints($bid_data);
			$round_data['bid_data'] = $bid_data;
			// Commit round data
			$rounds[] = $round_data;
		} else if ($bid_type === "solo") {
			if ($solo_data === NULL) {
				// First solo player
				assert($round !== $last_round);
				$solo_data = [
						'type' => $row['solo_type'],
						'bid_winner_tricks_by_position' => []
				];
			} else {
				assert($round === $last_round);
			}
			$player_position = $row['solo_player_position'];
			$tricks = $row['solo_tricks'];
			$solo_data['bid_winner_tricks_by_position'][$player_position] = $tricks;
			if (count($solo_data['bid_winner_tricks_by_position']) > DEFAULT_PLAYERS) {
				assert(FALSE);
			}
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

