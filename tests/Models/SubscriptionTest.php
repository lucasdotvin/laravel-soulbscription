<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use LucasDotVin\Soulbscription\Events\SubscriptionCanceled;
use LucasDotVin\Soulbscription\Events\SubscriptionRenewed;
use LucasDotVin\Soulbscription\Events\SubscriptionStarted;
use LucasDotVin\Soulbscription\Events\SubscriptionSuppressed;
use LucasDotVin\Soulbscription\Models\Plan;
use LucasDotVin\Soulbscription\Models\Subscription;
use Tests\Mocks\Models\User;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testModelRenews()
    {
        Carbon::setTestNow(now());

        $plan = Plan::factory()->create();
        $subscriber = User::factory()->create();
        $subscription = Subscription::factory()
            ->for($plan)
            ->for($subscriber, 'subscriber')
            ->create([
                'expired_at' => now()->addDays(1),
            ]);

        $expectedExpiredAt = $plan->calculateNextRecurrenceEnd($subscription->expired_at)->toDateTimeString();

        Event::fake();

        $subscription->renew();

        Event::assertDispatched(SubscriptionRenewed::class);

        $this->assertDatabaseHas('subscriptions', [
            'plan_id' => $plan->id,
            'subscriber_id' => $subscriber->id,
            'subscriber_type' => User::class,
            'expired_at' => $expectedExpiredAt,
        ]);
    }

    public function testModelRenewsBasedOnCurrentDateIfOverdue()
    {
        Carbon::setTestNow(now());

        $plan = Plan::factory()->create();
        $subscriber = User::factory()->create();
        $subscription = Subscription::factory()
            ->for($plan)
            ->for($subscriber, 'subscriber')
            ->create([
                'expired_at' => now()->subDay(),
            ]);

        $expectedExpiredAt = $plan->calculateNextRecurrenceEnd()->toDateTimeString();

        Event::fake();

        $subscription->renew();

        Event::assertDispatched(SubscriptionRenewed::class);

        $this->assertDatabaseHas('subscriptions', [
            'plan_id' => $plan->id,
            'subscriber_id' => $subscriber->id,
            'subscriber_type' => User::class,
            'expired_at' => $expectedExpiredAt,
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

        Event::fake();

        $subscription->cancel();

        Event::assertDispatched(SubscriptionCanceled::class);

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

        Event::fake();

        $subscription->start();

        Event::assertDispatched(SubscriptionStarted::class);

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

        Event::fake();

        $subscription->suppress();

        Event::assertDispatched(SubscriptionSuppressed::class);

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

    public function testModelReturnsNotStartedSubscriptionsInNotActiveScope()
    {
        Subscription::factory()
            ->count($this->faker()->randomDigitNotNull())
            ->started()
            ->notExpired()
            ->notSuppressed()
            ->create();

        $notStartedSubscription = Subscription::factory()
            ->count($notStartedSubscriptionCount = $this->faker()->randomDigitNotNull())
            ->notStarted()
            ->notExpired()
            ->notSuppressed()
            ->create();

        $returnedSubscriptions = Subscription::notActive()->get();

        $this->assertCount($notStartedSubscriptionCount, $returnedSubscriptions);
        $notStartedSubscription->each(
            fn ($subscription) => $this->assertContains($subscription->id, $returnedSubscriptions->pluck('id'))
        );
    }

    public function testModelReturnsExpiredSubscriptionsInNotActiveScope()
    {
        Subscription::factory()
            ->count($this->faker()->randomDigitNotNull())
            ->started()
            ->notExpired()
            ->notSuppressed()
            ->create();

        $expiredSubscription = Subscription::factory()
            ->count($expiredSubscriptionCount = $this->faker()->randomDigitNotNull())
            ->started()
            ->expired()
            ->notSuppressed()
            ->create();

        $returnedSubscriptions = Subscription::notActive()->get();

        $this->assertCount($expiredSubscriptionCount, $returnedSubscriptions);
        $expiredSubscription->each(
            fn ($subscription) => $this->assertContains($subscription->id, $returnedSubscriptions->pluck('id'))
        );
    }

    public function testModelReturnsSuppressedSubscriptionsInNotActiveScope()
    {
        Subscription::factory()
            ->count($this->faker()->randomDigitNotNull())
            ->started()
            ->notExpired()
            ->notSuppressed()
            ->create();

        $suppressedSubscription = Subscription::factory()
            ->count($suppressedSubscriptionCount = $this->faker()->randomDigitNotNull())
            ->started()
            ->notExpired()
            ->suppressed()
            ->create();

        $returnedSubscriptions = Subscription::notActive()->get();

        $this->assertCount($suppressedSubscriptionCount, $returnedSubscriptions);
        $suppressedSubscription->each(
            fn ($subscription) => $this->assertContains($subscription->id, $returnedSubscriptions->pluck('id'))
        );
    }

    public function testModelReturnsOnlyCanceledSubscriptionsWithTheScope()
    {
        Subscription::factory()
            ->count($this->faker()->randomDigitNotNull())
            ->started()
            ->notExpired()
            ->notSuppressed()
            ->notCanceled()
            ->create();

        $canceledSubscription = Subscription::factory()
            ->count($canceledSubscriptionCount = $this->faker()->randomDigitNotNull())
            ->started()
            ->notExpired()
            ->notSuppressed()
            ->canceled()
            ->create();

        $returnedSubscriptions = Subscription::canceled()->get();

        $this->assertCount($canceledSubscriptionCount, $returnedSubscriptions);
        $canceledSubscription->each(
            fn ($subscription) => $this->assertContains($subscription->id, $returnedSubscriptions->pluck('id'))
        );
    }

    public function testModelReturnsOnlyNotCanceledSubscriptionsWithTheScope()
    {
        Subscription::factory()
            ->count($this->faker()->randomDigitNotNull())
            ->started()
            ->notExpired()
            ->notSuppressed()
            ->canceled()
            ->create();

        $notCanceledSubscription = Subscription::factory()
            ->count($notCanceledSubscriptionCount = $this->faker()->randomDigitNotNull())
            ->started()
            ->notExpired()
            ->notSuppressed()
            ->notCanceled()
            ->create();

        $returnedSubscriptions = Subscription::notCanceled()->get();

        $this->assertCount($notCanceledSubscriptionCount, $returnedSubscriptions);
        $notCanceledSubscription->each(
            fn ($subscription) => $this->assertContains($subscription->id, $returnedSubscriptions->pluck('id'))
        );
    }

    public function testModelUpdatesGraceDaysEndedAtWhenRenewing()
    {
        $subscriber = User::factory()->create();
        $subscription = Subscription::factory()
            ->for($subscriber, 'subscriber')
            ->create([
                'grace_days_ended_at' => now()->subDay(),
            ]);

        $subscription->renew();

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'grace_days_ended_at' => $subscription->expired_at->addDays($subscription->plan->grace_days),
        ]);
    }

    public function testModelLeavesGraceDaysEmptyWhenRenewingIfPlanDoesNotHaveIt()
    {
        $subscriber = User::factory()->create();
        $subscription = Subscription::factory()
            ->for($subscriber, 'subscriber')
            ->create([
                'grace_days_ended_at' => null,
            ]);

        $subscription->plan->update(['grace_days' => 0]);

        $subscription->renew();

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'grace_days_ended_at' => null,
        ]);
    }
}
