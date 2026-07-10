<?php

namespace App\Providers;

use App\Models\RecurringTransaction;
use App\Policies\RecurringTransactionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(RecurringTransaction::class, RecurringTransactionPolicy::class);
    }
}
