<?php

use App\Console\Commands\ApplyLateFeesCommand;
use App\Console\Commands\GenerateInvoicesCommand;
use App\Console\Commands\MarkOverdueInvoicesCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Recurring monthly billing (idempotent): generate + issue every 1st.
Schedule::command(GenerateInvoicesCommand::class)
    ->monthlyOn(1, '00:30')
    ->withoutOverlapping()
    ->onOneServer();

// Daily bookkeeping: mark overdue first, then recalculate late fees.
Schedule::command(MarkOverdueInvoicesCommand::class)
    ->dailyAt('00:45')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command(ApplyLateFeesCommand::class)
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->onOneServer();
