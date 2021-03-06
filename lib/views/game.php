<?php
global $SOLO_GAMES;
global $ATTACHMENTS;


function rewrite_null($e) {
	return $e === NULL ? "?" : $e;
}


function avg_string($sum, $n) {
	if ($n === 0) {
		return "?";
	} else {
		return sprintf("%.2f", $sum / $n);
	}
}


function rms_string($square_sum, $n) {
	if ($n === 0) {
		return "?";
	} else {
		return sprintf("%.2f", sqrt($square_sum / $n));
	}
}


function number_class($number) {
	if ($number < 0) {
		return "negative";
	} else if ($number > 0) {
		return "positive";
	} else {
		return "zero";
	}
}


$number_of_players = count($players);
$controls_view_data['number_of_players'] = $number_of_players;

$render_controls = function($position) use($controls_positions, $controls_view, $controls_view_data) {
	if (in_array($position, $controls_positions)) {
		$controls_view_data['id_qualifier'] = $position;
		render_view('controls/' . $controls_view, $controls_view_data);
	}
};
if ($cancel_view !== NULL) {
	render_view('controls/' . $cancel_view, $cancel_view_data);
}
$render_controls('top');

$player_round_acc_points = array_fill(0, $number_of_players, []);
$n_rounds = count($rounds);
$rounds_percent = function ($number, $n_rounds) {
	if ($n_rounds === 0) {
		return 0.0;
	}
	return round(($number / $n_rounds) * 100.0);
};

if ($number_of_players > DEFAULT_PLAYERS) {
	$show_bye = true;
	$extra_class = 'player-stats-bye';
} else {
	$show_bye = false;
	$extra_class = 'player-stats-normal';
}

$print_player_stat_cells = function($player_stat, $key) use ($rounds_percent, $n_finished_rounds, $show_bye) {
	?>
	<td><?php echo $player_stat[$key] ?></td>
	<td><?php echo $rounds_percent($player_stat[$key], $player_stat['participating_rounds']) ?>%
		<?php if ($show_bye): ?>
			(<?php echo $rounds_percent($player_stat[$key], $n_finished_rounds) ?>%)
		<?php endif; ?>
	</td>
	<?php
};
?>
<h2>Player Stats</h2>
<table class="table player-stats <?php echo $extra_class ?>">
	<thead>
		<tr>
			<th>#</th>
			<th>Player</th>
			<th>Points</th>
			<th colspan="2">Bid winner</th>
			<th colspan="2">B.w. mate</th>
			<th colspan="2">Opponent</th>
			<th colspan="2">Won</th>
			<th colspan="2">Lost</th>
			<?php if ($show_bye): ?><th colspan="2">Bye</th><?php endif ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($player_stats as $rank => $player_stat): ?>
			<?php $points_class = number_class($player_stat['total_points']) ?>
			<tr>
				<td><?php echo $rank + 1 ?></td>
				<td><?php echo $players[$player_stat['position']]['nickname'] ?></td>
				<td class="<?php echo $points_class ?>"><?php echo $player_stat['total_points'] ?></td>
				<?php
				$print_player_stat_cells($player_stat, 'bid_winner_rounds');
				$print_player_stat_cells($player_stat, 'bid_winner_mate_rounds');
				$print_player_stat_cells($player_stat, 'opponent_rounds');
				$print_player_stat_cells($player_stat, 'won_rounds');
				$print_player_stat_cells($player_stat, 'lost_rounds');
				if ($show_bye):
					?>
					<td><?php echo $player_stat['bye_rounds'] ?></td>
					<td><?php echo $rounds_percent($player_stat['bye_rounds'], $n_finished_rounds) ?>%</td>
				<?php endif ?>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>


