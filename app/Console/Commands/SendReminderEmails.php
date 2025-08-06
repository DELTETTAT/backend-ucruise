<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\HrmsReminder;
use App\Models\User;
use App\Mail\ReminderMail;
use DB;
use Mail;
use Illuminate\Support\Facades\Config;
use App\Jobs\reminderSendEmailJob;

class SendReminderEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Reminders Emails';

    /**
     * Execute the console command.
     *
     * @return int
     */

     public function connectDB($db_name){
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

    public function handle()
    {
        $admin = User::orderBy('id', 'desc')->whereNotNull('database_name')->get()->toArray();

        foreach ($admin as $user) {

            $databaseName = $user['database_name'];
            $dbExists = DB::select("SHOW DATABASES LIKE '$databaseName'");

            if($dbExists) {

                $this->connectDB($databaseName);

                $today = Carbon::today()->toDateString();
                // $reminders = HrmsReminder:: whereDate('date', $today)->where('status', 0)->with(['employees' => function ($query){
                //              $query->select('id', 'first_name', 'last_name', 'email');
                //      }])->get();

                //  if (isset($reminders)) {
                //     foreach ($reminders as $reminder) {
                //         $email = $reminder->employees->email;

                //         $reminderData = [
                //             'title' => $reminder->title,
                //             'description' => $reminder->description,
                //             'name' => $reminder->employees->first_name,
                //         ];

                //         DB::setDefaultConnection('mysql');
                //         dispatch(new reminderSendEmailJob($email, $reminderData));
                //         $reminder->status =  1;
                //         $reminder->save();
                //     }
                //  }


                $reminders = HrmsReminder::whereDate('date', $today)->where('status', 0)->get();

                if (isset($reminders)) {
                   foreach ($reminders as $reminder) {

                       $role_ids = is_array($reminder->target) ? $reminder->target : explode(',',$reminder->target);

                       $employees_ids = DB::table('role_sub_user')->whereIn('role_id', $role_ids)->get();

                       foreach ($employees_ids as $key => $employee) {
                        $empData =  User::find($employee->sub_user_id);

                            $email = $empData->email;
                            $reminderData = [
                                'title' => $reminder->title,
                                'description' => $reminder->description,
                                'name' => $empData->first_name,
                            ];

                            $childDBname = DB::connection()->getDatabaseName();
                            DB::setDefaultConnection('mysql');
                            dispatch(new reminderSendEmailJob($email, $reminderData));

                            $this->connectDB($childDBname);

                    }

                       $reminder->status =  1;
                       $reminder->save();

                   }
                }

            }

        }



    }
}
