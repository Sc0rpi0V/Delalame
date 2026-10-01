<?php

namespace App\DataFixtures;

use App\Entity\SiteContent;
use App\Entity\TeamMember;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TeamFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Intro block (idempotent)
        $intro = $manager->getRepository(SiteContent::class)->findOneBy(['contentKey' => 'team_intro']);
        if (!$intro) {
            $intro = new SiteContent();
            $intro->setContentKey('team_intro');
            $intro->setContent('<p>Spécialistes de la menuiserie extérieure depuis plus de 20 ans, nous intervenons chez les particuliers et les professionnels pour la <strong>fourniture et la pose de fenêtres, portes, portes de garage et volets</strong>.</p><p>Notre équipe de conseillers techniques et de poseurs qualifiés vous accompagne de la première visite jusqu\'à la réception du chantier — avec un seul interlocuteur tout au long du projet.</p>');
            $manager->persist($intro);
        }

        // Membres (idempotent par nom)
        $members = [
            ['Marc Lefebvre',  'Gérant & conseiller technique', 20, 'Fenêtres & baies vitrées', 1],
            ['Julie Moreau',   'Chargée de projet',              8, 'Suivi chantier & SAV',     2],
            ['Thomas Renard',  "Poseur chef d'équipe",          12, 'Portes de garage & volets', 3],
        ];

        foreach ($members as [$name, $role, $years, $specialty, $pos]) {
            $exists = $manager->getRepository(TeamMember::class)->findOneBy(['name' => $name]);
            if ($exists) { continue; }

            $member = new TeamMember();
            $member->setName($name)
                ->setRole($role)
                ->setYearsExperience($years)
                ->setSpecialty($specialty)
                ->setPosition($pos);
            $manager->persist($member);
        }

        $manager->flush();
    }
}
