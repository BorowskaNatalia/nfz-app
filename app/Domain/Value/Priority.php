<?php

namespace App\Domain\Value;

enum Priority: string
{
    case STABLE = 'STABLE';
    case URGENT = 'URGENT';
}
