<?php

namespace LucasDotVin\Soulbscription\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use LogicException;
use LucasDotVin\Soulbscription\Events\FeatureConsumed;
use LucasDotVin\Soulbscription\Events\FeatureTicketCreated;
use LucasDotVin\Soulbscription\Models\Feature;
use LucasDotVin\Soulbscription\Models\FeatureTicket;
use LucasDotVin\Soulbscription\Models\Plan;
use LucasDotVin\Soulbscription\Models\Subscription;
use OutOfBoundsException;
use OverflowException;

trait HasSubscriptions
{
    protected ?Collection $loadedFeatures = null;

    protected ?Collection $loadedSubscriptionFeatures = null;

    protected ?Collection $loadedTicketFeatures = null;

    public function featureConsumptions()
    {
        return $this->morphMany(config('soulbscription.models.feature_consumption'), 'subscriber');
    }

    public function featureTickets()
    {
        return $this->morphMany(config('soulbscription.models.feature_ticket'), 'subscriber');
    }

    public function renewals()
    {
        return $this->hasManyThrough(config('soulbscription.models.subscription_renewal'), config('soulbscription.models.subscription'), 'subscriber_id');
    }

    public function subscription()
    {
        return $this->morphOne(config('soulbscription.models.subscription'), 'subscriber')->ofMany('started_at', 'MAX');
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

        $graceDaysEnd = $plan->hasGraceDays
            ? $plan->calculateGraceDaysEnd($expiration)
            : null;

        return $this->subscription()
            ->make([
                'expired_at' => $expiration,
                'grace_days_ended_at' => $graceDaysEnd,
            ])
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

    /**
     * @throws LogicException
     * @throws ModelNotFoundException
     */
    public function giveTicketFor($featureName, $expiration, ?float $charges = null): FeatureTicket
    {
        throw_unless(config('soulbscription.feature_tickets'), new LogicException('The tickets are not enabled in the configs.'));

        $feature = Feature::whereName($featureName)->firstOrFail();

        $featureTicket = $this->featureTickets()
            ->make([
                'charges' => $charges,
                'expired_at' => $expiration,
            ]);

        $featureTicket->feature()->associate($feature);
        $featureTicket->save();

        event(new FeatureTicketCreated($this, $feature, $featureTicket));

        return $featureTicket;
    }

    public function canConsume($featureName, ?float $consumption = null): bool
    {
        if (empty($feature = $this->getFeature($featureName))) {
            return false;
        }

        if (! $feature->consumable) {
            return true;
        }

        $remainingCharges = $this->getRemainingCharges($featureName);

        return $remainingCharges >= $consumption;
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

    public function getRemainingCharges($featureName): float
    {
        if (empty($this->getFeature($featureName))) {
            return 0;
        }

        $currentConsumption = $this->getCurrentConsumption($featureName);
        $totalCharges = $this->getTotalCharges($featureName);

        return $totalCharges - $currentConsumption;
    }

    public function getCurrentConsumption($featureName): float
    {
        if (empty($feature = $this->getFeature($featureName))) {
            return 0;
        }

        return $this->featureConsumptions()
            ->whereBelongsTo($feature)
            ->sum('consumption');
    }

    public function getTotalCharges($featureName): float
    {
        if (empty($feature = $this->getFeature($featureName))) {
            return 0;
        }

        $subscriptionCharges = $this->getSubscriptionChargesForAFeature($feature);
        $ticketCharges = $this->getTicketChargesForAFeature($feature);

        return $subscriptionCharges + $ticketCharges;
    }

    protected function getSubscriptionChargesForAFeature(Model $feature): float
    {
        $subscriptionFeature = $this->loadedSubscriptionFeatures
            ->find($feature);

        if (empty($subscriptionFeature)) {
            return 0;
        }

        return $subscriptionFeature
            ->pivot
            ->charges;
    }

    protected function getTicketChargesForAFeature(Model $feature): float
    {
        $ticketFeature = $this->loadedTicketFeatures
            ->find($feature);

        if (empty($ticketFeature)) {
            return 0;
        }

        return $ticketFeature
            ->tickets
            ->sum('charges');
    }

    public function getFeature(string $featureName): ?Feature
    {
        $feature = $this->features->firstWhere('name', $featureName);

        return $feature;
    }

    public function getFeaturesAttribute(): Collection
    {
        if (! is_null($this->loadedFeatures)) {
            return $this->loadedFeatures;
        }

        $this->loadedFeatures = $this->loadSubscriptionFeatures()
            ->concat($this->loadTicketFeatures());

        return $this->loadedFeatures;
    }

    protected function loadSubscriptionFeatures(): Collection
    {
        if (! is_null($this->loadedSubscriptionFeatures)) {
            return $this->loadedSubscriptionFeatures;
        }

        $this->loadMissing('subscription.plan.features');

        return $this->loadedSubscriptionFeatures = $this->subscription->plan->features ?? Collection::empty();
    }

    protected function loadTicketFeatures(): Collection
    {
        if (! config('soulbscription.feature_tickets')) {
            return $this->loadedTicketFeatures = Collection::empty();
        }

        if (! is_null($this->loadedTicketFeatures)) {
            return $this->loadedTicketFeatures;
        }

        return $this->loadedTicketFeatures = Feature::with('tickets')
            ->whereHas('tickets', fn (Builder $query) => $query->withoutExpired()->whereMorphedTo('subscriber', $this))
            ->get();
    }
}
