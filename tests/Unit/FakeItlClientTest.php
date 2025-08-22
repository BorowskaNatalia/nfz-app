<?php

use app\Domain\Value\Priority;
use app\Domain\Value\SearchParams;
use app\Infrastructure\Fake\FakeItlClient;

it('returns a list of SearchResultDTO from FakeItlClient', function () {
    $client = new FakeItlClient;

    $params = new SearchParams(
        query: 'kardiolog',
        province: '07',
        priority: Priority::STABLE,
        forChildren: null,
        maxDays: null,
    );

    $results = $client->search($params);

    expect($results)->toBeArray()->toHaveCount(3);

    // sprawdźmy przykładowe pola pierwszego rekordu
    $first = $results[0];

    expect($first->provider->name)->toBeString()
        ->and($first->appointment->queueSize)->toBeInt()
        ->and($first->appointment->priority)->toBe(Priority::STABLE);
});
