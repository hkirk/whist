<?php
global $POINT_RULES;
global $OPTIONAL_ATTACHMENT_KEYS_ORDER;
global $ATTACHMENTS;
?>
<form action="newgame.php" method="post">
	<fieldset>
		<legend>Informations</legend>
		<label for="location_id">Location?</label>
		<select id="location_id" name="location_id">
			<option value="">Pick location</option>
			<?php foreach ($locations as $id => $name): ?>
				<option value="<?php echo $id ?>"><?php echo htmlspecialchars($name) ?></option>
			<?php endforeach; ?>
		</select>
        <br/>

		<label for="description">Description</label>
		<textarea id="description" name="description" rows="5" cols="50"></textarea>
	</fieldset>
	<fieldset>
		<legend>Point rules</legend>
		<?php
		$name = "point_rules";
		?>
		<?php foreach ($POINT_RULES as $point_rule_key => $point_rule): ?>
			<div>
				<?php
				multi_checkbox($name, $point_rule_key);
				multi_element_label($name, $point_rule_key, $point_rule['name'] . '?');
				?>
				<?php if (isset($point_rule['description'])) : ?>
					<p><?php echo $point_rule['description'] ?></p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</fieldset>
	<fieldset>
		<legend>Bid attachments</legend>
		<?php
		$name = 'attachments';
		?>
		<?php foreach ($OPTIONAL_ATTACHMENT_KEYS_ORDER as $attachment_key) : ?>
			<div>
				<?php
				$attachment = $ATTACHMENTS[$attachment_key];
				multi_checkbox($name, $attachment_key);
				multi_element_label($name, $attachment_key, $attachment['name'] . '?');
				?>
				<?php if (isset($attachment['description'])) : ?>
					<p><?php echo $attachment['description'] ?></p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</fieldset>
	<fieldset>
		<legend>Players</legend>
		<?php
        $player_numbers = 4;

        if (isset($_GET['players'])) {
            $player_numbers = $_GET['players'];
        }
		for ($p = 0; $p < $player_numbers; $p++):
			?>
			<?php
			$id = "player_ids$p";
			$name = "player_ids[$p]";
			?>
			<label for="<?php echo $id ?>">Player <?php echo $p + 1 ?></label>
			<select id="<?php echo $id ?>" name="<?php echo $name ?>">
				<option value="">Pick player</option>
				<?php foreach ($players as $player): ?>
					<?php
					$value = $player['id'];
					$text = "${player['nickname']} (${player['fullname']})";
					?>
					<option value="<?php echo $value; ?>"><?php echo htmlspecialchars($text) ?></option>
				<?php endforeach ?>
			</select><br/>
		<?php endfor; ?>
	</fieldset>
	<div>
		<button type="submit" class="btn btn-success">Create</button>
	</div>
</form>
