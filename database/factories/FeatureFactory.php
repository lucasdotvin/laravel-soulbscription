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
            'quota'            => false,
            'postpaid'         => false,
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
            'quota' => false,
            'consumable' => false,
            'periodicity' => null,
            'periodicity_type' => null,
        ]);
    }

    public function quota()
    {
        return $this->state(fn (array $attributes) => [
            'consumable' => true,
            'quota' => true,
            'periodicity' => null,
            'periodicity_type' => null,
        ]);
    }

    public function notQuota()
    {
        return $this->state(fn (array $attributes) => [
            'quota' => false,
        ]);
    }

    public function postpaid()
    {
        return $this->state(fn (array $attributes) => [
            'postpaid' => true,
        ]);
    }

    public function prepaid()
    {
        return $this->state(fn (array $attributes) => [
            'postpaid' => false,
        ]);
    }
}
