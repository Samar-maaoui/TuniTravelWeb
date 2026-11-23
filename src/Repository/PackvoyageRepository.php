<?php

namespace App\Repository;

use App\Entity\Packvoyage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PackvoyageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Packvoyage::class);
    }

    public function searchAndSort(?string $search, string $sort, string $direction): array
    {
        $qb = $this->createQueryBuilder('p');
    
        if ($search) {
            $qb->andWhere('p.nomPack LIKE :search OR p.details LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
    
        // Champs triables autorisés
        $sortableFields = ['nomPack', 'prix_total', 'nombre_places_restants'];
    
        if (in_array($sort, $sortableFields) && in_array(strtoupper($direction), ['ASC', 'DESC'])) {
            $qb->orderBy('p.' . $sort, $direction);
        }
    
        return $qb->getQuery()->getResult();
    }
    }
