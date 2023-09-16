<?php

namespace LucasDotVin\Soulbscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LucasDotVin\Soulbscription\Models\Concerns\HandlesRecurrence;

/**
 * @property int                             $id
 * @property string                          $consumable
 * @property string                          $name
 * @property string                          $periodicity_type
 * @property int                             $periodicity
 * @property int                             $quota
 * @property bool                            $postpaid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property Plan[]                          $plans
 * @property FeatureTicket[]                 $tickets
 * @method static \Illuminate\Database\Eloquent\Builder|Feature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature query()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereConsumable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature wherePeriodicity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature wherePeriodicityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature wherePostpaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereQuota($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Feature onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|Feature withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Feature withoutTrashed()
 *
 */
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
        'quota',
        'postpaid',
    ];

    public function plans(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(config('soulbscription.models.plan'))
            ->using(config('soulbscription.models.feature_plan'));
    }

    public function tickets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(config('soulbscription.models.feature_ticket'));
    }
}
