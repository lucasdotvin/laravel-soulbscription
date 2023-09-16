<?php

namespace LucasDotVin\Soulbscription\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use LucasDotVin\Soulbscription\Events\SubscriptionCanceled;
use LucasDotVin\Soulbscription\Events\SubscriptionRenewed;
use LucasDotVin\Soulbscription\Events\SubscriptionScheduled;
use LucasDotVin\Soulbscription\Events\SubscriptionStarted;
use LucasDotVin\Soulbscription\Events\SubscriptionSuppressed;
use LucasDotVin\Soulbscription\Models\Concerns\ExpiresAndHasGraceDays;
use LucasDotVin\Soulbscription\Models\Concerns\Starts;
use LucasDotVin\Soulbscription\Models\Concerns\Suppresses;
use LucasDotVin\Soulbscription\Models\Scopes\ExpiringWithGraceDaysScope;
use LucasDotVin\Soulbscription\Models\Scopes\StartingScope;
use LucasDotVin\Soulbscription\Models\Scopes\SuppressingScope;

/**
 * @property int                                                                 $id
 * @property int                                                                 $plan_id
 * @property int                                                                 $subscriber_id
 * @property string                                                              $subscriber_type
 * @property \Illuminate\Support\Carbon                                          $canceled_at
 * @property \Illuminate\Support\Carbon                                          $expired_at
 * @property \Illuminate\Support\Carbon                                          $grace_days_ended_at
 * @property \Illuminate\Support\Carbon                                          $started_at
 * @property \Illuminate\Support\Carbon                                          $suppressed_at
 * @property bool                                                                $was_switched
 * @property \Illuminate\Support\Carbon|null                                     $created_at
 * @property \Illuminate\Support\Carbon |null                                    $updated_at
 * @property \Illuminate\Support\Carbon|null                                     $deleted_at
 * @property Plan                                                                $plan
 * @property Model                                                               $subscriber
 * @property-read \Illuminate\Database\Eloquent\Collection|SubscriptionRenewal[] $renewals
 * @property-read int|null                                                       $renewals_count
 * @property-read boolean                                                        $is_overdue
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
class Subscription extends Model
{
    use ExpiresAndHasGraceDays;
    use HasFactory;
    use SoftDeletes;
    use Starts;
    use Suppresses;

    protected $casts = [
        'canceled_at'         => 'datetime',
        'grace_days_ended_at' => 'datetime',
        'expired_at'          => 'datetime',
        'started_at'          => 'datetime',
        'suppressed_at'       => 'datetime',
    ];

    protected $fillable = [
        'canceled_at',
        'expired_at',
        'grace_days_ended_at',
        'started_at',
        'suppressed_at',
        'was_switched',
    ];

    protected $appends = [
        'is_overdue',
    ];

    public function plan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(config('soulbscription.models.plan'));
    }

    public function renewals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(config('soulbscription.models.subscription_renewal'));
    }

    public function subscriber(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo('subscriber');
    }

    public function scopeNotActive(Builder $query): Builder
    {
        return $query->withoutGlobalScopes([
            ExpiringWithGraceDaysScope::class,
            StartingScope::class,
            SuppressingScope::class,
        ])
            ->where(function (Builder $query) {
                $query->where(fn(Builder $query) => $query->onlyExpired())
                    ->orWhere(fn(Builder $query) => $query->onlyNotStarted())
                    ->orWhere(fn(Builder $query) => $query->onlySuppressed());
            });
    }

    public function scopeCanceled(Builder $query): Builder
    {
        return $query->whereNotNull('canceled_at');
    }

    public function scopeNotCanceled(Builder $query): Builder
    {
        return $query->whereNull('canceled_at');
    }

    public function markAsSwitched(): self
    {
        return $this->fill([
            'was_switched' => true,
        ]);
    }

    public function start(?\Illuminate\Support\Carbon $startDate = null): self
    {
        $startDate = $startDate ?: today();

        $this->fill(['started_at' => $startDate])
            ->save();

        if ($startDate->isToday()) {
            event(new SubscriptionStarted($this));
        } elseif ($startDate->isFuture()) {
            event(new SubscriptionScheduled($this));
        }

        return $this;
    }

    public function renew(?\Illuminate\Support\Carbon $expirationDate = null): self
    {
        $this->renewals()->create([
            'renewal' => true,
            'overdue' => $this->isOverdue,
        ]);

        $expirationDate = $this->getRenewedExpiration($expirationDate);

        $this->update([
            'expired_at' => $expirationDate,
        ]);

        event(new SubscriptionRenewed($this));

        return $this;
    }

    public function cancel(?\Illuminate\Support\Carbon $cancelDate = null): self
    {
        $cancelDate = $cancelDate ?: now();

        $this->fill(['canceled_at' => $cancelDate])
            ->save();

        event(new SubscriptionCanceled($this));

        return $this;
    }

    public function suppress(?\Illuminate\Support\Carbon $suppressation = null): static
    {
        $suppressationDate = $suppressation ?: now();

        $this->fill(['suppressed_at' => $suppressationDate])
            ->save();

        event(new SubscriptionSuppressed($this));

        return $this;
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->grace_days_ended_at) {
            return $this->expired_at->isPast()
                and $this->grace_days_ended_at->isPast();
        }

        return $this->expired_at->isPast();
    }

    private function getRenewedExpiration(?\Illuminate\Support\Carbon $expirationDate = null): Carbon
    {
        if (!empty($expirationDate)) {
            return $expirationDate;
        }

        if ($this->is_overdue) {
            return $this->plan->calculateNextRecurrenceEnd();
        }

        return $this->plan->calculateNextRecurrenceEnd($this->expired_at);
    }
}