<h2>Game stats</h2>
<table class="table game-stats">
	<thead>
		<tr>
			<th rowspan="2">Aggregate</th>
			<th colspan="4">Total tricks (<?php echo $game_stats['total']['n_bid_winners'] ?> b.w.)</th>
			<th colspan="4">Normal tricks  (<?php echo $game_stats['normal']['n_bid_winners'] ?> b.w.)</th>
			<th colspan="4">Solo tricks  (<?php echo $game_stats['solo']['n_bid_winners'] ?> b.w.)</th>
		</tr>
		<tr>
			<?php for ($i = 0; $i < 3; $i++): ?>
				<th>Bid</th>
				<th>Realized</th>
				<th>&Delta;</th>
				<th>&#x01c0; &Delta; &#x01c0;</th>
			<?php endfor ?>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th>Sum:</th>
			<?php foreach ($game_stats as $gs): ?>
				<td><?php echo $gs['bid_tricks_sum'] ?></td>
				<td><?php echo $gs['realized_tricks_sum'] ?></td>
				<td><?php echo $gs['tricks_diff_sum'] ?></td>
				<td><?php echo $gs['abs_tricks_diff_sum'] ?></td>
			<?php endforeach; ?>
		</tr>
		<tr>
			<th>Avg.:</th>
			<?php foreach ($game_stats as $gs): ?>
				<td><?php echo avg_string($gs['bid_tricks_sum'], $gs['n_bid_winners']) ?></td>
				<td><?php echo avg_string($gs['realized_tricks_sum'], $gs['n_bid_winners']) ?></td>
				<td><?php echo avg_string($gs['tricks_diff_sum'], $gs['n_bid_winners']) ?></td>
				<td><?php echo avg_string($gs['abs_tricks_diff_sum'], $gs['n_bid_winners']) ?></td>
			<?php endforeach; ?>
		</tr>
		<tr>
			<th>RMS:</th>
			<?php foreach ($game_stats as $gs): ?>
				<td><?php echo rms_string($gs['bid_tricks_square_sum'], $gs['n_bid_winners']) ?></td>
				<td><?php echo rms_string($gs['realized_tricks_square_sum'], $gs['n_bid_winners']) ?></td>
				<td><?php echo rms_string($gs['tricks_diff_square_sum'], $gs['n_bid_winners']) ?></td>
				<td><?php echo rms_string($gs['abs_tricks_diff_square_sum'], $gs['n_bid_winners']) ?></td>
			<?php endforeach; ?>
		</tr>
	</tbody>
</table>


<?php


function print_full_header_row($players) {
	?>
	<tr class="full-row">
		<th>#</th>
		<th>Bid winner(s)</th>
		<th>Bid</th>
		<th>Tricks</th>
		<th>&Delta;</th>
		<?php foreach ($players as $player): ?>
			<th colspan="2"><?php echo htmlspecialchars($player['nickname']) ?></th>
		<?php endforeach ?>
	</tr>
	<?php
}
?>


