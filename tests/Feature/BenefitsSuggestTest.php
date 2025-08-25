<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

it('returns suggestions from NFZ benefits and caches result', function () {
    config()->set('cache.default', 'array');
    Cache::flush();

    // 1) fake odpowiedź NFZ /benefits
    $payload = [
        'data' => [
            ['attributes' => ['benefit' => 'poradnia kardiologiczna']],
            ['attributes' => ['benefit' => 'poradnia kardiologiczna dla dzieci']],
            ['attributes' => ['benefit' => 'poradnia endokrynologiczna']],
        ],
    ];

    $seq = Http::fakeSequence()
        ->push($payload, 200); // tylko 1 raz wolno!

    // 2) pierwsze wywołanie
    $res1 = $this->getJson('/api/benefits?q=poradnia&limit=3')
        ->assertOk()
        ->assertJsonCount(3, 'data');

    // 3) drugie identyczne – powinno pójść z cache (brak kolejnego HTTP)
    $res2 = $this->getJson('/api/benefits?q=poradnia&limit=3')
        ->assertOk()
        ->assertJsonCount(3, 'data');

    expect($res1->json())->toEqual($res2->json());
    expect($seq->isEmpty())->toBeTrue(); // sekwencja HTTP wyczerpana = brak 2. żądania
});