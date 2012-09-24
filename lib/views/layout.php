<!DOCTYPE html>
<html>
	<head>
		<title>WhistCalc - <?php echo $subtitle; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	</head>
	<body>
		<nav>
			<a href="games.php">Games</a>
		</nav>
		<div>
			<h1><?php echo $headline; ?></h1>
			<div>
				<?php render_view($view, $viewData); ?>
			</div>
		</div>
	</body>
</html>
