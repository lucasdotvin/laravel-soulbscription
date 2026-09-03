<?php

namespace LucasDotVin\Soulbscription\Enums;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PeriodicityType
{
    public const Year = 'Year';

    public const Month = 'Month';

    public const Week = 'Week';

    public const Day = 'Day';

    public static function getDateDifference(Carbon $from, Carbon $to, string $unit): int
    {
        if ($from->isAfter($to)) {
            $delta = -1;
        } else {
            $delta = 1;
        }

        $unitInPlural = Str::plural($unit);

        $differenceMethodName = 'diffIn' . $unitInPlural;
        // Carbon 3: diffIn*() return float, while the signature promises int — PHP 8.5
        // deprecates the implicit conversion whenever the difference is fractional.
        // Whole periods elapsed = the absolute difference truncated, exactly what
        // Carbon 2 returned here.
        $difference = (int) floor(abs($from->{$differenceMethodName}($to)));

        return $difference * $delta;
    }
}
