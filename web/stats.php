<?php

require("lib.php");


check_request_method("GET");


$games = db_get_game_list(0, 10000);
// Sort/filer $games
$games = array_reverse($games);
$filtered_games = [];
foreach ($games as $game) {
  if (strpos(strtolower($game['description']), "test") !== false) continue;
  else $filtered_games[] = $game;
}
$games = $filtered_games;

// Statistic work
$rounds = [];
$global_players = [];
$cumulative = [];
$sum = [];
// TODO consider creating a counter for round number - since some are test

foreach ($games as $game) {
  $id = $game['id'];
  $db_game = db_get_game_with_players($id);

  $total_points = [];
  $max_players = 0;
  foreach ($db_game['players'] as $player) {
    $p = array_filter_entries($player, "", ["id", "nickname", "fullname"]);
    $global_players[$p['id']] = $p;
    $total_points[$p['id']] = $player['total_points'];
    $sum[$p['id']] += $player['total_points'];
  }

  $rounds[] = [
    'round' => $id,
    'points' => $total_points
  ];
}

// Calculate cumulative sum - after all rounds, to make sure all players are represented
foreach ($global_players as $player) {
  $pid = $player['id'];
  $cumulative[$pid][] = 0;
}

foreach ($rounds as $round) {
  foreach ($global_players as $player) {
    $pid = $player['id'];
    $points = $cumulative[$pid][count($cumulative[$pid]) -1];
    if (array_key_exists($pid, $round['points'])) {
      $cumulative[$pid][] = $points + $round['points'][$pid];
    } else {
      $cumulative[$pid][] = $points;
    }
  }
}

$data = [
  'rounds' => $rounds,
  'players' => $global_players,
  'sum' => $sum,
  'cumulative' => $cumulative
];
render_page("Statistics", "Statistics", "stats", $data);

