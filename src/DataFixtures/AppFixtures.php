<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    /** Identifiants du compte admin de dev : ADMIN_EMAIL / ADMIN_PASSWORD dans .env.dev.local (jamais versionné). */
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        #[Autowire('%env(default::ADMIN_EMAIL)%')] private ?string $adminEmail = null,
        #[Autowire('%env(default::ADMIN_PASSWORD)%')] private ?string $adminPassword = null,
    ) {}

    public function load(ObjectManager $manager): void
    {
        if (!$this->adminEmail || !$this->adminPassword) {
            return;
        }

        $existing = $manager->getRepository(User::class)->findOneBy(['email' => $this->adminEmail]);
        if (!$existing) {
            $user = new User();
            $user->setEmail($this->adminEmail);
            $user->setRoles(['ROLE_ADMIN']);
            $user->setPassword($this->hasher->hashPassword($user, $this->adminPassword));
            $manager->persist($user);
        }

        $manager->flush();
    }
}
