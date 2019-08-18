<?php

namespace App\Console;

use App\Console\Commands\CronJob;
use App\Console\Commands\update;
use App\Console\Commands\UpdateCompletedAt;
use App\Console\Commands\updatedistrict;
use App\Console\Commands\UpdateOweBooking;
use App\Console\Commands\UpdateUrlImage;
use App\Console\Commands\HelloCrontab;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        updatedistrict::class,
        \App\Console\Commands\CronJob::class,
        update::class,
        UpdateUrlImage::class,
        UpdateCompletedAt::class,
        UpdateOweBooking::class,
        HelloCrontab::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('revenue:update')
            ->everyTenMinutes();
        // $schedule->command('say:hello')
        //     ->everyMinute();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
