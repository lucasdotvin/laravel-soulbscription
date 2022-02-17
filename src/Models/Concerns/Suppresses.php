<?php

namespace LucasDotDev\Soulbscription\Models\Concerns;

use Illuminate\Support\Carbon;
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

    public function suppress(?Carbon $suppressation = null)
    {
        return $this->fill([
            'suppressed_at' => $suppressation ?: now(),
        ]);
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
