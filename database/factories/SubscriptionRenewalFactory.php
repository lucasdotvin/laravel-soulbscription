<?php

namespace LucasDotVin\Soulbscription\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionRenewalFactory extends Factory
{
    protected $model;

    public function __construct()
    {
        $this->model = config('soulbscription.models.subscription_renewal');
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'subscription_id' => config('soulbscription.models.subscription')::factory(),
            'overdue'         => $this->faker->boolean(),
            'renewal'         => $this->faker->boolean(),
        ];
    }
}
