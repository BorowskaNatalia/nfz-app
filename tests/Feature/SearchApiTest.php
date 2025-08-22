<?php

use Illuminate\Support\Carbon;

it('returns fastest-sorted results with correct shape', function () {
    Carbon::setTestNow('2025-08-22');

    $res = $this->getJson('/api/search?q=kardiolog&province=07&priority=stable&sort=fastest');

    $res->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'provider' => ['id', 'name', 'address', 'phone', 'website', 'forChildren', 'location' => ['lat', 'lng']],
                    'appointment' => ['firstAvailableDate', 'queueSize', 'priority', 'lastUpdated'],
                    'distanceKm',
                ],
            ],
        ]);

    $dates = array_map(fn ($i) => $i['appointment']['firstAvailableDate'], $res->json('data'));
    expect($dates)->toBe(['2025-09-02', '2025-09-05', '2025-09-10']);
});

it('applies kids and maxDays filters', function () {
    Carbon::setTestNow('2025-08-22');

    $res = $this->getJson('/api/search?q=kardiolog&province=07&priority=stable&kids=1&maxDays=15');

    $res->assertOk()->assertJsonCount(1, 'data');
    expect($res->json('data.0.provider.name'))->toBe('Przychodnia Alfa');
});
