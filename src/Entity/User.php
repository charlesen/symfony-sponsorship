<?php

namespace App\Entity;

use App\Enum\RewardTier;
use App\Repository\UserRepository;
use App\Trait\Timestampable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_REFERRER_CODE', fields: ['referrerCode'])]
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

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 255, unique: true)]
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
    private ?string $locale = 'en';

    #[ORM\Column(options: ['default' => 0])]
    private int $points = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $referralClicks = 0;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $registrationIp = null;

    /**
     * @var Collection<int, UserAssignment>
     */
    #[ORM\OneToMany(targetEntity: UserAssignment::class, mappedBy: 'user')]
    private Collection $assignments;

    public function __construct()
    {
        $this->referrals = new ArrayCollection();
        $this->assignments = new ArrayCollection();
        if ($this->referrerCode === null) {
            $this->referrerCode = substr(bin2hex(random_bytes(6)), 0, 8);
        }
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
        // No sensitive credentials stored with magic link auth
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return null;
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

    public function getDisplayName(): string
    {
        if ($this->firstname) {
            return $this->firstname . ($this->lastname ? ' ' . mb_substr($this->lastname, 0, 1) . '.' : '');
        }

        return explode('@', $this->email)[0];
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

    public function getReferralsCount(): int
    {
        return $this->referrals->count();
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
            if ($referral->getReferrer() === $this) {
                $referral->setReferrer(null);
            }
        }

        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;

        return $this;
    }

    public function addPoints(int $points): static
    {
        $this->points += $points;

        return $this;
    }

    public function getReferralClicks(): int
    {
        return $this->referralClicks;
    }

    public function setReferralClicks(int $referralClicks): static
    {
        $this->referralClicks = $referralClicks;

        return $this;
    }

    public function incrementReferralClicks(): static
    {
        $this->referralClicks++;

        return $this;
    }

    public function getRegistrationIp(): ?string
    {
        return $this->registrationIp;
    }

    public function setRegistrationIp(?string $registrationIp): static
    {
        $this->registrationIp = $registrationIp;

        return $this;
    }

    public function getRewardTier(): ?RewardTier
    {
        return RewardTier::getTierForCount($this->getReferralsCount());
    }

    public function getNextRewardTier(): ?RewardTier
    {
        return RewardTier::getNextTier($this->getReferralsCount());
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
        $assignmentPoints = array_sum(
            $this->getCompletedAssignments()
                ->map(fn(UserAssignment $assignment) => $assignment->getPointsEarned())
                ->toArray()
        );

        return $this->points + $assignmentPoints;
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
