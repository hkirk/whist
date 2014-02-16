--
-- To be used after the database upgrade 2014-02-15, where the dealer position became a part of the round data.
-- Previously, the position was hardcoded to (round-1) % #players in game.php.
-- After the database upgrade all dealer positions was set to the default value 0
-- Therefore, we must restore the old dealer positions.
-- WARNING Do not run this after playing a game with manual bye or dealer position!
--

UPDATE game_rounds AS gr
SET dealer_position = (round - 1) % (
	SELECT COUNT(*)
	FROM game_players AS gp
	WHERE gp.game_id = gr.game_id
)
-- WHERE gr.game_id <> 27

