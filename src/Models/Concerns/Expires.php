<?php

namespace LucasDotVin\Soulbscription\Models\Concerns;

use LucasDotVin\Soulbscription\Models\Scopes\ExpiringScope;

trait Expires
{
    public static function bootExpires()
    {
        static::addGlobalScope(new ExpiringScope());
    }

    public function initializeExpires()
    {
        if (! isset($this->casts['expired_at'])) {
            $this->casts['expired_at'] = 'datetime';
        }
    }

    public function expired()
    {
        if (is_null($this->expired_at)) {
            return false;
        }

        return $this->expired_at->isPast();
    }

    public function notExpired()
    {
        return ! $this->expired();
    }
}
