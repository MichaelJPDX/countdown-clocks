<?php
date_default_timezone_set('America/Los_Angeles');
include 'GIFEncoder.class.php';

//$time = $_GET['time'];
$sendDate = $_GET['date'];
$tvals = explode('-', $sendDate);
if (isset($_GET['hour'])) $hour = $_GET['hour'];
else $hour = 8;
//$time = '13 Nov 2013 23:01:07';
//$future_date = new DateTime(date('r',strtotime($time)));
if ($sendDate != '') {
	$future_date = mktime ($hour, 0, 0, $tvals[1], $tvals[2]+1, $tvals[0]);
} else {
	$future_date = mktime (8, 0, 0, date("n"), date("j")+1, date("Y"));
}
//echo $tvals[0] . "-" . $tvals[1] . '-' . $tvals[2];
//exit;
//$future_date = strtotime($time);
//http://seaundersea.com/clock/timer-basic.php?time=13+Nov+2014+23:07:00
$time_now = time();
//$now = new DateTime(date('r', $time_now));
$now = $time_now;

$frames = array();
$delays = array();
 
$bgimage = '../backgrounds/ebayPM.png';
//$bgimage = 'ebayDeals.gif';
$r = 255;
$g = 255;
$b = 255;

//$image = imagecreatefromgif($bgimage);
$image = imagecreatefrompng($bgimage);
imagealphablending($image, true);
imagesavealpha($image, true);
$delay = 100; // milliseconds
$font = array(
			'size'=>34,
			'angle'=>0,
			'x-offset'=>30,
			'y-offset'=>40,
			'file'=>'../fonts/HelveticaNeue_Lt.ttf',
			'color'=>imagecolorallocate($image, $r, $g, $b),
);
 
for($i = 0; $i <= 60; $i++){
	//$interval = date_diff($future_date, $now);
	if($future_date < $now){
		// Open the first source image and add the text.
		$image = imagecreatefrompng($bgimage);;
		imagealphablending($image, true);
		imagesavealpha($image, true);
		$text = '00';
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] , $font['color'] , $font['file'], $text );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+100 , $font['y-offset'] , $font['color'] , $font['file'], $text );
		ob_start();
		imagecolortransparent($image);
		imagegif($image);
		$frames[]=ob_get_contents();
		$delays[]=$delay;
		$loops = 1;
		ob_end_clean();
		break;
	} else {
		// Open the first source image and add the text.
		$image = imagecreatefrompng($bgimage);
		imagealphablending($image, true);
		imagesavealpha($image, true);
		//$trans = imagecolorallocate($image, 53, 141, 43);
		//imagecolortransparent($image, $trans);
		$textArray = myDDarray($now, $future_date, false);
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] , $font['color'] , $font['file'], $textArray[1] );
		imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset']+100 , $font['y-offset'] , $font['color'] , $font['file'], $textArray[2] );
		ob_start();
		imagecolortransparent($image);
		imagegif($image);
		$frames[]=ob_get_contents();
		$delays[]=$delay;
		$loops = 0;
		ob_end_clean();
	}
	$now += 1;
}
//expire this image instantly
header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );
$gif = new AnimatedGif($frames,$delays,$loops, 53, 141, 43);
//$gif = new AnimatedGif($frames,$delays,$loops);
$gif->display();
?>