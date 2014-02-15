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
	error_add($missing_dealer, 'Please choose a dealer.');
	error_add($missing_bid, 'Please choose a bid.');
	error_add($missing_attachment, 'Please choose an attachment.');
	error_add($illegal_attachment, 'The attachment type is illegal in this game!');
	error_add($illegal_solo_bid_winner_count, "Please choose 1-4 solo bid winners.");
	error_add($illegal_normal_bid_winner_count, "Please choose a normal bid winner.");
	error_add($illegal_bye_count, 'Invalid number of bye players!');
	error_add($multi_bye_position, 'Bye player selected multiple times!');
	error_add($joint_bid_winner_bye, 'Cannot have overlapping bid winner and bye player(s)!');
	error_add($joint_bye_dealer, 'Cannot have overlapping bye and dealer player!');
	?>
</ul>

