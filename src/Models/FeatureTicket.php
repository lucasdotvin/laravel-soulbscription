<?php

namespace LucasDotVin\Soulbscription\Models;

use Illuminate\Database\Eloquent\Model;
use LucasDotVin\Soulbscription\Models\Concerns\Expires;

/**
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
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureTicket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureTicket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureTicket query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureTicket whereConsumption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureTicket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureTicket whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureTicket whereFeatureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureTicket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureTicket whereSubscriberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureTicket whereSubscriberType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureTicket whereUpdatedAt($value)
 *
 */
class FeatureTicket extends Model
{
    use Expires;

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    protected $fillable = [
        'charges',
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
