<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReservationvolRepository;

#[ORM\Entity(repositoryClass: ReservationvolRepository::class)]
#[ORM\Table(name: "reservationvol")]
class Reservationvol
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id_reservation;

    #[ORM\Column(type: "string", columnDefinition: "ENUM('ÉCONOMIE','AFFAIRES','PREMIÈRE')")]
    private string $classe_service;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "utilisateur_id", referencedColumnName: "id_utilisateur", nullable: false, onDelete: "CASCADE")]
    private Utilisateur $utilisateur;

    #[ORM\ManyToOne(targetEntity: Vol::class)]
    #[ORM\JoinColumn(name: "vol_id", referencedColumnName: "id_vol", nullable: false, onDelete: "CASCADE")]
    private Vol $vol;

    public function getIdReservation(): ?int
    {
        return $this->id_reservation;
    }

    public function setIdReservation(int $id_reservation): self
    {
        $this->id_reservation = $id_reservation;
        return $this;
    }

    public function getClasseService(): ?string
    {
        return $this->classe_service;
    }

    public function setClasseService(string $classe_service): self
    {
        $this->classe_service = $classe_service;
        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getVol(): ?Vol
    {
        return $this->vol;
    }

    public function setVol(Vol $vol): self
    {
        $this->vol = $vol;
        return $this;
    }
}