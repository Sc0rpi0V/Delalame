<?php

namespace App\Tests\Functional;

use App\Entity\Service;
use App\Entity\SiteContent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServicesMailtoTest extends WebTestCase
{
    private const REFERENCE = 'PHPUNIT-01';

    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->cleanup();

        $service = (new Service())
            ->setReference(self::REFERENCE)
            ->setName('Service de test')
            ->setIcon('🧪')
            ->setIntro('Intro')
            ->setPoints(['Point A'])
            ->setMailBody("Bonjour,\nMessage pré-rédigé.");
        $this->em->persist($service);
        $this->em->flush();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
        parent::tearDown();
    }

    private function cleanup(): void
    {
        foreach ($this->em->getRepository(Service::class)->findBy(['reference' => self::REFERENCE]) as $service) {
            $this->em->remove($service);
        }
        foreach ($this->em->getRepository(SiteContent::class)->findBy(['contentKey' => 'contact_email']) as $entry) {
            $this->em->remove($entry);
        }
        $this->em->flush();
    }

    public function testServiceButtonOpensPrefilledMail(): void
    {
        $email = (new SiteContent())->setContentKey('contact_email')->setContent('atelier@example.com');
        $this->em->persist($email);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/services');

        self::assertResponseIsSuccessful();
        $href = $crawler->filter('a[href^="mailto:"]')->attr('href');
        $url = parse_url($href);
        parse_str($url['query'], $query);

        self::assertSame('atelier@example.com', $url['path']);
        self::assertSame('Demande de devis — PHPUNIT-01 · Service de test', $query['subject']);
        self::assertStringStartsWith("Bonjour,\r\nMessage pré-rédigé.\r\n", $query['body']);
        self::assertStringContainsString('Informations complémentaires', $query['body']);
    }

    public function testServiceButtonFallsBackToContactPageWithoutSiteEmail(): void
    {
        $crawler = $this->client->request('GET', '/services');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('a[href^="mailto:"]'));
        self::assertSelectorExists('a.btn-primary[href="/contact"]');
    }

    public function testRemovedFormPagesAreGone(): void
    {
        $this->client->request('GET', '/devis');
        self::assertResponseStatusCodeSame(404);

        $this->client->request('GET', '/contact');
        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('form');
    }
}
