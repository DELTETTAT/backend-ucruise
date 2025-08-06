<?php

namespace App\Http\Controllers\Api;

use App\Mail\SendEmail;
use App\Mail\SendMailToUser;
use App\Models\ClientDocuments;
use App\Models\Holiday;
use App\Models\Invoice;
use App\Models\Leave;
use App\Models\Reschedule;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\ScheduleCarer;
use App\Models\ScheduleCarerRelocation;
use App\Models\ScheduleCarerStatus;
use App\Models\SubUser;
use App\Models\SubUserAddresse;
use App\Models\Teams;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\RouteGroupUser;
use App\Models\RouteGroup;
use App\Models\HrmsTimeAndShift;
use App\Models\RouteGroupSchedule;
use App\Models\CompanyAddresse;
use App\Models\PriceBook;
use App\Models\ShiftTypes;
use App\Models\GroupLoginUser;
use App\Models\TeamManager;
use App\Models\EmployeeTeamManager;
use App\Models\HrmsTeam;
use App\Models\HrmsTeamMember;
use App\Models\Designation;
use App\Models\HrmsEmployeeRole;
use App\Models\HrmsRole;
use App\Models\UserInfo;
use App\Models\Resignation;
use App\Models\CompanyDetails;
use App\Models\ScheduleCarerComplaint;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\{DB, Mail, Hash};
use Illuminate\Support\Facades\Http;
use Validator;
use App\Models\Rating;
use App\Models\PriceTableData;
use Illuminate\Validation\Rule;


class StaffController extends Controller
{

    // *********************** Add staff api **************************************

    /**
     * @OA\Post(
     * path="/uc/api/addStaff",
     * operationId="addStaff",
     * tags={"Ucruise Employee"},
     * summary="Store Employee",
     *   security={ {"Bearer": {} }},
     * description="Store Employee",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={ "name", "employee_id", "dob", "doj", "position", "email", "phone", "gender", "marital_status", "address", "latitude", "longitude"},
     *               @OA\Property(property="profileImage", type="file"),
     *               @OA\Property(property="name", type="text"),
     *               @OA\Property(property="employee_id", type="text"),
     *               @OA\Property(property="dob", type="date"),
     *               @OA\Property(property="doj", type="date"),
     *               @OA\Property(property="position", type="text"),
     *               @OA\Property(property="gender", type="text"),
     *               @OA\Property(property="email", type="text"),
     *               @OA\Property(property="phone", type="text"),
     *               @OA\Property(property="address", type="text"),
     *               @OA\Property(property="latitude", type="text"),
     *               @OA\Property(property="longitude", type="text"),
     *               @OA\Property(property="marital_status", type="text"),
     *               @OA\Property(property="worked_for", type="text"),
     *               @OA\Property(property="emergency_contact", type="integer", description="Emergency contact number"),
     *               @OA\Property(property="shift_type", type="string", description="Shift type: 0 = Morning shift, 1 = Evening shift (next finish day)"),
     *               @OA\Property(
     *                property="user_type",
     *                     type="integer",
     *                     enum={0, 1, 2},
     *                     default=0,
     *                     description="User type: 0 = Office user, 1 = Admin, 2 = Super admin"
     *                 )
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The staff added successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The staff added successfully.",
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




    public function addStaff(Request $request)
    {
        try {
            //store current DB name in temp variable
            $temp_DB_name = DB::connection()->getDatabaseName();

            //check if there existing staff in child DB for entered information
            $request->validate([
                "email" => "required|email|unique:sub_users,email",
                "phone" => "required|unique:sub_users,phone",
                'emergency_contact' => 'nullable',
                'name' => 'required',
                'last_name' => 'nullable',
                'dob' => 'required|date|date_format:Y-m-d',
                'doj' => 'required|date|date_format:Y-m-d',
                'employee_id' => 'required|unique:sub_users,unique_id',
                'position' => 'required',
                'role' => 'nullable|exists:hrms_roles,id',
                'gender' => 'nullable ',
                'address' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
                'marital_status' => 'required',
                'shift_type' => 'required',
                'employee_shift' => 'nullable',

            ]);

            $designation = Designation::find($request->position);
            //connecting to parent DB
            $default_DBName = env("DB_DATABASE");
            $this->connectDB($default_DBName);
            $company_name = auth('sanctum')->user()->company_name;

            // Check if the email already exists for the same company (excluding current user)
            $existingDriver = SubUser::where('email', $request->email)
                ->where('company_name', $company_name)  // Check for the same company
                ->first();
            //creating staff in parent DB
            // $unique_id = rand(1000, 9999) . str_pad(10, 3, STR_PAD_LEFT);
            $rand = Str::random(10);
            $password = $existingDriver ? $existingDriver->password : Hash::make($rand);
            $staff = new SubUser();
            $staff->first_name = $request->name;
            $staff->last_name = $request->last_name;
            $staff->unique_id = $request->employee_id;
            $staff->email = $request->email;
            $staff->phone = $request->phone;
            $staff->mobile = $request->emergency_contact;
            $staff->gender = $request->gender;
            //$staff->role = $request->role;
            $staff->dob = date('Y-m-d', strtotime($request->dob));
            $staff->doj = date('Y-m-d', strtotime($request->doj));
            $staff->employement_type = $designation->title ?? null;
            $staff->marital_status = $request->marital_status;
            $staff->worked_for = $request->worked_for;
            $staff->blood_group = @$request->blood_group;
            $staff->user_type = isset($request->user_type) ? $request->user_type : "0";
            $staff->shift_type = @$request->shift_type;
            $staff->employee_shift = @$request->employee_shift ?? "Morning Shift";

            $staff->company_name = auth('sanctum')->user()->company_name;
            $staff->database_path = env("DB_HOST");
            $staff->database_name = $temp_DB_name;
            $staff->database_username = env("DB_USERNAME");
            $staff->database_password = env("DB_PASSWORD");
            $getRole =  'carer';
            // $rand = Str::random(10);
            // $password = Hash::make($rand);

            //$password = $password;
            // $driver->password = $password;
            $staff->password = $password;
            if ($request->hasFile('profileImage')) {
                $path = public_path('images/');
                !is_dir($path) &&
                    mkdir($path, 0777, true);

                $profilefilename = time() . '.' . $request->profileImage->extension();
                $request->profileImage->move($path, $profilefilename);
                $staff->profile_image = $profilefilename;
            }
            $admin = Role::where('name', 'staff')->first();
            if ($staff->save()) {
                if (!$staff->hasRole($getRole)) {
                    $staff->roles()->attach($admin);
                }
            }

            //connecting back to Child DB
            $this->connectDB($temp_DB_name);

            //create staff in users table in child DB

            $child_user = new User();
            $child_user->id = $staff->id;
            $child_user->first_name = $request->name;
            $child_user->last_name = $request->last_name;
            $child_user->unique_id = $request->employee_id;
            $child_user->email = $request->email;
            $child_user->phone = $request->phone;
            $child_user->mobile = $request->emergency_contact;
            $child_user->gender = $request->gender;
            $child_user->dob = date('Y-m-d', strtotime($request->dob));
            $child_user->doj = date('Y-m-d', strtotime($request->doj));
            $child_user->employement_type = $designation->title ?? null;
            $child_user->worked_for = $request->worked_for;
            $child_user->marital_status = $request->marital_status;
            $child_user->company_name = $staff->company_name;

            $child_user->blood_group = @$request->blood_group;
            $child_user->user_type = ($request->has('user_type') && !empty($request->user_type)) ? $request->user_type : "0";
            $child_user->shift_type = @$request->shift_type;
            $child_user->employee_shift = @$request->employee_shift ?? "Morning Shift";

            $child_user->database_path = env("DB_HOST");
            $child_user->database_name = $temp_DB_name;
            $child_user->database_username = env("DB_USERNAME");
            $child_user->database_password = env("DB_PASSWORD");
            $child_user->latitude = $request->latitude;
            $child_user->longitude = $request->longitude;

            if ($request->hasFile('profileImage')) {
                $child_user->profile_image = $profilefilename;
            }

            $child_user->password = $password;
            $child_user->save();
            $getRole =  'carer';

            $admin = Role::where('name', $getRole)->first();

            if ($child_user->save()) {
                if (!$child_user->hasRole($getRole)) {
                    $child_user->roles()->attach($admin);
                }
            }
            $child_staff = new SubUser();

            $child_staff->id = $staff->id;
            $child_staff->first_name = $request->name;
            $child_staff->last_name = $request->last_name;
            $child_staff->unique_id = $request->employee_id;
            $child_staff->email = $request->email;
            $child_staff->phone = $request->phone;
            $child_staff->mobile = $request->emergency_contact;
            $child_staff->gender = $request->gender;
            $child_staff->dob = date('Y-m-d', strtotime($request->dob));
            $child_staff->doj = date('Y-m-d', strtotime($request->doj));
            $child_staff->worked_for = $request->worked_for;
            $child_staff->marital_status = $request->marital_status;
            $child_staff->company_name = $staff->company_name;
            $child_staff->employement_type = $designation->title ?? null;
            $child_staff->blood_group = @$request->blood_group;
            $child_staff->shift_type = @$request->shift_type;
            $child_staff->employee_shift = @$request->employee_shift ?? "Morning Shift";
            $child_staff->user_type = ($request->has('user_type') && !empty($request->user_type)) ? $request->user_type : "0";
            $child_staff->database_path = env("DB_HOST");
            $child_staff->database_name = $temp_DB_name;
            $child_staff->database_username = env("DB_USERNAME");
            $child_staff->database_password = env("DB_PASSWORD");
            $child_staff->password = $password;
            if ($request->hasFile('profileImage')) {
                $child_staff->profile_image = $profilefilename;
            }

            $getRole = 'carer';
            $admin = Role::where('name', $getRole)->first();

            if ($child_staff->save()) {
                if (!$child_staff->hasRole($getRole)) {
                    $child_staff->roles()->attach($admin);
                }
            }

              if (isset($request->experience)) {
                UserInfo::updateOrCreate(
                    ['user_id' => $staff->id],
                    ['experience' => $request->experience]
                );
            }

            $this->data["detais"] = [
                "email" => $request->email,
                "pass" => $rand,
                "unique_id" => $request->employee_id,
                "first_name" => $request->name,
            ];
            $email = $request->email;
            $subject = "Welcome to UCruise! Your Account is Successfully Created";
            Mail::to($email)->send(new SendMailToUser($this->data["detais"], $subject));

            $sub_user = SubUser::find($child_staff->id);

            if ($sub_user) {
                $sub_user_address = SubUserAddresse::where('sub_user_id', $sub_user->id)->whereNull('end_date')->first();

                if ($sub_user_address) {
                    if ($sub_user_address->start_date == date('Y-m-d')) {
                        $sub_user_address->sub_user_id = $sub_user->id;
                        $sub_user_address->address = $request->address;
                        $sub_user_address->latitude = $request->latitude;
                        $sub_user_address->longitude = $request->longitude;
                    } else {
                        $sub_user_address->end_date = date('Y-m-d');
                        $sub_new_address = new SubUserAddresse();
                        $sub_new_address->sub_user_id = $sub_user->id;
                        $sub_new_address->address = $request->address;
                        $sub_new_address->latitude = $request->latitude;
                        $sub_new_address->longitude = $request->longitude;
                        $sub_new_address->start_date = date('Y-m-d');
                        $sub_new_address->save();
                    }
                    $sub_user_address->update();
                } else {
                    $sub_new_address = new SubUserAddresse();
                    $sub_new_address->sub_user_id = $sub_user->id;
                    $sub_new_address->address = $request->address;
                    $sub_new_address->latitude = $request->latitude;
                    $sub_new_address->longitude = $request->longitude;
                    $sub_new_address->start_date = date('Y-m-d');
                    $sub_new_address->save();
                }
            }


             if (isset($request->role) && !empty($request->role)) {
                $role = HrmsRole::find($request->role);
                if ($role) {
                    HrmsEmployeeRole::updateOrCreate(
                        ['employee_id' => $sub_user->id],
                        ['role_id' => $role->id, 'employee_id' => $sub_user->id]
                    );
                } else {
                    return $this->errorResponse("The given role is not found");
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully added staff"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }



    //************************ Delete Staff api *********************************/

