<?php

namespace LucasDotDev\Soulbscription\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LucasDotDev\Soulbscription\Models\Subscription;

class SubscriptionScheduled
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Subscription $subscription,
    ) {
        //
    }
}
