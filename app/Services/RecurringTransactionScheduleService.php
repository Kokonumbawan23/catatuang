<?php

namespace App\Services;

use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecurringTransactionScheduleService
{
    public function shouldExecuteToday(RecurringTransaction $recurring): bool
    {
        $today = Carbon::today();
        $config = $recurring->schedule_config;
        $lastExecuted = $recurring->last_executed_at
            ? Carbon::parse($recurring->last_executed_at)->startOfDay()
            : null;

        if ($recurring->last_executed_at && $lastExecuted->equalTo($today)) {
            return false;
        }

        return match ($recurring->frequency) {
            'daily' => $this->shouldExecuteDaily($today, $lastExecuted, $config),
            'weekly' => $this->shouldExecuteWeekly($today, $config),
            'monthly' => $this->shouldExecuteMonthly($today, $config),
            'yearly' => $this->shouldExecuteYearly($today, $config),
            default => false,
        };
    }

    protected function shouldExecuteDaily(Carbon $today, ?Carbon $lastExecuted, array $config): bool
    {
        $intervalDays = $config['interval_days'] ?? 1;

        if (! $lastExecuted) {
            return true;
        }

        $daysSinceLastExecution = $lastExecuted->diffInDays($today);

        return $daysSinceLastExecution >= $intervalDays;
    }

    protected function shouldExecuteWeekly(Carbon $today, array $config): bool
    {
        $dayOfWeek = $config['day_of_week'] ?? [];
        $dayOfWeek = array_map('intval', $dayOfWeek);

        return in_array($today->dayOfWeek, $dayOfWeek);
    }

    protected function shouldExecuteMonthly(Carbon $today, array $config): bool
    {
        $dayOfMonth = (int) ($config['day_of_month'] ?? 0);
        $intervalMonths = (int) ($config['interval_months'] ?? 1);

        if ($dayOfMonth === 0) {
            return false;
        }

        if ($today->day !== $dayOfMonth) {
            return false;
        }

        if ($intervalMonths <= 1) {
            return true;
        }

        $monthsSinceStart = Carbon::parse($dayOfMonth.'-'.$today->format('d'))->diffInMonths();

        return $monthsSinceStart % $intervalMonths === 0;
    }

    protected function shouldExecuteYearly(Carbon $today, array $config): bool
    {
        $dayOfMonth = (int) ($config['day_of_month'] ?? 0);
        $monthOfYear = (int) ($config['month_of_year'] ?? 0);

        if ($dayOfMonth === 0 || $monthOfYear === 0) {
            return false;
        }

        return $today->day === $dayOfMonth && $today->month === $monthOfYear;
    }

    protected function isWithinDateRange(RecurringTransaction $recurring): bool
    {
        $today = Carbon::today();

        if ($recurring->start_date && $today->lt(Carbon::parse($recurring->start_date))) {
            return false;
        }

        if ($recurring->end_date && $today->gt(Carbon::parse($recurring->end_date))) {
            return false;
        }

        return true;
    }

    public function execute(RecurringTransaction $recurring): ?Transaction
    {
        if (! $recurring->is_active) {
            return null;
        }

        if (! $this->isWithinDateRange($recurring)) {
            return null;
        }

        if (! $this->shouldExecuteToday($recurring)) {
            return null;
        }

        return DB::transaction(function () use ($recurring) {
            $transaction = Transaction::create([
                'user_id' => $recurring->user_id,
                'wallet_id' => $recurring->wallet_id,
                'category_id' => $recurring->category_id,
                'type' => $recurring->type,
                'amount' => $recurring->amount,
                'description' => $recurring->title.' (Otomatis)',
                'transaction_date' => now()->toDateString(),
            ]);

            $wallet = $recurring->wallet;
            if ($recurring->type === 'income') {
                $wallet->increment('balance', $recurring->amount);
            } else {
                $wallet->decrement('balance', $recurring->amount);
            }

            $recurring->update(['last_executed_at' => now()]);

            return $transaction;
        });
    }

    public function processAll(): int
    {
        $recurrings = RecurringTransaction::query()
            ->active()
            ->validDateRange()
            ->with('wallet')
            ->get();

        $count = 0;
        foreach ($recurrings as $recurring) {
            if ($this->execute($recurring)) {
                $count++;
            }
        }

        return $count;
    }
}
