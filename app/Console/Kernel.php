<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('erp:mark-missed-faculty-logs')->hourly();
        $schedule->command('erp:mark-student-absents')->everyThirtyMinutes();
        $schedule->command('erp:send-fee-reminders')->dailyAt('09:00');
        $schedule->command('erp:send-homework-incomplete-alerts')->dailyAt('18:00');
        $schedule->command('erp:send-maintenance-alerts')->dailyAt('10:00');
        $schedule->command('erp:send-low-inventory-alerts')->dailyAt('10:30');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
