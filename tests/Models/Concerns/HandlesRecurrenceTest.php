<?php

namespace Tests\Models\Concerns;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use LucasDotVin\Soulbscription\Models\Plan;
use Tests\TestCase;

class HandlesRecurrenceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public const MODEL = Plan::class;

    public function testModelCalculateYearlyExpiration(): void
    {
        Carbon::setTestNow(now());

        $years = $this->faker->randomDigitNotNull();
        $plan  = self::MODEL::factory()->create([
            'periodicity_type' => PeriodicityType::YEAR,
            'periodicity'      => $years,
        ]);

        $this->assertEquals(now()->addYears($years), $plan->calculateNextRecurrenceEnd());
    }

    public function testModelCalculateMonthlyExpiration(): void
    {
        Carbon::setTestNow(now());

        $months = $this->faker->randomDigitNotNull();
        $plan   = self::MODEL::factory()->create([
            'periodicity_type' => PeriodicityType::MONTH,
            'periodicity'      => $months,
        ]);

        $this->assertEquals(now()->addMonths($months), $plan->calculateNextRecurrenceEnd());
    }

    public function testModelCalculateWeeklyExpiration(): void
    {
        Carbon::setTestNow(now());

        $weeks = $this->faker->randomDigitNotNull();
        $plan  = self::MODEL::factory()->create([
            'periodicity_type' => PeriodicityType::WEEK,
            'periodicity'      => $weeks,
        ]);

        $this->assertEquals(now()->addWeeks($weeks), $plan->calculateNextRecurrenceEnd());
    }

    public function testModelCalculateDailyExpiration(): void
    {
        Carbon::setTestNow(now());

        $days = $this->faker->randomDigitNotNull();
        $plan = self::MODEL::factory()->create([
            'periodicity_type' => PeriodicityType::DAY,
            'periodicity'      => $days,
        ]);

        $this->assertEquals(now()->addDays($days), $plan->calculateNextRecurrenceEnd());
    }

    public function testModelCalculateExpirationWithADifferentStart(): void
    {
        Carbon::setTestNow(now());

        $weeks = $this->faker->randomDigitNotNull();
        $plan  = self::MODEL::factory()->create([
            'periodicity_type' => PeriodicityType::WEEK,
            'periodicity'      => $weeks,
        ]);

        $start = now()->subDay();

        $this->assertEquals($start->copy()->addWeeks($weeks), $plan->calculateNextRecurrenceEnd($start));
    }

    public function testModelCalculateExpirationWithADifferentStartAsString(): void
    {
        Carbon::setTestNow(today());

        $weeks = $this->faker->randomDigitNotNull();
        $plan  = self::MODEL::factory()->create([
            'periodicity_type' => PeriodicityType::WEEK,
            'periodicity'      => $weeks,
        ]);

        $start = today()->subDay();

        $this->assertEquals(
            $start->copy()->addWeeks($weeks),
            $plan->calculateNextRecurrenceEnd($start->toDateTimeString()),
        );
    }
}
