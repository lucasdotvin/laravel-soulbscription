<?php

namespace LucasDotDev\Soulbscription\Tests\Feature\Models;

use LucasDotDev\Soulbscription\Enums\PeriodicityType;
use LucasDotDev\Soulbscription\Models\Plan;
use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use Illuminate\Support\Carbon;
use LucasDotDev\Soulbscription\Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testModelCalculateYearlyExpiration()
    {
        Carbon::setTestNow(now());

        $years = $this->faker->randomDigitNotZero();
        $plan  = Plan::factory()->create([
            'periodicity_type' => PeriodicityType::Year->value,
            'periodicity'      => $years,
        ]);

        $this->assertEquals(now()->addYears($years), $plan->calculateExpiration());
    }

    public function testModelCalculateMonthlyExpiration()
    {
        Carbon::setTestNow(now());

        $months = $this->faker->randomDigitNotZero();
        $plan   = Plan::factory()->create([
            'periodicity_type' => PeriodicityType::Month->value,
            'periodicity'      => $months,
        ]);

        $this->assertEquals(now()->addMonths($months), $plan->calculateExpiration());
    }

    public function testModelCalculateWeeklyExpiration()
    {
        Carbon::setTestNow(now());

        $weeks = $this->faker->randomDigitNotZero();
        $plan  = Plan::factory()->create([
            'periodicity_type' => PeriodicityType::Week->value,
            'periodicity'      => $weeks,
        ]);

        $this->assertEquals(now()->addWeeks($weeks), $plan->calculateExpiration());
    }

    public function testModelCalculateDailyExpiration()
    {
        Carbon::setTestNow(now());

        $days = $this->faker->randomDigitNotZero();
        $plan = Plan::factory()->create([
            'periodicity_type' => PeriodicityType::Day->value,
            'periodicity'      => $days,
        ]);

        $this->assertEquals(now()->addDays($days), $plan->calculateExpiration());
    }
}
