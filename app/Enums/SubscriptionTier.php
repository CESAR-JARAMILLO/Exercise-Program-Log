<?php

namespace App\Enums;

enum SubscriptionTier: string
{
    case FREE = 'free';
    case BASIC = 'basic';
    case TRAINER = 'trainer';
    case PRO_TRAINER = 'pro_trainer';

    /**
     * Get all tier values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get tier display name
     */
    public function name(): string
    {
        return match($this) {
            self::FREE => 'Free',
            self::BASIC => 'Basic',
            self::TRAINER => 'Trainer',
            self::PRO_TRAINER => 'Pro Trainer',
        };
    }
}

