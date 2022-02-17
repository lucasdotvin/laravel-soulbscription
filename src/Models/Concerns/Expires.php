<?php

namespace LucasDotDev\Soulbscription\Models\Concerns;

use LucasDotDev\Soulbscription\Models\Scopes\ExpiringScope;

trait Expires
{
    public static function bootExpires()
    {
        static::addGlobalScope(new ExpiringScope);
    }

    public function initializeExpires()
    {
        if (! isset($this->casts['expired_at'])) {
            $this->casts['expired_at'] = 'datetime';
        }
    }

    public function expired()
    {
        return ! is_null($this->expired_at);
    }

    public function notExpired()
    {
        return is_null($this->expired_at);
    }
}
