<?php

namespace App\Tests\Functional;

use App\Entity\GalleryImage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GalleryCategoryLabelTest extends WebTestCase
{
    private const TITLE = 'PHPUnit — porte de garage';

    public function testCategoryFilterShowsHumanLabels(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $image = (new GalleryImage())
            ->setTitle(self::TITLE)
            ->setCategory('portes-garage')
            ->setImageName('phpunit.jpg');
        $em->persist($image);
        $em->flush();

        try {
            $client->request('GET', '/realisations');

            self::assertResponseIsSuccessful();
            self::assertSelectorTextContains('a[href*="categorie=portes-garage"]', 'Portes de garage');
        } finally {
            $em->remove($em->getRepository(GalleryImage::class)->findOneBy(['title' => self::TITLE]));
            $em->flush();
        }
    }
}
