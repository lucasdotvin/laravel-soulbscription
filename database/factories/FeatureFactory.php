<?php

namespace LucasDotDev\Soulbscription\Database\Factories;

use LucasDotDev\Soulbscription\Enums\PeriodicityType;
use Illuminate\Database\Eloquent\Factories\Factory;
use LucasDotDev\Soulbscription\Models\Feature;

class FeatureFactory extends Factory
{
    protected $model = Feature::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'consumable'       => $this->faker->boolean(),
            'name'             => $this->faker->words(asText: true),
            'periodicity'      => $this->faker->randomDigitNotNull(),
            'periodicity_type' => $this->faker->randomElement([
                PeriodicityType::Year,
                PeriodicityType::Month,
                PeriodicityType::Week,
                PeriodicityType::Day,
            ]),
        ];
    }

    public function consumable()
    {
        return $this->state(fn (array $attributes) => [
            'consumable' => true,
        ]);
    }

    public function notConsumable()
    {
        return $this->state(fn (array $attributes) => [
            'consumable' => false,
            'periodicity' => null,
            'periodicity_type' => null,
        ]);
    }
}
