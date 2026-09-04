<?php

namespace App\Services;

use App\Enums\BalanceAlertLevel;
use App\Models\PushSubscription;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    private const NOTIFY_THRESHOLD_PERCENT = 20;

    private const WARNING_THRESHOLD_PERCENT = 10;

    private const CRITICAL_THRESHOLD_PERCENT = 0;

    private const NOTIFICATION_ICON = '/icons/icon-192.png';

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

        if ($pct > self::NOTIFY_THRESHOLD_PERCENT) {
            $this->clearAlertLevel($wallet);

            return 0;
        }

        $level = $this->resolveAlertLevel($pct);

        // Level sama dengan alert terakhir yang sudah dikirim -> jangan spam, tunggu sampai levelnya berubah.
        if ($wallet->last_alert_level === $level) {
            return 0;
        }

        $subscriptions = PushSubscription::where('user_id', $wallet->user_id)->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $notification = $this->buildBalanceLimitNotification($wallet, $level, $balance, $threshold, $pct);

        $sent = $this->sendNotification($subscriptions, $notification);

        $wallet->update(['last_alert_level' => $level]);

        return $sent;
    }

    private function resolveAlertLevel(float $pct): BalanceAlertLevel
    {
        return match (true) {
            $pct <= self::CRITICAL_THRESHOLD_PERCENT => BalanceAlertLevel::Critical,
            $pct <= self::WARNING_THRESHOLD_PERCENT => BalanceAlertLevel::Warning,
            default => BalanceAlertLevel::Info,
        };
    }

    private function clearAlertLevel(Wallet $wallet): void
    {
        if ($wallet->last_alert_level !== null) {
            $wallet->update(['last_alert_level' => null]);
        }
    }

    private function buildBalanceLimitNotification(Wallet $wallet, BalanceAlertLevel $level, float $balance, float $threshold, float $pct): array
    {
        $formattedBalance = 'Rp '.number_format($balance, 0, ',', '.');
        $formattedLimit = 'Rp '.number_format($threshold, 0, ',', '.');

        [$title, $body] = match ($level) {
            BalanceAlertLevel::Critical => [
                '🚨 Saldo Kritis',
                "Dompet \"{$wallet->name}\" sudah di bawah batas! ({$formattedBalance} / {$formattedLimit})",
            ],
            BalanceAlertLevel::Warning => [
                '⚠️ Saldo Hampir Habis',
                "Dompet \"{$wallet->name}\" tinggal {$formattedBalance} — mendekati limit ({$formattedLimit})",
            ],
            BalanceAlertLevel::Info => [
                'ℹ️ Saldo Menipis',
                "Dompet \"{$wallet->name}\" tersisa {$formattedBalance}. Limit dompet: {$formattedLimit}",
            ],
        };

        return [
            'title' => $title,
            'body' => $body,
            'icon' => self::NOTIFICATION_ICON,
            'badge' => self::NOTIFICATION_ICON,
            'tag' => 'balance-limit-'.$level->value.'-'.$wallet->id,
            'data' => [
                'type' => 'balance_limit',
                'wallet_id' => $wallet->id,
                'wallet_name' => $wallet->name,
                'balance' => $balance,
                'balance_limit' => $threshold,
                'percentage' => round($pct, 1),
            ],
        ];
    }

    public function sendTestPush(User $user): int
    {
        $subscriptions = PushSubscription::where('user_id', $user->id)->get();

        $payload = [
            'title' => '🔔 Test Notification',
            'body' => 'Push notification CatatUang berfungsi!',
            'icon' => self::NOTIFICATION_ICON,
            'badge' => self::NOTIFICATION_ICON,
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
