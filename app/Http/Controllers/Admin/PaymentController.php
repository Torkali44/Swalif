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

    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Payment::query()
            ->with(['user', 'subscription.plan'])
            ->latest();

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        // User search
        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->whereHas('user', fn ($b) => $b
                ->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
            );
        }

        $payments = $query->paginate(20)->withQueryString();

        // Counts for badge display
        $counts = Payment::query()
            ->selectRaw("
                SUM(CASE WHEN status = 'pending'        THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'waiting_review' THEN 1 ELSE 0 END) as waiting_review,
                SUM(CASE WHEN status = 'paid'           THEN 1 ELSE 0 END) as paid,
                SUM(CASE WHEN status = 'cancelled'      THEN 1 ELSE 0 END) as cancelled
            ")
            ->first();

        return view('admin.payments.index', [
            'payments' => $payments,
            'counts'   => $counts,
            'filters'  => $request->only(['status', 'q']),
        ]);
    }

    // ─── Confirm (Activate) ───────────────────────────────────────────────────

    /**
     * Admin confirms the payment and activates the subscription.
     * Works for both "pending" and "waiting_review" payments.
     */
    public function confirm(Payment $payment)
    {
        if ($payment->isPaid() && $payment->subscription_id) {
            return redirect()
                ->route('admin.subscribers.edit', $payment->subscription_id)
                ->with('info', 'الدفع مؤكد بالفعل.');
        }

        if (! $payment->canBeConfirmed()) {
            return back()->with('error', 'لا يمكن تأكيد هذه العملية (الحالة: ' . $payment->status . ').');
        }

        try {
            $subscription = $this->subscriptions->markPaymentPaidAndActivate($payment);

            \Illuminate\Support\Facades\Log::info('[Admin Audit] Payment confirmed manually', [
                'admin_id'        => auth()->id(),
                'payment_id'      => $payment->id,
                'user_id'         => $payment->user_id,
                'subscription_id' => $subscription->id,
            ]);
        } catch (RuntimeException $e) {
            \Illuminate\Support\Facades\Log::error('[Admin Audit] Manual payment confirm failed', [
                'admin_id'   => auth()->id(),
                'payment_id' => $payment->id,
                'error'      => $e->getMessage(),
            ]);

            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.subscribers.edit', $subscription)
            ->with('success', 'تم تأكيد الدفع وتفعيل الاشتراك حتى ' . $subscription->ends_at->format('Y-m-d H:i'));
    }

    // ─── Cancel ───────────────────────────────────────────────────────────────

    /**
     * Admin cancels a pending/waiting_review payment.
     * Does NOT touch any existing subscription.
     */
    public function cancel(Payment $payment)
    {
        if ($payment->isPaid()) {
            return back()->with('error', 'لا يمكن إلغاء عملية مدفوعة ومفعّلة.');
        }

        $payment->update(['status' => 'cancelled']);

        \Illuminate\Support\Facades\Log::info('[Admin Audit] Payment cancelled manually', [
            'admin_id'   => auth()->id(),
            'payment_id' => $payment->id,
            'user_id'    => $payment->user_id,
        ]);

        return back()->with('success', 'تم إلغاء عملية الدفع رقم ' . ($payment->payment_reference ?? $payment->id));
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    /**
     * Admin deletes a payment record.
     */
    public function destroy(Payment $payment)
    {
        $ref = $payment->payment_reference ?? ('PAY-' . $payment->id);

        $payment->delete();

        \Illuminate\Support\Facades\Log::info('[Admin Audit] Payment deleted manually', [
            'admin_id'   => auth()->id(),
            'payment_id' => $payment->id,
            'ref'        => $ref,
        ]);

        return back()->with('success', "تم حذف عملية الدفع {$ref} بنجاح.");
    }
}
