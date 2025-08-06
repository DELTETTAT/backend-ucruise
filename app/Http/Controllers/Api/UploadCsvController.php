<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\SubUser;
use App\Models\SubUserAddresse;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use DB;

class UploadCsvController extends Controller
{
    /**
     * @OA\Post(
     * path="/uc/api/uploadDriverCsv",
     * operationId="driverCsv",
     * tags={"AccountSetup"},
     * summary="Upload driver csv",
     *   security={ {"Bearer": {} }},
     * description="Upload driver csv",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"file"},
     *               @OA\Property(property="file", type="file"),
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

    public function driverCsv(Request $request)
    {
        $user_id = auth('sanctum')->user()->id;
        $user = User::find($user_id);
        if ($user && $user->hasRole('admin')) {
            error_reporting(0);
            if ($request->all()) {
                $request->validate([
                    'file' => 'required|mimes:csv|max:2048', // Validate file type and size
                ]);

                // Open the uploaded file in memory
                $fileContent = file_get_contents($request->file('file')->getPathname());

                // Convert the CSV content to an array
                $rows = array_map('str_getcsv', explode(PHP_EOL, $fileContent));

                // Initialize arrays to track unique identifiers and empty columns


                // Skip the header (optional)
                array_shift($rows);
                array_pop($rows);

                //echo '<pre>';print_r($rows);die;
                $duplicateEmail = $this->findDuplicateEmails($rows);
                $duplicatePhone = $this->findDuplicatePhones($rows);
                $checkEmptyColumn = $this->findEmptyColumn($rows);
                $checkExistInDb = $this->existInDb($rows, $user);


                if (count($duplicateEmail) > 0 || count($duplicatePhone) > 0 || count($checkEmptyColumn) > 0) {
                    $errors = [];


                    if (count($checkEmptyColumn) > 0) {
                        $errors[] = [
                            'field' => 'empty_column',
                            'message' => 'Empty Column found',
                            'data' => $checkEmptyColumn
                        ];
                    }



                    if (count($duplicateEmail) > 0) {
                        $errors[] = [
                            'field' => 'email',
                            'message' => 'Duplicate email addresses found',
                            'data' => $duplicateEmail
                        ];
                    }

                    if (count($duplicatePhone) > 0) {
                        $errors[] = [
                            'field' => 'phone',
                            'message' => 'Duplicate phone numbers found',
                            'data' => $duplicatePhone
                        ];
                    }

                    $response = [
                        'success' => 'false',
                        'message' => 'Validation errors',
                        'errors' => $errors
                    ];

                    // echo '<pre>';
                    // print_r($response);
                    $jsonResponse = json_encode($response);
                    return $jsonResponse;
                }
                $this->data['accepted_drivers'] = $checkExistInDb['accepted_emails'];
                $this->data['existing_drivers'] = $checkExistInDb['rejected_emails'];

                return response()->json([
                    'success' => true,
                    'message' => 'CSV uploaded successfully',
                    'data' => $this->data
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No file uploaded'
                ], 500);
            }
        }
        return response()->json([
            'success' => false,
            'message' => 'Unauthorised user'
        ], 401);
    }

    public function existInDb($rows, $user)
    {
        // dd($rows);
        $errors = [];
        $discarded_emails = [];
        $accepted_emails = [];
        //database info
        $temp_DB_name = DB::connection()->getDatabaseName();
        $default_DBName = env("DB_DATABASE");
        $database_path = env("DB_HOST");
        $company_name = $user->company_name;
        $database_name = $temp_DB_name;
        $database_username = env("DB_USERNAME");
        $database_password = env("DB_PASSWORD");
        foreach ($rows as $key => $subArray) {
            //password creation 
            $rand = Str::random(10);
            $password = Hash::make($rand);

            $this->connectDB($default_DBName);
            //connected  to parent db
            $driver = SubUser::where('email', $subArray[5])->first();
            if ($driver) {
                $discarded_emails[] = $subArray[5];
                continue;
            } else {

                // If validation passes, insert the record into the database
                $parent_subuser = new SubUser();
                $parent_subuser->salutation = $subArray[0];
                $parent_subuser->gender = $subArray[1];
                $parent_subuser->first_name = $subArray[2];
                $parent_subuser->last_name = $subArray[3];
                $parent_subuser->display_name = $subArray[4];
                $parent_subuser->email = $subArray[5];
                $parent_subuser->phone = $subArray[6];
                $parent_subuser->mobile = $subArray[7];
                $parent_subuser->dob = date('Y-m-d', strtotime($subArray[8]));
                $parent_subuser->password = $password;
                $parent_subuser->company_name = $company_name;
                $parent_subuser->database_path = $database_path;
                $parent_subuser->database_name = $database_name;
                $parent_subuser->database_username = $database_username;
                $parent_subuser->database_password = $database_password;
                $parent_subuser->status = 1;
                $parent_subuser->save();
                $role = Role::where("name", "driver")->first();

                // Manage Role
                if (!$parent_subuser->hasRole("driver")) {
                    $parent_subuser->roles()->attach($role);
                }
                //connected  to child db
                $this->connectDB($temp_DB_name);
                $child_subuser = new SubUser();
                $child_subuser->id = $parent_subuser->id;
                $child_subuser->salutation = $subArray[0];
                $child_subuser->gender = $subArray[1];
                $child_subuser->first_name = $subArray[2];
                $child_subuser->last_name = $subArray[3];
                $child_subuser->display_name = $subArray[4];
                $child_subuser->email = $subArray[5];
                $child_subuser->phone = $subArray[6];
                $child_subuser->mobile = $subArray[7];
                $child_subuser->dob = date('Y-m-d', strtotime($subArray[8]));
                $child_subuser->password = $password;
                $child_subuser->company_name = $company_name;
                $child_subuser->database_path = $database_path;
                $child_subuser->database_name = $database_name;
                $child_subuser->database_username = $database_username;
                $child_subuser->database_password = $database_password;
                $child_subuser->status = 1;
                $child_subuser->save();
                $role = Role::where("name", "driver")->first();

                // Manage Role
                if (!$child_subuser->hasRole("driver")) {
                    $child_subuser->roles()->attach($role);
                }
                $this->data1['detais'] = [];
                // Send email to the user containing login details
                $this->data1['detais'] = [
                    "email" => $subArray[5],
                    "pass" => $rand
                ];
                $email = $subArray[5];
                $accepted_emails[] = $email;


                //Sending emails to the drivers 
                Mail::send("email.adminlogin", $this->data1, function ($message) use ($email) {
                    $message
                        ->to($email)
                        ->from("info@gmail.com")
                        ->subject("Client Login Details");
                });
                //Storing Address info in child db
                $sub_user_address = new SubUserAddresse();
                $sub_user_address->sub_user_id = $child_subuser->id;
                $sub_user_address->address = $subArray[9];
                $sub_user_address->latitude = $subArray[10];
                $sub_user_address->longitude = $subArray[11];
                $sub_user_address->start_date = date('Y-m-d');
                $sub_user_address->save();
                //Storing Vehicle info in child db
                $vehicle = new Vehicle();
                $vehicle->driver_id = $child_subuser->id;
                $vehicle->name = $subArray[12];
                $vehicle->color = $subArray[13];
                $vehicle->registration_no = $subArray[14];
                $vehicle->vehicle_no = $subArray[15];
                $vehicle->chasis_no = $subArray[16];
                $vehicle->seats = intval($subArray[17]);
                $vehicle->fare = $subArray[18];
                $vehicle->save();
            }
        }
        return [
            'accepted_emails' => $accepted_emails,
            'rejected_emails' => $discarded_emails
        ];
    }


    public function findEmptyColumn($rows)
    {
        //dd($users);
        $errors = [];
        foreach ($rows as $key => $subArray) {
            if (!$subArray[9]) {
                $errors[] = 'Address cannot be empty';
            }

            if (!$subArray[10]) {
                $errors[] = 'Longitude cannot be empty';
            }

            if (!$subArray[11]) {
                $errors[] = 'Latitude cannot be empty';
            }
        }
        //dd($errors);
        return $errors;
    }



    public function findDuplicateEmails($users)
    {
        $emails = [];
        $duplicateEmails = [];

        foreach ($users as $user) {
            $email = $user[5];
            if (!$email) {
                return ['Email cannot be empty'];
            }


            if (in_array($email, $emails)) {
                $duplicateEmails[] = $email;
            } else {
                $emails[] = $email;
            }
        }

        return $duplicateEmails;
    }

    public function findDuplicatePhones($phones)
    {
        $existPhone = [];
        $duplicatePhone  = [];

        foreach ($phones as $phone) {
            $phone = $phone[6];


            if (!$phone) {
                return ['Phone cannot be empty'];
            }

            if (in_array($phone, $existPhone)) {
                $duplicatePhone[] = $phone;
            } else {
                $existPhone[] = $phone;
            }
        }

        return $duplicatePhone;
    }
}
