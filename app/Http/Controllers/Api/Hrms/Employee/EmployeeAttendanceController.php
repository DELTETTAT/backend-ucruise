<?php

namespace App\Http\Controllers\Api\Hrms\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeAttendance;
use App\Models\User;
use Carbon\Carbon;
use DB;
use App\Http\Resources\EmployeeAttendance\EmployeeAttendanceCollection;
use App\Http\Controllers\Api\Hrms\Employee\EmployeeCalenderAttendanceController;
use App\Http\Controllers\Api\Hrms\Payroll\HrmsPayrollController;

class EmployeeAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    /**
     * @OA\post(
     * path="/uc/api/employee_attendace/index",
     * operationId="getemployee_attendace",
     * tags={"Employee Attendance"},
     * summary="Get Employee Attendance Request",
     *   security={ {"Bearer": {} }},
     * description="Get Employee Attendance Request",
     *      @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="employee_id", type="string"),
     *                 @OA\Property(property="date", type="string", format="date", example="2024-04-20"),
     *                 @OA\Property(property="month", type="integer", example=2),
     *                 @OA\Property(property="year", type="integer", example=2024),
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
            $date = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : null;
            $month = $request->month ? Carbon::parse($request->month)->format('m') : null;

            $employee_id = $request->employee_id;
            $user_id = $request->user_id;

            $attendances = EmployeeAttendance:: where('user_id', $user_id)
                                ->when($date, function($q) use ($date) {
                                        $q->whereDate('date', $date);
                                })
                                ->when($month, function($q) use ($month) {
                                      $q->whereMonth('date', $month);
                                })
                                ->get();

             return $this->successResponse(
               new EmployeeAttendanceCollection($attendances, false),
                "Attendance List"
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


    /**
     * @OA\Post(
     * path="/uc/api/employee_attendace/store",
     * operationId="storeemployee_attendace",
     * tags={"Employee Attendance"},
     * summary="Store employee_attendace Request",
     *   security={ {"Bearer": {} }},
     * description="Store employee_attendace Request",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="user_id", type="integer"),
     *              @OA\Property(property="employee_id", type="string" ),
     *              @OA\Property(property="status", type="integer", description="1 for login 0 for logout"),
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="email", type="email"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="employee_attendace Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="employee_attendace Created Successfully",
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
    public function store(Request $request)
    {
        try {
            $request->validate([
              //  'user_id' => 'required',
                'status' => 'required|integer',
            ]);

            $status = $request->status;
            $employee_id = $request->employee_id;
            $user_id = auth('sanctum')->user()->id;
            $currentTime = Carbon:: now();
            $today = Carbon:: today();

            $attendance = EmployeeAttendance::where('user_id', $user_id)
                                            ->where('date', $today)
                                            ->latest()
                                            ->first();

            if ($status == 1) {             // punch in

                $newActivity = [
                        'login_time' => $currentTime->toTimeString(),
                        'logout_time' => null,
                        'ideal_time' => null,
                        'production' => null,
                ] ;

                    if ($attendance) {

                        $activityLog = $attendance->activity_log ?? [];

                        $existingIdealTime = Carbon::parse($attendance->ideal_time) ?? Carbon::parse('00:00:00');
                        $exiatsIdeal = $existingIdealTime->diffInSeconds(Carbon::parse('00:00:00'));

                        if (count($activityLog) > 0) {
                            $lastIndex = count($activityLog) - 1;

                            if(isset($activityLog[$lastIndex]['logout_time'])){
                                $ideal_time =   gmdate('H:i:s', Carbon::parse($activityLog[$lastIndex]['logout_time'])->diffInSeconds($currentTime));
                                $IdealTime = Carbon::parse($ideal_time)->diffInSeconds(Carbon::parse('00:00:00'));
                                $activityLog[$lastIndex]['ideal_time'] = $ideal_time;
                                $totalIdealSeconds = $exiatsIdeal + $IdealTime;

                                $attendance->ideal_time = gmdate('H:i:s', $totalIdealSeconds);
                            };

                            $activityLog[] = $newActivity;
                            $attendance->activity_log = $activityLog;
                            $attendance->save();
                        }

                    }else {
                        $createdAttendance = EmployeeAttendance::create([
                            'employee_id' => $employee_id,
                            'user_id' => $user_id,
                            'date' => $today,
                            'login_time' => $currentTime,
                            'activity_log' => [$newActivity],
                        ]);

                        // this code used for entry in the calender attendance
                        $calenderAttendanceController = new EmployeeCalenderAttendanceController();
                        $calenderAttendanceController->store($createdAttendance);

                        // payrolls entry
                        // $employeePayrolles = new HrmsPayrollController();
                        // $employeePayrolles->store($createdAttendance);

                    }

                return $this->successResponse([], "You Are Attendated");
            }else {                                                              /// punch out

                  if ($attendance) {

                             $existingProduction = Carbon::parse($attendance->production) ?? Carbon::parse('00:00:00');
                             $exiatsProduction = $existingProduction->diffInSeconds(Carbon::parse('00:00:00'));


                             $activityLog = $attendance->activity_log ;
                             if (count($activityLog) > 0) {
                                 $lastIndex = count($activityLog) - 1 ;

                                 $production_seconds = Carbon::parse($activityLog[$lastIndex]['login_time'])->diffInSeconds($currentTime);

                                 $production = gmdate('H:i:s', $production_seconds);

                                 $productionTime = Carbon::parse($production)->diffInSeconds(Carbon::parse('00:00:00'));

                                 if (!isset($activityLog[$lastIndex]['logout_time'])) {
                                    $activityLog[$lastIndex]['logout_time'] = $currentTime->toTimeString();
                                    $activityLog[$lastIndex]['production'] = $production;

                                    $totalProductionSeconds = $exiatsProduction + $productionTime;

                                    $attendance->activity_log = $activityLog;
                                    $attendance->logout_time = $currentTime;
                                    $attendance->production = gmdate('H:i:s',$totalProductionSeconds);
                                    $attendance->save();

                                    return $this->successResponse([], "You Are Logout");

                                }else {
                                    return $this->errorResponse("Please Login First");
                                }


                             }

                  }else {
                      return $this->errorResponse("Please Login First");
                  }

            }

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
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
    public function update(Request $request, $id)
    {
        //
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
     * @OA\post(
     * path="/uc/api/employee_attendace/todayActivity",
     * operationId="gettodayActivity",
     * tags={"Employee Attendance"},
     * summary="Get Employee todayActivity Request",
     *   security={ {"Bearer": {} }},
     * description="Get Employee todayActivity Request",
     *      @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="employee_id", type="string"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Employee todayActivity Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Employee todayActivity Get Successfully",
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

    public function todayActivity(Request $request)
    {
        try {
            $request->validate(['user_id' => 'required']);

            $today = today();

            $user_id = $request->user_id;

             $todayRecords = EmployeeAttendance::select('activity_log')->where('user_id', $user_id)
                                                ->where('date', $today)->get();
                return $this->successResponse(
                    $todayRecords,
                    "Today Activity List"
               );

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }





     /**
     * @OA\post(
     * path="/uc/api/employee_attendace/attendancePerformance",
     * operationId="getattendancePerformance",
     * tags={"Employee Attendance"},
     * summary="Get Employee attendancePerformance Request",
     *   security={ {"Bearer": {} }},
     * description="Get Employee attendancePerformance Request",
     *      @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="employee_id", type="string"),
     *                 @OA\Property(property="filter_flag", type="integer", description=" 1 => weekly, 2 => monthly, 3 => yearly"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Employee attendancePerformance Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Employee attendancePerformance Get Successfully",
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

     public function attendancePerformance(Request $request)
     {
         try {
             $request->validate(['user_id' => 'required|exists:sub_users,id']);

             $filter_flag = isset($request->filter_flag) ?  $request->filter_flag : 1 ;
             $today = Carbon::today();
             $user_id = $request->user_id;

             $startOfWeek = (clone $today)->startOfWeek()->toDateString();
             $endOfWeek = (clone $today)->endOfWeek()->toDateString();

             $startOfMonth = (clone $today)->startOfMonth()->toDateString();
             $endOfMonth = (clone $today)->endOfMonth()->toDateString();

             $startOfYear = (clone $today)->startOfYear()->toDateString();
             $endOfYear = (clone $today)->endOfYear()->toDateString();

             $query = EmployeeAttendance::where('user_id', $user_id);

             if ($filter_flag == 2) {
                $startDate = $startOfMonth;
                $endDate = $endOfMonth;
             }elseif ($filter_flag == 3) {
                $startDate = $startOfYear;
                $endDate = $endOfYear;
             }else {
                $startDate = $startOfWeek;
                $endDate = $endOfWeek;
             }


             $attendancePerformance = EmployeeAttendance:: where('user_id', $user_id)
                                      ->whereBetween('date', [$startDate, $endDate])
                                      ->get()
                                      ->groupBy('date')
                                      ->map(function($entries) {
                                           return [
                                             'date' => $entries->first()->date,
                                             'production' => gmdate("H:i:s", $entries->sum(function ($entry) {
                                                    return strtotime($entry->production) - strtotime('00:00:00');
                                                })),
                                           ];
                                      });

                    $dateRange = [];
                    $start = Carbon::parse($startDate);

                    while ($start <= $endDate) {
                    $dateRange[] = $start->toDateString();

                    $start->addDay();
                    }

                    $formattedData = [];
                    foreach ($dateRange as $date) {
                        $formattedData[] = [
                        'date' => $date,
                        'production' => $attendancePerformance[$date]['production'] ?? "00:00:00",
                        ];
                    }

                 return $this->successResponse(
                     $formattedData,
                     "Attendance Performance List"
                );

         } catch (\Throwable $th) {
             return $this->errorResponse($th->getMessage());
         }
     }





     /**
     * @OA\post(
     * path="/uc/api/employee_attendace/attendanceTimesheet",
     * operationId="attendanceTimesheet",
     * tags={"Employee Attendance"},
     * summary="Get Employee attendanceTimesheet Request",
     *   security={ {"Bearer": {} }},
     * description="Get Employee attendanceTimesheet Request",
     *      @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="employee_id", type="string"),
     *                 @OA\Property(property="date", type="date"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Employee attendanceTimesheet Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Employee attendanceTimesheet Get Successfully",
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


    public function  attendanceTimesheet(Request $request){
            try {
                // $request->validate([
                //     'user_id' => 'required'
                //  ]);

                 //$user_id = $request->user_id;
                 $user_id = auth('sanctum')->user()->id;

                $date = isset($request->date) ? Carbon::parse($request->date)->format('Y-m-d') : Carbon::today()->toDateString();

                $attendanceData = EmployeeAttendance::where('user_id', $user_id)
                                    ->where('date', $date)
                                    ->get()
                                    ->groupBy('date')
                                    ->map(function($entries){
                                        return [
                                            'punch_in' => $entries->first()->login_time,
                                            'punch_out' => $entries->last()->logout_time,
                                            'production' => gmdate("H:i:s", $entries->sum(function ($entry) {
                                                     return strtotime($entry->production) - strtotime('00:00:00');
                                               })),

                                        ];
                                    });

                return $this->successResponse(
                    $attendanceData,
                    "Employee Timesheet"
                ) ;
            } catch (\Exception $ex) {
                 return $this->errorResponse($ex->getMessage());
            }


    }

     /**
     * @OA\post(
     * path="/uc/api/employee_attendace/trackingEmployeeAttendance",
     * operationId="trackingEmployeeAttendance",
     * tags={"Employee Attendance"},
     * summary="Tracking Employee attendance Request",
     *   security={ {"Bearer": {} }},
     * description="Tracking Employee attendance Request",
     *      @OA\Response(
     *          response=201,
     *          description="Tracking Employee attendance Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Tracking Employee attendance Successfully",
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


    public function trackingEmployeeAttendance()
    {
        try {

             $Companies =  User::whereNotNull('database_name')->get();

             foreach ($Companies as $key => $Company) {
                      try {
                            $this->connectDB($Company->database_name);
                            $user_ids = UserInfo::where('assign_pc_status', 1)
                                            ->pluck('user_id')
                                            ->toArray();
                             $DB_name = $Company->database_name;//DB::connection()->getDataBaseName();
                                $curl = curl_init();

                                curl_setopt_array($curl, array(
                                    CURLOPT_URL => 'https://tracker.unifygroup.in/api/hrms/get-shift-history',
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => '',
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 10,
                                    CURLOPT_FOLLOWLOCATION => true,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => 'POST',
                                    CURLOPT_POSTFIELDS => json_encode([
                                        'userId' => $user_ids,
                                        'orgName' => $DB_name,
                                    ]),
                                    CURLOPT_HTTPHEADER => array(
                                        'Content-Type: application/json'
                                    ),
                                ));

                                $response = curl_exec($curl);
                                $err = curl_error($curl);
                                if ($err) {
                                    info("cURL Error #:" . $err);
                                    //return $this->errorResponse("cURL Error: $err");
                                }
                                curl_close($curl);

                                // Usually response is JSON, so decode it
                                $data = json_decode($response, true);
                                foreach ($data as $user) {
                                    $userName = $user['name'] ?? 'Unknown User';
                                    $empId = $user['empId'] ?? null;

                                    if (isset($user['computer']['ShiftHistory']) && is_array($user['computer']['ShiftHistory'])) {
                                        foreach ($user['computer']['ShiftHistory'] as $shift) {
                                            $clockInTime = isset($shift['clockInTime']) ? Carbon::parse($shift['clockInTime'])->setTimezone('Asia/Kolkata')->toTimeString() : null;
                                            $clockOutTime = isset($shift['clockOutTime']) ? Carbon::parse($shift['clockOutTime'])->setTimezone('Asia/Kolkata')->toTimeString() : null;
                                            $activeTime = $shift['activeTime'] ?? 0;
                                            $activeTime = gmdate('H:i:s', $activeTime);
                                            $idleTime = $shift['idleTime'] ?? 0;
                                            $idleTime = gmdate('H:i:s', $idleTime);
                                            $attDate = isset($shift['clockInTime']) ? Carbon::parse($shift['clockInTime'])->setTimezone('Asia/Kolkata') : null;

                                            $newActivity = [
                                                    'login_time' => $clockInTime,
                                                    'logout_time' => $clockOutTime,
                                                    'ideal_time' => $idleTime,
                                                    'production' => $activeTime,
                                            ] ;

                                            $alredyAttendance = EmployeeAttendance::where('user_id', $empId)
                                                                        ->whereDate('date', $attDate)
                                                                        ->latest()
                                                                        ->first();
                                            if (!$alredyAttendance) {
                                                $createdAttendance = EmployeeAttendance::create([
                                                'user_id' => $empId,
                                                'date' => $attDate,
                                                'login_time' => $clockInTime,
                                                'logout_time' => $clockOutTime,
                                                'ideal_time' => $idleTime,
                                                'production' => $activeTime,
                                                //'break' => $production,
                                                'activity_log' => [$newActivity],
                                                ]);

                                                // this code used for entry in the calender attendance
                                                $calenderAttendanceController = new EmployeeCalenderAttendanceController();
                                                $calenderAttendanceController->store($createdAttendance);
                                            }else {
                                             info("Attendance already exists for user: $userName on date: " . $attDate->toDateString());
                                            }

                                        }
                                    } else {
                                    // info("No ShiftHistory found for user: $userName");
                                    }
                                }
                      } catch (\Throwable $th) {
                          info("Error processing company: {$Company->database_name}, Error: " . $th->getMessage());
                          continue; // Skip to the next company if there's an error
                      }
             }

        } catch (\Throwable $th) {
            info("Error in trackingEmployeeAttendance: " . $th->getMessage());
            return $this->errorResponse($th->getMessage());
        }
    }


}
