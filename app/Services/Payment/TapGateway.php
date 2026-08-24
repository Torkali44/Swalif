<?php

namespace App\Services\Payment;

use App\Models\Plan;
use App\Models\User;

/**
 * Placeholder for Tap Payments (GCC) — falls back to FakeGateway until configured.
 */
class TapGateway implements PaymentGatewayInterface
{
    public function __construct(private FakeGateway $fallback) {}

    public function charge(User $user, Plan $plan, float $amount): array
    {
        $result = $this->fallback->charge($user, $plan, $amount);
        $result['meta']['gateway'] = 'tap_stub';

        return $result;
    }
}
