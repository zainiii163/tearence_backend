<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\SendJobAlerts;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Disable expired promo/listing windows (do not hard-delete at 21 days)
        $schedule->command('ads:disable-expired')
                ->daily()
                ->at('00:15')
                ->withoutOverlapping()
                ->runInBackground();

        // Hard-delete only very old inactive ads (90+ days) — after disable window
        $schedule->command('ads:delete-old 90')
                ->daily()
                ->at('01:00')
                ->withoutOverlapping()
                ->runInBackground();

        // Moderate harmful content - runs every 6 hours
        $schedule->command('ads:moderate-harmful --delete')
                ->everySixHours()
                ->withoutOverlapping()
                ->runInBackground();

        // Send job alerts - runs daily at 9 AM
        $schedule->command('jobs:send-alerts')
                ->daily()
                ->at('09:00')
                ->withoutOverlapping()
                ->runInBackground();

        // Remind users about expiring / ending promotions — daily 08:00
        $schedule->command('ads:send-expiry-reminders')
                ->daily()
                ->at('08:00')
                ->withoutOverlapping()
                ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
