<?php

namespace LucasDotVin\Soulbscription\Events;

use InvalidArgumentException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class SubscriptionSuppressed
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public mixed $subscription;

    public function __construct(mixed $subscription)
    {
        $subscriptionClass = config('soulbscription.models.subscription');

        throw_if(!($subscription instanceof $subscriptionClass), new InvalidArgumentException(
            "Subscription must be an instance of $subscriptionClass."
        ));

        $this->subscription = $subscription;
    }
}
