<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Trait\Timestampable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    // Aucun mot de passe n'est nécessaire avec l'authentification par magic link

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    private ?string $referrerCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastname = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'referrals')]
    private ?self $referrer = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'referrer')]
    private Collection $referrals;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $locale = null;

    /**
     * @var Collection<int, UserAssignment>
     */
    #[ORM\OneToMany(targetEntity: UserAssignment::class, mappedBy: 'user')]
    private Collection $assignments;

    public function __construct()
    {
        $this->referrals = new ArrayCollection();
        $this->assignments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // Pas de données sensibles à effacer avec l'authentification par magic link
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return null; // Aucun mot de passe avec l'authentification par magic link
    }

    public function getReferrerCode(): ?string
    {
        return $this->referrerCode;
    }

    public function setReferrerCode(string $referrerCode): static
    {
        $this->referrerCode = $referrerCode;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getReferrer(): ?self
    {
        return $this->referrer;
    }

    public function setReferrer(?self $referrer): static
    {
        $this->referrer = $referrer;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getReferrals(): Collection
    {
        return $this->referrals;
    }

    public function addReferral(self $referral): static
    {
        if (!$this->referrals->contains($referral)) {
            $this->referrals->add($referral);
            $referral->setReferrer($this);
        }

        return $this;
    }

    public function removeReferral(self $referral): static
    {
        if ($this->referrals->removeElement($referral)) {
            // set the owning side to null (unless already changed)
            if ($referral->getReferrer() === $this) {
                $referral->setReferrer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserAssignment>
     */
    public function getAssignments(): Collection
    {
        return $this->assignments;
    }

    public function addAssignment(UserAssignment $assignment): static
    {
        if (!$this->assignments->contains($assignment)) {
            $this->assignments->add($assignment);
            $assignment->setUser($this);
        }

        return $this;
    }

    public function removeAssignment(UserAssignment $assignment): static
    {
        if ($this->assignments->removeElement($assignment)) {
            // set the owning side to null (unless already changed)
            if ($assignment->getUser() === $this) {
                $assignment->setUser(null);
            }
        }
        return $this;
    }

    public function getCompletedAssignments(): Collection
    {
        return $this->assignments->filter(
            fn(UserAssignment $assignment) => $assignment->isComplete()
        );
    }

    public function getTotalPointsEarned(): int
    {
        return array_sum(
            $this->getCompletedAssignments()
                ->map(fn(UserAssignment $assignment) => $assignment->getPointsEarned())
                ->toArray()
        );
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): static
    {
        $this->locale = $locale;
        return $this;
    }
}
