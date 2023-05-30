<?php

namespace LucasDotVin\Soulbscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LucasDotVin\Soulbscription\Models\Concerns\Expires;

/**
 * @package LucasDotVin\Soulbscription\Models
 * @property int                                             $id
 * @property int                                             $feature_id
 * @property int                                             $subscriber_id
 * @property string                                          $subscriber_type
 * @property int                                             $consumption
 * @property \Illuminate\Support\Carbon                      $expired_at
 * @property \Illuminate\Support\Carbon|null                 $created_at
 * @property \Illuminate\Support\Carbon|null                 $updated_at
 * @property-read \LucasDotVin\Soulbscription\Models\Feature $feature
 * @property-read \Illuminate\Database\Eloquent\Model        $subscriber
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureConsumption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureConsumption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureConsumption query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureConsumption whereConsumption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureConsumption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureConsumption whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureConsumption whereFeatureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureConsumption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureConsumption whereSubscriberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureConsumption whereSubscriberType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureConsumption whereUpdatedAt($value)
 *
 */
class FeatureConsumption extends Model
{
    use Expires;
    use HasFactory;

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    protected $fillable = [
        'consumption',
        'expired_at',
    ];

    public function feature(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(config('soulbscription.models.feature'));
    }

    public function subscriber(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo('subscriber');
    }
}
