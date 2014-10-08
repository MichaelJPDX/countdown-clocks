<?php
/*****************************************************************
** "Button" timmer
**
**  Countdown timer with bars between digits and full labels above.
@PARAMS
name = Button Timer - white background
width = 300
height = 89
sample = button-white.gif
@PARAMS
**
**
*****************************************************************/
include '../GIFEncoder.class.php';

	$bgimage = '../backgrounds/button-white.png';
	$r = 0;
	$g = 255;
	$b = 255;

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
			'size'=>32,
			'angle'=>0,
			'x-offset'=>11,
			'y-offset'=>53,
			'file'=>'../fonts/NeoSans.ttf',
			'color'=>imagecolorallocate($image, $r, $g, $b),
);
for($i = 0; $i <= 60; $i++){
	//$interval = date_diff($future_date, $now);
	if($future_date < $now){
		// Open the first source image and add the text.
		$image = imagecreatefrompng($bgimage);;
		//$text = $interval->format('00:00:00:00');
		$text = '00:00:00:00';
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] , $font['color'] , $font['file'], $text );
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
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] , $font['color'] , $font['file'], $textArray[0] );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+75 , $font['y-offset'] , $font['color'] , $font['file'], $textArray[1] );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+150 , $font['y-offset'] , $font['color'] , $font['file'], $textArray[2] );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+223 , $font['y-offset'] , $font['color'] , $font['file'], $textArray[3] );
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