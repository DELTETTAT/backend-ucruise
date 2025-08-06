<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\Hrms\Employee\EmployeeAttendanceController;

class TrackingEmployeeAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracking:employee-attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tracking App Employee Attendance Command';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $controller = new EmployeeAttendanceController();
        $controller->trackingEmployeeAttendance();
       // return Command::SUCCESS;
       $this->info('attendance stored successfully.');
    }
}
