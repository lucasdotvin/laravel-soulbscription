<?php

namespace LucasDotDev\Soulbscription\Models\Concerns;

use LucasDotDev\Soulbscription\Models\Scopes\SuppressingScope;

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
        return ! is_null($this->suppressed_at);
    }

    public function notSuppressed()
    {
        return is_null($this->suppressed_at);
    }
}
