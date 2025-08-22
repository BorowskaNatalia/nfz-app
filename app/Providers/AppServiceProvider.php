<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Contracts\ItlClient::class, \App\Infrastructure\Fake\FakeItlClient::class);

        // zewnętrzny kontrakt używa wersji z cache
        $this->app->bind(ItlClient::class, function ($app) {
            $inner = $app->make(FakeItlClient::class);
            return new CachedItlClient($inner);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}