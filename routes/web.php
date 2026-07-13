<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('spa'));

Route::get('/spa', fn () => view('spa'))->name('spa');

Route::get('/dashboard', fn () => redirect()->route('spa'))->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/spa/{any}', fn () => view('spa'))->where('any', '.*');

Route::middleware('guest')->group(function () {
    Route::get('login', fn () => redirect()->route('spa'))->name('login');
    Route::post('login', fn () => redirect()->route('spa'));
    Route::get('register', fn () => redirect()->route('spa'))->name('register');
    Route::post('register', fn () => redirect()->route('spa'));
    Route::get('forgot-password', fn () => redirect()->route('spa'))->name('password.request');
    Route::post('forgot-password', fn () => redirect()->route('spa'))->name('password.email');
    Route::get('reset-password/{token}', fn () => redirect()->route('spa'))->name('password.reset');
    Route::post('reset-password', fn () => redirect()->route('spa'))->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', fn () => redirect()->route('spa'))->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', fn () => redirect()->route('spa'))->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', fn () => redirect()->route('spa'))->middleware('throttle:6,1')->name('verification.send');
    Route::get('confirm-password', fn () => redirect()->route('spa'))->name('password.confirm');
    Route::post('confirm-password', fn () => redirect()->route('spa'));
    Route::put('password', fn () => redirect()->route('spa'))->name('password.update');
    Route::post('logout', fn () => redirect()->route('spa'))->name('logout');
});
