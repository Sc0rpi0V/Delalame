<?php

namespace App\Controller\Admin;

use App\Entity\GalleryImage;
use App\Entity\LegalPage;
use App\Entity\Service;
use App\Entity\SiteContent;
use App\Entity\TeamMember;
use App\Repository\LegalPageRepository;
use App\Repository\SiteContentRepository;
use App\Service\GeocodingService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private EntityManagerInterface $em,
        private LegalPageRepository $legalPageRepo,
        private SiteContentRepository $siteContentRepo,
    ) {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $stats = [
            'services_total' => $this->em->getRepository(Service::class)->count(['published' => true]),
            'gallery_total'  => $this->em->getRepository(GalleryImage::class)->count([]),
        ];

        return $this->render('admin/dashboard.html.twig', ['stats' => $stats]);
    }

    #[Route('/admin/legal-pages', name: 'admin_legal_pages')]
    public function legalPages(Request $request): Response
    {
        $pages = [];
        foreach (array_keys(LegalPage::TYPES) as $type) {
            $page = $this->legalPageRepo->findByType($type);
            if (!$page) {
                $page = new LegalPage();
                $page->setType($type);
                $page->setContent('');
                $this->em->persist($page);
            }
            $pages[$type] = $page;
        }
        $this->em->flush();

        $forms = [];
        foreach ($pages as $type => $page) {
            $forms[$type] = $this->container->get('form.factory')
                ->createNamedBuilder('legal_' . $type, FormType::class, $page, [
                    'attr' => ['class' => 'legal-form'],
                ])
                ->add('content', TextareaType::class, [
                    'label' => false,
                    'attr'  => ['class' => 'legal-editor', 'rows' => 15],
                ])
                ->add('save', SubmitType::class, [
                    'label' => 'Enregistrer',
                    'attr'  => ['class' => 'btn btn-primary mt-3'],
                ])
                ->getForm();

            $forms[$type]->handleRequest($request);

            if ($forms[$type]->isSubmitted() && $forms[$type]->isValid()) {
                $this->em->flush();
                $this->addFlash('success', LegalPage::TYPES[$type] . ' enregistrée.');
                return $this->redirectToRoute('admin_legal_pages', ['tab' => $type]);
            }
        }

        return $this->render('admin/legal_pages.html.twig', [
            'forms'      => array_map(fn($f) => $f->createView(), $forms),
            'pages'      => $pages,
            'active_tab' => $request->query->get('tab', array_key_first(LegalPage::TYPES)),
        ]);
    }

    #[Route('/admin/team-page', name: 'admin_team_page')]
    public function teamPage(Request $request): Response
    {
        $block = $this->siteContentRepo->findByKey('team_intro');
        if (!$block) {
            $block = new SiteContent();
            $block->setContentKey('team_intro');
            $block->setContent('<p>Spécialistes de la menuiserie extérieure depuis plus de 20 ans…</p>');
            $this->em->persist($block);
            $this->em->flush();
        }

        $form = $this->container->get('form.factory')
            ->createNamedBuilder('team_intro', FormType::class, $block)
            ->add('content', TextareaType::class, [
                'label' => false,
                'attr'  => ['class' => 'team-intro-editor', 'rows' => 12],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr'  => ['class' => 'btn btn-primary mt-3'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Présentation de l\'équipe mise à jour.');
            return $this->redirectToRoute('admin_team_page');
        }

        return $this->render('admin/team_page.html.twig', [
            'form'  => $form->createView(),
            'block' => $block,
        ]);
    }

    #[Route('/admin/site-settings', name: 'admin_site_settings')]
    public function siteSettings(Request $request, GeocodingService $geocodingService): Response
    {
        $defaults = [
            'contact_address'        => '12 rue des Artisans, 75001 Paris',
            'contact_phone'          => '01 23 45 67 89',
            'contact_phone_raw'      => '+33123456789',
            'contact_email'          => 'contact@delalame.fr',
            'hours_weekdays_label'   => 'Lundi – Vendredi',
            'hours_weekdays_value'   => '8 h – 18 h',
            'hours_saturday_label'   => 'Samedi',
            'hours_saturday_value'   => '9 h – 12 h',
            'hours_sunday_label'     => 'Dimanche',
            'hours_sunday_value'     => 'Fermé',
            'contact_lat'            => '',
            'contact_lng'            => '',
        ];

        $entries = [];
        foreach ($defaults as $key => $default) {
            $entry = $this->siteContentRepo->findByKey($key);
            if (!$entry) {
                $entry = new SiteContent();
                $entry->setContentKey($key);
                $entry->setContent($default);
                $this->em->persist($entry);
            }
            $entries[$key] = $entry;
        }
        $this->em->flush();

        $data = array_map(fn($e) => $e->getContent(), $entries);

        $form = $this->container->get('form.factory')
            ->createNamedBuilder('site_settings', FormType::class, $data)
            ->add('contact_address',      TextType::class, ['label' => 'Adresse'])
            ->add('contact_phone',        TextType::class, ['label' => 'Téléphone (affiché)'])
            ->add('contact_phone_raw',    TextType::class, ['label' => 'Téléphone (lien tel:)'])
            ->add('contact_email',        TextType::class, ['label' => 'Email'])
            ->add('hours_weekdays_label', TextType::class, ['label' => 'Jours semaine (libellé)'])
            ->add('hours_weekdays_value', TextType::class, ['label' => 'Jours semaine (horaires)'])
            ->add('hours_saturday_label', TextType::class, ['label' => 'Samedi (libellé)'])
            ->add('hours_saturday_value', TextType::class, ['label' => 'Samedi (horaires)'])
            ->add('hours_sunday_label',   TextType::class, ['label' => 'Dimanche (libellé)'])
            ->add('hours_sunday_value',   TextType::class, ['label' => 'Dimanche (horaires)'])
            ->add('save', SubmitType::class, ['label' => 'Enregistrer', 'attr' => ['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $previousAddress = $entries['contact_address']->getContent();

            foreach ($form->getData() as $key => $value) {
                if (isset($entries[$key])) {
                    $entries[$key]->setContent($value);
                }
            }

            $newAddress = $entries['contact_address']->getContent();
            if ($newAddress !== '' && $newAddress !== $previousAddress) {
                $coordinates = $geocodingService->geocode($newAddress);
                if ($coordinates !== null) {
                    $entries['contact_lat']->setContent((string) $coordinates['lat']);
                    $entries['contact_lng']->setContent((string) $coordinates['lng']);
                    $this->addFlash('success', 'Coordonnées et horaires mis à jour — la carte a été repositionnée sur la nouvelle adresse.');
                } else {
                    $this->addFlash('warning', 'Coordonnées et horaires mis à jour, mais l\'adresse n\'a pas pu être localisée sur la carte. Vérifiez son orthographe.');
                }
            } else {
                $this->addFlash('success', 'Coordonnées et horaires mis à jour.');
            }

            $this->em->flush();
            return $this->redirectToRoute('admin_site_settings');
        }

        return $this->render('admin/site_settings.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/home-content', name: 'admin_home_content')]
    public function homeContent(Request $request): Response
    {
        $defaults = [
            'home_hero_desc'        => 'Fourniture et pose de menuiseries pour particuliers et professionnels. PVC, aluminium, bois, mixte — nous trouvons la solution adaptée à chaque projet et chaque budget.',
            'home_services_title'   => 'Nos domaines d\'intervention',
            'home_services_subtitle'=> 'Installation, remplacement et rénovation — tous matériaux',
            'home_service_1_icon'   => '🪟',
            'home_service_1_name'   => 'Fenêtres & baies',
            'home_service_1_desc'   => 'PVC, aluminium, bois ou mixte. Isolation renforcée, double ou triple vitrage.',
            'home_service_2_icon'   => '🚪',
            'home_service_2_name'   => 'Portes',
            'home_service_2_desc'   => 'Portes d\'entrée blindées, portes-fenêtres, portes intérieures sur mesure.',
            'home_service_3_icon'   => '🏠',
            'home_service_3_name'   => 'Portes de garage',
            'home_service_3_desc'   => 'Sectionnelles, basculantes, coulissantes — motorisation incluse ou en option.',
            'home_service_4_icon'   => '🪟',
            'home_service_4_name'   => 'Volets & stores',
            'home_service_4_desc'   => 'Volets battants ou roulants, stores extérieurs, pergolas bioclimatiques.',
            'home_atout_1_icon'     => '✅',
            'home_atout_1_title'    => 'Pose professionnelle',
            'home_atout_1_desc'     => 'Nos installateurs qualifiés interviennent dans les règles de l\'art, avec garantie décennale.',
            'home_atout_2_icon'     => '💰',
            'home_atout_2_title'    => 'Devis transparent',
            'home_atout_2_desc'     => 'Chiffrage détaillé, sans surprise. Nous vous accompagnons dans vos demandes de subventions (MaPrimeRénov\').',
            'home_atout_3_icon'     => '📞',
            'home_atout_3_title'    => 'SAV réactif',
            'home_atout_3_desc'     => 'Un problème après la pose ? Notre équipe intervient rapidement pour honorer sa garantie.',
            'home_stat_1_value'     => '+20',
            'home_stat_1_label'     => "ans d'expérience",
            'home_stat_2_value'     => '+800',
            'home_stat_2_label'     => 'chantiers réalisés',
            'home_stat_3_value'     => '10 ans',
            'home_stat_3_label'     => 'garantie décennale',
            'home_stat_4_value'     => '4.8/5',
            'home_stat_4_label'     => 'satisfaction client',
        ];

        $entries = [];
        foreach ($defaults as $key => $default) {
            $entry = $this->siteContentRepo->findByKey($key);
            if (!$entry) {
                $entry = new SiteContent();
                $entry->setContentKey($key);
                $entry->setContent($default);
                $this->em->persist($entry);
            }
            $entries[$key] = $entry;
        }
        $this->em->flush();

        $data = array_map(fn($e) => $e->getContent(), $entries);

        $form = $this->container->get('form.factory')
            ->createNamedBuilder('home_content', FormType::class, $data)
            ->add('home_hero_desc',         TextareaType::class, ['label' => 'Texte accroche hero', 'attr' => ['rows' => 3]])
            ->add('home_services_title',    TextType::class,     ['label' => 'Section services — titre'])
            ->add('home_services_subtitle', TextType::class,     ['label' => 'Section services — sous-titre'])
            ->add('home_service_1_icon',    TextType::class,     ['label' => 'Service 1 — icône (emoji)'])
            ->add('home_service_1_name',    TextType::class,     ['label' => 'Service 1 — nom'])
            ->add('home_service_1_desc',    TextareaType::class, ['label' => 'Service 1 — description', 'attr' => ['rows' => 2]])
            ->add('home_service_2_icon',    TextType::class,     ['label' => 'Service 2 — icône (emoji)'])
            ->add('home_service_2_name',    TextType::class,     ['label' => 'Service 2 — nom'])
            ->add('home_service_2_desc',    TextareaType::class, ['label' => 'Service 2 — description', 'attr' => ['rows' => 2]])
            ->add('home_service_3_icon',    TextType::class,     ['label' => 'Service 3 — icône (emoji)'])
            ->add('home_service_3_name',    TextType::class,     ['label' => 'Service 3 — nom'])
            ->add('home_service_3_desc',    TextareaType::class, ['label' => 'Service 3 — description', 'attr' => ['rows' => 2]])
            ->add('home_service_4_icon',    TextType::class,     ['label' => 'Service 4 — icône (emoji)'])
            ->add('home_service_4_name',    TextType::class,     ['label' => 'Service 4 — nom'])
            ->add('home_service_4_desc',    TextareaType::class, ['label' => 'Service 4 — description', 'attr' => ['rows' => 2]])
            ->add('home_atout_1_icon',      TextType::class,     ['label' => 'Atout 1 — icône (emoji)'])
            ->add('home_atout_1_title',     TextType::class,     ['label' => 'Atout 1 — titre'])
            ->add('home_atout_1_desc',      TextareaType::class, ['label' => 'Atout 1 — description', 'attr' => ['rows' => 2]])
            ->add('home_atout_2_icon',      TextType::class,     ['label' => 'Atout 2 — icône (emoji)'])
            ->add('home_atout_2_title',     TextType::class,     ['label' => 'Atout 2 — titre'])
            ->add('home_atout_2_desc',      TextareaType::class, ['label' => 'Atout 2 — description', 'attr' => ['rows' => 2]])
            ->add('home_atout_3_icon',      TextType::class,     ['label' => 'Atout 3 — icône (emoji)'])
            ->add('home_atout_3_title',     TextType::class,     ['label' => 'Atout 3 — titre'])
            ->add('home_atout_3_desc',      TextareaType::class, ['label' => 'Atout 3 — description', 'attr' => ['rows' => 2]])
            ->add('home_stat_1_value',      TextType::class,     ['label' => 'Statistique 1 — valeur'])
            ->add('home_stat_1_label',      TextType::class,     ['label' => 'Statistique 1 — libellé'])
            ->add('home_stat_2_value',      TextType::class,     ['label' => 'Statistique 2 — valeur'])
            ->add('home_stat_2_label',      TextType::class,     ['label' => 'Statistique 2 — libellé'])
            ->add('home_stat_3_value',      TextType::class,     ['label' => 'Statistique 3 — valeur'])
            ->add('home_stat_3_label',      TextType::class,     ['label' => 'Statistique 3 — libellé'])
            ->add('home_stat_4_value',      TextType::class,     ['label' => 'Statistique 4 — valeur'])
            ->add('home_stat_4_label',      TextType::class,     ['label' => 'Statistique 4 — libellé'])
            ->add('save', SubmitType::class, ['label' => 'Enregistrer', 'attr' => ['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($form->getData() as $key => $value) {
                if (isset($entries[$key])) {
                    $entries[$key]->setContent($value);
                }
            }
            $this->em->flush();
            $this->addFlash('success', 'Contenu de la page d\'accueil mis à jour.');
            return $this->redirectToRoute('admin_home_content');
        }

        return $this->render('admin/home_content.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function configureAssets(): Assets
    {
        $inlineScript = <<<'JS'
<script>
(function(){
  var ea=localStorage.getItem('ea/colorScheme');
  if(ea==='warm'||ea==='soft'){
    localStorage.setItem('artisan/adminScheme',ea);
    localStorage.removeItem('ea/colorScheme');
  }
  var s=localStorage.getItem('artisan/adminScheme');
  if(s==='warm'||s==='soft'){document.documentElement.setAttribute('data-artisan-scheme',s);}
})();
</script>
JS;

        return Assets::new()
            ->addHtmlContentToHead($inlineScript)
            ->addCssFile('/admin/custom-schemes.css')
            ->addJsFile('/admin/custom-schemes.js');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Delalame')
            ->setFaviconPath('favicon.ico')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::section('Galerie');
        yield MenuItem::linkTo(GalleryImageCrudController::class, 'Images', 'fa fa-images');
        yield MenuItem::section('Services');
        yield MenuItem::linkTo(ServiceCrudController::class, 'Services & mails de devis', 'fa fa-tools');
        yield MenuItem::section('Notre équipe');
        yield MenuItem::linkToRoute('Présentation', 'fa fa-align-left', 'admin_team_page');
        yield MenuItem::linkTo(TeamMemberCrudController::class, 'Membres', 'fa fa-users');
        yield MenuItem::section('Contenu légal');
        yield MenuItem::linkToRoute('Pages légales', 'fa fa-file-contract', 'admin_legal_pages');
        yield MenuItem::section('Paramètres');
        yield MenuItem::linkToRoute('Page d\'accueil', 'fa fa-home', 'admin_home_content');
        yield MenuItem::linkToRoute('Coordonnées & Horaires', 'fa fa-map-marker-alt', 'admin_site_settings');
        yield MenuItem::section();
        yield MenuItem::linkToRoute('Voir le site', 'fa fa-eye', 'app_home');
        yield MenuItem::linkToRoute('Déconnexion', 'fa fa-sign-out-alt', 'admin_logout');
    }
}
