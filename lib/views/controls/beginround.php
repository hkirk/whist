<?php
/* * *
 * Input:
 * 
 * $id_qualifier (string) (from parent view)
 * $game_id
 * $players (array)
 * $point_rules (array)
 * $is_tips_legal (bool)
 * $tips_count (bool)
 * $legal_attachment_keys (array)
 * 
 */
global $ATTACHMENTS;
global $SOLO_GAMES, $SOLO_GAME_KEY_ORDER;
global $TIPS_COUNT_MULTIPLIERS;
?>
<form action="beginround.php" method="post" class="beginround">
	<h2>Begin round</h2>
	<input type="hidden" name="game_id" value="<?php echo $game_id ?>" />
	<fieldset class="bid">
		<legend>Game bid</legend>
		<?php label('bid', 'Base bid:', $id_qualifier); ?>
		<select name="bid" id="<?php echo name_id('bid', $id_qualifier) ?>">
			<?php
			option('', "Choose a base bid");
			$beats = FIRST_SOLO_GAME_BEATS;
			reset($SOLO_GAME_KEY_ORDER);
			$solo_game_key = current($SOLO_GAME_KEY_ORDER);
			for ($tricks = MIN_BID_TRICKS; $tricks <= MAX_BID_TRICKS; $tricks++) {
				$text = sprintf('%s (%s pts)', $tricks, normal_game_bid_base_points($point_rules, $tricks));
				$value = BID_PREFIX_NORMAL . $tricks;
				option($value, $text);
				if ($tricks === $beats && $solo_game_key !== FALSE) {
					$solo_game = $SOLO_GAMES[$solo_game_key];
					$text = sprintf('[%s] %s (%s pts)', $solo_game['max_tricks'], $solo_game['name'], solo_game_bid_base_points($point_rules, $solo_game));
					$value = BID_PREFIX_SOLO . $solo_game_key;
					option($value, $text);
					$beats++;
					$solo_game_key = next($SOLO_GAME_KEY_ORDER);
				}
			}
			?>
		</select>
		<div class="description">Please choose a bid. Either a normal game with minimum tricks, or a solo game [with maximum tricks in square brackets]. The base points are shown in soft brackets.</div>
	</fieldset>
	<fieldset class="attachment">
		<legend>Attachment</legend>
		<?php label('attachment', 'Attachment:', $id_qualifier); ?>
		<select name="attachment" id="<?php echo name_id('attachment', $id_qualifier) ?>">
			<?php
			// The "null" attachment for solo games:
			$attachment_key = '';
			$text = '[Solo game]';
			option($attachment_key, $text);
			// The actual attachments:
			foreach ($legal_attachment_keys as $attachment_key):
				$attachment = $ATTACHMENTS[$attachment_key];
				$name = $attachment['name'];
				$multiplier = $attachment['multiplier'];
				if ($attachment_key === TIPS) {
					?>
					<optgroup label="<?php echo $name ?>" class="tips">
						<?php
						for ($tips = MIN_TIPS; $tips <= MAX_TIPS; $tips++) {
							if ($tips_count) {
								$multiplier = $TIPS_COUNT_MULTIPLIERS[$tips];
							}
							$value = $attachment_key . '-' . $tips;
							$noun = $tips === 1 ? "Tip" : "Tips";
							$text = sprintf('%s %s (x%s)', $tips, $noun, $multiplier);
							option($value, $text);
						}
						?>
					</optgroup>
					<?php
				} else {
					$text = sprintf('%s (x%s)', $name, $multiplier);
					option($attachment_key, $text);
				}
			endforeach;
			?>
		</select>
		<div class="description">A normal non-solo game requires an attachment. If the attachment is "Tips", then also choose the number of tips, please.</div>
	</fieldset>
	<fieldset class="bid_winners">
		<legend>Bid winner(s)</legend>
		<?php $name = "bid_winner_positions"; ?>
		<select multiple name="<?php echo $name ?>[]" id="<?php echo name_id($name, $id_qualifier)?>">
			<?php 
			foreach ($players as $position => $player): 
				option($position, $player['nickname']);
			endforeach; 
			?>
		</select>
		<div class="description">One player for normal games. One or more players for solo games</div>
	</fieldset>
	<button type="submit">Begin round</button>
	<div>
		<label>Point rules:</label>
		<?php echo implode(',', $point_rules) ?>
	</div>
</form>
