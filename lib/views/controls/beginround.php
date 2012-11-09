<?php
global $ATTACHMENTS;
global $SOLO_GAMES, $SOLO_GAME_KEY_ORDER;
global $TIPS_COUNT_MULTIPLIERS;
?>
<form action="beginround.php" method="post" class="beginround">
	<h2>Begin round</h2>
	<input type="hidden" name="game_id" value="<?php echo $game_id ?>" />
	<fieldset class="bid">
		<legend>Game bid</legend>
		<div>Please choose a bid, normal (min tricks), or a solo game (max tricks).</div>
		<ol>
			<?php
			$beats = FIRST_SOLO_GAME_BEATS;
			reset($SOLO_GAME_KEY_ORDER);
			$solo_game_key = current($SOLO_GAME_KEY_ORDER);
			for ($tricks = MIN_BID_TRICKS; $tricks <= MAX_BID_TRICKS; $tricks++) {
				$label = sprintf('%s (%s)', $tricks, normal_game_bid_base_points($point_rules, $tricks));
				$value = BID_PREFIX_NORMAL . $tricks;
				?>
				<li>
					<?php
					radio_button('bid', $value, $id_qualifier);
					multi_element_label('bid', $value, $label, $id_qualifier);
					?>
				</li>
				<?php
				if ($tricks === $beats && $solo_game_key !== FALSE) {
					$solo_game = $SOLO_GAMES[$solo_game_key];
					$label = sprintf('%s[%s] (%s)', $solo_game['name'], $solo_game['max_tricks'], solo_game_bid_base_points($point_rules, $solo_game));
					$value = BID_PREFIX_SOLO . $solo_game_key;
					?>
					<li>
						<?php
						radio_button('bid', $value, $id_qualifier);
						multi_element_label('bid', $value, $label, $id_qualifier);
						?>
					</li>
					<?php
					$beats++;
					$solo_game_key = next($SOLO_GAME_KEY_ORDER);
				}
			}
			?>
		</ol>
	</fieldset>
	<fieldset class="attachment">
		<legend>Attachment</legend>
		<div>A normal non-solo game requires an attachment. If the attachment is "Tips", then also choose the number of tips, please.</div>
		<ol>
			<?php
			// As a lambda function, because this file is included twice, and "normal" functions cannot be redeclared
			$beginround_attachment = function ($value, $label, $id_qualifier) {
						radio_button('attachment', $value, $id_qualifier);
						multi_element_label('attachment', $value, $label, $id_qualifier);
					};
			foreach ($legal_attachment_keys as $attachment_key) {
				?>
				<li>
					<?php
					$attachment = $ATTACHMENTS[$attachment_key];
					$name = $attachment['name'];
					$multiplier = $attachment['multiplier'];
					if ($attachment_key === TIPS) {
						?>
						<div class="tips"><?php echo $name ?>:
							<ol>
								<?php
								for ($tips = MIN_TIPS; $tips <= MAX_TIPS; $tips++) {
									if ($tips_count) {
										$multiplier = $TIPS_COUNT_MULTIPLIERS[$tips];
									}
									$value = $attachment_key . '-' . $tips;
									$label = sprintf('%s (x%s)', $tips, $multiplier);
									?>
									<li>
										<?php
										$beginround_attachment($value, $label, $id_qualifier);
										?>
									</li>
									<?php
								}
								?>
							</ol>
						</div>
						<?php
					} else {
						$label = sprintf('%s (x%s)', $name, $multiplier);
						$beginround_attachment($attachment_key, $label, $id_qualifier);
					}
					?>
				</li>
				<?php
			}
			?>
		</ol>
	</fieldset>
	<fieldset class="bid_winners">
		<legend>Bid winner(s)</legend>
		<div>One player for normal games. One or more players for solo games</div>
		<ol>
		<?php foreach ($players as $position => $player): ?>
			<li>
				<?php multi_checkbox("bid_winner_positions", $position, $id_qualifier) ?>
				<?php multi_element_label("bid_winner_positions", $position, $player['nickname'], $id_qualifier) ?>
			</li>
		<?php endforeach; ?>
		</ol>
	</fieldset>
	<button type="submit">Begin round</button>
	<div>
		<label>Point rules:</label>
		<?php echo implode(',', $point_rules) ?>
	</div>
</form>
