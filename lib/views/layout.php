<!DOCTYPE html>
<html>
	<head>
		<title>WhistCalc - <?php echo $subtitle; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" href="styles/style.css" />
	</head>
	<body>
		<header>
			<nav>
				<ul>
					<li><a href="games.php">Games</a></li>
					<li><a href="newgame.php">New game</a></li>
				</ul>
			</nav>
		</header>
		<hr />
		<div>
			<h1><?php echo $headline; ?></h1>
			<hr />
			<div>
				<?php render_view($view, $view_data); ?>
			</div>
		</div>
		<hr />
		<footer>
			By Clausa, CA-IT, 2012
		</footer>
	</body>
</html>
