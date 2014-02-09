<?php

$db_dir = dirname(__FILE__);
require($db_dir . "/games.php");
require($db_dir . "/players.php");
require($db_dir . "/round_mutation.php");
require($db_dir . "/locations.php");


$_db = NULL;


function _db_connect() {
	global $_db;
	if (!$_db) {
		_db_force_connect();
	}
}


function _db_force_connect() {
	global $_db;
	global $SETTINGS;
	$s = $SETTINGS['database'];
	$host = $s['host'];
	$name = $s['name'];
	$dsn = "mysql:host=$host;dbname=$name";
	$attributes = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
	];
	$_db = new PDO($dsn, $s['username'], $s['password'], $attributes);
	return $_db;
}


function _db_set_string_from_array($array) {
	return implode(",", $array);
}


function _db_set_array_from_string($string) {
	if ($string === '') {
		return [];
	}
	return explode(",", $string);
}


function _db_placeholders_list($array) {
	$length = count($array);
	if ($length === 0) {
		return "";
	} else {
		return "?" . str_repeat(", ?", $length - 1);
	}
}


function _db_quoted_values_list($array) {
	global $_db;
	$quoted_array = [];
	foreach ($array as $value) {
		$quoted_array[] = $_db->quote($value);
	}
	return implode(", ", $quoted_array);
}


function _db_assert_player_position($position, $n_players) {
	assert(is_int($position) && $position >= 0 && $position < $n_players); // TODO fix constant
}


function _db_assert_player_positions($positions, $n_players) {
	assert(is_array($positions));
	foreach ($positions as $position) {
		_db_assert_player_position($position, $n_players);
	}
}


function _db_beginTransaction() {
	global $_db;
	_db_connect();
	$_db->beginTransaction();
}


function _db_commit() {
	global $_db;
	_db_connect();
	$_db->commit();
}


function _db_prepare_execute($sql, $params = []) {
	global $_db;
	_db_connect();
	$stm = $_db->prepare($sql);
	$result = $stm->execute($params);
	return [$stm, $result];
}


function _db_prepare_execute_rowCount($sql, $params = []) {
	list($stm, $result) = _db_prepare_execute($sql, $params);
	if ($result) {
		$rows_affected = $stm->rowCount();
	} else {
		$rows_affected = NULL;
	}
	return [$stm, $result, $rows_affected];
}


function _db_prepare_execute_fetch($sql, $params = []) {
	list($stm, $result) = _db_prepare_execute($sql, $params);
	if ($result) {
		$row = $stm->fetch();
	} else {
		$row = NULL;
	}
	return [$stm, $result, $row];
}


function _db_prepare_execute_fetchAll($sql, $params = []) {
	list($stm, $result) = _db_prepare_execute($sql, $params);
	if ($result) {
		$rows = $stm->fetchAll();
	} else {
		$rows = NULL;
	}
	return [$stm, $result, $rows];
}

