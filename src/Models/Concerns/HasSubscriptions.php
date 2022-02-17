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
    public function featureConsumptions()
    {
        return $this->morphMany(FeatureConsumption::class, 'subscriber');
    }

    public function renewals()
    {
        return $this->hasManyThrough(SubscriptionRenewal::class, Subscription::class, 'subscriber_id');
    }

    public function subscription()
    {
        return $this->morphOne(Subscription::class, 'subscriber')->ofMany('started_at', 'MAX');
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

        $feature = $this->subscription->plan->features->firstWhere('name', $featureName);

        $consumptionExpiration = $feature->consumable
            ? $feature->calculateNextRecurrenceEnd($this->subscription->started_at)
            : null;

        $this->featureConsumptions()
            ->make([
                'consumption' => $consumption,
                'expired_at' => $consumptionExpiration,
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
                    'expired_at' => $expiration,
                ])
                ->plan()
                ->associate($plan)
                ->start($startDate),
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

        $startDate = $this->subscription->expired_at;

        return $this->subscribeTo($plan, startDate: $startDate);
    }

    private function getAvailableFeature(string $featureName): ?Feature
    {
        $this->loadMissing('subscription.plan.features');

        if (empty($this->subscription)) {
            return null;
        }

        $feature = $this->subscription->plan->features->firstWhere('name', $featureName);

        return $feature;
    }
}
