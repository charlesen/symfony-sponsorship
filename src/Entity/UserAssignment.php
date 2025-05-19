<?php

namespace App\Entity;

use App\Enum\UserAssignmentStatus;
use App\Repository\UserAssignmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserAssignmentRepository::class)]
class UserAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Assignment $assignment = null;

    #[ORM\Column(length: 20, enumType: UserAssignmentStatus::class)]
    private ?UserAssignmentStatus $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $completedAt = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $data = [];

    #[ORM\Column]
    private ?int $pointsEarned = 0;

    #[ORM\Column]
    private bool $validated = false;

    #[ORM\ManyToOne]
    private ?User $validatedBy = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $validatedAt = null;

    public function __construct()
    {
        $this->startedAt = new \DateTimeImmutable();
        $this->status = UserAssignmentStatus::PENDING;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getAssignment(): ?Assignment
    {
        return $this->assignment;
    }

    public function setAssignment(?Assignment $assignment): static
    {
        $this->assignment = $assignment;
        return $this;
    }

    public function getStatus(): ?UserAssignmentStatus
    {
        return $this->status;
    }

    public function setStatus(UserAssignmentStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): static
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(\DateTimeInterface $completedAt): static
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function getPointsEarned(): ?int
    {
        return $this->pointsEarned;
    }

    public function setPointsEarned(int $pointsEarned): static
    {
        $this->pointsEarned = $pointsEarned;
        return $this;
    }

    public function isValidated(): bool
    {
        return $this->validated;
    }

    public function setValidated(bool $validated): static
    {
        $this->validated = $validated;
        return $this;
    }

    public function getValidatedBy(): ?User
    {
        return $this->validatedBy;
    }

    public function setValidatedBy(?User $validatedBy): static
    {
        $this->validatedBy = $validatedBy;
        return $this;
    }

    public function getValidatedAt(): ?\DateTimeInterface
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(\DateTimeInterface $validatedAt): static
    {
        $this->validatedAt = $validatedAt;
        return $this;
    }

    // Méthodes utilitaires
    public function isComplete(): bool
    {
        return $this->status === UserAssignmentStatus::COMPLETED;
    }

    public function markAsCompleted(array $data = null): static
    {
        $this->status = UserAssignmentStatus::COMPLETED;
        $this->completedAt = new \DateTimeImmutable();
        $this->data = $data;
        
        // Par défaut, les points gagnés sont ceux de la mission
        // Peut être personnalisé si nécessaire
        $this->pointsEarned = $this->assignment?->getPoints() ?? 0;
        
        return $this;
    }
}
