<?php

namespace LucasDotVin\Soulbscription\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LucasDotVin\Soulbscription\Contracts\SubscriptionContract;

class SubscriptionRenewed
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public SubscriptionContract $subscription,
    ) {
        //
    }
}
