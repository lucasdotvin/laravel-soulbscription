<?php

namespace LucasDotDev\Soulbscription;

use Illuminate\Support\ServiceProvider;

class SoulbscriptionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
