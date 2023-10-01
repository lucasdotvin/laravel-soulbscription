<?php

namespace LucasDotVin\Soulbscription\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ExpiringWithGraceDaysScope implements Scope
{
    protected $extensions = [
        'OnlyExpired',
        'WithExpired',
        'WithoutExpired',
    ];

    public function apply(Builder $builder, Model $model)
    {
        $builder->where(
            fn (Builder $query) => $query
            ->where('expired_at', '>', now())
            ->orWhereNull('expired_at')
            ->orWhere('grace_days_ended_at', '>', now())
        );
    }

    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    protected function addWithExpired(Builder $builder)
    {
        $builder->macro('withExpired', function (Builder $builder, $withExpired = true) {
            if ($withExpired) {
                return $builder->withoutGlobalScope($this);
            }

            return $builder->withoutExpired();
        });
    }

    protected function addWithoutExpired(Builder $builder)
    {
        $builder->macro('withoutExpired', function (Builder $builder) {
            $builder->withoutGlobalScope($this)->where('expired_at', '>', now())
                ->orWhereNull('expired_at')
                ->orWhere('grace_days_ended_at', '>', now());

            return $builder;
        });
    }

    protected function addOnlyExpired(Builder $builder)
    {
        $builder->macro('onlyExpired', function (Builder $builder) {
            $builder->withoutGlobalScope($this)
                ->where('expired_at', '<=', now())
                ->where(
                    fn (Builder $query) => $query->where('grace_days_ended_at', '<=', now())
                        ->orWhereNull('grace_days_ended_at')
                );

            return $builder;
        });
    }
}
