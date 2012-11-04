<?php
global $ATTACHMENTS;
global $SOLO_GAMES, $SOLO_GAME_KEY_ORDER;
global $TIPS_COUNT_MULTIPLIERS;
?>
<form action="beginround.php" method="post">
	<h2>Begin round</h2>
	<input type="hidden" name="game_id" value="<?php echo $game_id ?>" />
	<fieldset>
		<legend>Game bid</legend>
		<div>Please choose a bid, normal (min tricks), or a solo game (max tricks).</div>
		<?php
		$beats = FIRST_SOLO_GAME_BEATS;
		reset($SOLO_GAME_KEY_ORDER);
		$solo_game_key = current($SOLO_GAME_KEY_ORDER);
		for ($tricks = MIN_BID_TRICKS; $tricks <= MAX_BID_TRICKS; $tricks++) {
			$label = sprintf('%s (%s)', $tricks, normal_game_bid_base_points($point_rules, $tricks));
			$value = BID_PREFIX_NORMAL . $tricks;
			radio_button('bid', $value, $id_qualifier);
			multi_element_label('bid', $value, $label, $id_qualifier);
			if ($tricks === $beats && $solo_game_key !== FALSE) {
				$solo_game = $SOLO_GAMES[$solo_game_key];
				$label = sprintf('[%s] %s (%s)', $solo_game['max_tricks'], $solo_game['name'], solo_game_bid_base_points($point_rules, $solo_game));
				$value = BID_PREFIX_SOLO . $solo_game_key;
				?>
				<span>
					<?php
					radio_button('bid', $value, $id_qualifier);
					multi_element_label('bid', $value, $label, $id_qualifier);
					?>
				</span>
				<?php
				$beats++;
				$solo_game_key = next($SOLO_GAME_KEY_ORDER);
			}
		}
		?>
	</fieldset>
	<fieldset>
		<legend>Attachment</legend>
		<div>A normal non-solo game requires an attachment. If the attachment is "Tips", then also choose the number of tips, please.</div>
		<?php
		// As a lambda function, because this file is include twice, and "normal" functions cannot be redeclared
		$beginround_attachment = function ($value, $label, $id_qualifier) {
					?>
					<span>
						<?php
						radio_button('attachment', $value, $id_qualifier);
						multi_element_label('attachment', $value, $label, $id_qualifier);
						?>
					</span>
					<?php
				};
		foreach ($legal_attachment_keys as $attachment_key) {
			$attachment = $ATTACHMENTS[$attachment_key];
			$name = $attachment['name'];
			$multiplier = $attachment['multiplier'];
			if ($attachment_key === TIPS) {
				?>
				<fieldset class="tips">
					<legend><?php echo $name ?></legend>
					<?php
					for ($tips = MIN_TIPS; $tips <= MAX_TIPS; $tips++) {
						if ($tips_count) {
							$multiplier = $TIPS_COUNT_MULTIPLIERS[$tips];
						}
						$value = $attachment_key . '-' . $tips;
						$label = sprintf('%s (x%s)', $tips, $multiplier);
						$beginround_attachment($value, $label, $id_qualifier);
					}
					?>
				</fieldset>
				<?php
			} else {
				$label = sprintf('%s (x%s)', $name, $multiplier);
				$beginround_attachment($attachment_key, $label, $id_qualifier);
			}
		}
		?>
	</fieldset>
	<fieldset>
		<legend>Bid winner(s)</legend>
		<div>One player for normal games. One or more players for solo games</div>
		<?php foreach ($players as $position => $player): ?>
			<span>
				<?php multi_checkbox("bid_winner_positions", $position, $id_qualifier) ?>
				<?php multi_element_label("bid_winner_positions", $position, $player['nickname'], $id_qualifier) ?>
			</span>
		<?php endforeach; ?>
	</fieldset>
	<button type="submit">Begin round</button>
	<div>
		<label>Point rules:</label>
		<?php echo implode(',', $point_rules) ?>
	</div>
</form>
