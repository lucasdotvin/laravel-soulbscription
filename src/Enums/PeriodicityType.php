<?php

namespace LucasDotDev\Soulbscription\Enums;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

enum PeriodicityType
{
    case Year;
    case Month;
    case Week;
    case Day;

    public static function getDateDifference(Carbon $from, Carbon $to, string|PeriodicityType $unit): int
    {
        $unitName     = $unit->name ?? $unit;
        $unitInPlural = Str::plural($unitName);

        $differenceMethodName = 'diffIn' . $unitInPlural;

        return $from->{$differenceMethodName}($to);
    }
}
