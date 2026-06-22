<?php

namespace App\Enums;

enum PlanInterval: string
{
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';
    
}
