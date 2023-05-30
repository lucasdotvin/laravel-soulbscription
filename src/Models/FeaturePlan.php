<?php

namespace LucasDotVin\Soulbscription\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int                                             $id
 * @property int                                             $feature_id
 * @property int                                             $plan_id
 * @property string                                          $charges
 * @property \Illuminate\Support\Carbon|null                 $created_at
 * @property \Illuminate\Support\Carbon|null                 $updated_at
 * @property-read \LucasDotVin\Soulbscription\Models\Feature $feature
 * @property-read \LucasDotVin\Soulbscription\Models\Plan    $plan
 * @method static \Illuminate\Database\Eloquent\Builder|FeaturePlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeaturePlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeaturePlan query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeaturePlan whereCharges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeaturePlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeaturePlan whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeaturePlan whereFeatureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeaturePlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeaturePlan whereUpdatedAt($value)
 */
class FeaturePlan extends Pivot
{
    protected $fillable = [
        'charges',
    ];

    public function feature(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(config('soulbscription.models.feature'));
    }

    public function plan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(config('soulbscription.models.plan'));
    }
}
