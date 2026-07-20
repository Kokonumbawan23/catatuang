<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
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

        $totals = DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('wallet_id', $activeWallet?->id)
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income, SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense")
            ->first();

        $recentTransactions = $user->transactions()
            ->with(['category', 'wallet'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $categoryData = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id', 'left')
            ->where('transactions.user_id', $user->id)
            ->where('transactions.type', 'expense')
            ->whereMonth('transactions.transaction_date', $month)
            ->whereYear('transactions.transaction_date', $year)
            ->when($activeWallet, fn ($q) => $q->where('transactions.wallet_id', $activeWallet->id))
            ->selectRaw('transactions.category_id, categories.name, categories.color, SUM(transactions.amount) as total')
            ->groupBy('transactions.category_id', 'categories.name', 'categories.color')
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name ?? 'Tanpa Kategori',
                'color' => $item->color ?? '#95A5A6',
                'total' => (float) $item->total,
            ]);

        return response()->json([
            'data' => [
                'wallets' => $wallets,
                'active_wallet' => $activeWallet,
                'summary' => [
                    'total_income' => $totals->total_income ?? 0,
                    'total_expense' => $totals->total_expense ?? 0,
                    'balance' => $activeWallet?->balance ?? 0,
                    'month' => $month,
                    'year' => $year,
                ],
                'recent_transactions' => $recentTransactions,
                'category_data' => $categoryData,
            ],
        ]);
    }
}
