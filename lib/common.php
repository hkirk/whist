<?php


function redirect_to_game($game_id) {
	redirect_path("/game.php?id=" . $game_id);
}
