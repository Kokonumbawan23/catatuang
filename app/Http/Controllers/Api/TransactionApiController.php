<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\ActivityLogger;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TransactionApiController extends Controller
{
    public function __construct(
        private ActivityLogger $logger,
        private PushNotificationService $pushService
    ) {}

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

        $totals = DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('wallet_id', $activeWallet?->id)
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income, SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense")
            ->first();

        $categories = Cache::remember('categories', 3600, fn () => Category::all()); // ponytail: cache 1 hour, categories rarely change

        return response()->json([
            'data' => $transactions,
            'meta' => [
                'wallets' => $wallets,
                'active_wallet' => $activeWallet,
                'active_wallet_balance' => $activeWallet?->balance ?? 0,
                'total_income' => $totals->total_income ?? 0,
                'total_expense' => $totals->total_expense ?? 0,
                'categories' => $categories,
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
            $this->logger->unauthorizedAccess($user->id, 'wallet:'.$validated['wallet_id'], 'create_transaction');

            return response()->json(['message' => 'Dompet tidak valid.'], 422);
        }

        DB::transaction(function () use ($validated, $user, $wallet) {
            $validated['user_id'] = $user->id;
            $transaction = Transaction::create($validated);

            $amount = $validated['amount'];
            if ($validated['type'] === 'income') {
                $wallet->increment('balance', $amount);
            } else {
                $wallet->decrement('balance', $amount);
            }

            $this->logger->transactionCreated(
                $user->id,
                $transaction->id,
                $validated['type'],
                $amount,
                $wallet->id
            );
        });

        $wallet->refresh();
        $this->pushService->checkAndNotify($wallet);

        return response()->json([
            'message' => 'Transaksi berhasil dicatat.',
        ], 201);
    }

    public function show(Transaction $transaction): JsonResponse
    {
        $this->authorize('view', $transaction);

        $transaction->load(['category', 'wallet']);

        return response()->json([
            'data' => $transaction,
        ]);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $this->authorize('update', $transaction);

        $user = Auth::user();
        $validated = $request->validated();

        $wallet = Wallet::findOrFail($validated['wallet_id']);

        if ($wallet->user_id !== $user->id) {
            $this->logger->unauthorizedAccess($user->id, 'transaction:'.$transaction->id, 'update');

            return response()->json(['message' => 'Dompet tidak valid.'], 422);
        }

        $oldWallet = $transaction->wallet;
        $walletSwitched = $oldWallet->id !== $wallet->id;

        DB::transaction(function () use ($validated, $transaction, $user) {
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

            $this->logger->transactionUpdated($user->id, $transaction->id);
        });

        $oldWallet->refresh();
        $this->pushService->checkAndNotify($oldWallet);

        if ($walletSwitched) {
            $wallet->refresh();
            $this->pushService->checkAndNotify($wallet);
        }

        return response()->json([
            'message' => 'Transaksi berhasil diperbarui.',
        ]);
    }

    public function destroy(Transaction $transaction): JsonResponse
    {
        $this->authorize('delete', $transaction);

        $userId = Auth::id();
        $transactionId = $transaction->id;
        $wallet = $transaction->wallet;

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

        $this->logger->transactionDeleted($userId, $transactionId);

        $wallet->refresh();
        $this->pushService->checkAndNotify($wallet);

        return response()->json([
            'message' => 'Transaksi berhasil dihapus.',
        ]);
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $walletId = $request->input('wallet_id');

        $query = $user->transactions()
            ->with(['category', 'wallet'])
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year);

        if ($walletId) {
            $query->where('wallet_id', $walletId);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transactions_'.$year.'_'.$month.'.csv"',
        ];

        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Tanggal', 'Dompet', 'Kategori', 'Tipe', 'Nominal', 'Keterangan']);

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->transaction_date->format('Y-m-d'),
                    $t->wallet->name ?? '-',
                    $t->category->name ?? '-',
                    $t->type === 'income' ? 'Pemasukan' : 'Pengeluaran',
                    number_format($t->amount, 0, ',', '.'),
                    $t->description ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, 'transactions_'.$year.'_'.$month.'.csv', $headers);
    }
}
