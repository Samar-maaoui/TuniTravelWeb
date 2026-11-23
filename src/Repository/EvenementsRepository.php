<?php

namespace App\Repository;

use App\Entity\Evenements;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class EvenementsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenements::class);
    }

    public function findByFilters(?string $search, ?string $type, ?string $lieu): array
    {
        $queryBuilder = $this->createQueryBuilder('e');

        if ($search) {
            $queryBuilder->andWhere('e.nom LIKE :search')
                         ->setParameter('search', '%' . $search . '%');
        }

        if ($type) {
            $queryBuilder->andWhere('e.type = :type')
                         ->setParameter('type', $type);
        }

        if ($lieu) {
            $queryBuilder->andWhere('e.lieu = :lieu')
                         ->setParameter('lieu', $lieu);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function findDistinctLieux()
    {
        return $this->createQueryBuilder('e')
            ->select('e.lieu')
            ->distinct()
            ->getQuery()
            ->getResult();
    }
    
}
