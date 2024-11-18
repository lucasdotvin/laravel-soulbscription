<?php

namespace LucasDotVin\Soulbscription\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LucasDotVin\Soulbscription\Models\Subscription;
use LucasDotVin\Soulbscription\Models\SubscriptionRenewal;

class SubscriptionRenewalFactory extends Factory
{
    protected $model = SubscriptionRenewal::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'subscription_id' => Subscription::factory(),
            'overdue' => $this->faker->boolean(),
            'renewal' => $this->faker->boolean(),
        ];
    }
}
