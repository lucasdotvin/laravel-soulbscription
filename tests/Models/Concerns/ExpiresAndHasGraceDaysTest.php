<?php

namespace Tests\Feature\Models\Concerns;

use Tests\TestCase;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LucasDotVin\Soulbscription\Models\Concerns\ExpiresAndHasGraceDays;
use LucasDotVin\Soulbscription\Models\Scopes\ExpiringWithGraceDaysScope;

class ExpiresAndHasGraceDaysTest extends TestCase
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

    public function testTraitAppliesScope()
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->create();

        $this->assertArrayHasKey(ExpiresAndHasGraceDays::class, class_uses_recursive($model));
        $this->assertArrayHasKey(ExpiringWithGraceDaysScope::class, $model->getGlobalScopes());
    }

    public function testModelReturnsExpiredStatus()
    {
        $modelClass = $this->getModelClass();

        $expiredModel = $modelClass::factory()->expired()->create();

        $expiredModelWithFutureGraceDays = $modelClass::factory()->expired()->create([
            'grace_days_ended_at' => now()->addDay(),
        ]);

        $expiredModelWithPastGraceDays = $modelClass::factory()->expired()->create([
            'grace_days_ended_at' => now()->subDay(),
        ]);

        $notExpiredModel = $modelClass::factory()->notExpired()->create();

        $this->assertTrue($expiredModel->expired());
        $this->assertFalse($expiredModelWithFutureGraceDays->expired());
        $this->assertTrue($expiredModelWithPastGraceDays->expired());
        $this->assertFalse($notExpiredModel->expired());
    }

    public function testModelReturnsNotExpiredStatus()
    {
        $modelClass = $this->getModelClass();

        $expiredModel = $modelClass::factory()->expired()->create();

        $modelWithNullExpiredAt = $modelClass::factory()->expired()->create([
            'expired_at' => null,
        ]);

        $expiredModelWithFutureGraceDays = $modelClass::factory()->expired()->create([
            'grace_days_ended_at' => now()->addDay(),
        ]);

        $expiredModelWithPastGraceDays = $modelClass::factory()->expired()->create([
            'grace_days_ended_at' => now()->subDay(),
        ]);

        $notExpiredModel = $modelClass::factory()->notExpired()->create();

        $this->assertFalse($expiredModel->notExpired());
        $this->assertTrue($expiredModelWithFutureGraceDays->notExpired());
        $this->assertFalse($expiredModelWithPastGraceDays->notExpired());
        $this->assertTrue($notExpiredModel->notExpired());
        $this->assertTrue($modelWithNullExpiredAt->notExpired());
    }

    public function testModelReturnsIfItHasExpired()
    {
        $modelClass = $this->getModelClass();

        $expiredModel = $modelClass::factory()->expired()->create();

        $modelWithNullExpiredAt = $modelClass::factory()->expired()->create([
            'expired_at' => null,
        ]);

        $expiredModelWithFutureGraceDays = $modelClass::factory()->expired()->create([
            'grace_days_ended_at' => now()->addDay(),
        ]);

        $expiredModelWithPastGraceDays = $modelClass::factory()->expired()->create([
            'grace_days_ended_at' => now()->subDay(),
        ]);

        $notExpiredModel = $modelClass::factory()->notExpired()->create();

        $this->assertTrue($expiredModel->hasExpired());
        $this->assertFalse($expiredModelWithFutureGraceDays->hasExpired());
        $this->assertTrue($expiredModelWithPastGraceDays->hasExpired());
        $this->assertFalse($notExpiredModel->hasExpired());
        $this->assertFalse($modelWithNullExpiredAt->hasExpired());
    }

    public function testModelReturnsIfItHasNotExpired()
    {
        $modelClass = $this->getModelClass();

        $expiredModel = $modelClass::factory()->expired()->create();

        $modelWithNullExpiredAt = $modelClass::factory()->expired()->create([
            'expired_at' => null,
        ]);

        $expiredModelWithFutureGraceDays = $modelClass::factory()->expired()->create([
            'grace_days_ended_at' => now()->addDay(),
        ]);

        $expiredModelWithPastGraceDays = $modelClass::factory()->expired()->create([
            'grace_days_ended_at' => now()->subDay(),
        ]);

        $notExpiredModel = $modelClass::factory()->notExpired()->create();

        $this->assertFalse($expiredModel->hasNotExpired());
        $this->assertTrue($expiredModelWithFutureGraceDays->hasNotExpired());
        $this->assertFalse($expiredModelWithPastGraceDays->hasNotExpired());
        $this->assertTrue($notExpiredModel->hasNotExpired());
        $this->assertTrue($modelWithNullExpiredAt->hasNotExpired());
    }
}
