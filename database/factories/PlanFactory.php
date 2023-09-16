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
     * @return array{grace_days: int, name: string, periodicity: int, periodicity_type: string}
     */
    public function definition(): array
    {
        return [
            'grace_days'       => 0,
            'name'             => $this->faker->words(asText: true),
            'periodicity'      => $this->faker->randomDigitNotNull(),
            'periodicity_type' => $this->faker->randomElement([
                PeriodicityType::YEAR,
                PeriodicityType::MONTH,
                PeriodicityType::WEEK,
                PeriodicityType::DAY,
            ]),
        ];
    }

    public function withGraceDays(): PlanFactory
    {
        return $this->state([
            'grace_days' => $this->faker->randomDigitNotNull(),
        ]);
    }
}
