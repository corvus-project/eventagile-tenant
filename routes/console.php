<?php

use App\Console\Commands\AccountSetup;
use App\Console\Commands\DemoData;
use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command(DemoData::class)->dailyAt('04:00');

Schedule::command(AccountSetup::class)->everyFiveMinutes();
