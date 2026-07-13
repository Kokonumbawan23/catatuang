<?php

namespace App\Console\Commands;

use App\Services\RecurringTransactionScheduleService;
use Illuminate\Console\Command;

class ProcessRecurringTransactions extends Command
{
    protected $signature = 'recurring:process';

    protected $description = 'Process all active recurring transactions that are due today';

    public function handle(RecurringTransactionScheduleService $service): int
    {
        $this->info('Processing recurring transactions...');

        $count = $service->processAll();

        $this->info("Done. Created {$count} transaction(s).");

        return Command::SUCCESS;
    }
}
