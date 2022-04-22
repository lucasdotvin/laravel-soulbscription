<?php

namespace LucasDotVin\Soulbscription\Models\Concerns;

use LucasDotVin\Soulbscription\Models\Scopes\StartingScope;

trait Starts
{
    public static function bootStarts()
    {
        static::addGlobalScope(new StartingScope());
    }

    public function initializeStarts()
    {
        if (! isset($this->casts['started_at'])) {
            $this->casts['started_at'] = 'datetime';
        }
    }

    public function started()
    {
        return ! is_null($this->started_at);
    }

    public function notStarted()
    {
        return is_null($this->started_at);
    }
}
