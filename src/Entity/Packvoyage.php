<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PackvoyageRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: PackvoyageRepository::class)]
#[ORM\Table(name: "packvoyage")]
#[UniqueEntity(fields: ["nomPack"], message: "Ce nom de pack est déjà utilisé.")]
class Packvoyage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id_pack;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "admin_id", referencedColumnName: "id_utilisateur", nullable: false)]
    private ?Utilisateur $admin = null;
    

    #[ORM\ManyToOne(targetEntity: Hotel::class)]
    #[ORM\JoinColumn(name: "hotel_id", referencedColumnName: "id_hotel")]
    #[Assert\NotNull(message: "L'hôtel est obligatoire.")]
    private ?Hotel $hotel;

    #[ORM\ManyToOne(targetEntity: Vol::class)]
    #[ORM\JoinColumn(name: "vol_id", referencedColumnName: "id_vol")]
    #[Assert\NotNull(message: "Le vol est obligatoire.")]
    private ?Vol $vol;

    #[ORM\ManyToOne(targetEntity: Excursion::class)]
    #[ORM\JoinColumn(name: "excursion_id", referencedColumnName: "id_excursion")]
    #[Assert\NotNull(message: "L'excursion est obligatoire.")]
    private ?Excursion $excursion;

    #[ORM\Column(type: "float")]
    #[Assert\NotNull(message: "Le prix total est obligatoire.")]
    #[Assert\Positive(message: "Le prix total doit être positif.")]
    private ?float $prix_total = null;  


    #[ORM\Column(type: "string")]
    #[Assert\NotBlank(message: "Les détails ne peuvent pas être vides.")]
    private string $details;

    #[ORM\Column(type: "integer")]
    #[Assert\NotNull(message: "Le nombre de places restantes est obligatoire.")]
    #[Assert\PositiveOrZero(message: "Le nombre de places restantes ne peut pas être négatif.")]
    private ?int $nombre_places_restants = null;

    #[ORM\Column(type: "string", name: "nomPack")]
    #[Assert\NotBlank(message: "Le nom du pack ne peut pas être vide.")]
    private string $nomPack;

    #[ORM\Column(type: "string")]
    private string $image_url;

    // Getters et Setters pour toutes les propriétés

    public function getIdPack(): ?int
    {
        return $this->id_pack;
    }

    public function setIdPack(int $id_pack): self
    {
        $this->id_pack = $id_pack;
        return $this;
    }

    public function getAdmin(): ?Utilisateur
{
    return $this->admin;
}

public function setAdmin(?Utilisateur $admin): self
{
    $this->admin = $admin;
    return $this;
}


    // Getters et setters pour Hotel, Vol, Excursion
    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    public function setHotel(?Hotel $hotel): self
    {
        $this->hotel = $hotel;
        return $this;
    }

    public function getVol(): ?Vol
    {
        return $this->vol;
    }

    public function setVol(?Vol $vol): self
    {
        $this->vol = $vol;
        return $this;
    }

    public function getExcursion(): ?Excursion
    {
        return $this->excursion;
    }

    public function setExcursion(?Excursion $excursion): self
    {
        $this->excursion = $excursion;
        return $this;
    }

    public function getPrixTotal(): ?float
    {
        return $this->prix_total;
    }

    public function setPrixTotal(float $prix_total): self
    {
        $this->prix_total = $prix_total;
        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(string $details): self
    {
        $this->details = $details;
        return $this;
    }

    public function getNombrePlacesRestants(): ?int
    {
        return $this->nombre_places_restants;
    }

    public function setNombrePlacesRestants(int $nombre_places_restants): self
    {
        $this->nombre_places_restants = $nombre_places_restants;
        return $this;
    }

    public function getNomPack(): ?string
    {
        return $this->nomPack;
    }

    public function setNomPack(string $nomPack): self
    {
        $this->nomPack = $nomPack;
        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function setImageUrl(string $image_url): self
    {
        $this->image_url = $image_url;
        return $this;
    }
}
