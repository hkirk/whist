<?php
require("../lib/lib.php");

$conn = db_connect();

$stm = $conn->query("SELECT * FROM games");

$subtitle = "Games";
$headline = "The games";
$view = "games";
$data = Array(
	'stm' => $stm
);

render_page($subtitle, $headline, $view, $data);

/*
printf("Games:");
foreach($stm as $row) {
	printf("Game: ".$row['id']);
}
*/