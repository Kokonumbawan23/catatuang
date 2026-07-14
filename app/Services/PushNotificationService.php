<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    private ?WebPush $webPush = null;

    public function __construct()
    {
        if (! config('webpush.vapid.public_key') || ! config('webpush.vapid.private_key')) {
            return;
        }

        $this->webPush = new WebPush([
            'VAPID' => [
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
                'subject' => config('webpush.vapid.subject'),
            ],
        ]);
    }

    public function subscribe(User $user, array $subscriptionData): PushSubscription
    {
        return PushSubscription::updateOrCreate(
            [
                'user_id' => $user->id,
                'endpoint' => $subscriptionData['endpoint'],
            ],
            [
                'public_key' => $subscriptionData['publicKey'] ?? null,
                'auth_token' => $subscriptionData['authToken'] ?? null,
                'content_encoding' => $subscriptionData['contentEncoding'] ?? 'aesgcm',
                'expires_at' => $subscriptionData['expirationTime'] ?? null,
            ]
        );
    }

    public function unsubscribe(User $user, string $endpoint): void
    {
        PushSubscription::where('user_id', $user->id)
            ->where('endpoint', $endpoint)
            ->delete();
    }

    public function sendBalanceLimitAlert(Wallet $wallet): int
    {
        if (! $wallet->balance_limit || $wallet->balance_limit <= 0) {
            return 0;
        }

        $threshold = (float) $wallet->balance_limit;
        $balance = (float) $wallet->balance;
        $pct = ($balance - $threshold) / $threshold * 100;

        // di atas threshold + 20% → gak alert
        if ($pct > 20) {
            return 0;
        }

        $subscriptions = PushSubscription::where('user_id', $wallet->user_id)->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $formattedBalance = 'Rp '.number_format($balance, 0, ',', '.');
        $formattedLimit = 'Rp '.number_format($threshold, 0, ',', '.');

        if ($pct <= 0) {
            $title = '🚨 Saldo Kritis';
            $body = "Dompet \"{$wallet->name}\" sudah di bawah batas! ({$formattedBalance} / {$formattedLimit})";
            $tag = 'balance-limit-critical-'.$wallet->id;
        } elseif ($pct <= 10) {
            $title = '⚠️ Saldo Hampir Habis';
            $body = "Dompet \"{$wallet->name}\" tinggal {$formattedBalance} — mendekati limit ({$formattedLimit})";
            $tag = 'balance-limit-warning-'.$wallet->id;
        } else {
            $title = 'ℹ️ Saldo Menipis';
            $body = "Dompet \"{$wallet->name}\" tersisa {$formattedBalance}. Limit dompet: {$formattedLimit}";
            $tag = 'balance-limit-info-'.$wallet->id;
        }

        $notification = [
            'title' => $title,
            'body' => $body,
            'icon' => '/icons/icon-192.png',
            'badge' => '/icons/icon-192.png',
            'tag' => $tag,
            'data' => [
                'type' => 'balance_limit',
                'wallet_id' => $wallet->id,
                'wallet_name' => $wallet->name,
                'balance' => $balance,
                'balance_limit' => $threshold,
                'percentage' => round($pct, 1),
            ],
        ];

        return $this->sendNotification($subscriptions, $notification);
    }

    public function sendTestPush(User $user): int
    {
        $subscriptions = PushSubscription::where('user_id', $user->id)->get();

        $payload = [
            'title' => '🔔 Test Notification',
            'body' => 'Push notification CatatUang berfungsi!',
            'icon' => '/icons/icon-192.png',
            'badge' => '/icons/icon-192.png',
            'tag' => 'test-notification',
            'data' => ['type' => 'test'],
        ];

        return $this->sendNotification($subscriptions, $payload);
    }

    public function sendNotification(iterable $subscriptions, array $payload): int
    {
        if (! $this->webPush) {
            return 0;
        }

        $count = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $sub = Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'publicKey' => $subscription->public_key,
                    'authToken' => $subscription->auth_token,
                    'contentEncoding' => $subscription->content_encoding,
                ]);

                $this->webPush->queueNotification($sub, json_encode($payload));

                $count++;
            } catch (\Exception $e) {
                report($e);
            }
        }

        $sent = 0;

        foreach ($this->webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $sent++;
            } else {
                Log::error('Push notification failed', [
                    'endpoint' => $report->getEndpoint(),
                    'reason' => $report->getReason(),
                    'success' => $report->isSuccess(),
                    'expired' => $report->isSubscriptionExpired(),
                ]);
            }
        }

        return $sent;
    }

    public function checkAndNotify(Wallet $wallet): int
    {
        return $this->sendBalanceLimitAlert($wallet);
    }
}
