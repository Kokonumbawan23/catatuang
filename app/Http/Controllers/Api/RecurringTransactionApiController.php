<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecurringTransactionRequest;
use App\Http\Requests\UpdateRecurringTransactionRequest;
use App\Models\Category;
use App\Models\RecurringTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecurringTransactionApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $recurrings = RecurringTransaction::with(['wallet', 'category'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $categories = Category::all();
        $wallets = $user->wallets()->orderBy('name')->get();

        $totalActiveThisMonth = $recurrings->where('is_active', true)->count();
        $monthlyCommitment = $recurrings->where('is_active', true)->sum('amount');

        return response()->json([
            'data' => $recurrings,
            'meta' => [
                'categories' => $categories,
                'wallets' => $wallets,
                'total_active_this_month' => $totalActiveThisMonth,
                'monthly_commitment' => $monthlyCommitment,
            ],
        ]);
    }

    public function store(StoreRecurringTransactionRequest $request): JsonResponse
    {
        $user = Auth::user();

        RecurringTransaction::create([
            ...$request->validated(),
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Transaksi berulang berhasil dibuat.',
        ], 201);
    }

    public function update(UpdateRecurringTransactionRequest $request, RecurringTransaction $recurringTransaction): JsonResponse
    {
        $this->authorize('update', $recurringTransaction);

        $recurringTransaction->update($request->validated());

        return response()->json([
            'message' => 'Transaksi berulang berhasil diperbarui.',
        ]);
    }

    public function toggleActive(RecurringTransaction $recurringTransaction): JsonResponse
    {
        $this->authorize('update', $recurringTransaction);

        $recurringTransaction->update([
            'is_active' => ! $recurringTransaction->is_active,
        ]);

        return response()->json([
            'data' => $recurringTransaction,
            'message' => $recurringTransaction->is_active ? 'Transaksi berulang diaktifkan.' : 'Transaksi berulang dinonaktifkan.',
        ]);
    }

    public function destroy(RecurringTransaction $recurringTransaction): JsonResponse
    {
        $this->authorize('delete', $recurringTransaction);

        $recurringTransaction->delete();

        return response()->json([
            'message' => 'Transaksi berulang berhasil dihapus.',
        ]);
    }
}
