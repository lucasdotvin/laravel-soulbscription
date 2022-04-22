<?php

namespace LucasDotVin\Soulbscription\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class StartingScope implements Scope
{
    protected $extensions = [
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
}
