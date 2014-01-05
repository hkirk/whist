<?php


function db_load_locations() {
	$sql = <<<EOS
SELECT 
id,
name
FROM locations
ORDER BY name
EOS;
	$params = [];
	list($stm,) = _db_prepare_execute($sql, $params);
	$locations = [];
	foreach ($stm as $row) {
		$locations[$row['id']] = $row['name'];
	}
	return $locations;
}


function db_check_location_id($id) {
	$sql = <<<EOS
SELECT COUNT(*) AS count
FROM locations
WHERE id = ?
EOS;
	$params = [$id];
	list(,, $row) = _db_prepare_execute_fetch($sql, $params);
	return (int) $row['count'] === 1;
}