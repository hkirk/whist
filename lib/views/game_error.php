<ul>
	<?php if ($unknown_game): ?>
		<li>Unknown game!</li>
	<?php endif; ?>
	<?php if ($bad_rounds): ?>
		<li>Error fetching game rounds!</li>
	<?php endif; ?>
	<?php if ($inconsistent_points): ?>
		<li>Game accumulated rounds points mismatch game total points!</li>
	<?php endif; ?>
</ul>

