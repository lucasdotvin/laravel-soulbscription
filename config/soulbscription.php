<?php

return [
    'feature_tickets' => false,

    'models' => [

        'feature' => \LucasDotVin\Soulbscription\Models\Feature::class,

        'feature_consumption' => \LucasDotVin\Soulbscription\Models\FeatureConsumption::class,

        'feature_ticket' => \LucasDotVin\Soulbscription\Models\FeatureTicket::class,

        'feature_plan' => \LucasDotVin\Soulbscription\Models\FeaturePlan::class,

        'plan' => \LucasDotVin\Soulbscription\Models\Plan::class,

        'subscription' => \LucasDotVin\Soulbscription\Models\Subscription::class,

        'subscription_renewal' => \LucasDotVin\Soulbscription\Models\SubscriptionRenewal::class,
    ],
];
