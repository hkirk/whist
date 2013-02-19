<table>
	<head>
	<tr>
		<th>Link</th><th>Started</th><th>Ended</th><th>Last update</th><th>Location</th>
	</tr>
</head>
<tbody>
	<?php foreach ($games as $game): ?>
		<tr>
			<td><a href="game.php?id=<?php echo $game['id'] ?>">Go to</a></td>
			<td><?php echo $game['started_at'] ?></td>
			<td><?php echo value_or($game['ended_at'], "Not ended") ?></td>
			<td><?php echo $game['updated_at'] ?></td>
			<td><?php echo value_or($game['location'], "?") ?></td>
		</tr>
	<?php endforeach ?>
</tbody>
</table>
