<!DOCTYPE html>
<html>
	<head>
    <title>WhistCalc - <?php echo $subtitle; ?></title>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="stylesheet" type="text/css" href="http://twitter.github.com/bootstrap/assets/css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="http://twitter.github.com/bootstrap/assets/css/bootstrap-responsive.css" />
		<link rel="stylesheet" type="text/css" href="styles/style.css" />

    <script type="text/javascript" src="http://twitter.github.com/bootstrap/assets/js/bootstrap.min.js"></script>
	</head>
	<body>

    <div id="wrap">
			<div class="navbar navbar-fixed-top">
				<div class="navbar-inner">
					<div class="container">
						<header>
							<nav>
								<ul class="nav nav-pills" data-tabs="tabs">
									<li <?php echo ($view == "games" ? "class=\"active\"" : "") ?>><a href="games.php">Games</a></li>
									<li <?php echo ($view == "newgame" ? "class=\"active\"" : "") ?>><a href="newgame.php">New game</a></li>
									<li <?php echo ($view == "createplayer" ? "class=\"active\"" : "") ?>><a href="createplayer.php">Create player</a></li>
									<li <?php echo ($view == "createlocation" ? "class=\"active\"" : "") ?>><a href="createlocation.php">Create location</a></li>
								</ul>
							</nav>
						</header>
					</div>
				</div>
			</div>

			<div class="container-fluid">
				<h1><?php echo $headline; ?></h1>
				<div>
					<?php render_view($view, $view_data); ?>
				</div>
			</div>
    </div>

    <div id="footer">
			<div class="container-fluid">
				<footer>
					By Clausa @ CA-IT and Hense @ Busywait, 2013
				</footer>
			</div>
    </div>

    <script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
    <script type="text/javascript" src="http://twitter.github.com/bootstrap/assets/js/jquery.js"></script>
    <script type="text/javascript" src="http://twitter.github.com/bootstrap/assets/js/bootstrap-transition.js"></script>
    <script type="text/javascript" src="http://twitter.github.com/bootstrap/assets/js/bootstrap-alert.js"></script>
    <script type="text/javascript" src="http://twitter.github.com/bootstrap/assets/js/bootstrap-modal.js"></script>
    <script type="text/javascript" src="http://twitter.github.com/bootstrap/assets/js/bootstrap-dropdown.js"></script>
    <script type="text/javascript" src="http://twitter.github.com/bootstrap/assets/js/bootstrap-scrollspy.js"></script>
    <script type="text/javascript" src="http://twitter.github.com/bootstrap/assets/js/bootstrap-tab.js"></script>
    <script type="text/javascript" src="http://twitter.github.com/bootstrap/assets/js/bootstrap-tooltip.js"></script>
    <script type="text/javascript" src="http://twitter.github.com/bootstrap/assets/js/bootstrap-popover.js"></script>
    <script type="text/javascript" src="http://twitter.github.com/bootstrap/assets/js/bootstrap-button.js"></script>
    <script type="text/javascript" src="http://twitter.github.com/bootstrap/assets/js/bootstrap-collapse.js"></script>
    <script type="text/javascript" src="http://twitter.github.com/bootstrap/assets/js/bootstrap-carousel.js"></script>
    <script type="text/javascript" src="http://twitter.github.com/bootstrap/assets/js/bootstrap-typeahead.js"></script>
    <script type="text/javascript" src="http://twitter.github.com/bootstrap/assets/js/bootstrap-affix.js"></script>
	</body>
</html>
