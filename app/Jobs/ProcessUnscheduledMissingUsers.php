<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Leave;
use App\Models\Checking;
use App\Models\MissingSchedule;
use Carbon\Carbon;
use Mail;

class ProcessUnscheduledMissingUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

     protected $unassignedUsers, $assignusers, $shifttype, $shiftFinishesNextDay, $scheduleCreated,$db;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($unassignedUsers, $assignusers, $shifttype, $shiftFinishesNextDay, $scheduleCreated, $db)
    {
        $this->unassignedUsers = $unassignedUsers;
        $this->assignusers = $assignusers;
        $this->shifttype = $shifttype;
        $this->shiftFinishesNextDay = $shiftFinishesNextDay;
        $this->scheduleCreated = $scheduleCreated;
        $this->db= $db;
    }
 

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
    //    \Log::info($this->unassignedUsers);
    //    \Log::info("=============================================");

    //    \Log::info( $this->assignusers);
    //    \Log::info("=============================================");

    //        \Log::info( $this->scheduleType);
    //    \Log::info("=============================================");


    //        \Log::info($this->shiftFinishesNextDay);
    //    \Log::info("=============================================");

    //        \Log::info($this->scheduleCreated);
    //    \Log::info("=============================================");
  
      
        if ($this->scheduleCreated === "No") {
            \Log::info('shedual not created yet rohit'); 
            }
            $this->connectDB($this->db);
            $shiftType = $this->shifttype;
           \Log::info('ProcessUnscheduledMissingUsers job started at rohit');
                $sentUsers = [];

                if (!empty($this->unassignedUsers)) {
                    $sentUsers = array_column($this->unassignedUsers, 'user_id');
                        foreach( $sentUsers as $unshineduser){
                            $missuser = User::find($unshineduser);
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
             Mail::send('email.driver_assigned', ['unassignedEmployees' => $this->unassignedUsers], function ($message) {
                $message->to('developerphp1995@gmail.com')
                        ->subject('Driver Unassignment Notification');
            });
        }

      

                if (!empty($this->assignusers)) {
                    foreach ($this->assignusers as $assign) {
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
                ->where('users.shift_type', $this->shiftFinishesNextDay)
                ->where('users.cab_facility', 1)
                ->whereIn('users.status', [1, 3, 4])
                ->where('users.close_account', 1)
                ->get();


            $today = now()->toDateString();
            $currentHour = now()->hour;

            $leaveStaffIds = Leave::whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->where('status', 1)
                ->get()
                ->filter(function ($leave) use ($currentHour) {
                    if ($leave->type == 1) return true;
                    if ($leave->type == 2 && $currentHour < 12) return true;
                    if ($leave->type == 3 && $currentHour >= 12) return true;
                    return false;
                })
                ->pluck('staff_id')
                ->unique()
                ->toArray();

            foreach ($unshinedUsersList as &$user) {
                $missingReason = [];

                if (in_array($user->id, $leaveStaffIds)) {
                    $missingReason[] = 'On leave';
                }

                if (empty($user->latitude) || empty($user->longitude)) {
                    $missingReason[] = 'Missing location lat & long';
                }

                if (!empty($missingReason)) {
                    $user->missing_reason = implode(', ', $missingReason);
                }
            }

            foreach ($unshinedUsersList as $missuser) {
                if ($missuser) {
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
                        'schedule_type'  => $this->scheduleType == 1 ? 'pick' : 'drop',
                        'missing_reason' => $missuser->missing_reason ?? null,
                        'date'           => Carbon::today()->toDateString(),
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
           }
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
