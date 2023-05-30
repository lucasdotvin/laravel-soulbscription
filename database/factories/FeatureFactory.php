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
     * @return array{consumable: bool, name: string, periodicity: int|null,
     *      periodicity_type: string|null, quota: bool, postpaid: bool}
     */
    public function definition(): array
    {
        return [
            'consumable'       => $this->faker->boolean(),
            'name'             => $this->faker->words(asText: true),
            'periodicity'      => $this->faker->randomDigitNotNull(),
            'periodicity_type' => $this->faker->randomElement([
                PeriodicityType::YEAR,
                PeriodicityType::MONTH,
                PeriodicityType::WEEK,
                PeriodicityType::DAY,
            ]),
            'quota'            => false,
            'postpaid'         => false,
        ];
    }

    public function consumable(): FeatureFactory
    {
        return $this->state(fn(array $attributes): array => [
            'consumable' => true,
        ]);
    }

    public function notConsumable(): FeatureFactory
    {
        return $this->state(fn(array $attributes): array => [
            'quota'            => false,
            'consumable'       => false,
            'periodicity'      => null,
            'periodicity_type' => null,
        ]);
    }

    public function quota(): FeatureFactory
    {
        return $this->state(fn(array $attributes): array => [
            'consumable'       => true,
            'quota'            => true,
            'periodicity'      => null,
            'periodicity_type' => null,
        ]);
    }

    public function notQuota(): FeatureFactory
    {
        return $this->state(fn(array $attributes): array => [
            'quota' => false,
        ]);
    }

    public function postpaid(): FeatureFactory
    {
        return $this->state(fn(array $attributes): array => [
            'postpaid' => true,
        ]);
    }

    public function prepaid(): FeatureFactory
    {
        return $this->state(fn(array $attributes): array => [
            'postpaid' => false,
        ]);
    }
}
