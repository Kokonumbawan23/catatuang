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
        if ($this->app->environment('local')) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    public function boot(): void
    {
        Gate::policy(RecurringTransaction::class, RecurringTransactionPolicy::class);
    }
}
