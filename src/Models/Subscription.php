<?php

namespace LucasDotDev\Soulbscription\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LucasDotDev\Soulbscription\Models\Concerns\Expires;
use LucasDotDev\Soulbscription\Models\Concerns\Starts;
use LucasDotDev\Soulbscription\Models\Concerns\Suppresses;

class Subscription extends Model
{
    use Expires;
    use HasFactory;
    use SoftDeletes;
    use Starts;
    use Suppresses;

    protected $dates = [
        'canceled_at',
    ];

    protected $fillable = [
        'canceled_at',
        'expired_at',
        'started_at',
        'suppressed_at',
        'was_switched',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function renewals()
    {
        return $this->hasMany(SubscriptionRenewal::class);
    }

    public function subscriber()
    {
        return $this->morphTo('subscriber');
    }

    public function scopeActive(Builder $query)
    {
        return $query->withoutNotStarted();
    }

    public function scopeNotActive(Builder $query)
    {
        return $query->where(function (Builder $query) {
            return $query->onlyExpired()
                ->onlyNotStarted()
                ->onlySuppressed();
        });
    }

    public function scopeCanceled(Builder $query)
    {
        return $query->whereNotNull('canceled_at');
    }

    public function scopeNotCanceled(Builder $query)
    {
        return $query->whereNull('canceled_at');
    }

    public function markAsSwitched(): self
    {
        return $this->fill([
            'was_switched' => true,
        ]);
    }

    public function renew(): self
    {
        $overdue = $this->expired_at->isPast();

        $this->renewals()->create([
            'renewal' => true,
            'overdue' => $overdue,
        ]);

        $expiration = $this->plan->calculateNextRecurrenceEnd();

        $this->update([
            'expired_at' => $expiration,
        ]);

        return $this;
    }

    public function cancel(): self
    {
        return $this->fill([
            'canceled_at' => now(),
        ]);
    }
}
