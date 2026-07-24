<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\Request;
use RuntimeException;

class PaymentController extends Controller
{
    public function __construct(private SubscriptionService $subscriptions) {}

    public function index(Request $request)
    {
        $payments = Payment::query()
            ->with(['user', 'subscription.plan'])
            ->latest()
            ->paginate(20);

        return view('admin.payments.index', compact('payments'));
    }

    public function confirm(Payment $payment)
    {
        try {
            $subscription = $this->subscriptions->markPaymentPaidAndActivate($payment);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.subscribers.edit', $subscription)
            ->with('success', 'تم تأكيد الدفع وتفعيل الاشتراك حتى '.$subscription->ends_at->format('Y-m-d H:i'));
    }
}
