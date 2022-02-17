<?php

namespace LucasDotDev\Soulbscription\Models\Concerns;

use Illuminate\Support\Carbon;
use LucasDotDev\Soulbscription\Models\Scopes\StartingScope;

trait Starts
{
    public static function bootStarts()
    {
        static::addGlobalScope(new StartingScope);
    }

    public function initializeStarts()
    {
        if (! isset($this->casts['started_at'])) {
            $this->casts['started_at'] = 'datetime';
        }
    }

    public function start(?Carbon $startDate = null)
    {
        $this->fill([
            'started_at' => $startDate ?: today(),
        ]);

        if ($this->exists()) {
            $this->save();
        }

        return $this;
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
