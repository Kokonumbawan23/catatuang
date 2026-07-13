<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecurringTransactionRequest;
use App\Http\Requests\UpdateRecurringTransactionRequest;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecurringTransactionApiController extends Controller
{
    public function __construct(
        private ActivityLogger $logger
    ) {}

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

        $recurring = RecurringTransaction::create([
            ...$request->validated(),
            'user_id' => $user->id,
        ]);

        $this->logger->recurringTransactionCreated($user->id, $recurring->id, $recurring->type);

        return response()->json([
            'message' => 'Transaksi berulang berhasil dibuat.',
        ], 201);
    }

    public function update(UpdateRecurringTransactionRequest $request, RecurringTransaction $recurringTransaction): JsonResponse
    {
        $this->authorize('update', $recurringTransaction);

        $recurringTransaction->update($request->validated());

        $this->logger->recurringTransactionUpdated(Auth::id(), $recurringTransaction->id);

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

        $this->logger->recurringTransactionToggled(
            Auth::id(),
            $recurringTransaction->id,
            $recurringTransaction->is_active
        );

        return response()->json([
            'data' => $recurringTransaction,
            'message' => $recurringTransaction->is_active ? 'Transaksi berulang diaktifkan.' : 'Transaksi berulang dinonaktifkan.',
        ]);
    }

    public function destroy(RecurringTransaction $recurringTransaction): JsonResponse
    {
        $this->authorize('delete', $recurringTransaction);

        $recurringId = $recurringTransaction->id;
        $recurringTransaction->delete();

        $this->logger->recurringTransactionDeleted(Auth::id(), $recurringId);

        return response()->json([
            'message' => 'Transaksi berulang berhasil dihapus.',
        ]);
    }
}
