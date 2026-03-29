<?php

namespace LucasDotVin\Soulbscription\Models\Concerns;

use Illuminate\Support\Carbon;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use Illuminate\Support\Str;

/**
 * @property \LucasDotVin\Soulbscription\Enums\PeriodicityType $periodicity_type
 */
trait HandlesRecurrence
{
    public function calculateNextRecurrenceEnd(Carbon|string $start = null): Carbon
    {
        if (empty($start)) {
            $start = now();
        }

        if (is_string($start)) {
            $start = Carbon::parse($start);
        }

        $recurrences = max(
            PeriodicityType::getDateDifference(from: $start, to: now(), unit: $this->periodicity_type),
            0,
        );

        $expirationDate = $start->copy()->add(Str::lower($this->periodicity_type), $this->periodicity + $recurrences);

        return $expirationDate;
    }
}
