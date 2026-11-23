<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ExcursionstatRepository;

#[ORM\Entity(repositoryClass: ExcursionstatRepository::class)]
#[ORM\Table(name: "excursionstat")]
class Excursionstat
{
    #[ORM\Column(type: "integer")]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: "integer")]
    private int $excursion_id;

    #[ORM\Column(type: "integer")]
    private int $reservations_count;

    #[ORM\Column(type: "integer")]
    private int $total_reserved_places;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_created;

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExcursionId(): ?int
    {
        return $this->excursion_id;
    }

    public function setExcursionId(int $excursion_id): self
    {
        $this->excursion_id = $excursion_id;
        return $this;
    }

    public function getReservationsCount(): ?int
    {
        return $this->reservations_count;
    }

    public function setReservationsCount(int $reservations_count): self
    {
        $this->reservations_count = $reservations_count;
        return $this;
    }

    public function getTotalReservedPlaces(): ?int
    {
        return $this->total_reserved_places;
    }

    public function setTotalReservedPlaces(int $total_reserved_places): self
    {
        $this->total_reserved_places = $total_reserved_places;
        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->date_created;
    }

    public function setDateCreated(\DateTimeInterface $date_created): self
    {
        $this->date_created = $date_created;
        return $this;
    }
}
