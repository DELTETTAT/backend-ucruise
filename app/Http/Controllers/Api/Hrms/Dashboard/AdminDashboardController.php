<?php

namespace App\Http\Controllers\Api\Hrms\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeAttendance;
use App\Models\SubUser;
use App\Models\HrmsTimeAndShift;
use App\Models\EmployeeTeamManager;
use App\Models\TeamManager;
use App\Models\HrmsCalenderAttendance;
use App\Models\Leave;
use App\Models\Holiday;
use App\Models\Reschedule;
use App\Models\ScheduleCarerRelocation;
use App\Models\JobRequirement;
use App\Models\NewApplicant;
use App\Models\Designation;
use App\Models\HrmsAnnouncement;
use App\Models\HrmsReminder;
use App\Models\Resignation;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class AdminDashboardController extends Controller
{
    /**
     * @OA\Get(
     * path="/uc/api/admin_dashboard/attendanceStatus",
     * operationId="attendanceStatus",
     * tags={"Admin Dashboard"},
     * summary="Get all employee attendance details Request",
     *   security={ {"Bearer": {} }},
     * description="Get all employee attendance details Request",
     *      @OA\Response(
     *          response=201,
     *          description=" all employee attendance Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description=" all employee attendance details Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */


    public function attendanceStatus()
    {
        try {
            $taday = today()->format('Y-m-d');
            $employees = SubUser::get();

            $shift = HrmsTimeAndShift::first();
            //$shiftLoginTime = $shift['shift_time']['start'];

            $total_absent = 0;
            $total_late = 0;
            $total_on_time = 0;
            foreach ($employees as $key => $employee) {
                $attendance = EmployeeAttendance::where('user_id', $employee->id)->whereDate('date', $taday)->first();
                $shiftLoginTime = $employee->employee_shift ?? $shift['shift_time']['start'];

                if (!$attendance) {
                    $total_absent += 1;
                } elseif ($attendance->login_time) {
                    $employee_login_time = Carbon::parse($attendance->login_time);
                    $shift_login_time = Carbon::parse($shiftLoginTime);

                    $diffInMinutes = $employee_login_time->diffInMinutes($shift_login_time, true);

                    if ($diffInMinutes > 30) {
                        $total_late += 1;
                    } elseif ($diffInMinutes <= 30) {
                        $total_on_time += 1;
                    }
                }
            }

            $data['total_employee'] = $employees->count();
            $data['total_absent'] = $total_absent;
            $data['total_late'] = $total_late;
            $data['total_on_time'] = $total_on_time;

            return $this->successResponse(
                $data,
                "Geted Attendance Data"
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }





    /**
     * @OA\Get(
     * path="/uc/api/admin_dashboard/team_leaves",
     * operationId="leaveStatus",
     * tags={"Admin Dashboard"},
     * summary="Get team accourding employee leave details Request",
     *   security={ {"Bearer": {} }},
     * description="Get team accourding employee leave details Request",
     *      @OA\Response(
     *          response=201,
     *          description=" team accourding employee leave Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description=" team accourding employee leave details Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */

    public function teamLeaves()
    {
        try {
            $employee = auth('sanctum')->user();

            $team_manager = EmployeeTeamManager::where('employee_id', $employee->id)->first();

            $employees_ids = [];
            if ($team_manager) {

                $getTeamList = TeamManager::with(['employees', 'teams.teamLeader', 'teams.teamMembers.user'])->find($team_manager->team_manager_id);

                foreach ($getTeamList->employees as $key => $employee) {
                    $employees_ids[] = $employee->id;
                }
                foreach ($getTeamList->teams as $key => $team) {
                    $employees_ids[] = (int) $team->team_leader;

                    foreach ($team->teamMembers as $key => $memeber) {
                        $employees_ids[] =  $memeber->user->id;
                    }
                }
            } else {
                $employees_ids[] = $employee->id;
            }

            // $leaves = Leave::with('user')->whereIn('staff_id', $employees_ids)->get();
            $today = today()->format('Y-m-d');
            $history_leaves = SubUser::with(['leave' => function ($query) use ($today) {
                $query->where('end_date', '<', $today);
            }])->whereIn('id', $employees_ids)->get();

            $upcoming_leaves  = SubUser::with(['leave' => function ($query) use ($today) {
                $query->where('end_date', '>', $today);
            }])->whereIn('id', $employees_ids)->get();

            $data['history_leaves'] = $history_leaves;
            $data['upcoming_leaves'] = $upcoming_leaves;


            return $this->successResponse(
                $data,
                "Team Employee Leave List"
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }



    /**
     * @OA\Get(
     * path="/uc/api/admin_dashboard/event_list",
     * operationId="eventStatus",
     * tags={"Admin Dashboard"},
     * summary="Get Events details Request",
     *   security={ {"Bearer": {} }},
     * description="Get Events details Request",
     *      @OA\Response(
     *          response=201,
     *          description=" Events Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description=" Events details Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */

    // public function eventList(){
    //     try {
    //           $today = today()->format('Y-m-d');

    //           $endDate = today()->addDay(10)->format('Y-m-d');
    //           $employees = SubUser::whereBetween('dob',[$today,$endDate])->get();
    //           $data['upcomming_birthday'] = $employees;
    //           $data['events'] = "";

    //         return $this->successResponse(
    //             $data,
    //             "Event List"
    //         );
    //     } catch (\Throwable $th) {
    //         return $this->errorResponse($th->getMessage());
    //     }
    // }
    // public function eventList()
    // {
    //     try {
    //         $today = now();
    //         $currentMonth = $today->month;
    //         $nextMonth = $today->copy()->addMonth()->month;

    //         // Get all sub-users
    //         $subUsers = SubUser::where('status', 1)->get();

    //         // Filter birthdays
    //         $upcomingBirthdays = $subUsers->filter(function ($user) use ($today, $currentMonth, $nextMonth) {
    //             if (!$user->dob) return false;

    //             $dob = Carbon::parse($user->dob);
    //             $birthdayThisYear = $dob->copy()->year($today->year);

    //             if ($birthdayThisYear->isBefore($today)) {
    //                 $birthdayThisYear->addYear();
    //             }

    //             $month = $birthdayThisYear->month;
    //             return in_array($month, [$currentMonth, $nextMonth]);
    //         })->map(function ($user) {
    //             return [
    //                 'name' => $user->first_name,
    //                 'dob' => Carbon::parse($user->dob)->format('F d'),
    //                 'role' => $user->employement_type,
    //             ];
    //         })->values();

    //         // Get holidays
    //         $holidays = Holiday::whereMonth('date', $currentMonth)
    //             ->orWhereMonth('date', $nextMonth)
    //             ->get()
    //             ->map(function ($holiday) {
    //                 return [
    //                     'type' => 'Holiday',
    //                     'title' => $holiday->name,
    //                     'description' => $holiday->description,
    //                     'date' => Carbon::parse($holiday->date)->format('F d'),
    //                 ];
    //             });

    //         // Get announcements
    //         $announcements = HrmsAnnouncement::whereMonth('date', $currentMonth)
    //             ->orWhereMonth('date', $nextMonth)
    //             ->get()
    //             ->map(function ($announcement) {
    //                 return [
    //                     'type' => 'Announcement',
    //                     'title' => $announcement->title,
    //                     'description' => $announcement->description,
    //                     'date' => Carbon::parse($announcement->date)->format('F d'),
    //                 ];
    //             });

    //         // ✅ Get reminders
    //         $reminders = HrmsReminder::whereMonth('date', $currentMonth)
    //             ->orWhereMonth('date', $nextMonth)
    //             ->get()
    //             ->map(function ($reminder) {
    //                 return [
    //                     'type' => 'Reminder',
    //                     'title' => $reminder->title,
    //                     'description' => $reminder->description,
    //                     'date' => Carbon::parse($reminder->date)->format('F d'),
    //                 ];
    //             });

    //         // Merge all events
    //        $events = $holidays
    // ->merge($announcements)
    // ->merge($reminders)
    // ->sortBy(function ($event) {
    //     return Carbon::parse($event['date']); // Sort by parsed date
    // })
    // ->values();

    //         $data['upcoming_birthday'] = $upcomingBirthdays;
    //         $data['events'] = $events;

    //         return $this->successResponse($data, "Event List");
    //     } catch (\Throwable $th) {
    //         return $this->errorResponse($th->getMessage());
    //     }
    // }
   public function eventList()
{
    try {
        $today = now();
        $currentMonth = $today->month;
        $nextMonth = $today->copy()->addMonth()->month;

        // // Get all sub-users
        // $subUsers = SubUser::where('status', 1)->get();

        // // Filter and map upcoming birthdays
        // $upcomingBirthdays = $subUsers->filter(function ($user) {
        //     return !empty($user->dob); // Filter out users without DOB
        // })->map(function ($user) use ($today) {
        //     $dob = Carbon::parse($user->dob);
        //     $nextBirthday = $dob->copy()->year($today->year);
            
        //     // Adjust to next year if birthday already passed
        //     if ($nextBirthday->isBefore($today)) {
        //         $nextBirthday->addYear();
        //     }

        //     return [
        //         'user' => $user,
        //         'next_birthday' => $nextBirthday,
        //         'days_until' => $today->diffInDays($nextBirthday),
        //     ];
        // })->filter(function ($entry) use ($currentMonth, $nextMonth) {
        //     // Filter for current/next month birthdays
        //     return in_array($entry['next_birthday']->month, [$currentMonth, $nextMonth]);
        // })->sortBy('days_until')->map(function ($entry) {
        //     $user = $entry['user'];
        //     return [
        //         'name' => $user->first_name,
        //         'dob' => Carbon::parse($user->dob)->format('F d'),
        //         'role' => $user->employement_type,
        //     ];
        // })->values();
        // Filter and map upcoming birthdays

        $subUsers = SubUser::where('status', 1)
            ->whereNotNull('dob')
            ->get();

        $upcomingBirthdays = $subUsers->map(function ($user) use ($today) {
        $dob = Carbon::parse($user->dob);
        $birthdayThisYear = Carbon::createFromDate($today->year, $dob->month, $dob->day);

        return (object)[
            'user' => $user,
            'birthday' => $birthdayThisYear,
        ];
        })->filter(function ($item) use ($today, $currentMonth, $nextMonth) {
            return $item->birthday->greaterThanOrEqualTo($today)
                && in_array($item->birthday->month, [$currentMonth, $nextMonth]);
        })->sortBy(function ($item) use ($today) {
            return $today->diffInDays($item->birthday);
        })->map(function ($item) {
            return [
                'name' => $item->user->first_name,
                'dob' => $item->birthday->format('F d'),
                'role' => $item->user->employement_type,
            ];
        })->values();

        // Get holidays
        $holidays = collect(
            Holiday::whereMonth('date', $currentMonth)
                ->orWhereMonth('date', $nextMonth)
                ->get()
                ->map(function ($holiday) {
                    return [
                        'type' => 'Holiday',
                        'title' => $holiday->name,
                        'description' => $holiday->description,
                        'date' => Carbon::parse($holiday->date),
                        'formatted_date' => Carbon::parse($holiday->date)->format('F d'),
                    ];
                })
        );

        // Get announcements
        $announcements = collect(
            HrmsAnnouncement::whereMonth('date', $currentMonth)
                ->orWhereMonth('date', $nextMonth)
                ->get()
                ->map(function ($announcement) {
                    return [
                        'type' => 'Announcement',
                        'title' => $announcement->title,
                        'description' => $announcement->description,
                        'date' => Carbon::parse($announcement->date),
                        'formatted_date' => Carbon::parse($announcement->date)->format('F d'),
                    ];
                })
        );

        // Get reminders
        $reminders = collect(
            HrmsReminder::whereMonth('date', $currentMonth)
                ->orWhereMonth('date', $nextMonth)
                ->get()
                ->map(function ($reminder) {
                    return [
                        'type' => 'Reminder',
                        'title' => $reminder->title,
                        'description' => $reminder->description,
                        'date' => Carbon::parse($reminder->date),
                        'formatted_date' => Carbon::parse($reminder->date)->format('F d'),
                    ];
                })
        );

        // Merge all events and sort by date
        $events = $holidays
            ->merge($announcements)
            ->merge($reminders)
            ->sortBy('date')
            ->map(function ($event) {
                $event['date'] = $event['formatted_date'];
                unset($event['formatted_date']);
                return $event;
            })
            ->values();

        $data['upcoming_birthday'] = $upcomingBirthdays;
        $data['events'] = $events;

        return $this->successResponse($data, "Event List");
    } catch (\Throwable $th) {
        return $this->errorResponse($th->getMessage());
    }
}




    /**
     * @OA\Get(
     * path="/uc/api/admin_dashboard/all_attendance",
     * operationId="all attendance",
     * tags={"Admin Dashboard"},
     * summary="Get All Employee Attendance Counting Request",
     *   security={ {"Bearer": {} }},
     * description="Get All Employee Attendance Counting Request",
     *      @OA\Response(
     *          response=201,
     *          description=" All Employee Attendance Counting Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description=" All Employee Attendance Counting Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */



    public function allAttendance()
    {
        try {
            $today = now()->format('Y-m-d');

            //Get all resigned employee IDs from sub_users (status=3), no filter on notice period here
            $all_resigned_ids = SubUser::where('user_type', "0")
                ->where('status', 3)
                ->pluck('id');
            $total_resign = SubUser::where('user_type', "0")
                ->where('status', 3)
                ->count();

            //Get resigned employees still within 45-day notice period
            $notice_cutoff_date = Carbon::today()->subDays(45);
            $resigned_in_notice_ids = Resignation::whereDate('date', '>=', $notice_cutoff_date)
                ->whereDate('date', '<=', $today)
                ->pluck('user_id');

            //Get active employee IDs
            $active_employee_ids = SubUser::where('user_type', "0")
                ->where('status', 1)
                ->pluck('id');

            //Valid employees for attendance: active + resigned in notice
            $valid_employee_ids = $active_employee_ids->merge($resigned_in_notice_ids)->unique();

            //Total employees count = active + all resigned (regardless of notice)
            $total_employee = $active_employee_ids->count() + $all_resigned_ids->count();

            //Present employees today (only from valid employees)
            $present_employee_ids = EmployeeAttendance::whereDate('date', $today)
                ->whereIn('user_id', $valid_employee_ids)
                ->pluck('user_id');

            $present_employee = $present_employee_ids->count();

            //Half day employees today
            $half_days = HrmsCalenderAttendance::where('date', $today)
                ->where('status', 'halfday')
                ->whereIn('user_id', $valid_employee_ids)
                ->count();

            //Employees on leave today (only valid employees)
            $leave_employee_ids = Leave::whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->where('status', 1)
                ->whereIn('staff_id', $valid_employee_ids)
                ->pluck('staff_id');

            $today_leaves = $leave_employee_ids->count();

            //Absent employees = valid employees NOT present and NOT on leave
            $excluded_ids = $present_employee_ids->merge($leave_employee_ids);

            $absent_employees = SubUser::select('first_name', 'last_name', 'email', 'unique_id')
                ->whereIn('id', $valid_employee_ids)
                ->whereNotIn('id', $excluded_ids)
                ->get();
            foreach ($absent_employees as $key => $employee) {
                $absent_employees[$key]['status'] = "Absent";
            }

            //Present employees list with check-in/out times
            $present_employees = SubUser::select('id', 'first_name', 'last_name', 'email', 'unique_id')
                ->whereIn('id', $present_employee_ids)
                ->get();

            foreach ($present_employees as $key => $employee) {
                $attendance_time = EmployeeAttendance::whereDate('date', $today)->where('user_id', $employee->id)->first();
                $present_employees[$key]['check_in_time'] = $attendance_time?->login_time;
                $present_employees[$key]['check_out_time'] = $attendance_time?->logout_time;
                $present_employees[$key]['status'] = "Present";
            }

            //Employees on leave full day
            $on_leave_employees = SubUser::select('first_name', 'last_name', 'email', 'unique_id')
                ->whereIn('id', $leave_employee_ids)
                ->get();
            foreach ($on_leave_employees as $key => $employee) {
                $on_leave_employees[$key]['status'] = "On Leave";
            }

            //Half day leave employees (leave type != 1)
            $halfday_employee_ids = Leave::whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->where('status', 1)->where('type', '!=', 1)->whereIn('staff_id', $valid_employee_ids)
                ->pluck('staff_id');

            $half_leave_employees = SubUser::select('first_name', 'last_name', 'email', 'unique_id')
                ->whereIn('id', $halfday_employee_ids)
                ->get();

            foreach ($half_leave_employees as $key => $employee) {
                $attendance_time = EmployeeAttendance::whereDate('date', $today)->where('user_id', $employee->id)->first();
                $on_leave_employees[$key]['check_in_time'] = $attendance_time?->login_time;
                $on_leave_employees[$key]['check_out_time'] = $attendance_time?->logout_time;
                $on_leave_employees[$key]['status'] = "Half Leave";
            }

            //Prepare final data
            $attendance['total_employee'] = $total_employee; // Active + all resigned (full)
            $attendance['present_employee'] = $present_employee;
            $attendance['half_days'] = $half_days;
            $attendance['today_leaves'] = $today_leaves;
            $attendance['absent_employee'] = $absent_employees->count();
            $attendance['auth_name'] = auth('sanctum')->user()->first_name . " " . auth('sanctum')->user()->last_name;
            $attendance['total_resign'] =  $total_resign;
            $attendance['present_employees'] = $present_employees;
            $attendance['absent_employees'] = $absent_employees;
            $attendance['on_leave_employees'] = $on_leave_employees;
            $attendance['half_leave_employees'] = $half_leave_employees;

            return $this->successResponse($attendance, "Today All Employee Attendance Status");
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }


  /**
     * @OA\Get(
     * path="/uc/api/admin_dashboard/idCardReceivedStats",
     * operationId="all idCardReceivedStats",
     * tags={"Admin Dashboard"},
     * summary="Get All Employee idCard Counting Request",
     *   security={ {"Bearer": {} }},
     * description="Get All Employee idCard Counting Request",
     *      @OA\Response(
     *          response=201,
     *          description=" All Employee idCard Counting Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description=" All Employee idCard Counting Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function idCardReceivedStats()
{
    try {

       // $totalActive = SubUser::where('status', 1)->count();
         $totalActive = SubUser::where('status', 1)
                ->where('employement_type', '!=', 'Driver')
                ->count();

        $receivedCount = SubUser::where('status', 1)
            ->whereHas('userInfo', fn ($q) =>
                $q->where('id_card_receive', 'Received')
            )
            ->count();


        $notReceivedCount = $totalActive - $receivedCount;

        $receivedPct     = $totalActive ? round(($receivedCount     / $totalActive) * 100, 2) : 0;
        $notReceivedPct  = $totalActive ? round(($notReceivedCount  / $totalActive) * 100, 2) : 0;
        return $this->successResponse([
            'total_active_employees'      => $totalActive,
            'received_id_card_count'      => $receivedCount,
            'received_id_card_percentage' => $receivedPct,      // e.g. 73.33
            'not_received_id_card_count'  => $notReceivedCount,
            'not_received_id_card_percentage' => $notReceivedPct // e.g. 26.67
        ], 'ID-card distribution stats fetched successfully');

    } catch (\Throwable $th) {
        return $this->errorResponse($th->getMessage(), 500);
    }
}

 /**
 * @OA\Post(
 *     path="/uc/api/admin_dashboard/idCardIndex",
 *     operationId="idCardIndex",
 *     tags={"Admin Dashboard"},
 *     summary="Get All Employee ID Card Index",
 *     description="Get a list of active employees filtered by ID card status (Received / Not Received).",
 *     security={{"Bearer":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"status"},
 *             @OA\Property(
 *                 property="status",
 *                 type="string",
 *                 enum={"Received", "Not Received"},
 *                 example="Received",
 *                 description="Filter by ID card status"
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="All Employee ID Card data fetched successfully",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="All Employee ID Card Index Created",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Unprocessable Entity",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Resource Not Found"
 *     )
 * )
 */


        public function idCardIndex(Request $request)
        {
            try {
                $statusFilter = $request->input('status'); // 'Received' or 'Not Received'

                // Query active employees only
                $query = SubUser::where('status', 1);

                // Apply filters based on status
                if ($statusFilter === 'Received') {
                    $query->whereHas('userInfo', function ($q) {
                        $q->where('id_card_receive', 'Received');
                    });
                } elseif ($statusFilter === 'Not Received') {
                    $query->where(function ($q) {
                        $q->whereDoesntHave('userInfo')
                        ->orWhereHas('userInfo', function ($subQ) {
                            $subQ->where('id_card_receive', '!=', 'Received')
                                ->orWhereNull('id_card_receive');
                        });
                    });
                }

                // Fetch employees with userInfo
                $employees = $query->with('userInfo')->get(['id', 'first_name', 'last_name', 'email', 'unique_id']);

                // Add 'id_card_status' field
                $employees = $employees->map(function ($employee) {
                    $employee->id_card_status = $employee->userInfo && $employee->userInfo->id_card_receive === 'Received'
                        ? 'Received'
                        : 'Not Received';
                    unset($employee->userInfo); // optional
                    return $employee;
                });

                return $this->successResponse([
                    'count' => $employees->count(),
                    'employees' => $employees
                ], 'Filtered ID card employee list fetched successfully');

            } catch (\Throwable $th) {
                return $this->errorResponse($th->getMessage(), 500);
            }
        }




    /**
     * @OA\Get(
     * path="/uc/api/admin_dashboard/all_leave_requests",
     * operationId="all leave request",
     * tags={"Admin Dashboard"},
     * summary="Get All Employee Leave request Request",
     *   security={ {"Bearer": {} }},
     * description="Get All Employee Leave request  Request",
     *      @OA\Response(
     *          response=201,
     *          description=" All Employee Leave request  Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description=" All Employee Leave request  Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */


    public function allLeaveRequests()
    {
        try {
            $today = today()->format('Y-m-d');
            $data = [];

            $leaves = Leave::whereDate('start_date', '>=', $today)
                ->whereDate('end_date', '>=', $today)
                ->where('status', 0)
                ->count();

            $reSchedule = Reschedule::whereDate('date', '>=', $today)->where('status', 0)->count();
            $temLocationRequests = ScheduleCarerRelocation::whereDate('date', '>=', $today)->where('status', 0)->count();
            $resignationRequests = Resignation::whereDate('date', '>=', $today)->where('status', 0)->count();

            $data['leave_requests'] = $leaves;
            $data['reschedule_requests'] = $reSchedule;
            $data['tem_location_requests'] = $temLocationRequests;
            $data['resignation_requests'] = $resignationRequests;

            return $this->successResponse(
                $data,
                "Request Data List"
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

    /**
     * @OA\Get(
     * path="/uc/api/admin_dashboard/job_opening_status",
     * operationId="job opening status",
     * tags={"Admin Dashboard"},
     * summary="Get Jop Openig status Request",
     *   security={ {"Bearer": {} }},
     * description="Get Jop Openig status Request",
     *      @OA\Response(
     *          response=201,
     *          description=" Jop Openig status Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description=" Jop Openig status Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */


    public function jobOpeningStatus()
    {
        try {

            $month = now()->month;
            $jobs = JobRequirement::get();

            $data = [];
            foreach ($jobs as $key => $job) {
                $query = NewApplicant::where('designation_id', $job->designation_id)->whereMonth('created_at', $month);
                $designation = Designation::find($job->designation_id);

                $offered_candidate = $query->where('is_offered', 1)->count();
                $rejected_candidate = (clone $query)->where('is_rejected', 1)->count();
                $future_reference_candidate = (clone $query)->where('is_feature_reference', 1)->count();
                $apllied_candidate = (clone $query)->where('stages', 0)->count();
                $in_progress_candidate = (clone $query)->whereIn('stages', [1, 2, 3])->count();
                $total_candidate = (clone $query)->count();

                $data[] = [
                    'designation' => $designation->title,
                    'address' => $job->address,
                    'offered_candidate' => $offered_candidate,
                    'rejected_candidate' => $rejected_candidate,
                    'future_reference_candidate' => $future_reference_candidate,
                    'apllied_candidate' => $apllied_candidate,
                    'in_progress_candidate' => $in_progress_candidate,
                    'total_candidate' => $total_candidate,
                ];
            }

            return $this->successResponse(
                $data,
                "Job Opening Status"
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }



    /**
     * @OA\Get(
     * path="/uc/api/admin_dashboard/authDetails",
     * operationId="authDetails",
     * tags={"Admin Dashboard"},
     * summary="Get Auth Details Request",
     *   security={ {"Bearer": {} }},
     * description="Get Auth Details Request",
     *      @OA\Response(
     *          response=201,
     *          description="Get Auth Details Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Get Auth Details Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function authDetails()
    {
        try {
            $user_id = auth('sanctum')->user()->id;

            $auth_role = DB::table('role_user')->where('user_id', $auth_id)->first();

            $Role = DB::table('roles')->find($auth_role->role_id);

            $user = SubUser::select('id', 'unique_id', 'first_name', 'last_name', 'database_name', 'company_name', 'email', 'status', 'user_type')->findOrFail($user_id);
            if (!$user) {
                return $this->errorResponse("User not found", 404);
            }
            $data = [
                'role' => $Role->name,
                'user_details' => $user,
            ];

            return $this->successResponse(
                $data,
                "Auth Details"
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }
}
