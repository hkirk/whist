<!DOCTYPE html>
<html>
	<head>
		<title>WhistCalc - <?php echo $subtitle; ?></title>

		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />

		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="styles/style.css" />

		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
	</head>
	<body>

		<div class="navbar navbar-default">
			<div class="navbar-inner">
				<div class="container">
					<header>
						<nav>
							<ul class="nav nav-pills" data-tabs="tabs">
								<li <?php echo ($view == "games" ? "class=\"active\"" : "") ?>><a href="games.php">Games</a></li>
								<li <?php echo ($view == "newgame" ? "class=\"active\"" : "") ?>><a href="newgame.php">New game</a></li>
								<li <?php echo ($view == "createplayer" ? "class=\"active\"" : "") ?>><a href="createplayer.php">Create player</a></li>
								<li <?php echo ($view == "createlocation" ? "class=\"active\"" : "") ?>><a href="createlocation.php">Create location</a></li>
                <li <?php echo ($view == "stats" ? "class=\"active\"" : "") ?>><a href="stats.php">Statistics</a></li>
							</ul>
						</nav>
					</header>
				</div>
			</div>
    </div>

		<div class="container">
			<h1><?php echo $headline; ?></h1>
			<div>
				<?php render_view($view, $view_data); ?>
			</div>
		</div>

		<div id="footer">
			<div class="jumbotron">
				<footer>
					By Clausa @ CA-IT and Hense @ Busywait, 2012 - 2014.
				</footer>
			</div>
		</div>

	</body>
</html>
