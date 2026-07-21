<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        return view('admin.plans.index', [
            'plans' => Plan::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        $plan = new Plan([
            'currency' => 'AED',
            'duration_days' => 30,
            'features' => [],
            'is_active' => true,
            'sort_order' => ((int) Plan::max('sort_order')) + 1,
        ]);

        return view('admin.plans.form', compact('plan'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $plan = Plan::create($data);

        $this->applyOrdering($plan, (int) ($data['sort_order'] ?? PHP_INT_MAX));

        return redirect()->route('admin.plans.index')->with('success', 'تم إضافة الباقة.');
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.form', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $this->validatedData($request);
        $plan->update($data);

        $this->applyOrdering($plan, (int) ($data['sort_order'] ?? PHP_INT_MAX));

        return redirect()->route('admin.plans.index')->with('success', 'تم تحديث الباقة.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:20'],
            'type' => ['required', 'string', 'max:50'],
            'stripe_checkout_url' => ['nullable', 'url', 'max:2048'],
            'price' => ['required', 'numeric', 'min:1'],
            'old_price' => ['nullable', 'numeric', 'min:1', 'gte:price'],
            'currency' => ['required', 'string', 'size:3'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'string', 'max:160'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable'],
            'is_recommended' => ['nullable'],
        ], [
            'sort_order.min' => 'ترتيب الظهور يجب أن يبدأ من 1.',
            'price.min' => 'السعر يجب أن يكون 1 على الأقل.',
            'stripe_checkout_url.url' => 'رابط دفع Stripe غير صالح.',
        ]);

        $data['features'] = collect($request->input('features', []))
            ->map(fn ($feature) => trim((string) $feature))
            ->filter()
            ->values()
            ->all();
        $data['old_price'] = $data['old_price'] ?? null;
        $data['stripe_checkout_url'] = filled($data['stripe_checkout_url'] ?? null)
            ? trim($data['stripe_checkout_url'])
            : null;
        $data['currency'] = strtoupper($data['currency']);
        $data['sort_order'] = (int) $data['sort_order'];
        $data['is_active'] = $request->boolean('is_active');
        $data['is_recommended'] = $request->boolean('is_recommended');

        return $data;
    }

    /**
     * Place the given plan at the desired 1-based position and
     * re-sequence every plan so each one holds a unique order (1..N).
     */
    private function applyOrdering(Plan $saved, int $desired): void
    {
        $others = Plan::where('id', '!=', $saved->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $position = max(1, min($desired, $others->count() + 1));

        $ordered = $others->values();
        $ordered->splice($position - 1, 0, [$saved]);

        foreach ($ordered->values() as $i => $plan) {
            $newOrder = $i + 1;
            if ((int) $plan->sort_order !== $newOrder) {
                $plan->sort_order = $newOrder;
                $plan->saveQuietly();
            }
        }
    }
}
