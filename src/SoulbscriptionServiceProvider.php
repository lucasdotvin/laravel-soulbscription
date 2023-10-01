<?php

namespace LucasDotVin\Soulbscription;

use Illuminate\Support\ServiceProvider;

class SoulbscriptionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/soulbscription.php', 'soulbscription');

        if (! config('soulbscription.database.cancel_migrations_autoloading')) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        $this->publishes([
            __DIR__ . '/../config/soulbscription.php' => config_path('soulbscription.php'),
        ], 'soulbscription-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'soulbscription-migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations/upgrades/1.x-2.x' => database_path('migrations'),
        ], 'soulbscription-migrations-upgrades-1.x-2.x');

        $this->publishes([
            __DIR__ . '/../database/migrations/upgrades/2.1-2.2' => database_path('migrations'),
        ], 'soulbscription-migrations-upgrades-2.1-2.2');

        $this->publishes([
            __DIR__ . '/../database/migrations/upgrades/2.4-2.5' => database_path('migrations'),
        ], 'soulbscription-migrations-upgrades-2.4-2.5');

        $this->publishes([
            __DIR__ . '/../database/migrations/upgrades/2.5-2.6' => database_path('migrations'),
        ], 'soulbscription-migrations-upgrades-2.5-2.6');

        $this->publishes([
            __DIR__ . '/../database/migrations/upgrades/4.0-4.1' => database_path('migrations'),
        ], 'soulbscription-migrations-upgrades-4.0-4.1');
    }
}
