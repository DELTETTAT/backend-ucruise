<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Models\SubUser ;
use App\Models\Vehicle;
use App\Models\Role;
use App\Models\SubUserAddresse;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Jobs\UpdateUserLatLong;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Config;
use Illuminate\Support\Facades\{DB, Mail, Hash};

class ProcessDriverImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $rows;
    protected $data;
    public function __construct($rows,$data)
    {
        $this->rows = $rows;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */

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


    public function handle()
    {
        $role = Role::where('name', 'driver')->first();
        $hashedPassword  = Hash::make('unify@123');
        $company_name = $this->data['company_name'];
        $default_DB = $this->data['default_db'];
        $child_DB = $this->data['child_DB'];
        $database_username = $this->data['database_username'];
        $database_password = $this->data['database_password'];
        $database_path = $this->data['database_path'];
        $created_at = now();
        foreach ($this->rows as $index => $row) {

            if (empty($row['sr_no'])) {
                continue;
            }
            //info("shifttype....index......".($index+2)."__".$row['shift_type']);
            $this->connectDB($default_DB);

            $email = trim($row['email']);


            // Check if driver already exists for this company
            // if (!empty($email)) {
            //     $existingDriver = SubUser::where('email', $email)
            //         ->where('company_name', $company_name)
            //         ->first();

            //     if ($existingDriver) {
            //         continue;
            //     }
            // }

            // if ($existingDriver) {
            //     continue; // Skip if driver already exists
            // }

            // Generate a random password
            //$randomPassword = Str::random(10);


            // Create new SubUser  as driver (Parent DB)
            $driver = new SubUser ();
            $driver->first_name = $row['first_name'] ?? '';
            $driver->last_name = $row['last_name'] ?? '';
            $driver->email = $email;
            $driver->phone = $row['phone'] ?? '';
            $driver->mobile = $row['emergency_phone_number'] ?? '';
            $driver->employement_type = $row['position'] ?? '';
            $driver->password = $hashedPassword;
            $driver->company_name = $company_name;
            //$driver->pricebook_id = $row['pricebook_id'] ?? '';
            $driver->verified_by = $row['verified_by'] ?? '';
            $driver->blood_group = $row['blood_group'] ?? '';
            $driver->database_path = $database_path;
            $driver->database_name = $child_DB;
            $driver->database_username = $database_username;
            $driver->database_password = $database_password;

            // Save driver record
            $driver->save();

            if ($role && !$driver->hasRole('driver')) {
                $driver->roles()->attach($role);
            }

            // Connect to child DB to mirror driver info
            $this->connectDB($child_DB);

            // $pricebook_id = 1;
            // if (isset($row['pricebook_name']) && !empty($row['pricebook_name'])) {
            //      $pricebook = \App\Models\PriceBook::where('name', $row['pricebook_name'])->first();
            //     if ($pricebook) {
            //         $pricebook_id = $pricebook->id;
            //     } else {
            //         $pricebook_id = 1;
            //     }
            // }

            $childDriver = new SubUser ();
            $childDriver->id = $driver->id;
            $childDriver->first_name = $driver->first_name;
            $childDriver->last_name = $driver->last_name;
            $childDriver->email = $driver->email;
            $childDriver->phone = $driver->phone;
            $childDriver->mobile = $driver->mobile;
            $childDriver->employement_type = $driver->employement_type;
            $childDriver->password = $hashedPassword;
            $childDriver->company_name = $company_name;
            //$childDriver->pricebook_id = $pricebook_id ?? '';
            $childDriver->verified_by = $row['verified_by'] ?? '';
            $childDriver->blood_group = $row['blood_group'] ?? '';
            $childDriver->database_path = $database_path;
            $childDriver->database_name = $child_DB;
            $childDriver->database_username = $database_username;
            $childDriver->database_password = $database_password;
            $childDriver->save();

            // Assign 'driver' role on child DB
            if ($role && !$childDriver->hasRole('driver')) {
                $childDriver->roles()->attach($role);
            }

            // Handle address information
            if ($childDriver) {
                $sub_user_address = SubUserAddresse::where('sub_user_id', $childDriver->id)->whereNull('end_date')->first();

                if ($sub_user_address) {
                    if ($sub_user_address->start_date == date('Y-m-d')) {
                        $sub_user_address->sub_user_id = $childDriver->id;
                        $sub_user_address->address = $row['address'] ?? '';
                        $sub_user_address->latitude = $row['latitude'] ?? '';
                        $sub_user_address->longitude = $row['longitude'] ?? '';
                    } else {
                        $sub_user_address->end_date = date('Y-m-d');
                        $sub_new_address = new SubUserAddresse();
                        $sub_new_address->sub_user_id = $childDriver->id;
                        $sub_new_address->address = $row['address'] ?? '';
                        $sub_new_address->latitude = $row['latitude'] ?? '';
                        $sub_new_address->longitude = $row['longitude'] ?? '';
                        $sub_new_address->start_date = date('Y-m-d');
                        $sub_new_address->save();
                    }
                    $sub_user_address->update();
                } else {
                    $sub_new_address = new SubUserAddresse();
                    $sub_new_address->sub_user_id = $childDriver->id;
                    $sub_new_address->address = $row['address'] ?? '';
                    $sub_new_address->latitude = null; //$row['latitude'] ?? '';
                    $sub_new_address->longitude = null; //$row['longitude'] ?? '';
                    $sub_new_address->start_date = date('Y-m-d');
                    $sub_new_address->created_at = $created_at;
                    $sub_new_address->save();
                }
            }

            // Storing vehicle info
            if ($childDriver->id) {
                $vehicle = new Vehicle();
                $vehicle->name = $row['model'] ?? '';
                $vehicle->driver_id = $childDriver->id;
                $vehicle->chasis_no = $row['chasis_no'] ?? '';
                $vehicle->seats = $row['seats'] ?? '';
                $vehicle->registration_no = $row['registration_no'] ?? '';
                $vehicle->vehicle_no = $row['vehicle_no'] ?? '';
                $vehicle->color = $row['color'] ?? '';
             //   $vehicle->shift_type_id = $row['shift_type_id'] ?? null;
                $shiftTypeMap = [
                    'pick' => 1,
                    'drop' => 3,
                    'pick & drop' => 2,
                ];

                $rawShiftType = trim($row['shift_type'] ?? '');
                $normalizedShiftType = strtolower($rawShiftType);

                $vehicle->shift_type_id = $shiftTypeMap[$normalizedShiftType] ?? null;

                if (isset($row['vehicle_image']) && $row['vehicle_image'] instanceof \Illuminate\Http\UploadedFile) {
                    $path = public_path('images/vehicles');
                    !is_dir($path) && mkdir($path, 0777, true);

                    $vehiclefilename = time() . '.' . $row['vehicle_image']->extension();
                    $row['vehicle_image']->move($path, $vehiclefilename);
                    $vehicle->image = $vehiclefilename;
                }
                $vehicle->save();
            }
        }

        $this->connectDB($default_DB);
        UpdateUserLatLong::dispatch($child_DB,$created_at);
    }
}
