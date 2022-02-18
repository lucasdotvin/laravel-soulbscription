<?php

namespace LucasDotDev\Soulbscription\Events;

use LucasDotDev\Soulbscription\Models\Subscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionScheduled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Subscription $subscription,
    ) {
        //
    }
}
