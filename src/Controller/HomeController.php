<?php

namespace App\Controller;

use App\Repository\GalleryImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(GalleryImageRepository $galleryRepo): Response
    {
        $previewImages = $galleryRepo->findPublishedPage(null, 1, 6);

        return $this->render('home/index.html.twig', [
            'preview_images' => $previewImages,
        ]);
    }
}
