<?php

namespace LucasDotVin\Soulbscription\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SuppressingScope implements Scope
{
    protected $extensions = [
        'OnlySuppressed',
        'WithSuppressed',
        'WithoutSuppressed',
    ];

    public function apply(Builder $builder, Model $model)
    {
        $builder->where(
            fn (Builder $query) => $query
                ->whereNull('suppressed_at')
                ->orWhere('suppressed_at', '>', now())
        );
    }

    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    protected function addWithSuppressed(Builder $builder)
    {
        $builder->macro('withSuppressed', function (Builder $builder, $withSuppressed = true) {
            if ($withSuppressed) {
                return $builder->withoutGlobalScope($this);
            }

            return $builder->withoutSuppressed();
        });
    }

    protected function addWithoutSuppressed(Builder $builder)
    {
        $builder->macro('withoutSuppressed', function (Builder $builder) {
            $builder->withoutGlobalScope($this)->where(
                fn (Builder $query) => $query
                    ->whereNull('suppressed_at')
                    ->orWhere('suppressed_at', '>', now())
            );

            return $builder;
        });
    }

    protected function addOnlySuppressed(Builder $builder)
    {
        $builder->macro('onlySuppressed', function (Builder $builder) {
            $builder->withoutGlobalScope($this)
                ->whereNotNull('suppressed_at')
                ->where('suppressed_at', '<=', now());

            return $builder;
        });
    }
}
