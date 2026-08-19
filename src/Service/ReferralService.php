<?php

namespace App\Service;

use App\Entity\User;
use App\Enum\RewardTier;
use App\EventListener\ReferralTrackingListener;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ReferralService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Attempts to find a referrer from Request (cookie, session, or query) and attach it to the new user.
     */
    public function processReferral(User $newUser, Request $request): ?User
    {
        $refCode = $request->query->get('ref')
            ?? $request->query->get('r')
            ?? $request->cookies->get(ReferralTrackingListener::COOKIE_NAME)
            ?? ($request->hasSession() ? $request->getSession()->get(ReferralTrackingListener::SESSION_KEY) : null);

        if (!$refCode || !is_string($refCode)) {
            return null;
        }

        $refCode = trim($refCode);
        $referrer = $this->userRepository->findOneBy(['referrerCode' => $refCode]);

        if (!$referrer) {
            $this->logger->info(sprintf('Referral code %s was not found in database.', $refCode));
            return null;
        }

        // Anti-fraud: cannot refer oneself
        if (strtolower($referrer->getEmail()) === strtolower($newUser->getEmail())) {
            $this->logger->warning(sprintf('Self-referral attempt detected for %s', $newUser->getEmail()));
            return null;
        }

        // Attach referrer
        $newUser->setReferrer($referrer);
        $newUser->setRegistrationIp($request->getClientIp());

        // Award welcome points to new user (20 pts) and referrer (50 pts)
        $newUser->addPoints(20);
        $referrer->addPoints(50);
        $referrer->incrementReferralClicks();

        // Check if referrer unlocked a milestone bonus
        $this->checkMilestoneUnlocked($referrer);

        $this->entityManager->flush();

        $this->logger->info(sprintf(
            'User %s successfully attributed to referrer %s (#%s)',
            $newUser->getEmail(),
            $referrer->getEmail(),
            $referrer->getReferrerCode()
        ));

        return $referrer;
    }

    public function getReferralUrl(User $user, ?string $locale = 'en'): string
    {
        return $this->urlGenerator->generate('register', [
            '_locale' => $locale ?: 'en',
            'ref' => $user->getReferrerCode(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function getShareText(User $user, string $platform = 'twitter', string $locale = 'en'): string
    {
        $url = $this->getReferralUrl($user, $locale);

        return match ($platform) {
            'whatsapp' => sprintf("Hey! I just joined this exclusive platform. Use my private invite link to get 20 bonus points: %s", $url),
            'twitter', 'x' => sprintf("🚀 Excited to join this new platform! Claim early access and bonus perks with my invite link: %s #Symfony #DevCommunity", $url),
            'linkedin' => sprintf("I just joined an innovative community platform. Join me using my personal invitation link: %s", $url),
            'telegram' => sprintf("Join me on this awesome platform! Exclusive invite link: %s", $url),
            'email' => sprintf("Subject: Your personal invitation\n\nHi,\n\nI wanted to invite you to check out this platform. Click here to get started with bonus credits: %s", $url),
            default => sprintf("Join me using my personal referral link: %s", $url),
        };
    }

    public function getTierProgress(User $user): array
    {
        $referralCount = $user->getReferralsCount();
        $currentTier = $user->getRewardTier();
        $nextTier = $user->getNextRewardTier();

        if (!$nextTier) {
            return [
                'currentTier' => $currentTier,
                'nextTier' => null,
                'currentCount' => $referralCount,
                'neededCount' => 0,
                'percentage' => 100,
            ];
        }

        $previousRequired = $currentTier ? $currentTier->requiredReferrals() : 0;
        $nextRequired = $nextTier->requiredReferrals();
        $range = max(1, $nextRequired - $previousRequired);
        $progress = max(0, $referralCount - $previousRequired);

        $percentage = (int) min(100, max(0, round(($progress / $range) * 100)));

        return [
            'currentTier' => $currentTier,
            'nextTier' => $nextTier,
            'currentCount' => $referralCount,
            'neededCount' => max(0, $nextRequired - $referralCount),
            'percentage' => $percentage,
        ];
    }

    private function checkMilestoneUnlocked(User $referrer): void
    {
        $count = $referrer->getReferralsCount() + 1; // +1 including current referral
        $tier = RewardTier::getTierForCount($count);

        if ($tier && $count === $tier->requiredReferrals()) {
            $referrer->addPoints($tier->bonusPoints());
            $this->logger->info(sprintf('User %s unlocked milestone %s (+%d bonus points)!', $referrer->getEmail(), $tier->label(), $tier->bonusPoints()));
        }
    }
}
