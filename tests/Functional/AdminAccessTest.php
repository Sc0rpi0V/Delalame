<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminAccessTest extends WebTestCase
{
    public function testAdminRedirectsToLoginWhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        self::assertResponseRedirects('/admin/login');
    }

    public function testAdminLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input[name="_username"]');
        self::assertSelectorExists('input[name="_password"]');
    }

    public function testRepeatedFailedLoginsAreThrottled(): void
    {
        $client = static::createClient();

        for ($i = 0; $i < 6; ++$i) {
            $crawler = $client->request('GET', '/admin/login');
            $client->submitForm('Se connecter', [
                '_username' => 'throttle-test@example.com',
                '_password' => 'wrong-password-'.$i,
            ]);
            $client->followRedirect();
        }

        self::assertSelectorTextContains('.error', 'tentatives');
    }
}
