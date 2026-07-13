<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWalletRequest;
use App\Http\Requests\UpdateWalletRequest;
use App\Models\Wallet;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletApiController extends Controller
{
    public function __construct(
        private ActivityLogger $logger
    ) {}

    public function index(Request $request): JsonResponse
    {
        $wallets = Auth::user()->wallets()
            ->withCount('transactions')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $wallets,
        ]);
    }

    public function store(StoreWalletRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        $wallet = Wallet::create($validated);

        $this->logger->walletCreated(Auth::id(), $wallet->id, $wallet->name);

        return response()->json([
            'data' => $wallet,
            'message' => 'Dompet berhasil dibuat.',
        ], 201);
    }

    public function show(Wallet $wallet): JsonResponse
    {
        $this->authorize('view', $wallet);

        $wallet->loadCount('transactions');

        return response()->json([
            'data' => $wallet,
        ]);
    }

    public function update(UpdateWalletRequest $request, Wallet $wallet): JsonResponse
    {
        $this->authorize('update', $wallet);

        $wallet->update($request->validated());

        $this->logger->walletUpdated(Auth::id(), $wallet->id);

        return response()->json([
            'data' => $wallet,
            'message' => 'Dompet berhasil diperbarui.',
        ]);
    }

    public function destroy(Wallet $wallet): JsonResponse
    {
        $this->authorize('delete', $wallet);

        $walletId = $wallet->id;
        $wallet->delete();

        $this->logger->walletDeleted(Auth::id(), $walletId);

        return response()->json([
            'message' => 'Dompet berhasil dihapus.',
        ]);
    }
}
