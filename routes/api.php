<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PushController;
use App\Http\Controllers\Api\RecurringTransactionController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/auth/login', [AuthController::class, 'login'])->name('api.auth.login');
Route::post('/auth/register', [AuthController::class, 'register'])->name('api.auth.register');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
    Route::get('/auth/me', [AuthController::class, 'me'])->name('api.auth.me');

    Route::get('/profile', [ProfileController::class, 'show'])->name('api.profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('api.profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('api.profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('api.profile.destroy');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.dashboard');
    Route::get('/transactions/export', [TransactionController::class, 'export'])->name('api.transactions.export');
    Route::apiResource('wallets', WalletController::class)->names([
        'index' => 'api.wallets.index',
        'store' => 'api.wallets.store',
        'show' => 'api.wallets.show',
        'update' => 'api.wallets.update',
        'destroy' => 'api.wallets.destroy',
    ]);
    Route::apiResource('transactions', TransactionController::class)->except(['export'])->names([
        'index' => 'api.transactions.index',
        'store' => 'api.transactions.store',
        'show' => 'api.transactions.show',
        'update' => 'api.transactions.update',
        'destroy' => 'api.transactions.destroy',
    ]);
    Route::apiResource('recurring-transactions', RecurringTransactionController::class)->names([
        'index' => 'api.recurring-transactions.index',
        'store' => 'api.recurring-transactions.store',
        'show' => 'api.recurring-transactions.show',
        'update' => 'api.recurring-transactions.update',
        'destroy' => 'api.recurring-transactions.destroy',
    ]);
    Route::patch('/recurring-transactions/{recurringTransaction}/toggle', [RecurringTransactionController::class, 'toggleActive'])->name('api.recurring-transactions.toggle');
    Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
    Route::post('/push/subscribe', [PushController::class, 'subscribe'])->name('api.push.subscribe');
    Route::delete('/push/unsubscribe', [PushController::class, 'unsubscribe'])->name('api.push.unsubscribe');
    Route::post('/push/test', [PushController::class, 'test'])->name('api.push.test');
});
