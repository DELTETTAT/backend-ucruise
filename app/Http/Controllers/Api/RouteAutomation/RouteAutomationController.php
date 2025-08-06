<?php

namespace App\Http\Controllers\Api\RouteAutomation;

use App\Http\Controllers\Api\ScheduleController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Jobs\ProcessUnscheduledMissingUsers;
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
use Mail;
use App\Jobs\ProcessScheduleJob;

class RouteAutomationController extends Controller
{

    /**
     * @OA\get(
     * path="/uc/api/routeautomation/dailyAutomationCluster/{db}/{shifttype}/{employeeshift}",
     * operationId="dailyAutomationCluster",
     * tags={"Daily Automation"},
     * summary="Get Automation Request",
     *   security={ {"Bearer": {} }},
     *    description="Get Automation Request",
     *    @OA\Parameter(
     *     name="db",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *    ),
     *     @OA\Parameter(
     *         name="shifttype",
     *         in="path",
     *         required=false,
     *         description="Optional shift type (e.g., 1 for pick or 3 for drop)",
     *         @OA\Schema(type="string")
     *     ),
     *    @OA\Parameter(
     *         name="employeeshift",
     *         in="path",
     *         required=false,
     *         description="Optional shift type (e.g., Evening shift , Morning shift, Morning shift 2 )",
     *         @OA\Schema(type="string")
     *     ),
     *      @OA\Response(
     *          response=201,
     *          description="Automation Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Automation Get Successfully",
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
    public function dailyAutomationCluster_old($db)
    {

        // $dbs = ['UC_logisticllp', 'UC_unifytest'];
        // $db = ['UC_logisticllp'];

        try {

            $this->connectDB($db);
            $shiftTime = HrmsTimeAndShift::get()->toArray();

            // Company lat & long
            $companylocation = CompanyAddresse::select('id','address','latitude','longitude')->get()->first();
            $lat = $companylocation->latitude;
            $long = $companylocation->longitude;

            $now = Carbon::now();
            $currentTime = $now->format('H:i');
            $currentDay = strtoupper($now->format('D'));

            foreach ($shiftTime as $shift) {

                $start = $shift['shift_time']['start'];
                $end = $shift['shift_time']['end'];
                $days = $shift['shift_days'];
                $shiftFinish = $shift['shift_finishs_next_day'];

                // if (!isset($days[$currentDay]) || $days[$currentDay] != 1) {
                //    // info("SKIPPED: Day '$currentDay' is not active for shift '{$shift['shift_name']}' in DB: $db");
                //     continue;
                // }

                $now = Carbon::now(); // current time
                $startTime = Carbon::parse($start);
                $endTime = Carbon::parse($end);

                $shiftFinishesNextDay = $shiftFinish;

                $scheduleStart = "00:00";
                $scheduleEnd = "00:00";

                // Initialize shift type
                $shiftType = 0; // 1 for pick, 3 for drop

                // Calculate 2.5 hours (150 minutes) before start and end
                $pickWindowStart = $startTime->copy()->subMinutes(150);
                $dropWindowStart = $endTime->copy()->subMinutes(150);


                // On Saturday should be make schedule for next day finish cases
                if (!isset($days[$currentDay]) || $days[$currentDay] != 1) {
                    if (!($currentDay == "SAT" && $shiftFinish == 1 && $now->between($pickWindowStart, $startTime))) {
                        continue;
                    }
                }

                // On Monday drop schedule should not make for next day finish cases
                if($currentDay =="MON" && $shiftFinish == 1){
                    if ($now->between($dropWindowStart, $endTime)) {
                            continue;
                    }
                }

                // Determine if current time is in pick or drop window
                if ($now->between($pickWindowStart, $startTime)) {
                    $shiftType = 1; // Pick
                    $scheduleStart = $start;
                   // info("PICK: Now {$now->format('H:i')} is within 2.5 hours before shift start — {$shift['shift_name']}");
                } elseif ($now->between($dropWindowStart, $endTime)) {
                    $shiftType = 3; // Drop
                    $scheduleEnd = $end;
                  //  info("DROP: Now {$now->format('H:i')} is within 2.5 hours before shift end — {$shift['shift_name']}");
                } else {
                  //  info("SKIPPED: Now {$now->format('H:i')} is not near start or end — {$shift['shift_name']}");
                }


                if ($now->between($pickWindowStart, $startTime) || $now->between($dropWindowStart, $endTime)) {
                    if ($shiftType != 0) {
                        $users = $this->allusers($shiftFinishesNextDay);
                        $drivers =  $this->drivers($shiftType);
                        $this->data['company'] = $companylocation;
                        $this->data['users'] = $users;
                        $this->data['drivers'] = $drivers;
                        $this->data['shift'] = $shift;

                        return response()->json([
                            'status'  => true,
                            'db' => $db,
                            'message' => 'Groups saved successfully.',
                            'data' => $this->data
                        ]);
                    } else {

                       // info("SKIPPED: Not time for pick or drop — Now: {$now->format('H:i')} — Shift: {$shift['shift_name']} in DB: $db");
                    }
                } else {

                   // info(" Not within 2.5 hours before start or end — Now: $currentTime — Shift: {$shift['shift_name']} in DB: $db");
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Server error.',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }




    public function dailyAutomationCluster($db, $shifttype, $employeeshift)
    {

        try {

            $this->connectDB($db);
            $shiftTime = HrmsTimeAndShift::get()->toArray();
            // Company lat & long
            $companylocation = CompanyAddresse::select('id','address','latitude','longitude')->get()->first();

            $now = Carbon::now();
            $currentDay = strtoupper($now->format('D'));

            foreach ($shiftTime as $shift) {

                $days = $shift['shift_days'];
                $shiftFinishesNextDay = $shift['shift_finishs_next_day'];

                if (!isset($days[$currentDay]) || $days[$currentDay] != 1) {
                   // info("SKIPPED: Day '$currentDay' is not active for shift '{$shift['shift_name']}' in DB: $db");
                    continue;
                }

                // Initialize shift type
                $shifttype = $shifttype ?? 2; // 1 for pick, 3 for drop

                $users = $this->allusers($shiftFinishesNextDay, $employeeshift);
                $drivers =  $this->drivers($shifttype);
                $this->data['company'] = $companylocation;
                $this->data['users'] = $users;
                $this->data['drivers'] = $drivers;
                $this->data['shift'] = $shift;

                return response()->json([
                    'status'  => true,
                    'db' => $db,
                    'message' => 'Groups saved successfully.',
                    'data' => $this->data
                ]);

            }
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Server error.',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }



    /**
     * @OA\post(
     * path="/uc/api/routeautomation/dailyAutomationClusterschedule",
     * operationId="dailyAutomationClusterschedule",
     * tags={"Daily Automation"},
     * summary="Get Automation Request",
     *   security={ {"Bearer": {} }},
     *    description="Get Automation Request",
     *      @OA\Response(
     *          response=201,
     *          description="Automation Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Automation Get Successfully",
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



    public function dailyAutomationClusterschedule(Request $request)
    {

        $dbs = 'UC_logisticllp'; //live db
        //$dbs = 'UC_unify_test'; //live test db

        $response = Http::post('http://157.245.99.137/assign-drivers/' . $dbs);
        $dataArray = json_decode($response, true);

        $finalOutput = [];
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

        foreach ($shiftTime as $shift) {

            $start = $shift['shift_time']['start'];
            $end = $shift['shift_time']['end'];
            $days = $shift['shift_days'];
            $shiftFinish = $shift['shift_finishs_next_day'];

            // if (!isset($days[$currentDay]) || $days[$currentDay] != 1) {
            //    info("SKIPPED: Day '$currentDay' is not active for shift '{$shift['shift_name']}' in DB: $dbs");
            //     continue;
            // }

            $now = Carbon::now(); // current time
            $startTime = Carbon::parse($start);
            $endTime = Carbon::parse($end);

            $shiftFinishesNextDay = $shiftFinish;

            $scheduleStart = "00:00";
            $scheduleEnd = "00:00";

            // Initialize shift type
            $shiftType = 0; // 1 for pick, 3 for drop

            // Calculate 2.5 hours (150 minutes) before start and end
            $pickWindowStart = $startTime->copy()->subMinutes(150);
            $dropWindowStart = $endTime->copy()->subMinutes(150);

            // On Saturday should be make schedule for next day finish cases
            if (!isset($days[$currentDay]) || $days[$currentDay] != 1) {
                if (!($currentDay == "SAT" && $shiftFinish == 1 && $now->between($dropWindowStart, $endTime))) {
                    continue;
                }
            }

            // On Monday drop schedule should not make for next day finish cases
            if($currentDay =="MON" && $shiftFinish == 1){
                if ($now->between($dropWindowStart, $endTime)) {
                    continue;
                }
            }

            // Determine if current time is in pick or drop window
            if ($now->between($pickWindowStart, $startTime)) {
                $shiftType = 1; // Pick
                $scheduleStart = $start;
                info("PICK: Now {$now->format('H:i')} is within 2.5 hours before shift start — {$shift['shift_name']}");
            } elseif ($now->between($dropWindowStart, $endTime)) {
                $shiftType = 3; // Drop
                $scheduleEnd = $end;
               info("DROP: Now {$now->format('H:i')} is within 2.5 hours before shift end — {$shift['shift_name']}");
            } else {
               info("SKIPPED: Now {$now->format('H:i')} is not near start or end — {$shift['shift_name']}");
            }

            $shiftDays = [];
            foreach ($days as $day => $value) {
                if ($value == '1') {
                    $shiftDays[strtolower($day)] = 1;
                }
            }
        }

        foreach ($dataArray['data'] as $users) {

            $seatInfo = explode('-', $users['vehicle_type']);
            $users['assigned_users'][0];
            $dlatitude = $users['assigned_users'][0]['lat'];
            $dlongitude = $users['assigned_users'][0]['lng'];
            $scheduleCity = $this->getLocalityName($dlatitude, $dlongitude);

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
                'selectedLocationLat' => @$dlatitude,
                'selectedLocationLng' => @$dlongitude,
                "pricebook_id" => 1,
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


        //return $finalOutput;

        foreach ($finalOutput as $schedule) {

            try {

                info("Processing schedule for driver_id: {$schedule['driver_id']}, carers: " . implode(',', $schedule['carers']));
                ///info("Group data1 ". print_r($schedule, true));
                $request = new Request(['data' => json_encode($schedule)]);
                //app(ScheduleController::class)->dailyaddSchedule($request);
                app(ScheduleController::class)->addSchedule($request);

            } catch (\Throwable $e) {
                info("Schedule failed: " . $e->getMessage());
            }
        }
    }


    /**
     * @OA\post(
     * path="/uc/api/routeautomation/dailyschedulewithsingledatabase",
     * operationId="dailyschedulewithsingledatabase",
     * tags={"Daily Automation"},
     * summary="Make schedule with single database",
     *   security={ {"Bearer": {} }},
     *    description="Make schedule with single database",
     *      @OA\Response(
     *          response=201,
     *          description="Automation Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Automation Get Successfully",
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



    public function dailyschedulewithsingledatabase(Request $request)
    {

        $dbs = DB::connection()->getDatabaseName();

        $response = Http::post('http://157.245.99.137/assign-drivers/' . $dbs);
        $dataArray = json_decode($response, true);

        $finalOutput = [];
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
        // $shiftType = 0;

        foreach ($shiftTime as $shift) {

            $start = $shift['shift_time']['start'];
            $end = $shift['shift_time']['end'];
            $days = $shift['shift_days'];
            $shiftFinish = $shift['shift_finishs_next_day'];

            // if (!isset($days[$currentDay]) || $days[$currentDay] != 1) {
            //    info("SKIPPED: Day '$currentDay' is not active for shift '{$shift['shift_name']}' in DB: $dbs");
            //     continue;
            // }
            $now = Carbon::now(); // current time
            $startTime = Carbon::parse($start);
            $endTime = Carbon::parse($end);

            $shiftFinishesNextDay = $shiftFinish;

            $scheduleStart = "00:00";
            $scheduleEnd = "00:00";

            // Initialize shift type
            $shiftType = 0; // 1 for pick, 3 for drop

            // Calculate 2.5 hours (150 minutes) before start and end

            $pickWindowStart = $startTime->copy()->subMinutes(600);
            $dropWindowStart = $endTime->copy()->subMinutes(600);

            // $pickWindowStart = $startTime->copy()->subHours(8);
            // $dropWindowStart = $endTime->copy()->subHours(8);

            // On Saturday should be make schedule for next day finish cases
            if (!isset($days[$currentDay]) || $days[$currentDay] != 1) {
                if (!($currentDay == "SAT" && $shiftFinish == 1 && $now->between($dropWindowStart, $endTime))) {
                    continue;
                }
            }

            // On Monday drop schedule should not make for next day finish cases
            if($currentDay =="MON" && $shiftFinish == 1){
                if ($now->between($dropWindowStart, $endTime)) {
                    continue;
                }
            }

            // Determine if current time is in pick or drop window
            if ($now->between($pickWindowStart, $startTime)) {
                $shiftType = 1; // Pick
                $scheduleStart = $start;
                info("PICK: Now {$now->format('H:i')} is within 2.5 hours before shift start — {$shift['shift_name']}");
            } elseif ($now->between($dropWindowStart, $endTime)) {
                $shiftType = 3; // Drop
                $scheduleEnd = $end;
               info("DROP: Now {$now->format('H:i')} is within 2.5 hours before shift end — {$shift['shift_name']}");
            } else {
               info("SKIPPED: Now {$now->format('H:i')} is not near start or end — {$shift['shift_name']}");
            }
            if ($shiftType === 0) {
                info("Shift time details are currently unavailable — {$shift['shift_name']}");
                continue;
            }

            $shiftDays = [];
            foreach ($days as $day => $value) {
                if ($value == '1') {
                    $shiftDays[strtolower($day)] = 1;
                }
            }


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
                    //"pricebook_id" => 1,
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
        }

        $response =[];
        foreach ($finalOutput as $schedule) {

            try {

                info("Processing schedule for driver_id: {$schedule['driver_id']}, carers: " . implode(',', $schedule['carers']));
                ///info("Group data1 ". print_r($schedule, true));
                $request = new Request(['data' => json_encode($schedule)]);
                //app(ScheduleController::class)->dailyaddSchedule($request);
                $response = app(ScheduleController::class)->addSchedule($request);

            } catch (\Throwable $e) {
                info("Schedule failed: " . $e->getMessage());
            }
        }
        if (empty($response)) {
           return response()->json(['success' => false, 'message' => 'Not found data for schedule']);
        }
        return $response;

    }




    /**
     * @OA\post(
     * path="/uc/api/routeautomation/dailyschedulewithsingledatabasepickandDrop",
     * operationId="dailyschedulewithsingledatabasepickandDrop",
     * tags={"Daily Automation"},
     * summary="Make schedule with single database pick & drop schedules",
     *   security={ {"Bearer": {} }},
     *    description="Make schedule with single database pick & drop schedules",
     *      @OA\Response(
     *          response=201,
     *          description="Automation Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Automation Get Successfully",
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

    public function dailyschedulewithsingledatabasepickandDrop_oldBkup(Request $request){

        $db = DB::connection()->getDatabaseName();

        $currentDate = Carbon::now()->format('Y-m-d');

        $this->connectDB($db);
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
                $shiftFinish = $shift['shift_finishs_next_day'];
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

                //$response = Http::post('http://157.245.99.137/assign-drivers/'. $dbs.'/'.$shiftType);
                $response = Http::post('http://157.245.99.137/assign-drivers/'. $db.'/'.$shiftType.'/'.$employeeShift);
                $dataArray = json_decode($response, true);

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


                $response =[];
                foreach ($finalOutput as $schedule) {
                    try {
                        info("Processing schedule for driver_id: {$schedule['driver_id']}, carers: " . implode(',', $schedule['carers']));
                        ///info("Group data1 ". print_r($schedule, true));
                        $request = new Request(['data' => json_encode($schedule)]);
                        //app(ScheduleController::class)->dailyaddSchedule($request);
                        $response = app(ScheduleController::class)->addSchedule($request);

                    } catch (\Throwable $e) {
                        info("Schedule failed: " . $e->getMessage());
                    }
                }

            }

            info("here shcedule type is ".$shiftType);
        }

        if(empty($response)) {
           return response()->json(['success' => false, 'message' => 'Not found data for schedule']);
        }

        return $response;

    }




     public function dailyschedulewithsingledatabasepickandDrop(Request $request){

        $dbs = DB::connection()->getDatabaseName();
        $defaultdb = env('DB_DATABASE');
        $database = ['current_db' =>$dbs, 'default_db'=> $defaultdb];
        $this->connectDB($defaultdb);
        $currentDB = DB::connection()->getDatabaseName();
        info("after connected db function db name  ". $currentDB);
        ProcessScheduleJob::dispatch($database);

        return response()->json([
            'status' => true,
            'message' => 'Schedule processing started.',
        ]);
        // $currentDate = Carbon::now()->format('Y-m-d');

        // $this->connectDB($dbs);
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

        // $shiftFinish = $shift['shift_finishs_next_day'];

        //     $timeLoop = $shiftFinish == 1 ? ['end' => 3, 'start' => 1] : ['start' => 1, 'end' => 3];

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
        //             info("SKIPPED: Day '$currentDay' is not active for shift '{$shift['shift_name']}' in DB: $dbs");
        //             continue;
        //         }

        //         if($currentDay =="MON" && $shiftFinish == 1){
        //             if ($key == 'end') {
        //                 continue;
        //             }
        //         }

        //         $now = Carbon::now(); // current time
        //         $startTime = Carbon::parse($start);
        //         $endTime = Carbon::parse($end);

        //         $shiftFinishesNextDay = $shiftFinish;

        //         $scheduleStart = $start;
        //         $scheduleEnd = $end;

        //         // Initialize shift type
        //         $shiftType = 0; // 1 for pick, 3 for drop

        //         if ($key == 'start') {
        //             $shiftType = 1;
        //         } elseif ($key == 'end') {
        //             $shiftType = 3;
        //         }

        //         //$response = Http::post('http://157.245.99.137/assign-drivers/'. $dbs.'/'.$shiftType);
        //         $response = Http::post('http://157.245.99.137/assign-drivers/'. $dbs.'/'.$shiftType.'/'.$employeeShift);
        //         $dataArray = json_decode($response, true);

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
        //                 "pick_time" => $scheduleStart,
        //                 "shift_finishes_next_day" => $shiftFinishesNextDay,
        //                 "previous_day_pick" => 0,
        //                 "custom_checked" => 0,
        //                 "infinite_checked" => 0, // modify =1
        //                 "drop_time" => $scheduleEnd,
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

        //                 // $this->connectDB($defaultdb);
        //                 // ProcessScheduleJob::dispatch((array) $request);
        //                 // $this->connectDB($dbs);

        //             } catch (\Throwable $e) {
        //                 info("Schedule failed: " . $e->getMessage());
        //             }
        //         }

        //     }

        //     info("here shcedule type is ".$shiftType);
        // }

        // if(empty($response)) {
        //    return response()->json(['success' => false, 'message' => 'Not found data for schedule']);
        // }

        // return $response;

    }
    /**
     * @OA\get(
     * path="/uc/api/routeautomation/dailyAutomation",
     * operationId="Automation",
     * tags={"Daily Automation"},
     * summary="Get Automation Request",
     *   security={ {"Bearer": {} }},
     *    description="Get Automation Request",
     *      @OA\Response(
     *          response=201,
     *          description="Automation Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Automation Get Successfully",
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

    public function dailyAutomation()
    {

        //$dbs = ['uc_sdna','uc_nikh','uc_tech']; // local db
        //$dbs = ['uc_sdna']; // local db
        //$dbs = ['UC_logisticllp','UC_unify_test']; //live db

        $dbs = ['UC_healthcarellp','UC_logisticllp']; //live db multiple
        //$dbs = ['UC_pctracking']; //live db multiple
        $response =[];
        foreach ($dbs as $db) {

            $this->connectDB($db);
            $shiftTime = HrmsTimeAndShift::get()->toArray();

            //Company lat & long
            $companylocation = CompanyAddresse::get()->first();
            $lat = $companylocation->latitude;
            $long = $companylocation->longitude;

            $now = Carbon::now();
            $currentTime = $now->format('H:i');
            $currentDay = strtoupper($now->format('D'));

            foreach ($shiftTime as $shift) {

                $start = $shift['shift_time']['start'];
                $end = $shift['shift_time']['end'];
                $days = $shift['shift_days'];
                $shiftFinish = $shift['shift_finishs_next_day'];
                $employeeShift = $shift['shift_name'];

                $now = Carbon::now(); // current time
                $startTime = Carbon::parse($start);
                $endTime = Carbon::parse($end);

                $shiftFinishesNextDay = $shiftFinish;

                $scheduleStart = "00:00";
                $scheduleEnd = "00:00";

                // Initialize shift type
                $shiftType = 0; // 1 for pick, 3 for drop

                // Calculate 2.5 hours (150 minutes) before start and end
                $pickWindowStart = $startTime->copy()->subMinutes(150);
                $dropWindowStart = $endTime->copy()->subMinutes(150);


                // Old cod
                // if (!isset($days[$currentDay]) || $days[$currentDay] != 1) {
                //    // info("SKIPPED: Day '$currentDay' is not active for shift '{$shift['shift_name']}' in DB: $db");
                //     continue;
                // }


                // New code
                // On Saturday should be make schedule for next day finish cases
                if (!isset($days[$currentDay]) || $days[$currentDay] != 1) {
                    if (!($currentDay == "SAT" && $shiftFinish == 1 && $now->between($pickWindowStart, $startTime))) {
                        continue;
                    }
                }

                // On Monday drop schedule should not make for next day finish cases
                if($currentDay =="MON" && $shiftFinish == 1){
                    if ($now->between($dropWindowStart, $endTime)) {
                         continue;
                    }
                }


                // Determine if current time is in pick or drop window
                if ($now->between($pickWindowStart, $startTime)) {
                    $shiftType = 1; // Pick
                   // $scheduleStart = $start;
                  //  info("PICK: Now {$now->format('H:i')} is within 2.5 hours before shift start $startTime shift name is — {$shift['shift_name']} database is. $db");
                } elseif ($now->between($dropWindowStart, $endTime)) {
                    $shiftType = 3; // Drop
                   // $scheduleEnd = $end;
                  //  info("DROP: Now {$now->format('H:i')} is within 2.5 hours before shift end  $endTime shift name is — {$shift['shift_name']} database is. $db");
                } else {
                   info("SKIPPED: Now {$now->format('H:i')} is not near start $startTime or end — $endTime shift name - {$shift['shift_name']} database is. $db");
                }

                $scheduleStart = $start;
                $scheduleEnd = $end;

                if ($now->between($pickWindowStart, $startTime) || $now->between($dropWindowStart, $endTime)) {

                    if ($shiftType != 0) {

                        $users = $this->allusers($shiftFinishesNextDay);
                        $drivers =  $this->drivers($shiftType);
                        //$users = $users->toArray();
                        $drivers = $drivers->toArray();

                        $officeLat = $lat;
                        $officeLng = $long;

                        // Update the office_distance field for the user with the given user_id and distance

                        foreach ($users as $employee) {
                            $user = User::find($employee['id']);
                            if ($user) {
                                if (empty($user->office_distance)) {

                                    //$distance = $this->haversine($officeLat, $officeLng, $employee->latitude, $employee->longitude);
                                    $distance = $this->getRoadDistance($officeLat, $officeLng, $employee->latitude, $employee->longitude);
                                    User::where('id', $employee['id'])->update(['office_distance' => $distance]);
                                }

                                //  $distance = $this->getRoadDistance($officeLat, $officeLng, $employee->latitude, $employee->longitude);
                                //  User::where('id', $employee['id'])->update(['office_distance' => $distance]);
                            }
                        }


                        $finalOutput = [];
                        $currentDate = Carbon::now()->format('Y-m-d');

                        $shiftDays = [];
                        foreach ($days as $day => $value) {
                            if ($value == '1') {
                                $shiftDays[strtolower($day)] = 1;
                            }
                        }

                        $priceBooks =  PriceBook::get();
                        $unassignedEmployees = [];

                        //$response = Http::post('http://157.245.99.137/assign-drivers/' . $db);
                        $response = Http::post('http://157.245.99.137/assign-drivers/'. $db.'/'.$shiftType.'/'.$employeeShift);
                        $dataArray = json_decode($response, true);

                        foreach ($dataArray['data'] as $key => $groupData) {

                            if ($groupData['driver_id'] == null) {
                                continue;
                            }
                            $capacity =  $groupData['vehicle_type'] ?? null;
                            $dlatitude = $groupData['assigned_users'][0]['lat'];
                            $dlongitude = $groupData['assigned_users'][0]['lng'];

                            $groupNamePrice = $this->getLocalityName($dlatitude, $dlongitude);

                            // Fetch nearest PriceBook using Haversine formula
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

                           // info("data pricebook lat $ long is. $dlatitude, $dlongitude and locality is $groupNamePrice also capacity is.  $capacity " . print_r(@$nearestPriceBook, true));

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
                                "driver_id" => isset($groupData['driver_id']) ? (int) $groupData['driver_id'] : null,
                                "vehicle_id" => $groupData['vehicle_id'] ?? null,
                                "shift_type_id" => $shiftType,
                                "scheduleLocation" => $groupNamePrice,
                                "scheduleCity" => $groupNamePrice,
                                'selectedLocationLat' => $dlatitude,
                                'selectedLocationLng' => $dlongitude,
                                "pricebook_id" => $nearestPriceBook['id'] ?? $priceBookId, //$priceBookId,
                                "is_repeat" => "1",
                                "carers" => array_map('intval', array_column($groupData['assigned_users'], 'user_id')),
                                "repeat" => "0",
                                "seats" => $groupData['vehicle_type'] ?? null,
                                "reacurrance" => "weekly",
                                "end_date" => $currentDate,
                                "repeat_days" => 1,
                                "repeat_weeks" => "1"
                            ];

                            // Add days (mon, tue, etc.) dynamically
                            $entry = array_merge($entry, $shiftDays);
                            $finalOutput[] = $entry;
                        }

                        $scheduleCreated = "No";

                        $response = [];
                        foreach ($finalOutput as $schedule) {

                            try {
                                $request = new Request(['data' => json_encode($schedule)]);
                                //app(ScheduleController::class)->dailyaddSchedule($request);
                                $response = app(ScheduleController::class)->addSchedule($request);
                                $datas = $response->getData(true);
                                if (isset($datas['success']) && $datas['success'] ==1) {
                                    $scheduleCreated = "Yes";
                                }
                            } catch (\Throwable $e) {
                                //info("Schedule failed: " . $e->getMessage());
                            }
                        }

                        if (!empty($dataArray['unassignedUsers']) && count($dataArray['unassignedUsers']) > 0) {

                            // if($scheduleCreated === "Yes") {
                            //     // Unshined users save in missing_schedule table
                            //     foreach($dataArray['unassignedUsers'] as $unshineduser){
                            //         $missuser = User::find($unshineduser['user_id']);
                            //         if($missuser){

                            //             MissingSchedule::create([
                            //                 'user_id'        => $missuser->id,
                            //                 'first_name'     => $missuser->first_name,
                            //                 'last_name'      => $missuser->last_name,
                            //                 'email'          => $missuser->email,
                            //                 'office_distance'=> $missuser->office_distance,
                            //                 'latitude'       => $missuser->latitude,
                            //                 'longitude'      => $missuser->longitude,
                            //                 'address'        => $missuser->address,
                            //                 'profile_image'  => $missuser->profile_image,
                            //                 'shift_type'     => $missuser->shift_type,
                            //                 'schedule_type'  => $shiftType == 1 ? 'pick' : 'drop',
                            //                 'missing_reason' => 'Driver missing',
                            //                 'date'           => Carbon::today()->toDateString(),
                            //                 'created_at'     => now(),
                            //                 'updated_at'     => now(),
                            //             ]);
                            //         }
                            //     }
                            // }

                            // Mail::send('email.driver_assigned', ['unassignedEmployees' => $dataArray['unassignedUsers']], function ($message) use ($drivers) {
                            //     $message->to('developerphp1995@gmail.com')
                            //         ->subject('Driver Unassignment Notification');
                            // });

                            if($scheduleCreated === "Yes") {
                                ProcessUnscheduledMissingUsers::dispatch($dataArray['unassignedUsers'],  $dataArray['data'], $shiftType, $shiftFinishesNextDay, $scheduleCreated);
                            }
                        }

                        // add in mising schedule users

                        // if($scheduleCreated === "Yes") {
                        //    $this->unschedulemissingUsers($dataArray['unassignedUsers'], $dataArray['data'], $shiftType, $shiftFinishesNextDay, $scheduleCreated);
                        // }



                    } else {
                       // info("SKIPPED: Not the correct time for pick or drop — Now: {$now->format('H:i')} — Shift: {$shift['shift_name']} in DB: $db");
                    }
                } else {

                  //  info(" Not within 2.5 hours before start or end — Now: $currentTime — Shift: {$shift['shift_name']} in DB: $db");
                }
            }
        }

        return $response;
    }



    public function clusterDriversToGroupsUsingKMeans($users, $drivers)
    {
        $totalUsers = count($users);

        // Flatten drivers into seat units
        $availableSeats = [];
        foreach ($drivers as $driver) {
            for ($i = 0; $i < $driver['capacity']; $i++) {
                $availableSeats[] = $driver['id']; // repeat driver id per seat
            }
        }

        // Determine number of clusters (min between drivers and users)
        $clusterCount = min(count($availableSeats), $totalUsers);

        // Prepare points for KMeans
        $points = array_map(function ($user) {
            return [$user['latitude'], $user['longitude']];
        }, $users);

        // Cluster using KMeans
        $clusters = $this->kmeans->cluster($points, $clusterCount);

        // Map cluster indexes to users
        $userClusters = array_fill(0, $clusterCount, []);
        foreach ($clusters as $index => $clusterId) {
            $userClusters[$clusterId][] = $users[$index];
        }

        // Assign clusters to nearest available drivers (based on capacity)
        $assignedDrivers = [];
        $usedDriverIds = [];

        foreach ($userClusters as $groupIndex => $clusterUsers) {
            $groupLat = collect($clusterUsers)->avg('latitude');
            $groupLng = collect($clusterUsers)->avg('longitude');
            $groupSize = count($clusterUsers);

            $nearestDriver = null;
            $minDistance = PHP_FLOAT_MAX;

            foreach ($drivers as $driver) {
                if (in_array($driver['id'], $usedDriverIds)) {
                    continue; // driver already assigned
                }

                if ($driver['capacity'] < $groupSize) {
                    continue; // not enough seats
                }

                $distance = $this->calculateDistance($groupLat, $groupLng, $driver['latitude'], $driver['longitude']);

                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $nearestDriver = $driver;
                }
            }

            if ($nearestDriver) {
                $usedDriverIds[] = $nearestDriver['id'];
                $groupName = "Group " . ($groupIndex + 1);
                $this->saveGroup($groupName, $nearestDriver['id'], $clusterUsers);
            } else {
                // Log or handle unassigned cluster
                $this->log("No driver available for cluster #{$groupIndex} with {$groupSize} users");
            }
        }
    }




    private function getOptimalK(array $data, int $maxK = 10): int
    {
        $wcssValues = [];
        $totalPoints = count($data);

        // Start from k=2 (k=1 WCSS is always the highest)
        for ($k = 2; $k <= $maxK; $k++) {
            $result = $this->kmeans($data, $k);
            $wcssValues[$k] = $result['wcss'];
        }

        // Normalize the k-WCSS values for the "knee" detection
        $ks = array_keys($wcssValues);
        $wcss = array_values($wcssValues);

        // Compute line from first to last point
        $kFirst = $ks[0];
        $kLast = end($ks);
        $wcssFirst = $wcssValues[$kFirst];
        $wcssLast = end($wcssValues);

        $maxDistance = -1;
        $optimalK = $kFirst;

        foreach ($wcssValues as $k => $wcssCurrent) {
            // Coordinates of the line
            $x0 = $kFirst;
            $y0 = $wcssFirst;
            $x1 = $kLast;
            $y1 = $wcssLast;

            // Coordinates of current point
            $x = $k;
            $y = $wcssCurrent;

            // Distance from point to line (perpendicular)
            $numerator = abs(($y1 - $y0) * $x - ($x1 - $x0) * $y + $x1 * $y0 - $y1 * $x0);
            $denominator = sqrt(pow($y1 - $y0, 2) + pow($x1 - $x0, 2));
            $distance = $denominator == 0 ? 0 : $numerator / $denominator;

            if ($distance > $maxDistance) {
                $maxDistance = $distance;
                $optimalK = $k;
            }
        }

        return $optimalK;
    }

    private function kmeans(array $data, int $k, int $maxIterations = 100)
    {
        // Initialize centroids randomly
        $centroids = array_slice($data, 0, $k);

        $iterations = 0;
        $oldAssignments = [];

        do {
            $clusters = array_fill(0, $k, []);
            $assignments = [];

            // Assign each point to the nearest centroid
            foreach ($data as $point) {
                $distances = [];
                foreach ($centroids as $c) {
                    $distances[] = $this->distance($point, $c);
                }

                $clusterIndex = array_keys($distances, min($distances))[0];
                $clusters[$clusterIndex][] = $point;
                $assignments[] = $clusterIndex;
            }

            // Recalculate centroids
            foreach ($clusters as $i => $cluster) {
                if (count($cluster) === 0) continue;

                $latSum = array_sum(array_column($cluster, 'latitude'));
                $lngSum = array_sum(array_column($cluster, 'longitude'));
                $centroids[$i] = [
                    'latitude' => $latSum / count($cluster),
                    'longitude' => $lngSum / count($cluster),
                ];
            }

            $iterations++;
        } while ($assignments !== $oldAssignments && $iterations < $maxIterations);

        // Calculate WCSS
        $wcss = 0.0;
        foreach ($clusters as $i => $cluster) {
            foreach ($cluster as $point) {
                $dist = $this->distance($point, $centroids[$i]);
                $wcss += pow($dist, 2);
            }
        }


        // Final grouped output
        $grouped = [];
        foreach ($clusters as $clusterId => $points) {
            foreach ($points as $p) {
                $grouped[] = [
                    'id' => $p['id'],
                    'first_name' => $p['first_name'],
                    'last_name' => $p['last_name'],
                    'email' => $p['email'],
                    'shift_type' => $p['shift_type'],
                    'office_distance' => $p['office_distance'],
                    'sub_user_id' => $p['id'],
                    'latitude' => $p['latitude'],
                    'longitude' => $p['longitude'],
                    'staff_language' => $p['staff_language'],
                    'schedule_date' => $p['schedule_date'],
                    'cluster' => $clusterId
                ];
            }
        }

        // Return grouped data with WCSS
        return [
            'clusters' => $grouped,
            'wcss' => round($wcss, 6)
        ];
    }


    // Euclidean distance function
    private function distance($a, $b)
    {
        return sqrt(pow($a['latitude'] - $b['latitude'], 2) + pow($a['longitude'] - $b['longitude'], 2));
    }



    // New code 7may start

    function processEmployeeGrouping($employees, $officeLat, $officeLon, $initialRadius = 1, $mergeRadius = 3, $maxGroupSize = 7)
    {
        $initialGroups = $this->createInitialGroups($employees, $initialRadius);
        return $this->mergeGroupsByDirectionAndProximity($initialGroups, $officeLat, $officeLon, $mergeRadius, $maxGroupSize);
    }


    function createInitialGroups($employees, $radius = 1)
    {
        $groups = [];
        $checked = [];

        foreach ($employees as $i => $employee) {
            if (in_array($employee['id'], $checked)) continue;

            $group = [$employee];
            $checked[] = $employee['id'];

            foreach ($employees as $j => $other) {
                if ($i === $j || in_array($other['id'], $checked)) continue;

                $distance = $this->haversineDistance(
                    $employee['latitude'],
                    $employee['longitude'],
                    $other['latitude'],
                    $other['longitude']
                );

                if ($distance <= $radius) {
                    $group[] = $other;
                    $checked[] = $other['id'];
                }
            }

            $groups[] = $group;
        }

        return $groups;
    }



    function mergeGroupsByDirectionAndProximity($groups, $officeLat, $officeLon, $mergeRadius = 3, $maxGroupSize = 7)
    {
        $groupCenters = [];
        $groupDirections = [];

        foreach ($groups as $index => $group) {
            $latSum = 0;
            $lonSum = 0;

            foreach ($group as $emp) {
                $latSum += $emp['latitude'];
                $lonSum += $emp['longitude'];
            }

            $centerLat = $latSum / count($group);
            $centerLon = $lonSum / count($group);
            $bearing = $this->getBearing1($officeLat, $officeLon, $centerLat, $centerLon);
            $direction = $this->getDirectionFromBearing1($bearing);

            $groupCenters[$index] = ['lat' => $centerLat, 'lon' => $centerLon];
            $groupDirections[$index] = $direction;
        }

        $merged = [];
        $used = [];

        foreach ($groups as $i => $groupA) {
            if (in_array($i, $used)) continue;

            $currentGroup = $groupA;
            $used[] = $i;

            for ($j = 0; $j < count($groups); $j++) {
                if ($i == $j || in_array($j, $used)) continue;

                $directionMatch = $groupDirections[$i] === $groupDirections[$j];
                $distance = $this->haversineDistance1(
                    $groupCenters[$i]['lat'],
                    $groupCenters[$i]['lon'],
                    $groupCenters[$j]['lat'],
                    $groupCenters[$j]['lon']
                );

                if ($directionMatch && $distance <= $mergeRadius && (count($currentGroup) + count($groups[$j]) <= $maxGroupSize)) {
                    $currentGroup = array_merge($currentGroup, $groups[$j]);
                    $used[] = $j;
                }
            }

            $merged[] = $currentGroup;
        }

        return $merged;
    }

    function haversineDistance1($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    function getBearing1($lat1, $lon1, $lat2, $lon2)
    {
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);
        $deltaLon = deg2rad($lon2 - $lon1);

        $y = sin($deltaLon) * cos($lat2);
        $x = cos($lat1) * sin($lat2) -
            sin($lat1) * cos($lat2) * cos($deltaLon);

        $bearing = rad2deg(atan2($y, $x));
        return fmod(($bearing + 360), 360);
    }

    function getDirectionFromBearing1($bearing)
    {
        $directions = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
        $index = round($bearing / 45) % 8;
        return $directions[$index];
    }


    // new code 7 may end



    // Old 7 may

    function groupEmployeesByOfficeDistance($employees, $distance_threshold = 5)
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


    //Old 7 may
    function refineGroups($groups, $radius = 2)
    {

        $companylocation = CompanyAddresse::get()->first();
        $lat = $companylocation->latitude;
        $long = $companylocation->longitude;

        $companyLat = $lat;
        $companyLon = $long;

        // Flatten employee list
        $allEmployees = [];
        foreach ($groups as $group) {
            $allEmployees = array_merge($allEmployees, $group);
        }

        // Assign direction sector to each employee
        foreach ($allEmployees as &$employee) {
            $bearing = $this->getBearing($companyLat, $companyLon, $employee['latitude'], $employee['longitude']);
            $employee['direction'] = $this->getDirectionFromBearing($bearing);
        }

        // Group employees by direction first
        $directionGroups = [];
        foreach ($allEmployees as $employee) {
            $directionGroups[$employee['direction']][] = $employee;
        }

        // Now cluster within each direction group by distance
        $newGroups = [];
        $groupIndex = 1;
        foreach ($directionGroups as $direction => $employees) {
            $checked = [];

            foreach ($employees as $employee) {
                if (in_array($employee['id'], $checked)) continue;

                $group = [$employee];
                $checked[] = $employee['id'];

                foreach ($employees as $other) {
                    if (in_array($other['id'], $checked)) continue;

                    $dist = $this->haversineDistance($employee['latitude'], $employee['longitude'], $other['latitude'], $other['longitude']);

                    if ($dist <= $radius) {
                        $group[] = $other;
                        $checked[] = $other['id'];
                    }
                }

                $newGroups["Group " . $groupIndex . " (" . $direction . ")"] = $group;
                $groupIndex++;
            }
        }

        return $newGroups;
    }



    function getBearing($lat1, $lon1, $lat2, $lon2)
    {
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);
        $deltaLon = deg2rad($lon2 - $lon1);

        $y = sin($deltaLon) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($deltaLon);
        $bearing = atan2($y, $x);
        $bearing = rad2deg($bearing);
        return ($bearing + 360) % 360; // Normalize to 0–360 degrees
    }


    function getDirectionFromBearing($bearing)
    {
        $directions = ['North', 'North-East', 'East', 'South-East', 'South', 'South-West', 'West', 'North-West'];
        $index = round($bearing / 45) % 8;
        return $directions[$index];
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


    // Old code
    // function assignDriversToGroups($groups, $drivers){

    //     return $groups;
    //     // Sort drivers by capacity ASC to help in combination logic
    //     usort($drivers, function ($a, $b) {
    //         return $a['capacity'] - $b['capacity'];
    //     });

    //     $newGroups = [];
    //     $usedDrivers = []; // Keep track of used drivers
    //     $groupCounters = [];
    //     $remainingEmployees = [];

    //     // Phase 1: Assign drivers to groups based on the best combinations
    //     foreach ($groups as $groupName => $employees) {
    //         $employeeCount = count($employees);
    //         $bestCombination = $this->getBestDriverCombination($drivers, $employeeCount);

    //         if (!$bestCombination) {
    //             // If no combination found, assign all employees without a driver
    //             $groupCounters[$groupName] = ($groupCounters[$groupName] ?? 0) + 1;
    //             $newGroups[] = [
    //                 'group' => $groupName . ' - Group ' . $groupCounters[$groupName] . ' (Unassigned)',
    //                 'employees' => $employees,
    //                 'driver' => null
    //             ];
    //             continue;
    //         }

    //         $start = 0;
    //         foreach ($bestCombination as $driver) {
    //             if (in_array($driver['id'], $usedDrivers)) {
    //                 // Skip this driver if already used
    //                 continue;
    //             }

    //             $take = min($driver['capacity'], $employeeCount - $start);
    //             $assignedEmployees = array_slice($employees, $start, $take);
    //             $start += $take;

    //             $groupCounters[$groupName] = ($groupCounters[$groupName] ?? 0) + 1;
    //             $newGroups[] = [
    //                 'group' => $groupName . ' - Group ' . $groupCounters[$groupName],
    //                 'employees' => $assignedEmployees,
    //                 'driver' => $driver
    //             ];

    //             // Mark driver as used
    //             $usedDrivers[] = $driver['id'];
    //         }

    //         // Store unassigned employees in case of unassigned groups
    //         if ($start < $employeeCount) {
    //             $remainingEmployees[$groupName] = array_slice($employees, $start);
    //         }
    //     }

    //     // Phase 2: Assign remaining employees to available drivers
    //     foreach ($remainingEmployees as $groupName => $employees) {
    //         $remainingCount = count($employees);

    //         foreach ($drivers as $driver) {
    //             if (in_array($driver['id'], $usedDrivers)) {
    //                 // Skip this driver if already used
    //                 continue;
    //             }

    //             // Check if the driver has available capacity
    //             $availableSeats = $driver['capacity'] - ($driver['assigned'] ?? 0);
    //             if ($availableSeats > 0) {
    //                 $take = min($availableSeats, $remainingCount);
    //                 $assignedEmployees = array_slice($employees, 0, $take);
    //                 $remainingCount -= $take;

    //                 $newGroups[] = [
    //                     'group' => $groupName . ' - Extra Group',
    //                     'employees' => $assignedEmployees,
    //                     'driver' => $driver
    //                 ];

    //                 // Mark driver as used
    //                 $usedDrivers[] = $driver['id'];

    //                 // Break early if all employees are assigned
    //                 if ($remainingCount <= 0) {
    //                     break;
    //                 }
    //             }
    //         }

    //         // If employees are still left and no driver can take them
    //         if ($remainingCount > 0) {
    //             $newGroups[] = [
    //                 'group' => $groupName . ' - Remaining Unassigned',
    //                 'employees' => $employees,
    //                 'driver' => null
    //             ];
    //         }
    //     }

    //     return $newGroups;
    // }






    // 9th may code


    function assignDriversToGroups($groups, $drivers)
    {
        // Sort drivers by capacity ASC to help in combination logic
        usort($drivers, function ($a, $b) {
            return $a['capacity'] - $b['capacity'];
        });

        $newGroups = [];
        $usedDrivers = []; // Keep track of used drivers
        $groupCounters = [];
        $remainingEmployees = [];

        // Phase 1: Assign drivers to groups based on the best combinations
        foreach ($groups as $groupName => $employees) {
            $employeeCount = count($employees);
            $bestCombination = $this->getBestDriverCombination($drivers, $employeeCount);

            if (!$bestCombination) {
                // If no combination found, assign all employees without a driver
                $groupCounters[$groupName] = ($groupCounters[$groupName] ?? 0) + 1;
                $newGroups[] = [
                    'group' => $groupName . ' - Group ' . $groupCounters[$groupName] . ' (Unassigned)',
                    'employees' => $employees,
                    'driver' => null
                ];
                continue;
            }

            $start = 0;
            foreach ($bestCombination as $driver) {
                if (in_array($driver['id'], $usedDrivers)) {
                    // Skip this driver if already used
                    continue;
                }

                $take = min($driver['capacity'], $employeeCount - $start);
                $assignedEmployees = array_slice($employees, $start, $take);
                $start += $take;

                $groupCounters[$groupName] = ($groupCounters[$groupName] ?? 0) + 1;
                $newGroups[] = [
                    'group' => $groupName . ' - Group ' . $groupCounters[$groupName],
                    'employees' => $assignedEmployees,
                    'driver' => $driver
                ];

                // Mark driver as used
                $usedDrivers[] = $driver['id'];
            }

            // Store unassigned employees in case of unassigned groups
            if ($start < $employeeCount) {
                $remainingEmployees[$groupName] = array_slice($employees, $start);
            }
        }

        // Phase 2: Assign remaining employees to available drivers
        foreach ($remainingEmployees as $groupName => $employees) {
            $remainingCount = count($employees);

            foreach ($drivers as $driver) {
                if (in_array($driver['id'], $usedDrivers)) {
                    // Skip this driver if already used
                    continue;
                }

                // Check if the driver has available capacity
                $availableSeats = $driver['capacity'] - ($driver['assigned'] ?? 0);
                if ($availableSeats > 0) {
                    $take = min($availableSeats, $remainingCount);
                    $assignedEmployees = array_slice($employees, 0, $take);
                    $remainingCount -= $take;

                    $newGroups[] = [
                        'group' => $groupName . ' - Extra Group',
                        'employees' => $assignedEmployees,
                        'driver' => $driver
                    ];

                    // Mark driver as used
                    $usedDrivers[] = $driver['id'];

                    // Break early if all employees are assigned
                    if ($remainingCount <= 0) {
                        break;
                    }
                }
            }

            // If employees are still left and no driver can take them
            if ($remainingCount > 0) {
                $newGroups[] = [
                    'group' => $groupName . ' - Remaining Unassigned',
                    'employees' => $employees,
                    'driver' => null
                ];
            }
        }

        return $newGroups;
    }


    // code


    function getBearing2($lat1, $lon1, $lat2, $lon2)
    {
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLon = $lon2 - $lon1;
        $y = sin($dLon) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);
        $initial_bearing = rad2deg(atan2($y, $x));
        // Normalize the bearing to 0-360 degrees
        $compass_bearing = (fmod($initial_bearing + 360, 360));

        return $compass_bearing;
    }


    // Function to get the direction (N, S, E, W) based on the bearing
    function getDirectionFromBearing2($bearing)
    {
        if ($bearing >= 0 && $bearing < 45) {
            return 'North';
        } elseif ($bearing >= 45 && $bearing < 135) {
            return 'East';
        } elseif ($bearing >= 135 && $bearing < 225) {
            return 'South';
        } elseif ($bearing >= 225 && $bearing < 315) {
            return 'West';
        } else {
            return 'North';
        }
    }


    // Function to calculate the distance between two geographic points (Haversine formula)
    function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371;  // Radius of the Earth in km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $R * $c;  // Distance in km
        return $distance;
    }



    // Function to assign locality to each employee based on proximity to known localities
    function assignLocality($employeeLat, $employeeLon, $localities)
    {
        $closestLocality = '';
        $minDistance = PHP_INT_MAX;

        foreach ($localities as $locality => $coords) {
            $distance = $this->getDistance($employeeLat, $employeeLon, $coords['lat'], $coords['lon']);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestLocality = $locality;
            }
        }

        return $closestLocality;
    }




    // Function to group employees based on locality, proximity (grid), and direction (bearing)

    function groupEmployees($employees, $officeLat, $officeLon, $localities, $maxGroupSize = 7)
    {
        $finalGroups = [];
        $groupedEmployeeIds = [];
        $bearingBuckets = [];

        // Step 1: Assign bearing and locality
        foreach ($employees as $employee) {
            if (!isset($employee['latitude'], $employee['longitude'])) {
                continue;
            }

            $employee['locality'] = $this->assignLocality($employee['latitude'], $employee['longitude'], $localities);
            $employee['bearing'] = $this->getBearing2($officeLat, $officeLon, $employee['latitude'], $employee['longitude']);

            $bucketKey = floor($employee['bearing'] / 30) * 30;
            $bearingBuckets[$bucketKey][] = $employee;
        }

        // Step 2: Group employees in each bearing bucket
        foreach ($bearingBuckets as $bucket => $employeesInBucket) {
            while (!empty($employeesInBucket)) {
                $base = array_shift($employeesInBucket);
                $cluster = [$base];
                $usedKeys = [];
                $groupedEmployeeIds[] = $base['id'];

                foreach ([1, 2, 3] as $radiusKm) {
                    foreach ($employeesInBucket as $key => $other) {
                        if (count($cluster) >= $maxGroupSize) break;

                        if (
                            $other['locality'] === $base['locality'] &&
                            !in_array($other['id'], $groupedEmployeeIds)
                        ) {
                            $distance = $this->haversineDistance(
                                $base['latitude'],
                                $base['longitude'],
                                $other['latitude'],
                                $other['longitude']
                            );

                            if ($distance <= $radiusKm) {
                                $cluster[] = $other;
                                $groupedEmployeeIds[] = $other['id'];
                                $usedKeys[] = $key;
                            }
                        }
                    }

                    if (count($cluster) >= $maxGroupSize) break;
                }

                foreach ($usedKeys as $key) {
                    unset($employeesInBucket[$key]);
                }
                $employeesInBucket = array_values($employeesInBucket);

                // ✅ Only add group if it has 3 or more members
                if (count($cluster) >= 3) {
                    $finalGroups[] = [
                        'bearing_bucket' => $bucket,
                        'locality' => $base['locality'],
                        'group' => $cluster,
                    ];
                }
            }
        }

        return $finalGroups;
    }


    function createEmployeeGroups($employees)
    {
        $maxDistance = 4; // km
        $maxBearingDiff = 45; // degrees
        $maxGroupSize = 7;
        $minGroupSize = 5;

        $employees = $employees->toArray();

        // Get company coordinates
        $companylocation = CompanyAddresse::first();
        $officeLat = $companylocation->latitude;
        $officeLng = $companylocation->longitude;

        // Step 1: Add bearing to each employee
        foreach ($employees as &$employee) {
            $employee['bearing'] = $this->getBearing(
                $officeLat,
                $officeLng,
                $employee['latitude'],
                $employee['longitude']
            );
        }

        $unassigned = $employees;
        $groups = [];

        // Step 2: Form base groups using proximity
        while (!empty($unassigned)) {
            $base = array_shift($unassigned);
            $group = [$base];
            $toRemove = [];

            foreach ($unassigned as $index => $other) {
                $distance = $this->haversineGreatCircleDistance(
                    $base['latitude'],
                    $base['longitude'],
                    $other['latitude'],
                    $other['longitude']
                );
                $bearingDiff = abs($base['bearing'] - $other['bearing']);
                $bearingDiff = min($bearingDiff, 360 - $bearingDiff);

                if ($distance <= $maxDistance && $bearingDiff <= $maxBearingDiff && count($group) < $maxGroupSize) {
                    $group[] = $other;
                    $toRemove[] = $index;
                }
            }

            foreach ($toRemove as $index) {
                unset($unassigned[$index]);
            }

            $unassigned = array_values($unassigned);
            $groups[] = $group;
        }

        // Step 3: Try to combine small groups into larger ones
        $finalGroups = [];

        while (!empty($groups)) {
            $group = array_shift($groups);

            // Try to fill up group if it's smaller than minGroupSize
            if (count($group) < $minGroupSize) {
                $bestMatch = -1;
                $bestDistance = PHP_INT_MAX;

                foreach ($groups as $index => $candidate) {
                    if (count($group) + count($candidate) > $maxGroupSize) continue;

                    $centerA = $this->getCenterPoint($group);
                    $centerB = $this->getCenterPoint($candidate);

                    $distance = $this->haversineGreatCircleDistance(
                        $centerA['lat'],
                        $centerA['lng'],
                        $centerB['lat'],
                        $centerB['lng']
                    );

                    if ($distance < $bestDistance) {
                        $bestDistance = $distance;
                        $bestMatch = $index;
                    }
                }

                if ($bestMatch >= 0) {
                    $group = array_merge($group, $groups[$bestMatch]);
                    unset($groups[$bestMatch]);
                    $groups = array_values($groups);
                }
            }

            $finalGroups[] = $group;
        }

        return $finalGroups;
    }


    private function getCenterPoint($group)
    {
        $lat = array_column($group, 'latitude');
        $lng = array_column($group, 'longitude');

        return [
            'lat' => array_sum($lat) / count($lat),
            'lng' => array_sum($lng) / count($lng),
        ];
    }



    function haversineGreatCircleDistance($lat1, $lon1, $lat2, $lon2, $earthRadius = 6371)
    {
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }




    // Function to calculate bearing between two coordinates
    function getBearingNew($lat1, $lng1, $lat2, $lng2)
    {
        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($lng1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($lng2);

        $dLng = $lng2 - $lng1;
        $y = sin($dLng) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLng);
        $bearing = atan2($y, $x);
        return (rad2deg($bearing) + 360) % 360; // Normalize to 0-360 degrees
    }





    // end new 8 may code


    function getBestDriverCombination($drivers, $target)
    {
        $best = null;
        $minWaste = PHP_INT_MAX;

        $totalDrivers = count($drivers);
        $maxCombinations = pow(2, $totalDrivers);

        for ($i = 1; $i < $maxCombinations; $i++) {
            $combo = [];
            $capacitySum = 0;

            for ($j = 0; $j < $totalDrivers; $j++) {
                if ($i & (1 << $j)) {
                    $combo[] = $drivers[$j];
                    $capacitySum += $drivers[$j]['capacity'];
                }
            }

            if ($capacitySum >= $target) {
                $waste = $capacitySum - $target;
                if ($waste < $minWaste || ($waste == $minWaste && count($combo) < count($best))) {
                    $best = $combo;
                    $minWaste = $waste;
                }
            }
        }

        return $best;
    }



    // Function to group employees based on distance
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



    public function allusers($shiftFinishesNextDay, $employeeshift= null)
    {

        $companylocation = CompanyAddresse::get()->first();
        $officeLat = $companylocation->latitude;
        $officeLng = $companylocation->longitude;

        // Delete tep location address
        $subusertemlocations = SubUserAddresse::whereNotNull('schedule_carer_relocations_id')->get();
        foreach ($subusertemlocations as $location) {
            if (Carbon::parse($location->end_date)->lte(Carbon::yesterday())) {
                $location->delete();
            }
        }

        $users = User::whereNotNull('latitude')
            ->whereNotNull('longitude')
                ->select(
                'id',
                'first_name',
                'last_name',
                'latitude',
                'longitude',
                'email',
                'shift_type',
                'office_distance',
            )->get();

            foreach ($users as $employee) {
                $user = User::find($employee['id']);
                if ($user) {
                    if (empty($user->office_distance)) {
                        $distance = $this->getRoadDistance($officeLat, $officeLng, $employee->latitude, $employee->longitude);
                        User::where('id', $employee['id'])->update(['office_distance' => $distance]);
                    }
                }
            }

        $today = now()->toDateString();
        $currentHour = now()->hour;

        $leaveStaffIds = Leave::whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->where('status', 1)
            ->get()
            ->filter(function ($leave) use ($currentHour) {
                if ($leave->type == 1) {
                    return true; // Full day leave — include
                } elseif ($leave->type == 2 && $currentHour < 12) {
                    return true; // Morning half — include only in morning
                } elseif ($leave->type == 3 && $currentHour >= 12) {
                    return true; // Evening half — include only in afternoon
                }
                return false;
            })
            ->pluck('staff_id')
            ->unique()
            ->toArray();

        // Step 3: Exclude users who have resigned from the list
        $resignedUserIds = DB::table('resignations')
            ->pluck('user_id')
            ->toArray();

        // Step 1: Get the latest address IDs per sub_user
        // $latestAddressIds = DB::table('sub_user_addresses')
        //     ->selectRaw('MAX(id) as id')
        //     ->groupBy('sub_user_id');


        // $latestAddressIds = DB::table('sub_user_addresses')
        // ->selectRaw('MAX(id) as id')
        // ->whereNotNull('latitude')
        // ->whereNotNull('longitude')
        // ->where(function ($query) {
        //     $query->whereDate('end_date', '>=', now()->toDateString())
        //         ->orWhereNull('end_date');
        // })
        // ->groupBy('sub_user_id');


        $latestAddressIds = DB::table('sub_user_addresses')
        ->selectRaw('MAX(id) as id')
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->where(function ($query) {
            $query->where(function ($q) {
                $q->whereDate('start_date', '<=', now()->toDateString())
                ->orWhereNull('start_date');
            })->where(function ($q) {
                $q->whereDate('end_date', '>=', now()->toDateString())
                ->orWhereNull('end_date');
            });
        })
        ->groupBy('sub_user_id');


        // Step 2: Get the full latest address rows, aliasing the id to avoid conflict
        $latestAddresses = DB::table('sub_user_addresses as a')
            ->joinSub($latestAddressIds, 'latest', function ($join) {
                $join->on('a.id', '=', 'latest.id');
            })->select('a.id as address_id', 'a.sub_user_id', 'a.latitude', 'a.longitude', 'a.start_date', 'a.end_date');

        // Step 3: Join with users and filter
        return User::joinSub($latestAddresses, 'sub_user_addresses', function ($join) {
            $join->on('users.id', '=', 'sub_user_addresses.sub_user_id');
        })
            ->whereHas('roles', function ($query) {
                $query->where('role_id', 4);
            })
            ->whereNotNull('sub_user_addresses.latitude')
            ->whereNotNull('sub_user_addresses.longitude')
            ->whereNotIn('users.id', $leaveStaffIds)
            ->where(function ($query) {
                $query->whereDate('sub_user_addresses.end_date', '>=', now()->toDateString())
                    ->orWhereNull('sub_user_addresses.end_date');
            })
            //->whereNotIn('users.id', $resignedUserIds)  // Exclude resigned users
            ->where('office_distance', '<=', 20)
            ->where('users.shift_type', $shiftFinishesNextDay)
            ->where('users.employee_shift', $employeeshift)
            ->where('users.no_show', "No")
            ->where('users.cab_facility', 1)
            ->whereIn('users.status', [1, 3, 4])
            ->where('users.close_account', 1)
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.shift_type',
                'users.office_distance',
                'sub_user_addresses.sub_user_id',
                'sub_user_addresses.latitude',
                'sub_user_addresses.longitude'
            )
            ->orderBy('sub_user_addresses.sub_user_id', 'asc')
            ->get();
    }


    public function drivers($shiftType)
    {

        $today = now()->toDateString();

        // Step 1: Get the latest address IDs per sub_user
        $latestAddressIds = DB::table('sub_user_addresses as sua')->select(['sua.sub_user_id', DB::raw('MAX(sua.id) as addr_id'),])->groupBy('sua.sub_user_id');


        // $subUsers = SubUser::whereHas('roles', function ($query) {
        //     $query->where('role_id', 5);
        // })
        //     ->joinSub($latestAddressIds, 'latest_addr', function ($join) {
        //         $join->on('sub_users.id', '=', 'latest_addr.sub_user_id');
        //     })
        //     ->join('sub_user_addresses as sua', function ($join) {
        //         $join->on('sua.id', '=', 'latest_addr.addr_id');
        //     })
        //     ->join('vehicles', 'vehicles.driver_id', '=', 'sub_users.id')
        //     ->whereNotNull('sua.latitude')
        //     ->whereNotNull('sua.longitude')
        //     ->where(function ($query) use ($shiftType) {
        //         $query->where('vehicles.shift_type_id', $shiftType)
        //             ->orWhere('vehicles.shift_type_id', 2);
        //     })
        //     ->whereDoesntHave('schedulesAsDriver', function ($query) use ($today, $shiftType) {
        //         $query->whereDate('start_date', $today)
        //             ->where('shift_type', $shiftType);
        //     })
        //     ->with('vehicle')
        //     ->select(
        //         'sub_users.id',
        //         'sub_users.first_name',
        //         'sub_users.last_name',
        //         'sub_users.email',
        //         'sub_users.profile_image',
        //         'sua.latitude',
        //         'sua.longitude',
        //         'sua.sub_user_id'
        //     )
        //     ->distinct('sub_users.id')
        //     ->get()
        //     ->map(function ($user) {
        //         // Flatten vehicle details
        //         $vehicle = $user->vehicle;
        //         return [
        //             'id' => $user->id,
        //             'first_name' => $user->first_name,
        //             'last_name' => $user->last_name,
        //             'email' => $user->email,
        //             'profile_image' => $user->profile_image,
        //             'sub_user_id' => $user->sub_user_id,
        //             'latitude' => $user->latitude,
        //             'longitude' => $user->longitude,
        //             'vehicle_id' => $vehicle->id ?? null,
        //             'vehicle_name' => $vehicle->name ?? null,
        //             'vehicle_no' => $vehicle->vehicle_no ?? null,
        //             'capacity' => $vehicle->seats ?? null,
        //             'chasis_no' => $vehicle->chasis_no ?? null,
        //             'color' => $vehicle->color ?? null,
        //             'registration_no' => $vehicle->registration_no ?? null,
        //             'shift_type_id' => $vehicle->shift_type_id ?? null,
        //         ];
        //     });

        // return $subUsers;

        $driversassigned = SubUser::whereHas('roles', function ($query) {
            $query->where('role_id', 5);
        })
        ->joinSub($latestAddressIds, 'latest_addr', function ($join) {
            $join->on('sub_users.id', '=', 'latest_addr.sub_user_id');
        })
        ->join('sub_user_addresses as sua', function ($join) {
            $join->on('sua.id', '=', 'latest_addr.addr_id');
        })
        ->join('vehicles', 'vehicles.driver_id', '=', 'sub_users.id')
        ->whereNotNull('sua.latitude')
        ->whereNotNull('sua.longitude')
        ->where(function ($query) use ($shiftType) {
            $query->where('vehicles.shift_type_id', $shiftType)
                ->orWhere('vehicles.shift_type_id', 2);
        })
        ->whereHas('schedulesAsDriver', function ($query) use ($today) {
            $query->whereDate('date', $today);
        })
        ->with('vehicle')
        ->select(
            'sub_users.id',
            'sub_users.first_name',
            'sub_users.last_name',
            'sub_users.email',
            'sub_users.profile_image',
            'sua.latitude',
            'sua.longitude',
            'sua.sub_user_id'
        )
        ->distinct('sub_users.id')
        ->get()
        ->map(function ($user) {
            $vehicle = $user->vehicle;
            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'profile_image' => $user->profile_image,
                'sub_user_id' => $user->sub_user_id,
                'latitude' => $user->latitude,
                'longitude' => $user->longitude,
                'vehicle_id' => $vehicle->id ?? null,
                'vehicle_name' => $vehicle->name ?? null,
                'vehicle_no' => $vehicle->vehicle_no ?? null,
                'capacity' => $vehicle->seats ?? null,
                'chasis_no' => $vehicle->chasis_no ?? null,
                'color' => $vehicle->color ?? null,
                'registration_no' => $vehicle->registration_no ?? null,
                'shift_type_id' => $vehicle->shift_type_id ?? null,
            ];
        });

        $maxSchedules = ($shiftType == 2) ? 2 : 1;
        $subUsers = SubUser::whereHas('roles', function ($query) {
                $query->where('role_id', 5);
            })
            ->joinSub($latestAddressIds, 'latest_addr', function ($join) {
                $join->on('sub_users.id', '=', 'latest_addr.sub_user_id');
            })
            ->join('sub_user_addresses as sua', function ($join) {
                $join->on('sua.id', '=', 'latest_addr.addr_id');
            })
            ->join('vehicles', 'vehicles.driver_id', '=', 'sub_users.id')
            ->whereNotNull('sua.latitude')
            ->whereNotNull('sua.longitude')
            ->where(function ($query) use ($shiftType) {
                $query->where('vehicles.shift_type_id', $shiftType)
                    ->orWhere('vehicles.shift_type_id', 2);
            })
            ->with([
                'vehicle',
                'schedulesAsDriver' => function ($q) use ($today, $shiftType) {
                    $q->whereDate('date', $today)
                    ->where('shift_type_id', $shiftType);
                }
            ])
            ->select(
                'sub_users.id',
                'sub_users.first_name',
                'sub_users.last_name',
                'sub_users.email',
                'sub_users.profile_image',
                'sua.latitude',
                'sua.longitude',
                'sua.sub_user_id'
            )
            ->distinct('sub_users.id')
            ->get();

        // ✅ Filter based on schedule count logic
        $subUsers = $subUsers->filter(function ($user) use ($maxSchedules) {
            return $user->schedulesAsDriver->count() < $maxSchedules;
        })->values();

        // ✅ Map to formatted result
        $subUsers = $subUsers->map(function ($user) {
            $vehicle = $user->vehicle;
            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'profile_image' => $user->profile_image,
                'sub_user_id' => $user->sub_user_id,
                'latitude' => $user->latitude,
                'longitude' => $user->longitude,
                'vehicle_id' => $vehicle->id ?? null,
                'vehicle_name' => $vehicle->name ?? null,
                'vehicle_no' => $vehicle->vehicle_no ?? null,
                'capacity' => $vehicle->seats ?? null,
                'chasis_no' => $vehicle->chasis_no ?? null,
                'color' => $vehicle->color ?? null,
                'registration_no' => $vehicle->registration_no ?? null,
                'shift_type_id' => $vehicle->shift_type_id ?? null,
            ];
        });

        return [
            'driversAssigned' => $driversassigned,
            'driversUnassigned' => $subUsers,
        ];

    }


    function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);

        $a = sin($dLat / 2) ** 2 +
            sin($dLon / 2) ** 2 * cos($lat1) * cos($lat2);
        $c = 2 * asin(sqrt($a));

        return $earthRadius * $c;
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



    public function getRoadDistance($officeLat, $officeLng, $employeeLat, $employeeLng)
    {
        $apiKey = 'AIzaSyAjESclgKTCpmdmHKc7bzCKIQtYe_vIbb4'; // Replace with your actual Google API Key

        // Construct the request URL
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$officeLat},{$officeLng}&destinations={$employeeLat},{$employeeLng}&key={$apiKey}";

        // Send the HTTP request with SSL verification disabled for local development
        $response = Http::withOptions(['verify' => false])->get($url);

        // Check if the API request was successful
        if ($response->successful()) {
            $data = $response->json();
            // $distanceInMeters = $data['rows'][0]['elements'][0]['distance']['value']; // Distance in meters
            // $distanceInKm = $distanceInMeters / 1000; // Convert to kilometers
            // return $distanceInKm;

                $element = $data['rows'][0]['elements'][0] ?? null;
                // Check if 'distance' key exists and status is OK
                if (isset($element['status']) && $element['status'] === 'OK' && isset($element['distance']['value'])) {
                    $distanceInMeters = $element['distance']['value'];
                    $distanceInKm = $distanceInMeters / 1000;
                    return $distanceInKm;
                } else {
                    // Skip if distance is not available
                    return null;
                }

        } else {
            // Handle error (e.g., API limit exceeded, invalid request, etc.)
            return null; // or handle accordingly
        }
    }


    /**
     * @OA\get(
     * path="/uc/api/routeautomation/dailyAutomationDelete",
     * operationId="dailyAutomationDelete",
     * tags={"Daily Automation"},
     * summary="Delete Automation Request",
     *   security={ {"Bearer": {} }},
     *    description="delete Automation Request",
     *      @OA\Response(
     *          response=201,
     *          description="Automation delete Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Automation delete Successfully",
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

    public function dailyAutomationDelete()
    {

        try {

            //$dbs = ['uc_sdna','uc_nikh','uc_tech']; // local db
            //$dbs = ['UC_unifytest','UC_logisticllp'];  // live db
            $dbs = ['UC_unifytest'];  // live db

            foreach ($dbs as $db) {
                $this->connectDB($db);

                // First delete child table data
                DailyScheduleCarer::query()->delete();
                // Then delete parent table data
                DailySchedule::query()->delete();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Daily schedules and related data deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting daily schedules: ' . $e->getMessage(),
            ], 500);
        }
    }



    /**
     * @OA\post(
     *     path="/uc/api/routeautomation/dailyUnshinedEmployee",
     *     operationId="dailyUnshinedEmployee",
     *     tags={"Daily Automation"},
     *     summary="Unshined Automation Request",
     *     security={{"Bearer": {}}},
     *     description="Unshined Automation Request",
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *                 @OA\Schema(
     *                     required={"month", "filtertype"},
     *                     @OA\Property(
     *                         property="month",
     *                         type="string",
     *                         format="date",
     *                         description="Month in YYYY-MM format",
     *                         example="2025-05"
     *                     ),
     *                     @OA\Property(
     *                         property="filtertype",
     *                         type="string",
     *                         description="Choose one of: unshinedUsers, unshinedDriver, underAssignedDrivers",
     *                         example="unshinedUsers"
     *                     )
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Automation Unshined Successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Automation Unshined Successfully",
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

    public function dailyUnshinedEmployee(Request $request)
    {


        try {

            //$today = now()->toDateString();

            $filterTypeArray  = $request->input('filtertype');
            $startDate = Carbon::parse($request->input('month')); //now()->startOfMonth(); // Start of current month
            $endDate = now(); // Today

            $resultsByDate = [];

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $today = $date->toDateString();
                $result = [];

                // 1. unshinedUsers
                if ($filterTypeArray === 'unshinedUsers') {

                    // Unshined users list
                    // $unshinedUsers = User::whereHas('roles', function ($query) {
                    //     $query->where('role_id', 4);
                    // })
                    //     ->whereNotNull('latitude')
                    //     ->whereNotNull('longitude')
                    //     ->whereNotNull('shift_type')
                    //     ->select('id', 'first_name', 'last_name', 'email', 'office_distance', 'latitude', 'longitude', 'address', 'profile_image', 'shift_type')
                    //     ->get()
                    //     ->map(function ($user) use ($today) {
                    //         $existingTypes = $user->scheduleCarers()
                    //             ->whereDate('created_at', $today)
                    //             ->pluck('shift_type')
                    //             ->toArray();

                    //         if (in_array('pick', $existingTypes) && !in_array('drop', $existingTypes)) {
                    //             $user->schedule_type = 'drop';
                    //         } elseif (in_array('drop', $existingTypes) && !in_array('pick', $existingTypes)) {
                    //             $user->schedule_type = 'pick';
                    //         } elseif (!in_array('pick', $existingTypes) && !in_array('drop', $existingTypes)) {
                    //             $user->schedule_type = 'pick and drop'; // default when both missing
                    //         } else {
                    //             $user->schedule_type = null; // both exist — exclude
                    //         }

                    //         return $user;
                    //     })
                    //     ->filter(function ($user) {
                    //         return $user->schedule_type !== null;
                    //     })
                    //     ->values();


                    //$missingschedule = MissingSchedule::where('date',$today)->get();

                    $result['data'] = [];
                    MissingSchedule::where('date', $today)
                        ->chunk(500, function ($schedules) use (&$result) {
                            foreach ($schedules as $schedule) {
                                $result['data'][] = $schedule;
                            }
                        });

                    //$result['data'] = $unshinedUsers;
                    //$result['data'] = $missingschedule;
                }

                // 2. unshinedDriver

                if ($filterTypeArray === 'unshinedDriver') {

                    $unshinedDriver = SubUser::whereHas('roles', function ($query) {
                        $query->where('role_id', 5); // Driver role
                    })
                        ->with([
                            'vehicle',
                            'schedulesAsDriver' => function ($query) use ($today) {
                                $query->whereDate('created_at', $today);
                            }
                        ])
                        ->select('id', 'first_name', 'last_name', 'email', 'profile_image')
                        ->get()
                        ->map(function ($driver) {
                            $vehicle = $driver->vehicle;

                            if (!$vehicle) {
                                $driver->missing_schedules = [];
                                return $driver;
                            }

                            // Get today's existing shift types for this driver
                            $existingTypes = collect();

                            foreach ($driver->schedulesAsDriver as $schedule) {
                                if ($schedule->shift_type_id == 1) {
                                    $existingTypes->push('pick');
                                } elseif ($schedule->shift_type_id == 3) {
                                    $existingTypes->push('drop');
                                } elseif ($schedule->shift_type_id == 2) {
                                    // Both pick and drop
                                    $existingTypes->push('pick');
                                    $existingTypes->push('drop');
                                }
                            }

                            $existingTypes = $existingTypes->unique()->toArray();

                            // Initialize missing_schedules
                            $missing = [];

                            switch ($vehicle->shift_type_id) {
                                case 1: // Pick-only
                                    if (!in_array('pick', $existingTypes)) {
                                        $missing[] = 'pick';
                                    }
                                    break;

                                case 2: // Both pick and drop
                                    if (!in_array('pick', $existingTypes)) {
                                        $missing[] = 'pick';
                                    }
                                    if (!in_array('drop', $existingTypes)) {
                                        $missing[] = 'drop';
                                    }
                                    break;

                                case 3: // Drop-only
                                    if (!in_array('drop', $existingTypes)) {
                                        $missing[] = 'drop';
                                    }
                                    break;
                            }

                            $driver->missing_schedules = $missing;
                            return $driver;
                        })
                        ->filter(function ($driver) {
                            return !empty($driver->missing_schedules); // Only drivers with missing shifts
                        })
                        ->values();

                    $result['data'] = $unshinedDriver;
                }

                // 3. underAssignedDrivers
                if ($filterTypeArray === 'underAssignedDrivers') {
                    // seates left in this driver list
                    $underAssignedDrivers = SubUser::whereHas('roles', function ($query) {
                        $query->where('role_id', 5);
                    })
                        ->whereHas('vehicle')
                        ->with([
                            'vehicle',
                            'schedulesAsDriver' => function ($query) use ($today) {
                                $query->whereDate('end_date', '>=', $today);
                            },
                            'schedulesAsDriver.carers'
                        ])
                        ->get()
                        ->filter(function ($driver) {
                            $vehicleSeats = $driver->vehicle->seats ?? 0;
                            $assignedSeats = 0;

                            foreach ($driver->schedulesAsDriver as $schedule) {
                                $assignedSeats += $schedule->carers->count();
                            }

                            return $assignedSeats > 0 && $assignedSeats < $vehicleSeats;
                        })
                        ->map(function ($driver) {
                            $carers = collect();
                            foreach ($driver->schedulesAsDriver as $schedule) {
                                foreach ($schedule->carers as $carer) {
                                    $carers->push(['carer_id' => $carer->carer_id, 'schedule_id' => $carer->schedule_id,]);
                                }
                            }

                            return [
                                'id' => $driver->id,
                                'first_name' => $driver->first_name,
                                'email' => $driver->email,
                                'phone' => $driver->phone,
                                'mobile' => $driver->mobile,
                                'vehicle' => [
                                    'id' => $driver->vehicle->id,
                                    'name' => $driver->vehicle->name,
                                    'vehicle_no' => $driver->vehicle->vehicle_no,
                                    'registration_no' => $driver->vehicle->registration_no,
                                    'seats' => $driver->vehicle->seats,
                                    'assigned_seats' => $carers->count(),
                                    'carers' => $carers,
                                ],
                            ];
                        })
                        ->values();

                    // $resultsByDate[$today] = [
                    //     'unshinedUsers' => $unshinedUsers,
                    //     'unshinedDriver' => $unshinedDriver,
                    //     'leftunshinedDriver' => $underAssignedDrivers,
                    // ];

                    $result['data'] = $underAssignedDrivers;
                }

                $resultsByDate[$today] = $result;
            }


            // $this->data['unshinedUsers'] = $unshinedUsers;
            // $this->data['unshinedDriver'] = $unshinedDriver;
            // $this->data['leftunshinedDriver'] = $underAssignedDrivers;



            return response()->json([
                'status' => true,
                'message' => 'Unshined emaployee list of ' . @$filterTypeArray,
                'data' => $resultsByDate,
            ], 200);
        } catch (\Throwable $th) {

            return response()->json([
                'status' => false,
                'message' => 'Get data: ' . $th->getMessage(),
                'file' => $th->getLine(),
            ], 500);
        }
    }



    /**
     * @OA\post(
     *     path="/uc/api/routeautomation/dailyUnshinedEmployeedateWise",
     *     operationId="dailyUnshinedEmployeedateWise",
     *     tags={"Daily Automation"},
     *     summary="Unshined Automation Request",
     *     security={{"Bearer": {}}},
     *     description="Unshined Automation Request",
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *                 @OA\Schema(
     *                     required={"month", "filtertype"},
     *                     @OA\Property(
     *                         property="month",
     *                         type="string",
     *                         format="date",
     *                         description="Month in YYYY-MM format",
     *                         example="2025-05-01"
     *                     ),
     *                     @OA\Property(
     *                         property="filtertype",
     *                         type="string",
     *                         description="Choose one of: unshinedUsers, unshinedDriver, underAssignedDrivers",
     *                         example="unshinedUsers"
     *                     )
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Automation Unshined Successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Automation Unshined Successfully",
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


    public function dailyUnshinedEmployeedateWise(Request $request)
    {

        try {

            $filterTypeArray  = $request->input('filtertype');
            $startDate = Carbon::parse($request->input('month')); //now()->startOfMonth(); // Start of current month
            $endDate =  $startDate; //now(); // Today

            $resultsByDate = [];

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $today = $date->toDateString();
                $result = [];

                // 1. unshinedUsers
                if ($filterTypeArray === 'unshinedUsers') {
                    // Unshined users list
                    $unshinedUsers = User::whereHas('roles', function ($query) {
                        $query->where('role_id', 4);
                    })
                        ->whereNotNull('latitude')
                        ->whereNotNull('longitude')
                        ->whereNotNull('shift_type')
                        ->select('id', 'first_name', 'last_name', 'email', 'office_distance', 'latitude', 'longitude', 'address', 'profile_image', 'shift_type')
                        ->get()
                        ->map(function ($user) use ($today) {
                            $existingTypes = $user->scheduleCarers()
                                ->whereDate('created_at', $today)
                                ->pluck('shift_type')
                                ->toArray();

                            if (in_array('pick', $existingTypes) && !in_array('drop', $existingTypes)) {
                                $user->schedule_type = 'drop';
                            } elseif (in_array('drop', $existingTypes) && !in_array('pick', $existingTypes)) {
                                $user->schedule_type = 'pick';
                            } elseif (!in_array('pick', $existingTypes) && !in_array('drop', $existingTypes)) {
                                $user->schedule_type = 'pick and drop'; // default when both missing
                            } else {
                                $user->schedule_type = null; // both exist — exclude
                            }

                            return $user;
                        })
                        ->filter(function ($user) {
                            return $user->schedule_type !== null;
                        })
                        ->values();

                        $missingschedule = MissingSchedule::where('date',$today)->get();

                   // $result['data'] = $unshinedUsers;
                    $result['data'] = $missingschedule;
                }

                // 2. unshinedDriver

                if ($filterTypeArray === 'unshinedDriver') {

                    $unshinedDriver = SubUser::whereHas('roles', function ($query) {
                        $query->where('role_id', 5); // Driver role
                    })
                        ->with([
                            'vehicle',
                            'schedulesAsDriver' => function ($query) use ($today) {
                                $query->whereDate('created_at', $today);
                            }
                        ])
                        ->select('id', 'first_name', 'last_name', 'email', 'profile_image')
                        ->get()
                        ->map(function ($driver) {
                            $vehicle = $driver->vehicle;

                            if (!$vehicle) {
                                $driver->missing_schedules = [];
                                return $driver;
                            }

                            // Get today's existing shift types for this driver
                            $existingTypes = collect();

                            foreach ($driver->schedulesAsDriver as $schedule) {
                                if ($schedule->shift_type_id == 1) {
                                    $existingTypes->push('pick');
                                } elseif ($schedule->shift_type_id == 3) {
                                    $existingTypes->push('drop');
                                } elseif ($schedule->shift_type_id == 2) {
                                    // Both pick and drop
                                    $existingTypes->push('pick');
                                    $existingTypes->push('drop');
                                }
                            }

                            $existingTypes = $existingTypes->unique()->toArray();

                            // Initialize missing_schedules
                            $missing = [];

                            switch ($vehicle->shift_type_id) {
                                case 1: // Pick-only
                                    if (!in_array('pick', $existingTypes)) {
                                        $missing[] = 'pick';
                                    }
                                    break;

                                case 2: // Both pick and drop
                                    if (!in_array('pick', $existingTypes)) {
                                        $missing[] = 'pick';
                                    }
                                    if (!in_array('drop', $existingTypes)) {
                                        $missing[] = 'drop';
                                    }
                                    break;

                                case 3: // Drop-only
                                    if (!in_array('drop', $existingTypes)) {
                                        $missing[] = 'drop';
                                    }
                                    break;
                            }

                            $driver->missing_schedules = $missing;
                            return $driver;
                        })
                        ->filter(function ($driver) {
                            return !empty($driver->missing_schedules); // Only drivers with missing shifts
                        })
                        ->values();

                    $result['data'] = $unshinedDriver;
                }

                // 3. underAssignedDrivers
                if ($filterTypeArray === 'underAssignedDrivers') {
                    // seates left in this driver list
                    $underAssignedDrivers = SubUser::whereHas('roles', function ($query) {
                        $query->where('role_id', 5);
                    })
                        ->whereHas('vehicle')
                        ->with([
                            'vehicle',
                            'schedulesAsDriver' => function ($query) use ($today) {
                                $query->whereDate('end_date', '>=', $today);
                            },
                            'schedulesAsDriver.carers'
                        ])
                        ->get()
                        ->filter(function ($driver) {
                            $vehicleSeats = $driver->vehicle->seats ?? 0;
                            $assignedSeats = 0;

                            foreach ($driver->schedulesAsDriver as $schedule) {
                                $assignedSeats += $schedule->carers->count();
                            }

                            return $assignedSeats > 0 && $assignedSeats < $vehicleSeats;
                        })
                        ->map(function ($driver) {
                            $carers = collect();
                            foreach ($driver->schedulesAsDriver as $schedule) {
                                foreach ($schedule->carers as $carer) {
                                    $carers->push(['carer_id' => $carer->carer_id, 'schedule_id' => $carer->schedule_id,]);
                                }
                            }

                            return [
                                'id' => $driver->id,
                                'first_name' => $driver->first_name,
                                'email' => $driver->email,
                                'phone' => $driver->phone,
                                'mobile' => $driver->mobile,
                                'vehicle' => [
                                    'id' => $driver->vehicle->id,
                                    'name' => $driver->vehicle->name,
                                    'vehicle_no' => $driver->vehicle->vehicle_no,
                                    'registration_no' => $driver->vehicle->registration_no,
                                    'seats' => $driver->vehicle->seats,
                                    'assigned_seats' => $carers->count(),
                                    'carers' => $carers,
                                ],
                            ];
                        })
                        ->values();
                    $result['data'] = $underAssignedDrivers;
                }
                $resultsByDate[$today] = $result;
            }

            return response()->json([
                'status' => true,
                'message' => 'Unshined emaployee list of ' . @$filterTypeArray,
                'data' => $resultsByDate,
            ], 200);
        } catch (\Throwable $th) {

            return response()->json([
                'status' => false,
                'message' => 'Get data: ' . $th->getMessage(),
                'file' => $th->getLine(),
            ], 500);
        }
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
