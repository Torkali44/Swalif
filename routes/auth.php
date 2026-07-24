<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    // توافق خلفي: صفحة الأدمن القديمة توجّه لنفس صفحة الدخول
    Route::get('/login/admin', [LoginController::class, 'adminCreate'])->name('login.admin');
    Route::post('/login/admin', [LoginController::class, 'adminStore'])->name('login.admin.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
