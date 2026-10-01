<?php

namespace App\Controller;

use App\Entity\GalleryImage;
use App\Repository\GalleryImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GalleryController extends AbstractController
{
    private const PER_PAGE = 10;

    #[Route('/realisations', name: 'app_gallery')]
    public function index(Request $request, GalleryImageRepository $repo): Response
    {
        $category   = $request->query->get('categorie') ?: null;
        $page       = max(1, (int) $request->query->get('page', 1));
        $total      = $repo->countPublished($category);
        $images     = $repo->findPublishedPage($category, $page, self::PER_PAGE);
        $categories = $repo->findDistinctCategories();

        return $this->render('gallery/index.html.twig', [
            'images'          => $images,
            'categories'      => $categories,
            'category_labels' => array_flip(GalleryImage::CATEGORIES),
            'active_category' => $category,
            'page'            => $page,
            'total'           => $total,
            'per_page'        => self::PER_PAGE,
            'has_prev'        => $page > 1,
            'has_next'        => ($page * self::PER_PAGE) < $total,
        ]);
    }
}
