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

        $totalMonth = $user->expenses()
            ->whereMonth('spent_at', $month)
            ->whereYear('spent_at', $year)
            ->sum('amount');

        $countMonth = $user->expenses()
            ->whereMonth('spent_at', $month)
            ->whereYear('spent_at', $year)
            ->count();

        $avgMonth = $user->expenses()
            ->whereMonth('spent_at', $month)
            ->whereYear('spent_at', $year)
            ->avg('amount') ?? 0;

        $recentExpenses = $user->expenses()
            ->with('category')
            ->whereMonth('spent_at', $month)
            ->whereYear('spent_at', $year)
            ->orderBy('spent_at', 'desc')
            ->limit(5)
            ->get();

        $categoryData = $user->expenses()
            ->whereMonth('spent_at', $month)
            ->whereYear('spent_at', $year)
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
            'totalMonth' => $totalMonth,
            'countMonth' => $countMonth,
            'avgMonth' => $avgMonth,
            'recentExpenses' => $recentExpenses,
            'categoryData' => $categoryData,
            'month' => $month,
            'year' => $year,
        ]);
    }
}
