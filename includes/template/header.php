<?php
/*****************************************************************
**  Header template for countodown timers
**
**  Author: Michael Holland - Sept. 2014
**
**	Set var $pageTitle to set the page title.
**
**	Revisions:
**
*****************************************************************/
if (!isset($pagetitle)) $pagetitle = "Yesmail Countdown Timers";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $pagetitle ?></title>
	<link rel="stylesheet" href="/clock/prod/css/pure.css">
	<link rel="stylesheet" href="/clock/prod/css/grids-responsive.css">
	<!--link rel="stylesheet" href="/clock/prod/css/marketing.css"-->
	<link rel="stylesheet" href="/clock/prod/css/awesome/css/font-awesome.min.css">
	<style type="text/css">
		body { background-color: #F7F7F7; margin: 0; padding: 0; font-family: Helvetica, Verdana, sans-serif; font-size: medium; }
		#header { width: 100%; height: 75px; padding: 25px 0 15px 0;
			background-image: -webkit-gradient(linear, left top, left bottom, from(#e0f0ff), to(#000064));
			background-image: -webkit-linear-gradient(top, #DDE5E8, #989FA5);
			background-image: -moz-linear-gradient(top, #DDE5E8, #989FA5);
			background-image: -ms-linear-gradient(top, #DDE5E8, #989FA5);
			background-image: -o-linear-gradient(top, #DDE5E8, #989FA5);
			background-image: linear-gradient(top, #DDE5E8, #989FA5);
			border-bottom: 1px solid #A0A7AC; margin: 0 0 20px 0;
		}
		h1 { font-size: 180%; text-align: center; }
		.wrapper {
			width: 950px; max-width: 100%; margin: 0 auto 0 auto;
		}
		#main { width: 950px; max-width: 100%; margin: 0 auto 0 auto; border: 1px solid #CCCCCC; border-radius: 5px; background-color: white; padding-bottom: 30px; }
		#sample { float: right; width: 410px; margin: 0 24px 0 0; }
		form { padding: 24px; }
		#inner { padding: 24px; }
		form { margin: 0; padding: 0; }
		button { color: #0064d2; font-size: 16px; font-weight: bold; background-color: #f0dde1; display: block; }
		textarea { width: 700px; height: 80px; margin: 0 auto 0 auto; font-family: "Courier New", Courier, monospace; padding: 12px; }
       .button-go {
            color: white;
            border-radius: 4px;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
            background: rgb(28, 184, 65); /* this is a green */
            margin: 0 0 0 8px;
        }
        #logoff { color: #1CB841; font-size: 36px; width: 50px; float: right; margin: 24px 0 0 0; }
        #logoff a { color: #1CB841; }
        #logoff a:hover { color: red; }
        .pure-control-group td { border: none; padding: 0 6px 0 0; }
		/*
		** Admin styles
		*/
		td { padding: 6px; border: 1px solid black; }
		tr.even { background: rgba(226, 226, 226, 0.7); }
		tr.odd { background: rgba(226, 226, 255, 0.7); }
		.trashicon { font-size: 1.3em; color: red; }
		.homelink { float: right; font-size: 1.5em; }
        .homelink a { color: #1CB841; }
	</style>
</head>
<body>
<div id="header">
	<div class="wrapper">
		<img src="/clock/prod/images/logob.png" width="180" height="63" alt="logo">
		<?php
		if ($this->getUserLoginStatus()) {
			echo '<div id="logoff"><a href="' . $_SERVER['SCRIPT_NAME'] . '?action=logout" title="Log Off"><i class="fa fa-power-off"></i></a></div>';
		}
		?>
	</div>
</div>
<div id="main">
