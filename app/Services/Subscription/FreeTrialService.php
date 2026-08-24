<?php

namespace App\Services\Subscription;

use App\Models\User;

class FreeTrialService
{
    public function canPlayCategory(User $user, int $categoryId): bool
    {
        if ($user->is_admin || $user->hasActiveSubscription()) {
            return true;
        }

        if (! $user->free_category_id) {
            return true;
        }

        return (int) $user->free_category_id === (int) $categoryId;
    }

    public function claimFreeCategory(User $user, int $categoryId): void
    {
        if ($user->is_admin || $user->hasActiveSubscription()) {
            return;
        }

        if ($user->free_category_id) {
            return;
        }

        $user->forceFill(['free_category_id' => $categoryId])->save();
    }

    public function freeCategoryId(User $user): ?int
    {
        return $user->free_category_id ? (int) $user->free_category_id : null;
    }

    public function isLimitedFreeUser(User $user): bool
    {
        return ! $user->is_admin && ! $user->hasActiveSubscription();
    }

    public function hasConsumedFreeCategory(User $user): bool
    {
        return $this->isLimitedFreeUser($user) && (bool) $user->free_category_id;
    }

    public function shouldWarnOnLeave(User $user): bool
    {
        return $this->hasConsumedFreeCategory($user);
    }

    public function subscribeRequiredMessage(): string
    {
        return 'انتهت فئتك المجانية. اشترك عشان تقدر تلعب فئات ثانية.';
    }

    public function canCreateCustomGame(User $user): bool
    {
        if ($user->is_admin || $user->hasActiveSubscription()) {
            return true;
        }

        return $user->customGames()->count() < 1;
    }

    public function hasConsumedFreeCustomGame(User $user): bool
    {
        return $this->isLimitedFreeUser($user) && $user->customGames()->exists();
    }

    public function customGameSubscribeRequiredMessage(): string
    {
        return 'انتهت تجربتك المجانية للعبة الخاصة (لعبة واحدة فقط). اشترك الحين عشان تقدر تنشئ ألعاب خاصة إضافية! 🎮';
    }

    public function leaveWarningMessage(): string
    {
        return 'إذا طلعت الحين بتنتهي تجربتك المجانية، وحق تلعب فئة ثانية لازم تشترك. متأكد تبي تطلع؟';
    }

    /** @deprecated kept for compatibility */
    public function canOpenQuestion(User $user): bool
    {
        return $user->is_admin || $user->hasActiveSubscription() || ! $user->free_category_id;
    }

    /** @deprecated kept for compatibility */
    public function remaining(User $user): int
    {
        if ($user->is_admin || $user->hasActiveSubscription()) {
            return PHP_INT_MAX;
        }

        return $user->free_category_id ? 0 : 1;
    }
}
