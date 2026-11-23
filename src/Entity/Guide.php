<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\GuideRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(fields: ["email"], message: "Cet email est déjà utilisé.")]
#[UniqueEntity(fields: ["numeroTel"], message: "Ce numéro de téléphone est déjà utilisé.")]
#[ORM\Entity(repositoryClass: GuideRepository::class)]
#[ORM\Table(name: "guide")]
class Guide
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "id_guide")] 
    private int $id_guide;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(min: 2, max: 50, minMessage: "Le nom doit contenir au moins {{ limit }} caractères.")]
    private string $nom;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    #[Assert\Length(min: 2, max: 50, minMessage: "Le prénom doit contenir au moins {{ limit }} caractères.")]
    private string $prenom;

    #[ORM\Column(type: "string", length: 255)]
    private string $langues;

    #[ORM\Column(type: "string", length: 20)]
    #[Assert\NotBlank(message: "Le numéro de téléphone est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^\+?[0-9]{8,15}$/",
        message: "Le numéro de téléphone doit être valide (8 à 15 chiffres)."
    )]
    private string $numeroTel;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "Veuillez fournir une adresse email valide.")]
    private string $email;

    #[ORM\Column(type: "string", nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "Le chemin de l'image est trop long.")]
    private ?string $image = null;

    // Getters et Setters
    public function getIdGuide(): ?int
    {
        return $this->id_guide;
    }

    public function setIdGuide(int $id_guide): self
    {
        $this->id_guide = $id_guide;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }



   

    public function getLangues(): ?string
    {
        return $this->langues;
    }

    public function setLangues(string $langues): self
    {
        $this->langues = $langues;
        return $this;
    }

    public function getNumeroTel(): ?string
    {
        return $this->numeroTel;
    }

    public function setNumeroTel(string $numeroTel): self
    {
        $this->numeroTel = $numeroTel;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }
}
