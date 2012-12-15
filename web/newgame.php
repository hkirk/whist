<?php

require("lib.php");

$player_id_validator = function($value) {
			return $value === "" || ctype_digit($value);
		};


switch (request_method()) {

	case "POST" :
		$location_id = check_get_uint($_POST, 'location_id', true);
		$description = check_get_string($_POST, 'description');
		$player_ids = check_get_indexed_array($_POST, 'player_ids', 4, $player_id_validator);
		$attachments = check_get_multi_checkbox_array($_POST, 'attachments', $OPTIONAL_ATTACHMENTS);
		$point_rules = check_get_multi_checkbox_array($_POST, 'point_rules', $POINT_RULES);
		check_input($location_id, $description, $player_ids, $attachments, $point_rules);
		$used_player_ids = array();
		$data = array(
			'missing_location' => FALSE,
			'missing_player' => FALSE,
			'multi_player' => FALSE,
			'unknown_player' => FALSE,
			'unknown_location' => FALSE
		);
		$input_error = FALSE;
		if ($location_id === "") {
			$input_error = $data['missing_location'] = TRUE;
		} else if(!db_check_location_id($location_id)) {
			$input_error = $data['unknown_location'] = TRUE;
		}
		foreach ($player_ids as $player_id) {
			if ($player_id === "") {
				$input_error = $data['missing_player'] = TRUE;
			} else if (isset($used_player_ids[$player_id])) {
				$input_error = $data['multi_player'] = TRUE;
			}
			$used_player_ids[$player_id] = TRUE;
		}
		if (!$input_error && !db_check_player_ids($player_ids)) {
			$input_error = $data['unknown_player'] = TRUE;
		}
		if ($input_error) {
			render_page("Input error", "Input error", "newgame_input_error", $data);
			exit;
		}
		$game_id = db_create_game($location_id, $description, $player_ids, $attachments, $point_rules);
		redirect_to_game($game_id);
		return;

	case "GET" :
		$locations = db_load_locations();
		$players = db_load_players();
		$data = array(
			'locations' => $locations,
			'players' => $players
		);

		render_page("New game", "New game", "newgame", $data);

		return;

	default:
		printf("Unknown: %s", request_method());
}





