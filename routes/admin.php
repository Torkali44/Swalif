<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('categories', CategoryController::class)->except(['show']);
Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');

Route::resource('questions', QuestionController::class)->except(['show']);
Route::patch('questions/{question}/toggle', [QuestionController::class, 'toggle'])->name('questions.toggle');

Route::resource('plans', PlanController::class)->only(['index', 'edit', 'update']);

Route::get('subscribers', [SubscriberController::class, 'index'])->name('subscribers.index');
Route::patch('subscribers/{subscription}/cancel', [SubscriberController::class, 'cancel'])->name('subscribers.cancel');
Route::patch('subscribers/{subscription}/activate', [SubscriberController::class, 'activate'])->name('subscribers.activate');
Route::patch('subscribers/{subscription}/extend', [SubscriberController::class, 'extend'])->name('subscribers.extend');

Route::get('users', [UserController::class, 'index'])->name('users.index');
Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggleActive');
Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
