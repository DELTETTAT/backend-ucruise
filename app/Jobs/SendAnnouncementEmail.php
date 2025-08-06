<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Mail\AnnouncementMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\HrmsAnnouncement;

use App\Http\Controllers\Controller;
use DB;



class SendAnnouncementEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $announcement, $users;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $announcement, $users)
    {
        $this->announcement = $announcement; // Store as an array
        $this->users = $users; // Store as an array
    }
     

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $users = collect($this->users)->map(function ($user) {
            return (object) $user;
        });

        // $temp_DB_name = DB::connection()->getDatabaseName();

        //connecting to parent DB
        // $default_DBName = env("DB_DATABASE");
        // $controller = new Controller();
        // $controller->connectDB($default_DBName);
        
        foreach ($users as $user) {
            Mail::to($user->email)->queue(new AnnouncementMail($this->announcement, $user));
        }
        // $controller->connectDB($temp_DB_name);

    }
}
