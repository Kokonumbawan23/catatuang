<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function index(): View
    {
        $wallets = Auth::user()->wallets()
            ->withCount('transactions')
            ->orderBy('name')
            ->get();

        return view('wallets.index', [
            'wallets' => $wallets,
        ]);
    }

    public function create(): View
    {
        return view('wallets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'balance' => ['required', 'numeric', 'min:0', 'max:99999999999999'],
        ]);

        $validated['user_id'] = Auth::id();

        Wallet::create($validated);

        return redirect()->route('wallets.index')->with('success', 'Dompet berhasil dibuat.');
    }

    public function show(Wallet $wallet): RedirectResponse
    {
        return redirect()->route('wallets.index');
    }

    public function edit(Wallet $wallet): View
    {
        $this->authorize('update', $wallet);

        return view('wallets.edit', [
            'wallet' => $wallet,
        ]);
    }

    public function update(Request $request, Wallet $wallet): RedirectResponse
    {
        $this->authorize('update', $wallet);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'balance' => ['required', 'numeric', 'min:0', 'max:99999999999999'],
        ]);

        $wallet->update($validated);

        return redirect()->route('wallets.index')->with('success', 'Dompet berhasil diperbarui.');
    }

    public function destroy(Wallet $wallet): RedirectResponse
    {
        $this->authorize('delete', $wallet);

        $wallet->delete();

        return redirect()->route('wallets.index')->with('success', 'Dompet berhasil dihapus.');
    }
}
