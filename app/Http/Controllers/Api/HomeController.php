<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\{CarersNoshowTimer, CompanyAddresse, CompanyDetails, Faq, Holiday, Invoice, Leave, Notification, Rating, Reason, Reminder, RideSetting, Schedule, ScheduleCarer, ScheduleCarerComplaint, ScheduleCarerStatus, SubUser, DailySchedule, UpdateEmployeeHistory, CapRequest};
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\{DB, Mail, Hash};

use function PHPSTORM_META\type;

/**
 * @OA\SecurityScheme(
 *     securityScheme="Bearer",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="sanctum",
 * )
 */
class HomeController extends Controller
{

    public function profile()
    {
        return auth('sanctum')->user();
    }

    /**
     * @OA\Get(
     * path="/uc/api/home",
     * operationId="home",
     * tags={"Home Data"},
     * summary="Home Data",
     *   security={ {"Bearer": {} }},
     * description="Account Setup",
     *      @OA\Response(
     *          response=201,
     *          description="List home data",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="List home data",
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

    public function home()
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            $user_ids = array($user_id);
            $today_date = Carbon::now()->format('Y-m-d');
            //$today_date = '2024-01-04';
            $previous_date = Carbon::now()->subDay()->format('Y-m-d');

            $dates = array($today_date);
            $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');


            $this->data1['all_schedule'] = [];
            if ($user) {
                if ($user->hasRole('carer')) {

                    $this->data['schedules'] = $this->getWeeklyScheduleInfo($user_ids, $dates, 2, "all");

                    foreach ($this->data['schedules'] as $key => $schedule) {

                        $scheduleDate = date('Y-m-d', strtotime($today_date));
                        $holiday = Holiday::where('date', $scheduleDate)->exists();
                        if (!$holiday) {
                            $start = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['start_time']));
                            $end = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['end_time']));

                            if ($schedule['type'] == 'pick') {
                                $this->data1['all_schedule'][$key]['time'] = $start;
                            } else {
                                $this->data1['all_schedule'][$key]['time'] = $end;
                            }

                            $this->data1['all_schedule'][$key]['type'] = $schedule['type'];
                            $this->data1['all_schedule'][$key]['scheudleId'] = $schedule['id'];
                            $this->data1['all_schedule'][$key]['schedule_ride_status'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['name'];

                            $this->data1['all_schedule'][$key]['ride_start_hours'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['hours'];

                            $this->data1['all_schedule'][$key]['schedule_ride_status_id'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['id'];
                            $this->data1['all_schedule'][$key]['schedule_driver_rating'] = @$this->getdriverRating($schedule['driver_id']);

                            $this->data1['all_schedule'][$key]['schedule'] = $schedule;
                            $this->data1['all_schedule'][$key]['driver'] = @$this->getScheduleDriver($schedule['id'], $scheduleDate);
                            $this->data1['all_schedule'][$key]['schedule']['carers'] = $this->getScheduleCarers($schedule['id'], $schedule['type'], $scheduleDate);
                        }
                        if ($this->data1['all_schedule']) {
                            usort($this->data1['all_schedule'], function ($a, $b) {
                                $dateTimeA = new \DateTime($a['time']);
                                $dateTimeB = new \DateTime($b['time']);

                                return $dateTimeA <=> $dateTimeB;
                            });
                        }
                    }

                    $this->data1['Announcements']['holidays'] = Holiday::whereYear('date', '=', date('Y'))->orderBy('date', 'desc')->get();
                    $this->data1['Announcements']['reminders'] = Reminder::whereYear('date', '=', date('Y'))->whereIn('target', ['staff', 'both'])->orderBy('date', 'desc')->get();

                    $this->data1['employee_summary']['leaves'] = Leave::where('staff_id', $user_id)->whereYear('start_date', '=', date('Y'))->count();
                    $this->data1['employee_summary']['complaints'] = ScheduleCarerComplaint::where('staff_id', $user_id)->whereYear('date', '=', date('Y'))->count();
                    $this->data1['employee_summary']['holidays'] = Holiday::whereYear('date', '=', date('Y'))->count();
                    $this->data1['employee_summary']['absents'] = ScheduleCarerStatus::where('status_id', 5)->whereYear('date', '=', date('Y'))->whereHas('scheduleCarer', function ($q) use ($user_id) {
                        $q->where('carer_id', $user_id);
                    })->count();
                } else if ($user->hasRole('driver')) {
                   
                    $this->data['schedules'] = $this->getWeeklyScheduleInfo($user_ids, $dates, 1, "all");

                    //dd($this->data['schedules']); 
                    foreach ($this->data['schedules'] as $key => $schedule) {

                        $scheduleDate = date('Y-m-d', strtotime($today_date));
                        $holiday = Holiday::where('date', $scheduleDate)->exists();
                        if (!$holiday) {
                            $start = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['start_time']));
                            $end = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['end_time']));

                            if ($schedule['type'] == 'pick') {
                                $this->data1['all_schedule'][$key]['time'] = $start;
                            } else {
                                $this->data1['all_schedule'][$key]['time'] = $end;
                            }

                            $this->data1['all_schedule'][$key]['type'] = $schedule['type'];
                            $this->data1['all_schedule'][$key]['scheudleId'] = $schedule['id'];
                            $this->data1['all_schedule'][$key]['schedule_ride_status'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['name'];
                            $this->data1['all_schedule'][$key]['ride_start_hours'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['hours'];
                            $this->data1['all_schedule'][$key]['schedule_ride_status_id'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['id'];
                            $this->data1['all_schedule'][$key]['schedule_driver_rating'] = @$this->getdriverRating($schedule['driver_id']);
                            $this->data1['all_schedule'][$key]['schedule'] = $schedule;
                            $this->data1['all_schedule'][$key]['driver'] = @$this->getScheduleDriver($schedule['id'], $scheduleDate);
                            $this->data1['all_schedule'][$key]['schedule']['carers'] = $this->getScheduleCarers($schedule['id'], $schedule['type'], $scheduleDate);


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


                    $this->data1['Announcements']['holidays'] = Holiday::whereYear('date', '=', date('Y'))->orderBy('date', 'desc')->get();
                    $this->data1['Announcements']['reminders'] = Reminder::whereYear('date', '=', date('Y'))->whereIn('target', ['driver', 'both'])->orderBy('date', 'desc')->get();

                    $this->data1['weeklystats'] = $this->getWeeklyStats($user_ids);
                }

                $this->data1['company_info'] = @$this->companyInfo($today_date);
                $this->data1['ride_setting'] = @RideSetting::first();
                $this->data1['user_info'] = @$this->getDriverEmpoyeeById($user->id);

                $scheduleExists = DB::table('schedule_carers')
                    ->join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
                    ->where('schedule_carers.carer_id', $user_id)
                    ->exists();

                $this->data1['request_cab_status'] = $scheduleExists ? 0 : 1;
                $this->data1['cap_status'] = CapRequest::where('user_id', $user_id)->latest()->first() ?? (object)[];;

                return response()->json(['success' => true, "data" => @$this->data1, "message" => "The home page details listed successfully"], 200);
            }

            return response()->json(['success' => false, "message" => "The user is neither a registered staff nor driver"], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }



    /**
     * @OA\Get(
     * path="/uc/api/dailyhome",
     * operationId="dailyhome",
     * tags={"Home Data"},
     * summary="Home Data",
     *   security={ {"Bearer": {} }},
     * description="Account Setup",
     *      @OA\Response(
     *          response=201,
     *          description="List daily home data",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="List daily home data",
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

    public function dailyhome(){

        try {
            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            $user_ids = array($user_id);
            $today_date = Carbon::now()->format('Y-m-d');
            //$today_date = '2024-01-04';
            $previous_date = Carbon::now()->subDay()->format('Y-m-d');

            $dates = array($today_date);
            $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

            $this->data1['all_schedule'] = [];
            if ($user) {
                if ($user->hasRole('carer')) {

                   $this->data['schedules'] = $this->dailygetWeeklyScheduleInfo($user_ids, $dates, 2, "all");

                    foreach ($this->data['schedules'] as $key => $schedule) {

                        $scheduleDate = date('Y-m-d', strtotime($today_date));
                        $holiday = Holiday::where('date', $scheduleDate)->exists();
                        if (!$holiday) {
                            $start = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['start_time']));
                            $end = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['end_time']));

                            if ($schedule['type'] == 'pick') {
                                $this->data1['all_schedule'][$key]['time'] = $start;
                            } else {
                                $this->data1['all_schedule'][$key]['time'] = $end;
                            }

                            $this->data1['all_schedule'][$key]['type'] = $schedule['type'];
                            $this->data1['all_schedule'][$key]['scheudleId'] = $schedule['id'];
                            $this->data1['all_schedule'][$key]['schedule_ride_status'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['name'];

                            $this->data1['all_schedule'][$key]['ride_start_hours'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['hours'];

                            $this->data1['all_schedule'][$key]['schedule_ride_status_id'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['id'];
                            $this->data1['all_schedule'][$key]['schedule_driver_rating'] = @$this->getdriverRating($schedule['driver_id']);

                            $this->data1['all_schedule'][$key]['schedule'] = $schedule;
                            $this->data1['all_schedule'][$key]['driver'] = @$this->getScheduleDriverDaily($schedule['id'], $scheduleDate);
                            $this->data1['all_schedule'][$key]['schedule']['carers'] = $this->getScheduleCarers($schedule['id'], $schedule['type'], $scheduleDate);
                        }
                        if ($this->data1['all_schedule']) {
                            usort($this->data1['all_schedule'], function ($a, $b) {
                                $dateTimeA = new \DateTime($a['time']);
                                $dateTimeB = new \DateTime($b['time']);

                                return $dateTimeA <=> $dateTimeB;
                            });
                        }
                    }

                    $this->data1['Announcements']['holidays'] = Holiday::whereYear('date', '=', date('Y'))->orderBy('date', 'desc')->get();
                    $this->data1['Announcements']['reminders'] = Reminder::whereYear('date', '=', date('Y'))->whereIn('target', ['staff', 'both'])->orderBy('date', 'desc')->get();

                    $this->data1['employee_summary']['leaves'] = Leave::where('staff_id', $user_id)->whereYear('start_date', '=', date('Y'))->count();
                    $this->data1['employee_summary']['complaints'] = ScheduleCarerComplaint::where('staff_id', $user_id)->whereYear('date', '=', date('Y'))->count();
                    $this->data1['employee_summary']['holidays'] = Holiday::whereYear('date', '=', date('Y'))->count();
                    $this->data1['employee_summary']['absents'] = ScheduleCarerStatus::where('status_id', 5)->whereYear('date', '=', date('Y'))->whereHas('scheduleCarer', function ($q) use ($user_id) {
                        $q->where('carer_id', $user_id);
                    })->count();
                } else if ($user->hasRole('driver')) {
                   
                    $this->data['schedules'] = $this->dailygetWeeklyScheduleInfo($user_ids, $dates, 1, "all");

                    //dd($this->data['schedules']); 
                    foreach ($this->data['schedules'] as $key => $schedule) {

                        $scheduleDate = date('Y-m-d', strtotime($today_date));
                        $holiday = Holiday::where('date', $scheduleDate)->exists();
                        if (!$holiday) {
                            $start = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['start_time']));
                            $end = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['end_time']));

                            if ($schedule['type'] == 'pick') {
                                $this->data1['all_schedule'][$key]['time'] = $start;
                            } else {
                                $this->data1['all_schedule'][$key]['time'] = $end;
                            }

                            $this->data1['all_schedule'][$key]['type'] = $schedule['type'];
                            $this->data1['all_schedule'][$key]['scheudleId'] = $schedule['id'];
                            $this->data1['all_schedule'][$key]['schedule_ride_status'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['name'];
                            $this->data1['all_schedule'][$key]['ride_start_hours'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['hours'];
                            $this->data1['all_schedule'][$key]['schedule_ride_status_id'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['id'];
                            $this->data1['all_schedule'][$key]['schedule_driver_rating'] = @$this->getdriverRating($schedule['driver_id']);
                            $this->data1['all_schedule'][$key]['schedule'] = $schedule;
                            $this->data1['all_schedule'][$key]['driver'] = @$this->getScheduleDriver($schedule['id'], $scheduleDate);
                            $this->data1['all_schedule'][$key]['schedule']['carers'] = $this->getScheduleCarers($schedule['id'], $schedule['type'], $scheduleDate);


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


                    $this->data1['Announcements']['holidays'] = Holiday::whereYear('date', '=', date('Y'))->orderBy('date', 'desc')->get();
                    $this->data1['Announcements']['reminders'] = Reminder::whereYear('date', '=', date('Y'))->whereIn('target', ['driver', 'both'])->orderBy('date', 'desc')->get();

                    $this->data1['weeklystats'] = $this->getWeeklyStats($user_ids);
                }

                $this->data1['company_info'] = @$this->companyInfo($today_date);
                $this->data1['ride_setting'] = @RideSetting::first();
                $this->data1['user_info'] = @$this->getDriverEmpoyeeById($user->id);

                return response()->json(['success' => true, "data" => @$this->data1, "message" => "The daily home page details listed successfully"], 200);
            }

            return response()->json(['success' => false, "message" => "The user is neither a registered staff nor driver"], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    

    //old code

    // public function getScheduleCarers($scheduleId, $type, $date)
    // {
    //     //return $type;
    //     // Valid code with loop
    //     $schedule_carers =  DB::table('schedule_carers')
    //         ->select('schedule_carers.*', 'sub_users.*', 'schedule_carers.id as cId')
    //         ->join('sub_users', 'schedule_carers.carer_id', '=', 'sub_users.id')
    //         ->where('schedule_carers.schedule_id', $scheduleId)
    //         ->where('schedule_carers.shift_type', $type)
    //         ->get();


    //     foreach ($schedule_carers as $carer) {
    //         $getStaus = DB::table('schedule_carer_statuses')
    //             ->leftjoin('statuses', 'schedule_carer_statuses.status_id', '=', 'statuses.id')

    //             ->where('schedule_carer_id', $carer->cId)
    //             ->where('date', $date)
    //             ->first();


    //         $carer->ride_status = $getStaus ? $getStaus->name : 'Waiting';
    //         $carer->mobile_otp = @$getStaus->otp;
    //     }

    //     return $schedule_carers;
    //     // End code

    // }

    // new code 



    public function getScheduleCarers($scheduleId, $type, $date)
    {

        //return $type;
        // Valid code with loop
        $company =  @$this->companyInfo($date);
        $companyLat = $company->latitude;
        $companyLong = $company->longitude;
        $schedule_carers =  DB::table('schedule_carers')
            ->select('schedule_carers.*', 'sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address', 'schedule_carers.id as cId')
            ->join('sub_users', 'schedule_carers.carer_id', '=', 'sub_users.id')
            ->leftJoin('sub_user_addresses', 'sub_users.id', '=', 'sub_user_addresses.sub_user_id')
            ->where('schedule_carers.schedule_id', $scheduleId)
            ->where('schedule_carers.shift_type', $type)
            ->whereDate('sub_user_addresses.start_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereDate('sub_user_addresses.end_date', '>', $date)
                    ->orWhereNull('sub_user_addresses.end_date');
            })->get();


        foreach ($schedule_carers as $carer) {

            $carerLat = $carer->latitude;
            $carerLong = $carer->longitude;;
            if ($carer->temp_date == $date && $carer->temp_lat && $carer->temp_long) {
                $distance = $this->calculateDistance($companyLat, $companyLong, $carer->temp_lat, $carer->temp_long);
            } else {
                $distance = $this->calculateDistance($companyLat, $companyLong, $carerLat, $carerLong);
            }
            $getStaus = DB::table('schedule_carer_statuses')
                ->select('statuses.id as SId', 'statuses.*', 'schedule_carer_statuses.*')
                ->leftjoin('statuses', 'schedule_carer_statuses.status_id', '=', 'statuses.id')

                ->where('schedule_carer_id', $carer->cId)
                ->where('date', $date)
                ->first();


            $carer->distance = number_format($distance, 2);
            $carer->ride_status = $getStaus ? @$getStaus->name : 'Waiting';
            $carer->ride_status_id = @$getStaus->SId ? $getStaus->SId : 1;
            $carer->mobile_otp = @$getStaus->otp;
            $carer->noshow_timer = @$this->getDifferenceBetweenTimers($carer->carer_id, $type, $date);
            //changes
            if (date('Y-m-d', strtotime($carer->temp_date)) != $date) {

                $carer->temp_date = null;
                $carer->temp_lat = null;
                $carer->temp_long = null;
                $carer->temp_address = null;
            }
            //end changes
        }

        if ($type == 'pick') {
            $schedule_carers = $schedule_carers->sortByDesc('distance');
        } else {
            $schedule_carers = $schedule_carers->sortBy('distance');
        }

        // Check if there are carers available and if the first carer is female
        $checkSafty = DB::table('ride_settings')->first();
        if ($checkSafty->female_safety == 1) {
            if ($schedule_carers->isNotEmpty() && $type == 'drop') {
                // Separate male and female carers
                $femaleCarers = $schedule_carers->filter(function ($carer) {
                    return strtolower($carer->gender) === 'female';
                });

                $maleCarers = $schedule_carers->filter(function ($carer) {
                    return strtolower($carer->gender) === 'male';
                });

                // Place female carers at the beginning of the array
                $schedule_carers = $femaleCarers;

                // Sort male carers by distance and append them to the array
                if ($maleCarers->isNotEmpty()) {
                    $maxDistanceMale = $maleCarers->sortByDesc('distance');
                    $schedule_carers = $schedule_carers->concat($maxDistanceMale);
                }
            }
        }

        return $schedule_carers->values();
        // End code

    }



    public function getScheduleCarersDaily($scheduleId, $type, $date){

        //return $type;
        // Valid code with loop
        $company =  @$this->companyInfo($date);
        $companyLat = $company->latitude;
        $companyLong = $company->longitude;
        $schedule_carers =  DB::table('daily_schedule_carers')
            ->select('daily_schedule_carers.*', 'sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address', 'daily_schedule_carers.id as cId')
            ->join('sub_users', 'daily_schedule_carers.carer_id', '=', 'sub_users.id')
            ->leftJoin('sub_user_addresses', 'sub_users.id', '=', 'sub_user_addresses.sub_user_id')
            ->where('daily_schedule_carers.schedule_id', $scheduleId)
            ->where('daily_schedule_carers.shift_type', $type)
            ->whereDate('sub_user_addresses.start_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereDate('sub_user_addresses.end_date', '>', $date)
                    ->orWhereNull('sub_user_addresses.end_date');
            })->get();


        foreach ($schedule_carers as $carer) {

            $carerLat = $carer->latitude;
            $carerLong = $carer->longitude;;
            if ($carer->temp_date == $date && $carer->temp_lat && $carer->temp_long) {
                $distance = $this->calculateDistance($companyLat, $companyLong, $carer->temp_lat, $carer->temp_long);
            } else {
                $distance = $this->calculateDistance($companyLat, $companyLong, $carerLat, $carerLong);
            }
            $getStaus = DB::table('schedule_carer_statuses')
                ->select('statuses.id as SId', 'statuses.*', 'schedule_carer_statuses.*')
                ->leftjoin('statuses', 'schedule_carer_statuses.status_id', '=', 'statuses.id')

                ->where('schedule_carer_id', $carer->cId)
                ->where('date', $date)
                ->first();


            $carer->distance = number_format($distance, 2);
            $carer->ride_status = $getStaus ? @$getStaus->name : 'Waiting';
            $carer->ride_status_id = @$getStaus->SId ? $getStaus->SId : 1;
            $carer->mobile_otp = @$getStaus->otp;
            $carer->noshow_timer = @$this->getDifferenceBetweenTimers($carer->carer_id, $type, $date);
            //changes
            if (date('Y-m-d', strtotime($carer->temp_date)) != $date) {

                $carer->temp_date = null;
                $carer->temp_lat = null;
                $carer->temp_long = null;
                $carer->temp_address = null;
            }
            //end changes
        }

        if ($type == 'pick') {
            $schedule_carers = $schedule_carers->sortByDesc('distance');
        } else {
            $schedule_carers = $schedule_carers->sortBy('distance');
        }

        // Check if there are carers available and if the first carer is female
        $checkSafty = DB::table('ride_settings')->first();
        if ($checkSafty->female_safety == 1) {
            if ($schedule_carers->isNotEmpty() && $type == 'drop') {
                // Separate male and female carers
                $femaleCarers = $schedule_carers->filter(function ($carer) {
                    return strtolower($carer->gender) === 'female';
                });

                $maleCarers = $schedule_carers->filter(function ($carer) {
                    return strtolower($carer->gender) === 'male';
                });

                // Place female carers at the beginning of the array
                $schedule_carers = $femaleCarers;

                // Sort male carers by distance and append them to the array
                if ($maleCarers->isNotEmpty()) {
                    $maxDistanceMale = $maleCarers->sortByDesc('distance');
                    $schedule_carers = $schedule_carers->concat($maxDistanceMale);
                }
            }
        }

        return $schedule_carers->values();
        // End code

    }




    public function getDriverRating($driver_id)
    {
        $ratings = Rating::where('driver_id', $driver_id)->pluck('rate')->toArray();

        if (empty($ratings)) {
            return 0; // Return 0 if no ratings found
        }

        $sumOfRatings = array_sum($ratings);
        $averageRating = round($sumOfRatings / count($ratings), 1);
        return $averageRating;
    }


    //*********************** function to get noshow_timer ****************************/

