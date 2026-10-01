<?php

namespace App\Repository;

use App\Entity\SiteContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SiteContent> */
class SiteContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiteContent::class);
    }

    public function findByKey(string $key): ?SiteContent
    {
        return $this->findOneBy(['contentKey' => $key]);
    }

    public function getContent(string $key, string $default = ''): string
    {
        return $this->findByKey($key)?->getContent() ?? $default;
    }
}
