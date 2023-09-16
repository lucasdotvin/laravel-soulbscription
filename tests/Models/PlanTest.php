<?php

namespace Tests\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use LucasDotVin\Soulbscription\Models\Plan;
use LucasDotVin\Soulbscription\Models\Subscription;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testModelCancalculateGraceDaysEnd(): void
    {
        Carbon::setTestNow(now());

        $days      = $this->faker->randomDigitNotNull();
        $graceDays = $this->faker->randomDigitNotNull();
        $plan      = Plan::factory()->create([
            'grace_days'       => $graceDays,
            'periodicity_type' => PeriodicityType::DAY,
            'periodicity'      => $days,
        ]);

        $this->assertEquals(
            now()->addDays($days)->addDays($graceDays),
            $plan->calculateGraceDaysEnd($plan->calculateNextRecurrenceEnd()),
        );
    }

    public function testModelCanRetrieveSubscriptions(): void
    {
        $plan = Plan::factory()
            ->create();

        $subscriptions = Subscription::factory()
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
}
