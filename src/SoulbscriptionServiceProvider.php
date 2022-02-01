<?php

namespace LucasDotDev\Soulbscription;

use LucasDotDev\Soulbscription\Commands\SoulbscriptionCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SoulbscriptionServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-soulbscription')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-soulbscription_table')
            ->hasCommand(SoulbscriptionCommand::class);
    }
}
