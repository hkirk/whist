<?php


function error_add($condition, $message) {
	if ($condition) {
		?><li><?php echo $message ?></li><?php
	}
}
?>
<ul>
	<?php
	error_add($unknown_game, 'Unknown game!');
	error_add($no_active_round, 'The game already has no active round!');
	error_add($missing_tricks, 'Missing bid winner tricks!');
	error_add($bad_tricks_sum, 'The sum of tricks is invalid!');
	error_add($missing_bid_winner_mate_position, 'Missing the bid winner mate!');
	?>
</ul>

