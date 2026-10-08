<?php

use App\Console\Commands\ApplyLateFeesCommand;
use App\Console\Commands\CheckMaintenanceSlaCommand;
use App\Console\Commands\GenerateInvoicesCommand;
use App\Console\Commands\MarkOverdueInvoicesCommand;
use App\Console\Commands\PublishDueAnnouncementsCommand;
use App\Console\Commands\SendDueRemindersCommand;
use App\Console\Commands\SendLeaseExpiryRemindersCommand;
use App\Console\Commands\SendOverdueRemindersCommand;
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

// Maintenance SLA breach check (hourly; notifies staff once per ticket).
Schedule::command(CheckMaintenanceSlaCommand::class)
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

// Automated reminders (idempotent per offset via reminder_logs).
Schedule::command(SendDueRemindersCommand::class)
    ->dailyAt('07:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command(SendOverdueRemindersCommand::class)
    ->dailyAt('07:15')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command(SendLeaseExpiryRemindersCommand::class)
    ->dailyAt('07:30')
    ->withoutOverlapping()
    ->onOneServer();

// Publish any announcements whose scheduled time has arrived.
Schedule::command(PublishDueAnnouncementsCommand::class)
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();
