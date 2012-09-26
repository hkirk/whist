<?php
global $ATTACHMENTS, $ATTACHMENT_KEY_ORDER;
global $SOLO_GAMES, $SOLO_GAME_KEY_ORDER;
?>
<form action="beginround.php" method="post">
	<input type="hidden" name="game_id" value="<?php echo $game_id ?>" />
	<fieldset>
		<legend>Solo game bid</legend>
		<select name="solo">
			<?php
			option('', 'Solo Type');
			foreach ($SOLO_GAME_KEY_ORDER as $solo_game_key) {
				$solo_game = $SOLO_GAMES[$solo_game_key];
				$content = sprintf('%s (%s)', $solo_game['name'], solo_game_points($solo_game));
				option($solo_game_key, $content);
			}
			?>
		</select>
	</fieldset>
	<div>Or</div>
	<fieldset>
		<legend>Normal game bid</legend>
		<select name="tricks">
			<?php
			option('', 'Tricks');
			for ($tricks = MIN_BID_TRICKS; $tricks <= MAX_BID_TRICKS; $tricks++) {
				$content = sprintf('%s (%s)', $tricks, normal_game_bid_base_points($tricks));
				option($tricks, $content);
			}
			?>
		</select>
		<select name="attachment">
			<?php
			option('', 'Attachment');
			foreach ($legal_attachment_keys as $attachment_key) {
				$attachment = $ATTACHMENTS[$attachment_key];
				$content = sprintf('%s (x%s)', $attachment['name'], $attachment['multiplier']);
				option($attachment_key, $content);
			}
			?>
		</select>
		<?php if ($is_tips_legal): ?>
			<select name="tips">
				<?php
				option('', 'Tips');
				for ($tips = MIN_TIPS; $tips <= MAX_TIPS; $tips++) {
					option($tips, $tips);
				}
				?>
			</select>
		<?php else: ?>
			<input type="hidden" name="tips" value="" />
		<?php endif ?>
	</fieldset>
	<fieldset>
		<legend>Bid winners</legend>
		<div>One player for normal games. One or more players for solo games</div>
		<?php foreach ($players as $position => $player): ?>
			<?php multi_checkbox_label("bid_winner_positions", $position, $player['nickname']) ?>:
			<?php multi_checkbox("bid_winner_positions", $position) ?>
		<?php endforeach; ?>
	</fieldset>
	<button type="submit">Begin round</button>
</form>
