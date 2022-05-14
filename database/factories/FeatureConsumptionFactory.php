<?php

namespace LucasDotVin\Soulbscription\Database\Factories;

use LucasDotVin\Soulbscription\Models\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;
use LucasDotVin\Soulbscription\Models\FeatureConsumption;

class FeatureConsumptionFactory extends Factory
{
    protected $model = FeatureConsumption::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'feature_id'      => Feature::factory(),
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
