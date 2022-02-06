<?php

namespace LucasDotDev\Soulbscription\Tests\Feature\Models;

use LucasDotDev\Soulbscription\Enums\PeriodicityType;
use LucasDotDev\Soulbscription\Models\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use LucasDotDev\Soulbscription\Tests\TestCase;

class FeatureTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testModelCalculateYearlyExpiration()
    {
        Carbon::setTestNow(now());

        $years   = $this->faker->randomDigitNotZero();
        $feature = Feature::factory()->create([
            'periodicity_type' => PeriodicityType::Year->name,
            'periodicity'      => $years,
        ]);

        $this->assertEquals(now()->addYears($years), $feature->calculateNextRecurrenceEnd());
    }

    public function testModelCalculateMonthlyExpiration()
    {
        Carbon::setTestNow(now());

        $months  = $this->faker->randomDigitNotZero();
        $feature = Feature::factory()->create([
            'periodicity_type' => PeriodicityType::Month->name,
            'periodicity'      => $months,
        ]);

        $this->assertEquals(now()->addMonths($months), $feature->calculateNextRecurrenceEnd());
    }

    public function testModelCalculateWeeklyExpiration()
    {
        Carbon::setTestNow(now());

        $weeks   = $this->faker->randomDigitNotZero();
        $feature = Feature::factory()->create([
            'periodicity_type' => PeriodicityType::Week->name,
            'periodicity'      => $weeks,
        ]);

        $this->assertEquals(now()->addWeeks($weeks), $feature->calculateNextRecurrenceEnd());
    }

    public function testModelCalculateDailyExpiration()
    {
        Carbon::setTestNow(now());

        $days    = $this->faker->randomDigitNotZero();
        $feature = Feature::factory()->create([
            'periodicity_type' => PeriodicityType::Day->name,
            'periodicity'      => $days,
        ]);

        $this->assertEquals(now()->addDays($days), $feature->calculateNextRecurrenceEnd());
    }

    public function testModelcalculateNextRecurrenceEndConsideringRecurrences()
    {
        Carbon::setTestNow(now());

        $feature = Feature::factory()->create([
            'periodicity_type' => PeriodicityType::Week->name,
            'periodicity'      => 1,
        ]);

        $startDate = now()->subDays(11);

        $this->assertEquals(now()->addDays(3), $feature->calculateNextRecurrenceEnd($startDate));
    }
}
