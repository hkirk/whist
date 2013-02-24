<?php

$lib_dir = dirname(__FILE__);
$conf_dir = $lib_dir . "/../conf";

// Config files:
require($conf_dir . "/settings.php");

// Lib files:
require($lib_dir . "/core.php");
require($lib_dir . "/db/database.php");
require($lib_dir . "/util.php");
require($lib_dir . "/common.php");
