<?php
global $SOLO_GAMES;
global $ATTACHMENTS;


function rewrite_null($e) {
	return $e === NULL ? "?" : $e;
}
?>

<?php
$render_controls = function($position) use($controls_positions, $controls_view, $controls_view_data) {
					//global $controls_positions;
					if (in_array($position, $controls_positions)) {
						$controls_view_data['id_qualifier'] = $position;
						render_view('controls/' . $controls_view, $controls_view_data);
					}
				};
if ($cancel_view !== NULL) {
	render_view('controls/' . $cancel_view, $cancel_view_data);
}
$render_controls('top');
?>
<h2>Score board</h2>
<table class="scoreboard">
	<thead>
		<tr>
			<th>#</th>
			<th>Bid winner(s)</th>
			<th>Bid</th>
			<th>Tricks</th>
			<th>&Delta;</th>
			<?php foreach ($players as $player): ?>
				<th colspan="2"><?php echo htmlspecialchars($player['nickname']) ?></th>
			<?php endforeach ?>
		</tr>
	</thead>
	<tbody>
		<?php
		$bid_winner_count_by_position = array_fill(0, N_PLAYERS, 0);
		$bid_winner_mate_count_by_position = array_fill(0, N_PLAYERS, 0);
		$tricks_sum = 0;
		$tricks_diff_sum = 0;
		$bid_winners_with_tricks_count = 0;
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
				$bid_winner_names = array();
				$bid_winner_tricks_diff = array();
				//var_dump($bid_winner_tricks_by_position);
				foreach ($round['bid_winner_tricks_by_position'] as $position => $tricks) {
					$bid_winner_names[] = $players[$position]['nickname'];
					if ($tricks === NULL) {
						$bid_winner_tricks_diff[] = "?";
					} else {
						$tricks_sum += $tricks;
						$diff = $tricks_diff_sign * ($tricks - $target_tricks);
						$bid_winner_tricks_diff[] = $diff;
						$tricks_diff_sum += $diff;
						$bid_winners_with_tricks_count++;
					}
					$bid_winner_count_by_position[$position]++;
				}
				if ($bid_winner_mate_position !== NULL) {
					$bid_winner_names[0] .= " (" . $players[$bid_winner_mate_position]['nickname'] . ")";
					$bid_winner_mate_count_by_position[$bid_winner_mate_position]++;
				}
				?>
				<td><?php echo $round['index'] ?></td>
				<td><?php echo implode(", ", $bid_winner_names) ?></td>
				<td><?php echo $bid_text ?> </td>
				<td><?php echo implode(", ", $bid_winner_tricks_or_unknown_by_position) ?></td>
				<td><?php echo implode(", ", $bid_winner_tricks_diff) ?></td>
				<?php foreach ($round['player_data'] as $position => $player_data): ?>
					<?php
					$player_round_points = $player_data['round_points'];
					$player_total_points = $player_data['total_points'];
					$is_dealer = $position === $dealer_position;
					$is_bid_winner = in_array($position, $bid_winner_positions);
					$is_bid_winner_mate = $position === $bid_winner_mate_position;
					$round_points_class = array();
					$total_points_class = array();
					if ($player_round_points !== NULL) {
						if ($player_round_points < 0) {
							$player_round_points = "" . $player_round_points;
							$round_points_class[] = "negative";
						} else {
							// Explicit plus
							$player_round_points = "+" . $player_round_points;
							$round_points_class[] = "positive";
						}
					}
					if ($is_dealer) {
						$round_points_class[] = "dealer";
						$total_points_class[] = "dealer";
					}
					$is_bid_winner && $round_points_class[] = "bidwinner";
					$is_bid_winner_mate && $round_points_class[] = "bidwinnermate";
					?>
					<td class="<?php echo implode(" ", $round_points_class) ?>"><?php echo rewrite_null($player_round_points) ?></td>
					<td class="<?php echo implode(" ", $total_points_class) ?>"><?php echo rewrite_null($player_total_points) ?></td>
				<?php endforeach ?>
			</tr>
		<?php endforeach ?>
		<?php
		for ($p = 0; $p < N_PLAYERS; $p++) {
			$bid_winner_count_texts[$p] = sprintf("%d (%d)", $bid_winner_count_by_position[$p], $bid_winner_mate_count_by_position[$p]);
		}
		if ($bid_winners_with_tricks_count === 0) {
			$tricks_avg = "?";
			$tricks_diff_avg = "?";
		} else {
			$tricks_avg = $tricks_sum / $bid_winners_with_tricks_count;
			$tricks_diff_avg = $tricks_diff_sum / $bid_winners_with_tricks_count;
		}
		?>
	</tbody>
	<tfoot>
		<tr>
			<th rowspan="3">#</th>
			<th>Bid winner(s)</th>
			<th>Bid</th>
			<th>Tricks</th>
			<th>&Delta;</th>
			<?php foreach ($players as $player): ?>
				<th colspan="2"><?php echo htmlspecialchars($player['nickname']) ?></th>
			<?php endforeach ?>
		</tr>
		<tr>
			<th colspan="2">Total:</th>
			<th><?php printf("%d", $tricks_sum) ?></th>
			<th><?php printf("%d", $tricks_diff_sum) ?></th>
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
		<tr>
			<th colspan="2">Avg. / bid winner count:</th>
			<th><?php printf("%.2f", $tricks_avg) ?></th>
			<th><?php printf("%.2f", $tricks_diff_avg) ?></th>
			<?php foreach ($bid_winner_count_texts as $bid_winner_count_text): ?>
				<th colspan="2"><?php echo $bid_winner_count_text ?></th>
			<?php endforeach ?>
		</tr>
	</tfoot>
</table>
<div class="point-rules">
	<label>Point rules:</label>
	<?php echo implode(',', $point_rules) ?>
</div>
<?php
$render_controls('bottom');
?>
