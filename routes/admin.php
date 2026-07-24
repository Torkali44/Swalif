<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClassificationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('categories', CategoryController::class)->except(['show']);
Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');

Route::resource('classifications', ClassificationController::class)->except(['show']);
Route::patch('classifications/{classification}/toggle', [ClassificationController::class, 'toggle'])->name('classifications.toggle');

Route::resource('questions', QuestionController::class)->except(['show']);
Route::patch('questions/{question}/toggle', [QuestionController::class, 'toggle'])->name('questions.toggle');

Route::resource('plans', PlanController::class)->only(['index', 'create', 'store', 'edit', 'update']);

Route::get('subscribers', [SubscriberController::class, 'index'])->name('subscribers.index');
Route::get('subscribers/create', [SubscriberController::class, 'create'])->name('subscribers.create');
Route::post('subscribers', [SubscriberController::class, 'store'])->name('subscribers.store');
Route::get('subscribers/{subscription}/edit', [SubscriberController::class, 'edit'])->name('subscribers.edit');
Route::put('subscribers/{subscription}', [SubscriberController::class, 'update'])->name('subscribers.update');
Route::patch('subscribers/{subscription}/cancel', [SubscriberController::class, 'cancel'])->name('subscribers.cancel');
Route::patch('subscribers/{subscription}/activate', [SubscriberController::class, 'activate'])->name('subscribers.activate');
Route::patch('subscribers/{subscription}/extend', [SubscriberController::class, 'extend'])->name('subscribers.extend');

Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
Route::post('payments/{payment}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');

Route::get('users', [UserController::class, 'index'])->name('users.index');
Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggleActive');
Route::patch('users/{user}/toggle-play', [UserController::class, 'togglePlayBlock'])->name('users.togglePlay');
Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