<h2>Score board</h2>
<table class="table table-striped .table-responsive scoreboard">
	<?php if ($n_rounds > 0): ?>
		<thead>
			<?php print_full_header_row($players) ?>
		</thead>
	<?php endif ?>
	<tbody>
		<?php foreach ($rounds as $round): ?>
			<tr>
				<?php
				$dealer_position = $round['dealer_position'];
				$bid = $round['bid'];
				if ($bid['type'] === "solo") {
					$solo_game = $SOLO_GAMES[$bid['solo_type']];
					$bid_text = sprintf('[%d] %s', $solo_game['max_tricks'], $solo_game['name']);
					$target_tricks = $solo_game['max_tricks'];
					$tricks_diff_sign = -1;
				} else {
					$bid_text = sprintf('%d', $bid['tricks']);
					if ($bid['attachment'] !== NONE) {
						$bid_text .= ' ' . $ATTACHMENTS[$bid['attachment']]['name'];
					}
					$target_tricks = $bid['tricks'];
					$tricks_diff_sign = 1;
				}
				$bid_winner_tricks_or_unknown_by_position = array_map_nulls($round['bid_winner_tricks_by_position'], "?");
				$bid_winner_positions = array_keys($bid_winner_tricks_or_unknown_by_position);
				$bid_winner_mate_position = $round['bid_winner_mate_position'];
				$bid_winner_names = [];
				$bid_winner_tricks_diff = [];
				foreach ($round['bid_winner_tricks_by_position'] as $position => $tricks) {
					$bid_winner_names[] = $players[$position]['nickname'];
					if ($tricks === NULL) {
						$bid_winner_tricks_diff[] = "?";
					} else {
						$diff = $tricks_diff_sign * ($tricks - $target_tricks);
						$bid_winner_tricks_diff[] = $diff;
					}
				}
				if ($bid_winner_mate_position !== NULL) {
					$bid_winner_names[0] .= " (" . $players[$bid_winner_mate_position]['nickname'] . ")";
				}
				?>
				<td><?php echo $round['index'] ?></td>
				<td><?php echo implode(", ", $bid_winner_names) ?></td>
				<td><?php echo $bid_text ?> </td>
				<td><?php echo implode(", ", $bid_winner_tricks_or_unknown_by_position) ?></td>
				<td><?php echo implode(", ", $bid_winner_tricks_diff) ?></td>
				<?php foreach ($round['player_data'] as $position => $player_data): ?>
					<?php
					$player_round_points = $player_data['points'];
					$player_acc_points = $player_data['acc_points'];
					$is_bye = $player_data['is_bye'];
					$is_dealer = $position === $dealer_position;
					$is_bid_winner = in_array($position, $bid_winner_positions);
					$is_bid_winner_mate = $position === $bid_winner_mate_position;
					$round_points_class = [];
					$total_points_class = [];
					if ($player_round_points === NULL) {
						$round_points_class[] = "nan"; // Not a number
					} else {
						assert(!$is_bye);
						if ($player_round_points < 0) {
							$player_round_points = "" . $player_round_points;
							$round_points_class[] = "negative";
						} else {
							// Explicit plus
							$player_round_points = "+" . $player_round_points;
							$round_points_class[] = "positive";
						}
					}
					if ($is_bye) {
						assert($player_round_points === null);
						assert(!$is_dealer);
						$player_round_points = "&ndash;";
						$round_points_class[] = "bye";
						$total_points_class[] = "bye";
					}
					if ($is_dealer) {
						$round_points_class[] = "dealer";
						$total_points_class[] = "dealer";
					}
					$is_bid_winner && $round_points_class[] = "bidwinner";
					$is_bid_winner_mate && $round_points_class[] = "bidwinnermate";
					$player_round_acc_points[$position][] = $player_acc_points;
					?>
					<td class="<?php echo implode(" ", $round_points_class) ?>"><?php echo rewrite_null($player_round_points) ?></td>
					<td class="<?php echo implode(" ", $total_points_class) ?>"><?php echo rewrite_null($player_acc_points) ?></td>
				<?php endforeach ?>
			</tr>
		<?php endforeach ?>
	</tbody>
	<tfoot>
		<?php print_full_header_row($players) ?>
		<tr class="aggregate-row">
			<th colspan="5">Total points (&sum;):</th>
			<?php foreach ($total_points as $points): ?>
				<?php
				$class = number_class($points);
				?>
				<th colspan="2" class="<?php echo $class ?>"><?php echo $points ?></th>
			<?php endforeach ?>
		</tr>
	</tfoot>
</table>

<script src="//code.highcharts.com/highcharts.js"></script>
<script src="//code.highcharts.com/modules/exporting.js"></script>

<div id="container" style="min-width: 310px; height: 300px; margin: 0 auto"></div>
<?php
// json_encode() does not work when the outermost array is numeric (a JSON array). Therefore we must join the individual objects
$series_json_entries = [];
foreach ($player_round_acc_points as $player_position => $acc_points) {
	array_unshift($acc_points, 0); // start with 0 points in "round 0"
	$series_json_entries[] = json_encode([
			'name' => $players[$player_position]['nickname'],
			'data' => $acc_points
	]);
}
// TODO HTML encode does not work...
$series_json = join(",\n", $series_json_entries);
?>

<script type="text/javascript">
	$(function() {
		$('#container').highcharts({
			title: {
				text: 'Points progression',
				x: -20 //center
			},
			xAxis: {
				title: {
					text: 'Round #'
				}
			},
			yAxis: {
				title: {
					text: 'Points'
				},
				plotLines: [{
						value: 0,
						width: 1,
						color: '#808080'
					}]
			},
			legend: {
				layout: 'vertical',
				align: 'right',
				verticalAlign: 'middle',
				borderWidth: 0
			},
			series: [
<?php echo $series_json ?>
			]
		});
	});
</script>

<div class="point-rules">
	<label>Point rules:</label>
	<?php echo implode(', ', $point_rules) ?>
</div>
<?php
$render_controls('bottom');
