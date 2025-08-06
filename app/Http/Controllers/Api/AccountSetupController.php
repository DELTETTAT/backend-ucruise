<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use App\Models\{
    CarersNoshowTimer,
    CompanyAddresse,
    CompanyDetails,
    DocCategory,
    Faq,
    Holiday,
    PriceBook,
    PriceTableData,
    Reason,
    Reminder,
    RideSetting,
    Subscription,
    SubUser,
    User,
    UserSubscription,
    SubUserAddresse,
    Vehicle,
    Role,
    Schedule,
    ScheduleCarer,
    ScheduleCarerStatus,
    ScheduleTemplate,
    ShiftTypes,
    HrmsTimeAndShift,
    GroupLoginUser,
    HrmsEmployeeRole,
    HrmsRole,
    TeamManager,
    NewApplicant,
    UpdateSystemSetupHistory
};
use Carbon\Carbon;
use App\Mail\AnnouncementMail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Jobs\SendAnnouncementEmail;

use Illuminate\Support\Facades\{Auth, DB, Hash, Mail, Validator};

class AccountSetupController extends Controller
{

    //****************************** Account Info api ******************************/

    /**
     * @OA\Get(
     * path="/uc/api/accountInfo",
     * operationId="accountInfo",
     * tags={"AccountSetup"},
     * summary="Account info",
     *   security={ {"Bearer": {} }},
     * description="Account info",
     *      @OA\Response(
     *          response=201,
     *          description="Account info listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Account info listed successfully",
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
    public function accountInfo()
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {
                $company = CompanyDetails::first();
                $companyDetails = \DB::table('company_addresses')
                    ->where('company_addresses.company_id', $company->id)
                    ->whereNull('company_addresses.end_date')
                    ->join('company_details', 'company_addresses.company_id', '=', 'company_details.id')
                    ->select('company_details.id', 'company_details.name', 'company_details.logo', 'company_details.email', 'company_details.phone', 'company_addresses.address', 'company_addresses.latitude', 'company_addresses.longitude')
                    ->first();

                if ($companyDetails) {
                    @$companyDetails->logo_url = url('public/images');
                    $this->data['companyDetails'] = $companyDetails;
                } else {
                    @$company->logo_url = url('public/images');
                    $this->data['companyDetails'] = $company;
                }
                $this->data['current_subscription'] = @$this->getCurrentSubscription($user_id);
                $this->data['announcements'] = $this->getAnnouncement();
                $this->data['shiftTypes'] = ShiftTypes::orderBy('id', 'DESC')->get();
                $this->data['docCategories'] = DocCategory::orderBy('id', 'DESC')->get();
                $this->data['cancelRideReasons'] = Reason::where('type', 3)->get();
                $this->data['leaveReasons'] = Reason::where('type', 0)->get();
                $this->data['ratingReasons'] = Reason::where('type', 4)->get();
                $this->data['complaintReasons'] = Reason::where('type', 1)->get();
                $this->data['shiftChangeReasons'] = Reason::where('type', 2)->get();
                $this->data['tempChangeReasons'] = Reason::where('type', 5)->get();
                $this->data['rideSettings'] = RideSetting::first();
                $this->data['holiday'] = Holiday::orderBy('id', 'DESC')->get();
                // $this->data['faqs'] = Faq::get();
                $this->data['Faq'] = Faq::select('id', 'question as title', 'answer as description', 'created_at', 'updated_at')->get();
                $this->data['pricebooks'] = PriceBook::orderBy('id', 'DESC')->with('priceBookData')->get();
                $this->data['filterPriceBooks'] = PriceBook::orderBy('id', 'DESC')
                    ->has('priceBookData')
                    ->get();

                $this->data['scheduleTemplate'] = ScheduleTemplate::with('pricebook')->get();

                $this->data['basicSettings'] = $company;
                $this->data['cdb'] = base64_encode($user->database_name);

                $this->data['hrmsReason']['reject_employee'] = Reason::where('type', 6)->get();
                $this->data['hrmsReason']['accept_employee'] = Reason::where('type', 7)->get();
                $this->data['hrmsReason']['future_reference_employee'] = Reason::where('type', 8)->get();
                $this->data['hrmsReason']['Reconsideration'] = Reason::where('type', 9)->get();

                return response()->json([
                    'success' => true,
                    'data' => @$this->data,
                    'message' => "Account info listed successfully"

                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "User is not admin"

                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    //******************** Function to get the current subscription plan ************/


    // scanner details


    /**
     * @OA\Get(
     * path="/uc/api/scannerInfo",
     * operationId="scannerInfo",
     * tags={"AccountSetup"},
     * summary="Scanner info",
     *   security={ {"Bearer": {} }},
     * description="Scanner info",
     *      @OA\Response(
     *          response=201,
     *          description="Scanner info listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Scanner info listed successfully",
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

    // end scanner details

    // public function scannerInfo()
    // {

    //     try {

    //        // $user_id = auth('sanctum')->user()->id;
    //         //  $user = User::find($user_id);
    //         $user = auth('sanctum')->user();
    //         $user_id = $user->id;

    //         // --- Get role of the user ---
    //         $auth_role = DB::table('role_user')->where('user_id', $user_id)->first();
    //         $employeeRole = DB::table('roles')->find($auth_role->role_id);

    //         if (!$employeeRole) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Role not assigned to user.'
    //             ], 403);
    //         }

    //         // --- Role logic check (admin or custom) ---
    //         if ($employeeRole->name != 'admin') {
    //             $role = HrmsEmployeeRole::where('employee_id', $user_id)->first();

    //             if (!$role) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'Employee role not found.'
    //                 ], 403);
    //             }

    //             $authRolePermissions = HrmsRole::with(['viewrole', 'hrms_permissions'])
    //                 ->where('id', $role->role_id)
    //                 ->first();

    //             if (!$authRolePermissions) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'No permissions assigned to this role.'
    //                 ], 403);
    //             }

    //             $this->data['permissions'] = $authRolePermissions;
    //             $this->data['is_admin'] = 0;
    //         }
    //         else {
    //             $this->data['permissions'] = null;
    //             $this->data['is_admin'] = 1;
    //         }


    //         $company = CompanyDetails::first();
    //         $companyDetails = DB::table('company_addresses')
    //             ->where('company_addresses.company_id', $company->id)
    //             ->whereNull('company_addresses.end_date')
    //             ->join('company_details', 'company_addresses.company_id', '=', 'company_details.id')
    //             ->select('company_details.id', 'company_details.name', 'company_details.logo', 'company_details.email', 'company_details.phone', 'company_addresses.address', 'company_addresses.latitude', 'company_addresses.longitude')
    //             ->first();

    //         if ($companyDetails) {
    //             @$companyDetails->logo_url = url('images');
    //             $this->data['companyDetails'] = $companyDetails;
    //         } else {
    //             @$company->logo_url = url('images');
    //             $this->data['companyDetails'] = $company;
    //         }
    //         $this->data['cdb'] = base64_encode($user->database_name);
    //         // $getTeamList = TeamManager::with(['employees','hrmsEmployees'])->get();
    //         //  $this->data['manager'] = $getTeamList;
    //         $teamManagers = TeamManager::with(['employees', 'hrmsTeam'])->get();



    //         $this->data['manager'] = $teamManagers;
    //         $today = now();
    //         $currentMonth = $today->format('Y-m');

    //         // $this->data['total_employees'] = SubUser::where('status', 1)->count();
    //         // $this->data['total_employees'] = SubUser::where('status', 1)
    //         //     ->where('employement_type', '!=', 'Driver')
    //         //     ->count();

    //         $this->data['on_board'] = SubUser::where('status', 1)
    //             ->where('doj', 'like', $currentMonth . '%')
    //             ->count();

    //         // $resigned = $this->data['resigned_employees'] = SubUser::where('status', 3)
    //         //     ->whereHas('statusUpdateReason', function ($query) use ($currentMonth) {
    //         //         $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth]);
    //         //     })
    //         //  ->count();
    //         $currentDate = now()->format('Y-m-d');

    //         $resigned = $this->data['resigned_employees'] = SubUser::where('status', 3)
    //             ->whereHas('statusUpdateReason', function ($query) use ($currentDate) {
    //                 $query->whereDate('created_at', '<=', $currentDate)
    //                     ->whereDate('last_working_date', '>=', $currentDate);
    //             })
    //             ->count();

    //         // $this->data['notice_period'] = $resigned;
    //         $terminated = $this->data['terminated_employees'] = SubUser::where('status', 6)
    //             ->whereHas('statusUpdateReason', function ($query) use ($currentMonth) {
    //                 $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth]);
    //             })
    //             ->count();
    //         $absconded = $this->data['absconded_employees'] = SubUser::where('status', 8)
    //             ->whereHas('statusUpdateReason', function ($query) use ($currentMonth) {
    //                 $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth]);
    //             })
    //             ->count();
    //         $suspanded = $this->data['suspanded_employees'] = SubUser::where('status', 5)
    //             ->whereHas('statusUpdateReason', function ($query) use ($currentMonth) {
    //                 $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth]);
    //             })
    //             ->count();
    //         foreach ($teamManagers as $manager) {
    //             $members = json_decode($manager->hrmsTeam->members, true);
    //             if ($members) {
    //                 $this->data['team_members_count'] = SubUser::whereIn('id', $members)
    //                     ->where('status', 1)
    //                     ->count();
    //             }
    //         }

    //         // Same for notice_period
    //         $this->data['resigned_employees'] = $resigned;
    //         $this->data['terminated_employees'] = $terminated;
    //         $this->data['absconded_employees'] = $absconded;
    //         $this->data['suspanded_employees'] = $suspanded;
    //         return response()->json([
    //             'success' => true,
    //             'data' => @$this->data,
    //             'message' => "Scanner info listed successfully"

    //         ], 200);
    //     } catch (\Throwable $th) {

    //         return response()->json([
    //             'success' => false,
    //             'message' => $th->getMessage()
    //         ], 500);
    //     }
    // }
    // public function scannerInfo()
    // {
    //     try {
    //         $user = auth('sanctum')->user();
    //         $user_id = $user->id;

    //         // --- Get role of the user ---
    //         $auth_role = DB::table('role_user')->where('user_id', $user_id)->first();
    //         $employeeRole = DB::table('roles')->find($auth_role->role_id);

    //         if (!$employeeRole) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Role not assigned to user.'
    //             ], 403);
    //         }

    //         $teamManagers = TeamManager::with(['employees', 'hrmsTeam'])->get();

    //         // --- Role logic check (admin or custom) ---
    //         if ($employeeRole->name != 'admin') {
    //             $role = HrmsEmployeeRole::where('employee_id', $user_id)->first();

    //             if (!$role) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'Employee role not found.'
    //                 ], 403);
    //             }

    //             $authRolePermissions = HrmsRole::with(['viewrole', 'hrms_permissions'])
    //                 ->where('id', $role->role_id)
    //                 ->first();

    //             if (!$authRolePermissions) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'No permissions assigned to this role.'
    //                 ], 403);
    //             }

    //             $this->data['permissions'] = $authRolePermissions;
    //             $this->data['is_admin'] = 0;

    //             // 👇 Check if HRMS role name is "Manager"
    //             if (strtolower($authRolePermissions->name) === 'manager') {
    //                 foreach ($teamManagers as $manager) {
    //                     $members = json_decode($manager->hrmsTeam->members, true);
    //                     if ($members) {
    //                         $this->data['total_employees'] = SubUser::whereIn('id', $members)
    //                             ->where('status', 1)->where('employement_type', '!=', 'Driver')
    //                             ->count();
    //                         $today = now();
    //                         $currentMonth = $today->format('Y-m');

    //                         $this->data['on_board'] = SubUser::whereIn('id', $members)->where('status', 1)
    //                             ->where('doj', 'like', $currentMonth . '%')
    //                             ->count();

    //                         $currentDate = now()->format('Y-m-d');

    //                         $resigned = SubUser::whereIn('id', $members)->where('status', 3)
    //                             ->whereHas('statusUpdateReason', function ($query) use ($currentDate) {
    //                                 $query->whereDate('created_at', '<=', $currentDate)
    //                                     ->whereDate('last_working_date', '>=', $currentDate);
    //                             })
    //                             ->count();

    //                         $terminated = SubUser::whereIn('id', $members)->where('status', 6)
    //                             ->whereHas('statusUpdateReason', function ($query) use ($currentMonth) {
    //                                 $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth]);
    //                             })
    //                             ->count();

    //                         $absconded = SubUser::whereIn('id', $members)->where('status', 8)
    //                             ->whereHas('statusUpdateReason', function ($query) use ($currentMonth) {
    //                                 $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth]);
    //                             })
    //                             ->count();

    //                         $suspanded = SubUser::whereIn('id', $members)->where('status', 5)
    //                             ->whereHas('statusUpdateReason', function ($query) use ($currentMonth) {
    //                                 $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth]);
    //                             })
    //                             ->count();
    //                     }
    //                 }
    //             } else {
    //                 $this->data['total_employees'] = SubUser::where('status', 1)
    //                     ->where('employement_type', '!=', 'Driver')
    //                     ->count();
    //             }
    //         } else {
    //             $this->data['permissions'] = null;
    //             $this->data['is_admin'] = 1;

    //          //   👇 For admin, show total employees
    //             $this->data['total_employees'] = SubUser::where('status', 1)
    //                 ->where('employement_type', '!=', 'Driver')
    //                 ->count();
    //             $today = now();
    //             $currentMonth = $today->format('Y-m');

    //             $this->data['on_board'] = SubUser::where('status', 1)
    //                 ->where('doj', 'like', $currentMonth . '%')
    //                 ->count();

    //             $currentDate = now()->format('Y-m-d');

    //             $resigned = SubUser::where('status', 3)
    //                 ->whereHas('statusUpdateReason', function ($query) use ($currentDate) {
    //                     $query->whereDate('created_at', '<=', $currentDate)
    //                         ->whereDate('last_working_date', '>=', $currentDate);
    //                 })
    //                 ->count();

    //             $terminated = SubUser::where('status', 6)
    //                 ->whereHas('statusUpdateReason', function ($query) use ($currentMonth) {
    //                     $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth]);
    //                 })
    //                 ->count();

    //             $absconded = SubUser::where('status', 8)
    //                 ->whereHas('statusUpdateReason', function ($query) use ($currentMonth) {
    //                     $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth]);
    //                 })
    //                 ->count();

    //             $suspanded = SubUser::where('status', 5)
    //                 ->whereHas('statusUpdateReason', function ($query) use ($currentMonth) {
    //                     $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth]);
    //                 })
    //                 ->count();

    //             // Assign to response
    //             $this->data['resigned_employees'] = $resigned;
    //             $this->data['terminated_employees'] = $terminated;
    //             $this->data['absconded_employees'] = $absconded;
    //             $this->data['suspanded_employees'] = $suspanded;
    //         }

    //         // --- Company Info ---
    //         $company = CompanyDetails::first();
    //         $companyDetails = DB::table('company_addresses')
    //             ->where('company_addresses.company_id', $company->id)
    //             ->whereNull('company_addresses.end_date')
    //             ->join('company_details', 'company_addresses.company_id', '=', 'company_details.id')
    //             ->select('company_details.id', 'company_details.name', 'company_details.logo', 'company_details.email', 'company_details.phone', 'company_addresses.address', 'company_addresses.latitude', 'company_addresses.longitude')
    //             ->first();

    //         if ($companyDetails) {
    //             @$companyDetails->logo_url = url('images');
    //             $this->data['companyDetails'] = $companyDetails;
    //         } else {
    //             @$company->logo_url = url('images');
    //             $this->data['companyDetails'] = $company;
    //         }

    //         $this->data['cdb'] = base64_encode($user->database_name);
    //         $this->data['manager'] = $teamManagers;

    //         // --- Monthly stats ---


    //         return response()->json([
    //             'success' => true,
    //             'data' => @$this->data,
    //             'message' => "Scanner info listed successfully"
    //         ], 200);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $th->getMessage()
    //         ], 500);
    //     }
    // }
    public function scannerInfo()
    {
        try {
            $user = auth('sanctum')->user();
            $user_id = $user->id;

            // Initialize counts with default values
            $this->data = [
                'total_employees' => 0,
                'on_board' => 0,
                'resigned_employees' => 0,
                'terminated_employees' => 0,
                'absconded_employees' => 0,
                'suspanded_employees' => 0,
                'permissions' => null,
                'is_admin' => 0
            ];

            // --- Get role of the user ---
            $auth_role = DB::table('role_user')->where('user_id', $user_id)->first();
            $employeeRole = DB::table('roles')->find($auth_role->role_id);

            if (!$employeeRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not assigned to user.'
                ], 403);
            }

            $teamManagers = TeamManager::with(['employees', 'hrmsTeam'])->get();

            // --- Role logic check (admin or custom) ---
            if ($employeeRole->name != 'admin') {
                $role = HrmsEmployeeRole::where('employee_id', $user_id)->first();

                if (!$role) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Employee role not found.'
                    ], 403);
                }

                $authRolePermissions = HrmsRole::with(['viewrole', 'hrms_permissions'])
                    ->where('id', $role->role_id)
                    ->first();

                if (!$authRolePermissions) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No permissions assigned to this role.'
                    ], 403);
                }

                $this->data['permissions'] = $authRolePermissions;

                // 👇 Check if HRMS role name is "Manager"
                if (strtolower($authRolePermissions->name) === 'manager') {
                    foreach ($teamManagers as $manager) {
                        // Check if hrmsTeam exists and has members
                        if ($manager->hrmsTeam && $manager->hrmsTeam->members) {
                            $members = json_decode($manager->hrmsTeam->members, true);
                            if (is_array($members)) {
                                $this->data['total_employees'] = SubUser::whereIn('id', $members)
                                    ->where('status', 1)->where('employement_type', '!=', 'Driver')
                                    ->count();
                                $today = now();
                                $currentMonth = $today->format('Y-m');

                                $this->data['on_board'] = SubUser::whereIn('id', $members)->where('status', 1)->where('employement_type', '!=', 'Driver')
                                    ->where('doj', 'like', $currentMonth . '%')
                                    ->count();

                                $currentDate = now()->format('Y-m-d');

                                $resigned = SubUser::whereIn('id', $members)->where('status', 3)->where('employement_type', '!=', 'Driver')
                                    ->whereHas('statusUpdateReason', function ($query) use ($currentDate) {
                                        $query->whereDate('created_at', '<=', $currentDate)
                                            ->whereDate('last_working_date', '>=', $currentDate);
                                    })
                                    ->count();

                                $terminated = SubUser::whereIn('id', $members)->where('status', 6)->where('employement_type', '!=', 'Driver')
                                    ->whereHas('statusUpdateReason', function ($query) use ($currentMonth) {
                                        $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth]);
                                    })
                                    ->count();

                                $absconded = SubUser::whereIn('id', $members)->where('status', 8)->where('employement_type', '!=', 'Driver')
                                    ->whereHas('statusUpdateReason', function ($query) use ($currentMonth) {
                                        $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth]);
                                    })
                                    ->count();

                                $suspanded = SubUser::whereIn('id', $members)->where('status', 5)->where('employement_type', '!=', 'Driver')
                                    ->whereHas('statusUpdateReason', function ($query) use ($currentMonth) {
                                        $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth]);
                                    })
                                    ->count();

                                // Assign to response
                                $this->data['resigned_employees'] = $resigned;
                                $this->data['terminated_employees'] = $terminated;
                                $this->data['absconded_employees'] = $absconded;
                                $this->data['suspanded_employees'] = $suspanded;
                            }
                        }
                    }
                } else {
                    $this->data['total_employees'] = SubUser::where('status', 1)
                        ->where('employement_type', '!=', 'Driver')
                        ->count();
                }
            } else {
                $this->data['is_admin'] = 1;

                // For admin, show total employees
                $this->data['total_employees'] = SubUser::where('status', 1)
                    ->where('employement_type', '!=', 'Driver')
                    ->count();
                $today = now();
                $currentMonth = $today->format('Y-m');

                $this->data['on_board'] = SubUser::where('status', 1)->where('employement_type', '!=', 'Driver')
                    ->where('doj', 'like', $currentMonth . '%')->where('employement_type', '!=', 'Driver')
                    ->count();

                $currentDate = now()->format('Y-m-d');

                $resigned = SubUser::where('status', 3)->where('employement_type', '!=', 'Driver')
                    ->whereHas('statusUpdateReason', function ($query) use ($currentDate) {
                        $query->whereDate('created_at', '<=', $currentDate)
                            ->whereDate('last_working_date', '>=', $currentDate);
                    })
                    ->count();

                $terminated = SubUser::where('status', 6)->where('employement_type', '!=', 'Driver')
                    ->whereHas('statusUpdateReason', function ($query) use ($currentMonth) {
                        $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth]);
                    })
                    ->count();

                $absconded = SubUser::where('status', 8)->where('employement_type', '!=', 'Driver')
                    ->whereHas('statusUpdateReason', function ($query) use ($currentMonth) {
                        $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth]);
                    })
                    ->count();

                $suspanded = SubUser::where('status', 5)->where('employement_type', '!=', 'Driver')
                    ->whereHas('statusUpdateReason', function ($query) use ($currentMonth) {
                        $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth]);
                    })
                    ->count();

                // Assign to response
                $this->data['resigned_employees'] = $resigned;
                $this->data['terminated_employees'] = $terminated;
                $this->data['absconded_employees'] = $absconded;
                $this->data['suspanded_employees'] = $suspanded;
            }

            // --- Company Info ---
            $company = CompanyDetails::first();
            $companyDetails = DB::table('company_addresses')
                ->where('company_addresses.company_id', $company->id)
                ->whereNull('company_addresses.end_date')
                ->join('company_details', 'company_addresses.company_id', '=', 'company_details.id')
                ->select('company_details.id', 'company_details.name', 'company_details.logo', 'company_details.email', 'company_details.phone', 'company_addresses.address', 'company_addresses.latitude', 'company_addresses.longitude')
                ->first();

            if ($companyDetails) {
                @$companyDetails->logo_url = url('images');
                $this->data['companyDetails'] = $companyDetails;
            } else {
                @$company->logo_url = url('images');
                $this->data['companyDetails'] = $company;
            }

            $this->data['cdb'] = base64_encode($user->database_name);
            $this->data['manager'] = $teamManagers;

            return response()->json([
                'success' => true,
                'data' => $this->data,
                'message' => "Scanner info listed successfully"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }






    public function getCurrentSubscription($id)
    {
        // for current subscription
        $temp_DB_name = \DB::connection()->getDatabaseName();
        $default_DBName = env("DB_DATABASE");
        //connection with parent db
        $this->connectDB($default_DBName);
        $currentSubscription = UserSubscription::with('subscription')->where('user_id', $id)
            ->where('status', 1)
            ->first();
        if ($currentSubscription) {
            $features = $currentSubscription->subscription->features;
        }
        $this->connectDB($temp_DB_name);
        return $currentSubscription;
        //end connection with the parent db
    }




    //**************************** Update Company api ****************************** */

