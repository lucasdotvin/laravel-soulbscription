<?php

namespace LucasDotVin\Soulbscription\Events;

use InvalidArgumentException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class FeatureTicketCreated
{
    use Dispatchable;
    use SerializesModels;
    use InteractsWithSockets;

    public mixed $feature;
    public mixed $subscriber;
    public mixed $featureTicket;

    public function __construct(
        $subscriber,
        mixed $feature,
        mixed $featureTicket
    ) {
        $featureClass = config('soulbscription.models.feature');
        $featureTicketClass = config('soulbscription.models.feature_ticket');

        throw_if(!($feature instanceof $featureClass), new InvalidArgumentException(
            "Feature must be an instance of $featureClass."
        ));

        throw_if(!($featureTicket instanceof $featureTicketClass), new InvalidArgumentException(
            "FeatureTicket must be an instance of $featureTicketClass."
        ));

        $this->feature = $feature;
        $this->subscriber = $subscriber;
        $this->featureTicket = $featureTicket;
    }
}
