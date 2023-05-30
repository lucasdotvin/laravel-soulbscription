<?php

namespace LucasDotVin\Soulbscription\Enums;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PeriodicityType
{
    public const YEAR = 'Year';

    public const MONTH = 'Month';

    public const WEEK = 'Week';

    public const DAY = 'Day';

    public static function getDateDifference(Carbon $from, Carbon $to, string $unit): int
    {
        $unitInPlural = Str::plural($unit);

        $differenceMethodName = 'diffIn' . $unitInPlural;

        return $from->{$differenceMethodName}($to);
    }
}
