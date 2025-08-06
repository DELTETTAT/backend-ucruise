<?php

namespace App\Http\Controllers\Api;

use Mail;
use App\Models\CompanyAddresse;
use App\Models\Holiday;
use App\Models\Invoice;
use App\Models\Leave;
use App\Models\Rating;
use App\Models\Reschedule;
use App\Models\Schedule;
use App\Models\ScheduleCarer;
use App\Models\ScheduleCarerComplaint;
use App\Models\ScheduleCarerRelocation;
use App\Models\ScheduleCarerStatus;
use App\Models\ScheduleStatus;
use App\Models\SubUser;
use App\Models\User;
use App\Models\SubUserAddresse;
use App\Models\Vehicle;
use App\Models\ShiftTypes;
use App\Models\CapRequest;
use App\Models\CompanyDetails;
use Carbon\Carbon;
use App\Mail\LeaveApprovalEmail;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use DB;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\HomeController;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends Controller
{

    protected $notification;

    public function __construct(ScheduleController $notification)
    {
        $this->notification = $notification;
    }



    //******************** List Dashboard Data api ***********************************/

    /**
     * @OA\Get(
     * path="/uc/api/listDashboardData",
     * operationId="listDashboardData",
     * tags={"Dashboard"},
     * summary="Dashboard",
     *   security={ {"Bearer": {} }},
     * description="Account Setup",
     *      @OA\Response(
     *          response=201,
     *          description="Data listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Data listed successfully",
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
    public function listDashboardData(Request $request)
    {
        try {
            $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

            $leaveRequests = Leave::with(['reason', 'staff' => function ($query) {
                $query->select('id', 'first_name', 'unique_id');
            }])->whereDate('start_date', '>=', now())
                ->orderBy('start_date', 'desc')
                ->get();
            $tempRequests = ScheduleCarerRelocation::with(['reason', 'user' => function ($query) {
                $query->select('id', 'first_name', 'unique_id');
            }])
                ->orderBy('date', 'desc')

                ->get();

            $rescheduleRequests = Reschedule::with(['reason', 'user' => function ($query) {
                $query->select('id', 'first_name', 'email');
            }])
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'desc')
                ->get();

            $rescheduleStatus = [
                'total' => $rescheduleRequests->count(),
                'submit' => $rescheduleRequests->where('status', 0)->count(),
                'accept' => $rescheduleRequests->where('status', 1)->count(),
                'reject' => $rescheduleRequests->where('status', 2)->count(),
            ];

            $capRequests = CapRequest::with(['user' => function ($query) {
                $query->select('id', 'first_name', 'email');
            }])
            ->whereBetween('from_date', [$startOfMonth, $endOfMonth])
            ->orderBy('from_date', 'desc')->get();

            $caprequestStatus = [
                'total' => $capRequests->count(),
                'pending' => $capRequests->where('status', 0)->count(),
                'approved' => $capRequests->where('status', 1)->count(),
                'reject' => $capRequests->where('status', 2)->count(),
            ];

            $this->data['driver_activity'] = @$this->driverActivity($request);
            $this->data['employee_activity'] = @$this->employeeActivity();
            $this->data['Route_management'] = @$this->routeManagement();
            $this->data['billing_data'] = @$this->billing($startOfMonth, $endOfMonth);
            $this->data['rating_tracker'] = @$this->driverRating();
            $this->data['gelocation']['drivers'] = @$this->getGeolocation(1, 0);
            $this->data['gelocation']['employees'] = @$this->getGeolocation(2, 0);
            $this->data['gelocation']['drivers_percentage'] = @$this->getGeolocation(1, 1);
            $this->data['gelocation']['employees_percentage'] = @$this->getGeolocation(2, 1);
            $this->data['leaves'] = @$leaveRequests;
            $this->data['leaves_count'] = count($this->data['leaves']);
            $this->data['temp_location_change'] = @$tempRequests;
            $this->data['temp_location_change_count'] = count($this->data['temp_location_change']);
            $this->data['reschedule'] = @$rescheduleRequests;
            $this->data['rescheduleStatus'] = $rescheduleStatus;
            $this->data['caprequests'] = $capRequests;   
            $this->data['caprequestStatus'] = $caprequestStatus;
            $this->data['complaints'] = @$this->raisedComplaints();
            $this->data['driver_trips_details'] = @$this->driverTripsDetails();
            //$this->data['driver_trips_details_month'] = @$this->driverTripsDetailsOfMinth();
            $this->data['alert_type_breakdown'] = @$this->alertTypeBreakdown();
         
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
    //******************* Function to get the dashboard data **********************/
    public function billing($startOfMonth, $endOfMonth)
    {
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        $billingThisMonth = Invoice::whereBetween('date', [$startOfMonth, $endOfMonth])->where('is_included', 1)->sum('fare');
        $billingLastMonth = Invoice::whereBetween('date', [$startOfLastMonth, $endOfLastMonth])->where('is_included', 1)->sum('fare');
        $percentageChange = 0;
        if ($billingLastMonth != 0) {
            $percentageChange = (($billingThisMonth - $billingLastMonth) / $billingLastMonth) * 100;
            $percentageChange = number_format($percentageChange, 2);
        }
        if ($percentageChange > 0) {
            $changeType = 'increase';
        } elseif ($percentageChange < 0) {
            $changeType = 'decrease';
        } else {
            $changeType = 'no change';
        }
        return [
            'billing_this_month' => $billingThisMonth,
            'billing_last_month' => $billingLastMonth,
            'percentage_change' => $percentageChange,
            'change_type' => $changeType,
        ];
    }


    //************************ Function to get the geolocation data **********************/
    public function getGeolocation($type, $task)
    {
        if ($task == 0) {
            $role = ($type == 1) ? 'driver' : 'carer';
            $date = date('Y-m-d');
            $usersData = SubUser::whereHas("roles", function ($q) use ($role) {
                $q->where("name", $role);
            })
                ->where('close_account', 1)
                ->leftJoin('sub_user_addresses', function ($join) use ($date) {
                    $join->on('sub_users.id', '=', 'sub_user_addresses.sub_user_id')
                        ->whereDate('sub_user_addresses.start_date', '<=', $date)
                        ->where(function ($query) use ($date) {
                            $query->whereDate('sub_user_addresses.end_date', '>', $date)
                                ->orWhereNull('sub_user_addresses.end_date');
                        });
                })
                ->orderBy("sub_users.id", "DESC")
                ->select('sub_users.id', 'sub_users.first_name', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address')
                ->get();

            return $usersData;
        } else if ($task == 1) {
            $role = ($type == 1) ? 'driver' : 'carer';
            $totalUsers = SubUser::count();
            $user = SubUser::whereHas("roles", function ($q) use ($role) {
                $q->where("name", $role);
            })->count();
            if ($totalUsers == 0) {
                return 0.00;
            }
            @$percentage = ($user / $totalUsers) * 100;
            $percentage = number_format($percentage, 2);
            return $percentage;
        }
    }


    /**
     * @OA\post(
     * path="/uc/api/driverActivity",
     * operationId="driverActivity",
     * tags={"Dashboard"},
     * summary="drivers activitysummary details",
     *   security={ {"Bearer": {} }},
     *    description="Booking summary details",
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="startDate",
     *                     type="string",
     *                     format="date",
     *                     nullable=true,
     *                     example="2025-07-01"
     *                 ),
     *                 @OA\Property(
     *                     property="endDate",
     *                     type="string",
     *                     format="date",
     *                     nullable=true,
     *                     example="2025-07-07"
     *                 )
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *          response=201,
     *          description="Data listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Data listed successfully",
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


    

    //********************** Driver Activing Data ********************************/
    public function driverActivity(Request $request)
    {

        
        $startdate = $request->input('startDate') ?: date('Y-m-d');
        $end_date = $request->input('endDate') ?: date('Y-m-d');

        $total_driver = SubUser::whereHas('roles', function ($q) {
            $q->where('name', 'driver');
        })->count();

        $driver_with_schedule = Schedule::whereHas('driver')->where('end_date', '>=', $end_date)
            ->orWhere('date', '>=', $startdate)
            ->distinct('driver_id')
            ->count('driver_id');
            
        $driver_without_schedule = $total_driver - $driver_with_schedule;

        return [
            'total_drivers' => $total_driver,
            'driver_with_schedule' => $driver_with_schedule,
            'driver_without_schedule' => $driver_without_schedule,

        ];
    }


    // ***************** Start Driver trips details of current date************************/

    public function driverTripsDetails(){

        $company = CompanyDetails::first();
        $companyAddress = CompanyAddresse::first();
        $schedules = Schedule::with([
            'shiftType:id,name,external_id,color,created_at,updated_at',
            'driver:id,first_name,last_name,email,phone,profile_image',
            'vehicle:id,name,seats,vehicle_no',
            'carers.user:id,first_name,last_name,email,profile_image',
            'scheduleStatus.status:id,name',
            'pricebook.priceBookData:id,price_book_id,day_of_week,per_ride'
        ])
        ->whereDate('date', today())
        ->get(['id', 'date', 'vehicle_id','driver_id', 'shift_type_id','start_time', 'end_time', 'locality', 'city', 'latitude', 'longitude', 'created_at']);
        return [
            'company'=>[
                'company_address' => $companyAddress->address,
                'company_latitude' => $companyAddress->latitude,
                'company_longitude' => $companyAddress->longitude,
                'company_name' => $company->name,
                'company_logo' => $company->logo
            ],
            'url'=>[
                 'employee_image_url' => url('images'),
            ],
            'trips_details' => $schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'date' => $schedule->date,
                    'driver_id' => $schedule->driver_id,
                    'vehicle_id' => $schedule->vehicle_id,
                    'times' => [
                        'start' => $schedule->start_time,
                        'end' => $schedule->end_time
                    ],
                    'location' => [
                        'locality' => $schedule->locality,
                        'city' => $schedule->city,
                        'coordinates' => [
                            'latitude' => $schedule->latitude,
                            'longitude' => $schedule->longitude
                        ]
                    ],
                    'driver' => $schedule->driver ?[
                        'id' => $schedule->driver->id,
                        'name' => trim($schedule->driver->first_name.' '.$schedule->driver->last_name),
                        'email' => $schedule->driver->email,
                        'profile_image' => $schedule->driver->profile_image,
                    ] : null,
                    'vehicle' =>  $schedule->vehicle ? [
                        'id' => $schedule->vehicle->id,
                        'name' => $schedule->vehicle->name,
                        'seats' => $schedule->vehicle->seats,
                        'number' => $schedule->vehicle->vehicle_no
                    ] : null,
                    'shift_type' => $schedule->shiftType?->name,
                    'status' => $schedule->scheduleStatus ? [
                        'id' => $schedule->scheduleStatus->id,
                        'schedule_id' => $schedule->scheduleStatus->schedule_id,
                        'name' => $schedule->scheduleStatus->Status->name,
                        'date' => $schedule->scheduleStatus->date,
                        'status_id' => $schedule->scheduleStatus->status_id,
                        'type' => $schedule->scheduleStatus->type,
                        'times' => [
                            'start' => $schedule->scheduleStatus->start_time,
                            'end' => $schedule->scheduleStatus->end_time
                        ]
                    ] : null,
                    'carers' => $schedule->carers->map(function ($carer) {
                        return $carer->user ? [
                            'id' => $carer->user->id,
                            'name' => trim($carer->user->first_name . ' ' . $carer->user->last_name),
                            'email' => $carer->user->email,
                            'image' => $carer->user->profile_image ?? null
                        ] : null;
                    })->filter()->values()
                ];
            })
        ];

    }


    /**
     * @OA\post(
     * path="/uc/api/driverTripsDetailsOfMinth",
     * operationId="driverTripsDetailsOfMinth",
     * tags={"Dashboard"},
     * summary="drivers summary details",
     *   security={ {"Bearer": {} }},
     * description="Booking summary details",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               @OA\Property(property="month", type="string", description="2025-07"),
     *               @OA\Property(property="per_page", type="string", description="10"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Data listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Data listed successfully",
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



    // ***************** Start Driver trips details of current month************************/
    public function driverTripsDetailsOfMinth(Request $request){
        
        // $company = CompanyDetails::first();
        // $companyAddress = CompanyAddresse::first();

        // $perPage = $request->input('per_page', 5);


        // $monthInput = $request->input('month');
        // $month = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        // $startOfMonth = Carbon::parse($month)->startOfMonth()->format('Y-m-d');
        // $endOfMonth = Carbon::parse($month)->endOfMonth()->format('Y-m-d');

        // // Get all schedules from start of month to today
        // $schedules = Schedule::with([
        //     'shiftType:id,name,external_id,color,created_at,updated_at',
        //     'driver:id,first_name,last_name,email,phone,profile_image',
        //     'vehicle:id,name,seats,vehicle_no',
        //     'carers.user:id,first_name,last_name,email,profile_image',
        //     'scheduleStatus.status:id,name',
        //     'pricebook.priceBookData:id,price_book_id,day_of_week,per_ride'
        // ])
        // ->whereBetween('date', [$startOfMonth, $endOfMonth])
        // ->get(['id', 'date', 'vehicle_id','driver_id', 'shift_type_id','start_time', 'end_time', 'locality', 'city', 'latitude', 'longitude', 'created_at']);

        // // Convert date strings to Carbon instances for grouping
        // $schedules->each(function ($item) {
        //     $item->date = \Carbon\Carbon::parse($item->date);
        // });

        // // Group schedules by date
        // $groupedSchedules = $schedules->groupBy(function ($item) {
        //     return $item->date->format('Y-m-d');
        // });

        // // Create a date range from start of month to today
        // $dateRange = collect();
        // $currentDate = \Carbon\Carbon::parse($startOfMonth);
        // $endDate = \Carbon\Carbon::parse($endOfMonth);

        // while ($currentDate <= $endDate) {
        //     $dateString = $currentDate->format('Y-m-d');
        //     $dateRange[$dateString] = [
        //         'data' => $groupedSchedules->has($dateString) ? 
        //             $groupedSchedules[$dateString]->map(function ($schedule) {
        //                 return [
        //                     'id' => $schedule->id,
        //                     'date' => $schedule->date->format('Y-m-d'),
        //                     'driver_id' => $schedule->driver_id,
        //                     'vehicle_id' => $schedule->vehicle_id,
        //                     'times' => [
        //                         'start' => $schedule->start_time,
        //                         'end' => $schedule->end_time
        //                     ],
        //                     'location' => [
        //                         'locality' => $schedule->locality,
        //                         'city' => $schedule->city,
        //                         'coordinates' => [
        //                             'latitude' => $schedule->latitude,
        //                             'longitude' => $schedule->longitude
        //                         ]
        //                     ],
        //                     'driver' => $schedule->driver ? [
        //                         'id' => $schedule->driver->id,
        //                         'name' => trim($schedule->driver->first_name.' '.$schedule->driver->last_name),
        //                         'email' => $schedule->driver->email,
        //                         'profile_image' => $schedule->driver->profile_image,
        //                     ] : null,
        //                     'vehicle' => $schedule->vehicle ? [
        //                         'id' => $schedule->vehicle->id,
        //                         'name' => $schedule->vehicle->name,
        //                         'seats' => $schedule->vehicle->seats,
        //                         'number' => $schedule->vehicle->vehicle_no
        //                     ] : null,
        //                     'shift_type' => $schedule->shiftType?->name,
        //                     'status' => $schedule->scheduleStatus ? [
        //                         'id' => $schedule->scheduleStatus->id,
        //                         'schedule_id' => $schedule->scheduleStatus->schedule_id,
        //                         'name' => $schedule->scheduleStatus->Status->name,
        //                         'date' => $schedule->scheduleStatus->date,
        //                         'status_id' => $schedule->scheduleStatus->status_id,
        //                         'type' => $schedule->scheduleStatus->type,
        //                         'times' => [
        //                             'start' => $schedule->scheduleStatus->start_time,
        //                             'end' => $schedule->scheduleStatus->end_time
        //                         ]
        //                     ] : null,
        //                     'carers' => $schedule->carers->map(function ($carer) {
        //                         return $carer->user ? [
        //                             'id' => $carer->user->id,
        //                             'name' => trim($carer->user->first_name . ' ' . $carer->user->last_name),
        //                             'email' => $carer->user->email,
        //                             'image' => $carer->user->profile_image ?? null
        //                         ] : null;
        //                     })->filter()->values()
        //                 ];
        //             })->toArray() : []
        //     ];
        //     $currentDate->addDay();
        // }

        // return [
        //         'company' => [
        //             'company_address' => $companyAddress->address,
        //             'company_latitude' => $companyAddress->latitude,
        //             'company_longitude' => $companyAddress->longitude,
        //             'company_name' => $company->name,
        //             'company_logo' => $company->logo,
        //         ],
        //         'url' => [
        //             'employee_image_url' => url('images'),
        //         ],
        //         'trips_details' => $dateRange
        // ];



            $company = CompanyDetails::first();
            $companyAddress = CompanyAddresse::first();

            $perPage = (int) $request->input('per_page', 10);
            $page = (int) $request->input('page', 1);

            $monthInput = $request->input('month');
            $month = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
            $startOfMonth = Carbon::parse($month)->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::parse($month)->endOfMonth()->format('Y-m-d');

            $schedules = Schedule::with([
                'shiftType:id,name,external_id,color,created_at,updated_at',
                'driver:id,first_name,last_name,email,phone,profile_image',
                'vehicle:id,name,seats,vehicle_no',
                'carers.user:id,first_name,last_name,email,profile_image',
                'scheduleStatus.status:id,name',
                'pricebook.priceBookData:id,price_book_id,day_of_week,per_ride'
            ])
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get(['id', 'date', 'vehicle_id','driver_id', 'shift_type_id','start_time', 'end_time', 'locality', 'city', 'latitude', 'longitude', 'created_at']);

            // Convert dates to Carbon for grouping
            $schedules->each(function ($item) {
                $item->date = Carbon::parse($item->date);
            });

            // Group schedules by date
            $groupedSchedules = $schedules->groupBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

            // Build only non-empty schedule days
            $dateRange = collect();

            foreach ($groupedSchedules as $dateString => $schedulesOfDay) {
                $dateRange[$dateString] = [
                    'data' => $schedulesOfDay->map(function ($schedule) {
                        return [
                            'id' => $schedule->id,
                            'date' => $schedule->date->format('Y-m-d'),
                            'driver_id' => $schedule->driver_id,
                            'vehicle_id' => $schedule->vehicle_id,
                            'times' => [
                                'start' => $schedule->start_time,
                                'end' => $schedule->end_time
                            ],
                            'location' => [
                                'locality' => $schedule->locality,
                                'city' => $schedule->city,
                                'coordinates' => [
                                    'latitude' => $schedule->latitude,
                                    'longitude' => $schedule->longitude
                                ]
                            ],
                            'driver' => $schedule->driver ? [
                                'id' => $schedule->driver->id,
                                'name' => trim($schedule->driver->first_name . ' ' . $schedule->driver->last_name),
                                'email' => $schedule->driver->email,
                                'profile_image' => $schedule->driver->profile_image,
                            ] : null,
                            'vehicle' => $schedule->vehicle ? [
                                'id' => $schedule->vehicle->id,
                                'name' => $schedule->vehicle->name,
                                'seats' => $schedule->vehicle->seats,
                                'number' => $schedule->vehicle->vehicle_no
                            ] : null,
                            'shift_type' => $schedule->shiftType?->name,
                            'status' => $schedule->scheduleStatus ? [
                                'id' => $schedule->scheduleStatus->id,
                                'schedule_id' => $schedule->scheduleStatus->schedule_id,
                                'name' => $schedule->scheduleStatus->Status->name,
                                'date' => $schedule->scheduleStatus->date,
                                'status_id' => $schedule->scheduleStatus->status_id,
                                'type' => $schedule->scheduleStatus->type,
                                'times' => [
                                    'start' => $schedule->scheduleStatus->start_time,
                                    'end' => $schedule->scheduleStatus->end_time
                                ]
                            ] : null,
                            'carers' => $schedule->carers->map(function ($carer) {
                                return $carer->user ? [
                                    'id' => $carer->user->id,
                                    'name' => trim($carer->user->first_name . ' ' . $carer->user->last_name),
                                    'email' => $carer->user->email,
                                    'image' => $carer->user->profile_image ?? null
                                ] : null;
                            })->filter()->values()
                        ];
                    })->toArray()
                ];
            }

            // Sort and paginate
            $sorted = $dateRange->sortKeysDesc();

            // Convert associative dates to indexed array for paginator
            $indexedDates = collect($sorted->all());

            $paginated = new LengthAwarePaginator(
                $indexedDates->forPage($page, $perPage)->all(),
                $indexedDates->count(),
                $perPage,
                $page,
                ['path' => url()->current(), 'query' => $request->query()]
            );

            return [
                'company' => [
                    'company_address' => $companyAddress->address ?? '',
                    'company_latitude' => $companyAddress->latitude ?? '',
                    'company_longitude' => $companyAddress->longitude ?? '',
                    'company_name' => $company->name ?? '',
                    'company_logo' => $company->logo ?? '',
                ],
                'url' => [
                    'employee_image_url' => url('images'),
                ],
                'pagination' => [
                    'total' => $paginated->total(),
                    'per_page' => $paginated->perPage(),
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'from' => $paginated->firstItem(),
                    'to' => $paginated->lastItem(),
                ],
                'trips_details' => $paginated->items()
            ];

    }


    // ***************** End Driver trips details ************************/


 // ********************** Start Alert type break down *************************/ 

    protected function alertTypeBreakdown(){
        $noshows['noshows'] = $noshow = User::where('no_show', 'Yes')->get();
        $noshows['total_noshows'] =  $noshow->count();


            $startdate = date('Y-m-d');
            $end_date = date('Y-m-d');

            $driver_without_schedule = Schedule::whereHas('driver')
            ->whereBetween('date', [$startdate, $end_date])
            ->whereDoesntHave('scheduleStatus')
            ->distinct('driver_id')
            ->get();

            $homecontrollerData  = new HomeController();

            foreach($driver_without_schedule as $drivSchedules){

                $scheduleId = $drivSchedules->id;
                $type = "";
                if ($drivSchedules->shift_type_id == 1) {
                    $type = 'pick';
                } elseif ($drivSchedules->shift_type_id == 3) {
                    $type = 'drop';
                } elseif ($drivSchedules->shift_type_id == 2) {
                    $type = 'pick and drop';
                } else {
                    $type = null;
                }
                $date = $drivSchedules->date;

                $driversStatus = $homecontrollerData->checkRideStatus($scheduleId, $type, $date);
                $drivSchedules['ride_status'] =  $driversStatus['name'];
            }



               $scheduledUsers = SubUser::whereHas('roles', function ($query) {
                    $query->where('role_id', 4);
                })
                ->whereHas('scheduleCarers', function ($query) use ($startdate) {
                    $query->whereDate('created_at', $startdate)
                        ->whereHas('carerStatus', function ($q) {
                            $q->whereIn('status_id', [4, 5, 11]);
                        });
                })
                ->with(['scheduleCarers' => function ($query) use ($startdate) {
                    $query->whereDate('created_at', $startdate)
                        ->whereHas('carerStatus', function ($q) {
                            $q->whereIn('status_id', [4, 5, 11]);
                        })
                        ->with('carerStatus');
                }])
                ->select('id', 'first_name', 'last_name', 'email', 'profile_image', 'shift_type')
                ->get();



        return [
            'noshow' =>$noshows,
            'scheduleDrivers' =>$driver_without_schedule,
            'totalscheduleDrivers' =>$driver_without_schedule->count(),
            'scheduleCaremiss' =>$scheduledUsers,
            'totalscheduleCaremiss' =>$scheduledUsers->count(),
        ];
    }

 // ********************** End Alert type break down *************************/ 




    //********************* Function for employee activity ****************/

    public function employeeActivity()
    {
        $total_employees = SubUser::whereHas('roles', function ($q) {
            $q->where('name', 'carer');
        })->count();
        $employee_with_schedule = ScheduleCarer::whereHas('schedule', function ($query) {
            $query->where('end_date', '>=', date('Y-m-d'))
                ->orWhere('date', '>=', date('Y-m-d'));
        })
            ->distinct('carer_id')
            ->pluck('carer_id')
            ->count();
        $employee_without_schedule = $total_employees - $employee_with_schedule;

        return [
            'total_employees' => $total_employees,
            'employees_with_schedule' => $employee_with_schedule,
            'employees_without_schedule' => $employee_without_schedule,

        ];
    }

    //*************************** Route Management ***************************/
    public function routeManagement()
    {
        $schedules = Schedule::where('end_date', '>=', date('Y-m-d'))
            ->orWhere('date', '>=', date('Y-m-d'))
            ->get();

        $groupedSchedules = $schedules->groupBy('city');


        $totalSimilarCount = 0;
        $totalDistinctCount = 0;

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
            $total = $totalSimilarCount + $totalDistinctCount;
        }

        return [
            'distint_routes' => $totalDistinctCount,
            'similar_routes' => $totalSimilarCount,
            'total_routes' => $total,

        ];
    }

    //*************************** Employee Activity data  ***********************/
    public function raisedComplaints()
    {
        $closed = ScheduleCarerComplaint::where('status', 0)->count();
        $pending = ScheduleCarerComplaint::where('status', 1)->count();
        $reject = ScheduleCarerComplaint::where('status', 2)->count();
        $total = $closed + $pending;
        return [
            'closed' => $closed,
            'pending' => $pending,
            'reject' => $reject,
            'total' => $total
        ];
    }

    //******************** Function to get the driver rating ***************************/
    public function driverRating()
    {
        $drivers = SubUser::with('vehicle')->whereHas("roles", function ($q) {
            $q->where("name", "driver");
        })->get();

        $driverRatings = [];

        foreach ($drivers as $driver) {
            $ratings = Rating::where('driver_id', $driver->id)->pluck('rate')->toArray();

            $averageRating = 0;

            if (!empty($ratings)) {
                $sumOfRatings = array_sum($ratings);
                $averageRating = round($sumOfRatings / count($ratings), 1);
            }

            $driverRatings[] = [
                'id' => $driver->id,
                'name' => $driver->first_name,
                'email' => $driver->email,
                'rating' => $averageRating,
                'image' => $driver->profile_image,
                'image_url' => url('images'),
            ];
        }

        return $driverRatings;
    }



    
    //************************** Dashboard Alert breakdown Api *******************/
    /**
     * @OA\Post(
     *     path="/uc/api/dashboardAlertbreakdown",
     *     operationId="dashboardAlertbreakdown",
     *     tags={"Dashboard"},
     *     summary="Alert type breakdown API",
     *     description="Returns the alert type breakdown based on the provided date range. If dates are not provided, the system will use defaults or return all data.",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="startDate",
     *                     type="string",
     *                     format="date",
     *                     nullable=true,
     *                     example="2025-07-01"
     *                 ),
     *                 @OA\Property(
     *                     property="endDate",
     *                     type="string",
     *                     format="date",
     *                     nullable=true,
     *                     example="2025-07-07"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Alert breakdown retrieved successfully.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Alert breakdown created successfully.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found")
     * )
     */


    public function dashboardAlertbreakdown(Request $request){

        try {
            $noshows['noshows'] = $noshow = User::where('no_show', 'Yes')->get();
            $noshows['total_noshows'] =  $noshow->count();

            $startdate = $request->input('startDate') ?: date('Y-m-d');
            $end_date = $request->input('endDate') ?: date('Y-m-d');


            $unshinedDriverCount = SubUser::whereHas('roles', function ($query) {
                    $query->where('role_id', 5); // Driver role
                })
            ->whereDoesntHave('schedulesAsDriver', function ($query) use ($startdate, $end_date) {
                    $query->whereBetween('date', [$startdate, $end_date]);
                })->count();


            $unshinedUsers = User::whereHas('roles', function ($query) {
                    $query->where('role_id', 4);
                    $query->where('cab_facility', 1);
            })
            ->whereDoesntHave('scheduleCarers', function ($query) use ($startdate, $end_date) {
                    $query->whereBetween('created_at', [$startdate, $end_date]);
                })->count();

            $driver_without_schedule = Schedule::whereHas('driver')
            ->whereBetween('date', [$startdate, $end_date])
            ->whereDoesntHave('scheduleStatus')
            ->distinct('driver_id')
            ->get();


            $driver_without_schedulecount = Schedule::whereHas('driver')
            ->whereBetween('date', [$startdate, $end_date])
            ->whereDoesntHave('scheduleStatus')
            ->distinct('driver_id')
            ->count();


            $driver_with_schedule = Schedule::whereHas('driver')
            ->whereBetween('date', [$startdate, $end_date])
            ->distinct('driver_id')
            ->count();

            $homecontrollerData  = new HomeController();

            foreach($driver_without_schedule as $drivSchedules){

                $scheduleId = $drivSchedules->id;
                $type = "";
                if ($drivSchedules->shift_type_id == 1) {
                    $type = 'pick';
                } elseif ($drivSchedules->shift_type_id == 3) {
                    $type = 'drop';
                } elseif ($drivSchedules->shift_type_id == 2) {
                    $type = 'pick and drop';
                } else {
                    $type = null;
                }
                $date = $drivSchedules->date;

                $driversStatus = $homecontrollerData->checkRideStatus($scheduleId, $type, $date);
                $drivSchedules['ride_status'] =  $driversStatus['name'];
            }

            $startdate1 = Carbon::parse($startdate)->startOfDay();
            $end_date1 = Carbon::parse($end_date)->endOfDay();

           $scheduledUsers = SubUser::whereHas('roles', function ($query) {
                    $query->where('role_id', 4);
                })
                ->whereHas('scheduleCarers', function ($query) use ($startdate1, $end_date1) {
                    $query->whereBetween('created_at', [$startdate1, $end_date1])
                        ->whereHas('carerStatus', function ($q) {
                            $q->whereIn('status_id', [4, 5, 11]);
                        });
                })
                ->with(['scheduleCarers' => function ($query) use ($startdate1, $end_date1) {
                    $query->whereBetween('created_at', [$startdate1, $end_date1])
                        ->whereHas('carerStatus', function ($q) {
                            $q->whereIn('status_id', [4, 5, 11]);
                        })
                        ->with('carerStatus');
                }])
                ->select('id', 'first_name', 'last_name', 'email', 'profile_image', 'shift_type')
                ->get();

           $carerCount = SubUser::whereHas('roles', function ($query) {
                $query->where('role_id', 4);
            })
            ->whereHas('scheduleCarers', function ($query) use ($startdate1, $end_date1) {
                $query->whereBetween('created_at', [$startdate1, $end_date1]);
            })
            ->count();

            $data = [
                'noshow' =>$noshows,
                'scheduleDriversmiss' =>$driver_without_schedule,
                'totalscheduleDriversmiss' =>$driver_without_schedulecount,
                'totalscheduleDrivers' =>$driver_with_schedule,
                'totalunshinedDriver' =>$unshinedDriverCount,
                'scheduleCaremiss' =>$scheduledUsers,
                'totalscheduleCaremiss' =>$scheduledUsers->count(),
                'totalscheduleCares' =>$carerCount,
                'totalunshinedCares' =>$unshinedUsers,
            ];

        return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }

    }


    /**
     * @OA\post(
     * path="/uc/api/usersComplances",
     * operationId="usersComplances",
     * tags={"Dashboard"},
     * summary="usersComplances",
     *   security={ {"Bearer": {} }},
     *     description="Account Setup",
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="startDate",
     *                     type="string",
     *                     format="date",
     *                     nullable=true,
     *                     example="2025-07"
     *                 ),
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *          response=201,
     *          description="Data listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Data listed successfully",
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

    public function usersComplances(Request $request){

        try {

        // Getting for current week
        $startOfWeek = Carbon::now()->startOfWeek(); // Monday
        $endOfWeek = Carbon::now()->endOfWeek();     // Sunday
        $weekData = [];

        $weektotal = 0;
        for ($date = $startOfWeek->copy(); $date->lte($endOfWeek); $date->addDay()) {
            $formattedDate = $date->toDateString();
            $dayName = $date->format('D');
            $complaints = ScheduleCarerComplaint::whereDate('date', $formattedDate);
            $total   = (clone $complaints)->count();
            $closed  = (clone $complaints)->where('status', 0)->count();
            $pending = (clone $complaints)->where('status', 1)->count();
            $reject  = (clone $complaints)->where('status', 2)->count();

            $weekData[$formattedDate] = [
                'data' => [
                    'total' => $total,
                    'closed' => $closed,
                    'pending' => $pending,
                    'reject' => $reject,
                    'day' => strtolower($dayName),
                ]
            ];

            $weektotal+= $total;
        }

        $this->data['week'] = $weekData;
        $this->data['totalWeek'] = $weektotal;


        // Month wise list 

        $month =  $request->input('startDate') ?: now()->format('Y-m');
        $startOfMonth = Carbon::parse($month . '-01')->startOfDay();

        $today = Carbon::now()->endOfDay();
        $requestedMonth = Carbon::parse($month . '-01');
        $isCurrentMonth = $requestedMonth->isSameMonth($today);
        $endOfMonth = $isCurrentMonth ? $today : $requestedMonth->copy()->endOfMonth()->endOfDay();

        $monthData = [];
        $monthtotal = 0;
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $formattedDate = $date->toDateString();
            $dayName = strtolower($date->format('D'));
            $total   = ScheduleCarerComplaint::whereDate('date', $formattedDate)->count();
            $closed  = ScheduleCarerComplaint::whereDate('date', $formattedDate)->where('status', 0)->count();
            $pending = ScheduleCarerComplaint::whereDate('date', $formattedDate)->where('status', 1)->count();
            $reject  = ScheduleCarerComplaint::whereDate('date', $formattedDate)->where('status', 2)->count();

            $monthData[$formattedDate] = [
                'data' => [
                    'total' => $total,
                    'closed' => $closed,
                    'pending' => $pending,
                    'reject' => $reject,
                    'day' => $dayName,
                ]
            ];
            $monthtotal+= $total;
        }
        $this->data['month'] = $monthData;
        $this->data['totalMonth'] = $monthtotal;

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


    /**
     * @OA\post(
     * path="/uc/api/billingSummarywithfilter",
     * operationId="billingSummarywithfilter",
     * tags={"Dashboard"},
     * summary="billingSummarywithfilter",
     *   security={ {"Bearer": {} }},
     *     description="billing Summary with filter",
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="startDate",
     *                     type="string",
     *                     format="date",
     *                     nullable=true,
     *                     example="2025-07"
     *                 ),
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *          response=201,
     *          description="Data listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Data listed successfully",
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


  //  billing summary with filter start 
    public function billingSummarywithfilter(Request $request){

        try {

        $startOfWeek = Carbon::now()->startOfWeek(); // Monday
        $endOfWeek = Carbon::now()->endOfWeek();     // Sunday
        $weekData = [];

        $weektotal = 0;
        for ($date = $startOfWeek->copy(); $date->lte($endOfWeek); $date->addDay()) {
            $formattedDate = $date->toDateString();
            $dayName = $date->format('D');
            $details = Invoice::with(['driver','schedule','pricebook.priceBookData'])->whereDate('date', $formattedDate)->get();
            $total   = Invoice::whereDate('date', $formattedDate)->sum('fare');
            $unpaid = Invoice::whereDate('date', $formattedDate)->where('status', 1)->sum('fare');
            $paid = Invoice::whereDate('date', $formattedDate)->where('status', 2)->sum('fare');

            $weekData[$formattedDate] = [
                'data' => [
                    'total' => $total,
                    'unpaid' => $unpaid,
                    'paid' => $paid,
                    'day' => strtolower($dayName),
                    'details' => $details
                ]
            ];
            $weektotal+= $total;
        }

        $this->data['week'] = $weekData;
        $this->data['totalWeek'] = $weektotal;

        // Month wise list 
        $month =  $request->input('startDate') ?: now()->format('Y-m');
        $startOfMonth = Carbon::parse($month . '-01')->startOfDay();

        $today = Carbon::now()->endOfDay();
        $requestedMonth = Carbon::parse($month . '-01');
        $isCurrentMonth = $requestedMonth->isSameMonth($today);
        $endOfMonth = $isCurrentMonth ? $today : $requestedMonth->copy()->endOfMonth()->endOfDay();

        $monthData = [];
        $monthtotal = 0;
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $formattedDate = $date->toDateString();
            $dayName = strtolower($date->format('D'));
            $details = Invoice::with(['driver','schedule','pricebook.priceBookData'])->whereDate('date', $formattedDate)->get();
            $total   = Invoice::whereDate('date', $formattedDate)->sum('fare');
            $unpaid = Invoice::whereDate('date', $formattedDate)->where('status', 1)->sum('fare');
            $paid = Invoice::whereDate('date', $formattedDate)->where('status', 2)->sum('fare');

            $monthData[$formattedDate] = [
                'data' => [
                    'total' => $total,
                    'unpaid' => $unpaid,
                    'paid' => $paid,
                    'day' => $dayName,
                    'details' => $details
                ]
            ];
            $monthtotal+= $total;
        }
        $this->data['month'] = $monthData;
        $this->data['totalMonth'] = $monthtotal;

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


    // ************** Start Map Analysis **************************

    /**
     * @OA\post(
     * path="/uc/api/mapAnalysis",
     * operationId="mapAnalysis",
     * tags={"Dashboard"},
     * summary="mapAnalysis",
     *   security={ {"Bearer": {} }},
     *     description="Map Analysis with filter",
     *      @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="startDate",
     *                     type="string",
     *                     format="date",
     *                     nullable=true,
     *                     example="2025-07-01"
     *                 ),
     *                 @OA\Property(
     *                     property="endDate",
     *                     type="string",
     *                     format="date",
     *                     nullable=true,
     *                     example="2025-07-07"
     *                 )
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *          response=201,
     *          description="Map Analysis successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Map Analysis successfully",
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


    public function mapAnalysis(Request $request){

        try {

            $startdate = $request->input('startDate') ?: date('Y-m-d');
            $end_date = $request->input('endDate') ?: date('Y-m-d');

            $schedules = Schedule::with([
                'carers:id,schedule_id,carer_id,shift_type',
                'carers.user:id,first_name,email'
            ])
            ->whereBetween('date', [$startdate, $end_date])
            ->select('id','date','driver_id','vehicle_id','start_time','end_time','end_date','locality','latitude','longitude',)
            ->get();


            // Group and format the data
            $formattedData = [];

            foreach ($schedules as $schedule) {
                $date = $schedule->date;
                $locality = $schedule->locality;
                if (!isset($formattedData[$date])) {
                    $formattedData[$date] = [
                        'total_today_cares' => 0,
                        'map' => []
                    ];
                }
                // Check if this locality already exists in the map array
                $existingIndex = null;
                foreach ($formattedData[$date]['map'] as $index => $entry) {
                    if ($entry['locality'] === $locality) {
                        $existingIndex = $index;
                        break;
                    }
                }

                $carerCount = count($schedule->carers);
                $formattedData[$date]['total_today_cares'] += $carerCount;

                if ($existingIndex !== null) {
                    // If locality already exists, update total_care
                    $formattedData[$date]['map'][$existingIndex]['total_care'] += $carerCount;
                } else {
                    // Otherwise, add new entry
                    $formattedData[$date]['map'][] = [
                        'date' => $date,
                        'locality' => $locality,
                        'latitude' => $schedule->latitude,
                        'longitude' => $schedule->longitude,
                        'shift_type' => $schedule->carers[0]->shift_type ?? null,
                        'total_care' => $carerCount
                    ];
                }
            }


            return response()->json([
                'success' => true,
                'data' =>$formattedData
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }

    }

    // ************** End Start Map Analysis **************************




    //********************* Api to get the dashboard leave requests ******/
    /**
     * @OA\Post(
     * path="/uc/api/dashboardLeave",
     * operationId="dashboardLeave",
     * tags={"Dashboard"},
     * summary="List leave requests",
     *   security={ {"Bearer": {} }},
     * description="List leave requests",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"status"},
     *               @OA\Property(property="status", type="text", description="0:submitted, 1:accepted, 2:rejected, all"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Leave request listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Leave request listed successfully",
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


    public function dashboardLeave(Request $request)
    {
        try {

            $request->validate([
                'status' => 'required|in:0,1,2,all'
            ]);
            $query = Leave::with(['reason', 'staff' => function ($query) {
                $query->select('id', 'first_name', 'unique_id','employement_type');
            }])->whereDate('start_date', '>=', now())
            ->orderByRaw('CASE WHEN status = 0 THEN 0 ELSE 1 END')
            ->orderBy('start_date', 'desc');

            // Check if status filter is present in the request
            if ($request->status != 'all') {
                if ($request->has('status')) {
                    $status = $request->status;
                    $query->where('status', $status);
                }
            }

            $this->data['leaveRequests'] = $query->get();

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

    //********************* Api to get the dashboard temp location requests ******/

    /**
     * @OA\Post(
     * path="/uc/api/dashboardTempLocation",
     * operationId="dashboardTempLocation",
     * tags={"Dashboard"},
     * summary="Temp Location requests",
     *   security={ {"Bearer": {} }},
     * description="Temp Location requests",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"status"},
     *               @OA\Property(property="status", type="text", description="0:submitted, 1:accepted, 2:rejected, all"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Temp location requests listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Temp location requests listed successfully",
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


    public function dashboardTempLocation(Request $request)
    {
        try {
            $request->validate([
                'status' => 'required|in:0,1,2,all'
            ]);
            $query = ScheduleCarerRelocation::with(['reason', 'user' => function ($query) {
                $query->select('id', 'first_name', 'unique_id','employement_type');
            }])->orderBy('date', 'desc');

            // Check if status filter is present in the request
            if ($request->status != 'all') {
                if ($request->has('status')) {
                    $status = $request->status;
                    $query->where('status', $status);
                }
            }

            $this->data['tempRequests'] = $query->get();

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

    //********************* Api to get the dashboard reschedule location requests ******/

    /**
     * @OA\Post(
     * path="/uc/api/dashboardReschedule",
     * operationId="ddashboardReschedule",
     * tags={"Dashboard"},
     * summary="Reschedule requests",
     *   security={ {"Bearer": {} }},
     * description="Reschedule requests",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"status"},
     *               @OA\Property(property="status", type="text", description="0:submitted, 1:accepted, 2:rejected, all"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Reschedule requests listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Reschedule requests listed successfully",
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

    public function dashboardReschedule(Request $request)
    {
        try {
            $request->validate([
                'status' => 'required|in:0,1,2,all'
            ]);
            $query = Reschedule::with(['reason', 'user' => function ($query) {
                $query->select('id', 'first_name', 'unique_id','employement_type');
            }])->orderBy('date', 'desc');

            // Check if status filter is present in the request
            if ($request->status != 'all') {
                if ($request->has('status')) {
                    $status = $request->status;
                    $query->where('status', $status);
                }
            }

            $this->data['reschedule'] = $query->get();

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

    /**
     * @OA\Post(
     * path="/uc/api/dashBoardComplaints",
     * operationId="dashBoardComplaints",
     * tags={"Dashboard"},
     * summary="List Complaints",
     *   security={ {"Bearer": {} }},
     * description="List Complaints",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"status"},
     *               @OA\Property(property="status", type="text", description="0:Closed, 1:Opened,  all"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Complaints listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Complaints listed successfully",
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


    public function dashBoardComplaints(Request $request)
    {

        try {

            $request->validate([
                'status' => 'required|in:0,1,all'
            ]);
            $query = ScheduleCarerComplaint::with(['employee:id,first_name,unique_id', 'driver:id,first_name', 'reason'])->orderBy('date', 'desc');

            if ($request->status != 'all') {
                if ($request->has('status')) {
                    $status = $request->status;
                    $query->where('status', $status);
                }
            }

            $this->data['complaints'] = $query->get();

            return response()->json([
                'success' => true,
                'data' => $this->data,
                'file_path' => url('public/files/complaints/'),
                'message' => 'Complaints listed successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, "message" => $th->getMessage()], 500);
        }
    }

    //***************************** Accept leave requests api***************************/

    /**
     * @OA\Post(
     * path="/uc/api/acceptLeaveRequest",
     * operationId="acceptLeaveRequest",
     * tags={"Dashboard"},
     * summary="Accept leave",
     *   security={ {"Bearer": {} }},
     * description="Accept leave",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Leave accepted successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Leave accepted successfully",
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
    public function acceptLeaveRequest(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|exists:leaves,id',
            ]);
            $leaveRequest = Leave::find($request->id);

            $auth_user = auth('sanctum')->user();
            $user_name = $auth_user->first_name ."". $auth_user->last_name;

            $user_ids = [$leaveRequest->staff_id];
            $startDate = Carbon::parse($leaveRequest->start_date);
            if ($startDate->lt(Carbon::today())) {
                return response()->json(['success' => false, 'message' => 'Leave request cannot be accepted for past dates'], 422);
            }

            $endDate = $leaveRequest->end_date ? Carbon::parse($leaveRequest->end_date) : NULL;

            // Convert to Carbon instance
            $dates = [];

            while ($startDate->lte($endDate)) {
                array_push($dates, $startDate->toDateString());
                $startDate->addDay();
            }

            if (!$leaveRequest->end_date) {
                array_push($dates, $leaveRequest->start_date);
            }

            $schedules = $this->getWeeklyScheduleInfo($user_ids, $dates, 2, 'all');
            $schedulesCount = count($schedules) - 1;

            foreach ($schedules as $key => $schedule) {
                $missingDate = false;
                if ($leaveRequest->type == 1) {
                    // If leave type is 1, set type as "pick" and "drop"

                    if ($key == 0 && $schedule['shift_finishes_next_day'] == 1) {
                        $type = "drop";
                        $type2 = null;
                    } else {
                        if ($key != 0 && $schedulesCount != $key) {

                            $currentDate = new DateTime($schedule['date']);
                            $nextDate = new DateTime($schedules[$key + 1]['date']);

                            // Find the difference in days between the current date and the next date
                            $dateDiff = $currentDate->diff($nextDate)->days;


                            // If the difference is greater than 1, there are skipped dates
                            if ($dateDiff > 1) {
                                // Loop through the missing days and find the skipped dates
                                // for ($i = 1; $i < $dateDiff; $i++) {
                                $missingDate = $currentDate->modify('+1 day')->format('Y-m-d');
                                // }
                            }
                        }
                        $type = "pick";
                        $type2 = "drop";
                    }
                } elseif ($leaveRequest->type == 3) {
                    // If leave type is 3, set type as "drop"
                    $type = "drop";
                    $type2 = null; // Set type2 to null since it's not relevant
                } else if ($leaveRequest->type == 2) {
                    $type = "pick";
                    $type2 = null;
                }
                // Find schedule carer for pick
                $schedule_carer = ScheduleCarer::where('schedule_id', $schedule['id'])
                ->where('carer_id', $leaveRequest->staff_id)
                    ->where('shift_type', $type)
                    ->first();

                // Find schedule carer for drop if needed
                if ($type2) {
                    $schedule_carer2 = ScheduleCarer::where('schedule_id', $schedule['id'])
                    ->where('carer_id', $leaveRequest->staff_id)
                        ->where('shift_type', $type2)
                        ->first();
                }

                // Check if schedule carer exists for pick
                if (!$schedule_carer) {
                    return response()->json(['success' => false, "message" => "Staff does not exist in this ride"], 500);
                }

                // Cancel pick ride
                $schedule_carer_status = ScheduleCarerStatus::where([
                    'schedule_carer_id' => $schedule_carer->id,
                    'date' => $schedule['date']
                ])->first();

                if (!$schedule_carer_status) {
                    $schedule_carer_status = new ScheduleCarerStatus();
                }
                $schedule_carer_status->schedule_carer_id = $schedule_carer->id;
                $schedule_carer_status->date = $schedule['date'];
                $schedule_carer_status->status_id = 11;
                $schedule_carer_status->cancel_message = 'On_Leave';
                $schedule_carer_status->save();

                if ($missingDate != false) {
                    $schedule_carer_status2 = ScheduleCarerStatus::where([
                        'schedule_carer_id' => $schedule_carer2->id,
                        'date' => $schedule['date']
                    ])->first();

                    if (!$schedule_carer_status2) {
                        $schedule_carer_status2 = new ScheduleCarerStatus();
                    }
                    $schedule_carer_status2->schedule_carer_id = $schedule_carer2->id;
                    $schedule_carer_status2->date = $missingDate;
                    $schedule_carer_status2->status_id = 11;
                    $schedule_carer_status2->cancel_message = 'On_Leave';
                    $schedule_carer_status2->save();
                }

                // Check if schedule carer exists for drop and cancel if needed
                if ($type2 && $schedule_carer2) {
                    $schedule_carer_status2 = ScheduleCarerStatus::where([
                        'schedule_carer_id' => $schedule_carer2->id,
                        'date' => $schedule['date']
                    ])->first();

                    if (!$schedule_carer_status2) {
                        $schedule_carer_status2 = new ScheduleCarerStatus();
                    }
                    $schedule_carer_status2->schedule_carer_id = $schedule_carer2->id;
                    $schedule_carer_status2->date = $schedule['date'];
                    $schedule_carer_status2->status_id = 11;
                    $schedule_carer_status2->cancel_message = 'On_Leave';
                    $schedule_carer_status2->save();
                }
            }

            $subUser = SubUser::where('id', $leaveRequest->staff_id)->first();
            $token = @$subUser->fcm_id;
            $title = "Leave Approved";
            $body = "Your leave request has been approved.";
            @$this->notification->sendPushNotification($token, $title, $body);
            $leaveRequest->status = 1;
            $leaveRequest->save();

            // Send approval email
            if ($subUser && $subUser->email) {
                $emailData = [
                    'name' => $subUser->name,
                    'start_date' => $leaveRequest->start_date,
                    'end_date' => $leaveRequest->end_date ?? $leaveRequest->start_date,
                    'admin' => $user_name
                ];

                Mail::to($subUser->email)->send(new LeaveApprovalEmail($emailData, $user_name));
            }
            return response()->json(['success' => true,  "message" => "Leave approved successfully"], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    /**
     * @OA\Post(
     * path="/uc/api/rejectLeaveRequest",
     * operationId="rejectLeaveRequest",
     * tags={"Dashboard"},
     * summary="Reject leave",
     *   security={ {"Bearer": {} }},
     * description="Reject leave",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Leave accepted successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Leave accepted successfully",
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
    public function rejectLeaveRequest(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|exists:leaves,id',
            ]);
            $leaveRequest = Leave::find($request->id);
            $subUser = SubUser::where('id', $leaveRequest->staff_id)->first();
            $token = @$subUser->fcm_id;
            $title = "Leave rejected";
            $body = "Your leave request has been rejected.";
            @$this->notification->sendPushNotification($token, $title, $body);
            $leaveRequest->status = 2;
            $leaveRequest->save();
            return response()->json(['success' => true,  "message" => "Leave rejected successfully"], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //************************* Api to accept temp location change *****************/


    /**
     * @OA\Post(
     * path="/uc/api/acceptTempRequest",
     * operationId="acceptTempRequest",
     * tags={"Dashboard"},
     * summary="Accept temp location change",
     *   security={ {"Bearer": {} }},
     * description="Accept temp location change",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Temp location change request accepted successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Temp location change request accepted successfully",
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
    public function acceptTempRequest(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|exists:schedule_carer_relocations,id',
            ]);
            $shiftChange = ScheduleCarerRelocation::find($request->id);
            $shiftTypesToUpdate = [];

            if ($shiftChange->shift_type == 1) {
                $shiftTypesToUpdate = ['pick', 'drop'];
            } elseif ($shiftChange->shift_type == 2) {
                $shiftTypesToUpdate = ['pick'];
            } elseif ($shiftChange->shift_type == 3) {
                $shiftTypesToUpdate = ['drop'];
            }
            $requestDate = Carbon::parse($shiftChange->date);
            if ($requestDate->isPast()) {
                return response()->json(['success' => false, 'message' => 'Shift relocation request cannot be accepted for past dates'], 422);
            }
            $dates = $shiftChange->date;
            $schedules = $this->getWeeklyScheduleInfo([$shiftChange->staff_id], [$dates], 2, 'all');
            //dd($schedules);
            foreach ($schedules as $schedule) {

                // Update temporary info for appropriate shift types
                if (in_array($schedule['type'], $shiftTypesToUpdate)) {

                    $this->updateScheduleTempInfo($schedule, $shiftChange, $shiftTypesToUpdate);
                }
            }
            $subUser = SubUser::where('id', $shiftChange->staff_id)->first();
            $token = @$subUser->fcm_id;
            $title = "Temp location request approved";
            $body = "Your temp location change request has been approved.";
            @$this->notification->sendPushNotification($token, $title, $body);
            $shiftChange->status = 1;
            $shiftChange->save();
            // Add SubUserAddresse for this Temp location acceptance
            SubUserAddresse::create([
                'sub_user_id' => $shiftChange->staff_id,
                'start_date' => Carbon::parse($shiftChange->date)->format('Y-m-d'),
                'end_date' => Carbon::parse($shiftChange->date)->format('Y-m-d'),
                'address' => $shiftChange->temp_address,
                'latitude' => $shiftChange->temp_latitude,
                'longitude' => $shiftChange->temp_longitude,
                'schedule_carer_relocations_id' => $shiftChange->id,
            ]);
            return response()->json(['success' => true,  "message" => "Temp location change accepted successfully"], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function updateScheduleTempInfo($schedule, $shiftChange, $shiftTypesToUpdate)
    {

        $schedule_carers = ScheduleCarer::where('schedule_id', $schedule['id'])
            ->where('carer_id', $shiftChange->staff_id)->whereIn('shift_type', $shiftTypesToUpdate)
            ->get();

        foreach ($schedule_carers as $schedule_carer) {
            $schedule_carer->temp_date = $shiftChange->date;
            $schedule_carer->temp_lat = $shiftChange->temp_latitude;
            $schedule_carer->temp_long = $shiftChange->temp_longitude;
            $schedule_carer->temp_address = $shiftChange->temp_address;
            $schedule_carer->save();
        }
    }

    /**
     * @OA\Post(
     * path="/uc/api/rejectTempRequest",
     * operationId="rejectTempRequest",
     * tags={"Dashboard"},
     * summary="Reject temp location request",
     *   security={ {"Bearer": {} }},
     * description="Reject temp location request",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Temp location rejected successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Temp location rejected successfully",
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
    public function rejectTempRequest(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|exists:schedule_carer_relocations,id',
            ]);
            $shiftChange = ScheduleCarerRelocation::find($request->id);
            $subUser = SubUser::where('id', $shiftChange->staff_id)->first();
            $token = @$subUser->fcm_id;
            $title = "Temp location request rejected";
            $body = "Your temp location change request has been rejected.";
            @$this->notification->sendPushNotification($token, $title, $body);
            $shiftChange->status = 2;
            $shiftChange->save();
            return response()->json(['success' => true,  "message" => "Temp location change rejected successfully"], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    //********************* Reject reschedule request *******************/

    /**
     * @OA\Post(
     * path="/uc/api/rejectReschedule",
     * operationId="rejectReschedule",
     * tags={"Dashboard"},
     * summary="Reject reschedule",
     *   security={ {"Bearer": {} }},
     * description="Reject reschedule",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Reschedule request rejected successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Reschedule request rejected successfully",
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
    public function rejectReschedule(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|exists:reschedules,id'
            ]);
            $reschedule = Reschedule::find($request->id);
            $subUser = SubUser::where('id', $reschedule->user_id)->first();
            $token = @$subUser->fcm_id;
            $title = "Reschedule request rejected";
            $body = "Your reschedule request has been rejected.";
            @$this->notification->sendPushNotification($token, $title, $body);
            $reschedule->status = 2;
            $reschedule->save();
            return response()->json(['success' => true,  "message" => "Reschedule rejected successfully"], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/uc/api/similarRoutes",
     * operationId="similarRoutes",
     * tags={"Dashboard"},
     * summary="Similar Routes",
     *   security={ {"Bearer": {} }},
     * description="Similar Routes",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Similar routes found successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Similar routes found successfully",
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

    public function similarRoutes(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:reschedules,id'
        ]);
        $this->data1 = [];
        $reschedule = Reschedule::find($request->id);
        $rescheduleLatitude = $reschedule->latitude;
        $rescheduleLongitude = $reschedule->longitude;
        $rescheduleCity = $this->findCityFromLatLng($rescheduleLatitude, $rescheduleLongitude);
        $rescheduleDate = $reschedule->date;
        $date = date('Y-m-d');
        $schedules = Schedule::where(function ($query) {
            $query->where(function ($query) {
                $query->where('is_repeat', 1);
                $query->where('end_date', '>', now());
            });
        })->where('city', $rescheduleCity)
            ->with(['carers' => function ($q) use ($rescheduleDate) {
                $q->with(['user' => function ($u) use ($rescheduleDate) {
                    $u->leftJoin('sub_user_addresses', function ($join) use ($rescheduleDate) {
                        $join->on('sub_users.id', '=', 'sub_user_addresses.sub_user_id')
                            ->whereDate('sub_user_addresses.start_date', '<=', $rescheduleDate)
                            ->where(function ($query) use ($rescheduleDate) {
                                $query->whereDate('sub_user_addresses.end_date', '>', $rescheduleDate)
                                    ->orWhereNull('sub_user_addresses.end_date');
                            });
                    })
                        ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address');
                }]);
            }])->get();
        $radius = 5;
       $filteredSchedules = collect([]);
        $pickSchedules = collect([]);
        $pickAndDropSchedules = collect([]);
        $dropSchedules = collect([]);

        foreach ($schedules as $schedule) {
            $scheduleStartDate = $schedule->date;
    $scheduleEndDate = $schedule->end_date;

    // Check if the reschedule date falls within the schedule's date range
    if (($scheduleStartDate <=$rescheduleDate  &&  $scheduleEndDate > $rescheduleDate ) ||($scheduleStartDate >=$rescheduleDate) ) {
            $scheduleLatitude = $schedule->latitude;
            $scheduleLongitude = $schedule->longitude;
            $this->isLocationWithinRadius($scheduleLatitude, $scheduleLongitude, $rescheduleLatitude, $rescheduleLongitude, $radius, function ($isWithinRadius) use ($schedule, $filteredSchedules,$pickSchedules, $pickAndDropSchedules,$dropSchedules, ) {
                if ($isWithinRadius) {
                    $vehicle = Vehicle::find($schedule->vehicle_id);
                    $totalSeats = $vehicle->seats;
                    if ($schedule->shift_type_id == 1 || $schedule->shift_type_id == 3) {
                        $assignedCarers = count($schedule->carers);
                    }
                    if ($schedule->shift_type_id == 2) {
                        $pickCarers = $schedule->carers->where('shift_type', 'pick')->count();
                        $dropCarers = $schedule->carers->where('shift_type', 'drop')->count();
                        $assignedCarers = max($pickCarers, $dropCarers);
                    }
                    $vacantSeats = $totalSeats - $assignedCarers;
                    $schedule->vacant_seats = $vacantSeats;
                    $shiftType = ShiftTypes::find($schedule->shift_type_id);
                    $schedule->shift_type_label = $shiftType ? $shiftType->name : 'Unknown';

                   $driver = Subuser::where('id', $schedule->driver_id)->first();
                  $schedule->driver = $driver;
                    // switch ($schedule->shift_type_id) {
                    //     case 1:
                    //         $schedule->shift_type_label = 'Pick';
                    //         $pickSchedules->push($schedule); // Add to pickSchedules
                    //         break;
                    //     case 2:
                    //         $schedule->shift_type_label = 'Pick and Drop';
                    //         $pickAndDropSchedules->push($schedule); // Add to pickAndDropSchedules
                    //         break;
                    //     case 3:
                    //         $schedule->shift_type_label = 'Drop';
                    //         $dropSchedules->push($schedule); // Add to dropSchedules
                    //         break;
                    //     default:
                    //         $schedule->shift_type_label = 'Unknown';
                    //         break;
                    // }
                    $filteredSchedules->push($schedule);
                }
            });
        }
        }


    //    $similarSchedules = [
    //        'pick' => $pickSchedules,
    //        'pick_and_drop' => $pickAndDropSchedules,
    //         'drop' => $dropSchedules,

    //    ];

    $old_schedules = $this->staffSchedules([$reschedule->user_id], [$reschedule->date], 2, 'all');
    $oldScheduleIds = $old_schedules ? $old_schedules->pluck('id')->toArray() : [];

    // Filter schedules to exclude old ones
    $filteredSchedules = $filteredSchedules->filter(function ($schedule) use ($oldScheduleIds) {
        return !in_array($schedule->id, $oldScheduleIds);
    });
       $groupedSchedules = $filteredSchedules->groupBy('driver_id')->map(function ($schedules, $driverId) {
        $driver = Subuser::find($driverId);

        // Group schedules by shift type
        $shiftTypeGroups = $schedules->groupBy(function ($schedule) {
            switch ($schedule->shift_type_id) {
                case 1:
                    return 'pick';
                case 2:
                    return 'pick_and_drop';
                case 3:
                    return 'drop';
                default:
                    return 'unknown';
            }
        });


        $schedulesWithDriver = $schedules->map(function ($schedule) use ($driver) {
            $schedule->driver = $driver; // Ensure driver is attached to each schedule
            return $schedule;
        });


        return [
            'driver' => array_merge(
                $driver->toArray(),
                [
                    'schedules' =>$schedulesWithDriver
                    // 'schedules' => [
                    //     'pick' => $shiftTypeGroups->get('pick', collect())->values(),
                    //     'pick_and_drop' => $shiftTypeGroups->get('pick_and_drop', collect())->values(),
                    //     'drop' => $shiftTypeGroups->get('drop', collect())->values(),
                    //     'unknown' => $shiftTypeGroups->get('unknown', collect())->values(),
                    // ]
                ]
            ),
        ];

    });


      //  $old_schedules = @$this->staffSchedules([$reschedule->user_id], [$reschedule->date], 2, 'all');
        $company_details = CompanyAddresse::where('company_id', 1)->whereNull('end_date')->first();
        $this->data1['old_schedules'] = @$old_schedules;
        $this->data1['similar_schedules'] = @$groupedSchedules->values();
      //  $this->data1['similar_schedules'] = @$filteredSchedules;
        $this->data1['company_details'] = @$company_details;
        $this->data1['reschedule'] = @$reschedule;
          // $this->data1 = [
        //     'old_schedules' => $old_schedules,
        //     'similar_schedules' => [
        //         'by_driver' => $groupedSchedules->values(),
        //     ],
        //     'company_details' => $company_details,
        //     'reschedule' => $reschedule,
        // ];
        return response()->json([
            'success' => true,
            'data' => @$this->data1,
            'message' => "Similar routes found successfully."

        ], 200);
    }
    public function staffSchedules($user_ids, $dates, $clientStaff, $shift_type_id)
    {
        $schedule_id_arr = array();

        $previous_date = Carbon::createFromFormat('Y-m-d', min($dates))->subDay()->format('Y-m-d');

        $schedules = Schedule::where(function ($query) use ($dates, $previous_date) {
            $query->where(function ($query) use ($dates) {
                $query->whereIn('date', $dates);

                $query->exists();
            });
            $query->orwhere(function ($query) {
                $query->where('is_repeat', 1);
                $query->where('end_date', '>', now());
            });
            $query->orwhere(function ($query) use ($dates) {
                $query->where('is_repeat', 1);
                $query->where('end_date', '>', min($dates));
                $query->where('end_date', '<', max($dates));
            });
            $query->orwhere(function ($query) use ($previous_date) {
                $query->where('date', $previous_date);
                $query->where('shift_finishes_next_day', 1);
            });
        });

        if ($clientStaff) {
            if ($clientStaff == 2) {
                $schedules = $schedules->whereHas('carers', function ($q) use ($user_ids) {

                    $q->whereIn('carer_id', $user_ids);
                });
                $schedules = $schedules->whereNotNull('driver_id');
                //$leaves = Leave::where('status', 'Approved')->whereIn('date', $dates)->whereIn('staff_id', $user_ids)->pluck('date', 'staff_id');
            } else {
                $schedules = $schedules->whereIn('driver_id', $user_ids);
            }
        }


        $schedules = $schedules->with('shiftType')

            ->with('driver');

        if ($shift_type_id) {
            if ($shift_type_id != 'all') {
                $schedules = $schedules->where('shift_type_id', $shift_type_id);
            }
        }

        $holidays = Holiday::whereIn('date', $dates)->pluck('date');

        $schedules = $schedules->get();
        return $schedules;
    }

    public function isLocationWithinRadius($carerLatitude, $carerLongitude, $scheduleLocationLatitude, $scheduleLocationLongitude, $radius, $callback)
    {
        $carerLatLng = $carerLatitude . ',' . $carerLongitude;
        $scheduleLatLng = $scheduleLocationLatitude . ',' . $scheduleLocationLongitude;

        $apiKey = env('GOOGLE_API_KEY'); // Replace with your actual API key
        $baseUrl = 'https://maps.googleapis.com/maps/api/distancematrix/json';

        $client = new Client(['verify' => false]);

        try {
            $response = $client->get($baseUrl, [
                'query' => [
                    'origins' => $carerLatLng,
                    'destinations' => $scheduleLatLng,
                    'mode' => 'driving',
                    'units' => 'metric',
                    'key' => $apiKey,
                ],
            ]);
            $data = json_decode($response->getBody(), true);
            if ($data['status'] === 'OK') {
                $distanceInKm = $data['rows'][0]['elements'][0]['distance']['value'] / 1000;
                $isWithinRadius = $distanceInKm <= $radius;
                $callback($isWithinRadius);
            } else {

                $callback(false);
            }
        } catch (\Exception $e) {

            $callback(false);
        }
    }
    public function findCityFromLatLng($latitude, $longitude)
    {

        $latLng = $latitude . ',' . $longitude;
        $apiKey = env('GOOGLE_API_KEY');
        $baseUrl = 'https://maps.googleapis.com/maps/api/geocode/json';
        $client = new Client(['verify' => false]);
        try {
            $response = $client->get($baseUrl, [
                'query' => [
                    'latlng' => $latLng,
                    'key' => $apiKey,
                ],
            ]);
            $data = json_decode($response->getBody(), true);

            if ($data['status'] === 'OK') {
                $city = null;
                foreach ($data['results'][0]['address_components'] as $component) {
                    if (in_array('locality', $component['types'])) {

                        $city = $component['long_name'];
                        break;
                    }
                }

                return $city;
            } else {
                return null;
            }
        } catch (\Exception $e) {

            return null;
        }
    }


    //*********************** Accept Rescheduling api *******************/

    /**
     * @OA\Post(
     * path="/uc/api/acceptReschedule",
     * operationId="acceptReschedule",
     * tags={"Dashboard"},
     * summary="Accept reschedule request",
     *   security={ {"Bearer": {} }},
     * description="Accept reschedule request",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"reschedule_id", "similar_route_id"},
     *               @OA\Property(property="reschedule_id", type="text"),
     *               @OA\Property(property="similar_route_id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Reschedule request accepted successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Reschedule request accepted successfully",
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

    public function acceptReschedule(Request $request)    {
        try {
            DB::beginTransaction();
            $request->validate([
                'reschedule_id' => 'required|exists:reschedules,id',
                'similar_route_id' => 'required|exists:schedules,id'
            ]);


            $reschedule = Reschedule::find($request->reschedule_id);
            if (strtotime($reschedule->date) <= strtotime('today')) {
                return response()->json(['success' => false, "message" => "Reschedule date must be in the future"], 400);
            }

            // $reschedule_exists = Reschedule::where('date', '>=', date('Y-m-d'))->where('user_id', $reschedule->user_id)->where('status', 1)->first();
            // if ($reschedule_exists) {
            //     return response()->json(['success' => false,  "message" => "Reschedule request is already accepted"], 400);
            // }

            $old_schedules = $this->staffSchedules([$reschedule->user_id], [$reschedule->date], 2, 'all');
           // $new_schedule = Schedule::find($request->similar_route_id);
          foreach ($request->similar_route_id as $route_id) {
           $new_schedule = Schedule::find($route_id);

            if (count($old_schedules) === 1 && $old_schedules[0]['id'] === $new_schedule->id) {
                $sub_user = SubUser::find($reschedule->user_id);

                if ($sub_user) {
                    $sub_user_address = SubUserAddresse::where('sub_user_id', $sub_user->id)->whereNull('end_date')->first();

                    if ($sub_user_address) {
                        if ($sub_user_address->start_date == $reschedule->date) {

                            $sub_user_address->sub_user_id = $sub_user->id;
                            $sub_user_address->address = $reschedule->address;
                            $sub_user_address->latitude = $reschedule->latitude;
                            $sub_user_address->longitude = $reschedule->longitude;
                        } else {
                            $sub_user_address->end_date = $reschedule->date;
                            $sub_new_address = new SubUserAddresse();

                            $sub_new_address->sub_user_id = $sub_user->id;
                            $sub_new_address->address = $reschedule->address;
                            $sub_new_address->latitude = $reschedule->latitude;
                            $sub_new_address->longitude = $reschedule->longitude;
                            $sub_new_address->start_date = $reschedule->date;
                            $sub_new_address->save();
                        }
                        $sub_user_address->update();
                    } else {

                        $sub_new_address = new SubUserAddresse();

                        $sub_new_address->sub_user_id = $sub_user->id;
                        $sub_new_address->address = $reschedule->address;
                        $sub_new_address->latitude = $reschedule->latitude;
                        $sub_new_address->longitude = $reschedule->longitude;
                        $sub_new_address->start_date = $reschedule->date;

                        $sub_new_address->save();
                    }
                }
                $reschedule->status = 1;
                $reschedule->save();
            } else {


                foreach ($old_schedules as $old_schedule) {


                    $old_schedule = Schedule::find($old_schedule['id']);
                    // if (strtotime($old_schedule->date) > strtotime($reschedule->date)) {
                    //     $old_schedule->carers()->syncWithoutDetaching([$reschedule->user_id => ['shift_type' => 'updated']]);
                    //     continue;
                    // }
                   // info($old_schedule->date);

                    $carerCount = $old_schedule->carers()->count();
                    $isReschedulingCarer = $old_schedule->carers()->where('carer_id', $reschedule->user_id)->exists();

                    // if ($carerCount !=0 && $isReschedulingCarer && $old_schedule['is_repeat'] == 1) {

                    //     $old_schedule->end_date = date('Y-m-d', strtotime($reschedule->date . ' -1 day'));
                    //     info('--------------------test150' . $old_schedule->end_date);
                    //     $old_schedule->save();


                    // }

                        if ($carerCount != 0 && $isReschedulingCarer && $old_schedule['is_repeat'] == 1) {
                            $newEndDate = date('Y-m-d', strtotime($reschedule->date . ' -1 day'));

                            // Check if the current end_date is already less than the calculated new end date
                            if (strtotime($old_schedule->end_date) < strtotime($newEndDate)) {
                                // Do not update the end_date if it's already earlier
                                continue;
                            }

                            $old_schedule->end_date = $newEndDate;
                            info('--------------------test150 ' . $old_schedule->end_date);
                            $old_schedule->save();
                        }

                    else {
                        if ($old_schedule && $old_schedule['is_repeat'] == 1) {
                            $old_new_schedule = $old_schedule->replicate();
                            info('--------------------test122343');
                         //   $old_new_schedule->schedule_parent_id = $old_schedule->id;
                         $old_new_schedule->schedule_parent_id = $old_schedule->schedule_parent_id
                         ? $old_schedule->schedule_parent_id
                         : $old_schedule->id;
                            $old_new_schedule->date = $reschedule->date;
                            $old_new_schedule->save();
                           // $old_schedule->end_date = date('Y-m-d', strtotime($reschedule->date . ' -1 day'));
                            info('--------------------test321' . $old_schedule->end_date);
                           // $old_schedule->save();
                           $newEndDate = date('Y-m-d', strtotime($reschedule->date . ' -1 day'));

                        // Check if the current end_date is already less than the calculated new end date
                        if (strtotime($old_schedule['end_date']) < strtotime($newEndDate)) {
                           continue;
                        }
                            $old_schedule['end_date'] = $newEndDate;
                            info('Updated end date: ' . $new_schedule['end_date']);
                            $old_schedule->save();

                           // $old_schedule['end_date'] = date('Y-m-d', strtotime($reschedule->date . ' - 1 days'));
                          //  $old_schedule->save();
                        }



                        if ($old_new_schedule['shift_type_id'] == 2 || $old_new_schedule['shift_type_id'] == 1) {
                            $pickcarers = ScheduleCarer::where('schedule_id', $old_schedule->id)
                                ->whereNotIn('carer_id', [$reschedule->user_id])
                                ->where('shift_type', 'pick')
                                ->get();
                            foreach ($pickcarers as $pickcarer) {
                                $new_carer = new ScheduleCarer();
                                $new_carer->schedule_id = $old_new_schedule['id'];
                                $new_carer->carer_id = $pickcarer['carer_id'];
                                $new_carer->shift_type = $pickcarer['shift_type'];
                                $new_carer->save();
                            }
                        }
                        if ($old_schedule->shift_type_id == 2 || $old_schedule->shift_type_id == 3) {
                            $dropCarers = ScheduleCarer::where('schedule_id', $old_schedule->id)
                                ->whereNotIn('carer_id', [$reschedule->user_id])
                                ->where('shift_type', 'drop')
                                ->get();
                            foreach ($dropCarers as $dropCarer) {
                                $new_carer = new ScheduleCarer();
                                $new_carer->schedule_id = $old_new_schedule['id'];
                                $new_carer->carer_id = $dropCarer['carer_id'];
                                $new_carer->shift_type = $dropCarer['shift_type'];
                                $new_carer->save();
                            }
                        }
                    }
                }
info($new_schedule);
                if ($new_schedule && $new_schedule['is_repeat'] == 1) {
                    if (strtotime($new_schedule->date) > strtotime($reschedule->date)) {
                        info('New schedule date is greater than the reschedule date. Skipping creation of a new schedule.');
                        $carers = ScheduleCarer::where('schedule_id', $new_schedule->id)->get();
                        foreach ($carers as $carer) {
                            $new_carer = new ScheduleCarer();
                            $new_carer->schedule_id = $new_schedule->id; // Use the existing schedule ID
                            $new_carer->carer_id = $carer['carer_id'];
                            $new_carer->shift_type = $carer['shift_type'];
                            $new_carer->save();
                        }

                        // Add the rescheduled carer if applicable
                        if ($new_schedule->shift_type_id == 2 || $new_schedule->shift_type_id == 1) {
                            $new_carer = new ScheduleCarer();
                            $new_carer->schedule_id = $new_schedule->id;
                            $new_carer->carer_id = $reschedule['user_id'];
                            $new_carer->shift_type = 'pick';
                            $new_carer->save();
                        }

                        if ($new_schedule->shift_type_id == 2 || $new_schedule->shift_type_id == 3) {
                            $new_carer = new ScheduleCarer();
                            $new_carer->schedule_id = $new_schedule->id;
                            $new_carer->carer_id = $reschedule['user_id'];
                            $new_carer->shift_type = 'drop';
                            $new_carer->save();
                        }
                       // return;
                    }
                    else{
                    $new_new_schedule = $new_schedule->replicate();
                    info('------------------------test345');
                   // $new_new_schedule->schedule_parent_id = $new_schedule->id;
                   $new_new_schedule->schedule_parent_id = $new_schedule->schedule_parent_id
                   ? $new_schedule->schedule_parent_id
                   : $new_schedule->id;
                    $new_new_schedule->date = $reschedule->date;
                    $new_new_schedule->save();
                   // $new_schedule['end_date'] = date('Y-m-d', strtotime($reschedule->date . ' - 1 days'));
                   $newEndDate = date('Y-m-d', strtotime($reschedule->date . ' -1 day'));

                    // Check if the current end_date is already less than the calculated new end date
                    if (strtotime($new_schedule['end_date']) < strtotime($newEndDate)) {
                       continue;
                    }
                        $new_schedule['end_date'] = $newEndDate;

                        $new_schedule->save();

                   // $new_schedule->save();
                  $carers = ScheduleCarer::where('schedule_id', $new_schedule->id)->get();
                  foreach ($carers as $carer) {
                      $new_carer = new ScheduleCarer();
                      $new_carer->schedule_id = $new_new_schedule['id'];
                      $new_carer->carer_id = $carer['carer_id'];
                      $new_carer->shift_type = $carer['shift_type'];
                      $new_carer->save();
                  }
                  if ($new_new_schedule['shift_type_id'] == 2 || $new_new_schedule['shift_type_id'] == 1) {

                      $new_carer = new ScheduleCarer();
                      $new_carer->schedule_id = $new_new_schedule['id'];
                      $new_carer->carer_id = $reschedule['user_id'];
                      $new_carer->shift_type = 'pick';

                      $new_carer->save();
                  }


                  if ($new_new_schedule->shift_type_id == 2 || $new_new_schedule->shift_type_id == 3) {
                      $new_carer = new ScheduleCarer();
                      $new_carer->schedule_id = $new_new_schedule['id'];
                      $new_carer->carer_id = $reschedule['user_id'];
                      $new_carer->shift_type = 'drop';

                      $new_carer->save();
                  }

                    }

            }

                $sub_user = SubUser::find($reschedule->user_id);

                if ($sub_user) {
                    $sub_user_address = SubUserAddresse::where('sub_user_id', $sub_user->id)->whereNull('end_date')->first();

                    if ($sub_user_address) {
                        if ($sub_user_address->start_date == $reschedule->date) {

                            $sub_user_address->sub_user_id = $sub_user->id;
                            $sub_user_address->address = $reschedule->address;
                            $sub_user_address->latitude = $reschedule->latitude;
                            $sub_user_address->longitude = $reschedule->longitude;
                        } else {
                            $sub_user_address->end_date = $reschedule->date;
                            $sub_new_address = new SubUserAddresse();

                            $sub_new_address->sub_user_id = $sub_user->id;
                            $sub_new_address->address = $reschedule->address;
                            $sub_new_address->latitude = $reschedule->latitude;
                            $sub_new_address->longitude = $reschedule->longitude;
                            $sub_new_address->start_date = $reschedule->date;
                            $sub_new_address->save();
                        }
                        $sub_user_address->update();
                    } else {

                        $sub_new_address = new SubUserAddresse();

                        $sub_new_address->sub_user_id = $sub_user->id;
                        $sub_new_address->address = $reschedule->address;
                        $sub_new_address->latitude = $reschedule->latitude;
                        $sub_new_address->longitude = $reschedule->longitude;
                        $sub_new_address->start_date = $reschedule->date;

                        $sub_new_address->save();
                    }
                }
                $subUser = SubUser::where('id', $reschedule->user_id)->first();
                $token = @$subUser->fcm_id;
                $title = "Reschedule request accepted";
                $body = "Your reschedule request has been accepted.";
                @$this->notification->sendPushNotification($token, $title, $body);
                $reschedule->status = 1;
                $reschedule->save();
            }
        }
            DB::commit();
            return response()->json(['success' => true,  "message" => "Reschedule accepted successfully"], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //************************** Schedule history api *******************/
    /**
     * @OA\Post(
     * path="/uc/api/scheduleHistory",
     * operationId="scheduleHistory",
     * tags={"Dashboard"},
     * summary="Schedule History api",
     *   security={ {"Bearer": {} }},
     * description="Schedule History api",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"schedule_id", "date"},
     *               @OA\Property(property="schedule_id", type="text"),
     *               @OA\Property(property="date", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Schedule history listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Schedule history listed successfully",
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
    public function scheduleHistory(Request $request)
    {
        try {
            // Validate request inputs
            $request->validate([
                'schedule_id' => 'required|exists:schedules,id',
                'date' => 'required|date_format:Y-m-d'
            ]);

            $schedule = Schedule::find($request->schedule_id);

            if (!$schedule) {
                return response()->json(['success' => false, 'message' => 'Schedule not found'], 404);
            }

            $similar_schedules = Schedule::where('end_date', '>=', date('Y-m-d'))
                ->orWhere('date', '>=', date('Y-m-d'))
                ->where('city', $schedule->city)
                ->get();

            $date = date('Y-m-d', strtotime($request->date));
            $this->data = ['message' => ['pick' => [], 'drop' => []]];

            // Fetch and construct messages for schedule statuses
            $this->fetchScheduleStatus($schedule, 'pick', $date);
            $this->fetchScheduleStatus($schedule, 'drop', $date);

            // Fetch and construct messages for schedule carer statuses
            $this->fetchScheduleCarerStatuses($request->schedule_id, 'pick', $date);
            $this->fetchScheduleCarerStatuses($request->schedule_id, 'drop', $date);

            $this->data['similar_schedules'] = $similar_schedules;

            return response()->json(['success' => true, 'data' => $this->data], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    private function fetchScheduleStatus($schedule, $type, $date)
    {
        $scheduleStatus = ScheduleStatus::where('schedule_id', $schedule->id)
            ->where('type', $type)
            ->where('date', $date)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($scheduleStatus) {
            $message = $scheduleStatus->end_time ?
                "Ride for $type completed at {$scheduleStatus->end_time}." :
                "Schedule for $type started at {$scheduleStatus->start_time}.";
            $this->data['message'][$type][] = $message;
        }
    }

    public function fetchScheduleCarerStatuses($scheduleId, $type, $date)
    {
        $scheduleCarers = ScheduleCarer::with('user')
            ->where('schedule_id', $scheduleId)
            ->where('shift_type', $type)
            ->get();

        foreach ($scheduleCarers as $scheduleCarer) {
            $this->fetchCarerStatusMessage($scheduleCarer, $type, $date);
            $this->fetchCarerStatus($scheduleCarer, $type, $date);
        }
    }

    public function fetchCarerStatusMessage($scheduleCarer, $type, $date)
    {
        $getStatus = DB::table('schedule_carer_statuses')
            ->select('statuses.id as SId', 'statuses.*', 'schedule_carer_statuses.*')
            ->leftJoin('statuses', 'schedule_carer_statuses.status_id', '=', 'statuses.id')
            ->where('schedule_carer_id', $scheduleCarer->id)
            ->where('date', $date)
            ->first();

        if (!$getStatus) return;

        $user = $scheduleCarer->user->first_name;
        $messages = [
            11 => "$user is on leave",
            5  => "Driver marked absent $user",
            3  => "Ride completed for $user",
            1  => "$user is waiting for the ride"
        ];

        if (isset($messages[$getStatus->SId])) {
            $this->data['message'][$type][] = $messages[$getStatus->SId];
        }
    }

    public function fetchCarerStatus($scheduleCarer, $type, $date)
    {
        $status = ScheduleCarerStatus::where('schedule_carer_id', $scheduleCarer->id)
            ->where('date', $date)
            ->whereIn('status_id', [2, 3])
            ->first();

        if (!$status) return;

        $user = $scheduleCarer->user->first_name;
        $message = $status->end_time ?
            "$user dropped to location at {$status->end_time}." :
            "$user picked from location at {$status->start_time}.";

        $this->data['message'][$type][] = $message;
    }

    //******************* Drag and drop functionality **************************/

    /**
     * @OA\Post(
     *     path="/uc/api/dragAndDrop",
     *     operationId="dragAndDrop",
     *     tags={"Ucruise Schedule"},
     *     summary="Drag and drop",
     *     security={{"Bearer": {}}},
     *     description="Drag and drop",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="object",
     *                     description="Your description here"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Carers moved successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Carers moved successfully",
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

    public function dragAndDrop(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'source_schedule_id' => 'required|integer',
                'source_schedule_carers' => 'required|array',
                'destination_schedule_mapping' => 'required|array',
            ]);

            $sourceScheduleId = $request->source_schedule_id;
            $sourceScheduleCarers = $request->source_schedule_carers;
            $destinationScheduleMapping = $request->destination_schedule_mapping;


            $sourceSchedule = Schedule::findOrFail($sourceScheduleId);


            foreach ($destinationScheduleMapping as $destinationId => $shiftTypes) {

                $destinationSchedule = Schedule::with('carers')->findOrFail($destinationId);
                $newSchedule = $destinationSchedule->replicate();
                $newSchedule->date = date('Y-m-d');
                $newSchedule->schedule_parent_id = $destinationSchedule->id;
                $newSchedule->save();

                foreach ($destinationSchedule->carers as $carer) {
                    ScheduleCarer::create([
                        'schedule_id' => $newSchedule->id,
                        'carer_id' => $carer->carer_id,
                        'shift_type' => $carer->shift_type
                    ]);
                }

                foreach ($shiftTypes as $shiftType => $carerIds) {
                    foreach ($carerIds as $carerId) {
                        $carer = $sourceSchedule->carers()->where('carer_id', $carerId)->first();
                        if ($carer) {
                            ScheduleCarer::create([
                                'schedule_id' => $newSchedule->id,
                                'carer_id' => $carerId,
                                'shift_type' => $shiftType
                            ]);

                            $destinationSchedule->update(['end_date' => date('Y-m-d')]);
                        }
                    }
                }
            }
            if (!empty($sourceScheduleCarers['pick']) || !empty($sourceScheduleCarers['drop'])) {
                $newSourceSchedule = $sourceSchedule->replicate();
                $newSchedule->schedule_parent_id = $sourceSchedule->id;
                $newSourceSchedule->date = date('Y-m-d');
                $newSourceSchedule->save();

                foreach ($sourceScheduleCarers as $shiftType => $carerIds) {
                    foreach ($carerIds as $carerId) {
                        ScheduleCarer::create([
                            'schedule_id' => $newSourceSchedule->id,
                            'carer_id' => $carerId,
                            'shift_type' => $shiftType,
                        ]);
                    }
                }
            }

            $sourceSchedule->end_date = date('Y-m-d');
            $sourceSchedule->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Carers moved successfully'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        }
    }

    //************************* Temp Route api*********************************************************************/
    /**
     * @OA\Post(
     * path="/uc/api/tempRoute",
     * operationId="tempRoute",
     * tags={"Dashboard"},
     * summary="Temporary route",
     *   security={ {"Bearer": {} }},
     * description="Temporary route",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Temp route data listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Temp route data listed successfully",
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
    public function tempRoute(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:schedule_carer_relocations,id'
        ]);
        $scheduleCarerRelocation = ScheduleCarerRelocation::where('id', $request->id)->first();

        $home = new HomeController();
        $user_ids = [$scheduleCarerRelocation->staff_id];
        $dates = [$scheduleCarerRelocation->date];
        $this->data1['route'] = [];
        $this->data['schedules'] = $home->getWeeklyScheduleInfo($user_ids, $dates, 2, "all");
        $company = @$home->companyInfo($scheduleCarerRelocation->date);
        foreach ($this->data['schedules'] as $key => $schedule) {

            $scheduleDate = date('Y-m-d', strtotime($scheduleCarerRelocation->date));
            $holiday = Holiday::where('date', $scheduleDate)->exists();
            if (!$holiday) {
                $start = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['start_time']));
                $end = $scheduleDate . ' ' . date('H:i:s', strtotime($schedule['end_time']));
                if ($schedule['type'] == 'pick' && ($scheduleCarerRelocation->shift_type == 2 || $scheduleCarerRelocation->shift_type == 1)) {
                    $this->data1['route'][$key]['time'] = $start;
                    $this->data1['route'][$key]['type'] = $schedule['type'];
                    $this->data1['route'][$key]['scheudleId'] = $schedule['id'];
                    $this->data1['route'][$key]['route1']['carers'] = @$home->getScheduleCarers($schedule['id'], $schedule['type'], $scheduleDate);
                    $carers = @$home->getScheduleCarers($schedule['id'], $schedule['type'], $scheduleDate);
                    foreach ($carers as $carer) {
                        if ($carer->carer_id == $scheduleCarerRelocation->staff_id) {
                            $distance = @$home->calculateDistance($company->latitude, $company->longitude, $scheduleCarerRelocation->temp_latitude, $scheduleCarerRelocation->temp_longitude);
                            $carer->latitude = $scheduleCarerRelocation->temp_latitude;
                            $carer->distance = $distance;
                            $carer->longitude = $scheduleCarerRelocation->temp_longitude;
                            $carer->address = $scheduleCarerRelocation->temp_address;
                        }
                    }
                    $this->data1['route'][$key]['route2']['carers'] = @$carers;
                } else if ($schedule['type'] == 'drop' && ($scheduleCarerRelocation->shift_type == 3 || $scheduleCarerRelocation->shift_type == 1)) {
                    $this->data1['route'][$key]['time'] = $end;
                    $this->data1['route'][$key]['type'] = $schedule['type'];
                    $this->data1['route'][$key]['scheudleId'] = $schedule['id'];
                    $this->data1['route'][$key]['route1']['carers'] = @$home->getScheduleCarers($schedule['id'], $schedule['type'], $scheduleDate);
                    $carers = @$home->getScheduleCarers($schedule['id'], $schedule['type'], $scheduleDate);
                    foreach ($carers as $carer) {
                        if ($carer->carer_id == $scheduleCarerRelocation->staff_id) {
                            $distance = @$home->calculateDistance($company->latitude, $company->longitude, $scheduleCarerRelocation->temp_latitude, $scheduleCarerRelocation->temp_longitude);
                            $carer->distance = $distance;
                            $carer->latitude = $scheduleCarerRelocation->temp_latitude;
                            $carer->longitude = $scheduleCarerRelocation->temp_longitude;
                            $carer->address = $scheduleCarerRelocation->temp_address;
                        }
                    }
                    $this->data1['route'][$key]['route2']['carers'] = @$carers;
                }
            }
            if ($this->data1['route']) {
                usort($this->data1['route'], function ($a, $b) {
                    $dateTimeA = new \DateTime($a['time']);
                    $dateTimeB = new \DateTime($b['time']);

                    return $dateTimeA <=> $dateTimeB;
                });
            }
        }
        $this->data1['company_info'] = @$company;
        $this->data1['temp_location_request'] = @$scheduleCarerRelocation;
        return response()->json(['success' => true, 'data' => $this->data1], 200);
    }

    // Driver activity 

    public function driverActivety(){

        
    }



}
