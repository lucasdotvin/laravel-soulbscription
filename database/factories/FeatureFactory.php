<?php

namespace LucasDotVin\Soulbscription\Database\Factories;

use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use Illuminate\Database\Eloquent\Factories\Factory;
use LucasDotVin\Soulbscription\Models\Feature;

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
            'quote'            => false,
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
            'quote' => false,
            'consumable' => false,
            'periodicity' => null,
            'periodicity_type' => null,
        ]);
    }

    public function quote()
    {
        return $this->state(fn (array $attributes) => [
            'consumable' => true,
            'quote' => true,
            'periodicity' => null,
            'periodicity_type' => null,
        ]);
    }

    public function notQuote()
    {
        return $this->state(fn (array $attributes) => [
            'quote' => false,
        ]);
    }
}