    public function getDifferenceBetweenTimers($carerId, $type, $date)
    {
        $carer_noshow = CarersNoshowTimer::where('carer_id', $carerId)->where('type', $type)->where('date', $date)->first();
        if (!$carer_noshow || !$carer_noshow->start_time) {
            return null;
        }

        $rideSetting = RideSetting::first();
        $noshowTimer = $rideSetting->noshow_timer ?? '00:05:00';

        $currentTime = Carbon::now('Asia/Kolkata');
        $startTime = Carbon::parse($carer_noshow->start_time, 'Asia/Kolkata');
        $difference = $currentTime->diffInMilliseconds($startTime);

        list($hours, $minutes, $seconds) = explode(':', $noshowTimer);
        //*******************old code***********************
        // $noshowTimerInSeconds = $hours * 3600 + $minutes * 60 + $seconds;
        // $remainingTime = max(0, $noshowTimerInSeconds - $difference);
        // Convert remaining time to the format hh:mm:ss
        // $formattedTime = sprintf('%02d:%02d:%02d', floor($remainingTime / 3600), floor(($remainingTime % 3600) / 60), $remainingTime % 60);
        // return $formattedTime; 

        //**************new code *******************************
        $noshowTimerInMilliseconds = ($hours * 3600 + $minutes * 60 + $seconds) * 1000;
        $remainingTime = max(0, $noshowTimerInMilliseconds - $difference);

        return $remainingTime;
    }



