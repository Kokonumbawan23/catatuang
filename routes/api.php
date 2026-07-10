<?php

use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\RecurringTransactionApiController;
use App\Http\Controllers\Api\TransactionApiController;
use App\Http\Controllers\Api\WalletApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [DashboardApiController::class, 'index'])->name('api.dashboard');
    Route::apiResource('wallets', WalletApiController::class)->names([
        'index' => 'api.wallets.index',
        'store' => 'api.wallets.store',
        'show' => 'api.wallets.show',
        'update' => 'api.wallets.update',
        'destroy' => 'api.wallets.destroy',
    ]);
    Route::apiResource('transactions', TransactionApiController::class)->except(['export'])->names([
        'index' => 'api.transactions.index',
        'store' => 'api.transactions.store',
        'show' => 'api.transactions.show',
        'update' => 'api.transactions.update',
        'destroy' => 'api.transactions.destroy',
    ]);
    Route::get('/transactions/export', [TransactionApiController::class, 'export'])->name('api.transactions.export');
    Route::apiResource('recurring-transactions', RecurringTransactionApiController::class)->names([
        'index' => 'api.recurring-transactions.index',
        'store' => 'api.recurring-transactions.store',
        'show' => 'api.recurring-transactions.show',
        'update' => 'api.recurring-transactions.update',
        'destroy' => 'api.recurring-transactions.destroy',
    ]);
    Route::patch('/recurring-transactions/{recurringTransaction}/toggle', [RecurringTransactionApiController::class, 'toggleActive'])->name('api.recurring-transactions.toggle');
    Route::get('/categories', [CategoryApiController::class, 'index'])->name('api.categories.index');
});
