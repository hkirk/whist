<?php
global $SOLO_GAMES;
global $ATTACHMENTS;


function rewrite_null($e) {
	return $e === NULL ? "?" : $e;
}


function avg_string($sum, $n) {
	if ($n === 0) {
		return NULL;
	} else {
		return sprintf("%.2f", $sum / $n);
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
$nicknames = array();
?>
<h2>Score board</h2>
<table class="table table-striped .table-responsive scoreboard">
	<thead>
		<tr class="full-row">
			<th>#</th>
			<th>Bid winner(s)</th>
			<th>Bid</th>
			<th>Tricks</th>
			<th>&Delta;</th>
			<?php foreach ($players as $player): ?>
				<th colspan="2"><?php echo htmlspecialchars($player['nickname']);
				                      $nicknames[] = array("nickname" => htmlspecialchars($player['nickname']),
				                                           "points" => array()
				                                           );
				                ?></th>
			<?php endforeach ?>
		</tr>
	</thead>
	<tbody>
		<?php
		$bid_winner_count_by_position = array_fill(0, $number_of_players, 0);
		$bid_winner_mate_count_by_position = array_fill(0, $number_of_players, 0);
		$tricks_sum_normal = 0;
		$tricks_sum_solo = 0;
		$tricks_diff_sum = 0;
		$tricks_abs_diff_sum = 0;
		$bid_winners_with_tricks_count_normal = 0;
		$bid_winners_with_tricks_count_solo = 0;
		?>
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
					$tricks_sum_ref = &$tricks_sum_solo;
					$bid_winners_with_tricks_count_ref = &$bid_winners_with_tricks_count_solo;
				} else {
					$bid_text = sprintf('%d', $bid['tricks']);
					if ($bid['attachment'] !== NONE) {
						$bid_text .= ' ' . $ATTACHMENTS[$bid['attachment']]['name'];
					}
					$target_tricks = $bid['tricks'];
					$tricks_diff_sign = 1;
					$tricks_sum_ref = &$tricks_sum_normal;
					$bid_winners_with_tricks_count_ref = &$bid_winners_with_tricks_count_normal;
				}
				$bid_winner_tricks_or_unknown_by_position = array_map_nulls($round['bid_winner_tricks_by_position'], "?");
				$bid_winner_positions = array_keys($bid_winner_tricks_or_unknown_by_position);
				$bid_winner_mate_position = $round['bid_winner_mate_position'];
				$bid_winner_names = [];
				$bid_winner_tricks_diff = [];
				//var_dump($bid_winner_tricks_by_position);
				foreach ($round['bid_winner_tricks_by_position'] as $position => $tricks) {
					$bid_winner_names[] = $players[$position]['nickname'];
					if ($tricks === NULL) {
						$bid_winner_tricks_diff[] = "?";
					} else {
						$tricks_sum_ref += $tricks;
						$diff = $tricks_diff_sign * ($tricks - $target_tricks);
						$bid_winner_tricks_diff[] = $diff;
						$tricks_diff_sum += $diff;
						$tricks_abs_diff_sum += abs($diff);
						$bid_winners_with_tricks_count_ref++;
					}
					$bid_winner_count_by_position[$position] ++;
				}
				if ($bid_winner_mate_position !== NULL) {
					$bid_winner_names[0] .= " (" . $players[$bid_winner_mate_position]['nickname'] . ")";
					$bid_winner_mate_count_by_position[$bid_winner_mate_position] ++;
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
					?>
					<td class="<?php echo implode(" ", $round_points_class) ?>"><?php echo rewrite_null($player_round_points) ?></td>
					<td class="<?php echo implode(" ", $total_points_class) ?>"><?php echo rewrite_null($player_acc_points) ?></td>
					<?php
					  $nicknames[$position]["points"][] = $player_acc_points;
					?>
				<?php endforeach ?>
			</tr>
		<?php endforeach ?>
		<?php
		$tricks_sum = $tricks_sum_normal + $tricks_sum_solo;
		$bid_winners_with_tricks_count = $bid_winners_with_tricks_count_normal + $bid_winners_with_tricks_count_solo;
		$tricks_avg_normal_string = avg_string($tricks_sum_normal, $bid_winners_with_tricks_count_normal);
		$tricks_avg_solo_string = avg_string($tricks_sum_solo, $bid_winners_with_tricks_count_solo);
		$tricks_avg_string = avg_string($tricks_sum, $bid_winners_with_tricks_count);
		$tricks_diff_avg_string = avg_string($tricks_diff_sum, $bid_winners_with_tricks_count);
		$tricks_abs_diff_avg_string = avg_string($tricks_abs_diff_sum, $bid_winners_with_tricks_count);
		for ($p = 0; $p < $number_of_players; $p++) {
			$bid_winner_count_texts[$p] = sprintf("%d (%d)", $bid_winner_count_by_position[$p], $bid_winner_mate_count_by_position[$p]);
		}
		?>
	</tbody>
	<tfoot>
		<tr class="full-row">
			<th rowspan="3">#</th>
			<th>Bid winner(s)</th>
			<th>Bid</th>
			<th>Tricks</th>
			<th>&Delta;</th>
			<?php foreach ($players as $player): ?>
				<th colspan="2"><?php echo htmlspecialchars($player['nickname']) ?></th>
			<?php endforeach ?>
		</tr>
		<tr class="aggregate-row">
			<th colspan="2">Total (&sum;):</th>
			<th title="(Normal, Solo)"><?php echo "$tricks_sum ($tricks_sum_normal, $tricks_sum_solo)" ?></th>
			<th title="&#x01c0;Abs&#x01c0;"><?php echo "$tricks_diff_sum ~ &#x01c0;$tricks_abs_diff_sum&#x01c0;" ?></th>
			<?php foreach ($total_points as $points): ?>
				<?php
				if ($points < 0) {
					$class = "negative";
				} else if ($points > 0) {
					$class = "positive";
				} else {
					$class = "zero";
				}
				?>
				<th colspan="2" class="<?php echo $class ?>"><?php echo $points ?></th>
			<?php endforeach ?>
		</tr>
		<tr class="aggregate-row">
			<th colspan="2">Avg. / bid winner (mate) count:</th>
			<th title="(Normal, Solo)"><?php echo rewrite_null($tricks_avg_string) . ' (' . rewrite_null($tricks_avg_normal_string) . ', ' . rewrite_null($tricks_avg_solo_string) . ')' ?></th>
			<th title="&#x01c0;Abs&#x01c0;"><?php echo rewrite_null($tricks_diff_avg_string) . ' ~ &#x01c0;' . rewrite_null($tricks_abs_diff_avg_string) . '&#x01c0;' ?></th>
			<?php foreach ($bid_winner_count_texts as $bid_winner_count_text): ?>
				<th colspan="2"><?php echo $bid_winner_count_text ?></th>
			<?php endforeach ?>
		</tr>
	</tfoot>
</table>

<script src="http://code.highcharts.com/highcharts.js"></script>
<script src="http://code.highcharts.com/modules/exporting.js"></script>

<div id="container" style="min-width: 310px; height: 300px; margin: 0 auto"></div>

<script type="text/javascript">
$(function () {
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
            <?php foreach ($nicknames as $nickname): ?>
              {
                name: '<?php echo htmlspecialchars($nickname['nickname']); ?>',
                data: [ <?php echo implode(",", $nickname["points"]); ?> ]
              },
            <?php endforeach ?>
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
?>
