<?php

namespace LucasDotVin\Soulbscription\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class FeaturePlan extends Pivot
{
    protected $table = 'feature_plan';

    protected $fillable = [
        'charges',
    ];

    public function feature()
    {
        return $this->belongsTo(config('soulbscription.models.feature'), 'feature_id');
    }

    public function plan()
    {
        return $this->belongsTo(config('soulbscription.models.plan'), 'plan_id');
    }
}
