<?php
/* * *
 * Input:
 * 
 * $id_qualifier (string) (from parent view)
 * $game_id
 * $players (array<array>)
 * $point_rules (array)
 * $bid_type (string)
 * $bid_winner_positions (array<int>)
 * $bye_player_positions (array<int>)
 * $participating_player_positions (array<int>)
 * 
 */
?>
<form action="endround.php" method="post" class="endround">
	<h2>End round</h2>
	<input type="hidden" name="game_id" value="<?php echo $game_id ?>" />

	<?php foreach ($bid_winner_positions as $position): ?>
		<?php
		$bid_winner = $players[$position];
		$name = 'tricks[' . $position . ']';
		?>
		<fieldset>
			<legend>Outcome for <?php echo $bid_winner['nickname'] ?></legend>
			<div>
				<?php label($name, 'Tricks:', $id_qualifier) ?>
				<select name="<?php echo $name ?>" id="<?php echo name_id($name, $id_qualifier) ?>">			
					<?php
					option('', 'Choose tricks');
					for ($tricks = MIN_TRICKS; $tricks <= MAX_TRICKS; $tricks++) {
						option($tricks, $tricks);
					}
					?>
				</select>
			</div>
			<?php if ($bid_type === "normal"): ?>
				<?php
				$name = "bid_winner_mate_position";
				?>
				<div>
					<?php label($name, 'Mate:', $id_qualifier) ?>
					<select name="<?php echo $name ?>" id="<?php echo name_id($name, $id_qualifier) ?>">
						<?php
						option('', 'Choose a mate');
						foreach ($participating_player_positions as $position) {
							$player = $players[$position];
							$label = $player['nickname'];
							if ($position === $bid_winner_positions[0]) {
								$label .= ' (Self mate)';
							}
							option($position, $label);
						}
						?>
					</select>
				</div>
			<?php else: ?>
				<input type="hidden" name="bid_winner_mate_position" value="" /> <!-- FIXME claus why -->
			<?php endif; ?>
		</fieldset>
	<?php endforeach; ?>
	<div>Bye players:
		<?php
		$bye_nicknames = [];
		foreach ($bye_player_positions as $position) {
			$bye_nicknames[] = $players[$position]['nickname'];
		}
		echo join(", ", $bye_nicknames);
		?>
	</div>
	<div>
		<button type="submit">End round</button>
	</div>
</form>
