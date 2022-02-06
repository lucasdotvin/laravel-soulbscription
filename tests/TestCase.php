<?php

namespace LucasDotDev\Soulbscription\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use LucasDotDev\Soulbscription\SoulbscriptionServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) =>
                'LucasDotDev\\Soulbscription\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            SoulbscriptionServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}
