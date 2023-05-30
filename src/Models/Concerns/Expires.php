<?php

namespace LucasDotVin\Soulbscription\Models\Concerns;

use LucasDotVin\Soulbscription\Models\Scopes\ExpiringScope;

/**
 * @property \Illuminate\Support\Carbon $expired_at
 */
trait Expires
{
    public static function bootExpires(): void
    {
        static::addGlobalScope(new ExpiringScope());
    }

    public function initializeExpires(): void
    {
        if (! isset($this->casts['expired_at'])) {
            $this->casts['expired_at'] = 'datetime';
        }
    }

    public function expired(): bool
    {
        return $this->expired_at->isPast();
    }

    public function notExpired(): bool
    {
        return ! $this->expired();
    }
}
