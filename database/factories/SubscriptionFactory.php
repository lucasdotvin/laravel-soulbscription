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
            'plan_id'       => Plan::factory(),
            'canceled_at'   => null,
            'suppressed_at' => null,
            'expires_at'    => $this->faker->dateTime(),
        ];
    }

    public function canceled()
    {
        return $this->state(fn (array $attributes) => [
            'canceled_at' => $this->faker->dateTime(),
        ]);
    }

    public function uncanceled()
    {
        return $this->state(fn (array $attributes) => [
            'canceled_at' => null,
        ]);
    }

    public function expired()
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTime(),
        ]);
    }

    public function started()
    {
        return $this->state(fn (array $attributes) => [
            'started_at' => $this->faker->dateTime(),
        ]);
    }

    public function notStarted()
    {
        return $this->state(fn (array $attributes) => [
            'started_at' => null,
        ]);
    }

    public function suppressed()
    {
        return $this->state(fn (array $attributes) => [
            'suppressed_at' => $this->faker->dateTime(),
        ]);
    }

    public function unsuppressed()
    {
        return $this->state(fn (array $attributes) => [
            'suppressed_at' => null,
        ]);
    }
}
