<?php

require("lib.php");

check_request_method("GET");


$subtitle = "Games";
$headline = "The games";
$view = "games";
$data = Array(
	'games' => array()
);

render_page($subtitle, $headline, $view, $data);
