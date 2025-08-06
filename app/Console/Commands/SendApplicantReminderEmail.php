<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Carbon\Carbon;
use App\Models\HrmsApplicantReminder;
use App\Models\User;
use App\Mail\ReminderMail;
use DB;
use Mail;
use Illuminate\Support\Facades\Config;
use App\Jobs\reminderSendEmailJob;


class SendApplicantReminderEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applicant:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails to applicants for interview';

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

                $reminders = HrmsApplicantReminder:: whereDate('date', $today)->where('status', 0)->with(['template', 'applicant' => function ($query){
                             $query->select('id', 'first_name', 'last_name', 'email');
                     }])->get();

                 if (isset($reminders)) {
                    foreach ($reminders as $reminder) {
                        $email = $reminder->applicant->email;


                       if ($reminder->template) {

                            $reminderData = [
                                'title' => $reminder->template->title,
                                'description' => $reminder->template->content,
                                'name' => $reminder->applicant->first_name,
                                'header_image' => $reminder->template->header_image,
                                'background_image' => $reminder->template->background_image,
                                'watermark' => $reminder->template->watermark,
                                'footer_image' => $reminder->template->footer_image,
                            ];

                        }else{
                            
                            $reminderData = [
                                'title' => $reminder->title,
                                'description' => $reminder->description,
                                'name' => $reminder->applicant->first_name,
                            ];
                        }

                            // $reminderData = [
                            //     'title' => $reminder->title,
                            //     'description' => $reminder->description,
                            //     'name' => $reminder->applicant->first_name,
                            // ];

                        DB::setDefaultConnection('mysql');
                        dispatch(new reminderSendEmailJob($email, $reminderData));
                        $reminder->status =  1;
                        $reminder->save();
                    }
                 }

            }

        }

    }
}
