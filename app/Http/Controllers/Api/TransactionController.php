<?php

namespace App\Http\Controllers\Api;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\TransactionExportService;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    private const PER_PAGE = 20;

    public function __construct(
        private TransactionService $transactions,
        private TransactionExportService $transactionExport
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

        $totals = (clone $query)->toBase()
            ->selectRaw(sprintf(
                "SUM(CASE WHEN type = '%s' THEN amount ELSE 0 END) as total_income, SUM(CASE WHEN type = '%s' THEN amount ELSE 0 END) as total_expense",
                TransactionType::Income->value,
                TransactionType::Expense->value
            ))
            ->first();

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(self::PER_PAGE);

        $categories = Category::cached();

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
        $this->transactions->create(Auth::user(), $request->validated());

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

        $this->transactions->update($transaction, Auth::user(), $request->validated());

        return response()->json([
            'message' => 'Transaksi berhasil diperbarui.',
        ]);
    }

    public function destroy(Transaction $transaction): JsonResponse
    {
        $this->authorize('delete', $transaction);

        $this->transactions->delete($transaction, Auth::user());

        return response()->json([
            'message' => 'Transaksi berhasil dihapus.',
        ]);
    }

    public function export(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $walletId = $request->input('wallet_id');
        $type = TransactionType::tryFrom((string) $request->input('type'));

        $transactions = $this->transactionExport->fetchForExport(Auth::user(), $month, $year, $walletId, $type);
        $csvRows = $this->transactionExport->toCsvRows($transactions);
        $filename = $this->transactionExport->csvFilename($year, $month);

        $callback = function () use ($csvRows) {
            $handle = fopen('php://output', 'w');

            foreach ($csvRows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
