<?php

namespace Tests\Feature\Models\Concerns;

use Tests\TestCase;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SuppressesTest extends TestCase
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

    public function testModelReturnsSuppressedWhenSuppressedAtIsOnThePast()
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->make([
            'suppressed_at' => now()->subDay(),
        ]);

        $this->assertTrue($model->suppressed());
        $this->assertFalse($model->notSuppressed());
    }

    public function testModelReturnsNotSuppressedWhenSuppressedAtIsOnTheFuture()
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->make([
            'suppressed_at' => now()->addDay(),
        ]);

        $this->assertFalse($model->suppressed());
        $this->assertTrue($model->notSuppressed());
    }

    public function testModelReturnsNotSuppressedWhenSuppressedAtIsNull()
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->make();
        $model->suppressed_at = null;

        $this->assertFalse($model->suppressed());
        $this->assertTrue($model->notSuppressed());
    }
}
