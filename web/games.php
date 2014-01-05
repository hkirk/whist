<?php

require("lib.php");

check_request_method("GET");

$games = db_get_game_list(0, 10);

$subtitle = "Games";
$headline = "The games";
$view = "games";
$data = [
	'games' => $games
];

render_page($subtitle, $headline, $view, $data);
