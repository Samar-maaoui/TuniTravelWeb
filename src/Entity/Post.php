<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\PostRepository;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name: "post")]
class Post
{
    #[ORM\Column(type: "integer")]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private int $id_post;

    #[ORM\Column(type: "string")]
    #[Assert\NotBlank(message: "Le titre du post ne peut pas être vide.")]
    #[Assert\Length(max: 255, maxMessage: "Le titre ne peut pas dépasser 255 caractères.")]
    private ?string $titre = null;

    #[ORM\Column(type: "string")]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    #[Assert\Length(max: 1000, maxMessage: "La description ne peut pas dépasser 1000 caractères.")]
    private string $description;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotNull(message: "La date de publication est obligatoire.")]
    #[Assert\Type(\DateTimeInterface::class, message: "La date de publication doit être une date valide.")]
    #[Assert\GreaterThanOrEqual("today", message: "La date de publication ne peut pas être dans le passé.")]
    private ?\DateTimeInterface $date_publication = null;
    
    #[ORM\Column(type: "datetime")]
    #[Assert\NotNull(message: "La date d'expiration est obligatoire.")]
    #[Assert\Type(\DateTimeInterface::class, message: "La date d'expiration doit être une date valide.")]
    #[Assert\Expression(
        "this.getDateExpiration() === null or this.getDatePublication() === null or this.getDateExpiration() > this.getDatePublication()",
        message: "La date d'expiration doit être après la date de publication."
    )]
    private ?\DateTimeInterface $date_expiration = null;
    
    #[ORM\ManyToOne(targetEntity: Packvoyage::class)]
    #[ORM\JoinColumn(name: "pack_id", referencedColumnName: "id_pack", nullable: false)]
    #[Assert\NotNull(message: "Veuillez sélectionner un pack voyage.")]

    private ?Packvoyage $packvoyage;

    #[ORM\Column(type: "string", columnDefinition: "ENUM('disponible', 'non_disponible')")]
    #[Assert\NotBlank(message: "Le statut ne peut pas être vide.")]
    #[Assert\Choice(choices: ['disponible', 'nondisponible'], message: "Le statut doit être 'disponible' ou 'non_disponible'.")]
    private string $statut;

    #[ORM\Column(type: "string")]
    private string $image_url;

    // Getters and setters for each property...

    public function getIdPost(): ?int
    {
        return $this->id_post;
    }

    public function setIdPost(int $id_post): self
    {
        $this->id_post = $id_post;
        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;
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

    public function getDatePublication(): ?\DateTimeInterface
    {
        return $this->date_publication;
    }

    public function setDatePublication(?\DateTimeInterface $date_publication): self
{
    $this->date_publication = $date_publication;
    return $this;
}

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->date_expiration;
    }

    public function setDateExpiration(?\DateTimeInterface $date_expiration): self
    {
        $this->date_expiration = $date_expiration;
        return $this;
    }

    public function getPackvoyage(): ?Packvoyage
    {
        return $this->packvoyage;
    }

    public function setPackvoyage(?Packvoyage $packvoyage): self
    {
        $this->packvoyage = $packvoyage;
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

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }
    public function setImageUrl(string $image_url): self
    {
        $this->image_url = $image_url;
        return $this;
    }
    public function __construct()
    {
        // Définir le statut par défaut à 'disponible' lors de la création d'un nouveau post
        $this->statut = 'disponible';
    }
  
}
