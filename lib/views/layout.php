<!DOCTYPE html>
<html>
<head>
    <title>WhistCalc - <?php echo $subtitle; ?></title>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="http://twitter.github.com/bootstrap/assets/css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="http://twitter.github.com/bootstrap/assets/css/bootstrap-responsive.css" />

    <script type="text/javascript" src="http://twitter.github.com/bootstrap/assets/js/bootstrap.min.js"></script>

    <style type="text/css">
        html,
        body {
            height: 100%;
            /* The html and body elements cannot have any padding or margin. */
        }

        /* Wrapper for page content to push down footer */
        #wrap {
            min-height: 100%;
            height: auto !important;
            height: 100%;
            /* Negative indent footer by it's height */
            padding-bottom:60px;
            margin: 0 auto -60px;
        }

        /* Set the fixed height of the footer here */
        #footer {
            height: 60px;
            background-color: #f5f5f5;
        }

        /* Lastly, apply responsive CSS fixes as necessary */
        @media (max-width: 767px) {
            #footer {
                margin-left: -20px;
                margin-right: -20px;
                padding-left: 20px;
                padding-right: 20px;
            }
        }
        #wrap > .container-fluid {
            padding-top: 60px;
        }

    </style>
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
                By Clausa, CA-IT, 2012
            </footer>
        <div class="container-fluid">
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
