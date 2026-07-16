<?php

namespace App\Services\Subscription;

use App\Models\Plan;

class PlanService
{
    public function activePlans()
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}
