<?php

namespace Tests\Feature\Models;

use Tests\TestCase;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpiringWithGraceDaysScopeTest extends TestCase
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

        $modelsWithNullExpiredAtCount = $this->faker->randomDigitNotNull();
        $modelsWithNullExpired = $modelClass::factory()->count($modelsWithNullExpiredAtCount)->create([
            'expired_at' => null,
        ]);

        $expectedSubscriptions = $unexpiredModels->concat($modelsWithNullExpired);
        $returnedSubscriptions = $modelClass::all();

        $this->assertEqualsCanonicalizing(
            $expectedSubscriptions->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testExpiredModelsWithGraceDaysAreReturnedByDefault()
    {
        $modelClass = $this->getModelClass();
        $expiredModelsWithoutGraceDaysCount = $this->faker->randomDigitNotNull();

        $modelClass::factory()->count($expiredModelsWithoutGraceDaysCount)->create([
            'expired_at' => now()->subDay(),
            'grace_days_ended_at' => null,
        ]);

        $expiredModelsWithPastGraceDaysCount = $this->faker->randomDigitNotNull();
        $modelClass::factory()->count($expiredModelsWithPastGraceDaysCount)->create([
            'expired_at' => now()->subDay(),
            'grace_days_ended_at' => now()->subDay(),
        ]);

        $expiredModelsWithFutureGraceDaysCount = $this->faker->randomDigitNotNull();
        $expiredModelsWithFutureGraceDays = $modelClass::factory()
            ->count($expiredModelsWithFutureGraceDaysCount)->create([
                'expired_at' => now()->subDay(),
                'grace_days_ended_at' => now()->addDay(),
            ]);

        $returnedSubscriptions = $modelClass::all();

        $this->assertEqualsCanonicalizing(
            $expiredModelsWithFutureGraceDays->pluck('id'),
            $returnedSubscriptions->pluck('id'),
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

        $expiredModelsWithFutureGraceDays = $modelClass::factory()
            ->count($this->faker->randomDigitNotNull())
            ->create([
                'expired_at' => now()->subDay(),
                'grace_days_ended_at' => now()->addDay(),
            ]);

        $modelsWithNullExpiredAtCount = $this->faker->randomDigitNotNull();
        $modelsWithNullExpired = $modelClass::factory()->count($modelsWithNullExpiredAtCount)->create([
            'expired_at' => null,
        ]);

        $expectedSubscriptions = $expiredModels->concat($unexpiredModels)
            ->concat($expiredModelsWithFutureGraceDays)
            ->concat($modelsWithNullExpired);

        $returnedSubscriptions = $modelClass::withExpired()->get();

        $this->assertEqualsCanonicalizing(
            $expectedSubscriptions->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
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

        $expiredModelsWithFutureGraceDays = $modelClass::factory()
            ->count($this->faker->randomDigitNotNull())
            ->create([
                'expired_at' => now()->subDay(),
                'grace_days_ended_at' => now()->addDay(),
            ]);

        $modelsWithNullExpiredAtCount = $this->faker->randomDigitNotNull();
        $modelsWithNullExpired = $modelClass::factory()->count($modelsWithNullExpiredAtCount)->create([
            'expired_at' => null,
        ]);

        $expectedSubscriptions = $unexpiredModels->concat($expiredModelsWithFutureGraceDays)
            ->concat($modelsWithNullExpired);

        $returnedSubscriptions = $modelClass::withExpired(false)->get();

        $this->assertEqualsCanonicalizing(
            $expectedSubscriptions->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
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

        $modelsWithNullExpiredAtCount = $this->faker->randomDigitNotNull();
        $modelClass::factory()->count($modelsWithNullExpiredAtCount)->create([
            'expired_at' => null,
        ]);

        $expiredModelsWithPastGraceDays = $modelClass::factory()
            ->count($this->faker->randomDigitNotNull())
            ->create([
                'expired_at' => now()->subDay(),
                'grace_days_ended_at' => now()->subDay(),
            ]);

        $expiredModelsWithNullGraceDays = $modelClass::factory()
            ->count($this->faker->randomDigitNotNull())
            ->create([
                'expired_at' => now()->subDay(),
                'grace_days_ended_at' => null,
            ]);

        $expectedSubscriptions = $expiredModels->concat($expiredModelsWithNullGraceDays)
            ->concat($expiredModelsWithPastGraceDays);

        $returnedSubscriptions = $modelClass::onlyExpired()->get();

        $this->assertEqualsCanonicalizing(
            $expectedSubscriptions->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }
}
