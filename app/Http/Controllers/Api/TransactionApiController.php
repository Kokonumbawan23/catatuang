<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $wallets = $user->wallets()->orderBy('name')->get();
        $walletId = $request->input('wallet_id');

        $activeWallet = $walletId
            ? $wallets->firstWhere('id', $walletId)
            : $wallets->first();

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $search = $request->input('search');

        $query = $user->transactions()
            ->with(['category', 'wallet'])
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year);

        if ($activeWallet) {
            $query->where('wallet_id', $activeWallet->id);
        }

        if ($search) {
            $query->where('description', 'like', '%'.$search.'%');
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $baseQuery = $user->transactions()
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year);

        if ($activeWallet) {
            $baseQuery->where('wallet_id', $activeWallet->id);
        }

        $totalIncome = (clone $baseQuery)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $baseQuery)->where('type', 'expense')->sum('amount');
        $activeWalletBalance = $activeWallet ? $activeWallet->balance : 0;

        return response()->json([
            'data' => $transactions,
            'meta' => [
                'wallets' => $wallets,
                'active_wallet' => $activeWallet,
                'active_wallet_balance' => $activeWalletBalance,
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'categories' => Category::all(),
                'month' => $month,
                'year' => $year,
                'search' => $search,
            ],
        ]);
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        $wallet = Wallet::findOrFail($validated['wallet_id']);

        if ($wallet->user_id !== $user->id) {
            return response()->json(['message' => 'Dompet tidak valid.'], 422);
        }

        DB::transaction(function () use ($validated, $user) {
            $wallet = Wallet::findOrFail($validated['wallet_id']);

            $validated['user_id'] = $user->id;
            Transaction::create($validated);

            $amount = $validated['amount'];
            if ($validated['type'] === 'income') {
                $wallet->increment('balance', $amount);
            } else {
                $wallet->decrement('balance', $amount);
            }
        });

        return response()->json([
            'message' => 'Transaksi berhasil dicatat.',
        ], 201);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $this->authorize('update', $transaction);

        $user = Auth::user();
        $validated = $request->validated();

        $wallet = Wallet::findOrFail($validated['wallet_id']);

        if ($wallet->user_id !== $user->id) {
            return response()->json(['message' => 'Dompet tidak valid.'], 422);
        }

        DB::transaction(function () use ($validated, $transaction) {
            $oldWallet = $transaction->wallet;
            $oldAmount = $transaction->amount;
            $oldType = $transaction->type;

            if ($oldType === 'income') {
                $oldWallet->decrement('balance', $oldAmount);
            } else {
                $oldWallet->increment('balance', $oldAmount);
            }

            $transaction->update($validated);

            $newWallet = Wallet::findOrFail($validated['wallet_id']);
            $newAmount = $validated['amount'];
            $newType = $validated['type'];

            if ($newType === 'income') {
                $newWallet->increment('balance', $newAmount);
            } else {
                $newWallet->decrement('balance', $newAmount);
            }
        });

        return response()->json([
            'message' => 'Transaksi berhasil diperbarui.',
        ]);
    }

    public function destroy(Transaction $transaction): JsonResponse
    {
        $this->authorize('delete', $transaction);

        DB::transaction(function () use ($transaction) {
            $wallet = $transaction->wallet;
            $amount = $transaction->amount;

            if ($transaction->type === 'income') {
                $wallet->decrement('balance', $amount);
            } else {
                $wallet->increment('balance', $amount);
            }

            $transaction->delete();
        });

        return response()->json([
            'message' => 'Transaksi berhasil dihapus.',
        ]);
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $type = $request->input('type');

        $query = $user->transactions()
            ->with('category')
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year);

        if ($type) {
            $query->where('type', strtolower($type));
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        return response()->json([
            'data' => $transactions,
            'meta' => [
                'month' => $month,
                'year' => $year,
                'type' => $type,
            ],
        ]);
    }
}
