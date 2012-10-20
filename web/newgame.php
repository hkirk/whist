<?php

require("lib.php");

$player_id_validator = function($value) {
			return $value === "" || ctype_digit($value);
		};


switch (request_method()) {

	case "POST" :
		$location = check_get_string($_POST, 'location');
		$description = check_get_string($_POST, 'description');
		$player_ids = check_get_indexed_array($_POST, 'player_ids', 4, $player_id_validator);
		$attachments = check_get_multi_checkbox_array($_POST, 'attachments', $OPTIONAL_ATTACHMENTS);
		$point_rules = check_get_multi_checkbox_array($_POST, 'point_rules', $POINT_RULES);
		check_input($location, $description, $player_ids, $attachments, $point_rules);
		$used_player_ids = array();
		$data = array(
			'missing_location' => FALSE,
			'missing_player' => FALSE,
			'multi_player' => FALSE,
			'unknown_player' => FALSE
		);
		$input_error = FALSE;
		if (trim($location) === "") {
			$input_error = $data['missing_location'] = TRUE;
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
		$game_id = db_create_game($location, $description, $player_ids, $attachments, $point_rules);
		redirect_path("/game.php?id=" . $game_id);
		return;

	case "GET" :
		$players = db_load_players();
		$data = array(
			'players' => $players
		);

		render_page("New game", "New game", "newgame", $data);

		return;

	default:
		printf("Unknown: %s", request_method());
}





