<?php

namespace Tests\Unit;

use App\Models\RecurringTransaction;
use App\Services\RecurringTransactionScheduleService;
use App\Services\WalletBalanceService;
use Carbon\Carbon;
use Tests\TestCase;

class RecurringTransactionScheduleServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function makeMonthlyRecurring(string $startDate, int $dayOfMonth, int $intervalMonths): RecurringTransaction
    {
        return new RecurringTransaction([
            'frequency' => 'monthly',
            'schedule_config' => [
                'day_of_month' => $dayOfMonth,
                'interval_months' => $intervalMonths,
            ],
            'start_date' => $startDate,
            'last_executed_at' => null,
        ]);
    }

    public function test_monthly_recurring_without_interval_runs_every_month(): void
    {
        $service = new RecurringTransactionScheduleService(new WalletBalanceService);
        $recurring = $this->makeMonthlyRecurring('2026-01-15', 15, 1);

        Carbon::setTestNow('2026-02-15');
        $this->assertTrue($service->shouldExecuteToday($recurring));

        Carbon::setTestNow('2026-03-15');
        $this->assertTrue($service->shouldExecuteToday($recurring));
    }

    public function test_monthly_recurring_with_interval_skips_months_in_between(): void
    {
        $service = new RecurringTransactionScheduleService(new WalletBalanceService);
        $recurring = $this->makeMonthlyRecurring('2026-01-15', 15, 3);

        // 1 bulan setelah start_date: belum waktunya (interval 3 bulan).
        Carbon::setTestNow('2026-02-15');
        $this->assertFalse($service->shouldExecuteToday($recurring));

        // 2 bulan setelah start_date: masih belum waktunya.
        Carbon::setTestNow('2026-03-15');
        $this->assertFalse($service->shouldExecuteToday($recurring));

        // Tepat 3 bulan setelah start_date: harus jalan.
        Carbon::setTestNow('2026-04-15');
        $this->assertTrue($service->shouldExecuteToday($recurring));

        // 4 bulan setelah start_date: belum kelipatan 3 lagi.
        Carbon::setTestNow('2026-05-15');
        $this->assertFalse($service->shouldExecuteToday($recurring));

        // 6 bulan setelah start_date (kelipatan 3 berikutnya): harus jalan.
        Carbon::setTestNow('2026-07-15');
        $this->assertTrue($service->shouldExecuteToday($recurring));
    }

    public function test_monthly_recurring_does_not_run_on_wrong_day(): void
    {
        $service = new RecurringTransactionScheduleService(new WalletBalanceService);
        $recurring = $this->makeMonthlyRecurring('2026-01-15', 15, 3);

        Carbon::setTestNow('2026-04-16');
        $this->assertFalse($service->shouldExecuteToday($recurring));
    }
}
