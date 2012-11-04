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
	error_add($has_active_round, 'The game already has an active round!');
	error_add($solo_and_attachment, 'You cannot choose an attachment for a solo game!');
	error_add($missing_bid, 'Please choose a bid');
	error_add($missing_solo_bid_winners, "Please choose one or more solo bid winners");
	error_add($missing_normal_bid_winner, "Please choose a normal bid winner");
	error_add($missing_attachment, 'Please choose an attachment');
	error_add($illegal_attachment, 'The attachment type is illegal in this game!');
	?>
</ul>

