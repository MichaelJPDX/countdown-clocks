<?php
/*****************************************************************
**  Back-end to create a countdown timer
**
**  Author: Michael Holland - Sept. 2014
**
**	Revisions:
**
**  Posted parms from create-countdown.php -
**		tfile - Master timer file to copy
**		eventdate - Event date 
**		hour - Event hour (0 - 23)
**		minute - Event minue
**		tz - Event time zone
*****************************************************************/
if (!isset($_POST['tz'])) {
	echo "<p><strong>No data posted. This page has been called incorrectly.</strong></p>\n";
	exit;
}
$file = $_POST['tfile'];
$eventdate = $_POST['eventdate'];
$hour = $_POST['hour'];
$minute = $_POST['minute'];
$tz = $_POST['tz'];
$time = $eventdate . " " . $hour . ":" . $minute . ":00";

//  get the master file data
$lines = file($file, FILE_IGNORE_NEW_LINES);

// generate a file for the new event timer
$eventimg = uniqid("YM") . ".gif";
//$eventimg = tempnam("./ymcdt", "YM");
$handle = fopen("./ymctx/" . $eventimg, "w");
// Write the hard-coded parameters to the top of the file
fwrite($handle, "<?php\n");
fwrite($handle, "date_default_timezone_set('" . $tz . "');\n");
fwrite($handle, "\$future_date = strtotime('" . $time . "');\n");
$parmstr = '';
$start = false;
$endp = false;
// write out the rest of the master as-is
for ($i = 1; $i < count($lines); $i++) {
	fwrite($handle, $lines[$i] . "\n");
	if (substr(trim($lines[$i]), 0, 7) == "@PARAMS") {
		if (!$start) $start = true;
		else $endp = true;
	} else {
		if ($start and !$endp) {
			$parmstr .= $lines[$i] . "\n";
			//echo "<p>" . $lines[$i] . "</p>";
		}
	}
}
fclose($handle);
$parma = parse_ini_string($parmstr);
$parma['file'] = $file;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Countdown Generator</title>
	<link rel="stylesheet" href="css/pure.css">
	<link rel="stylesheet" href="css/grids-responsive.css">
	<link rel="stylesheet" href="css/layouts/marketing.css">
	<link rel="stylesheet" href="css/awesome/css/font-awesome.min.css">
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
			width: 950px; margin: 0 auto 0 auto;
		}
		#main { width: 950px; margin: 0 auto 0 auto; border: 1px solid #CCCCCC; border-radius: 5px; background-color: white; padding-bottom: 30px; }
		#sample { float: right; width: 300px; }
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
	</style>
</head>
<body>
<div id="header">
	<div class="wrapper">
		<img src="images/logo.png" width="180" height="63" alt="logo">
	</div>
</div>
<div id="main">
	<h1>Timer Generated!</h1>
	<div id="inner">
		<p>Image tag for use in your email:</p>

		<form name="iform" id="colprefs">
			<textarea name="xlate">&lt;img src="ymctx/<?php echo $eventimg ?>" width="<?php echo $parma['width'] ?>" height="<?php echo $parma['height'] ?>" style="display: block;" border="0" alt="" /&gt;</textarea>
			<button type="button" class="button-go pure-button" onClick="javascript:this.form.xlate.focus();this.form.xlate.select();"><i class="fa fa-copy"></i> Select Text</button>
		</form>
		<p>Image:</p>
		<img src="ymctx/<?php echo $eventimg ?>" width="<?php echo $parma['width'] ?>" height="<?php echo $parma['height'] ?>" style="display: block;" border="0" alt="" />
	</div>
</div>
</body>
</html>