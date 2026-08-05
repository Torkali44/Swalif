<?php

use App\Http\Controllers\Site;
use App\Http\Controllers\User;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::get('/', [Site\HomeController::class, 'index'])->name('home');
Route::get('/categories', [Site\CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category:slug}', [Site\CategoryController::class, 'show'])->name('categories.show');

// Subscription plans visible to all (guests see plans but can't pay)
Route::get('/subscribe', [Site\SubscriptionController::class, 'index'])->name('subscription.index');

Route::middleware('auth')->group(function () {

    // ── Game Routes ──────────────────────────────────────────────────────────
    Route::prefix('game')->name('game.')->middleware(['play.access', 'free.trial'])->group(function () {
        Route::get('/setup/{category}', [Site\GameController::class, 'setup'])->name('setup');
        Route::post('/start', [Site\GameController::class, 'start'])->name('start');
        Route::get('/{game}/board', [Site\GameController::class, 'board'])->name('board');
        Route::get('/{game}/question/{question}', [Site\GameController::class, 'question'])->name('question');
        Route::get('/{game}/answer/{gameQuestion}', [Site\GameController::class, 'answer'])->name('answer');
        Route::post('/{game}/answer/{gameQuestion}', [Site\GameController::class, 'answer'])->name('answer.store');
        Route::post('/{game}/assign/{gameQuestion}', [Site\GameController::class, 'assign'])->name('assign');
        Route::get('/{game}/result', [Site\GameController::class, 'result'])->name('result');
        Route::post('/{game}/team/{team}/use-helper/{helper}', [Site\GameController::class, 'useHelper'])->name('useHelper');
        Route::post('/{game}/team/{team}/adjust-score', [Site\GameController::class, 'adjustScore'])->name('adjustScore');
    });

    // ── Subscription / Payment Routes ────────────────────────────────────────

    // Checkout (creates pending payment, redirects to Stripe)
    Route::post('/subscribe/opening-offer', [Site\SubscriptionController::class, 'openingOfferCheckout'])->name('subscription.opening_offer');
    Route::post('/subscribe/{plan}', [Site\SubscriptionController::class, 'checkout'])->name('subscription.checkout');

    // Return URL — Stripe redirects here after payment (NO auto-activation)
    Route::get('/subscribe/return', [Site\SubscriptionController::class, 'returnFromPayment'])->name('subscription.return');

    // "I have paid" button — moves pending → waiting_review (ONE-TIME)
    Route::post('/subscribe/claim', [Site\SubscriptionController::class, 'claimPayment'])->name('subscription.claim');

    // Success page — only accessible with an active subscription
    Route::get('/subscribe/success', [Site\SubscriptionController::class, 'success'])->name('subscription.success');

    // ── Profile / History ────────────────────────────────────────────────────
    Route::get('/profile', [User\ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [User\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [User\ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/history', [User\HistoryController::class, 'index'])->name('history');
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(base_path('routes/admin.php'));
