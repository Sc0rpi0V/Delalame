<?php

namespace App\DataFixtures;

use App\Entity\Service;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ServiceFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $services = [
            [
                'FEN-01', '🪟', 'Fenêtres & baies vitrées',
                "Améliorer le confort thermique et acoustique de votre logement commence par des fenêtres performantes. Nous intervenons en neuf comme en rénovation.",
                [
                    'PVC, aluminium, bois ou mixte bois-alu',
                    'Double ou triple vitrage (Uw ≤ 1,0)',
                    'Fenêtres à battants, oscillo-battants, coulissantes, galandage',
                    'Baies vitrées grand format et vérandas',
                    'Pose sur dormant existant (rénovation rapide sans maçonnerie)',
                ],
                "Bonjour,\n\nJe souhaiterais recevoir un devis pour la fourniture et la pose de fenêtres / baies vitrées.\n\nNombre de menuiseries :\nMatériau souhaité (PVC, alu, bois, mixte) :\nTravaux en neuf ou en rénovation :",
            ],
            [
                'POR-01', '🚪', 'Portes',
                "De la porte d'entrée blindée à la porte-fenêtre, nous vous proposons des solutions alliant sécurité, esthétique et isolation.",
                [
                    "Portes d'entrée blindées (certification A2P)",
                    'Portes-fenêtres et portes coulissantes',
                    'Portes intérieures & blocs-portes',
                    'Portes de cave, de service, de buanderie',
                    'Serrures multipoints et connectées',
                ],
                "Bonjour,\n\nJe souhaiterais recevoir un devis pour la fourniture et la pose d'une ou plusieurs portes.\n\nType de porte (entrée, porte-fenêtre, intérieure…) :\nNombre de portes :\nMatériau souhaité :",
            ],
            [
                'GAR-01', '🏠', 'Portes de garage',
                "Pratique au quotidien et indispensable pour la sécurité de votre véhicule. Nous installons tous types de portes de garage, motorisées ou non.",
                [
                    'Sectionnelles plafond ou débord de toit',
                    'Basculantes débordantes ou non débordantes',
                    'Portes coulissantes latérales',
                    'Motorisation radio avec smartphone (sur demande)',
                    "Isolation thermique jusqu'à Rw 40 dB",
                ],
                "Bonjour,\n\nJe souhaiterais recevoir un devis pour la fourniture et la pose d'une porte de garage.\n\nType souhaité (sectionnelle, basculante, coulissante) :\nMotorisation (oui / non) :\nDimensions de l'ouverture (L x H) :",
            ],
            [
                'VOL-01', '🌿', 'Volets, stores & pergolas',
                "Protection solaire, intimité et isolation nocturne : les solutions de fermeture extérieure complètent parfaitement vos menuiseries.",
                [
                    'Volets battants aluminium ou PVC',
                    'Volets roulants (caisson rénovation ou neuf)',
                    'Stores bannes et stores extérieurs à projection',
                    'Pergolas bioclimatiques à lames orientables',
                    'Motorisation et domotique (Somfy, Nice…)',
                ],
                "Bonjour,\n\nJe souhaiterais recevoir un devis pour des volets / stores / une pergola.\n\nProduit souhaité :\nNombre et dimensions approximatives :\nMotorisation (oui / non) :",
            ],
        ];

        // Idempotent par référence
        foreach ($services as $position => [$reference, $icon, $name, $intro, $points, $mailBody]) {
            if ($manager->getRepository(Service::class)->findOneBy(['reference' => $reference])) {
                continue;
            }

            $service = new Service();
            $service->setReference($reference)
                ->setIcon($icon)
                ->setName($name)
                ->setIntro($intro)
                ->setPoints($points)
                ->setMailBody($mailBody)
                ->setPosition($position + 1);
            $manager->persist($service);
        }

        $manager->flush();
    }
}
