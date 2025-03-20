<?php

namespace Tests\Feature\Models;

use Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;

class PlanTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testModelCancalculateGraceDaysEnd()
    {
        Carbon::setTestNow(now());

        $days = $this->faker->randomDigitNotNull();
        $graceDays = $this->faker->randomDigitNotNull();
        $plan = config('soulbscription.models.plan')::factory()->create([
            'grace_days' => $graceDays,
            'periodicity_type' => PeriodicityType::Day,
            'periodicity' => $days,
        ]);

        $this->assertEquals(
            now()->addDays($days)->addDays($graceDays),
            $plan->calculateGraceDaysEnd($plan->calculateNextRecurrenceEnd()),
        );
    }

    public function testModelCanRetrieveSubscriptions()
    {
        $plan = config('soulbscription.models.plan')::factory()
            ->create();

        $subscriptions = config('soulbscription.models.subscription')::factory()
            ->for($plan)
            ->count($subscriptionsCount = $this->faker->randomDigitNotNull())
            ->started()
            ->notExpired()
            ->notSuppressed()
            ->create();

        $this->assertEquals($subscriptionsCount, $plan->subscriptions()->count());
        $subscriptions->each(function ($subscription) use ($plan) {
            $this->assertTrue($plan->subscriptions->contains($subscription));
        });
    }

    public function testPlanCanBeCreatedWithoutPeriodicity()
    {
        $plan = config('soulbscription.models.plan')::factory()
            ->create([
                'periodicity' => null,
                'periodicity_type' => null,
            ]);

        $this->assertNull($plan->periodicity);
        $this->assertNull($plan->periodicity_type);
    }
}
