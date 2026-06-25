<?php

namespace App\Providers;

use App\Services\ProxyClient;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProxyClient::class, fn() => new ProxyClient(
            rtrim(config('variables.PROXY_URL', ''), '/'),
            config('variables.PROXY_SECRET', ''),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
    }
}
