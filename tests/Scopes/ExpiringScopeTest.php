<?php

namespace LucasDotVin\Soulbscription\Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use LucasDotVin\Soulbscription\Models\FeatureConsumption;
use LucasDotVin\Soulbscription\Tests\TestCase;

class ExpiringScopeTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public const MODEL = FeatureConsumption::class;

    public function testExpiredModelsAreNotReturnedByDefault()
    {
        $expiredModelsCount = $this->faker()->randomDigitNotNull();
        self::MODEL::factory()->count($expiredModelsCount)->create([
            'expired_at' => now()->subDay(),
        ]);

        $unexpiredModelsCount = $this->faker()->randomDigitNotNull();
        $unexpiredModels = self::MODEL::factory()->count($unexpiredModelsCount)->create([
            'expired_at' => now()->addDay(),
        ]);

        $returnedFeatureConsumptions = self::MODEL::all();

        $this->assertEqualsCanonicalizing(
            $unexpiredModels->pluck('id')->toArray(),
            $returnedFeatureConsumptions->pluck('id')->toArray(),
        );
    }

    public function testExpiredModelsAreReturnedWhenCallingMethodWithExpired()
    {
        $expiredModelsCount = $this->faker()->randomDigitNotNull();
        $expiredModels = self::MODEL::factory()->count($expiredModelsCount)->create([
            'expired_at' => now()->subDay(),
        ]);

        $unexpiredModelsCount = $this->faker()->randomDigitNotNull();
        $unexpiredModels = self::MODEL::factory()->count($unexpiredModelsCount)->create([
            'expired_at' => now()->addDay(),
        ]);

        $expectedFeatureConsumptions = $expiredModels->concat($unexpiredModels);

        $returnedFeatureConsumptions = self::MODEL::withExpired()->get();

        $this->assertEqualsCanonicalizing(
            $expectedFeatureConsumptions->pluck('id')->toArray(),
            $returnedFeatureConsumptions->pluck('id')->toArray(),
        );
    }

    public function testExpiredModelsAreNotReturnedWhenCallingMethodWithExpiredAndPassingFalse()
    {
        $expiredModelsCount = $this->faker()->randomDigitNotNull();
        self::MODEL::factory()->count($expiredModelsCount)->create([
            'expired_at' => now()->subDay(),
        ]);

        $unexpiredModelsCount = $this->faker()->randomDigitNotNull();
        $unexpiredModels = self::MODEL::factory()->count($unexpiredModelsCount)->create([
            'expired_at' => now()->addDay(),
        ]);

        $returnedFeatureConsumptions = self::MODEL::withExpired(false)->get();

        $this->assertEqualsCanonicalizing(
            $unexpiredModels->pluck('id')->toArray(),
            $returnedFeatureConsumptions->pluck('id')->toArray(),
        );
    }

    public function testOnlyExpiredModelsAreReturnedWhenCallingMethodOnlyExpired()
    {
        $expiredModelsCount = $this->faker()->randomDigitNotNull();
        $expiredModels = self::MODEL::factory()->count($expiredModelsCount)->create([
            'expired_at' => now()->subDay(),
        ]);

        $unexpiredModelsCount = $this->faker()->randomDigitNotNull();
        self::MODEL::factory()->count($unexpiredModelsCount)->create([
            'expired_at' => now()->addDay(),
        ]);

        $returnedFeatureConsumptions = self::MODEL::onlyExpired()->get();

        $this->assertEqualsCanonicalizing(
            $expiredModels->pluck('id')->toArray(),
            $returnedFeatureConsumptions->pluck('id')->toArray(),
        );
    }
}
