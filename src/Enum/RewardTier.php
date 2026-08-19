<?php

namespace App\Enum;

enum RewardTier: string
{
    case BRONZE = 'bronze';
    case SILVER = 'silver';
    case GOLD = 'gold';
    case PLATINUM = 'platinum';

    public function label(): string
    {
        return match ($this) {
            self::BRONZE => 'Bronze Supporter',
            self::SILVER => 'Silver Champion',
            self::GOLD => 'Gold Ambassador',
            self::PLATINUM => 'Platinum VIP',
        };
    }

    public function requiredReferrals(): int
    {
        return match ($this) {
            self::BRONZE => 1,
            self::SILVER => 3,
            self::GOLD => 5,
            self::PLATINUM => 10,
        };
    }

    public function bonusPoints(): int
    {
        return match ($this) {
            self::BRONZE => 50,
            self::SILVER => 200,
            self::GOLD => 500,
            self::PLATINUM => 1500,
        };
    }

    public function perkDescription(): string
    {
        return match ($this) {
            self::BRONZE => 'Early beta access & Community Supporter badge.',
            self::SILVER => '10% Discount Promo Code on all subscription plans.',
            self::GOLD => '1 Month Free PRO access & exclusive Discord role.',
            self::PLATINUM => 'Exclusive swag pack & lifetime VIP affiliate bonus.',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::BRONZE => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-700',
            self::SILVER => 'bg-slate-100 text-slate-800 border-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-600',
            self::GOLD => 'bg-yellow-100 text-yellow-800 border-yellow-300 dark:bg-yellow-900/30 dark:text-yellow-300 dark:border-yellow-700',
            self::PLATINUM => 'bg-purple-100 text-purple-800 border-purple-300 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-700',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::BRONZE => '🥉',
            self::SILVER => '🥈',
            self::GOLD => '🥇',
            self::PLATINUM => '👑',
        };
    }

    public static function getTierForCount(int $referralsCount): ?self
    {
        if ($referralsCount >= self::PLATINUM->requiredReferrals()) {
            return self::PLATINUM;
        }
        if ($referralsCount >= self::GOLD->requiredReferrals()) {
            return self::GOLD;
        }
        if ($referralsCount >= self::SILVER->requiredReferrals()) {
            return self::SILVER;
        }
        if ($referralsCount >= self::BRONZE->requiredReferrals()) {
            return self::BRONZE;
        }

        return null;
    }

    public static function getNextTier(int $referralsCount): ?self
    {
        if ($referralsCount < self::BRONZE->requiredReferrals()) {
            return self::BRONZE;
        }
        if ($referralsCount < self::SILVER->requiredReferrals()) {
            return self::SILVER;
        }
        if ($referralsCount < self::GOLD->requiredReferrals()) {
            return self::GOLD;
        }
        if ($referralsCount < self::PLATINUM->requiredReferrals()) {
            return self::PLATINUM;
        }

        return null;
    }
}
