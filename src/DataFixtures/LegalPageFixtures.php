<?php

namespace App\DataFixtures;

use App\Entity\LegalPage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LegalPageFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (LegalPage::TYPES as $type => $label) {
            $existing = $manager->getRepository(LegalPage::class)->findOneBy(['type' => $type]);
            if ($existing) {
                continue;
            }

            $page = new LegalPage();
            $page->setType($type);
            $page->setContent('<p>Contenu de la page <strong>' . $label . '</strong> à rédiger dans le back-office.</p>');
            $manager->persist($page);
        }

        $manager->flush();
    }
}
