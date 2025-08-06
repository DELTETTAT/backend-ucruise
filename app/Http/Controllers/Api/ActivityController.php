<?php

namespace App\Http\Controllers\Api;

use App\Models\Holiday;
use App\Models\Rating;
use App\Models\Reschedule;
use App\Models\Schedule;
use App\Models\ScheduleCarerComplaint;
use App\Models\ScheduleCarerRelocation;
use App\Models\SubUser;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\DashboardController;

class ActivityController extends Controller
{
    protected $dash;
    public function __construct(DashboardController $dash)
    {
        $this->dash = $dash;
    }
    /**
     * @OA\Post(
     * path="/uc/api/allDriverActivity",
     * operationId="allDriverActivity",
     * tags={"Dashboard"},
     * summary="All Employee activity",
     *   security={ {"Bearer": {} }},
     * description="All Employee activity",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"startDate"},
     *               @OA\Property(property="startDate", type="text"),
     *               @OA\Property(property="endDate", type="text"),
     *                
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The activity data listed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The activity data listed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */

    public function allDriverActivity(Request $request)
    {
        try {
            $request->validate([
                'startDate' => 'required|date_format:Y-m-d|before_or_equal:today',
                'endDate' => 'nullable|date_format:Y-m-d',
            ]);

            $startDate = $request->startDate;
            if ($request->has('endDate')) {
                $endDate = min($request->endDate, now()->toDateString());
            }
            if ($endDate === null) {
                $endDate = $startDate;
            }

            $dates = $this->generateDatesInRange($startDate, $endDate);

            $drivers = SubUser::whereHas('roles', function ($q) {
                $q->where('name', 'driver');
            })->get();
            $home = new HomeController();
            $allDriverData = []; // Array to store data for all drivers
            $total_Rides = 0;
            $ride_Skipped = 0;
            $total_Complaints = 0;
            $forgot_to_complete = 0;
            $excellent_ratings_count = 0;
            $good_ratings_count = 0;
            $critical_ratings_count = 0;
            $chartData = [
                'total_Rides' => 0,
                'ride_Skipped' => 0,
                'total_Complaints' => 0,
                'forgot_to_complete' => 0,
                'excellent_ratings_count' => 0,
                'good_ratings_count' => 0,
                'critical_ratings_count' => 0
            ];
            foreach ($drivers as $driver) {
                $driverData = [
                    'id' => $driver->id,
                    'name' => $driver->first_name,
                    'total_rides' => 0,
                    'ride_skiped' => 0,
                    'absent_marked' => 0,
                    'complaints' => 0,
                    'rating' => 0,
                    'absent_marked_data' => [],
                    'complaints_data' => [],
                    'forgot_to_complete_data' => [],
                    'absent_marked_data' => [],
                ];
                
                $driverData['rating'] = @$this->getDriverRating($driver->id, $dates);
                $driverData['rating_data'] = Rating::where('driver_id', $driver->id)->whereIn('date', $dates) ->orderBy('date', 'asc')->get();
                $schedules = $this->getWeeklyScheduleInfo([$driver->id], $dates, 1, "all");

                foreach ($schedules as $schedule) {
                    $scheduleDate = date('Y-m-d', strtotime($schedule['date']));
                    $holiday = Holiday::where('date', $scheduleDate)->exists();
                    if (!$holiday) {
                        $driverData['total_rides']++;
                        $total_Rides++;
                        $status = @$home->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['id'];
                        if ($status == 9) {
                            $driverData['ride_skiped']++;
                            $ride_Skipped++;
                            $driverData['ride_skiped_data'][] = [
                                'date' => $scheduleDate,
                                'message' => "Ride not started"
                            ];
                            usort($driverData['ride_skiped_data'], function ($a, $b) {
                                return strtotime($a['date']) - strtotime($b['date']);
                            });
                            
                        } else if ($status != 8) {
                            $driverData['forgot_to_complete_data'][] = [
                                'date' => $scheduleDate,
                                'message' => "Forget to Complete"
                            ];
                            usort($driverData['forgot_to_complete_data'], function ($a, $b) {
                                return strtotime($a['date']) - strtotime($b['date']);
                            });
                            $forgot_to_complete++;
                        
                        }

                        $complaint = ScheduleCarerComplaint::where('driver_id', $driver->id)->where('schedule_id', $schedule['id'])->where('schedule_type', $schedule['type'])->where('date', $scheduleDate)->first();
                        if ($complaint) {
                            $driverData['complaints']++;
                            $driverData['complaints_data'][] = $complaint;
                            usort($driverData['complaints_data'], function ($a, $b) {
                                return strtotime($a['date']) - strtotime($b['date']);
                            });
                            $total_Complaints++;
                        }
                        $carers = @$home->getScheduleCarers($schedule['id'], $schedule['type'], $scheduleDate);
                        if ($carers) {
                         
                            foreach ($carers as $carer) {
                                if ($carer->ride_status_id == 5) {
                                    $driverData['absent_marked_data'][] = [
                                        'date' => $scheduleDate,
                                        'first_name'=>@$carer->first_name,
                                        'message' => "Absent marked"
                                    ];
                                    usort($driverData['absent_marked_data'], function ($a, $b) {
                                        return strtotime($a['date']) - strtotime($b['date']);
                                    });
                                    $driverData['absent_marked']++;
                                }
                            }
                        }
                    }
                }
                if ($driverData['rating'] >= 3.5 && $driverData['rating'] <= 5) {
                    $excellent_ratings_count++;
                } elseif ($driverData['rating'] > 2.5 && $driverData['rating'] < 3.5) {
                    $good_ratings_count++;
                } elseif ($driverData['rating'] <= 2.5 && $driverData['rating'] > 0) {
                    $critical_ratings_count++;
                }


                $allDriverData[] = $driverData;
            }
            $chartData['total_Rides'] = $total_Rides;
            $chartData['ride_Skipped'] = $ride_Skipped;
            $chartData['total_Complaints'] = $total_Complaints;
            $chartData['forgot_to_complete'] = $forgot_to_complete;
            $chartData['excellent_ratings_count'] = $excellent_ratings_count;
            $chartData['good_ratings_count'] = $good_ratings_count;
            $chartData['critical_ratings_count'] = $critical_ratings_count;

            return response()->json([
                'success' => true,
                'chart_data' => $chartData,
                'data' => $allDriverData
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //************************** Employee Acitivity Api *******************/
    /**
     * @OA\Post(
     * path="/uc/api/allEmployeeActivity",
     * operationId="allEmployeeActivity",
     * tags={"Dashboard"},
     * summary="All Employee activity",
     *   security={ {"Bearer": {} }},
     * description="All Employee activity",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"startDate"},
     *               @OA\Property(property="startDate", type="text"),
     *               @OA\Property(property="endDate", type="text"),
     *                
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The activity data listed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The activity data listed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */

    public function allEmployeeActivity(Request $request)
    {
        try {
            $request->validate([
                'startDate' => 'required|date_format:Y-m-d|before_or_equal:today',
                'endDate' => 'nullable|date_format:Y-m-d',
            ]);

            $startDate = $request->startDate;
            if ($request->has('endDate')) {
                $endDate = min($request->endDate, now()->toDateString());
            }
            if ($endDate === null) {
                $endDate = $startDate;
            }
            $dates = $this->generateDatesInRange($startDate, $endDate);
            $employees = SubUser::whereHas('roles', function ($q) {
                $q->where('name', 'carer');
            })->get();
            $home = new HomeController();
            $allEmployeeData = [];
            $total_Rides = 0;
            $total_Leaves = 0;
            $total_Absent = 0;
            $total_Cancel = 0;
            $total_Reschedule = 0;
            $total_Temp_Reschedule = 0;
            $total_Complaints = 0;
            $chartData = [
                'total_Rides' => 0,
                'total_Leaves' => 0,
                'total_Absent' => 0,
                'total_Cancel' => 0,
                'total_Reschedule' => 0,
                'total_Temp_Reschedule' => 0,
                'total_Complaints' => 0
            ];
            foreach ($employees as $employee) {


                $employeeData = [
                    'id' => $employee->id,
                    'name' => @$employee->first_name,
                    'total_rides' => 0,
                    'cancel' => 0,
                    'leaves' => 0,
                    'absents' => 0,
                    'reschedule' => 0,
                    'temp_reschedule' => 0,
                    'complaints' => 0,
                    'absent_data' => [],
                    'cancel_data' => [], 
                    'leaves_data' => []
                

                ];

                $schedules = $this->getWeeklyScheduleInfo([$employee->id], $dates, 2, "all");
                foreach ($schedules as $schedule) {
                    $scheduleDate = date('Y-m-d', strtotime($schedule['date']));
                    $holiday = Holiday::where('date', $scheduleDate)->exists();
                    if (!$holiday) {
                        $employeeData['total_rides']++;

                        $total_Rides++;
                        $carers = $home->getScheduleCarers($schedule['id'], $schedule['type'], $scheduleDate);

                        if ($carers) {
                            foreach ($carers as $key => $carer) {

                                //echo '<pre>';print_r($carer);

                                if ($carer->carer_id == $employee->id) {
                                    if ($carer->ride_status_id == 11) {
                                        $employeeData['leaves']++;

                                        $leaveArray  = [
                                            'date' => $scheduleDate,
                                            'message' => 'Employee on leave',
                                            'type' => $schedule['type'],
                                            'start_time' => $schedule['start_time'],
                                            'end_time' => @$schedule['end_time'],
                                        ];

                                        $employeeData['leaves_data'][] = $leaveArray;

                                        $total_Leaves++;
                                        
                                        usort($employeeData['leaves_data'], function ($a, $b) {
                                            return strtotime($a['date']) - strtotime($b['date']);
                                        });
                                    } else if ($carer->ride_status_id == 5) {
                                        $employeeData['absents']++;
                                        $absentData  = [
                                            'date' => $scheduleDate,
                                            'message' => 'Employee absent',
                                           
                                        ];
                                        $employeeData['absent_data'][] = $absentData;
                                        usort($employeeData['absent_data'], function ($a, $b) {
                                            return strtotime($a['date']) - strtotime($b['date']);
                                        });
                                        $total_Absent++;
                                    } else if ($carer->ride_status_id == 4) {
                                        $employeeData['cancel']++;
                                        $cancelData  = [
                                            'date' => $scheduleDate,
                                            'message' => 'Employee cancelled the ride',
                                         
                                        ];
                                        $employeeData['cancel_data'][] = $cancelData;
                                        usort($employeeData['cancel_data'], function ($a, $b) {
                                            return strtotime($a['date']) - strtotime($b['date']);
                                        });

                                        $total_Cancel++;
                                    }
                                }
                            }
                        }
                    }
                }
                $employeeData['reschedule'] = Reschedule::where('user_id', $employee->id)->whereIn('date', $dates)->count();

                $employeeData['reschedule_data'] = Reschedule::where('user_id', $employee->id)->whereIn('date', $dates)->get();

                $total_Reschedule += $employeeData['reschedule'];
                $employeeData['temp_reschedule'] = ScheduleCarerRelocation::where('staff_id', $employee->id)->whereIn('date', $dates)->count();

                $employeeData['temp_reschedule_data'] = ScheduleCarerRelocation::where('staff_id', $employee->id)->whereIn('date', $dates)->get();

                $total_Temp_Reschedule += $employeeData['temp_reschedule'];

                $employeeData['complaints'] = ScheduleCarerComplaint::where('staff_id', $employee->id)->whereIn('date', $dates)->count();
                // Complaint data
                $employeeData['complaints_data'] = ScheduleCarerComplaint::where('staff_id', $employee->id)->whereIn('date', $dates)->get();

                $total_Complaints += $employeeData['complaints'];

                $allEmployeeData[] = $employeeData;
            }
            $chartData['total_Rides'] = $total_Rides;
            $chartData['total_Leaves'] = $total_Leaves;
            $chartData['total_Absent'] = $total_Absent;
            $chartData['total_Cancel'] = $total_Cancel;
            $chartData['total_Complaints'] = $total_Complaints;
            $chartData['total_Reschedule'] = $total_Reschedule;
            $chartData['total_Temp_Reschedule'] = $total_Temp_Reschedule;

            return response()->json([
                'success' => true,
                'chart_data' => $chartData,
                'data' => $allEmployeeData
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
   


    public function generateDatesInRange($startDate, $endDate)
    {
        $dates = [];

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Iterate through the range of dates and add them to the array
        while ($start->lte($end)) {
            $dates[] = $start->toDateString();
            $start->addDay();
        }

        return $dates;
    }
    public function getDriverRating($id, $dates)
    {
        $ratings = Rating::where('driver_id', $id)->whereIn('date', $dates)->pluck('rate')->toArray();

        $averageRating = 0;

        if (!empty($ratings)) {
            $sumOfRatings = array_sum($ratings);
            $averageRating = round($sumOfRatings / count($ratings), 1);
        }
        return $averageRating;
    }

    //********************* Route Management *******************************/

    /**
     * @OA\Get(
     * path="/uc/api/manageRoute",
     * operationId="manageRoute",
     * tags={"Dashboard"},
     * summary="Route management",
     *   security={ {"Bearer": {} }},
     * description="Route management",
     *      @OA\Response(
     *          response=201,
     *          description="Route management data listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Route management data listed successfully",
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
    public function manageRoute()
    {
        try {

            $schedules = Schedule::whereHas('carers')
                ->where('end_date', '>=', date('Y-m-d'))
                ->orWhere('date', '>=', date('Y-m-d'))
                ->distinct('id')
                ->get();

            $totalSeats = 0;
            $fullyOccupiedSchedules = 0;
            $totalVacantSeats = 0;

            foreach ($schedules as $schedule) {
                $vehicle = Vehicle::find($schedule->vehicle_id);
                $totalSeats += $vehicle->seats;

                if ($schedule->shift_type_id == 1 || $schedule->shift_type_id == 3) {
                    $assignedCarers = count($schedule->carers);
                }
                if ($schedule->shift_type_id == 2) {
                    $pickCarers = $schedule->carers->where('shift_type', 'pick')->count();
                    $dropCarers = $schedule->carers->where('shift_type', 'drop')->count();
                    $assignedCarers = max($pickCarers, $dropCarers);
                }
                $vacantSeats = $vehicle->seats - $assignedCarers;
                $schedule->vacant_seats = $vacantSeats;

                $totalVacantSeats += $vacantSeats; // Accumulate vacant seats

                if ($vacantSeats === 0) {
                    $fullyOccupiedSchedules++;
                }
            }

            $total = $schedules->count();

            $this->data['schedule_details']['total_seats'] = $totalSeats;
            $this->data['schedule_details']['fully_occupied'] = $fullyOccupiedSchedules;
            $this->data['schedule_details']['total_vacant_seats'] = $totalVacantSeats; // Include total vacant seats
            $this->data['schedule_details']['total_schedules'] = $total;
            $this->data['schedule_analysis']['driver_chart_data'] = @$this->dash->driverActivity();
            $this->data['schedule_analysis']['employee_chart_data'] = @$this->dash->employeeActivity();
            $this->data['similar_routes'] = @$this->findSimilarAndDistinctRoutes();

            return response()->json([
                'success' => true,
                'data' => $this->data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function findSimilarAndDistinctRoutes()
    {
        $schedules = Schedule::where('end_date', '>=', date('Y-m-d'))
            ->orWhere('date', '>=', date('Y-m-d'))
            ->get();

        $groupedSchedules = $schedules->groupBy('city');

        $totalSimilarCount = 0;
        $totalDistinctCount = 0;
        $cityRoutes = [];
        $totalVacantSeats = 0;
        $fullyOccupiedSchedules = 0;

        foreach ($groupedSchedules as $city => $citySchedules) {
            $scheduleCounts = $citySchedules->countBy('schedule');
            $similarCount = $citySchedules->filter(function ($schedule) use ($scheduleCounts) {
                return $scheduleCounts[$schedule->schedule] > 1;
            })->count();

            $distinctCount = $citySchedules->filter(function ($schedule) use ($scheduleCounts) {
                return $scheduleCounts[$schedule->schedule] == 1;
            })->count();

            $totalSimilarCount += $similarCount;
            $totalDistinctCount += $distinctCount;

            // Process schedules to calculate vacant seats
            foreach ($citySchedules as $schedule) {
                $vehicle = Vehicle::find($schedule->vehicle_id);
                $totalSeats = $vehicle->seats;

                if ($schedule->shift_type_id == 1 || $schedule->shift_type_id == 3) {
                    $assignedCarers = count($schedule->carers);
                } elseif ($schedule->shift_type_id == 2) {
                    $pickCarers = $schedule->carers->where('shift_type', 'pick')->count();
                    $dropCarers = $schedule->carers->where('shift_type', 'drop')->count();
                    $assignedCarers = max($pickCarers, $dropCarers);
                }

                $vacantSeats = $totalSeats - $assignedCarers;
                $schedule->vacant_seats = $vacantSeats;

                $totalVacantSeats += $vacantSeats;

                if ($vacantSeats === 0) {
                    $fullyOccupiedSchedules++;
                }
            }

            // Store city routes
            $cityRoutes[] = [
                'city' => $city,
                'distinct_routes' => $distinctCount,
                'similar_routes' => $similarCount,
                'total_routes' => $distinctCount + $similarCount,
                'similar_route_info' => $citySchedules->filter(function ($schedule) use ($scheduleCounts) {
                    return $scheduleCounts[$schedule->schedule] > 1;
                })->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'vacant_seats' => $schedule->vacant_seats,
                        'schedule' => $schedule,
                    ];
                })->toArray(),
            ];
        }

        return [
            'city_routes' => $cityRoutes,
            'total_distinct_routes' => $totalDistinctCount,
            'total_similar_routes' => $totalSimilarCount,
            'total_routes' => $totalDistinctCount + $totalSimilarCount,
            'total_vacant_seats' => $totalVacantSeats,
            'fully_occupied_schedules' => $fullyOccupiedSchedules,
        ];
    }
}
