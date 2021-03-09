<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Events\MinitlyStocksCheck;
use App\Events\DailyStocksCheck;
use App\Events\DailyDBStoreCheck;
use \App\InvokeUpdateStocksInfo;
use App\Events\DailyCheckAllMeigara;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            event(new MinitlyStocksCheck());
        })->everyMinute()->between('9:01', '15:30');

        $schedule->call(function () {
            event(new DailyStocksCheck());
        })->weekdays()->at('16:00');

        $schedule->call(function () {
            event(new DailyDBStoreCheck());
        })->dailyAt('17:00');

        $schedule->call(function () {
            event(new DailyCheckAllMeigara());
        })->dailyAt('17:10');

        //$schedule->call(new InvokeUpdateStocksInfo)->everyMinute();
        // $schedule->command('inspire')->hourly();
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
