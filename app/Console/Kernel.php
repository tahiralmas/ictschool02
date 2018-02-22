<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    
    '\App\Console\Commands\feeNotification',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $cronschedule = DB::table('cronschedule')->first();
        if(!empty($cronschedule)){
        $schedule->command('feeNotification:notification')
       // ->everyMinute();
         ->monthlyOn($cronschedule->date, $cronschedule->time)->timezone('Asia/Karachi');
         
         }
        //$test = $schedule->exec('touch /tmp/mytest____')->everyMinute();

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
