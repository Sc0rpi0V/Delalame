<?php

namespace App\Controller;

use App\Repository\SiteContentRepository;
use App\Repository\TeamMemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EquipeController extends AbstractController
{
    #[Route('/notre-equipe', name: 'app_equipe')]
    public function index(TeamMemberRepository $memberRepo, SiteContentRepository $contentRepo): Response
    {
        return $this->render('equipe/index.html.twig', [
            'members'    => $memberRepo->findPublished(),
            'team_intro' => $contentRepo->getContent('team_intro'),
        ]);
    }
}
