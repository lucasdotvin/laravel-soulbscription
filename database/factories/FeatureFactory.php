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
            'slug'             => $this->faker->slug(),
            'periodicity'      => $this->faker->randomDigitNotZero(),
            'periodicity_type' => $this->faker->randomElement([
                PeriodicityType::Year->name,
                PeriodicityType::Month->name,
                PeriodicityType::Week->name,
                PeriodicityType::Day->name,
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
        ]);
    }
}
