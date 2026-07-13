<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $query = $user->transactions()
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year);

        if ($activeWallet) {
            $query->where('wallet_id', $activeWallet->id);
        }

        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');
        $activeWalletBalance = $activeWallet ? $activeWallet->balance : 0;

        $recentTransactions = $user->transactions()
            ->with(['category', 'wallet'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $categoryData = $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->when($activeWallet, function ($query) use ($activeWallet) {
                $query->where('wallet_id', $activeWallet->id);
            })
            ->selectRaw('category_id, SUM(amount) as total')
            ->with('category')
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->category->name ?? 'Tanpa Kategori',
                    'color' => $item->category->color ?? '#95A5A6',
                    'total' => (float) $item->total,
                ];
            });

        return response()->json([
            'data' => [
                'wallets' => $wallets,
                'active_wallet' => $activeWallet,
                'summary' => [
                    'total_income' => $totalIncome,
                    'total_expense' => $totalExpense,
                    'balance' => $activeWalletBalance,
                    'month' => $month,
                    'year' => $year,
                ],
                'recent_transactions' => $recentTransactions,
                'category_data' => $categoryData,
            ],
        ]);
    }
}
