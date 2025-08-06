<?php

namespace App\Observers;

use App\Models\HrmsAnnouncement;
use App\Models\SubUser;
use App\Models\User;
use App\Mail\AnnouncementMail;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendAnnouncementEmail;
use Illuminate\Support\Facades\Config;
use DB;

class HrmsAnnouncementObserver
{
    /**
     * Handle the HrmsAnnouncement "created" event.
     *
     * @param  \App\Models\HrmsAnnouncement  $hrmsAnnouncement
     * @return void
     */
    public function created(HrmsAnnouncement $hrmsAnnouncement)
    {
       
        $users = User::whereNotNull('email')->get();
        $temp_DB_name = DB::connection()->getDatabaseName();

        // Switch to parent DB
        $default_DBName = env("DB_DATABASE");
        $this->connectDB($default_DBName);

        SendAnnouncementEmail::dispatch($hrmsAnnouncement->toArray(), $users->toArray());
        $this->connectDB($temp_DB_name);
    }



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


    /**
     * Handle the HrmsAnnouncement "updated" event.
     *
     * @param  \App\Models\HrmsAnnouncement  $hrmsAnnouncement
     * @return void
     */
    public function updated(HrmsAnnouncement $hrmsAnnouncement)
    {
        //
    }

    /**
     * Handle the HrmsAnnouncement "deleted" event.
     *
     * @param  \App\Models\HrmsAnnouncement  $hrmsAnnouncement
     * @return void
     */
    public function deleted(HrmsAnnouncement $hrmsAnnouncement)
    {
        //
    }

    /**
     * Handle the HrmsAnnouncement "restored" event.
     *
     * @param  \App\Models\HrmsAnnouncement  $hrmsAnnouncement
     * @return void
     */
    public function restored(HrmsAnnouncement $hrmsAnnouncement)
    {
        //
    }

    /**
     * Handle the HrmsAnnouncement "force deleted" event.
     *
     * @param  \App\Models\HrmsAnnouncement  $hrmsAnnouncement
     * @return void
     */
    public function forceDeleted(HrmsAnnouncement $hrmsAnnouncement)
    {
        //
    }
}
