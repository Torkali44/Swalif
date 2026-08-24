<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscription\PlayAccessService;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Mark expired subscriptions as expired and lock play access';

    public function handle(PlayAccessService $playAccess): int
    {
        $expired = Subscription::query()
            ->where('status', 'active')
            ->where('ends_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($expired as $subscription) {
            $subscription->update(['status' => 'expired']);
            $count++;

            $user = User::query()->find($subscription->user_id);
            if (! $user || $user->is_admin || $user->hasActiveSubscription()) {
                continue;
            }

            $playAccess->block(
                $user,
                'انتهى اشتراكك. اشترك من جديد أو تواصل مع الإدارة لفتح اللعب.'
            );
        }

        $this->info("Expired {$count} subscription(s).");

        return self::SUCCESS;
    }
}
