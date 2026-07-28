<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Workbench\App\Models\User;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // The testbench skeleton points auth at App\Models\User, which does not
        // exist here.
        config(['auth.providers.users.model' => User::class]);
    }
}
