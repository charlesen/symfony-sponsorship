<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Service\ReferralService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ReferralShareCard
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

    public function getReferralUrl(): string
    {
        $user = $this->getUser();
        if (!$user) {
            return '';
        }

        return $this->referralService->getReferralUrl($user, $this->locale);
    }

    /**
     * @return array<string, array{name: string, url: string, iconClass: string, colorClass: string}>
     */
    public function getSocialShares(): array
    {
        $user = $this->getUser();
        if (!$user) {
            return [];
        }

        $refUrl = $this->getReferralUrl();
        $encodedUrl = urlencode($refUrl);
        $twitterText = urlencode($this->referralService->getShareText($user, 'twitter', $this->locale));
        $whatsappText = urlencode($this->referralService->getShareText($user, 'whatsapp', $this->locale));
        $linkedinSummary = urlencode($this->referralService->getShareText($user, 'linkedin', $this->locale));
        $telegramText = urlencode($this->referralService->getShareText($user, 'telegram', $this->locale));
        $emailBody = urlencode($this->referralService->getShareText($user, 'email', $this->locale));

        return [
            'whatsapp' => [
                'name' => 'WhatsApp',
                'url' => sprintf('https://api.whatsapp.com/send?text=%s', $whatsappText),
                'colorClass' => 'bg-emerald-600 hover:bg-emerald-700 text-white',
            ],
            'x' => [
                'name' => 'X (Twitter)',
                'url' => sprintf('https://twitter.com/intent/tweet?text=%s', $twitterText),
                'colorClass' => 'bg-black hover:bg-gray-800 text-white',
            ],
            'linkedin' => [
                'name' => 'LinkedIn',
                'url' => sprintf('https://www.linkedin.com/sharing/share-offsite/?url=%s', $encodedUrl),
                'colorClass' => 'bg-blue-600 hover:bg-blue-700 text-white',
            ],
            'telegram' => [
                'name' => 'Telegram',
                'url' => sprintf('https://t.me/share/url?url=%s&text=%s', $encodedUrl, $telegramText),
                'colorClass' => 'bg-sky-500 hover:bg-sky-600 text-white',
            ],
            'email' => [
                'name' => 'Email',
                'url' => sprintf('mailto:?subject=%s&body=%s', urlencode('Special Invitation Link'), $emailBody),
                'colorClass' => 'bg-gray-700 hover:bg-gray-800 text-white',
            ],
        ];
    }
}
