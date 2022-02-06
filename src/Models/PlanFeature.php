<?php

namespace LucasDotDev\Soulbscription\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PlanFeature extends Pivot
{
    protected $fillable = [
        'charges',
    ];

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
