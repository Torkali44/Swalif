<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Subscription\PlanService;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function __construct(
        private PlanService $plans,
        private SubscriptionService $subscriptions,
        private PaymentGatewayInterface $gateway,
    ) {}

    public function index()
    {
        return view('site.subscription.plans', [
            'plans' => $this->plans->activePlans(),
        ]);
    }

    public function checkout(Plan $plan, Request $request)
    {
        abort_unless($plan->is_active, 404);

        if (filled($plan->stripe_checkout_url)) {
            return redirect()->away($plan->stripe_checkout_url);
        }

        $user = $request->user();

        DB::transaction(function () use ($user, $plan) {
            $charge = $this->gateway->charge($user, $plan, (float) $plan->price);

            $payment = Payment::create([
                'user_id' => $user->id,
                'gateway' => $charge['meta']['gateway'] ?? 'fake',
                'gateway_reference' => $charge['reference'],
                'amount' => $plan->price,
                'currency' => $plan->currency ?? 'AED',
                'status' => $charge['status'],
                'meta' => $charge['meta'] ?? [],
            ]);

            $subscription = $this->subscriptions->activate($user, $plan, $payment->id);
            $payment->update(['subscription_id' => $subscription->id]);
        });

        return redirect()
            ->route('subscription.index')
            ->with('success', 'تم تفعيل اشتراكك بنجاح.');
    }
}
