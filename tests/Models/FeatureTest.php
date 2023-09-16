<?php

namespace Tests\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use LucasDotVin\Soulbscription\Models\Feature;
use Tests\TestCase;

class FeatureTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testModelCalculateYearlyExpiration(): void
    {
        Carbon::setTestNow(now());

        $years   = $this->faker->randomDigitNotNull();
        $feature = Feature::factory()->create([
            'periodicity_type' => PeriodicityType::YEAR,
            'periodicity'      => $years,
        ]);

        $this->assertEquals(now()->addYears($years), $feature->calculateNextRecurrenceEnd());
    }

    public function testModelCalculateMonthlyExpiration(): void
    {
        Carbon::setTestNow(now());

        $months  = $this->faker->randomDigitNotNull();
        $feature = Feature::factory()->create([
            'periodicity_type' => PeriodicityType::MONTH,
            'periodicity'      => $months,
        ]);

        $this->assertEquals(now()->addMonths($months), $feature->calculateNextRecurrenceEnd());
    }

    public function testModelCalculateWeeklyExpiration(): void
    {
        Carbon::setTestNow(now());

        $weeks   = $this->faker->randomDigitNotNull();
        $feature = Feature::factory()->create([
            'periodicity_type' => PeriodicityType::WEEK,
            'periodicity'      => $weeks,
        ]);

        $this->assertEquals(now()->addWeeks($weeks), $feature->calculateNextRecurrenceEnd());
    }

    public function testModelCalculateDailyExpiration(): void
    {
        Carbon::setTestNow(now());

        $days    = $this->faker->randomDigitNotNull();
        $feature = Feature::factory()->create([
            'periodicity_type' => PeriodicityType::DAY,
            'periodicity'      => $days,
        ]);

        $this->assertEquals(now()->addDays($days), $feature->calculateNextRecurrenceEnd());
    }

    public function testModelcalculateNextRecurrenceEndConsideringRecurrences(): void
    {
        Carbon::setTestNow(now());

        $feature = Feature::factory()->create([
            'periodicity_type' => PeriodicityType::WEEK,
            'periodicity'      => 1,
        ]);

        $startDate = now()->subDays(11);

        $this->assertEquals(now()->addDays(3), $feature->calculateNextRecurrenceEnd($startDate));
    }

    public function testModelIsNotQuotaByDefault(): void
    {
        $creationPayload = Feature::factory()->raw();

        unset($creationPayload['quota']);

        $feature = Feature::create($creationPayload);

        $this->assertDatabaseHas('features', [
            'id'    => $feature->id,
            'quota' => false,
        ]);
    }
}
