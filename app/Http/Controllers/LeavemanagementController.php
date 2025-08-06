<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Leave;
use App\Models\Schedule;
use App\Models\ScheduleCarer;
use App\Models\ScheduleCarerStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LeavemanagementController extends Controller
{
    // leave listing--------------------------------------
    public function getLeaveRequests()
    {
        return view('leaves.index');
    }
    public function getLeaveRequestsAjax(Request $request)
    {

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $this->data['leaveRequests'] = Leave::with('staff')
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->get();

        return response()->json(["leaveRequests" => $this->data['leaveRequests']], 200);
    }

    // approve leave ----------------------------------------------

    public function approveLeave($id)
    {
        $leaveRequest = Leave::find($id);
        $user_ids = [$leaveRequest->staff_id];

        $startDate = Carbon::parse($leaveRequest->start_date);

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


        foreach ($schedules as $schedule) {
            if ($leaveRequest->type == 1) {
                // If leave type is 1, set type as "pick" and "drop"
                $type = "pick";
                $type2 = "drop";
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
            $schedule_carer_status->cancel_message = 'absent';
            $schedule_carer_status->save();

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
                $schedule_carer_status2->cancel_message = 'absent';
                $schedule_carer_status2->save();
            }
        }

        $leaveRequest->status = 1;
        $leaveRequest->save();
        return redirect()->back()->with('success', 'Approved leave');
    }


    // reject leave----------------------------------------------------------
    public function rejectLeave($id)
    {
        $leaveRequest = Leave::find($id);
        $leaveRequest->status = 2;
        $leaveRequest->save();
        return redirect()->back()->with('success', 'Rejected leave');
    }

    // weekly schedules---------------------------------------------------------------


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
                //$leaves = Leave::where('status', 'Approved')->whereIn('date', $dates)->whereIn('staff_id', $user_ids)->pluck('date', 'staff_id');
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

            ->with('driver');
        // ->with(['carers' => function ($q) {
        // $q->with('user');
        // // ->groupBy('carer_id');
        // }]);




        if ($shift_type_id) {
            if ($shift_type_id != 'all') {
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
            // }
            if ($schedule->is_repeat == 1) {
                if ($schedule->reacurrance == 0) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date)->addDays($schedule->repeat_time);
                    while ($current_date < $schedule->end_date) {
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
                    while ($current_date->copy()->startOfWeek() < $schedule->end_date) {
                        $current_date = $current_date->copy()->startOfWeek() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfWeek();
                        $endofthisweek = $current_date->copy()->endOfWeek();
                        if ($this->dates_in_range($current_date->copy()->format('Y-m-d'), $endofthisweek->copy()->format('Y-m-d'), $dates) == true) {
                            while ($current_date->copy() < $endofthisweek & $current_date->copy() < $schedule->end_date) {
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
                    while ($current_date->copy()->startOfMonth() < $schedule->end_date) {
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
    public function staffSchedules($user_ids, $dates, $clientStaff, $shift_type_id){
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


        // Old code
        // $schedules = $schedules->with('shiftType')->with('driver')->with(['carers' => function ($q) {
        //     $q->with('user');  

        // }]);

        // New code

        $schedules = $schedules->with('shiftType')

            ->with('driver');
        // ->with(['carers' => function ($q) {
        // $q->with('user');
        // // ->groupBy('carer_id');
        // }]);




        if ($shift_type_id) {
            if ($shift_type_id != 'all') {
                $schedules = $schedules->where('shift_type_id', $shift_type_id);
            }
        }

        $holidays = Holiday::whereIn('date', $dates)->pluck('date');

        $this->data['schedules'] = $schedules->get();
        return $this->data['schedules'];

    }
}
