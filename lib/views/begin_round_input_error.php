<ul>
	<?php if ($unknown_game): ?>
		<li>Unknown game!</li>
	<?php endif; ?>
	<?php if ($has_active_round): ?>
		<li>The game already has an active round!</li>
	<?php endif; ?>
	<?php if ($multiple_bid_types): ?>
		<li>Must must chose either a solo game or a normal game, but not both!</li>
	<?php endif; ?>
</ul>

