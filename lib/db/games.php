<?php


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
	_db_beginTransaction();
	$sql = <<<EOS
INSERT INTO games
(location, description, attachments, point_rules, started_at, ended_at, updated_at)
VALUES
(?, ?, ?, ?, NOW(), NULL, NOW())
EOS;
	$params = array(
		$location,
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


function db_get_game_rounds($game_id) {
	_db_beginTransaction();
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
	list($stm, ) = _db_prepare_execute($sql, $params);
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
	_db_commit();
	// Return
	return $rounds;
}


function db_get_game_rounds_commit_solo_round($solo_data, $round_data, &$rounds) {
	if ($solo_data !== NULL) {
		$round_data['bid_data'] = $solo_data;
		$rounds[] = $round_data;
	}
}


function db_get_game_with_players($game_id) {
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
	list(,, $rows) = _db_prepare_execute_fetchAll($sql, $params);
	printf("Rows: ");
	var_dump($rows);
	printf("Rows done");
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
 * @return Game array with keys 'attachments', 'point_rules', and 'active_round_bid_type'. The latter is NULL, if there is no active game round
 */
function db_get_game_type_with_active_round_bid_type($game_id) {
	$sql = <<<EOS
SELECT 
	g.attachments AS attachments,
	g.point_rules AS point_rules,
	gr.bid_type AS bid_type, 
	gr.ended_at AS ended_at
FROM games AS g 
LEFT OUTER JOIN game_rounds AS gr ON g.id = gr.game_id
WHERE g.id = ?
ORDER BY gr.round DESC
LIMIT 1
EOS;
	$params = array($game_id);
	list(,, $row) = _db_prepare_execute_fetch($sql, $params);
	printf("Row: ");
	var_dump($row);
	printf("Row done");
	if ($row === FALSE) {
		// No such game!
		return NULL;
	}
	$game = array(
		'attachments' => _db_set_array_from_string($row['attachments']),
		'point_rules' => _db_set_array_from_string($row['point_rules']),
		'active_round_bid_type' => $row['ended_at'] === NULL ? $row['bid_type'] : NULL
	);
	return $game;
}


