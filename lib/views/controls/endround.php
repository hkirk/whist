<form action="endround.php" method="post">
	<h2>End round</h2>
	<input type="hidden" name="game_id" value="<?php echo $game_id ?>" />

	<?php foreach ($bid_winner_positions as $position): ?>
		<?php $bid_winner = $players[$position]; ?>
		<fieldset>
			<legend>Outcome for <?php echo $bid_winner['nickname'] ?></legend>
			<div>
				Tricks:
				<?php
				$name = 'tricks[' . $position . ']';
				for ($tricks = MIN_TRICKS; $tricks <= MAX_TRICKS; $tricks++) {
					radio_button($name, $tricks, $id_qualifier);
					multi_element_label($name, $tricks, $tricks, $id_qualifier);
				}
				?>
			</div>
			<?php if ($bid_type === "normal"): ?>
				<div>
					Mate:
					<?php
					$name = "bid_winner_mate_position";
					foreach ($players as $position => $player) {
						$label = $player['nickname'];
						if ($position === $bid_winner_positions[0]) {
							$label .= ' (Self mate)';
						}
						radio_button($name, $position, $id_qualifier);
						multi_element_label($name, $position, $label, $id_qualifier);
					}
					?>
				</div>
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
