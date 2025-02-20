<?php

namespace Tests\Feature\Models;

use Tests\TestCase;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SuppressingScopeTest extends TestCase
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

    public function testSuppressedModelsAreNotReturnedByDefault()
    {
        $modelClass = $this->getModelClass();
        $suppressedModelsCount = $this->faker->randomDigitNotNull();

        $modelClass::factory()
            ->count($suppressedModelsCount)
            ->suppressed()
            ->notExpired()
            ->started()
            ->create();

        $notSuppressedModelsCount = $this->faker->randomDigitNotNull();
        $notSuppressedModels = $modelClass::factory()
            ->count($notSuppressedModelsCount)
            ->notSuppressed()
            ->notExpired()
            ->started()
            ->create();

        $returnedSubscriptions = $modelClass::all();

        $this->assertEqualsCanonicalizing(
            $notSuppressedModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testSuppressedModelsAreNotReturnedWhenCallingWithoutNotSuppressed()
    {
        $modelClass = $this->getModelClass();
        $suppressedModelsCount = $this->faker->randomDigitNotNull();

        $modelClass::factory()
            ->count($suppressedModelsCount)
            ->suppressed()
            ->notExpired()
            ->started()
            ->create();

        $notSuppressedModelsCount = $this->faker->randomDigitNotNull();
        $notSuppressedModels = $modelClass::factory()
            ->count($notSuppressedModelsCount)
            ->notSuppressed()
            ->notExpired()
            ->started()
            ->create();

        $returnedSubscriptions = $modelClass::withoutSuppressed()->get();

        $this->assertEqualsCanonicalizing(
            $notSuppressedModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testSuppressedModelsAreReturnedWhenCallingMethodWithNotSuppressed()
    {
        $modelClass = $this->getModelClass();
        $suppressedModelsCount = $this->faker->randomDigitNotNull();

        $suppressedModels = $modelClass::factory()
            ->count($suppressedModelsCount)
            ->suppressed()
            ->notExpired()
            ->started()
            ->create();

        $notSuppressedModelsCount = $this->faker->randomDigitNotNull();
        $notSuppressedModels = $modelClass::factory()
            ->count($notSuppressedModelsCount)
            ->notSuppressed()
            ->notExpired()
            ->started()
            ->create();

        $expectedSubscriptions = $suppressedModels->concat($notSuppressedModels);
        $returnedSubscriptions = $modelClass::withSuppressed()->get();

        $this->assertEqualsCanonicalizing(
            $expectedSubscriptions->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testSuppressedModelsAreReturnedWhenCallingMethodWithNotSuppressedAndPassingAFalse()
    {
        $modelClass = $this->getModelClass();
        $suppressedModelsCount = $this->faker->randomDigitNotNull();

        $modelClass::factory()
            ->count($suppressedModelsCount)
            ->suppressed()
            ->notExpired()
            ->started()
            ->create();

        $notSuppressedModelsCount = $this->faker->randomDigitNotNull();
        $notSuppressedModels = $modelClass::factory()
            ->count($notSuppressedModelsCount)
            ->notSuppressed()
            ->notExpired()
            ->started()
            ->create();

        $returnedSubscriptions = $modelClass::withSuppressed(false)->get();

        $this->assertEqualsCanonicalizing(
            $notSuppressedModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testOnlySuppressedModelsAreReturnedWhenCallingMethodOnlyNotSuppressed()
    {
        $modelClass = $this->getModelClass();
        $suppressedModelsCount = $this->faker->randomDigitNotNull();

        $suppressedModels = $modelClass::factory()
            ->count($suppressedModelsCount)
            ->suppressed()
            ->notExpired()
            ->started()
            ->create();

        $notSuppressedModelsCount = $this->faker->randomDigitNotNull();

        $modelClass::factory()
            ->count($notSuppressedModelsCount)
            ->notSuppressed()
            ->notExpired()
            ->started()
            ->create();

        $returnedSubscriptions = $modelClass::onlySuppressed()->get();

        $this->assertEqualsCanonicalizing(
            $suppressedModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }
}
