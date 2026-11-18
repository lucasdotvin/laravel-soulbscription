<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LucasDotVin\Soulbscription\Database\Factories\FeatureFactory;
use LucasDotVin\Soulbscription\Database\Factories\PlanFactory;
use Tests\Mocks\Models\TenantFeature;
use Tests\Mocks\Models\TenantPlan;
use Tests\Mocks\Models\TenantSubscription;
use Tests\Mocks\Models\User;
use Tests\TestCase;

/**
 * Custom models whose class names differ from the package's must keep working
 * with the published tables and columns, without overriding $table or keys.
 */
class CustomModelNamesTest extends TestCase
{
    use RefreshDatabase;

    public function testPlansAndFeaturesAreLinkedThroughThePublishedPivot()
    {
        config()->set('soulbscription.models.feature', TenantFeature::class);
        config()->set('soulbscription.models.plan', TenantPlan::class);

        $feature = TenantFeature::create(FeatureFactory::new()->raw());
        $plan = TenantPlan::create(PlanFactory::new()->raw());

        $plan->features()->attach($feature, ['charges' => 5]);

        $this->assertInstanceOf(TenantFeature::class, $plan->features->first());
        $this->assertTrue($plan->features->first()->is($feature));
        $this->assertEquals(5, $plan->features->first()->pivot->charges);
        $this->assertTrue($feature->plans->first()->is($plan));
    }

    public function testSubscriptionsAndRenewalsUseThePublishedKeys()
    {
        config()->set('soulbscription.models.plan', TenantPlan::class);
        config()->set('soulbscription.models.subscription', TenantSubscription::class);

        $plan = TenantPlan::create(PlanFactory::new()->raw());
        $subscriber = User::factory()->createOne();

        $subscription = $subscriber->subscribeTo($plan);
        $subscription->renew();

        $this->assertInstanceOf(TenantSubscription::class, $subscription);
        $this->assertTrue($plan->subscriptions->first()->is($subscription));
        $this->assertTrue($subscription->plan->is($plan));
        $this->assertCount(1, $subscription->renewals);
        $this->assertCount(1, $subscriber->renewals);
    }

    public function testFeatureTicketsUseThePublishedKeys()
    {
        config()->set('soulbscription.feature_tickets', true);
        config()->set('soulbscription.models.feature', TenantFeature::class);

        $feature = TenantFeature::create(FeatureFactory::new()->notConsumable()->raw());
        $subscriber = User::factory()->createOne();

        $subscriber->giveTicketFor($feature->name, now()->addDay());

        $this->assertCount(1, $feature->tickets);
        $this->assertTrue($feature->tickets->first()->feature->is($feature));
    }

    public function testFeaturesCanBeConsumed()
    {
        config()->set('soulbscription.models.feature', TenantFeature::class);
        config()->set('soulbscription.models.plan', TenantPlan::class);

        $feature = TenantFeature::create(FeatureFactory::new()->consumable()->raw());
        $plan = TenantPlan::create(PlanFactory::new()->raw());
        $plan->features()->attach($feature, ['charges' => 5]);

        $subscriber = User::factory()->createOne();
        $subscriber->subscribeTo($plan);
        $subscriber->consume($feature->name, 2);

        $this->assertEquals(2, $subscriber->fresh()->getCurrentConsumption($feature->name));
        $this->assertEquals(3, $subscriber->fresh()->getRemainingCharges($feature->name));
    }
}
