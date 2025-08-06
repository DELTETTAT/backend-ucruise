<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\{Holiday, Leave, Schedule, DailySchedule};
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @OA\Info(
 *    title="Your super  ApplicationAPI",
 *    version="1.0.0",
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $user;
    protected $data;

    /**
     * Contructor Class
     *
     * @void
     */
    public function __construct()
    {
        $this->data = array();
    }


    public function getWeeklyScheduleInfo($user_ids, $dates, $clientStaff, $shift_type_id)
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
                // $leaves = Leave::where('status', 'Approved')->whereIn('date', $dates)->whereIn('staff_id', $user_ids)->pluck('date', 'staff_id');
            } else {
                $schedules = $schedules->whereIn('driver_id', $user_ids);
            }
        }


        // Old code
        // $schedules = $schedules->with('shiftType')->with('driver')->with(['carers' => function ($q) {
        //     $q->with('user');  

        // }]);

        // New code

        $schedules = $schedules->with('shiftType')

            ->with('vehicle');
        // ->with(['carers' => function ($q) {
        // $q->with('user');
        // // ->groupBy('carer_id');
        // }]);




        if ($shift_type_id) {
            if ($shift_type_id != "all") {
                $schedules = $schedules->where('shift_type_id', $shift_type_id);
            }
        }

        $holidays = Holiday::whereIn('date', $dates)->pluck('date');

        $schedules = $schedules->get();


        // array_push($dates, $previous_date);

        foreach ($schedules as $schedule) {
            $exc_dates = array();
            if ($schedule->excluded_dates) {
                foreach (json_decode($schedule->excluded_dates) as $exc_date) {
                    array_push($exc_dates, Carbon::createFromFormat('Y-m-d', $exc_date));
                }
            }

            $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
            $date = $current_date->copy()->format('Y-m-d');
            $day_name = $current_date->copy()->format('D');
            $checkArr = json_decode($schedule->occurs_on);
            if ((!empty($checkArr) && in_array(strtolower($day_name), json_decode($schedule->occurs_on)) && $schedule->reacurrance == 1) || $schedule->reacurrance == 0 || $schedule->reacurrance == null) {
                // if (!in_array($current_date->copy(), $public_dates)) {
                if (in_array($date, $dates)) {
                    if ($schedule->shift_type_id == 2) {
                        $schedule->type = "pick";
                        array_push($schedule_id_arr, $schedule->toArray());
                        if ($schedule->shift_finishes_next_day == 0) {
                            $schedule->type = "drop";
                            array_push($schedule_id_arr, $schedule->toArray());
                        }
                    } else if ($schedule->shift_type_id == 1) {
                        $schedule->type = "pick";
                        array_push($schedule_id_arr, $schedule->toArray());
                    } else if ($schedule->shift_type_id == 3) {
                        $schedule->type = "drop";
                        array_push($schedule_id_arr, $schedule->toArray());
                    }
                }
                if ($date == $previous_date) {
                    if ($schedule->shift_finishes_next_day == 1) {
                        $schedule->type = "drop";
                        array_push($schedule_id_arr, $schedule->toArray());
                    }
                }
            }
            // }
            if ($schedule->is_repeat == 1) {
                if ($schedule->reacurrance == 0) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date)->addDays($schedule->repeat_time);
                    $schedule->end_date = date('Y-m-d', strtotime($schedule->end_date . ' +1 day'));
                    while ($current_date <= $schedule->end_date) {
                        $date = $current_date->format('Y-m-d');
                        // if (!in_array($current_date, $public_dates)) {
                        if (!in_array($current_date, $exc_dates)) {
                            $schedule->date = $current_date->copy()->format('Y-m-d');
                            if (in_array($date, $dates)) {
                                if ($schedule->shift_type_id == 2) {
                                    $schedule->type = "pick";
                                    array_push($schedule_id_arr, $schedule->toArray());
                                    if ($schedule->shift_finishes_next_day == 0) {
                                        $schedule->type = "drop";
                                        array_push($schedule_id_arr, $schedule->toArray());
                                    }
                                } else if ($schedule->shift_type_id == 1) {
                                    $schedule->type = "pick";
                                    array_push($schedule_id_arr, $schedule->toArray());
                                } else if ($schedule->shift_type_id == 3) {
                                    $schedule->type = "drop";
                                    array_push($schedule_id_arr, $schedule->toArray());
                                }
                            } else if ($date == $previous_date) {
                                if ($schedule->shift_finishes_next_day == 1) {
                                    $schedule->type = "drop";
                                    array_push($schedule_id_arr, $schedule->toArray());
                                }
                            }
                        }
                        // }
                        $current_date = $current_date->addDays($schedule->repeat_time);
                    }
                } else if ($schedule->reacurrance == 1) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                    $scheduleDate = $current_date->copy();
                    $schedule->end_date = date('Y-m-d', strtotime($schedule->end_date . ' +1 day'));
                    while ($current_date->copy()->startOfWeek() < $schedule->end_date) {
                        $current_date = $current_date->copy()->startOfWeek() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfWeek();
                        $endofthisweek = $current_date->copy()->endOfWeek();
                        if ($this->dates_in_range($current_date->copy()->format('Y-m-d'), $endofthisweek->copy()->format('Y-m-d'), $dates) == true) {
                            while ($current_date->copy() < $endofthisweek & $current_date->copy() <= $schedule->end_date) {
                                $date = $current_date->format('Y-m-d');
                                $day_name = $current_date->copy()->format('D');
                                // if (!in_array($current_date, $public_dates)) {
                                if (!in_array($current_date, $exc_dates)) {
                                    $schedule->date = $current_date->copy()->format('Y-m-d');
                                    if (in_array($date, $dates) & in_array(strtolower($day_name), json_decode($schedule->occurs_on))) {
                                        if ($schedule->shift_type_id == 2) {
                                            $schedule->type = "pick";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                            if ($schedule->shift_finishes_next_day == 0) {
                                                $schedule->type = "drop";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            }
                                        } else if ($schedule->shift_type_id == 1) {
                                            $schedule->type = "pick";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        } else if ($schedule->shift_type_id == 3) {
                                            $schedule->type = "drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        }
                                    } else if ($date == $previous_date & in_array(strtolower($day_name), json_decode($schedule->occurs_on))) {
                                        if ($schedule->shift_finishes_next_day == 1) {
                                            $schedule->type = "drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        }
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
                    while ($current_date->copy()->startOfMonth() <= $schedule->end_date) {
                        $current_date = $current_date->copy()->startOfMonth() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfMonth();
                        // $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                        $endofthismonth = $current_date->copy()->endOfMonth();
                        if ($this->dates_in_range($current_date->copy()->format('Y-m-d'), $endofthismonth->copy()->format('Y-m-d'), $dates) == true) {
                            while ($current_date->copy() < $endofthismonth & $current_date->copy() < $schedule->end_date) {
                                $date = $current_date->format('Y-m-d');
                                // if (!in_array($current_date, $public_dates)) {
                                if (!in_array($current_date, $exc_dates)) {
                                    $schedule->date = $current_date->copy()->format('Y-m-d');
                                    if (in_array($date, $dates)) {
                                        if ($schedule->shift_type_id == 2) {
                                            $schedule->type = "pick";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                            if ($schedule->shift_finishes_next_day == 0) {
                                                $schedule->type = "drop";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            }
                                        } else if ($schedule->shift_type_id == 1) {
                                            $schedule->type = "pick";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        } else if ($schedule->shift_type_id == 3) {
                                            $schedule->type = "drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        }
                                    } else if ($date == $previous_date) {
                                        if ($schedule->shift_finishes_next_day == 1) {
                                            $schedule->type = "drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        }
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


        return $this->data['schedules'];
    }


    public function dailygetWeeklyScheduleInfo($user_ids, $dates, $clientStaff, $shift_type_id)
    {

        $schedule_id_arr = array();

        $previous_date = Carbon::createFromFormat('Y-m-d', min($dates))->subDay()->format('Y-m-d');

        $schedules = DailySchedule::where(function ($query) use ($dates, $previous_date) {
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
                // $leaves = Leave::where('status', 'Approved')->whereIn('date', $dates)->whereIn('staff_id', $user_ids)->pluck('date', 'staff_id');
            } else {
                $schedules = $schedules->whereIn('driver_id', $user_ids);
            }
        }

        //$schedules = $schedules->with('shiftType')->with('vehicle');
        if ($shift_type_id) {
            if ($shift_type_id != "all") {
                $schedules = $schedules->where('shift_type_id', $shift_type_id);
            }
        }

    
        $holidays = Holiday::whereIn('date', $dates)->pluck('date');
        $schedules = $schedules->get();


        // array_push($dates, $previous_date);

        foreach ($schedules as $schedule) {
            $exc_dates = array();
            if ($schedule->excluded_dates) {
                foreach (json_decode($schedule->excluded_dates) as $exc_date) {
                    array_push($exc_dates, Carbon::createFromFormat('Y-m-d', $exc_date));
                }
            }

            $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
            $date = $current_date->copy()->format('Y-m-d');
            $day_name = $current_date->copy()->format('D');
            $checkArr = json_decode($schedule->occurs_on);
            if ((!empty($checkArr) && in_array(strtolower($day_name), json_decode($schedule->occurs_on)) && $schedule->reacurrance == 1) || $schedule->reacurrance == 0 || $schedule->reacurrance == null) {
                // if (!in_array($current_date->copy(), $public_dates)) {
                if (in_array($date, $dates)) {
                    if ($schedule->shift_type_id == 2) {
                        $schedule->type = "pick";
                        array_push($schedule_id_arr, $schedule->toArray());
                        if ($schedule->shift_finishes_next_day == 0) {
                            $schedule->type = "drop";
                            array_push($schedule_id_arr, $schedule->toArray());
                        }
                    } else if ($schedule->shift_type_id == 1) {
                        $schedule->type = "pick";
                        array_push($schedule_id_arr, $schedule->toArray());
                    } else if ($schedule->shift_type_id == 3) {
                        $schedule->type = "drop";
                        array_push($schedule_id_arr, $schedule->toArray());
                    }
                }
                if ($date == $previous_date) {
                    if ($schedule->shift_finishes_next_day == 1) {
                        $schedule->type = "drop";
                        array_push($schedule_id_arr, $schedule->toArray());
                    }
                }
            }
            // }
            if ($schedule->is_repeat == 1) {
                if ($schedule->reacurrance == 0) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date)->addDays($schedule->repeat_time);
                    $schedule->end_date = date('Y-m-d', strtotime($schedule->end_date . ' +1 day'));
                    while ($current_date <= $schedule->end_date) {
                        $date = $current_date->format('Y-m-d');
                        // if (!in_array($current_date, $public_dates)) {
                        if (!in_array($current_date, $exc_dates)) {
                            $schedule->date = $current_date->copy()->format('Y-m-d');
                            if (in_array($date, $dates)) {
                                if ($schedule->shift_type_id == 2) {
                                    $schedule->type = "pick";
                                    array_push($schedule_id_arr, $schedule->toArray());
                                    if ($schedule->shift_finishes_next_day == 0) {
                                        $schedule->type = "drop";
                                        array_push($schedule_id_arr, $schedule->toArray());
                                    }
                                } else if ($schedule->shift_type_id == 1) {
                                    $schedule->type = "pick";
                                    array_push($schedule_id_arr, $schedule->toArray());
                                } else if ($schedule->shift_type_id == 3) {
                                    $schedule->type = "drop";
                                    array_push($schedule_id_arr, $schedule->toArray());
                                }
                            } else if ($date == $previous_date) {
                                if ($schedule->shift_finishes_next_day == 1) {
                                    $schedule->type = "drop";
                                    array_push($schedule_id_arr, $schedule->toArray());
                                }
                            }
                        }
                        // }
                        $current_date = $current_date->addDays($schedule->repeat_time);
                    }
                } else if ($schedule->reacurrance == 1) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                    $scheduleDate = $current_date->copy();
                    $schedule->end_date = date('Y-m-d', strtotime($schedule->end_date . ' +1 day'));
                    while ($current_date->copy()->startOfWeek() < $schedule->end_date) {
                        $current_date = $current_date->copy()->startOfWeek() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfWeek();
                        $endofthisweek = $current_date->copy()->endOfWeek();
                        if ($this->dates_in_range($current_date->copy()->format('Y-m-d'), $endofthisweek->copy()->format('Y-m-d'), $dates) == true) {
                            while ($current_date->copy() < $endofthisweek & $current_date->copy() <= $schedule->end_date) {
                                $date = $current_date->format('Y-m-d');
                                $day_name = $current_date->copy()->format('D');
                                // if (!in_array($current_date, $public_dates)) {
                                if (!in_array($current_date, $exc_dates)) {
                                    $schedule->date = $current_date->copy()->format('Y-m-d');
                                    if (in_array($date, $dates) & in_array(strtolower($day_name), json_decode($schedule->occurs_on))) {
                                        if ($schedule->shift_type_id == 2) {
                                            $schedule->type = "pick";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                            if ($schedule->shift_finishes_next_day == 0) {
                                                $schedule->type = "drop";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            }
                                        } else if ($schedule->shift_type_id == 1) {
                                            $schedule->type = "pick";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        } else if ($schedule->shift_type_id == 3) {
                                            $schedule->type = "drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        }
                                    } else if ($date == $previous_date & in_array(strtolower($day_name), json_decode($schedule->occurs_on))) {
                                        if ($schedule->shift_finishes_next_day == 1) {
                                            $schedule->type = "drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        }
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
                    while ($current_date->copy()->startOfMonth() <= $schedule->end_date) {
                        $current_date = $current_date->copy()->startOfMonth() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfMonth();
                        // $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                        $endofthismonth = $current_date->copy()->endOfMonth();
                        if ($this->dates_in_range($current_date->copy()->format('Y-m-d'), $endofthismonth->copy()->format('Y-m-d'), $dates) == true) {
                            while ($current_date->copy() < $endofthismonth & $current_date->copy() < $schedule->end_date) {
                                $date = $current_date->format('Y-m-d');
                                // if (!in_array($current_date, $public_dates)) {
                                if (!in_array($current_date, $exc_dates)) {
                                    $schedule->date = $current_date->copy()->format('Y-m-d');
                                    if (in_array($date, $dates)) {
                                        if ($schedule->shift_type_id == 2) {
                                            $schedule->type = "pick";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                            if ($schedule->shift_finishes_next_day == 0) {
                                                $schedule->type = "drop";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            }
                                        } else if ($schedule->shift_type_id == 1) {
                                            $schedule->type = "pick";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        } else if ($schedule->shift_type_id == 3) {
                                            $schedule->type = "drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        }
                                    } else if ($date == $previous_date) {
                                        if ($schedule->shift_finishes_next_day == 1) {
                                            $schedule->type = "drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        }
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
        return $this->data['schedules'];
    }

    function dates_in_range(string $start_date, string $end_date, array $dates): bool
    {
        foreach ($dates as $date) {
            if ($date >= $start_date & $date <= $end_date) {
                return true;
            }
        }
        return false;
    }

    public function getWeeklyStats($user_ids)
    {

        $currentDate = Carbon::now();
        $startOfWeek = $currentDate->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $currentDate->copy()->endOfWeek(Carbon::FRIDAY);
        $dates = array();
        while ($startOfWeek->lte($endOfWeek)) {
            array_push($dates, $startOfWeek->toDateString());
            $startOfWeek->addDay();
        }

        $schedule_id_arr = array();

        //-------------------New code----------------------------

        $schedules = $this->getWeeklyScheduleInfo($user_ids, $dates, 1, "all");
        $totalCompletedRides = 0;
        $totalOnGoingRides = 0;
        $totalNotStartedRides = 0;
        $totalStartedRides = 0;

        foreach ($schedules as $schedule) {
            $rideStatus = $this->checkRideStatus($schedule['id'], $schedule['type'], $schedule['date']);

            switch ($rideStatus['id']) {
                case 8:
                    // Complete Ride
                    $totalCompletedRides++;
                    break;

                case 7:
                    // Ongoing Ride (All picked)
                    $totalOnGoingRides++;
                    break;

                case 9:
                    // Not Started Ride
                    $totalNotStartedRides++;
                    break;

                case 6:
                    // Not Started Ride
                    $totalStartedRides++;
                    break;

                default:
                    // Handle other statuses if necessary
                    break;
            }
        }

        $schedule_id_arr['total_Completed_Rides'] = $totalCompletedRides;
        $schedule_id_arr['total_OnGoing_Rides'] = $totalOnGoingRides;
        $schedule_id_arr['total_NotStarted_Rides'] = $totalNotStartedRides;
        $schedule_id_arr['total_Started_Rides'] = $totalStartedRides;

        $this->data['weeklystats'] = $schedule_id_arr;

        return $this->data['weeklystats'];





        //-----------------------old code---------------------------

        // $previous_date = Carbon::createFromFormat('Y-m-d', min($dates))->subDay()->format('Y-m-d');

        // $schedules = Schedule::where(function ($query) use ($dates, $previous_date) {
        //     $query->where(function ($query) use ($dates) {
        //         $query->whereIn('date', $dates);

        //         $query->exists();
        //     });
        //     $query->orwhere(function ($query) {
        //         $query->where('is_repeat', 1);
        //         $query->where('end_date', '>', now());
        //     });
        //     $query->orwhere(function ($query) use ($dates) {
        //         $query->where('is_repeat', 1);
        //         $query->where('end_date', '>', min($dates));
        //         $query->where('end_date', '<', max($dates));
        //     });
        //     $query->orwhere(function ($query) use ($previous_date) {
        //         $query->where('date', $previous_date);
        //         $query->where('shift_finishes_next_day', 1);
        //     });
        // });


        // $schedules = $schedules->whereIn('driver_id', $user_ids);


        // $holidays = Holiday::whereIn('date', $dates)->pluck('date');

        // $schedules = $schedules->get();


        // foreach ($schedules as $schedule) {
        //     $exc_dates = array();
        //     if ($schedule->excluded_dates) {
        //         foreach (json_decode($schedule->excluded_dates) as $exc_date) {
        //             array_push($exc_dates, Carbon::createFromFormat('Y-m-d', $exc_date));
        //         }
        //     }

        //     $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
        //     $date = $current_date->copy()->format('Y-m-d');

        //     // if (!in_array($current_date->copy(), $public_dates)) {
        //     if (in_array($date, $dates)) {
        //         if ($schedule->shift_type_id == 2) {
        //             $schedule_id_arr['pick_drop'] = array_key_exists("pick_drop", $schedule_id_arr) ? $schedule_id_arr['pick_drop'] + 1 : 1;
        //         } else if ($schedule->shift_type_id == 1) {
        //             $schedule_id_arr['pick'] = array_key_exists("pick", $schedule_id_arr) ? $schedule_id_arr['pick'] + 1 : 1;
        //         } else if ($schedule->shift_type_id == 3) {
        //             $schedule_id_arr['drop'] = array_key_exists("drop", $schedule_id_arr) ? $schedule_id_arr['drop'] + 1 : 1;
        //         }
        //     }
        //     if ($date == $previous_date) {
        //         if ($schedule->shift_finishes_next_day == 1) {
        //             $schedule_id_arr['drop'] = array_key_exists("drop", $schedule_id_arr) ? $schedule_id_arr['drop'] + 1 : 1;
        //         }
        //     }
        //     // }
        //     if ($schedule->is_repeat == 1) {
        //         if ($schedule->reacurrance == 0) {
        //             $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date)->addDays($schedule->repeat_time);
        //             while ($current_date < $schedule->end_date) {
        //                 $date = $current_date->format('Y-m-d');
        //                 // if (!in_array($current_date, $public_dates)) {
        //                 if (!in_array($current_date, $exc_dates)) {
        //                     $schedule->date = $current_date->copy()->format('Y-m-d');
        //                     if (in_array($date, $dates)) {
        //                         if ($schedule->shift_type_id == 2) {
        //                             $schedule_id_arr['pick_drop'] = array_key_exists("pick_drop", $schedule_id_arr) ? $schedule_id_arr['pick_drop'] + 1 : 1;
        //                         } else if ($schedule->shift_type_id == 1) {
        //                             $schedule_id_arr['pick'] = array_key_exists("pick", $schedule_id_arr) ? $schedule_id_arr['pick'] + 1 : 1;
        //                         } else if ($schedule->shift_type_id == 3) {
        //                             $schedule_id_arr['drop'] = array_key_exists("drop", $schedule_id_arr) ? $schedule_id_arr['drop'] + 1 : 1;
        //                         }
        //                     } else if ($date == $previous_date) {
        //                         if ($schedule->shift_finishes_next_day == 1) {
        //                             $schedule_id_arr['drop'] = array_key_exists("drop", $schedule_id_arr) ? $schedule_id_arr['drop'] + 1 : 1;
        //                         }
        //                     }
        //                 }
        //                 // }
        //                 $current_date = $current_date->addDays($schedule->repeat_time);
        //             }
        //         } else if ($schedule->reacurrance == 1) {
        //             $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
        //             $scheduleDate = $current_date->copy();
        //             while ($current_date->copy()->startOfWeek() < $schedule->end_date) {
        //                 $current_date = $current_date->copy()->startOfWeek() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfWeek();
        //                 $endofthisweek = $current_date->copy()->endOfWeek();
        //                 if ($this->dates_in_range($current_date->copy()->format('Y-m-d'), $endofthisweek->copy()->format('Y-m-d'), $dates) == true) {
        //                     while ($current_date->copy() < $endofthisweek & $current_date->copy() < $schedule->end_date) {
        //                         $date = $current_date->format('Y-m-d');
        //                         $day_name = $current_date->copy()->format('D');
        //                         // if (!in_array($current_date, $public_dates)) {
        //                         if (!in_array($current_date, $exc_dates)) {
        //                             $schedule->date = $current_date->copy()->format('Y-m-d');
        //                             if (in_array($date, $dates) & in_array(strtolower($day_name), json_decode($schedule->occurs_on))) {
        //                                 if ($schedule->shift_type_id == 2) {
        //                                     $schedule_id_arr['pick_drop'] = array_key_exists("pick_drop", $schedule_id_arr) ? $schedule_id_arr['pick_drop'] + 1 : 1;
        //                                 } else if ($schedule->shift_type_id == 1) {
        //                                     $schedule_id_arr['pick'] = array_key_exists("pick", $schedule_id_arr) ? $schedule_id_arr['pick'] + 1 : 1;
        //                                 } else if ($schedule->shift_type_id == 3) {
        //                                     $schedule_id_arr['drop'] = array_key_exists("drop", $schedule_id_arr) ? $schedule_id_arr['drop'] + 1 : 1;
        //                                 }
        //                             } else if ($date == $previous_date & in_array(strtolower($day_name), json_decode($schedule->occurs_on))) {
        //                                 if ($schedule->shift_finishes_next_day == 1) {
        //                                     $schedule_id_arr['drop'] = array_key_exists("drop", $schedule_id_arr) ? $schedule_id_arr['drop'] + 1 : 1;
        //                                 }
        //                             }
        //                         }
        //                         // }
        //                         $current_date = $current_date->copy()->addDay();
        //                     }
        //                     $current_date = $current_date->copy()->subDay();
        //                 }
        //                 $current_date = $current_date->copy()->addWeeks($schedule->repeat_time);
        //             }
        //         } else if ($schedule->reacurrance == 2) {
        //             $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
        //             $scheduleDate = $current_date->copy();
        //             while ($current_date->copy()->startOfMonth() < $schedule->end_date) {
        //                 $current_date = $current_date->copy()->startOfMonth() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfMonth();
        //                 // $current_date = $current_date->copy()->addDays($schedule->occurs_on);
        //                 $endofthismonth = $current_date->copy()->endOfMonth();
        //                 if ($this->dates_in_range($current_date->copy()->format('Y-m-d'), $endofthismonth->copy()->format('Y-m-d'), $dates) == true) {
        //                     while ($current_date->copy() < $endofthismonth & $current_date->copy() < $schedule->end_date) {
        //                         $date = $current_date->format('Y-m-d');
        //                         // if (!in_array($current_date, $public_dates)) {
        //                         if (!in_array($current_date, $exc_dates)) {
        //                             $schedule->date = $current_date->copy()->format('Y-m-d');
        //                             if (in_array($date, $dates)) {
        //                                 if ($schedule->shift_type_id == 2) {
        //                                     $schedule_id_arr['pick_drop'] = array_key_exists("pick_drop", $schedule_id_arr) ? $schedule_id_arr['pick_drop'] + 1 : 1;
        //                                 } else if ($schedule->shift_type_id == 1) {
        //                                     $schedule_id_arr['pick'] = array_key_exists("pick", $schedule_id_arr) ? $schedule_id_arr['pick'] + 1 : 1;
        //                                 } else if ($schedule->shift_type_id == 3) {
        //                                     $schedule_id_arr['drop'] = array_key_exists("drop", $schedule_id_arr) ? $schedule_id_arr['drop'] + 1 : 1;
        //                                 }
        //                             } else if ($date == $previous_date) {
        //                                 if ($schedule->shift_finishes_next_day == 1) {
        //                                     $schedule_id_arr['drop'] = array_key_exists("drop", $schedule_id_arr) ? $schedule_id_arr['drop'] + 1 : 1;
        //                                 }
        //                             }
        //                         }
        //                         // }
        //                         $current_date = $current_date->copy()->addDays($schedule->occurs_on);
        //                     }
        //                     $current_date = $current_date->copy()->subDays($schedule->occurs_on);
        //                 }
        //                 $current_date = $current_date->copy()->addMonths($schedule->repeat_time);
        //             }
        //         }
        //         }
        //     }

        //     $this->data['weeklystats'] = $schedule_id_arr;

        //     return $this->data['weeklystats'];
    }
}
