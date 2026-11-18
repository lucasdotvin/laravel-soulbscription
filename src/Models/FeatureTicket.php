<?php

namespace LucasDotVin\Soulbscription\Models;

use Illuminate\Database\Eloquent\Model;
use LucasDotVin\Soulbscription\Models\Concerns\Expires;

class FeatureTicket extends Model
{
    use Expires;

    protected $table = 'feature_tickets';

    protected $fillable = [
        'charges',
        'expired_at',
    ];

    public function feature()
    {
        return $this->belongsTo(config('soulbscription.models.feature'), 'feature_id');
    }

    public function subscriber()
    {
        return $this->morphTo('subscriber');
    }
}
