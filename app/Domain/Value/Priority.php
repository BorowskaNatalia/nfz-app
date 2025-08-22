<?php

namespace app\Domain\Value;

enum Priority: string
{
    case STABLE = 'STABLE';
    case URGENT = 'URGENT';
}
