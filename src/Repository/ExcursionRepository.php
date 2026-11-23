<?php

namespace App\Repository;

use App\Entity\Excursion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExcursionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Excursion::class);
    }
    public function findByFilters(?string $location = null, ?float $price = null)
    {
        $qb = $this->createQueryBuilder('e');
    
        if (!empty($location)) {
            $qb->andWhere('e.lieu = :location')
               ->setParameter('location', $location);
        }
    
        if (!empty($price)) {
            $qb->andWhere('e.prix <= :price')
               ->setParameter('price', $price);
        }
    
        return $qb->orderBy('e.prix', 'ASC')
                  ->getQuery()
                  ->getResult();
    }
  
  
   }



