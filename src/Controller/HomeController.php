<?php

namespace App\Controller;

use Jcupitt\Vips\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $image = Image::newFromFile(__DIR__ . '/../../test.jpg');
        dump($image);
        die();
    }
}
