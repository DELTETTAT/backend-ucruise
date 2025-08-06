<?php

namespace App\Http\Controllers\Api\Hrms\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use App\Models\NewApplicant;
use App\Models\EmployeeAttendance;
use App\Models\HrmsTimeAndShift;
use App\Models\Holiday;
use App\Models\Leave;
use Carbon\CarbonPeriod;
use App\Http\Controllers\Api\HomeController;
use App\Models\SubUser;
use App\Models\User;
use App\Models\PfAndLeaveSetting;
use App\Models\HrmsCalenderAttendance;
use App\Models\Reschedule;
use App\Models\ScheduleCarerRelocation;
use App\Models\Resignation;

class HrmsDashboardController extends Controller
{

    /**
     * @OA\Post(
     *     path="/uc/api/dashboard/hiringStatusForGraph",
     *     operationId="hiringStatusForGraph",
     *     tags={"HRMS Dashboard"},
     *     summary="Get hiring Status For Graph",
     *     security={{"Bearer": {}}},
     *     description="Endpoint to process Hiring Status For Graph data.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="filtered_type", type="integer", description="1 => Application, 2 => Shortlisted, 3 => Rejected."),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Get Hiring Status successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Hiring Status successfully."),
     *             @OA\Property(property="template", type="object", description="Details of Hiring Status.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Validation error."),
     *             @OA\Property(property="details", type="object", description="Validation errors.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid request.")
     *         )
     *     )
     * )
     */


