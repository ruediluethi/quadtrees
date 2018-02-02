<?php

// start and end parameters
$detail_start = 170;
$detail_end = 10;

$maxSize_start = 512;
$maxSize_end = 256;

$minSize_start = 16;
$minSize_end = 1;

$count = 5; // amount of frames

$delta_detail = $detail_start-$detail_end;
$delta_max = $maxSize_start-$maxSize_end;
$delta_min = $minSize_start-$minSize_end;
for ($i = 0; $i < $count; $i++){

	$x = $i/($count-1);
	$y = ($x-1)*($x-1); // quadratic interpolation
	//$y = (1-$x); // linear interpolation

	$detail = $y*$delta_detail + $detail_end;
	$max = $y*$delta_max + $maxSize_end;
	$min = $y*$delta_min + $minSize_end;

	echo '<img src="quadtree.php?detail='.round($detail).'&min='.round($min).'&max='.round($max).'&nr='.$i.'" width="200">';
}

?>