<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileApiController extends Controller
{
    public function __construct(
        private ActivityLogger $logger
    ) {}

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $changedFields = array_keys($validated);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->logger->profileUpdated($user->id, $changedFields);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user,
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->logger->passwordChanged($request->user()->id);

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $userId = $user->id;
        $userEmail = $user->email;

        $user->currentAccessToken()->delete();

        $user->delete();

        $this->logger->accountDeleted($userId, $userEmail);

        return response()->json([
            'message' => 'Account deleted successfully.',
        ]);
    }
}
