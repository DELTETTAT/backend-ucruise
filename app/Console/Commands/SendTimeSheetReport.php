<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SubUser;
use App\Models\Holiday;
use App\Models\HrmsCalenderAttendance;
use App\Models\Leave;
use App\Models\EmployeeAttendance;
use App\Exports\TimesheetExport;
use App\Mail\timesheetReport;
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Http\Controllers\Api\Timesheet\TimesheetController;
use Illuminate\Http\Request;

class SendTimeSheetReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:send-timesheet-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send timesheet report on last day of month at 10 PM';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $request = new Request([
            'download' => "0",
            'user_id' => null,
            'month' => null,
        ]);
        $controller = new TimesheetController();
        $controller->timeSheetReport($request); // Call your controller function

        $this->info('Timesheet report sent successfully.');
    }
}
