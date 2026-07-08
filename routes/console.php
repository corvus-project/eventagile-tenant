<?php

use App\Console\Commands\AccountSetup;
use App\Console\Commands\BackupTenantDatabases;
use App\Console\Commands\DemoData;
use Illuminate\Support\Facades\Schedule;


Schedule::command(DemoData::class)->dailyAt('04:00');
Schedule::command(BackupTenantDatabases::class)->dailyAt('04:00')->withoutOverlapping();
Schedule::command(AccountSetup::class)->everyFiveMinutes();