    /**
     * @OA\Post(
     * path="/uc/api/updateCompany",
     * operationId="updateCompanyInfo",
     * tags={"AccountSetup"},
     * summary="Update company info",
     *   security={ {"Bearer": {} }},
     * description="Update company info",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               @OA\Property(property="name", type="text"),
     *               @OA\Property(property="phone", type="text"),
     *               @OA\Property(property="country", type="text"),
     *               @OA\Property(property="file", type="file"),
     *               @OA\Property(property="social_link", type="text"),
     *               @OA\Property(property="city", type="text"),
     *               @OA\Property(property="postal_code", type="text"),
     *               @OA\Property(property="time_zone_status", type="text"),
     *               @OA\Property(property="language", type="text"),
     *               @OA\Property(property="date_formate", type="text"),
     *               @OA\Property(property="secondary_address", type="text"),
     *               @OA\Property(property="app_version", type="text"),
     *               @OA\Property(property="theme_option", type="text"),
     *               @OA\Property(property="api_setting", type="text"),
     *               @OA\Property(property="custom_field", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Company info updated successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Company info updated successfully",
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

    public function updateCompanyInfo(Request $request)
{
    try {
        $user = auth('sanctum')->user();

        if ($user && $user->hasRole('admin')) {
            $companyId = 1;
            $currentValues = DB::table('company_details')->where('id', $companyId)->first();
            $currentValues = $currentValues ? (array)$currentValues : [];

            // Define updatable fields
            $updatableFields = [
                'name', 'phone', 'country', 'address', 'social_link', 'city', 'postal_code',
                'time_zone_status', 'language', 'date_formate', 'secondary_address',
                'app_version', 'theme_option', 'api_setting', 'custom_field'
            ];

            $updates = [];

            foreach ($updatableFields as $field) {
                if ($request->has($field)) {
                    $newValue = $request->$field;
                    $oldValue = $currentValues[$field] ?? null;

                    // Only update if value has changed
                    if ($newValue != $oldValue) {
                        $this->trackCompanyInfoChange($field, $oldValue, $newValue);
                        $updates[$field] = $newValue;
                    }
                }
            }

            // Handle logo upload
            if ($request->hasFile('file')) {
                $image = $request->file('file');
                $path = public_path('images/');
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }

                $filename = time() . '.' . $image->getClientOriginalExtension();
                $image->move($path, $filename);

                $this->trackCompanyInfoChange('logo', $currentValues['logo'] ?? null, $filename, 'Company logo updated');
                $updates['logo'] = $filename;
            }

            if (!empty($updates)) {
                DB::table('company_details')->updateOrInsert(['id' => $companyId], $updates);
            }

            return response()->json(['success' => true, 'message' => 'Successfully updated'], 200);
        }

        return response()->json(['success' => false, 'message' => 'Unauthorised user'], 401);
    } catch (\Throwable $th) {
        return response()->json([
            'success' => false,
            'message' => $th->getMessage()
        ], 500);
    }
}


    /**
     * Log changes to company info in history table
     */
    private function trackCompanyInfoChange($field, $oldValue, $newValue, $note = null)
    {
        // Skip if values are the same
        if ($oldValue == $newValue) {
            return;
        }

        // Format field name for display
        $fieldName = ucfirst(str_replace('_', ' ', $field));

        // Special formatting for time_zone_status
        if ($field === 'time_zone_status') {
            $oldValue = $oldValue ? 'Enabled' : 'Disabled';
            $newValue = $newValue ? 'Enabled' : 'Disabled';
        }

        // Format null/empty values
        $oldValue = is_null($oldValue) ? 'empty' : $oldValue;
        $newValue = is_null($newValue) ? 'empty' : $newValue;

        $changedDescription = "$fieldName changed from '$oldValue' to '$newValue'";

        DB::table('update_system_setup_histories')->insert([
            'employee_id' => auth('sanctum')->user()->id,
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'updated_by' => auth('sanctum')->user()->id,
            'notes' => $note ?? 'Company info updated',
            'changed' => $changedDescription,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @OA\Get(
     * path="/uc/api/subscriptionPlan",
     * operationId="subscriptionPlans",
     * tags={"AccountSetup"},
     * summary="Subscription plans and active plan",
     *   security={ {"Bearer": {} }},
     * description="Subscription plans and active plan",
     *      @OA\Response(
     *          response=201,
     *          description="Subscription plans listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Subscription plans listed successfully",
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
    public function subscriptionPlans()
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {
                $temp_DB_name = \DB::connection()->getDatabaseName();
                $default_DBName = env("DB_DATABASE");
                $this->connectDB($default_DBName);
                $this->data['monthlySubscriptions'] = Subscription::with('features')->where('billing_cycle', 'monthly')->get();
                $this->data['yearlySubscriptions'] = Subscription::with('features')->where('billing_cycle', 'yearly')->get();
                $subscriptions_id = Subscription::get()->pluck('id');
                $this->data['currentSubscription'] = UserSubscription::with('subscription')->where('user_id', auth('sanctum')->user()->id)
                    ->whereIn('subscription_id', $subscriptions_id)
                    ->where('status', 1)
                    ->first();
                if ($this->data['currentSubscription']) {
                    $features = $this->data['currentSubscription']->subscription->features;
                }
                $this->connectDB($temp_DB_name);
                return response()->json([
                    'success' => true,
                    'data' => @$this->data,
                    'message' => "Subscription plans listed successfully"

                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "User is not admin"

                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //********************************** Upgrade subscription plan api ********************/

    /**
     * @OA\Post(
     * path="/uc/api/upgradePlan",
     * operationId="upgradePlans",
     * tags={"AccountSetup"},
     * summary="Upgrade subscription plan",
     *   security={ {"Bearer": {} }},
     * description="Upgrade subscription plan",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"subscription_id"},
     *               @OA\Property(property="subscription_id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Plan upgraded successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Plan upgraded successfully.",
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
    public function upgradePlans(Request $request)
    {
        $user = auth('sanctum')->user();
        try {
            $request->validate([
                'subscription_id' => 'required',

            ]);
            $temp_DB_name = \DB::connection()->getDatabaseName();
            $default_DBName = env("DB_DATABASE");
            $this->connectDB($default_DBName);
            $existing_subscription = UserSubscription::where('user_id', $user->id)->where('status', 1)->first();
            if ($existing_subscription) {

                $existing_subscription->status = 0;
                $existing_subscription->update();
            }
            $new_subscription = new UserSubscription();
            $new_subscription->user_id =  $user->id;
            $new_subscription->subscription_id = $request->subscription_id;
            $new_subscription->start_date = date('Y-m-d');
            $subscription = Subscription::where('id', $request->subscription_id)->first();
            if ($subscription->billing_cycle == 'monthly') {
                $new_subscription->end_date = now()->addMonth()->format('Y-m-d');
            } else if ($subscription->billing_cycle == 'yearly') {
                $new_subscription->end_date = now()->addYear()->format('Y-m-d');
            }
            $new_subscription->status = 1;
            $new_subscription->save();

            $this->connectDB($temp_DB_name);
            $this->data = $new_subscription;

            return response()->json([
                'status' => true,
                'data' => @$this->data,
                'message' => "Plan upgraded successfully"

            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    //*************************** Update company location *******************************/
    /**
     * @OA\Post(
     * path="/uc/api/updateCompanyLocation",
     * operationId="updateCompanyLocation",
     * tags={"AccountSetup"},
     * summary="Update company location",
     *   security={ {"Bearer": {} }},
     * description="Update company location",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"address", "latitude","longitude"},
     *               @OA\Property(property="address", type="text"),
     *               @OA\Property(property="latitude", type="text"),
     *               @OA\Property(property="longitude", type="text"),
     *
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Company address updated successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Company address updated successfully",
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
    public function updateCompanyLoction(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'address' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',

            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {
                $company = CompanyDetails::find(1);

                if ($company) {
                    $company_address = CompanyAddresse::whereNull('end_date')->first();
                    // Capture old values for history tracking
                    $oldAddress = $company_address ? $company_address->address : null;
                    $oldLatitude = $company_address ? $company_address->latitude : null;
                    $oldLongitude = $company_address ? $company_address->longitude : null;

                    if ($company_address) {
                        if ($company_address->start_date == date('Y-m-d')) {

                            $company_address->company_id = $company->id;
                            $company_address->address = $request->address;
                            $company_address->latitude = $request->latitude;
                            $company_address->longitude = $request->longitude;
                        } else {
                            $company_address->end_date = date('Y-m-d');
                            $company_details = new CompanyAddresse();

                            $company_details->company_id = $company->id;
                            $company_details->address = $request->address;
                            $company_details->latitude = $request->latitude;
                            $company_details->longitude = $request->longitude;
                            $company_details->start_date = date('Y-m-d');
                            $company_details->save();
                        }
                        $company_address->update();
                        // Track location update history
                        $changes = [];
                        if ($oldAddress !== $request->address) {
                            $changes[] = "Address changed from {$oldAddress} to {$request->address}";
                        }

                        if (!empty($changes)) {
                            DB::table('update_system_setup_histories')->insert([
                                'employee_id' => $user->id,
                                'date' => date('Y-m-d'),
                                'time' => date('H:i:s'),
                                'updated_by' => $user->id,
                                'notes' => 'Company location updated',
                                'changed' => implode("\n", $changes),
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    } else {

                        $company_details = new CompanyAddresse();

                        $company_details->company_id = $company->id;
                        $company_details->address = $request->address;
                        $company_details->latitude = $request->latitude;
                        $company_details->longitude = $request->longitude;
                        $company_details->start_date = date('Y-m-d');

                        $company_details->save();
                        // Track location update history
                        $changes = [];
                        if ($oldAddress !== $request->address) {
                            $changes[] = "Address changed from {$oldAddress} to {$request->address}";
                        }

                        DB::table('update_system_setup_histories')->insert([
                            'employee_id' => $user->id,
                            'date' => date('Y-m-d'),
                            'time' => date('H:i:s'),
                            'updated_by' => $user->id,
                            'notes' => 'Company location updated (previous location archived)',
                            'changed' => implode("\n", $changes),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
                $companyAddressExists = CompanyAddresse::exists() ? 1 : 0;
                return response()->json(['success' => true, 'companyAddressExists' => @$companyAddressExists, "message" => "Updated compnay location"], 200);
            } else {
                $companyAddressExists = CompanyAddresse::exists() ? 1 : 0;
                return response()->json(['success' => true, 'companyAddressExists' => @$companyAddressExists,  "message" => "Updated compnay location"], 200);
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
     * path="/uc/api/getReason",
     * operationId="reason",
     * tags={"AccountSetup"},
     * summary="List Reasons",
     *   security={ {"Bearer": {} }},
     * description="List Reasons",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"type"},
     *
     *                @OA\Property(property="type", type="text", description="0-Leave, 1-Complaint, 2-ShiftChange, 3-CancelRide, 4-RatingReason, 5-TempLocationChange, 6-RejectCandidate, 7-AcceptCandidate, 8-FutureReferenceCandidate"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Reasons listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Reasons listed successfully",
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
    public function reason(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'type' => 'required|integer|in:0,1,2,3,4,5,6,7,8,9'

            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && ($user->hasRole('admin') || $user->hasRole('carer'))) {

                $this->data['reasons'] = Reason::where('type', $request->type)->get();
                return response()->json(['success' => true, "data" => $this->data], 200);
            } else {
                return response()->json(['success' => false, "message" => "Unauthorised user"], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //************************ Add reason api******************************************* */

    /**
     * @OA\Post(
     * path="/uc/api/addReason",
     * operationId="addReason",
     * tags={"AccountSetup"},
     * summary="Add reason",
     *   security={ {"Bearer": {} }},
     * description="Add reason",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"reason", "type"},
     *                @OA\Property(property="reason", type="text"),
     *                @OA\Property(property="type", type="text", description="0-Leave, 1-Complaint, 2-ShiftChange, 3-CancelRide, 4-RatingReason, 5-TempLocationChange , 6-RejectEmployee, 7-AcceptEmployee, 8-FutureReferenceEmployee"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Reasons added successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Reasons added successfully",
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
    public function addReason(Request $request)
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {
                $validatedData = $request->validate([
                    'reason' => 'required',
                    'type' => 'required|integer|in:0,1,2,3,4,5,6,7,8,9'

                ]);
                $reason = new Reason();
                $reason->message = $request->reason;
                $reason->type = $request->type;
                $reason->save();

                // Record history
                $this->recordReasonAddition($reason->message, $reason->type);

                return response()->json(['success' => true, 'data' => @$reason, "message" => "Successfully added reason"], 200);
            } else {
                return response()->json(['success' => false, "message" => "Unauthorised user"], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    private function recordReasonAddition($reasonMessage, $reasonType)
    {
        $user = auth('sanctum')->user();
        if (!$user) return;

        $typeMapping = [
            0 => 'Leave Reasons',
            1 => 'Complaint Reasons',
            2 => 'ShiftChange Reasons',
            3 => 'CancelRide Reasons',
            4 => 'Rating Reason',
            5 => 'TempLocationChange Reasons',
            6 => 'Reject Reasons',
            7 => 'Accept Reasons',
            8 => 'FutureReference Reasons',
            9 => 'Reconsideration Reasons'
        ];

        $typeName = $typeMapping[$reasonType] ?? 'Unknown Type';

        DB::table('update_system_setup_histories')->insert([
            'employee_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'updated_by' => $user->id,
            'notes' => 'Reason added',
            'changed' => sprintf(
                "Added new reason\nType: %s\nMessage: %s",
                $typeName,
                $reasonMessage
            ),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    //***************************** Delete reason api *****************************8 */

    /**
     * @OA\Post(
     * path="/uc/api/deleteReason",
     * operationId="deleteReason",
     * tags={"AccountSetup"},
     * summary="Delete reason",
     *   security={ {"Bearer": {} }},
     * description="Delete reason",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *                @OA\Property(property="id"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Reason deleted successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Reason deleted successfully",
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
    public function deleteReason(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|integer',

            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {
                // Get reason details before deletion (added line)
                $reasonDetails = Reason::find($request->id);

                $reason = Reason::where('id', $request->id)->delete();
                if ($reason) {

                    // Record deletion history (added line)
                    if ($reasonDetails) {
                        $this->recordReasonDeletion($reasonDetails->message, $reasonDetails->type);
                    }

                    return response()->json(['success' => true, "message" => "Successfully deleted reason"], 200);
                } else {
                    return response()->json(['success' => false, "message" => "The given data is not found"], 404);
                }
            } else {
                return response()->json(['success' => false, "message" => "Unauthorised user"], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // Private function added
    private function recordReasonDeletion($reasonMessage, $reasonType)
    {
        $user = auth('sanctum')->user();
        if (!$user) return;

        $typeMapping = [
            0 => 'Type 0',
            1 => 'Type 1',
            2 => 'Type 2',
            3 => 'Type 3',
            4 => 'Type 4',
            5 => 'Type 5',
            6 => 'Type 6',
            7 => 'Type 7',
            8 => 'Type 8',
            9 => 'Type 9'
        ];

        $typeName = $typeMapping[$reasonType] ?? 'Unknown Type';

        DB::table('update_system_setup_histories')->insert([
            'employee_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'updated_by' => $user->id,
            'notes' => 'Reason deleted',
            'changed' => sprintf(
                "Deleted reason\nType: %s\nMessage: %s",
                $typeName,
                $reasonMessage
            ),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    //**************************** Update reason api******************************** */

    /**
     * @OA\Post(
     * path="/uc/api/updateReason",
     * operationId="updateReason",
     * tags={"AccountSetup"},
     * summary="Update reason",
     *   security={ {"Bearer": {} }},
     * description="Update reason",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id", "reason"},
     *                @OA\Property(property="id", type="text"),
     *                @OA\Property(property="reason", type="text"),
     *
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Reasons updated successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Reasons updated successfully",
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
    public function updateReason(Request $request)
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {
                $reason = Reason::find($request->id);
                if ($reason) {
                    $reason->message = $request->reason;
                    $reason->update();
                    return response()->json(['success' => true, 'data' => @$reason, "message" => "Successfully updated reason"], 200);
                } else {
                    return response()->json(['success' => false, "message" => "The given data is not found"], 404);
                }
            } else {
                return response()->json(['success' => false, "message" => "Unauthorised user"], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //**************************** Add faq api **************************************** */


    /**
     * @OA\Post(
     * path="/uc/api/addFaq",
     * operationId="addFaq",
     * tags={"AccountSetup"},
     * summary="Add FAQ",
     *   security={ {"Bearer": {} }},
     * description="Add FAQ",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"title", "description"},
     *                @OA\Property(property="title", type="text"),
     *                @OA\Property(property="description", type="text"),
     *
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="FAQ added successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="FAQ added successfully",
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
    public function addFaq(Request $request)
    {
        try {
            $validatedData = $request->validate([

                'title' => 'required',
                'description' => 'required',

            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {
                $faq = new Faq();
                $faq->question = $request->title;
                $faq->answer = $request->description;
                $faq->save();
                return response()->json(['success' => true, 'data' => @$faq, "message" => "Successfully added faq"], 200);
            } else {
                return response()->json(['success' => false, "message" => "Unauthorised user"], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //**************************** Update faq api************************************** */

    /**
     * @OA\Post(
     * path="/uc/api/updateFaq",
     * operationId="updateFaq",
     * tags={"AccountSetup"},
     * summary="Update FAQ",
     *   security={ {"Bearer": {} }},
     * description="Update FAQ",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id", "title", "description"},
     *                @OA\Property(property="id", type="text"),
     *                @OA\Property(property="title", type="text"),
     *                @OA\Property(property="description", type="text"),
     *
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="FAQ added successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="FAQ added successfully",
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
    public function updateFaq(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|integer',
                'title' => 'required',
                'description' => 'required',

            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {
                $faq = Faq::find($request->id);
                if ($faq) {
                    $faq->question = $request->title;
                    $faq->answer = $request->description;
                    $faq->update();
                }
                return response()->json(['success' => true, 'data' => @$faq, "message" => "Successfully updated faq"], 200);
            } else {
                return response()->json(['success' => false, "message" => "Unauthorised user"], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //************************************ Delete faq api******************************* */

    /**
     * @OA\Post(
     * path="/uc/api/deleteFaq",
     * operationId="deleteFaq",
     * tags={"AccountSetup"},
     * summary="Delete FAQ",
     *   security={ {"Bearer": {} }},
     * description="Delete FAQ",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *                @OA\Property(property="id", type="text"),
     *
     *
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="FAQ added successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="FAQ added successfully",
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
    public function deleteFaq(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|integer',

            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {
                $faq = Faq::where('id', $request->id)->delete();
                if ($faq) {
                    return response()->json(['success' => true,  "message" => "Successfully deleted FAQ"], 200);
                }
            } else {
                return response()->json(['success' => false, "message" => "Unauthorised user"], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //******************************* Add holiday api ***********************************/

    /**
     * @OA\Post(
     * path="/uc/api/addHoliday",
     * operationId="addHoliday",
     * tags={"AccountSetup"},
     * summary="Add Holiday",
     *   security={ {"Bearer": {} }},
     * description="Add Holiday",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"name", "date"},
     *
     *                @OA\Property(property="date", type="text"),
     *                @OA\Property(property="name", type="text"),
     *                @OA\Property(property="description", type="text"),
     *
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Holiday added successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Holiday added successfully",
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
    public function addHoliday(Request $request)
    {
        try {
            $validatedData = $request->validate([

                'name' => 'required',
                'date' => 'required|date|date_format:Y-m-d'
            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {

                $holiday = new Holiday();
                $holiday->date = $request->date;
                $holiday->name = $request->name;
                $holiday->description = $request->description;
                $holiday->save();

                // Add this ONE LINE to save history
                $this->saveHolidayHistory($holiday);

                DB::commit();

                return response()->json(['success' => true, 'data' => @$holiday, "message" => "Successfully added holiday"], 200);
            } else {
                return response()->json(['success' => false, "message" => "Unauthorised user"], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }

    }


    private function saveHolidayHistory($holiday)
{
    $user = auth('sanctum')->user();

    DB::table('update_system_setup_histories')->insert([
        'employee_id' => $user->id,
        'date' => date('Y-m-d'),
        'time' => date('H:i:s'),
        'updated_by' => $user->id,
        'notes' => 'Holiday added',
        'changed' => 'Added holiday: '.$holiday->name.' ('.$holiday->date.')',
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

    //********************************* Update holiday api ****************************** */


    /**
     * @OA\Post(
     * path="/uc/api/updateHoliday",
     * operationId="updateHoliday",
     * tags={"AccountSetup"},
     * summary="Update Holiday",
     *   security={ {"Bearer": {} }},
     * description="Update Holiday",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id", "name", "date"},
     *                @OA\Property(property="id", type="text"),
     *                @OA\Property(property="date", type="text"),
     *                @OA\Property(property="name", type="text"),
     *                @OA\Property(property="description", type="text"),
     *
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Holiday updated successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Holiday updated successfully",
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
    public function updateHoliday(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|integer',
                'name' => 'required',
                'date' => 'required|date|date_format:Y-m-d'
            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {
                $holiday = Holiday::find($request->id);
                if ($holiday) {
                    // Get original data before update
                    $originalData = [
                        'name' => $holiday->name,
                        'date' => $holiday->date,
                        'description' => $holiday->description
                    ];

                    $holiday->date = $request->date;
                    $holiday->name = $request->name;
                    $holiday->description = $request->description;
                    $holiday->update();

                    // Save update history
                    $this->saveHolidayUpdateHistory($holiday, $originalData);
                    return response()->json([
                    'success' => true,
                    'data' => $holiday,
                    'message' => "Successfully updated holiday"
                    ], 200);
                }
                return response()->json(['success' => true, 'data' => @$holiday, "message" => "Successfully updated holiday"], 200);
            } else {
                return response()->json(['success' => false, "message" => "Unauthorised user"], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    private function saveHolidayUpdateHistory($holiday, $originalData)
    {
        $changes = [];
        $user = auth('sanctum')->user();

        // Check each field for changes
        foreach (['name', 'date', 'description'] as $field) {
            if ($originalData[$field] != $holiday->$field) {
                $changes[] = ucfirst($field)." changed from '".$originalData[$field]."' to '".$holiday->$field."'";
            }
        }

        // Only save if there were changes
        if (!empty($changes)) {
            DB::table('update_system_setup_histories')->insert([
                'employee_id' => $user->id,
                'date' => date('Y-m-d'),
                'time' => date('H:i:s'),
                'updated_by' => $user->id,
                'notes' => 'Holiday updated',
                'changed' => implode(', ', $changes),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * @OA\Post(
     * path="/uc/api/addPrice",
     * operationId="addPrice",
     * tags={"AccountSetup"},
     * summary="Add Price",
     *   security={ {"Bearer": {} }},
     * description="Add Price",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"name"},
     *               @OA\Property(property="name", type="text"),
     *               @OA\Property(property="external_id", type="text"),
     *               @OA\Property(property="fixed_price", type="text", example="1"),
     *               @OA\Property(property="provider_travel", type="text",example="0"),
     *               @OA\Property(property="address", type="string"),
     *               @OA\Property(property="locality", type="string"),
     *               @OA\Property(property="latitude", type="string"),
     *               @OA\Property(property="longitude", type="string"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="You have sucessfully addedd",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="You have sucessfully addedd",
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


    public function addPrice(Request $request)
    {
        try {

            $validatedData = $request->validate([
                "name" => "required|unique:price_books",
                "address" => "nullable",
                "locality" => "nullable",
                "latitude" => "nullable",
                "longitude" => "nullable",
            ]);

            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {
                $store = new PriceBook();
                $store->name = $request->name;
                $store->external_id = $request->external_id;
                $store->fixed_price = 0;
                $store->provider_travel = 0;
                $store->address = @$validatedData['address'];
                $store->locality = @$validatedData['locality'];
                $store->latitude = @$validatedData['latitude'];
                $store->longitude = @$validatedData['longitude'];
                if (isset($request->fixed_price)) {
                    $store->fixed_price = 1;
                }

                if (isset($request->provider_travel)) {
                    $store->provider_travel = 1;
                }

                $store->save();
                return response()->json([
                    'success' => true,
                    'data' => @$store,
                    'message' => 'You have successfully added.'

                ], 200);
            }
            return response()->json(['success' => false, 'message' => 'Unauthorised user']);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/uc/api/editPrice",
     * operationId="editPrice",
     * tags={"AccountSetup"},
     * summary="Edit Price",
     *   security={ {"Bearer": {} }},
     * description="Edit Price",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id","name"},
     *               @OA\Property(property="id", type="text"),
     *               @OA\Property(property="name", type="text"),
     *                @OA\Property(property="external_id", type="text"),
     *               @OA\Property(property="fixed_price", type="text", example="1"),
     *               @OA\Property(property="provider_travel", type="text",example="0"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="You have sucessfully updated",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="You have sucessfully updated",
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


    public function editPrice(Request $request)
    {
        try {

            $validatedData = $request->validate([
                "id" => "required",
                "name" => "required|unique:price_books,name," . $request->id

            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {
                $store = PriceBook::where('id', $request->id)->first();
                $store->name = $request->name;
                $store->external_id = $request->external_id;
                $store->fixed_price = isset($request->fixed_price) ? 1 : 0;
                $store->provider_travel = isset($request->provider_travel) ? 1 : 0;

                $store->save();


                return response()->json([
                    'success' => true,
                    'data' => @$store,
                    'message' => 'You have successfully updated.'

                ], 200);
            }
            return response()->json(['success' => false, 'message' => 'Unauthorised user']);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/uc/api/deletePrice",
     * operationId="deletePrice",
     * tags={"AccountSetup"},
     * summary="Delete Price",
     *   security={ {"Bearer": {} }},
     * description="Delete Price",
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
     *          description="Report heading deleted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Report heading deleted successfully.",
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

    public function deletePrice(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|string|max:255',


            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {
                $price = PriceBook::where('id', $request->id)->first();
                $isAssociated = Schedule::where('pricebook_id', $price->id)->exists();
                if ($isAssociated) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Price table data cannot be deleted as it is associated with a schedule.'
                    ], 400);
                }

                $price->delete();

                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Report heading deleted successfully.',
                ], 200);
            }
            return response()->json(['success' => false, 'message' => 'Unauthorised user']);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/uc/api/addPriceBook",
     * operationId="addPriceBook",
     * tags={"AccountSetup"},
     * summary="Add Price Data",
     *   security={ {"Bearer": {} }},
     * description="Add Price Data",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"price_book_id","day_of_week","start_date","end_date","per_ride"},
     *               @OA\Property(property="price_book_id", type="text"),
     *               @OA\Property(property="day_of_week", type="text"),
     *               @OA\Property(property="start_date", type="start_date"),
     *               @OA\Property(property="end_date", type="end_date"),
     *               @OA\Property(property="per_ride", type="text",example="0"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="You have sucessfully addedd",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="You have sucessfully addedd",
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

    public function addPriceBook(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'day_of_week' => 'required',
                // 'start_time' => 'required',
                // 'end_time' => 'required',
                // 'per_hour' => 'required',
                'per_ride' => 'required',
                //'start_date' => 'required',
                //'end_date' => 'required',
                //'per_km' => 'required',
                // 'multiplier' => 'required',
                // 'day_of_week' => 'required|unique:price_table_data,day_of_week,NULL,id,price_book_id,' . $request->price_book_id,



            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {

                $existingEntries = PriceTableData::where('price_book_id', $request->price_book_id)
                    //->where('start_date', '<=', date('Y-m-d', strtotime($request->end_date)))
                    // ->where('end_date', '>=', date('Y-m-d', strtotime($request->start_date)))
                    ->where('day_of_week', $request->day_of_week)
                    ->exists();

                if ($existingEntries) {
                    return response()->json([
                        'status' => false,
                        'message' => 'There is already an existing entry between the specified start and end dates for the day of the week.',
                    ], 500);
                }


                //     $store = new PriceTableData();
                //     $store->price_book_id = $request->price_book_id;
                //     $store->day_of_week = $request->day_of_week;
                //     // $store->start_time = $request->start_time;
                //     // $store->end_time = $request->end_time;
                //     //$store->start_date = date('Y-m-d', strtotime($request->start_date));
                //    // $store->end_date = date('Y-m-d', strtotime($request->end_date));
                //     // $store->per_hour = $request->per_hour;
                //     $store->per_ride = $request->per_ride;
                //     //$store->refrence_no_hr = $request->refrence_no_hr;
                //     // $store->per_km = $request->per_km;
                //     // $store->refrence_no = $request->refrence_no;
                //     // $store->effective_date = $request->effective_date;
                //     // $store->multiplier = $request->multiplier;
                //     $store->save();


                $cehckUnivarsal = "";
                if ($request->day_of_week == 'all') {
                    $cehckUnivarsal = $request->day_of_week;
                    $request->merge(['day_of_week' => 'Weekdays (mon- fri)']);
                }

                if (!empty($cehckUnivarsal) && $cehckUnivarsal == 'all') {
                    $daysOfWeek = ['Weekdays (mon- fri)', 'saturday', 'sunday', 'Public Holiday'];
                    foreach ($daysOfWeek as $day) {
                        $store = new PriceTableData();
                        $store->price_book_id = $request->price_book_id;
                        $store->day_of_week = $day;
                        $store->per_ride = $request->per_ride;
                        $store->start_date = $request->start_date;
                        $store->end_date = $request->end_date;
                        $store->save();
                    }
                    return response()->json([
                        'status' => true,
                        'message' => 'You have successfully added for all days.',
                    ], 200);
                }

                $store = new PriceTableData();
                $store->price_book_id = $request->price_book_id;
                $store->day_of_week = $request->day_of_week;
                $store->per_ride = $request->per_ride;
                $store->start_date = $request->start_date;
                $store->end_date = $request->end_date;
                $store->save();

                return response()->json([
                    'success' => true,
                    'data' => @$store,
                    'message' => 'You have successfully added.',
                ], 200);
            }
            return response()->json(['success' => false, 'message' => 'Unauthorised user']);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    /**
     * @OA\Post(
     * path="/uc/api/updatePriceBook",
     * operationId="updatePriceBook",
     * tags={"AccountSetup"},
     * summary="Update Price Data",
     *   security={ {"Bearer": {} }},
     * description="Update Price Data",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id","price_book_id","day_of_week","start_date","end_date","per_ride"},
     *               @OA\Property(property="id", type="text"),
     *               @OA\Property(property="price_book_id", type="text"),
     *               @OA\Property(property="day_of_week", type="text"),
     *               @OA\Property(property="start_date", type="date"),
     *               @OA\Property(property="end_date", type="date"),
     *               @OA\Property(property="per_ride", type="text",example="0"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="You have sucessfully updated",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="You have sucessfully updated",
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

    public function updatePriceBook(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required',
                'day_of_week' => 'required',
                // 'start_time' => 'required',
                // 'end_time' => 'required',
                'start_date' => 'required',
                'end_date' => 'required',
                'per_ride' => 'required',
                "day_of_week" => "required"
                //"day_of_week" => "required|unique:price_table_data,day_of_week,".$request->price_book_id


            ]);


            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {

                $update = PriceTableData::where('id', $request->id)->first();
                $existingEntries = PriceTableData::where('id', '!=', $request->id)->where('price_book_id', $update->price_book_id)
                    ->where('start_date', '<=', date('Y-m-d', strtotime($request->end_date)))
                    ->where('end_date', '>=', date('Y-m-d', strtotime($request->start_date)))
                    ->where('day_of_week', $request->day_of_week)
                    ->exists();

                if ($existingEntries) {
                    return response()->json([
                        'status' => false,
                        'message' => 'There is already an existing entry between the specified start and end dates for the day of the week.',
                    ], 500);
                }
                $update->price_book_id = $request->price_book_id;
                $update->day_of_week = $request->day_of_week;
                $update->start_time = $request->start_time;
                $update->end_time = $request->end_time;
                $update->start_date = date('Y-m-d', strtotime($request->start_date));
                $update->end_date = date('Y-m-d', strtotime($request->end_date));
                // $update->per_hour = $request->per_hour;
                $update->per_ride = $request->per_ride;
                //$store->refrence_no_hr = $request->refrence_no_hr;
                // $update->per_km = $request->per_km;
                // $store->refrence_no = $request->refrence_no;
                // $store->effective_date = $request->effective_date;
                // $update->multiplier = $request->multiplier;
                $update->save();

                return response()->json([
                    'success' => true,
                    'data' => @$update,
                    'message' => 'You have successfully added.',
                ], 200);
            }
            return response()->json(['success' => false, 'message' => 'Unauthorised user']);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/uc/api/deletePriceBook",
     * operationId="deletePriceBook",
     * tags={"AccountSetup"},
     * summary="Delete Price Book Data",
     *   security={ {"Bearer": {} }},
     * description="Delete Price Book Data",
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
     *          description="Report heading deleted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Report heading deleted successfully.",
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

    public function deletePriceBook(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|string|max:255',

            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {


                $price = PriceTableData::where('id', $request->id)->first();

                $isAssociated = Schedule::where('pricebook_id', $price->price_book_id)->exists();
                if ($isAssociated) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Price table data cannot be deleted as it is associated with a schedule.'
                    ], 400);
                } elseif ($price->name == 'DEFAULT') {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can not delete default pricebook.'
                    ], 400);
                }

                $price->delete();

                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'You have successfully deleted.',
                ], 200);
            }
            return response()->json(['success' => false, 'message' => 'Unauthorised user']);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //*************************** List price and pricebookdata ***********************/

    /**
     * @OA\Post(
     * path="/uc/api/listPriceAndPriceBookdata",
     * operationId="listPriceAndPriceBookdata",
     * tags={"AccountSetup"},
     * summary="Get Price and price book data",
     *   security={ {"Bearer": {} }},
     * description="Get Price and price book data",
     *      @OA\Response(
     *          response=201,
     *          description="List State data",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="List State data",
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

    public function listPriceAndPriceBookdata(Request $request)
    {
        //echo '<pre>';print_r(\DB::connection()->getDatabaseName());die;
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {
                $pricebooks = PriceBook::orderBy('id', 'DESC')->with('priceBookData')->get();

                return response()->json([
                    'success' => true,
                    'data' => @$pricebooks,
                    'message' => 'Get Price and price book data'

                ], 200);
            }
            return response()->json(['success' => false, 'message' => 'Unauthorised user']);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    /**
     * @OA\Post(
     * path="/uc/api/listDrivers",
     * operationId="listDriver",
     * tags={"Ucruise Driver"},
     * summary="List drivers",
     *   security={ {"Bearer": {} }},
     * description="List drivers",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"page", "archive_unarchive"},
     *               @OA\Property(property="page", type="text"),
     *               @OA\Property(property="archive_unarchive", type="text", description="0:unarchived_driver, 1:archived_driver"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Drivers listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Drivers listed successfully",
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


    //**************************** Driver listing api ******************************* */

    public function listDriver(Request $request)
    {
        //echo '<pre>';print_r(\DB::connection()->getDatabaseName());die;
        try {
            $request->validate([
                'page' => 'required',
                'archive_unarchive' => 'nullable|in:0,1',
                'status' => 'nullable|in:1,2'  // Add status validation

            ]);
            $user = auth('sanctum')->user()->id;
            if ($user) {

                $d = date('Y-m-d');
                $perPage = $request->input('items'); // Default to 6 items per page
                $currentPage = $request->input('page', 1); // Default to page 1
                // if ($request->archive_unarchive == 1) {
                //     $role = 'archived_driver';
                // } else {
                //     $role = 'driver';
                // }
                $drivers = SubUser::with('vehicle')->with('pricebook')
                    // ->whereHas("roles", function ($q) use ($role) {
                    // $q->where("name", $role);
               // })
                    ->whereHas("roles", function ($q) {
                        $q->where("name", "driver");
                    })
                    ->where('close_account', 1)
                    ->where('status', $request->status ?? 1)
                    ->leftJoin('sub_user_addresses', function ($join) use ($d) {
                        $join->on('sub_users.id', '=', 'sub_user_addresses.sub_user_id')
                            ->whereDate('sub_user_addresses.start_date', '<=', $d)
                            ->where(function ($query) use ($d) {
                                $query->whereDate('sub_user_addresses.end_date', '>', $d)
                                    ->orWhereNull('sub_user_addresses.end_date');
                            });
                    })
                    ->orderBy("sub_users.id", "DESC")
                    ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address')
                    ->paginate($perPage, ['*'], 'page', $currentPage);
                foreach ($drivers as $driver) {
                    $driver->monthly_stats = @$this->getMonthlyStats($driver['id']);
                }

                return response()->json([
                    'success' => true,
                    'data' => $drivers,
                    'driver_image_url' => url('images'),
                    'vehicle_image_url' => url('public/images/vehicles'),
                    'message' => 'Drivers listed successfully'

                ], 200);
            }
            return response()->json(['success' => false, 'message' => 'Unauthorised user']);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //******************************* Employees Listing api **************************** */
    /**
     * @OA\Post(
     * path="/uc/api/listStaffs",
     * operationId="listStaff",
     * tags={"Ucruise Employee"},
     * summary="List staff",
     *   security={ {"Bearer": {} }},
     * description="List staff",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"page","archive_unarchive"},
     *               @OA\Property(property="page", type="text"),
     *               @OA\Property(property="archive_unarchive", type="text", description="0:unarchived_staff, 1:archived_staff"),
     *               @OA\Property(property="status", type="text", description="0:inactive, 1:active, 3:Resigned, 4:On Notice Period, 5:Suspended, 6:Terminated, 7:Deceased , 8:abscond"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="staff listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Staff listed successfully",
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

    public function listStaff(Request $request)
    {
        try {
            $request->validate([
                'page' => 'required',
                //'archive_unarchive' => 'required|in:0,1',
                //'having_cab' => 'required|in:0,1'
            ]);

            $user = auth('sanctum')->user()->id;

            if ($user) {
                $d = date('Y-m-d');
                $perPage = $request->input('items'); // Default to 6 items per page
                $currentPage = $request->input('page', 1);

                if ($request->status == "15") {
                    $role = 'archived_staff';
                } else {
                    $role = 'carer';
                }

                // Start the query
                $query = SubUser::whereHas("roles", function ($q) use ($role) {
                    $q->where("name", $role);
                })
                    ->where('close_account', 1)
                    ->where('user_type', '0')
                    ->leftJoin('sub_user_addresses', function ($join) use ($d) {
                        $join->on('sub_users.id', '=', 'sub_user_addresses.sub_user_id')
                            ->whereDate('sub_user_addresses.start_date', '<=', $d)
                            ->where(function ($query) use ($d) {
                                $query->whereDate('sub_user_addresses.end_date', '>', $d)
                                    ->orWhereNull('sub_user_addresses.end_date');
                            });
                    })
                    ->orderBy("sub_users.id", "DESC")
                    ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address');

                // If 'having_cab' is provided in the request, filter based on latitude and longitude
                if ($request->has('having_cab')) {
                    if ($request->having_cab == "0") {
                        // Only users without latitude and longitude
                            $query->where('cab_facility',0);
                            $query->where(function ($query) {
                            $query->whereNull('sub_user_addresses.latitude')
                                ->orWhereNotNull('sub_user_addresses.latitude');
                            $query->whereNull('sub_user_addresses.longitude')
                                ->orWhereNotNull('sub_user_addresses.longitude');
                        });

                    } elseif ($request->having_cab == "1") {
                        // Only users with latitude and longitude
                        $query->whereNotNull('sub_user_addresses.latitude')
                            ->whereNotNull('sub_user_addresses.longitude')
                            ->where('cab_facility',1);
                    }
                }else {
                    // No filtering on latitude and longitude, return all records
                    $query->where(function ($query) {
                        $query->whereNull('sub_user_addresses.latitude')
                            ->orWhereNotNull('sub_user_addresses.latitude');
                        $query->whereNull('sub_user_addresses.longitude')
                            ->orWhereNotNull('sub_user_addresses.longitude');
                    });
                }

                // filter with status
                if ($request->has('status')) {
                    $status = trim($request->status);
                    if ($status !== 'all' && $status !== '' && (int)$status !== 15) {
                        $query->where('sub_users.status', (int)$status);
                    }
                }

                // Get the paginated result
                $this->data['employees'] = $query->paginate($perPage, ['*'], 'page', $currentPage);

                return response()->json([
                    'success' => true,
                    'data' => $this->data,
                    'employee_image_url' => url('images'),
                    'message' => 'Employees listed successfully'
                ], 200);
            }
            return response()->json(['success' => false, 'message' => 'Unauthorised user']);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'line' => $th->getLine(),
                'file' => $th->getFile(),
                'message' => $th->getMessage()
            ], 500);
        }
    }


    /**
     * @OA\Post(
     * path="/uc/api/downloadSampleExcelfile",
     * operationId="downloadSampleExcelfile",
     * tags={"Ucruise Employee"},
     * summary="Sample file List ",
     *   security={ {"Bearer": {} }},
     * description="Hrms List staff",
     *      @OA\Response(
     *          response=201,
     *          description="staff listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Staff listed successfully",
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
    public function downloadSampleExcelfile(Request $request){

        try {

        $files = File::files(public_path('assets/excel'));
        $driver_url = null;
        $employee_url = null;
        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (str_contains($filename, 'driver')) {
                $driver_url = asset('assets/excel/' . $filename);
            } elseif (str_contains($filename, 'employee')) {
                $employee_url = asset('assets/excel/' . $filename);
            }
        }
        $this->data['driver_url'] = $driver_url;
        $this->data['employee_url'] = $employee_url;

        return response()->json([
            'success' => true,
            'data' => $this->data,
            'message' => 'Download excel files'
        ], 200);

        } catch (\Throwable $th) {
             return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    /**
     * @OA\Post(
     * path="/uc/api/hrmslistStaffs",
     * operationId="hrmslistStaffs",
     * tags={"Ucruise Employee"},
     * summary="Hrms List staff",
     *   security={ {"Bearer": {} }},
     * description="Hrms List staff",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"page","archive_unarchive"},
     *               @OA\Property(property="page", type="text"),
     *               @OA\Property(property="archive_unarchive", type="text", description="0:unarchived_staff, 1:archived_staff"),
     *               @OA\Property(property="status", type="text", description="0:inactive, 1:active, 3:Resigned, 4:On Notice Period, 5:Suspended, 6:Terminated, 7:Deceased , 8:abscond"),
     *               @OA\Property(property="search", type="text"),
     *               @OA\Property(property="user_department", type="text"),
     *              @OA\Property(property="doj_from", type="date"),
     *              @OA\Property(property="doj_to", type="date"),
     *               @OA\Property(property="user_type", type="integer", description="0 => Normal User, 1 => Office User, null => all User"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="staff listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Staff listed successfully",
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


    public function hrmslistStaffs(Request $request)
    {


        try {
            $request->validate([
                'page' => 'required',
                'archive_unarchive' => 'required|in:0,1',
                'status' => 'nullable|integer|in:1,2,3,4,5,6,7,8,10,11,12',
                'user_type' => 'nullable|in:0,1',
                'created_from' => 'nullable|date',
                'created_to' => 'nullable|date',
                'doj_from' => 'nullable|date',
                'doj_to' => 'nullable|date'
            ]);

            $user = auth('sanctum')->user()->id;

            $admin = 0;
            $is_manager = 0;
            $user_id = $user;
            $user_type = $request->user_type;

            // if view role admin view
            $getTeamMembers = SubUser::with('hrmsroles.viewrole')->find($user_id);

            $role_view = "";
            if ($getTeamMembers) {
                $role_view = @$getTeamMembers->hrmsroles[0]->viewrole->name;
                if ($role_view == 'Admin View') {
                    $admin = 1;
                } elseif ($role_view == 'Manager View') {   // manager view
                    $is_manager = 1;
                    $manager_id = $user;
                }
            }

            $auth_role = DB::table('role_user')->where('user_id', $user_id)->first();
            $employeeRole = DB::table('roles')->find($auth_role->role_id);
            if ($employeeRole->name == "admin") {
                $admin = 1;
            }

            $userIds = [];
            if ($admin == 0) {
                $is_manager = 1;

                if (!empty($role_view)) {
                    $getManagerList = $getManagerList = TeamManager::with(['employees', 'teams.teamLeader', 'teams.teamMembers.user'])
                        ->whereHas('employees', function ($q) use ($user_id) {
                            $q->where('sub_users.id', $user_id);
                        })
                        ->get();
                } else {
                    $getManagerList = TeamManager::with(['employees', 'teams.teamLeader', 'teams.teamMembers.user'])->get();
                }

                foreach ($getManagerList as $key => $manager) {
                    foreach ($manager->teams as $key => $team) {
                        $userIds = json_decode($team->members, true);
                        if ($team->team_leader == $user_id) {
                            $manager_id = $manager->employees[0]->id;
                            $userIds = json_decode($team->members, true);
                            break 2;
                        } else {
                            foreach ($team->teamMembers as $key => $member) {
                                if ($member->user->id == $user_id) {
                                    $manager_id = $manager->employees[0]->id;
                                    $userIds = json_decode($team->members, true);
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }

            if ($user) {
                $d = date('Y-m-d');
                $perPage = $request->input('items'); // Default to 6 items per page
                $currentPage = $request->input('page', 1);

                if ($request->archive_unarchive == 1) {
                    $role = 'archived_staff';
                } else {
                    $role = 'carer';
                }

                // Start the query
                $status = $request->status;
                $managerId = $request->manager_id ?? null;


                $query = SubUser::query();
                if (!is_null($managerId)) {
                    $query->whereHas('employeesUnderOfManagerRelation', function ($q) use ($managerId) {
                        $q->where('manager_id', $managerId);
                    });
                }
                ///whereHas('employeesUnderOfManagerRelation') // <-- relation name in User model
                $query->with(['UserInfo', 'statusUpdateReason'])
                    ->whereHas("roles", function ($q) use ($role) {
                        $q->where("name", $role);
                    })
                    ->filterBySearch($request->search)
                    ->filterByDepartment($request->user_department)
                    ->where('close_account', 1)
                    //->where('user_type', '0')
                    ->when(!is_null($user_type), function ($q) use ($user_type) {
                        $q->where('user_type', $user_type);
                    })
                    ->when(!empty($userIds), function ($q) use ($userIds) {
                        $q->whereIn('sub_users.id', $userIds);
                    })
                    // Add date range filters for date_of_joining (DOJ)
                    ->when($request->filled('doj_from'), function ($q) use ($request) {
                        $q->whereDate('sub_users.doj', '>=', $request->doj_from);
                    })
                    ->when($request->filled('doj_to'), function ($q) use ($request) {
                        $q->whereDate('sub_users.doj', '<=', $request->doj_to);
                    });


                    // Card received filter
                   if ($request->filled('card')) {
                        $card = $request->input('card');

                        // Card Received (exact match)
                        if ($card == 10) {
                            $query->whereHas('userInfo', function ($q) {
                                $q->where('id_card_receive', 'Received');
                            });
                        }

                        // Card NOT Received or user_info missing
                        if ($card == 11) {
                            $query->where(function ($q) {
                                $q->whereDoesntHave('userInfo')
                                ->orWhereHas('userInfo', function ($q) {
                                    $q->where('id_card_receive', '!=', 'Received')
                                        ->orWhereNull('id_card_receive');
                                });
                            });
                        }
                    }

                    // No show users
                    if($request->input('show')) {
                        if($request->input('show') ==12) {
                            $query->where('no_show', 'Yes');
                        }else{
                            $query->where('no_show', 'No');
                        }
                    }

                    if ($request->has('status')) {
                        $query->where('status', $status);
                    }


                $query->leftJoin('sub_user_addresses', function ($join) use ($d) {
                    $join->on('sub_users.id', '=', 'sub_user_addresses.sub_user_id')
                        ->whereDate('sub_user_addresses.start_date', '<=', $d)
                        ->where(function ($query) use ($d) {
                            $query->whereDate('sub_user_addresses.end_date', '>', $d)
                                ->orWhereNull('sub_user_addresses.end_date');
                        });
                })
                    ->orderBy("sub_users.id", "DESC")
                    ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address');



                // If 'having_cab' is provided in the request, filter based on latitude and longitude
                if ($request->has('having_cab')) {
                    if ($request->having_cab == 0) {
                        // Only users without latitude and longitude
                        $query->whereNull('sub_user_addresses.latitude')
                            ->whereNull('sub_user_addresses.longitude');
                    } elseif ($request->having_cab == 1) {
                        // Only users with latitude and longitude
                        $query->whereNotNull('sub_user_addresses.latitude')
                            ->whereNotNull('sub_user_addresses.longitude');
                    }
                }
                // If 'having_cab' is not provided, return both users with and without latitude and longitude
                else {
                    // No filtering on latitude and longitude, return all records
                    $query->where(function ($query) {
                        $query->whereNull('sub_user_addresses.latitude')
                            ->orWhereNotNull('sub_user_addresses.latitude');
                        $query->whereNull('sub_user_addresses.longitude')
                            ->orWhereNotNull('sub_user_addresses.longitude');
                    });
                }

                // Get the paginated result
                //   $this->data['employees'] = $query->paginate($perPage, ['*'], 'page', $currentPage);

                //     return response()->json([
                //         'success' => true,
                //         'data' => $this->data,
                //         'employee_image_url' => url('public/images'),
                //         'message' => 'Employees listed successfully'
                //     ], 200);
                // }
                $this->data['employees'] = $query->paginate(Subuser::PAGINATE);

                // Transform the results to include ID card status
                $transformedEmployees = $this->data['employees']->getCollection()->map(function ($employee) {
                    $idCardStatus = 'Not Received';
                    if ($employee->UserInfo && $employee->UserInfo->id_card_receive === 'Received') {
                        $idCardStatus = 'Received';
                    }

                    $employee->id_card_status = $idCardStatus;
                    return $employee;
                });

                // Replace the collection in the paginator
                $this->data['employees']->setCollection($transformedEmployees);

                return response()->json([
                    'success' => true,
                    'data' => $this->data,
                    'employee_image_url' => url('public/images'),

                    'message' => 'Employees listed successfully'
                ], 200);
            }

            return response()->json(['success' => false, 'message' => 'Unauthorised user']);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'line' => $th->getLine(),
                'file' => $th->getFile(),
                'message' => $th->getMessage()
            ], 500);
        }
    }



    //*********************************** Get shiftTypes api ***************************** */

    /**
     * @OA\Get(
     * path="/uc/api/scheduleTypes",
     * operationId="shiftTypes",
     * tags={"AccountSetup"},
     * summary="Schedule types",
     *   security={ {"Bearer": {} }},
     * description="Schedule types",
     *      @OA\Response(
     *          response=201,
     *          description="Schedule types listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Schedule types listed successfully",
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

    public function shiftTypes()
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user->hasRole('admin')) {
                $this->data['scheduleTypes'] = ShiftTypes::get();
                return response()->json([
                    'success' => true,
                    'data' => @$this->data,
                    'message' => 'Schedule types listed successfully.',
                ], 200);
            }
            return response()->json(['success' => false, 'message' => 'Unauthorised user']);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    /**
     * @OA\Get(
     * path="/uc/api/documentType",
     * operationId="documentType",
     * tags={"AccountSetup"},
     * summary="Document types",
     *   security={ {"Bearer": {} }},
     * description="Document type",
     *      @OA\Response(
     *          response=201,
     *          description="Document types listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Document types listed successfully",
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

    public function documentType()
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {
                $this->data['documentTypes'] = DocCategory::get();
                return response()->json([
                    'success' => true,
                    'data' => @$this->data,
                    'message' => 'Schedule types listed successfully.',
                ], 200);
            }
            return response()->json(['success' => false, 'message' => 'Unauthorised user']);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //*********************************** Add document type api ********************** */


    /**
     * @OA\Post(
     * path="/uc/api/addDocumentType",
     * operationId="addDocumentType",
     * tags={"AccountSetup"},
     * summary="Add document type",
     *   security={ {"Bearer": {} }},
     * description="Add document type",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"category_name"},
     *               @OA\Property(property="category_name", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Document type added successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Document type added successfully.",
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
    public function addDocumentType(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'category_name' => 'required',
            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {


                $documentType = new DocCategory();
                $documentType->category_name = $request->category_name;
                $documentType->save();
                return response()->json([
                    'success' => true,
                    'data' => @$documentType,
                    'message' => 'Document type added successfully',
                ], 200);
            }
            return response()->json(['success' => false, 'message' => 'Unauthorised user']);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //*********************************** Delete document type api ********************** */


    /**
     * @OA\Post(
     * path="/uc/api/deleteDocumentType",
     * operationId="deleteDocumentType",
     * tags={"AccountSetup"},
     * summary="Delete document type",
     *   security={ {"Bearer": {} }},
     * description="Delete document type",
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
     *          description="Document type deleted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Document type deleted successfully.",
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
    public function deleteDocumentType(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|integer',
            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user) {
                $documentType = DocCategory::where('id', $request->id)->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Document type deleted successfully',
                ], 200);
            }
            return response()->json(['success' => false, 'message' => 'Unauthorised user']);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getAnnouncement()
    {
        try {


            $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');
            $holidays = Holiday::where('date', '>=', $startOfMonth)->get();
            $reminders = Reminder::where('date', '>=', $startOfMonth)->get();

            $announcement = $holidays->concat($reminders);
            $announcement = $announcement->sortByDesc('date')->values()->all();

            return $announcement;
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/uc/api/rideSetting",
     *     operationId="rideSettings",
     *     tags={"AccountSetup"},
     *     summary="Update ride setting",
     *     security={{"Bearer": {}}},
     *     description="Update ride setting",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"female_employee_security", "cancel_timer", "noshow_frequency", "noshow_count", "leave_timer"},
     *               @OA\Property(property="female_employee_security", type="text", description="1 for true, 0 for false"),
     *               @OA\Property(property="cancel_timer", type="text"),
     *               @OA\Property(property="noshow_frequency", type="text", description="weekly or monthly or yearly"),
     *               @OA\Property(property="noshow_count", type="text"),
     *               @OA\Property(property="noshow_timer", type="text"),
     *               @OA\Property(property="leave_timer", type="text"),
     *               @OA\Property(property="multiple_schedule", type="text"),
     *               @OA\Property(property="multiple_schedule_hr", type="text"),
     *               @OA\Property(property="schedule_bound", type="text"),
     *               @OA\Property(property="radius", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully added.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully added.",
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
    public function rideSettings(Request $request)
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);

            if ($user && $user->hasRole('admin')) {
                $data = [];

                $data['female_safety'] = $request->female_safety ?? null;
                $data['noshow_frequency'] = $request->noshow_frequency ?? null;
                $data['noshow_count'] = $request->noshow_count ?? null;
                $data['noshow'] = $request->show_noshow_timer ?? null;
                $data['leave_timer'] = $request->leave_timer ?? null;
                $data['noshow_timer'] = $request->noshow_timer ?? null;
                $data['cancel_timer'] = $request->cancel_timer ?? null;
                $data['multiple_schedule'] = $request->multiple_schedule ?? null;
                $data['multiple_schedule_hr'] = $request->multiple_schedule_hr ?? null;
                $data['schedule_bound'] = $request->schedule_bound ?? null;
                $data['radius'] = $request->radius ?? null;

                // Get existing value from DB
                $existing = DB::table('ride_settings')->where('id', 1)->value('all_noshow');

                // Decode it or start fresh
                $all_noshow = [
                    'week' => 0,
                    'month' => 0,
                    'year' => 0,
                ];

                if (!empty($existing)) {
                    $decoded = json_decode($existing, true);
                    if (is_array($decoded) && isset($decoded[0])) {
                        $all_noshow = array_merge($all_noshow, $decoded[0]); // merge with default 0s
                    }
                }

                // Update the selected key without removing others
                if ($request->noshow_frequency === 'weekly') {
                    $all_noshow['week'] = $request->noshow_count;
                } elseif ($request->noshow_frequency === 'monthly') {
                    $all_noshow['month'] = $request->noshow_count;
                } elseif ($request->noshow_frequency === 'yearly') {
                    $all_noshow['year'] = $request->noshow_count;
                }

                // Store updated value
                $data['all_noshow'] = json_encode([$all_noshow]);

                DB::table('ride_settings')->updateOrInsert(['id' => 1], $data);

                $this->data = RideSetting::first();

                return response()->json([
                    'success' => true,
                    'message' => 'Ride setting updated successfully.',
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //*************************** Delete holiday api*************************/

    /**
     * @OA\Post(
     *     path="/uc/api/deleteHoliday",
     *     operationId="deleteHoliday",
     *     tags={"AccountSetup"},
     *     summary="Delete holiday",
     *     security={{"Bearer": {}}},
     *     description="Delete holiday",
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
     *     @OA\Response(
     *         response=201,
     *         description="Holiday deleted successfully.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Holiday deleted successfully",
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
    public function deleteHoliday(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required',

            ]);

            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);

            if ($user && $user->hasRole('admin')) {

                 // First get the holiday before deleting
                 $holiday = Holiday::find($request->id);

                $deleteResult = Holiday::where('id', $request->id)->delete();
                 if ($deleteResult) {
                // Save delete history if holiday existed and was deleted
                if ($holiday) {
                    $user = auth('sanctum')->user();
                    DB::table('update_system_setup_histories')->insert([
                        'employee_id' => $user->id,
                        'date' => date('Y-m-d'),
                        'time' => date('H:i:s'),
                        'updated_by' => $user->id,
                        'notes' => 'Holiday deleted',
                        'changed' => "Deleted holiday: ".$holiday->name." (Date: ".$holiday->date.")",
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                    return response()->json([
                        'success' => true,
                        'message' => "Holiday deleted successfully"

                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => "Holiday couldn't be deleted"
                    ], 500);
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    private function saveHolidayDeleteHistory($holiday)
    {
        $user = auth('sanctum')->user();

        DB::table('update_system_setup_histories')->insert([
            'employee_id' => $user->id,
            'date' => date('Y-m-d'),
            'time' => date('H:i:s'),
            'updated_by' => $user->id,
            'notes' => 'Holiday deleted',
            'changed' => "Deleted holiday: {$holiday->name} (Date: {$holiday->date})",
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }


    //************************************Add template api********************************/

    /**
     * @OA\Post(
     *     path="/uc/api/addTemplate",
     *     operationId="addTemplate",
     *     tags={"AccountSetup"},
     *     summary="Add schedule template",
     *     security={{"Bearer": {}}},
     *     description="Add schedule template",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"title", "pricebook", "shift_finishes_next_day", "is_repeat"},
     *               @OA\Property(property="title", type="text"),
     *               @OA\Property(property="pick_time", type="text"),
     *               @OA\Property(property="drop_time", type="text"),
     *               @OA\Property(property="pricebook", type="text"),
     *               @OA\Property(property="is_repeat", type="text", description="1 or 0"),
     *               @OA\Property(property="shift_finishes_next_day", type="text",  description="1 or 0"),
     *
     *            ),
     *        ),
     *    ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully added schedule template.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully added schedule template.",
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
    public function addTemplate(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required',
                'pricebook' => 'required|integer',
                'shift_finishes_next_day' => 'required|in:1,0',
                'is_repeat' => 'required|in:1,0'

            ]);
            // $request = json_decode($request->data);
            // $week_arr = array();
            $schedule_template = new ScheduleTemplate();
            $schedule_template->pick_time = @$request->pick_time;
            $schedule_template->drop_time = @$request->drop_time;
            $schedule_template->title = $request->title;
            $schedule_template->shift_finishes_next_day = @$request->shift_finishes_next_day == 1 ? 1 : 0;
            $schedule_template->pricebook_id = @$request->pricebook;
            $schedule_template->is_repeat = @$request->is_repeat == 1 ? 1 : 0;
            // if (@$request->is_repeat) {
            //     if ($request->reacurrance == "daily") {
            //         $schedule_template->reacurrance = 0;
            //         $schedule_template->repeat_time = $request->repeat_days;
            //     } else if ($request->reacurrance == "weekly") {
            //         $schedule_template->reacurrance = 1;
            //         $schedule_template->repeat_time = $request->repeat_weeks;
            //         if (@$request->mon) {
            //             array_push($week_arr, "mon");
            //         }
            //         if (@$request->tue) {
            //             array_push($week_arr, "tue");
            //         }
            //         if (@$request->wed) {
            //             array_push($week_arr, "wed");
            //         }
            //         if (@$request->thu) {
            //             array_push($week_arr, "thu");
            //         }
            //         if (@$request->fri) {
            //             array_push($week_arr, "fri");
            //         }
            //         if (@$request->sat) {
            //             array_push($week_arr, "sat");
            //         }
            //         if (@$request->sun) {
            //             array_push($week_arr, "sun");
            //         }
            //         $schedule_template->occurs_on = json_encode($week_arr);
            //     } else if ($request->reacurrance == "monthly") {
            //         $schedule_template->reacurrance = 2;
            //         $schedule_template->repeat_time = $request->repeat_months;
            //         $schedule_template->occurs_on = $request->repeat_day_of_month;
            //     }
            //}
            $schedule_template->save();
            return response()->json([
                'success' => true,
                'data' => @$schedule_template,
                'message' => 'Schedule template added successfully.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    //******************************* Delete template api *******************************/

    /**
     * @OA\Post(
     * path="/uc/api/deleteTemplate",
     * operationId="deleteTemplate",
     * tags={"AccountSetup"},
     * summary="Delete template",
     *   security={ {"Bearer": {} }},
     * description="Delete template",
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
     *          description="Template deleted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Template deleted successfully.",
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
    public function deleteTemplate(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required',


            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {
                $price = ScheduleTemplate::where('id', $request->id)->first();
                $price->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'You have successfully deleted.',
                ], 200);
            }
            return response()->json(['success' => false, 'message' => 'Unauthorised user']);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //************************ Update template api********************************** */

    /**
     * @OA\Post(
     *     path="/uc/api/updateTemplate",
     *     operationId="updateTemplate",
     *     tags={"AccountSetup"},
     *     summary="Update schedule template",
     *     security={{"Bearer": {}}},
     *     description="Update schedule template",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id", "title", "pricebook", "shift_finishes_next_day", "is_repeat"},
     *               @OA\Property(property="id", type="text"),
     *               @OA\Property(property="title", type="text"),
     *               @OA\Property(property="pick_time", type="text"),
     *               @OA\Property(property="drop_time", type="text"),
     *               @OA\Property(property="pricebook", type="text"),
     *               @OA\Property(property="is_repeat", type="text", description="1 or 0"),
     *               @OA\Property(property="shift_finishes_next_day", type="text",  description="1 or 0"),
     *
     *            ),
     *        ),
     *    ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully updated schedule template.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated schedule template.",
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
    public function updateTemplate(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required',
                'title' => 'required',
                'pricebook' => 'required',
                'shift_finishes_next_day' => 'required|in:1,0',
                'is_repeat' => 'required|in:1,0',


            ]);

            $schedule_template = ScheduleTemplate::find($request->id);
            if ($schedule_template) {
                $schedule_template->pick_time = @$request->pick_time;
                $schedule_template->drop_time = @$request->drop_time;
                $schedule_template->title = $request->title;
                $schedule_template->shift_finishes_next_day = @$request->shift_finishes_next_day == 1 ? 1 : 0;
                // $schedule_template->pricebook_id = @$request->pricebook;
                $schedule_template->pricebook_id = is_array($request->pricebook) ? $request->pricebook['id'] : $request->pricebook;
                $schedule_template->is_repeat = @$request->is_repeat == 1 ? 1 : 0;
                $schedule_template->save();
                return response()->json([
                    'success' => true,
                    'data' => @$schedule_template,
                    'message' => 'Schedule template added successfully.',
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    //********************************* Schedule template listing api ******************** */

    /**
     * @OA\Get(
     * path="/uc/api/scheduleTemplate",
     * operationId="listScheduleTemplate",
     * tags={"AccountSetup"},
     * summary="List schedule template",
     *   security={ {"Bearer": {} }},
     * description="",
     *      @OA\Response(
     *          response=201,
     *          description="Schedule template listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Schedule template listed successfully",
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
    public function listScheduleTemplate()
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {

                $this->data['scheduleTemplate'] = ScheduleTemplate::with('pricebook')->get();

                return response()->json([
                    'success' => true,
                    'data' => @$this->data,
                    'message' => "Schedule template listed successfully"

                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    //******************************Send notification using fcm token api**************** */
    /**
     * @OA\Post(
     *     path="/uc/api/send/notification",
     *     operationId="sendNotification",
     *     tags={"AccountSetup"},
     *     summary="Send Notification",
     *     security={{"Bearer": {}}},
     *     description="Send Notification",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"notification_type", "title", "body", "schedule_id", "type"},
     *               @OA\Property(property="id", type="text"),
     *               @OA\Property(property="schedule_id", type="text"),
     *               @OA\Property(property="type", type="text"),
     *               @OA\Property(property="notification_type", type="text"),
     *               @OA\Property(property="title", type="text"),
     *               @OA\Property(property="body", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *     @OA\Response(
     *         response=201,
     *         description="Notification sent successfully.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification sent successfully.",
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

    public function sendNotification(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required_if:notification_type,1',
                'notification_type' => 'required|in:1,2',
                'type' => 'required', // Only required if notification_type is 2
                'schedule_id' => 'required', // Only required if notification_type is 2
                'title' => 'required',
                'body' => 'required'
            ]);

            if ($request->notification_type == 1) {
                $apiCallTime = Carbon::now('Asia/Kolkata')->toTimeString();
                $firebaseToken = null;
                $user = SubUser::find($request->id);
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found.'
                    ], 404);
                }
                $noshowTimerExist = CarersNoshowTimer::where('carer_id', $user->id)->where('date', date('Y-m-d'))->where('schedule_id', $request->schedule_id)->where('type', $request->type)->first();
                if (!$noshowTimerExist) {
                    $firebaseToken = $user->fcm_id;
                    //     $noshowTimerExist->start_time = $apiCallTime;
                    //     $noshowTimerExist->update();
                    // } else {
                    $noshowtimer = new CarersNoshowTimer();
                    $noshowtimer->carer_id = $user->id;
                    $noshowtimer->schedule_id = $request->schedule_id;
                    $noshowtimer->type = @$request->type;
                    $noshowtimer->date = date('Y-m-d');
                    $noshowtimer->start_time = $apiCallTime;
                    $noshowtimer->save();
                }
                if (empty($firebaseToken)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'FCM token not found for the carer.'
                    ], 400);
                }
            } elseif ($request->notification_type == 2) {
                $apiCallTime = Carbon::now('Asia/Kolkata')->toTimeString();
                $scheduleCarers = ScheduleCarer::with('user')
                    ->where('schedule_id', $request->schedule_id)
                    ->where('shift_type', $request->type)
                    ->get();

                $fcmTokens = [];
                foreach ($scheduleCarers as $scheduleCarer) {
                    $scheduleCarerStatus = ScheduleCarerStatus::where([
                        'schedule_carer_id' => $scheduleCarer->id,
                        'date' => date('Y-m-d')
                    ])->whereNotIn('status_id', [4, 11])->first();



                    $noshowTimerExist = CarersNoshowTimer::where('carer_id', $scheduleCarer->user->id)->where('date', date('Y-m-d'))->where('schedule_id', $request->schedule_id)->where('type', $request->type)->first();
                    if (!$noshowTimerExist) {
                        // $noshowTimerExist->start_time = $apiCallTime;
                        // $noshowTimerExist->update();
                        //     continue;
                        // } else {
                        if ($scheduleCarerStatus) {
                            $fcmToken = @$scheduleCarer->user->fcm_id;
                            if ($fcmToken) {
                                $fcmTokens[] = $fcmToken;
                            }
                        }
                        $noshowtimer = new CarersNoshowTimer();
                        $noshowtimer->carer_id = $scheduleCarer->user->id;
                        $noshowtimer->date = date('Y-m-d');
                        $noshowtimer->start_time = $apiCallTime;
                        $noshowtimer->schedule_id = $request->schedule_id;
                        $noshowtimer->type = $request->type;
                        $noshowtimer->save();
                    }
                }

                if (empty($fcmTokens)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'FCM tokens not found for the carers.'
                    ], 400);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid notification type.'
                ], 400);
            }

            $SERVER_API_KEY = env('FCM_SERVER_KEY');

            $data = [
                "registration_ids" => $request->notification_type == 1 ? [$firebaseToken] : $fcmTokens,
                "notification" => [
                    "title" => $request->title,
                    "body" => $request->body
                ]
            ];

            $dataString = json_encode($data);

            $headers = [
                'Authorization: key=' . $SERVER_API_KEY,
                'Content-Type: application/json',
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);

            if ($response === false) {
                // Handle curl error
                $error = curl_error($ch);
                curl_close($ch);
                return response(['success' => false, 'message' => $error], 500);
            }

            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $responseData = json_decode($response, true);
            if (isset($responseData['results'][0]['error']) && $responseData['results'][0]['error'] === 'NotRegistered') {
                return response(['success' => false, 'message' => 'FCM token is no longer registered.'], 400);
            }

            if ($statusCode >= 200 && $statusCode < 300) {
                return response(['success' => true, 'message' => 'Notification sent successfully'], 200);
            } else {
                return response(['success' => false, 'message' => 'Something went wrong'], $statusCode);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //***************************Update Announcement api***************************** */

    /**
     * @OA\Post(
     *     path="/uc/api/updateAnnouncement",
     *     operationId="updateAnnouncement",
     *     tags={"AccountSetup"},
     *     summary="Update announcement]",
     *     security={{"Bearer": {}}},
     *     description="Update announcement",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"type", "id", "date"},
     *               @OA\Property(property="id", type="text"),
     *               @OA\Property(property="type", type="text"),
     *               @OA\Property(property="date", type="text"),
     *               @OA\Property(property="description", type="text"),
     *               @OA\Property(property="target", type="text", description="target is required if type is reminder."),
     *               @OA\Property(property="title", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *     @OA\Response(
     *         response=201,
     *         description="Announcement updated successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Announcement updated successfully",
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

    public function updateAnnouncement(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required',
                'type' => 'required|string|in:reminder,holiday',
                'target' => 'nullable|required_if:type,reminder|in:staff,driver,both',
                'date' => 'required|date',
                // 'title' => 'required',
            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);

            if ($user && $user->hasRole('admin')) {

                if ($request->type == 'reminder') {
                    $reminder = Reminder::where('id', $request->id)->first();
                    if ($reminder) {
                        $reminder->target = $request->target;
                        $reminder->date = $request->date;
                        if ($request->title) {
                            $reminder->content = $request->title;
                        }
                        $reminder->description = $request->description;
                        $reminder->update();
                    }
                    $this->data['Announcement'] = $reminder;
                }
                if ($request->type == 'holiday') {
                    $holiday = Holiday::where('id', $request->id)->first();
                    if ($holiday) {
                        $holiday->date = $request->date;
                        if ($request->title) {
                            $holiday->name = $request->title;
                        }
                        $holiday->description = $request->description;
                        $holiday->update();
                    }
                    $this->data['Announcement'] = $holiday;
                }

                return response()->json([
                    'success' => true,
                    'data' => @$this->data,
                    'message' => "Announcement updated successfully"

                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //************************************Delete announcement api******************** */

    /**
     * @OA\Post(
     *     path="/uc/api/deleteAnnouncement",
     *     operationId="deleteAnnouncement",
     *     tags={"AccountSetup"},
     *     summary="Delete announcement",
     *     security={{"Bearer": {}}},
     *     description="Delete announcement",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id", "type"},
     *               @OA\Property(property="id", type="text"),
     *               @OA\Property(property="type", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *     @OA\Response(
     *         response=201,
     *         description="Announcement deleted successfully.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Announcement deleted successfully",
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
    public function deleteAnnouncement(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|integer',
                'type' => 'required|in:reminder,holiday',

            ]);

            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);

            if ($user && $user->hasRole('admin')) {

                if ($request->type == 'reminder') {
                    $reminder = Reminder::where('id', $request->id)->delete();
                } else if ($request->type == 'holiday') {
                    $holiday = Holiday::where('id', $request->id)->delete();
                }
                return response()->json([
                    'success' => true,
                    'message' => "Announcement deleted successfully"


                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    //******************************** Add announcement api***************************/

    /**
     * @OA\Post(
     *     path="/uc/api/addAnnouncement",
     *     operationId="addAnnouncement",
     *     tags={"AccountSetup"},
     *     summary="Add announcement]",
     *     security={{"Bearer": {}}},
     *     description="Add announcement",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"type", "date", "title"},
     *               @OA\Property(property="type", type="text", description="holiday or reminder"),
     *               @OA\Property(property="date", type="text"),
     *               @OA\Property(property="description", type="text"),
     *               @OA\Property(property="target", type="text", description="target is required if type is reminder(driver or staff)."),
     *               @OA\Property(property="title", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *     @OA\Response(
     *         response=201,
     *         description="Announcement updated successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Announcement updated successfully",
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

    public function addAnnouncement(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'type' => 'required|string|in:reminder,holiday',
                'target' => 'required_if:type,reminder|nullable|in:staff,driver,both',
                'date' => 'required|date',
                'title' => 'required',

            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);

            if ($user && $user->hasRole('admin')) {
                if ($request->type == 'reminder') {
                    $reminder = new Reminder();

                    $reminder->target = $request->target;
                    $reminder->date = $request->date;
                    $reminder->content = $request->title;
                    $reminder->description = $request->description;
                    $reminder->save();


                    $this->data['Announcement'] = $reminder; // or $holiday

                    // Send email to all active employees
                    $users = SubUser::where('status', 1)->get();
                    $announcement = [
                        'type' => $request->type,
                        'date' => $request->date,
                        'title' => $request->title,
                        'description' => $request->description,
                    ];

                    $temp_DB_name = DB::connection()->getDatabaseName();

                    $default_DBName = env("DB_DATABASE");
                    $this->connectDB($default_DBName);
                    //foreach ($activeEmployees as $users) {
                    //Mail::to($email)->queue(new AnnouncementMail($announcement));
                    dispatch(new SendAnnouncementEmail($announcement, $users));
                    // }
                    $this->connectDB($temp_DB_name);
                } elseif ($request->type == 'holiday') {
                    $holiday = new Holiday();

                    $holiday->date = $request->date;
                    $holiday->name = $request->title;
                    $holiday->description = $request->description;
                    $holiday->save();


                    $this->data['Announcement'] = $holiday;

                    $users = User::where('status', 1)->get();
                    $announcement = [
                        'type' => $request->type,
                        'date' => $request->date,
                        'title' => $request->title,
                        'description' => $request->description,
                    ];

                    $temp_DB_name = DB::connection()->getDatabaseName();

                    $default_DBName = env("DB_DATABASE");
                    $this->connectDB($default_DBName);

                    //foreach ($activeEmployees as $users) {
                    //Mail::to($email)->queue(new AnnouncementMail($announcement));
                    dispatch(new SendAnnouncementEmail($announcement, $users));
                    // }

                    $this->connectDB($temp_DB_name);
                }

                return response()->json([
                    'success' => true,
                    'data' => @$this->data,
                    'message' => "Announcement added successfully"
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //******************************* List faq  api *************************************

    /**
     * @OA\Get(
     * path="/uc/api/listFaq",
     * operationId="listFaq",
     * tags={"AccountSetup"},
     * summary="List Faq",
     *   security={ {"Bearer": {} }},
     * description="List Faq",
     *      @OA\Response(
     *          response=201,
     *          description="FAQ's listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="FAQ's listed successfully",
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
    public function listFaq()
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            if ($user && $user->hasRole('admin')) {
                // $this->data['Faq'] = Faq::get();
                $this->data['Faq'] = Faq::select('id', 'question as title', 'answer as description', 'created_at', 'updated_at')->get();
                return response()->json([
                    'success' => true,
                    'data' => @$this->data,
                    'message' => "Succesfully listed FAQ's"

                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    //************************ Monthlystats info for driver pick drop ***********************
    public function getMonthlyStats($user_ids)
    {
        $currentDate = Carbon::now();
        $startOfMonth = $currentDate->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();
        $dates = array();
        while ($startOfMonth->lte($endOfMonth)) {
            // Check if the current day is not a Saturday or Sunday
            // if (!$startOfMonth->isWeekend()) {
            $dates[] = $startOfMonth->toDateString();
            // }
            $startOfMonth->addDay();
        }

        $schedule_id_arr = array();
        $previous_date = Carbon::createFromFormat('Y-m-d', min($dates))->subDay()->format('Y-m-d');
        $schedules = Schedule::where(function ($query) use ($dates, $previous_date) {
            $query->where(function ($query) use ($dates) {
                $query->whereIn('date', $dates);

                $query->exists();
            });
            $query->orwhere(function ($query) {
                $query->where('is_repeat', 1);
                $query->where('end_date', '>', now());
            });
            $query->orwhere(function ($query) use ($dates) {
                $query->where('is_repeat', 1);
                $query->where('end_date', '>', min($dates));
                $query->where('end_date', '<', max($dates));
            });
            $query->orwhere(function ($query) use ($previous_date) {
                $query->where('date', $previous_date);
                $query->where('shift_finishes_next_day', 1);
            });
        });


        $schedules = $schedules->where('driver_id', $user_ids);


        $holidays = Holiday::whereIn('date', $dates)->pluck('date');

        $schedules = $schedules->get();


        foreach ($schedules as $schedule) {
            $exc_dates = array();
            if ($schedule->excluded_dates) {
                foreach (json_decode($schedule->excluded_dates) as $exc_date) {
                    array_push($exc_dates, Carbon::createFromFormat('Y-m-d', $exc_date));
                }
            }

            $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
            $date = $current_date->copy()->format('Y-m-d');

            // if (!in_array($current_date->copy(), $public_dates)) {
            if (in_array($date, $dates) && !$holidays->contains($date)) {
                if ($schedule->shift_type_id == 2) {
                    $schedule_id_arr['pick_drop'] = array_key_exists("pick_drop", $schedule_id_arr) ? $schedule_id_arr['pick_drop'] + 1 : 1;
                } else if ($schedule->shift_type_id == 1) {
                    $schedule_id_arr['pick'] = array_key_exists("pick", $schedule_id_arr) ? $schedule_id_arr['pick'] + 1 : 1;
                } else if ($schedule->shift_type_id == 3) {
                    $schedule_id_arr['drop'] = array_key_exists("drop", $schedule_id_arr) ? $schedule_id_arr['drop'] + 1 : 1;
                }
            }
            if ($date == $previous_date) {
                if ($schedule->shift_finishes_next_day == 1) {
                    $schedule_id_arr['drop'] = array_key_exists("drop", $schedule_id_arr) ? $schedule_id_arr['drop'] + 1 : 1;
                }
            }
            // }
            if ($schedule->is_repeat == 1) {
                if ($schedule->reacurrance == 0) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date)->addDays($schedule->repeat_time);
                    while ($current_date < $schedule->end_date) {
                        $date = $current_date->format('Y-m-d');
                        // if (!in_array($current_date, $public_dates)) {
                        if (!in_array($current_date, $exc_dates)) {
                            $schedule->date = $current_date->copy()->format('Y-m-d');
                            if (in_array($date, $dates) && !$holidays->contains($date)) {
                                if ($schedule->shift_type_id == 2) {
                                    $schedule_id_arr['pick_drop'] = array_key_exists("pick_drop", $schedule_id_arr) ? $schedule_id_arr['pick_drop'] + 1 : 1;
                                } else if ($schedule->shift_type_id == 1) {
                                    $schedule_id_arr['pick'] = array_key_exists("pick", $schedule_id_arr) ? $schedule_id_arr['pick'] + 1 : 1;
                                } else if ($schedule->shift_type_id == 3) {
                                    $schedule_id_arr['drop'] = array_key_exists("drop", $schedule_id_arr) ? $schedule_id_arr['drop'] + 1 : 1;
                                }
                            } else if ($date == $previous_date) {
                                if ($schedule->shift_finishes_next_day == 1) {
                                    $schedule_id_arr['drop'] = array_key_exists("drop", $schedule_id_arr) ? $schedule_id_arr['drop'] + 1 : 1;
                                }
                            }
                        }
                        // }
                        $current_date = $current_date->addDays($schedule->repeat_time);
                    }
                } else if ($schedule->reacurrance == 1) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                    $scheduleDate = $current_date->copy();
                    while ($current_date->copy()->startOfWeek() < $schedule->end_date) {
                        $current_date = $current_date->copy()->startOfWeek() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfWeek();
                        $endofthisweek = $current_date->copy()->endOfWeek();
                        if ($this->dates_in_range($current_date->copy()->format('Y-m-d'), $endofthisweek->copy()->format('Y-m-d'), $dates) == true) {
                            while ($current_date->copy() < $endofthisweek & $current_date->copy() < $schedule->end_date) {
                                $date = $current_date->format('Y-m-d');
                                $day_name = $current_date->copy()->format('D');
                                // if (!in_array($current_date, $public_dates)) {
                                if (!in_array($current_date, $exc_dates) && !$holidays->contains($date)) {
                                    $schedule->date = $current_date->copy()->format('Y-m-d');
                                    if (in_array($date, $dates) & in_array(strtolower($day_name), json_decode($schedule->occurs_on))) {
                                        if ($schedule->shift_type_id == 2) {
                                            $schedule_id_arr['pick_drop'] = array_key_exists("pick_drop", $schedule_id_arr) ? $schedule_id_arr['pick_drop'] + 1 : 1;
                                        } else if ($schedule->shift_type_id == 1) {
                                            $schedule_id_arr['pick'] = array_key_exists("pick", $schedule_id_arr) ? $schedule_id_arr['pick'] + 1 : 1;
                                        } else if ($schedule->shift_type_id == 3) {
                                            $schedule_id_arr['drop'] = array_key_exists("drop", $schedule_id_arr) ? $schedule_id_arr['drop'] + 1 : 1;
                                        }
                                    } else if ($date == $previous_date & in_array(strtolower($day_name), json_decode($schedule->occurs_on))) {
                                        if ($schedule->shift_finishes_next_day == 1) {
                                            $schedule_id_arr['drop'] = array_key_exists("drop", $schedule_id_arr) ? $schedule_id_arr['drop'] + 1 : 1;
                                        }
                                    }
                                }
                                // }
                                $current_date = $current_date->copy()->addDay();
                            }
                            $current_date = $current_date->copy()->subDay();
                        }
                        $current_date = $current_date->copy()->addWeeks($schedule->repeat_time);
                    }
                } else if ($schedule->reacurrance == 2) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                    $scheduleDate = $current_date->copy();
                    while ($current_date->copy()->startOfMonth() < $schedule->end_date) {
                        $current_date = $current_date->copy()->startOfMonth() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfMonth();
                        // $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                        $endofthismonth = $current_date->copy()->endOfMonth();
                        if ($this->dates_in_range($current_date->copy()->format('Y-m-d'), $endofthismonth->copy()->format('Y-m-d'), $dates) == true) {
                            while ($current_date->copy() < $endofthismonth & $current_date->copy() < $schedule->end_date) {
                                $date = $current_date->format('Y-m-d');
                                // if (!in_array($current_date, $public_dates)) {
                                if (!in_array($current_date, $exc_dates) && !$holidays->contains($date)) {
                                    $schedule->date = $current_date->copy()->format('Y-m-d');
                                    if (in_array($date, $dates)) {
                                        if ($schedule->shift_type_id == 2) {
                                            $schedule_id_arr['pick_drop'] = array_key_exists("pick_drop", $schedule_id_arr) ? $schedule_id_arr['pick_drop'] + 1 : 1;
                                        } else if ($schedule->shift_type_id == 1) {
                                            $schedule_id_arr['pick'] = array_key_exists("pick", $schedule_id_arr) ? $schedule_id_arr['pick'] + 1 : 1;
                                        } else if ($schedule->shift_type_id == 3) {
                                            $schedule_id_arr['drop'] = array_key_exists("drop", $schedule_id_arr) ? $schedule_id_arr['drop'] + 1 : 1;
                                        }
                                    } else if ($date == $previous_date) {
                                        if ($schedule->shift_finishes_next_day == 1) {
                                            $schedule_id_arr['drop'] = array_key_exists("drop", $schedule_id_arr) ? $schedule_id_arr['drop'] + 1 : 1;
                                        }
                                    }
                                }
                                // }
                                $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                            }
                            $current_date = $current_date->copy()->subDays($schedule->occurs_on);
                        }
                        $current_date = $current_date->copy()->addMonths($schedule->repeat_time);
                    }
                }
            }
        }
        $schedule_id_arr['total'] = $schedule_id_arr['pick'] + $schedule_id_arr['drop'] + $schedule_id_arr['pick_drop'];


        $this->data['monthlyStats']['pick'] = $schedule_id_arr['pick'] ?? 0;
        $this->data['monthlyStats']['pick_and_drop'] = $schedule_id_arr['pick_drop'] ?? 0;
        $this->data['monthlyStats']['drop'] = $schedule_id_arr['drop'] ?? 0;
        $this->data['monthlyStats']['total'] = $schedule_id_arr['total'] ?? 0;
        return $this->data['monthlyStats'];
    }
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


    //******************************** Time and shift ***************************/
    /**
     * @OA\Post(
     *     path="/uc/api/timeandShift",
     *     operationId="timeandShift",
     *     tags={"AccountSetup"},
     *     summary="Time and shift",
     *     security={{"Bearer": {}}},
     *     description="Time and shift",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={},
     *               @OA\Property(property="shift_name", type="text"),
     *               @OA\Property(property="login_time", type="text", description="login time"),
     *               @OA\Property(property="logout_time", type="text", description="logout time"),
     *               @OA\Property(property="sun", type="text", description="Sun"),
     *               @OA\Property(property="mon", type="text", description="Mon"),
     *               @OA\Property(property="tue", type="text", description="Tue"),
     *               @OA\Property(property="wed", type="text", description="Wed"),
     *               @OA\Property(property="thu", type="text", description="Thu"),
     *               @OA\Property(property="fri", type="text", description="Fri"),
     *               @OA\Property(property="sat", type="text", description="Sat"),
     *            ),
     *        ),
     *    ),
     *     @OA\Response(
     *         response=201,
     *         description="Announcement updated successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Announcement updated successfully",
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

    public function timeandShift(Request $request)
    {

        try {

            $validated = $request->validate([
                'shift_name'  => 'required|string|unique:hrms_time_and_shifts,shift_name',
                'login_time'  => 'required|string',
                'logout_time' => 'required|string',
                'sun'         => 'nullable|string',
                'mon'         => 'nullable|string',
                'tue'         => 'nullable|string',
                'wed'         => 'nullable|string',
                'thu'         => 'nullable|string',
                'fri'         => 'nullable|string',
                'sat'         => 'nullable|string',
                'shift_finishs_next_day' => 'required|integer'
            ]);
             $shift = HrmsTimeAndShift::create([
                'shift_name' => $validated['shift_name'],
                'shift_finishs_next_day' => $validated['shift_finishs_next_day'],
                'shift_time' => [
                    'start' => $validated['login_time'],
                    'end'   => $validated['logout_time']
                ],
                'shift_days' => [
                    'SUN' => $request->sun ?? '',
                    'MON' => $request->mon ?? '',
                    'TUE' => $request->tue ?? '',
                    'WED' => $request->wed ?? '',
                    'THU' => $request->thu ?? '',
                    'FRI' => $request->fri ?? '',
                    'SAT' => $request->sat ?? '',
                ]
            ]);
            // Added history tracking
            $this->recordShiftCreation($shift);

            return response()->json([
                'success' => true,
                'message' => "Successfully added shift"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    private function recordShiftCreation($shift)
    {
        $user = auth('sanctum')->user();
        if (!$user) return;

        DB::table('update_system_setup_histories')->insert([
            'employee_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'updated_by' => $user->id,
            'notes' => 'New shift created',
            'changed' => sprintf(
                "Created shift: %s (%s to %s) %s next day",
                $shift->shift_name,
                $shift->shift_time['start'],
                $shift->shift_time['end'],
                $shift->shift_finishs_next_day ? 'finishes' : 'does not finish'
            ),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }



    /**
     * @OA\Get(
     * path="/uc/api/timeandShiftlist",
     * operationId="timeandShiftlist",
     * tags={"AccountSetup"},
     * summary="timeandShift list",
     *   security={ {"Bearer": {} }},
     * description="List timeandShift",
     *      @OA\Response(
     *          response=201,
     *          description="timeandShift's listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="timeandShift's listed successfully",
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

    public function timeandShiftlist(Request $request)
    {
        try {

            $timeSheet = HrmsTimeAndShift::get();
            return response()->json([
                'success' => true,
                'data' => $timeSheet,
                'message' => "Time shift list"
            ], 200);
        } catch (\Throwable $th) {

            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/uc/api/timeandShiftEdit/{id}",
     *     operationId="timeandShiftEdit",
     *     tags={"AccountSetup"},
     *     summary="Time and shift",
     *     security={{"Bearer": {}}},
     *     description="Time and shift",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={},
     *               @OA\Property(property="shift_name", type="text"),
     *               @OA\Property(property="login_time", type="text", description="login time"),
     *               @OA\Property(property="logout_time", type="text", description="logout time"),
     *               @OA\Property(property="sun", type="text", description="Sun"),
     *               @OA\Property(property="mon", type="text", description="Mon"),
     *               @OA\Property(property="tue", type="text", description="Tue"),
     *               @OA\Property(property="wed", type="text", description="Wed"),
     *               @OA\Property(property="thu", type="text", description="Thu"),
     *               @OA\Property(property="fri", type="text", description="Fri"),
     *               @OA\Property(property="sat", type="text", description="Sat"),
     *            ),
     *        ),
     *    ),
     *     @OA\Response(
     *         response=201,
     *         description="Announcement updated successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Announcement updated successfully",
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

    public function timeandShiftEdit(Request $request, $id)
    {

        try {

            $timeandshift = HrmsTimeAndShift::find($id);
            $validated = $request->validate([
                'shift_name'  => 'required|string',
                'login_time'  => 'required|string',
                'logout_time' => 'required|string',
                'sun'         => 'nullable|string',
                'mon'         => 'nullable|string',
                'tue'         => 'nullable|string',
                'wed'         => 'nullable|string',
                'thu'         => 'nullable|string',
                'fri'         => 'nullable|string',
                'sat'         => 'nullable|string',
                'shift_finishs_next_day' => 'required|integer'
            ]);

            // Get original data before update
            $originalData = [
                'shift_name' => $timeandshift->shift_name,
                'login_time' => $timeandshift->shift_time['start'] ?? '',
                'logout_time' => $timeandshift->shift_time['end'] ?? '',
                'shift_finishs_next_day' => $timeandshift->shift_finishs_next_day
            ];

            $timeandshift->update([
                'shift_name' => $validated['shift_name'],
                'shift_finishs_next_day' => $validated['shift_finishs_next_day'],
                'shift_time' => [
                    'start' => $validated['login_time'],
                    'end'   => $validated['logout_time']
                ],
                'shift_days' => [
                    'SUN' => $request->sun ?? '',
                    'MON' => $request->mon ?? '',
                    'TUE' => $request->tue ?? '',
                    'WED' => $request->wed ?? '',
                    'THU' => $request->thu ?? '',
                    'FRI' => $request->fri ?? '',
                    'SAT' => $request->sat ?? '',
                ]
            ]);

            // Track changes and save history
            $this->trackTimeAndShiftChanges($originalData, $validated);

            return response()->json([
                'success' => true,
                'message' => "Time and shift updated successfull"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    private function trackTimeAndShiftChanges(array $originalData, array $newData)
    {
        $user = auth('sanctum')->user();
        if (!$user) return;

        $changes = collect([
            'shift_name' => [
                'old' => $originalData['shift_name'],
                'new' => $newData['shift_name'],
                'label' => 'Shift name'
            ],
            'login_time' => [
                'old' => $originalData['login_time'],
                'new' => $newData['login_time'],
                'label' => 'Login time'
            ],
            'logout_time' => [
                'old' => $originalData['logout_time'],
                'new' => $newData['logout_time'],
                'label' => 'Logout time'
            ],
            'shift_finishs_next_day' => [
                'old' => $originalData['shift_finishs_next_day'] ? 'Yes' : 'No',
                'new' => $newData['shift_finishs_next_day'] ? 'Yes' : 'No',
                'label' => 'Shift finishes next day'
            ]
        ])->filter(function ($item) {
            return $item['old'] != $item['new'];
        })->map(function ($item) {
            return "{$item['label']} changed from '{$item['old']}' to '{$item['new']}'";
        })->values()->toArray();

        if (!empty($changes)) {
            DB::table('update_system_setup_histories')->insert([
                'employee_id' => $user->id,
                'date' => now()->format('Y-m-d'),
                'time' => now()->format('H:i:s'),
                'updated_by' => $user->id,
                'notes' => 'Time and Shift updated',
                'changed' => implode(', ', $changes),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }






    /**
     * @OA\Get(
     * path="/uc/api/hrmsAccountInfo",
     * operationId="accountInfohrms",
     * tags={"AccountSetup"},
     * summary="Account info",
     *   security={ {"Bearer": {} }},
     * description="Account info",
     *      @OA\Response(
     *          response=201,
     *          description="Account info listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Account info listed successfully",
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
    public function hrmsAccountInfo()
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
            //   if ($user && $user->hasRole('admin')) {
            $company = CompanyDetails::first();
            $companyDetails = \DB::table('company_addresses')
                ->where('company_addresses.company_id', $company->id)
                ->whereNull('company_addresses.end_date')
                ->join('company_details', 'company_addresses.company_id', '=', 'company_details.id')
                ->select('company_details.id', 'company_details.name', 'company_details.logo', 'company_details.email', 'company_details.phone', 'company_addresses.address', 'company_addresses.latitude', 'company_addresses.longitude')
                ->first();

            if ($companyDetails) {
                @$companyDetails->logo_url = url('public/images');
                $this->data['companyDetails'] = $companyDetails;
            } else {
                @$company->logo_url = url('images');
                $this->data['companyDetails'] = $company;
            }
            $this->data['current_subscription'] = @$this->getCurrentSubscription($user_id);
            $this->data['announcements'] = $this->getAnnouncement();
            $this->data['shiftTypes'] = ShiftTypes::orderBy('id', 'DESC')->get();
            $this->data['docCategories'] = DocCategory::orderBy('id', 'DESC')->get();
            $this->data['cancelRideReasons'] = Reason::where('type', 3)->get();
            $this->data['leaveReasons'] = Reason::where('type', 0)->get();
            $this->data['ratingReasons'] = Reason::where('type', 4)->get();
            $this->data['complaintReasons'] = Reason::where('type', 1)->get();
            $this->data['shiftChangeReasons'] = Reason::where('type', 2)->get();
            $this->data['tempChangeReasons'] = Reason::where('type', 5)->get();
            $this->data['rideSettings'] = RideSetting::first();
            $this->data['holiday'] = Holiday::orderBy('id', 'DESC')->get();
            // $this->data['faqs'] = Faq::get();
            $this->data['Faq'] = Faq::select('id', 'question as title', 'answer as description', 'created_at', 'updated_at')->get();
            $this->data['pricebooks'] = PriceBook::orderBy('id', 'DESC')->with('priceBookData')->get();
            $this->data['filterPriceBooks'] = PriceBook::orderBy('id', 'DESC')
                ->has('priceBookData')
                ->get();

            $this->data['scheduleTemplate'] = ScheduleTemplate::with('pricebook')->get();
            $this->data['basicSettings'] = $company;
            $this->data['cdb'] = base64_encode($user->database_name);

            $this->data['hrmsReason']['reject_employee'] = Reason::where('type', 6)->get();
            $this->data['hrmsReason']['accept_employee'] = Reason::where('type', 7)->get();
            $this->data['hrmsReason']['future_reference_employee'] = Reason::where('type', 8)->get();
            $this->data['hrmsReason']['Reconsideration'] = Reason::where('type', 9)->get();
            $this->data['job_requirements'] = ['requester' => $user->first_name, 'unique_id' => $user->unique_id, 'email' => $user->email, 'phone' => $user->phone, 'company_name' => $user->company_name, 'address' => $user->address];

            return response()->json([
                'success' => true,
                'data' => @$this->data,
                'message' => "Account info listed successfully"

            ], 200);
            // }
            //  else {
            //     return response()->json([
            //         'success' => false,
            //         'message' => "User is not admin"

            //     ], 401);
            // }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }



    //********************************** Grouping users password ********************/

    /**
     * @OA\Post(
     *     path="/uc/api/groupingPassword",
     *     operationId="groupingPassword",
     *     tags={"AccountSetup"},
     *     summary="Upgrade subscription plan",
     *     security={ {"Bearer": {} }},
     *     description="Upgrade update group password",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"email", "password", "user_id"},
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="password", type="string", example="secret123"),
     *                 @OA\Property(
     *                     property="user_id",
     *                     type="array",
     *                     @OA\Items(type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Plan upgraded successfully.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Plan upgraded successfully.",
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



    public function groupingPassword(Request $request)
    {

        try {

            $data = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
                'user_id' => 'required'
            ]);


            // Convert comma-separated user_id string to an array
            $data['user_id'] = explode(',', $data['user_id']);

            $default_DBName = env("DB_DATABASE");
            $this->connectDB($default_DBName);

            // DB::beginTransaction();

            $subUsers =  SubUser::whereIn('id', $data['user_id'])->where('email', $data['email'])->get();

            GroupLoginUser::firstOrCreate(['email' => $data['email']], $data);

            foreach ($subUsers as $user) {
                $user->password = Hash::make($data['password']);
                $user->save();
                $this->connectDB($user->database_name);
                GroupLoginUser::firstOrCreate(['email' => $data['email']], $data);

                $userChild = User::find($user->id);
                $userSubChild = SubUser::find($user->id);
                if ($userChild) {
                    $userChild->password = Hash::make($data['password']);
                }
                if ($userSubChild) {
                    $userSubChild->password = Hash::make($data['password']);
                }
                // $this->connectDB($default_DBName);
            }
            // DB::commit();
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => "Maked group users successfully"

            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $th->getMessage()
            ], 500);
        }
    }




    /**
     * @OA\Delete(
     *     path="/uc/api/timeandShiftdelete/{id}",
     *     operationId="timeandShiftdelete",
     *     tags={"AccountSetup"},
     *     summary="Delete timeandShiftdelete Request",
     *     security={ {"Bearer": {} }},
     *     description="Delete timeandShiftdelete Request",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="timeandShiftdelete deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="timeandShiftdelete deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource Not Found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="timeandShiftdelete not found.")
     *         )
     *     )
     * )
     */


    public function timeandShiftdelete($id)
    {
        try {
            $HrmsTimeAndShift = HrmsTimeAndShift::find($id);
            if (isset($HrmsTimeAndShift)) {

                // Record deletion history (new code)
                $this->recordShiftDeletion($HrmsTimeAndShift);

                $HrmsTimeAndShift->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Time and Shift Removed Successfully',
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'The given data is not found',
                ]);
            }
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }

    //Private function for tracking
    private function recordShiftDeletion($shift)
    {
        $user = auth('sanctum')->user();
        if (!$user) return;

        DB::table('update_system_setup_histories')->insert([
            'employee_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'updated_by' => $user->id,
            'notes' => 'Time and Shift deleted',
            'changed' => sprintf(
                "Deleted shift: %s (%s to %s)",
                $shift->shift_name,
                $shift->shift_time['start'] ?? '',
                $shift->shift_time['end'] ?? ''
            ),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }


    /**
     * @OA\Post(
     *     path="/uc/api/SystemSetupHistory",
     *     operationId="listSystemSetupHistory",
     *     tags={"AccountSetup"},
     *     summary="List System Setup History",
     *     security={ {"Bearer": {} }},
     *     description="Retrieve history of system setup changes with filtering options",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               @OA\Property(property="employee_id", type="integer"),
     *            ),
     *        ),
     *    ),
     *     @OA\Response(
     *         response=200,
     *         description="System setup history retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="employee_id", type="integer", example=5),
     *                     @OA\Property(property="date", type="string", example="2023-05-15"),
     *                     @OA\Property(property="time", type="string", example="14:30:00"),
     *                     @OA\Property(property="notes", type="string", example="Holiday deleted"),
     *                     @OA\Property(property="changed", type="string", example="Deleted holiday: New Year (2023-01-01)"),
     *                     @OA\Property(
     *                         property="employee",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=5),
     *                         @OA\Property(property="first_name", type="string", example="John"),
     *                         @OA\Property(property="last_name", type="string", example="Doe")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="History retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="date_to",
     *                     type="array",
     *                     @OA\Items(type="string", example="The date to must be a date after or equal to date from.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */


    public function SystemSetupHistory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'nullable|integer|exists:users,id',
                'action_type' => 'nullable|string',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $history = UpdateSystemSetupHistory::with([
                    'employee:id,first_name,last_name',
                    'changedBy:id,first_name,last_name'
                ])
                ->when($request->employee_id, fn($q) => $q->where('employee_id', $request->employee_id))
                ->when($request->action_type, fn($q) => $q->where('notes', 'like', '%'.$request->action_type.'%'))
                ->when($request->date_from && $request->date_to,
                    fn($q) => $q->whereBetween('date', [$request->date_from, $request->date_to]))
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'date' => $item->date,
                        'time' => $item->time,
                        'notes' => $item->notes,
                        'changed' => $item->changed,
                        'employee' => $item->employee ? [
                            'id' => $item->employee->id,
                            'name' => $item->employee->first_name . ' ' . $item->employee->last_name
                        ] : null,
                        'changedBy' => $item->changedBy ? [
                            'id' => $item->changedBy->id,
                            'name' => $item->changedBy->first_name . ' ' . $item->changedBy->last_name
                        ] : null
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'History retrieved successfully',
                'data' => $history
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    /**
     * @OA\Post(
     * path="/uc/api/alldriversDelete",
     * operationId="alldriversDelete",
     * tags={"Ucruise Employee"},
     * summary="Delete all drivers List ",
     *   security={ {"Bearer": {} }},
     * description="Delete all drivers",
     *      @OA\Response(
     *          response=201,
     *          description="Driver listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Driver listed successfully",
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

    public function alldriversDelete(Request $request){

        try {

            $temp_DB_name = DB::connection()->getDatabaseName(); // save current DB name
            $default_DBName = env("DB_DATABASE"); // get default DB name

            // Get all sub_users with role "driver" or "archived_driver"
            $drivers = SubUser::whereHas("roles", function ($q) {
                $q->whereIn("name", ["driver", "archived_driver"]);
            })->get();

            foreach ($drivers as $sub_user) {
                // Switch to default DB
                $this->connectDB($default_DBName);
                DB::table('role_sub_user')->where('sub_user_id', $sub_user->id)->delete();
                SubUser::where('id', $sub_user->id)->forceDelete();

                // Switch back to original (tenant) DB
                $this->connectDB($temp_DB_name);
                DB::table('role_sub_user')->where('sub_user_id', $sub_user->id)->delete();
                $sub_user->forceDelete();
            }

            return response()->json([
                'success' => true,
                'message' => 'All Drivers deleted successfully witb db '. $temp_DB_name,
                'data' => []
            ], 200);

        } catch (\Throwable $th) {
             return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }

    }


    /**
     * @OA\Post(
     * path="/uc/api/allschedulesDelete",
     * operationId="allschedulesDelete",
     * tags={"Ucruise Employee"},
     * summary="Delete all schedules",
     *   security={ {"Bearer": {} }},
     * description="Delete all schedules",
     *      @OA\Response(
     *          response=201,
     *          description="Schedule listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Schedule listed successfully",
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

    public function allschedulesDelete(Request $request){

          try {
            $temp_DB_name = DB::connection()->getDatabaseName(); // save current DB name
            // $default_DBName = env("DB_DATABASE"); // get default DB name

            // Ensure we're working with the current tenant database
            $this->connectDB($temp_DB_name);

            $scheduleIds = Schedule::pluck('id');
            if ($scheduleIds->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No schedules found to delete.',
                ], 404);
            }

            // Delete related schedule carers first
            ScheduleCarer::whereIn('schedule_id', $scheduleIds)->delete();

            // Then delete the schedules
            Schedule::whereIn('id', $scheduleIds)->delete();

            return response()->json([
                'status' => true,
                'message' => 'All schedules and related records deleted successfully.',
            ]);

          } catch (\Throwable $th) {
             return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
          }

    }


}
