<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReservationHotelRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationHotelRepository::class)]
#[ORM\Table(name: "reservationhotel")]
class ReservationHotel
{
    #[ORM\Column(type: "integer")]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private int $id_reservation;

    #[ORM\ManyToOne(targetEntity: Hotel::class)]
    #[ORM\JoinColumn(name: "hotel_id", referencedColumnName: "id_hotel")]
    private ?Hotel $hotel = null;
    
    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank(message: "La date d'arrivée est requise.")]
    #[Assert\Type("\DateTimeInterface")]
    private \DateTimeInterface $check_in_date;
    
    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank(message: "La date de départ est requise.")]
    #[Assert\Type("\DateTimeInterface")]
    #[Assert\LessThan(
        propertyPath: "check_in_date",
        message: "La date de départ doit être avant la date d'arrivée."
    )]
    private \DateTimeInterface $check_out_date;
    #[ORM\Column(type: "string")]
    private string $type_chambre;

    public function getIdReservation(): ?int
    {
        return $this->id_reservation;
    }

    public function setIdReservation(int $id_reservation): self
    {
        $this->id_reservation = $id_reservation;
        return $this;
    }

    // public function getHotelId(): ?int
    // {
    //     return $this->hotel_id;
    // }

    // public function setHotelId(int $hotel_id): self
    // {
    //     $this->hotel_id = $hotel_id;
    //     return $this;
    // }

    public function getCheckInDate(): ?\DateTimeInterface
    {
        return $this->check_in_date;
    }

    public function setCheckInDate(\DateTimeInterface $check_in_date): self
    {
        $this->check_in_date = $check_in_date;
        return $this;
    }

    public function getCheckOutDate(): ?\DateTimeInterface
    {
        return $this->check_out_date;
    }

    public function setCheckOutDate(\DateTimeInterface $check_out_date): self
    {
        $this->check_out_date = $check_out_date;
        return $this;
    }

    public function getTypeChambre(): ?string
    {
        return $this->type_chambre;
    }

    public function setTypeChambre(string $type_chambre): self
    {
        $this->type_chambre = $type_chambre;
        return $this;
    }
    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    public function setHotel(?Hotel $hotel): self
    {
        $this->hotel = $hotel;

        return $this;
    }
    public function getPrixTotal(): float
{
    $prixBase = $this->getHotel()->getPrixNuit();

    return match ($this->getTypeChambre()) {
        'double' => $prixBase * 1.5,
        'mvp' => $prixBase * 2.5,
        default => $prixBase,
    };
}
}
