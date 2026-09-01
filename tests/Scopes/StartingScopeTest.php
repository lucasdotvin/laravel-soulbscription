<?php

namespace Tests\Feature\Models;

use Tests\TestCase;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StartingScopeTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public const MODEL = 'soulbscription.models.subscription';

    protected function getModelClass()
    {
        $modelClass = config(self::MODEL);

        throw_if(!is_a($modelClass, Model::class, true), new InvalidArgumentException(
            "Configured subscription model must be a subclass of " . Model::class
        ));

        return $modelClass;
    }

    public function testNotStartedModelsAreNotReturnedByDefault()
    {
        $modelClass = $this->getModelClass();
        $startedModelsCount = $this->faker->randomDigitNotNull();

        $startedModels = $modelClass::factory()->count($startedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->subDay(),
        ]);

        $notStartedModelsCount = $this->faker->randomDigitNotNull();
        $modelClass::factory()->count($notStartedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->addDay(),
        ]);

        $returnedSubscriptions = $modelClass::all();

        $this->assertEqualsCanonicalizing(
            $startedModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testNotStartedModelsAreNotReturnedWhenCallingWithoutNotStarted()
    {
        $modelClass = $this->getModelClass();
        $startedModelsCount = $this->faker->randomDigitNotNull();

        $startedModels = $modelClass::factory()->count($startedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->subDay(),
        ]);

        $notStartedModelsCount = $this->faker->randomDigitNotNull();
        $modelClass::factory()->count($notStartedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->addDay(),
        ]);

        $returnedSubscriptions = $modelClass::withoutNotStarted()->get();

        $this->assertEqualsCanonicalizing(
            $startedModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testStartedModelsAreReturnedWhenCallingMethodWithNotStarted()
    {
        $modelClass = $this->getModelClass();
        $startedModelsCount = $this->faker->randomDigitNotNull();

        $startedModels = $modelClass::factory()->count($startedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->subDay(),
        ]);

        $notStartedModelsCount = $this->faker->randomDigitNotNull();
        $notStartedModels = $modelClass::factory()->count($notStartedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->addDay(),
        ]);

        $expectedSubscriptions = $startedModels->concat($notStartedModels);
        $returnedSubscriptions = $modelClass::withNotStarted()->get();

        $this->assertEqualsCanonicalizing(
            $expectedSubscriptions->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testNotStartedModelsAreReturnedWhenCallingMethodWithNotStartedAndPassingAFalse()
    {
        $modelClass = $this->getModelClass();
        $startedModelsCount = $this->faker->randomDigitNotNull();

        $startedModels = $modelClass::factory()->count($startedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->subDay(),
        ]);

        $notStartedModelsCount = $this->faker->randomDigitNotNull();
        $modelClass::factory()->count($notStartedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->addDay(),
        ]);

        $returnedSubscriptions = $modelClass::withNotStarted(false)->get();

        $this->assertEqualsCanonicalizing(
            $startedModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testOnlyStartedModelsAreReturnedWhenCallingMethodOnlyNotStarted()
    {
        $modelClass = $this->getModelClass();
        $startedModelsCount = $this->faker->randomDigitNotNull();

        $modelClass::factory()->count($startedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->subDay(),
        ]);

        $notStartedModelsCount = $this->faker->randomDigitNotNull();
        $notStartedModels = $modelClass::factory()->count($notStartedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->addDay(),
        ]);

        $returnedSubscriptions = $modelClass::onlyNotStarted()->get();

        $this->assertEqualsCanonicalizing(
            $notStartedModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }
}
