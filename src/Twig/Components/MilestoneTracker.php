<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Enum\RewardTier;
use App\Service\ReferralService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class MilestoneTracker
{
    use DefaultActionTrait;

    #[LiveProp]
    public string $locale = 'en';

    public function __construct(
        private readonly ReferralService $referralService,
        private readonly Security $security,
    ) {
    }

    public function getUser(): ?User
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        return $user;
    }

    public function getProgress(): array
    {
        $user = $this->getUser();
        if (!$user) {
            return [
                'currentTier' => null,
                'nextTier' => RewardTier::BRONZE,
                'currentCount' => 0,
                'neededCount' => 1,
                'percentage' => 0,
            ];
        }

        return $this->referralService->getTierProgress($user);
    }

    /**
     * @return array<array{tier: RewardTier, isUnlocked: bool, isNext: bool}>
     */
    public function getTiers(): array
    {
        $user = $this->getUser();
        $refCount = $user ? $user->getReferralsCount() : 0;
        $nextTier = $user ? $user->getNextRewardTier() : RewardTier::BRONZE;

        $tiers = [];
        foreach ([RewardTier::BRONZE, RewardTier::SILVER, RewardTier::GOLD, RewardTier::PLATINUM] as $tier) {
            $isUnlocked = $refCount >= $tier->requiredReferrals();
            $isNext = $nextTier === $tier;

            $tiers[] = [
                'tier' => $tier,
                'isUnlocked' => $isUnlocked,
                'isNext' => $isNext,
            ];
        }

        return $tiers;
    }
}
