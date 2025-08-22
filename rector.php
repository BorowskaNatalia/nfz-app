<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/app',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ]);

    // najnowsze reguły PHP 8.3
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_83,
    ]);

    // przykładowe reguły jakości
    $rectorConfig->rules([
        ReadOnlyPropertyRector::class,
        InlineConstructorDefaultToPropertyRector::class,
    ]);
};