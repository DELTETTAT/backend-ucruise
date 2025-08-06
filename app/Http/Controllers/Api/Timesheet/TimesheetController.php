<?php

namespace App\Http\Controllers\Api\Timesheet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Holiday;
use App\Models\Reason;
use App\Models\SubUser;
use App\Models\TeamManager;
use App\Models\HrmsEmployeeRole;
use App\Models\HrmsCalenderAttendance;
use App\Models\HrmsRole;
use App\Models\EmployeeSeparation;
use Carbon\Carbon;
use App\Models\Leave;
use App\Models\HrmsTimeAndShift;
use App\Models\SalarySetting;
use App\Models\Resignation;
use App\Models\UserInfo;
use App\Models\EmployeesUnderOfManager;
use App\Models\EmployeeAttendance;
use App\Models\EmailAddressForAttendanceAndLeave;
use DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Employees\EmployeeCollection;

use App\Exports\TimesheetExport;
use App\Mail\timesheetReport;
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use Illuminate\Support\Facades\Storage;

class TimesheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     *     path="/uc/api/timesheet/index",
     *     operationId="timesheet",
     *     tags={"Timesheet"},
     *     summary="Get Timesheet Request",
     *     security={{"Bearer": {}}},
     *     description="Get timesheet Request",
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date"),
     *         description="Start date (YYYY-MM-DD)"
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date"),
     *         description="End date (YYYY-MM-DD)"
     *     ),
     *     @OA\Parameter(
     *         name="department",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by department (employment type)"
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="search"
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *     @OA\Schema(type="string", enum={"days", "hours"}, default="days"),
     *     description="Filter by type: 'days' (default), 'hours'"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Timesheet fetched successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource Not Found"
     *     )
     * )
     */


     public function checkCompanyLeave($date)
     {
         $dayOfWeek = Carbon::parse($date)->dayOfWeek;
         return ($dayOfWeek == Carbon::SATURDAY || $dayOfWeek == Carbon::SUNDAY) ? "Company Leave" : null;
     }


    // public function index(Request $request)
    // {

    //  $query = SubUser::select('id', 'first_name', 'email','employement_type')
    //     ->with(['timesheet', 'leave'])
    //     ->filterByDepartment($request->department)
    //     ->filterBySearch($request->search);
    //    $users = $query->paginate(SubUser::PAGINATE);

    //     // Fetch holidays
    //     $holidays = Holiday::pluck('name', 'date')->toArray();

    //     $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfWeek(); // Monday
    //     $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now()->endOfWeek(); // Sunday

    //     // Ensure endDate is not before startDate
    //     if ($endDate->lt($startDate)) {
    //         return response()->json(['error' => 'Invalid date range.'], 400);
    //     }

    //     $attendanceData = [];

    //     if($request->type ==='days' ){
    //         // If type of flage for days

    //         foreach ($users as $user) {

    //             $leaveDates = [];
    //             foreach ($user->leave as $leave) {
    //                 $leaveStart = Carbon::parse($leave->start_date);
    //                 $leaveEnd = Carbon::parse($leave->end_date);
    //                 while ($leaveStart->lte($leaveEnd)) {
    //                     $leaveDates[$leaveStart->toDateString()] = Reason::where('id', $leave->reason_id)->value('message'); // $leave->reason_id; // Store leave name
    //                     $leaveStart->addDay();
    //                 }
    //             }

    //             $userAttendance = [
    //                 'id' => $user->id,
    //                 'first_name' => $user->first_name,
    //                 'email' => $user->email,
    //                 'department' => $user->employement_type,
    //                 'timesheet' => [],
    //             ];

    //             for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
    //                 $dateString = $date->toDateString();
    //                 $status = $this->checkCompanyLeave($dateString);

    //                 if (isset($holidays[$dateString])) {
    //                     $status = $holidays[$dateString];
    //                     $flage =4; // public holiday leave
    //                 } elseif ($user->timesheet->where('date', $dateString)->first()) {
    //                     $status = 'Present';
    //                     $flage =1; // present
    //                 } elseif (isset($leaveDates[$dateString])) {
    //                     $status = $leaveDates[$dateString]; // Get original leave name
    //                     $flage =5;  // Leave
    //                 } elseif($status) {
    //                     $status = $status;
    //                     $flage =3;  // saturday & sunday company leave
    //                 }else{
    //                     $status ='Absent'; //'Absent';
    //                     $flage =2;  // absent
    //                 }

    //                 // Add attendance to timesheet
    //                 $userAttendance['timesheet'][] = [
    //                     'date' => $dateString,
    //                     'user' => $user->first_name,
    //                     'email' => $user->email,
    //                     'status' => $status,
    //                     'flag' => $flage,
    //                 ];
    //             }

    //             $attendanceData[] = $userAttendance;
    //         }

    //         $attendanceData[]['pagination'] = [
    //             'current_page' => $users->currentPage(),
    //             'first_page_url' => $users->url(1),
    //             'from' => $users->firstItem(),
    //             'last_page' => $users->lastPage(),
    //             'last_page_url' => $users->url($users->lastPage()),
    //             'next_page_url' => $users->nextPageUrl(),
    //             'path' => $users->path(),
    //             'per_page' => $users->perPage(),
    //             'prev_page_url' => $users->previousPageUrl(),
    //             'to' => $users->lastItem(),
    //             'total' => $users->total(),
    //         ];

    //     }else{

    //         // If type of flage for hours
    //         $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfWeek();
    //         $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now()->endOfWeek();

    //         $dateRange = collect();
    //         $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
    //         foreach ($period as $date) {
    //             $dateRange->push($date->format('Y-m-d'));
    //         }

    //         // Query Users with Timesheet Relationship
    //         $users = SubUser::select('id', 'first_name', 'email', 'employement_type')
    //             ->with([
    //                 'timesheet' => function ($query) use ($startDate, $endDate) {
    //                     $query->whereBetween('date', [$startDate, $endDate])
    //                         ->selectRaw('user_id, date, MIN(login_time) as login_time, MAX(logout_time) as logout_time')
    //                         ->groupBy('user_id', 'date');
    //                 }
    //             ])
    //             ->filterByDepartment($request->department)
    //             ->filterBySearch($request->search)
    //             ->paginate(SubUser::PAGINATE);

    //             $users->transform(function ($user) use ($dateRange) {
    //                 $existingDates = $user->timesheet->pluck('date')->toArray();

    //                 // Fill missing dates with NULL values
    //                 foreach ($dateRange as $date) {
    //                     if (!in_array($date, $existingDates)) {
    //                         $user->timesheet->push((object) [
    //                             'user_id' => $user->id,
    //                             'date' => $date,
    //                             'login_time' => null,
    //                             'logout_time' => null,
    //                             'flage'=> 1,
    //                         ]);
    //                     }
    //                 }

    //                 // Set flage = 1 where login_time and logout_time exist
    //                 $user->timesheet = $user->timesheet->map(function ($timesheet) {
    //                     if ($timesheet->login_time !== null && $timesheet->logout_time !== null) {
    //                         $timesheet->flage = 1; // Both login and logout exist
    //                     } elseif ($timesheet->login_time !== null && $timesheet->logout_time === null) {
    //                         $timesheet->flage = 6; // Login exists but logout is missing
    //                     }elseif ($timesheet->login_time == null && $timesheet->logout_time !== null) {
    //                         $timesheet->flage = 6; // Login missing but logout is exists
    //                     } else {
    //                         $timesheet->flage = 2; // Neither login nor logout exist
    //                     }
    //                     return $timesheet;
    //                 });

    //                 $user->setRelation('timesheet', collect($user->timesheet)->sortBy('date')->values());
    //                 return $user;
    //             });

    //         $attendanceData[] = $users;

    //     }

    //     return response()->json([
    //         'data' => $attendanceData
    //     ]);

    // }

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
    public function store(Request $request)
    {
        //
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
     * path="/uc/api/timesheet/timesheet",
     * operationId="gettimesheet",
     * tags={"Timesheet"},
     * summary="Get payrolls Request",
     *   security={ {"Bearer": {} }},
     * description="Get payrolls Request",
     *      @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *                 @OA\Property(property="date", type="date"),
     *                 @OA\Property(property="search", type="string", description="Searching by employee name or employee Id"),
     *                 @OA\Property(property="manager_id", type="integer", description="Please fill Team Manager id"),
     *                 @OA\Property(property="timesheet_type", type="integer", description="Timesheet type like ActiveTimesheet => 1, ResignedTimesheet => 2"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description=" Payrolls Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Payrolls Get Successfully",
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

     public function timesheet(Request $request)
     {
           try {

                $validator = Validator::make($request->all(), [
                    'manager_id' => 'required|integer|exists:team_managers,id',
                    'timesheet_type' => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'errors' => $validator->errors()
                    ], 422);
                }

               $month = $request->date ? Carbon::parse($request->date)->format('m') : now()->format('m');
               $days_in_month = Carbon::parse($request->date)->daysInMonth;
               $search = $request->search;
               $startDate = $request->date ?? now()->format('Y-m-d');
               $startDate = Carbon::parse($startDate)->startOfMonth();
               $endDate = $request->date ? Carbon::parse($request->date)->startOfMonth() : now();



               $user_ids = [];

              $getManagerList = TeamManager::with(['employees','teams.teamLeader', 'teams.teamMembers.user'])->find($request->manager_id);

               foreach ($getManagerList->employees as $key => $employee) {
                  $user_ids[] = $employee->id;
               }
               foreach ($getManagerList->teams as $key => $team) {
                $user_ids[] =  (int) $team->team_leader;

                  foreach ($team->teamMembers as $key => $member) {
                    //$user_ids[] = $member->user->id;
                    if ($member->user && $member->user->id !== null) {
                        $user_ids[] = $member->user->id;
                    }
                  }
               }


            //    $users = SubUser::select('id', 'first_name', 'last_name', 'email', 'employement_type', 'unique_id')->whereIn('id',$user_ids)
            //                          ->FilterBySearch($search)
            //                          ->with('payrolls')
            //                          ->where('user_type',"0")
            //                          ->where('status', 1)
            //                          ->paginate(SubUser::PAGINATE);



                $data = [];

                $start = $request->date ? Carbon::parse($request->date)->startOfMonth() : now()->startOfMonth();
                $end = $request->date ? (Carbon::parse($request->date)->isCurrentMonth() ? today() : Carbon::parse($request->date)->endOfMonth()) : today();


                //******  accourding timesheet type  *****/
                $status = 3;
                if ($request->timesheet_type == "ActiveTimesheet") {
                   $status = 1;
                }elseif ($request->timesheet_type == "ResignedTimesheet") {
                   $status = 3;
                }
                $users = SubUser::select('id', 'first_name', 'last_name', 'email', 'employement_type', 'unique_id')->whereIn('id',$user_ids)
                                     ->FilterBySearch($search)
                                     ->with('payrolls')
                                     ->where('user_type',"0")
                                     ->where('status', $status)
                                     ->paginate(SubUser::PAGINATE);

                if ($status == 3) {
                    $salaySetting = SalarySetting::first();
                    if ($salaySetting) {
                        $notice_period_days = $salaySetting->notice_period_days;
                        $salary_process_after_in_days = $salaySetting->salary_process_after_in_days;
                        $clear_salary = $salaySetting->clear_salary;    //  Salary is cleared every month if set, 1 for clear, 0 for not clear
                        $hold_one_month_salary = $salaySetting->hold_one_month_salary;
                        $clear_salary_after_notice = $salaySetting->clear_salary_after_notice;
                    }
                }
                foreach ($users as $user) {

                    /////////

                if($status == 3){

                               $reginationData = Resignation::where('user_id',$user->id)->where('status', 1)->first();
                               $user_info = UserInfo::where('user_id',$user->id)->first();
                               //$resignDate = Carbon::parse($reginationData->accept_or_reject_date_of_resignation);
                               if ($reginationData && $reginationData->accept_or_reject_date_of_resignation) {
                                    $resignDate = Carbon::parse($reginationData->accept_or_reject_date_of_resignation);
                                } else {
                                    continue;
                                }

                               $date = $request->date ?? now()->format('Y-m-d');
                               $date = Carbon::parse($date);
                               $daysDiff = $resignDate->diffInDays($date);
                               //$notice_priod_cleared_date = $resignDate->addDays($notice_period_days);
                               //info('notice_priod_cleared_date...'.$notice_priod_cleared_date->format('Y-m-d'));


                               // user info data  only this employee
                               $clear_salary2 = $user_info->clear_salary;
                               $hold_one_month_salary2 = $user_info->hold_one_month_salary;
                               $clear_salary_after_notice2 = $user_info->clear_salary_after_notice;

                               if ($notice_period_days >= $daysDiff) {
                                    if ($clear_salary != 1 || (isset($clear_salary2) && $clear_salary2 != 1)) {   // pay salary every month
                                        continue;
                                    }elseif (($hold_one_month_salary != 1 || (isset($hold_one_month_salary2) && $hold_one_month_salary2 != 1)) && $date->copy()->addMonth()->format('Y-m') != $resignDate->format('Y-m')) {
                                        continue;
                                    }elseif (($clear_salary_after_notice != 1 || (isset($clear_salary_after_notice2) && $clear_salary_after_notice2 != 1)) && $notice_period_days <= $daysDiff) {
                                        continue;
                                    }
                               }

                    }
                // Get non-working days from shift
                // $shift_name = $user->employee_shift;
                // $shift = HrmsTimeAndShift::where('shift_name', $shift_name)->first();
                // if (!$shift) {
                //     $shift = HrmsTimeAndShift::where('shift_name', $shift_name)->first();
                // }

                // $shiftDays = $shift->shift_days; // assuming it's like ['SUN' => 0, 'MON' => 1, ...]
                //     // Get company off-days (like SUN, SAT)
                // return    $companyOffDays = collect($shiftDays)->filter(fn($value) => $value == "0")->keys()->toArray();



                // //////


                $companyHoliday = 0;
                while ($start->lte($end)) {

                    if ($start->isSaturday()) {
                        $companyHoliday += 1;
                    }
                    if ($start->isMonday()) {
                        $companyHoliday += 1;
                    }
                    $start->addDay();
                }


                $attendanceRecords = HrmsCalenderAttendance::where('user_id', $user->id)
                                        ->whereMonth('date', $month)
                                        ->where('status', 'present')
                                        ->get();
                $paid_leaves = HrmsCalenderAttendance::where('user_id', $user->id)
                                        ->whereMonth('date', $month)
                                        ->where('status','paidleave',)
                                        ->get();
                $halfday = HrmsCalenderAttendance::where('user_id', $user->id)
                                        ->whereMonth('date', $month)
                                        ->where('status','halfday',)
                                        ->get();
                $unpaid_halfday = HrmsCalenderAttendance::where('user_id', $user->id)
                                        ->whereMonth('date', $month)
                                        ->where('status','unpaidhalfday',)
                                        ->get();
                $leaves = Leave::where('staff_id', $user->id)
                            ->where(function($query) use ($startDate, $endDate) {
                                $query->whereBetween('start_date', [$startDate, $endDate])
                                    ->orWhereBetween('end_date', [$startDate, $endDate]);
                            })->where('status',1)
                            ->get();

                $leaveDates = [];

                foreach ($leaves as $leave) {
                    $leaveStart = Carbon::parse($leave->start_date);
                    $leaveEnd = Carbon::parse($leave->end_date);

                    while ($leaveStart->lte($leaveEnd)) {
                        if ($leaveStart->format('Y-m') === $startDate->format('Y-m')) {
                            $leaveDates[] = $leaveStart->format('Y-m-d');
                        }
                        $leaveStart->addDay();
                    }
                }

                   $paidAttendanceDates = $attendanceRecords->pluck('date')->unique();

                   $paid_leaves = $paid_leaves->pluck('date')->unique();
                   $paid_leaves = $paid_leaves->merge($leaveDates);
                   $paid_leaves = $paid_leaves->unique()->values();

                   $paid_leaves = $paid_leaves->diff($paidAttendanceDates)->values();

                   $halfday = $halfday->pluck('date')->unique();
                   //$halfday_count = $halfday->count() / 2;
                    // Public holidays
                   $unpaid_halfday = $unpaid_halfday->pluck('date')->unique();
                   $unpaid_halfday_count = $unpaid_halfday->count() / 2;

                   $publicHolidays = Holiday::whereMonth('date', $month)->count();

                   $paidDays = $paidAttendanceDates->count() + $publicHolidays + $companyHoliday + $paid_leaves->count() + $halfday->count() + $unpaid_halfday_count; //$halfday_count;

                    $data[] = [
                        'id' => $user->id,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'email' => $user->email,
                        'unique_id' => $user->unique_id,
                        'halfDays' => $halfday->count(),
                        'unpaidLeave' => HrmsCalenderAttendance::where('user_id', $user->id)->where('status', 'unpaidleave')->whereMonth('date', $month)->count(),
                        'unpaidHalfDay' => $unpaid_halfday->count(), //HrmsCalenderAttendance::where('user_id', $user->id)->where('status', 'unpaidhalfday')->whereMonth('date', $month)->count(),
                        'paidLeave' => $paid_leaves->count(),
                        'present' =>  $paidAttendanceDates->count(),
                        'PublicHolidays' => $publicHolidays,
                        'CompanyHolidays' => $companyHoliday,
                        'totalPaidDays' => $paidDays,
                    ];
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Timesheet data fetched successfully.',
                    'data' => $data,
                    'pagination' => [
                        'total' => $users->total(),
                        'current_page' => $users->currentPage(),
                        'last_page' => $users->lastPage(),
                        'per_page' => $users->perPage(),
                        'next_page_url' => $users->nextPageUrl(),
                        'prev_page_url' => $users->previousPageUrl(),
                    ]
                ]);


           } catch (\Exception $ex) {
                //return $this->errorResponse($ex->getMessage());
                return $this->errorResponse(sprintf(
                    '%s in %s on line %d',
                    $ex->getMessage(),
                    $ex->getFile(),
                    $ex->getLine()
                ));
           }
     }




    /**
     * @OA\Post(
     * path="/uc/api/timesheet/authTimesheet",
     * operationId="auth user timesheet",
     * tags={"Timesheet"},
     * summary="Get Login user timesheet",
     * security={ {"Bearer": {} }},
     * description="Get Login user timesheet",
     *      @OA\Response(
     *          response=201,
     *          description="Login user timesheet Retrieved Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Login user timesheet Retrieved Successfully",
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


    // public function authTimesheet(Request $request){
    //      try {

    //         $month = $request->date ? Carbon::parse($request->date)->format('m') : now()->format('m');
    //         $date = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : now()->format('Y-m-d');
    //         $user_id = auth('sanctum')->user()->id;


    //         $getTeamMembers = SubUser::whereHas('hrmsroles.viewrole', function ($query) {
    //             $query->where('name', 'Employee View');
    //         })->with(['hrmsroles.viewrole' => function ($query) {
    //             $query->where('name', 'Employee View');
    //         }])->get();


    //         $data = [];

    //         $start = $request->date ? Carbon::parse($request->date)->startOfMonth() : now()->startOfMonth();
    //         $end = $request->date
    //                  ? (Carbon::parse($request->date)->isCurrentMonth()
    //                  ? today()
    //                  : Carbon::parse($request->date)->endOfMonth())
    //                  : today();

    //         $companyHoliday = 0;
    //         while ($start->lte($end)) {

    //             if ($start->isSaturday()) {
    //                 $companyHoliday += 1;
    //             }
    //             if ($start->isSaturday()) {
    //                 $companyHoliday += 1;
    //             }
    //             $start->addDay();
    //         }

    //         //// use for super admin
    //         $auth_role = DB::table('role_user')->where('user_id',$user_id)->first();
    //         $employeeRole = DB::table('roles')->find($auth_role->role_id);


    //         /// use for admin view
    //         $role =   HrmsEmployeeRole::where('employee_id', $user_id)->first();
    //         if ($role) {
    //            // return $this->errorResponse("This user has no assigned role.");
    //             $authRole = HrmsRole::with('viewrole')->where('id', $role->role_id)->first();
    //             $viewRole = $authRole->viewrole->name;
    //         }else {
    //            $viewRole = 'Employee View';
    //         }


    //        // return $authRole->viewrole->name;

    //         if ($employeeRole->name == "admin" || $viewRole  == 'Admin View') {
    //             $listTeamManager = TeamManager::with('employees')->get();

    //             $userIds = [];
    //             foreach ($listTeamManager as $key => $manager) {
    //                 foreach ($manager->employees as $employee) {
    //                     $userIds[] = $employee->id;
    //                 }
    //             }

    //             $users = SubUser::whereIn('id', $userIds)->with('teamManagers')->get();
    //         }
    //         else {
    //             $users = SubUser::where('id', $user_id)->with('teamManagers')->get();
    //         }


    //         $groupedData = [];
    //         $teamManagers = TeamManager::with('employees')->get();

    //         /////
    //         //$resignedUsers = SubUser::where('id', $user_id)->with('teamManagers')->get();
    //         $salaySetting = SalarySetting::first();
    //         if ($salaySetting) {
    //             $notice_period_days = $salaySetting->notice_period_days;
    //             $salary_process_after_in_days = $salaySetting->salary_process_after_in_days;
    //             $clear_salary = $salaySetting->clear_salary;    //  Salary is cleared every month if set, 1 for clear, 0 for not clear
    //             $hold_one_month_salary = $salaySetting->hold_one_month_salary;
    //             $clear_salary_after_notice = $salaySetting->clear_salary_after_notice;
    //         }
    //         //$timesheeetType = ['ActiveTimesheet', 'ResignedTimesheet'];

    //         /////

    //         foreach ($teamManagers as $manager) {
    //             if (!isset($groupedData[$manager->id])) {
    //                 $groupedData[$manager->id] = [
    //                     'manager_id' => $manager->id,
    //                     'manager_name' => $manager->name,
    //                     'employees' => []
    //                 ];

    //                 $resignedUsers = EmployeesUnderOfManager::where('manager_id', $manager->id)
    //                                     ->whereHas('employee', function($q) {
    //                                         $q->where('status', 3);
    //                                     })
    //                                    // ->with('employee')
    //                                     ->get();

    //                 $timesheeetType = [];
    //                 $timesheeetType[] = (object) ['type' => 'ActiveTimesheet', 'status' => 'Approved'];

    //                 if(isset($resignedUsers)){
    //                       foreach ($resignedUsers as $key => $user) {
    //                            $reginationData = Resignation::where('user_id',$user->employee_id)->where('status', 1)->first();
    //                            $user_info = UserInfo::where('user_id',$user->employee_id)->first();
    //                            //$resignDate = Carbon::parse($reginationData->accept_or_reject_date_of_resignation);
    //                            if ($reginationData) {
    //                                 $notice_served_date = Carbon::parse($reginationData->notice_served_date);
    //                                 $last_working_date = Carbon::parse($reginationData->last_working_date);
    //                             } else {
    //                                 continue;
    //                             }
    //                            $date = Carbon::parse($date);
    //                            //$notice_priod_cleared_date = $resignDate->addDays($notice_period_days);
    //                            $daysDiff = $resignDate->diffInDays($date);

    //                            // user info data  only this employee
    //                            $clear_salary2 = $user_info->clear_salary;
    //                            $hold_one_month_salary2 = $user_info->hold_one_month_salary;
    //                            $clear_salary_after_notice2 = $user_info->clear_salary_after_notice;
    //                            $notice_priod_cleared_date = $resignDate->copy()->addDays($notice_period_days);

    //                             // 1. Salary clear every month
    //                             if ($clear_salary == 1 || (isset($clear_salary2) && $clear_salary2 == 1)) {
    //                                 $timesheeetType[] = (object) ['type' => 'ResignedTimesheet', 'status' => 'Approved'];
    //                             }
    //                             // 2. Salary hold for one month
    //                             elseif ($hold_one_month_salary == 1 || (isset($hold_one_month_salary2) && $hold_one_month_salary2 == 1)) {
    //                                 $resignMonth = $resignDate->format('Y-m');
    //                                 $holdMonth = $resignDate->copy()->subMonth()->format('Y-m');
    //                                 $backMonth = $resignDate->copy()->subMonth(2)->format('Y-m');
    //                                 info('holdMonth...'.$holdMonth);
    //                                 // Current month
    //                                 $currentMonth = $date->format('Y-m');

    //                                 if ($currentMonth == $holdMonth) {
    //                                     $timesheeetType[] = (object) ['type' => 'ResignedTimesheet', 'status' => 'Approved'];
    //                                 }

    //                             }
    //                             elseif ($hold_one_month_salary == 0 || (isset($hold_one_month_salary2) && $hold_one_month_salary2 == 0)) {
    //                                 if ($currentMonth == $holdMonth) {
    //                                     $timesheeetType[] = (object) ['type' => 'ResignedTimesheet', 'status' => 'Pending'];
    //                                 }

    //                             }elseif ($notice_priod_cleared_date->lt($date) && ($clear_salary_after_notice == 1 || (isset($clear_salary_after_notice2) && $clear_salary_after_notice2 == 1))) {
    //                                 # code...
    //                             }


    //                       }
    //                 }

    //             }

    //             foreach ($manager->employees as $employee) {
    //                 $groupedData[$manager->id]['employees'][] = [
    //                     'id' => $employee->id,
    //                     'name' => $employee->first_name . ' ' . $employee->last_name,
    //                     'email' => $employee->email,
    //                     'unique_id' => $employee->unique_id,
    //                 ];
    //             }


    //             foreach ($timesheeetType as $key => $type) {
    //                  $groupedData[$manager->id]['TimesheetType'][] = $type;
    //             }
    //         }



    //         return $this->successResponse(
    //             array_values($groupedData),
    //             "TimeSheet List Grouped by Manager"
    //         );


    //     //     foreach ($users as $user) {

    //     //         $attendanceRecords = HrmsCalenderAttendance::where('user_id', $user->id)
    //     //          ->whereMonth('date', $month)
    //     //          ->whereIn('status', ['present', 'paidleave', 'halfday'])
    //     //          ->get();

    //     //         $paidAttendanceDates = $attendanceRecords->pluck('date')->unique();
    //     //          // Public holidays
    //     //         $publicHolidays = Holiday::whereMonth('date', $month)->count();

    //     //         $paidDays = $paidAttendanceDates->count() + $publicHolidays + $companyHoliday;

    //     //          $data[] = [
    //     //              'id' => $user->id,
    //     //              'name' => $user->first_name . ' ' . $user->last_name,
    //     //              'email' => $user->email,
    //     //              'unique_id' => $user->unique_id,
    //     //             //  'halfDays' => HrmsCalenderAttendance::where('user_id', $user->id)->where('status', 'halfday')->whereMonth('date', $month)->count(),
    //     //             //  'unpaidLeave' => HrmsCalenderAttendance::where('user_id', $user->id)->where('status', 'unpaidleave')->whereMonth('date', $month)->count(),
    //     //             //  'unpaidHalfDay' => HrmsCalenderAttendance::where('user_id', $user->id)->where('status', 'unpaidhalfday')->whereMonth('date', $month)->count(),
    //     //             //  'paidLeave' => HrmsCalenderAttendance::where('user_id', $user->id)->where('status', 'paidleave')->whereMonth('date', $month)->count(),
    //     //             //  'present' => HrmsCalenderAttendance::where('user_id', $user->id)->where('status', 'present')->whereMonth('date', $month)->count(),
    //     //             //  'PublicHolidays' => $publicHolidays,
    //     //             //  'CompanyHolidays' => $companyHoliday,
    //     //             //  'totalPaidDays' => $paidDays,
    //     //              'Manager' => $user->teamManagers,
    //     //              'name' => $user->manager_teams

    //     //          ];
    //     //      }

    //     //    return  $this->successResponse(
    //     //        $data,
    //     //        "TimeSheet List"
    //     //    );
    //      } catch (\Throwable $th) {
    //         return $this->errorResponse($th->getMessage());
    //      }
    // }


      public function authTimesheet(Request $request){
         try {

            $month = $request->date ? Carbon::parse($request->date)->format('m') : now()->format('m');
            $date = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : now()->format('Y-m-d');
            $user_id = auth('sanctum')->user()->id;


            $getTeamMembers = SubUser::whereHas('hrmsroles.viewrole', function ($query) {
                $query->where('name', 'Employee View');
            })->with(['hrmsroles.viewrole' => function ($query) {
                $query->where('name', 'Employee View');
            }])->get();


            $data = [];

            $start = $request->date ? Carbon::parse($request->date)->startOfMonth() : now()->startOfMonth();
            $end = $request->date
                     ? (Carbon::parse($request->date)->isCurrentMonth()
                     ? today()
                     : Carbon::parse($request->date)->endOfMonth())
                     : today();

            $companyHoliday = 0;
            while ($start->lte($end)) {

                if ($start->isSaturday()) {
                    $companyHoliday += 1;
                }
                if ($start->isSaturday()) {
                    $companyHoliday += 1;
                }
                $start->addDay();
            }

            //// use for super admin
            $auth_role = DB::table('role_user')->where('user_id',$user_id)->first();
            $employeeRole = DB::table('roles')->find($auth_role->role_id);


            /// use for admin view
            $role =   HrmsEmployeeRole::where('employee_id', $user_id)->first();
            if ($role) {
               // return $this->errorResponse("This user has no assigned role.");
                $authRole = HrmsRole::with('viewrole')->where('id', $role->role_id)->first();
                $viewRole = $authRole->viewrole->name;
            }else {
               $viewRole = 'Employee View';
            }


           // return $authRole->viewrole->name;

            if ($employeeRole->name == "admin" || $viewRole  == 'Admin View') {
                $listTeamManager = TeamManager::with('employees')->get();

                $userIds = [];
                foreach ($listTeamManager as $key => $manager) {
                    foreach ($manager->employees as $employee) {
                        $userIds[] = $employee->id;
                    }
                }

                $users = SubUser::whereIn('id', $userIds)->with('teamManagers')->get();
            }
            else {
                $users = SubUser::where('id', $user_id)->with('teamManagers')->get();
            }


            $groupedData = [];
            $teamManagers = TeamManager::with('employees')->get();

            /////
            //$resignedUsers = SubUser::where('id', $user_id)->with('teamManagers')->get();
            $salaySetting = SalarySetting::first();
            if ($salaySetting) {
                $notice_period_days = $salaySetting->notice_period_days;
                $salary_process_after_in_days = $salaySetting->salary_process_after_in_days;
                $clear_salary = $salaySetting->clear_salary;    //  Salary is cleared every month if set, 1 for clear, 0 for not clear
                $hold_one_month_salary = $salaySetting->hold_one_month_salary;
                $clear_salary_after_notice = $salaySetting->clear_salary_after_notice;
            }
            //$timesheeetType = ['ActiveTimesheet', 'ResignedTimesheet'];

            /////

            foreach ($teamManagers as $manager) {
                if (!isset($groupedData[$manager->id])) {
                    $groupedData[$manager->id] = [
                        'manager_id' => $manager->id,
                        'manager_name' => $manager->name,
                        'employees' => []
                    ];

                    $resignedUsers = EmployeesUnderOfManager::where('manager_id', $manager->id)
                                        ->whereHas('employee', function($q) {
                                           // $q->where('status', 3);
                                        })
                                       // ->with('employee')
                                        ->pluck('employee_id');

                    $timesheeetType = [];
                    $timesheeetType[] = (object) ['type' => 'ActiveTimesheet', 'status' => 'Approved'];

                    if(isset($resignedUsers) && !empty($resignedUsers)){
                        //  foreach ($resignedUsers as $key => $user) {
                               $reginationData = Resignation::whereIn('user_id',$resignedUsers)->where('status', 1)->get();
                               if ($reginationData->isNotEmpty()) {
                                    foreach ($reginationData as $key => $user) {
                                         $data = [
                                            'notice_served_date' => Carbon::parse($user->notice_served_date),
                                            'last_working_date' => Carbon::parse($user->last_working_date),
                                            'clear_salary' => @$clear_salary,
                                            'hold_one_month_salary' => $hold_one_month_salary,
                                            'clear_salary_after_notice' => $clear_salary_after_notice,
                                            'notice_period_days' => $notice_period_days,
                                            'salary_process_after_in_days' => $salary_process_after_in_days,
                                         ];

                                         $timesheeetType[] = (object) $this->employeeTimesheetStatus($user->user_id,$data,$month);
                                    }
                               }

                    }

                }

                foreach ($manager->employees as $employee) {
                    $groupedData[$manager->id]['employees'][] = [
                        'id' => $employee->id,
                        'name' => $employee->first_name . ' ' . $employee->last_name,
                        'email' => $employee->email,
                        'unique_id' => $employee->unique_id,
                    ];
                }


                foreach ($timesheeetType as $key => $type) {
                     $groupedData[$manager->id]['TimesheetType'][] = $type;
                }
            }



            return $this->successResponse(
                array_values($groupedData),
                "TimeSheet List Grouped by Manager"
            );

         } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
         }
    }


    public function employeeTimesheetStatus($userID,$data,$month){
            try {
                   $last_working_date = "";//$data['notice_period_days'];
                   $userInfo = UserInfo::where('user_id',$userID)->first();
                   $clear_salary_every_month = $userInfo->clear_salary ?? $data['clear_salary'];
                   $hold_one_month_salary = $userInfo->hold_one_month_salary ?? $data['hold_one_month_salary'];
                   $clear_salary_after_notice = $userInfo->clear_salary_after_notice ?? $data['clear_salary_after_notice'];
                   $notice_served_date =  Carbon::parse($data['notice_served_date']);
                   $last_working_date = Carbon::parse($data['last_working_date']) ?? $notice_served_date->copy()->addDays($data['notice_period_days']);

                   if ($clear_salary_every_month == 1) {
                         if (Carbon::parse($month)->between($notice_served_date,$last_working_date)) {
                              return  ['type' => 'ResignedTimesheet', 'status' => 'Approved'];
                         }
                   }
                   if ($hold_one_month_salary == 0) {
                         if ($notice_served_date->day > 10 || $notice_served_date->month == Carbon::parse($month)->month) {
                              return  ['type' => 'ResignedTimesheet', 'status' => 'Approved'];
                         }
                   }
                   if ($clear_salary_after_notice == 1) {
                        if ($last_working_date->month == Carbon::parse($month)->month) {
                            return  ['type' => 'ResignedTimesheet', 'status' => 'Approved'];
                        }
                   }

            } catch (\Throwable $th) {
                return $this->errorResponse($th->getMessage());
            }
    }


    /**
     * @OA\Post(
     * path="/uc/api/timesheet/timeSheetReport",
     * operationId="timeSheet Report",
     * tags={"Timesheet"},
     * summary="timeSheet Reportt to send HR",
     * security={ {"Bearer": {} }},
     * description="timeSheet Reportt to send HR",
     *       @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *                 @OA\Property(property="download", type="integer"),
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="month", type="date"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="timeSheet Reportt to send HR Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="timeSheet Reportt to send HR Successfully",
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


     public function timeSheetReport(Request $request){
         try {

              $validated = $request->validate([
                  'download' => 'nullable|integer|in:0,1',   // 1 => all employee timesheet download (only one company), 0 => using for cron job all company sent timesheet attach email
                  'user_id' => 'nullable|integer|exists:sub_users,id',   // using if only single employee download timesheet
                  'month' => 'nullable|date',     // using if only single employee download timesheet
              ]);

            $default_DBName = env("DB_DATABASE");
            $this->connectDB($default_DBName);

            $date = $request->month ? Carbon::parse($request->month)->format('Y-m-d') : now()->format('Y-m-d');

            if ($request->download == 0) {
                //$companies = User::whereNotNull('database_name')->get();      used permantly
                $companies = User::whereNotNull('database_name')->where('database_name', 'UC_unifytechsolutions')->get();
            }else {
                if (isset($request->user_id)) {
                    $user_id = $request->user_id;
                    $company = SubUser::whereNotNull('database_name')->find($user_id); // returns single model
                    $companies = $company ? [$company] : [];
                }else {
                    $user = auth('sanctum')->user();
                    $companies = $user ? [$user] : [];
                }

            }

            foreach ($companies as  $company) {
                 $databaseName = $company->database_name;
                 $this->connectDB($databaseName);

                $month = $request->month ? Carbon::parse($request->month)->format('m') : now()->format('m');

                $baseQuery  = SubUser::select('id', 'first_name', 'last_name', 'email', 'employement_type', 'unique_id','status')
                                                    ->where('user_type',"0");

                if (isset($request->user_id)) {
                    // $separation_users = $query->whereIn('status', [2,3,4,5,6,7])->where('id',$request->user_id)->get(); // 2 => Inactive, 5 => Suspended, 6 => Terminated, 7 => Deceased
                    // $attendance_users = $query->with('payrolls')->whereIn('status', [1,3,4])->where('id',$request->user_id)->get(); //1 => Active, 3 => Resigned, 4 => On Notice Period,"


                     $separation_users = (clone $baseQuery)->whereIn('status', [2,3,4,5,6,7,8])->where('id', $request->user_id)->get();
                     $attendance_users = (clone $baseQuery) ->with('payrolls')->whereIn('status', [1,3,4])->where('id', $request->user_id)->get();
                }else {
                    // $separation_users = $query->whereIn('status', [2,3,4,5,6,7])->get();
                    // $attendance_users = $query->with('payrolls')->whereIn('status', [1,3,4])->get();

                     $separation_users = (clone $baseQuery)->whereIn('status', [2,3,4,5,6,7,8])->get();
                     $attendance_users = (clone $baseQuery)->with('payrolls')->whereIn('status', [1,3,4])->get();
                }


                $data = [];

                foreach ($attendance_users as $user) {

                    $dailyStatus = [];
                    $startMonth = $request->month ? Carbon::parse($request->month)->startOfMonth() : now()->startOfMonth();
                    $endMonth = $request->month ? Carbon::parse($request->month)->endOfMonth() : now()->endOfMonth();

                    // ***** count Company Holiday

                        $time_shift =  HrmsTimeAndShift::first();
                        $employee_shift = $user->employee_shift ?? $time_shift->shift_name ;

                        $employee_shift_time = HrmsTimeAndShift::where('shift_name', $employee_shift)->first();
                        $shift_days = $employee_shift_time->shift_days;

                        $companyHolidayDays = collect($shift_days)->filter(function ($value) {
                            return $value == "0";
                        })->keys()->map(function ($day) {
                            return strtoupper($day); // optional agar keys already "SUN", "SAT" me hain
                        })->toArray();

                        $start =  $request->month ? Carbon::parse($request->month)->startOfMonth() : now()->startOfMonth();
                        $end = $request->month ? Carbon::parse($request->month)->endOfMonth() : now();
                        if ($end->gt(now())) {
                            $end = now();
                        }

                        $companyHoliday = 0;
                        while ($start->lte($end)) {

                            $currentDay = strtoupper($start->format('D')); // Like "SUN", "MON", etc.

                            if (in_array($currentDay, $companyHolidayDays)) {
                                $companyHoliday += 1;
                            }

                            $start->addDay();
                        }
                    // ***** count Company Holiday

                    while ($startMonth->lte($endMonth)) {
                        $statusRecord = HrmsCalenderAttendance::where('user_id', $user->id)
                                                ->whereDate('date', $startMonth->format('Y-m-d'))
                                                ->first();

                        $leaves = Leave::where('staff_id', $user->id)->whereDate('start_date', '<=', $startMonth->format('Y-m-d'))
                                        ->whereDate('end_date', '>=', $startMonth->format('Y-m-d'))->where('status', 1)->first();

                        $absent = EmployeeAttendance::where('user_id', $user->id)->whereDate('date', $startMonth->format('Y-m-d'))->first();

                            $status = '';

                             if (!$absent) {
                                $status = 'ABS';
                             }

                            if ($statusRecord) {
                                if ($statusRecord->status == "present") {
                                    $status = 'P';
                                } elseif ($statusRecord->status == "paidleave") {
                                    $status = 'PL';
                                } elseif ($statusRecord->status == "halfday") {
                                    $status = 'HD';
                                } elseif ($statusRecord->status == "unpaidleave") {
                                    $status = 'UPL';
                                } elseif ($statusRecord->status == "unpaidhalfday") {
                                    $status = 'UHD';
                                } else {
                                    $status = strtoupper(substr($statusRecord->status, 0, 2));
                                }
                            }

                            // if (!$absent) {
                            //     $status = 'ABS';
                            // }

                            if ($leaves) {
                                $status = 'PL';
                            }

                            if ($startMonth->isSaturday() || $startMonth->isSunday()) {
                                $status = 'CH';
                            }

                            $dailyStatus[$startMonth->format('Y-m-d')] = $status;
                            $startMonth->addDay();


                    }
                    ////////////////

                $attendanceRecords = HrmsCalenderAttendance::where('user_id', $user->id)
                                            ->whereMonth('date', $month)
                                            ->where('status', 'present')
                                            ->get();
                $paid_leaves = HrmsCalenderAttendance::where('user_id', $user->id)
                                            ->whereMonth('date', $month)
                                            ->where('status','paidleave',)
                                            ->get();
                $halfday = HrmsCalenderAttendance::where('user_id', $user->id)
                                            ->whereMonth('date', $month)
                                            ->where('status','halfday',)
                                            ->get();
                $unpaid_halfday = HrmsCalenderAttendance::where('user_id', $user->id)
                                            ->whereMonth('date', $month)
                                            ->where('status','unpaidhalfday',)
                                            ->get();


                    // ** Leave counting
                    $startDate = $request->month ? Carbon::parse($request->month)->startOfMonth()->format('Y-m-d') : now()->startOfMonth()->format('Y-m-d');
                    $endDate = $request->month ? Carbon::parse($request->month)->endOfMonth()->format('Y-m-d') : now()->endOfMonth()->format('Y-m-d');

                    $leaves = Leave::where('staff_id', $user->id)
                    ->where(function($query) use ($startDate, $endDate) {
                        $query->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereBetween('end_date', [$startDate, $endDate]);
                    })->where('status', 1)
                    ->get();

                    $leaveDates = [];

                    foreach ($leaves as $leave) {
                        $leaveStart = Carbon::parse($leave->start_date);
                        $leaveEnd = Carbon::parse($leave->end_date);

                        while ($leaveStart->lte($leaveEnd)) {
                            $leaveDate = $request->month ? Carbon::parse($request->month)->startOfMonth()->format('Y-m') : now()->startOfMonth()->format('Y-m');
                            if ($leaveStart->format('Y-m') === $leaveDate) {
                                $leaveDates[] = $leaveStart->format('Y-m-d');
                            }
                            $leaveStart->addDay();
                        }
                    }

                    $leaveDaysByType = [];

                    foreach ($leaves as $leave) {
                        $leaveStart = Carbon::parse($leave->start_date);
                        $leaveEnd = Carbon::parse($leave->end_date);

                        while ($leaveStart->lte($leaveEnd)) {
                            // Count only if within this month
                            $leaveDate = $request->month ? Carbon::parse($request->month)->startOfMonth()->format('Y-m') : now()->startOfMonth()->format('Y-m');
                            if ($leaveStart->format('Y-m') === $leaveDate) {
                                $currentDay = strtoupper($leaveStart->format('D')); // Like "SUN", "MON", etc.

                                if (!in_array($currentDay, $companyHolidayDays)) {
                                        $leaveType = $leave->leave_type;
                                        if (!isset($leaveDaysByType[$leaveType])) {
                                            $leaveDaysByType[$leaveType] = 0;
                                        }
                                        $leaveDaysByType[$leaveType]++;
                                }

                            }
                            $leaveStart->addDay();
                        }
                    }

                    // ** Leave counting end

                $paidAttendanceDates = $attendanceRecords->pluck('date')->unique();

                $paid_leaves = $paid_leaves->pluck('date')->unique();
                $paid_leaves = $paid_leaves->merge($leaveDates);
                $paid_leaves = $paid_leaves->unique()->values();

                $paid_leaves = $paid_leaves->diff($paidAttendanceDates)->values();

                $halfday = $halfday->pluck('date')->unique();
                $unpaid_halfday = $unpaid_halfday->pluck('date')->unique();
                $unpaid_halfday_count = $unpaid_halfday->count() / 2;
                    // Public holidays
                $publicHolidays = Holiday::whereMonth('date', $month)->count();

                $paidDays = $paidAttendanceDates->count() + $publicHolidays + $companyHoliday + $paid_leaves->count() + $halfday->count() + $unpaid_halfday_count; //$halfday_count;

                $data[] = [
                        'unique_id' => $user->unique_id,
                        'Name' => $user->first_name . ' ' . $user->last_name,
                        'halfday' => $halfday->count() ?: "0",
                        'UnpaidLeave' => HrmsCalenderAttendance::where('user_id', $user->id)->where('status', 'unpaidleave')->whereMonth('date', $month)->count() ?: "0",
                        'UnpaidHalfDay' => $unpaid_halfday->count() ?: "0",
                        'PaidLeave' => $paid_leaves->count() ?: "0",
                        'Present' =>  $paidAttendanceDates->count() ?: "0",
                        'PublicHolidays' => $publicHolidays ?: "0",
                        'CompanyHolidays' => $companyHoliday ?: "0",
                        'TotalPaidDays' => $paidDays ?: "0",
                        'WenddingLeave' => $leaveDaysByType['wedding_leave']  ?? "0",
                        'MedicalLeave' => $leaveDaysByType['medical_leave']  ?? "0",
                        'BereavementLeave' => $leaveDaysByType['bereavement_leave']  ?? "0",
                        'CasualLeave' => $leaveDaysByType['casual_leave']  ?? "0",
                        'daily' => $dailyStatus,
                    ];
                }

                //  ******  Separation Users Data

                $separationData = [];

                foreach ($separation_users as $user) {
                       $user_location = DB::table('sub_user_addresses')->find($user->id);
                       $separation = EmployeeSeparation::where('user_id',$user->id)->first();

                       if ($separation) {
                           $separationData[] = [
                            'unique_id' => $user->unique_id,
                            'Name' => $user->first_name . ' ' . $user->last_name,
                            'Domain' => $user->company_name ?? "",
                            'Location' => $user_location->address ?? "",
                            'Separation_type' => $separation->separation_type ?? "",
                            'Notice_served_date' => $separation->notice_served_date ?? "",
                            'Last_working_date' =>  $separation->last_working_date ?? "",
                            'Reason' => $separation->reason ?? "",
                            'Description_Of_reason' => $separation->description_of_reason ?? "",
                            'salary_process' => $separation->salary_process ?? "",
                            'GOOD_for_rehire' => $separation->good_for_rehire ?? "",
                            'Remarks' => $separation->remarks ?? "",
                          ];
                       }else {
                           $separationData[] = [
                            'unique_id' => $user->unique_id,
                            'Name' => $user->first_name . ' ' . $user->last_name,
                            'Domain' => $user->company_name ?? "",
                            'Location' => $user_location->address ?? "",
                            'Separation_type' => "",
                            'Notice_served_date' => "",
                            'Last_working_date' =>  "",
                            'Reason' => "",
                            'Description_Of_reason' => "",
                            'salary_process' => "",
                            'GOOD_for_rehire' => "",
                            'Remarks' => "",
                          ];
                       }

                }

                $timestamp = now()->format('Y-m-d-H-i-s');
                $filename = "timesheet_report_" . $timestamp . ".xlsx";
                $folder = 'exports';
                $filepath = $folder . '/' . $filename;

                if (!Storage::disk('local')->exists($folder)) {
                    Storage::disk('local')->makeDirectory($folder);
                }

               // Excel::store(new TimesheetExport($data), $filepath, 'local');
                Excel::store(
                        new TimesheetExport($data, $separationData, $date),
                        $filepath,
                        'public'
                    );
                $publicUrl = asset('storage/' . $filepath);
                $publicUrl = asset('storage/exports/');
                // Full absolute path
                $fullPath = storage_path('app/public/' . $filepath);

                // Send email with attachment
                // if ($request->download == 0) {
                //     $emails = EmailAddressForAttendanceAndLeave::where('type', 1)->get();

                //     if ($emails->isNotEmpty()) {
                //         $toEmail = $emails->first()->email;
                //         $ccEmails = $emails->skip(1)->pluck('email')->toArray();

                //         Mail::to($toEmail)
                //             ->cc($ccEmails)
                //             ->send(new timesheetReport($fullPath));
                //     }
                //      // Mail::to('zainab.mirza@yopmail.com')->send(new timesheetReport($fullPath));
                //    // Mail::to('sonurana.creativecoder@gmail.com')->send(new timesheetReport($fullPath));
                // }
            }
         return response()->json(['Url' => $publicUrl,'message' => 'Report generated and email sent successfully.']);


         } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
         }
     }



    // public function timeSheetReport(Request $request){
    //      try {

    //           $validated = $request->validate([
    //               'download' => 'nullable|integer|in:0,1',   // 1 => all employee timesheet download (only one company), 0 => using for cron job all company sent timesheet attach email
    //               'user_id' => 'nullable|integer|exists:sub_users,id',   // using if only single employee download timesheet
    //               'month' => 'nullable|date',     // using if only single employee download timesheet
    //           ]);

    //         $default_DBName = env("DB_DATABASE");
    //         $this->connectDB($default_DBName);

    //         $date = now()->subMonth()->format('Y-m-d');

    //         if ($request->download == 0) {
    //             //$companies = User::whereNotNull('database_name')->get();      used permantly
    //             $companies = User::whereNotNull('database_name')->where('database_name', 'UC_unifytechsolutions')->get();
    //         }else {
    //             if (isset($request->user_id)) {
    //                 $user_id = $request->user_id;
    //                 $company = SubUser::whereNotNull('database_name')->find($user_id); // returns single model
    //                 $companies = $company ? [$company] : [];
    //             }else {
    //                 $user = auth('sanctum')->user();
    //                 $companies = $user ? [$user] : [];
    //             }

    //         }

    //         foreach ($companies as  $company) {
    //              $databaseName = $company->database_name;
    //              $this->connectDB($databaseName);

    //             $month = now()->subMonth()->format('m');

    //             $baseQuery  = SubUser::select('id', 'first_name', 'last_name', 'email', 'employement_type', 'unique_id','status')
    //                                                 ->where('user_type',"0");

    //             if (isset($request->user_id)) {
    //                 // $separation_users = $query->whereIn('status', [2,3,4,5,6,7])->where('id',$request->user_id)->get(); // 2 => Inactive, 5 => Suspended, 6 => Terminated, 7 => Deceased
    //                 // $attendance_users = $query->with('payrolls')->whereIn('status', [1,3,4])->where('id',$request->user_id)->get(); //1 => Active, 3 => Resigned, 4 => On Notice Period,"


    //                  $separation_users = (clone $baseQuery)->whereIn('status', [2,3,4,5,6,7,8])->where('id', $request->user_id)->get();
    //                  $attendance_users = (clone $baseQuery) ->with('payrolls')->whereIn('status', [1,3,4])->where('id', $request->user_id)->get();
    //             }else {
    //                 // $separation_users = $query->whereIn('status', [2,3,4,5,6,7])->get();
    //                 // $attendance_users = $query->with('payrolls')->whereIn('status', [1,3,4])->get();

    //                  $separation_users = (clone $baseQuery)->whereIn('status', [2,3,4,5,6,7,8])->get();
    //                  $attendance_users = (clone $baseQuery)->with('payrolls')->whereIn('status', [1,3,4])->get();
    //             }


    //             $data = [];

    //             foreach ($attendance_users as $user) {

    //                 $dailyStatus = [];
    //                 $startMonth =  now()->subMonth()->startOfMonth();
    //                 $endMonth =  now()->subMonth()->endOfMonth();

    //                 // ***** count Company Holiday

    //                     $time_shift =  HrmsTimeAndShift::first();
    //                     $employee_shift = $user->employee_shift ?? $time_shift->shift_name ;

    //                     $employee_shift_time = HrmsTimeAndShift::where('shift_name', $employee_shift)->first();
    //                     $shift_days = $employee_shift_time->shift_days;

    //                     $companyHolidayDays = collect($shift_days)->filter(function ($value) {
    //                         return $value == "0";
    //                     })->keys()->map(function ($day) {
    //                         return strtoupper($day); // optional agar keys already "SUN", "SAT" me hain
    //                     })->toArray();

    //                     $start =  now()->subMonth()->startOfMonth();
    //                     $end = now()->subMonth()->endOfMonth();
    //                     if ($end->gt(now())) {
    //                         $end = now();
    //                     }

    //                     $companyHoliday = 0;
    //                     while ($start->lte($end)) {

    //                         $currentDay = strtoupper($start->format('D')); // Like "SUN", "MON", etc.

    //                         if (in_array($currentDay, $companyHolidayDays)) {
    //                             $companyHoliday += 1;
    //                         }

    //                         $start->addDay();
    //                     }
    //                 // ***** count Company Holiday

    //                 while ($startMonth->lte($endMonth)) {
    //                     $statusRecord = HrmsCalenderAttendance::where('user_id', $user->id)
    //                                             ->whereDate('date', $startMonth->format('Y-m-d'))
    //                                             ->first();

    //                     $leaves = Leave::where('staff_id', $user->id)->whereDate('start_date', '<=', $startMonth->format('Y-m-d'))
    //                                     ->whereDate('end_date', '>=', $startMonth->format('Y-m-d'))->where('status', 1)->first();

    //                     $absent = EmployeeAttendance::where('user_id', $user->id)->whereDate('date', $startMonth->format('Y-m-d'))->first();

    //                         $status = '';

    //                          if (!$absent) {
    //                             $status = 'ABS';
    //                          }

    //                         if ($statusRecord) {
    //                             if ($statusRecord->status == "present") {
    //                                 $status = 'P';
    //                             } elseif ($statusRecord->status == "paidleave") {
    //                                 $status = 'PL';
    //                             } elseif ($statusRecord->status == "halfday") {
    //                                 $status = 'HD';
    //                             } elseif ($statusRecord->status == "unpaidleave") {
    //                                 $status = 'UPL';
    //                             } elseif ($statusRecord->status == "unpaidhalfday") {
    //                                 $status = 'UHD';
    //                             } else {
    //                                 $status = strtoupper(substr($statusRecord->status, 0, 2));
    //                             }
    //                         }

    //                         // if (!$absent) {
    //                         //     $status = 'ABS';
    //                         // }

    //                         if ($leaves) {
    //                             $status = 'PL';
    //                         }

    //                         if ($startMonth->isSaturday() || $startMonth->isSunday()) {
    //                             $status = 'CH';
    //                         }

    //                         $dailyStatus[$startMonth->format('Y-m-d')] = $status;
    //                         $startMonth->addDay();


    //                 }
    //                 ////////////////

    //             $attendanceRecords = HrmsCalenderAttendance::where('user_id', $user->id)
    //                                         ->whereMonth('date', $month)
    //                                         ->where('status', 'present')
    //                                         ->get();
    //             $paid_leaves = HrmsCalenderAttendance::where('user_id', $user->id)
    //                                         ->whereMonth('date', $month)
    //                                         ->where('status','paidleave',)
    //                                         ->get();
    //             $halfday = HrmsCalenderAttendance::where('user_id', $user->id)
    //                                         ->whereMonth('date', $month)
    //                                         ->where('status','halfday',)
    //                                         ->get();
    //             $unpaid_halfday = HrmsCalenderAttendance::where('user_id', $user->id)
    //                                         ->whereMonth('date', $month)
    //                                         ->where('status','unpaidhalfday',)
    //                                         ->get();


    //                 // ** Leave counting
    //                 $startDate =  now()->subMonth()->startOfMonth()->format('Y-m-d');
    //                 $endDate = now()->subMonth()->endOfMonth()->format('Y-m-d');

    //                 $leaves = Leave::where('staff_id', $user->id)
    //                 ->where(function($query) use ($startDate, $endDate) {
    //                     $query->whereBetween('start_date', [$startDate, $endDate])
    //                         ->orWhereBetween('end_date', [$startDate, $endDate]);
    //                 })->where('status', 1)
    //                 ->get();

    //                 $leaveDates = [];

    //                 foreach ($leaves as $leave) {
    //                     $leaveStart = Carbon::parse($leave->start_date);
    //                     $leaveEnd = Carbon::parse($leave->end_date);

    //                     while ($leaveStart->lte($leaveEnd)) {
    //                         $leaveDate =  now()->subMonth()->startOfMonth()->format('Y-m');
    //                         if ($leaveStart->format('Y-m') === $leaveDate) {
    //                             $leaveDates[] = $leaveStart->format('Y-m-d');
    //                         }
    //                         $leaveStart->addDay();
    //                     }
    //                 }

    //                 $leaveDaysByType = [];

    //                 foreach ($leaves as $leave) {
    //                     $leaveStart = Carbon::parse($leave->start_date);
    //                     $leaveEnd = Carbon::parse($leave->end_date);

    //                     while ($leaveStart->lte($leaveEnd)) {
    //                         // Count only if within this month
    //                         $leaveDate =  now()->subMonth()->startOfMonth()->format('Y-m');
    //                         if ($leaveStart->format('Y-m') === $leaveDate) {
    //                             $currentDay = strtoupper($leaveStart->format('D')); // Like "SUN", "MON", etc.

    //                             if (!in_array($currentDay, $companyHolidayDays)) {
    //                                     $leaveType = $leave->leave_type;
    //                                     if (!isset($leaveDaysByType[$leaveType])) {
    //                                         $leaveDaysByType[$leaveType] = 0;
    //                                     }
    //                                     $leaveDaysByType[$leaveType]++;
    //                             }

    //                         }
    //                         $leaveStart->addDay();
    //                     }
    //                 }

    //                 // ** Leave counting end

    //             $paidAttendanceDates = $attendanceRecords->pluck('date')->unique();

    //             $paid_leaves = $paid_leaves->pluck('date')->unique();
    //             $paid_leaves = $paid_leaves->merge($leaveDates);
    //             $paid_leaves = $paid_leaves->unique()->values();

    //             $paid_leaves = $paid_leaves->diff($paidAttendanceDates)->values();

    //             $halfday = $halfday->pluck('date')->unique();
    //             $unpaid_halfday = $unpaid_halfday->pluck('date')->unique();
    //             $unpaid_halfday_count = $unpaid_halfday->count() / 2;
    //                 // Public holidays
    //             $publicHolidays = Holiday::whereMonth('date', $month)->count();

    //             $paidDays = $paidAttendanceDates->count() + $publicHolidays + $companyHoliday + $paid_leaves->count() + $halfday->count() + $unpaid_halfday_count; //$halfday_count;

    //             $data[] = [
    //                     'unique_id' => $user->unique_id,
    //                     'Name' => $user->first_name . ' ' . $user->last_name,
    //                     'halfday' => $halfday->count() ?: "0",
    //                     'UnpaidLeave' => HrmsCalenderAttendance::where('user_id', $user->id)->where('status', 'unpaidleave')->whereMonth('date', $month)->count() ?: "0",
    //                     'UnpaidHalfDay' => $unpaid_halfday->count() ?: "0",
    //                     'PaidLeave' => $paid_leaves->count() ?: "0",
    //                     'Present' =>  $paidAttendanceDates->count() ?: "0",
    //                     'PublicHolidays' => $publicHolidays ?: "0",
    //                     'CompanyHolidays' => $companyHoliday ?: "0",
    //                     'TotalPaidDays' => $paidDays ?: "0",
    //                     'WenddingLeave' => $leaveDaysByType['wedding_leave']  ?? "0",
    //                     'MedicalLeave' => $leaveDaysByType['medical_leave']  ?? "0",
    //                     'BereavementLeave' => $leaveDaysByType['bereavement_leave']  ?? "0",
    //                     'CasualLeave' => $leaveDaysByType['casual_leave']  ?? "0",
    //                     'daily' => $dailyStatus,
    //                 ];
    //             }

    //             //  ******  Separation Users Data

    //             $separationData = [];

    //             foreach ($separation_users as $user) {
    //                    $user_location = DB::table('sub_user_addresses')->find($user->id);
    //                    $separation = EmployeeSeparation::where('user_id',$user->id)->first();

    //                    if ($separation) {
    //                        $separationData[] = [
    //                         'unique_id' => $user->unique_id,
    //                         'Name' => $user->first_name . ' ' . $user->last_name,
    //                         'Domain' => $user->company_name ?? "",
    //                         'Location' => $user_location->address ?? "",
    //                         'Separation_type' => $separation->separation_type ?? "",
    //                         'Notice_served_date' => $separation->notice_served_date ?? "",
    //                         'Last_working_date' =>  $separation->last_working_date ?? "",
    //                         'Reason' => $separation->reason ?? "",
    //                         'Description_Of_reason' => $separation->description_of_reason ?? "",
    //                         'salary_process' => $separation->salary_process ?? "",
    //                         'GOOD_for_rehire' => $separation->good_for_rehire ?? "",
    //                         'Remarks' => $separation->remarks ?? "",
    //                       ];
    //                    }else {
    //                        $separationData[] = [
    //                         'unique_id' => $user->unique_id,
    //                         'Name' => $user->first_name . ' ' . $user->last_name,
    //                         'Domain' => $user->company_name ?? "",
    //                         'Location' => $user_location->address ?? "",
    //                         'Separation_type' => "",
    //                         'Notice_served_date' => "",
    //                         'Last_working_date' =>  "",
    //                         'Reason' => "",
    //                         'Description_Of_reason' => "",
    //                         'salary_process' => "",
    //                         'GOOD_for_rehire' => "",
    //                         'Remarks' => "",
    //                       ];
    //                    }

    //             }

    //             $timestamp = now()->format('Y-m-d-H-i-s');
    //             $filename = "timesheet_report_" . $timestamp . ".xlsx";
    //             $folder = 'exports';
    //             $filepath = $folder . '/' . $filename;

    //             if (!Storage::disk('local')->exists($folder)) {
    //                 Storage::disk('local')->makeDirectory($folder);
    //             }

    //            // Excel::store(new TimesheetExport($data), $filepath, 'local');
    //             Excel::store(
    //                     new TimesheetExport($data, $separationData, $date),
    //                     $filepath,
    //                     'public'
    //                 );
    //             $publicUrl = asset('storage/' . $filepath);
    //             // Full absolute path
    //             $fullPath = storage_path('app/public/' . $filepath);

    //             // Send email with attachment
    //             // if ($request->download == 0) {
    //             //     $emails = EmailAddressForAttendanceAndLeave::where('type', 1)->get();

    //             //     if ($emails->isNotEmpty()) {
    //             //         $toEmail = $emails->first()->email;
    //             //         $ccEmails = $emails->skip(1)->pluck('email')->toArray();

    //             //         Mail::to($toEmail)
    //             //             ->cc($ccEmails)
    //             //             ->send(new timesheetReport($fullPath));
    //             //     }
    //             //      // Mail::to('zainab.mirza@yopmail.com')->send(new timesheetReport($fullPath));
    //             //    // Mail::to('sonurana.creativecoder@gmail.com')->send(new timesheetReport($fullPath));
    //             // }
    //         }
    //      return response()->json(['Url' => $publicUrl,'message' => 'Report generated and email sent successfully.']);


    //      } catch (\Throwable $th) {
    //         return $this->errorResponse($th->getMessage());
    //      }
    // }







    /**
     * @OA\Post(
     * path="/uc/api/timesheet/employeeImportSample",
     * operationId="employeeImportSample",
     * tags={"Timesheet"},
     * summary="employeeImportSample",
     * security={ {"Bearer": {} }},
     * description="timeSheet Reportt to send HR",
     *       @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *                 @OA\Property(property="download", type="integer"),
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="month", type="date"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="timeSheet Reportt to send HR Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="timeSheet Reportt to send HR Successfully",
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






}
