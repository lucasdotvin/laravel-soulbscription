<?php

namespace LucasDotDev\Soulbscription\Models\Concerns;

use Illuminate\Database\Eloquent\Collection;
use LucasDotDev\Soulbscription\Events\FeatureConsumed;
use LucasDotDev\Soulbscription\Models\Feature;
use LucasDotDev\Soulbscription\Models\Plan;
use LucasDotDev\Soulbscription\Models\Subscription;
use OutOfBoundsException;
use OverflowException;

trait HasSubscriptions
{
    public ?Collection $loadedFeatures = null;

    public function featureConsumptions()
    {
        return $this->morphMany(config('soulbscription.models.feature_consumption'), 'subscriber');
    }

    public function renewals()
    {
        return $this->hasManyThrough(config('soulbscription.models.subscription_renewal'), config('soulbscription.models.subscription'), 'subscriber_id');
    }

    public function subscription()
    {
        return $this->morphOne(config('soulbscription.models.subscription'), 'subscriber')->ofMany('started_at', 'MAX');
    }

    public function canConsume($featureName, ?float $consumption = null): bool
    {
        if (empty($feature = $this->getFeature($featureName))) {
            return false;
        }

        if (! $feature->consumable) {
            return true;
        }

        $currentConsumption = $this->featureConsumptions()
            ->whereBelongsTo($feature)
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
        return empty($this->getFeature($featureName));
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

        $feature = $this->getFeature($featureName);

        $consumptionExpiration = $feature->consumable
            ? $feature->calculateNextRecurrenceEnd($this->subscription->started_at)
            : null;

        $featureConsumption = $this->featureConsumptions()
            ->make([
                'consumption' => $consumption,
                'expired_at' => $consumptionExpiration,
            ])
            ->feature()
            ->associate($feature);

        $featureConsumption->save();

        event(new FeatureConsumed($this, $feature, $featureConsumption));
    }

    public function subscribeTo(Plan $plan, $expiration = null, $startDate = null): Subscription
    {
        $expiration = $expiration ?? $plan->calculateNextRecurrenceEnd($startDate);

        return $this->subscription()
            ->make(['expired_at' => $expiration])
            ->plan()
            ->associate($plan)
            ->start($startDate);
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

        $startDate = $this->subscription->expired_at;
        $newSubscription = $this->subscribeTo($plan, startDate: $startDate);

        return $newSubscription;
    }

    public function getFeature(string $featureName): ?Feature
    {
        $feature = $this->features->firstWhere('name', $featureName);

        return $feature;
    }

    public function getFeaturesAttribute(): Collection
    {
        if (! $this->loadedFeatures) {
            $this->loadFeatures();
        }

        return $this->loadedFeatures;
    }

    private function loadFeatures()
    {
        $this->loadMissing('subscription.plan.features');

        $this->loadedFeatures = $this->subscription->plan->features ?? Collection::empty();
    }
}
