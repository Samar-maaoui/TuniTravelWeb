<?php

namespace App\Repository;

use App\Entity\Reservationpackvoyage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationpackvoyageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservationpackvoyage::class);
    }

    // Ajoutez ici vos méthodes personnalisées
    // src/Repository/ReservationpackvoyageRepository.php
public function countReservationsByPack(): array
{
    return $this->createQueryBuilder('r')
        ->select('p.titre AS titre, COUNT(r.id) AS total')
        ->join('r.pack', 'p')
        ->groupBy('p.titre')
        ->getQuery()
        ->getResult();
}
public function countByMonth(): array
{
    return $this->getEntityManager()->createQuery(
        'SELECT SUBSTRING(r.date_reservation, 6, 2) AS mois, COUNT(r.id_reservation) AS total
         FROM App\Entity\Reservationpackvoyage r
         GROUP BY mois
         ORDER BY mois'
    )->getResult();
}

public function topUtilisateurs(): array
{
    $conn = $this->getEntityManager()->getConnection();

    $sql = '
        SELECT CONCAT(u.nom, " ", u.prenom) AS nom_complet, COUNT(r.id_reservation) AS total
        FROM reservationpackvoyage r
        JOIN utilisateur u ON r.utilisateur_id = u.id_utilisateur
        GROUP BY u.id_utilisateur
        ORDER BY total DESC
        LIMIT 5
    ';

    return $conn->executeQuery($sql)->fetchAllAssociative();
}



}
