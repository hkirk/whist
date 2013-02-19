<?php

require("lib.php");

check_request_method("GET");


$id = check_get_uint($_GET, 'id');
check_input($id);


function game_render_error($key) {
	$data = array(
		'unknown_game' => FALSE,
		'bad_rounds' => FALSE,
		'inconsistent_points' => FALSE
	);
	$data[$key] = TRUE;
	render_page("Error", "Error", 'game_error', $data);
	exit;
}


$db_game = db_get_game_with_players($id);
if ($db_game === NULL) {
	game_render_error('unknown_game');
}

$location = $db_game['location'];

$players = array();
$total_points = array();
foreach ($db_game['players'] as $player) {
	$players[] = array_filter_entries($player, "", array("nickname", "fullname"));
	$total_points[] = $player['total_points'];
}
$point_rules = &$db_game['point_rules'];

$db_game_with_active_round = db_get_game_type_with_active_round($id);


$active_round = $db_game_with_active_round['active_round'];
//printf("<pre>");
//printf("Active round:");
//var_dump($active_round);
//printf("</pre>");

if ($active_round === NULL) {
	$controls_view = 'beginround';
	$cancel_view = NULL;
	$legal_attachment_keys = array();
	foreach ($ATTACHMENT_KEY_ORDER as $attachment_key) {
		if (in_array($attachment_key, $REQUIRED_ATTACHMENT_KEYS_ORDER) || in_array($attachment_key, $db_game['attachments'])) {
			$legal_attachment_keys[] = $attachment_key;
		}
	}
	$is_tips_legal = in_array(TIPS, $legal_attachment_keys);
	$tips_count = in_array(POINT_RULE_TIPS, $point_rules);
	//printf("Tips count: %s",$tips_count);
	$cancel_view_data = NULL;
	$controls_view_data = array(
		'is_tips_legal' => $is_tips_legal,
		'tips_count' => $tips_count,
		'legal_attachment_keys' => $legal_attachment_keys
	);
} else {
	$controls_view = 'endround';
	$cancel_view = 'cancelactiveround';
	$bid_type = $active_round['bid_type'];
	$bid_data = $active_round['bid_data'];
	if ($bid_type === 'normal') {
		$bid_winner_positions = array($bid_data['bid_winner_position']);
	} else {
		$bid_winner_positions = array_keys($bid_data['bid_winner_tricks_by_position']);
	}
	$cancel_view_data = array(
		'game_id' => &$id
	);
	$controls_view_data = array(
		'bid_type' => $bid_type,
		'bid_winner_positions' => $bid_winner_positions
	);
}



$db_rounds = db_get_game_rounds($id);
if ($db_rounds === NULL) {
	game_render_error('bad_rounds');
}

$acc_total_points = array_fill(0, 4, 0);

$rounds = array();

//
// Build round information for each round
//
foreach ($db_rounds as $r) {
	$bid_type = $r['bid_type'];
	$data = $r['bid_data'];
	if ($bid_type === "normal") {
		$bid = array(
			'tricks' => $data['bid_tricks'],
			'attachment' => $data['bid_attachment']
		);
		$bid_winner_tricks_by_position = array($data['bid_winner_position'] => $data['tricks']);
		$bid_winner_mate_position = $data['bid_winner_mate_position'];
	} else if ($bid_type === "solo") {
		$bid = array(
			'solo_type' => $data['type']
		);
		$bid_winner_tricks_by_position = $data['bid_winner_tricks_by_position'];
		$bid_winner_mate_position = NULL;
	} else {
		assert(FALSE);
	}
	$bid['type'] = $bid_type;
	$player_data = array();
	foreach ($r['player_points'] as $position => $player_points) {
		if ($player_points !== NULL) {
			$acc_total_points[$position] += $player_points;
		}
		$player_data[] = array(
			'round_points' => $player_points,
			'total_points' => $acc_total_points[$position]
		);
	}
	$round = array(
		'index' => $r['round'],
		'dealer_position' => $r['round'] % 4,
		'player_data' => $player_data,
		'bid' => $bid,
		'bid_winner_tricks_by_position' => $bid_winner_tricks_by_position,
		'bid_winner_mate_position' => $bid_winner_mate_position
	);
	$rounds[] = $round;
}
/*
  printf("DB:");
  var_dump($db_rounds);
  printf("<p>View:");
  var_dump($rounds);
  printf("</p>");
  printf("<p>TP:");
  var_dump($total_points);
  printf("</p>");
  printf("<p>ACC TP:");
  var_dump($acc_total_points);
  printf("</p>");
 */

// TODO reactivate
// Consistency check
//if ($acc_total_points !== $total_points) {
//	game_render_error('inconsistent_points');
//}

$controls_view_data = array_merge($controls_view_data, array(
	'game_id' => &$id,
	'players' => &$players,
	'point_rules' => &$point_rules
		));


$data = array(
	'game_id' => &$id,
	'location' => &$location,
	'players' => &$players,
	'rounds' => &$rounds,
	'total_points' => &$total_points,
	'cancel_view' => &$cancel_view,
	'cancel_view_data' => &$cancel_view_data,
	'controls_view' => &$controls_view,
	'controls_view_data' => &$controls_view_data
);

$title = "Whist game at " . $location;

render_page($title, $title, "game", $data);
