<?php

namespace App\Http\Controllers\Superadmin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\{User, Schedule};
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BillingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['drivers'] = User::whereHas("roles", function ($q) {
            $q->whereIn("name", ["driver"]);
        })->get();
        return view('billing.index', $this->data);
    }

    /**
     * Function to get driver total shift within Specific Time Period
     *
     * @return \Illuminate\Http\Response
     */
    public function getBillingInformation(Request $request)
    {
        $startDate = $request->startDate ? Carbon::createFromFormat('Y-m-d', $request->startDate) : Carbon::now()->startOfMonth();
        $endDate = $request->endDate ? Carbon::createFromFormat('Y-m-d', $request->endDate) : Carbon::now()->endOfMonth();

        $user_ids = $request->user_ids;

        $dates = array();
        $months = array();
        $years = array();
        $days = array();
        while ($startDate->lte($endDate)) {
            array_push($days, $startDate->toDateString());
            array_push($dates, $startDate->format('d'));
            array_push($months, $startDate->format('m'));
            array_push($years, $startDate->format('Y'));
            $startDate->addDay();
        }

        $schedule_id_arr = array();
        $schedule_clients = array();

        $schedules = Schedule::whereIn('driver_id', $user_ids)->where(function ($query) use ($dates, $months, $years) {
            $query->where(function ($query) use ($dates, $months, $years) {
                $query->whereIn(DB::raw("DAY(date)"), $dates);

                $query->whereIn(DB::raw("MONTH(date)"), $months);

                $query->whereIn(DB::raw("YEAR(date)"), $years);

                $query->exists();
            });
            $query->orwhere(function ($query) {
                $query->where('is_repeat', 1);
                $query->where('end_date', '>', now());
            });
        });

        // $schedules = $schedules->whereHas('clients', function ($q) use ($user_ids) {

        //     $q->whereIn('client_id', $user_ids);
        // });


        // $schedules = $schedules->with(['clients' => function ($q) {

        //     $q->with('user');
        // }])->with(['carers' => function ($q) {

        //     $q->with('user');
        // }]);

        // if ($request->shift_type_id) {
        //     if ($request->shift_type_id != "all") {
        //         $schedules = $schedules->where('shift_type_id', $request->shift_type_id);
        //     }
        // }

        $schedules = $schedules->get();

        foreach ($schedules as $schedule) {
            $current_date = $schedule->date;
            $year = Carbon::createFromFormat('Y-m-d', $current_date)->format('Y');
            $day = Carbon::createFromFormat('Y-m-d', $current_date)->format('d');
            $month = Carbon::createFromFormat('Y-m-d', $current_date)->format('m');
            if (in_array($year, $years) & in_array($day, $dates) & in_array($month, $months)) {
                array_push($schedule_id_arr, $schedule->toArray());
            }
            if ($schedule->is_repeat == 1) {
                if ($schedule->reacurrance == 0) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date)->addDays($schedule->repeat_time);
                    while ($current_date < $schedule->end_date) {
                        $year = $current_date->format('Y');
                        $day = $current_date->format('d');
                        $month = $current_date->format('m');
                        if (in_array($year, $years) & in_array($day, $dates) & in_array($month, $months)) {
                            $schedule->date = $current_date->format('Y-m-d');
                            array_push($schedule_id_arr, $schedule->toArray());
                        }
                        $current_date = $current_date->addDays($schedule->repeat_time);
                    }
                } else if ($schedule->reacurrance == 1) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                    $scheduleDate = $current_date->copy();
                    while ($current_date->copy()->startOfWeek() < $schedule->end_date) {
                        $current_date = $current_date->copy()->startOfWeek() < $scheduleDate ? $scheduleDate : $current_date->copy()->startOfWeek();
                        $endofthisweek = $current_date->copy()->endOfWeek();
                        if ($this->dates_in_range($current_date, $endofthisweek, $days) == true) {
                            while ($current_date->copy() < $endofthisweek & $current_date->copy() < $schedule->end_date) {
                                $year = $current_date->copy()->format('Y');
                                $day = $current_date->copy()->format('d');
                                $month = $current_date->copy()->format('m');
                                $day_name = $current_date->copy()->format('D');
                                if (in_array($year, $years) & in_array($day, $dates) & in_array($month, $months) & in_array(strtolower($day_name), json_decode($schedule->occurs_on))) {
                                    $schedule->date = $current_date->copy()->format('Y-m-d');
                                    array_push($schedule_id_arr, $schedule->toArray());
                                }
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
                        $current_date = $current_date->copy()->startOfMonth() < $scheduleDate ? $scheduleDate : $current_date->copy()->startOfMonth();
                        // $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                        $endofthismonth = $current_date->copy()->endOfMonth();
                        if ($this->dates_in_range($current_date, $endofthismonth, $days) == true) {
                            while ($current_date->copy() < $endofthismonth & $current_date->copy() < $schedule->end_date) {
                                $year = $current_date->copy()->format('Y');
                                $day = $current_date->copy()->format('d');
                                $month = $current_date->copy()->format('m');
                                $day_name = $current_date->copy()->format('D');
                                if (in_array($year, $years) & in_array($day, $dates) & in_array($month, $months)) {
                                    $schedule->date = $current_date->copy()->format('Y-m-d');
                                    array_push($schedule_id_arr, $schedule->toArray());
                                }
                                $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                            }
                            $current_date = $current_date->copy()->subDays($schedule->occurs_on);
                        }
                        $current_date = $current_date->copy()->addMonths($schedule->repeat_time);
                    }
                }
            }
        }

        foreach($schedule_id_arr as $schedule){
            $schedule_clients[$schedule['driver_id']] = (array_key_exists($schedule['driver_id'],$schedule_clients) ? $schedule_clients[$schedule['driver_id']] : 0) + 1 ;  
        }

        return response()->json(["schedule" => $schedule_clients, "users" => $user_ids], 200);
    }

    function dates_in_range(string $start_date, string $end_date, array $dates): bool
    {
        foreach ($dates as $date) {
            if ($date['date'] > $start_date & $date['date'] < $end_date) {
                return true;
            }
        }
        return false;
    }
}
