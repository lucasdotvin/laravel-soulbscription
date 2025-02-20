<?php

namespace LucasDotVin\Soulbscription\Events;

use InvalidArgumentException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class FeatureConsumed
{
    use Dispatchable;
    use SerializesModels;
    use InteractsWithSockets;

    public mixed $feature;
    public mixed $subscriber;
    public mixed $featureConsumption;

    public function __construct(
        $subscriber,
        mixed $feature,
        mixed $featureConsumption
    ) {
        $featureClass = config('soulbscription.models.feature');
        $featureConsumptionClass = config('soulbscription.models.feature_consumption');

        throw_if(!($feature instanceof $featureClass), new InvalidArgumentException(
            "Feature must be an instance of $featureClass."
        ));

        throw_if(!($featureConsumption instanceof $featureConsumptionClass), new InvalidArgumentException(
            "FeatureConsumption must be an instance of $featureConsumptionClass."
        ));

        $this->feature = $feature;
        $this->subscriber = $subscriber;
        $this->featureConsumption = $featureConsumption;
    }
}
