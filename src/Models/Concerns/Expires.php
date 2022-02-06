<?php

namespace LucasDotDev\Soulbscription\Models\Concerns;

use Illuminate\Support\Carbon;

trait Expires
{
    public function calculateExpiration(?Carbon $start = null)
    {
        if (empty($start)) {
            $start = now();
        }

        return $start->add($this->periodicity, $this->periodicity_type);
    }
}
