<?php

namespace LucasDotDev\Soulbscription\Enums;

enum PeriodicityType: string
{
    case Year  = 'year';
    case Month = 'month';
    case Week  = 'week';
    case Day   = 'day';
}
