<?php
namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ExcursionRepository;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ExcursionRepository::class)]
#[ORM\Table(name: "excursion")]
class Excursion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "id_excursion")] // Renommé ici
    private int $id_excursion;

    #[Assert\NotBlank(message: "Le nom de l'excursion est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    #[ORM\Column(type: "string", length: 255)]
    private string $nom;
    
    #[Assert\NotBlank(message: "Le lieu est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le lieu ne peut pas dépasser {{ limit }} caractères."
    )]
    #[ORM\Column(type: "string", length: 255)]
    private string $lieu;
    
    #[Assert\NotBlank(message: "Le prix est obligatoire.")]
    #[Assert\Positive(message: "Le prix doit être un nombre positif.")]
    #[ORM\Column(type: "float")]
    private float $prix;
    
    #[Assert\NotNull(message: "La date de départ est obligatoire.")]
#[Assert\GreaterThanOrEqual(
    value: "+4 days",
    message: "La date de départ doit être dans au moins 4 jours."
)]
#[ORM\Column(type: 'date', nullable: true)]
private ?\DateTimeInterface $date_depart = null;

#[Assert\NotNull(message: "La date de retour est obligatoire.")]
#[Assert\GreaterThanOrEqual(
    value: "today",
    message: "La date de retour doit être aujourd’hui ou plus tard."
)]
#[ORM\Column(type: 'date', nullable: true)]
private ?\DateTimeInterface $date_retour = null;

    
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(
        min: 10,
        minMessage: "La description doit contenir au moins {{ limit }} caractères."
    )]
    #[ORM\Column(type: "text")]
    private string $description;
    
    #[Assert\NotBlank(message: "Le nombre de participants est obligatoire.")]
    #[Assert\Positive(message: "Le nombre de participants doit être supérieur à zéro.")]
    #[ORM\Column(type: "integer")]
    private int $nombre_max_participants;
    
    #[Assert\NotBlank(message: "La ville de départ est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "La ville de départ ne peut pas dépasser {{ limit }} caractères."
    )]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $villeDepart = null;
    
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\File(
        mimeTypes: ["image/jpeg", "image/png"],
        mimeTypesMessage: "Veuillez uploader une image JPEG ou PNG valide.",
        maxSize: "2M",
        maxSizeMessage: "L’image ne doit pas dépasser 2 Mo."
    )]
    private ?string $image = null;
    

    #[ORM\ManyToOne(targetEntity: Guide::class)]
    #[ORM\JoinColumn(name: "id_guide", referencedColumnName: "id_guide", nullable: false)]
    private ?Guide $guide = null;

    
    

public function getGuide(): ?Guide
{
    return $this->guide;
}

public function setGuide(?Guide $guide): self
{
    $this->guide = $guide;
    return $this;
}
    // Getters et Setters pour les propriétés

    public function getIdExcursion(): int
    {
        return $this->id_excursion;
    }

    public function setIdExcursion(int $id_excursion): self
    {
        $this->id_excursion = $id_excursion;
        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }
    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getLieu(): string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getPrix(): float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->date_depart;
    }
    
    public function setDateDepart(\DateTimeInterface $date_depart): self
    {
        $this->date_depart = $date_depart;
        return $this;
    }
    
    public function getDateRetour(): ?\DateTimeInterface
    {
        return $this->date_retour;
    }
    
    public function setDateRetour(\DateTimeInterface $date_retour): self
    {
        $this->date_retour = $date_retour;
        return $this;
    }
    

 

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getNombreMaxParticipants(): int
    {
        return $this->nombre_max_participants;
    }

    public function setNombreMaxParticipants(int $nombre_max_participants): self
    {
        $this->nombre_max_participants = $nombre_max_participants;
        return $this;
    }

   
    // Getter for image
    public function getImage(): string
    {
        return $this->image ?? 'path/to/default/image.jpg'; // Return a default image if null
    }

    // Setter for image
    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

   
    public function getvilleDepart(): ?string
    {
        return $this->villeDepart;
    }

    public function setvilleDepart(string $villeDepart): static
    {
        $this->villeDepart = $villeDepart;

        return $this;
    }



  
    
}