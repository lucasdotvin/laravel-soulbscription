<?php

namespace LucasDotDev\Soulbscription\Models;

use LucasDotDev\Soulbscription\Models\Concerns\HandlesRecurrence;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HandlesRecurrence;
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
