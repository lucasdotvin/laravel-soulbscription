<?php

namespace LucasDotDev\Soulbscription\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Carbon;

class StartingScope implements Scope
{
    protected $extensions = [
        'Start',
        'OnlyNotStarted',
        'WithNotStarted',
        'WithoutNotStarted',
    ];

    public function apply(Builder $builder, Model $model)
    {
        $builder->where('started_at', '<=', now());
    }

    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    protected function addWithNotStarted(Builder $builder)
    {
        $builder->macro('withNotStarted', function (Builder $builder, $withNotStarted = true) {
            if ($withNotStarted) {
                return $builder->withoutGlobalScope($this);
            }

            return $builder->withoutNotStarted();
        });
    }

    protected function addWithoutNotStarted(Builder $builder)
    {
        $builder->macro('withoutNotStarted', function (Builder $builder) {
            $builder->withoutGlobalScope($this)->where('started_at', '<=', now());

            return $builder;
        });
    }

    protected function addOnlyNotStarted(Builder $builder)
    {
        $builder->macro('onlyNotStarted', function (Builder $builder) {
            $builder->withoutGlobalScope($this)->where('started_at', '>', now());

            return $builder;
        });
    }

    protected function addStart(Builder $builder)
    {
        $builder->macro('start', function (Builder $builder, ?Carbon $startation = null) {
            $builder->withoutNotStarted();

            $startation = $startation ?: now();

            return $builder->update(['started_at' => $startation]);
        });
    }
}
