<?php

require("../lib/lib.php");

if (request_method() !== "GET") {
	return;
}

$id = check_get_uint($_GET, 'id');

check_input($id);

$db_game = db_get_game($id);

$players = array();
$total_points = array();

foreach ($db_game['players'] as $player) {
	$players[] = array_filter_entries($player, "", array("nickname", "fullname"));
	$total_points[] = $player['total_points'];
}


$db_rounds = db_get_game_rounds($id);

$acc_total_points = array_fill(0, 4, 0);

$rounds = array();

foreach ($db_rounds as $r) {
	$bid_type = $r['bid_type'];
	$data = $r['bid_data'];
	if ($bid_type === "normal") {
		$bid = "${data['bid_tricks']} ${data['bid_attachment']}";
		$bid_winner_tricks_by_position = array($data['bid_winner_position'] => $data['tricks']);
		$bid_winner_mate_position = $data['bid_winner_mate_position'];
	} else if ($bid_type === "solo") {
		$bid = $data['type'];
		$bid_winner_tricks_by_position = $data['bid_winner_tricks_by_position'];
		$bid_winner_mate_position = NULL;
	} else {
		assert(FALSE);
	}
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
			'players' => $player_data,
			'bid' => $bid,
			'bid_winner_tricks_by_position' => $bid_winner_tricks_by_position,
			'bid_winner_mate_position' => $bid_winner_mate_position
	);
	$rounds[] = $round;
}

printf("DB:");
var_dump($db_rounds);
printf("<p>View:");
var_dump($rounds);
printf("</p>");
printf("<p>TP:");
var_dump($total_points);
printf("</p>");
assert($acc_total_points === $total_points);

$data = array(
		'game_id' => $id,
		'players' => $players,
		'rounds' => $rounds,
		'total_points' => $total_points,
		'controls_view' => 'endround',
		'controls_view_data' => array(
				'game_id' => $id,
				'players' => $players,
				'bid_type' => 'solo',
				'bid_winner_positions' => array(
						0, 2, 3
				)
		)
//'controls_view' => 'beginround',
// 'controls_view_data' => array(
//'game_id' => $id,
// 'players' => $players
//)
);

render_page("Game", "Game", "game", $data);
