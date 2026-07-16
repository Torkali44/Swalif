<?php

namespace App\Services\Payment;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Str;

class FakeGateway implements PaymentGatewayInterface
{
    public function charge(User $user, Plan $plan, float $amount): array
    {
        return [
            'reference' => 'fake_'.Str::lower(Str::random(16)),
            'status' => 'paid',
            'meta' => [
                'gateway' => 'fake',
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'amount' => $amount,
            ],
        ];
    }
}
