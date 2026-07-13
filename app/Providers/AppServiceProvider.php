<?php

namespace App\Providers;

use App\Models\RecurringTransaction;
use App\Policies\RecurringTransactionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS when running on live cloud servers like Railway
        if ($this->app->environment('production') || env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }

        Gate::policy(RecurringTransaction::class, RecurringTransactionPolicy::class);
    }
}
