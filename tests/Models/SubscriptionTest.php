<?php

namespace LucasDotDev\Soulbscription\Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use LucasDotDev\Soulbscription\Events\SubscriptionCanceled;
use LucasDotDev\Soulbscription\Events\SubscriptionRenewed;
use LucasDotDev\Soulbscription\Events\SubscriptionStarted;
use LucasDotDev\Soulbscription\Events\SubscriptionSuppressed;
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

        $this->expectsEvents(SubscriptionRenewed::class);

        $subscription->renew();

        $this->assertDatabaseHas('subscriptions', [
            'plan_id' => $plan->id,
            'subscriber_id' => $subscriber->id,
            'subscriber_type' => User::class,
            'expired_at' => $plan->calculateNextRecurrenceEnd(),
        ]);
    }

    public function testModelCanCancel()
    {
        Carbon::setTestNow(now());

        $plan = Plan::factory()->create();
        $subscriber = User::factory()->create();
        $subscription = Subscription::factory()
            ->for($plan)
            ->for($subscriber, 'subscriber')
            ->notStarted()
            ->create();

        $this->expectsEvents(SubscriptionCanceled::class);

        $subscription->cancel();

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'canceled_at' => now(),
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

        $this->expectsEvents(SubscriptionStarted::class);

        $subscription->start();

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

        $this->expectsEvents(SubscriptionSuppressed::class);

        $subscription->suppress();

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'suppressed_at' => now(),
        ]);
    }

    public function testModelCanMarkAsSwitched()
    {
        $plan = Plan::factory()->create();
        $subscriber = User::factory()->create();
        $subscription = Subscription::factory()
            ->for($plan)
            ->for($subscriber, 'subscriber')
            ->create();

        $subscription->markAsSwitched()
            ->save();

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'was_switched' => true,
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
                'expired_at' => now()->subDay(),
            ]);

        $subscription->renew();

        $this->assertDatabaseCount('subscription_renewals', 1);
        $this->assertDatabaseHas('subscription_renewals', [
            'subscription_id' => $subscription->id,
            'overdue' => true,
        ]);
    }

    public function testModelConsidersGraceDaysOnOverdue()
    {
        $subscriber = User::factory()->create();
        $subscription = Subscription::factory()
            ->for($subscriber, 'subscriber')
            ->create([
                'grace_days_ended_at' => now()->addDay(),
                'expired_at' => now()->subDay(),
            ]);

        $subscription->renew();

        $this->assertDatabaseCount('subscription_renewals', 1);
        $this->assertDatabaseHas('subscription_renewals', [
            'subscription_id' => $subscription->id,
            'overdue' => false,
        ]);
    }
}
