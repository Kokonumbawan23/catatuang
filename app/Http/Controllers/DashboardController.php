<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $baseQuery = $user->transactions()
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year);

        $totalIncome = (clone $baseQuery)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $baseQuery)->where('type', 'expense')->sum('amount');

        $countMonth = (clone $baseQuery)->count();

        $avgMonth = (clone $baseQuery)->avg('amount') ?? 0;

        $recentTransactions = $user->transactions()
            ->with(['category', 'wallet'])
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->orderBy('transaction_date', 'desc')
            ->limit(5)
            ->get();

        $categoryData = $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->selectRaw('category_id, SUM(amount) as total')
            ->with('category')
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->category->name ?? 'Tanpa Kategori',
                    'color' => $item->category->color ?? '#95A5A6',
                    'total' => $item->total,
                ];
            });

        return view('dashboard', [
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'countMonth' => $countMonth,
            'avgMonth' => $avgMonth,
            'recentTransactions' => $recentTransactions,
            'categoryData' => $categoryData,
            'month' => $month,
            'year' => $year,
        ]);
    }
}
