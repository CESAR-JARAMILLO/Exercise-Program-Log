<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Tiers
    |--------------------------------------------------------------------------
    |
    | Define the subscription tiers available in the application.
    | Each tier includes a display name, maximum programs allowed, and
    | a list of features available to that tier.
    |
    */

    'tiers' => [
        'free' => [
            'name' => 'Free',
            'max_programs' => 1,
            'features' => [
                'basic_programs',
                'personal_stats',
            ],
        ],
        'basic' => [
            'name' => 'Basic',
            'max_programs' => 5,
            'features' => [
                'basic_programs',
                'personal_stats',
                'enhanced_analytics',
            ],
        ],
        'trainer' => [
            'name' => 'Trainer',
            'max_programs' => 20,
            'features' => [
                'basic_programs',
                'personal_stats',
                'enhanced_analytics',
                'share_programs',
                'view_client_analytics',
                'invite_clients',
            ],
        ],
        'pro_trainer' => [
            'name' => 'Pro Trainer',
            'max_programs' => 50,
            'features' => [
                'basic_programs',
                'personal_stats',
                'enhanced_analytics',
                'share_programs',
                'view_client_analytics',
                'invite_clients',
                'unlimited_clients',
                'advanced_analytics',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Access
    |--------------------------------------------------------------------------
    |
    | Define which tiers have access to specific features.
    | This is used for feature gating throughout the application.
    |
    */

    'features' => [
        'share_programs' => ['trainer', 'pro_trainer'],
        'view_client_analytics' => ['trainer', 'pro_trainer'],
        'invite_clients' => ['trainer', 'pro_trainer'],
        'unlimited_clients' => ['pro_trainer'],
        'advanced_analytics' => ['pro_trainer'],
    ],
];

