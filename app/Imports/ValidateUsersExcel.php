<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Validation\Validator;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Models\User;
use App\Models\SubUser;
use DB;

class ValidateUsersExcel implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows, WithChunkReading
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
         DB::setDefaultConnection($this->data['child_DB']);
         // Check headings if file is uploaded
        if (request()->hasFile('file')) {
            $headings = (new \Maatwebsite\Excel\HeadingRowImport)
                ->toArray(request()->file('file'))[0][0] ?? [];

            $invalid = [];
            foreach ($headings as $heading) {
                $heading_key = strtolower(str_replace(' ', '_', trim($heading)));
                // Ignore blank or purely numeric headings (like "70", "71")
                if ($heading_key === '' || is_numeric($heading_key)) {
                    continue;
                }
                if (!in_array($heading_key, $this->allowedHeadings)) {
                    $invalid[] = $heading;
                }
            }

            if (count($invalid)) {
                throw new \Exception('Invalid headings found in Excel: ' . implode(', ', $invalid));
            }
        }
    }




     protected $allowedHeadings = [
        'sr_no',
       // 'employee_name',
        'first_name',
        'last_name',
        'official_email_id',
        'personal_email_id',
        'new_emp_code',
        'emp_code',
        'phone_number',
        'emergency_phone_number',
        'gender',
        'blood_group',
        'marital_status',
        'current_status',
        'id_card_receive',
        'designation',
        'employee_shift',
        'shift_finishs_next_day',
        'shift_login_time',
        'shift_logout_time',
        'do_you_need_cab',
        'correspondence_address',
        'latitude',
        'longitude',
        'manager_group_name',
        'assign_manager', //
        'team_leader', //
        'team_name', //
        'doj',
        'documented_birthday',
        'last_working_day',
        'actual_birthday',
        'permanent_address_as_per_adhar',
        'country',
        'official_name',
        'appointment_letter',
        'bond_nda_signed_or_not',
        'passport',
        'dl',
        'pf_no',
        'recovery_amount_pending',
        'form11',
        'antipochy_policy',
        'city',
        'assign_pc',
        'salary',
        'employee_pf_type',
        'father_name',
        'mother_name',
        'spouse_name',
        'no_of_children',
        'educational_qualification',
        'reporting_leader',
        'aadhar_card',
        'pan_card',
        'voter_card',
        'account_bank_name',
        'account_number',
        'ifsc_code',
        'remarks',
        'pf_status',
        'relieveing_letter',
        'fnf',
        'uan_no',
        'assests',
        'recovery',
        'salary_cycle',
        'age_in_year',
        'department',
        'drug_policy',
        'transport_policy',
        'cell_phone_policy',
        'appraisal_policy',
        'ijp_policy',
        'lob',
        'state',
        'reason',
        'sub_reason',
        'id_cards',
        'holiday',
        'holiday_name',
        'holiday_description',

    ];




      public function rules(): array{
        return [
            '*.first_name' => 'required|string',
            '*.last_name' => 'nullable|string',
            '*.official_email_id' => 'nullable|unique:users,email',
            '*.personal_email_id' => 'nullable|email',
            '*.new_emp_code' => 'required|unique:users,unique_id|unique:sub_users,unique_id',
            '*.phone_number' => 'nullable',
            '*.emergency_phone_number' => 'nullable',
            '*.gender' => 'required|in:Male,M,Female,F,m,f,Other',
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


    public function customValidationMessages()
    {
        return [
            '*.current_status.in' => '(current_status) The current status must be one of: Active, Inactive, Resigned, OnNoticePeriod, Suspended, Terminated, Abscond.',
            '*.marital_status.in' => '(marital_status) The marital status must be one of: Single, Married, Divorced, Widowed, Separated, Engaged, In a Relationship, Domestic Partnership / Civil Union, Prefer Not to Say.',
            '*.do_you_need_cab.in' => '(do_you_need_cab) The Cab Facility must be one of:yes, no',
            '*.id_card_receive.in' => '(id_card_receive) The ID Card must be one of:yes, no',
            '*.gender.in' => 'The gender must be one of:Male, Female, Other',
            '*.shift_login_time.date_format' => 'Shift login time must be in 24-hour format (HH:MM).',
            '*.shift_logout_time.date_format' => '(shift_logout_time) Shift logout time must be in 24-hour format (HH:MM).',
            '*.shift_finishs_next_day.in' => '(shift_finishs_next_day) The Shift Finishs Next Day must be one of: yes, no',
            '*.assign_manager.in' => '(assign_manager) The Assign Manager must be one of: yes, no',
            '*.team_leader.in' => '(team_leader) The Team Leader must be one of: yes, no',
            // You can add more custom messages for other fields if needed
        ];
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
             foreach ($validator->getData() as $index => $row) {
                if (!empty($row['team_name']) && empty($row['manager_group_name'])) {
                    $validator->errors()->add("{$index}.manager_group_name", "Manager group name is required when team name is filled. This helps to identify which manager is responsible for the team.");
                }
                if (isset($row['assign_manager']) && $row['assign_manager'] == 'yes' && empty($row['manager_group_name'])) {
                    $validator->errors()->add("{$index}.manager_group_name", "Since 'Assign Manager' is set to 'yes', the Manager Group Name is required to specify which manager group the user will be assigned to.");
                }
                if (isset($row['team_leader']) && $row['team_leader'] == 'yes' && empty($row['team_name'])) {
                    $validator->errors()->add("{$index}.team_name", "Team name is required when 'Team Leader' is set to 'yes'. This ensures the team leader is linked to a specific team.");
                }
                // Salary & PF type validation
                if (!empty($row['salary']) && empty($row['employee_pf_type'])) {
                    $validator->errors()->add("{$index}.employee_pf_type", "Employee PF Type is required when salary is filled.");
                }
                if (isset($row['assign_manager']) && isset($row['team_leader']) && $row['assign_manager'] == 'yes' && $row['team_leader'] == 'yes') {
                       // $errors[] = 'Only one role can be assigned (Manager OR Team Leader) (Row: ' . ($index + 2) . ')';
                       $validator->errors()->add("{$index}.assign_manager", "Only one role can be assigned (Manager OR Team Leader) (Row: ' . ($index) . ')");
                }
            }
        });
    }



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



    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw new \Exception('No data found in the Excel file. Please add at least one record and try again.');
        }

    //$rows = collect($rows->toArray());    22/07/25

    // Filter out NA or N/A and then find duplicates
    $duplicateEmails = $rows->pluck('official_email_id')
        ->filter(function ($email) {
            return !empty($email) && !in_array(strtolower(trim($email)), ['na', 'n/a']);
        })
        ->duplicates();

    $duplicateUserIds = $rows->pluck('new_emp_code')
        ->filter()
        ->duplicates();

    $errors = [];

    if ($duplicateEmails->isNotEmpty()) {
        $errors[] = 'Duplicate emails found: ' . $duplicateEmails->implode(', ');
    }

    if ($duplicateUserIds->isNotEmpty()) {
        $errors[] = 'Duplicate New Emp Code: ' . $duplicateUserIds->implode(', ');
    }
    if (!empty($errors)) {
        throw new \Exception(implode("\n", $errors));
    }

        $allEmails = collect($rows)->pluck('official_email_id')->filter()->unique()->toArray();
        $allEmpCodes = collect($rows)->pluck('new_emp_code')->filter()->unique()->toArray();
        $existingEmailsSubUser = SubUser::whereIn('email', $allEmails)->pluck('email')->toArray();
        $existingEmailsUser = User::whereIn('email', $allEmails)->pluck('email')->toArray();
        $existingEmails = array_unique(array_merge($existingEmailsSubUser, $existingEmailsUser));

        $existingEmpCodesSubUser = SubUser::whereIn('unique_id', $allEmpCodes)->pluck('unique_id')->toArray();
        $existingEmpCodesUser = User::whereIn('unique_id', $allEmpCodes)->pluck('unique_id')->toArray();
        $existingEmpCode = array_unique(array_merge($existingEmpCodesSubUser, $existingEmpCodesUser));
        // validate the rows
        foreach ($rows as $index => $row) {
                if (isset($row['sr_no']) && !empty($row['sr_no'])) {
                    // if (!isset($row['official_email_id']) || empty($row['official_email_id'])) {
                    //     $errors[] = 'Official Email ID is required (Row: ' . ($index + 2) . ')';
                    // }
                    if (!isset($row['current_status']) || empty($row['current_status'])) {
                        $errors[] = 'Current Status is required (Row: ' . ($index + 2) . ')';
                    }
                    if (!isset($row['first_name']) || empty($row['first_name'])) {
                        $errors[] = 'First Name is required (Row: ' . ($index + 2) . ')';
                    }
                    if (!isset($row['do_you_need_cab']) || empty($row['do_you_need_cab'])) {
                        $errors[] = 'Do You Need Cab Facility is required (Row: ' . ($index + 2) . ')';
                    }
                    if (isset($row['assign_manager']) && isset($row['team_leader']) && $row['assign_manager'] == 'yes' && $row['team_leader'] == 'yes') {
                        $errors[] = 'Only one role can be assigned (Manager OR Team Leader) (Row: ' . ($index + 2) . ')';
                    }
                    if (!isset($row['designation']) || empty($row['designation'])) {
                        $errors[] = 'Designation is required (Row: ' . ($index + 2) . ')';
                    }
                    if (!isset($row['new_emp_code']) || empty($row['new_emp_code'])) {
                        $errors[] = 'New Employee Code is required (Row: ' . ($index + 2) . ')';
                    }

                     $email = $row['official_email_id'] ?? null;
                     if (!empty($email)) {
                            if (in_array($email, $existingEmails)) {
                                info('alredy email exist');
                                $errors[] = "$email Email Id is Alredy Exist (Row: ' . ($index + 2) . ')";
                            }
                     }
                    // if ($email && SubUser::where('email', $email)->exists()) {
                    //    $errors[] = 'This Email Id is Alredy Exist (Row: ' . ($index + 2) . ')';
                    // }
                    // if ($email && User::where('email', $email)->exists()) {
                    //     $errors[] = 'This Email Id is Alredy Exist (Row: ' . ($index + 2) . ')';
                    // }

                    $unique_id = $row['new_emp_code'];
                    if (!empty($unique_id)) {
                            if (in_array($unique_id, $existingEmpCode)) {
                                info('alredy Employee Code exist');
                                 $errors[] = "$unique_id Employee Code is Alredy Exist (Row: ' . ($index + 2) . ')";
                            }
                    }
                    // if (!empty($unique_id)) {
                    //     $existUniqueId = SubUser::where('unique_id', $unique_id)->exists();
                    //     $existSubUniqueId = User::where('unique_id', $unique_id)->exists();
                    //     if ($existUniqueId || $existSubUniqueId) {
                    //        $errors[] = 'This Employee Code is Alredy Exist (Row: ' . ($index + 2) . ')';
                    //     }
                    // }

                }

        }

        if (!empty($errors)) {
                    throw new \Exception(implode("\n", $errors));
        }
    }

    public function chunkSize(): int
    {
        return 200; // Read 200 rows at a time
    }
}
