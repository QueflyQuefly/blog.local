<?php
session_start();
$font = __DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'georgia.ttf';
$img = imageCreateTrueColor(160, 25);
imageAntiAlias($img, true);

$grey = imageColorAllocate($img, 192, 192, 192);
$black = imageColorAllocate($img, 90, 90, 90);

imageFill($img, 0, 0, $grey);


$integer = (string) random_int(1000000, 9999999);
$_SESSION['integer'] = $integer;

$length = strlen($integer);

for ($j = 0; $j < $length; $j++){
    $angle = random_int(-45, 45);
    $size = random_int(10, 20);
    static $x = 5;
    imageTtfText($img, $size, $angle, $x, 18, $black, $font, $integer[$j]);
    $x +=20;
}

header("Content-type: image/jpeg");
imageJpeg($img, null, 100);