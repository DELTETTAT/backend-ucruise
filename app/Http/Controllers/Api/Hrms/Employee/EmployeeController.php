<?php

namespace App\Http\Controllers\Api\Hrms\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;
use Str;
use Mail;
use Hash;
use App\Mail\BirthdayEmail;
use App\Models\SubUser;
use App\Models\Designation;
use App\Models\Resignation;
use App\Models\User;
use App\Models\Role;
use App\Models\HrmsRole;
use App\Models\SubUserAddresse;
use App\Models\Leave;
use App\Models\HrmsEmployeeRole;
use App\Models\TeamManager;
use App\Models\EmployeeTeamManager;
use App\Models\HrmsTeam;
use App\Models\HrmsTeamMember;
use App\Models\UserInfo;
use App\Models\EmployeesUnderOfManager;
use App\Models\EmployeeSeparation;
use App\Models\UpdateEmployeeHistory;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Http\Resources\EmployeeLeave\LeaveCollection;
use Carbon\Carbon;
use App\Jobs\UpdateUserLatLong;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     * path="/uc/api/employee/index",
     * operationId="getemployees",
     * tags={"HRMS Employee"},
     * summary="Get Employee Request",
     *   security={ {"Bearer": {} }},
     * description="Get Employee Request",
     *      @OA\Response(
     *          response=201,
     *          description="Employee Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Employee Get Successfully",
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
    public function index()
    {
        try {
            $id =  auth('sanctum')->user()->id;
            return User::with('roles')->get();
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


     /**
     * @OA\post(
     * path="/uc/api/employee/update/{id}",
     * operationId="updateemployee",
     * tags={"HRMS Employee"},
     * summary="Update employee Request",
     *   security={ {"Bearer": {} }},
     * description="Update employee Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="first_name", type="string"),
     *              @OA\Property(property="last_name", type="string"),
     *              @OA\Property(property="email", type="string"),
     *              @OA\Property(property="employee_id", type="string"),
     *              @OA\Property(property="phone", type="string"),
     *              @OA\Property(property="address", type="string"),
     *              @OA\Property(property="position", type="integer"),
     *              @OA\Property(property="role", type="integer"),
     *              @OA\Property(property="gender", type="string"),
     *              @OA\Property(property="dob", type="string",format="date", description="enter date of birth"),
     *              @OA\Property(property="profile_image", type="string", format="binary"),
     *              @OA\Property(property="marital_status", type="integer"),
     *              @OA\Property(property="blood_group", type="string"),
     *              @OA\Property(property="emergency_contact", type="string"),
     *              @OA\Property(property="experience", type="string"),
     *              @OA\Property(property="shift_type", type="string"),
     *              @OA\Property(property="doj", type="string", format="date", description="employee hiring date"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Employee Updated Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Employee Updated Successfully",
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
    public function update(EmployeeUpdateRequest $request, $id)
    {

            try {

                 $validatedData = $request->validated();

                 $temp_DB_name = DB::connection()->getDatabaseName();

                 $employee_type = Designation::find($request->position);

                  if (!$employee_type) {
                    return $this->errorResponse("the given position is not found");
                  }

                    // Get current role before making any changes
                    $currentRole = HrmsEmployeeRole::where('employee_id', $id)->first();
                    $getRole = $request->role;

                    // Check if role is being changed
                    if ($currentRole && $currentRole->role_id != $getRole) {
                        //Role is being changed - remove from all teams
                        HrmsTeamMember::where('member_id', $id)->delete();

                        //Remove as team leader if they were one
                        HrmsTeam::where('team_leader', $id)->update(['team_leader' => null]);

                        //Remove as team manager
                        EmployeeTeamManager::where('employee_id', $id)->delete();
                    }

                  //connecting to parent DB
                  $default_DBName = env("DB_DATABASE");
                  $this->connectDB($default_DBName);

                  $parentEmployee = SubUser::find($id);
                  if (!$parentEmployee) {
                    return $this->errorResponse("the given data is not found");
                  }
                  $parentEmployee->first_name = $request->first_name;
                  $parentEmployee->last_name = $request->last_name;
                 // $parentEmployee->unique_id = "UTS".$request->id;
                  $parentEmployee->email = $request->email;
                  $parentEmployee->phone = $request->phone;
                  $parentEmployee->gender = $request->gender;
                  $parentEmployee->dob = date('Y-m-d', strtotime($request->dob));
                  $parentEmployee->doj = $request->hire_date;
                  $parentEmployee->employee_shift = $request->employee_shift;
                  $parentEmployee->employement_type = $employee_type->title;
                 // $parentEmployee->worked_for = $employee_type->title;
                  $parentEmployee->marital_status = $request->marital_status;
                  $parentEmployee->blood_group = $request->blood_group;
                  //$parentEmployee->unique_id = $request->employee_id;
                  $parentEmployee->mobile = $request->emergency_contact;
                 //$parentEmployee->experince = $request->experince;    this field not available sub_users table

                 if ($request->hasFile('profile_image')) {
                       // Removed old file
                       $oldFile = $parentEmployee->profile_image;

                       if (!empty($oldFile) && file_exists(public_path('profile_image/' . $oldFile))) {
                           unlink(public_path('profile_image/' . $oldFile));
                       }
                       $fileName = time(). "_". $request->file('profile_image')->getClientOriginalName();

                       $request->file('profile_image')->move(public_path('profile_image'), $fileName);

                       $parentEmployee->profile_image = $fileName;

                 }
                 $parentEmployee->save();

                  $this->connectDB($temp_DB_name);

                  //create staff in users table in child DB
                  $childEmployee = User::find($id);
                  $childEmployee->first_name = $request->first_name;
                  $childEmployee->last_name = $request->last_name;
                  //$childEmployee->unique_id = "UTS".$id;
                  $childEmployee->email = $request->email;
                  $childEmployee->phone = $request->phone;
                  $childEmployee->gender = $request->gender;
                  $childEmployee->employee_shift = $request->employee_shift;
                  $childEmployee->address = $request->address;
                  $childEmployee->dob = date('Y-m-d', strtotime($request->dob));
                  $childEmployee->doj = date('Y-m-d', strtotime($request->doj));
                 // $childEmployee->employement_type = $employee_type->title;
                  $childEmployee->employement_type = $employee_type->title;
                 // $childEmployee->worked_for = $employee_type->title;
                  $childEmployee->marital_status = $request->marital_status;
                  $childEmployee->blood_group = $request->blood_group;
                  $childEmployee->mobile = $request->emergency_contact;

                  if ($parentEmployee->profile_image) {
                      $childEmployee->profile_image = $parentEmployee->profile_image;
                  }
                  $childEmployee->save();

                  $child_staff =  SubUser::find($id);

                  $child_staff->first_name = $request->first_name;
                  $child_staff->last_name = $request->last_name;
                 // $child_staff->unique_id =  $childEmployee->unique_id = "UTS".$id;
                  $child_staff->email = $request->email;
                  $child_staff->phone = $request->phone;
                  $child_staff->gender = $request->gender;
                  $child_staff->employee_shift = $request->employee_shift;
                  $child_staff->dob = date('Y-m-d', strtotime($request->dob));
                  $child_staff->doj = date('Y-m-d', strtotime($request->doj));
                 // $child_staff->worked_for = $employee_type->title;
                  $child_staff->marital_status = $request->marital_status;
                 // $child_staff->employement_type = $employee_type->title;
                  $child_staff->employement_type = $employee_type->title;
                  $child_staff->blood_group = $request->blood_group;
                  $child_staff->mobile = $request->emergency_contact;

                  if ($parentEmployee->profile_image) {
                       $child_staff->profile_image = $parentEmployee->profile_image;
                  }
                  $this->updateEmployeeHistory($request, $id);
                  $child_staff->save();

                  $getRole = $request->role;
                  $this->updateEmployeeHistory($request, $id);

                  $role = HrmsRole::find($getRole);
                  if ($role) {
                        HrmsEmployeeRole::updateOrCreate(
                            ['employee_id' => $id],
                            ['role_id' => $getRole, 'employee_id' => $id],
                        );

                  }else {
                        return $this->errorResponse("the given role is not found");
                  }

                  if (isset($request->experience)) {
                        UserInfo::updateOrCreate(
                            ['user_id' => $id],
                            ['experience' => $request->experience]
                        );
                    }

                  if ($childEmployee->address) {
                          $sub_user_address = SubUserAddresse::where('sub_user_id', $id)->first();
                          $sub_user_address->address = $childEmployee->address;
                          $sub_user_address->save();
                  }

                  return $this->successResponse(
                      [],
                      "Employee Updated Successfully"
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
     * path="/uc/api/employee/getRoles",
     * operationId="getemployeesroles",
     * tags={"HRMS Employee"},
     * summary="Get Employee Roles Request",
     *   security={ {"Bearer": {} }},
     * description="Get Employee Roles Request",
     *      @OA\Response(
     *          response=201,
     *          description="Employee Roles Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Employee Roles Get Successfully",
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
    public function getRoles()
    {
        try {
              $getRoles =  HrmsRole::select('id','name','specific_role_id')->get();

            return $this->successResponse(
                $getRoles,
                "HRMS Roles List"
            );

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }


     /**
     * @OA\Post(
     * path="/uc/api/employee/leaves",
     * operationId="getemployeesLeaves",
     * tags={"HRMS Employee"},
     * summary="Get Employee Leaves Request",
     *   security={ {"Bearer": {} }},
     * description="Get Employee Leaves Request",
     *   *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="employee_id", type="integer",),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Employee Leaves Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Employee Leaves Get Successfully",
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
    public function employeeLeaves(Request $request)
    {
        try {
              $employeeLeaves =  Leave::with('reason')->where('staff_id', $request->employee_id)->paginate(Leave::PAGINATE);

            return $this->successResponse(
                new LeaveCollection($employeeLeaves),
                "HRMS Roles List"
            );

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }



    /**
     * @OA\Get(
     * path="/uc/api/employee/employeeList",
     * operationId="getemployeeList",
     * tags={"HRMS Employee"},
     * summary="Get Employee list Request",
     *   security={ {"Bearer": {} }},
     * description="Get Employee list Request",
     *      @OA\Response(
     *          response=201,
     *          description="Employee list Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Employee list Get Successfully",
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
    public function employeeList()
    {
        try {

            $user_id = auth('sanctum')->user()->id;
            $admin = 0;
            $is_manager = 0;

             // if view role admin view
            $getTeamMembers = SubUser::with('hrmsroles.viewrole')->find($user_id);
            if ($getTeamMembers) {
                 $role_view = $getTeamMembers->hrmsroles[0]->viewrole->name;
                if ($role_view == 'Admin View') {
                    $admin = 1;
                }elseif ($role_view == 'Manager View') {   // manager view
                    $is_manager = 1;
                }
            }
             ///  if Main Admin
            $auth_role = DB::table('role_user')->where('user_id',$user_id)->first();
            $employeeRole = DB::table('roles')->find($auth_role->role_id);
            if ($employeeRole->name == "admin") {
                 $admin = 1;
            }

            if ($admin == 0) {
                 if ($is_manager == 1 ) {
                     $team_manager = EmployeeTeamManager::where('employee_id', $user_id)->first();
                     if ($team_manager) {
                         $team_manager_id = $team_manager->team_manager_id;
                     }
                 }else {
                     $getManagerList = TeamManager::with(['employees','teams.teamLeader', 'teams.teamMembers.user'])->get();
                      //$team_manager_id = null;
                     foreach ($getManagerList as $key => $manager) {
                         foreach ($manager->teams as $key => $team) {
                             if ($team->team_leader == $user_id) {
                                 $team_manager_id =  $manager->id;
                                 $team__id =   $team->id;
                                 break 2;
                             }else {

                                 foreach ($team->teamMembers as $key => $member) {

                                       if ($member->user->id == $user_id) {
                                             $team_manager_id =  $manager->id;
                                             $team__id =   $team->id;
                                             break 2;
                                       }
                                 }
                             }


                         }
                     }
                 }
            }

             $data = [];
             if ($admin == 1) {
                $data['main_team'] = [];

                $data['additional'] = TeamManager::with([
                    'teams:id,team_manager_id,team_name,team_leader',
                    'teams.teamLeader:id,first_name,last_name,unique_id',
                    'teams.teamMembers.user:id,first_name,last_name,unique_id'
                ])->get();

             }elseif ($is_manager == 1) {
                $data['main_team'] = TeamManager::with([
                    'teams:id,team_manager_id,team_name,team_leader',
                    'teams.teamLeader:id,first_name,last_name,unique_id',
                    'teams.teamMembers.user:id,first_name,last_name,unique_id'
                ])->where('id', $team_manager_id)->get();

                $data['additional'] = TeamManager::with([
                    'teams:id,team_manager_id,team_name,team_leader',
                    'teams.teamLeader:id,first_name,last_name,unique_id',
                    'teams.teamMembers.user:id,first_name,last_name,unique_id'
                ])->where('id','!=', $team_manager_id)->get();
             }else {

                    $data['main_team'] = TeamManager::with([
                        'teams:id,team_manager_id,team_name,team_leader',
                        'teams.teamLeader:id,first_name,last_name,unique_id',
                        'teams.teamMembers.user:id,first_name,last_name,unique_id'
                    ])->where('id', $team_manager_id)->get();

                    $data['additional'] = TeamManager::with([
                        'teams:id,team_manager_id,team_name,team_leader',
                        'teams.teamLeader:id,first_name,last_name,unique_id',
                        'teams.teamMembers.user:id,first_name,last_name,unique_id'
                    ])->where('id','!=', $team_manager_id)->get();
             }

              return $this->successResponse(
                $data,
                "Employee List with Team"
              );

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }


    /**
     * @OA\Post(
     * path="/uc/api/employee/updateStatus",
     * operationId="updateStatus",
     * tags={"HRMS Employee"},
     * summary="Update Employee Status Request",
     *   security={ {"Bearer": {} }},
     * description="Update Employee Status Request",
     *   *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="employee_id", type="integer"),
     *              @OA\Property(property="status", type="integer", description="1 => Active, 2 => Inactive, 3 => Resigned, 4 => On Notice Period, 5 => Suspended, 6 => Terminated, 7 => Deceased, 8 => Abscond"),
     *              @OA\Property(property="separation_type", type="string", description="Voluntary ,Involuntary"),
     *              @OA\Property(property="notice_served_date", type="date"),
     *              @OA\Property(property="last_working_date", type="date"),
     *              @OA\Property(property="reason", type="string"),
     *              @OA\Property(property="description_of_reason", type="string"),
     *              @OA\Property(property="salary_process", type="string"),
     *              @OA\Property(property="good_for_rehire", type="string"),
     *              @OA\Property(property="remarks", type="string"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Update Employee Status Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Update Employee Status Successfully",
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



     public function updateStatus(Request $request){
         try {
              $validatedData = $request->validate([
                  'employee_id' => 'required|integer|exists:sub_users,id',
                  'status' => 'required|integer|in:1,2,3,4,5,6,7,8',
                  'separation_type' => 'nullable|string',
                  'notice_served_date' => 'nullable|date',
                  'last_working_date' => 'nullable|date',
                  'reason' => 'nullable|string',
                  'description_of_reason' => 'nullable',
                  'salary_process' => 'nullable|string',
                  'good_for_rehire' => 'nullable|string',
                  'remarks' => 'nullable|string',
              ]);

              $temp_DB_name = DB::connection()->getDatabaseName();

              $default_DBName = env("DB_DATABASE");
              $this->connectDB($default_DBName);  // parent database connection

              $employee = SubUser::find($validatedData['employee_id']);
              //$employee->status = $validatedData['status'];
              $oldStatus = $employee->status;  // This is where we properly define $oldStatus
              $newStatus = $validatedData['status'];

            $changeCab = false;
            if (in_array((int) $newStatus, [2, 6, 7, 8])) {
                $changeCab = true;
            }

              //Now proceed with the updates
              $employee->status = $newStatus;
              $employee->save();

              // child database connection
              $this->connectDB($temp_DB_name);

              $employee = User::find($validatedData['employee_id']);
              $employee->status = $validatedData['status'];
              if($changeCab == true){
                $employee->cab_facility = 0;
              }
              $employee->save();

              $employee = SubUser::find($validatedData['employee_id']);
              $employee->status = $validatedData['status'];
              if($changeCab == true){
                $employee->cab_facility = 0;
              }
              $employee->save();

              $validatedData['user_id'] = $validatedData['employee_id'];

              $exist_history = EmployeeSeparation::where('user_id', $validatedData['user_id'])->get();
              if ($exist_history) {
                  foreach ($exist_history as $key => $history) {
                       $history->delete();
                  }
              }

              EmployeeSeparation::create($validatedData);

              if ($validatedData['status'] == 3) {
                   $resignation = Resignation::where('user_id',$validatedData['employee_id'])->first();
                   if ($resignation) {
                         $resignation->notice_served_date = $validatedData['notice_served_date'];
                         $resignation->last_working_date = $validatedData['last_working_date'];
                         $resignation->save();
                   }else {
                         Resignation::create([
                               'user_id' => $validatedData['employee_id'],
                               'date' => now()->format('Y-m-d'),
                               'reason' => $validatedData['reason'],
                               'description' => $validatedData['description_of_reason'],
                               'status' => 1,
                               'accept_or_reject_date_of_resignation' => now()->format('Y-m-d'),
                               'notice_served_date' => $validatedData['notice_served_date'],
                               'last_working_date' => $validatedData['last_working_date'],
                         ]);
                   }
              }
              if ($validatedData['status'] == 1) {
                    $resignation = Resignation::where('user_id',$validatedData['employee_id'])->first();
                    if ($resignation) {
                        $resignation->delete();
                    }
              }

              $statusMap = [
                1 => 'Active',
                2 => 'Inactive',
                3 => 'Resigned',
                4 => 'On Notice Period',
                5 => 'Suspended',
                6 => 'Terminated',
                7 => 'Deceased',
                8 => 'Abscond'
            ];

            UpdateEmployeeHistory::create([
                'employee_id' => $validatedData['employee_id'],
                'date' => now()->format('Y-m-d'),
                'time' => now()->format('H:i:s'),
                'updated_by' => auth('sanctum')->id(),
                'notes' => 'Status updated from '.$statusMap[$oldStatus].' to '.$statusMap[$newStatus],
                'changed' => 'Status changed from '.$statusMap[$oldStatus].' to '.$statusMap[$newStatus],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

              return $this->successResponse([], "Status Updated Successfully");
         } catch (\Throwable $th) {
             return $this->errorResponse($th->getMessage());
         }
     }




     /**
     * @OA\Post(
     *     path="/uc/api/employee/user_info_update",
     *     operationId="UserInfoUpdate",
     *     tags={"HRMS Employee"},
     *     summary="User Information Update",
     *     security={ {"Bearer": {} }},
     *     description="User Information Update",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="user_id", type="integer", description="User ID"),
     *                 @OA\Property(property="emergency_phone", type="string", description="Emergency Phone Number"),
     *                 @OA\Property(property="date_of_join", type="date", description="Date of Joining"),
     *                 @OA\Property(property="department", type="string", description="Assign department"),
     *                 @OA\Property(property="assign_employee_position", type="integer", description="Assign employee position like, teamleader, manager, employee (ID)"),
     *                 @OA\Property(property="manager_id", type="integer", description="Under in manager (ID)"),
     *                 @OA\Property(property="assign_team", type="integer", description="Assign team (ID)"),
     *                 @OA\Property(property="current_status", type="integer", description="1 => Active, 2 => InActive"),
     *                 @OA\Property(property="parmanent_address", type="string", description="permanent address"),
     *                 @OA\Property(property="country", type="string", description="country"),
     *                 @OA\Property(property="state", type="string", description="state"),
     *                 @OA\Property(property="city", type="string", description="city"),
     *                 @OA\Property(property="assign_pc", type="string", description="Assigned PC"),
     *                 @OA\Property(property="sallary", type="string", description="Salary"),
     *                 @OA\Property(property="father_name", type="string", description="Father Name"),
     *                 @OA\Property(property="mother_name", type="string", description="Mother Name"),
     *                 @OA\Property(property="spouse_name", type="string", description="Spouse Name"),
     *                 @OA\Property(property="no_of_childern", type="integer", description="Number of Childerns"),
     *                 @OA\Property(property="documented_birthday", type="date", description="Document Birthday"),
     *                 @OA\Property(property="induction_status", type="string", description="induction status"),
     *                 @OA\Property(property="reporting_leader", type="string", description="Reporting Leader"),
     *                 @OA\Property(property="interview_souce", type="string", description="interview souce"),
     *                 @OA\Property(property="referal_by", type="string", description="referal by"),
     *                 @OA\Property(property="aadhar_card_number", type="string", description="Aadhar Card Number"),
     *                 @OA\Property(property="PAN_card_number", type="string", description="PAN Card Number"),
     *                 @OA\Property(property="voter_id", type="string", description="voter Id"),
     *                 @OA\Property(property="driving_lincense", type="string", description="Driving lincense"),
     *                 @OA\Property(property="account_name", type="string", description="Account Name"),
     *                 @OA\Property(property="account_number", type="string", description="Account Number"),
     *                 @OA\Property(property="IFSC_code", type="string", description="IFSC Code"),
     *                 @OA\Property(property="reason", type="string", description="reason"),
     *                 @OA\Property(property="remark", type="string", description="remark"),
     *                 @OA\Property(property="PF_status", type="string", description="PF Status"),
     *                 @OA\Property(property="relieving_letter", type="string", description="Relieving Letter"),
     *                 @OA\Property(property="FNF", type="string", description="FNF"),
     *                 @OA\Property(property="UAN_no", type="string", description="UAN Number"),
     *                 @OA\Property(property="assets", type="string", description="assets"),
     *                 @OA\Property(property="recovery", type="string", description="recovery"),
     *                 @OA\Property(property="genious_employee_code", type="string", description="Recgenious Employee Codeovery"),
     *                 @OA\Property(property="salary_cycle", type="string", description="salary cycle"),
     *                 @OA\Property(property="skills", type="string", description="skills"),
     *                 @OA\Property(property="qualification", type="string", description="Qualification"),
     *                 @OA\Property(property="age_in_year", type="string", description="Qualification"),
     *                 @OA\Property(property="experience", type="string", description="Experience"),
     *                 @OA\Property(property="company_email", type="email", format="email", description="Company Email"),
     *                 @OA\Property(property="cab_facility", type="integer", description="Requierment Cab Facility,  yes => 1, no => 0"),
     *                 @OA\Property(property="pickup_address", type="string", description="Pickup Address"),
     *                 @OA\Property(property="latitude", type="numeric", description="latitude"),
     *                 @OA\Property(property="longitude", type="numeric", description="longitude"),
     *                 @OA\Property(property="drug_policy", type="string", description="Drug Policy"),
     *                 @OA\Property(property="transport_policy", type="string", description="Transport Policy"),
     *                 @OA\Property(property="laptop_phone_policy", type="string", description="Laptop Phone Policy"),
     *                 @OA\Property(property="IJP_policy", type="string", description="IJP Policy"),
     *                 @OA\Property(property="appraisal_policy", type="string", description="Appraisal Policy"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Make Permanent User",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Make Permanent User",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */


     public function userInfoUpdate(Request $request){
        try {

            $validate = $request->validate([
                'user_id' => 'required|integer'
            ]);

            $cab_facility = $request->cab_facility ?? 0;
            $chield_DB_name = DB::connection()->getDatabaseName();
            $default_DBName = env("DB_DATABASE");
            $this->connectDB($default_DBName); // parent database connection
            $parentUser = SubUser::find($validate['user_id']);
            $parentUser->cab_facility = $cab_facility;
            $parentUser->unique_id = $request->id_card ?? null;
            $parentUser->doj = $request->date_of_join ?? null;
            $parentUser->mobile = $request->emergency_phone ?? null;
            $parentUser->save();

            $this->connectDB($chield_DB_name); // child database connection
            $childUser = User::find($validate['user_id']);
            $childUser->cab_facility = $cab_facility;
            $childUser->unique_id = $request->id_card;
             $childUser->doj = $request->date_of_join ?? null;
             $childUser->mobile = $request->emergency_phone ?? null;
            if ($cab_facility == 1) {
                $childUser->latitude = $request->latitude ?? null;
                $childUser->longitude = $request->longitude ?? null;
            }
            $childUser->save();

            $child_staff = SubUser::find($validate['user_id']);
            $child_staff->cab_facility = $cab_facility;
            $child_staff->unique_id = $request->id_card;
             $child_staff->doj = $request->date_of_join ?? null;
             $child_staff->mobile = $request->emergency_phone ?? null;
            $child_staff->save();

            $role_id = $request->assign_employee_position ?? null;
            if ($role_id) {
                 $role = HrmsRole::find($role_id);

                 HrmsEmployeeRole::updateOrCreate(
                            ['employee_id' => $validate['user_id']],
                            ['role_id' => $role_id, 'employee_id' => $validate['user_id']],
                        );

                 if ($role->specific_role_id == 3) {
                        if (isset($request->assign_team)) {
                                HrmsTeamMember::updateOrCreate(
                                ['member_id' => $validate['user_id']],
                                ['hrms_team_id' => $request->assign_team, 'member_id' => $validate['user_id']],
                            );
                        }
                 }
                 if ($role->specific_role_id == 4) {
                    $team = HrmsTeam::find($request->assign_team);
                    $team->team_leader = $validate['user_id'];
                    $team->save();
                 }
            }

            if (isset($request->manager_id)) {
                EmployeesUnderOfManager::updateOrCreate(
                    ['employee_id' => $validate['user_id']],
                    ['manager_id' => $request->manager_id, 'employee_id' => $validate['user_id']]
                );
            }




            $userInfo = UserInfo::where('user_id', $request->user_id)->first();

            if (!$userInfo) {
                $userInfo =  new UserInfo;
                $userInfo->user_id = $request->user_id;
            }

            $userInfo->parmanent_address = $request->parmanent_address;
            $userInfo->country = $request->country;
            $userInfo->state = $request->state;
            $userInfo->city = $request->city;
            $userInfo->assign_pc = $request->assign_pc;
            $userInfo->sallary = $request->sallary;
            $userInfo->father_name = $request->father_name;
            $userInfo->mother_name = $request->mother_name;
            $userInfo->spouse_name = $request->spouse_name;
            $userInfo->no_of_childern = $request->no_of_childern;
            $userInfo->documented_birthday = $request->documented_birthday;
            $userInfo->qualification = $request->qualification;
            $userInfo->induction_status = $request->induction_status;
            $userInfo->reporting_leader = $request->reporting_leader;
            $userInfo->interview_souce = $request->interview_souce;
            $userInfo->referal_by = $request->referal_by;
            $userInfo->referral_code = $request->referral_code; // referral code
            //$userInfo->referral_employee_id = $request->referral_employee_id;
            $userInfo->aadhar_card_number = $request->aadhar_card_number;
            $userInfo->PAN_card_number = $request->PAN_card_number;
            $userInfo->voter_id = $request->voter_id;
            $userInfo->driving_lincense = $request->driving_lincense;
            $userInfo->account_name = $request->account_name;
            $userInfo->account_number = $request->account_number;
            $userInfo->IFSC_code = $request->IFSC_code;
            $userInfo->reason = $request->reason;
            $userInfo->remark = $request->remark;
            $userInfo->PF_status = $request->PF_status;
            $userInfo->pf_no = $request->pf_no;
            $userInfo->relieving_letter = $request->relieving_letter;
            $userInfo->FNF = $request->FNF;
            $userInfo->UAN_no = $request->UAN_no;
            $userInfo->assets = $request->assets;
            $userInfo->recovery = $request->recovery;
            $userInfo->genious_employee_code = $request->genious_employee_code;
            $userInfo->salary_cycle = $request->salary_cycle;
            $userInfo->age_in_year = $request->age_in_year;
            $userInfo->skills = is_array($request->skills) ? implode(',', $request->skills) : '';
            $userInfo->experience = $request->experience;
            $userInfo->company_email = $request->company_email; //
            $userInfo->drug_policy = $request->drug_policy;
            $userInfo->transport_policy = $request->transport_policy;
            $userInfo->laptop_phone_policy = $request->laptop_phone_policy;
            $userInfo->IJP_policy = $request->IJP_policy;
            $userInfo->appraisal_policy = $request->appraisal_policy;
            $userInfo->department = $request->department;
            $userInfo->id_card_receive = $request->id_card_receive;
            $this->updateEmployeeHistory($request, $validate['user_id']);

            $userInfo->save();


            if ($cab_facility) {
                $userAddress = SubUserAddresse::where('sub_user_id', $request->user_id)->first();
                if (!$userAddress) {
                    $userAddress = new SubUserAddresse;
                    $userAddress->sub_user_id = $request->user_id;
                }
                $userAddress->address = $request->pickup_address;
                $userAddress->latitude = $request->latitude;
                $userAddress->longitude = $request->longitude;
                $userAddress->save();
            }

            return response()->json([
                'status' => true,
                'message' => "Employee Addition Information Updated Successfully"
            ],200);
        } catch (\Throwable $th) {
            //return $this->errorResponse($th->getMessage());
            return $this->errorResponse(sprintf(
                    '%s in %s on line %d',
                    $th->getMessage(),
                    $th->getFile(),
                    $th->getLine()
                ));
        }
    }





    /**
     * @OA\Get(
     * path="/uc/api/employee/get_profile_data",
     * operationId="Profile data",
     * tags={"HRMS Employee"},
     * summary="Get Employee Profile Data Request",
     *   security={ {"Bearer": {} }},
     * description="Get Employee Profile Data Request",
     *      @OA\Response(
     *          response=201,
     *          description="Employee Profile Data Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Employee Profile Data Get Successfully",
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


    public function getProfileData(){
        try {
             $id = auth('sanctum')->user()->id;

             $profileData = SubUser::find($id);

             $userAddress = User::find($id);

             $profileData->address = $userAddress->address ?? '';

             $designation_name = $profileData->employement_type ?? null;
             $user_designation = '';
             if (isset($designation_name)) {
                 $user_designation = Designation::where('title', $designation_name)->first();
             }

             $profileData->designation = $user_designation ?? '';

             if ($profileData) {
                    return response()->json([
                            'status' => true,
                            'data' => $profileData,
                            'message' => "User Profile Data"
                    ],200);
             }else {
                return response()->json([
                            'status' => false,
                            'message' => "Not Found Data"
                    ],404);
             }
        } catch (\Throwable $th) {
            return response()->json([
                            'status' => false,
                            'message' => $th->getMessage(),
                    ],500);
        }
    }




    /**
     * @OA\Post(
     * path="/uc/api/employee/update_profile",
     * operationId="authupdateProfile",
     * tags={"HRMS Employee"},
     * summary="Update Employee Profile Request",
     *   security={ {"Bearer": {} }},
     * description="Update Employee Profile Request",
     *   *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="first_name", type="string"),
     *              @OA\Property(property="last_name", type="string"),
     *              @OA\Property(property="email", type="string"),
     *              @OA\Property(property="phone", type="string"),
     *              @OA\Property(property="address", type="string"),
     *              @OA\Property(property="gender", type="string"),
     *              @OA\Property(property="dob", type="date"),
     *              @OA\Property(property="marital_status", type="string"),
     *              @OA\Property(property="blood_group", type="string"),
     *              @OA\Property(property="emergency_contact", type="string"),
     *              @OA\Property(property="experience", type="string"),
     *              @OA\Property(property="position", type="string"),
     *              @OA\Property(property="employee_shift", type="string"),
     *              @OA\Property(property="shift_type", type="string"),
     *              @OA\Property(property="profile_image", type="string", format="binary"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Update Employee Profile Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Update Employee Profile Successfully",
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


     public function updateProfile(Request $request){
          try {
               $validated = $request->validate([
                   'first_name' => 'required|string',
                   'last_name' => 'nullable|string',
                   'position' => 'nullable|integer',
               ]);


               $designation = Designation::find($request->position);


               $id = auth('sanctum')->user()->id;

               $temp_DB_name = DB::connection()->getDatabaseName();

               $default_DBName = env("DB_DATABASE");
               $this->connectDB($default_DBName);  // parent database connection

               $authUser = SubUser::find($id);

               $authUser->first_name = $request->first_name;
               $authUser->last_name = $request->last_name;
               $authUser->email = $request->email;
               $authUser->phone = $request->phone;
              // $authUser->address = $request->address;
               $authUser->gender = $request->gender;
               $authUser->dob = $request->dob;
               $authUser->marital_status = $request->marital_status;
               $authUser->mobile = $request->emergency_contact;
             //  $authUser->experience = $request->experience;
               $authUser->employement_type = $designation->title ?? '';
               $authUser->employee_shift = $request->employee_shift;
               $authUser->shift_type = $request->shift_type;

               if ($request->hasFile('profile_image')) {
                $imgName = time() . "_" . $request->file('profile_image')->getClientOriginalName();
                $request->file('profile_image')->move(public_path('profile_image'), $imgName);

                $authUser->profile_image = $imgName;
               }

               $authUser->save();

               $this->connectDB($temp_DB_name);

               //// ******  Child User Update
               $childUser = User::find($id);

               $childUser->first_name = $request->first_name;
               $childUser->last_name = $request->last_name;
               $childUser->email = $request->email;
               $childUser->phone = $request->phone;
               $childUser->address = $request->address;
               $childUser->gender = $request->gender;
               $childUser->dob = $request->dob;
               $childUser->marital_status = $request->marital_status;
               $childUser->mobile = $request->emergency_contact;
             //  $childUser->experience = $request->experience;
               $childUser->employement_type = $designation->title ?? '';
               $childUser->employee_shift = $request->employee_shift;
               $childUser->shift_type = $request->shift_type;

               if ($request->hasFile('profile_image')) {
                $childUser->profile_image = $imgName;
               }

               $childUser->save();

               /// ***********Child Sub User

               $childSubUser = SubUser::find($id);

               $childSubUser->first_name = $request->first_name;
               $childSubUser->last_name = $request->last_name;
               $childSubUser->email = $request->email;
               $childSubUser->phone = $request->phone;
               //$childSubUser->address = $request->address;
               $childSubUser->gender = $request->gender;
               $childSubUser->dob = $request->dob;
               $childSubUser->marital_status = $request->marital_status;
               $childSubUser->mobile = $request->emergency_contact;
             //  $childSubUser->experience = $request->experience;
               $childSubUser->employement_type = $designation->title ?? '';
               $childSubUser->employee_shift = $request->employee_shift;
               $childSubUser->shift_type = $request->shift_type;

               if ($request->hasFile('profile_image')) {
                  $childSubUser->profile_image = $imgName;
               }

               $childSubUser->save();

               $userInfo = UserInfo::where('user_id',$id)->first();

               if ($userInfo) {
                  $userInfo->experience = $request->experience;
                  $userInfo->save();
               }

               return response()->json([
                  'status' => true,
                  'message' => "Profile Update Successfully"
               ],200);


          } catch (\Throwable $th) {
              return response()->json([
                  'status' => false,
                  'message' => $th->getMessage()
              ]);
          }
     }





     /**
     * @OA\Post(
     * path="/uc/api/sendOtpForgotPassword",
     * operationId="sendOtp",
     * tags={"HRMS Employee"},
     * summary="Send OTP For Forgot Password",
     *   security={ {"Bearer": {} }},
     * description="Send OTP For Forgot Password",
     *   *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="email", type="email"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Send OTP For Forgot Password Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Send OTP For Forgot Password Successfully",
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


     public function sendOtpForgotPassword(Request $request){
         try {
                $validated = $request->validate(['email' => 'required|email']);

                $default_DBName = env('DB_DATABASE');

                $this->connectDB($default_DBName);

                $user = User::where('email',$validated['email'])->first();
                if(!$user){
                    $user = SubUser::where('email',$validated['email'])->first();
                }


                if (!$user) {
                   return response()->json(['status' => false,'message' => "Incorrect Email" ],401);
                }

                $OTP = rand(100000, 999999);


                $user->otp = $OTP;
                $user->save();

                $emailData = [
                    'email' => $validated['email'],
                    'otp' => $OTP,
                    'name' => $user->first_name.' '.$user->last_name,
                    'date' => now()->format('Y-m-d'),
                ];

                $email = $validated['email'];
                Mail:: send("email.forgotPasswordOTP",['emailData' => $emailData], function ($message) use ($email) {
                        $message->to($email)
                                ->subject("OTP Send For Forgot Password");
                });

                return response()->json(['status' => true, 'message' => 'OTP Send Successfully'],200);

         } catch (\Throwable $th) {
             return response()->json([
                'status' => false,
                'message' => $th->getMessage()
             ],500);
         }
     }



      /**
     * @OA\Post(
     * path="/uc/api/varifyForgotPasswordOTP",
     * operationId="matchOtp",
     * tags={"HRMS Employee"},
     * summary="Match OTP For Forgot Password",
     *   security={ {"Bearer": {} }},
     * description="Match OTP For Forgot Password",
     *   *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="email", type="email"),
     *              @OA\Property(property="otp", type="string"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Match OTP For Forgot Password Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Match OTP For Forgot Password Successfully",
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


     public function varifyForgotPasswordOTP(Request $request){
        try {
              $validated = $request->validate(['email' => 'required|email', 'otp' => 'required']);

              $default_DBName = env('DB_DATABASE');

              $this->connectDB($default_DBName);


              $user = User::where('email', $validated['email'])->where('otp',$validated['otp'])->first();
              if (!$user) {
                $user = SubUser::where('email', $validated['email'])->where('otp',$validated['otp'])->first();
              }


              if (!$user) {
                  return response()->json(['status' => false, 'message' => 'Invalid OTP'],401);
              }

              $otpCreatedTime = Carbon::parse($user->updated_at);

              if (Carbon::now()->diffInMinutes($otpCreatedTime) > 5) {
                  return response()->json(['status' => false, 'message' => 'OTP expired'],401);
              }

              return response()->json(['status' => true, 'message' => ' OTP verified successfully. You can now reset your password.']);
        } catch (\Throwable $th) {
             return response()->json(['status' => false, 'message' => $th->getMessage()],500);
        }
     }



    /**
     * @OA\Post(
     * path="/uc/api/change_password",
     * operationId="changePassword",
     * tags={"HRMS Employee"},
     * summary="Change Password",
     *   security={ {"Bearer": {} }},
     * description="Change Password",
     *   *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="email", type="email"),
     *              @OA\Property(property="password", type="string"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Change Password Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Change Password Successfully",
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

     public function changePassword(Request $request){
           try {
                   $validated = $request->validate(['email' => 'required|email', 'password' => 'required']);

                   $default_DBName = env('DB_DATABASE');

                   $this->connectDB($default_DBName);

                   $user = User::where('email',$validated['email'])->first();
                   if (!$user) {
                      $user = SubUser::where('email',$validated['email'])->first();
                   }

                   $user_BD_name = $user->database_name;

                   $password = Hash::make($validated['password']);

                   $user->password = $password;
                   $user->save();

                   $this->connectDB($user_BD_name);

                   $chieldUser = User::where('email',$validated['email'])->first();
                   if ($chieldUser) {
                      $chieldUser->password = $password;
                      $chieldUser->save();
                   }


                   $chieldSubUser = SubUser::where('email',$validated['email'])->first();
                   if ($chieldSubUser) {
                       $chieldSubUser->password = $password;
                       $chieldSubUser->save();
                   }


                   UpdateEmployeeHistory::create([
                        'employee_id' => $user->id,
                        'updated_by' => auth('sanctum')->id() ?? $user->id,
                        'date' => now()->format('Y-m-d'),
                        'time' => now()->format('H:i:s'),
                        'notes' => 'Password changed',
                        'changed' => 'Password was changed',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                   return response()->json(['status' => true, 'message' => 'Change Password Successfully']);

           } catch (\Throwable $th) {
                return response()->json(['status' => false, 'message' => $th->getMessage()],500);
           }
     }




     /**
     * @OA\Post(
     * path="/uc/api/employee/updateOfficeStatus",
     * operationId="updateOfficeStatus",
     * tags={"HRMS Employee"},
     * summary="Update Employee Office Status Request",
     *   security={ {"Bearer": {} }},
     * description="Update Employee Office Status Request",
     *   *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="employee_id", type="integer"),
     *              @OA\Property(property="status", type="integer", description="0 => Normal User, 1 => Office User"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Update Employee Office Status Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Update Employee Office Status Successfully",
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


     public function updateOfficeStatus(Request $request){
         try {

            $validatedData = $request->validate([
                'employee_id' => 'required',
                'status' => 'required|integer|in:0,1',
            ]);

              $temp_DB_name = DB::connection()->getDatabaseName();
              $default_DBName = env("DB_DATABASE");
              $this->connectDB($default_DBName);  // parent database connection

              $employee = SubUser::find($validatedData['employee_id']);
              $oldStatus = $employee->user_type;
              $employee->user_type = $validatedData['status'];
              $employee->save();


              // child database connection
              $this->connectDB($temp_DB_name);

              $employee = User::find($validatedData['employee_id']);
              $employee->user_type = $validatedData['status'];
              $employee->save();

              $employee = SubUser::find($validatedData['employee_id']);
              $employee->user_type = $validatedData['status'];
              $employee->save();

              // Log the status change history
                try {
                    $user = auth('sanctum')->user();
                    if ($user) {
                        $statusMap = [
                            0 => 'Employee',
                            1 => 'Office user'
                        ];

                        $oldStatusName = $statusMap[$oldStatus] ?? $oldStatus;
                        $newStatusName = $statusMap[$validatedData['status']] ?? $validatedData['status'];

                        UpdateEmployeeHistory::create([
                            'employee_id' => $validatedData['employee_id'],
                            'date' => now()->format('Y-m-d'),
                            'time' => now()->format('H:i:s'),
                            'updated_by' => $user->id,
                            'notes' => 'Office Status Changed',
                            'changed' => "Status changed from {$oldStatusName} to {$newStatusName}",
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                } catch (\Throwable $e) {
                    \Log::error('Failed to log Office Status change: ' . $e->getMessage());
                }

              return response()->json([
                'status' => true,
                'message' => 'Office Status Chenged Successfully'
              ]);
         } catch (\Throwable $th) {
              return response()->json(['status' => false, 'message' => $th->getMessage()]);
         }
     }


    /**
     * @OA\Post(
     *     path="/uc/api/employee/generateReferralCode",
     *     operationId="generateReferralCode",
     *     tags={"HRMS Employee"},
     *     summary="Generate Employee Referral Code",
     *     security={ {"Bearer": {} }},
     *     description="Generate a unique 5-character referral code that expires in 15 days",
     *     @OA\Response(
     *         response=200,
     *         description="Referral Code Generated Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="code", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to generate referral code"),
     *             @OA\Property(property="error", type="string", example="SQLSTATE[HY000] [2002] Connection refused")
     *         )
     *     )
     * )
     */


    public function generateReferralCode(Request $request)
    {
        try {
            $default_DBName = env("DB_DATABASE");
            $temp_DB_name = DB::connection()->getDatabaseName();

            $user_id = auth('sanctum')->user()->id;
            $referralCode = strtoupper(Str::random(8));

            // Switch to parent DB
            $this->connectDB($default_DBName);

            // Keep generating until we find a unique code in parent DB
            while (SubUser::where('referral_code', $referralCode)->exists()) {
                $referralCode = strtoupper(Str::random(8));
            }

            // Set expiration date (15 days from now)
            $expireDate = now()->addDays(15);

            // Update in parent DB
            $parentUser = SubUser::find($user_id);
            $parentUser->referral_code = $referralCode;
            $parentUser->expires_at = $expireDate;
            $parentUser->save();

            // Switch back to child DB
            $this->connectDB($temp_DB_name);

            // Update in child DB
            $childUser = SubUser::find($user_id);
            $childUser->referral_code = $referralCode;
            $childUser->expires_at = $expireDate;
            $childUser->save();

            return response()->json([
                'status' => true,
                'referral_code' => $referralCode,
                'expires_at' => $expireDate->format('Y-m-d H:i:s'),
                'message' => "Generate Referral Code Successfully"
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate referral code',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * @OA\Post(
     * path="/uc/api/employee/cabFacility",
     * operationId="cabFacility",
     * tags={"HRMS Employee"},
     * summary="Cab Facility Request",
     *   security={ {"Bearer": {} }},
     * description="Cab Facility Request",
     *    @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="cab_facility", type="integer", description="Cab Facility, 1 => Yes, 0 => No"),
     *             @OA\Property(property="address", type="string", description="Pickup Address"),
     *             @OA\Property(property="latitude", type="number", format="float", description="Latitude"),
     *             @OA\Property(property="longitude", type="number", format="float", description="Longitude"),
     *           ),
     *     ),
     *      @OA\Response(
     *          response=201,
     *          description="Cab Facility Request Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Cab Facility Request Successfully",
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


     public function cabFacility(Request $request){
            try {
                    $validated = $request->validate([
                        'cab_facility' => 'required|integer|in:0,1',
                        'address' => 'nullable|string|max:255',
                        'latitude' => 'nullable|numeric',
                        'longitude' => 'nullable|numeric',
                        'user_id' => 'required|integer|exists:sub_users,id',
                    ]);

                    $chield_DB_name = DB::connection()->getDatabaseName();
                    $default_DBName = env("DB_DATABASE");
                    $this->connectDB($default_DBName);  // parent database connection

                    $parentUser = SubUser::find($validated['user_id']);
                    if (!$parentUser) {
                        return response()->json([
                            'status' => false,
                            'message' => "User Not Found"
                        ], 404);
                    }


                    if($validated['cab_facility'] != $parentUser->cab_facility){
                        if($validated['cab_facility'] == 0){
                           $newValue = "No";
                        }else {
                            $newValue = "Yes";
                        }
                        if($parentUser->cab_facility == 0){
                            $oldValue = "No";
                        }else {
                            $oldValue = "Yes";
                        }
                        $changedData[] = "Cab Facility: {$oldValue} to {$newValue}";
                    }

                    $parentUser->cab_facility = (int) $validated['cab_facility'];
                    $parentUser->save();

                    $this->connectDB($chield_DB_name); // child database connection

                    $childUser = User::find($validated['user_id']);
                    $childUser->cab_facility = (int) $validated['cab_facility'];
                    if (isset($validated['address']) && !empty($validated['address'])) {
                        $childUser->latitude =  $validated['latitude'];
                        $childUser->longitude =  $validated['longitude'];
                    }
                    $childUser->save();

                    $childSubUser = SubUser::find($validated['user_id']);
                    $childSubUser->cab_facility = (int) $validated['cab_facility'];
                    $childSubUser->save();

                    if (isset($validated['address']) && !empty($validated['address'])) {
                         $sub_user_address = SubUserAddresse::where('sub_user_id', $childSubUser->id)->first();
                        if ($sub_user_address) {
                                $changedData[] = "Address Changed: {$sub_user_address->address} to {$validated['address']}";
                                info("Em: {$sub_user_address->address}");
                                $sub_user_address->address = $validated['address'];
                                $sub_user_address->latitude = $validated['latitude'];
                                $sub_user_address->longitude = $validated['longitude'];
                                $sub_user_address->save();

                        } else {
                                $sub_new_address = new SubUserAddresse();

                                $changedData[] = "Address Changed: Empty to {$validated['address']}";

                                $sub_new_address->sub_user_id = $childSubUser->id;
                                $sub_new_address->address = $validated['address'];
                                $sub_new_address->latitude = $validated['latitude'];
                                $sub_new_address->longitude = $validated['longitude'];
                                $sub_new_address->save();

                        }

                    }
                    foreach ($changedData as $change) {
                        UpdateEmployeeHistory::create([
                            'employee_id' => $validated['user_id'],
                            'date' => now()->format('Y-m-d'),
                            'time' => now()->format('H:i:s'),
                            'updated_by' => auth('sanctum')->id(),
                            'notes' => "Cab Facility Request",
                            'changed' => $change,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }


                    return response()->json([
                        'status' => true,
                        'message' => "Cab Facility Request Successfully"
                    ], 200);

            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
     }


    /**
     * @OA\Post(
     *     path="/uc/api/employee/updateEmployeeHistory/{id}",
     *     operationId="updateEmployeeHistory",
     *     tags={"HRMS Employee"},
     *     summary="Update Employee Details and Log History",
     *     security={ {"Bearer": {} }},
     *     description="Updates employee details and logs every change in the update_employee_histories table.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Employee ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="first_name", type="string", description="First name"),
     *                 @OA\Property(property="last_name", type="string", description="Last name"),
     *                 @OA\Property(property="email", type="string", description="Email address"),
     *                 @OA\Property(property="phone", type="string", description="Phone number"),
     *                 @OA\Property(property="address", type="string", description="Address"),
     *                 @OA\Property(property="role", type="integer", description="Role ID"),
     *                 @OA\Property(property="gender", type="string", description="Gender"),
     *                 @OA\Property(property="dob", type="date", description="Date of birth"),
     *                 @OA\Property(property="profile_image", type="string", format="binary", description="Profile image"),
     *                 @OA\Property(property="marital_status", type="string", description="Marital status"),
     *                 @OA\Property(property="status", type="integer", description="Employment status"),
     *                 @OA\Property(property="blood_group", type="string", description="Blood group"),
     *                 @OA\Property(property="experience", type="string", description="Experience"),
     *                 @OA\Property(property="shift_type", type="string", description="Shift type"),
     *                 @OA\Property(property="doj", type="date", description="Date of joining"),
     *                 @OA\Property(property="note", type="string", description="Change note"),
     *                 @OA\Property(property="employee_shift", type="string", description="Employee shift"),
     *                 @OA\Property(property="employement_type", type="string", description="Employment type"),
     *                 @OA\Property(property="mobile", type="string", description="Mobile number"),
     *                 @OA\Property(property="unique_id", type="string", description="Unique employee ID"),
     *                 @OA\Property(property="skills", type="string", description="Skills (comma separated)"),
     *                 @OA\Property(property="company_email", type="string", description="Company email"),
     *                 @OA\Property(property="cab_facility", type="integer", description="Cab facility (1=Yes, 0=No)"),
     *                 @OA\Property(property="latitude", type="number", format="float", description="Latitude"),
     *                 @OA\Property(property="longitude", type="number", format="float", description="Longitude"),
     *                 @OA\Property(property="department", type="string", description="Department"),
     *                 @OA\Property(property="assign_employee_position", type="integer", description="Assign employee position"),
     *                 @OA\Property(property="manager_id", type="integer", description="Manager ID"),
     *                 @OA\Property(property="assign_team", type="integer", description="Team ID"),
     *                 @OA\Property(property="current_status", type="integer", description="Current status"),
     *                 @OA\Property(property="parmanent_address", type="string", description="Permanent address"),
     *                 @OA\Property(property="country", type="string", description="Country"),
     *                 @OA\Property(property="state", type="string", description="State"),
     *                 @OA\Property(property="city", type="string", description="City"),
     *                 @OA\Property(property="assign_pc", type="string", description="Assigned PC"),
     *                 @OA\Property(property="sallary", type="string", description="Salary"),
     *                 @OA\Property(property="father_name", type="string", description="Father's name"),
     *                 @OA\Property(property="mother_name", type="string", description="Mother's name"),
     *                 @OA\Property(property="spouse_name", type="string", description="Spouse's name"),
     *                 @OA\Property(property="no_of_childern", type="integer", description="Number of children"),
     *                 @OA\Property(property="documented_birthday", type="string", format="date", description="Documented birthday"),
     *                 @OA\Property(property="induction_status", type="string", description="Induction status"),
     *                 @OA\Property(property="reporting_leader", type="string", description="Reporting leader"),
     *                 @OA\Property(property="interview_souce", type="string", description="Interview source"),
     *                 @OA\Property(property="referal_by", type="string", description="Referred by"),
     *                 @OA\Property(property="aadhar_card_number", type="string", description="Aadhar card number"),
     *                 @OA\Property(property="PAN_card_number", type="string", description="PAN card number"),
     *                 @OA\Property(property="voter_id", type="string", description="Voter ID"),
     *                 @OA\Property(property="driving_lincense", type="string", description="Driving license"),
     *                 @OA\Property(property="account_name", type="string", description="Account name"),
     *                 @OA\Property(property="account_number", type="string", description="Account number"),
     *                 @OA\Property(property="IFSC_code", type="string", description="IFSC code"),
     *                 @OA\Property(property="reason", type="string", description="Reason"),
     *                 @OA\Property(property="remark", type="string", description="Remark"),
     *                 @OA\Property(property="PF_status", type="string", description="PF status"),
     *                 @OA\Property(property="relieving_letter", type="string", description="Relieving letter"),
     *                 @OA\Property(property="FNF", type="string", description="FNF"),
     *                 @OA\Property(property="UAN_no", type="string", description="UAN number"),
     *                 @OA\Property(property="assets", type="string", description="Assets"),
     *                 @OA\Property(property="recovery", type="string", description="Recovery"),
     *                 @OA\Property(property="genious_employee_code", type="string", description="Genius employee code"),
     *                 @OA\Property(property="salary_cycle", type="string", description="Salary cycle"),
     *                 @OA\Property(property="age_in_year", type="string", description="Age in years"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Employee updated and changes logged.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employee not found",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent()
     *     )
     * )
     */

    public function updateEmployeeHistory(Request $request, $id)
    {
        try {
            $employee = SubUser::find($id);
            if (!$employee) return $this->errorResponse("Employee not found");

            $getRole = $request->role ?? null;
            $currentRoleId = optional(HrmsEmployeeRole::where('employee_id', $id)->first())->role_id;

            $user = User::find($id);
            $userInfo = UserInfo::where('user_id', $id)->first();
            $subUserAddress = SubUserAddresse::where('sub_user_id', $id)->first();

            $note = $request->input('note', 'Employee details updated');
            $ignoredFields = ['updated_at', 'created_at', 'latitude', 'longitude', 'user_id', 'employee_shift'];
            $historyLogs = [];

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $fileName = time() . "_" . $file->getClientOriginalName();
                $file->move(public_path('profile_image'), $fileName);
                $request->merge(['profile_image' => $fileName]);
            }

            // Map of model data for easier comparison
            $models = [
                'SubUser' => [$employee, $employee?->getOriginal()],
                'User' => [$user, $user?->getOriginal()],
                'UserInfo' => [$userInfo, $userInfo?->getOriginal()],
                'SubUserAddresse' => [$subUserAddress, $subUserAddress?->getOriginal()],
            ];

            foreach ($request->except($ignoredFields) as $field => $newValue) {
                foreach ($models as [$modelInstance, $originalData]) {
                    if ($modelInstance && array_key_exists($field, $originalData)) {
                        $oldValue = $originalData[$field] ?? null;
                        $normalizedOld = $this->normalizeValue($oldValue);
                        $normalizedNew = $this->normalizeValue($newValue);

                        if ($normalizedOld !== $normalizedNew) {
                            $changedData = $this->getEmployeeChangeLogString($field, $oldValue, $newValue);
                            if ($changedData) {
                                $historyLogs[] = $this->prepareLogEntry($employee->id, $changedData, $note);
                                $modelInstance->$field = $newValue;
                            }
                        }
                        break;
                    }
                }
            }

            // Handle role change
            if ($getRole && $getRole != $currentRoleId) {
                $oldRole = optional(HrmsRole::find($currentRoleId))->name ?? 'None';
                $newRole = optional(HrmsRole::find($getRole))->name ?? 'None';
                $changedData = $this->getEmployeeChangeLogString('role', $oldRole, $newRole);

                if ($changedData) {
                    $historyLogs[] = $this->prepareLogEntry($employee->id, $changedData, $note);

                    HrmsEmployeeRole::updateOrCreate(
                        ['employee_id' => $id],
                        ['role_id' => $getRole, 'employee_id' => $id]
                    );
                }
            }

            // Save all updated models
            foreach ([$employee, $user, $userInfo, $subUserAddress] as $model) {
                if ($model && $model->isDirty()) {
                    $model->save();
                }
            }

            // Insert history logs
            if (!empty($historyLogs)) {
                UpdateEmployeeHistory::insert($historyLogs);
            }

            return $this->successResponse($employee, "Employee updated and changes logged.");
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

    // Helper to prepare log entry
    private function prepareLogEntry($employeeId, $changed, $note)
    {
        return [
            'employee_id' => $employeeId,
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'updated_by' => auth('sanctum')->id(),
            'notes' => $note,
            'changed' => $changed,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function normalizeValue($value)
    {
        if (is_null($value) || $value === '') return null;
        if (is_bool($value)) return $value ? 1 : 0;
        return is_scalar($value) ? trim((string)$value) : $value;
    }

    /**
     * Helper to create a readable change log string for employee fields
     */
    private function getEmployeeChangeLogString($field, $oldValue, $newValue)
    {
        if ($field === 'password') {
            return 'Password was changed';
        }

        $readableFieldName = str_replace('_', ' ', $field);

        $displayOldValue = $this->formatValueForDisplay($oldValue, $field);
        $displayNewValue = $this->formatValueForDisplay($newValue, $field);

        return ucfirst($readableFieldName) . " changed from {$displayOldValue} to {$displayNewValue}";
    }

    private function formatValueForDisplay($value, $field)
    {
        if (is_null($value)) return 'empty';
        if ($value === '') return 'empty string';
        if (is_array($value)) return json_encode($value);
        if (is_bool($value)) return $value ? 'true' : 'false';

        // Handle special field cases
        switch ($field) {
            case 'gender':
                return $this->genderDisplay($value);
            case 'cab_facility':
                return $this->yesNoDisplay($value);
            case 'shift_type':
                return $value == 1 ? 'Evening Shift' : 'Morning Shift';
            default:
                return $value;
        }
    }

    private function genderDisplay($val)
    {
        if ($val === null || $val === '') return 'empty';
        if ($val == 1) return 'Female';
        if ($val == 2) return 'Male';
        return $val;
    }

    private function yesNoDisplay($val)
    {
        if ($val === null || $val === '') return 'empty';
        if ($val == 0) return 'No';
        if ($val == 1) return 'Yes';
        return $val;
    }


    /**
     * @OA\Get(
     * path="/uc/api/employee/sendBirthDayEmail",
     * operationId="sendBirthDayEmail",
     * tags={"HRMS Employee"},
     * summary="sendBirthDayEmail",
     *   security={ {"Bearer": {} }},
     * description="sendBirthDayEmail",
     *      @OA\Response(
     *          response=201,
     *          description="send BirthDay Email Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="send BirthDay Email Get Successfully",
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

    public function sendBirthDayEmail(){
        try {

             $todayMonth = now()->format('m');
             $todayDay = now()->format('d');

             $databases = User::whereNotNull('database_name')->get();

             foreach ($databases as $key => $database) {
                try {
                           $this->connectDB($database->database_name);

                    $birthdayUser = SubUser::whereMonth('dob', $todayMonth)->whereDay('dob', $todayDay)->get();

                    if ($birthdayUser->isNotEmpty()) {
                            foreach ($birthdayUser as $key => $user) {
                                $user_id = $user->id;
                                $getManagerList = TeamManager::with(['employees','teams.teamLeader', 'teams.teamMembers.user'])->get();
                                    foreach ($getManagerList as $key => $manager) {
                                        foreach ($manager->teams as $key => $team) {
                                            if (optional($team->team_leader == $user_id)) {
                                                $team_manager_id =  $manager->id;
                                                break 2;
                                            }else {

                                                foreach ($team->teamMembers as $key => $member) {

                                                    if (optional($member->user->id == $user_id)) {
                                                            $team_manager_id =  $manager->id;
                                                            break 2;
                                                    }
                                                }
                                            }


                                        }
                                    }

                                $getTeamList = TeamManager::with(['employees','teams.teamLeader', 'teams.teamMembers.user'])->where('id', $team_manager_id)->get();
                                $ids = [];
                                if ($getTeamList) {
                                    foreach ($getTeamList as $key => $manager) {
                                            $ids[] = (int) $manager->employees[0]->id;
                                                foreach ($manager->teams as $key => $team) {
                                                    $ids[] = (int) $team->team_leader;
                                                        foreach ($team->teamMembers as $key => $member) {
                                                        $ids[] = (int) $member->member_id;
                                                        }

                                                }
                                    }
                                }

                                $memberusers =  SubUser::whereIn('id', $ids)->get();

                                $userData = [
                                    'name' => $user->first_name." ".$user->last_name,
                                    'year' => now()->format('Y'),
                                    'company' => $user->company_name
                                ];

                                foreach ($memberusers as $member) {
                                    Mail::to($member->email)->send(new BirthdayEmail($userData));
                                }


                            }

                }
                } catch (\Throwable $th) {
                       // info('log...'.$th->getMessage().' in '.$th->getFile().' on line '.$th->getLine());
                        continue;
                }

             }


        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ],500);
        }
    }


    /**
     * @OA\Post(
     *     path="/uc/api/employee/EmployeeHistoryList",
     *     operationId="employeeHistoryList",
     *     tags={"HRMS Employee"},
     *     summary="Employee Update History List",
     *     security={{"Bearer": {}}},
     *     description="Get history of updates for an employee",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"employee_id"},
     *               @OA\Property(property="employee_id", type="integer", description="ID of the employee"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=200,
     *          description="Employee update History List Retrieved Successfully",
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


    public function EmployeeHistoryList(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors());
            }

            $employee = SubUser::find($request->employee_id);

            if (!$employee) {
                return $this->errorResponse("Employee not found");
            }

            $history = UpdateEmployeeHistory::with([
                    'employee:id,first_name,last_name',
                    'changedBy:id,first_name,last_name'
                ])
                ->where('employee_id', $request->employee_id)
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->get();

            return $this->successResponse(
                $history,
                "Employee history retrieved successfully"
            );

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }



    /**
     * @OA\Post(
     *     path="/uc/api/employee/newEmployeeAssignPC",
     *     operationId="newEmployeeAssignPC",
     *     tags={"HRMS Employee"},
     *     summary="Employee Assign PC",
     *     security={{"Bearer": {}}},
     *     description="Employee Assign PC",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"employee_id"},
     *               @OA\Property(property="employee_id", type="integer", description="ID of the employee"),
     *               @OA\Property(property="is_assign", type="integer", description="1 for Assigned, 0 for removed to assigned"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=200,
     *          description="Employee Assigned PC Successfully",
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

     public function newEmployeeAssignPC(Request $request){
            try {
                    $validated = $request->validate([
                        'employee_id' => 'required|integer|exists:sub_users,id',
                        'is_assign' => 'required|integer|in:1,0',
                    ]);

                    $userInfo = UserInfo::where('user_id', $validated['employee_id'])->first();
                    if ($userInfo) {
                        $userInfo->assign_pc_status = $validated['is_assign'];
                        $userInfo->save();
                    }else {
                        info("User Info not found for employee ID: {$validated['employee_id']}");
                    }
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ],500);
            }
     }



    /**
     * @OA\Post(
     *     path="/uc/api/employee/uploadedEmployeelatLong",
     *     operationId="uploadedEmployeelatLong",
     *     tags={"HRMS Employee"},
     *     summary="Employee Assign PC",
     *     security={{"Bearer": {}}},
     *     description="Employee Assign PC",
     *      @OA\Response(
     *          response=200,
     *          description="Employee Assigned PC Successfully",
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


    // public function uploadedEmployeelatLong(Request $request)
    // {
    //     try {

    //      return   $SubUserAddressess = SubUserAddresse::whereNotNull('address')
    //                                     ->where('address', '!=', '')
    //                                     ->first();

    //         foreach ($SubUserAddressess as $key => $SubUserAddresses) {
    //              $latLong = $this->getLatLongFromAddress($SubUserAddresses->address);

    //             $SubUserAddresses->latitude = $latLong['lat'];
    //             $SubUserAddresses->longitude = $latLong['lng'];
    //             $SubUserAddresses->save();

    //             $user = User::find($SubUserAddresses->sub_user_id);
    //             if ($user) {
    //                 $user->latitude = $latLong['lat'];
    //                 $user->longitude = $latLong['lng'];
    //                 $user->save();
    //             }


    //         }

    //         return response()->json([
    //             'status' => true,
    //             'message' => "Latitude and Longitude retrieved successfully",
    //             'data' => $latLong
    //         ], 200);

    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $th->getMessage()
    //         ], 500);
    //     }
    // }

    public function uploadedEmployeelatLong(Request $request)
    {
        try {

           $tempDBname = DB::connection()->getDatabaseName();

            $default_DB = env("DB_DATABASE");
            $this->connectDB($default_DB);
            UpdateUserLatLong::dispatch($tempDBname);

            // Process addresses in chunks of 100 to handle large datasets efficiently
            // SubUserAddresse::whereNotNull('address')
            //     ->where('address', '!=', '')
            //     ->chunk(100, function ($SubUserAddressesChunk) {
            //         foreach ($SubUserAddressesChunk as $SubUserAddresses) {
            //             $latLong = $this->getLatLongFromAddress($SubUserAddresses->address);

            //             $SubUserAddresses->latitude = $latLong['lat'];
            //             $SubUserAddresses->longitude = $latLong['lng'];
            //             $SubUserAddresses->save();

            //             $user = User::find($SubUserAddresses->sub_user_id);
            //             if ($user) {
            //                 $user->latitude = $latLong['lat'];
            //                 $user->longitude = $latLong['lng'];
            //                 $user->save();
            //             }
            //         }
            //     });

            // return response()->json([
            //     'status' => true,
            //     'message' => "Latitude and Longitude updated successfully for all records",
            // ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    function getLatLongFromAddress($address)
    {
        $apiKey = 'AIzaSyAjESclgKTCpmdmHKc7bzCKIQtYe_vIbb4';
        $address = urlencode($address);
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$apiKey}";
        $resp_json = file_get_contents($url);
        $resp = json_decode($resp_json, true);

        if ($resp['status'] == 'OK') {
            $lat = $resp['results'][0]['geometry']['location']['lat'];
            $lng = $resp['results'][0]['geometry']['location']['lng'];
            return ['lat' => $lat, 'lng' => $lng];
        }
        return ['lat' => null, 'lng' => null];
    }


    /**
     * @OA\Post(
     *     path="/uc/api/employee/updateDriverStatus",
     *     operationId="updateDriverStatus",
     *     tags={"HRMS Employee"},
     *     summary="Update a driver's status",
     *     description="Updates the status of a driver in both the parent and child databases without affecting separation or history records.",
     *     security={ {"Bearer": {}} },
     *     @OA\RequestBody(
     *         required=true,
     *         description="Provide the driver's ID and their new status.",
     *         @OA\JsonContent(
     *             required={"employee_id", "status"},
     *             @OA\Property(property="employee_id", type="integer", example=123, description="The unique ID of the driver."),
     *             @OA\Property(property="status", type="integer", example=2, description="The new status ID. (1: Active, 2: Inactive, 6: Terminated, etc.)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Driver status updated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Driver Status Updated Successfully"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Driver not found in the parent database."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error if employee_id or status are missing or invalid."
     *     )
     * )
     */

    public function updateDriverStatus(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'employee_id' => 'required|integer|exists:sub_users,id',
                'status' => 'required|integer|in:1,2,3,4,5,6,7,8',
            ]);

            $temp_DB_name = DB::connection()->getDatabaseName();
            $default_DBName = env("DB_DATABASE");

            // --- Parent DB Operations ---
            $this->connectDB($default_DBName);
            $parentEmployee = SubUser::find($validatedData['employee_id']);

            if (!$parentEmployee) {
                return $this->errorResponse('Driver not found in the parent database.');
            }

            $newStatus = $validatedData['status'];
            $changeCab = in_array((int) $newStatus, [2, 6, 7, 8]);

            // Update parent employee record
            $parentEmployee->status = $newStatus;
            $parentEmployee->save();

            // --- Child DB Operations ---
            $this->connectDB($temp_DB_name);

            // Update child 'users' table record, if it exists
            $childUser = User::find($validatedData['employee_id']);
            if ($childUser) {
                $childUser->status = $newStatus;
                if ($changeCab) {
                    $childUser->cab_facility = 0;
                }
                $childUser->save();
            }

            // Update child 'sub_users' table record, if it exists
            $childSubUser = SubUser::find($validatedData['employee_id']);
            if ($childSubUser) {
                $childSubUser->status = $newStatus;
                if ($changeCab) {
                    $childSubUser->cab_facility = 0;
                }
                $childSubUser->save();
            }

            return $this->successResponse([], "Driver Status Updated Successfully");
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

}

