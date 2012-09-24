<?php


function rewrite_null($e) {
	return $e === NULL ? "?" : $e;
}
?>
<table>
	<thead>
		<tr>
			<th>#</th>
			<th>Bid</th>
			<th>Tricks</th>
			<?php foreach ($players as $player): ?>
				<th colspan="2"><?php echo htmlspecialchars($player['nickname']) ?></th>
			<?php endforeach ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($rounds as $round): ?>
			<tr>
				<?php
				$dealer_position = $round['dealer_position'];
				$bid_winner_tricks_by_position = array_map_nulls($round['bid_winner_tricks_by_position'], "?");
				$bid_winner_positions = array_keys($bid_winner_tricks_by_position);
				$bid_winner_mate_position = $round['bid_winner_mate_position'];
				?>
				<td><?php echo $round['index'] ?></td>
				<td><?php echo $round['bid'] ?> </td>
				<td><?php echo implode(", ", $bid_winner_tricks_by_position) ?></td>
				<?php foreach ($round['players'] as $position => $player_data): ?>
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
			</tr>
		<?php endforeach ?>
	</tbody>
	<tfoot>
		<tr>
			<td>#</td>
			<td colspan="2">Total:</td>
			<?php foreach ($total_points as $points): ?>
				<td colspan="2"><?php echo $points ?></td>
			<?php endforeach ?>
		</tr>
	</tfoot>
</table>
<?php
render_view('controls/' . $controls_view, $controls_view_data);
?>
