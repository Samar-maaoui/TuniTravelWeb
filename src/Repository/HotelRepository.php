<?php

namespace App\Repository;

use App\Entity\Hotel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class HotelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hotel::class);
    }
    public function findByNomLike(string $nom): array
{
    return $this->createQueryBuilder('h')
        ->where('LOWER(h.nom) LIKE LOWER(:nom)')
        ->setParameter('nom', '%' . $nom . '%')
        ->getQuery()
        ->getResult();
}

    // Ajoutez ici vos méthodes personnalisées si nécessaire
}
