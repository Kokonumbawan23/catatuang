<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $expenses = $user->expenses()
            ->with('category')
            ->whereMonth('spent_at', $month)
            ->whereYear('spent_at', $year)
            ->orderBy('spent_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalMonth = $user->expenses()
            ->whereMonth('spent_at', $month)
            ->whereYear('spent_at', $year)
            ->sum('amount');

        return view('expenses.index', [
            'expenses' => $expenses,
            'totalMonth' => $totalMonth,
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function create(): View
    {
        $categories = Category::all();
        return view('expenses.create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        Expense::create($validated);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function edit(Expense $expense): View
    {
        $this->authorize('update', $expense);

        $categories = Category::all();
        return view('expenses.edit', [
            'expense' => $expense,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorize('update', $expense);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:1', 'max:99999999999999'],
            'description' => ['nullable', 'string', 'max:1000'],
            'spent_at' => ['required', 'date'],
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil dihapus.');
    }

    public function show(Expense $expense): RedirectResponse
    {
        return redirect()->route('expenses.index');
    }
}
