<?php

namespace LucasDotDev\Soulbscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LucasDotDev\Soulbscription\Models\Concerns\HandlesRecurrence;

class Feature extends Model
{
    use HandlesRecurrence;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'consumable',
        'name',
        'periodicity_type',
        'periodicity',
    ];

    public function plans()
    {
        return $this->belongsToMany(Plan::class)
            ->using(PlanFeature::class);
    }
}
