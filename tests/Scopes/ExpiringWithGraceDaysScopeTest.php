<?php

namespace LucasDotVin\Soulbscription\Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use LucasDotVin\Soulbscription\Models\Subscription;
use LucasDotVin\Soulbscription\Tests\TestCase;

class ExpiringWithGraceDaysScopeTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public const MODEL = Subscription::class;

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

        $returnedSubscriptions = self::MODEL::all();

        $this->assertEqualsCanonicalizing(
            $unexpiredModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }

    public function testExpiredModelsWithGraceDaysAreReturnedByDefault()
    {
        $expiredModelsWithoutGraceDaysCount = $this->faker()->randomDigitNotNull();
        self::MODEL::factory()->count($expiredModelsWithoutGraceDaysCount)->create([
            'expired_at' => now()->subDay(),
            'grace_days_ended_at' => null,
        ]);

        $expiredModelsWithPastGraceDaysCount = $this->faker()->randomDigitNotNull();
        self::MODEL::factory()->count($expiredModelsWithPastGraceDaysCount)->create([
            'expired_at' => now()->subDay(),
            'grace_days_ended_at' => now()->subDay(),
        ]);

        $expiredModelsWithFutureGraceDaysCount = $this->faker()->randomDigitNotNull();
        $expiredModelsWithFutureGraceDays = self::MODEL::factory()
            ->count($expiredModelsWithFutureGraceDaysCount)->create([
                'expired_at' => now()->subDay(),
                'grace_days_ended_at' => now()->addDay(),
            ]);

        $returnedSubscriptions = self::MODEL::all();

        $this->assertEqualsCanonicalizing(
            $expiredModelsWithFutureGraceDays->pluck('id'),
            $returnedSubscriptions->pluck('id'),
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

        $expectedSubscriptions = $expiredModels->concat($unexpiredModels);

        $returnedSubscriptions = self::MODEL::withExpired()->get();

        $this->assertEqualsCanonicalizing(
            $expectedSubscriptions->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
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

        $returnedSubscriptions = self::MODEL::onlyExpired()->get();

        $this->assertEqualsCanonicalizing(
            $expiredModels->pluck('id')->toArray(),
            $returnedSubscriptions->pluck('id')->toArray(),
        );
    }
}
