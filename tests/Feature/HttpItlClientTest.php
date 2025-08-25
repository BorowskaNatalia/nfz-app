<?php

use App\Domain\Value\Priority;
use App\Domain\Value\SearchParams;
use App\Infrastructure\Http\HttpItlClient;
use Illuminate\Support\Facades\Http;

it('maps NFZ /queues into our DTOs', function () {
    Http::fake([
        'https://api.nfz.gov.pl/app-itl-api/queues*' => Http::response([
            'meta' => ['@context' => '/schema/queue', 'count' => 1, 'page' => 1, 'limit' => 10],
            'links' => [],
            'data' => [[
                'id' => 'queue-abc',
                'attributes' => [
                    'provider' => 'Szpital Demo',
                    'provider-code' => 'UMW-999',
                    'address' => 'Warszawa, ul. Przykładowa 1',
                    'phone' => '+48 111 222 333',
                    'benefits-for-children' => 'Y',
                    'latitude' => 52.25,
                    'longitude' => 21.0,
                    'statistics' => [
                        'provider-data' => ['awaiting' => 42],
                    ],
                    'dates' => [
                        'date' => '2025-09-03',
                        'date-situation-as-at' => '2025-08-22T10:00:00Z',
                    ],
                ],
            ]],
        ], 200),
    ]);

    $client = new HttpItlClient;

    $res = $client->search(new SearchParams(
        query: 'kardiolog',
        province: '07',
        priority: Priority::STABLE,
    ));

    expect($res)->toHaveCount(1);
    $r = $res[0];

    expect($r->provider->id)->toBe('UMW-999');
    expect($r->provider->forChildren)->toBeTrue();
    expect($r->appointment->queueSize)->toBe(42);
    expect($r->appointment->firstAvailableDate->format('Y-m-d'))->toBe('2025-09-03');
});