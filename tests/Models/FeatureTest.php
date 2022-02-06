<?php

namespace LucasDotDev\Soulbscription\Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use LucasDotDev\Soulbscription\Enums\PeriodicityType;
use LucasDotDev\Soulbscription\Models\Feature;
use LucasDotDev\Soulbscription\Tests\TestCase;

class FeatureTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testModelCalculateYearlyExpiration()
    {
        Carbon::setTestNow(now());

        $years = $this->faker->randomDigitNotZero();
        $feature = Feature::factory()->create([
            'periodicity_type' => PeriodicityType::Year->value,
            'periodicity' => $years,
        ]);

        $this->assertEquals(now()->addYears($years), $feature->calculateExpiration());
    }

    public function testModelCalculateMonthlyExpiration()
    {
        Carbon::setTestNow(now());

        $months = $this->faker->randomDigitNotZero();
        $feature = Feature::factory()->create([
            'periodicity_type' => PeriodicityType::Month->value,
            'periodicity' => $months,
        ]);

        $this->assertEquals(now()->addMonths($months), $feature->calculateExpiration());
    }

    public function testModelCalculateWeeklyExpiration()
    {
        Carbon::setTestNow(now());

        $weeks = $this->faker->randomDigitNotZero();
        $feature = Feature::factory()->create([
            'periodicity_type' => PeriodicityType::Week->value,
            'periodicity' => $weeks,
        ]);

        $this->assertEquals(now()->addWeeks($weeks), $feature->calculateExpiration());
    }

    public function testModelCalculateDailyExpiration()
    {
        Carbon::setTestNow(now());

        $days = $this->faker->randomDigitNotZero();
        $feature = Feature::factory()->create([
            'periodicity_type' => PeriodicityType::Day->value,
            'periodicity' => $days,
        ]);

        $this->assertEquals(now()->addDays($days), $feature->calculateExpiration());
    }
}
