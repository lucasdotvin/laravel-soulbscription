<?php

namespace Tests\Feature\Models\Concerns;

use Tests\TestCase;
use InvalidArgumentException;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;

class HandlesRecurrenceTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public const MODEL = 'soulbscription.models.plan';

    protected function getModelClass()
    {
        $modelClass = config(self::MODEL);

        throw_if(!is_a($modelClass, Model::class, true), new InvalidArgumentException(
            "Configured plan model must be a subclass of " . Model::class
        ));

        return $modelClass;
    }

    public function testModelCalculateYearlyExpiration()
    {
        Carbon::setTestNow(now());

        $years = $this->faker->randomDigitNotNull();
        $modelClass = $this->getModelClass();
        $plan = $modelClass::factory()->create([
            'periodicity_type' => PeriodicityType::Year,
            'periodicity' => $years,
        ]);

        $this->assertEquals(now()->addYears($years), $plan->calculateNextRecurrenceEnd());
    }

    public function testModelCalculateMonthlyExpiration()
    {
        Carbon::setTestNow(now());

        $months = $this->faker->randomDigitNotNull();
        $modelClass = $this->getModelClass();
        $plan = $modelClass::factory()->create([
            'periodicity_type' => PeriodicityType::Month,
            'periodicity' => $months,
        ]);

        $this->assertEquals(now()->addMonths($months), $plan->calculateNextRecurrenceEnd());
    }

    public function testModelCalculateWeeklyExpiration()
    {
        Carbon::setTestNow(now());

        $weeks = $this->faker->randomDigitNotNull();
        $modelClass = $this->getModelClass();
        $plan = $modelClass::factory()->create([
            'periodicity_type' => PeriodicityType::Week,
            'periodicity' => $weeks,
        ]);

        $this->assertEquals(now()->addWeeks($weeks), $plan->calculateNextRecurrenceEnd());
    }

    public function testModelCalculateDailyExpiration()
    {
        Carbon::setTestNow(now());

        $days = $this->faker->randomDigitNotNull();
        $modelClass = $this->getModelClass();
        $plan = $modelClass::factory()->create([
            'periodicity_type' => PeriodicityType::Day,
            'periodicity' => $days,
        ]);

        $this->assertEquals(now()->addDays($days), $plan->calculateNextRecurrenceEnd());
    }

    public function testModelCalculateExpirationWithADifferentStart()
    {
        Carbon::setTestNow(now());

        $weeks = $this->faker->randomDigitNotNull();
        $modelClass = $this->getModelClass();
        $plan = $modelClass::factory()->create([
            'periodicity_type' => PeriodicityType::Week,
            'periodicity' => $weeks,
        ]);

        $start = now()->subDay();

        $this->assertEquals($start->copy()->addWeeks($weeks), $plan->calculateNextRecurrenceEnd($start));
    }

    public function testModelCalculateExpirationWithADifferentStartAsString()
    {
        Carbon::setTestNow(today());

        $weeks = $this->faker->randomDigitNotNull();
        $modelClass = $this->getModelClass();
        $plan = $modelClass::factory()->create([
            'periodicity_type' => PeriodicityType::Week,
            'periodicity' => $weeks,
        ]);

        $start = today()->subDay();

        $this->assertEquals(
            $start->copy()->addWeeks($weeks),
            $plan->calculateNextRecurrenceEnd($start->toDateTimeString())
        );
    }

    public function testModelCalculateExpirationWithRenewalAfterOneMonth()
    {
        Carbon::setTestNow('2021-02-18');

        $plan = self::MODEL::factory()->create([
            'periodicity_type' => PeriodicityType::Month,
            'periodicity' => 1,
        ]);

        $start = '2021-02-20';

        $this->assertEquals('2021-03-20', $plan->calculateNextRecurrenceEnd($start)->toDateString());
    }

    public function testModelCalculateExpirationWithTwoRenewalsInOneMonth()
    {
        Carbon::setTestNow('2021-02-19');

        $plan = self::MODEL::factory()->create([
            'periodicity_type' => PeriodicityType::Month,
            'periodicity' => 1,
        ]);

        $start = '2021-03-20';

        $this->assertEquals('2021-04-20', $plan->calculateNextRecurrenceEnd($start)->toDateString());
    }

    public function testModelCalculateExpirationWithThreeRenewalsInOneMonth()
    {
        Carbon::setTestNow('2021-02-20');

        $plan = self::MODEL::factory()->create([
            'periodicity_type' => PeriodicityType::Month,
            'periodicity' => 1,
        ]);

        $start = '2021-04-20';

        $this->assertEquals('2021-05-20', $plan->calculateNextRecurrenceEnd($start)->toDateString());
    }

    public function testModelCalculateExpirationWithRenewalAfterExpiration()
    {
        Carbon::setTestNow('2021-02-21');

        $plan = self::MODEL::factory()->create([
            'periodicity_type' => PeriodicityType::Month,
            'periodicity' => 1,
        ]);

        $start = '2021-02-20';

        $this->assertEquals('2021-03-20', $plan->calculateNextRecurrenceEnd($start)->toDateString());
    }
}
