<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
     protected $commands = [
        Commands\CheckDoc::class,
        Commands\RunMigration::class,
        Commands\SendReminderEmails::class,
        Commands\ScheduleAutomation::class,
        Commands\SendTimeSheetReport::class,
        Commands\SendBirthdayEmail::class,
        Commands\TrackingEmployeeAttendance::class,
        Commands\RunSalaryImport::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->command('demo:cron');
         $schedule->command('command:reminder');
         $schedule->command('schedule:automation');
        // $schedule->command('report:send-timesheet-report')->everyMinute();
        $schedule->command('report:send-timesheet-report')
            ->dailyAt('23:00')
            ->when(function () {
               return Carbon::now()->isSameDay(Carbon::now()->endOfMonth());
        });
        $schedule->command('command:birthdayemail')->dailyAt('01:00');
        $schedule->command('tracking:employee-attendance')->dailyAt('11:50');
        $schedule->command('salary:import')->hourly();
       // $schedule->command('queue:work --timeout=1800 --tries=3 --stop-when-empty');


    }
     /*
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }


}
