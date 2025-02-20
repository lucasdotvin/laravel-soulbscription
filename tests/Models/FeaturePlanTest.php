<?php

namespace Tests\Feature\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FeaturePlanTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function testModelCanRetrievePlan()
    {
        $feature = config('soulbscription.models.feature')::factory()
            ->create();

        $plan = config('soulbscription.models.plan')::factory()->create();
        $plan->features()->attach($feature);

        $featurePlanPivot = config('soulbscription.models.feature_plan')::first();

        $this->assertEquals($plan->id, $featurePlanPivot->plan->id);
    }

    public function testModelCanRetrieveFeature()
    {
        $feature = config('soulbscription.models.feature')::factory()
            ->create();

        $plan = config('soulbscription.models.plan')::factory()->create();
        $plan->features()->attach($feature);

        $featurePlanPivot = config('soulbscription.models.feature_plan')::first();

        $this->assertEquals($feature->id, $featurePlanPivot->feature->id);
    }
}
