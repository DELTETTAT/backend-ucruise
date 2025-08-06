<?php

namespace App\Http\Controllers\Admin;


use DateTime;
use Carbon\Carbon;
use App\Models\Notification;
use App\Models\Reminder;
use App\Models\Leave;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

use App\Models\{Allowances, CompanyAddresse, CompanyDetails, Holiday, Language, Paygroup, PriceBook, ReportHeading, RescheduleHistory, Schedule, ScheduleCarer, ScheduleCarerReloaction, ScheduleClient, ScheduleMileageClient, ScheduleTask, ShiftTypes, SubUser, Teams, User, Vehicle};


class SchedularController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->storeNotification();
        $this->data['flag'] = @$_REQUEST['flag'];
        $this->data['shiftTypes'] = ShiftTypes::get();
        return view("superadmin.scheduler.add", $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create(Request $request)
    {
        // dd($request->all());
        $this->data['flag'] = $flag = @$_REQUEST['flag'];

        $this->data['shift_type_id'] = $request->shift_type_id ? $request->shift_type_id : '';

        $this->data['client_staff'] = $request->client_staff ? $request->client_staff : 1;

        $plusDays = 6;
        if ($flag == 2) {
            $plusDays = 13;
        }

        if ($request->ajax()) {

            if (@$_REQUEST['calendarDate']) {

                $providedDate = date('Y-m-d', strtotime($_REQUEST['calendarDate'])); // Example date
                $year = date('Y-m-d', strtotime($_REQUEST['calendarDate'])); // Example date

            } else {

                $providedDate = date('Y-m-d', strtotime("today")); // Example date
                $year = date('Y');
            }

            if ($flag == 3) {
                return $this->dailyData($providedDate);
            }

            $weekNumber = date('W', strtotime($providedDate));
            // $year = date('Y');      // Example year

            // $firstDayOfYear = new \DateTime("$year-01-01");
            // // Calculate the start date of the desired week (first Monday)
            // $startDate = clone $firstDayOfYear;
            // $startDate->modify("+" . ($weekNumber - 1) . " weeks");
            // $startDate->modify('next monday');


            // $weekNumber = date('W', strtotime($providedDate));
            // $year = 2024;      // Example year

            $firstDayOfYear = new \DateTime("$year-01-01");
            // Calculate the start date of the desired week (first Monday)
            $startDate = clone $firstDayOfYear;
            $startDate->modify("+" . ($weekNumber - 1) . " weeks");
            $startDate->modify('this monday');



            // Calculate the end date of the week (Sunday)

            $endDate = clone $startDate;

            $endDate->modify('+' . $plusDays . ' days');
            // Print the dates from Monday to Sunday

            $currentDate = clone $startDate;
            $currentDate = strtotime($currentDate->format('Y-m-d'));
            // echo $endDate->format('Y-m-d');
            $endDate = strtotime($endDate->format('Y-m-d'));


            $days = [];
            $dates = [];

            while ($currentDate <= $endDate) {

                $dayData = [

                    'date' => date("Y-m-d", $currentDate),

                    'day' => date("D", $currentDate),

                ];

                $days[] = $dayData;
                $dates[] = date("Y-m-d", $currentDate);
                $currentDate = strtotime("+1 day", $currentDate);
            }

            //$this->data['users'] = User::paginate(5);

            $this->data['id'] = 1007;
            $this->data['days'] = $days;
            $this->data['endDate'] = $endDate;
            $this->data['currentMonth'] = date('F Y', strtotime($providedDate));

            if ($this->data['client_staff'] == 1) {
                $this->data['users_new'] = SubUser::whereHas("roles", function ($q) {
                    $q->whereIn("name", ["driver"]);
                })
                    ->get();
            } else if ($this->data['client_staff'] == 2) {
                $this->data['users_new'] = SubUser::whereHas("roles", function ($q) {
                    $q->whereIn("name", ['carer']);
                })->orderBy("id", "DESC")->get();
            }

            $this->data['shiftTypes'] = ShiftTypes::get();

            $html = view("superadmin.scheduler.index3", $this->data)->render();

            return response()->json([
                "success" => true,
                "html" => $html,
                "dates" => $dates,
                "days" => $days,
                "shift_type_id" => $this->data['shift_type_id'],
                "client_staff" => $this->data['client_staff'],
                "current_month" => $this->data['currentMonth'],
            ], 200);
        }
    }

    // Daily data
    public function dailyData($providedDate)
    {

        $days = [];

        $dayData = [

            'date' => date("Y-m-d", strtotime($providedDate)),

            'day' => date("D", strtotime($providedDate)),

        ];

        $days[] = $dayData;

        $dates[] = date("Y-m-d", strtotime($providedDate));

        $this->data['currentMonth'] = date('F Y', strtotime($providedDate));

        $this->data['providedDate'] = $providedDate;
        $this->data['days'] = $days;
        $this->data['dates'] = $dates;

        if ($this->data['client_staff'] == 1) {
            $this->data['users_new'] = SubUser::whereHas("roles", function ($q) {
                $q->whereIn("name", ["driver"]);
            })
                ->get();
        } else if ($this->data['client_staff'] == 2) {
            $this->data['users_new'] = SubUser::whereHas("roles", function ($q) {
                $q->whereIn("name", ['carer']);
            })->orderBy("id", "DESC")->get();
        }

        $this->data['shiftTypes'] = ShiftTypes::get();

        $html = view("superadmin.scheduler.daily", $this->data)->render();

        return response()->json([
            "success" => true,
            "html" => $html,
            "days" => $days,
            "dates" => $dates,
            "shift_type_id" => $this->data['shift_type_id'],
            "client_staff" => $this->data['client_staff'],
            "current_month" => $this->data['currentMonth'],
        ], 200);
    }


    public function getweeklyScheduleInfo(Request $request)
    {

        $user_ids = $request->user_ids;
        $dates = $request->dates;
        // dd($dates);

        $schedule_id_arr = array();

        $previous_date = Carbon::createFromFormat('Y-m-d', min($dates))->subDay()->format('Y-m-d');

        $public_dates = array();

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

        if ($request->client_staff) {
            // if ($request->client_staff == 1) {
            //     $schedules = $schedules->whereHas('clients', function ($q) use ($user_ids) {

            //         $q->whereIn('client_id', $user_ids);
            //     });
            // } else 
            $leaves = array();
            if ($request->client_staff == 2) {

                $schedules = $schedules->whereHas('carers', function ($q) use ($user_ids) {

                    $q->whereIn('carer_id', $user_ids);
                });
                // dd($user_ids);
                // $schedules = $schedules->whereHas('carers', function ($q) use ($user_ids, $dates) {
                //     $q->whereIn('carer_id', $user_ids);
                //     $q->with('status', function ($q) use ($dates, $user_ids) {
                //         $q->whereIn('date', $dates)->whereIn('schedule_carer_id', $user_ids)->where('status_id', 4);
                //     });
                // });

                $schedules = $schedules->whereNotNull('driver_id');

                $leaves = Leave::where('status', 1)->whereIn('start_date', $dates)->whereIn('staff_id', $user_ids)->pluck('start_date', 'staff_id');
            } else {
                $schedules = $schedules->whereIn('driver_id', $user_ids);
            }
        }

        $schedules = $schedules->with('shiftType')->with('driver')->with(['carers' => function ($q) use ($dates, $previous_date) {
            $q->with('user');
            //$q->groupBy('carer_id');
        }]);




        if ($request->shift_type_id) {
            if ($request->shift_type_id != "all") {
                $schedules = $schedules->where('shift_type_id', $request->shift_type_id);
            }
        }

        $holidays = Holiday::whereIn('date', $dates)->pluck('date');

        // foreach ($holidays as $holiday) {
        //     array_push($public_dates, Carbon::createFromFormat('Y-m-d', $holiday->date));
        // }

        //  dd($public_dates);
        $schedules = $schedules->get();

        array_push($dates, $previous_date);

        foreach ($schedules as $schedule) {
            $exc_dates = array();
            if ($schedule->excluded_dates) {
                foreach (json_decode($schedule->excluded_dates) as $exc_date) {
                    array_push($exc_dates, Carbon::createFromFormat('Y-m-d', $exc_date));
                }
            }

            $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
            $date = $current_date->copy()->format('Y-m-d');

            // if (!in_array($current_date->copy(), $public_dates)) {
            if (in_array($date, $dates)) {
                array_push($schedule_id_arr, $schedule->toArray());
            }
            // }
            if ($schedule->is_repeat == 1) {
                if ($schedule->reacurrance == 0) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date)->addDays($schedule->repeat_time);
                    while ($current_date < $schedule->end_date) {
                        $date = $current_date->format('Y-m-d');
                        // if (!in_array($current_date, $public_dates)) {
                        if (!in_array($current_date, $exc_dates)) {
                            if (in_array($date, $dates)) {
                                $schedule->date = $current_date->format('Y-m-d');;
                                array_push($schedule_id_arr, $schedule->toArray());
                            }
                        }
                        // }
                        $current_date = $current_date->addDays($schedule->repeat_time);
                    }
                } else if ($schedule->reacurrance == 1) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                    $scheduleDate = $current_date->copy();
                    while ($current_date->copy()->startOfWeek() < $schedule->end_date) {
                        $current_date = $current_date->copy()->startOfWeek() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfWeek();
                        $endofthisweek = $current_date->copy()->endOfWeek();
                        if ($this->dates_in_range($current_date->copy()->format('Y-m-d'), $endofthisweek->copy()->format('Y-m-d'), $request->days) == true) {
                            while ($current_date->copy() < $endofthisweek & $current_date->copy() < $schedule->end_date) {
                                $date = $current_date->format('Y-m-d');
                                $day_name = $current_date->copy()->format('D');
                                // if (!in_array($current_date, $public_dates)) {
                                if (!in_array($current_date, $exc_dates)) {
                                    if (in_array($date, $dates) & in_array(strtolower($day_name), json_decode($schedule->occurs_on))) {
                                        $schedule->date = $current_date->copy()->format('Y-m-d');
                                        array_push($schedule_id_arr, $schedule->toArray());
                                    }
                                }
                                // }
                                $current_date = $current_date->copy()->addDay();
                            }
                            $current_date = $current_date->copy()->subDay();
                        }
                        $current_date = $current_date->copy()->addWeeks($schedule->repeat_time);
                    }
                } else if ($schedule->reacurrance == 2) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                    $scheduleDate = $current_date->copy();
                    while ($current_date->copy()->startOfMonth() < $schedule->end_date) {
                        $current_date = $current_date->copy()->startOfMonth() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfMonth();
                        // $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                        $endofthismonth = $current_date->copy()->endOfMonth();
                        if ($this->dates_in_range($current_date->copy()->format('Y-m-d'), $endofthismonth->copy()->format('Y-m-d'), $request->days) == true) {
                            while ($current_date->copy() < $endofthismonth & $current_date->copy() < $schedule->end_date) {
                                $date = $current_date->format('Y-m-d');
                                // if (!in_array($current_date, $public_dates)) {
                                if (!in_array($current_date, $exc_dates)) {
                                    if (in_array($date, $dates)) {
                                        $schedule->date = $current_date->copy()->format('Y-m-d');
                                        array_push($schedule_id_arr, $schedule->toArray());
                                    }
                                }
                                // }
                                $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                            }
                            $current_date = $current_date->copy()->subDays($schedule->occurs_on);
                        }
                        $current_date = $current_date->copy()->addMonths($schedule->repeat_time);
                    }
                }
            }
        }
        $this->data['schedules'] = collect($schedule_id_arr);

        return response()->json(["schedule" => $this->data['schedules'], "holidays" => $holidays, "leaves" => $leaves, "client_staff" => $request->client_staff], 200);
    }

    function dates_in_range(string $start_date, string $end_date, array $dates): bool
    {
        foreach ($dates as $date) {
            if ($date['date'] >= $start_date & $date['date'] <= $end_date) {
                return true;
            }
        }
        return false;
    }

    public function addSchedule()
    {
        $date = date('Y-m-d');
        $this->data['company_details'] = CompanyAddresse::whereNull('end_date')->first();

        $this->data['drivers'] = SubUser::join('vehicles', 'sub_users.id', '=', 'vehicles.driver_id')
            ->whereHas("roles", function ($q) {
                $q->where("name", "driver");
            })
            ->where('close_account', 1)
            ->orderBy("sub_users.id", "DESC") // Assuming 'sub_users' is the table name for drivers
            ->select('sub_users.*', 'vehicles.id as vehicle_id', 'vehicles.*')
            ->get();



        $this->data['clients'] = User::whereHas("roles", function ($q) {
            $q->where("name", "client");
        })->where('close_account', 1)->orderBy("id", "DESC")->get();

        $this->data['carers'] = SubUser::join('sub_user_addresses', 'sub_users.id', '=', 'sub_user_addresses.sub_user_id')
            ->whereHas("roles", function ($q) {
                $q->whereIn("name", ['carer']);
            })
            ->where(function ($query) use ($date) {
                $query->whereDate('sub_user_addresses.start_date', '<=', $date)
                    ->where(function ($query) use ($date) {
                        $query->whereDate('sub_user_addresses.end_date', '>', $date)
                            ->orWhereNull('sub_user_addresses.end_date');
                    });
            })
            ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address')
            ->orderBy("sub_users.id", "DESC")
            ->get();

        $this->data['pricebooks'] = PriceBook::get();
        $this->data['teams'] = Teams::get();
        $this->data['languages'] = Language::get();
        $this->data['allowances'] = Allowances::get();
        $this->data['shiftTypes'] = ShiftTypes::get();
        $this->data['paygroups'] = Paygroup::get();
        $this->data['compliances'] = ReportHeading::where('category_id', 1)->get();
        $this->data['competencies'] = ReportHeading::where('category_id', 3)->get();
        $this->data['kpis'] = ReportHeading::where('category_id', 2)->get();
        $this->data['vehicles'] = Vehicle::all();
        return view("superadmin.scheduler.create", $this->data);
    }

    public function storeSchedule(Request $request)
    {
        try {
            $request->validate(
                [
                    'start_time' => 'required_if:shift_types,1,2',
                    'end_time' => 'required_if:shift_types,2,3',
                    'reacurrance_end_time' => 'required_if:is_repeat,on',
                    'pickUpCarer' => 'required_if:shift_types,1,2|array',
                    'dropOffCarer' => 'required_if:shift_types,2,3|array',


                ],
                [
                    'start_time.required_if' => 'The pickup time is required.',
                    'end_time.required_if' => 'The dropoff time is required.',
                    'reacurrance_end_time.required_if' => 'The recurrence end time is required .',
                    'pickUpCarer.required_if' => 'Pick Up Staffs are required.',
                    'pickUpCarer.array' => 'Pick Up Staffs must be selected in an array.',
                    'dropOffCarer.required_if' => 'Drop Off Staffs are required .',
                    'dropOffCarer.array' => 'Drop Off Staffs must be selected in an array.',



                ]
            );

            $week_arr = array();
            if ($request->driver) {
                $vehicle = Vehicle::where('driver_id', $request->driver)->first();
            }
            $schedule = new Schedule();
            $schedule->date = $request->date;
            $schedule->locality = $request->scheduleLocation;
            $schedule->city = $request->scheduleCity;
            $schedule->latitude = $request->selectedLocationLat;
            $schedule->longitude = $request->selectedLocationLng;
            $schedule->driver_id = $request->driver;
            $schedule->vehicle_id = $vehicle->id;
            $schedule->shift_finishes_next_day = $request->shift_types == "2" ? ($request->shift_finishes_next_day ? 1 : 0) : 0;
            $schedule->start_time = $request->date . ' ' . $request->start_time . ':00';
            $schedule->end_time = $request->date . ' ' . $request->end_time . ':00';
            $schedule->break_time_in_minutes = $request->break_time_in_minutes;
            $schedule->is_repeat = $request->is_repeat ? 1 : 0;
            if ($request->is_repeat) {
                if ($request->reacurrance == "daily") {
                    $schedule->reacurrance = 0;
                    $schedule->repeat_time = $request->repeat_days;
                } else if ($request->reacurrance == "weekly") {
                    $schedule->reacurrance = 1;
                    $schedule->repeat_time = $request->repeat_weeks;
                    if ($request->mon) {
                        array_push($week_arr, "mon");
                    }
                    if ($request->tue) {
                        array_push($week_arr, "tue");
                    }
                    if ($request->wed) {
                        array_push($week_arr, "wed");
                    }
                    if ($request->thu) {
                        array_push($week_arr, "thu");
                    }
                    if ($request->fri) {
                        array_push($week_arr, "fri");
                    }
                    if ($request->sat) {
                        array_push($week_arr, "sat");
                    }
                    if ($request->sun) {
                        array_push($week_arr, "sun");
                    }
                    $schedule->occurs_on = json_encode($week_arr);
                } else if ($request->reacurrance == "monthly") {
                    $schedule->reacurrance = 2;
                    $schedule->repeat_time = $request->repeat_months;
                    $schedule->occurs_on = $request->repeat_day_of_month;
                }
                $schedule->end_date = $request->reacurrance_end_time;
            }

            if ($request->shift_types) {
                $schedule->shift_type_id = $request->shift_types;
            }
            if ($request->pricebook) {
                $schedule->pricebook_id = $request->pricebook;
            }

            $schedule->ignore_staff_count = $request->ignore_staff_count ? 1 : 0;
            $schedule->confirmation_required = $request->confirmation_required ? 1 : 0;

            $schedule->add_to_job_board = $request->add_to_job_board ? 1 : 0;

            $schedule->notify_carer = $request->notify_carer ? 1 : 0;
            $schedule->instructions = $request->instructions;
            $schedule->save();

            if ($request->shift_types == "2" || $request->shift_types == "1") {
                if ($request->pickUpCarer) {
                    // dd($request->carer);
                    foreach ($request->pickUpCarer as $carer) {
                        $scheduleCarers = new ScheduleCarer();
                        $scheduleCarers->schedule_id = $schedule->id;
                        $scheduleCarers->carer_id = $carer;
                        $scheduleCarers->shift_type = "pick";
                        $scheduleCarers->save();
                    }
                }
            }

            if ($request->shift_types == "2" || $request->shift_types == "3") {
                if ($request->dropOffCarer) {
                    // dd($request->carer);
                    foreach ($request->dropOffCarer as $carer) {
                        $scheduleCarers = new ScheduleCarer();
                        $scheduleCarers->schedule_id = $schedule->id;
                        $scheduleCarers->carer_id = $carer;
                        $scheduleCarers->shift_type = "drop";
                        $scheduleCarers->save();
                    }
                }
            }

            if ($request->tasks) {
                foreach ($request->tasks as $task) {
                    $scheduleTasks = new ScheduleTask();
                    $scheduleTasks->schedule_id = $schedule->id;
                    foreach ($task as $key => $value) {
                        if ($key == "name") {
                            $scheduleTasks->name = $value;
                        } else if ($key == "is_mandatory") {
                            $scheduleTasks->is_mandatory = $value == 'on' ? 1 : 0;
                        }
                    }
                    $scheduleTasks->save();
                }
            }
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back()->withInput();
        }
        return redirect()->route('scheduler.index');
    }

    public function updateSchedule(Request $request, $id)
    {
        try {
            $request->validate(
                [
                    'start_time' => 'required_if:shift_types,1,2',
                    'end_time' => 'required_if:shift_types,2,3',

                ],
                [
                    'start_time.required_if' => 'The pickup time is required.',
                    'end_time.required_if' => 'The dropoff time is required.',

                ]
            );
            $week_arr = array();
            if ($request->driver) {
                $vehicle = Vehicle::where('driver_id', $request->driver)->first();
            }
            $excluded_dates = array();

            $schedule_check = Schedule::find($id);
            if ($schedule_check) {
                $excluded_dates = json_decode($schedule_check->excluded_dates);
                if ($schedule_check->is_repeat == 1) {
                    if (date('Y-m-d', $request->current_date) != $schedule_check->date) {
                        if ($schedule_check->schedule_parent_id == NULL) {
                            if ($request->apply_to_future) {
                                $schedule = new Schedule();
                                $schedule->date = date('Y-m-d', $request->current_date);
                                $schedule->end_date = $schedule_check->end_date;
                                $schedule_check->end_date = date('Y-m-d', $request->current_date);
                                $schedule_check->save();

                                $schedule->is_repeat = $schedule_check->is_repeat;
                                $schedule->reacurrance = $schedule_check->reacurrance;
                                $schedule->repeat_time = $schedule_check->repeat_time;
                                $schedule->occurs_on = $schedule_check->occurs_on;
                            } else {
                                $schedule = new Schedule();
                                $schedule->schedule_parent_id = $id;
                                $schedule->date = date('Y-m-d', $request->current_date);
                                if ($excluded_dates === NULL) {
                                    $excluded_dates = array();
                                }
                                array_push($excluded_dates, date('Y-m-d', $request->current_date));
                                $schedule_check->excluded_dates = $excluded_dates;
                                $schedule_check->save();
                            }
                        } else {
                            $schedule = $schedule_check;
                        }
                    } else {
                        if ($request->apply_to_future) {
                            $schedule = $schedule_check;
                        } else {
                            $schedule = new Schedule();
                            $schedule->date = date('Y-m-d', $request->current_date);
                            $schedule->end_date = date('Y-m-d', $request->current_date);
                            $date = date('Y-m-d', $request->current_date);
                            $schedule_check->date = date('Y-m-d', strtotime($date . ' + 1 days'));
                            $schedule_check->save();
                        }
                    }
                } else {
                    $schedule = $schedule_check;
                }
            }

            $schedule->driver_id = $request->driver;
            $schedule->vehicle_id = $vehicle->id;
            $schedule->shift_finishes_next_day = $request->shift_types == "2" ? ($request->shift_finishes_next_day ? 1 : 0) : 0;
            $schedule->start_time = $request->date . ' ' . $request->start_time . ':00';
            $schedule->end_time = $request->date . ' ' . $request->end_time . ':00';
            $schedule->break_time_in_minutes = $request->break_time_in_minutes;


            if ($request->shift_types) {
                $schedule->shift_type_id = $request->shift_types;
            }
            if ($request->pricebook) {
                $schedule->pricebook_id = $request->pricebook;
            }


            $schedule->ignore_staff_count = $request->ignore_staff_count ? 1 : 0;
            $schedule->confirmation_required = $request->confirmation_required ? 1 : 0;

            $schedule->add_to_job_board = $request->add_to_job_board ? 1 : 0;

            $schedule->notify_carer = $request->notify_carer ? 1 : 0;
            $schedule->longitude = $schedule_check->longitude;
            $schedule->latitude = $schedule_check->latitude;
            $schedule->city = $schedule_check->city;
            $schedule->locality = $schedule_check->locality;
            $schedule->save();

            if ($request->shift_types == "2" || $request->shift_types == "1") {
                if ($request->pickUpCarer) {

                    ScheduleCarer::where('schedule_id', $schedule->id)->whereNotIn('carer_id', $request->pickUpCarer)->where('shift_type', 'pick')->delete();
                    foreach ($request->pickUpCarerTimes as $carer) {
                        $scheduleCarers = ScheduleCarer::where('schedule_id', $schedule->id)->where('carer_id', $carer['carer_id'])->where('shift_type', 'pick')->first();
                        if (array_key_exists("make_working", $carer)) {
                            if ($scheduleCarers) {
                                $working_days = json_decode($scheduleCarers->working_days);
                                if ($working_days === NULL) {
                                    $working_days = array();
                                }
                                array_push($working_days, date('Y-m-d', $request->current_date));
                                $scheduleCarers->save();
                            } else {
                                $scheduleCarers = new ScheduleCarer();
                                $scheduleCarers->schedule_id = $schedule->id;
                                $scheduleCarers->carer_id = $carer['carer_id'];
                                $working_days = array();
                                array_push($working_days, date('Y-m-d', $request->current_date));
                                $scheduleCarers->working_days = json_encode($working_days);
                                $scheduleCarers->shift_type = 'pick';
                                $scheduleCarers->save();
                            }
                        } else {
                            if (!$scheduleCarers) {
                                $scheduleCarers = new ScheduleCarer();
                                $scheduleCarers->schedule_id = $schedule->id;
                                $scheduleCarers->carer_id = $carer['carer_id'];
                                $scheduleCarers->shift_type = 'pick';
                                $scheduleCarers->save();
                            }
                        }
                    }
                } else {
                    ScheduleCarer::where('schedule_id', $schedule->id)->where('shift_type', 'pick')->delete();
                }
            } else {
                ScheduleCarer::where('schedule_id', $schedule->id)->where('shift_type', 'pick')->delete();
            }

            if ($request->shift_types == "2" || $request->shift_types == "3") {
                if ($request->dropOffCarer) {

                    ScheduleCarer::where('schedule_id', $schedule->id)->whereNotIn('carer_id', $request->dropOffCarer)->where('shift_type', 'drop')->delete();
                    foreach ($request->dropOffCarerTimes as $carer) {
                        $scheduleCarers = ScheduleCarer::where('schedule_id', $schedule->id)->where('carer_id', $carer['carer_id'])->where('shift_type', 'drop')->first();
                        if (array_key_exists("make_working", $carer)) {
                            if ($scheduleCarers) {
                                $working_days = json_decode($scheduleCarers->working_days);
                                if ($working_days === NULL) {
                                    $working_days = array();
                                }
                                array_push($working_days, date('Y-m-d', $request->current_date));
                                $scheduleCarers->save();
                            } else {
                                $scheduleCarers = new ScheduleCarer();
                                $scheduleCarers->schedule_id = $schedule->id;
                                $scheduleCarers->carer_id = $carer['carer_id'];
                                $working_days = array();
                                array_push($working_days, date('Y-m-d', $request->current_date));
                                $scheduleCarers->working_days = json_encode($working_days);
                                $scheduleCarers->shift_type = 'drop';
                                $scheduleCarers->save();
                            }
                        } else {
                            if (!$scheduleCarers) {
                                $scheduleCarers = new ScheduleCarer();
                                $scheduleCarers->schedule_id = $schedule->id;
                                $scheduleCarers->carer_id = $carer['carer_id'];
                                $scheduleCarers->shift_type = 'drop';
                                $scheduleCarers->save();
                            }
                        }
                    }
                } else {
                    ScheduleCarer::where('schedule_id', $schedule->id)->where('shift_type', 'drop')->delete();
                }
            } else {
                ScheduleCarer::where('schedule_id', $schedule->id)->where('shift_type', 'pick')->delete();
            }

            if ($request->tasks) {
                ScheduleTask::where('schedule_id', $schedule->id)->delete();
                foreach ($request->tasks as $task) {
                    $scheduleTasks = new ScheduleTask();
                    $scheduleTasks->schedule_id = $schedule->id;
                    foreach ($task as $key => $value) {
                        if ($key == "name") {
                            $scheduleTasks->name = $value;
                        } else if ($key == "is_mandatory") {
                            $scheduleTasks->is_mandatory = $value == 'on' ? 1 : 0;
                        }
                    }
                    $scheduleTasks->save();
                }
            }
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }

        return redirect()->route('scheduler.index');
    }

    public function editSchedule($id, $date)
    {
        $d = Carbon::createFromTimestamp($date)->format('Y-m-d');
        $this->data['company_details'] = CompanyAddresse::whereDate('start_date', '<=', $d)
            ->where(function ($query) use ($d) {
                $query->whereDate('end_date', '>', $d)
                    ->orWhereNull('end_date');
            })
            ->first();
        $this->data['schedule'] = Schedule::with('clients', 'tasks', 'mileageClients')->with(['carers' => function ($q) use ($d) {

            $q->whereDoesntHave('carerStatus', function ($statusQuery) use ($d) {
                $statusQuery->where('date', $d);
                $statusQuery->where('status_id', 4);
            });
        }])->find($id);
        $this->data['drivers'] = SubUser::whereHas("roles", function ($q) {
            $q->where("name", "driver");
        })
            ->where('close_account', 1)
            ->leftJoin('sub_user_addresses', function ($join) use ($d) {
                $join->on('sub_users.id', '=', 'sub_user_addresses.sub_user_id')
                    ->whereDate('sub_user_addresses.start_date', '<=', $d)
                    ->where(function ($query) use ($d) {
                        $query->whereDate('sub_user_addresses.end_date', '>', $d)
                            ->orWhereNull('sub_user_addresses.end_date');
                    });
            })
            ->leftJoin('vehicles', 'sub_users.id', '=', 'vehicles.driver_id')
            ->orderBy("sub_users.id", "DESC")
            ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address', 'vehicles.*')
            ->get();

        $this->data['clients'] = User::whereHas("roles", function ($q) {
            $q->where("name", "client");
        })->where('close_account', 1)->orderBy("id", "DESC")->get();

        $this->data['carers'] = SubUser::whereHas("roles", function ($q) {
            $q->whereIn("name", ['carer']);
        })
            ->leftJoin('sub_user_addresses', function ($join) use ($d) {
                $join->on('sub_users.id', '=', 'sub_user_addresses.sub_user_id')
                    ->whereDate('sub_user_addresses.start_date', '<=', $d)
                    ->where(function ($query) use ($d) {
                        $query->whereDate('sub_user_addresses.end_date', '>', $d)
                            ->orWhereNull('sub_user_addresses.end_date');
                    });
            })
            ->orderBy("sub_users.id", "DESC")
            ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.longitude')
            ->get();




        $carer = $this->data['schedule']->carers;

        $carerIds = $carer->pluck('carer_id');

        $leaveRequests = Leave::whereIn('leaves.staff_id', $carerIds)
            ->where('leaves.start_date', $d) // Specify the table for "date"
            ->join('sub_users', 'leaves.staff_id', '=', 'sub_users.id')
            ->select('leaves.*', 'sub_users.first_name', 'sub_users.last_name')
            ->get();
        $this->data['leaveRequests'] = $leaveRequests;

        $event_carers = \DB::table('schedule_carer_relocations')->whereIn('staff_id', $carerIds)->where('schedule_carer_relocations.status', 0)->where('schedule_carer_relocations.date', $d)
            ->join('sub_users', 'schedule_carer_relocations.staff_id', '=', 'sub_users.id')
            ->select('schedule_carer_relocations.*', 'sub_users.first_name', 'sub_users.last_name')
            ->get();

        $this->data['event_carers'] =  $event_carers;
        $this->data['leaveRequests'] = $leaveRequests;

        $this->data['pricebooks'] = PriceBook::get();
        $this->data['teams'] = Teams::get();
        $this->data['languages'] = Language::get();
        $this->data['allowances'] = Allowances::get();
        $this->data['shiftTypes'] = ShiftTypes::get();
        $this->data['paygroups'] = Paygroup::get();
        $this->data['compliances'] = ReportHeading::where('category_id', 1)->get();
        $this->data['competencies'] = ReportHeading::where('category_id', 3)->get();
        $this->data['kpis'] = ReportHeading::where('category_id', 2)->get();
        $this->data['vehicles'] = Vehicle::get();
        $this->data['date'] = $date;
        $holidays = Holiday::where('date', date('Y-m-d', $date))->first();
        if ($holidays) {
            $this->data['holiday'] = 'yes';
        } else {
            $this->data['holiday'] = 'no';
        }

        return view("superadmin.scheduler.edit", $this->data);
    }
    public function storeNotification()
    {
        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->addDays(6);
        $reminders = Reminder::where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->get();


        $holidays = Holiday::where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->get();


        foreach ($reminders as $reminder) {

            $existingReminder = Notification::where('target', $reminder->target)
                ->where('type', 'Reminder')
                ->where('message', $reminder->content)
                ->where('date', $reminder->date)
                ->exists();


            if (!$existingReminder) {
                Notification::create([
                    'target' => $reminder->target,
                    'type' => 'Reminder',
                    'message' => $reminder->content,
                    'read_status' => 0, // 0 for unread
                    'date' => $reminder->date,
                ]);
            }
        }


        foreach ($holidays as $holiday) {

            $existingHoliday = Notification::where('type', 'Holiday')
                ->where('message', $holiday->name)
                ->where('date', $holiday->date)
                ->exists();

            if (!$existingHoliday) {
                Notification::create([
                    'type' => 'Holiday',
                    'message' => $holiday->name,
                    'read_status' => 0, // 0 for unread
                    'date' => $holiday->date,
                ]);
            }
        }
    }
}
