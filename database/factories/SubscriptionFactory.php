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
     * @return array{plan_id: int, canceled_at: null,
     *     started_at: \DateTimeInterface, suppressed_at: null,
     *     expired_at: \DateTimeInterface, was_switched: bool,
     *     subscriber_id: int, subscriber_type: string}
     */
    public function definition(): array
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

    public function canceled(): SubscriptionFactory
    {
        return $this->state(fn(array $attributes): array => [
            'canceled_at' => $this->faker->dateTime(),
        ]);
    }

    public function notCanceled(): SubscriptionFactory
    {
        return $this->state(fn(array $attributes): array => [
            'canceled_at' => null,
        ]);
    }

    public function expired(): SubscriptionFactory
    {
        return $this->state(fn(array $attributes): array => [
            'expired_at' => $this->faker->dateTime(),
        ]);
    }

    public function notExpired(): SubscriptionFactory
    {
        return $this->state(fn(array $attributes): array => [
            'expired_at' => now()->addDays($this->faker->randomDigitNotNull()),
        ]);
    }

    public function started(): SubscriptionFactory
    {
        return $this->state(fn(array $attributes): array => [
            'started_at' => $this->faker->dateTime(),
        ]);
    }

    public function notStarted(): SubscriptionFactory
    {
        return $this->state(fn(array $attributes): array => [
            'started_at' => $this->faker->dateTimeBetween('now', '+30 years'),
        ]);
    }

    public function suppressed(): SubscriptionFactory
    {
        return $this->state(fn(array $attributes): array => [
            'suppressed_at' => $this->faker->dateTime(),
        ]);
    }

    public function notSuppressed(): SubscriptionFactory
    {
        return $this->state(fn(array $attributes): array => [
            'suppressed_at' => null,
        ]);
    }

    public function switched(): SubscriptionFactory
    {
        return $this->state(fn(array $attributes): array => [
            'was_switched' => true,
        ]);
    }

    public function notSwitched(): SubscriptionFactory
    {
        return $this->state(fn(array $attributes): array => [
            'was_switched' => false,
        ]);
    }
}
