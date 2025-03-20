<?php

namespace LucasDotVin\Soulbscription\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
class FeatureConsumptionFactory extends Factory
{
    protected $model;

    public function __construct()
    {
        $this->model = config('soulbscription.models.feature_consumption');
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'feature_id'      => config('soulbscription.models.feature')::factory(),
            'consumption'     => $this->faker->randomFloat(),
            'expired_at'      => $this->faker->dateTime(),
            'subscriber_id'   => $this->faker->randomNumber(),
            'subscriber_type' => $this->faker->word(),
        ];
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
}
