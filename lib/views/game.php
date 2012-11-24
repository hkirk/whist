<?php
global $SOLO_GAMES;
global $ATTACHMENTS;


function rewrite_null($e) {
	return $e === NULL ? "?" : $e;
}
?>

<?php
$controls_view_data['id_qualifier'] = 'top';
render_view('controls/' . $controls_view, $controls_view_data);
?>
<h2>Score board</h2>
<table>
	<thead>
		<tr>
			<th>#</th>
			<th>Bid</th>
			<th>Tricks</th>
			<?php foreach ($players as $player): ?>
				<th colspan="2"><?php echo htmlspecialchars($player['nickname']) ?></th>
			<?php endforeach ?>
			<th>Bid winner(s)</th>
            <th>Diff.</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$tricks_diff_sum = 0;
		$tricks_diff_count = 0;
		?>
		<?php foreach ($rounds as $round): ?>
			<tr>
				<?php
				$dealer_position = $round['dealer_position'];
				$bid = $round['bid'];
				if ($bid['type'] === "solo") {
					$solo_game = $SOLO_GAMES[$bid['solo_type']];
					$bid_text = sprintf('%s (%d)', $solo_game['name'], $solo_game['max_tricks']);
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
						$diff = $tricks_diff_sign * ($tricks - $target_tricks);
						$bid_winner_tricks_diff[] = $diff;
						$tricks_diff_sum += $diff;
						$tricks_diff_count++;
					}
				}
				if ($bid_winner_mate_position !== NULL) {
					$bid_winner_names[0] .= " (" . $players[$bid_winner_mate_position]['nickname'] . ")";
				}
				?>
				<td><?php echo $round['index'] ?></td>
				<td><?php echo $bid_text ?> </td>
				<td><?php echo implode(", ", $bid_winner_tricks_or_unknown_by_position) ?></td>
				<?php foreach ($round['player_data'] as $position => $player_data): ?>
					<?php
					$player_round_points = $player_data['round_points'];
					$player_total_points = $player_data['total_points'];
					$is_dealer = $position === $dealer_position;
					$is_bid_winner = in_array($position, $bid_winner_positions);
					$is_bid_winner_mate = $position === $bid_winner_mate_position;
					$class = $player_round_points < 0 ? "negative" : "positive";
					if ($is_dealer) {
						$class .= " dealer";
					}
					$is_bid_winner && $class .= " bidwinner";
					$is_bid_winner_mate && $class .= " bidwinnermate";
					?>
					<td class="<?php echo $class ?>"><?php echo rewrite_null($player_round_points) ?></td>
					<td><?php echo rewrite_null($player_total_points) ?></td>
				<?php endforeach ?>
				<td><?php echo implode(", ", $bid_winner_names) ?></td>
				<td><?php echo implode(", ", $bid_winner_tricks_diff) ?></td>
			</tr>
		<?php endforeach ?>
		<?php
		if ($tricks_diff_count === 0) {
			$tricks_diff_avg = "?";
		} else {
			$tricks_diff_avg = $tricks_diff_sum / $tricks_diff_count;
		}
		?>
	</tbody>
	<tfoot>
		<tr>
			<th>#</th>
			<th colspan="2">Total:</th>
			<?php foreach ($total_points as $points): ?>
				<th colspan="2"><?php echo $points ?></th>
			<?php endforeach ?>
			<th>Bid winner(s)</th>
            <th><?php echo $tricks_diff_sum ?> (<?php echo $tricks_diff_avg ?>)</th>
		</tr>
	</tfoot>
</table>
<?php
$controls_view_data['id_qualifier'] = 'bottom';
render_view('controls/' . $controls_view, $controls_view_data);
?>
