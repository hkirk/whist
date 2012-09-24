<form action="endround.php" action="post">
	<input type="hidden" name="game_id" value="<?php echo $game_id ?>" />

	<?php foreach ($bid_winner_positions as $position): ?>
		<?php $bid_winner = $players[$position]; ?>
		<fieldset>
			<legend><?php echo $bid_winner['nickname'] ?></legend>
			<select name="tricks[<?php echo $position ?>]">
				<?php
				option('', 'Tricks');
				for ($tricks = MIN_TRICKS; $tricks <= MAX_TRICKS; $tricks++) {
					option($tricks, $tricks);
				}
				?>
			</select>
		</fieldset>
	<?php endforeach; ?>
	<?php if ($bid_type === "normal"): ?>
		<select name="bid_winner_mate_position">
			<?php
			option('', 'Mate');
			foreach ($players as $position => $player) {
				$content = $player['nickname'];
				if ($position === $bid_winner_positions[0]) {
					$content .= ' (Self mate)';
				}
				option($position, $content);
			}
			?>
		</select>
	<?php endif; ?>
	<div>
		<button type="submit">End round</button>
	</div>
</form>
