<?php
/*****************************************************************
**  GNC standard timer
**
**  Black background, white labels above blue numbers
@PARAMS
name = GNC White Template Timer
width = 300
height = 90
sample = gnc-white.gif
@PARAMS
**
**
*****************************************************************/
include 'GIFEncoder.class.php';

//  The following variables change for each master
$bgimage = '../backgrounds/gncwhite.png'; // The background image of the timer
$endimage = '../backgrounds/gncend.png'; // The background image of the timer
$fontfile = '../fonts/Arial_Bold.ttf'; //  Font file to use - MUST be Truetype
//  Font colors red, green, blue
$r = 8;
$g = 97;
$b = 149;

// For development & testing of new masters, uncomment the following three lines and adjust the $time to a future date
//date_default_timezone_set('America/Los_Angeles');
//$time = '16 Feb 2015 23:01:07';
//$future_date = strtotime($time);

$time_now = time();
$now = $time_now;

$frames = array();
$delays = array();

$image = imagecreatefrompng($bgimage);
$delay = 100; // milliseconds
$font = array(
			'size'=>50,
			'angle'=>0,
			'x-offset'=>5,
			'y-offset'=>80,
			'file'=>$fontfile,
			'color'=>imagecolorallocate($image, $r, $g, $b),
);
for($i = 0; $i <= 60; $i++){
	if($future_date < $now){
		// The event date is now passed, fill the clock with 00 values
		$image = imagecreatefrompng($endimage);
		$text = '00';
		ob_start();
		imagegif($image);
		$frames[]=ob_get_contents();
		$delays[]=$delay;
		$loops = 1;
		ob_end_clean();
		break;
	} else {
		// Add an overlay for this second
		$image = imagecreatefrompng($bgimage);;
		//$text = $interval->format('%a:%H:%I:%S');
		$textArray = myDDarray($now, $future_date, false);
		//echo "<p>days: " . $textArray[0] . " h: " . $textArray[1] . " m: " . $textArray[2] . " s: " . $textArray[3];
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] , $font['color'] , $font['file'], str_pad($textArray[0], 2, "0", STR_PAD_LEFT);
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+105 , $font['y-offset'] , $font['color'] , $font['file'], $textArray[1] );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+210 , $font['y-offset'] , $font['color'] , $font['file'], $textArray[2] );
		ob_start();
		imagegif($image);
		$frames[]=ob_get_contents();
		$delays[]=$delay;
		$loops = 0;
		ob_end_clean();
	}
	//$now->modify('+1 second');
	$now += 1;
}
//expire this image instantly
header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );
$gif = new AnimatedGif($frames,$delays,$loops);
$gif->display();
?>