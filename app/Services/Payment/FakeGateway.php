<?php

namespace App\Services\Payment;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Str;

class FakeGateway implements PaymentGatewayInterface
{
    public function charge(User $user, Plan $plan, float $amount): array
    {
        // Auto-paid only in local/testing. Production stays pending (admin confirm / real gateway).
        $autoPaid = app()->environment(['local', 'testing'])
            || filter_var(env('PAYMENT_FAKE_AUTO_PAID', false), FILTER_VALIDATE_BOOL);

        return [
            'reference' => 'fake_'.Str::lower(Str::random(16)),
            'status' => $autoPaid ? 'paid' : 'pending',
            'meta' => [
                'gateway' => 'fake',
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'amount' => $amount,
                'auto_paid' => $autoPaid,
            ],
        ];
    }
}
