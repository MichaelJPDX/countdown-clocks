<?php
// start secure page.
//include './secure-page.php';
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
		select { font-size: 13px; }

       .button-go {
            color: white;
            border-radius: 4px;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
            background: rgb(28, 184, 65); /* this is a green */
        }

	</style>
<?php
/*****************************************************************
**  Front-end to create a countdown timer
**
**  Author: Michael Holland - Sept. 2014
**
**	Revisions:
**
*****************************************************************/

//login server: https://papemlhcp02.intra.infousa.com/ews/exchange.asmx
// user name IUSA\username

//  function to parse file and find parameters.
function timerParser($file) {
	$lines = file($file);
	$parmstr = '';
	$start = false;
	foreach ($lines as $line_num => $line) {
		//echo "<p>" . $line . "</p>\n";
		if (substr(trim($line), 0, 7) == "@PARAMS") {
			if ($start) {
				break;
			} else {
				$start = true;
			}
		}
		if ($start) $parmstr .= $line;
	}
	$parma = parse_ini_string($parmstr);
	$parma['file'] = $file;
	return $parma;
}

function ddGen($maxVal) {
	$optlist = "";
	for ($n = 0; $n <= $maxVal; $n++) {
		$optlist .= "<option value=\"" . $n . "\">" . str_pad($n, 2, "0", STR_PAD_LEFT) . "</option>\n";
	}
	return $optlist;
}

//  Find and parse clock masters
if ($handle = opendir('./masters')) {
	$clockcount = 0;
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
        	//echo "<p>File: " . $entry;
            $timers[$clockcount] = timerParser('./masters/' . $entry);
            $clockcount++;
        }
    }
    closedir($handle);
}
//var_dump($timers);
//  Now we have an array of clock parameters. Build the forms and JS.
$namesel = "";
$namejs = "var tnames = new Array(";
$imgjs = "var timgs = new Array(";
$widjs = "var twids = new Array(";
$htjs = "var thts = new Array(";
$filejs = "var tfile = new Array(";
for($i = 0; $i < $clockcount; $i++) {
	$namesel .= "<option value=\"" . $i . "\">" . $timers[$i]['name'] . "</option>\n";
	if ($i > 0) $namejs .= ",";
	$namejs .= "\"" . $timers[$i]['name'] . "\"";
	if ($i > 0) $imgjs .= ",";
	$imgjs .= "\"" . $timers[$i]['sample'] . "\"";
	if ($i > 0) $widjs .= ",";
	$widjs .= "\"" . $timers[$i]['width'] . "\"";
	if ($i > 0) $htjs .= ",";
	$htjs .= "\"" . $timers[$i]['height'] . "\"";
	if ($i > 0) $filejs .= ",";
	$filejs .= "\"" . $timers[$i]['file'] . "\"";
}
$namejs .= ");";
$imgjs .= ");";
$widjs .= ");";
$htjs .= ");";
$filejs .= ");";

?>
	<script type="text/javascript" src="js/calendarDateInput.js"></script>
	<script type="text/javascript">
		<?php 
		echo $namejs . "\n";
		echo $imgjs . "\n";
		echo $widjs . "\n";
		echo $htjs . "\n";
		echo $filejs . "\n";
		?>
		function showSample() {
			var ts = document.timerGen.timer.options[document.timerGen.timer.selectedIndex].value;
			//alert("Selected item: " + ts);
			var imgTag = document.getElementById("sampleimg");
			imgTag.src = "samples/" + timgs[ts];
			imgTag.width = twids[ts];
			imgTag.height = thts[ts];
			document.timerGen.tfile.value = tfile[ts];
			document.getElementById("imgwid").innerHTML = twids[ts];
			document.getElementById("imght").innerHTML = thts[ts];
		}
	</script>
</head>
<body>
<div id="header">
	<div class="wrapper">
		<img src="images/logo.png" width="180" height="63" alt="logo">
	</div>
</div>
<div id="main">
<form name="timerGen" method="POST" action="generate-timer.php" class="pure-form pure-form-aligned">
	<h1>Countdown Clock Generator</h1>
	<div id="sample">
		<p>Sample of selected timer:</p>
		<img src="bars-black.png" width="300" height="73" alt="bars-black" id="sampleimg" />
		<p>Width: <span id="imgwid"></span> &nbsp; &nbsp; Height: <span id="imght"></span></p>
	</div>
	<fieldset>
        <div class="pure-control-group">
            <label for="timer">Timer Master:</label>
			<select name="timer" size="1" onchange="showSample();">
				<?php echo $namesel ?>
			</select>
        </div>
		<input name="tfile" type="hidden" value="" />
        <div class="pure-control-group">
            <label for="eventdate">Event date:</label>
			<script>DateInput('eventdate', true, 'DD-MON-YYYY')</script>
        </div>
        <div class="pure-control-group">
            <label for="hour">Event time:</label>
			<select name="hour" size="1">
				<?php echo ddGen(24); ?>
			</select>:<select name="minute" size="1">
				<?php echo ddGen(60); ?>
			</select>
			<select name="tz" size="1">
				<option value="America/New_York">Eastern Time</option>
				<option value="America/Chicago">Central Time</option>
				<option value="America/Denver">Mountain Time</option>
				<option value="America/Los_Angeles">Pacific Time</option>
				<option value="Pacific/Midway">Hammer Time</option>
			</select>
        </div>
       	<div class="pure-controls">
            <button type="submit" class="button-go pure-button"><i class="fa fa-cog fa-spin"></i> Generate!</button>
        </div>
</form>
</div>
</body>
<script type="text/javascript">
	showSample();
</script>
</html>