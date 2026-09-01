<?php

namespace Tests\Feature\Models\Concerns;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Mocks\Models\CustomFeature;
use Tests\Mocks\Models\User;
use Tests\TestCase;

class ConfiguredModelsTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function testItLoadsTicketFeaturesUsingTheConfiguredModel()
    {
        config()->set('soulbscription.feature_tickets', true);
        config()->set('soulbscription.models.feature', CustomFeature::class);

        $subscriber = User::factory()->createOne();
        $feature = CustomFeature::factory()->createOne(['consumable' => false]);

        $subscriber->giveTicketFor($feature->name, now()->addDay());

        $loadedFeatures = $subscriber->features;

        $this->assertNotEmpty($loadedFeatures);
        $this->assertInstanceOf(CustomFeature::class, $loadedFeatures->first());
    }

    public function testItResolvesTicketedFeaturesByNameUsingTheConfiguredModel()
    {
        config()->set('soulbscription.feature_tickets', true);
        config()->set('soulbscription.models.feature', CustomFeature::class);

        $subscriber = User::factory()->createOne();
        $feature = CustomFeature::factory()->createOne(['consumable' => false]);

        $featureTicket = $subscriber->giveTicketFor($feature->name, now()->addDay());

        $this->assertEquals($feature->id, $featureTicket->feature_id);
        $this->assertInstanceOf(CustomFeature::class, $subscriber->getFeature($feature->name));
    }
}
