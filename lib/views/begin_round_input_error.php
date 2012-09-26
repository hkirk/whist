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
	error_add($multiple_bid_types, 'Must must chose either a solo game or a normal game, but not both!');
	error_add($missing_bid_type, 'Please choose a bid type');
	error_add($missing_solo_bid_winners, "Please choose one or more solo bid winners");
	error_add($missing_normal_bid_winner, "Please choose a normal bid winner");
	error_add($missing_attachment, 'Please choose an attachment');
	error_add($illegal_attachment, 'The attachment type is illegal in this game!');
	error_add($missing_tips, "Please choose the number of tips for the tips attachment!");
	error_add($tips_chosen, 'You cannot choose the number of tips, when the attachment is not tips!');
	?>
</ul>

