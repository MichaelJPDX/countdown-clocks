<?php
/*****************************************************************
**  GNC standard timer
**
**  Black background, white labels above blue numbers
@PARAMS
name = GNC Master Template Timer
width = 401
height = 102
sample = gnc-black.gif
@PARAMS
**
**
*****************************************************************/
include 'GIFEncoder.class.php';

//  The following variables change for each master
$bgimage = '../backgrounds/gnctimer.png'; // The background image of the timer
$fontfile = '../fonts/MYRIADAB.TTF'; //  Font file to use - MUST be Truetype
//  Font colors red, green, blue
$r = 0;
$g = 174;
$b = 239;

// For development & testing of new masters, uncomment the following three lines and adjust the $time to a future date
//date_default_timezone_set('America/Los_Angeles');
//$time = '13 Nov 2014 23:01:07';
//$future_date = strtotime($time);

$time_now = time();
$now = $time_now;

$frames = array();
$delays = array();

$image = imagecreatefrompng($bgimage);
$delay = 100; // milliseconds
$font = array(
			'size'=>40,
			'angle'=>0,
			'x-offset'=>37,
			'y-offset'=>80,
			'file'=>$fontfile,
			'color'=>imagecolorallocate($image, $r, $g, $b),
);
for($i = 0; $i <= 60; $i++){
	if($future_date < $now){
		// The event date is now passed, fill the clock with 00 values
		$image = imagecreatefrompng($bgimage);;
		$text = '00';
		//imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] , $font['color'] , $font['file'], $text );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] , $font['color'] , $font['file'], $text );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+90 , $font['y-offset'] , $font['color'] , $font['file'], $text );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+180 , $font['y-offset'] , $font['color'] , $font['file'], $text );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+270 , $font['y-offset'] , $font['color'] , $font['file'], $text );
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
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] , $font['color'] , $font['file'], $textArray[0] );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+90 , $font['y-offset'] , $font['color'] , $font['file'], $textArray[1] );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+180 , $font['y-offset'] , $font['color'] , $font['file'], $textArray[2] );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+270 , $font['y-offset'] , $font['color'] , $font['file'], $textArray[3] );
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