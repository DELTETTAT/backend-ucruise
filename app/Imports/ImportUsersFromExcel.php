<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
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
use DB;
use Hash;
use Config;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportUsersFromExcel implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows, WithChunkReading
{
    /**
     * @param Collection $collection
     */

    protected $data;
    protected $errors = [];
    protected $validRows = [];

    public function __construct(array $data)
    {
        $this->data = $data;

         // Check headings if file is uploaded
        // if (request()->hasFile('file')) {
        //     $headings = (new \Maatwebsite\Excel\HeadingRowImport)
        //         ->toArray(request()->file('file'))[0][0] ?? [];

        //     $invalid = [];
        //     foreach ($headings as $heading) {
        //         $heading_key = strtolower(str_replace(' ', '_', trim($heading)));
        //         // Ignore blank or purely numeric headings (like "70", "71")
        //         if ($heading_key === '' || is_numeric($heading_key)) {
        //             continue;
        //         }
        //         if (!in_array($heading_key, $this->allowedHeadings)) {
        //             $invalid[] = $heading;
        //         }
        //     }

        //     if (count($invalid)) {
        //         throw new \Exception('Invalid headings found in Excel: ' . implode(', ', $invalid));
        //     }
        // }
    }



    // protected $allowedHeadings = [
    //     'sr_no',
    //    // 'employee_name',
    //     'first_name',
    //     'last_name',
    //     'official_email_id',
    //     'personal_email_id',
    //     'new_emp_code',
    //     'emp_code',
    //     'phone_number',
    //     'emergency_phone_number',
    //     'gender',
    //     'blood_group',
    //     'marital_status',
    //     'current_status',
    //     'id_card_receive',
    //     'designation',
    //     'employee_shift',
    //     'shift_finishs_next_day',
    //     'shift_login_time',
    //     'shift_logout_time',
    //     'do_you_need_cab',
    //     'correspondence_address',
    //     'latitude',
    //     'longitude',
    //     'manager_group_name',
    //     'assign_manager', //
    //     'team_leader', //
    //     'team_name', //
    //     'doj',
    //     'documented_birthday',
    //     'last_working_day',
    //     'actual_birthday',
    //     'permanent_address_as_per_adhar',
    //     'country',
    //     'official_name',
    //     'appointment_letter',
    //     'bond_nda_signed_or_not',
    //     'passport',
    //     'dl',
    //     'pf_no',
    //     'recovery_amount_pending',
    //     'form11',
    //     'antipochy_policy',
    //     'city',
    //     'assign_pc',
    //     'salary',
    //     'employee_pf_type',
    //     'father_name',
    //     'mother_name',
    //     'spouse_name',
    //     'no_of_children',
    //     'educational_qualification',
    //     'reporting_leader',
    //     'aadhar_card',
    //     'pan_card',
    //     'voter_card',
    //     'account_bank_name',
    //     'account_number',
    //     'ifsc_code',
    //     'remarks',
    //     'pf_status',
    //     'relieveing_letter',
    //     'fnf',
    //     'uan_no',
    //     'assests',
    //     'recovery',
    //     'salary_cycle',
    //     'age_in_year',
    //     'department',
    //     'drug_policy',
    //     'transport_policy',
    //     'cell_phone_policy',
    //     'appraisal_policy',
    //     'ijp_policy',
    //     'lob',
    //     'state',
    //     'reason',
    //     'sub_reason',
    //     'id_cards',
    //     'holiday',
    //     'holiday_name',
    //     'holiday_description',

    // ];



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



    public function rules(): array{
        return [
            '*.first_name' => 'required|string',
            '*.last_name' => 'nullable|string',
            '*.official_email_id' => 'nullable|unique:users,email',
            '*.personal_email_id' => 'nullable|email',
            '*.new_emp_code' => 'nullable|unique:users,unique_id|unique:sub_users,unique_id',
            '*.phone_number' => 'nullable',
            '*.emergency_phone_number' => 'nullable',
            '*.gender' => 'nullable|in:Male,M,Female,F,m,f,Other',
            '*.blood_group' => 'nullable',
            '*.marital_status' => 'nullable|in:Single,Married,Divorced,Widowed,Separated,Engaged,In a Relationship,Domestic Partnership / Civil Union,Prefer Not to Say',
            '*.current_status' => 'required|in:Active,Inactive,Resigned,OnNoticePeriod,Suspended,Terminated,Abscond',
            '*.id_card_receive' => 'nullable|in:yes,no',
            '*.designation' => 'required',
            '*.employee_shift' => 'required',
            '*.shift_finishs_next_day' => 'nullable|in:yes,no',
            '*.shift_login_time' => 'nullable',
            '*.shift_logout_time' => 'nullable',
            '*.do_you_need_cab' => 'required|in:yes,no',
            '*.correspondence_address' => 'required_if:*.do_you_need_cab,yes',
            //'*.latitude' => 'required_if:*.do_you_need_cab,yes',
            //'*.longitude' => 'required_if:*.do_you_need_cab,yes',
            '*.manager_group_name' => 'nullable',
            '*.assign_manager' => 'nullable|in:yes,no',
            '*.team_leader' => 'nullable|in:yes,no',
            '*.team_name' => 'nullable',
            '*.salary' => 'nullable',
            '*.employee_pf_type' => 'nullable',
        ];
    }


    // public function customValidationMessages()
    // {
    //     return [
    //         '*.current_status.in' => 'The current status must be one of: Active, Inactive, Resigned, OnNoticePeriod, Suspended, Terminated, Abscond.',
    //         '*.marital_status.in' => 'The marital status must be one of: Single, Married, Divorced, Widowed, Separated, Engaged, In a Relationship, Domestic Partnership / Civil Union, Prefer Not to Say.',
    //         '*.do_you_need_cab.in' => 'The Cab Facility must be one of:yes, no',
    //         '*.id_card_receive.in' => 'The ID Card must be one of:yes, no',
    //         '*.gender.in' => 'The gender must be one of:Male, M, Female, F, Other',
    //         '*.shift_login_time.date_format' => 'Shift login time must be in 24-hour format (HH:MM).',
    //         '*.shift_logout_time.date_format' => 'Shift logout time must be in 24-hour format (HH:MM).',
    //         '*.shift_finishs_next_day.in' => 'The Shift Finishs Next Day must be one of: yes, no',
    //         '*.assign_manager.in' => 'The Assign Manager must be one of: yes, no',
    //         // You can add more custom messages for other fields if needed
    //     ];
    // }


    // public function withValidator($validator)
    // {
    //     $validator->after(function ($validator) {
    //          foreach ($validator->getData() as $index => $row) {
    //             if (!empty($row['team_name']) && empty($row['manager_group_name'])) {
    //                 $validator->errors()->add("{$index}.manager_group_name", "Manager group name is required when team name is filled. This helps to identify which manager is responsible for the team.");
    //             }
    //             if (isset($row['assign_manager']) && $row['assign_manager'] == 'yes' && empty($row['manager_group_name'])) {
    //                 $validator->errors()->add("{$index}.manager_group_name", "Since 'Assign Manager' is set to 'yes', the Manager Group Name is required to specify which manager group the user will be assigned to.");
    //             }
    //             if (isset($row['team_leader']) && $row['team_leader'] == 'yes' && empty($row['team_name'])) {
    //                 $validator->errors()->add("{$index}.team_name", "Team name is required when 'Team Leader' is set to 'yes'. This ensures the team leader is linked to a specific team.");
    //             }
    //             // Salary & PF type validation
    //             if (!empty($row['salary']) && empty($row['employee_pf_type'])) {
    //                 $validator->errors()->add("{$index}.employee_pf_type", "Employee PF Type is required when salary is filled.");
    //             }
    //         }
    //     });
    // }



    public function prepareForValidation($row, $index)
    {

        if (!isset($row['sr_no']) || trim($row['sr_no']) === '') {
            return [];
        }
        //return $row;


        // Trim marital_status and other fields if needed
        if (isset($row['marital_status'])) {
            $row['marital_status'] = ucfirst(strtolower(trim($row['marital_status'])));
        }
        if (isset($row['first_name'])) {
            $row['first_name'] = ucfirst(strtolower(trim($row['first_name'])));
        }
        if (isset($row['current_status'])) {
            $row['current_status'] = ucfirst(strtolower(trim($row['current_status'])));
        }
        if (isset($row['gender'])) {
            $row['gender'] = ucfirst(strtolower(trim($row['gender'])));
        }
        if (isset($row['do_you_need_cab'])) {
            $row['do_you_need_cab'] = strtolower(trim($row['do_you_need_cab']));
        }
        if (isset($row['id_card_receive'])) {
            $row['id_card_receive'] = strtolower(trim($row['id_card_receive']));
        }
        if (isset($row['shift_finishs_next_day'])) {
            $row['shift_finishs_next_day'] = strtolower(trim($row['shift_finishs_next_day']));
        }

        if (isset($row['team_leader'])) {
            $row['team_leader'] = strtolower(trim($row['team_leader']));
        }
        if (isset($row['assign_manager'])) {
            $row['assign_manager'] = strtolower(trim($row['assign_manager']));
        }


        return $row;
    }

    // *** Convert Excel time to string format (HH:MM:SS) ** //
    function excelTimeToString($excelTime) {
        if (is_numeric($excelTime)) {
            $seconds = round($excelTime * 24 * 60 * 60);
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return sprintf('%02d:%02d', $hours, $minutes);
        }
        // Already string, just ensure format
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $excelTime)) {
            $parts = explode(':', $excelTime);
            return sprintf('%02d:%02d', $parts[0], $parts[1]);
        }
        return '00:00'; // fallback
    }

    // function getLatLongFromAddress($address)
    // {
    //     $apiKey = env("GOOGLE_API_KEY");

    //     $address = urlencode($address);
    //     $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$apiKey}";

    //     $resp_json = file_get_contents($url);
    //     $resp = json_decode($resp_json, true);

    //     if ($resp['status'] == 'OK') {
    //         $lat = $resp['results'][0]['geometry']['location']['lat'];
    //         $lng = $resp['results'][0]['geometry']['location']['lng'];
    //         return ['lat' => $lat, 'lng' => $lng];
    //     }
    //     return ['lat' => null, 'lng' => null];
    // }



    public function collection(Collection $rows)
    {

        // if ($rows->isEmpty()) {
        //     throw new \Exception('No data found in the Excel file. Please add at least one record and try again.');
        // }

        // $rows = collect($rows->toArray());
        // $duplicateEmails = $rows->pluck('official_email_id')
        // ->filter()
        // ->duplicates();

        // $duplicateUserIds = $rows->pluck('new_emp_code')
        //     ->filter()
        //     ->duplicates();

        // $errors = [];

        // if ($duplicateEmails->isNotEmpty()) {
        //     $errors[] = 'Duplicate emails found: ' . $duplicateEmails->implode(', ');
        // }

        // if ($duplicateUserIds->isNotEmpty()) {
        //     $errors[] = 'Duplicate New Emp Code: ' . $duplicateUserIds->implode(', ');
        // }

        // if (!empty($errors)) {
        //     throw new \Exception(implode("\n", $errors));
        // }

        // validate the rows
        foreach ($rows as $index => $row) {

                if (isset($row['sr_no']) && !empty($row['sr_no'])) {
                    // if (!isset($row['official_email_id']) || empty($row['official_email_id'])) {
                    //     $errors[] = 'Official Email ID is required (Row: ' . ($index + 2) . ')';
                    // }
                    // if (!isset($row['current_status']) || empty($row['current_status'])) {
                    //     $errors[] = 'Current Status is required (Row: ' . ($index + 2) . ')';
                    // }
                    // if (!isset($row['first_name']) || empty($row['first_name'])) {
                    //     $errors[] = 'First Name is required (Row: ' . ($index + 2) . ')';
                    // }
                    // if (!isset($row['do_you_need_cab']) || empty($row['do_you_need_cab'])) {
                    //     $errors[] = 'Do You Need Cab Facility is required (Row: ' . ($index + 2) . ')';
                    // }
                    if (isset($row['assign_manager']) && isset($row['team_leader']) && $row['assign_manager'] == 'yes' && $row['team_leader'] == 'yes') {
                        $errors[] = 'Only one role can be assigned (Manager OR Team Leader) (Row: ' . ($index + 2) . ')';
                    }
                    // if (!isset($row['designation']) || empty($row['designation'])) {
                    //     $errors[] = 'Designation is required (Row: ' . ($index + 2) . ')';
                    // }
                    // if (!isset($row['employee_shift']) || empty($row['employee_shift'])) {
                    //     $errors[] = 'Employee Shift is required (Row: ' . ($index + 2) . ')';
                    // }

                }

                if (!empty($errors)) {
                    throw new \Exception(implode("\n", $errors));
                }



        }

        $password = Hash::make('unify@123');

        if(empty($errors)){
            foreach ($rows as $index =>  $row) {
                $status = 1;
                // if (!empty($row['current_status'])) {
                //     if ($row['current_status'] == 'Active') {
                //         $status = 1;
                //     }elseif ($row['current_status'] == 'Inactive') {
                //         $status = 2;
                //     }elseif ($row['current_status'] == 'Resigned') {
                //     $status = 3;
                //     }elseif ($row['current_status'] == 'OnNoticePeriod') {
                //     $status = 4;
                //     }elseif ($row['current_status'] == 'Suspended') {
                //     $status = 5;
                //     }elseif ($row['current_status'] == 'Terminated') {
                //     $status = 6;
                //     }elseif ($row['current_status'] == 'Abscond') {
                //     $status = 8;
                //     }
                // }else {
                //     $status = 2;
                // }
                //  handle status
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

                // handle Gender
                if (!empty($row['gender'])) {
                    $firstChar = strtolower(trim($row['gender']))[0] ?? '';
                    if ($firstChar === 'm') {
                        $row['gender'] = 'Male';
                    } elseif ($firstChar === 'f') {
                        $row['gender'] = 'Female';
                    }else {
                        $row['gender'] = 'Other';
                    }
                }else {
                    $row['gender'] = null;
                }

                if (empty($row['sr_no'])) {
                    continue;
                }

                $this->connectDB($this->data['default_db']);

                //$email = $row['official_email_id'] ?? null;
                $email = isset($row['official_email_id']) && filter_var($row['official_email_id'], FILTER_VALIDATE_EMAIL) ? $row['official_email_id'] : null;
                $unique_id = null;
                if (!empty($row['new_emp_code'])) {
                    $unique_id = $row['new_emp_code'];
                }elseif (!empty($row['emp_code'])) {
                    $unique_id = $row['emp_code'];
                }

                $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y'];

                $date_of_joining = $row['doj'] ?? now()->format('Y-m-d');
                $documented_birthday = $row['documented_birthday'] ?? null;
                $last_working_day = $row['last_working_day'] ?? null;
                $date_of_birth = $row['actual_birthday'] ?? null;

                $fields = [
                    'doj' => &$date_of_joining,
                    'documented_birthday' => &$documented_birthday,
                    'last_working_day' => &$last_working_day,
                    'actual_birthday' => &$date_of_birth,
                ];

                foreach ($fields as $key => &$value) {
                    $original = $row[$key] ?? null;
                    if ($original === null) continue;

                    try {
                        // If it's numeric, it's likely an Excel serial date
                        if (is_numeric($original)) {
                            $value = Carbon::instance(ExcelDate::excelToDateTimeObject($original))->format('Y-m-d');
                        } else {
                            foreach ($formats as $format) {
                                try {
                                    $value = Carbon::createFromFormat($format, $original)->format('Y-m-d');
                                    break;
                                } catch (\Exception $e) {
                                    continue;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        $value = null; // fallback if all parsing fails
                    }
                }


                // if (isset($row['do_you_need_cab'])) {
                //     if ($row['do_you_need_cab'] == 'yes') {
                //         $cabfacility = 1;
                //     } elseif ($row['do_you_need_cab'] == 'no') {
                //         $cabfacility = 0;
                //     } else {
                //         $cabfacility = 0; // Default value if not specified
                //     }
                // }else {
                //     $cabfacility = 0; // Default value if not specified
                // }
                $cabfacility = 0;
                if (!empty($row['do_you_need_cab'])) {
                    $normalizedCab = strtolower(trim($row['do_you_need_cab']));
                    if (in_array($normalizedCab, ['yes', 'y'])) {
                            $cabfacility = 1;
                    }
                }



                // if (SubUser::where('unique_id', $unique_id)->exists()) {
                //     $unique_id = "ULT$index";
                // }


                $subUser = new SubUser();
                $subUser->unique_id = $unique_id;
                $subUser->doj = $date_of_joining;
                $subUser->status = $status ?? 1;
                $subUser->first_name = $row['first_name'] ?? "";
                $subUser->last_name = $row['last_name'] ?? "";
                $subUser->email = $email;
                $subUser->password = $password;
                $subUser->employement_type = $row['designation'] ?? "";
                $subUser->dob = $date_of_birth;
                $subUser->gender = $row['gender'] ?? "";
                $subUser->phone = $row['phone_number'] ?? "";
                $subUser->mobile = $row['emergency_phone_number'] ?? "";
                $subUser->blood_group = $row['blood_group'] ?? "";
                $subUser->employee_shift = $row['employee_shift'] ?? "Morning Shift";
                $subUser->shift_type = '0';
                $subUser->cab_facility = $cabfacility;
                $subUser->marital_status = $row['marital_status'] ?? "";
                $subUser->database_path = $this->data['database_path'];
                $subUser->database_name = $this->data['child_DB'];
                $subUser->database_username = $this->data['database_username'];
                $subUser->database_password = $this->data['database_password'];
                $subUser->company_name = $this->data['company_name'];
                $getRole =  'carer';
                // $subUser->save();


                $admin = Role::where('name', 'staff')->first();
                if ($subUser->save()) {
                    if (!$subUser->hasRole($getRole)) {
                        $subUser->roles()->attach($admin);
                    }
                }


                $this->connectDB($this->data['child_DB']);

                // **********************  Check if the system setup is already done ********************************* //
                if (isset($row['designation']) && !empty($row['designation'])) {
                    $existsDesignation = Designation::where('title', $row['designation'])->first();
                    if ($existsDesignation) {
                        $row['designation'] = $existsDesignation->title;
                    }else {
                        $designation = Designation::create(['title' => $row['designation'], 'status' => 1]);
                        $row['designation'] = $designation->title;
                    }
                }

                // ***  employee shift *** //
                if(isset($row['employee_shift']) && !empty($row['employee_shift'])) {
                    $shift = HrmsTimeAndShift::where('shift_name', $row['employee_shift'])->first();

                    if ($shift) {
                        $employee_shift = $shift->shift_name;
                        $shift_type = $shift->shift_finishs_next_day ?? '';
                    } else {
                        $loginTime = $this->excelTimeToString($row['shift_login_time'] ?? '');
                        $logoutTime = $this->excelTimeToString($row['shift_logout_time'] ?? '');

                            if ($row['shift_finishs_next_day'] == 'yes') {
                                $row['shift_finishs_next_day'] = 1;
                            }else {
                                $row['shift_finishs_next_day'] = 0;
                            }

                        $shift =  HrmsTimeAndShift::create([
                                        'shift_name' => $row['employee_shift'],
                                        'shift_finishs_next_day' => $row['shift_finishs_next_day'] ?? 0,
                                        'shift_time' => [
                                            'start' => $loginTime ?? '10:00',
                                            'end'   => $logoutTime ?? '19:00',
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
                                    ]);
                        $employee_shift = $shift ? $shift->shift_name : "Morning Shift";
                        $shift_type = $shift->shift_finishs_next_day ?? 0;
                    }
                } else {
                    $shift = HrmsTimeAndShift::first();
                    if ($shift) {
                        $employee_shift = $shift->shift_name;
                        $shift_type = $shift->shift_finishs_next_day ?? 0;
                    }else {
                        $employee_shift = "Morning Shift";
                        $shift_type = 0;
                    }
                }


                // ***  manager group  *** //
                if (isset($row['manager_group_name']) && !empty($row['manager_group_name'])) {
                    $managerGroup = TeamManager::where('name', $row['manager_group_name'])->first();
                    if ($managerGroup) {
                        $manager_group_id = $managerGroup->id;
                    } else {
                        $managerGroup = TeamManager::create(['name' => $row['manager_group_name']]);
                        $manager_group_id = $managerGroup->id;
                    }
                }else {
                    $manager_group_id = null;
                }


                // ***  team group  *** //
                $team_id = null;
                if (isset($row['team_name']) && !empty($row['team_name'])) {
                    $team = HrmsTeam::where('team_name', $row['team_name'])->first();
                    if ($team) {
                        $team_id = $team->id;
                    } else {
                        $team = HrmsTeam::create(['team_name' => $row['team_name'], 'team_manager_id' => $manager_group_id]);
                        $team_id = $team->id;
                    }
                }

                // **********************  Check if the system setup is already done End********************************* //


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
                if (User::where('unique_id', $unique_id)->exists()) {
                    $unique_id = null;
                }
                if (SubUser::where('unique_id', $unique_id)->exists()) {
                    $unique_id = null;
                }
                // if (!empty($row['correspondence_address'])) {
                //     $latLong = $this->getLatLongFromAddress($row['correspondence_address']);
                //     $latitude = $row['latitude'] ?? $latLong['lat'];
                //     $longitude = $row['longitude'] ?? $latLong['lng'];
                //  }else {
                //      $latitude = $row['latitude'];
                //      $longitude = $row['longitude'];
                //  }
               $latitude =  null;
               $longitude =  null;


                $childUser = new User();
                $childUser->id = $subUser->id;
                $childUser->unique_id = $unique_id;
                $childUser->doj = $date_of_joining;
                $childUser->status = $status ?? 1;
                $childUser->password = $password;
                $childUser->address = $row['permanent_address_as_per_adhar'] ?? "";
                $childUser->first_name = $row['first_name'] ?? "";
                $childUser->last_name = $row['last_name'] ?? "";
                $childUser->email = $user_email;
                $childUser->employement_type = $row['designation'] ?? "";
                $childUser->dob = $date_of_birth;
                $childUser->gender = $row['gender'] ?? "";
                $childUser->phone = $row['phone_number'] ?? "";
                $childUser->mobile = $row['emergency_phone_number'] ?? "";
                $childUser->blood_group = $row['blood_group'] ?? "";
                $childUser->employee_shift = $employee_shift;
                $childUser->shift_type = $shift_type ?? 0;
                $childUser->cab_facility = $subUser->cab_facility;
                $childUser->latitude = $latitude ?? null;
                $childUser->longitude = $longitude?? null;
                $childUser->marital_status = $row['marital_status'] ?? "";
                $childUser->database_path = $this->data['database_path'];
                $childUser->database_name = $this->data['child_DB'];
                $childUser->database_username = $this->data['database_username'];
                $childUser->database_password = $this->data['database_password'];
                $childUser->company_name = $this->data['company_name'];


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
                $childSubUser->doj = $date_of_joining;
                $childSubUser->status = $status ?? 1;
                $childSubUser->password = $password;
                $childSubUser->first_name = $row['first_name'] ?? "";
                $childSubUser->last_name = $row['last_name'] ?? "";
                $childSubUser->email = $sub_user_email;
                $childSubUser->employement_type = $row['designation'] ?? "";
                $childSubUser->dob = $date_of_birth;
                $childSubUser->gender = $row['gender'] ?? "";
                $childSubUser->phone = $row['phone_number'] ?? "";
                $childSubUser->mobile = $row['emergency_phone_number'] ?? "";
                $childSubUser->blood_group = $row['blood_group'] ?? "";
                $childSubUser->employee_shift = $employee_shift;
                $childSubUser->shift_type = $shift_type ?? 0;
                $childSubUser->cab_facility = $cabfacility;
                $childSubUser->marital_status = $row['marital_status'] ?? "";
                $childSubUser->database_path = $this->data['database_path'];
                $childSubUser->database_name = $this->data['child_DB'];
                $childSubUser->database_username = $this->data['database_username'];
                $childSubUser->database_password = $this->data['database_password'];
                $childSubUser->company_name = $this->data['company_name'];
                //$childSubUser->save();


                $getRole = 'carer';
                $admin = Role::where('name', $getRole)->first();

                if ($childSubUser->save()) {
                    if (!$childSubUser->hasRole($getRole)) {
                        $childSubUser->roles()->attach($admin);
                    }
                }

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
                    'documented_birthday' => $documented_birthday,
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
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'start_date' => now()->format('Y-m-d'),
                ]);
                 }

                if (!empty($row['reason'])) {
                    DB::table('employee_separations')->insert([
                        'user_id'  => $subUser->id,
                        'last_working_date' => $last_working_day,
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
                    //$role_id = 3;
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
                    //$role_id = 4;
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

        }
    }

    public function chunkSize(): int
    {
        return 200; // Read 200 rows at a time
    }
}
