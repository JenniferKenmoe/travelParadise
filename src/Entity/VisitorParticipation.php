<?php

namespace App\Entity;

use App\Repository\VisitorParticipationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisitorParticipationRepository::class)]
class VisitorParticipation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Lien vers la visite
    #[ORM\ManyToOne(targetEntity: Visite::class, inversedBy: 'visitors')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Visite $visite = null;

    // Lien vers le visiteur
    #[ORM\ManyToOne(targetEntity: Visitor::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Visitor $visitor = null;

    // Présence (Oui/Non)
    #[ORM\Column(type: 'boolean')]
    private ?bool $present = null;

    // Commentaire sur le visiteur pour cette visite
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVisite(): ?Visite
    {
        return $this->visite;
    }

    public function setVisite(?Visite $visite): self
    {
        $this->visite = $visite;
        return $this;
    }

    public function getVisitor(): ?Visitor
    {
        return $this->visitor;
    }

    public function setVisitor(?Visitor $visitor): self
    {
        $this->visitor = $visitor;
        return $this;
    }

    public function isPresent(): ?bool
    {
        return $this->present;
    }

    public function setPresent(bool $present): self
    {
        $this->present = $present;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }
}