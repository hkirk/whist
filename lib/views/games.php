<table class="table">
	<thead>
		<tr>
			<th>Link</th><th>Started</th><th>Ended</th><th>Last update</th><th>Players</th><th>Location</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($games as $game): ?>
			<tr>
				<td><a href="game.php?id=<?php echo $game['id'] ?>">Go to</a></td>
				<td><?php echo datetime_string($game['started_at']) ?></td>
				<td><?php echo datetime_string($game['ended_at'], "&ndash;") ?></td>
				<td><?php echo $game['updated_at'] ?></td>
				<td><?php echo $game['n_players'] ?></td>
				<td><?php echo value_or($game['location'], "?") ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>