    public function hiringStatusForGraph()
    {

        try {
            $startDate = Carbon::now()->subDays(30)->startOfDay();
            $endDate = Carbon::now()->endOfDay();


            $applicantsData = NewApplicant::selectRaw("
            DATE(created_at) as date,
            COUNT(*) as total_applicants")
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'ASC')
                ->pluck('total_applicants', 'date');


            $shortlistedData = NewApplicant::selectRaw("
            DATE(updated_at) as date,
            COUNT(*) as total_shortlisted")
                ->where('stages', 4)
                ->where('is_rejected', 0)
                ->where('is_employee', 0)
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(updated_at)'))
                ->orderBy('date', 'ASC')
                ->pluck('total_shortlisted', 'date');

            $rejectedData = NewApplicant::selectRaw("
            DATE(updated_at) as date,
            COUNT(*) as total_rejected")
                ->where('is_rejected', 1)
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(updated_at)'))
                ->orderBy('date', 'ASC')
                ->pluck('total_rejected', 'date');


            $last30Days = Carbon::now()->subDays(30);
            $currentDate = Carbon::now();
            $dateRange = [];

            while ($last30Days->lte($currentDate)) {
                $date = $last30Days->format('Y-m-d'); // Database date format

                $dateRange[$date] = [
                    'applicant' => $applicantsData[$date] ?? 0,
                    'shortlisted' => $shortlistedData[$date] ?? 0,
                    'rejected' => $rejectedData[$date] ?? 0,
                ];

                $last30Days->addDay();
            }


            return $this->successResponse(
                $dateRange,
                "Graph data of hiring status"
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }





    /**
     * @OA\Post(
     * path="/uc/api/employee_dashboard/today_attendance",
     * operationId="todayattendance",
     * tags={"Employee Dashboard"},
     * summary="Get today attendance Request",
     *   security={ {"Bearer": {} }},
     * description="Get today attendance Request",
     *      @OA\Response(
     *          response=201,
     *          description="today attendance Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="today attendance Get Successfully",
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

    // public function todayAttendance(Request $request)
    // {
    //     try {

    //         if ($request->employee_id) {
    //             $auth_employee = SubUser::find($request->employee_id);
    //             $id = $request->employee_id;
    //         } else {
    //             $auth_employee = auth('sanctum')->user();
    //             $id = $auth_employee->id;
    //         }


    //         $lateFormatted = null;
    //         $today = today()->format('Y-m-d');
    //         $user = SubUser::find($id);
    //         $shift_name = $user->employee_shift ?? 'Morning Shift';
    //         $startDate = now()->startOfMonth()->format('Y-m-d');
    //         $endDate = now()->format('Y-m-d');
    //         $totalDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;     // this month total days

    //         $shift = HrmsTimeAndShift::where('shift_name', $shift_name)->first();

    //         if (!$shift) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => "Not define Time Shift in System Setup"
    //             ]);
    //         }

    //         $employee_attendance = EmployeeAttendance::where('user_id', $id)->whereDate('date', $today)->first();

    //         if (!$employee_attendance) {
    //             $present = 0;
    //         } else {
    //             $activityLog = $employee_attendance->activity_log;
    //             $lastActivity = end($activityLog);

    //             if (empty($lastActivity['logout_time'])) {
    //                 $present = 1;
    //             } else {
    //                 $present = 0;
    //             }
    //         }

    //         $startTime = Carbon::parse($today . ' ' . $shift->shift_time['start']);
    //         $endTime = Carbon::parse($today . ' ' . $shift->shift_time['end']);

    //         $totalShiftWorkingHours = $startTime->diffInHours($endTime);

    //         if ($employee_attendance) {
    //         }

    //         if ($employee_attendance) {
    //             $employee_login_time = Carbon::parse($employee_attendance->login_time);
    //         } else {
    //             $employee_login_time = now();
    //         }


    //         if ($shift->shift_finishs_next_day == 1 && $employee_login_time->lessThan($startTime)) {
    //             $startTime->subDay(); // If shift starts "yesterday" night
    //         }


    //         if ($employee_login_time->greaterThan($startTime)) {
    //             $lateMinutes = $employee_login_time->diffInMinutes($startTime);

    //             if ($lateMinutes >= 30) {
    //                 $lateFormatted = gmdate("H\h i\m", $lateMinutes * 60);
    //             }
    //         }

    //         $shiftDays = $shift->shift_days;


    //         $companyHolidayDays = collect($shiftDays)->filter(function ($value) {
    //             return $value == "0"; // values are strings like "0"
    //         })->keys(); // ['SUN', 'SAT']

    //         // 4. Loop through date range and count non-working days
    //         $period = CarbonPeriod::create($startDate, $endDate);

    //         $companyHolidaysCount = 0;

    //         foreach ($period as $date) {
    //             $day = strtoupper($date->format('D')); // 'Mon' => 'MON'
    //             if ($companyHolidayDays->contains($day)) {
    //                 $companyHolidaysCount++;
    //             }
    //         }


    //         $holidayDates = Holiday::whereBetween('date', [$startDate, $endDate])->get();

    //         // 3. Filter out holidays which fall on weekly off days
    //         $filteredHolidays = $holidayDates->filter(function ($holiday) use ($companyHolidayDays) {
    //             $dayName = strtoupper(Carbon::parse($holiday->date)->format('D')); // e.g., MON, TUE
    //             return !$companyHolidayDays->contains($dayName); // keep only those not in weekly off
    //         });

    //         // 4. Final holiday count (excluding weekly offs)
    //         $holidayCount =  $filteredHolidays->pluck('date')->unique()->count();  //$filteredHolidays->count();


    //         $allHolidays = $companyHolidaysCount + $holidayCount;
    //         $workingDays = $totalDays - $allHolidays;
    //         $presentDays = EmployeeAttendance::where('user_id', $id)->whereBetween('date', [$startDate, $endDate])->count();
    //         //$Attendance  = $Attendance = $workingDays > 0 ? ($presentDays / $workingDays) * 100 : 0;
    //         $Attendance = $workingDays > 0 ? min(100, ($presentDays / $workingDays) * 100) : 0;

    //         /////////////// Average logout time

    //         $employeelogout = EmployeeAttendance::where('user_id', $id)
    //             ->whereNotNull('logout_time')
    //             ->whereBetween('date', [$startDate, $endDate])
    //             ->pluck('logout_time');

    //         if ($employeelogout->count() > 0) {
    //             $totalSeconds = 0;

    //             foreach ($employeelogout as $logoutTime) {
    //                 $time = Carbon::parse($logoutTime);
    //                 $seconds = ($time->hour * 3600) + ($time->minute * 60);
    //                 $totalSeconds += $seconds;
    //             }

    //             $averageSeconds = $totalSeconds / $employeelogout->count();

    //             $avgHours = floor($averageSeconds / 3600);
    //             $avgMinutes = floor(($averageSeconds % 3600) / 60);

    //             // Create Carbon object for formatting
    //             $avgTimeCarbon = Carbon::createFromTime($avgHours, $avgMinutes, 0);
    //             $averageLogoutTime = $avgTimeCarbon->format('h:i A'); // 12-hour format with AM/PM
    //         } else {
    //             $averageLogoutTime = null;
    //         }


    //         //////////////  Average hours

    //         $attendances = EmployeeAttendance::where('user_id', $id)
    //             ->whereBetween('date', [$startDate, $endDate])
    //             ->get(['login_time', 'logout_time']);

    //         $totalSeconds = 0;
    //         $validDays = 0;

    //         foreach ($attendances as $attendance) {
    //             if ($attendance->login_time && $attendance->logout_time) {
    //                 $login = Carbon::parse($attendance->login_time);
    //                 $logout = Carbon::parse($attendance->logout_time);

    //                 $secondsWorked = $logout->diffInSeconds($login);

    //                 $totalSeconds += $secondsWorked;
    //                 $validDays++;
    //             }
    //         }

    //         if ($validDays > 0) {
    //             $averageSeconds = $totalSeconds / $validDays;

    //             $avgHours = floor($averageSeconds / 3600);
    //             $avgMinutes = floor(($averageSeconds % 3600) / 60);

    //             $formattedAverage = sprintf('%02d:%02d', $avgHours, $avgMinutes); // HH:MM format
    //         } else {
    //             $formattedAverage = '00:00';
    //         }


    //         $employeelogins = EmployeeAttendance::where('user_id', $id)
    //             ->whereBetween('date', [$startDate, $endDate])
    //             ->pluck('login_time');

    //         if ($employeelogins->count() > 0) {
    //             $totalSeconds = 0;

    //             foreach ($employeelogins as $loginTime) {
    //                 $time = Carbon::parse($loginTime);
    //                 $seconds = ($time->hour * 3600) + ($time->minute * 60) + $time->second;
    //                 $totalSeconds += $seconds;
    //             }

    //             $averageSeconds = $totalSeconds / $employeelogins->count();

    //             // Convert average seconds to HH:MM format
    //             $avgHour = floor($averageSeconds / 3600);
    //             $avgMinute = floor(($averageSeconds % 3600) / 60);
    //             $avgSecond = floor($averageSeconds % 60);

    //             $averageLoginTime = Carbon::createFromTime($avgHour, $avgMinute, $avgSecond)->format('h:i A');
    //         } else {
    //             $averageLoginTime = "No login data";
    //         }


    //         ///////////////// on time arrival

    //         $shiftLoginTimeLimit = Carbon::parse('10:30'); // Shift ke hisaab se allowed limit


    //         $onTimeCount = 0;
    //         $totalLogins = 0;

    //         foreach ($employeelogins as $loginTime) {
    //             if ($loginTime) {
    //                 $login = Carbon::parse($loginTime);

    //                 // Compare only time (not date)
    //                 if ($login->format('H:i:s') <= $shiftLoginTimeLimit->format('H:i:s')) {
    //                     $onTimeCount++;
    //                 }

    //                 $totalLogins++;
    //             }
    //         }

    //         $onTimePercentage = $totalLogins > 0
    //             ? round(($onTimeCount / $totalLogins) * 100, 2)
    //             : 0;

    //         /// /////// view status


    //         $viewStatus = [];

    //         $viewStatus['totalDays'] = $totalDays;

    //         $viewStatus['present'] = $presentDays;

    //         $leaves = Leave::where('staff_id', $id)
    //             ->where('status', 1)
    //             ->where(function ($query) use ($startDate, $endDate) {
    //                 $query->whereBetween('start_date', [$startDate, $endDate])
    //                     ->orWhereBetween('end_date', [$startDate, $endDate]);
    //             })
    //             ->get();

    //         // Get all holidays
    //         $holidayDates = Holiday::whereBetween('date', [$startDate, $endDate])
    //             ->pluck('date')
    //             ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
    //             ->toArray();

    //         // Get all attendance dates of employee
    //         $attendanceDates = EmployeeAttendance::where('user_id', $id)
    //             ->whereBetween('date', [$startDate, $endDate])
    //             ->pluck('date')
    //             ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
    //             ->toArray();


    //         $leaveDates = [];

    //         foreach ($leaves as $leave) {
    //             $leaveStart = Carbon::parse($leave->start_date);
    //             $leaveEnd = Carbon::parse($leave->end_date);

    //             while ($leaveStart->lte($leaveEnd)) {
    //                 if ($leaveStart->format('Y-m') === now()->format('Y-m')) {
    //                     $leaveDates[] = $leaveStart->format('Y-m-d');
    //                 }

    //                 $leaveStart->addDay();
    //             }
    //         }


    //         $viewStatus['leaves'] = count($leaveDates);

    //         // Get non-working days from shift
    //         $shift = HrmsTimeAndShift::where('shift_name', $shift_name)->first();
    //         $shiftDays = $shift->shift_days; // assuming it's like ['SUN' => 0, 'MON' => 1, ...]

    //         // Get company off-days (like SUN, SAT)
    //         $companyOffDays = collect($shiftDays)->filter(fn($value) => $value == "0")->keys()->toArray();

    //         // Create a date range for the month
    //         $period = CarbonPeriod::create($startDate, $endDate);

    //         $publicHoliday = Holiday::whereMonth('date', $date)->pluck('date');
    //         $absentDates = [];
    //         $count_company_holiday = 0;

    //         foreach ($period as $date) {
    //             $dayName = strtoupper($date->format('D')); // 'MON', 'TUE', etc.
    //             $formattedDate = $date->format('Y-m-d');

    //             if (
    //                 !in_array($dayName, $companyOffDays) && // It's a working day
    //                 !in_array($formattedDate, $holidayDates) && // Not a public holiday
    //                 !in_array($formattedDate, $attendanceDates) && // No attendance
    //                 !in_array($formattedDate, $leaveDates)
    //             ) {
    //                 $absentDates[] = $formattedDate;
    //             }

    //             // Count company holidays
    //             if (in_array($dayName, $companyHolidayDays->toArray())) {
    //                 $count_company_holiday++;
    //             }
    //         }


    //         $viewStatus['absentDates'] = count($absentDates);

    //         $averageData['averageWorkingHours'] = $formattedAverage;
    //         $averageData['averageCheckIn'] = $averageLoginTime;
    //         $averageData['averageCheckOut'] = $averageLogoutTime;
    //         $averageData['onTimeArrival'] = $onTimePercentage . '%';

    //         /////  count company holidays
    //         // $count_company_holiday = $companyHolidayDays;
    //         $viewStatus['company_holiday'] = $count_company_holiday;

    //         /// half days counting
    //         $half_days = HrmsCalenderAttendance::where('user_id', $id)->whereBetween('date', [$startDate, $endDate])->where('status', 'halfday')->count();
    //         $viewStatus['half_days'] = $half_days;

    //         $viewStatus['calender_data'] = $this->calendarAttendance($request, $id);

    //         return response()->json([
    //             'late_time' => $lateFormatted,
    //             'present' => $present,
    //             // 'holiday' => $allHolidays,
    //             // 'working Days' => $workingDays,
    //             // 'present Days' => $presentDays,
    //             'Total Days' => $totalDays,
    //             'Attendance ' => round($Attendance, 2) . '%',
    //             'averageData' => $averageData,
    //             'employeeName' => $auth_employee->first_name . " " . $auth_employee->last_name,
    //             // 'totalValidDays' => $validDays,
    //             'viewStatus' => $viewStatus,
    //         ]);
    //     } catch (\Throwable $th) {
    //         return $this->errorResponse($th->getMessage());
    //     }
    // }

    public function todayAttendance(Request $request)
    {
        try {
            $month = $request->month ?? now()->month;
            $year = $request->year ?? now()->year;

            if ($request->employee_id) {
                $auth_employee = SubUser::find($request->employee_id);
                $id = $request->employee_id;
            } else {
                $auth_employee = auth('sanctum')->user();
                $id = $auth_employee->id;
            }

            $lateFormatted = null;
            $today = today()->format('Y-m-d');

            $user = SubUser::find($id);
            $shift_name = $user->employee_shift ?? 'Morning Shift';

            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            $todayCarbon = Carbon::today();
            if ($endDate > $todayCarbon) {
                $endDate = $todayCarbon;
            }
            $endDate = $endDate->format('Y-m-d');

            $totalDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

            $shift = HrmsTimeAndShift::where('shift_name', $shift_name)->first();

            if (!$shift) {
                return response()->json([
                    'status' => false,
                    'message' => "Not define Time Shift in System Setup"
                ]);
            }

            $employee_attendance = EmployeeAttendance::where('user_id', $id)->whereDate('date', $today)->first();

            if (!$employee_attendance) {
                $present = 0;
            } else {
                $activityLog = $employee_attendance->activity_log;
                $lastActivity = end($activityLog);

                $present = empty($lastActivity['logout_time']) ? 1 : 0;
            }

            $startTime = Carbon::parse($today . ' ' . $shift->shift_time['start']);
            $endTime = Carbon::parse($today . ' ' . $shift->shift_time['end']);
            $totalShiftWorkingHours = $startTime->diffInHours($endTime);

            $employee_login_time = $employee_attendance ? Carbon::parse($employee_attendance->login_time) : now();

            if ($shift->shift_finishs_next_day == 1 && $employee_login_time->lessThan($startTime)) {
                $startTime->subDay();
            }

            if ($employee_login_time->greaterThan($startTime)) {
                $lateMinutes = $employee_login_time->diffInMinutes($startTime);
                if ($lateMinutes >= 30) {
                    $lateFormatted = gmdate("H\h i\m", $lateMinutes * 60);
                }
            }

            $shiftDays = $shift->shift_days;
            $companyHolidayDays = collect($shiftDays)->filter(fn($value) => $value == "0")->keys();

            $period = CarbonPeriod::create($startDate, $endDate);
            $companyHolidaysCount = collect($period)->filter(function ($date) use ($companyHolidayDays) {
                return $companyHolidayDays->contains(strtoupper($date->format('D')));
            })->count();

            $holidayDates = Holiday::whereBetween('date', [$startDate, $endDate])->get();
            $filteredHolidays = $holidayDates->filter(function ($holiday) use ($companyHolidayDays) {
                return !$companyHolidayDays->contains(strtoupper(Carbon::parse($holiday->date)->format('D')));
            });

            $holidayCount = $filteredHolidays->pluck('date')->unique()->count();
            $allHolidays = $companyHolidaysCount + $holidayCount;
            $workingDays = $totalDays - $allHolidays;

            $presentDays = EmployeeAttendance::where('user_id', $id)->whereBetween('date', [$startDate, $endDate])->count();
            $Attendance = $workingDays > 0 ? min(100, ($presentDays / $workingDays) * 100) : 0;

            // Average logout time
            $employeelogout = EmployeeAttendance::where('user_id', $id)
                ->whereNotNull('logout_time')
                ->whereBetween('date', [$startDate, $endDate])
                ->pluck('logout_time');

            if ($employeelogout->count() > 0) {
                $totalSeconds = $employeelogout->sum(function ($logoutTime) {
                    $time = Carbon::parse($logoutTime);
                    return ($time->hour * 3600) + ($time->minute * 60);
                });
                $averageSeconds = $totalSeconds / $employeelogout->count();
                $avgHours = floor($averageSeconds / 3600);
                $avgMinutes = floor(($averageSeconds % 3600) / 60);
                $avgTimeCarbon = Carbon::createFromTime($avgHours, $avgMinutes, 0);
                $averageLogoutTime = $avgTimeCarbon->format('h:i A');
            } else {
                $averageLogoutTime = null;
            }

            // Average hours
            $attendances = EmployeeAttendance::where('user_id', $id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get(['login_time', 'logout_time']);

            $totalSeconds = 0;
            $validDays = 0;
            foreach ($attendances as $attendance) {
                if ($attendance->login_time && $attendance->logout_time) {
                    $login = Carbon::parse($attendance->login_time);
                    $logout = Carbon::parse($attendance->logout_time);
                    $totalSeconds += $logout->diffInSeconds($login);
                    $validDays++;
                }
            }
            $formattedAverage = $validDays > 0 ? sprintf('%02d:%02d', floor($totalSeconds / $validDays / 3600), floor(($totalSeconds / $validDays % 3600) / 60)) : '00:00';

            // Average login time
            $employeelogins = EmployeeAttendance::where('user_id', $id)
                ->whereBetween('date', [$startDate, $endDate])
                ->pluck('login_time');

            if ($employeelogins->count() > 0) {
                $totalSeconds = $employeelogins->sum(function ($loginTime) {
                    $time = Carbon::parse($loginTime);
                    return ($time->hour * 3600) + ($time->minute * 60) + $time->second;
                });
                $averageSeconds = $totalSeconds / $employeelogins->count();
                $avgHour = floor($averageSeconds / 3600);
                $avgMinute = floor(($averageSeconds % 3600) / 60);
                $avgSecond = floor($averageSeconds % 60);
                $averageLoginTime = Carbon::createFromTime($avgHour, $avgMinute, $avgSecond)->format('h:i A');
            } else {
                $averageLoginTime = "No login data";
            }

            // On time arrival
            $shiftLoginTimeLimit = Carbon::parse('10:30');
            $onTimeCount = $employeelogins->filter(function ($loginTime) use ($shiftLoginTimeLimit) {
                return Carbon::parse($loginTime)->format('H:i:s') <= $shiftLoginTimeLimit->format('H:i:s');
            })->count();

            $onTimePercentage = $employeelogins->count() > 0
                ? round(($onTimeCount / $employeelogins->count()) * 100, 2)
                : 0;

            $leaves = Leave::where('staff_id', $id)
                ->where('status', 1)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate]);
                })
                ->get();

            $holidayDates = Holiday::whereBetween('date', [$startDate, $endDate])
                ->pluck('date')
                ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
                ->toArray();

            $attendanceDates = EmployeeAttendance::where('user_id', $id)
                ->whereBetween('date', [$startDate, $endDate])
                ->pluck('date')
                ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
                ->toArray();

            $leaveDates = [];
            foreach ($leaves as $leave) {
                $leaveStart = Carbon::parse($leave->start_date);
                $leaveEnd = Carbon::parse($leave->end_date);
                while ($leaveStart->lte($leaveEnd)) {
                    if ($leaveStart->format('Y-m') === Carbon::createFromDate($year, $month)->format('Y-m')) {
                        $leaveDates[] = $leaveStart->format('Y-m-d');
                    }
                    $leaveStart->addDay();
                }
            }

            $shiftDays = $shift->shift_days;
            $companyOffDays = collect($shiftDays)->filter(fn($value) => $value == "0")->keys()->toArray();
            $period = CarbonPeriod::create($startDate, $endDate);
            $absentDates = [];
            $count_company_holiday = 0;

            foreach ($period as $date) {
                $formattedDate = $date->format('Y-m-d');
                $dayName = strtoupper($date->format('D'));
                if (
                    !in_array($dayName, $companyOffDays) &&
                    !in_array($formattedDate, $holidayDates) &&
                    !in_array($formattedDate, $attendanceDates) &&
                    !in_array($formattedDate, $leaveDates)
                ) {
                    $absentDates[] = $formattedDate;
                }
                if (in_array($dayName, $companyHolidayDays->toArray())) {
                    $count_company_holiday++;
                }
            }

            $viewStatus = [
                'totalDays' => $totalDays,
                'present' => $presentDays,
                'leaves' => count($leaveDates),
                'absentDates' => count($absentDates),
                'company_holiday' => $count_company_holiday,
                'half_days' => HrmsCalenderAttendance::where('user_id', $id)->whereBetween('date', [$startDate, $endDate])->where('status', 'halfday')->count(),
                'calender_data' => $this->calendarAttendance($request, $id)
            ];

            $averageData = [
                'averageWorkingHours' => $formattedAverage,
                'averageCheckIn' => $averageLoginTime,
                'averageCheckOut' => $averageLogoutTime,
                'onTimeArrival' => $onTimePercentage . '%',
            ];

            return response()->json([
                'late_time' => $lateFormatted,
                'present' => $present,
                'Total Days' => $totalDays,
                'Attendance ' => round($Attendance, 2) . '%',
                'averageData' => $averageData,
                'employeeName' => $auth_employee->first_name . " " . $auth_employee->last_name,
                'viewStatus' => $viewStatus,
            ]);
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }



    // Placeholder for your calendarAttendance function


    // public function calendarAttendance($id)
    // {


    //     $user_id = $id ?? auth('sanctum')->user()->id;
    //     $date =  now()->format('m');

    //     $startOfMonth =  now()->startOfMonth()->toDateString();
    //     $endOfMonth =  now()->toDateString();

    //     // employee attendance
    //     $employeeAttendance = HrmsCalenderAttendance::where('user_id', $user_id)->whereMonth('date', $date)->pluck('status', 'date');


    //     $employeattendenceact = EmployeeAttendance::where('user_id', $user_id)
    //         ->whereMonth('date', $date)
    //         ->get(['date', 'login_time', 'logout_time', 'ideal_time', 'production'])
    //         ->mapWithKeys(function ($item) {
    //             return [$item->date => ['login_time' => $item->login_time, 'logout_time' => $item->logout_time, 'ideal_time' => $item->ideal_time, 'production' => $item->production]];
    //         });


    //     $employeattendenceact  = $employeattendenceact->toArray();


    //     $formatedData = [];

    //     $start = Carbon::parse($startOfMonth);

    //     // public holodays
    //     $publicHoliday = Holiday::whereMonth('date', $date)->pluck('name', 'date');
    //     // employee leaves
    //     $startDate = now()->startOfMonth()->format('Y-m-d');
    //     $endDate = now()->format('Y-m-d');
    //     $leaves = Leave::where('staff_id', $user_id)
    //         ->where(function ($query) use ($startDate, $endDate) {
    //             $query->whereBetween('start_date', [$startDate, $endDate])
    //                 ->orWhereBetween('end_date', [$startDate, $endDate]);
    //         })
    //         ->get();

    //     $leaveDates = [];

    //     foreach ($leaves as $leave) {
    //         $leaveStart = Carbon::parse($leave->start_date);
    //         $leaveEnd = Carbon::parse($leave->end_date);

    //         while ($leaveStart->lte($leaveEnd)) {
    //             $leaveDates[$leaveStart->toDateString()] = $leave->leave_type ?? 'Leave';
    //             $leaveStart->addDay();
    //         }
    //     }

    //     $pieChartData = [
    //         'present' => 0,
    //         'Absent' => 0,
    //         'public_holiday' => 0,
    //         'company_holiday' => 0,
    //         'leaves' => 0,

    //     ];
    //     while ($start->lte($endOfMonth)) {
    //         $date = $start->toDateString();

    //         if (isset($employeeAttendance[$date])) {
    //             $status = $employeeAttendance[$date];
    //             $flag = 1;
    //             $pieChartData['present'] += 1;

    //             $activity = $employeattendenceact[$date] ?? [];

    //             // }elseif (isset($publicHoliday[$date])) {
    //             //     $status = "public holiday";
    //             //     $flag = 4;
    //             //     $pieChartData['public_holiday'] += 1;
    //             //     $activity = $employeattendenceact[$date] ?? [];
    //         } elseif ($start->isSaturday() || $start->isSunday() || isset($publicHoliday[$date])) {
    //             $status = "company holiday";
    //             $flag = 3;
    //             $pieChartData['company_holiday'] += 1;
    //             $activity = $employeattendenceact[$date] ?? [];
    //         } elseif (isset($leaveDates[$date])) {
    //             $status = $leaveDates[$date];
    //             $flag = 5;
    //             $pieChartData['leaves'] += 1;
    //             $activity = $employeattendenceact[$date] ?? [];
    //         } else {
    //             $status = "Absent";
    //             $flag = 2;
    //             $pieChartData['Absent'] += 1;
    //             $activity = $employeattendenceact[$date] ?? [];
    //         }

    //         $formatedData[] = [
    //             'date' => $date,
    //             'status' => $status,
    //             'flag' => $flag,
    //             'activity' => $activity
    //         ];

    //         $start->addDay();
    //     }


    //     $employee = HrmsCalenderAttendance::with('employee')
    //         ->where('user_id', $user_id)
    //         ->first();

    //     //  return $this->successResponse(
    //     //     [
    //     //         'user_id' => $user_id,
    //     //         'aatendance' => $formatedData,
    //     //         'pieChart_data' => $pieChartData
    //     //     ],

    //     //      "Calender Attendance"
    //     //  );
    //     return $formatedData;
    // }

    public function calendarAttendance(Request $request)
    {
        $user_id = $request->employeeId ?? auth('sanctum')->user()->id;
        $month = $request->month ?? now()->format('m');
        $year = $request->year ?? now()->format('Y');

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        // employee attendance
        $employeeAttendance = HrmsCalenderAttendance::where('user_id', $user_id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->pluck('status', 'date');

        $employeattendenceact = EmployeeAttendance::where('user_id', $user_id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get(['date', 'login_time', 'logout_time', 'ideal_time', 'production'])
            ->mapWithKeys(function ($item) {
                return [$item->date => [
                    'login_time' => $item->login_time,
                    'logout_time' => $item->logout_time,
                    'ideal_time' => $item->ideal_time,
                    'production' => $item->production
                ]];
            })->toArray();

        $formatedData = [];

        $start = Carbon::parse($startOfMonth);
        $publicHoliday = Holiday::whereBetween('date', [$startOfMonth, $endOfMonth])->pluck('name', 'date');

        $leaves = Leave::where('staff_id', $user_id)
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth]);
            })->get();

        $leaveDates = [];

        foreach ($leaves as $leave) {
            $leaveStart = Carbon::parse($leave->start_date);
            $leaveEnd = Carbon::parse($leave->end_date);
            while ($leaveStart->lte($leaveEnd)) {
                $leaveDates[$leaveStart->toDateString()] = $leave->leave_type ?? 'Leave';
                $leaveStart->addDay();
            }
        }

        $pieChartData = [
            'present' => 0,
            'Absent' => 0,
            'public_holiday' => 0,
            'company_holiday' => 0,
            'leaves' => 0,
        ];

        $today = now()->toDateString();
        while ($start->lte($endOfMonth) && $start->lte($today)) {
            $date = $start->toDateString();

            if (isset($employeeAttendance[$date])) {
                $status = $employeeAttendance[$date];
                $flag = 1;
                $pieChartData['present'] += 1;
                $activity = $employeattendenceact[$date] ?? [];
            } elseif (isset($employeattendenceact[$date])) {
                $status = 'Present';
                $flag = 1;
                $pieChartData['present'] += 1;
                $activity = $employeattendenceact[$date];
            } elseif ($start->isSaturday() || $start->isSunday() || isset($publicHoliday[$date])) {
                $status = "company holiday";
                $flag = 3;
                $pieChartData['company_holiday'] += 1;
                $activity = $employeattendenceact[$date] ?? [];
            } elseif (isset($leaveDates[$date])) {
                $status = $leaveDates[$date];
                $flag = 5;
                $pieChartData['leaves'] += 1;
                $activity = $employeattendenceact[$date] ?? [];
            } else {
                $status = "Absent";
                $flag = 2;
                $pieChartData['Absent'] += 1;
                $activity = $employeattendenceact[$date] ?? [];
            }

            $formatedData[] = [
                'date' => $date,
                'status' => $status,
                'flag' => $flag,
                'activity' => $activity
            ];

            $start->addDay();
        }

        return $formatedData;
}






    public function authEmployeeDetail(Request $request)
    {
        try {

            $user_id = 13; //auth('sanctum')->user()->id;
            $homeController = new HomeController();
            $user = SubUser::find($user_id);
            $user_ids = array($user_id);
            $today_date = Carbon::now()->format('Y-m-d');
            $dates = array($today_date);

            $this->data1['employee'] = [];
            $this->data1['all_schedule'] = [];
            $this->data1['reschedules'] = [];
            $this->data1['temp_location_change'] = [];
            $this->data1['leaves'] = [];
            $this->data1['team'] = [];
            $this->data1['schedule_report'] = [];
            $this->data1['role'] = User::with('roles')->find($user_id)->roles;
            $this->data1['sub_role'] =  SubUser::with('roles')->find($user_id)->roles;
            $this->data1['hrms_sub_role'] =  SubUser::with('hrmsroles')->find($user_id)->hrmsroles;


            if ($user->hasRole('carer')) {
                $this->data1['employee'] = $this->getDriverEmpoyeeById($user->id); // Retrieve driver info
                $this->data['schedules'] = $this->getWeeklyScheduleInfo($user_ids, $dates, 2, "all");
                foreach ($this->data['schedules'] as $key => $schedule) {
                    $scheduleDate = date('Y-m-d', strtotime($schedule['date']));
                    $start = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['start_time']));
                    $end = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['end_time']));
                    if ($schedule['type'] == 'pick') {
                        $this->data1['all_schedule'][$key]['time'] = $start;
                    } else {
                        $this->data1['all_schedule'][$key]['time'] = $end;
                    }
                    $this->data1['all_schedule'][$key]['type'] = $schedule['type'];
                    $this->data1['all_schedule'][$key]['scheudleId'] = $schedule['id'];
                    $this->data1['all_schedule'][$key]['schedule_ride_status'] = @$homeController->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['name'];
                    $this->data1['all_schedule'][$key]['ride_start_hours'] = @$homeController->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['hours'];
                    $this->data1['all_schedule'][$key]['schedule_ride_status_id'] = @$homeController->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['id'];
                    $this->data1['all_schedule'][$key]['schedule_driver_rating'] = @$homeController->getdriverRating($schedule['driver_id']);
                    $this->data1['all_schedule'][$key]['schedule'] = $schedule;
                    $this->data1['all_schedule'][$key]['driver'] = @$homeController->getScheduleDriver($schedule['id'], $scheduleDate);
                    $this->data1['all_schedule'][$key]['carers'] = @$homeController->getScheduleCarers($schedule['id'], $schedule['type'], $scheduleDate);
                }

                if ($this->data1['all_schedule']) {
                    usort($this->data1['all_schedule'], function ($a, $b) {
                        $dateTimeA = new \DateTime($a['time']);
                        $dateTimeB = new \DateTime($b['time']);
                        return $dateTimeA <=> $dateTimeB;
                    });
                }
                $this->data1['reschedules'] = @$this->employeeReschedules($user->id);
                $this->data1['temp_location_change'] = @$this->employeeTempLocationChange($user->id);
                $this->data1['leaves'] = @$this->employeeLeaves($user->id);
                $this->data1['team'] = @$this->teams($user->id);
                $this->data1['schedule_report'] = @$this->scheduleReport($user->id);
            }
            return response()->json(['success' => true, "data" => $this->data1, 'employee_image_url' => url('public/images')], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    //************* Employee Reschedules**************************************************
    public function employeeReschedules($id)
    {
        $reschedules = Reschedule::where('user_id', $id)
            ->with(['reason', 'user' => function ($query) {
                $query->select('id', 'first_name');
            }])
            ->get();
        return $reschedules;
    }
    //************* Employee Leaves*****************************************************
    public function employeeLeaves($id)
    {
        $leaves = Leave::where('staff_id', $id)->with(['reason', 'staff' => function ($query) {
            $query->select('id', 'first_name');
        }])->get();
        return $leaves;
    }
    //************ Employee temp location change****************************************
    public function employeeTempLocationChange($id)
    {
        $templocationchange = ScheduleCarerRelocation::where('staff_id', $id)->with(['reason', 'user' => function ($query) {
            $query->select('id', 'first_name');
        }])->get();
        return $templocationchange;
    }




    /**
     * @OA\Get(
     * path="/uc/api/employee_dashboard/authleavetype",
     * operationId="authleavetype",
     * tags={"Employee Dashboard"},
     * summary="Get auth employee details Request",
     *   security={ {"Bearer": {} }},
     * description="Get auth employee details Request",
     *      @OA\Response(
     *          response=201,
     *          description="auth employee details Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="auth employee details Get Successfully",
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



    public function authleavetype()
    {
        try {

            $user_id = auth('sanctum')->user()->id;
            $leaves = Leave::where('staff_id', $user_id)->get();

            $leavesData = [];

            $leavesSttings = PfAndLeaveSetting::first();
            if (!$leavesSttings) {
                return response()->json([
                    "medical_leave" => [
                        "allowed_leave" => "0",
                        "leave_taken" => 0
                    ],
                    "casual_leave" => [
                        "allowed_leave" => "0",
                        "leave_taken" => 0
                    ],
                    "maternity_leave" => [
                        "allowed_leave" => "0",
                        "leave_taken" => 0
                    ],
                    "bereavement_leave" => [
                        "allowed_leave" => "0",
                        "leave_taken" => 0
                    ],
                    "wedding_leave" => [
                        "allowed_leave" => "0",
                        "leave_taken" => 0
                    ],
                    "paternity_leave" => [
                        "allowed_leave" => "0",
                        "leave_taken" => 0
                    ],
                    "total_leave_taken" => 0,
                    "total_allowed_leave" => 0
                ]);
            }

            $medical_leave = 0;
            $casual_leave = 0;
            $maternity_leave = 0;
            $bereavement_leave = 0;
            $wedding_leave = 0;
            $paternity_leave = 0;

            foreach ($leaves as $leave) {
                $startDate = Carbon::parse($leave->start_date);
                $endDate = Carbon::parse($leave->end_date);
                $days = $startDate->diffInDays($endDate) + 1;

                if ($leave->leave_type == "medical_leave") {
                    $medical_leave += $days;
                }
                if ($leave->leave_type == "casual_leave") {
                    $casual_leave += $days;
                }
                if ($leave->leave_type == "maternity_leave") {
                    $maternity_leave += $days;
                }
                if ($leave->leave_type == "bereavement_leave") {
                    $bereavement_leave += $days;
                }
                if ($leave->leave_type == "wedding_leave") {
                    $wedding_leave += $days;
                }
                if ($leave->leave_type == "paternity_leave") {
                    $paternity_leave += $days;
                }
            }

            $leavesData['medical_leave']  = [
                'allowed_leave' => $leavesSttings->leave_deduction['medical_leave']['full'],
                'leave_taken' => $medical_leave
            ];
            $leavesData['casual_leave']  = [
                'allowed_leave' => $leavesSttings->leave_deduction['casual_leave']['full'],
                'leave_taken' => $casual_leave
            ];
            $leavesData['maternity_leave']  = [
                'allowed_leave' => $leavesSttings->leave_deduction['maternity_leave']['full'],
                'leave_taken' => $maternity_leave
            ];
            $leavesData['bereavement_leave']  = [
                'allowed_leave' => $leavesSttings->leave_deduction['bereavement_leave']['full'],
                'leave_taken' => $bereavement_leave
            ];
            $leavesData['wedding_leave']  = [
                'allowed_leave' => $leavesSttings->leave_deduction['wedding_leave']['full'],
                'leave_taken' => $wedding_leave
            ];
            $leavesData['paternity_leave']  = [
                'allowed_leave' => $leavesSttings->leave_deduction['paternity_leave']['full'],
                'leave_taken' => $paternity_leave
            ];

            $leavesData['total_leave_taken'] = $medical_leave + $casual_leave + $maternity_leave + $bereavement_leave + $wedding_leave + $paternity_leave;

            $leavesData['total_allowed_leave'] = $leavesSttings->leave_deduction['medical_leave']['full'] +
                $leavesSttings->leave_deduction['casual_leave']['full'] +
                $leavesSttings->leave_deduction['maternity_leave']['full'] +
                $leavesSttings->leave_deduction['bereavement_leave']['full'] +
                $leavesSttings->leave_deduction['wedding_leave']['full'] +
                $leavesSttings->leave_deduction['paternity_leave']['full'];

            return $leavesData;
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }





    /**
     * @OA\Get(
     * path="/uc/api/employee_dashboard/authTodayAttendance",
     * operationId="authtodayattendance",
     * tags={"Employee Dashboard"},
     * summary="Get auth today attendance details Request",
     *   security={ {"Bearer": {} }},
     * description="Get today attendance  details Request",
     *      @OA\Response(
     *          response=201,
     *          description="today attendance  details Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="today attendance  details Get Successfully",
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


    public function authTodayAttendance(Request $request)
    {
        try {
            //$user = auth('sanctum')->user();
            if ($request->employee_id) {
                $user = SubUser::find($request->employee_id);
            } else {
                $user = auth('sanctum')->user();
            }

            $user_shift = $user->employee_shift ?? 'Morning Shift';
            $user_id = $user->id;
            $today = today()->format('Y-m-d');
            $employeeAttendance = EmployeeAttendance::where('user_id', $user_id)->whereDate('date', $today)->first();

            $todayAttendanceData = [];

            if (!$employeeAttendance) {
                $todayAttendanceData['check_in'] = 0;
                $todayAttendanceData['check_out'] = 0;
                $todayAttendanceData['today_hours'] = 0;
                $todayAttendanceData['on_time_arrivel'] = 'no';

                return $this->successResponse(
                    $todayAttendanceData,
                    "Today Attendance"
                );
            }

            $todayAttendanceData['check_in'] = $employeeAttendance->login_time;
            $todayAttendanceData['check_out'] = $employeeAttendance->logout_time;

            $loginTime = Carbon::parse($employeeAttendance->login_time);
            $logoutTime = $employeeAttendance->logout_time ? Carbon::parse($employeeAttendance->logout_time) : now();

            $diff = $loginTime->diff($logoutTime);
            $todayAttendanceData['today_hours'] = $diff->format('%H:%I');

            $shift = HrmsTimeAndShift::where('shift_name', $user_shift)->first();

            $shift_login_time = Carbon::parse($shift->shift_time['start']);

            $thresholdTime = $shift_login_time->copy()->addMinutes(30);


            $onTime = $loginTime->lte($thresholdTime) ? 'yes' : 'no';
            $todayAttendanceData['on_time_arrivel'] = $onTime;

            return $this->successResponse(
                $todayAttendanceData,
                "Today Attendance"
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }


    /**
     * @OA\Get(
     * path="/uc/api/employee_dashboard/authRequest",
     * operationId="authRequest",
     * tags={"Employee Dashboard"},
     * summary="Get auth leave etc. request details Request",
     *   security={ {"Bearer": {} }},
     * description="Get auth leave etc. request  details Request",
     *      @OA\Response(
     *          response=201,
     *          description="auth leave etc. request  details Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="auth leave etc. request  details Get Successfully",
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


    public function authRequest()
    {
        try {

            $user_id = auth('sanctum')->user()->id;
            $today = today()->format('Y-m-d');

            $leaves = Leave::where('staff_id', $user_id)->whereDate('start_date', '>=', $today)
                ->whereDate('end_date', '>=', $today)
                ->count();
            $data = [];


            $reSchedule = Reschedule::where('user_id', $user_id)->whereDate('date', '>=', $today)->count();
            $temLocationRequests = ScheduleCarerRelocation::where('staff_id', $user_id)->whereDate('date', '>=', $today)->count();
            $resignationRequests = Resignation::where('user_id', $user_id)->whereDate('date', '>=', $today)->count();

            $data['leave'] = $leaves;
            $data['reschedule_request'] = $reSchedule;
            $data['tem_location_request'] = $temLocationRequests;
            $data['resignation_requests'] = $resignationRequests;

            return $this->successResponse(
                $data,
                "Request Data"
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }
}
