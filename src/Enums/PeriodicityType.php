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
        $unitInPlural = Str::plural($unit);

        $differenceMethodName = 'diffIn' . $unitInPlural;

        return $from->{$differenceMethodName}($to);
    }
}
