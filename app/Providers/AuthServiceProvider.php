<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Season;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('rapor.cetak', fn ($user) => true);
        Gate::define('ledger.view', fn ($user) => true);
        Gate::define('cetak-print-ledger', fn ($user) => true);

    Gate::define('input-nilai', function ($user) {
        return Season::currentOpen() !== null;
    });
    }
}