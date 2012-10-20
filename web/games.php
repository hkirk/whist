<?php

require("lib.php");

if (request_method() !== "GET") {
	return;
}


$subtitle = "Games";
$headline = "The games";
$view = "games";
$data = Array(
	'games' => array()
);

render_page($subtitle, $headline, $view, $data);
