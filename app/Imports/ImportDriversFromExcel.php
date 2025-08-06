<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Models\SubUser ;
use App\Models\Vehicle;
use App\Models\Role;
use App\Models\SubUserAddresse;
use App\Jobs\ProcessDriverImportJob;

use Illuminate\Support\Str;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Config;
use Illuminate\Support\Facades\{DB, Mail, Hash};
class ImportDriversFromExcel implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;

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
        'first_name',
        'last_name',
        'email',
        'phone',
        'emergency_phone_number',
        'verified_by',
        'blood_group',
        'position',
        'pricebook_name',
        'address',
        'latitude',
        'longitude',
        'model',
        'chasis_no',
        'seats',
        'registration_no',
        'vehicle_no',
        'color',
        'shift_type',
        'profile_image',
        'vehicle_image',
    ];

    // public function chunkSize(): int
    // {
    //     return 100; // Read 200 rows at a time
    // }


     public function rules(): array{
        return [
            '*.first_name' => 'required|string',
            '*.last_name' => 'nullable|string',
            '*.email' => 'nullable|email|unique:users,email|email|unique:sub_users,email',
            '*.phone' => 'required|unique:sub_users,phone|digits:10',
            '*.emergency_phone_number' => 'nullable|unique:sub_users,mobile',
            '*.verified_by' => 'nullable',
            '*.blood_group' => 'nullable',
            '*.position' => 'required',
            '*.address' => 'required',
            '*.latitude' => 'nullable',
            '*.longitude' => 'nullable',
            '*.model' => 'required',
            '*.chasis_no' => 'required|unique:vehicles,chasis_no',
            '*.seats' => 'required',
            '*.registration_no' => 'required|unique:vehicles,registration_no',
            '*.vehicle_no' => 'required|unique:vehicles,vehicle_no',
            '*.color' => 'required',
            '*.profile_image' => 'nullable',
            '*.vehicle_image' => 'nullable',
            '*.shift_type' => 'required|in:Pick,Drop,Pick & Drop',
        ];
    }


    public function customValidationMessages()
    {
        return [
            '*.shift_type.in' => 'The shift type must be one of: Pick, Drop, Pick & Drop.',
        ];

    }

    public function prepareForValidation($row, $index)
    {
        // If sr_no is empty, return an empty array so it will be skipped
        if (empty($row['sr_no'])) {
            return [];
        }

        if (isset($row['email'])) {
            $row['email'] = trim($row['email']);
        }
        if (isset($row['phone'])) {
            $row['phone'] = trim($row['phone']);
        }
        if (isset($row['emergency_phone_number'])) {
            $row['emergency_phone_number'] = trim($row['emergency_phone_number']);
        }
        if (isset($row['chasis_no'])) {
            $row['chasis_no'] = trim($row['chasis_no']);
        }
        if (isset($row['registration_no'])) {
            $row['registration_no'] = trim($row['registration_no']);
        }
        if (isset($row['vehicle_no'])) {
            $row['vehicle_no'] = trim($row['vehicle_no']);
        }

        if (isset($row['shift_type'])) {
            $row['shift_type'] = Str::title(trim($row['shift_type'])); // Pick, Drop, Pick & Drop
        }
        return $row;
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

    public function collection(Collection $rows)
    {

        if ($rows->isEmpty()) {
                throw new \Exception('No data found in the Excel file. Please add at least one record and try again.');
        }

            $duplicateEmails = $rows->pluck('email')
                ->filter(function ($email) {
                    return !empty($email) && !in_array(strtolower(trim($email)), ['na', 'n/a']);
                })
                ->duplicates();

            $duplicateChasis_no = $rows->pluck('chasis_no')
            ->filter()
            ->duplicates();

            $duplicateRegistration_no = $rows->pluck('registration_no')
            ->filter()
            ->duplicates();

             $duplicateVehicle_no = $rows->pluck('vehicle_no')
            ->filter()
            ->duplicates();

            $duplicatePhone = $rows->pluck('phone')
            ->filter()
            ->duplicates();

            $duplicateEmergencyPhoneNumber = $rows->pluck('emergency_phone_number')
            ->filter()
            ->duplicates();

            $errors = [];

            if ($duplicateEmails->isNotEmpty()) {
                $errors[] = 'Duplicate emails found: ' . $duplicateEmails->implode(', ');
            }
            if ($duplicateChasis_no->isNotEmpty()) {
                $errors[] = 'Duplicate Chasis No. found: ' . $duplicateChasis_no->implode(', ');
            }
            if ($duplicateRegistration_no->isNotEmpty()) {
                $errors[] = 'Duplicate Registration No. found: ' . $duplicateRegistration_no->implode(', ');
            }
            if ($duplicateVehicle_no->isNotEmpty()) {
                $errors[] = 'Duplicate Vehicle No. found: ' . $duplicateVehicle_no->implode(', ');
            }
            if ($duplicatePhone->isNotEmpty()) {
                $errors[] = 'Duplicate Phone No. found: ' . $duplicatePhone->implode(', ');
            }
            if ($duplicateEmergencyPhoneNumber->isNotEmpty()) {
                $errors[] = 'Duplicate Emergency Phone No. found: ' . $duplicateEmergencyPhoneNumber->implode(', ');
            }

            $allEmails = $rows->pluck('email')->filter()->toArray();
            $existingEmails = SubUser::whereIn('email', $allEmails)->pluck('email')->toArray();
            // foreach ($rows as $key => $row) {
            //        if (!empty($row['email'])) {
            //             $existingDriver = SubUser::where('email', $row['email'])
            //                 //->where('company_name', $company_name)
            //                 ->whereNotNull('email')
            //                 ->where('email', '!=', '')
            //                 ->first();

            //             if ($existingDriver) {
            //                 $errors[] = "Alredy Exists this email ".$row['email'];
            //             }
            //         }
            // }


            if (!empty($errors)) {
                throw new \Exception(implode("\n", $errors));
            }

            $this->connectDB($this->data['default_db']);

            ProcessDriverImportJob::dispatch($rows, $this->data);

    }




}
