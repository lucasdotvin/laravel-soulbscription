<?php

namespace LucasDotDev\Soulbscription\Models\Concerns;

use Illuminate\Support\Carbon;
use LucasDotDev\Soulbscription\Enums\PeriodicityType;

trait HandlesRecurrence
{
    public function calculateNextRecurrenceEnd(?Carbon $start = null)
    {
        if (empty($start)) {
            $start = now();
        }

        $recurrences = PeriodicityType::getDateDifference(from: now(), to: $start, unit: $this->periodicity_type);
        $expirationDate = $start->add($this->periodicity_type, $this->periodicity + $recurrences);

        return $expirationDate;
    }
}