    /**
     * @OA\Post(
     * path="/uc/api/deleteStaff",
     * operationId="deleteStaff",
     * tags={"Ucruise Employee"},
     * summary="Delete Employee",
     *   security={ {"Bearer": {} }},
     * description="Delete Employee",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={ "id"},
     *               @OA\Property(property="id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Staff deleted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Staff deleted successfully.",
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

    public function deleteStaff(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required'
            ]);
           

            // Find and delete sub user
            $sub_user = SubUser::findOrFail($request->id);

            // Find and delete associated user
            $user = User::where('id', $request->id)->firstOrFail();

            // Switch to default database if needed (same as original)
            $temp_DB_name = DB::connection()->getDatabaseName();
            $default_DBName = env("DB_DATABASE");
            $this->connectDB($default_DBName);
            DB::table('role_sub_user')->where('sub_user_id', $sub_user->id)->delete();
            // Permanently delete all subusers with this email (using forceDelete)
            SubUser::where('id', $sub_user->id)->forceDelete();

            // Switch back to original database
            $this->connectDB($temp_DB_name);
            DB::table('role_sub_user')->where('sub_user_id', $sub_user->id)->delete();
            DB::table('role_user')->where('user_id', $sub_user->id)->delete();
            // Permanently delete the main records

            // Delete if assigned manager , team leader or team member
            $teamManager = EmployeeTeamManager::where('employee_id', $sub_user->id)->delete();
            $teamLeader = HrmsTeam::where('team_leader', $sub_user->id)->first();
            if ($teamLeader) {
                $teamLeader->team_leader = null;
                $teamLeader->save();
            }
            $teamMember = HrmsTeamMember::where('member_id', $sub_user->id)->delete();
            $teamManager = UserInfo::where('user_id', $sub_user->id)->delete();

            $sub_user->forceDelete();
            $user->forceDelete();




            return response()->json([
                'success' => true,
                'message' => "Employee deleted successfully"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //     public function deleteStaff(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'id' => 'required'
    //         ]);
    //        $numbers = [3186, 3187];

    // foreach($numbers as $number) {
    //         // Find and delete sub user
    //         $sub_user = SubUser::findOrFail($number);

    //         // Find and delete associated user
    //         //$user = User::where('id', $number)->firstOrFail();
    //         $user = User::where('id', $number)->firstOrFail();

    //         // Switch to default database if needed (same as original)
    //         $temp_DB_name = DB::connection()->getDatabaseName();
    //         $default_DBName = env("DB_DATABASE");
    //         $this->connectDB($default_DBName);
    //         DB::table('role_sub_user')->where('sub_user_id', $sub_user->id)->delete();
    //         // Permanently delete all subusers with this email (using forceDelete)
    //         SubUser::where('id', $sub_user->id)->forceDelete();

    //         // Switch back to original database
    //         $this->connectDB($temp_DB_name);
    //         DB::table('role_sub_user')->where('sub_user_id', $sub_user->id)->delete();
    //         DB::table('role_user')->where('user_id', $sub_user->id)->delete();
    //         // Permanently delete the main records

    //         // Delete if assigned manager , team leader or team member
    //         $teamManager = EmployeeTeamManager::where('employee_id', $sub_user->id)->delete();
    //         $teamLeader = HrmsTeam::where('team_leader', $sub_user->id)->first();
    //         if ($teamLeader) {
    //             $teamLeader->team_leader = null;
    //             $teamLeader->save();
    //         }
    //         $teamMember = HrmsTeamMember::where('member_id', $sub_user->id)->delete();
    //         $teamManager = UserInfo::where('user_id', $sub_user->id)->delete();

    //         $sub_user->forceDelete();
    //         $user->forceDelete();

    //     }


    //         return response()->json([
    //             'success' => true,
    //             'message' => "Employee deleted successfully"
    //         ], 200);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $th->getMessage()
    //         ], 500);
    //     }
    // }




    // *********************** Update staff api **************************************

    /**
     * @OA\Post(
     * path="/uc/api/updateStaff",
     * operationId="updateStaff",
     * tags={"Ucruise Employee"},
     * summary="Store Employee",
     *   security={ {"Bearer": {} }},
     * description="Store Employee",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id", "name", "dob", "doj", "position", "phone", "gender", "marital_status", "address", "latitude", "longitude"},
     *               @OA\Property(property="id", type="text"),
     *               @OA\Property(property="profileImage", type="file"),
     *               @OA\Property(property="name", type="text"),
     *               @OA\Property(property="dob", type="date"),
     *               @OA\Property(property="doj", type="date"),
     *               @OA\Property(property="position", type="text"),
     *               @OA\Property(property="phone", type="text"),
     *               @OA\Property(property="gender", type="text"),
     *               @OA\Property(property="address", type="text"),
     *               @OA\Property(property="email", type="text"),
     *               @OA\Property(property="latitude", type="text"),
     *               @OA\Property(property="longitude", type="text"),
     *               @OA\Property(property="marital_status", type="text"),
     *               @OA\Property(property="worked_for", type="text"),
     *               @OA\Property(property="blood_group", type="text"),
     *               @OA\Property(property="shift_type", type="string", description="Shift type: 0 = Morning shift, 1 = Evening shift (next finish day)")
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The staff updated successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The staff updated successfully.",
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
    public function updateStaff(Request $request)
    {
        try {

            $request->validate([
                'id' => 'required',
                'name' => 'required',
                //'last_name' => 'nullable',
                'dob' => 'required|date|date_format:Y-m-d',
                'doj' => 'required|date|date_format:Y-m-d',
                // 'email' => 'required|email|unique:sub_users,email,' . $request->id,
                // 'unique_id' => 'required|unique:sub_users,unique_id,' . $request->id,
                'email' => [
                    'required',
                    'email',
                    Rule::unique('sub_users')->ignore($request->id),
                    Rule::unique('users')->ignore($request->id, 'id')
                ],
                'unique_id' => [
                    'required',
                    Rule::unique('sub_users')->ignore($request->id),
                    Rule::unique('users')->ignore($request->id, 'id')
                ],
                'position' => 'required',
                'phone' => 'required ',
                //'emergency_contact' => 'nullable',
                'gender' => 'required ',
                'address' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
                'marital_status' => 'required',
                'shift_type' => 'required',

            ]);
            $temp_DB_name = DB::connection()->getDatabaseName();
            //connecting to parent DB
            $default_DBName = env("DB_DATABASE");
            //dd($default_DBName);
            $this->connectDB($default_DBName);

            // $existingEmail = SubUser::where('email', $request->email)
            //     ->where('id', '!=', $request->id)
            //     ->first();
            // if ($existingEmail) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'The provided email already exists in the system.',
            //     ], 400);
            // }

            // $existingunique = SubUser::where('unique_id', $request->unique_id)
            //     ->where('id', '!=', $request->id)
            //     ->first();
            // if ($existingunique) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'The provided employee_id already exists in the system.',
            //     ], 400);
            // }

            $update = SubUser::where('id', $request->id)->first();
            $update->first_name = $request->name;
            //$update->last_name = $request->last_name;
            //  $update->email = $request->email;
            // $update->unique_id = $request->unique_id;
            $update->unique_id = "UTS-" . $request->id;
            $update->gender = $request->gender;
            $update->dob = date('Y-m-d', strtotime($request->dob));
            $update->doj = date('Y-m-d', strtotime($request->doj));
            $update->worked_for = $request->worked_for;
            $update->phone = $request->phone;
            //$update->mobile = $request->emergency_contact;
            $update->blood_group = @$request->blood_group;
            $update->shift_type = @$request->shift_type;

            $update->marital_status = $request->marital_status;
            if ($request->hasFile('profileImage')) {
                $path = public_path('images/');
                !is_dir($path) &&
                    mkdir($path, 0777, true);

                $profilefilename = time() . '.' . $request->profileImage->extension();
                $request->profileImage->move($path, $profilefilename);
                $update->profile_image = $profilefilename;
            }

            $update->save();
            //connecting back to Child DB
            $this->connectDB($temp_DB_name);
            // $existingUserEmail = User::where('email', $request->email)
            //     ->where('id', '!=', $request->id)
            //     ->first();
            // if ($existingUserEmail) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'The provided email already exists in the system ',
            //     ], 400);
            // }
            // $existingunique = User::where('unique_id', $request->unique_id)
            //     ->where('id', '!=', $request->id)
            //     ->first();
            // if ($existingunique) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'The provided employee_id already exists in the system.',
            //     ], 400);
            // }


            $update_user = User::where('email', $update->email)->first();

            $update_user->first_name = $request->name;
            //$update_user->last_name = $request->last_name;
            $update_user->email = $request->email;
            // $update_user->unique_id = $request->unique_id;
            $update_user->unique_id = "UTS-" . $request->id;

            $update_user->gender = $request->gender;
            $update_user->dob = date('Y-m-d', strtotime($request->dob));
            $update_user->doj = date('Y-m-d', strtotime($request->doj));
            $update_user->phone = $request->phone;
            //$update_user->mobile = $request->emergency_contact;
            $update_user->marital_status = $request->marital_status;
            $update_user->employement_type = $request->position;
            $update_user->worked_for = $request->worked_for;
            $update_user->blood_group = @$request->blood_group;
            $update_user->shift_type = @$request->shift_type;
            $update_user->address = $request->address;
            $update_user->latitude = $request->latitude;
            $update_user->longitude = $request->longitude;

            if ($request->hasFile('profileImage')) {
                $update_user->profile_image = $profilefilename;
            }


            $update_user->save();
            $update->email = $request->email;
            $update->save();
            // $existingUserEmail = SubUser::where('email', $request->email)
            //     ->where('id', '!=', $request->id)
            //     ->first();
            // if ($existingUserEmail) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'The provided email already exists in the  system ',
            //     ], 400);
            // }
            // $existingunique = SubUser::where('unique_id', $request->unique_id)
            //     ->where('id', '!=', $request->id)
            //     ->first();
            // if ($existingunique) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'The provided employee_id already exists in the system.',
            //     ], 400);
            // }
            $child_update = SubUser::where('id', $request->id)->first();

            $child_update->first_name = $request->name;
            //$child_update->last_name = $request->last_name;
            $child_update->email = $request->email;
            // $child_update->unique_id = $request->unique_id;
            $child_update->unique_id = "UTS-" . $request->id;
            $child_update->gender = $request->gender;
            $child_update->dob = date('Y-m-d', strtotime($request->dob));
            $child_update->doj = date('Y-m-d', strtotime($request->doj));
            $child_update->phone = $request->phone;
            //$child_update->mobile = $request->emergency_contact;
            $child_update->employement_type = $request->position;
            $child_update->marital_status = $request->marital_status;
            $child_update->worked_for = $request->worked_for;
            $child_update->blood_group = @$request->blood_group;
            $child_update->shift_type = @$request->shift_type;

            if ($request->hasFile('profileImage')) {
                $child_update->profile_image = $profilefilename;
            }
            $child_update->save();
            $sub_user = SubUser::find($child_update->id);

            if ($sub_user) {

                $sub_user_address = SubUserAddresse::where('sub_user_id', $sub_user->id)->whereNull('end_date')->first();

                if ($sub_user_address) {
                    if ($sub_user_address->start_date == date('Y-m-d')) {

                        $sub_user_address->sub_user_id = $sub_user->id;
                        $sub_user_address->address = $request->address;
                        $sub_user_address->latitude = $request->latitude;
                        $sub_user_address->longitude = $request->longitude;
                    } else if ($sub_user_address->start_date > date('Y-m-d')) {
                    } else {
                        $sub_user_address->end_date = date('Y-m-d');
                        $sub_new_address = new SubUserAddresse();

                        $sub_new_address->sub_user_id = $sub_user->id;
                        $sub_new_address->address = $request->address;
                        $sub_new_address->latitude = $request->latitude;
                        $sub_new_address->longitude = $request->longitude;
                        $sub_new_address->start_date = date('Y-m-d');
                        $sub_new_address->save();
                    }
                    $sub_user_address->update();
                } else {

                    $sub_new_address = new SubUserAddresse();
                    $sub_new_address->sub_user_id = $sub_user->id;
                    $sub_new_address->address = $request->address;
                    $sub_new_address->latitude = $request->latitude;
                    $sub_new_address->longitude = $request->longitude;
                    $sub_new_address->start_date = date('Y-m-d');
                    $sub_new_address->save();
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully updated staff"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // ************************** Add driver api *************************************

    /**
     * @OA\Post(
     * path="/uc/api/addDriver",
     * operationId="addDriver",
     * tags={"Ucruise Driver"},
     * summary="Store driver",
     *   security={ {"Bearer": {} }},
     * description="Store driver",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={ "name", "position", "email", "phone",  "emergency_no", "address", "latitude", "longitude",   "model", "chasis_no", "seats", "vehicle_no", "registration_no", "color", "pricebook_id","shift_type_id"},
     *               @OA\Property(property="profileImage", type="file"),
     *               @OA\Property(property="name", type="text"),
     *               @OA\Property(property="position", type="text"),
     *               @OA\Property(property="email", type="text"),
     *               @OA\Property(property="emergency_no", type="text"),
     *               @OA\Property(property="phone", type="text"),
     *               @OA\Property(property="address", type="text"),
     *               @OA\Property(property="latitude", type="text"),
     *               @OA\Property(property="longitude", type="text"),
     *               @OA\Property(property="vehicleImage", type="file"),
     *               @OA\Property(property="model", type="text"),
     *               @OA\Property(property="chasis_no", type="text"),
     *               @OA\Property(property="seats", type="text"),
     *               @OA\Property(property="color", type="text"),
     *               @OA\Property(property="vehicle_no", type="text"),
     *               @OA\Property(property="registration_no", type="text"),
     *               @OA\Property(property="blood_group", type="text"),
     *               @OA\Property(property="pricebook_id", type="text"),
     *               @OA\Property(property="shift_type_id", type="integer", description="1 => pick, 2 => pick and drop, 3 => drop"),
     *               @OA\Property(property="verified_by", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The driver added successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The driver added successfully.",
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

    public function addDriver(Request $request)
    {

        try {

            //store current DB name in temp variable
            $temp_DB_name = DB::connection()->getDatabaseName();

            //check if there existing driver in child DB for entered information
            $request->validate([
                "email" => "required|email",
                'name' => 'required',
                'position' => 'required ',
                'phone' => 'required',
                'emergency_no' => 'required',
                'address' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
                'model' => 'required',
                'chasis_no' => 'required',
                'seats' => 'required',
                'color' => 'required',
                'vehicle_no' => 'required',
                'registration_no' => 'required',
                'pricebook_id' => 'required',
                'shift_type_id' => 'required'
            ]);

            //connecting back to Child DB
            $this->connectDB($temp_DB_name);

            //connecting to parent DB
            $default_DBName = env("DB_DATABASE");

            $this->connectDB($default_DBName);

            //$existingDriver = SubUser::where('email', $request->email)->first();
            $company_name = auth('sanctum')->user()->company_name;

            // Check if the email already exists for the same company (excluding current user)
            $existingDriver = SubUser::where('email', $request->email)
                ->where('company_name', $company_name)  // Check for the same company
                ->first();

            if ($existingDriver) {
                return response()->json([
                    'success' => false,
                    'message' => 'The provided email already exists for the same company.',
                ], 400);
            }
            $rand = Str::random(10);
            // If the email exists, use the existing password; otherwise, generate a new one
            $password = $existingDriver ? $existingDriver->password : Hash::make($rand);

            //checking if there is existing driver with same information in parent DB


            $existingDriver = SubUser::where('email', $request->email)->get();
            $count = $existingDriver->count();
            $add = false;
            if ($count >= 1) {
                $add = true;
            }


            //creating driver in parent DB
            $driver = new SubUser();
            $driver->first_name = $request->name;
            $driver->phone = $request->phone;
            $driver->mobile = $request->emergency_no;
            $driver->employement_type = $request->position;
            $driver->verified_by = @$request->verified_by;
            $driver->blood_group = @$request->blood_group;
            $driver->email = $request->email;

            //$password = $password;
            $driver->password = $password;
            $driver->company_name = auth('sanctum')->user()->company_name;
            $driver->database_path = env("DB_HOST");
            $driver->database_name = $temp_DB_name;
            $driver->database_username = env("DB_USERNAME");
            $driver->database_password = env("DB_PASSWORD");

            if ($request->hasFile('profileImage')) {
                $path = public_path('images/');
                !is_dir($path) &&
                    mkdir($path, 0777, true);

                $profilefilename = time() . '.' . $request->profileImage->extension();
                $request->profileImage->move($path, $profilefilename);
                //\DB::table('company_details')->updateOrInsert(['id' => 1],['logo' => $filename]);
                $driver->profile_image = $profilefilename;
            }

            $driver->save();

            if ($add === true) {
                $existingDriver = SubUser::where('email', $request->email)->pluck('id')->toArray();
                $groupData = [
                    'email' => $request->email,
                    'user_id' =>  $existingDriver
                ];
                GroupLoginUser::updateOrCreate(['email' => $request->email], $groupData);
            }

            $role = Role::where("name", "driver")->first();

            // Manage Role
            if (!$driver->hasRole("driver")) {
                $driver->roles()->attach($role);
            }


            //connecting back to Child DB
            $this->connectDB($temp_DB_name);

            //creating driver in Staffs table in child db
            $child_driver = new SubUser();
            $child_driver->id = $driver->id;

            //$child_driver->salutation = @$request->salutation;
            $child_driver->first_name = $request->name;
            $child_driver->dob = date('Y-m-d', strtotime($request->dob));
            $child_driver->mobile = $request->emergency_no;
            $child_driver->phone = $request->phone;
            $child_driver->email = $request->email;
            $child_driver->employement_type = $request->position;
            $child_driver->blood_group = @$request->blood_group;
            $child_driver->verified_by = @$request->verified_by;
            $child_driver->pricebook_id = @$request->pricebook_id;
            $child_driver->password = $password;
            $child_driver->company_name = $driver->company_name;


            $child_driver->database_path = env("DB_HOST");
            $child_driver->database_name = $temp_DB_name;
            $child_driver->database_username = env("DB_USERNAME");
            $child_driver->database_password = env("DB_PASSWORD");

            if ($request->hasFile('profileImage')) {
                $child_driver->profile_image = $profilefilename;
            }
            $child_driver->save();
            $role = Role::where("name", "driver")->first();
            // Manage Role
            if (!$child_driver->hasRole("driver")) {
                $child_driver->roles()->attach($role);
            }

            $this->data["detais"] = [
                "email" => $request->email,
                "pass" => $rand,
                "first_name" => $request->name
            ];
            $email = $request->email;
            $subject = "Welcome to UCruise! Your Account is Successfully Created";
            Mail::to($email)->send(new SendMailToUser($this->data["detais"], $subject));
            $sub_user = SubUser::find($child_driver->id);

            if ($sub_user) {
                $sub_user_address = SubUserAddresse::where('sub_user_id', $sub_user->id)->whereNull('end_date')->first();

                if ($sub_user_address) {
                    if ($sub_user_address->start_date == date('Y-m-d')) {

                        $sub_user_address->sub_user_id = $sub_user->id;
                        $sub_user_address->address = $request->address;
                        $sub_user_address->latitude = $request->latitude;
                        $sub_user_address->longitude = $request->longitude;
                    } else {
                        $sub_user_address->end_date = date('Y-m-d');
                        $sub_new_address = new SubUserAddresse();
                        $sub_new_address->sub_user_id = $sub_user->id;
                        $sub_new_address->address = $request->address;
                        $sub_new_address->latitude = $request->latitude;
                        $sub_new_address->longitude = $request->longitude;
                        $sub_new_address->start_date = date('Y-m-d');
                        $sub_new_address->save();
                    }
                    $sub_user_address->update();
                } else {

                    $sub_new_address = new SubUserAddresse();
                    $sub_new_address->sub_user_id = $sub_user->id;
                    $sub_new_address->address = $request->address;
                    $sub_new_address->latitude = $request->latitude;
                    $sub_new_address->longitude = $request->longitude;
                    $sub_new_address->start_date = date('Y-m-d');
                    $sub_new_address->save();
                }
            }
            //Storing vehicle info
            if ($sub_user->id) {
                $vehicle = new Vehicle();
                $vehicle->name = $request->model;
                $vehicle->driver_id = $sub_user->id;
                //$vehicle->description = $request->description;
                $vehicle->chasis_no = $request->chasis_no;
                $vehicle->seats = $request->seats;
                $vehicle->registration_no = $request->registration_no;
                $vehicle->vehicle_no = $request->vehicle_no;
                $vehicle->color = $request->color;
                $vehicle->shift_type_id = @$request->shift_type_id;
                //$vehicle->fare = $request->fare;
                if ($request->hasFile('vehicleImage')) {

                    $path = public_path('images/vehicles');
                    !is_dir($path) &&
                        mkdir($path, 0777, true);

                    $vehiclefilename = time() . '.' . $request->vehicleImage->extension();
                    $request->vehicleImage->move($path, $vehiclefilename);
                    //\DB::table('company_details')->updateOrInsert(['id' => 1],['logo' => $filename]);
                    $vehicle->image = $vehiclefilename;
                }
                $vehicle->save();
            }
            return response()->json([
                'success' => true,
                'message' => 'Successfully added driver',
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //************************* Delete driver api *****************************/
    /**
     * @OA\Post(
     * path="/uc/api/deleteDriver",
     * operationId="deleteDriver",
     * tags={"Ucruise Driver"},
     * summary="Delete Driver",
     *   security={ {"Bearer": {} }},
     * description="Delete Driver",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={ "id"},
     *               @OA\Property(property="id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Driver  deleted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Driver deleted successfully.",
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

    public function deleteDriver(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required'
            ]);
            $sub_user = SubUser::find($request->id);

            $driver = Schedule::where('driver_id', $sub_user->id)->exists();
            if ($driver) {
                return response()->json([
                    'success' => false,
                    'message' => "Driver cannot be deleted as it is associated with a shift"
                ], 500);
            }
            $temp_DB_name = DB::connection()->getDatabaseName();
            $default_DBName = env("DB_DATABASE");
            $this->connectDB($default_DBName);
            DB::table('role_sub_user')->where('sub_user_id', $sub_user->id)->delete();
            SubUser::where('id', $sub_user->id)->forcedelete();
            $this->connectDB($temp_DB_name);
            DB::table('role_sub_user')->where('sub_user_id', $sub_user->id)->delete();
            $sub_user->forcedelete();

            return response()->json([
                'success' => true,
                'message' => "Driver deleted succesffully"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    //************************** Update driver api ************************************

    /**
     * @OA\Post(
     * path="/uc/api/updateDriver",
     * operationId="updateDriver",
     * tags={"Ucruise Driver"},
     * summary="Update driver",
     *   security={ {"Bearer": {} }},
     * description="Update driver",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id", "name", "position", "emergency_no", "address", "latitude", "longitude", "model", "chasis_no", "seats", "vehicle_no", "registration_no", "color"},
     *               @OA\Property(property="id", type="text"),
     *               @OA\Property(property="profileImage", type="file"),
     *               @OA\Property(property="name", type="text"),
     *               @OA\Property(property="position", type="text"),
     *               @OA\Property(property="phone", type="text"),
     *               @OA\Property(property="emergency_no", type="text"),
     *               @OA\Property(property="address", type="text"),
     *               @OA\Property(property="latitude", type="text"),
     *               @OA\Property(property="longitude", type="text"),
     *               @OA\Property(property="vehicleImage", type="file"),
     *               @OA\Property(property="model", type="text"),
     *               @OA\Property(property="chasis_no", type="text"),
     *               @OA\Property(property="seats", type="text"),
     *               @OA\Property(property="color", type="text"),
     *               @OA\Property(property="vehicle_no", type="text"),
     *               @OA\Property(property="registration_no", type="text"),
     *               @OA\Property(property="blood_group", type="text"),
     *               @OA\Property(property="verified_by", type="text"),
     *               @OA\Property(property="pricebook_id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The driver updated successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The driver updated successfully.",
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
    public function updateDriver(Request $request)
    {

        try {
            $request->validate([
                'id' => 'required',
                'name' => 'required',
                'position' => 'required ',
                // 'email'=>'required',
                'email' => 'required|email',
                'phone' => 'required',
                'emergency_no' => 'required',
                'address' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
                'model' => 'required',
                'chasis_no' => 'required',
                'seats' => 'required',
                'color' => 'required',
                'vehicle_no' => 'required',
                'registration_no' => 'required',
            ]);

            $temp_DB_name = DB::connection()->getDatabaseName();

            //connecting to parent DB
            $default_DBName = env("DB_DATABASE");
            $this->connectDB($default_DBName);

            // $existingEmail = SubUser::where('email', $request->email)
            // ->where('id', '!=', $request->id)
            // ->first();
            // if ($existingEmail) {
            // return response()->json([
            // 'success' => false,
            // 'message' => 'The provided email already exists in the system.',
            // ], 400);
            // }
            //updating driver in parent DB
            $currentUser = SubUser::find($request->id);

            // If the user doesn't exist, return an error
            if (!$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            // Retrieve the company name from the current user's record
            $company_name = $currentUser->company_name;

            // Check if the email already exists for the same company, excluding the current user
            $existingEmail = SubUser::where('email', $request->email)
                ->where('company_name', $company_name) // Check for the same company
                ->where('id', '!=', $request->id)  // Exclude the current user's ID
                ->first();

            if ($existingEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'The provided email already exists for the same company.',
                ], 400);
            }
            $update = SubUser::where('id', $request->id)->first();
            $update->first_name = $request->name;
            $update->email = $request->email;
            $update->employement_type = $request->position;
            $update->phone = $request->phone;
            $update->blood_group = @$request->blood_group;
            $update->verified_by = @$request->verified_by;


            $update->mobile = $request->emergency_no;

            if ($request->hasFile('profileImage')) {
                $path = public_path('images/');
                !is_dir($path) &&
                    mkdir($path, 0777, true);

                $profilefilename = time() . '.' . $request->profileImage->extension();
                $request->profileImage->move($path, $profilefilename);
                $update->profile_image = $profilefilename;
            }

            $update->save();

            //connecting back to Child DB
            $this->connectDB($temp_DB_name);
            // $existingEmail = SubUser::where('email', $request->email)
            // ->where('id', '!=', $request->id)
            // ->first();
            // if ($existingEmail) {
            // return response()->json([
            // 'success' => false,
            // 'message' => 'The provided email already exists in the system.',
            // ], 400);
            // }
            //updating driver in Staffs table in child db
            $child_update = SubUser::where('id', $request->id)->first();
            $child_update->first_name = $request->name;
            $child_update->email = $request->email;
            $child_update->employement_type = $request->position;
            $child_update->phone = $request->phone;
            $child_update->mobile = $request->emergency_no;
            $child_update->blood_group = @$request->blood_group;
            $child_update->pricebook_id = @$request->pricebook_id;
            $child_update->verified_by = @$request->verified_by;

            if ($request->hasFile('profileImage')) {
                $child_update->profile_image = $profilefilename;
            }

            $child_update->save();

            $sub_user = SubUser::find($child_update->id);

            if ($sub_user) {

                $sub_user_address = SubUserAddresse::where('sub_user_id', $sub_user->id)->whereNull('end_date')->first();

                if ($sub_user_address) {
                    if ($sub_user_address->start_date == date('Y-m-d')) {

                        $sub_user_address->sub_user_id = $sub_user->id;
                        $sub_user_address->address = $request->address;
                        $sub_user_address->latitude = $request->latitude;
                        $sub_user_address->longitude = $request->longitude;
                    } else if ($sub_user_address->start_date > date('Y-m-d')) {
                    } else {
                        $sub_user_address->end_date = date('Y-m-d');
                        $sub_new_address = new SubUserAddresse();

                        $sub_new_address->sub_user_id = $sub_user->id;
                        $sub_new_address->address = $request->address;
                        $sub_new_address->latitude = $request->latitude;
                        $sub_new_address->longitude = $request->longitude;
                        $sub_new_address->start_date = date('Y-m-d');
                        $sub_new_address->save();
                    }
                    $sub_user_address->update();
                } else {

                    $sub_new_address = new SubUserAddresse();

                    $sub_new_address->sub_user_id = $sub_user->id;
                    $sub_new_address->address = $request->address;
                    $sub_new_address->latitude = $request->latitude;
                    $sub_new_address->longitude = $request->longitude;
                    $sub_new_address->start_date = date('Y-m-d');
                    $sub_new_address->save();
                }

                $vehicle = Vehicle::where('driver_id', $sub_user->id)->first();
                if ($vehicle) {

                    $vehicle->name = $request->model;
                    $vehicle->seats = $request->seats;
                    $vehicle->chasis_no = $request->chasis_no;
                    $vehicle->registration_no = $request->registration_no;
                    $vehicle->vehicle_no = $request->vehicle_no;
                    $vehicle->color = $request->color;
                    $vehicle->shift_type_id = @$request->shift_type_id;
                    if ($request->hasFile('vehicleImage')) {

                        $path = public_path('images/vehicles');
                        !is_dir($path) &&
                            mkdir($path, 0777, true);

                        $vehiclefilename = time() . '.' . $request->vehicleImage->extension();
                        $request->vehicleImage->move($path, $vehiclefilename);
                        //\DB::table('company_details')->updateOrInsert(['id' => 1],['logo' => $filename]);
                        $vehicle->image = $vehiclefilename;
                    }
                    $vehicle->update();
                } else {
                    $vehicle = new Vehicle();
                    $vehicle->name = $request->model;
                    $vehicle->driver_id = $sub_user->id;
                    $vehicle->chasis_no = $request->chasis_no;
                    $vehicle->seats = $request->seats;
                    $vehicle->registration_no = $request->registration_no;
                    $vehicle->vehicle_no = $request->vehicle_no;
                    $vehicle->color = $request->color;
                    $vehicle->shift_type_id = @$request->shift_type_id;
                    if ($request->hasFile('vehicleImage')) {

                        $path = public_path('images/vehicles');
                        !is_dir($path) &&
                            mkdir($path, 0777, true);

                        $vehiclefilename = time() . '.' . $request->vehicleImage->extension();
                        $request->vehicleImage->move($path, $vehiclefilename);
                        //\DB::table('company_details')->updateOrInsert(['id' => 1],['logo' => $filename]);
                        $vehicle->image = $vehiclefilename;
                    }
                    $vehicle->save();
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Driver updated successfully.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //************************* Driver details api *************************************

    /**
     * @OA\Post(
     * path="/uc/api/driverDetails",
     * operationId="driverDetail",
     * tags={"Ucruise Driver"},
     * summary="Driver details",
     *   security={ {"Bearer": {} }},
     * description="Driver details",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The driver updated successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The driver updated successfully.",
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

    public function driverDetail(Request $request)
    {
        try {
            $homeController = new HomeController();
            $accountController = new AccountSetupController();
            $user = SubUser::find($request->id);
            $user_ids = array($user->id);
            $today_date = Carbon::now()->format('Y-m-d');
            $dates = array($today_date);

            $this->data1['driver'] = [];
            $this->data1['billing'] = [];
            $this->data1['documents'] = [];
            $this->data1['schedule_report'] = [];
            $this->data1['all_schedule'] = [];

            if ($user->hasRole('driver')) {
                $this->data1['driver'] = $this->getDriverEmpoyeeById($user->id); // Retrieve driver info
                $this->data1['documents']['accepted'] = @$this->getDriverDocuments($user->id, 1); // Retrieve driver accepted documents
                $this->data1['documents']['pending'] = @$this->getDriverDocuments($user->id, 0); // Retrieve driver pending documents
                $this->data1['schedule_report'] = @$accountController->getMonthlyStats($user->id, 0);
                $this->data1['billing'] = @$this->getDriverBilling($user->id);
                $this->data1['schedules'] = $this->getWeeklyScheduleInfo($user_ids, $dates, 1, "all");
                $this->data1['driver_trips'] = $this->driverTrips($user->id);

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
                    $this->data1['all_schedule'][$key]['carers'] = @$homeController->getScheduleCarers($schedule['id'], $schedule['type'], $scheduleDate);

                    //$this->data1['alldata'][$key]['company_name'] = @CompanyDetails::first();
                    //$this->data1['all_schedule'][$key]['schedule'] = $schedule;
                }

                if ($this->data1['all_schedule']) {
                    usort($this->data1['all_schedule'], function ($a, $b) {
                        $dateTimeA = new \DateTime($a['time']);
                        $dateTimeB = new \DateTime($b['time']);

                        return $dateTimeA <=> $dateTimeB;
                    });
                }
            }
            return response()->json([
                'success' => true,
                "data" => $this->data1,
                'driver_image_url' => url('images'),
                'vehicle_image_url' => url('public/images/vehicles'),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ], 500);
        }
    }

    //******************** Function to get the driver documents************************

    public function getDriverDocuments($id, $status_id)
    {
        $clientDocuments = ClientDocuments::where('client_id', $id)->where('status', $status_id)->get();
        foreach ($clientDocuments as $document) {

            if ($document->expire) {
                $expirationDate = Carbon::parse($document->expire);
                $isExpired = $expirationDate->isPast();
                $document->expired = $isExpired;
            } else {
                $document->expired = 'no_expiration';
            }
        }

        $clientDocuments->each(function ($document) {
            $document->document_url = url('public/files/uploads/');
        });
        //dd($clientDocuments);
        return $clientDocuments->toArray();
    }

    //**************** Function to get driver and employees by id *********************

    public function getDriverEmpoyeeById($user_id)
    {
        $date = date('Y-m-d');
        $userQuery = SubUser::with(['userInfo', 'subUserAddress','statusUpdateReason','employeeShift'])->join('sub_user_addresses', 'sub_users.id', '=', 'sub_user_addresses.sub_user_id')
            ->where('sub_users.id', $user_id)
            ->where('sub_user_addresses.start_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->where('sub_user_addresses.end_date', '>', $date)
                    ->orWhereNull('sub_user_addresses.end_date');
            })
            ->select('sub_users.*', 'sub_user_addresses.address', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude');
            
        $hasDriverRole = DB::table('role_sub_user')->where('sub_user_id', $user_id)->where('role_id', 5)->exists();
        if ($hasDriverRole) {
            $userQuery->with('vehicle')->with('pricebook');
        }
        $user = $userQuery->first();

        return $user;
    }


    // ********************* Driver trips detial ****************************************//

