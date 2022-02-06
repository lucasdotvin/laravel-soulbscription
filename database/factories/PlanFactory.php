<?php

namespace LucasDotDev\Soulbscription\Database\Factories;

use LucasDotDev\Soulbscription\Enums\PeriodicityType;
use Illuminate\Database\Eloquent\Factories\Factory;
use LucasDotDev\Soulbscription\Models\Plan;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
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
}
