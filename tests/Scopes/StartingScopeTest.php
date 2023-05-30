<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use LucasDotVin\Soulbscription\Models\Subscription;
use Tests\TestCase;

class StartingScopeTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public const MODEL = Subscription::class;

    public function testNotStartedModelsAreNotReturnedByDefault(): void
    {
        $startedModelsCount = $this->faker()->randomDigitNotNull();
        $startedModels      = self::MODEL::factory()->count($startedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->subDay(),
        ]);

        $notStartedModelsCount = $this->faker()->randomDigitNotNull();
        self::MODEL::factory()->count($notStartedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->addDay(),
        ]);

        $returnedSubscriptions = self::MODEL::all();

        $this->assertEqualsCanonicalizing(
            $startedModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testNotStartedModelsAreNotReturnedWhenCallingWithoutNotStarted(): void
    {
        $startedModelsCount = $this->faker()->randomDigitNotNull();
        $startedModels      = self::MODEL::factory()->count($startedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->subDay(),
        ]);

        $notStartedModelsCount = $this->faker()->randomDigitNotNull();
        self::MODEL::factory()->count($notStartedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->addDay(),
        ]);

        $returnedSubscriptions = self::MODEL::withoutNotStarted()->get();

        $this->assertEqualsCanonicalizing(
            $startedModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testStartedModelsAreReturnedWhenCallingMethodWithNotStarted(): void
    {
        $startedModelsCount = $this->faker()->randomDigitNotNull();
        $startedModels      = self::MODEL::factory()->count($startedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->subDay(),
        ]);

        $notStartedModelsCount = $this->faker()->randomDigitNotNull();
        $notStartedModels      = self::MODEL::factory()->count($notStartedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->addDay(),
        ]);

        $expectedSubscriptions = $startedModels->concat($notStartedModels);

        $returnedSubscriptions = self::MODEL::withNotStarted()->get();

        $this->assertEqualsCanonicalizing(
            $expectedSubscriptions->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testNotStartedModelsAreReturnedWhenCallingMethodWithNotStartedAndPassingAFalse(): void
    {
        $startedModelsCount = $this->faker()->randomDigitNotNull();
        $startedModels      = self::MODEL::factory()->count($startedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->subDay(),
        ]);

        $notStartedModelsCount = $this->faker()->randomDigitNotNull();
        self::MODEL::factory()->count($notStartedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->addDay(),
        ]);

        $returnedSubscriptions = self::MODEL::withNotStarted(false)->get();

        $this->assertEqualsCanonicalizing(
            $startedModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testOnlyStartedModelsAreReturnedWhenCallingMethodOnlyNotStarted(): void
    {
        $startedModelsCount = $this->faker()->randomDigitNotNull();
        self::MODEL::factory()->count($startedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->subDay(),
        ]);

        $notStartedModelsCount = $this->faker()->randomDigitNotNull();
        $notStartedModels      = self::MODEL::factory()->count($notStartedModelsCount)->create([
            'expired_at' => now()->addDay(),
            'started_at' => now()->addDay(),
        ]);

        $returnedSubscriptions = self::MODEL::onlyNotStarted()->get();

        $this->assertEqualsCanonicalizing(
            $notStartedModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }
}
