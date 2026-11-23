<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findLatest(int $maxResults)
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.date_publication', 'DESC')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult();
    }
    public function searchAndSort(?string $search, string $sort, string $direction): array
{
    $qb = $this->createQueryBuilder('p')
               ->leftJoin('p.packvoyage', 'pack')
               ->addSelect('pack');

    if ($search) {
        $qb->andWhere('p.titre LIKE :search OR p.description LIKE :search OR pack.nomPack LIKE :search')
           ->setParameter('search', '%' . $search . '%');
    }

    $sortableFields = ['titre', 'date_publication', 'date_expiration', 'statut'];

    if (in_array($sort, $sortableFields) && in_array($direction, ['ASC', 'DESC'])) {
        $qb->orderBy('p.' . $sort, $direction);
    }

    return $qb->getQuery()->getResult();
}

}
