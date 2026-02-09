<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        // Delete old ads older than 3 weeks - runs daily at midnight
        $schedule->command('ads:delete-old 21')
                ->daily()
                ->at('00:00')
                ->withoutOverlapping()
                ->runInBackground();

        // Moderate harmful content - runs every 6 hours
        $schedule->command('ads:moderate-harmful --delete')
                ->everySixHours()
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
