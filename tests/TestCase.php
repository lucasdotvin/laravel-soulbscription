<?php

namespace Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use LucasDotVin\Soulbscription\SoulbscriptionServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();

        Factory::guessFactoryNamesUsing(
            fn(string $modelName): string
                => 'LucasDotVin\\Soulbscription\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            SoulbscriptionServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }
}
