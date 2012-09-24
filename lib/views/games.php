All games

<table>
	<head>
		<tr>
			<th>Tid</th><th>Hvor</th>
		</tr>
	</head>
	<tbody>
		<?php foreach($stm as $row): ?>
			<tr>
				<td><?php echo $row['started_at']; ?></td>
				<td><?php echo $row['location']; ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>
