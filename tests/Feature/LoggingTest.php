<?php

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Log;

it('writes an info log with request_id', function () {
    $captured = null;

    Log::listen(function (MessageLogged $event) use (&$captured) {
        // bierzemy tylko nasz wpis, na wypadek innych logów w tle
        if (str_contains($event->message, 'Manual demo log')) {
            $captured = [
                'level' => $event->level,
                'message' => $event->message,
                'context' => $event->context,
            ];
        }
    });

    $this->get('/demo-log')->assertOk();

    expect($captured)->not->toBeNull()
        ->and($captured['level'])->toBe('info')
        ->and($captured['message'])->toContain('Manual demo log')
        ->and($captured['context']['foo'] ?? null)->toBe('bar')
        ->and($captured['context']['request_id'] ?? null)->not->toBeNull();
});
