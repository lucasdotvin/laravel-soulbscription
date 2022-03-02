<?php

return [
    'feature_tickets' => true,

    'models' => [

        'feature' => \LucasDotDev\Soulbscription\Models\Feature::class,

        'feature_consumption' => \LucasDotDev\Soulbscription\Models\FeatureConsumption::class,

        'feature_ticket' => \LucasDotDev\Soulbscription\Models\FeatureTicket::class,

        'feature_plan' => \LucasDotDev\Soulbscription\Models\FeaturePlan::class,

        'plan' => \LucasDotDev\Soulbscription\Models\Plan::class,

        'subscription' => \LucasDotDev\Soulbscription\Models\Subscription::class,

        'subscription_renewal' => \LucasDotDev\Soulbscription\Models\SubscriptionRenewal::class,
    ],
];
