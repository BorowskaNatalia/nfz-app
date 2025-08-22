<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

Route::get('/health', fn () => response('ok', 200));
use Illuminate\Support\Facades\Log;

Route::get('/demo-log', function () {
    Log::info('Manual demo log', [
        'foo' => 'bar',
        // dowolny Twój kontekst – request_id dorzuca middleware
    ]);

    // inne poziomy:
    // Log::warning('NFZ API slow', ['duration_ms' => 1234]);
    // Log::error('NFZ API failed', ['status' => 500]);

    return 'logged';
});
