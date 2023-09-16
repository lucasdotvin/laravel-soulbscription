<?php

namespace LucasDotVin\Soulbscription\Models\Concerns;

use LucasDotVin\Soulbscription\Models\Scopes\SuppressingScope;

trait Suppresses
{
    public static function bootSuppresses(): void
    {
        static::addGlobalScope(new SuppressingScope());
    }

    public function initializeSuppresses(): void
    {
        if (! isset($this->casts['suppressed_at'])) {
            $this->casts['suppressed_at'] = 'datetime';
        }
    }

    public function suppressed(): bool
    {
        if (empty($this->suppressed_at)) {
            return false;
        }

        return $this->suppressed_at->isPast();
    }

    public function notSuppressed(): bool
    {
        return ! $this->suppressed();
    }
}
