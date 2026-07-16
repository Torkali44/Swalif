<?php

namespace App\Services\Payment;

use App\Models\Plan;
use App\Models\User;

interface PaymentGatewayInterface
{
    /**
     * @return array{reference: string, status: string, meta?: array}
     */
    public function charge(User $user, Plan $plan, float $amount): array;
}
