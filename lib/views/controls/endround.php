<form action="endround.php" method="post">
	<h2>End round</h2>
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
			<?php else: ?>
				<input type="hidden" name="bid_winner_mate_position" value="" />
			<?php endif; ?>
		</fieldset>
	<?php endforeach; ?>
	<div>
		<button type="submit">End round</button>
	</div>
	<div>
		<label>Point rules:</label>
		<?php echo implode(',', $point_rules) ?>
	</div>
</form>
