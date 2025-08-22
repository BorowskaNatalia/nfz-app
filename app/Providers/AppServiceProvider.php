<?php

namespace App\Providers;

use App\Contracts\ItlClient;
use App\Infrastructure\Cache\CachedItlClient;
use App\Infrastructure\Fake\FakeItlClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // wewnętrzny klient (dziś fake, jutro http)
        $this->app->bind(FakeItlClient::class, fn () => new FakeItlClient);

        // kontrakt ItlClient dostaje wersję z cache
        $this->app->bind(ItlClient::class, function ($app) {
            $inner = $app->make(FakeItlClient::class);

            return new CachedItlClient($inner);
        });
    }

    public function boot(): void {}
}
