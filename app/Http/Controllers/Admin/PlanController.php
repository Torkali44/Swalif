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
            'plans' => Plan::orderBy('sort_order')->get(),
        ]);
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.form', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name' => ['required', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable'],
            'is_recommended' => ['nullable'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['is_recommended'] = $request->boolean('is_recommended');
        $plan->update($data);

        return redirect()->route('admin.plans.index')->with('success', 'تم تحديث الباقة.');
    }
}
