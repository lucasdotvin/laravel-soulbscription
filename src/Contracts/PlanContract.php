<?php

namespace LucasDotVin\Soulbscription\Contracts;

use Illuminate\Support\Carbon;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface PlanContract
{
    public function calculateNextRecurrenceEnd(Carbon|string $start = null): Carbon;

    public function calculateGraceDaysEnd(Carbon $recurrenceEnd): Carbon;
}