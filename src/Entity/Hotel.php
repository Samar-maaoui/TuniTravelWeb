<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\HotelRepository;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\UniqueEmail;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\ReservationHotel;

#[ORM\Entity(repositoryClass: HotelRepository::class)]
#[ORM\Table(name: "hotel")]
class Hotel
{
    #[ORM\Column(type: "integer")]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private int $id_hotel;
    #[ORM\OneToMany(mappedBy: 'hotel', targetEntity: ReservationHotel::class, cascade: ['persist', 'remove'])]
    private Collection $reservations;

    #[ORM\Column(type: "string")]
    #[Assert\NotBlank(message: "Le nom ne doit pas être vide.")]
    #[Assert\Length(
    min: 3,
    max: 100,
    minMessage: "Le nom doit faire au moins {{ limit }} caractères.",
    maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
)]
    private string $nom;

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank(message: "Le prix par nuit est requis.")]
    #[Assert\Positive(message: "Le prix doit être un nombre positif.")]
    private float $prix_nuit;

    #[ORM\Column(type: "boolean")]
    private bool $est_disponible;

    #[ORM\Column(type: "string")]
    #[Assert\NotBlank(message: "L'adresse est requise.")]
     private string $adresse;

    #[ORM\Column(type: "string")]
    private string $description;
    
    #[ORM\Column(type: "string")]
    #[Assert\NotBlank(message: "Le numéro de téléphone est requis.")]
    #[Assert\Regex(
    pattern: "/^\+?[0-9]{8,15}$/",
    message: "Le numéro de téléphone n'est pas valide." 

     )]
      private string $tel;

    #[ORM\Column(type: "string")]
    #[Assert\NotBlank(message: "L'email de contact est requis.")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas un email valide.")]
     private string $email_contact;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $image_hotel = null;

    public function __construct()
    {
        $this->image_hotel = null;
        $this->reservations = new ArrayCollection();

    }

    // Getters and setters for each property

    public function getIdHotel(): ?int
    {
        return $this->id_hotel;
    }

    public function setIdHotel(int $id_hotel): self
    {
        $this->id_hotel = $id_hotel;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrixNuit(): ?float
    {
        return $this->prix_nuit;
    }

    public function setPrixNuit(float $prix_nuit): self
    {
        $this->prix_nuit = $prix_nuit;
        return $this;
    }

    public function getEstDisponible(): ?bool
    {
        return $this->est_disponible;
    }

    public function setEstDisponible(bool $est_disponible): self
    {
        $this->est_disponible = $est_disponible;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(string $tel): self
    {
        $this->tel = $tel;
        return $this;
    }

    public function getEmailContact(): ?string
    {
        return $this->email_contact;
    }

    public function setEmailContact(string $email_contact): self
    {
        $this->email_contact = $email_contact;
        return $this;
    }

    public function getImageHotel(): ?string
    {
        return $this->image_hotel;
    }

    public function setImageHotel(?string $image_hotel): self
    {
        $this->image_hotel = $image_hotel;
        return $this;
    }


public function getReservations(): Collection
{
    return $this->reservations;
}

public function addReservation(ReservationHotel $reservation): self
{
    if (!$this->reservations->contains($reservation)) {
        $this->reservations[] = $reservation;
        $reservation->setHotel($this);
    }

    return $this;
}

public function removeReservation(ReservationHotel $reservation): self
{
    if ($this->reservations->removeElement($reservation)) {
        // set the owning side to null (unless already changed)
        if ($reservation->getHotel() === $this) {
            $reservation->setHotel(null);
        }
    }

    return $this;
}
    
}
