<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Whist calculator</title>
		<link type="text/css" href="index.css" rel="stylesheet" media="all" />
		<script src="http://code.jquery.com/jquery-latest.js"></script>
		<script type="text/javascript" src="index.js"></script>
	</head>
	<body>
		<h1>The funky Whist Calculator</h1>
		<div>
			<table class="results">
				<thead>
					<tr>
						<th class="round">#</th>
						<th>Melding / Stik</th>
						<?php for ($player = 1; $player <= 4; $player++) { ?>
							<th colspan='2'>
								Pl. <?= $player ?> <br />
								<input type="text" size="3" name="player[<?= $player ?>]" />
							</th>
						<?php } ?>
					</tr>
				</thead>
				<tbody>
				</tbody>
				<tfoot>
					<tr id="bidderteam">
						<th rowspan="2" class="round">#</th>
						<th class="bid-tricks">Melderhold:</th>
						<?php for ($p = 0; $p < 4; $p++) { ?>
							<td colspan="2">
								<input type="checkbox" id="bidderteam-<?= $p ?>" />
							</td>
						<?php } ?>
					</tr>
					<tr id="total">
						<th>Total:</th>
						<?php for ($p = 0; $p < 4; $p++) { ?>
							<th colspan='2' id='total-<?= $p ?>'> </th>
						<?php } ?>
					</tr>				
				</tfoot>
			</table>
		</div>
		<div id="bid-tricks-container">
			<span>
				<label for="bid">Melding:</label>
				<select id="bid">
					<option value="">Melding</option>
					<optgroup label="Normal" id="bid-normal"></optgroup>
					<optgroup label="Solo" id="bid-solo"></optgroup>
				</select>
			</span>
			/
			<span>
				<label for="tricks">Stik:</label>
				<select id="tricks">
					<option value="">Stik</option>
				</select>
			</span>
		</div>
		<div id="buttons-container">
			<button id="addRound">Add round</button>
			<button id="calculatePoints">Calculate latest round</button>
		</div>
	</body>
</html>
