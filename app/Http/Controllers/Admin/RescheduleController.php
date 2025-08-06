<?php

namespace App\Http\Controllers\Admin;

use App\Models\Reschedule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\LeavemanagementController;
use App\Models\CompanyAddresse;
use App\Models\CompanyDetails;
use App\Models\RescheduleHistory;
use App\Models\Schedule;
use App\Models\ScheduleCarer;
use App\Models\ScheduleTask;
use App\Models\SubUser;
use App\Models\SubUserAddresse;
use App\Models\Vehicle;
use Carbon\Carbon;
use GuzzleHttp\Client;



use Illuminate\Support\Facades\Session;

class RescheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['reschedules'] = Reschedule::where('status', 1)->with('user')->get();

        return view("reschedules.index", $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $reschedule = new Reschedule();
        $reschedule->user_id =  auth('sanctum')->user()->id;
        $reschedule->address = $request->address;
        $reschedule->latitude = $request->latitude;
        $reschedule->longitude = $request->longitude;
        $reschedule->status = 0;
        $reschedule->save();

        return response()->json(['success' => true, "message" => "Reschedule Request Submitted successfully"], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Reschedule  $reschedule
     * @return \Illuminate\Http\Response
     */
    public function show(Reschedule $reschedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Reschedule  $reschedule
     * @return \Illuminate\Http\Response
     */
    public function edit(Reschedule $reschedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reschedule  $reschedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reschedule $reschedule)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Reschedule  $reschedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reschedule $reschedule)
    {
        //
    }

    public function acceptReschedule($SId, $reschedule_id)
    {
        try {
            $leavemanagementController = new LeavemanagementController();
            $reschedule = Reschedule::find($reschedule_id);

            $old_schedules = $leavemanagementController->staffSchedules([$reschedule->user_id], [$reschedule->date], 2, 'all');
            // removing the employee from all the schedules   


            foreach ($old_schedules as $old_schedule) {
                $old_schedule = Schedule::find($old_schedule['id']);
                if ($old_schedule && $old_schedule['is_repeat'] == 1) {
                    $old_new_schedule = new Schedule();
                    $old_new_schedule['date'] = $reschedule->date;
                    $old_new_schedule['driver_id'] = $old_schedule['driver_id'];
                    $old_new_schedule['vehicle_id'] = $old_schedule['vehicle_id'];
                    $old_new_schedule['schedule_parent_id'] = $old_schedule['id'];
                    $old_new_schedule['start_time'] = $old_schedule['start_time'];
                    $old_new_schedule['end_time'] = $old_schedule['end_time'];
                    $old_new_schedule['break_time_in_minutes'] = $old_schedule['break_time_in_minutes'];
                    $old_new_schedule['is_repeat'] = $old_schedule['is_repeat'];
                    $old_new_schedule['is_splitted'] = $old_schedule['is_splitted'];
                    $old_new_schedule['reacurrance'] = $old_schedule['reacurrance'];
                    $old_new_schedule['repeat_time'] = $old_schedule['repeat_time'];
                    $old_new_schedule['occurs_on'] = $old_schedule['occurs_on'];
                    $old_new_schedule['end_date'] = $old_schedule['end_date'];
                    $old_new_schedule['address'] = $old_schedule['address'];
                    $old_new_schedule['pickup_lat'] = $old_schedule['pickup_lat'];
                    $old_new_schedule['pickup_long'] = $old_schedule['pickup_long'];
                    $old_new_schedule['apartment_no'] = $old_schedule['apartment_no'];
                    $old_new_schedule['is_drop_off_address'] = $old_schedule['is_drop_off_address'];
                    $old_new_schedule['excluded_dates'] = $old_schedule['excluded_dates'];
                    $old_new_schedule['dropoff_lat'] = $old_schedule['dropoff_lat'];
                    $old_new_schedule['dropoff_long'] = $old_schedule['dropoff_long'];
                    $old_new_schedule['drop_off_apartment_no'] = $old_schedule['drop_off_apartment_no'];
                    $old_new_schedule['mileage'] = $old_schedule['mileage'];
                    $old_new_schedule['shift_type_id'] = $old_schedule['shift_type_id'];
                    $old_new_schedule['allowance_id'] = $old_schedule['allowance_id'];
                    $old_new_schedule['additional_cost'] = $old_schedule['additional_cost'];
                    $old_new_schedule['ignore_staff_count'] = $old_schedule['ignore_staff_count'];
                    $old_new_schedule['confirmation_required'] = $old_schedule['confirmation_required'];
                    $old_new_schedule['notify_carer'] = $old_schedule['notify_carer'];
                    $old_new_schedule['add_to_job_board'] = $old_schedule['add_to_job_board'];
                    $old_new_schedule['shift_assignment'] = $old_schedule['shift_assignment'];
                    $old_new_schedule['team_id'] = $old_schedule['team_id'];
                    $old_new_schedule['language_id'] = $old_schedule['language_id'];
                    $old_new_schedule['compliance_id'] = $old_schedule['compliance_id'];
                    $old_new_schedule['competency_id'] = $old_schedule['competency_id'];
                    $old_new_schedule['kpi_id'] = $old_schedule['kpi_id'];
                    $old_new_schedule['distance_from_shift_location'] = $old_schedule['distance_from_shift_location'];
                    $old_new_schedule['distance_from_shift_location'] = $old_schedule['distance_from_shift_location'];
                    $old_new_schedule['instructions'] = $old_schedule['instructions'];
                    $old_new_schedule['locality'] = $old_schedule['locality'];
                    $old_new_schedule['city'] = $old_schedule['city'];
                    $old_new_schedule['latitude'] = $old_schedule['latitude'];
                    $old_new_schedule['longitude'] = $old_schedule['longitude'];
                    $old_new_schedule->save();
                    $old_schedule['end_date'] = $reschedule->date;
                    $old_schedule->save();
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
                $schedule_tasks = ScheduleTask::where('schedule_id', $old_schedule['id'])->get();
                foreach ($schedule_tasks as $schedule_task) {
                    $old_schedule_task = new ScheduleTask();
                    $old_schedule_task->schedule_id = $old_new_schedule['id'];
                    $old_schedule_task->name = $schedule_task['name'];
                    $old_schedule_task->is_mandatory = $schedule_task['is_mandatory'];
                    $old_schedule_task->save();
                }
            }
            // adding the employee to the similar schedule
            $new_schedule = Schedule::find($SId);
            if ($new_schedule && $new_schedule['is_repeat'] == 1) {
                $new_new_schedule = new Schedule();
                $new_new_schedule['date'] = $reschedule->date;
                $new_new_schedule['driver_id'] = $new_schedule['driver_id'];
                $new_new_schedule['vehicle_id'] = $new_schedule['vehicle_id'];
                $new_new_schedule['schedule_parent_id'] = $new_schedule['id'];
                $new_new_schedule['start_time'] = $new_schedule['start_time'];
                $new_new_schedule['end_time'] = $new_schedule['end_time'];
                $new_new_schedule['break_time_in_minutes'] = $new_schedule['break_time_in_minutes'];
                $new_new_schedule['is_repeat'] = $new_schedule['is_repeat'];
                $new_new_schedule['is_splitted'] = $new_schedule['is_splitted'];
                $new_new_schedule['reacurrance'] = $new_schedule['reacurrance'];
                $new_new_schedule['repeat_time'] = $new_schedule['repeat_time'];
                $new_new_schedule['occurs_on'] = $new_schedule['occurs_on'];
                $new_new_schedule['end_date'] = $new_schedule['end_date'];
                $new_new_schedule['address'] = $new_schedule['address'];
                $new_new_schedule['pickup_lat'] = $new_schedule['pickup_lat'];
                $new_new_schedule['pickup_long'] = $new_schedule['pickup_long'];
                $new_new_schedule['apartment_no'] = $new_schedule['apartment_no'];
                $new_new_schedule['is_drop_off_address'] = $new_schedule['is_drop_off_address'];
                $new_new_schedule['excluded_dates'] = $new_schedule['excluded_dates'];
                $new_new_schedule['dropoff_lat'] = $new_schedule['dropoff_lat'];
                $new_new_schedule['dropoff_long'] = $new_schedule['dropoff_long'];
                $new_new_schedule['drop_off_apartment_no'] = $new_schedule['drop_off_apartment_no'];
                $new_new_schedule['mileage'] = $new_schedule['mileage'];
                $new_new_schedule['shift_type_id'] = $new_schedule['shift_type_id'];
                $new_new_schedule['allowance_id'] = $new_schedule['allowance_id'];
                $new_new_schedule['additional_cost'] = $new_schedule['additional_cost'];
                $new_new_schedule['ignore_staff_count'] = $new_schedule['ignore_staff_count'];
                $new_new_schedule['confirmation_required'] = $new_schedule['confirmation_required'];
                $new_new_schedule['notify_carer'] = $new_schedule['notify_carer'];
                $new_new_schedule['add_to_job_board'] = $new_schedule['add_to_job_board'];
                $new_new_schedule['shift_assignment'] = $new_schedule['shift_assignment'];
                $new_new_schedule['team_id'] = $new_schedule['team_id'];
                $new_new_schedule['language_id'] = $new_schedule['language_id'];
                $new_new_schedule['compliance_id'] = $new_schedule['compliance_id'];
                $new_new_schedule['competency_id'] = $new_schedule['competency_id'];
                $new_new_schedule['kpi_id'] = $new_schedule['kpi_id'];
                $new_new_schedule['distance_from_shift_location'] = $new_schedule['distance_from_shift_location'];
                $new_new_schedule['distance_from_shift_location'] = $new_schedule['distance_from_shift_location'];
                $new_new_schedule['instructions'] = $new_schedule['instructions'];
                $new_new_schedule['locality'] = $new_schedule['locality'];
                $new_new_schedule['city'] = $new_schedule['city'];
                $new_new_schedule['latitude'] = $new_schedule['latitude'];
                $new_new_schedule['longitude'] = $new_schedule['longitude'];
                $new_new_schedule->save();

                $new_schedule['end_date'] = $reschedule->date;
                $new_schedule->save();
            }



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
            $schedule_tasks = ScheduleTask::where('schedule_id', $new_schedule['id'])->get();
            foreach ($schedule_tasks as $schedule_task) {
                $new_new_schedule_task = new ScheduleTask();
                $new_new_schedule_task->schedule_id = $new_new_schedule['id'];
                $new_new_schedule_task->name = $schedule_task['name'];
                $new_new_schedule_task->is_mandatory = $schedule_task['is_mandatory'];
                $new_new_schedule_task->save();
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
            $reschedule->status = 1;
            $reschedule->save();
            Session::flash("success", 'Successfully rescheduled');
            return redirect()->route('scheduler.index');
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }


    public function rejectReschedule($id)
    {
        try {
            $reschedule = Reschedule::find($id);
            $reschedule->status = 2;
            $reschedule->save();

            Session::flash("message", "Rejected Successfully");
            return redirect()->back();
        } catch (\Exception $ex) {
            $this->data["msg"] = $ex->getMessage() . " " . $ex->getLine();
            Session::flash("error", $this->data["msg"]);
            return redirect()->back();
        }
    }

    public function similarRoutes($id)
    {

        $reschedule = Reschedule::find($id);
        $rescheduleLatitude = $reschedule->latitude; // Replace with actual attribute name
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
                        // ->orderBy("sub_users.id", "DESC")
                        ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address');
                    // ->distinct('sub_users.id');
                }]);
            }])->get();


        // Replace with actual attribute name
        // $today = now();
        // $startOfWeek = $today->startOfWeek()->format('Y-m-d');
        // $endOfWeek = $today->endOfWeek()->format('Y-m-d');
        // $previousDate = Carbon::createFromFormat('Y-m-d', $startOfWeek)->subDay()->format('Y-m-d');
        // $schedules = Schedule::where(function ($query) use ($startOfWeek, $endOfWeek, $previousDate) {
        //     $query->where(function ($query) use ($startOfWeek, $endOfWeek) {
        //         $query->whereIn('date', range($startOfWeek, $endOfWeek));
        //         $query->exists();
        //     });
        //     $query->orWhere(function ($query) {
        //         $query->where('is_repeat', 1);
        //         $query->where('end_date', '>', now());
        //     });
        //     $query->orWhere(function ($query) use ($startOfWeek, $endOfWeek) {
        //         $query->where('is_repeat', 1);
        //         $query->where('end_date', '>', $startOfWeek);
        //         $query->where('end_date', '<', $endOfWeek);
        //     });
        //     $query->orWhere(function ($query) use ($previousDate) {
        //         $query->where('date', $previousDate);
        //         $query->where('shift_finishes_next_day', 1);
        //     });
        // })->with('carers')->get();


        $radius = 10;
        $filteredSchedules = collect([]);

        foreach ($schedules as $schedule) {
            $scheduleLatitude = $schedule->latitude;
            $scheduleLongitude = $schedule->longitude;


            $this->isLocationWithinRadius($scheduleLatitude, $scheduleLongitude, $rescheduleLatitude, $rescheduleLongitude, $radius, function ($isWithinRadius) use ($schedule, $filteredSchedules) {
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
                    $filteredSchedules->push($schedule);
                }
            });
        }
        $leavemanagementController = new LeavemanagementController();
        $old_schedules = $leavemanagementController->staffSchedules([$reschedule->user_id], [$reschedule->date], 2, 'all');
        $company_details = CompanyAddresse::where('company_id', 1)->whereNull('end_date')->first();


        return view('reschedules.similar', compact('filteredSchedules',  'company_details', 'reschedule', 'old_schedules'));
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

                // Check if the distance is within the specified radius
                $isWithinRadius = $distanceInKm <= $radius;

                // Call the callback function with the result
                $callback($isWithinRadius);
            } else {
                // Handle error
                $callback(false);
            }
        } catch (\Exception $e) {
            // Handle Guzzle HTTP request exception
            $callback(false);
        }
    }

    public function findCityFromLatLng($latitude, $longitude)
    {
        $latLng = $latitude . ',' . $longitude;

        $apiKey = env('GOOGLE_API_KEY'); // Replace with your actual API key
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
                // Extract city from the result
                $city = null;
                foreach ($data['results'][0]['address_components'] as $component) {
                    if (in_array('locality', $component['types'])) {
                        $city = $component['long_name'];
                        break;
                    }
                }

                return $city;
            } else {
                // Handle error
                return null;
            }
        } catch (\Exception $e) {
            // Handle Guzzle HTTP request exception
            return null;
        }
    }
}