    protected function driverTrips($driverId){

        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $today = now()->format('Y-m-d');
        $startOfMonth2 = now()->subDays(3)->format('Y-m-d');
        $company = CompanyDetails::first();
        $companyAddress = CompanyAddresse::first();
        $schedules = Schedule::with([
            'shiftType:id,name,external_id,color,created_at,updated_at',
            'driver:id,first_name,last_name,email,phone,profile_image',
            'vehicle:id,name,seats,vehicle_no',
            'carers.user:id,first_name,last_name,email,profile_image',
            'scheduleStatus.status:id,name',
            'pricebook.priceBookData:id,price_book_id,day_of_week,per_ride'
        ])
        //->whereDate('date', today())
        ->whereBetween('date', [$startOfMonth2, $today])
        ->where('driver_id', $driverId)
        ->get(['id', 'date', 'vehicle_id','driver_id', 'shift_type_id','start_time', 'end_time', 'locality', 'city', 'pricebook_id', 'latitude', 'longitude', 'created_at']);

        return [
            'company'=>[
                'company_address' => $companyAddress->address ?? $company->address ,
                'company_latitude' => $companyAddress->latitude ?? $company->latitude,
                'company_longitude' => $companyAddress->longitude ?? $company->longitude,
                'company_name' => $company->name,
                'company_logo' => $company->logo,
            ],
            'trips_details' => $schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'date' => $schedule->date,
                    'driver_id' => $schedule->driver_id,
                    'vehicle_id' => $schedule->vehicle_id,
                    'times' => [
                        'start' => $schedule->start_time,
                        'end' => $schedule->end_time
                    ],
                    'location' => [
                        'locality' => $schedule->locality,
                        'city' => $schedule->city,
                        'coordinates' => [
                            'latitude' => $schedule->latitude,
                            'longitude' => $schedule->longitude
                        ]
                    ],
                    'driver' => $schedule->driver ?[
                        'id' => $schedule->driver->id,
                        'name' => trim($schedule->driver->first_name.' '.$schedule->driver->last_name),
                        'email' => $schedule->driver->email,
                        'profile_image' => $schedule->driver->profile_image,
                    ] : null,
                    'vehicle' =>  $schedule->vehicle ? [
                        'id' => $schedule->vehicle->id,
                        'name' => $schedule->vehicle->name,
                        'seats' => $schedule->vehicle->seats,
                        'number' => $schedule->vehicle->vehicle_no
                    ] : null,
                    'shift_type' => $schedule->shiftType?->name,
                    'status' => $schedule->scheduleStatus ? [
                        'id' => $schedule->scheduleStatus->id,
                        'schedule_id' => $schedule->scheduleStatus->schedule_id,
                        'name' => $schedule->scheduleStatus->Status->name,
                        'date' => $schedule->scheduleStatus->date,
                        'status_id' => $schedule->scheduleStatus->status_id,
                        'type' => $schedule->scheduleStatus->type,
                        'times' => [
                            'start' => $schedule->scheduleStatus->start_time,
                            'end' => $schedule->scheduleStatus->end_time
                        ]
                    ] : null,
                    // 'pricebook' => $schedule->pricebook ?? null,
                    'pricebook' => $schedule->pricebook ? [
                        'id' => $schedule->pricebook->id,
                        'name' => $schedule->pricebook->name,
                        'latitude' => $schedule->pricebook->latitude,
                        'longitude' => $schedule->pricebook->longitude,
                        'per_ride' => optional(
                            collect($schedule->pricebook->priceBookData)->first(function ($data) use ($schedule) {
                                $day = \Carbon\Carbon::parse($schedule->date)->format('l'); // e.g., Monday, Tuesday...
                                return match (strtolower($day)) {
                                    'saturday' => strtolower($data->day_of_week) === 'saturday',
                                    'sunday' => strtolower($data->day_of_week) === 'sunday',
                                    default => strtolower($data->day_of_week) === 'weekdays (mon- fri)',
                                };
                            })
                        )?->per_ride,
                    ] : null,
                    'carers' => $schedule->carers->map(function ($carer) {
                        return $carer->user ? [
                            'id' => $carer->user->id,
                            'name' => trim($carer->user->first_name . ' ' . $carer->user->last_name),
                            'email' => $carer->user->email,
                            'image' => $carer->user->profile_image ?? null
                        ] : null;
                    })->filter()->values()
                ];
            })
        ];

    }


    /**
     * @OA\post(
     * path="/uc/api/driverTripDetailsOfMonth",
     * operationId="driverTripDetailsOfMonthtrips",
     * tags={"Ucruise Driver"},
     * summary="Driver details",
     *   security={ {"Bearer": {} }},
     * description="Driver details",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="text"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Data listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Data listed successfully",
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


    //  public function driverTripDetailsOfMonth(Request $request){

    //         $user = SubUser::find($request->id);
    //         if ($user->hasRole('driver')) {
    //             $company = CompanyDetails::first();
    //             $companyAddress = CompanyAddresse::first();

    //             // Get first day of current month and today's date
    //             $startOfMonth = now()->startOfMonth()->format('Y-m-d');
    //             $today = now()->format('Y-m-d');

    //             // Get all schedules from start of month to today
    //             $schedules = Schedule::with([
    //                 'shiftType:id,name,external_id,color,created_at,updated_at',
    //                 'driver:id,first_name,last_name,email,phone,profile_image,verified_by',

    //                 'vehicle:id,name,seats,vehicle_no',
    //                 'carers.user:id,first_name,last_name,email,profile_image',
    //                 'scheduleStatus.status:id,name',
    //                 'pricebook.priceBookData:id,price_book_id,day_of_week,per_ride'
    //             ])
    //             ->whereBetween('date', [$startOfMonth, $today])
    //              ->where('driver_id', $request->id)
    //             ->get(['id', 'date', 'vehicle_id','driver_id', 'shift_type_id','start_time', 'end_time', 'locality', 'city', 'pricebook_id', 'latitude', 'longitude', 'created_at']);

    //             // Convert date strings to Carbon instances for grouping
    //             $schedules->each(function ($item) {
    //                 $item->date = \Carbon\Carbon::parse($item->date);
    //             });

    //             // Group schedules by date
    //             $groupedSchedules = $schedules->groupBy(function ($item) {
    //                 return $item->date->format('Y-m-d');
    //             });

    //             // Create a date range from start of month to today
    //             $dateRange = collect();
    //             $currentDate = \Carbon\Carbon::parse($startOfMonth);
    //             $endDate = \Carbon\Carbon::parse($today);

    //             while ($currentDate <= $endDate) {
    //                 $dateString = $currentDate->format('Y-m-d');
    //                 $dateRange[$dateString] = [
    //                     'data' => $groupedSchedules->has($dateString) ?
    //                         $groupedSchedules[$dateString]->map(function ($schedule) {
    //                             return [
    //                                 'id' => $schedule->id,
    //                                 'date' => $schedule->date->format('Y-m-d'),
    //                                 'driver_id' => $schedule->driver_id,
    //                                 'vehicle_id' => $schedule->vehicle_id,
    //                                 'times' => [
    //                                     'start' => $schedule->start_time,
    //                                     'end' => $schedule->end_time
    //                                 ],
    //                                 'location' => [
    //                                     'locality' => $schedule->locality,
    //                                     'city' => $schedule->city,
    //                                     'coordinates' => [
    //                                         'latitude' => $schedule->latitude,
    //                                         'longitude' => $schedule->longitude
    //                                     ]
    //                                 ],
    //                                 'driver' => $schedule->driver ? [
    //                                     'id' => $schedule->driver->id,
    //                                     'name' => trim($schedule->driver->first_name.' '.$schedule->driver->last_name),
    //                                     'email' => $schedule->driver->email,
    //                                     'verified_by' => $schedule->driver->verified_by,
    //                                     'profile_image' => $schedule->driver->profile_image,
    //                                 ] : null,
    //                                 'vehicle' => $schedule->vehicle ? [
    //                                     'id' => $schedule->vehicle->id,
    //                                     'name' => $schedule->vehicle->name,
    //                                     'seats' => $schedule->vehicle->seats,
    //                                     'number' => $schedule->vehicle->vehicle_no
    //                                 ] : null,
    //                                 'shift_type' => $schedule->shiftType?->name,
    //                                 'pricebook' => $schedule->pricebook ?? null,
    //                                 'status' => $schedule->scheduleStatus ? [
    //                                     'id' => $schedule->scheduleStatus->id,
    //                                     'schedule_id' => $schedule->scheduleStatus->schedule_id,
    //                                     'name' => $schedule->scheduleStatus->Status->name,
    //                                     'date' => $schedule->scheduleStatus->date,
    //                                     'status_id' => $schedule->scheduleStatus->status_id,
    //                                     'type' => $schedule->scheduleStatus->type,
    //                                     'times' => [
    //                                         'start' => $schedule->scheduleStatus->start_time,
    //                                         'end' => $schedule->scheduleStatus->end_time
    //                                     ]
    //                                 ] : null,
    //                                 'carers' => $schedule->carers->map(function ($carer) {
    //                                     return $carer->user ? [
    //                                         'id' => $carer->user->id,
    //                                         'name' => trim($carer->user->first_name . ' ' . $carer->user->last_name),
    //                                         'email' => $carer->user->email,
    //                                         'image' => $carer->user->profile_image ?? null
    //                                     ] : null;
    //                                 })->filter()->values()
    //                             ];
    //                         })->toArray() : []
    //                 ];
    //                 $currentDate->addDay();
    //             }
    //             $dateRange = $dateRange->reverse();
    //             return [
    //                     'company' => [
    //                         'company_address' => $companyAddress->address,
    //                         'company_latitude' => $companyAddress->latitude,
    //                         'company_longitude' => $companyAddress->longitude,
    //                         'company_name' => $company->name,
    //                         'company_logo' => $company->logo,
    //                     ],
    //                     'url' => [
    //                         'employee_image_url' => url('images'),
    //                     ],
    //                     'trips_details' => $dateRange
    //             ];
    //        }
    //  }

        public function driverTripDetailsOfMonth(Request $request){
            $driverId=$request->id;
            $user = SubUser::find($request->id);
            if ($user->hasRole('driver')) {
                $company = CompanyDetails::first();
                $companyAddress = CompanyAddresse::first();

                // Get first day of current month and today's date
                $startOfMonth = now()->startOfMonth()->format('Y-m-d');
                $today = now()->format('Y-m-d');

                // Get all schedules from start of month to today
                $schedules = Schedule::with([
                    'shiftType:id,name,external_id,color,created_at,updated_at',
                    'driver:id,first_name,last_name,email,phone,profile_image,verified_by',
                    'vehicle:id,name,seats,vehicle_no',
                    'carers.user:id,first_name,last_name,email,profile_image',
                    'scheduleStatus.status:id,name',
                    'pricebook.priceBookData:id,price_book_id,day_of_week,per_ride'
                ])
                ->whereBetween('date', [$startOfMonth, $today])
                 ->where('driver_id', $request->id)
                ->get(['id', 'date', 'vehicle_id','driver_id', 'shift_type_id','start_time', 'end_time', 'locality', 'city', 'pricebook_id', 'latitude', 'longitude', 'created_at']);

                // Convert date strings to Carbon instances for grouping
                $schedules->each(function ($item) {
                    $item->date = \Carbon\Carbon::parse($item->date);
                });

                // Group schedules by date
                $groupedSchedules = $schedules->groupBy(function ($item) {
                    return $item->date->format('Y-m-d');
                });

                // Create a date range from start of month to today
                $dateRange = collect();
                $currentDate = \Carbon\Carbon::parse($startOfMonth);
                $endDate = \Carbon\Carbon::parse($today);


                while ($currentDate <= $endDate) {
                    $dateString = $currentDate->format('Y-m-d');
                    $dateRange[$dateString] = [
                        'data' => $groupedSchedules->has($dateString) ?
                            $groupedSchedules[$dateString]->map(function ($schedule) {
                                return [
                                    'id' => $schedule->id,
                                    'date' => $schedule->date->format('Y-m-d'),
                                    'driver_id' => $schedule->driver_id,
                                    'vehicle_id' => $schedule->vehicle_id,
                                    'carers_count'=>  $schedule->carers ?$schedule->carers->count():null,
                                    'vehicle_seats'=>  $schedule->vehicle ?$schedule->vehicle->seats:null,
                                    'avilable_seats'=>  $schedule->vehicle ?$schedule->vehicle->seats - $schedule->carers->count():null,
                                    'times' => [
                                        'start' => $schedule->start_time,
                                        'end' => $schedule->end_time
                                    ],
                                    'location' => [
                                        'locality' => $schedule->locality,
                                        'city' => $schedule->city,
                                        'coordinates' => [
                                            'latitude' => $schedule->latitude,
                                            'longitude' => $schedule->longitude
                                        ]
                                    ],
                                    'driver' => $schedule->driver ? [
                                        'id' => $schedule->driver->id,
                                        'name' => trim($schedule->driver->first_name.' '.$schedule->driver->last_name),
                                        'email' => $schedule->driver->email,
                                        'verified_by' => $schedule->driver->verified_by,
                                        'profile_image' => $schedule->driver->profile_image,

                                    ] : null,
                                    'vehicle' => $schedule->vehicle ? [
                                        'id' => $schedule->vehicle->id,
                                        'name' => $schedule->vehicle->name,
                                        'seats' => $schedule->vehicle->seats,
                                        'number' => $schedule->vehicle->vehicle_no
                                    ] : null,
                                    'shift_type' => $schedule->shiftType?->name,
                                    'pricebook' => $schedule->pricebook ? [
                                        'id' => $schedule->pricebook->id,
                                        'name' => $schedule->pricebook->name,
                                        'latitude' => $schedule->pricebook->latitude,
                                        'longitude' => $schedule->pricebook->longitude,
                                        'per_ride' => optional(
                                            collect($schedule->pricebook->priceBookData)->first(function ($data) use ($schedule) {
                                                $day = \Carbon\Carbon::parse($schedule->date)->format('l'); // e.g., Monday, Tuesday...
                                                return match (strtolower($day)) {
                                                    'saturday' => strtolower($data->day_of_week) === 'saturday',
                                                    'sunday' => strtolower($data->day_of_week) === 'sunday',
                                                    default => strtolower($data->day_of_week) === 'weekdays (mon- fri)',
                                                };
                                            })
                                        )?->per_ride,
                                    ] : null,

                                    'status' => $schedule->scheduleStatus ? [
                                        'id' => $schedule->scheduleStatus->id,
                                        'schedule_id' => $schedule->scheduleStatus->schedule_id,
                                        'name' => $schedule->scheduleStatus->Status->name,
                                        'date' => $schedule->scheduleStatus->date,
                                        'status_id' => $schedule->scheduleStatus->status_id,
                                        'type' => $schedule->scheduleStatus->type,
                                        'times' => [
                                            'start' => $schedule->scheduleStatus->start_time,
                                            'end' => $schedule->scheduleStatus->end_time
                                        ]
                                    ] : null,

                                    'carers' => $schedule->carers->map(function ($carer) {
                                        return $carer->user ? [
                                            'id' => $carer->user->id,
                                            'name' => trim($carer->user->first_name . ' ' . $carer->user->last_name),
                                            'email' => $carer->user->email,
                                            'image' => $carer->user->profile_image ?? null
                                        ] : null;
                                    })->filter()->values()
                                ];
                            })->toArray() : []
                    ];
                    $currentDate->addDay();
                }

                $dateRange = $dateRange->reverse();

                return [
                        'company' => [
                            'company_address' => $companyAddress->address ?? $company->address,
                            'company_latitude' => $companyAddress->latitude ?? $company->latitude,
                            'company_longitude' => $companyAddress->longitude ?? $company->longitude,
                            'company_name' => $company->name,
                            'company_logo' => $company->logo,
                        ],
                        'url' => [
                            'employee_image_url' => url('images'),
                            'driver_image_url' => url('images'),
                            'vehicle_image_url' => url('public/images/vehicles'),
                        ],
                        'driver_rating'=>$this->driverRating($driverId),
                        'trips_details' => $dateRange
                ];
           }
        }

    //******************* Employee details api*****************************************

    /**
     * @OA\Post(
     * path="/uc/api/employeeDetails",
     * operationId="employeeDetail",
     * tags={"Ucruise Employee"},
     * summary="Employee detail",
     *   security={ {"Bearer": {} }},
     * description="Employee detail",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The driver updated successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The driver updated successfully.",
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

    public function employeeDetail(Request $request)
    {
        try {
            $homeController = new HomeController();
            $user = SubUser::find($request->id);
            $user_ids = array($user->id);
            $today_date = Carbon::now()->format('Y-m-d');
            $dates = array($today_date);

            $this->data1['employee'] = [];
            $this->data1['all_schedule'] = [];
            $this->data1['reschedules'] = [];
            $this->data1['temp_location_change'] = [];
            $this->data1['leaves'] = [];
            $this->data1['team'] = [];
            $this->data1['schedule_report'] = [];
            $this->data1['role'] = User::with('roles')->find($request->id)->roles;
            $this->data1['sub_role'] =  SubUser::with('roles')->find($request->id)->roles;
            $this->data1['hrms_sub_role'] =  SubUser::with('hrmsroles')->find($request->id)->hrmsroles;
            $this->data1['resignations'] = Resignation::where('user_id', $user->id)->latest()->first();
            $this->carerCurrentride($user->id);


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
                $this->data1['address_history'] = @$this->employeeAddressHistory($user->id);
                $this->data1['leaves'] = @$this->employeeLeaves($user->id);
                $this->data1['team'] = @$this->teams($user->id);
                $this->data1['schedule_report'] = @$this->scheduleReport($user->id);
            }
            //*******  Type Team and Manager */
            $getManagerList = TeamManager::with(['employees', 'teams.teamLeader', 'teams.teamMembers.user'])->get();
            $team_id = null;
            //$team_manager_id = null;
            if (isset($getManagerList)) {
                foreach ($getManagerList as $key => $manager) {
                    foreach ($manager->teams as $key => $team) {
                        if (optional($team->team_leader == $user->id)) {
                            $team_manager_id =  $manager->id;
                            $team_id =   $team->id;
                            break 2;
                        } else {

                            foreach ($team->teamMembers as $key => $member) {

                                if (optional($member->user->id == $user->id)) {
                                    $team_manager_id =  $manager->id;
                                    $team_id =   $team->id;
                                    break 2;
                                }
                            }
                        }
                    }
                }
                // if (isset($team_manager_id)) {
                //     $this->data1['teamDetails'] = TeamManager::with(['employees:id,first_name,last_name', 'teams:team_manager_id,id,team_name'])->where('id', $team_manager_id)->get();
                // } else {
                //     $this->data1['teamDetails'] = "";
                // }

            //     if (isset($team_manager_id)) {
            //     // Fetch only the specific team where user is assigned
            //         $this->data1['teamDetails'] = TeamManager::with([
            //             'employees:id,first_name,last_name',
            //             'teams' => function ($q) use ($team_id) {
            //                $q->where('id', $team_id)->select('team_manager_id', 'id', 'team_name');
            //             }
            //             ])->where('id', $team_manager_id)->get();
            //     } else {
            //         $this->data1['teamDetails'] = "";
            //     }

            // }
            $employeeTeams = [];

                foreach ($getManagerList as $manager) {
                    foreach ($manager->teams as $team) {
                        // Team leader
                        if ($team->team_leader) {
                            $employeeTeams[] = [
                                'employee_id' => $team->team_leader,
                                'team_id' => $team->id,
                                'team_name' => $team->team_name,
                                'team_manager_id' => $manager->id,
                                'team_manager_name' => $manager->name,
                                'role' => 'leader'
                            ];
                        }
                        // Team members
                        foreach ($team->teamMembers as $member) {
                            if ($member->user) {
                                $employeeTeams[] = [
                                    'employee_id' => $member->user->id,
                                    'team_id' => $team->id,
                                    'team_name' => $team->team_name,
                                    'team_manager_id' => $manager->id,
                                    'team_manager_name' => $manager->name,
                                    'role' => 'member'
                                ];
                            }
                        }
                    }
                }

                // Filter for the requested user only, if you want to show only their team
                $userTeam = collect($employeeTeams)->where('employee_id', $user->id)->values()->all();

                $this->data1['teamDetails'] = $userTeam;

                } else {
                    $this->data1['teamDetails'] = "";
                }


            return response()->json(['success' => true, "data" => $this->data1, 'employee_image_url' => url('images')], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
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

    public function employeeAddressHistory($id)
    {
        $addressHistory = SubUserAddresse::where('sub_user_id', $id)
            ->with(['user:id,first_name'])
            ->get();

        return $addressHistory;
    }


    //********************** Teams **************************************************
    public function teams($id)
    {
        $sub_user = SubUser::find($id);
        $user_id = User::where('email', $sub_user->email)->pluck('id')->first();

        if ($sub_user->hasRole('carer')) {
            $employeeTeams = Teams::where('staff', 'LIKE', "%{$user_id}%")->get();
            $teams = $employeeTeams->map(function ($team) {
                $staffIds = explode(',', $team->staff);
                $staffInfo = User::whereIn('id', $staffIds)->get();
                $staffData = $staffInfo->map(function ($staff) {
                    $subUser = SubUser::where('email', $staff->email)->with('roles')->first();
                    return $subUser;
                });
                $team->staff = $staffData->toArray();
                return $team;
            });
            return $teams;
        }
    }
    //********************** Schedule Reports ******************************************

    public function scheduleReport($id)
    {
        $scheduleReport = [];
        $schedule_carer_id = ScheduleCarer::where('carer_id', $id)->pluck('id');
        $scheduleReport['total'] = ScheduleCarerStatus::whereIn('schedule_carer_id', $schedule_carer_id)->count();
        $scheduleReport['absent'] = ScheduleCarerStatus::whereIn('schedule_carer_id', $schedule_carer_id)->where('status_id', 5)->count();
        $scheduleReport['cancelled'] = ScheduleCarerStatus::whereIn('schedule_carer_id', $schedule_carer_id)->where('status_id', 4)->count();
        $scheduleReport['leave'] = ScheduleCarerStatus::whereIn('schedule_carer_id', $schedule_carer_id)->where('status_id', 11)->count();
        return $scheduleReport;
    }



    protected function carerCurrentride($id){
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $today = now()->format('Y-m-d');
        $startOfMonth2 = now()->subDays(3)->format('Y-m-d');
        $company = CompanyDetails::first();
        $companyAddress = CompanyAddresse::first();
        $schedules = Schedule::with([
            'shiftType:id,name,external_id,color,created_at,updated_at',
            'driver:id,first_name,last_name,email,phone,profile_image',
            'vehicle:id,name,seats,vehicle_no',
            'carers.user:id,first_name,last_name,email,profile_image',
            'scheduleStatus.status:id,name',
            'pricebook.priceBookData:id,price_book_id,day_of_week,per_ride'
        ])
        ->whereDate('date', today())
        ->whereHas('carers', function ($q) use ($id) {
            $q->where('carer_id', $id);
        })
        ->get([
            'id', 'date', 'vehicle_id', 'driver_id', 'shift_type_id',
            'start_time', 'end_time', 'locality', 'city',
            'pricebook_id', 'latitude', 'longitude', 'created_at'
        ]);

        return [
            'company'=>[
                'company_address' => @$companyAddress->address,
                'company_latitude' => @$companyAddress->latitude,
                'company_longitude' => @$companyAddress->longitude,
                'company_name' => @$company->name,
                'company_logo' => @$company->logo,
            ],
            'trips_details' => $schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'date' => $schedule->date,
                    'driver_id' => $schedule->driver_id,
                    'vehicle_id' => $schedule->vehicle_id,
                    'times' => [
                        'start' => $schedule->start_time,
                        'end' => $schedule->end_time
                    ],
                    'location' => [
                        'locality' => $schedule->locality,
                        'city' => $schedule->city,
                        'coordinates' => [
                            'latitude' => $schedule->latitude,
                            'longitude' => $schedule->longitude
                        ]
                    ],
                    'driver' => $schedule->driver ?[
                        'id' => $schedule->driver->id,
                        'name' => trim($schedule->driver->first_name.' '.$schedule->driver->last_name),
                        'email' => $schedule->driver->email,
                        'profile_image' => $schedule->driver->profile_image,
                    ] : null,
                    'vehicle' =>  $schedule->vehicle ? [
                        'id' => $schedule->vehicle->id,
                        'name' => $schedule->vehicle->name,
                        'seats' => $schedule->vehicle->seats,
                        'number' => $schedule->vehicle->vehicle_no
                    ] : null,
                    'shift_type' => $schedule->shiftType?->name,
                    'status' => $schedule->scheduleStatus ? [
                        'id' => $schedule->scheduleStatus->id,
                        'schedule_id' => $schedule->scheduleStatus->schedule_id,
                        'name' => $schedule->scheduleStatus->Status->name,
                        'date' => $schedule->scheduleStatus->date,
                        'status_id' => $schedule->scheduleStatus->status_id,
                        'type' => $schedule->scheduleStatus->type,
                        'times' => [
                            'start' => $schedule->scheduleStatus->start_time,
                            'end' => $schedule->scheduleStatus->end_time
                        ]
                    ] : null,
                    // 'pricebook' => $schedule->pricebook ?? null,
                    'pricebook' => $schedule->pricebook ? [
                        'id' => $schedule->pricebook->id,
                        'name' => $schedule->pricebook->name,
                        'latitude' => $schedule->pricebook->latitude,
                        'longitude' => $schedule->pricebook->longitude,
                        'per_ride' => optional(
                            collect($schedule->pricebook->priceBookData)->first(function ($data) use ($schedule) {
                                $day = \Carbon\Carbon::parse($schedule->date)->format('l'); // e.g., Monday, Tuesday...
                                return match (strtolower($day)) {
                                    'saturday' => strtolower($data->day_of_week) === 'saturday',
                                    'sunday' => strtolower($data->day_of_week) === 'sunday',
                                    default => strtolower($data->day_of_week) === 'weekdays (mon- fri)',
                                };
                            })
                        )?->per_ride,
                    ] : null,
                    'carers' => $schedule->carers->map(function ($carer) {
                        return $carer->user ? [
                            'id' => $carer->user->id,
                            'name' => trim($carer->user->first_name . ' ' . $carer->user->last_name),
                            'email' => $carer->user->email,
                            'image' => $carer->user->profile_image ?? null
                        ] : null;
                    })->filter()->values()
                ];
            })
        ];


    }

    // ******************* care schedule history start********************

    /**
     * @OA\post(
     * path="/uc/api/careTripDetailsOfMonth",
     * operationId="careTripDetailsOfMonthtrips",
     * tags={"Ucruise Driver"},
     * summary="Carer details",
     *   security={ {"Bearer": {} }},
     * description="Care details",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="text"),
     *               @OA\Property(property="selected_month", type="text"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Data listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Data listed successfully",
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

        public function careTripDetailsOfMonth(Request $request){
            $id = $request->id;
            $user = SubUser::find($request->id);

            if ($user->hasRole('carer')) {
                $company = CompanyDetails::first();
                $companyAddress = CompanyAddresse::first();

                // Get first day of current month and today's date
                // $startOfMonth = now()->startOfMonth()->format('Y-m-d');
                // $today = now()->format('Y-m-d');
                $month = $request->selected_month;
                $selectedMonthCarbon = Carbon::parse($month);
                $startOfMonth = $selectedMonthCarbon->startOfMonth()->format('Y-m-d');

                if ($selectedMonthCarbon->isCurrentMonth()) {
                    $endOfMonth = now()->format('Y-m-d'); // till today
                } else {
                    $endOfMonth = $selectedMonthCarbon->endOfMonth()->format('Y-m-d');
                }
                
                // Get all schedules from start of month to today
                $schedules = Schedule::with([
                    'shiftType:id,name,external_id,color,created_at,updated_at',
                    'driver:id,first_name,last_name,email,phone,profile_image,verified_by',
                    'vehicle:id,name,seats,vehicle_no',
                    'carers.user:id,first_name,last_name,email,profile_image',
                    'scheduleStatus.status:id,name',
                    'pricebook.priceBookData:id,price_book_id,day_of_week,per_ride'
                ])
                // ->whereBetween('date', [$startOfMonth, $today])
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                  ->whereHas('carers', function ($q) use ($id) {
                    $q->where('carer_id', $id);
                })
                ->get(['id', 'date', 'vehicle_id','driver_id', 'shift_type_id','start_time', 'end_time', 'locality', 'city', 'pricebook_id', 'latitude', 'longitude', 'created_at']);

                // Convert date strings to Carbon instances for grouping
                $schedules->each(function ($item) {
                    $item->date = \Carbon\Carbon::parse($item->date);
                });

                // Group schedules by date
                $groupedSchedules = $schedules->groupBy(function ($item) {
                    return $item->date->format('Y-m-d');
                });

                // Create a date range from start of month to today
                $dateRange = collect();
                $currentDate = \Carbon\Carbon::parse($startOfMonth);
                $endDate = \Carbon\Carbon::parse($endOfMonth);

                while ($currentDate <= $endDate) {
                    $dateString = $currentDate->format('Y-m-d');
                    $dateRange[$dateString] = [
                        'data' => $groupedSchedules->has($dateString) ?
                            $groupedSchedules[$dateString]->map(function ($schedule) {
                                return [
                                    'id' => $schedule->id,
                                    'date' => $schedule->date->format('Y-m-d'),
                                    'driver_id' => $schedule->driver_id,
                                    'vehicle_id' => $schedule->vehicle_id,
                                    'carers_count'=>  $schedule->carers ?$schedule->carers->count():null,
                                    'vehicle_seats'=>  $schedule->vehicle ?$schedule->vehicle->seats:null,
                                    'avilable_seats'=>  $schedule->vehicle ?$schedule->vehicle->seats - $schedule->carers->count():null,
                                    'times' => [
                                        'start' => $schedule->start_time,
                                        'end' => $schedule->end_time
                                    ],
                                    'location' => [
                                        'locality' => $schedule->locality,
                                        'city' => $schedule->city,
                                        'coordinates' => [
                                            'latitude' => $schedule->latitude,
                                            'longitude' => $schedule->longitude
                                        ]
                                    ],
                                    'driver' => $schedule->driver ? [
                                        'id' => $schedule->driver->id,
                                        'name' => trim($schedule->driver->first_name.' '.$schedule->driver->last_name),
                                        'email' => $schedule->driver->email,
                                        'verified_by' => $schedule->driver->verified_by,
                                        'profile_image' => $schedule->driver->profile_image,

                                    ] : null,
                                    'vehicle' => $schedule->vehicle ? [
                                        'id' => $schedule->vehicle->id,
                                        'name' => $schedule->vehicle->name,
                                        'seats' => $schedule->vehicle->seats,
                                        'number' => $schedule->vehicle->vehicle_no
                                    ] : null,
                                    'shift_type' => $schedule->shiftType?->name,
                                    // 'pricebook' => $schedule->pricebook ?? null,
                                    'pricebook' => $schedule->pricebook ? [
                                        'id' => $schedule->pricebook->id,
                                        'name' => $schedule->pricebook->name,
                                        'latitude' => $schedule->pricebook->latitude,
                                        'longitude' => $schedule->pricebook->longitude,
                                        'per_ride' => optional(
                                            collect($schedule->pricebook->priceBookData)->first(function ($data) use ($schedule) {
                                                $day = \Carbon\Carbon::parse($schedule->date)->format('l'); // e.g., Monday, Tuesday...
                                                return match (strtolower($day)) {
                                                    'saturday' => strtolower($data->day_of_week) === 'saturday',
                                                    'sunday' => strtolower($data->day_of_week) === 'sunday',
                                                    default => strtolower($data->day_of_week) === 'weekdays (mon- fri)',
                                                };
                                            })
                                        )?->per_ride,
                                    ] : null,
                                    'status' => $schedule->scheduleStatus ? [
                                        'id' => $schedule->scheduleStatus->id,
                                        'schedule_id' => $schedule->scheduleStatus->schedule_id,
                                        'name' => $schedule->scheduleStatus->Status->name,
                                        'date' => $schedule->scheduleStatus->date,
                                        'status_id' => $schedule->scheduleStatus->status_id,
                                        'type' => $schedule->scheduleStatus->type,
                                        'times' => [
                                            'start' => $schedule->scheduleStatus->start_time,
                                            'end' => $schedule->scheduleStatus->end_time
                                        ]
                                    ] : null,

                                    'carers' => $schedule->carers->map(function ($carer) {
                                        return $carer->user ? [
                                            'id' => $carer->user->id,
                                            'name' => trim($carer->user->first_name . ' ' . $carer->user->last_name),
                                            'email' => $carer->user->email,
                                            'image' => $carer->user->profile_image ?? null
                                        ] : null;
                                    })->filter()->values()
                                ];
                            })->toArray() : []
                    ];
                    $currentDate->addDay();
                }

                $dateRange = $dateRange->reverse();

                return [
                        'company' => [
                            'company_address' => $companyAddress->address,
                            'company_latitude' => $companyAddress->latitude,
                            'company_longitude' => $companyAddress->longitude,
                            'company_name' => $company->name,
                            'company_logo' => $company->logo,
                        ],
                        'url' => [
                            'employee_image_url' => url('images'),
                            'driver_image_url' => url('images'),
                            'vehicle_image_url' => url('public/images/vehicles'),
                        ],
                       // 'driver_rating'=>$this->driverRating($driverId),
                        'trips_details' => $dateRange
                ];
           }
        }

    // ******************* care schedule history end ********************

    // *************** Employee booking summary start *********************


    /**
     * @OA\post(
     * path="/uc/api/carebookingSummary",
     * operationId="carebookingSummary",
     * tags={"Ucruise Driver"},
     * summary="Booking summary details",
     *   security={ {"Bearer": {} }},
     * description="Booking summary details",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="text"),
     *               @OA\Property(property="month", type="string", description="2025-07")
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Data listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Data listed successfully",
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

    public function carebookingSummary(Request $request){

        try {


            $carerId  = $request->input('id');
            $monthInput = $request->input('month');
            if ($monthInput) {
                // Create Carbon from input and get start/end of that month
                $startDate = Carbon::parse($monthInput . '-01')->startOfMonth();
                $endDate = Carbon::parse($monthInput . '-01')->endOfMonth();
            } else {
                // Default to current month
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
            }

            $dates = $this->generateDatesInRange($startDate, $endDate);
            $employee = SubUser::whereHas('roles', function ($q) {
                $q->where('name', 'carer');
            })
            ->where('id',$carerId)
            ->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Carer not found'
                ], 404);
            }

            $home = new HomeController();

            $total_Rides = 0;
            $total_Leaves = 0;
            $total_Absent = 0;
            $total_Cancel = 0;
            $total_Reschedule = 0;
            $total_Temp_Reschedule = 0;
            $total_Complaints = 0;

            $chartData = [
                'total_Rides' => 0,
                'total_Leaves' => 0,
                'total_Absent' => 0,
                'total_Cancel' => 0,
                'total_Reschedule' => 0,
                'total_Temp_Reschedule' => 0,
                'total_Complaints' => 0
            ];

            $dateWiseData = [];

            // Initialize all dates with 0 stats
            foreach ($dates as $date) {
                $dateWiseData[$date] = [
                    'total_rides' => 0,
                    'leaves' => 0,
                    'absents' => 0,
                    'cancel' => 0,
                    'reschedule' => 0,
                    'temp_reschedule' => 0,
                    'complaints' => 0,
                ];
            }

            // Process data
            foreach ($dates as $date) {
                $schedules = $this->getWeeklyScheduleInfo([$employee->id], [$date], 2, "all");

                foreach ($schedules as $schedule) {
                    $scheduleDate = date('Y-m-d', strtotime($schedule['date']));
                    $holiday = Holiday::where('date', $scheduleDate)->exists();

                    if (!$holiday) {
                        $dateWiseData[$scheduleDate]['total_rides']++;
                        $total_Rides++;

                        $carers = $home->getScheduleCarers($schedule['id'], $schedule['type'], $scheduleDate);

                        foreach ($carers ?? [] as $carer) {
                            if ($carer->carer_id == $employee->id) {
                                if ($carer->ride_status_id == 11) {
                                    $dateWiseData[$scheduleDate]['leaves']++;
                                    $total_Leaves++;
                                } elseif ($carer->ride_status_id == 5) {
                                    $dateWiseData[$scheduleDate]['absents']++;
                                    $total_Absent++;
                                } elseif ($carer->ride_status_id == 4) {
                                    $dateWiseData[$scheduleDate]['cancel']++;
                                    $total_Cancel++;
                                }
                            }
                        }

                        // Reschedule
                        $rescheduleCount = Reschedule::where('user_id', $employee->id)
                            ->whereDate('date', $scheduleDate)
                            ->count();
                        $dateWiseData[$scheduleDate]['reschedule'] += $rescheduleCount;
                        $total_Reschedule += $rescheduleCount;

                        // Temp Reschedule
                        $tempRelocateCount = ScheduleCarerRelocation::where('staff_id', $employee->id)
                            ->whereDate('date', $scheduleDate)
                            ->count();
                        $dateWiseData[$scheduleDate]['temp_reschedule'] += $tempRelocateCount;
                        $total_Temp_Reschedule += $tempRelocateCount;

                        // Complaints
                        $complaintCount = ScheduleCarerComplaint::where('staff_id', $employee->id)
                            ->whereDate('date', $scheduleDate)
                            ->count();
                        $dateWiseData[$scheduleDate]['complaints'] += $complaintCount;
                        $total_Complaints += $complaintCount;
                    }
                }
            }

            // Assign totals
            $chartData['total_Rides'] = $total_Rides;
            $chartData['total_Leaves'] = $total_Leaves;
            $chartData['total_Absent'] = $total_Absent;
            $chartData['total_Cancel'] = $total_Cancel;
            $chartData['total_Complaints'] = $total_Complaints;
            $chartData['total_Reschedule'] = $total_Reschedule;
            $chartData['total_Temp_Reschedule'] = $total_Temp_Reschedule;

            return response()->json([
                'success' => true,
                'total' => $chartData,
                'data' => $dateWiseData
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }

    }



    // *************** Employee booking summary end ***********************



    //************************** Driver Billing *******************************/
    // public function getDriverBilling($id)
    // {


    //     $currentMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
    //     $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    //     $dates = $this->generateDatesInRange($currentMonth, $endDate);

    //     $invoicesQuery = Invoice::where('driver_id', $id)
    //         ->whereIn('date', $dates);

    //     $invoices = $invoicesQuery->get();

    //     $rides = [];
    //     foreach ($invoices as $invoice) {

    //         $scheduleCarers = ScheduleCarer::where('schedule_id', $invoice->schedule_id)
    //             ->where('shift_type', $invoice->type)
    //             ->get();
    //         $carersDetails = [];

    //         foreach ($scheduleCarers as $scheduleCarer) {
    //             $scheduleCarerStatus = ScheduleCarerStatus::where('schedule_carer_id', $scheduleCarer->id)
    //                 ->where('date', $invoice->date)
    //                 ->first();
    //             $carer = $scheduleCarer->user;
    //             $carerName = @$carer->first_name;
    //             $carerStartTime = @$scheduleCarerStatus->start_time;
    //             $carerEndTime = @$scheduleCarerStatus->end_time;
    //             // Add carer details to array
    //             $carersDetails[] = [
    //                 'name' => $carerName,
    //                 'start_time' => $carerStartTime,
    //                 'end_time' => $carerEndTime,
    //             ];
    //         }
    //         $day = Carbon::createFromFormat('Y-m-d', $invoice->date)->englishDayOfWeek;


    //         $rides[] = [
    //             'id' => $invoice->id,
    //             'date' => $invoice->date,
    //             'day' => $day,
    //             'type' => $invoice->type,
    //             'start_time' => $invoice->start_time,
    //             'end_time' => $invoice->end_time,
    //             'fare' => $invoice->fare,
    //             'ride_status' => $invoice->ride_status,
    //             'is_included' => $invoice->is_included,
    //             'carers' => $carersDetails,
    //         ];
    //     }


    //     usort($rides, function ($a, $b) {
    //         return strcmp($a['date'], $b['date']);
    //     });

    //     return $rides;
    // }
    // public function generateDatesInRange($startDate, $endDate)
    // {
    //     $dates = [];

    //     $start = Carbon::parse($startDate);
    //     $end = Carbon::parse($endDate);

    //     while ($start->lte($end)) {
    //         $dates[] = $start->toDateString();
    //         $start->addDay();
    //     }

    //     return $dates;
    // }




    //************** New billing code ************************************** */
    public function getDriverBilling($id)
    {

        $currentMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $dates = $this->generateDatesInRange($currentMonth, $endDate);

        // Fetch driver information
        $driver = SubUser::where('id', $id)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'driver');
            })->first();

        $invoices = Invoice::where('driver_id', $id)
            ->whereIn('date', $dates)
            ->get();
        $rides = [];
        $absent_rides = $this->getAbsentRidesInfo($driver->id, $dates)['absent_rides']; // Fetch absent rides


        foreach ($invoices as $invoice) {
            $scheduleCarers = ScheduleCarer::where('schedule_id', $invoice->schedule_id)
                ->where('shift_type', $invoice->type)
                ->get();
            $carersDetails = [];
            foreach ($scheduleCarers as $scheduleCarer) {
                $scheduleCarerStatus = ScheduleCarerStatus::where('schedule_carer_id', $scheduleCarer->id)
                    ->where('date', $invoice->date)
                    ->first();
                $carer = $scheduleCarer->user;
                $carerName = @$carer->first_name;
                $carerStartTime = @$scheduleCarerStatus->start_time;
                $carerEndTime = @$scheduleCarerStatus->end_time;
                // Add carer details to array
                $carersDetails[] = [
                    'name' => $carerName,
                    'start_time' => $carerStartTime,
                    'end_time' => $carerEndTime,
                ];
            }
            $day = Carbon::createFromFormat('Y-m-d', $invoice->date)->englishDayOfWeek;

            // Add invoice details to rides array
            $rides[] = [
                'id' => $invoice->id,
                'date' => $invoice->date,
                'day' => $day,
                'type' => $invoice->type,
                'start_time' => $invoice->start_time,
                'end_time' => $invoice->end_time,
                'fare' => $invoice->fare,
                'ride_status' => $invoice->ride_status,
                'is_included' => $invoice->is_included,
                'carers' => $carersDetails,
            ];
        }


        $allRides = array_merge($rides, $absent_rides);
        usort($allRides, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });


        return $allRides;
    }
    public function generateDatesInRange($startDate, $endDate)
    {
        $dates = [];

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($start->lte($end)) {
            $dates[] = $start->toDateString();
            $start->addDay();
        }

        return $dates;
    }

    public function getAbsentRidesInfo($driverId, $dates)
    {
        $absent_rides = [];
        $totalAbsentFare = 0;
        $scheduleInfo = $this->getWeeklyScheduleInfo([$driverId], $dates, 1, 'all');
        // dd($scheduleInfo);
        if (!$scheduleInfo) {
            return ['absent_rides' => $absent_rides, 'total_absent_fare' => $totalAbsentFare];
        }

        $home = new HomeController();
        $scheduleController = Container::getInstance()->make(ScheduleController::class);


        foreach ($scheduleInfo as $schedule) {
            $schedule_id = $schedule['id'];
            if ($schedule['type'] == 'drop' && $schedule['shift_finishes_next_day'] == 1) {
                $scheduleDate = date('Y-m-d', strtotime($schedule['date'] . ' +1 day'));
            } else {
                $scheduleDate = date('Y-m-d', strtotime($schedule['date']));
            }
            $date = Carbon::createFromFormat('Y-m-d', $scheduleDate);
            $day = $date->englishDayOfWeek;
            $status = @$home->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['id'];
            if ($scheduleDate <= date('Y-m-d')) {
                if ($status == 9) {
                    $absent_amount = @$scheduleController->getFare($date, $schedule['pricebook_id']);
                    $totalAbsentFare += $absent_amount;


                    $absent_rides[] = [
                        'date' => $scheduleDate,
                        'day' => $day,
                        'schedule_id' => $schedule_id,
                        'type' => $schedule['type'],
                        'start_time' => null,
                        'end_time' => null,
                        'ride_status' => $status,
                        'absent_fare' => @$absent_amount ?? 0,
                        'is_included' =>  0,
                    ];
                }
            }
        }
        return ['absent_rides' => $absent_rides, 'total_absent_fare' => $totalAbsentFare];
    }

    //****************************** Send mail to all *********************************/
    /**
     * @OA\Get(
     * path="/uc/api/sendMail",
     * operationId="sendMail",
     * tags={"Ucruise Employee"},
     * summary="Send mail to update password",
     *   security={ {"Bearer": {} }},
     * description="Send mail to update password",
     *      @OA\Response(
     *          response=201,
     *          description="Mail sent successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Mail sent successfully",
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
    public function sendMail()
    {
        try {
            // $to_email = "bangwal854759@yopmail.com"; // Replace with recipient email
            // $check = Mail::raw('This is a test email from Laravel.', function ($message) use ($to_email) {
            //     $message->to($to_email)
            //         ->from('shubhamb.codebake@gmail.com')  // Ensure this matches your Gmail email
            //         ->subject('Test Email from Laravel');
            // });
            Mail::to("webdeveloper1.agt@gmail.com")->send(new SendEmail());
            return response()->json([
                'success' => true,
                'message' => 'dfdsf'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    //******************************** List archive drivers ****************************/
    /**
     * @OA\Get(
     * path="/uc/api/listArchiveDrivers",
     * operationId="listArchiveDrivers",
     * tags={"Ucruise Driver"},
     * summary="List archived drivers",
     *   security={ {"Bearer": {} }},
     * description="List archived drivers",
     *      @OA\Response(
     *          response=201,
     *          description="Archived drivers listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Archived drivers listed successfully",
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
    public function listArchiveDrivers()
    {
        try {
            $d = date('Y-m-d');
            $this->data["archivedDrivers"] = SubUser::with('vehicle')->with('pricebook')->whereHas("roles", function ($q) {
                $q->whereIn("name", ['archived_driver']);
            })
                ->where('close_account', 1)
                ->leftJoin('sub_user_addresses', function ($join) use ($d) {
                    $join->on('sub_users.id', '=', 'sub_user_addresses.sub_user_id')
                        ->whereDate('sub_user_addresses.start_date', '<=', $d)
                        ->where(function ($query) use ($d) {
                            $query->whereDate('sub_user_addresses.end_date', '>', $d)
                                ->orWhereNull('sub_user_addresses.end_date');
                        });
                })
                ->orderBy("sub_users.id", "DESC")
                ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address')->get();

            return response()->json([
                'success' => true,
                "data" => @$this->data,
                'message' => "Archived driver listed successfully"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //******************************** List archive employees ****************************/
    /**
     * @OA\Get(
     * path="/uc/api/listArchiveEmployees",
     * operationId="listArchiveEmployees",
     * tags={"Ucruise Employee"},
     * summary="List archived employees",
     *   security={ {"Bearer": {} }},
     * description="List archived employees",
     *      @OA\Response(
     *          response=201,
     *          description="Archived employees listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Archived employees listed successfully",
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
    public function listArchiveEmployees()
    {
        try {
            $d = date('Y-m-d');
            $this->data["archivedEmployees"] = SubUser::whereHas("roles", function ($q) {
                $q->whereIn("name", ['archived_staff']);
            })
                ->where('close_account', 1)
                ->leftJoin('sub_user_addresses', function ($join) use ($d) {
                    $join->on('sub_users.id', '=', 'sub_user_addresses.sub_user_id')
                        ->whereDate('sub_user_addresses.start_date', '<=', $d)
                        ->where(function ($query) use ($d) {
                            $query->whereDate('sub_user_addresses.end_date', '>', $d)
                                ->orWhereNull('sub_user_addresses.end_date');
                        });
                })
                ->orderBy("sub_users.id", "DESC")
                ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address')->get();

            return response()->json([
                'success' => true,
                "data" => @$this->data,
                'message' => "Archived employees listed successfully"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //******************************** Archive driver **********************************/
    /**
     * @OA\Post(
     * path="/uc/api/archiveDriver",
     * operationId="archiveDriver",
     * tags={"Ucruise Driver"},
     * summary="Archive driver",
     *   security={ {"Bearer": {} }},
     * description="Archive driver",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={ "id"},
     *               @OA\Property(property="id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Driver archived successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Driver archived successfully.",
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
    public function archiveDriver(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:sub_users,id',
            ]);
            DB::table('role_sub_user')
                ->where('sub_user_id', $request->id)
                ->update(['role_id' => 12]);
            return response()->json([
                'success' => true,
                'message' => "Driver archived successfully"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    //******************************** Archive employee **********************************/
    /**
     * @OA\Post(
     * path="/uc/api/archiveEmployee",
     * operationId="archiveEmployee",
     * tags={"Ucruise Employee"},
     * summary="Archive employee",
     *   security={ {"Bearer": {} }},
     * description="Archive employee",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={ "id"},
     *               @OA\Property(property="id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Employee archived successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Employee archived successfully.",
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
    public function archiveEmployee(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:sub_users,id',
            ]);
            $sub_user = SubUser::find($request->id);
            $user = User::where('email', $sub_user->email)->first();

            // $staffShifts = ScheduleCarer::where('carer_id', $sub_user->id)->exists();
            // if ($staffShifts) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => "Employee cannot be archive as it is associated with a shift"
            //     ], 500);
            // }

            $futureSchedules = ScheduleCarer::where('carer_id', $sub_user->id) // filter by carer_id
                ->get();
            foreach ($futureSchedules as $scheduleCarer) {
                // Fetch the schedule details using the schedule_id from the schedule_carer table
                $schedule = Schedule::find($scheduleCarer->schedule_id); // Fetch schedule by schedule_id
                if ($schedule && ($schedule->date >= now()->toDateString() || ($schedule->date < now()->toDateString() && $schedule->end_date >= now()->toDateString()))) {
                    $driver = SubUser::find($schedule->driver_id);
                    $driverName = $driver->first_name;
                    return response()->json([
                        'success' => false,
                        'message' => "The employee cannot be archived as they are associated with a future schedule from {$schedule->date} to {$schedule->end_date} , assigned to the driver {$driverName}. "
                    ], 500);
                }
            }
            DB::table('role_sub_user')
                ->where('sub_user_id', $request->id)
                ->update(['role_id' => 10]);
            return response()->json([
                'success' => true,
                'message' => "Employee archived successfully"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    //************************************************** Unarchive driver api *************************************** */
    /**
     * @OA\Post(
     * path="/uc/api/unArchiveDriver",
     * operationId="unArchiveDriver",
     * tags={"Ucruise Driver"},
     * summary="Unarchive driver",
     *   security={ {"Bearer": {} }},
     * description="Unarchive driver",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={ "id"},
     *               @OA\Property(property="id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Driver unarchived successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Driver unarchived successfully.",
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
    public function unArchiveDriver(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:sub_users,id',
            ]);
            DB::table('role_sub_user')
                ->where('sub_user_id', $request->id)
                ->update(['role_id' => 5]);
            return response()->json([
                'success' => true,
                'message' => "Driver unarchived successfully"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //************* Unarchive staff api ************************************/
    /**
     * @OA\Post(
     * path="/uc/api/unArchiveEmployee",
     * operationId="unArchiveEmployee",
     * tags={"Ucruise Employee"},
     * summary="Unarchive employee",
     *   security={ {"Bearer": {} }},
     * description="Unarchive employee",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={ "id"},
     *               @OA\Property(property="id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Employee unarchived successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Employee unarchived successfully.",
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
    public function unArchiveEmployee(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:sub_users,id',
            ]);
            DB::table('role_sub_user')
                ->where('sub_user_id', $request->id)
                ->update(['role_id' => 4]);
            return response()->json([
                'success' => true,
                'message' => "Employee unarchived successfully"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    //************ Function to connect with multiple dbs********************************
    public function connectDB($db_name)
    {
        $default = [
            "driver" => env("DB_CONNECTION", "mysql"),
            "host" => env("DB_HOST"),
            "port" => env("DB_PORT"),
            "database" => $db_name,
            "username" => env("DB_USERNAME"),
            "password" => env("DB_PASSWORD"),
            "charset" => "utf8mb4",
            "collation" => "utf8mb4_unicode_ci",
            "prefix" => "",
            "prefix_indexes" => true,
            "strict" => false,
            "engine" => null,
        ];

        Config::set("database.connections.$db_name", $default);
        Config::set("client_id", 1);
        Config::set("client_connected", true);
        DB::setDefaultConnection($db_name);
        DB::purge($db_name);
    }






    /**
     * @OA\Post(
     * path="/uc/api/testDriver",
     * operationId="testDriver",
     * tags={"Ucruise Driver"},
     * summary="Driver details",
     *   security={ {"Bearer": {} }},
     * description="Driver details",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The driver updated successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The driver updated successfully.",
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

    public function testDriver(Request $reques)
    {

        $employees = [

            // Kharar Employees (13)

            ["id" => 1, "name" => "Aman Sharma", "address" => "Kharar", "latitude" => 30.7405, "longitude" => 76.6510, "phone" => "9876543201", "office_distance" => 9.2],
            ["id" => 2, "name" => "Pooja Verma", "address" => "Kharar", "latitude" => 30.7415, "longitude" => 76.6502, "phone" => "9876543202", "office_distance" => 9.5],
            ["id" => 3, "name" => "Rohit Kumar", "address" => "Kharar", "latitude" => 30.7451, "longitude" => 76.6567, "phone" => "9876543203", "office_distance" => 9.0],
            ["id" => 4, "name" => "Anjali Singh", "address" => "Kharar", "latitude" => 30.7487, "longitude" => 76.6641, "phone" => "9876543204", "office_distance" => 8.8],
            ["id" => 5, "name" => "Vikas Mehta", "address" => "Kharar", "latitude" => 30.7502, "longitude" => 76.6725, "phone" => "9876543205", "office_distance" => 8.5],
            ["id" => 6, "name" => "Sandeep Kaur", "address" => "Kharar", "latitude" => 30.7519, "longitude" => 76.6781, "phone" => "9876543206", "office_distance" => 8.3],
            ["id" => 7, "name" => "Harpreet Singh", "address" => "Kharar", "latitude" => 30.7525, "longitude" => 76.6850, "phone" => "9876543207", "office_distance" => 8.0],
            ["id" => 8, "name" => "Manpreet Kaur", "address" => "Kharar", "latitude" => 30.7540, "longitude" => 76.6902, "phone" => "9876543208", "office_distance" => 7.7],
            ["id" => 9, "name" => "Rajesh Kumar", "address" => "Kharar", "latitude" => 30.7555, "longitude" => 76.6955, "phone" => "9876543209", "office_distance" => 7.5],
            ["id" => 10, "name" => "Neetu Sharma", "address" => "Kharar", "latitude" => 30.7568, "longitude" => 76.7011, "phone" => "9876543210", "office_distance" => 7.2],
            ["id" => 11, "name" => "Rahul Bhatia", "address" => "Kharar", "latitude" => 30.7575, "longitude" => 76.7055, "phone" => "9876543250", "office_distance" => 7.0],
            ["id" => 12, "name" => "Simranjeet Kaur", "address" => "Kharar", "latitude" => 30.7589, "longitude" => 76.7122, "phone" => "9876543251", "office_distance" => 6.8],
            ["id" => 13, "name" => "Jaswinder Singh", "address" => "Kharar", "latitude" => 30.7595, "longitude" => 76.7158, "phone" => "9876543252", "office_distance" => 6.5],

            // More list
            ["id" => 25, "name" => "Harpreet Kaur", "address" => "Kharar", "latitude" => 30.7551, "longitude" => 76.6978, "phone" => "9876543253", "office_distance" => 9.2],
            ["id" => 26, "name" => "Rajinder Singh", "address" => "Kharar", "latitude" => 30.7523, "longitude" => 76.6901, "phone" => "9876543254", "office_distance" => 9.5],
            ["id" => 27, "name" => "Amandeep Sharma", "address" => "Kharar", "latitude" => 30.7508, "longitude" => 76.6845, "phone" => "9876543255", "office_distance" => 10.1],
            ["id" => 28, "name" => "Navjot Kaur", "address" => "Kharar", "latitude" => 30.7482, "longitude" => 76.6789, "phone" => "9876543256", "office_distance" => 10.5],
            ["id" => 29, "name" => "Gurpreet Singh", "address" => "Kharar", "latitude" => 30.7467, "longitude" => 76.6734, "phone" => "9876543257", "office_distance" => 10.9],



            // Mohali Employees (15)

            ["id" => 14, "name" => "Deepak Malhotra", "address" => "Phase 3, Mohali", "latitude" => 30.7046, "longitude" => 76.7179, "phone" => "9876543211", "office_distance" => 3.1],
            ["id" => 15, "name" => "Nidhi Kapoor", "address" => "Phase 5, Mohali", "latitude" => 30.7013, "longitude" => 76.7180, "phone" => "9876543212", "office_distance" => 2.75],
            ["id" => 16, "name" => "Suresh Yadav", "address" => "Sector 70, Mohali", "latitude" => 30.6956, "longitude" => 76.7371, "phone" => "9876543213", "office_distance" => 2.25],
            ["id" => 17, "name" => "Megha Rathi", "address" => "Sector 69, Mohali", "latitude" => 30.6894, "longitude" => 76.7222, "phone" => "9876543214", "office_distance" => 1.41],
            ["id" => 18, "name" => "Rajeev Chopra", "address" => "Aero City, Mohali", "latitude" => 30.6771, "longitude" => 76.7230, "phone" => "9876543215", "office_distance" => 0.15],

            // More list

            ["id" => 30, "name" => "Sukhwinder Singh", "address" => "Mohali", "latitude" => 30.6945, "longitude" => 76.6782, "phone" => "9876543258", "office_distance" => 9.1],
            ["id" => 31, "name" => "Manpreet Kaur", "address" => "Mohali", "latitude" => 30.6902, "longitude" => 76.6705, "phone" => "9876543259", "office_distance" => 9.4],
            ["id" => 32, "name" => "Harmanpreet Singh", "address" => "Mohali", "latitude" => 30.6858, "longitude" => 76.6628, "phone" => "9876543260", "office_distance" => 10.0],
            ["id" => 33, "name" => "Jaspreet Kaur", "address" => "Mohali", "latitude" => 30.6823, "longitude" => 76.6556, "phone" => "9876543261", "office_distance" => 10.6],
            ["id" => 34, "name" => "Arshdeep Singh", "address" => "Mohali", "latitude" => 30.6789, "longitude" => 76.6482, "phone" => "9876543262", "office_distance" => 10.9],


            // Chandigarh Employees (13)

            ["id" => 19, "name" => "Mohit Jain", "address" => "Sector 17, Chandigarh", "latitude" => 30.7333, "longitude" => 76.7794, "phone" => "9876543216", "office_distance" => 8.19],
            ["id" => 20, "name" => "Neha Bansal", "address" => "Sector 22, Chandigarh", "latitude" => 30.7268, "longitude" => 76.7655, "phone" => "9876543217", "office_distance" => 7.52],
            ["id" => 21, "name" => "Karan Kapoor", "address" => "Sector 35, Chandigarh", "latitude" => 30.7353, "longitude" => 76.7323, "phone" => "9876543218", "office_distance" => 6.64],


            // more list

            ["id" => 35, "name" => "Ravinder Kaur", "address" => "Chandigarh", "latitude" => 30.7452, "longitude" => 76.7905, "phone" => "9876543263", "office_distance" => 9.2],
            ["id" => 36, "name" => "Simran Kaur", "address" => "Chandigarh", "latitude" => 30.7356, "longitude" => 76.7753, "phone" => "9876543265", "office_distance" => 10.0],


            // Zirakpur Employees (9)

            ["id" => 22, "name" => "Priya Arora", "address" => "Zirakpur", "latitude" => 30.6422, "longitude" => 76.8173, "phone" => "9876543227", "office_distance" => 7.8],
            ["id" => 23, "name" => "Tarun Khanna", "address" => "Zirakpur", "latitude" => 30.6448, "longitude" => 76.8125, "phone" => "9876543228", "office_distance" => 7.2],
            ["id" => 24, "name" => "Rashmi Thakur", "address" => "Zirakpur", "latitude" => 30.6485, "longitude" => 76.8103, "phone" => "9876543229", "office_distance" => 6.9],
            ["id" => 24, "name" => "Raman", "address" => "Zirakpur", "latitude" => 30.596403, "longitude" => 76.843269, "phone" => "9876543229", "office_distance" => 11.5],

            // More list zirakpur

            ["id" => 29, "name" => "Harjot Singh", "address" => "Zirakpur", "latitude" => 30.6365, "longitude" => 76.8234, "phone" => "9876543268", "office_distance" => 9.1],
            ["id" => 32, "name" => "Navneet Kaur", "address" => "Zirakpur", "latitude" => 30.6254, "longitude" => 76.8009, "phone" => "9876543271", "office_distance" => 10.5],

        ];

        // Example usage:
        $drivers = [
            ['id' => 1, 'name' => 'Driver1', 'capacity' => 7],
            ['id' => 2, 'name' => 'Driver2', 'capacity' => 5],
            ['id' => 3, 'name' => 'Driver3', 'capacity' => 3]
        ];


        // Group according to ofice distance
        $groupedEmployees = $this->groupEmployeesByOfficeDistance($employees);

        // Group according to lat and log distande
        $refineGroups =  $this->refineGroups($groupedEmployees, $radius = 2);

        $assignLocalityToGroups = $this->assignLocalityToGroups($refineGroups);

        // Group according to lat and lan with radius and added group name dynamically city name.
        //return  $refineGroupsWithLocality =  $this->refineGroupsWithLocality($refineGroups, $radius = 5);


        return  $selectedDriver = $this->assignDriversToGroups($assignLocalityToGroups, $drivers);
    }



    // Function to group employees based on distance
    function haversine23($lat1, $lon1, $lat2, $lon2)
    {
        $earth_radius = 6371; // Earth's radius in km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earth_radius * $c;
    }

    function groupEmployeesByOfficeDistance($employees, $distance_threshold = 3)
    {
        // Sort employees by office distance
        usort($employees, function ($a, $b) {
            return $a['office_distance'] <=> $b['office_distance'];
        });

        $groups = [];
        $current_group = [];
        $first_employee_distance = null;

        foreach ($employees as $employee) {
            // If first employee in group, set reference distance
            if (empty($current_group)) {
                $first_employee_distance = $employee['office_distance'];
                $current_group[] = $employee;
            }
            // If within threshold from first employee in group, add to group
            else if (($employee['office_distance'] - $first_employee_distance) <= $distance_threshold) {
                $current_group[] = $employee;
            }
            // Otherwise, start a new group
            else {
                $groups[] = $current_group;
                $current_group = [$employee];
                $first_employee_distance = $employee['office_distance'];
            }
        }

        // Add last group if not empty
        if (!empty($current_group)) {
            $groups[] = $current_group;
        }

        // Format output
        $grouped_result = [];
        foreach ($groups as $index => $group) {
            $grouped_result["Group " . ($index + 1)] = $group;
        }

        return $grouped_result;
    }


    function refineGroups($groups, $radius = 2)
    {

        $newGroups = [];
        $groupIndex = 1;
        $checkedEmployees = [];

        foreach ($groups as $group) {
            foreach ($group as $employee) {
                if (in_array($employee['id'], $checkedEmployees)) {
                    continue; // Skip if already assigned
                }

                $newGroup = [$employee];
                $checkedEmployees[] = $employee['id'];

                foreach ($groups as $otherGroup) {
                    foreach ($otherGroup as $otherEmployee) {
                        if (in_array($otherEmployee['id'], $checkedEmployees)) {
                            continue;
                        }

                        // Check if within the given radius
                        if ($this->haversineDistance($employee['latitude'], $employee['longitude'], $otherEmployee['latitude'], $otherEmployee['longitude']) <= $radius) {
                            $newGroup[] = $otherEmployee;
                            $checkedEmployees[] = $otherEmployee['id'];
                        }
                    }
                }
                $newGroups["Group " . $groupIndex] = $newGroup;
                $groupIndex++;
            }
        }
        return $newGroups;
    }


    function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth radius in km
        $latDiff = deg2rad($lat2 - $lat1);
        $lonDiff = deg2rad($lon2 - $lon1);
        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDiff / 2) * sin($lonDiff / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }



    function assignLocalityToGroups($groups)
    {
        $groupedByLocality = [];
        $groupNumber = 1; // Start dummy group names from Group 1

        foreach ($groups as $group) {
            // Find the centroid of the group
            $centroid = $this->getCentroid($group);

            // Get locality name based on centroid location
            $locality = $this->getLocalityName($centroid['latitude'], $centroid['longitude']);

            // Assign a dummy name if locality is missing or unknown
            if (empty($locality) || $locality === "Unknown Area") {
                $locality = "Group " . $groupNumber;
                $groupNumber++; // Increment for the next unnamed group
            } else {
                // Append a unique group number even if locality is found
                $locality .= " - Group " . $groupNumber;
                $groupNumber++;
            }

            // Ensure the structure remains consistent
            if (!isset($groupedByLocality[$locality])) {
                $groupedByLocality[$locality] = [];
            }

            // $groupedByLocality[$locality][] = $group;
            $groupedByLocality[$locality] = $group;
        }

        return $groupedByLocality;
    }


    function assignDriversToGroups($groups, $drivers)
    {

        // Sort groups by size (largest first)
        usort($groups, function ($a, $b) {
            return count($b) - count($a);
        });

        // Sort drivers by capacity (largest first)
        usort($drivers, function ($a, $b) {
            return $b['capacity'] - $a['capacity'];
        });

        $newGroups = [];
        $assignedDrivers = []; // Track used drivers

        foreach ($groups as $groupName => $employees) {
            $remainingEmployees = $employees;
            $totalEmployees = count($employees);

            foreach ($drivers as $index => $driver) {
                if ($totalEmployees <= 0) break; // Stop if all employees are assigned
                if (isset($assignedDrivers[$driver['id']])) continue; // Skip used drivers

                if ($driver['capacity'] >= $totalEmployees) {
                    // Assign one driver to the full group
                    $newGroups[] = [
                        'group' => $groupName,
                        'employees' => $remainingEmployees,
                        'driver' => $driver
                    ];
                    $assignedDrivers[$driver['id']] = true;
                    $totalEmployees = 0;
                    break; // Move to the next group
                } else {
                    // Assign as many employees as the driver can take
                    $assignedEmployees = array_splice($remainingEmployees, 0, $driver['capacity']);
                    $newGroups[] = [
                        'group' => $groupName . ' (Split)',
                        'employees' => $assignedEmployees,
                        'driver' => $driver
                    ];
                    $assignedDrivers[$driver['id']] = true;
                    $totalEmployees -= count($assignedEmployees);
                }
            }

            // If employees are left without a driver, mark them as unassigned
            if ($totalEmployees > 0) {
                $newGroups[] = [
                    'group' => $groupName . ' (Unassigned)',
                    'employees' => $remainingEmployees,
                    'driver' => null
                ];
            }
        }

        return $newGroups;
    }




    /**
     * @OA\Post(
     * path="/uc/api/routeAutomation",
     * operationId="routeAutomation",
     * tags={"Ucruise Driver"},
     * summary="Driver route Automation",
     *   security={ {"Bearer": {} }},
     * description="Driver route Automation",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The driver updated successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The driver updated successfully.",
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


    public function addedEmploandDriverlist()
    {

        $oldSchedules = $this->oldScheduleAutomation();

        $assigneddrivers = collect($oldSchedules)->pluck('driver_id')->unique()->values()->toArray();

        $employees =  $this->unshinedemployeeList();

        $schedulesWithCares = [];
        $assignedCarers = [];

        // Collect already assigned carers from all old schedules
        foreach ($oldSchedules as $schedule) {
            if (!empty($schedule['cares']) && is_array($schedule['cares'])) {
                foreach ($schedule['cares'] as $cid) {
                    $assignedCarers[] = $cid;
                }
            }
        }

        $unassignedEmployees = collect($employees)->filter(function ($emp) use ($assignedCarers) {
            return !in_array($emp['id'], $assignedCarers);
        })->values();

        $leftUsers = $this->unshinedemployeeListLeft($assignedCarers);

        $combinedUsers = collect($unassignedEmployees)->concat(collect($leftUsers))->values();


        $oldScheduleDrivers = $this->unsinedDriverLeft($assigneddrivers);
        $unsinedDriver =  $this->unsinedDriver();
        $unsinedDriveralldrivers = collect($unsinedDriver)->concat(collect($oldScheduleDrivers))->values();

        return ['users' => $combinedUsers, 'drivers' => $unsinedDriveralldrivers];
    }


    public function routeAutomation(Request $request)
    {


        //DB::table('route_group_schedules')->delete();
        // DB::table('route_groups')->delete();

        $oldSchedules = $this->oldScheduleAutomation();

        $assigneddrivers = collect($oldSchedules)->pluck('driver_id')->unique()->values()->toArray();

        $employees =  $this->unshinedemployeeList();

        $schedulesWithCares = [];
        $assignedCarers = [];

        // Collect already assigned carers from all old schedules
        foreach ($oldSchedules as $schedule) {
            if (!empty($schedule['cares']) && is_array($schedule['cares'])) {
                foreach ($schedule['cares'] as $cid) {
                    $assignedCarers[] = $cid;
                }
            }
        }

        foreach ($oldSchedules as $schedule) {
            $scheduleLat = floatval($schedule['latitude']);
            $scheduleLon = floatval($schedule['longitude']);

            // Ensure cares is an array
            $schedule['cares'] = isset($schedule['cares']) && is_array($schedule['cares']) ? $schedule['cares'] : [];

            // Get available seats from vehicle info
            $availableSeats = isset($schedule['vehicle_seates']['seats']) ? intval($schedule['vehicle_seates']['seats']) : 0;
            $initialCarersCount = count($schedule['cares']);
            $remainingSeats = $availableSeats - $initialCarersCount;

            // Only assign more carers if seats are available
            if ($remainingSeats > 0) {
                foreach ($employees as $employee) {
                    if (in_array($employee['id'], $assignedCarers)) {
                        continue; // already assigned to another schedule
                    }

                    $employeeLat = floatval($employee['latitude']);
                    $employeeLon = floatval($employee['longitude']);

                    $distance = $this->haversineDistance($scheduleLat, $scheduleLon, $employeeLat, $employeeLon);

                    if ($distance <= 4) {
                        $schedule['cares'][] = $employee['id'];
                        $assignedCarers[] = $employee['id'];

                        $remainingSeats--;

                        if ($remainingSeats <= 0) {
                            break;
                        }
                    }
                }
            }

            $schedulesWithCares[] = $schedule;
        }

        // return $assignedCarers;

        // Now save old schedule date with assigned cares
        if (!empty($schedulesWithCares)) {
            $this->saveOldSchedule($schedulesWithCares);
        }

        // Remove assigned carers from employee list
        $unassignedEmployees = collect($employees)->filter(function ($emp) use ($assignedCarers) {
            return !in_array($emp['id'], $assignedCarers);
        })->values();

        //$leftUsers = $this->unshinedemployeeListLeft($assignedCarers);
        //$combinedUsers = collect($unassignedEmployees)->concat(collect($leftUsers))->values();

        $combinedUsers = $unassignedEmployees;

        $employees = User::select('id', 'first_name', 'email', 'latitude', 'longitude', 'address')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $officeLat = 30.6810489;
        $officeLng = 76.7260711;

        // Update the office_distance field for the user with the given user_id and distance

        foreach ($employees as $employee) {
            $user = User::find($employee['id']);
            if ($user) {

                if (empty($user->office_distance)) {
                    $distance = $this->haversine($officeLat, $officeLng, $user->latitude, $user->longitude);
                    User::where('id', $employee['id'])->update(['office_distance' => $distance]);
                }
                // $distance = $this->haversine($officeLat, $officeLng, $user->latitude, $user->longitude);
                // User::where('id', $employee['id'])->update(['office_distance' => $distance]);
            }
        }

        //$employees =  $this->unshinedemployeeList();

        $employees =  $combinedUsers;
        $unsinedDriver =  $this->unsinedDriver();

        //$oldScheduleDrivers = $this->unsinedDriverLeft($assigneddrivers);
        //$unsinedDriver = collect($unsinedDriver)->concat(collect($oldScheduleDrivers))->values();

        $drivers = [];
        foreach ($unsinedDriver as $driver) {
            $drivers[] = [
                'id' => $driver['id'],
                'name' => $driver['first_name'],
                'capacity' => $driver['vehicle']['seats'] ?? 0,
                'vehicle_id' => $driver['vehicle']['id'] ?? 0
            ];
        }



        $groupedEmployees = $this->groupEmployeesByOfficeDistanceNew($employees);
        $refineGroups =  $this->refineGroupsNew($groupedEmployees, $radius = 2);
        $assignLocalityToGroups = $this->assignLocalityToGroupsNew($refineGroups);

        $selectedDriver = $this->assignDriversToGroupsNew($assignLocalityToGroups, $drivers);

        $datasave  = $this->saveRouteGroup($selectedDriver);

        if ($datasave->getData()->status === true) {
            return response()->json([
                'status'  => true,
                'message' => 'Groups saved successfully.',
            ]);
        }
    }

    // Save old schedules in route group and route_group_schedules and also group members table

    public function saveOldSchedule($schedulesWithCares)
    {

        foreach ($schedulesWithCares as $schedule) {

            $routeGroup = RouteGroup::create([
                'group_name' => $schedule['locality'],
                'driver_id'  => $schedule['driver_id'] ?? null,
            ]);

            // Assign employees to the group
            foreach ($schedule['cares'] as $employee) {
                RouteGroupUser::create([
                    'route_group_id' => $routeGroup->id,
                    'user_id' => $employee,
                    'latitude' => $employee['latitude'] ?? null,
                    'longitude' => $employee['longitude'] ?? null
                ]);
            }

            $occursOn = is_string($schedule['occurs_on']) ? json_decode($schedule['occurs_on'], true) : $schedule['occurs_on'];

            $startTimeRaw = $schedule['start_time'] ?? null;
            $endTimeRaw = $schedule['end_time'] ?? null;

            $startTime = $startTimeRaw ? preg_replace('/\s+:/', ':', trim($startTimeRaw)) : null;
            $endTime = $endTimeRaw ? preg_replace('/\s+:/', ':', trim($endTimeRaw)) : null;


            $pickTime = ($startTime && strtotime($startTime)) ? Carbon::parse($startTime)->format('H:i') : null;
            $dropTime = ($endTime && strtotime($endTime)) ? Carbon::parse($endTime)->format('H:i') : null;


            RouteGroupSchedule::create([
                'route_group_id' => $routeGroup->id,
                'date' => now()->toDateString(),
                'pick_time' => $pickTime,
                'drop_time' => $dropTime,
                'shift_finishes_next_day' => $schedule['shift_finishes_next_day'],
                'custom_checked' => 0,
                'infinite_checked' => 1,
                'driver_id' => $schedule['driver_id'] ?? null,
                'vehicle_id' => $schedule['vehicle_id'] ?? null,
                'shift_type_id' => $schedule['shift_type_id'],
                'scheduleLocation' => $schedule['locality'],
                'scheduleCity' => $schedule['city'],
                'selectedLocationLat' => $schedule['latitude'],
                'selectedLocationLng' => $schedule['longitude'],
                'pricebook_id' => $schedule['pricebook_id'],
                'is_repeat' => 1,
                'carers' => $schedule['cares'],
                'repeat' => 0,
                'seats' => $schedule['vehicle_seates']->seats,
                'reacurrance' => $schedule['reacurrance'] ?? 0,
                'end_date' => $schedule['end_date'] ?? now()->addMonth()->toDateString(),
                'repeat_weeks' => 0,
                'occurs_on' => $occursOn,
                'is_schedule' => 1,
                'schedule_id' => $schedule['id'],
            ]);
        }
    }


    // Get schedule list
    public function oldScheduleAutomation()
    {

        $schedules = Schedule::with('carers')
            ->whereDate('end_date', '>=', Carbon::today())
            ->get();

        $result = [];

        foreach ($schedules as $schedule) {
            $uniqueCarers = [];
            foreach ($schedule['carers'] as $carer) {
                $uniqueCarers[$carer['carer_id']] = true;
            }
            $result[] = [
                'id' => $schedule['id'],
                'date' => $schedule['date'],
                'driver_id' => $schedule['driver_id'],
                'vehicle_id' => $schedule['vehicle_id'],
                'vehicle_seates' => Vehicle::select('id', 'name', 'seats')->find($schedule['vehicle_id']),
                'schedule_parent_id' => $schedule['schedule_parent_id'],
                'shift_finishes_next_day' => $schedule['shift_finishes_next_day'],
                'start_time' => $schedule['start_time'],
                'end_time' => $schedule['end_time'],
                'break_time_in_minutes' => $schedule['break_time_in_minutes'],
                'is_repeat' => $schedule['is_repeat'],
                'is_splitted' => $schedule['is_splitted'],
                'reacurrance' => $schedule['reacurrance'],
                'repeat_time' => $schedule['repeat_time'],
                'occurs_on' => $schedule['occurs_on'],
                'end_date' => $schedule['end_date'],
                'address' => $schedule['address'],
                'pickup_lat' => $schedule['pickup_lat'],
                'pickup_long' => $schedule['pickup_long'],
                'apartment_no' => $schedule['apartment_no'],
                'is_drop_off_address' => $schedule['is_drop_off_address'],
                'drop_off_address' => $schedule['drop_off_address'],
                'excluded_dates' => $schedule['excluded_dates'],
                'dropoff_lat' => $schedule['dropoff_lat'],
                'dropoff_long' => $schedule['dropoff_long'],
                'drop_off_apartment_no' => $schedule['drop_off_apartment_no'],
                'mileage' => $schedule['mileage'],
                'shift_type_id' => $schedule['shift_type_id'],
                'allowance_id' => $schedule['allowance_id'],
                'additional_cost' => $schedule['additional_cost'],
                'ignore_staff_count' => $schedule['ignore_staff_count'],
                'confirmation_required' => $schedule['confirmation_required'],
                'notify_carer' => $schedule['notify_carer'],
                'add_to_job_board' => $schedule['add_to_job_board'],
                'shift_assignment' => $schedule['shift_assignment'],
                'team_id' => $schedule['team_id'],
                'language_id' => $schedule['language_id'],
                'compliance_id' => $schedule['compliance_id'],
                'competency_id' => $schedule['competency_id'],
                'kpi_id' => $schedule['kpi_id'],
                'distance_from_shift_location' => $schedule['distance_from_shift_location'],
                'instructions' => $schedule['instructions'],
                'locality' => $schedule['locality'],
                'city' => $schedule['city'],
                'latitude' => $schedule['latitude'],
                'longitude' => $schedule['longitude'],
                'pricebook_id' => $schedule['pricebook_id'],
                'previous_day_pick' => $schedule['previous_day_pick'],
                'position_status' => $schedule['position_status'],
                'cares' => array_keys($uniqueCarers)
            ];
        }
        return $result;
    }


    // save gropup data
    public function saveRouteGroup($selectedDriver)
    {
        //DB::beginTransaction();
        try {


            $shiftTime = HrmsTimeAndShift::get()->first();
            // 1. Check if shift ends the next day
            $startTime = Carbon::createFromFormat('H:i', $shiftTime->shift_time['start']);
            $endTime = Carbon::createFromFormat('H:i', $shiftTime->shift_time['end']);

            $shiftFinishesNextDay = $endTime->lessThanOrEqualTo($startTime) ? 1 : 0;
            // 2. Convert shift_days to array of lowercase day names with value 1

            $shiftDays = [];
            foreach ($shiftTime->shift_days as $day => $value) {
                if ($value == '1') {
                    $shiftDays[] = strtolower($day);
                }
            }
            $occurs_on =  $shiftDays;
            $priceBooks =  PriceBook::get();

            foreach ($selectedDriver as $group) {
                // Create a new route group
                $routeGroup = RouteGroup::create([
                    'group_name' => $group['group'],
                    'driver_id'  => isset($group['driver']['id']) ? $group['driver']['id'] : null,
                ]);

                // Assign employees to the group
                foreach ($group['employees'] as $employee) {
                    RouteGroupUser::create([
                        'route_group_id' => $routeGroup->id,
                        'user_id' => $employee['id'],
                        'latitude' => $employee['latitude'],
                        'longitude' => $employee['longitude']
                    ]);
                }

                // save data into the route_group_schedules

                $scheduleLocation = trim(explode(' -', $group['group'])[0]);
                $groupName =  $group['group'];
                $capacity = $group['driver']['capacity'] ?? 0;

                // Extract area from group name (before ' - Group')



                $priceBookId = 1;
                $firstPriceBook = PriceBook::first();
                if ($firstPriceBook) {
                    $priceBookId = $firstPriceBook->id;
                }

                // Extract area from group name (before ' - Group')
                if (str_contains($groupName, ' -')) {
                    $area = $groupName;
                    $fullPriceBookName = "{$area} {$capacity} seaters";
                    $matchedPriceBook = $priceBooks->first(function ($book) use ($fullPriceBookName) {
                        return strcasecmp($book->name, $fullPriceBookName) === 0; // Case-insensitive match
                    });
                    if ($matchedPriceBook) {
                        $priceBookId = $matchedPriceBook->id;
                    }
                }

                // Get lat & log according group city name
                $coordinates = $this->getLatLngFromLocation($scheduleLocation);
                $carers = array_column($group['employees'], 'id');

                $sixYearsLater = Carbon::now()->addYears(6);
                $end_date  = $sixYearsLater->toDateString();

                RouteGroupSchedule::create([
                    'route_group_id' => $routeGroup->id,
                    'date' => now()->toDateString(),
                    'pick_time' => $shiftTime->shift_time['start'],
                    'drop_time' => $shiftTime->shift_time['end'],
                    'shift_finishes_next_day' => $shiftFinishesNextDay,
                    'custom_checked' => 0,
                    'infinite_checked' => 1,
                    'driver_id' => $group['driver']['id'] ?? null,
                    'vehicle_id' => $group['driver']['vehicle_id'] ?? null,
                    'shift_type_id' => 2,
                    'scheduleLocation' => $scheduleLocation,
                    'scheduleCity' => $scheduleLocation,
                    'selectedLocationLat' => $coordinates['lat'],
                    'selectedLocationLng' => $coordinates['lng'],
                    'pricebook_id' => $priceBookId,
                    'is_repeat' => 1,
                    'carers' => $carers,
                    'repeat' => 0,
                    'seats' => $capacity,
                    'reacurrance' => 'weekly',
                    'end_date' => $end_date,
                    'repeat_weeks' => 1,
                    'occurs_on' => $occurs_on
                ]);
            }

            // DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Groups saved successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status'  => false,
                'message' => 'Error saving groups.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getLatLngFromLocation($location)
    {
        $apiKey = 'AIzaSyAjESclgKTCpmdmHKc7bzCKIQtYe_vIbb4'; // Replace with your actual key

        $response = Http::withOptions(['verify' => false])->get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $location,
            'key' => $apiKey,
        ]);

        if ($response->ok() && !empty($response['results'])) {
            $coordinates = $response['results'][0]['geometry']['location'];

            return [
                'lat' => $coordinates['lat'],
                'lng' => $coordinates['lng'],
            ];
        }

        return null;
    }



    public function unshinedemployeeListLeft($users)
    {

        return User::whereHas('roles', function ($query) {
            $query->where('role_id', 4);
        })
            ->whereIn('id', $users)
            // ->whereNotNull('latitude')
            // ->whereNotNull('longitude')
            ->select('id', 'first_name', 'last_name', 'email', 'office_distance', 'latitude', 'longitude', 'address', 'profile_image')
            ->get();
    }

    public function unsinedDriverLeft($users)
    {

        return $subUsers = SubUser::whereHas('roles', function ($query) {
            $query->where('role_id', 5);
        })
            ->whereIn('id', $users)
            ->with('vehicle')
            ->select('id', 'first_name', 'last_name', 'email', 'profile_image')->get();
    }


    public function unshinedemployeeList()
    {


        $today = now()->toDateString();

        // return $subUsers = User::whereHas('roles', function ($query) {
        //     $query->where('role_id', 4);
        // })->doesntHave('scheduleCarers')
        // ->whereNotNull('latitude')
        // ->whereNotNull('longitude')
        // ->select('id', 'first_name', 'email','office_distance','latitude','longitude','address')
        // ->get();


        return User::whereHas('roles', function ($query) {
            $query->where('role_id', 4);
        })
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            // Only include users who don't have any schedule with end_date >= today
            ->whereDoesntHave('schedules', function ($query) use ($today) {
                $query->whereDate('end_date', '>=', $today);
            })
            ->select('id', 'first_name', 'last_name', 'email', 'office_distance', 'latitude', 'longitude', 'address', 'profile_image')
            ->get();
    }

    public function unsinedDriver()
    {

        $today = now()->toDateString();
        return $subUsers = SubUser::whereHas('roles', function ($query) {
            $query->where('role_id', 5);
        })
            ->whereDoesntHave('schedulesAsDriver', function ($query) use ($today) {
                $query->whereDate('end_date', '>=', $today);
            })
            ->with('vehicle')
            ->select('id', 'first_name', 'last_name', 'email', 'profile_image')->get();
    }


    function groupEmployeesByOfficeDistanceNew($employees, $distance_threshold = 3)
    {
        // Sort employees by office distance
        $employees = $employees->toArray();
        usort($employees, function ($a, $b) {
            return $a['office_distance'] <=> $b['office_distance'];
        });

        $groups = [];
        $current_group = [];
        $first_employee_distance = null;

        foreach ($employees as $employee) {
            // If first employee in group, set reference distance
            if (empty($current_group)) {
                $first_employee_distance = $employee['office_distance'];
                $current_group[] = $employee;
            }
            // If within threshold from first employee in group, add to group
            else if (($employee['office_distance'] - $first_employee_distance) <= $distance_threshold) {
                $current_group[] = $employee;
            }
            // Otherwise, start a new group
            else {
                $groups[] = $current_group;
                $current_group = [$employee];
                $first_employee_distance = $employee['office_distance'];
            }
        }

        // Add last group if not empty
        if (!empty($current_group)) {
            $groups[] = $current_group;
        }

        // Format output
        $grouped_result = [];
        foreach ($groups as $index => $group) {
            $grouped_result["Group " . ($index + 1)] = $group;
        }

        return $grouped_result;
    }


    function refineGroupsNew($groups, $radius = 2)
    {

        $newGroups = [];
        $groupIndex = 1;
        $checkedEmployees = [];

        foreach ($groups as $group) {
            foreach ($group as $employee) {
                if (in_array($employee['id'], $checkedEmployees)) {
                    continue; // Skip if already assigned
                }

                $newGroup = [$employee];
                $checkedEmployees[] = $employee['id'];

                foreach ($groups as $otherGroup) {
                    foreach ($otherGroup as $otherEmployee) {
                        if (in_array($otherEmployee['id'], $checkedEmployees)) {
                            continue;
                        }

                        // Check if within the given radius
                        if ($this->haversineDistance($employee['latitude'], $employee['longitude'], $otherEmployee['latitude'], $otherEmployee['longitude']) <= $radius) {
                            $newGroup[] = $otherEmployee;
                            $checkedEmployees[] = $otherEmployee['id'];
                        }
                    }
                }
                $newGroups["Group " . $groupIndex] = $newGroup;
                $groupIndex++;
            }
        }
        return $newGroups;
    }



    function getCentroid($group)
    {
        $latSum = 0;
        $lonSum = 0;
        $count = count($group);

        foreach ($group as $member) {
            $latSum += $member['latitude'];
            $lonSum += $member['longitude'];
        }

        return [
            'latitude' => $latSum / $count,
            'longitude' => $lonSum / $count
        ];
    }

    function getLocalityName($latitude, $longitude)
    {
        $apiKey = "AIzaSyAjESclgKTCpmdmHKc7bzCKIQtYe_vIbb4"; // Replace with your API key
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&key=$apiKey";

        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if ($data['status'] == 'OK') {
            foreach ($data['results'][0]['address_components'] as $component) {
                if (in_array("locality", $component["types"])) {
                    return $component["long_name"];
                }
            }
        }
        return "Unknown Area";
    }


    function assignLocalityToGroupsNew($groups)
    {
        $groupedByLocality = [];
        $groupNumber = 1; // Start dummy group names from Group 1

        foreach ($groups as $group) {
            // Find the centroid of the group
            $centroid = $this->getCentroid($group);

            // Get locality name based on centroid location
            $locality = $this->getLocalityName($centroid['latitude'], $centroid['longitude']);

            // Assign a dummy name if locality is missing or unknown
            if (empty($locality) || $locality === "Unknown Area") {
                $locality = "Group " . $groupNumber;
                $groupNumber++; // Increment for the next unnamed group
            } else {
                // Append a unique group number even if locality is found
                $locality .= " - Group " . $groupNumber;
                $groupNumber++;
            }

            // Ensure the structure remains consistent
            if (!isset($groupedByLocality[$locality])) {
                $groupedByLocality[$locality] = [];
            }

            // $groupedByLocality[$locality][] = $group;
            $groupedByLocality[$locality] = $group;
        }

        return $groupedByLocality;
    }



    function assignDriversToGroupsNew($groups, $drivers)
    {
        // Sort groups by size (largest first) while preserving locality keys
        uksort($groups, function ($a, $b) use ($groups) {
            return count($groups[$b]) - count($groups[$a]);
        });

        // Sort drivers by capacity (largest first)
        usort($drivers, function ($a, $b) {
            return $b['capacity'] - $a['capacity'];
        });

        $newGroups = [];
        $assignedDrivers = []; // Track used drivers

        foreach ($groups as $groupName => $employees) {
            $remainingEmployees = $employees;
            $totalEmployees = count($employees);

            foreach ($drivers as $index => $driver) {
                if ($totalEmployees <= 0) break; // Stop if all employees are assigned
                if (isset($assignedDrivers[$driver['id']])) continue; // Skip used drivers

                if ($driver['capacity'] >= $totalEmployees) {
                    // Assign one driver to the full group
                    $newGroups[] = [
                        'group' => $groupName,
                        'employees' => $remainingEmployees,
                        'driver' => $driver
                    ];
                    $assignedDrivers[$driver['id']] = true;
                    $totalEmployees = 0;
                    break; // Move to the next group
                } else {
                    // Assign as many employees as the driver can take
                    $assignedEmployees = array_splice($remainingEmployees, 0, $driver['capacity']);
                    $newGroups[] = [
                        'group' => $groupName . ' (Split)',
                        'employees' => $assignedEmployees,
                        'driver' => $driver
                    ];
                    $assignedDrivers[$driver['id']] = true;
                    $totalEmployees -= count($assignedEmployees);
                }
            }

            // If employees are left without a driver, mark them as unassigned
            if ($totalEmployees > 0) {
                $newGroups[] = [
                    'group' => $groupName . ' (Unassigned)',
                    'employees' => $remainingEmployees,
                    'driver' => null
                ];
            }
        }

        return $newGroups;
    }

    function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Radius of the Earth in km
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 2);
    }

    /**
     * @OA\Post(
     * path="/uc/api/routeAutomationList",
     * operationId="routeAutomationList",
     * tags={"Ucruise Driver"},
     * summary="Driver route Automation list",
     *   security={ {"Bearer": {} }},
     * description="Driver route Automation list",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The driver updated successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The driver updated successfully.",
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

    public function routeAutomationList(Request $request)
    {

        try {

            $userDriverData = $this->addedEmploandDriverlist();

            $groupsRoute = RouteGroup::with(['driver.vehicle', 'users.user', 'routeSchedule'])->get();
            $company = CompanyAddresse::get()->first();
            $pricbook = PriceBook::get();
            $shiftType =  ShiftTypes::get();

            $groupsRoute->transform(function ($group) {
                $assignedSeats = $group->users->count();

                // Check if driver and vehicle exist
                $vehicleSeats = $group->driver && $group->driver->vehicle
                    ? $group->driver->vehicle->seats
                    : 0;

                $leftSeats = max($vehicleSeats - $assignedSeats, 0);
                $group->assigned_seats = $assignedSeats;
                $group->left_seats = $leftSeats;
                return $group;
            });

            if ($groupsRoute->isNotEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Found route automation data',
                    'company' => $company,
                    'pricebook' => $pricbook,
                    'shiftType' => $shiftType,
                    'data' => $groupsRoute,
                    'user_driver' => $userDriverData
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Not found data'

                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }



    /**
     * @OA\Post(
     * path="/uc/api/routeAutomationAddNewEmployee",
     * operationId="routeAutomationAddNewEmployee",
     * tags={"Ucruise Driver"},
     * summary="Driver route Automation new employee",
     *   security={ {"Bearer": {} }},
     * description="Driver route Automation new employee",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The New employee updated successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The New employee updated successfully.",
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

    public function routeAutomationAddNewEmployee(Request $reques)
    {

        $employees = User::select('id', 'first_name', 'email', 'latitude', 'longitude', 'address')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $officeLat = 30.6810489;
        $officeLng = 76.7260711;

        // Update the office_distance field for the user with the given user_id and distance

        foreach ($employees as $employee) {
            $user = User::find($employee['id']);
            if ($user) {
                if (empty($user->office_distance)) {
                    $distance = $this->haversine($officeLat, $officeLng, $user->latitude, $user->longitude);
                    User::where('id', $employee['id'])->update(['office_distance' => $distance]);
                }
            }
        }

        $assigned = RouteGroupUser::select('id', 'route_group_id', 'user_id', 'latitude', 'longitude')->get();
        $mixed =  $this->unshinedemployeeList();

        $assignedUserIds = array_column($assigned->toArray(), 'user_id');

        // Filter mixed array to get unassigned users
        $unassigned = array_filter($mixed->toArray(), function ($user) use ($assignedUserIds) {
            return !in_array((string) $user['id'], $assignedUserIds); // compare as string
        });

        $unassigned = array_values($unassigned);

        if (empty($unassigned)) {
            return response()->json([
                'status' => false,
                'message' => 'No unassigned users found.',
            ], 422);
        }

        $request = Request::capture();
        $assignedGroupData = json_decode($this->routeAutomationList($request)->getContent(), true)['data'];

        // Checking near about distance in each group

        $matchedAssignments = [];

        // foreach ($unassigned as $user) {
        //     $bestMatch = null;
        //     $closestDistance = null;

        //     foreach ($assignedGroupData as $group) {
        //         $isGroupWithDriver = $group['driver'] !== null;
        //         $canCheckGroup = ($isGroupWithDriver && $group['left_seats'] > 0) || !$isGroupWithDriver;

        //         if (!$canCheckGroup) continue;

        //         foreach ($group['users'] as $groupUser) {
        //             $distance = $this->haversineDistance(
        //                 $user['latitude'], $user['longitude'],
        //                 $groupUser['latitude'], $groupUser['longitude']
        //             );

        //             $isWithinDistance = $isGroupWithDriver ? $distance <= 6 : true;

        //             if ($isWithinDistance) {
        //                 if (is_null($closestDistance) || $distance < $closestDistance) {
        //                     $closestDistance = $distance;
        //                     $bestMatch = [
        //                         'unsigned_user_id' => $user['id'],
        //                         'unsigned_user_name' => $user['first_name'],
        //                         'latitude' => $user['latitude'],
        //                         'longitude' => $user['longitude'],
        //                         'matched_group_id' => $group['id'],
        //                         'matched_group_name' => $group['group_name'],
        //                         'distance_km' => round($distance, 2),
        //                     ];
        //                 }
        //             }
        //         }
        //     }

        //     if (!is_null($bestMatch)) {
        //         $matchedAssignments[] = $bestMatch;
        //     }
        // }



        $matchedAssignments = [];

        foreach ($unassigned as $user) {
            $bestMatch = null;
            $closestDistance = null;

            foreach ($assignedGroupData as $group) {
                $isGroupWithDriver = $group['driver'] !== null;
                $canCheckGroup = ($isGroupWithDriver && $group['left_seats'] > 0) || !$isGroupWithDriver;

                if (!$canCheckGroup) continue;

                foreach ($group['users'] as $groupUser) {
                    $distance = $this->haversineDistance(
                        $user['latitude'],
                        $user['longitude'],
                        $groupUser['latitude'],
                        $groupUser['longitude']
                    );

                    $isWithinDistance = $isGroupWithDriver ? $distance <= 5 : true;

                    if ($isWithinDistance) {
                        if (is_null($closestDistance) || $distance < $closestDistance) {
                            $closestDistance = $distance;
                            $bestMatch = [
                                'unsigned_user_id' => $user['id'],
                                'unsigned_user_name' => $user['first_name'],
                                'latitude' => $user['latitude'],
                                'longitude' => $user['longitude'],
                                'matched_group_id' => $group['id'],
                                'matched_group_name' => $group['group_name'],
                                'distance_km' => round($distance, 2),
                            ];
                        }
                    }
                }
            }

            if (!is_null($bestMatch)) {
                $matchedAssignments[] = $bestMatch;
            } else {
                // Simulate a new group creation and include in response only
                $matchedAssignments[] = [
                    'unsigned_user_id' => $user['id'],
                    'unsigned_user_name' => $user['first_name'],
                    'latitude' => $user['latitude'],
                    'longitude' => $user['longitude'],
                    'matched_group_id' => null,
                    'matched_group_name' => $this->getLocalityName($user['latitude'], $user['longitude']) . ' - Group (Unassigned)',
                    'distance_km' => 0.0,
                    'new_group_created' => true, // Optional flag
                ];
            }
        }

        foreach ($matchedAssignments as $assignments) {

            if ($assignments['matched_group_id'] > 0) {
                RouteGroupUser::create([
                    'route_group_id' => $assignments['matched_group_id'],
                    'user_id' => $assignments['unsigned_user_id'],
                    'latitude' => $assignments['latitude'],
                    'longitude' => $assignments['longitude'],
                ]);
            } else {

                if (isset($assignments['new_group_created'])) {
                    $newGroup = RouteGroup::create([
                        'group_name' => $assignments['matched_group_name'],
                        'driver_id' => null,
                    ]);
                }

                RouteGroupUser::create([
                    'route_group_id' => $newGroup['id'],
                    'user_id' => $assignments['unsigned_user_id'],
                    'latitude' => $assignments['latitude'],
                    'longitude' => $assignments['longitude'],
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Assigned users in the sutable groups',
        ], 200);

        //return $matchedAssignments;

    }


    /**
     * @OA\Post(
     * path="/uc/api/unsignDriverEmployee",
     * operationId="unsignDriverEmployee",
     * tags={"Ucruise Driver"},
     * summary="Driver route Automation new employee driver",
     *   security={ {"Bearer": {} }},
     * description="Driver route Automation new employee driver",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The New employee updated successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The New employee updated successfully.",
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

    public function unsignDriverEmployee(Request $request)
    {

        $this->data['unshinedemployeeList'] = $this->unshinedemployeeList();
        $this->data['unsinedDriver'] = $this->unsinedDriver();

        return response()->json([
            'status' => true,
            'message' => 'Unshined employee and driver list',
            'data' => $this->data
        ], 200);
    }


    /**
     * @OA\Post(
     *     path="/uc/api/addEmployeeIngroup",
     *     operationId="addEmployeeIngroup",
     *     tags={"Ucruise Driver"},
     *     summary="Driver route Automation addEmployeeIngroup",
     *     security={{"Bearer": {}}},
     *     description="Driver route Automation addEmployeeIngroup",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"group_id", "user_id", "latitude", "longitude"},
     *                 @OA\Property(property="group_id", type="integer", description="Route automation group id"),
     *                 @OA\Property(property="user_id", type="integer", description="User ID added in group"),
     *                 @OA\Property(property="latitude", type="string", description="Latitude of user"),
     *                 @OA\Property(property="longitude", type="string", description="Longitude of user"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="The new employee updated successfully.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The new employee updated successfully.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found")
     * )
     */


    public function addEmployeeIngroup(Request $request)
    {

        $validated = $request->validate([
            'group_id'  => 'required|integer|exists:route_groups,id',
            'user_id'   => 'required|integer',
            'latitude'  => 'required',
            'longitude' => 'required',
        ]);
        $routeGroup = RouteGroup::find($validated['group_id']);
        if (!empty($routeGroup)) {
            RouteGroupUser::create([
                'route_group_id' => $validated['group_id'],
                'user_id' => $validated['user_id'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);
        }

        $data = RouteGroup::with(['driver.vehicle', 'users.user'])->find($validated['group_id']);

        return response()->json([
            'status' => true,
            'message' => 'Validation passed.',
            'data' => $data
        ]);
    }






    /**
     * @OA\Get(
     * path="/uc/api/employee_dashboard/authEmployeeDetail",
     * operationId="authemployeedetails",
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




    public function authEmployeeDetail(Request $request)
    {
        try {

            $user_id = auth('sanctum')->user()->id;
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

    public function driverRating($driverId)
    {

        $driver = SubUser::whereHas("roles", function ($q) {
            $q->where("name", "driver");
        })->find($driverId);

        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Driver not found or not assigned driver role.'
            ], 404);
        }

        // ✅ Fetch all ratings for this driver
        $ratings = Rating::where('driver_id', $driver->id)->pluck('rate')->toArray();

        $averageRating = 0;
        if (!empty($ratings)) {
            $sumOfRatings = array_sum($ratings);
            $averageRating = round($sumOfRatings / count($ratings), 1); // average out of 5
        }

         return  [
                'driver_id' => $driver->id,
                'name'      => $driver->first_name,
                'email'     => $driver->email,
                'average_rating' => $averageRating, // out of 5
                'total_reviews'  => count($ratings)
         ];

    }

    /**
 * @OA\Get(
 *     path="/api/driver/billing",
 *     operationId="allDriverBillingfordriver",
 *     tags={"Driver"},
 *     summary="Get billing data for a specific driver",
 *     description="Returns chart data for driver billing within current month",
 *     security={{"Bearer": {}}},
 *     @OA\Parameter(
 *         name="driver_id",
 *         in="query",
 *         required=true,
 *         description="ID of the driver",
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="chart_data",
 *                 type="object",
 *                 description="Billing chart data for the driver"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Validation failed."),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 @OA\Property(
 *                     property="driver_id",
 *                     type="array",
 *                     @OA\Items(type="string", example="The driver_id field is required.")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Error message")
 *         )
 *     )
 * )
 */

   public function allDriverBilling(Request $request)
    {
        try {


              $validator = Validator::make($request->all(), [
                    'driver_id' => 'required|integer|exists:sub_users,id',

                ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $driverId = $request->driver_id;

            // ✅ Current year and month
            $currentYear = Carbon::now()->year;
            $currentMonth = Carbon::now()->month;

            $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1);
            $endDate = Carbon::now(); // Only till today

            // Format dates
            $start_Date = $startDate->format('Y-m-d');
            $end_Date = $endDate->format('Y-m-d');

            $dates = $this->generateDatesInRange($start_Date, $end_Date);


            // ✅ Get chart data and drivers data only for this driver
            $chartData = $this->getChartData($dates, $driverId);




            return response()->json([
                'success' => true,
                'chart_data' => $chartData,

            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


      public function getChartData($dates, $driverId = null)
    {
        // Start building the query
        $invoiceData = Invoice::whereIn('date', $dates)
           ->where('is_included', '1');

        // ✅ Filter by driver if driverId is provided
        if (!is_null($driverId)) {
            $invoiceData->where('driver_id', $driverId);
        }

        // ✅ Get all invoices (paid + unpaid)
       $invoiceResults = $invoiceData->get();


        $totalBillings = 0;

        foreach ($invoiceResults as $invoice) {
            $daytype = Carbon::createFromFormat('Y-m-d', $invoice->date);
            $dayOfWeek = $daytype->dayOfWeek;

            $category = "";
            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                $category = 'Weekdays (mon- fri)';
            } elseif ($dayOfWeek === 6) {
                $category = 'saturday';
            } else {
                $category = 'sunday';
            }

            $pricebookId = $invoice->pricebook_id;
            $schedulePrice = PriceTableData::where('price_book_id', $pricebookId)
                ->where('day_of_week', $category)
                ->first();

            if ($schedulePrice) {
                $totalBillings += $schedulePrice->per_ride;
            }
        }

        // ✅ Calculate totals and counts
        $totalBilling = $totalBillings;
        $totalCount = $invoiceResults->count();
        $unpaidCount = $invoiceResults->where('status', 1)->count();
        $paidCount = $invoiceResults->where('status', 2)->count();
        $unpaid = $invoiceResults->where('status', 1)->sum('fare');
        $paid = $invoiceResults->where('status', 2)->sum('fare');

        return [
            'total_billing' => $totalBilling,
            'unpaid'        => $unpaid,
            'paid'          => $paid,
            'total_count'   => $totalCount,
            'unpaid_count'  => $unpaidCount,
            'paid_count'    => $paidCount,
        ];
    }




    /**
     * @OA\Post(
     *     path="/api/getDriverMonthlyRideStats",
     *     operationId="getDriverMonthlyRideStats",
     *     tags={"Driver Statistics"},
     *     summary="Get driver's monthly ride statistics",
     *     description="Returns count of total rides and breakdown by status (Absent, Cancel, Reschedule, Remaining, Complete) for the current month",
     *     security={ {"Bearer": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", example=123, description="Driver ID"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_rides", type="integer", example=15),
     *                 @OA\Property(property="Absent", type="integer", example=2),
     *                 @OA\Property(property="Cancel", type="integer", example=1),
     *                 @OA\Property(property="Reschedule", type="integer", example=3),
     *                 @OA\Property(property="Remaining", type="integer", example=5),
     *                 @OA\Property(property="Complete", type="integer", example=4)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid input")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Driver not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Driver not found or invalid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The id field is required")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function getDriverMonthlyRideStats(Request $request)
    {
        $driverId = $request->id;

        // Validate the driver exists and has driver role
        $driver = SubUser::find($driverId);
        if (!$driver || !$driver->hasRole('driver')) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not found or invalid'
            ], 404);
        }

        // Get first day of current month and today's date
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $today = now()->format('Y-m-d');

        // Get all schedules with their statuses
        $schedules = Schedule::with(['scheduleStatus.status'])
            ->whereBetween('date', [$startOfMonth, $today])
            ->where('driver_id', $driverId)
            ->get();

        // Initialize counters
        $stats = [
            'total_rides' => $schedules->count(),
            'Absent' => 0,
            'Cancel' => 0,
            'Reschedule' => 0,
            'Remaining' => 0,
            'Complete' => 0
        ];

        // Count statuses
        foreach ($schedules as $schedule) {
            if ($schedule->scheduleStatus && $schedule->scheduleStatus->status) {
                $statusName = $schedule->scheduleStatus->status->name;

                if (isset($stats[$statusName])) {
                    $stats[$statusName]++;
                }
            } else {
                // If no status is set, consider it as "Remaining"
                $stats['Remaining']++;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }


    /**
     * @OA\Get(
     * path="/uc/api/getDesignations",
     * operationId="getDesignations",
     * tags={"Ucruise Employee"},
     * summary="Get all designations",
     *   security={ {"Bearer": {} }},
     * description="Retrieve all designations for dropdown",
     *      @OA\Response(
     *          response=200,
     *          description="Designations retrieved successfully",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="title", type="string", example="Manager")
     *                  )
     *              ),
     *              @OA\Property(property="message", type="string", example="Designations retrieved successfully")
     *          )
     *       ),
     *      @OA\Response(
     *          response=500,
     *          description="Server error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="An error occurred")
     *          )
     *       ),
     * )
     */
    public function getDesignations()
    {
        try {
            $designations = Designation::select('id', 'title')->get();

            return response()->json([
                'success' => true,
                'data' => $designations,
                'message' => 'Designations retrieved successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }







}
