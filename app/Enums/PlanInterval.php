<?php

namespace App\Enums;

enum PlanInterval: string
{
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';

    public function label(): string
    {
        return match ($this) {
            self::DAY => 'day',
            self::WEEK => 'week',
            self::MONTH => 'month',
            self::YEAR => 'year',
        };
    }
}