    //*********************************End of code ************************************ */
    public function checkRideStatus($scheduleId, $type, $date)
    {
        $data = DB::table('schedule_statuses')
            ->select('statuses.name', 'statuses.id', 'schedule_statuses.created_at')
            ->join('statuses', 'schedule_statuses.status_id', '=', 'statuses.id')
            ->where('schedule_statuses.schedule_id', $scheduleId)
            ->where('schedule_statuses.type', $type)
            ->where('schedule_statuses.date', $date)
            ->get();

        if ($data->isEmpty()) {
            return ['id' => 9, 'name' => 'Ride Not Started', 'hours' => 0];
        }

        $allCarers = $this->getScheduleCarers($scheduleId, $type, $date);

        $allNoSo = true;
        $allCancelled = true;
        $allOnLeave = true;

        foreach ($allCarers as $carer) {
            if ($carer->ride_status_id != 5) {
                $allNoSo = false;
            }

            if ($carer->ride_status_id != 4) {
                $allCancelled = false;
            }
            if ($carer->ride_status_id != 11) {
                $allOnLeave = false;
            }
        }

        $startDate = new \DateTime($data->first()->created_at);
        $endDate = new \DateTime();
        $interval = $startDate->diff($endDate);
        $hoursDifference = $interval->h + ($interval->days * 24);

        if ($allNoSo) {
            return ['id' => 12, 'name' => 'All No-So', 'hours' => $hoursDifference];
        } elseif ($allCancelled) {
            return ['id' => 13, 'name' => 'All Cancelled', 'hours' => $hoursDifference];
        } elseif ($allOnLeave) {
            return ['id' => 14, 'name' => 'All On-leave', 'hours' => $hoursDifference];
        }

        $noShowCount = 0;
        $cancelledCount = 0;

        foreach ($allCarers as $carer) {
            if ($carer->ride_status_id == 5) {
                $noShowCount++;
            } elseif ($carer->ride_status_id == 4) {
                $cancelledCount++;
            }
        }

        if ($noShowCount > 0 && $noShowCount + $cancelledCount == count($allCarers)) {
            @$this->changeInvoiceStatus($scheduleId, $type, $date);
            return ['id' => 8, 'name' => 'Completed', 'hours' => $hoursDifference];
        }

        if ($cancelledCount > 0 && $noShowCount + $cancelledCount == count($allCarers)) {
            @$this->changeInvoiceStatus($scheduleId, $type, $date);
            return ['id' => 8, 'name' => 'Completed', 'hours' => $hoursDifference];
        }

        $allPicked = true;

        foreach ($allCarers as $carer) {
            if ($carer->ride_status_id == 1) {
                $allPicked = false;
                break;
            }
        }
        if ($data->first()->id == 8) {
            return ['id' => $data->first()->id, 'name' => $data->first()->name, 'hours' => $hoursDifference];
        } else if ($allPicked) {
            return ['id' => 10, 'name' => 'All picked', 'hours' => $hoursDifference];
        }

        return ['id' => $data->first()->id, 'name' => $data->first()->name, 'hours' => $hoursDifference];
    }


