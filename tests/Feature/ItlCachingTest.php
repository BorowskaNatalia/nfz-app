<?php

use App\Contracts\ItlClient;
use App\Domain\DTO\AppointmentDTO;
use App\Domain\DTO\ProviderDTO;
use App\Domain\DTO\SearchResultDTO;
use App\Domain\Value\SearchParams;
use App\Infrastructure\Cache\CachedItlClient;
use Illuminate\Support\Carbon;

it('uses cache for identical requests', function () {
    Carbon::setTestNow('2025-08-22');

    config()->set('cache.default', 'array');
    config()->set('itl.cache_ttl', 900);

    $counter = new class implements ItlClient
    {
        public int $calls = 0;

        public function search(SearchParams $params): array
        {
            $this->calls++;

            $p = new ProviderDTO('umw-x', 'Demo', 'Adres 1', null, null, null, null, true);
            $a = new \DateTimeImmutable('2025-09-05');
            $lu = new \DateTimeImmutable('2025-08-22T10:00:00Z');

            return [new SearchResultDTO(
                $p,
                new AppointmentDTO($a, 1, $params->priority, $lu),
            )];
        }
    };

    app()->bind(ItlClient::class, fn () => new CachedItlClient($counter));

    $res1 = $this->getJson('/api/search?q=kardiolog&province=07&priority=stable');
    $res1->assertOk()->assertJsonCount(1, 'data');
    expect($counter->calls)->toBe(1);

    $res2 = $this->getJson('/api/search?q=kardiolog&province=07&priority=stable');
    $res2->assertOk()->assertJsonCount(1, 'data');
    expect($counter->calls)->toBe(1); // nie wzrosło → cache działa
});
