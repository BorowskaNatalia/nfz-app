<?php

it('adds X-Request-Id if not provided', function () {
    $res = $this->get('/health');
    $res->assertOk()->assertHeader('X-Request-Id');
});

it('keeps provided X-Request-Id', function () {
    $id = 'test-123';
    $res = $this->get('/health', ['X-Request-Id' => $id]);
    $res->assertOk()->assertHeader('X-Request-Id', $id);
});
