<?php

namespace LucasDotDev\Soulbscription\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \LucasDotDev\Soulbscription\Soulbscription
 */
class Soulbscription extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-soulbscription';
    }
}
