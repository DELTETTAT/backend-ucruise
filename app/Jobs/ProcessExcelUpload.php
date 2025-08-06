<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ExcelUploadStaging;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\SubUser;
use App\Models\HrmsEmployeeRole;
use App\Models\Role;
use App\Models\HrmsTeam;
use App\Models\EmployeeTeamManager;
use App\Models\EmployeesUnderOfManager;
use App\Models\HrmsTimeAndShift;
use App\Models\Designation;
use App\Models\TeamManager;
use Hash;
use App\Jobs\UpdateUserLatLong;
use App\Jobs\ProcessExcelUploadEmployeeSalary;

class ProcessExcelUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $uploadId;
    public $databaseInfo;
    public function __construct($uploadId, array $databaseInfo)
    {
        $this->uploadId = $uploadId;
        $this->databaseInfo = $databaseInfo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $child_DB = $this->databaseInfo['child_DB'];
        $company_name = $this->databaseInfo['company_name'];
        $this->connectDB($child_DB);

        $batches = ExcelUploadStaging::where('upload_id', $this->uploadId)
                                        ->where('status', 'pending')
                                        ->get();
        // $batchIds = $batches->pluck('id')->toArray();
        // ExcelUploadStaging::whereIn('id', $batchIds)->update(['status' => 'processing']);
        ExcelUploadStaging::where('upload_id', $this->uploadId)->where('status', 'pending')->update(['status' => 'processing']);
        $default_DB = env("DB_DATABASE");
        $database_path = env("DB_HOST");
        $database_username = env("DB_USERNAME");
        $database_password = env("DB_PASSWORD");
        $password = Hash::make('unify@123');
        //$chunks = array_chunk($dataRows, 100);
        $designationCache = [];
        $teamCache = [];
        $managerGroupCache = [];
        $shiftCache = [];
        $created_at = now();
        foreach ($batches as $batch) {
            try {
                $dataRows = json_decode($batch->data, true);
                $count = is_array($dataRows) ? count($dataRows) : 0;
                info("Total records in this batch: ".$batch->batch_number. "..count".$count);

                // $batch->status = 'processing';
                // $batch->save();
                collect($dataRows)->chunk(50)->each(function ($chunkRows) use ($default_DB, $password,$database_path,$database_username,$database_password,$child_DB,$company_name,$created_at) {
                        $recordsToInsertInChildDB = [];
                        foreach ($chunkRows as $row) {
                                if (isset($row['designation']) && !empty($row['designation'])) {
                                    $designationKey = strtolower(trim($row['designation']));
                                    if (!isset($designationCache[$designationKey])) {
                                        $designation = Designation::firstOrCreate(
                                            ['title' => $row['designation']],
                                            ['status' => 1]
                                        );
                                        $designationCache[$designationKey] = $designation->title;
                                    }
                                    $row['designation'] = $designationCache[$designationKey];
                                }
                                // *** Employee Shift ***
                                if (!empty($row['employee_shift'])) {
                                    $shiftKey = strtolower(trim($row['employee_shift']));
                                    if (!isset($shiftCache[$shiftKey])) {
                                        $shiftFinishNextDay = (!empty($row['shift_finishs_next_day']) && strtolower($row['shift_finishs_next_day']) == 'yes') ? 1 : 0;
                                        $shift = HrmsTimeAndShift::firstOrCreate(
                                            ['shift_name' => $row['employee_shift']], // Search condition
                                            [
                                                'shift_finishs_next_day' => $shiftFinishNextDay,
                                                'shift_time' => [
                                                    'start' => $row['shift_logout_time'] ?? '10:00',
                                                    'end'   => $row['shift_login_time'] ?? '19:00',
                                                ],
                                                'shift_days' => [
                                                    'SUN' => 0,
                                                    'MON' => 1,
                                                    'TUE' => 1,
                                                    'WED' => 1,
                                                    'THU' => 1,
                                                    'FRI' => 1,
                                                    'SAT' => 0,
                                                ]
                                            ]
                                        );
                                        $shiftCache[$shiftKey] = [
                                            'name' => $shift->shift_name,
                                            'type' => $shift->shift_finishs_next_day,
                                        ];
                                    }


                                    $employee_shift = $shiftCache[$shiftKey]['name'];
                                    $shift_type = $shiftCache[$shiftKey]['type'] ?? 0;
                                }else {
                                    $shift = HrmsTimeAndShift::first();
                                    if ($shift) {
                                        $employee_shift = $shift->shift_name;
                                        $shift_type = $shift->shift_finishs_next_day ?? 0;
                                    }else {
                                        $shift = HrmsTimeAndShift::create([
                                                'shift_name' => "Morning Shift",
                                                'shift_finishs_next_day' => 0,
                                                'shift_time' => ['start' => '10:00', 'end' => '19:00'],
                                                'shift_days' => [
                                                        'SUN' => 0,
                                                        'MON' => 1,
                                                        'TUE' => 1,
                                                        'WED' => 1,
                                                        'THU' => 1,
                                                        'FRI' => 1,
                                                        'SAT' => 0,
                                                    ],
                                                ]);
                                        $employee_shift = "Morning Shift";
                                        $shift_type = 0;
                                    }
                                }
                                // *** Manager Group ***
                                if (isset($row['manager_group_name']) && !empty($row['manager_group_name'])) {
                                    $managerGroupKey = strtolower(trim($row['manager_group_name']));
                                    if (!isset($managerGroupCache[$managerGroupKey])) {
                                        $managerGroup = TeamManager::firstOrCreate(
                                            ['name' => $row['manager_group_name']],
                                            ['status' => 1]
                                        );
                                        $managerGroupCache[$managerGroupKey] = $managerGroup->id;
                                    }
                                    $manager_group_id = $managerGroupCache[$managerGroupKey];

                                }else {
                                    $manager_group_id = null;
                                }
                                // ***  team group  *** //
                                $team_id = null;
                                if (isset($row['team_name']) && !empty($row['team_name'])) {
                                    // $team = HrmsTeam::firstOrCreate(
                                    //     ['team_name' => $row['team_name']],
                                    //     ['team_manager_id' => $manager_group_id]
                                    // );
                                        $existingTeam = HrmsTeam::where('team_name', $row['team_name'])->where('team_manager_id', $manager_group_id)->first();

                                        if ($existingTeam) {
                                            $team_id = $existingTeam->id;
                                        } else {
                                            $newTeam = HrmsTeam::create([
                                                'team_name' => $row['team_name'],
                                                'team_manager_id' => $manager_group_id,
                                            ]);
                                            $team_id = $newTeam->id;
                                        }

                                }

                                $this->connectDB($default_DB);

                                $unique_id = null;
                                if (!empty($row['new_emp_code'])) {
                                    $unique_id = $row['new_emp_code'];
                                }elseif (!empty($row['emp_code'])) {
                                    $unique_id = $row['emp_code'];
                                }

                                // status
                                $statusMap = [
                                    'active' => 1,
                                    'inactive' => 2,
                                    'resigned' => 3,
                                    'onnoticeperiod' => 4,
                                    'suspended' => 5,
                                    'terminated' => 6,
                                    'abscond' => 8,
                                ];
                                if (!empty($row['current_status'])) {
                                    $normalizedStatus = strtolower(preg_replace('/\s+/', '', $row['current_status']));
                                    $status = $statusMap[$normalizedStatus] ?? 2; // default to Inactive if not matched
                                } else {
                                    $status = 2; // default Inactive
                                }

                                // Email Validate
                                $email = isset($row['official_email_id']) && filter_var($row['official_email_id'], FILTER_VALIDATE_EMAIL) ? $row['official_email_id'] : null;
                                // Cab Facility
                                $cabfacility = 0;
                                if (!empty($row['do_you_need_cab'])) {
                                    $normalizedCab = strtolower(trim($row['do_you_need_cab']));
                                    if (in_array($normalizedCab, ['yes', 'y'])) {
                                            $cabfacility = 1;
                                    }
                                }

                                $genderRaw = strtolower(trim($row['gender'] ?? ''));

                                switch ($genderRaw) {
                                    case 'm':
                                    case 'male':
                                        $row['gender'] = 'Male';
                                        break;
                                    case 'f':
                                    case 'female':
                                        $row['gender'] = 'Female';
                                        break;
                                    case 'o':
                                    case 'other':
                                        $row['gender'] = 'Other';
                                        break;
                                    default:
                                        $row['gender'] = ''; // ya 'Not Specified'
                                        break;
                                }

                                $subUser = new SubUser();
                                $subUser->unique_id = $unique_id;
                                $subUser->doj = $row['doj'];///$date_of_joining;
                                $subUser->status = $status ?? 1;
                                $subUser->first_name = $row['first_name'] ?? "";
                                $subUser->last_name = $row['last_name'] ?? "";
                                $subUser->email = $email;
                                $subUser->password = $password;
                                $subUser->employement_type = $row['designation'] ?? "";
                                $subUser->dob = $row['actual_birthday']; //$date_of_birth;
                                $subUser->gender = $row['gender'] ?? "";
                                $subUser->phone = $row['phone_number'] ?? "";
                                $subUser->mobile = $row['emergency_phone_number'] ?? "";
                                $subUser->blood_group = $row['blood_group'] ?? "";
                                $subUser->employee_shift = $employee_shift ?? "Morning Shift";
                                $subUser->shift_type = $shift_type ??'0';
                                $subUser->cab_facility = $cabfacility;
                                $subUser->marital_status = $row['marital_status'] ?? "";
                                $subUser->database_path = env("DB_HOST");//$this->data['database_path'];
                                $subUser->database_name = $this->databaseInfo['child_DB'];
                                $subUser->database_username = env("DB_USERNAME");//$this->data['database_username'];
                                $subUser->database_password = env("DB_PASSWORD");//$this->data['database_password'];
                                $subUser->company_name = $this->databaseInfo['company_name'];
                                $getRole =  'carer';

                                $admin = Role::where('name', 'staff')->first();
                                if ($subUser->save()) {
                                    if (!$subUser->hasRole($getRole)) {
                                        $subUser->roles()->attach($admin);
                                    }
                                }

                                $this->connectDB($child_DB);

                                if ($email && SubUser::where('email', $email)->exists()) {
                                    $sub_user_email = null;
                                }else {
                                    $sub_user_email = $email;
                                }
                                if ($email && User::where('email', $email)->exists()) {
                                    $user_email = null;
                                }else {
                                    $user_email = $email;
                                }

                                if (!empty($unique_id)) {
                                    $existUniqueId = SubUser::where('unique_id', $unique_id)->exists();
                                    $existSubUniqueId = User::where('unique_id', $unique_id)->exists();
                                    if ($existUniqueId || $existSubUniqueId) {
                                        $unique_id = null;
                                    }
                                }


                                $childUser = new User();
                                $childUser->id = $subUser->id;
                                $childUser->unique_id = $unique_id;
                                $childUser->doj = $row['doj'];//$date_of_joining;
                                $childUser->status = $status ?? 1;
                                $childUser->password = $password;
                                $childUser->address = $row['permanent_address_as_per_adhar'] ?? "";
                                $childUser->first_name = $row['first_name'] ?? "";
                                $childUser->last_name = $row['last_name'] ?? "";
                                $childUser->email = $user_email;
                                $childUser->employement_type = $row['designation'] ?? "";
                                $childUser->dob = $row['actual_birthday'];//$date_of_birth;
                                $childUser->gender = $row['gender'] ?? "";
                                $childUser->phone = $row['phone_number'] ?? "";
                                $childUser->mobile = $row['emergency_phone_number'] ?? "";
                                $childUser->blood_group = $row['blood_group'] ?? "";
                                $childUser->employee_shift = $employee_shift;
                                $childUser->shift_type = $shift_type ?? 0;
                                $childUser->cab_facility = $cabfacility;//$subUser->cab_facility;
                                $childUser->latitude =  null;
                                $childUser->longitude =  null;
                                $childUser->marital_status = $row['marital_status'] ?? "";
                                $childUser->database_path = $database_path;//env("DB_HOST");//$this->data['database_path'];
                                $childUser->database_name = $child_DB; //$this->databaseInfo['child_DB'];
                                $childUser->database_username = $database_username;//env("DB_USERNAME");//$this->data['database_username'];
                                $childUser->database_password = $database_password;//env("DB_PASSWORD");//$this->data['database_password'];
                                $childUser->company_name = $company_name; //$this->databaseInfo['company_name'];

                                $getRole =  'carer';
                                $admin = Role::where('name', $getRole)->first();
                                if ($childUser->save()) {
                                    if (!$childUser->hasRole($getRole)) {
                                        $childUser->roles()->attach($admin);
                                    }
                                }

                                $childSubUser = new SubUser();
                                $childSubUser->id = $subUser->id;
                                $childSubUser->unique_id = $unique_id;
                                $childSubUser->doj = $row['doj'];//$date_of_joining;
                                $childSubUser->status = $status ?? 1;
                                $childSubUser->password = $password;
                                $childSubUser->first_name = $row['first_name'] ?? "";
                                $childSubUser->last_name = $row['last_name'] ?? "";
                                $childSubUser->email = $sub_user_email;
                                $childSubUser->employement_type = $row['designation'] ?? "";
                                $childSubUser->dob = $row['actual_birthday'];//$date_of_birth;
                                $childSubUser->gender = $row['gender'] ?? "";
                                $childSubUser->phone = $row['phone_number'] ?? "";
                                $childSubUser->mobile = $row['emergency_phone_number'] ?? "";
                                $childSubUser->blood_group = $row['blood_group'] ?? "";
                                $childSubUser->employee_shift = $employee_shift;
                                $childSubUser->shift_type = $shift_type ?? 0;
                                $childSubUser->cab_facility = $cabfacility;
                                $childSubUser->marital_status = $row['marital_status'] ?? "";
                                $childSubUser->database_path = $database_path;//env("DB_HOST");//$this->data['database_path'];
                                $childSubUser->database_name = $child_DB; //$this->databaseInfo['child_DB'];
                                $childSubUser->database_username = $database_username;//env("DB_USERNAME");//$this->data['database_username'];
                                $childSubUser->database_password = $database_password;//env("DB_PASSWORD");//$this->data['database_password'];
                                $childSubUser->company_name = $company_name; //$this->databaseInfo['company_name'];

                                $getRole = 'carer';
                                $admin = Role::where('name', $getRole)->first();
                                if ($childSubUser->save()) {
                                    if (!$childSubUser->hasRole($getRole)) {
                                        $childSubUser->roles()->attach($admin);
                                    }
                                }


                                //////////////////////////////////////////
                                 // Store inserted data with ID
                                    // $recordsToInsertInChildDB[] = [
                                    //     'id' => $childUser->id,//$subUser->id,
                                    //     'first_name' => $childSubUser->first_name,
                                    //     'last_name' => $childSubUser->last_name,
                                    //     'email' => $childSubUser->email,
                                    //     'password' => $childSubUser->password,
                                    //     'unique_id' => $childSubUser->unique_id,
                                    //     'doj' => $childSubUser->doj,
                                    //     'status' => $childSubUser->status,
                                    //     'employement_type' => $childSubUser->employement_type,
                                    //     'dob' => $childSubUser->dob,
                                    //     'gender' => $childSubUser->gender,
                                    //     'phone' => $childSubUser->phone,
                                    //     'mobile' => $childSubUser->mobile,
                                    //     'blood_group' => $childSubUser->blood_group,
                                    //     'employee_shift' => $childSubUser->employee_shift,
                                    //     'shift_type' => $childSubUser->shift_type,
                                    //     'cab_facility' => $childSubUser->cab_facility,
                                    //     'marital_status' => $childSubUser->marital_status,
                                    //     'database_path' => $childSubUser->database_path,
                                    //     'database_name' => $childSubUser->database_name,
                                    //     'database_username' => $childSubUser->database_username,
                                    //     'database_password' => $childSubUser->database_password,
                                    //     'company_name' => $childSubUser->company_name,
                                    //     'created_at' => $childSubUser->created_at,
                                    //     'updated_at' => $childSubUser->updated_at,
                                    // ];

                                //////////////////////////////////////////

                                DB::table('user_infos')->insert([
                                    'user_id'  => $subUser->id,
                                    'parmanent_address'  => $row['permanent_address_as_per_adhar'] ?? "",
                                    'country' => $row['country'] ?? "",
                                    'new_emp_code' => $unique_id ?? "",
                                    'official_name' => $row['official_name'] ?? "",
                                    'employee_real_name' =>  trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                                    'official_email' => $email,
                                    'personal_email' => $row['personal_email_id'] ?? "",
                                    'LOB' => $row['lob'] ?? "",
                                    'id_card_receive' => $row['id_card_receive'] ?? "",
                                    'appointment_letter_receive' => $row['appointment_letter'] ?? "",
                                    'BOND_NDA_signed_or_not' => $row['bond_nda_signed_or_not'] ?? "",
                                    'passport' => $row['passport'] ?? "",
                                    'DL' => $row['dl'] ?? "",
                                    'PF_no' => $row['pf_no'] ?? "",
                                    'recovery_amount_pending' => $row['recovery_amount_pending'] ?? "",
                                    'form11' => $row['form11'] ?? "",
                                    'antipochy_policy' => $row['antipochy_policy'] ?? "",
                                    'city' => $row['city'] ?? "",
                                    'assign_pc' => $row['assign_pc'] ?? "",
                                    'sallary' => $row['salary'] ?? "",
                                    'father_name' => $row['father_name'] ?? "",
                                    'mother_name' => $row['mother_name'] ?? "",
                                    'spouse_name' => $row['spouse_name'] ?? "",
                                    'no_of_childern' => $row['no_of_children'] ?? "",
                                    'documented_birthday' => $row['documented_birthday'] ?? "",
                                    'qualification' => $row['educational_qualification'] ?? "",
                                    'reporting_leader' => $row['reporting_leader'] ?? "",
                                    'aadhar_card_number' => $row['aadhar_card'] ?? "",
                                    'PAN_card_number' => $row['pan_card'] ?? "",
                                    'voter_id' => $row['voter_card'] ?? "",
                                    'account_name' => $row['account_bank_name'] ?? "",
                                    'account_number' => $row['account_number'] ?? "",
                                    'IFSC_code' => $row['ifsc_code'] ?? "",
                                    'remark' => $row['remarks'] ?? "",
                                    'PF_status' => $row['pf_status'] ?? "",
                                    'relieving_letter' => $row['relieveing_letter'] ?? "",
                                    'FNF' => $row['fnf'] ?? "",
                                    'UAN_no' => $row['uan_no'] ?? "",
                                    'assets' => $row['assests'] ?? "",
                                    'recovery' => $row['recovery'] ?? "",
                                    'salary_cycle' => $row['salary_cycle'] ?? "",
                                    'age_in_year' => $row['age_in_year'] ?? "",
                                    'department' => $row['department'] ?? "",
                                    'drug_policy' => $row['drug_policy'] ?? "",
                                    'transport_policy' => $row['transport_policy'] ?? "",
                                    'laptop_phone_policy' => $row['cell_phone_policy'] ?? "",
                                    'IJP_policy' => $row['ijp_policy'] ?? "",
                                    'appraisal_policy' => $row['appraisal_policy'] ?? "",
                                    'state' => $row['state'] ?? "",
                                ]);

                                if (isset($row['salary']) && !empty($row['salary'])) {
                                    if (isset($row['employee_pf_type'])) {
                                        $epfType = $row['employee_pf_type'] == 'Employee Deducted' ? 1 : ($row['employee_pf_type'] == 'Employer Pays Both' ? 2 : 3);
                                    } else {
                                        $epfType = 3; // Default if not set
                                    }
                                    DB::table('import_employees_salary_from_excels')->insert([
                                        'employee_id' => $subUser->id,
                                        'salary' => $row['salary'] ?? 0,
                                        'epf_type' => $epfType,
                                    ]);
                                }

                                if (!empty($row['correspondence_address'])) {
                                    ///$latLong = $this->getLatLongFromAddress($row['correspondence_address']);
                                    DB::table('sub_user_addresses')->insert([
                                        'sub_user_id'  => $subUser->id,
                                        'address' => $row['correspondence_address'] ?? "",
                                        'latitude' => null,
                                        'longitude' => null,
                                        'start_date' => now()->format('Y-m-d'),
                                        'created_at' => $created_at,
                                    ]);
                                }

                                if (!empty($row['reason'])) {
                                    DB::table('employee_separations')->insert([
                                        'user_id'  => $subUser->id,
                                        'last_working_date' => @$row['last_working_day'],
                                        'reason' => $row['reason'] ?? "",
                                        'description_of_reason' => $row['sub_reason'] ?? "",
                                        'remarks' => $row['remarks'] ?? "",
                                    ]);
                                }

                                if (!empty($row['assign_manager']) && $row['assign_manager'] == 'yes') {
                                    $managerRole = DB::table('hrms_roles')->where('specific_role_id',2)->first();
                                    if($managerRole){
                                        $role_id = $managerRole->id;
                                    }else {
                                        $role_id = DB::table('hrms_roles')->insertGetId([
                                            'name' => "Manager",
                                            'specific_role_id' => 2,
                                            'status' => 1,
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ]);
                                    }

                                    EmployeeTeamManager::Create([
                                        'team_manager_id' => $manager_group_id,
                                        'employee_id' => $subUser->id,
                                    ]);
                                }elseif (!empty($row['team_leader']) && $row['team_leader'] == 'yes') {
                                    $teamLeadRole = DB::table('hrms_roles')->where('specific_role_id',4)->first();
                                    if($teamLeadRole){
                                        $role_id = $teamLeadRole->id;
                                    }else {
                                        $role_id = DB::table('hrms_roles')->insertGetId([
                                            'name' => "Team Leader",
                                            'specific_role_id' => 4,
                                            'status' => 1,
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ]);
                                    }

                                    $team = HrmsTeam::find($team_id);
                                    if ($team) {
                                        $team->team_leader = $subUser->id;
                                        $team->save();
                                    }

                                }else {
                                    $memberRole = DB::table('hrms_roles')->where('specific_role_id',3)->first();
                                    if($memberRole){
                                        $role_id = $memberRole->id;
                                    }else {
                                        $role_id = DB::table('hrms_roles')->insertGetId([
                                            'name' => "Employee",
                                            'specific_role_id' => 3,
                                            'status' => 1,
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ]);
                                    }
                                }

                                HrmsEmployeeRole::updateOrCreate(
                                        ['employee_id' => $subUser->id],
                                        ['role_id' => $role_id, 'employee_id' => $subUser->id],
                                    );

                                // Assign the user to the team if they are not a manager or team leader
                                if ((empty($row['assign_manager']) || $row['assign_manager'] == 'no') && (empty($row['team_leader']) || $row['team_leader'] == 'no')) {
                                    if (isset($team_id) && !empty($team_id)) {
                                            DB::table('hrms_team_members')->insert([
                                                'member_id' => $subUser->id,
                                                'hrms_team_id' => $team_id
                                            ]);
                                    }
                                }

                                if (isset($manager_group_id) && !empty($manager_group_id)) {
                                        EmployeesUnderOfManager::updateOrCreate(
                                        ['manager_id' => $manager_group_id, 'employee_id' => $subUser->id],
                                        ['manager_id' => $manager_group_id, 'employee_id' => $subUser->id]
                                    );
                                }

                        }

                        // if (!empty($recordsToInsertInChildDB)) {

                        //         $this->connectDB($default_DB);
                        //         foreach ($recordsToInsertInChildDB as $key => $user) {
                        //                     $subUser = new SubUser();
                        //                     $subUser->id = $user['id'];
                        //                     $subUser->unique_id = $user['unique_id'];
                        //                     $subUser->doj = $user['doj'];///$date_of_joining;
                        //                     $subUser->status = $user['status'] ?? 1;
                        //                     $subUser->first_name = $user['first_name'] ?? "";
                        //                     $subUser->last_name = $user['last_name'] ?? "";
                        //                     $subUser->email = $user['email'];
                        //                     $subUser->password = $user['password'];
                        //                     $subUser->employement_type = $user['employement_type'] ?? "";
                        //                     $subUser->dob = $user['dob']; //$date_of_birth;
                        //                     $subUser->gender = $user['gender'] ?? "";
                        //                     $subUser->phone = $user['phone'] ?? "";
                        //                     $subUser->mobile = $user['mobile'] ?? "";
                        //                     $subUser->blood_group = $user['blood_group'] ?? "";
                        //                     $subUser->employee_shift = $user['employee_shift'] ?? "Morning Shift";
                        //                     $subUser->shift_type = $user['shift_type'] ??'0';
                        //                     $subUser->cab_facility = $user['cab_facility'];
                        //                     $subUser->marital_status = $user['marital_status'] ?? "";
                        //                     $subUser->database_path = $user['database_path'];//$this->data['database_path'];
                        //                     $subUser->database_name = $user['database_name'];
                        //                     $subUser->database_username = $user['database_username'];//$this->data['database_username'];
                        //                     $subUser->database_password = $user['database_password'];//$this->data['database_password'];
                        //                     $subUser->company_name = $user['company_name'];
                        //                     $subUser->created_at = $user['created_at'];
                        //                     $subUser->updated_at = $user['updated_at'];
                        //                     $getRole =  'carer';

                        //                     $admin = Role::where('name', 'staff')->first();
                        //                     if ($subUser->save()) {
                        //                         if (!$subUser->hasRole($getRole)) {
                        //                             $subUser->roles()->attach($admin);
                        //                         }
                        //                     }

                        //         }
                        // }
                        DB::disconnect();
                });
                $batch->status = 'done';
                $batch->save();

            } catch (\Throwable $e) {
                $batch->status = 'failed';
                $batch->save();
                \Log::error("Failed to process batch: {$batch->id}, Error: {$e->getMessage()}");
            }
        }


        $this->connectDB($default_DB);
        UpdateUserLatLong::dispatch($child_DB,$created_at);
        ProcessExcelUploadEmployeeSalary::dispatch($child_DB);

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

        // Config::set("database.connections.$db_name", $default);
        // Config::set("client_id", 1);
        // Config::set("client_connected", true);
        // DB::setDefaultConnection($db_name);
        // DB::purge($db_name);
         // Set new connection config
        Config::set("database.connections.$db_name", $default);
        DB::purge($db_name);
        DB::reconnect($db_name);
        DB::setDefaultConnection($db_name);
        Config::set("client_id", 1);
        Config::set("client_connected", true);
    }
}
