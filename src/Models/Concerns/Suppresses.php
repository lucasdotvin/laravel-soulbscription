<?php

namespace LucasDotVin\Soulbscription\Models\Concerns;

use LucasDotVin\Soulbscription\Models\Scopes\SuppressingScope;

trait Suppresses
{
    public static function bootSuppresses()
    {
        static::addGlobalScope(new SuppressingScope());
    }

    public function initializeSuppresses()
    {
        if (! isset($this->casts['suppressed_at'])) {
            $this->casts['suppressed_at'] = 'datetime';
        }
    }

    public function suppressed()
    {
        if (empty($this->suppressed_at)) {
            return false;
        }

        return $this->suppressed_at->isPast();
    }

    public function notSuppressed()
    {
        return ! $this->suppressed();
    }
}
