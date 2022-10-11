<?php

return [
    'database' => [
        'cancel_migrations_autoloading' => false,
    ],

    'feature_tickets' => env('SOULBSCRIPTION_FEATURE_TICKETS', false),

    'models' => [

        'feature' => \LucasDotVin\Soulbscription\Models\Feature::class,

        'feature_consumption' => \LucasDotVin\Soulbscription\Models\FeatureConsumption::class,

        'feature_ticket' => \LucasDotVin\Soulbscription\Models\FeatureTicket::class,

        'feature_plan' => \LucasDotVin\Soulbscription\Models\FeaturePlan::class,

        'plan' => \LucasDotVin\Soulbscription\Models\Plan::class,

        'subscriber' => [
            'uses_uuid' => env('SOULBSCRIPTION_SUBSCRIBER_USES_UUID', false),
        ],

        'subscription' => \LucasDotVin\Soulbscription\Models\Subscription::class,

        'subscription_renewal' => \LucasDotVin\Soulbscription\Models\SubscriptionRenewal::class,
    ],
];
