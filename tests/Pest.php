<?php

use Tests\TestCase;

// Dzięki temu wszystkie testy w katalogu Feature dziedziczą po Laravel TestCase
uses(TestCase::class)->in('Feature');
