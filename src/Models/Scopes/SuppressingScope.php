<?php

namespace LucasDotVin\Soulbscription\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SuppressingScope implements Scope
{
    protected array $extensions = [
        'OnlySuppressed',
        'WithSuppressed',
        'WithoutSuppressed',
    ];

    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereNull('suppressed_at');
    }

    public function extend(Builder $builder): void
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    protected function addWithSuppressed(Builder $builder): void
    {
        $builder->macro('withSuppressed', function (Builder $builder, $withSuppressed = true) {
            if ($withSuppressed) {
                return $builder->withoutGlobalScope($this);
            }

            return $builder->withoutSuppressed();
        });
    }

    protected function addWithoutSuppressed(Builder $builder): void
    {
        $builder->macro('withoutSuppressed', function (Builder $builder) {
            $builder->withoutGlobalScope($this)->whereNull('suppressed_at');

            return $builder;
        });
    }

    protected function addOnlySuppressed(Builder $builder): void
    {
        $builder->macro('onlySuppressed', function (Builder $builder) {
            $builder->withoutGlobalScope($this)->whereNotNull('suppressed_at');

            return $builder;
        });
    }
}
