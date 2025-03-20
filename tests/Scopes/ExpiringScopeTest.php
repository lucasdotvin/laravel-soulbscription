<?php

namespace Tests\Feature\Models;

use Tests\TestCase;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpiringScopeTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public const MODEL = 'soulbscription.models.feature_consumption';

    protected function getModelClass()
    {
        $modelClass = config(self::MODEL);

        throw_if(!is_a($modelClass, Model::class, true), new InvalidArgumentException(
            "Configured feature consumption model must be a subclass of " . Model::class
        ));

        return $modelClass;
    }

    public function testExpiredModelsAreNotReturnedByDefault()
    {
        $modelClass = $this->getModelClass();
        $expiredModelsCount = $this->faker->randomDigitNotNull();

        $modelClass::factory()->count($expiredModelsCount)->create([
            'expired_at' => now()->subDay(),
        ]);

        $unexpiredModelsCount = $this->faker->randomDigitNotNull();
        $unexpiredModels = $modelClass::factory()->count($unexpiredModelsCount)->create([
            'expired_at' => now()->addDay(),
        ]);

        $returnedFeatureConsumptions = $modelClass::all();

        $this->assertEqualsCanonicalizing(
            $unexpiredModels->pluck('id')->toArray(),
            $returnedFeatureConsumptions->pluck('id')->toArray(),
        );
    }

    public function testExpiredModelsAreReturnedWhenCallingMethodWithExpired()
    {
        $modelClass = $this->getModelClass();
        $expiredModelsCount = $this->faker->randomDigitNotNull();

        $expiredModels = $modelClass::factory()->count($expiredModelsCount)->create([
            'expired_at' => now()->subDay(),
        ]);

        $unexpiredModelsCount = $this->faker->randomDigitNotNull();
        $unexpiredModels = $modelClass::factory()->count($unexpiredModelsCount)->create([
            'expired_at' => now()->addDay(),
        ]);

        $expectedFeatureConsumptions = $expiredModels->concat($unexpiredModels);
        $returnedFeatureConsumptions = $modelClass::withExpired()->get();

        $this->assertEqualsCanonicalizing(
            $expectedFeatureConsumptions->pluck('id')->toArray(),
            $returnedFeatureConsumptions->pluck('id')->toArray(),
        );
    }

    public function testExpiredModelsAreNotReturnedWhenCallingMethodWithExpiredAndPassingFalse()
    {
        $modelClass = $this->getModelClass();
        $expiredModelsCount = $this->faker->randomDigitNotNull();

        $modelClass::factory()->count($expiredModelsCount)->create([
            'expired_at' => now()->subDay(),
        ]);

        $unexpiredModelsCount = $this->faker->randomDigitNotNull();
        $unexpiredModels = $modelClass::factory()->count($unexpiredModelsCount)->create([
            'expired_at' => now()->addDay(),
        ]);

        $returnedFeatureConsumptions = $modelClass::withExpired(false)->get();

        $this->assertEqualsCanonicalizing(
            $unexpiredModels->pluck('id')->toArray(),
            $returnedFeatureConsumptions->pluck('id')->toArray(),
        );
    }

    public function testOnlyExpiredModelsAreReturnedWhenCallingMethodOnlyExpired()
    {
        $modelClass = $this->getModelClass();
        $expiredModelsCount = $this->faker->randomDigitNotNull();

        $expiredModels = $modelClass::factory()->count($expiredModelsCount)->create([
            'expired_at' => now()->subDay(),
        ]);

        $unexpiredModelsCount = $this->faker->randomDigitNotNull();
        $modelClass::factory()->count($unexpiredModelsCount)->create([
            'expired_at' => now()->addDay(),
        ]);

        $returnedFeatureConsumptions = $modelClass::onlyExpired()->get();

        $this->assertEqualsCanonicalizing(
            $expiredModels->pluck('id')->toArray(),
            $returnedFeatureConsumptions->pluck('id')->toArray(),
        );
    }
}
