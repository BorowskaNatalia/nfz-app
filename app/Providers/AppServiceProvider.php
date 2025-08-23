<?php

namespace App\Providers;

use App\Contracts\ItlClient;
use App\Infrastructure\Cache\CachedItlClient;
use App\Infrastructure\Fake\FakeItlClient;
use App\Infrastructure\Http\HttpItlClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ItlClient::class, function () {
            $driver = config('itl.driver', 'fake');

            $inner = match ($driver) {
                'http' => new HttpItlClient,
                default => new FakeItlClient,
            };

            return new CachedItlClient($inner);
        });
    }

    public function boot(): void {}
}
