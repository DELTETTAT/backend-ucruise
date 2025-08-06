<?php

namespace App\Http\Controllers;

use App\Models\ScheduleCarer;
use App\Models\ScheduleCarerRelocation;
use Illuminate\Http\Request;

class ShiftChangeRequestController extends Controller
{
    // approve temporary shift change 
    public function approveShiftChangeRequest($id)
    {
        $leavemanagementController = new LeavemanagementController();
        $shiftChange = ScheduleCarerRelocation::find($id);
        $shiftTypesToUpdate = [];

        if ($shiftChange->shift_type == 1) {
            $shiftTypesToUpdate = ['pick', 'drop'];
        } elseif ($shiftChange->shift_type == 2) {
            $shiftTypesToUpdate = ['pick'];
        } elseif ($shiftChange->shift_type == 3) {
            $shiftTypesToUpdate = ['drop'];
        }
        $dates = $shiftChange->date;
        $schedules = $leavemanagementController->getWeeklyScheduleInfo([$shiftChange->staff_id], [$dates], 2, 'all');
        //dd($schedules);
        foreach ($schedules as $schedule) {

            // Update temporary info for appropriate shift types
            if (in_array($schedule['type'], $shiftTypesToUpdate)) {

                $this->updateScheduleTempInfo($schedule, $shiftChange, $shiftTypesToUpdate);
            }
        }

        $shiftChange->status = 1;
        $shiftChange->save();

        return redirect()->back()->with('success', 'Approved shift change request status');
    }

       //reject temporary shift change 
    public function rejectShiftChangeRequest($id)
    {
        $shiftChange = ScheduleCarerRelocation::find($id);
        $shiftChange->status = 2;
        $shiftChange->save();
        return redirect()->back()->with('success', 'Rejected shift change request status');
    }
    
    public function updateScheduleTempInfo($schedule, $shiftChange, $shiftTypesToUpdate)
    {

        $schedule_carers = ScheduleCarer::where('schedule_id', $schedule['id'])
            ->where('carer_id', $shiftChange->staff_id)->whereIn('shift_type', $shiftTypesToUpdate)
            ->get();

        foreach ($schedule_carers as $schedule_carer) {
            $schedule_carer->temp_date = $shiftChange->date;
            $schedule_carer->temp_lat = $shiftChange->temp_latitude;
            $schedule_carer->temp_long = $shiftChange->temp_longitude;
            $schedule_carer->temp_address = $shiftChange->temp_address;
            $schedule_carer->save();
        }
    }
}
