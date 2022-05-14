<?php

namespace LucasDotVin\Soulbscription\Database\Factories;

use LucasDotVin\Soulbscription\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use LucasDotVin\Soulbscription\Models\Subscription;

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
            'plan_id'         => Plan::factory(),
            'canceled_at'     => null,
            'started_at'      => $this->faker->dateTime(),
            'suppressed_at'   => null,
            'expired_at'      => $this->faker->dateTime(),
            'was_switched'    => false,
            'subscriber_id'   => $this->faker->randomNumber(),
            'subscriber_type' => $this->faker->word(),
        ];
    }

    public function canceled()
    {
        return $this->state(fn (array $attributes) => [
            'canceled_at' => $this->faker->dateTime(),
        ]);
    }

    public function notCanceled()
    {
        return $this->state(fn (array $attributes) => [
            'canceled_at' => null,
        ]);
    }

    public function expired()
    {
        return $this->state(fn (array $attributes) => [
            'expired_at' => $this->faker->dateTime(),
        ]);
    }

    public function notExpired()
    {
        return $this->state(fn (array $attributes) => [
            'expired_at' => now()->addDays($this->faker->randomDigitNotNull()),
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
            'started_at' => $this->faker->dateTimeBetween('now', '+30 years'),
        ]);
    }

    public function suppressed()
    {
        return $this->state(fn (array $attributes) => [
            'suppressed_at' => $this->faker->dateTime(),
        ]);
    }

    public function notSuppressed()
    {
        return $this->state(fn (array $attributes) => [
            'suppressed_at' => null,
        ]);
    }

    public function switched()
    {
        return $this->state(fn (array $attributes) => [
            'was_switched' => true,
        ]);
    }

    public function notSwitched()
    {
        return $this->state(fn (array $attributes) => [
            'was_switched' => false,
        ]);
    }
}
