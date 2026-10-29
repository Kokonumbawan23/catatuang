<?php

namespace App\Jobs;

use App\Models\RecurringOccurrence;
use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRecurringConfirmationReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public RecurringOccurrence $occurrence
    ) {}

    public function handle(PushNotificationService $pushService): void
    {
        $pushService->sendRecurringConfirmationReminder($this->occurrence);
    }
}
