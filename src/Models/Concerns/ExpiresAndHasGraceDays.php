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
        if (is_null($this->grace_days_ended_at)) {
            return $this->expired_at->isPast();
        }

        return $this->expired_at->isPast()
            and $this->grace_days_ended_at->isPast();
    }

    public function notExpired()
    {
        if (is_null($this->grace_days_ended_at)) {
            return $this->expired_at->isFuture();
        }

        return $this->expired_at->isFuture()
            or $this->grace_days_ended_at->isFuture();
    }
}
