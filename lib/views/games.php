All games

<table>
	<head>
		<tr>
			<th>Tid</th><th>Hvor</th>
		</tr>
	</head>
	<tbody>
		<?php foreach($games as $game): ?>
			<tr>
				<td><?php echo $game['started_at']; ?></td>
				<td><?php echo $game['location']; ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>