    /**
     * @OA\Post(
     * path="/uc/api/scheduleDetails",
     * operationId="scheduleDetails",
     * tags={"Home Data"},
     * summary="Schedule data",
     *   security={ {"Bearer": {} }},
     * description="Schedule data",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"date"},
     *               @OA\Property(property="date", type="text"),
     *                
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Schedule data.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Schedule data.",
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

    public function scheduleDetails(Request $request)
    {
        try {

            $request->validate([
                'date' => 'required|date|date_format:Y-m-d',
            ]);



            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            $user_ids = array($user_id);
            $today_date = $request->date;
            //$today_date = '2024-01-04';
            //$previous_date = Carbon::now()->subDay()->format('Y-m-d');

            $dates = array($today_date);
            //$startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
            //$endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

            $this->data1['all_schedule'] = [];
            if ($user) {
                if ($user->hasRole('carer')) {

                    $this->data['schedules'] = $this->getWeeklyScheduleInfo($user_ids, $dates, 2, "all");
                    foreach ($this->data['schedules'] as $key => $schedule) {
                        $scheduleDate = date('Y-m-d', strtotime($today_date));
                        $holiday = Holiday::where('date', $scheduleDate)->exists();
                        if (!$holiday) {
                            $start = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['start_time']));
                            $end = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['end_time']));

                            //echo '<pre>';print_r($schedule['date']);die;
                            if ($schedule['type'] == 'pick') {
                                $this->data1['all_schedule'][$key]['time'] = $start;
                            } else {
                                $this->data1['all_schedule'][$key]['time'] = $end;
                            }

                            $this->data1['all_schedule'][$key]['type'] = $schedule['type'];
                            $this->data1['all_schedule'][$key]['scheudleId'] = $schedule['id'];
                            $this->data1['all_schedule'][$key]['schedule_ride_status'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['name'];
                            $this->data1['all_schedule'][$key]['schedule_ride_status_id'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['id'];
                            $this->data1['all_schedule'][$key]['schedule'] = $schedule;
                            $this->data1['all_schedule'][$key]['driver'] = $this->getScheduleDriver($schedule['id'], $scheduleDate);
                            $this->data1['all_schedule'][$key]['schedule']['carers'] = $this->getScheduleCarers($schedule['id'], $schedule['type'], $scheduleDate);
                        }

                        if ($this->data1['all_schedule']) {
                            usort($this->data1['all_schedule'], function ($a, $b) {
                                $dateTimeA = new \DateTime($a['time']);
                                $dateTimeB = new \DateTime($b['time']);

                                return $dateTimeA <=> $dateTimeB;
                            });
                        }
                    }
                    // $this->data['reminders'] = Reminder::whereBetween('created_at', [
                    //     Carbon::now()->startOfMonth(),
                    //     Carbon::now()->endOfMonth()
                    // ])->where('target', 'staff')->get();

                    // $this->data['monthlyStats']['leaves'] = Leave::where('date', '<=', $endOfMonth)->where('date', '>=', $startOfMonth)->where('status', 'Approved')->count();
                    // $this->data['monthlyStats']['complaints'] = 0;
                    // $this->data['monthlyStats']['holidays'] = Holiday::where('date', '<=', $endOfMonth)->where('date', '>=', $startOfMonth)->count();
                    // $this->data['monthlyStats']['absents'] = ScheduleCarerStatus::where('status_id', 5)->where('date', '<=', $endOfMonth)->where('date', '>=', $startOfMonth)->whereHas('scheduleCarer', function ($q) use ($user_id) {
                    //     $q->where('carer_id', $user_id);
                    // })->count();
                } else if ($user->hasRole('driver')) {
                    $this->data['schedules'] = $this->getWeeklyScheduleInfo($user_ids, $dates, 1, "all");

                    //dd($this->data['schedules']); 
                    foreach ($this->data['schedules'] as $key => $schedule) {
                        $scheduleDate = date('Y-m-d', strtotime($today_date));
                        $holiday = Holiday::where('date', $scheduleDate)->exists();
                        if (!$holiday) {
                            $start = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['start_time']));
                            $end = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['end_time']));

                            //echo '<pre>';print_r($schedule['date']);die;
                            if ($schedule['type'] == 'pick') {
                                $this->data1['all_schedule'][$key]['time'] = $start;
                            } else {
                                $this->data1['all_schedule'][$key]['time'] = $end;
                            }

                            $this->data1['all_schedule'][$key]['type'] = $schedule['type'];
                            $this->data1['all_schedule'][$key]['scheudleId'] = $schedule['id'];
                            $this->data1['all_schedule'][$key]['schedule_ride_status'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['name'];
                            $this->data1['all_schedule'][$key]['schedule_ride_status_id'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['id'];
                            $this->data1['all_schedule'][$key]['schedule'] = $schedule;
                            $this->data1['all_schedule'][$key]['driver'] = $this->getScheduleDriver($schedule['id'], $scheduleDate);
                            $this->data1['all_schedule'][$key]['schedule']['carers'] = $this->getScheduleCarers($schedule['id'], $schedule['type'], $scheduleDate);
                        }

                        if ($this->data1['all_schedule']) {
                            usort($this->data1['all_schedule'], function ($a, $b) {
                                $dateTimeA = new \DateTime($a['time']);
                                $dateTimeB = new \DateTime($b['time']);

                                return $dateTimeA <=> $dateTimeB;
                            });
                        }
                    }


                    // $this->data1['Announcements']['holidays'] = Holiday::where('date', '<=', $endOfMonth)->where('date', '>=', Carbon::now()->format('Y-m-d'))->get();
                    // $this->data1['Announcements']['reminders'] = Reminder::whereBetween('created_at', [
                    //     Carbon::now()->startOfMonth(),
                    //     Carbon::now()->endOfMonth()

                    // ])->where('target', 'driver')->get();

                    // $this->data1['weeklystats'] = $this->getWeeklyStats($user_ids);
                }

                $this->data1['company_info'] = @$this->companyInfo($today_date);
                return response()->json(['success' => true, "data" => @$this->data1, "message" => "The schedules listed successfully"], 200);
            }

            return response()->json(['success' => false, "message" => "The user is neither a registered staff nor driver"], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     * path="/uc/api/company-profile",
     * operationId="companyProfile",
     * tags={"Home Data"},
     * summary="Company Profile",
     *   security={ {"Bearer": {} }},
     * description="Company Profile",
     *      @OA\Response(
     *          response=201,
     *          description="List data",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="List data",
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

    public function companyProfile()
    {
        try {
            $company_profile = @$this->companyInfo(date('Y-m-d'));
            $company_profile->logo_url = url('/images') . '/' . $company_profile->logo;

            return response()->json(['success' => true, "data" => $company_profile], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     * path="/uc/api/announcements",
     * operationId="announcements",
     * tags={"Home Data"},
     * summary="Announcements",
     *   security={ {"Bearer": {} }},
     * description="Announcements",
     *      @OA\Response(
     *          response=201,
     *          description="List data",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="List data",
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

    public function announcements()
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            if ($user) {
                $announcements = collect([]);

                if ($user->hasRole('carer')) {
                    $holidays = Holiday::whereYear('date', '=', date('Y'))->get();
                    $reminders = Reminder::whereYear('date', '=', date('Y'))->whereIn('target', ['staff', 'both'])
                        ->select('id', 'date', 'content as name', 'description', 'created_at', 'updated_at', 'type')->get();
                } else if ($user->hasRole('driver')) {
                    $holidays = Holiday::whereYear('date', '=', date('Y'))->get();
                    $reminders = Reminder::whereYear('date', '=', date('Y'))->whereIn('target', ['driver', 'both'])
                        ->select('id', 'date', 'content as name', 'description', 'created_at', 'updated_at', 'type')->get();
                }
                $announcements = @$announcements->merge($holidays)->merge($reminders);
                $sortedAnnouncements = @$announcements->sortByDesc('date');
                $this->data['announcements'] = @$sortedAnnouncements->values();

                return response()->json(['success' => true, "data" => $this->data], 200);
            }

            return response()->json(['success' => false, "message" => "The user is neither a registered staff nor driver"], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }



    /**
     * @OA\Get(
     * path="/uc/api/absents",
     * operationId="absents",
     * tags={"Employee"},
     * summary="absents",
     *   security={ {"Bearer": {} }},
     * description="absents",
     *      @OA\Response(
     *          response=201,
     *          description="List data",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="List data",
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

    public function absents()
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            // $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
            // $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

            if ($user) {
                if ($user->hasRole('carer')) {

                    $this->data['absents'] = ScheduleCarerStatus::where('status_id', 5)->whereYear('date', '=', date('Y'))->whereHas('scheduleCarer', function ($q) use ($user_id) {
                        $q->where('carer_id', $user_id);
                    })->with('ScheduleCarer.schedule.driver')->get();
                    return response()->json(['success' => true, "data" => $this->data], 200);
                }
            }

            return response()->json(['success' => false, "message" => "The user is not a registered staff"], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     * path="/uc/api/help",
     * operationId="help",
     * tags={"Home Data"},
     * summary="help",
     *   security={ {"Bearer": {} }},
     * description="help",
     *      @OA\Response(
     *          response=201,
     *          description="List data",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="List data",
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

    public function help()
    {
        try {
            $this->data['faq'] = Faq::get()->toArray();
            return response()->json(['success' => true, "data" => $this->data], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function findCarers(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|in:pick,drop',
            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            if ($user->hasRole('driver')) {
                $company = CompanyDetails::first();
                $companyLong = $company->longitude;
                $companyLat = $company->latitude;
                // $radius = 25;

                $nearbyCarers = SubUser::whereHas('roles', function ($query) {
                    $query->where('name', 'carer');
                })->get();
                foreach ($nearbyCarers as $key => $nearbyCarer) {
                    $nearbyCarers[$key] = [
                        'distance' => $this->calculateDistance($companyLat, $companyLong, $nearbyCarer['latitude'], $nearbyCarer['longitude']),
                        'address' => $nearbyCarer->address,
                        'carer_id' => $nearbyCarer->id
                    ];
                }
                if ($request->type == 'pick') {
                    $this->data['destination'] = $company->address;
                    $this->data['carers'] = $nearbyCarers->sortByDesc('distance');
                } else {
                    $this->data['origin'] = $company->address;
                    $this->data['carers'] = $nearbyCarers->sortBy('distance');
                }

                return response()->json(['data' =>  $this->data], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    //********************** old code  ********************************************
    // private static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    // {
    //     $R = 6371; // Radius of the Earth in kilometers
    //     $dLat = deg2rad($lat2 - $lat1);
    //     $dLon = deg2rad($lon2 - $lon1);

    //     $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
    //     $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    //     $distance = $R * $c;

    //     return $distance;
    // }

    //*********************  New code ***********************************************//
    public function calculateDistance($originLat, $originLong, $destinationLat, $destinationLong)
    {
        $cacheKey = "distance:$originLat,$originLong:$destinationLat,$destinationLong";

        // Check if distance is already cached
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $apiKey = env('GOOGLE_API_KEY');
        $baseUrl = 'https://maps.googleapis.com/maps/api/distancematrix/json';

        $client = new Client([
            'verify' => false, // Disable SSL certificate verification
        ]);

        try {
            $response = $client->get($baseUrl, [
                'query' => [
                    'origins' => "$originLat,$originLong",
                    'destinations' => "$destinationLat,$destinationLong",
                    'key' => $apiKey,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if ($response->getStatusCode() === 200 && isset($data['rows'][0]['elements'][0]['distance']['value'])) {
                // Distance is returned in meters
                $distanceInMeters = $data['rows'][0]['elements'][0]['distance']['value'];

                // Convert distance from meters to kilometers
                $distanceInKilometers = $distanceInMeters / 1000;

                Cache::put($cacheKey, $distanceInKilometers, now()->addDays(15));

                return $distanceInKilometers;
            } else {
                return null;
            }
        } catch (RequestException $e) {
            return null;
        }
    }


    //********************* End of new code ****************************************
    public function getScheduleById($sId, $type,$date=null)
    {
        try {

            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            $user_ids = array($user_id);
            if($date){
                $today_date =$date;
            }
            else{
            $today_date = date('Y-m-d');
            }
            //$today_date = '2024-01-04';
            $previous_date = Carbon::now()->subDay()->format('Y-m-d');

            $dates = array($today_date);
            $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

            $this->data1['all_schedule'] = [];
            if ($user) {
                if ($user->hasRole('carer')) {

                    $this->data['schedules'] = $this->getWeeklyScheduleInfo($user_ids, $dates, 2, "all");
                    foreach ($this->data['schedules'] as $key => $schedule) {
                        $scheduleDate = date('Y-m-d', strtotime($today_date));
                        $holiday = Holiday::where('date', $scheduleDate)->exists();
                        if (!$holiday) {
                            $start = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['start_time']));
                            $end = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['end_time']));
                            if ($schedule['id'] == $sId && $schedule['type'] == $type) {
                                //echo '<pre>';print_r($schedule['date']);die;
                                if ($schedule['type'] == 'pick') {
                                    $this->data1['all_schedule'][$key]['time'] = $start;
                                } else {
                                    $this->data1['all_schedule'][$key]['time'] = $end;
                                }
                                $this->data1['all_schedule'][$key]['type'] = $schedule['type'];
                                $this->data1['all_schedule'][$key]['scheudleId'] = $schedule['id'];
                                $this->data1['all_schedule'][$key]['schedule_ride_status'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['name'];
                                $this->data1['all_schedule'][$key]['schedule_ride_status_id'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['id'];
                                $this->data1['all_schedule'][$key]['schedule_driver_rating'] = @$this->getdriverRating($schedule['driver_id']);
                                $this->data1['all_schedule'][$key]['schedule'] = $schedule;
                                $this->data1['all_schedule'][$key]['driver'] = @$this->getScheduleDriver($schedule['id'], $scheduleDate);
                                $this->data1['all_schedule'][$key]['schedule']['carers'] = $this->getScheduleCarers($schedule['id'], $schedule['type'], $scheduleDate);
                            }
                        }

                        if ($this->data1['all_schedule']) {
                            usort($this->data1['all_schedule'], function ($a, $b) {
                                $dateTimeA = new \DateTime($a['time']);
                                $dateTimeB = new \DateTime($b['time']);

                                return $dateTimeA <=> $dateTimeB;
                            });
                        }
                    }
                    // $this->data1['reminders'] = Reminder::whereBetween('created_at', [
                    //     Carbon::now()->startOfMonth(),
                    //     Carbon::now()->endOfMonth()
                    // ])->where('target', 'staff')->get();

                    // $this->data1['monthlyStats']['leaves'] = Leave::where('start_date', '<=', $endOfMonth)->where('start_date', '>=', $startOfMonth)->where('status', 'Approved')->count();
                    // $this->data1['monthlyStats']['complaints'] = 0;
                    // $this->data1['monthlyStats']['holidays'] = Holiday::where('date', '<=', $endOfMonth)->where('date', '>=', $startOfMonth)->count();
                    // $this->data1['monthlyStats']['absents'] = ScheduleCarerStatus::where('status_id', 5)->where('date', '<=', $endOfMonth)->where('date', '>=', $startOfMonth)->whereHas('scheduleCarer', function ($q) use ($user_id) {
                    //     $q->where('carer_id', $user_id);
                    // })->count();
                } else if ($user->hasRole('driver')) {
                    $this->data['schedules'] = $this->getWeeklyScheduleInfo($user_ids, $dates, 1, "all");

                    //dd($this->data['schedules']); 
                    foreach ($this->data['schedules'] as $key => $schedule) {
                        $scheduleDate = date('Y-m-d', strtotime($today_date));
                        $holiday = Holiday::where('date', $scheduleDate)->exists();
                        if (!$holiday) {
                            $start = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['start_time']));
                            $end = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['end_time']));
                            if ($schedule['id'] == $sId && $schedule['type'] == $type) {
                                //echo '<pre>';print_r($schedule['date']);die;
                                if ($schedule['type'] == 'pick') {
                                    $this->data1['all_schedule'][$key]['time'] = $start;
                                } else {
                                    $this->data1['all_schedule'][$key]['time'] = $end;
                                }
                                $this->data1['all_schedule'][$key]['type'] = $schedule['type'];
                                $this->data1['all_schedule'][$key]['scheudleId'] = $schedule['id'];
                                $this->data1['all_schedule'][$key]['schedule_ride_status'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['name'];
                                $this->data1['all_schedule'][$key]['schedule_ride_status_id'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['id'];
                                $this->data1['all_schedule'][$key]['schedule_driver_rating'] = @$this->getdriverRating($schedule['driver_id']);
                                $this->data1['all_schedule'][$key]['schedule'] = $schedule;
                                $this->data1['all_schedule'][$key]['driver'] = @$this->getScheduleDriver($schedule['id'], $scheduleDate);
                                $this->data1['all_schedule'][$key]['schedule']['carers'] = $this->getScheduleCarers($schedule['id'], $schedule['type'], $scheduleDate);
                            }
                        }

                        if ($this->data1['all_schedule']) {
                            usort($this->data1['all_schedule'], function ($a, $b) {
                                $dateTimeA = new \DateTime($a['time']);
                                $dateTimeB = new \DateTime($b['time']);

                                return $dateTimeA <=> $dateTimeB;
                            });
                        }
                    }
                }


                return @$this->data1;
            }

            return response()->json(['success' => false, "message" => "The user is neither a registered staff nor driver"], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }



    public function getScheduleByIdDaily($sId, $type,$date=null){
        try {

            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            $user_ids = array($user_id);
            if($date){
                $today_date =$date;
            }
            else{
            $today_date = date('Y-m-d');
            }
            //$today_date = '2024-01-04';
            $previous_date = Carbon::now()->subDay()->format('Y-m-d');

            $dates = array($today_date);
            $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

            $this->data1['all_schedule'] = [];
            if ($user) {
                if ($user->hasRole('carer')) {
                    $this->data['schedules'] = $this->dailygetWeeklyScheduleInfo($user_ids, $dates, 2, "all");
                    foreach ($this->data['schedules'] as $key => $schedule) {
                        $scheduleDate = date('Y-m-d', strtotime($today_date));
                        $holiday = Holiday::where('date', $scheduleDate)->exists();
                        if (!$holiday) {
                            $start = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['start_time']));
                            $end = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['end_time']));
                            if ($schedule['id'] == $sId && $schedule['type'] == $type) {
                                //echo '<pre>';print_r($schedule['date']);die;
                                if ($schedule['type'] == 'pick') {
                                    $this->data1['all_schedule'][$key]['time'] = $start;
                                } else {
                                    $this->data1['all_schedule'][$key]['time'] = $end;
                                }
                                $this->data1['all_schedule'][$key]['type'] = $schedule['type'];
                                $this->data1['all_schedule'][$key]['scheudleId'] = $schedule['id'];
                                $this->data1['all_schedule'][$key]['schedule_ride_status'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['name'];
                                $this->data1['all_schedule'][$key]['schedule_ride_status_id'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['id'];
                                $this->data1['all_schedule'][$key]['schedule_driver_rating'] = @$this->getdriverRating($schedule['driver_id']);
                                $this->data1['all_schedule'][$key]['schedule'] = $schedule;
                                $this->data1['all_schedule'][$key]['driver'] = @$this->getScheduleDriverDaily($schedule['id'], $scheduleDate);
                                $this->data1['all_schedule'][$key]['schedule']['carers'] = $this->getScheduleCarersDaily($schedule['id'], $schedule['type'], $scheduleDate);
                            }
                        }

                        if ($this->data1['all_schedule']) {
                            usort($this->data1['all_schedule'], function ($a, $b) {
                                $dateTimeA = new \DateTime($a['time']);
                                $dateTimeB = new \DateTime($b['time']);

                                return $dateTimeA <=> $dateTimeB;
                            });
                        }
                    }

                } else if ($user->hasRole('driver')) {
                    $this->data['schedules'] = $this->getWeeklyScheduleInfo($user_ids, $dates, 1, "all");

                    //dd($this->data['schedules']); 
                    foreach ($this->data['schedules'] as $key => $schedule) {
                        $scheduleDate = date('Y-m-d', strtotime($today_date));
                        $holiday = Holiday::where('date', $scheduleDate)->exists();
                        if (!$holiday) {
                            $start = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['start_time']));
                            $end = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['end_time']));
                            if ($schedule['id'] == $sId && $schedule['type'] == $type) {
                                //echo '<pre>';print_r($schedule['date']);die;
                                if ($schedule['type'] == 'pick') {
                                    $this->data1['all_schedule'][$key]['time'] = $start;
                                } else {
                                    $this->data1['all_schedule'][$key]['time'] = $end;
                                }
                                $this->data1['all_schedule'][$key]['type'] = $schedule['type'];
                                $this->data1['all_schedule'][$key]['scheudleId'] = $schedule['id'];
                                $this->data1['all_schedule'][$key]['schedule_ride_status'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['name'];
                                $this->data1['all_schedule'][$key]['schedule_ride_status_id'] = @$this->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['id'];
                                $this->data1['all_schedule'][$key]['schedule_driver_rating'] = @$this->getdriverRating($schedule['driver_id']);
                                $this->data1['all_schedule'][$key]['schedule'] = $schedule;
                                $this->data1['all_schedule'][$key]['driver'] = @$this->getScheduleDriver($schedule['id'], $scheduleDate);
                                $this->data1['all_schedule'][$key]['schedule']['carers'] = $this->getScheduleCarers($schedule['id'], $schedule['type'], $scheduleDate);
                            }
                        }

                        if ($this->data1['all_schedule']) {
                            usort($this->data1['all_schedule'], function ($a, $b) {
                                $dateTimeA = new \DateTime($a['time']);
                                $dateTimeB = new \DateTime($b['time']);

                                return $dateTimeA <=> $dateTimeB;
                            });
                        }
                    }
                }


                return @$this->data1;
            }

            return response()->json(['success' => false, "message" => "The user is neither a registered staff nor driver"], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    /**
     * @OA\Post(
     * path="/uc/api/reason",
     * operationId="reasons",
     * tags={"Home Data"},
     * summary="Reasons",
     *   security={ {"Bearer": {} }},
     * description="Reasons",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"type"},
     *               @OA\Property(property="type", type="text", description="0-Leave, 1-Complaint, 2-ShiftChange, 3-CancelRide, 4-RatingReason, 5-TempLocationChange"),
     *                
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Reasons listed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Reasons listed successfully.",
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

    public function reasons(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|integer',
            ]);

            $this->data['reasons'] = Reason::where('type', $request->type)->get();
            return response()->json(['success' => true, "data" => $this->data], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function companyInfo($date)
    {
        $company = CompanyDetails::first();

        $companyDetails = DB::table('company_addresses')
            ->where('company_addresses.company_id', $company->id)
            ->whereDate('start_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereDate('end_date', '>', $date)
                    ->orWhereNull('end_date');
            })
            ->join('company_details', 'company_addresses.company_id', '=', 'company_details.id')
            ->select('company_details.id', 'company_details.name', 'company_details.logo', 'company_details.email', 'company_details.phone', 'company_addresses.address', 'company_addresses.latitude', 'company_addresses.longitude')
            ->first();
        if ($companyDetails) {
            $companyDetails->logo_url = url('public/images');
            return $companyDetails;
        } else {
            return $company;
        }
    }
    public function getScheduleDriver($sId, $date)
    {

        $schedule_driver = Schedule::find($sId);

        $driver = DB::table('sub_users')
            ->join('sub_user_addresses', 'sub_users.id', '=', 'sub_user_addresses.sub_user_id')
            // ->leftJoin('vehicles', 'vehicles.driver_id', '=', 'sub_users.id')
            ->where('sub_users.id', $schedule_driver['driver_id'])
            ->where('sub_user_addresses.start_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->where('sub_user_addresses.end_date', '>', $date)
                    ->orWhereNull('sub_user_addresses.end_date');
            })
            ->select('sub_users.*', 'sub_user_addresses.address', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude')
            ->first();

        return  $driver;
    }


    public function getScheduleDriverDaily($sId, $date){

        $schedule_driver = DailySchedule::find($sId);

        $driver = DB::table('sub_users')
            ->join('sub_user_addresses', 'sub_users.id', '=', 'sub_user_addresses.sub_user_id')
            // ->leftJoin('vehicles', 'vehicles.driver_id', '=', 'sub_users.id')
            ->where('sub_users.id', $schedule_driver['driver_id'])
            ->where('sub_user_addresses.start_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->where('sub_user_addresses.end_date', '>', $date)
                    ->orWhereNull('sub_user_addresses.end_date');
            })
            ->select('sub_users.*', 'sub_user_addresses.address', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude')
            ->first();

        return  $driver;
    }

    //************************ Get Driver Employee Info who is logined in *****************************************/

    public function getDriverEmpoyeeById($user_id)
    {
        $date = date('Y-m-d');
        $userQuery = SubUser::join('sub_user_addresses', 'sub_users.id', '=', 'sub_user_addresses.sub_user_id')
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
    //**************************************************** Change Invoice Status **********************************************/
    public function changeInvoiceStatus($id, $type, $date)
    {
        $invoice = Invoice::where('schedule_id', $id)->where('type', $type)->where('date', $date)->first();
        $invoice->end_time = Carbon::now('Asia/Kolkata')->toTimeString();
        $invoice->is_included = 1;
        $invoice->ride_status = 8;
        $invoice->update();
    }


    // public function subUserAddress($sub_id, $date){


    //     $subUser = DB::table('sub_user_addresses')
    //         ->where('sub_user_addresses.sub_user_id', $sub_id)
    //         ->whereDate('start_date', '<=', $date)
    //         ->where(function ($query) use ($date) {
    //             $query->whereDate('end_date', '>', $date)
    //                 ->orWhereNull('end_date');
    //         })
    //         ->join('sub_users', 'sub_user_addresses.sub_user_id', '=', 'sub_users.id')
    //         ->select('sub_users.*', 'sub_user_addresses.address', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude')
    //         ->first();

    //     return $subUser ? $subUser : null;
    // }

    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required',
            ]);

            $temp_DB_name = DB::connection()->getDatabaseName();

            $default_DBName = env("DB_DATABASE");

            $this->connectDB($default_DBName);

            $findSubUser = SubUser::where('email', $request->email)->first();
            if (isset($findSubUser)) {
                $findSubUser->password = Hash::make($request->password);
                $findSubUser->save();

                //Add history tracking in parent DB
                DB::connection('mysql')->table('update_employee_histories')->insert([
                    'employee_id' => $findSubUser->id,
                    'updated_by' => auth('sanctum')->id() ?? $findSubUser->id,
                    'date' => now()->format('Y-m-d'),
                    'time' => now()->format('H:i:s'),
                    'notes' => 'Password changed',
                    'changed' => 'Password was changed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                //connecting back to Child DB
                $this->connectDB($temp_DB_name);


                // send mail to users with updated password start

                    $this->data["detais"] = [
                        "email" => $request->email,
                        "pass" => $request->password,
                        "name" => $findSubUser->first_name,
                        "date" => now()->format('Y-m-d'),
                    ];
                    $email = $request->email;
                    Mail::send("email.passwordreset", $this->data, function ($message) use ($email) {
                        $message
                        ->to($email)
                        ->from("info@unifygroup.in")
                        ->subject("Reset password");
                    });
                // send mail to users with updated password end 


                return response()->json(['success' => true, "message" => 'Done'], 200);
            }

            //connecting back to Child DB
            $this->connectDB($temp_DB_name);
            return response()->json(['success' => false, "message" => 'Something went wrong'], 500);
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
}
