<?php

namespace LucasDotDev\Soulbscription\Models;

use LucasDotDev\Soulbscription\Models\Concerns\HandlesRecurrence;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feature extends Model
{
    use HandlesRecurrence;
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'consumable'           => 'boolean',
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
