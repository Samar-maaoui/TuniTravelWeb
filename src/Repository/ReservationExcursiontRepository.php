<?php

namespace App\Repository;

use App\Entity\ReservationExcursion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationExcursionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationExcursion::class);
    }

    public function countReservationsByExcursion(): array
    {
        return $this->createQueryBuilder('r')
        ->select('e.nom AS excursion, COUNT(r.id_reservation) AS total')
        ->join('r.excursion', 'e') // This is correct based on your mapping
        ->groupBy('e.id_excursion') // Group by ID to avoid duplicate names
        ->getQuery()
        ->getResult();
    }}
