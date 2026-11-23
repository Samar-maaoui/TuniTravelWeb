<?php

namespace App\Repository;

use App\Entity\Excursionstat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExcursionstatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Excursionstat::class);
    }

    // Ajoutez ici vos méthodes personnalisées
}
