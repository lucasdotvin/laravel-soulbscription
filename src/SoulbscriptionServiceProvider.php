<?php

namespace LucasDotVin\Soulbscription;

use Illuminate\Support\ServiceProvider;

class SoulbscriptionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->mergeConfigFrom(__DIR__ . '/../config/soulbscription.php', 'soulbscription');

        $this->publishes([
            __DIR__ . '/../config/soulbscription.php' => config_path('soulbscription.php'),
        ], 'soulbscription-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'soulbscription-migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations/upgrades/v2.0' => database_path('migrations'),
        ], 'soulbscription-migrations-upgrades');
    }
}
