<?php

// parameters
$detail = $_GET['detail'];
$min = $_GET['min'];
$max = $_GET['max'];
$nr = $_GET['nr'];

$file = 'IMG_0506'; // filename
$imgSrc = 'input/'.$file.'.jpg';

$outputFile = 'output/'.$file.'_'.$nr.'.png';

if (file_exists($outputFile)){

	$img = imagecreatefrompng($outputFile);

	header("Content-Type: image/png;");
	imagepng($img);

	return;
}

// source: https://en.wikipedia.org/wiki/Color_difference
function calcColorDistance($a, $b){

	$ar = $a[0];
	$ag = $a[1];
	$ab = $a[2];

	$br = $b[0];
	$bg = $b[1];
	$bb = $b[2];

	$r_ = ($ar+$br)/2;

	$dr = $ar - $br;
	$dg = $ag - $bg;
	$db = $ab - $bb;

	return sqrt(2*$dr*$dr + 4*$dg*$dg + 3*$db*$db + ($r_*($dr*$dr-$db*$db))/256 );
}

// calculates the average color of a rect
function calcAverage($img, $x, $y, $w, $h){

	$r = 0;
	$g = 0;
	$b = 0;

	for ($i = $x; $i < $x + $w; $i++){
		for ($j = $y; $j < $y + $h; $j++){

			$color = imagecolorat($img, $i, $j);
			$rgbColor = imagecolorsforindex($img, $color);

			$r = $r + $rgbColor['red'];
			$g = $g + $rgbColor['green'];
			$b = $b + $rgbColor['blue'];
		}
	}

	$r = $r / ($w*$h);
	$g = $g / ($w*$h);
	$b = $b / ($w*$h);

	return array($r, $g, $b);
}

// calculates the average deviation of a rect
function calcDeltaToAverage($img, $rgbAvgColor, $x, $y, $w, $h){

	$distance = 0;

	for ($i = $x; $i < $x + $w; $i++){
		for ($j = $y; $j < $y + $h; $j++){

			$color = imagecolorat($img, $i, $j);
			$rgbColor = imagecolorsforindex($img, $color);

			$d = calcColorDistance($rgbAvgColor, array($rgbColor['red'],$rgbColor['green'],$rgbColor['blue']));

			$distance += $d;
		}
	}

	return $distance / ($w*$h);
}

// creates the quadtrees recursively
function doRectangle($img, $dstImg, $x, $y, $w, $h, $minSize, $maxSize, $randomRange, $levelOfDetail){

	$rgbAvgColor = calcAverage($img, $x, $y, $w, $h);
	$d = calcDeltaToAverage($img, $rgbAvgColor, $x, $y, $w, $h);

	// if the distance to the average color is below the level of detail, paint the rect
	if ($w < $maxSize && $d < $levelOfDetail || $w <= $minSize || $h <= $minSize){
		$avgColor = imagecolorallocate($img, $rgbAvgColor[0], $rgbAvgColor[1], $rgbAvgColor[2]);
		imagefilledrectangle(
			$dstImg,
			$x+$randomRange/2-rand(0,$randomRange), 
			$y+$randomRange/2-rand(0,$randomRange), 
			$x+$w-1, 
			$y+$h-1, 
			$avgColor
		);

	// split it otherwise into four rects
	}else{
		doRectangle($img, $dstImg, $x, $y, $w/2, $h/2, $minSize, $maxSize, $randomRange, $levelOfDetail);
		doRectangle($img, $dstImg, $x+$w/2, $y, $w/2, $h/2, $minSize, $maxSize, $randomRange, $levelOfDetail);
		doRectangle($img, $dstImg, $x, $y+$w/2, $w/2, $h/2, $minSize, $maxSize, $randomRange, $levelOfDetail);
		doRectangle($img, $dstImg, $x+$w/2, $y+$h/2, $w/2, $h/2, $minSize, $maxSize, $randomRange, $levelOfDetail);
	}

}


$srcImg = imagecreatefromjpeg($imgSrc); // load source, should be sized to 512x512px
$dstImg = imagecreatetruecolor(512, 512); // create empty image
// imagecopyresized ( $resizedImg , $srcImg , 0 , 0 , 60 , 8 , 512 , 512 , 472 , 472 ); // could be used if the source is not already sized

// only needed if random shift is used
$randomRange = ($min-1);
if ($randomRange > 4){
	$randomRange = 4;
}

doRectangle($srcImg, $dstImg, 0, 0, 512, 512, $min, $max, 0, $detail);
// doRectangle($srcImg, $dstImg, 0, 0, 512, 512, $min, $max, $randomRange, $detail); // same effect with a random shift


// output and save the image
header("Content-Type: image/png;");
imagepng($dstImg);
imagepng($dstImg, $outputFile);

imagedestroy($srcImg);
imagedestroy($resizedImg);
imagedestroy($dstImg);

?>