<?php
/*****************************************************************
** Timer with bars
**
**  Countdown timer with bars between digits and full labels above.
@PARAMS
name = Basic Timer with Bars - black
width = 300
height = 73
sample = sample-timer-bars.gif
@PARAMS
**
**
*****************************************************************/
include '../GIFEncoder.class.php';

$bgimage = '../backgrounds/bars-black.png';
$r = 255;
$g = 244;
$b = 104;

//$time = '13 Nov 2013 23:01:07';
//$future_date = new DateTime(date('r',strtotime($time)));
//$future_date = strtotime($time);
$time_now = time();
//$now = new DateTime(date('r', $time_now));
$now = $time_now;

$frames = array();
$delays = array();

$image = imagecreatefrompng($bgimage);
$delay = 100; // milliseconds
$font = array(
			'size'=>34,
			'angle'=>0,
			'x-offset'=>10,
			'y-offset'=>60,
			'file'=>'../fonts/Trebuchet-MS-Bold.ttf',
			'color'=>imagecolorallocate($image, $r, $g, $b),
);
for($i = 0; $i <= 60; $i++){
	//$interval = date_diff($future_date, $now);
	if($future_date < $now){
		// Open the first source image and add the text.
		$image = imagecreatefrompng($bgimage);;
		//$text = $interval->format('00:00:00:00');
		//$text = '00:00:00:00';
		$text = '00';
		//imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] , $font['color'] , $font['file'], $text );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] , $font['color'] , $font['file'], $text );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+72 , $font['y-offset'] , $font['color'] , $font['file'], $text );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+148 , $font['y-offset'] , $font['color'] , $font['file'], $text );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+222 , $font['y-offset'] , $font['color'] , $font['file'], $text );
		ob_start();
		imagegif($image);
		$frames[]=ob_get_contents();
		$delays[]=$delay;
		$loops = 1;
		ob_end_clean();
		break;
	} else {
		// Open the first source image and add the text.
		$image = imagecreatefrompng($bgimage);;
		//$text = $interval->format('%a:%H:%I:%S');
		$textArray = myDDarray($now, $future_date, false);
		//echo "<p>days: " . $textArray[0] . " h: " . $textArray[1] . " m: " . $textArray[2] . " s: " . $textArray[3];
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] , $font['color'] , $font['file'], $textArray[0] );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+72 , $font['y-offset'] , $font['color'] , $font['file'], $textArray[1] );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+148 , $font['y-offset'] , $font['color'] , $font['file'], $textArray[2] );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+222 , $font['y-offset'] , $font['color'] , $font['file'], $textArray[3] );
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