<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $count = $this->pushService->sendTestPush($user);

        return response()->json([
            'message' => $count > 0
                ? "Test notification sent to {$count} device(s)."
                : 'No push subscriptions found for this user.',
            'subscriptions_count' => $count,
        ]);
    }
}
