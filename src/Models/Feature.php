<?php

namespace LucasDotDev\Soulbscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LucasDotDev\Soulbscription\Models\Concerns\Expires;

class Feature extends Model
{
    use Expires;
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'consumable' => 'boolean',
        'recharges_on_renewal' => 'boolean',
    ];

    protected $fillable = [
        'consumable',
        'name',
        'periodicity_type',
        'periodicity',
        'slug',
    ];

    public function plans()
    {
        return $this->belongsToMany(Plan::class)
            ->using(PlanFeature::class);
    }
}
