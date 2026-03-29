<?php

namespace LucasDotVin\Soulbscription\Models;

use Illuminate\Database\Eloquent\Model;
use LucasDotVin\Soulbscription\Contracts\FeatureTicketContract;
use LucasDotVin\Soulbscription\Models\Concerns\Expires;

class FeatureTicket extends Model implements FeatureTicketContract
{
    use Expires;

    protected $fillable = [
        'charges',
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
