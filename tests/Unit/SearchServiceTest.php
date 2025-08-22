<?php

use app\Application\Search\SearchService;
use app\Domain\Value\Priority;
use app\Domain\Value\SearchParams;
use app\Infrastructure\Fake\FakeItlClient;
use Illuminate\Support\Carbon;

it('sorts results by the earliest appointment date', function () {
    Carbon::setTestNow('2025-08-22'); // stałe "dzisiaj" dla powtarzalności

    $service = new SearchService(new FakeItlClient);
    $params = new SearchParams('kardiolog', '07', Priority::STABLE);

    $res = $service->search($params, 'fastest');

    expect($res)->toHaveCount(3);
    expect($res[0]->appointment->firstAvailableDate->format('Y-m-d'))->toBe('2025-09-02');
    expect($res[1]->appointment->firstAvailableDate->format('Y-m-d'))->toBe('2025-09-05');
    expect($res[2]->appointment->firstAvailableDate->format('Y-m-d'))->toBe('2025-09-10');
});

it('filters by forChildren and maxDays', function () {
    Carbon::setTestNow('2025-08-22');

    $service = new SearchService(new FakeItlClient);
    // maxDays=15 → do 2025-09-06; z fake danych dla dzieci zostaje tylko "Przychodnia Alfa" (2025-09-05)
    $params = new SearchParams('kardiolog', '07', Priority::STABLE, forChildren: true, maxDays: 15);

    $res = $service->search($params, 'fastest');

    expect($res)->toHaveCount(1);
    expect($res[0]->provider->name)->toBe('Przychodnia Alfa');
});