<?php

namespace LucasDotVin\Soulbscription\Database\Factories;

use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use Illuminate\Database\Eloquent\Factories\Factory;
use LucasDotVin\Soulbscription\Models\Plan;

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
            'grace_days'       => 0,
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

    public function withGraceDays()
    {
        return $this->state([
            'grace_days' => $this->faker->randomDigitNotNull(),
        ]);
    }
}
