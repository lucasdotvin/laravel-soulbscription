<?php

namespace LucasDotDev\Soulbscription\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use LucasDotDev\Soulbscription\Models\Feature;
use LucasDotDev\Soulbscription\Models\FeatureConsumption;
use LucasDotDev\Soulbscription\Models\Plan;
use LucasDotDev\Soulbscription\Models\Subscription;
use LucasDotDev\Soulbscription\Models\SubscriptionRenewal;
use OutOfBoundsException;
use OverflowException;

trait HasSubscriptions
{
    public function activePlans()
    {
        return $this->plans()
            ->wherePivot('expires_at', '>', now());
    }

    public function featureConsumptions()
    {
        return $this->morphMany(FeatureConsumption::class, 'subscriber');
    }

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'subscriptions', 'subscriber_id')
            ->as('subscription')
            ->withPivot([
                'canceled_at',
                'expires_at',
                'started_at',
                'suppressed_at',
                'was_switched',
            ])
            ->withTimestamps();
    }

    public function renewals()
    {
        return $this->hasManyThrough(SubscriptionRenewal::class, Subscription::class, 'subscriber_id');
    }

    public function subscription()
    {
        return $this->morphOne(Subscription::class, 'subscriber')->ofMany(
            [
                'started_at' => 'max',
            ],
            fn (Builder $query) => $query->started(),
        );
    }

    public function canConsume($featureName, ?float $consumption = null): bool
    {
        if (empty($feature = $this->getAvailableFeature($featureName))) {
            return false;
        }

        if (! $feature->consumable) {
            return true;
        }

        $currentConsumption = $this->featureConsumptions()
            ->whereBelongsTo($feature)
            ->unexpired()
            ->sum('consumption');

        return ($currentConsumption + $consumption) <= $feature->pivot->charges;
    }

    public function cantConsume($featureName, ?float $consumption = null): bool
    {
        return ! $this->canConsume($featureName, $consumption);
    }

    public function hasFeature($featureName): bool
    {
        return ! $this->missingFeature($featureName);
    }

    public function missingFeature($featureName): bool
    {
        return empty($this->getAvailableFeature($featureName));
    }

    /**
     * @throws OutOfBoundsException
     * @throws OverflowException
     */
    public function consume($featureName, ?float $consumption = null)
    {
        throw_if($this->missingFeature($featureName), new OutOfBoundsException(
            'None of the active plans grants access to this feature.',
        ));

        throw_if($this->cantConsume($featureName, $consumption), new OverflowException(
            'The feature has no enough charges to this consumption.',
        ));

        $consumedPlan = $this->activePlans->first(fn (Plan $plan) => $plan->features->firstWhere('name', $featureName));
        $feature = $consumedPlan->features->firstWhere('name', $featureName);

        $consumptionExpiration = $feature->consumable
            ? $feature->calculateNextRecurrenceEnd($consumedPlan->subscription->started_at)
            : null;

        $this->featureConsumptions()
            ->make([
                'consumption' => $consumption,
                'expires_at' => $consumptionExpiration,
            ])
            ->feature()
            ->associate($feature)
            ->save();
    }

    public function subscribeTo(Plan $plan, $expiration = null, $startDate = null): Subscription
    {
        $expiration = $expiration ?? $plan->calculateNextRecurrenceEnd($startDate);

        return tap(
            $this->subscription()
                ->make([
                    'expires_at' => $expiration,
                ])
                ->start($startDate)
                ->plan()
                ->associate($plan),
        )->save();
    }

    public function switchTo(Plan $plan, $expiration = null, $immediately = true): Subscription
    {
        if ($immediately) {
            $this->subscription
                ->markAsSwitched()
                ->suppress()
                ->save();

            return $this->subscribeTo($plan, $expiration);
        }

        $this->subscription
            ->markAsSwitched()
            ->save();

        $startDate = $this->subscription->expires_at;

        return $this->subscribeTo($plan, startDate: $startDate);
    }

    private function getAvailableFeature(string $featureName): ?Feature
    {
        $this->loadMissing('activePlans.features');

        $availableFeatures = $this->activePlans->flatMap(fn (Plan $plan) => $plan->features);
        $feature = $availableFeatures->firstWhere('name', $featureName);

        return $feature;
    }
}
