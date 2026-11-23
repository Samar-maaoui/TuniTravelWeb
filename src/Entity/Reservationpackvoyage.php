<?php

// src/Entity/Reservationpackvoyage.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ReservationpackvoyageRepository;

#[ORM\Entity(repositoryClass: ReservationpackvoyageRepository::class)]
#[ORM\Table(name: "reservationpackvoyage")]
class Reservationpackvoyage
{
    #[ORM\Column(type: "integer")]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private int $id_reservation;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "utilisateur_id", referencedColumnName: "id_utilisateur")]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank(message: "La date de réservation ne peut pas être vide.")]
    #[Assert\Type("\DateTimeInterface", message: "La date de réservation doit être au format valide.")]
    private \DateTimeInterface $date_reservation;

    #[ORM\Column(name: "statut", type: "string")]
    #[Assert\NotBlank(message: "Le statut ne peut pas être vide.")]
    #[Assert\Choice(choices: ['CONFIRMEE', 'ANNULEE', 'EN_ATTENTE'], message: "Le statut doit être 'validée', 'annulée' ou 'en_attente'.")]
    private string $statut;

    #[ORM\Column(name: "statut_paiement", type: "string")]
    #[Assert\NotBlank(message: "Le statut de paiement ne peut pas être vide.")]
    #[Assert\Choice(choices: ['PAYE', 'NON_PAYE'], message: "Le statut de paiement doit être 'payée' ou 'non payée'.")]
    private string $statut_paiement;

    #[ORM\ManyToOne(targetEntity: Packvoyage::class)]
    #[ORM\JoinColumn(name: "pack_id", referencedColumnName: "id_pack", onDelete: "SET NULL", nullable: true)]
    private ?Packvoyage $packVoyage = null;
    

    

    #[ORM\Column(type: "integer")]
    private int $post_id;

    #[ORM\Column(type: "integer")]
    #[Assert\GreaterThan(value: 0, message: "Le nombre de places demandées doit être supérieur à 0.")]
    private int $places_demandees;

    #[ORM\Column(type: "float")]
    #[Assert\Positive(message: "Le montant total doit être positif.")]
    private float $montant_total;
    #[ORM\Column(name: "nom_pack_archive", type: "string", nullable: true)]
    private ?string $nomPackArchive = null;
    



    // Getters and setters for each property...

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

public function setUtilisateur(?Utilisateur $utilisateur): self
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

    public function getStatutPaiement(): ?string
    {
        return $this->statut_paiement;
    }

    public function setStatutPaiement(string $statut_paiement): self
    {
        $this->statut_paiement = $statut_paiement;
        return $this;
    }
    public function getPackVoyage(): ?PackVoyage
    {
        return $this->packVoyage;
    }
    
    public function setPackVoyage(?PackVoyage $packVoyage): self
    {
        $this->packVoyage = $packVoyage;
        return $this;
    }
    

    public function getPostId(): ?int
    {
        return $this->post_id;
    }

    public function setPostId(int $post_id): self
    {
        $this->post_id = $post_id;
        return $this;
    }

    public function getPlacesDemandees(): ?int
    {
        return $this->places_demandees;
    }

    public function setPlacesDemandees(int $places_demandees): self
    {
        $this->places_demandees = $places_demandees;
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
    public function getNomPackArchive(): ?string
{
    return $this->nomPackArchive;
}

public function setNomPackArchive(?string $nomPackArchive): self
{
    $this->nomPackArchive = $nomPackArchive;
    return $this;
}

}
