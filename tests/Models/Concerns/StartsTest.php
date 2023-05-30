<?php

namespace Tests\Models\Concerns;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use LucasDotVin\Soulbscription\Models\Subscription;
use Tests\TestCase;

class StartsTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public const MODEL = Subscription::class;

    public function testModelReturnsStartedWhenStartedAtIsOnThePast(): void
    {
        $model = self::MODEL::factory()->make([
            'started_at' => now()->subDay(),
        ]);

        $this->assertTrue($model->started());
        $this->assertFalse($model->notStarted());
    }

    public function testModelReturnsNotStartedWhenStartedAtIsOnTheFuture(): void
    {
        $model = self::MODEL::factory()->make([
            'started_at' => now()->addDay(),
        ]);

        $this->assertFalse($model->started());
        $this->assertTrue($model->notStarted());
    }

    public function testModelReturnsNotStartedWhenStartedAtIsNull(): void
    {
        $model             = self::MODEL::factory()->make();
        $model->started_at = null;

        $this->assertFalse($model->started());
        $this->assertTrue($model->notStarted());
    }
}
