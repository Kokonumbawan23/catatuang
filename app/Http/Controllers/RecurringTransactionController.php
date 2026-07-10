<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringTransactionRequest;
use App\Http\Requests\UpdateRecurringTransactionRequest;
use App\Models\Category;
use App\Models\RecurringTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RecurringTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $recurrings = RecurringTransaction::with(['wallet', 'category'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $categories = Category::all();
        $wallets = $user->wallets()->orderBy('name')->get();

        $totalActiveThisMonth = $recurrings->where('is_active', true)->count();
        $monthlyCommitment = $recurrings->where('is_active', true)->sum('amount');

        return view('recurring-transactions.index', [
            'recurrings' => $recurrings,
            'categories' => $categories,
            'wallets' => $wallets,
            'totalActiveThisMonth' => $totalActiveThisMonth,
            'monthlyCommitment' => $monthlyCommitment,
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        return redirect()->route('recurring-transactions.index');
    }

    public function store(StoreRecurringTransactionRequest $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        Log::info('RecurringTransaction store', [
            'user_id' => $user->id,
            'is_ajax' => request()->ajax(),
            'wants_json' => $request->wantsJson(),
            'x_requested_with' => $request->header('X-Requested-With'),
            'accept' => $request->header('Accept'),
            'validated' => $request->validated(),
        ]);

        RecurringTransaction::create([
            ...$request->validated(),
            'user_id' => $user->id,
        ]);

        if ($request->wantsJson()) {
            Log::info('RecurringTransaction store - returning JSON');

            return response()->json(['success' => true, 'message' => 'Transaksi berulang berhasil dibuat.']);
        }

        Log::info('RecurringTransaction store - returning redirect');

        return redirect()->route('recurring-transactions.index')
            ->with('success', 'Transaksi berulang berhasil dibuat.');
    }

    public function edit(RecurringTransaction $recurringTransaction): RedirectResponse
    {
        return redirect()->route('recurring-transactions.index');
    }

    public function update(UpdateRecurringTransactionRequest $request, RecurringTransaction $recurringTransaction): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $recurringTransaction);

        Log::info('RecurringTransaction update', [
            'recurring_id' => $recurringTransaction->id,
            'is_ajax' => request()->ajax(),
            'wants_json' => $request->wantsJson(),
            'x_requested_with' => $request->header('X-Requested-With'),
            'accept' => $request->header('Accept'),
            'validated' => $request->validated(),
        ]);

        $recurringTransaction->update($request->validated());

        if ($request->wantsJson()) {
            Log::info('RecurringTransaction update - returning JSON');

            return response()->json(['success' => true, 'message' => 'Transaksi berulang berhasil diperbarui.']);
        }

        Log::info('RecurringTransaction update - returning redirect');

        return redirect()->route('recurring-transactions.index')
            ->with('success', 'Transaksi berulang berhasil diperbarui.');
    }

    public function destroy(Request $request, RecurringTransaction $recurringTransaction): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $recurringTransaction);

        Log::info('RecurringTransaction destroy', [
            'recurring_id' => $recurringTransaction->id,
            'is_ajax' => request()->ajax(),
            'wants_json' => $request->wantsJson(),
            'x_requested_with' => $request->header('X-Requested-With'),
            'accept' => $request->header('Accept'),
        ]);

        $recurringTransaction->delete();

        if ($request->wantsJson()) {
            Log::info('RecurringTransaction destroy - returning JSON');

            return response()->json(['success' => true, 'message' => 'Transaksi berulang berhasil dihapus.']);
        }

        Log::info('RecurringTransaction destroy - returning redirect');

        return redirect()->route('recurring-transactions.index')
            ->with('success', 'Transaksi berulang berhasil dihapus.');
    }
}
