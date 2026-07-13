<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PushApiController extends Controller
{
    public function __construct(
        private PushNotificationService $pushService
    ) {}

    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'url', 'max:500'],
            'publicKey' => ['nullable', 'string', 'max:500'],
            'authToken' => ['nullable', 'string', 'max:500'],
            'contentEncoding' => ['nullable', 'string', 'max:50'],
            'expirationTime' => ['nullable'],
        ]);

        $this->pushService->subscribe(Auth::user(), $validated);

        return response()->json(['message' => 'Push subscription saved.']);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'url', 'max:500'],
        ]);

        $this->pushService->unsubscribe(Auth::user(), $validated['endpoint']);

        return response()->json(['message' => 'Push subscription removed.']);
    }

    public function test(Request $request): JsonResponse
    {
        $user = Auth::user();
        $wallet = $user->wallets()->whereNotNull('balance_limit')
            ->where('balance_limit', '>', 0)
            ->first();

        if (! $wallet) {
            return response()->json(['message' => 'No wallet with balance limit found.'], 422);
        }

        $count = $this->pushService->sendBalanceLimitAlert($wallet);

        if ($count > 0) {
            Log::info("Test push notification sent to {$count} subscription(s) for wallet {$wallet->name}.");
        }

        return response()->json([
            'message' => $count > 0
                ? "Test notification sent to {$count} device(s)."
                : 'No push subscriptions found for this user.',
            'subscriptions_count' => $count,
        ]);
    }
}
