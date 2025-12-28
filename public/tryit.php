<?php
use Jcupitt\Vips\Image;
final class HomeController extends AbstractController {
    #[Route('/test')]
    public function test(): Response
    {
        $image = Image::newFromFile(__DIR__ . '/images.jpg'); // Unable to open library 'libvips.so.42'. Make sure that you've installed libvips and that 'libvips.so.42' is on your system's library search path.
        dump($image);
        die();
    }
}
