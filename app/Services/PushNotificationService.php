<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use App\Models\Wallet;
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

        if ($wallet->balance > $wallet->balance_limit) {
            return 0;
        }

        $subscriptions = PushSubscription::where('user_id', $wallet->user_id)->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $remaining = $wallet->balance - $wallet->balance_limit;
        $formattedBalance = 'Rp '.number_format($wallet->balance, 0, ',', '.');
        $formattedLimit = 'Rp '.number_format($wallet->balance_limit, 0, ',', '.');

        $notification = [
            'title' => '⚠️ Alert Limit Saldo',
            'body' => "Saldo dompet \"{$wallet->name}\" telah mencapai {$formattedBalance} (limit: {$formattedLimit}). Sisa: {$remaining}",
            'icon' => '/icons/icon-192.png',
            'badge' => '/icons/icon-192.png',
            'tag' => 'balance-limit-'.$wallet->id,
            'data' => [
                'type' => 'balance_limit',
                'wallet_id' => $wallet->id,
                'wallet_name' => $wallet->name,
                'balance' => $wallet->balance,
                'balance_limit' => $wallet->balance_limit,
                'remaining' => $remaining,
            ],
        ];

        return $this->sendNotification($subscriptions, $notification);
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

        foreach ($this->webPush->flush() as $report) {
            // $report is a MessageSentReport
        }

        return $count;
    }

    public function checkAndNotify(Wallet $wallet): int
    {
        if (! $wallet->balance_limit || $wallet->balance_limit <= 0) {
            return 0;
        }

        if ($wallet->balance > $wallet->balance_limit) {
            return 0;
        }

        return $this->sendBalanceLimitAlert($wallet);
    }
}
