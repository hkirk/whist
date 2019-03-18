<div class="alert alert-error">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<?php if ($invalid_player_count): ?>
		Invalid player count!
	<?php endif; ?>
	<?php if ($missing_location): ?>
		Missing location!
	<?php endif; ?>
	<?php if ($missing_player): ?>
		Missing player!
	<?php endif; ?>
	<?php if ($multi_player): ?>
		Player choosed multiple times!
	<?php endif; ?>
	<?php if ($unknown_player): ?>
		Unknown player!
	<?php endif; ?>
	<?php if ($unknown_location): ?>
		Unknown location!
	<?php endif; ?>
</div>
<p>Try again.</p>
