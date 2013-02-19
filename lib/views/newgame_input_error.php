<ul>
	<?php if ($missing_location): ?>
		<li>Missing location!</li>
	<? endif; ?>
	<?php if ($missing_player): ?>
		<li>Missing player!</li>
	<?php endif; ?>
	<?php if ($multi_player): ?>
		<li>Player choosed multiple times!</li>
	<?php endif; ?>
	<?php if ($unknown_player): ?>
		<li>Unknown player!</li>
	<?php endif; ?>
	<?php if ($unknown_location): ?>
		<li>Unknown location!</li>
	<? endif; ?>
</ul>
<p>Try again.</p>
