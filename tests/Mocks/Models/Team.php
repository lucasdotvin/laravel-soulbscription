<?php

namespace Tests\Mocks\Models;

use Illuminate\Database\Eloquent\Model;
use LucasDotVin\Soulbscription\Models\Concerns\HasSubscriptions;

/**
 * A second subscriber type, to check that subscribers of different types never
 * see each other's records.
 */
class Team extends Model
{
    use HasSubscriptions;

    protected $guarded = [];
}
