<?php

namespace LucasDotDev\Soulbscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LucasDotDev\Soulbscription\Models\Concerns\Expires;

class Plan extends Model
{
    use Expires;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'periodicity_type',
        'periodicity',
        'slug',
    ];

    public function features()
    {
        return $this->belongsToMany(Feature::class)
            ->using(PlanFeature::class)
            ->withPivot(app(PlanFeature::class)->getFillable());
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
