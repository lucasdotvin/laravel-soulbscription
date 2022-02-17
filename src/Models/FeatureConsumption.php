<?php

namespace LucasDotDev\Soulbscription\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureConsumption extends Model
{
    use HasFactory;

    protected $dates = [
        'expires_at',
    ];

    protected $fillable = [
        'consumption',
        'expired_at',
    ];

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }

    public function subscriber()
    {
        return $this->morphTo('subscriber');
    }

    public function scopeExpired(Builder $query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeUnexpired(Builder $query)
    {
        return $query->where('expires_at', '>', now());
    }
}
