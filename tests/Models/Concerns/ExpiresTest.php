<?php

namespace Tests\Feature\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use InvalidArgumentException;
use Tests\TestCase;

class ExpiresTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

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
        $expiredModelsCount = $this->faker()->randomDigitNotNull();
        $modelClass::factory()->count($expiredModelsCount)->create([
            'expired_at' => now()->subDay(),
        ]);

        $unexpiredModelsCount = $this->faker()->randomDigitNotNull();
        $unexpiredModels = $modelClass::factory()->count($unexpiredModelsCount)->create([
            'expired_at' => now()->addDay(),
        ]);

        $returnedSubscriptions = $modelClass::all();

        $this->assertEqualsCanonicalizing(
            $unexpiredModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testExpiredModelsAreReturnedWhenCallingMethodWithExpired()
    {
        $modelClass = $this->getModelClass();
        $expiredModelsCount = $this->faker()->randomDigitNotNull();
        $expiredModels = $modelClass::factory()->count($expiredModelsCount)->create([
            'expired_at' => now()->subDay(),
        ]);

        $unexpiredModelsCount = $this->faker()->randomDigitNotNull();
        $unexpiredModels = $modelClass::factory()->count($unexpiredModelsCount)->create([
            'expired_at' => now()->addDay(),
        ]);

        $expectedSubscriptions = $expiredModels->concat($unexpiredModels);

        $returnedSubscriptions = $modelClass::withExpired()->get();

        $this->assertEqualsCanonicalizing(
            $expectedSubscriptions->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testOnlyExpiredModelsAreReturnedWhenCallingMethodOnlyExpired()
    {
        $modelClass = $this->getModelClass();
        $expiredModelsCount = $this->faker()->randomDigitNotNull();
        $expiredModels = $modelClass::factory()->count($expiredModelsCount)->create([
            'expired_at' => now()->subDay(),
        ]);

        $unexpiredModelsCount = $this->faker()->randomDigitNotNull();
        $modelClass::factory()->count($unexpiredModelsCount)->create([
            'expired_at' => now()->addDay(),
        ]);

        $returnedSubscriptions = $modelClass::onlyExpired()->get();

        $this->assertEqualsCanonicalizing(
            $expiredModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testModelReturnsExpiredStatus()
    {
        $modelClass = $this->getModelClass();
        $expiredModel = $modelClass::factory()
            ->expired()
            ->create();

        $notExpiredModel = $modelClass::factory()
            ->notExpired()
            ->create();

        $this->assertTrue($expiredModel->expired());
        $this->assertFalse($notExpiredModel->expired());
    }

    public function testModelReturnsNotExpiredStatus()
    {
        $modelClass = $this->getModelClass();
        $expiredModel = $modelClass::factory()
            ->expired()
            ->create();

        $notExpiredModel = $modelClass::factory()
            ->notExpired()
            ->create();

        $this->assertFalse($expiredModel->notExpired());
        $this->assertTrue($notExpiredModel->notExpired());
    }

    public function testModelsWithoutExpirationDateAreReturnedByDefault()
    {
        $modelClass = $this->getModelClass();
        $expiredModelsCount = $this->faker()->randomDigitNotNull();
        $modelClass::factory()->count($expiredModelsCount)->create([
            'expired_at' => now()->subDay(),
        ]);

        $unexpiredModelsCount = $this->faker()->randomDigitNotNull();
        $unexpiredModels = $modelClass::factory()->count($unexpiredModelsCount)->create([
            'expired_at' => null,
        ]);

        $returnedSubscriptions = $modelClass::all();

        $this->assertEqualsCanonicalizing(
            $unexpiredModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }
}
