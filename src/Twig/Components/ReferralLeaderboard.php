<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ReferralLeaderboard
{
    use DefaultActionTrait;

    #[LiveProp]
    public int $limit = 10;

    #[LiveProp]
    public string $locale = 'en';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly Security $security,
    ) {
    }

    public function getUser(): ?User
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        return $user;
    }

    /**
     * @return array<array{user: User, referralCount: int, points: int, rank: int}>
     */
    public function getLeaderboard(): array
    {
        $raw = $this->userRepository->findTopReferrers($this->limit);
        $results = [];
        $rank = 1;

        foreach ($raw as $row) {
            $user = $row['user'] ?? $row[0] ?? null;
            if ($user instanceof User) {
                $results[] = [
                    'user' => $user,
                    'referralCount' => (int) ($row['referralCount'] ?? 0),
                    'points' => (int) ($row['points'] ?? $user->getPoints()),
                    'rank' => $rank++,
                    'isCurrentUser' => $this->getUser() && $this->getUser()->getId() === $user->getId(),
                ];
            }
        }

        return $results;
    }

    public function getUserRank(): int
    {
        $user = $this->getUser();
        if (!$user) {
            return 0;
        }

        return $this->userRepository->getUserRank($user);
    }

    #[LiveAction]
    public function refresh(): void
    {
        // Triggers reactive reload of leaderboard
    }
}
