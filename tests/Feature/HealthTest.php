<?php

use Tests\TestCase;
it('returns ok on /health', function () {
    $this->get('/health')->assertOk()->assertSee('ok');
});