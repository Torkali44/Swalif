<?php

namespace App\Services\Payment;

use App\Models\Plan;
use App\Models\User;

/**
 * Placeholder for Stripe integration — falls back to FakeGateway until keys are configured.
 */
class StripeGateway implements PaymentGatewayInterface
{
    public function __construct(private FakeGateway $fallback) {}

    public function charge(User $user, Plan $plan, float $amount): array
    {
        $result = $this->fallback->charge($user, $plan, $amount);
        $result['meta']['gateway'] = 'stripe_stub';

        return $result;
    }
}
