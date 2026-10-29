<?php

namespace App\Http\Controllers\Api;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const RECENT_TRANSACTIONS_LIMIT = 5;

    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $wallets = $user->wallets()->orderBy('name')->get();
        // Tanpa wallet_id, ringkasan mencakup semua dompet milik user.
        $activeWallet = $wallets->firstWhere('id', $request->input('wallet_id'));

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $totals = DB::table('transactions')
            ->where('user_id', $user->id)
            ->when($activeWallet, fn ($totalsQuery) => $totalsQuery->where('wallet_id', $activeWallet->id))
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->selectRaw(sprintf(
                "SUM(CASE WHEN type = '%s' THEN amount ELSE 0 END) as total_income, SUM(CASE WHEN type = '%s' THEN amount ELSE 0 END) as total_expense",
                TransactionType::Income->value,
                TransactionType::Expense->value
            ))
            ->first();

        $recentTransactions = $user->transactions()
            ->with(['category', 'wallet'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(self::RECENT_TRANSACTIONS_LIMIT)
            ->get();

        $categoryData = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id', 'left')
            ->where('transactions.user_id', $user->id)
            ->where('transactions.type', TransactionType::Expense->value)
            ->whereMonth('transactions.transaction_date', $month)
            ->whereYear('transactions.transaction_date', $year)
            ->when($activeWallet, fn ($categoryQuery) => $categoryQuery->where('transactions.wallet_id', $activeWallet->id))
            ->selectRaw('transactions.category_id, categories.name, categories.color, SUM(transactions.amount) as total')
            ->groupBy('transactions.category_id', 'categories.name', 'categories.color')
            ->get()
            ->map(fn ($categoryBreakdown) => [
                'name' => $categoryBreakdown->name ?? 'Tanpa Kategori',
                'color' => $categoryBreakdown->color ?? '#95A5A6',
                'total' => (float) $categoryBreakdown->total,
            ]);

        return response()->json([
            'data' => [
                'wallets' => $wallets,
                'active_wallet' => $activeWallet,
                'summary' => [
                    'total_income' => $totals->total_income ?? 0,
                    'total_expense' => $totals->total_expense ?? 0,
                    'balance' => $activeWallet?->balance ?? $wallets->sum('balance'),
                    'month' => $month,
                    'year' => $year,
                ],
                'recent_transactions' => $recentTransactions,
                'category_data' => $categoryData,
            ],
        ]);
    }
}
