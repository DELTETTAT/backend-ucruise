<?php

namespace App\Http\Controllers\Api\Hrms\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HrmsCalenderAttendance;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\HrmsPayroll;
use App\Models\EmployeeAttendance;
use App\Models\HrmsTimeAndShift;
use Carbon\Carbon;

class EmployeeCalenderAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\post(
     * path="/uc/api/employee_attendace/calender",
     * operationId="employee_attendacecalender",
     * tags={"Employee Attendance"},
     * summary="Get Employee Attendance Calender Request",
     *   security={ {"Bearer": {} }},
     * description="Get Employee Attendance Calender Request",
     *      @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="employee_id", type="string"),
     *                 @OA\Property(property="month", type="string", format="date", example="2024-02-20"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Employee Attendance Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Employee Attendance Get Successfully",
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

    public function index(Request $request)
    {

        try {
            $request->validate([
                'user_id' => 'required|integer|exists:sub_users,id',
                'month'   => 'nullable'
            ]);

            $shift_name = auth('sanctum')->user()->shift_type;


             $user_id = $request->user_id;
             $date = $request->month ? Carbon::parse($request->month)->format('m') : now()->format('m');

             $startOfMonth = $request->month ? Carbon::parse($request->month)->startOfMonth()->toDateString() : now()->startOfMonth()->toDateString();
             $endOfMonth = $request->month ? Carbon::parse($request->month)->endOfMonth()->toDateString() : now()->endOfMonth()->toDateString();

               // employee attendance
             $employeeAttendance = HrmsCalenderAttendance:: where('user_id', $user_id)->whereMonth('date', $date)->pluck('status', 'date');
             //$employeattendenceact = EmployeeAttendance::where('user_id', $user_id)->whereMonth('date', $date)->get(['date', 'login_time', 'logout_time','ideal_time','production']);

            $employeattendenceact = EmployeeAttendance::where('user_id', $user_id)
                ->whereMonth('date', $date)
                ->get(['date', 'login_time', 'logout_time','ideal_time','production'])
                ->mapWithKeys(function ($item) {
                    return [$item->date => ['login_time' => $item->login_time, 'logout_time' => $item->logout_time, 'ideal_time' => $item->ideal_time, 'production' => $item->production]];
                });


               $employeattendenceact  = $employeattendenceact->toArray();


             $formatedData = [];

             $start = Carbon::parse($startOfMonth) ;

                // public holodays
             $publicHoliday = Holiday::whereMonth('date', $date)->pluck('name', 'date');
                // employee leaves

            $leaves = Leave::where('staff_id', $user_id)
                ->where('status', 1)
                ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                        ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth]);
                })
                ->get();

                $leaveDates = [];

            foreach ($leaves as $leave) {
                $leaveStart = Carbon::parse($leave->start_date);
                $leaveEnd = Carbon::parse($leave->end_date);

                while ($leaveStart->lte($leaveEnd)) {
                    //if ($leaveStart->format('Y-m') === now()->format('Y-m')) {
                        $leaveDates[$leaveStart->format('Y-m-d')] =  $leave->leave_type;
                    //}

                    $leaveStart->addDay();
                }
            }

             $leaveDates;

            $pieChartData = [
                'present' => 0,
                'Absent' => 0,
                'public_holiday' => 0,
                'company_holiday' => 0,
                'leaves' => 0,

            ];

                if (empty($shift_name)) {
                    $shift = HrmsTimeAndShift::first();
                }else {
                    $shift = HrmsTimeAndShift::where('shift_name', $shift_name)->first();
                }

                $shiftDays = $shift->shift_days; // assuming it's like ['SUN' => 0, 'MON' => 1, ...]
                // Get company off-days (like SUN, SAT)
                $companyOffDays = collect($shiftDays)->filter(fn($value) => $value == "0")->keys()->toArray();


             while ($start->lte($endOfMonth)) {
                $date = $start->toDateString();
                if (isset($employeeAttendance[$date])) {
                    $status = $employeeAttendance[$date];
                    $flag = 1;
                    $pieChartData['present'] += 1;

                    $activity = $employeattendenceact[$date] ?? [];

                }elseif (isset($publicHoliday[$date])) {
                    $status = "public holiday";
                    $flag = 4;
                    $pieChartData['public_holiday'] += 1;
                    $activity = $employeattendenceact[$date] ?? [];
                }elseif (in_array(strtoupper($start->format('D')), $companyOffDays) && now()->toDateString() >= $date) {
                    $status = "company holiday";
                    $flag = 3;
                    $pieChartData['company_holiday'] += 1;
                    $activity = $employeattendenceact[$date] ?? [];
                }elseif (isset($leaveDates[$date])) {
                    $status = $leaveDates[$date];
                    $flag = 5;
                    $pieChartData['leaves'] += 1;
                    $activity = $employeattendenceact[$date] ?? [];
                }elseif (now()->toDateString() < $date) {
                    $status = "NA";
                    $flag = 6;
                }else {
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


             $employee = HrmsCalenderAttendance::with('employee')
            ->where('user_id', $user_id)
            ->first();

             return $this->successResponse(
                [
                    'user_id' => $user_id,
                    'aatendance' => $formatedData,
                    'pieChart_data' => $pieChartData
                ],

                 "Calender Attendance"
             );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store($request)
    {
        $date = $request->date->toDateString();

        // checked alredy exist this date entry
        $existDate = HrmsCalenderAttendance:: where('user_id', $request->user_id)->where('date', $date)->first();

         if (empty($existDate)) {
            HrmsCalenderAttendance:: create([
                'user_id' => $request->user_id,
                'date' => $date
            ]);
         }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\post(
     * path="/uc/api/employee_attendace/calender/update",
     * operationId="employee_attendaceupdate",
     * tags={"Employee Attendance"},
     * summary="Get Employee Attendance Calender Update Request",
     *   security={ {"Bearer": {} }},
     * description="Get Employee Attendance Calender Update Request",
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             type="object",
    *             required={"user_id", "date_status"},
    *             @OA\Property(property="user_id", type="integer", example=12),
    *             @OA\Property(
    *                 property="date_status",
    *                 type="array",
    *                 @OA\Items(
    *                     type="object",
    *                     @OA\Property(property="date", type="string", format="date", example="2024-02-20"),
    *                     @OA\Property(
    *                         property="status",
    *                         type="string",
    *                         enum={"present", "absent", "halfday", "unpaidleave", "unpaidhalfday", "companyholiday", "paidleave", "medicalleave", "EC", "ABS"},
    *                         example="present"
    *                     )
    *                 )
    *             )
    *         )
    *     ),
     *      @OA\Response(
     *          response=201,
     *          description="Employee Attendance Updated Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Employee Attendance Updated Successfully",
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
    public function update(Request $request)
    {
         try {
            // $validated = $request->validate([
            //     'user_id' => 'required',
            //     'date' => 'required',
            //     'status' => 'required'
            // ]);

            $validated = $request->validate([
                'user_id' => 'required|integer',
                'date_status' => 'required',
                'date_status.*.date' => 'required|date|before_or_equal:today',
                'date_status.*.status' => 'required|string'
            ]);


             foreach ($validated['date_status'] as $key => $entry) {

                $date = Carbon::parse($entry['date'])->format('Y-m-d');
                $status = $entry['status'];
                $findExistAttendance = HrmsCalenderAttendance::where('user_id', $request->user_id)->whereDate('date', $date)->first();
                if ($findExistAttendance) {
                    $findExistAttendance->update([
                        'status' => $status,
                    ]);
                }else {
                    $updatedAttendance = HrmsCalenderAttendance::create(['user_id' => $request->user_id, 'date' => $date, 'status' => $status]);
                }


             }

            // $findUnpaidDays = HrmsCalenderAttendance::where('user_id', $request->user_id)
            //                                           ->whereMonth('date', $month)
            //                                           ->where('status', 'unpaidleave')->count();

            // $findHalfDays = HrmsCalenderAttendance::where('user_id', $request->user_id)
            //                                           ->whereMonth('date', $month)
            //                                           ->where('status', 'unpaidhalfday')->count();

            // $totalUnpaidLeave = 0;

            // if ($findUnpaidDays) {
            //     $totalUnpaidLeave = $totalUnpaidLeave + $findUnpaidDays;
            // }
            // if ($findHalfDays) {
            //     $totalUnpaidLeave = $totalUnpaidLeave + ($findHalfDays/2);

            // }

            // if ($totalUnpaidLeave > 0) {
            //     $findPayroll = HrmsPayroll::where('user_id', $request->user_id)
            //                                 ->whereMonth('date',$month)
            //                                 ->whereYear('date', $year)->first();

            //       if ($findPayroll) {
            //           $newPaidDays = $days_in_month - $totalUnpaidLeave;

            //             $findPayroll->update([
            //                 'total_paid_days' => $newPaidDays,
            //             ]);
            //       }
            // }

            return $this->successResponse(
                [],
                "Attendance Updated"
            );

         } catch (\Throwable $th) {
             return $this->errorResponse($th->getMessage());
         }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }



    /**
     * @OA\Get(
     * path="/uc/api/employee_attendace/calender_update_list",
     * operationId="calenderupdatelist",
     * tags={"Employee Attendance"},
     * summary="Calender Update List Request",
     *   security={ {"Bearer": {} }},
     * description="Calender Update ListRequest",
     *      @OA\Response(
     *          response=201,
     *          description="Calender Update List Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Calender Update List Successfully",
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


     public function calenderUpdateList(){
        try {
             $data = [
                'present',
                'absent',
                'halfday',
                'unpaidleave',
                'unpaidhalfday',
                'companyholiday',
                'paidleave',
             ];

             return $this->successResponse(
                $data,
                 "Calender Attendance Update List"
             );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
     }
}
