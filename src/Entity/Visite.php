<?php

namespace App\Entity;

use App\Repository\VisiteRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: VisiteRepository::class)]
#[Vich\Uploadable]
class Visite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoUrl = null;

    #[Vich\UploadableField(mapping: 'visite_images', fileNameProperty: 'photoUrl')]
    private ?File $imageFile = null;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    private ?Country $country = null;

    #[ORM\Column(length: 255)]
    private ?string $placeToVisit = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $visitDate = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: 'float')]
    private ?float $duration = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\ManyToOne(targetEntity: Guide::class)]
    private ?Guide $assignedGuide = null;

    // #[ORM\ManyToMany(targetEntity: Visitor::class)]
    // #[ORM\JoinTable(name: "visite_visitors")]
    // private Collection $visitors;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $visitComment = null;

    #[ORM\OneToMany(mappedBy: 'visite', targetEntity: VisitorParticipation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $visitorParticipations;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'not_scheduled'])]
    private ?string $status = null;

    public function __construct()
    {
        $this->visitorParticipations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhotoUrl(): ?string
    {
        return $this->photoUrl;
    }

    public function setPhotoUrl(?string $photoUrl): self
    {
        $this->photoUrl = $photoUrl;
        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): self
    {
        $this->imageFile = $imageFile;
        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;
        return $this;
    }

    public function getPlaceToVisit(): ?string
    {
        return $this->placeToVisit;
    }

    public function setPlaceToVisit(string $placeToVisit): self
    {
        $this->placeToVisit = $placeToVisit;
        return $this;
    }

    public function getVisitDate(): ?\DateTimeInterface
    {
        return $this->visitDate;
    }

    public function setVisitDate(\DateTimeInterface $visitDate): self
    {
        $this->visitDate = $visitDate;
        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        // Recalcule endTime si la durée est déjà définie
        if ($this->duration !== null) {
            $start = new \DateTime($startTime->format('H:i:s'));
            $interval = new \DateInterval('PT' . (int)$this->duration . 'H');
            $minutes = ($this->duration - (int)$this->duration) * 60;
            if ($minutes > 0) {
                $interval->i = (int)$minutes;
            }
            $start->add($interval);
            $this->endTime = $start;
        }

        return $this;
    }

    public function getDuration(): ?float
    {
        return $this->duration;
    }

    public function setDuration(float $duration): self
    {
        $this->duration = $duration;

        // Calcul automatique de l'heure de fin si l'heure de début est définie
        if ($this->startTime !== null) {
            $start = new \DateTime($this->startTime->format('H:i:s'));
            $interval = new \DateInterval('PT' . (int)$duration . 'H');
            $minutes = ($duration - (int)$duration) * 60;
            if ($minutes > 0) {
                $interval->i = (int)$minutes;
            }
            $start->add($interval);
            $this->endTime = $start;
        }

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getAssignedGuide(): ?Guide
    {
        return $this->assignedGuide;
    }

    public function setAssignedGuide(?Guide $assignedGuide): self
    {
        $this->assignedGuide = $assignedGuide;
        return $this;
    }

    public function getVisitComment(): ?string
    {
        return $this->visitComment;
    }

    public function setVisitComment(?string $visitComment): self
    {
        $this->visitComment = $visitComment;
        return $this;
    }

    public function getStatus(): ?string
    {
        // Calcul automatique du statut si non défini
        if ($this->status !== null && in_array($this->status, ['not_scheduled', 'en_cours', 'terminee'])) {
            return $this->status;
        }
        $now = new \DateTimeImmutable();
        if ($this->visitDate && $this->startTime && $this->endTime) {
            $visitDate = $this->visitDate->format('Y-m-d');
            $start = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $visitDate . ' ' . $this->startTime->format('H:i:s'));
            $end = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $visitDate . ' ' . $this->endTime->format('H:i:s'));
            if ($now < $start) {
                return 'not_scheduled';
            } elseif ($now >= $start && $now <= $end) {
                return 'en_cours';
            } else {
                return 'terminee';
            }
        }
        return 'not_scheduled';
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getStatusLabel(): string
    {
        return match ($this->getStatus()) {
            'en_cours' => 'En cours',
            'terminee' => 'Terminée',
            default => 'À venir',
        };
    }

    /**
     * @return Collection<int, VisitorParticipation>
     */
    public function getVisitorParticipations(): Collection
    {
        return $this->visitorParticipations;
    }

    public function addVisitorParticipation(VisitorParticipation $participation): self
    {
        if (!$this->visitorParticipations->contains($participation) && $this->visitorParticipations->count() < 15) {
            $this->visitorParticipations[] = $participation;
            $participation->setVisite($this);
        }
        return $this;
    }

    public function removeVisitorParticipation(VisitorParticipation $participation): self
    {
        if ($this->visitorParticipations->removeElement($participation)) {
            if ($participation->getVisite() === $this) {
                $participation->setVisite(null);
            }
        }
        return $this;
    }

    public function getVisitorsList(): string
    {
        $names = [];
        foreach ($this->getVisitorParticipations() as $participation) {
            $visitor = $participation->getVisitor();
            if ($visitor) {
                $names[] = $visitor->getFirstName() . ' ' . $visitor->getLastName();
            }
        }
        return implode(', ', $names);
    }

    public function getVisitorCount(): int
    {
        return $this->getVisitorParticipations()->count();
    }

    public function __toString(): string
    {
        // Adapte selon les champs pertinents pour identifier une visite
        return $this->getPlaceToVisit() . ' - ' . ($this->getVisitDate()?->format('Y-m-d') ?? '');
    }
}
