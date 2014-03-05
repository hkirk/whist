<?php
global $POINT_RULES;
global $OPTIONAL_ATTACHMENT_KEYS_ORDER;
global $ATTACHMENTS;

$player_numbers = DEFAULT_PLAYERS;
$description = "If more then 4 players";
if (isset($_GET['players']) && $_GET['players'] > DEFAULT_PLAYERS && $_GET['players'] <= MAX_PLAYERS) {
	$player_numbers = $_GET['players'];
	$description = "If different than 4 and different from $player_numbers";
}
?>

<form action="newgame.php" method="get" class="form-inline" role="form">
	<div class="form-group">
		<label class="sr-only" for="players">Players (<? echo $description; ?>)</label>
		<input type="number" min="<?php echo DEFAULT_PLAYERS ?>" max="<?php echo MAX_PLAYERS ?>" class="form-control" id="players" name="players" placeholder="#players" />
	</div>
	<button type="submit" class="btn btn-info">Change number of players</button>
</form>

<h3>Create game</h3>

<form action="newgame.php" class="form-horizontal" method="post" role="form">
	<fieldset>
		<legend>Informations</legend>
		<div class="form-group">
			<label for="location_id">Location?</label>
			<select id="location_id" name="location_id">
				<option value="">Pick location</option>
				<?php foreach ($locations as $id => $name): ?>
					<option value="<?php echo $id ?>"><?php echo htmlspecialchars($name) ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="form-group col-lg-4">
			<label for="description">Description</label>
			<textarea id="description" name="description" rows="3" class="form-control"></textarea>
		</div>
	</fieldset>
	<fieldset>
		<legend>Point rules</legend>
		<?php
		$name = "point_rules";
		?>
		<?php foreach ($POINT_RULES as $point_rule_key => $point_rule): ?>
			<div class="form-group">
				<?php
				multi_checkbox($name, $point_rule_key);
				multi_element_label($name, $point_rule_key, $point_rule['name'] . '?');
				?>
				<?php if (isset($point_rule['description'])) : ?>
					<span class="help-block"><?php echo $point_rule['description'] ?></span>
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
			<div class="form-group">
				<?php
				$attachment = $ATTACHMENTS[$attachment_key];
				multi_checkbox($name, $attachment_key);
				multi_element_label($name, $attachment_key, $attachment['name'] . '?');
				?>
				<?php if (isset($attachment['description'])) : ?>
					<span class="help-block"><?php echo $attachment['description'] ?></span>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</fieldset>
	<fieldset>
		<legend>Players</legend>
		<?php
		for ($p = 0; $p < $player_numbers; $p++):
			?>
			<div class="form-group">
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
				</select>
			</div>
		<?php endfor; ?>
	</fieldset>
	<div class="form-group">
		<div class="col-lg-offset-1 col-lg-10">
			<button type="submit" class="btn btn-success">Create</button>
		</div>
	</div>
</form>
