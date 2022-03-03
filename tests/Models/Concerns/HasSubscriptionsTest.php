<?php

namespace LucasDotDev\Soulbscription\Tests\Feature\Models\Concerns;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use LogicException;
use LucasDotDev\DBQueriesCounter\Traits\CountsQueries;
use LucasDotDev\Soulbscription\Events\FeatureConsumed;
use LucasDotDev\Soulbscription\Events\SubscriptionScheduled;
use LucasDotDev\Soulbscription\Events\SubscriptionStarted;
use LucasDotDev\Soulbscription\Events\SubscriptionSuppressed;
use LucasDotDev\Soulbscription\Models\Feature;
use LucasDotDev\Soulbscription\Models\FeatureConsumption;
use LucasDotDev\Soulbscription\Models\Plan;
use LucasDotDev\Soulbscription\Models\Subscription;
use LucasDotDev\Soulbscription\Models\SubscriptionRenewal;
use LucasDotDev\Soulbscription\Tests\Mocks\Models\User;
use LucasDotDev\Soulbscription\Tests\TestCase;
use OutOfBoundsException;
use OverflowException;

class HasSubscriptionsTest extends TestCase
{
    use CountsQueries;
    use RefreshDatabase;
    use WithFaker;

    public function testModelCanSubscribeToAPlan()
    {
        $plan = Plan::factory()->createOne();
        $subscriber = User::factory()->createOne();

        $this->expectsEvents(SubscriptionStarted::class);

        $subscription = $subscriber->subscribeTo($plan);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'plan_id' => $plan->id,
            'subscriber_id' => $subscriber->id,
            'started_at' => today(),
            'expired_at' => $plan->calculateNextRecurrenceEnd(),
        ]);
    }

    public function testModelCanSwitchToAPlan()
    {
        Carbon::setTestNow(now());

        $oldPlan = Plan::factory()->createOne();
        $newPlan = Plan::factory()->createOne();

        $subscriber = User::factory()->createOne();
        $oldSubscription = $subscriber->subscribeTo($oldPlan);

        $this->expectsEvents([SubscriptionStarted::class, SubscriptionSuppressed::class]);

        $newSubscription = $subscriber->switchTo($newPlan);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $newSubscription->id,
            'plan_id' => $newPlan->id,
            'subscriber_id' => $subscriber->id,
            'started_at' => today(),
            'expired_at' => $newPlan->calculateNextRecurrenceEnd(),
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $oldSubscription->id,
            'suppressed_at' => now(),
            'was_switched' => true,
        ]);
    }

    public function testModelCanScheduleSwitchToAPlan()
    {
        Carbon::setTestNow(now());

        $oldPlan = Plan::factory()->createOne();
        $newPlan = Plan::factory()->createOne();

        $subscriber = User::factory()->createOne();
        $oldSubscription = $subscriber->subscribeTo($oldPlan);

        $this->expectsEvents(SubscriptionScheduled::class);
        $this->doesntExpectEvents(SubscriptionStarted::class);

        $newSubscription = $subscriber->switchTo($newPlan, immediately: false);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $newSubscription->id,
            'plan_id' => $newPlan->id,
            'started_at' => $oldSubscription->expired_at,
            'expired_at' => $newPlan->calculateNextRecurrenceEnd($oldSubscription->expired_at),
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $oldSubscription->id,
            'was_switched' => true,
        ]);
    }

    public function testModelGetNewSubscriptionAfterSwitching()
    {
        $oldPlan = Plan::factory()->createOne();
        $newPlan = Plan::factory()->createOne();

        $subscriber = User::factory()->createOne();
        $subscriber->subscribeTo($oldPlan, startDate: now()->subDay());

        $newSubscription = $subscriber->switchTo($newPlan);

        $this->assertTrue($newSubscription->is($subscriber->fresh()->subscription));
    }

    public function testModelGetCurrentSubscriptionAfterScheduleASwitch()
    {
        Carbon::setTestNow(now());

        $oldPlan = Plan::factory()->createOne();
        $newPlan = Plan::factory()->createOne();

        $subscriber = User::factory()->createOne();
        $oldSubscription = $subscriber->subscribeTo($oldPlan);

        $subscriber->switchTo($newPlan, immediately: false);

        $this->assertTrue($oldSubscription->is($subscriber->fresh()->subscription));
    }

    public function testModelCanConsumeAFeature()
    {
        $charges = $this->faker->numberBetween(5, 10);
        $consumption = $this->faker->numberBetween(1, $charges);

        $plan = Plan::factory()->createOne();
        $feature = Feature::factory()->consumable()->createOne();
        $feature->plans()->attach($plan, [
            'charges' => $charges,
        ]);

        $subscriber = User::factory()->createOne();
        $subscription = $subscriber->subscribeTo($plan);

        $this->expectsEvents(FeatureConsumed::class);

        $subscriber->consume($feature->name, $consumption);

        $this->assertDatabaseHas('feature_consumptions', [
            'consumption' => $consumption,
            'feature_id' => $feature->id,
            'subscriber_id' => $subscriber->id,
            'expired_at' => $feature->calculateNextRecurrenceEnd($subscription->started_at),
        ]);
    }

    public function testModelCanConsumeANotConsumableFeatureIfItIsAvailable()
    {
        $plan = Plan::factory()->createOne();
        $feature = Feature::factory()->notConsumable()->createOne();
        $feature->plans()->attach($plan);

        $subscriber = User::factory()->createOne();
        $subscriber->subscribeTo($plan);

        $subscriber->consume($feature->name);

        $this->assertDatabaseHas('feature_consumptions', [
            'consumption' => null,
            'feature_id' => $feature->id,
            'subscriber_id' => $subscriber->id,
        ]);
    }

    public function testModelCantConsumeAnUnavailableFeature()
    {
        $charges = $this->faker->numberBetween(5, 10);
        $consumption = $this->faker->numberBetween(1, $charges);

        $plan = Plan::factory()->createOne();
        $feature = Feature::factory()->consumable()->createOne();
        $feature->plans()->attach($plan, [
            'charges' => $charges,
        ]);

        $subscriber = User::factory()->createOne();
        $subscriber->subscribeTo($plan, now()->subDay());

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('None of the active plans grants access to this feature.');

        $subscriber->consume($feature->name, $consumption);

        $this->assertDatabaseMissing('feature_consumptions', [
            'consumption' => $consumption,
            'feature_id' => $feature->id,
            'subscriber_id' => $subscriber->id,
        ]);
    }

    public function testModelCantConsumeAFeatureBeyondItsCharges()
    {
        $charges = $this->faker->numberBetween(5, 10);
        $consumption = $charges + 1;

        $plan = Plan::factory()->createOne();
        $feature = Feature::factory()->consumable()->createOne();
        $feature->plans()->attach($plan, [
            'charges' => $charges,
        ]);

        $subscriber = User::factory()->createOne();
        $subscriber->subscribeTo($plan);

        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('The feature has no enough charges to this consumption.');

        $subscriber->consume($feature->name, $consumption);

        $this->assertDatabaseMissing('feature_consumptions', [
            'consumption' => $consumption,
            'feature_id' => $feature->id,
            'subscriber_id' => $subscriber->id,
        ]);
    }

    public function testModelCanConsumeSomeAmountOfAConsumableFeature()
    {
        $charges = $this->faker->numberBetween(5, 10);
        $consumption = $this->faker->numberBetween(1, $charges);

        $plan = Plan::factory()->createOne();
        $feature = Feature::factory()->consumable()->createOne();
        $feature->plans()->attach($plan, [
            'charges' => $charges,
        ]);

        $subscriber = User::factory()->createOne();
        $subscriber->subscribeTo($plan);

        $modelCanUse = $subscriber->canConsume($feature->name, $consumption);

        $this->assertTrue($modelCanUse);
    }

    public function testModelCantConsumeSomeAmountOfAConsumableFeatureFromAnExpiredSubscription()
    {
        $charges = $this->faker->numberBetween(5, 10);
        $consumption = $this->faker->numberBetween(1, $charges);

        $plan = Plan::factory()->createOne();
        $feature = Feature::factory()->consumable()->createOne();
        $feature->plans()->attach($plan, [
            'charges' => $charges,
        ]);

        $subscriber = User::factory()->createOne();
        $subscriber->subscribeTo($plan, now()->subDay());

        $modelCanUse = $subscriber->canConsume($feature->name, $consumption);

        $this->assertFalse($modelCanUse);
    }

    public function testModelCantConsumeSomeAmountOfAConsumableFeature()
    {
        $charges = $this->faker->numberBetween(5, 10);
        $consumption = $charges + 1;

        $plan = Plan::factory()->createOne();
        $feature = Feature::factory()->consumable()->createOne();
        $feature->plans()->attach($plan, [
            'charges' => $charges,
        ]);

        $subscriber = User::factory()->createOne();
        $subscriber->subscribeTo($plan);

        $modelCanUse = $subscriber->canConsume($feature->name, $consumption);

        $this->assertFalse($modelCanUse);
    }

    public function testModelCanConsumeSomeAmountOfAConsumableFeatureIfItsConsumptionsAreExpired()
    {
        $charges = $this->faker->numberBetween(5, 10);
        $consumption = $this->faker->numberBetween(1, $charges);

        $plan = Plan::factory()->createOne();
        $feature = Feature::factory()->consumable()->createOne();
        $feature->plans()->attach($plan, [
            'charges' => $charges,
        ]);

        $subscriber = User::factory()->createOne();
        $subscriber->subscribeTo($plan);

        FeatureConsumption::factory()
            ->for($feature)
            ->for($subscriber, 'subscriber')
            ->createOne([
                'consumption' => $this->faker->numberBetween(5, 10),
                'expired_at' => now()->subDay(),
            ]);

        $modelCanUse = $subscriber->canConsume($feature->name, $consumption);

        $this->assertTrue($modelCanUse);
    }

    public function testModelHasSubscriptionRenewals()
    {
        $subscriber = User::factory()->createOne();
        $subscription = Subscription::factory()
            ->for($subscriber, 'subscriber')
            ->createOne();

        $renewalsCount = $this->faker->randomDigitNotNull();
        $renewals = SubscriptionRenewal::factory()
            ->times($renewalsCount)
            ->for($subscription)
            ->createOne();

        $this->assertEqualsCanonicalizing(
            $renewals->pluck('id'),
            $subscriber->renewals->pluck('id'),
        );
    }

    public function testModelCachesFeatures()
    {
        $charges = $this->faker->numberBetween(5, 10);
        $consumption = $this->faker->numberBetween(1, $charges);

        $plan = Plan::factory()->createOne();
        $feature = Feature::factory()->consumable()->createOne();
        $feature->plans()->attach($plan, [
            'charges' => $charges,
        ]);

        $subscriber = User::factory()->createOne();
        $subscriber->subscribeTo($plan);

        $this->whileCountingQueries(fn () => $subscriber->features);
        $initiallyPerformedQueries = $this->getQueryCount();

        $this->whileCountingQueries(fn () => $subscriber->features);
        $totalPerformedQueries = $this->getQueryCount();

        $this->assertEquals($initiallyPerformedQueries, $totalPerformedQueries);
    }

    public function testModelHasFeatureTickets()
    {
        $feature = Feature::factory()->consumable()->createOne();

        $subscriber = User::factory()->createOne();

        $ticket = $subscriber->featureTickets()->make(['expired_at' => now()->addDay()]);
        $ticket->feature()->associate($feature);
        $ticket->save();

        config()->set('soulbscription.feature_tickets', true);

        $this->assertSame(
            $ticket->id,
            $subscriber->featureTickets->first()->id,
        );
    }

    public function testModelFeatureTicketsGetsOnlyNotExpired()
    {
        $feature = Feature::factory()->consumable()->createOne();

        $subscriber = User::factory()->createOne();

        $expiredTicket = $subscriber->featureTickets()->make([
            'expired_at' => now()->subDay(),
        ]);

        $expiredTicket->feature()->associate($feature);
        $expiredTicket->save();

        $activeTicket = $subscriber->featureTickets()->make([
            'expired_at' => now()->addDay(),
        ]);

        $activeTicket->feature()->associate($feature);
        $activeTicket->save();

        config()->set('soulbscription.feature_tickets', true);

        $this->assertContains(
            $activeTicket->id,
            $subscriber->featureTickets->pluck('id'),
        );

        $this->assertNotContains(
            $expiredTicket->id,
            $subscriber->featureTickets->pluck('id'),
        );
    }

    public function testModelGetFeaturesFromTickets()
    {
        $feature = Feature::factory()->consumable()->createOne();

        $subscriber = User::factory()->createOne();

        $ticket = $subscriber->featureTickets()->make([
            'expired_at' => now()->addDay(),
        ]);

        $ticket->feature()->associate($feature);
        $ticket->save();

        config()->set('soulbscription.feature_tickets', true);

        $this->assertContains(
            $feature->id,
            $subscriber->features->pluck('id')->toArray(),
        );
    }

    public function testModelCanConsumeSomeAmountOfAConsumableFeatureFromATicket()
    {
        $charges = $this->faker->numberBetween(5, 10);
        $consumption = $this->faker->numberBetween(1, $charges);

        $feature = Feature::factory()->consumable()->createOne();
        $subscriber = User::factory()->createOne();

        $ticket = $subscriber->featureTickets()->make([
            'charges' => $charges,
            'expired_at' => now()->addDay(),
        ]);

        $ticket->feature()->associate($feature);
        $ticket->save();

        config()->set('soulbscription.feature_tickets', true);

        $modelCanUse = $subscriber->canConsume($feature->name, $consumption);

        $this->assertTrue($modelCanUse);
    }

    public function testModelCanRetrieveTotalChargesForAFeatureConsideringTickets()
    {
        $subscriptionFeatureCharges = $this->faker->numberBetween(5, 10);
        $ticketFeatureCharges = $this->faker->numberBetween(5, 10);

        $feature = Feature::factory()->consumable()->createOne();

        $plan = Plan::factory()->createOne();
        $feature->plans()->attach($plan, [
            'charges' => $subscriptionFeatureCharges,
        ]);

        $subscriber = User::factory()->createOne();
        $subscriber->subscribeTo($plan);

        $ticket = $subscriber->featureTickets()->make([
            'charges' => $ticketFeatureCharges,
            'expired_at' => now()->addDay(),
        ]);

        $ticket->feature()->associate($feature);
        $ticket->save();

        config()->set('soulbscription.feature_tickets', true);

        $totalCharges = $subscriber->getTotalCharges($feature->name);

        $this->assertEquals($totalCharges, $subscriptionFeatureCharges + $ticketFeatureCharges);
    }

    public function testModelCanConsumeANotConsumableFeatureFromATicket()
    {
        $feature = Feature::factory()->notConsumable()->createOne();
        $subscriber = User::factory()->createOne();

        $ticket = $subscriber->featureTickets()->make([
            'expired_at' => now()->addDay(),
        ]);

        $ticket->feature()->associate($feature);
        $ticket->save();

        config()->set('soulbscription.feature_tickets', true);

        $modelCanUse = $subscriber->canConsume($feature->name);

        $this->assertTrue($modelCanUse);
    }

    public function testModelCanRetrieveTotalConsumptionsForAFeature()
    {
        $consumption = $this->faker->randomDigitNotNull();

        $plan = Plan::factory()->createOne();
        $feature = Feature::factory()->consumable()->createOne();
        $feature->plans()->attach($plan);

        $subscriber = User::factory()->createOne();
        $subscriber->subscribeTo($plan);
        $subscriber->featureConsumptions()
            ->make([
                'consumption' => $consumption,
                'expired_at' => now()->addDay(),
            ])
            ->feature()
            ->associate($feature)
            ->save();

        config()->set('soulbscription.feature_tickets', true);

        $receivedConsumption = $subscriber->getCurrentConsumption($feature->name);

        $this->assertEquals($consumption, $receivedConsumption);
    }

    public function testModelCanRetrieveRemainingChargesForAFeature()
    {
        $charges = $this->faker->numberBetween(6, 10);
        $consumption = $this->faker->numberBetween(1, 5);

        $plan = Plan::factory()->createOne();
        $feature = Feature::factory()->consumable()->createOne();
        $feature->plans()->attach($plan, [
            'charges' => $charges,
        ]);

        $subscriber = User::factory()->createOne();
        $subscriber->subscribeTo($plan);
        $subscriber->featureConsumptions()
            ->make([
                'consumption' => $consumption,
                'expired_at' => now()->addDay(),
            ])
            ->feature()
            ->associate($feature)
            ->save();

        config()->set('soulbscription.feature_tickets', true);

        $receivedRemainingCharges = $subscriber->getRemainingCharges($feature->name);

        $this->assertEquals($charges - $consumption, $receivedRemainingCharges);
    }

    public function testModelCantUseChargesFromExpiredTickets()
    {
        $feature = Feature::factory()->consumable()->createOne();
        $subscriber = User::factory()->createOne();

        $plan = Plan::factory()->createOne();
        $subscriber->subscribeTo($plan);

        $subscriptionFeatureCharges = $this->faker->numberBetween(5, 10);
        $feature->plans()->attach($plan, [
            'charges' => $subscriptionFeatureCharges,
        ]);

        $activeTicketCharges = $this->faker->numberBetween(5, 10);
        $activeTicket = $subscriber->featureTickets()->make([
            'charges' => $activeTicketCharges,
            'expired_at' => now()->addDay(),
        ]);

        $activeTicket->feature()->associate($feature);
        $activeTicket->save();

        $expiredTicketCharges = $this->faker->numberBetween(5, 10);
        $expiredTicket = $subscriber->featureTickets()->make([
            'charges' => $expiredTicketCharges,
            'expired_at' => now()->subDay(),
        ]);

        $expiredTicket->feature()->associate($feature);
        $expiredTicket->save();

        config()->set('soulbscription.feature_tickets', true);

        $totalCharges = $subscriber->getTotalCharges($feature->name);

        $this->assertEquals($totalCharges, $subscriptionFeatureCharges + $activeTicketCharges);
    }

    public function testItIgnoresTicketsWhenItIsDisabled()
    {
        $feature = Feature::factory()->consumable()->createOne();
        $subscriber = User::factory()->createOne();

        $plan = Plan::factory()->createOne();
        $plan->features()->attach($feature);
        $subscriber->subscribeTo($plan);

        $ticket = $subscriber->featureTickets()->make([
            'expired_at' => now()->addDay(),
        ]);

        $ticket->feature()->associate($feature);
        $ticket->save();

        config()->set('soulbscription.feature_tickets', true);
        $featuresWithTickets = User::first()->features;

        config()->set('soulbscription.feature_tickets', false);
        $featuresWithoutTickets = User::first()->features;

        $this->assertCount(2, $featuresWithTickets);
        $this->assertCount(1, $featuresWithoutTickets);
    }

    public function testItCanCreateATicket()
    {
        $charges = $this->faker->randomDigitNotNull();
        $expiration = $this->faker->dateTime();

        $feature = Feature::factory()->consumable()->createOne();

        $subscriber = User::factory()->createOne();

        config()->set('soulbscription.feature_tickets', true);

        $subscriber->giveTicketFor($feature->name, $expiration, $charges);

        $this->assertDatabaseHas('feature_tickets', [
            'charges' => $charges,
            'expired_at' => $expiration,
            'subscriber_id' => $subscriber->id,
        ]);
    }

    public function testItRaisesAnExceptionWhenCreatingATicketForANonExistingFeature()
    {
        $charges = $this->faker->randomDigitNotNull();
        $expiration = $this->faker->dateTime();

        $unexistingFeatureName = $this->faker->word();

        $subscriber = User::factory()->createOne();

        $this->expectException(ModelNotFoundException::class);

        config()->set('soulbscription.feature_tickets', true);

        $subscriber->giveTicketFor($unexistingFeatureName, $expiration, $charges);
    }

    public function testItRaisesAnExceptionWhenCreatingATicketDespiteItIsDisabled()
    {
        $charges = $this->faker->randomDigitNotNull();
        $expiration = $this->faker->dateTime();

        $feature = Feature::factory()->consumable()->createOne();

        $subscriber = User::factory()->createOne();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The tickets are not enabled in the configs.');

        config()->set('soulbscription.feature_tickets', false);

        $subscriber->giveTicketFor($feature->name, $expiration, $charges);
    }

    public function testItAddsFeatureToLoadedAfterCreatingATicket()
    {
        $charges = $this->faker->randomDigitNotNull();
        $expiration = now()->addDay();

        $feature = Feature::factory()->consumable()->createOne();

        $subscriber = User::factory()->createOne();

        config()->set('soulbscription.feature_tickets', true);

        $this->assertCount(0, $subscriber->features);

        $subscriber->giveTicketFor($feature->name, $expiration, $charges);

        $this->assertCount(1, $subscriber->features);
    }

    public function testItOnlyAddsNotExpiredTicketsToTheLoadedFeatures()
    {
        $charges = $this->faker->randomDigitNotNull();
        $expiration = now()->subDay();

        $feature = Feature::factory()->consumable()->createOne();

        $subscriber = User::factory()->createOne();

        config()->set('soulbscription.feature_tickets', true);

        $this->assertCount(0, $subscriber->features);

        $subscriber->giveTicketFor($feature->name, $expiration, $charges);

        $this->assertCount(0, $subscriber->features);
    }
}
