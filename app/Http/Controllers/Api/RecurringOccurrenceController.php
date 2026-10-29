<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmRecurringOccurrenceRequest;
use App\Models\RecurringOccurrence;
use App\Services\RecurringOccurrenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RecurringOccurrenceController extends Controller
{
    public function __construct(
        private RecurringOccurrenceService $occurrences
    ) {}

    public function confirm(ConfirmRecurringOccurrenceRequest $request, RecurringOccurrence $recurringOccurrence): JsonResponse
    {
        $this->authorize('update', $recurringOccurrence);

        $transaction = $this->occurrences->confirm($recurringOccurrence, Auth::user(), $request->validated('amount'));

        return response()->json([
            'data' => $transaction,
            'message' => 'Pembayaran dikonfirmasi dan dicatat.',
        ]);
    }

    public function skip(RecurringOccurrence $recurringOccurrence): JsonResponse
    {
        $this->authorize('update', $recurringOccurrence);

        $this->occurrences->skip($recurringOccurrence);

        return response()->json([
            'message' => 'Tagihan dilewati, tidak dicatat.',
        ]);
    }
}
