<?php

namespace App\Controller;

use App\Repository\LegalPageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalController extends AbstractController
{
    public function __construct(private LegalPageRepository $repo) {}

    #[Route('/mentions-legales', name: 'app_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->renderLegalPage('mentions_legales', 'Mentions légales');
    }

    #[Route('/conditions-generales-de-vente', name: 'app_cgv')]
    public function cgv(): Response
    {
        return $this->renderLegalPage('cgv', 'Conditions générales de vente');
    }

    #[Route('/conditions-generales-utilisation', name: 'app_cgu')]
    public function cgu(): Response
    {
        return $this->renderLegalPage('cgu', "Conditions générales d'utilisation");
    }

    #[Route('/politique-de-confidentialite', name: 'app_politique_confidentialite')]
    public function politiqueConfidentialite(): Response
    {
        return $this->renderLegalPage('politique_confidentialite', 'Politique de confidentialité');
    }

    private function renderLegalPage(string $type, string $defaultTitle): Response
    {
        $page = $this->repo->findByType($type);

        return $this->render('legal/show.html.twig', [
            'page' => $page,
            'title' => $page ? $page->getLabel() : $defaultTitle,
        ]);
    }
}
