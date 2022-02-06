<?php

namespace LucasDotDev\Soulbscription\Database\Factories;

use LucasDotDev\Soulbscription\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use LucasDotDev\Soulbscription\Models\Subscription;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'plan_id'     => Plan::factory(),
            'canceled_at' => $this->faker->dateTime(),
            'expires_at'  => $this->faker->dateTime(),
        ];
    }

    public function canceled()
    {
        return $this->state(fn (array $attributes) => [
            'canceled_at' => $this->faker->dateTime(),
        ]);
    }

    public function expired()
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTime(),
        ]);
    }

    public function uncanceled()
    {
        return $this->state(fn (array $attributes) => [
            'canceled_at' => null,
        ]);
    }

    public function unexpired()
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => null,
        ]);
    }
}
