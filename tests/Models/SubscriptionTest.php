<?php

namespace Tests\Feature\Models;

use Tests\TestCase;
use Tests\Mocks\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LucasDotVin\Soulbscription\Events\SubscriptionCanceled;
use LucasDotVin\Soulbscription\Events\SubscriptionRenewed;
use LucasDotVin\Soulbscription\Events\SubscriptionStarted;
use LucasDotVin\Soulbscription\Events\SubscriptionSuppressed;

class SubscriptionTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function testModelRenews()
    {
        Carbon::setTestNow(now());

        $plan = config('soulbscription.models.plan')::factory()->create();
        $subscriber = User::factory()->create();
        $subscription = config('soulbscription.models.subscription')::factory()
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

        $plan = config('soulbscription.models.plan')::factory()->create();
        $subscriber = User::factory()->create();
        $subscription = config('soulbscription.models.subscription')::factory()
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

        $plan = config('soulbscription.models.plan')::factory()->create();
        $subscriber = User::factory()->create();
        $subscription = config('soulbscription.models.subscription')::factory()
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

        $plan = config('soulbscription.models.plan')::factory()->create();
        $subscriber = User::factory()->create();
        $subscription = config('soulbscription.models.subscription')::factory()
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

        $plan = config('soulbscription.models.plan')::factory()->create();
        $subscriber = User::factory()->create();
        $subscription = config('soulbscription.models.subscription')::factory()
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
        $plan = config('soulbscription.models.plan')::factory()->create();
        $subscriber = User::factory()->create();
        $subscription = config('soulbscription.models.subscription')::factory()
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
        $subscription = config('soulbscription.models.subscription')::factory()
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
        $subscription = config('soulbscription.models.subscription')::factory()
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
        $subscription = config('soulbscription.models.subscription')::factory()
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
        config('soulbscription.models.subscription')::factory()
            ->count($this->faker()->randomDigitNotNull())
            ->started()
            ->notExpired()
            ->notSuppressed()
            ->create();

        $notStartedSubscription = config('soulbscription.models.subscription')::factory()
            ->count($notStartedSubscriptionCount = $this->faker()->randomDigitNotNull())
            ->notStarted()
            ->notExpired()
            ->notSuppressed()
            ->create();

        $returnedSubscriptions = config('soulbscription.models.subscription')::notActive()->get();

        $this->assertCount($notStartedSubscriptionCount, $returnedSubscriptions);
        $notStartedSubscription->each(
            fn ($subscription) => $this->assertContains($subscription->id, $returnedSubscriptions->pluck('id'))
        );
    }

    public function testModelReturnsExpiredSubscriptionsInNotActiveScope()
    {
        config('soulbscription.models.subscription')::factory()
            ->count($this->faker()->randomDigitNotNull())
            ->started()
            ->notExpired()
            ->notSuppressed()
            ->create();

        $expiredSubscription = config('soulbscription.models.subscription')::factory()
            ->count($expiredSubscriptionCount = $this->faker()->randomDigitNotNull())
            ->started()
            ->expired()
            ->notSuppressed()
            ->create();

        $returnedSubscriptions = config('soulbscription.models.subscription')::notActive()->get();

        $this->assertCount($expiredSubscriptionCount, $returnedSubscriptions);
        $expiredSubscription->each(
            fn ($subscription) => $this->assertContains($subscription->id, $returnedSubscriptions->pluck('id'))
        );
    }

    public function testModelReturnsSuppressedSubscriptionsInNotActiveScope()
    {
        config('soulbscription.models.subscription')::factory()
            ->count($this->faker()->randomDigitNotNull())
            ->started()
            ->notExpired()
            ->notSuppressed()
            ->create();

        $suppressedSubscription = config('soulbscription.models.subscription')::factory()
            ->count($suppressedSubscriptionCount = $this->faker()->randomDigitNotNull())
            ->started()
            ->notExpired()
            ->suppressed()
            ->create();

        $returnedSubscriptions = config('soulbscription.models.subscription')::notActive()->get();

        $this->assertCount($suppressedSubscriptionCount, $returnedSubscriptions);
        $suppressedSubscription->each(
            fn ($subscription) => $this->assertContains($subscription->id, $returnedSubscriptions->pluck('id'))
        );
    }

    public function testModelReturnsOnlyCanceledSubscriptionsWithTheScope()
    {
        config('soulbscription.models.subscription')::factory()
            ->count($this->faker()->randomDigitNotNull())
            ->started()
            ->notExpired()
            ->notSuppressed()
            ->notCanceled()
            ->create();

        $canceledSubscription = config('soulbscription.models.subscription')::factory()
            ->count($canceledSubscriptionCount = $this->faker()->randomDigitNotNull())
            ->started()
            ->notExpired()
            ->notSuppressed()
            ->canceled()
            ->create();

        $returnedSubscriptions = config('soulbscription.models.subscription')::canceled()->get();

        $this->assertCount($canceledSubscriptionCount, $returnedSubscriptions);
        $canceledSubscription->each(
            fn ($subscription) => $this->assertContains($subscription->id, $returnedSubscriptions->pluck('id'))
        );
    }

    public function testModelReturnsOnlyNotCanceledSubscriptionsWithTheScope()
    {
        config('soulbscription.models.subscription')::factory()
            ->count($this->faker()->randomDigitNotNull())
            ->started()
            ->notExpired()
            ->notSuppressed()
            ->canceled()
            ->create();

        $notCanceledSubscription = config('soulbscription.models.subscription')::factory()
            ->count($notCanceledSubscriptionCount = $this->faker()->randomDigitNotNull())
            ->started()
            ->notExpired()
            ->notSuppressed()
            ->notCanceled()
            ->create();

        $returnedSubscriptions = config('soulbscription.models.subscription')::notCanceled()->get();

        $this->assertCount($notCanceledSubscriptionCount, $returnedSubscriptions);
        $notCanceledSubscription->each(
            fn ($subscription) => $this->assertContains($subscription->id, $returnedSubscriptions->pluck('id'))
        );
    }
}
