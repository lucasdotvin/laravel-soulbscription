<?php

namespace LucasDotVin\Soulbscription\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LucasDotVin\Soulbscription\Contracts\FeatureConsumptionContract;
use LucasDotVin\Soulbscription\Contracts\FeatureContract;

class FeatureConsumed
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public $subscriber,
        public FeatureContract $feature,
        public FeatureConsumptionContract $featureConsumption,
    ) {
        //
    }
}
