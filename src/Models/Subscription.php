<?php

namespace LucasDotDev\Soulbscription\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = [
        'canceled_at',
        'expires_at',
        'suppressed_at',
    ];

    protected $fillable = [
        'canceled_at',
        'expires_at',
        'suppressed_at',
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
        return $query->unexpired()->unsuppressed();
    }

    public function scopeInactive(Builder $query)
    {
        return $query->where(function (Builder $query) {
            return $query->expired()
                ->orWhere(fn (Builder $query) => $query->suppressed());
        });
    }

    public function scopeCanceled(Builder $query)
    {
        return $query->whereNotNull('canceled_at');
    }

    public function scopeUncanceled(Builder $query)
    {
        return $query->whereNull('canceled_at');
    }

    public function scopeExpired(Builder $query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeUnexpired(Builder $query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeSuppressed(Builder $query)
    {
        return $query->where('suppressed_at', '<', now());
    }

    public function scopeUnsuppressed(Builder $query)
    {
        return $query->where('suppressed_at', '<', now());
    }

    public function renew(): self
    {
        $overdue = $this->expires_at->isPast();

        $this->renewals()->create([
            'renewal' => true,
            'overdue' => $overdue,
        ]);

        $expiration = $this->plan->calculateNextRecurrenceEnd();

        $this->update([
            'expires_at' => $expiration,
        ]);

        return $this;
    }

    public function suppress(): self
    {
        return $this->fill([
            'suppressed_at' => now(),
        ]);
    }
}
