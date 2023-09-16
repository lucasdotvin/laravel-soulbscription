<?php

namespace LucasDotVin\Soulbscription\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property bool   $overdue
 * @property bool   $renewal
 * @property string $created_at
 * @property string $updated_at

 * @method static Builder|Subscription newModelQuery()
 * @method static Builder|Subscription newQuery()
 * @method static Builder|Subscription query()
 * @method static Builder|Subscription whereCanceledAt($value)
 * @method static Builder|Subscription whereCreatedAt($value)
 * @method static Builder|Subscription whereDeletedAt($value)
 * @method static Builder|Subscription whereExpiredAt($value)
 * @method static Builder|Subscription whereGraceDaysEndedAt($value)
 * @method static Builder|Subscription whereId($value)
 * @method static Builder|Subscription wherePlanId($value)
 * @method static Builder|Subscription whereStartedAt($value)
 * @method static Builder|Subscription whereSubscriberId($value)
 * @method static Builder|Subscription whereSubscriberType($value)
 * @method static Builder|Subscription whereSuppressedAt($value)
 * @method static Builder|Subscription whereUpdatedAt($value)
 * @method static Builder|Subscription whereWasSwitched($value)
 * @method static Builder|Subscription canceled()
 * @method static Builder|Subscription notCanceled()
 * @method static Builder|Subscription notActive()
 * @method static Builder|Subscription expiringWithGraceDays()
 */
class SubscriptionRenewal extends Model
{
    use HasFactory;

    protected $casts = [
        'overdue'    => 'boolean',
        'renewal'    => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
        'overdue',
        'renewal',
    ];

    public function subscription(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(config('soulbscription.models.subscription'));
    }
}
