<?php

namespace App\Jobs;

use App\Models\Wallet;
use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBalanceLimitAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Wallet $wallet
    ) {}

    public function handle(PushNotificationService $pushService): void
    {
        $pushService->checkAndNotify($this->wallet);
    }
}
