<?php

namespace LucasDotVin\Soulbscription\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LucasDotVin\Soulbscription\Contracts\FeatureContract;
use LucasDotVin\Soulbscription\Contracts\FeatureTicketContract;

class FeatureTicketCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public $subscriber,
        public FeatureContract $feature,
        public FeatureTicketContract $featureTicket,
    ) {
        //
    }
}
