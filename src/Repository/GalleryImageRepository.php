<?php

namespace App\Repository;

use App\Entity\GalleryImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GalleryImage>
 */
class GalleryImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GalleryImage::class);
    }

    private function basePublishedQuery(?string $category): \Doctrine\ORM\QueryBuilder
    {
        $qb = $this->createQueryBuilder('g')
            ->where('g.published = true')
            ->orderBy('g.featured', 'DESC')
            ->addOrderBy('g.createdAt', 'DESC');

        if ($category !== null) {
            $qb->andWhere('g.category = :category')->setParameter('category', $category);
        }

        return $qb;
    }

    /** @return GalleryImage[] */
    public function findPublishedPage(?string $category, int $page, int $perPage = 10): array
    {
        return $this->basePublishedQuery($category)
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function countPublished(?string $category): int
    {
        $qb = $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->where('g.published = true');

        if ($category !== null) {
            $qb->andWhere('g.category = :category')->setParameter('category', $category);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /** @return string[] */
    public function findDistinctCategories(): array
    {
        $result = $this->createQueryBuilder('g')
            ->select('DISTINCT g.category')
            ->where('g.published = true')
            ->orderBy('g.category', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'category');
    }
}
