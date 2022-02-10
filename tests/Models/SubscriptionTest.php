<?php

namespace LucasDotDev\Soulbscription\Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use LucasDotDev\Soulbscription\Models\Plan;
use LucasDotDev\Soulbscription\Models\Subscription;
use LucasDotDev\Soulbscription\Tests\Mocks\Models\User;
use LucasDotDev\Soulbscription\Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function testModelRenews()
    {
        $plan = Plan::factory()->create();
        $subscriber = User::factory()->create();
        $subscription = Subscription::factory()
            ->for($plan)
            ->for($subscriber, 'subscriber')
            ->create();

        $subscription->renew();

        $this->assertDatabaseHas('subscriptions', [
            'plan_id' => $plan->id,
            'subscriber_id' => $subscriber->id,
            'subscriber_type' => User::class,
            'expires_at' => $plan->calculateNextRecurrenceEnd(),
        ]);
    }

    public function testModelCanStart()
    {
        Carbon::setTestNow(now());

        $plan = Plan::factory()->create();
        $subscriber = User::factory()->create();
        $subscription = Subscription::factory()
            ->for($plan)
            ->for($subscriber, 'subscriber')
            ->notStarted()
            ->create();

        $subscription->start()
            ->save();

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'started_at' => today(),
        ]);
    }

    public function testModelCanSuppress()
    {
        Carbon::setTestNow(now());

        $plan = Plan::factory()->create();
        $subscriber = User::factory()->create();
        $subscription = Subscription::factory()
            ->for($plan)
            ->for($subscriber, 'subscriber')
            ->create();

        $subscription->suppress()
            ->save();

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'suppressed_at' => now(),
        ]);
    }

    public function testModelRegistersRenewal()
    {
        $subscriber = User::factory()->create();
        $subscription = Subscription::factory()
            ->for($subscriber, 'subscriber')
            ->create();

        $subscription->renew();

        $this->assertDatabaseCount('subscription_renewals', 1);
        $this->assertDatabaseHas('subscription_renewals', [
            'subscription_id' => $subscription->id,
            'renewal' => true,
        ]);
    }

    public function testModelRegistersOverdue()
    {
        $subscriber = User::factory()->create();
        $subscription = Subscription::factory()
            ->for($subscriber, 'subscriber')
            ->create([
                'expires_at' => now()->subDay(),
            ]);

        $subscription->renew();

        $this->assertDatabaseCount('subscription_renewals', 1);
        $this->assertDatabaseHas('subscription_renewals', [
            'subscription_id' => $subscription->id,
            'overdue' => true,
        ]);
    }
}
