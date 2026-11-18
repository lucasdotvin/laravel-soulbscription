<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use LucasDotVin\Soulbscription\Models\Feature;
use LucasDotVin\Soulbscription\Models\FeaturePlan;
use LucasDotVin\Soulbscription\Models\Plan;
use Tests\TestCase;

class FeaturePlanTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testModelCanRetrievePlan()
    {
        $feature = Feature::factory()
            ->create();

        $plan = Plan::factory()->create();
        $plan->features()->attach($feature);

        $featurePlanPivot = FeaturePlan::first();

        $this->assertEquals($plan->id, $featurePlanPivot->plan->id);
    }

    public function testModelCanRetrieveFeature()
    {
        $feature = Feature::factory()
            ->create();

        $plan = Plan::factory()->create();
        $plan->features()->attach($feature);

        $featurePlanPivot = FeaturePlan::first();

        $this->assertEquals($feature->id, $featurePlanPivot->feature->id);
    }
}
