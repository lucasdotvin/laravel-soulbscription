<?php

namespace LucasDotVin\Soulbscription\Models\Concerns;

use LucasDotVin\Soulbscription\Models\Scopes\ExpiringWithGraceDaysScope;

trait ExpiresAndHasGraceDays
{
    public static function bootExpiresAndHasGraceDays()
    {
        static::addGlobalScope(new ExpiringWithGraceDaysScope());
    }

    public function initializeExpiresAndHasGraceDays()
    {
        if (! isset($this->casts['expired_at'])) {
            $this->casts['expired_at'] = 'datetime';
        }

        if (! isset($this->casts['grace_days_ended_at'])) {
            $this->casts['grace_days_ended_at'] = 'datetime';
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
