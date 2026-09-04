<?php

namespace Tests\Unit;

use App\Enums\BalanceAlertLevel;
use App\Models\PushSubscription;
use App\Models\Wallet;
use App\Services\PushNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function walletWithSubscriber(float $balance, float $balanceLimit): Wallet
    {
        $wallet = Wallet::factory()->create([
            'balance' => $balance,
            'balance_limit' => $balanceLimit,
        ]);

        PushSubscription::create([
            'user_id' => $wallet->user_id,
            'endpoint' => 'https://push.example.com/'.uniqid(),
        ]);

        return $wallet;
    }

    private function serviceThatCountsSends(int &$sendCount): PushNotificationService
    {
        $service = \Mockery::mock(PushNotificationService::class)->makePartial();
        $service->shouldReceive('sendNotification')
            ->andReturnUsing(function () use (&$sendCount) {
                $sendCount++;

                return 1;
            });

        return $service;
    }

    public function test_first_time_crossing_threshold_sends_alert_and_records_level(): void
    {
        $sendCount = 0;
        $service = $this->serviceThatCountsSends($sendCount);

        // Saldo 1050 dari limit 1000 -> 5% di atas limit -> level "warning".
        $wallet = $this->walletWithSubscriber(balance: 1050, balanceLimit: 1000);

        $service->sendBalanceLimitAlert($wallet);

        $this->assertSame(1, $sendCount);
        $this->assertEquals(BalanceAlertLevel::Warning, $wallet->refresh()->last_alert_level);
    }

    public function test_repeated_check_at_same_level_does_not_resend(): void
    {
        $sendCount = 0;
        $service = $this->serviceThatCountsSends($sendCount);

        $wallet = $this->walletWithSubscriber(balance: 900, balanceLimit: 1000);

        $service->sendBalanceLimitAlert($wallet);
        $service->sendBalanceLimitAlert($wallet->refresh());
        $service->sendBalanceLimitAlert($wallet->refresh());

        $this->assertSame(1, $sendCount, 'Alert level yang sama seharusnya cuma dikirim sekali.');
    }

    public function test_escalating_to_a_more_severe_level_sends_alert_again(): void
    {
        $sendCount = 0;
        $service = $this->serviceThatCountsSends($sendCount);

        $wallet = $this->walletWithSubscriber(balance: 1050, balanceLimit: 1000);
        $service->sendBalanceLimitAlert($wallet);
        $this->assertSame(1, $sendCount);

        // Saldo turun sampai di bawah limit -> level naik jadi "critical".
        $wallet->refresh()->update(['balance' => 900]);
        $service->sendBalanceLimitAlert($wallet->refresh());

        $this->assertSame(2, $sendCount, 'Kenaikan level (warning -> critical) harus tetap mengirim alert baru.');
        $this->assertEquals(BalanceAlertLevel::Critical, $wallet->refresh()->last_alert_level);
    }

    public function test_recovering_above_threshold_clears_alert_level(): void
    {
        $sendCount = 0;
        $service = $this->serviceThatCountsSends($sendCount);

        $wallet = $this->walletWithSubscriber(balance: 1050, balanceLimit: 1000);
        $service->sendBalanceLimitAlert($wallet);
        $this->assertEquals(BalanceAlertLevel::Warning, $wallet->refresh()->last_alert_level);

        // Saldo pulih jauh di atas limit -> level di-reset, tidak ada alert baru.
        $wallet->update(['balance' => 5000]);
        $service->sendBalanceLimitAlert($wallet->refresh());

        $this->assertSame(1, $sendCount, 'Pemulihan saldo tidak boleh mengirim alert baru.');
        $this->assertNull($wallet->refresh()->last_alert_level);
    }
}
