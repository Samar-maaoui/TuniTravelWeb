<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\VolRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VolRepository::class)]
#[ORM\Table(name: "vol")]
class Vol
{
    #[ORM\Column(type: "integer")]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private int $id_vol;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le numéro du vol ne peut pas être vide.")]
    #[Assert\Regex(
        pattern: '/^[A-Z]{2}\d{3}$/',
        message: "Le numéro du vol doit contenir 2 lettres majuscules suivies de 3 chiffres (ex: AB123)."
    )]
    private ?string $numero_vol = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le nom de la compagnie ne peut pas être vide.")]
    private ?string $compagnie = null;

    #[ORM\Column(type: "float", nullable: true)]
    #[Assert\NotBlank(message: "Le prix ne peut pas être vide.")]
    #[Assert\Positive(message: "Le prix doit être un nombre positif.")]
    private ?float $prix = null;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Assert\NotBlank(message: "Le nombre de places disponibles ne peut pas être vide.")]
    #[Assert\PositiveOrZero(message: "Le nombre de places disponibles ne peut pas être négatif.")]
    private ?int $disponible = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\NotBlank(message: "L'aéroport de départ ne peut pas être vide.")]
    private ?string $aero_depart = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\NotBlank(message: "L'aéroport d'arrivée ne peut pas être vide.")]
    private ?string $aero_arrivee = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    #[Assert\NotBlank(message: "La date de départ est requise.")]
    #[Assert\Type(\DateTimeInterface::class)]
    #[Assert\GreaterThan("today", message: "La date de départ doit être dans le futur.")] // Ajout
    private ?\DateTimeInterface $date_depart = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    #[Assert\NotBlank(message: "La date d'arrivée est requise.")]
    #[Assert\Type(\DateTimeInterface::class)]
    #[Assert\GreaterThan(
        propertyPath: "date_depart",
        message: "La date d'arrivée doit être après la date de départ."
    )]
    private ?\DateTimeInterface $date_arrivee = null;

    // Getters et Setters

    public function getIdVol(): ?int
    {
        return $this->id_vol;
    }

    public function setIdVol(int $id_vol): self
    {
        $this->id_vol = $id_vol;
        return $this;
    }

    public function getNumeroVol(): ?string
    {
        return $this->numero_vol;
    }

    public function setNumeroVol(?string $numero_vol): self
    {
        $this->numero_vol = $numero_vol;
        return $this;
    }

    public function getCompagnie(): ?string
    {
        return $this->compagnie;
    }

    public function setCompagnie(?string $compagnie): self
    {
        $this->compagnie = $compagnie;
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function getDisponible(): ?int
    {
        return $this->disponible;
    }

    public function setDisponible(?int $disponible): self
    {
        $this->disponible = $disponible;
        return $this;
    }

    public function getAeroDepart(): ?string
    {
        return $this->aero_depart;
    }

    public function setAeroDepart(?string $aero_depart): self
    {
        $this->aero_depart = $aero_depart;
        return $this;
    }

    public function getAeroArrivee(): ?string
    {
        return $this->aero_arrivee;
    }

    public function setAeroArrivee(?string $aero_arrivee): self
    {
        $this->aero_arrivee = $aero_arrivee;
        return $this;
    }

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->date_depart;
    }

    public function setDateDepart(?\DateTimeInterface $date_depart): self
    {
        $this->date_depart = $date_depart;
        return $this;
    }

    public function getDateArrivee(): ?\DateTimeInterface
    {
        return $this->date_arrivee;
    }

    public function setDateArrivee(?\DateTimeInterface $date_arrivee): self
    {
        $this->date_arrivee = $date_arrivee;
        return $this;
    }
}