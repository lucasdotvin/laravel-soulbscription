<?php

namespace LucasDotVin\Soulbscription\Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use LucasDotVin\Soulbscription\Models\Subscription;
use LucasDotVin\Soulbscription\Tests\TestCase;

class SuppressingScopeTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public const MODEL = Subscription::class;

    public function testSuppressedModelsAreNotReturnedByDefault()
    {
        $suppressedModelsCount = $this->faker()->randomDigitNotNull();
        self::MODEL::factory()
            ->count($suppressedModelsCount)
            ->suppressed()
            ->notExpired()
            ->started()
            ->create();

        $notSuppressedModelsCount = $this->faker()->randomDigitNotNull();
        $notSuppressedModels = self::MODEL::factory()
            ->count($notSuppressedModelsCount)
            ->notSuppressed()
            ->notExpired()
            ->started()
            ->create();

        $returnedSubscriptions = self::MODEL::all();

        $this->assertEqualsCanonicalizing(
            $notSuppressedModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testSuppressedModelsAreNotReturnedWhenCallingWithoutNotSuppressed()
    {
        $suppressedModelsCount = $this->faker()->randomDigitNotNull();
        self::MODEL::factory()
            ->count($suppressedModelsCount)
            ->suppressed()
            ->notExpired()
            ->started()
            ->create();

        $notSuppressedModelsCount = $this->faker()->randomDigitNotNull();
        $notSuppressedModels = self::MODEL::factory()
            ->count($notSuppressedModelsCount)
            ->notSuppressed()
            ->notExpired()
            ->started()
            ->create();

        $returnedSubscriptions = self::MODEL::withoutSuppressed()->get();

        $this->assertEqualsCanonicalizing(
            $notSuppressedModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testSuppressedModelsAreReturnedWhenCallingMethodWithNotSuppressed()
    {
        $suppressedModelsCount = $this->faker()->randomDigitNotNull();
        $suppressedModels = self::MODEL::factory()
            ->count($suppressedModelsCount)
            ->suppressed()
            ->notExpired()
            ->started()
            ->create();

        $notSuppressedModelsCount = $this->faker()->randomDigitNotNull();
        $notSuppressedModels = self::MODEL::factory()
            ->count($notSuppressedModelsCount)
            ->notSuppressed()
            ->notExpired()
            ->started()
            ->create();

        $expectedSubscriptions = $suppressedModels->concat($notSuppressedModels);

        $returnedSubscriptions = self::MODEL::withSuppressed()->get();

        $this->assertEqualsCanonicalizing(
            $expectedSubscriptions->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testSuppressedModelsAreReturnedWhenCallingMethodWithNotSuppressedAndPassingAFalse()
    {
        $suppressedModelsCount = $this->faker()->randomDigitNotNull();
        self::MODEL::factory()
            ->count($suppressedModelsCount)
            ->suppressed()
            ->notExpired()
            ->started()
            ->create();

        $notSuppressedModelsCount = $this->faker()->randomDigitNotNull();
        $notSuppressedModels = self::MODEL::factory()
            ->count($notSuppressedModelsCount)
            ->notSuppressed()
            ->notExpired()
            ->started()
            ->create();

        $returnedSubscriptions = self::MODEL::withSuppressed(false)->get();

        $this->assertEqualsCanonicalizing(
            $notSuppressedModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testOnlySuppressedModelsAreReturnedWhenCallingMethodOnlyNotSuppressed()
    {
        $suppressedModelsCount = $this->faker()->randomDigitNotNull();
        $suppressedModels = self::MODEL::factory()
            ->count($suppressedModelsCount)
            ->suppressed()
            ->notExpired()
            ->started()
            ->create();

        $notSuppressedModelsCount = $this->faker()->randomDigitNotNull();
        self::MODEL::factory()
            ->count($notSuppressedModelsCount)
            ->notSuppressed()
            ->notExpired()
            ->started()
            ->create();

        $returnedSubscriptions = self::MODEL::onlySuppressed()->get();

        $this->assertEqualsCanonicalizing(
            $suppressedModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }
}
