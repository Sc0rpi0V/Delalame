<?php

namespace App\Controller;

use App\Entity\GalleryImage;
use App\Repository\GalleryImageRepository;
use App\Repository\LegalPageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SitemapController extends AbstractController
{
    /**
     * Pages statiques du site, avec leur priorité SEO relative.
     * L'URL absolue est générée à la volée (fonctionne sur n'importe quel domaine/environnement).
     */
    private const STATIC_PAGES = [
        ['route' => 'app_home', 'priority' => '1.0', 'changefreq' => 'weekly'],
        ['route' => 'app_services', 'priority' => '0.9', 'changefreq' => 'monthly'],
        ['route' => 'app_gallery', 'priority' => '0.9', 'changefreq' => 'weekly'],
        ['route' => 'app_equipe', 'priority' => '0.6', 'changefreq' => 'monthly'],
        ['route' => 'app_contact', 'priority' => '0.8', 'changefreq' => 'yearly'],
    ];

    private const LEGAL_PAGES = [
        ['route' => 'app_mentions_legales', 'type' => 'mentions_legales'],
        ['route' => 'app_cgv', 'type' => 'cgv'],
        ['route' => 'app_cgu', 'type' => 'cgu'],
        ['route' => 'app_politique_confidentialite', 'type' => 'politique_confidentialite'],
    ];

    #[Route('/sitemap.xml', name: 'app_sitemap')]
    public function sitemap(LegalPageRepository $legalPageRepo, GalleryImageRepository $galleryRepo): Response
    {
        $legalPages = [];
        foreach (self::LEGAL_PAGES as $legal) {
            $page = $legalPageRepo->findByType($legal['type']);
            $legalPages[] = [
                'route' => $legal['route'],
                'priority' => '0.3',
                'changefreq' => 'yearly',
                'lastmod' => $page?->getUpdatedAt(),
            ];
        }

        /** @var GalleryImage|null $lastImage */
        $lastImage = $galleryRepo->findOneBy(['published' => true], ['updatedAt' => 'DESC']);

        $response = $this->render('sitemap.xml.twig', [
            'staticPages' => self::STATIC_PAGES,
            'legalPages' => $legalPages,
            'galleryLastUpdate' => $lastImage?->getUpdatedAt(),
        ]);
        $response->headers->set('Content-Type', 'application/xml; charset=UTF-8');

        return $response;
    }

    #[Route('/robots.txt', name: 'app_robots')]
    public function robots(): Response
    {
        $response = $this->render('robots.txt.twig');
        $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');

        return $response;
    }
}
