<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\Api\ScheduleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\SubUser;
use App\Models\HrmsTimeAndShift;
use App\Models\CompanyAddresse;
use App\Models\Leave;
use App\Models\PriceBook;
use App\Models\DailySchedule;
use App\Models\DailyScheduleCarer;
use App\Models\MissingSchedule;
use App\Models\SubUserAddresse;


class ProcessScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $database;
    public function __construct(array $database)
    {
        $this->database = $database;
        Log::info(" construct  call {$this->database['current_db']}, {$this->database['default_db']}");
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $dbs = $this->database['current_db'];
        $parentdb = $this->database['default_db'];
        $currentDate = Carbon::now()->format('Y-m-d');
        $this->connectDB($dbs);
        $shiftTime = HrmsTimeAndShift::get()->toArray();

        // Company lat & long
        $companylocation = CompanyAddresse::get()->first();
        $lat = $companylocation->latitude;
        $long = $companylocation->longitude;

        $now = Carbon::now();
        $currentDay = strtoupper($now->format('D'));

        $scheduleStart = "";
        $scheduleEnd = "";
        $shiftFinishesNextDay = "";

        $response =[];

        foreach ($shiftTime as $shift) {

            $shiftFinish = $shift['shift_finishs_next_day'];

            $timeLoop = $shiftFinish == 1 ? ['end' => 3, 'start' => 1] : ['start' => 1, 'end' => 3];

            foreach ($timeLoop as $key => $shiftType) {

                $start = $shift['shift_time']['start'];
                $end = $shift['shift_time']['end'];
                $days = $shift['shift_days'];
                $employeeShift = $shift['shift_name'];

                // if (!isset($days[$currentDay]) || $days[$currentDay] != 1) {
                //    info("SKIPPED: Day '$currentDay' is not active for shift '{$shift['shift_name']}' in DB: $dbs");
                //     continue;
                // }


               $isSaturday = Carbon::parse($currentDay)->format('l') === 'Saturday';
                $previousDay = Carbon::parse($currentDay)->subDay()->format('Y-m-d');
                $isFridayOvernightShift = isset($days[$previousDay]) && $days[$previousDay] == 1 && $shift['end_time'] < $shift['start_time'];

                if ((!isset($days[$currentDay]) || $days[$currentDay] != 1) && !($isSaturday && $isFridayOvernightShift)) {
                    info("SKIPPED: Day '$currentDay' is not active for shift '{$shift['shift_name']}' in DB: $dbs");
                    continue;
                }

                if($currentDay =="MON" && $shiftFinish == 1){
                    if ($key == 'end') {
                        continue;
                    }
                }

                $now = Carbon::now(); // current time
                $startTime = Carbon::parse($start);
                $endTime = Carbon::parse($end);

                $shiftFinishesNextDay = $shiftFinish;

                $scheduleStart = $start;
                $scheduleEnd = $end;

                // Initialize shift type
                $shiftType = 0; // 1 for pick, 3 for drop

                if ($key == 'start') {
                    $shiftType = 1;
                } elseif ($key == 'end') {
                    $shiftType = 3;
                }

                $response = Http::post('http://157.245.99.137/assign-drivers/'. $dbs.'/'.$shiftType.'/'.$employeeShift);
                $dataArray = json_decode($response, true);

                Log::info("Here HRS response data shift : $employeeShift". print_r($dataArray, true));

                $shiftDays = [];
                foreach ($days as $day => $value) {
                    if ($value == '1') {
                        $shiftDays[strtolower($day)] = 1;
                    }
                }

                $finalOutput = [];

                foreach ($dataArray['data'] as $users) {

                    $seatInfo = explode('-', $users['vehicle_type']);
                    $users['assigned_users'][0];
                    $dlatitude = $users['assigned_users'][0]['lat'];
                    $dlongitude = $users['assigned_users'][0]['lng'];
                    $scheduleCity = $this->getLocalityName($dlatitude, $dlongitude);
                    $capacity = isset($seatInfo[0]) ? (int) $seatInfo[0] : null;

                    $nearestPriceBook = PriceBook::select('*')
                        ->selectRaw(
                            '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                            [$dlatitude, $dlongitude, $dlatitude]
                        )
                        ->whereNotNull('latitude')
                        ->whereNotNull('longitude')
                        ->where('name', 'like', "%{$capacity} Seaters")
                        ->orderBy('distance')
                        ->first();

                    $priceBookId = 1;
                    $firstPriceBook = PriceBook::first();
                    if ($firstPriceBook) {
                        $priceBookId = $firstPriceBook->id;
                    }

                    $entry = [
                        "date" => $currentDate,
                        "pick_time" => $scheduleStart,
                        "shift_finishes_next_day" => $shiftFinishesNextDay,
                        "previous_day_pick" => 0,
                        "custom_checked" => 0,
                        "infinite_checked" => 0, // modify =1
                        "drop_time" => $scheduleEnd,
                        "driver_id" => isset($users['driver_id']) ? (int) $users['driver_id'] : null,
                        "vehicle_id" => isset($users['vehicle_id']) ? (int) $users['vehicle_id'] : null,
                        "shift_type_id" => $shiftType,
                        "scheduleLocation" => $scheduleCity,
                        "scheduleCity" => $scheduleCity,
                        "selectedLocationLat" => @$dlatitude,
                        "selectedLocationLng" => @$dlongitude,
                        "pricebook_id" => $nearestPriceBook['id'] ?? $priceBookId, //$priceBookId,
                        "is_repeat" => "1",
                        "carers" => array_map('intval', array_column($users['assigned_users'], 'user_id')),
                        "repeat" => "0",
                        "seats" => isset($seatInfo[0]) ? (int) $seatInfo[0] : null, // $seatInfo[0] ?? null,
                        "reacurrance" => "weekly",
                        "end_date" => $currentDate,
                        "repeat_days" => 1,
                        "repeat_weeks" => "1"
                    ];

                    $entry = array_merge($entry, $shiftDays);
                    $finalOutput[] = $entry;
                }

                $scheduleCreated = "No";

                $response =[];
                foreach ($finalOutput as $schedule) {

                    try {
                        info("Processing schedule for driver_id: {$schedule['driver_id']}, carers: " . implode(',', $schedule['carers']));
                        $request = new Request(['data' => json_encode($schedule)]);
                        //app(ScheduleController::class)->dailyaddSchedule($request);
                        $response = app(ScheduleController::class)->addSchedule($request);
                         $datas = $response->getData(true);
                        if (isset($datas['success']) && $datas['success'] ==1) {
                              $scheduleCreated = "Yes";
                        }

                    } catch (\Throwable $e) {
                        info("Schedule failed: " . $e->getMessage());
                    }
                }

               

                    if (isset($dataArray['unassignedUsers'])  &&!empty($dataArray['unassignedUsers']) && count($dataArray['unassignedUsers']) > 0) {

                            if($scheduleCreated === "Yes") {
                                // Unshined users save in missing_schedule table
                                foreach($dataArray['unassignedUsers'] as $unshineduser){
                                    $missuser = User::find($unshineduser['user_id']);
                                    if($missuser){

                                        MissingSchedule::create([
                                            'user_id'        => $missuser->id,
                                            'first_name'     => $missuser->first_name,
                                            'last_name'      => $missuser->last_name,
                                            'email'          => $missuser->email,
                                            'office_distance'=> $missuser->office_distance,
                                            'latitude'       => $missuser->latitude,
                                            'longitude'      => $missuser->longitude,
                                            'address'        => $missuser->address,
                                            'profile_image'  => $missuser->profile_image,
                                            'shift_type'     => $missuser->shift_type,
                                            'schedule_type'  => $shiftType == 1 ? 'pick' : 'drop',
                                            'missing_reason' => 'Driver missing',
                                            'date'           => Carbon::today()->toDateString(),
                                            'created_at'     => now(),
                                            'updated_at'     => now(),
                                        ]);
                                    }
                                }
                            }

                            // Mail::send('email.driver_assigned', ['unassignedEmployees' => $dataArray['unassignedUsers']], function ($message) use ($drivers) {
                            //     $message->to('developerphp1995@gmail.com')
                            //         ->subject('Driver Unassignment Notification');
                            // });

                           
                        }

                     if($scheduleCreated === "Yes") {
                            $this->unschedulemissingUsers($dataArray['unassignedUsers'], $dataArray['data'], $shiftType, $shiftFinishesNextDay, $scheduleCreated);
                     }
            }

            info("here shcedule type is ".$shiftType);
        }
        

        if(empty($response)) {
           info("Not found data for schedule");
        }

        info("schedule save response ". print_r($response));




        //try {

        // $currentDate = Carbon::now()->format('Y-m-d');
        // $database = $this->database['current_db'];
        // $this->connectDB($database);
        // $shiftTime = HrmsTimeAndShift::get()->toArray();
        // // Company lat & long
        // $companylocation = CompanyAddresse::get()->first();
        // $lat = $companylocation->latitude;
        // $long = $companylocation->longitude;

        // $now = Carbon::now();
        // $currentDay = strtoupper($now->format('D'));

        // $scheduleStart = "";
        // $scheduleEnd = "";
        // $shiftFinishesNextDay = "";

        // $response =[];

        // foreach ($shiftTime as $shift) {

        //     $timeLoop = ['start' => 1, 'end' => 3];

        //     foreach ($timeLoop as $key => $shiftType) {

        //         $start = $shift['shift_time']['start'];
        //         $end = $shift['shift_time']['end'];
        //         $days = $shift['shift_days'];
        //         $shiftFinish = $shift['shift_finishs_next_day'];
        //         $employeeShift = $shift['shift_name'];

        //         // if (!isset($days[$currentDay]) || $days[$currentDay] != 1) {
        //         //    info("SKIPPED: Day '$currentDay' is not active for shift '{$shift['shift_name']}' in DB: $dbs");
        //         //     continue;
        //         // }


        //        $isSaturday = Carbon::parse($currentDay)->format('l') === 'Saturday';
        //         $previousDay = Carbon::parse($currentDay)->subDay()->format('Y-m-d');
        //         $isFridayOvernightShift = isset($days[$previousDay]) && $days[$previousDay] == 1 && $shift['end_time'] < $shift['start_time'];

        //         if ((!isset($days[$currentDay]) || $days[$currentDay] != 1) && !($isSaturday && $isFridayOvernightShift)) {
        //             info("SKIPPED: Day '$currentDay' is not active for shift '{$shift['shift_name']}' in DB: $database");
        //             continue;
        //         }

        //         if($currentDay =="MON" && $shiftFinish == 1){
        //             if ($key == 'end') {
        //                 continue;
        //             }
        //         }

        //         // Initialize shift type
        //         $shiftType = ""; // 1 for pick, 3 for drop

        //         if ($key == 'start') {
        //             $shiftType = 1;
        //         } elseif ($key == 'end') {
        //             $shiftType = 3;
        //         }

        //         $response = Http::post('http://157.245.99.137/assign-drivers/'. $database.'/'.$shiftType.'/'.$employeeShift);
        //         $dataArray = json_decode($response, true);

        //         info("API data $shiftType". print_r($dataArray, true));

        //         $shiftDays = [];
        //         foreach ($days as $day => $value) {
        //             if ($value == '1') {
        //                 $shiftDays[strtolower($day)] = 1;
        //             }
        //         }

        //         $finalOutput = [];

        //         foreach ($dataArray['data'] as $users) {

        //             $seatInfo = explode('-', $users['vehicle_type']);
        //             $users['assigned_users'][0];
        //             $dlatitude = $users['assigned_users'][0]['lat'];
        //             $dlongitude = $users['assigned_users'][0]['lng'];
        //             $scheduleCity = $this->getLocalityName($dlatitude, $dlongitude);
        //             $capacity = isset($seatInfo[0]) ? (int) $seatInfo[0] : null;

        //             $nearestPriceBook = PriceBook::select('*')
        //                 ->selectRaw(
        //                     '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
        //                     [$dlatitude, $dlongitude, $dlatitude]
        //                 )
        //                 ->whereNotNull('latitude')
        //                 ->whereNotNull('longitude')
        //                 ->where('name', 'like', "%{$capacity} Seaters")
        //                 ->orderBy('distance')
        //                 ->first();

        //             $priceBookId = 1;
        //             $firstPriceBook = PriceBook::first();
        //             if ($firstPriceBook) {
        //                 $priceBookId = $firstPriceBook->id;
        //             }

        //             $entry = [
        //                 "date" => $currentDate,
        //                 "pick_time" => $start,
        //                 "shift_finishes_next_day" => $shiftFinish,
        //                 "previous_day_pick" => 0,
        //                 "custom_checked" => 0,
        //                 "infinite_checked" => 0, // modify =1
        //                 "drop_time" => $end,
        //                 "driver_id" => isset($users['driver_id']) ? (int) $users['driver_id'] : null,
        //                 "vehicle_id" => isset($users['vehicle_id']) ? (int) $users['vehicle_id'] : null,
        //                 "shift_type_id" => $shiftType,
        //                 "scheduleLocation" => $scheduleCity,
        //                 "scheduleCity" => $scheduleCity,
        //                 "selectedLocationLat" => @$dlatitude,
        //                 "selectedLocationLng" => @$dlongitude,
        //                 "pricebook_id" => $nearestPriceBook['id'] ?? $priceBookId, //$priceBookId,
        //                 "is_repeat" => "1",
        //                 "carers" => array_map('intval', array_column($users['assigned_users'], 'user_id')),
        //                 "repeat" => "0",
        //                 "seats" => isset($seatInfo[0]) ? (int) $seatInfo[0] : null, // $seatInfo[0] ?? null,
        //                 "reacurrance" => "weekly",
        //                 "end_date" => $currentDate,
        //                 "repeat_days" => 1,
        //                 "repeat_weeks" => "1"
        //             ];

        //             $entry = array_merge($entry, $shiftDays);
        //             $finalOutput[] = $entry;
        //         }


        //         $response =[];
        //         foreach ($finalOutput as $schedule) {
        //             try {
        //                 info("Processing schedule for driver_id: {$schedule['driver_id']}, carers: " . implode(',', $schedule['carers']));
        //                 ///info("Group data1 ". print_r($schedule, true));
        //                 $request = new Request(['data' => json_encode($schedule)]);
        //                 //app(ScheduleController::class)->dailyaddSchedule($request);
        //                 $response = app(ScheduleController::class)->addSchedule($request);

        //             } catch (\Throwable $e) {
        //                 info("Schedule failed: " . $e->getMessage());
        //                 info("Schedule line: " . $e->getFile());
        //             }
        //         }

        //     }

        // }

        //     if(empty($response)) {
        //         //return response()->json(['success' => false, 'message' => 'Not found data for schedule']);
        //         info("Not found data for schedule");
        //     }
        //     //return $response;
        //     info("Not found data for schedule ". print_r($response, true));

        // } catch (\Throwable $e) {
        //     Log::error("Schedule failed in job: " . $e->getMessage());
        // }
    }



    public function getLocalityName($latitude, $longitude)
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

      public function unschedulemissingUsers($unassignedUsers, $assignusers, $scheduleType, $shiftFinishesNextDay, $scheduleCreated){

        if($scheduleCreated === "No") {
            return;
        }

        $sentUsers = [];

        // From unassigned users
        if (!empty($unassignedUsers)) {
            $sentUsers = array_column($unassignedUsers, 'user_id');
        }

        // From assigned users
        if (!empty($assignusers)) {
            foreach ($assignusers as $assign) {
                if (!empty($assign['assigned_users']) && is_array($assign['assigned_users'])) {
                    foreach ($assign['assigned_users'] as $user) {
                        if (isset($user['user_id'])) {
                            $sentUsers[] = $user['user_id'];
                        }
                    }
                }
            }
        }
        $sentUsers = array_unique($sentUsers);


         $unshinedUsersList = User::whereHas('roles', function ($query) {
                        $query->where('role_id', 4);
                    })
                    ->whereNotIn('users.id', $sentUsers)
                    ->where('users.shift_type', $shiftFinishesNextDay)
                    ->where('users.cab_facility', 1)
                    ->whereIn('users.status', [1, 3, 4])
                    ->where('users.close_account', 1)
                    ->get();



        // Get staff on live leave
        $today = now()->toDateString();
        $currentHour = now()->hour;
        $leaveStaffIds = Leave::whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->where('status', 1)
            ->get()
            ->filter(function ($leave) use ($currentHour) {
                if ($leave->type == 1) {
                    return true;
                } elseif ($leave->type == 2 && $currentHour < 12) {
                    return true;
                } elseif ($leave->type == 3 && $currentHour >= 12) {
                    return true;
                }
                return false;
            })
            ->pluck('staff_id')
            ->unique()
            ->toArray();

  
        foreach ($unshinedUsersList as &$user) {
            $missingReason = [];

            if (in_array($user['id'], $leaveStaffIds)) {
                $missingReason[] = 'On leave';
            }

            if (empty($user['latitude']) || empty($user['longitude'])) {
                $missingReason[] = 'Missing location lat & long';
            }

            if (isset($user['cab_facility']) && $user['cab_facility'] == 0) {
                $missingReason[] = 'No cab facility';
            }

             if (isset($user['no_show']) && $user['no_show'] == "Yes") {
                $missingReason[] = 'No show';
            }
            

            if (!empty($missingReason)) {
                $user['missing_reason'] = implode(', ', $missingReason);
            }
        }

   

        if(!empty($unshinedUsersList)){
            foreach($unshinedUsersList as $missuser){
                if($missuser){
                        MissingSchedule::create([
                            'user_id'        => $missuser->id,
                            'first_name'     => $missuser->first_name,
                            'last_name'      => $missuser->last_name,
                            'email'          => $missuser->email,
                            'office_distance'=> $missuser->office_distance,
                            'latitude'       => $missuser->latitude,
                            'longitude'      => $missuser->longitude,
                            'address'        => $missuser->address,
                            'profile_image'  => $missuser->profile_image,
                            'shift_type'     => $missuser->shift_type,
                            'schedule_type'  => $scheduleType == 1 ? 'pick' : 'drop',
                            'missing_reason' => $missuser->missing_reason,
                            'date'           => Carbon::today()->toDateString(),
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);
                }
            }
        }
 info("function is called ");
        //info("extract sent user list ". print_r($sentUsers, true));
        //info("unshinedUsersList user list ". print_r($unshinedUsersList->toArray(), true));

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
}
