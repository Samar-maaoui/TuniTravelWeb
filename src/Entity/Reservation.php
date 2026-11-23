<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReservationRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: "reservation")]
class Reservation
{
    #[ORM\Column(type: "integer")]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private int $id_reservation;

    // Relation avec l'entité User (ManyToOne)
    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "utilisateur_id", referencedColumnName: "id_utilisateur")]
    private $utilisateur;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_reservation;

    // Utilisation de constantes pour l'énumération
    #[ORM\Column(type: "string", columnDefinition: "ENUM('CONFIRMEE', 'ANNULEE', 'EN_ATTENTE')")]
    private string $statut;

    #[ORM\Column(type: "float")]
    private float $montant_total;

    // Constantes pour l'énumération du statut
    const STATUT_CONFIRME = 'CONFIRMEE';
    const STATUT_ANNULE = 'ANNULEE';
    const STATUT_EN_ATTENTE = 'EN_ATTENTE';

    // Getters and setters

    public function getIdReservation(): ?int
    {
        return $this->id_reservation;
    }

    public function setIdReservation(int $id_reservation): self
    {
        $this->id_reservation = $id_reservation;
        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur($utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getDateReservation(): ?\DateTimeInterface
    {
        return $this->date_reservation;
    }

    public function setDateReservation(\DateTimeInterface $date_reservation): self
    {
        $this->date_reservation = $date_reservation;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getMontantTotal(): ?float
    {
        return $this->montant_total;
    }

    public function setMontantTotal(float $montant_total): self
    {
        $this->montant_total = $montant_total;
        return $this;
    }

    // Méthode pour vérifier si le statut est valide
    public function isStatutValid(): bool
    {
        return in_array($this->statut, [self::STATUT_CONFIRME, self::STATUT_ANNULE, self::STATUT_EN_ATTENTE]);
    }
}