<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\OneToMany(mappedBy: "utilisateur", targetEntity: Feedback::class)]
    private Collection $feedbacks;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_utilisateur")]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    
    private ?string $email = null;

    #[ORM\Column(length: 255)]
   
   
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    
   
    private ?string $prenom = null;

    #[ORM\Column]
 
    private ?float $cin = null;

    #[ORM\Column]
   
    private ?float $telephone = null;

    #[ORM\Column(type: 'datetime')]
   
    private ?\DateTimeInterface $date_naissance = null;

    #[ORM\Column(length: 255)]
   
    private ?string $adresse = null;

    #[ORM\Column(length: 255)]
  
    private ?string $mot_de_passe = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 20)]
  
    private ?string $type_utilisateur = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $is_verified = false;
    #[ORM\Column(length: 64, nullable: true)]
    private ?string $verification_token = null;
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $verification_token_created_at = null;
    #[Vich\UploadableField(mapping: 'user_photo', fileNameProperty: 'photoName')]
    private ?File $photoFile = null;

    private ?string $photoName = null;

    // Getter / Setter pour photoFile
    public function setPhotoFile(?File $photoFile = null): void
    {
        $this->photoFile = $photoFile;
    }

    public function getPhotoFile(): ?File
    {
        return $this->photoFile;
    }

    // Getter / Setter pour photoName
    public function setPhotoName(?string $photoName): void
    {
        $this->photoName = $photoName;
    }

    public function getPhotoName(): ?string
    {
        return $this->photoName;
    }
    public function getVerificationToken(): ?string
    {
        return $this->verification_token;
    }

    public function setVerificationToken(?string $token): self
    {
        $this->verification_token = $token;
        return $this;
    }
    public function getVerificationTokenCreatedAt(): ?\DateTimeInterface
    {
        return $this->verification_token_created_at;
    }

    public function setVerificationTokenCreatedAt(?\DateTimeInterface $date): self
    {
        $this->verification_token_created_at = $date;
        return $this;
    }
    public function getId(): ?int
    {
        return $this->id;
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

    public function getCin(): ?float
    {
        return $this->cin;
    }

    public function setCin(float $cin): self
    {
        $this->cin = $cin;
        return $this;
    }

    public function getTelephone(): ?float
    {
        return $this->telephone;
    }

    public function setTelephone(float $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->date_naissance;
    }

    public function setDateNaissance(\DateTimeInterface $date_naissance): self
    {
        $this->date_naissance = $date_naissance;
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

 

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;
        return $this;
    }

    public function getTypeUtilisateur(): ?string
    {
        return $this->type_utilisateur;
    }

    public function setTypeUtilisateur(string $type_utilisateur): self
    {
        $this->type_utilisateur = $type_utilisateur;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;
        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->is_verified;
    }

    public function setIsVerified(bool $is_verified): self
    {
        $this->is_verified = $is_verified;
        return $this;
    }
    public function getMotDePasse(): ?string
    {
        return $this->mot_de_passe;
    }

    public function setMotDePasse(string $mot_de_passe): self
    {
        $this->mot_de_passe = $mot_de_passe;
        return $this;
    }
    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];  // Default role for all users
    
        // Check for other roles based on the type_utilisateur value
        if ($this->type_utilisateur === 'Admin') {
            $roles[] = 'ROLE_ADMIN';
        } elseif ($this->type_utilisateur === 'VIP') {
            $roles[] = 'ROLE_VIP';
        }
    
        return $roles;
    }
    
    public function eraseCredentials(): void
    {
        // Si vous stockez des données temporaires sensibles sur l'utilisateur, effacez-les ici
    }
// src/Entity/Utilisateur.php

public function getUsername(): string
{
    return $this->email; // Retourner l'email comme nom d'utilisateur
}

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    // Méthode requise par PasswordAuthenticatedUserInterface
    public function getPassword(): ?string
    {
        return $this->mot_de_passe;
    }
    public function setPassword(string $x): self
    {
        $this->mot_de_passe = $x;
        return $this;
    }
    public function __construct()
{
    $this->feedbacks = new ArrayCollection();
}
public function getFeedbacks(): Collection
{
    return $this->feedbacks;
}

public function addFeedback(Feedback $feedback): self
{
    if (!$this->feedbacks->contains($feedback)) {
        $this->feedbacks[] = $feedback;
        $feedback->setUtilisateur($this);
    }

    return $this;
}

public function removeFeedback(Feedback $feedback): self
{
    if ($this->feedbacks->removeElement($feedback)) {
        // set the owning side to null (unless already changed)
        if ($feedback->getUtilisateur() === $this) {
            $feedback->setUtilisateur(null);
        }
    }

    return $this;
    //push
}

}