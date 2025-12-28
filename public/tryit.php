<?php

require_once '../vendor/autoload.php';
use Jcupitt\Vips\Image;

$image = Image::newFromFile(__DIR__ . '/../../test.jpg');
dump($image);
die();
