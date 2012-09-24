<form action="" method="post">
	<fieldset>
		<legend>Informations</legend>
		<label for="location">Location?</label>
		<input type="text" id="location" name="location" />
		<label for="description">Description</label>
		<textarea id="description" name="description" rows="5" cols="50"></textarea>
	</fieldset>
	<fieldset>
		<legend>Point rules</legend>
		<?php multi_checkbox_label("point_rules", "reallybad", "Really bad points?") ?>
		<?php multi_checkbox("point_rules", "reallybad") ?>
		<?php multi_checkbox_label("point_rules", "solotricks", "Solo tricks?") ?>
		<?php multi_checkbox("point_rules", "solotricks") ?>
		<?php multi_checkbox_label("point_rules", "tiptricks", "Tip tricks?") ?>
		<?php multi_checkbox("point_rules", "tiptricks") ?>
	</fieldset>
	<fieldset>
		<legend>Bid attachments</legend>
		<?php multi_checkbox_label("attachments", "sans", "Sans (no trump)?") ?>
		<?php multi_checkbox("attachments", "sans") ?>
		<?php multi_checkbox_label("attachments", "halves", "Halves?") ?>
		<?php multi_checkbox("attachments", "halves") ?>
		<?php multi_checkbox_label("attachments", "strongs", "Strongs (spades)?") ?>
		<?php multi_checkbox("attachments", "strongs") ?>
		<?php multi_checkbox_label("attachments", "tips", "Tips?") ?>
		<?php multi_checkbox("attachments", "tips") ?>
	</fieldset>
	<fieldset>
		<legend>Players</legend>
		<?php for($p=0; $p<4; $p++): ?>
			<?php
			$id = "player_ids$p";
			$name = "player_ids[$p]";
			?>
			<label for="<?php echo $id ?>">Player <?php echo $p+1 ?></label>
			<select id="<?php echo $id ?>" name="<?php echo $name ?>">
				<option value="">Pick player</option>
				<?php foreach($players as $player): ?>
					<?php
					$value = $player['id'];
					$text = "${player['nickname']} (${player['fullname']})";
					?>
					<option value="<?php echo $value; ?>"><?php echo htmlspecialchars($text) ?></option>
				<?php endforeach ?>
			</select>
		<?php endfor; ?>
	</fieldset>
	<div>
		<button type="submit">Create</button>
	</div>
</form>
