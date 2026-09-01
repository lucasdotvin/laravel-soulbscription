<?php

namespace Tests\Feature\Models\Concerns;

use Tests\TestCase;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StartsTest extends TestCase
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

    public function testModelReturnsStartedWhenStartedAtIsOnThePast()
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->make([
            'started_at' => now()->subDay(),
        ]);

        $this->assertTrue($model->started());
        $this->assertFalse($model->notStarted());
    }

    public function testModelReturnsNotStartedWhenStartedAtIsOnTheFuture()
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->make([
            'started_at' => now()->addDay(),
        ]);

        $this->assertFalse($model->started());
        $this->assertTrue($model->notStarted());
    }

    public function testModelReturnsNotStartedWhenStartedAtIsNull()
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->make();
        $model->started_at = null;

        $this->assertFalse($model->started());
        $this->assertTrue($model->notStarted());
    }
}
