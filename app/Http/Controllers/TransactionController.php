<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $wallets = $user->wallets()->orderBy('name')->get();
        $walletId = $request->input('wallet_id');

        $activeWallet = $walletId
            ? $wallets->firstWhere('id', $walletId)
            : $wallets->first();

        if (! $activeWallet && $wallets->isEmpty()) {
            $activeWallet = null;
        }

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
            ->paginate(20)
            ->appends($request->query());

        $baseQuery = $user->transactions()
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year);

        if ($activeWallet) {
            $baseQuery->where('wallet_id', $activeWallet->id);
        }

        $totalIncome = (clone $baseQuery)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $baseQuery)->where('type', 'expense')->sum('amount');
        $activeWalletBalance = $activeWallet ? $activeWallet->balance : 0;

        return view('transactions.index', [
            'transactions' => $transactions,
            'wallets' => $wallets,
            'activeWallet' => $activeWallet,
            'activeWalletBalance' => $activeWalletBalance,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'categories' => Category::all(),
            'month' => $month,
            'year' => $year,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        $categories = Category::all();
        $wallets = Auth::user()->wallets()->orderBy('name')->get();

        return view('transactions.create', [
            'categories' => $categories,
            'wallets' => $wallets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'wallet_id' => [
                'required',
                'exists:wallets,id',
                function ($attribute, $value, $fail) use ($user) {
                    if (! Wallet::where('id', $value)->where('user_id', $user->id)->exists()) {
                        $fail('Dompet tidak valid.');
                    }
                },
            ],
            'type' => ['required', 'in:income,expense'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:1', 'max:99999999999999'],
            'description' => ['nullable', 'string', 'max:1000'],
            'transaction_date' => ['required', 'date'],
        ]);

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

        return redirect()->route('transactions.index', ['wallet_id' => $validated['wallet_id']])
            ->with('success', 'Transaksi berhasil dicatat.');
    }

    public function show(Transaction $transaction): RedirectResponse
    {
        return redirect()->route('transactions.index');
    }

    public function edit(Transaction $transaction): View
    {
        $this->authorize('update', $transaction);

        $categories = Category::all();
        $wallets = Auth::user()->wallets()->orderBy('name')->get();

        return view('transactions.edit', [
            'transaction' => $transaction,
            'categories' => $categories,
            'wallets' => $wallets,
        ]);
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $user = Auth::user();

        $validated = $request->validate([
            'wallet_id' => [
                'required',
                'exists:wallets,id',
                function ($attribute, $value, $fail) use ($user) {
                    if (! Wallet::where('id', $value)->where('user_id', $user->id)->exists()) {
                        $fail('Dompet tidak valid.');
                    }
                },
            ],
            'type' => ['required', 'in:income,expense'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:1', 'max:99999999999999'],
            'description' => ['nullable', 'string', 'max:1000'],
            'transaction_date' => ['required', 'date'],
        ]);

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

        return redirect()->route('transactions.index', ['wallet_id' => $validated['wallet_id']])
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $walletId = $transaction->wallet_id;

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

        return redirect()->route('transactions.index', ['wallet_id' => $walletId])
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $format = $request->input('format', 'csv');
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $type = $request->input('type');

        if ($user === null) {
            abort(403, 'User is null');
        }

        if (! in_array($format, ['csv', 'xlsx', 'pdf'])) {
            abort(404, 'Invalid format: '.$format);
        }

        $query = $user->transactions()
            ->with('category')
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year);

        if ($type) {
            $query->where('type', strtolower($type));
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        $filename = 'transactions_'.$month.'_'.$year.'.'.$format;

        if ($format === 'csv') {
            $headers = ['Content-Type' => 'text/csv; charset=UTF-8'];

            return response()->stream(function () use ($transactions) {
                $handle = fopen('php://output', 'w');
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($handle, ['Tanggal', 'Jenis', 'Kategori', 'Jumlah', 'Deskripsi']);

                foreach ($transactions as $t) {
                    fputcsv($handle, [
                        $t->transaction_date->format('d-m-Y'),
                        $t->type === 'income' ? 'Pemasukan' : 'Pengeluaran',
                        $t->category?->name ?? '-',
                        number_format($t->amount, 0, ',', '.'),
                        $t->description ?? '',
                    ]);
                }
                fclose($handle);
            }, 200, array_merge($headers, ['Content-Disposition' => 'attachment; filename="'.$filename.'"']));
        }

        abort(404, 'Format not supported: '.$format);
    }
}
