<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\FeedbackRepository;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Utilisateur;

#[ORM\Entity(repositoryClass: FeedbackRepository::class)]
#[ORM\Table(name: "feedback")]
class Feedback
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id_feedback;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'feedbacks')]
    #[ORM\JoinColumn(name: "utilisateur_id", referencedColumnName: "id_utilisateur", nullable: false)]
    private Utilisateur $utilisateur;

    #[ORM\Column(type: "text")]
   
    private string $commentaire;

    #[ORM\Column(type: "integer", nullable: true)]
   
    private ?int $note = null;

    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private \DateTimeInterface $date_feedback;

    // getters et setters...

 
    

    public function getId(): ?int
    {
        return $this->id_feedback;
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

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): self
    {
        $this->note = $note;
        return $this;
    }

    public function getDateFeedback(): ?\DateTimeInterface
    {
        return $this->date_feedback;
    }

    public function setDateFeedback(\DateTimeInterface $date_feedback): self
    {
        $this->date_feedback = $date_feedback;
        return $this;
    }
}
