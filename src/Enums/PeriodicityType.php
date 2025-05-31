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

        $unitInPlural = Str::plural(Str::studly(Str::lower($unit)));

        $differenceMethodName = 'diffIn' . $unitInPlural;
        $difference = abs($from->{$differenceMethodName}($to));

        return $difference * $delta;
    }
}
