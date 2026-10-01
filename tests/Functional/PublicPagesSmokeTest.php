<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PublicPagesSmokeTest extends WebTestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function publicUrlProvider(): iterable
    {
        yield 'home' => ['/'];
        yield 'services' => ['/services'];
        yield 'gallery' => ['/realisations'];
        yield 'contact' => ['/contact'];
        yield 'team' => ['/notre-equipe'];
        yield 'mentions legales' => ['/mentions-legales'];
        yield 'cgv' => ['/conditions-generales-de-vente'];
        yield 'cgu' => ['/conditions-generales-utilisation'];
        yield 'privacy policy' => ['/politique-de-confidentialite'];
        yield 'admin login' => ['/admin/login'];
        yield 'sitemap' => ['/sitemap.xml'];
        yield 'robots' => ['/robots.txt'];
    }

    /**
     * @dataProvider publicUrlProvider
     */
    public function testPageLoadsSuccessfully(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        self::assertResponseIsSuccessful();
    }

    public function testSitemapListsPublicPages(): void
    {
        $client = static::createClient();
        $client->request('GET', '/sitemap.xml');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('application/xml', $client->getResponse()->headers->get('Content-Type'));
        $content = $client->getResponse()->getContent();
        self::assertStringContainsString('<loc>http://localhost/</loc>', $content);
        self::assertStringContainsString('<loc>http://localhost/contact</loc>', $content);
    }

    public function testRobotsTxtReferencesSitemapAndDisallowsAdmin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/robots.txt');

        self::assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        self::assertStringContainsString('Disallow: /admin', $content);
        self::assertStringContainsString('Sitemap: http://localhost/sitemap.xml', $content);
    }

    public function testHomePageHasSeoMetaTags(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('link[rel="canonical"]');
        self::assertSelectorExists('meta[property="og:title"]');
        self::assertSelectorExists('meta[name="twitter:card"]');
        self::assertSelectorExists('script[type="application/ld+json"]');
    }
}
