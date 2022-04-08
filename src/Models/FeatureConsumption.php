<?php

namespace LucasDotVin\Soulbscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LucasDotVin\Soulbscription\Models\Concerns\Expires;

class FeatureConsumption extends Model
{
    use Expires;
    use HasFactory;

    protected $fillable = [
        'consumption',
        'expired_at',
    ];

    public function feature()
    {
        return $this->belongsTo(config('soulbscription.models.feature'));
    }

    public function subscriber()
    {
        return $this->morphTo('subscriber');
    }
}
