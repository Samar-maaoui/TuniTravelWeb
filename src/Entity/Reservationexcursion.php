<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReservationExcursionRepository;

#[ORM\Entity(repositoryClass: ReservationExcursionRepository::class)]
#[ORM\Table(name: "reservationexcursion")]
class ReservationExcursion
{
    #[ORM\Column(type: "integer")]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private int $id_reservation;

    // Lien avec l'entité Excursion (ManyToOne)
    #[ORM\ManyToOne(targetEntity: Excursion::class)]
    #[ORM\JoinColumn(name: "excursion_id", referencedColumnName: "id_excursion")]
    private Excursion $excursion;

    // Lien avec l'entité Guide (ManyToOne)
    #[ORM\ManyToOne(targetEntity: Guide::class)]
    #[ORM\JoinColumn(name: "guide_id", referencedColumnName: "id_guide")]
    private Guide $guide;
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

    #[ORM\Column(type: "integer")]
    private int $nbr_place_reservee;

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

    public function getExcursion(): ?Excursion
    {
        return $this->excursion;
    }

    public function setExcursion(Excursion $excursion): self
    {
        $this->excursion = $excursion;
        return $this;
    }

    public function getGuide(): ?Guide
    {
        return $this->guide;
    }

    public function setGuide(Guide $guide): self
    {
        $this->guide = $guide;
        return $this;
    }

    public function getNbrPlaceReservee(): ?int
    {
        return $this->nbr_place_reservee;
    }

    public function setNbrPlaceReservee(int $nbr_place_reservee): self
    {
        $this->nbr_place_reservee = $nbr_place_reservee;
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