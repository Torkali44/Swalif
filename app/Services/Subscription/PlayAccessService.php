<?php

namespace App\Services\Subscription;

use App\Models\Subscription;
use App\Models\User;

class PlayAccessService
{
    public function __construct(private FreeTrialService $freeTrial) {}

    public function syncExpiredSubscriptions(User $user): void
    {
        if ($user->is_admin) {
            return;
        }

        $expiredIds = Subscription::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('ends_at', '<=', now())
            ->pluck('id');

        if ($expiredIds->isEmpty()) {
            return;
        }

        Subscription::query()
            ->whereIn('id', $expiredIds)
            ->update(['status' => 'expired']);

        $user->unsetRelation('subscriptions');

        if (! $user->hasActiveSubscription() && ! $user->play_blocked) {
            $this->block(
                $user,
                'انتهى اشتراكك. اشترك من جديد أو تواصل مع الإدارة لفتح اللعب.'
            );
            $user->refresh();
        }
    }

    public function isBlocked(User $user): bool
    {
        if ($user->is_admin) {
            return false;
        }

        $this->syncExpiredSubscriptions($user);

        // اشتراك نشط يتجاوز قفل الأدمن/الانتهاء
        if ($user->hasActiveSubscription()) {
            return false;
        }

        return (bool) $user->play_blocked;
    }

    public function canAccessGames(User $user): bool
    {
        return ! $this->isBlocked($user);
    }

    public function canPlayCategory(User $user, int $categoryId): bool
    {
        if ($this->isBlocked($user)) {
            return false;
        }

        return $this->freeTrial->canPlayCategory($user, $categoryId);
    }

    public function blockMessage(User $user): string
    {
        if ($user->play_blocked && filled($user->play_blocked_reason)) {
            return (string) $user->play_blocked_reason;
        }

        if ($user->play_blocked) {
            return 'تم إيقاف اللعب على حسابك. اشترك من جديد أو تواصل مع الإدارة لفتح الحساب.';
        }

        return $this->freeTrial->subscribeRequiredMessage();
    }

    public function block(User $user, string $reason = 'تم إيقاف اللعب من الإدارة.'): void
    {
        if ($user->is_admin) {
            return;
        }

        $user->forceFill([
            'play_blocked' => true,
            'play_blocked_at' => now(),
            'play_blocked_reason' => $reason,
        ])->save();
    }

    public function unblock(User $user): void
    {
        $user->forceFill([
            'play_blocked' => false,
            'play_blocked_at' => null,
            'play_blocked_reason' => null,
        ])->save();
    }

    public function unlockForActiveSubscription(User $user): void
    {
        if ($user->play_blocked) {
            $this->unblock($user);
        }
    }
}
