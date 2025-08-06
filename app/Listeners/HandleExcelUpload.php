<?php

namespace App\Listeners;

use App\Events\ExcelUploaded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Jobs\ProcessExcelUpload;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\ExcelUploadStaging;
use App\Jobs\ValidateExcelUpload;

class HandleExcelUpload
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\ExcelUploaded  $event
     * @return void
     */
    public function handle(ExcelUploaded $event)
    {
         $databaseInfo = [
            'child_DB' => DB::connection()->getDatabaseName(),
            'company_name' => auth('sanctum')->user()->company_name,
            'uploadId' => $event->uploadId,
         ];
         $defaultDB = env("DB_DATABASE");
         $this->connectDB($defaultDB);
         //ProcessExcelUpload::dispatch($event->uploadId,$databaseInfo)->onQueue('default');
        // $filePath = storage_path('app/uploads/' . $event->fileName);
         $fileName =  $event->fileName;
        ValidateExcelUpload::dispatch($databaseInfo, $fileName)->onQueue('default');
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
        DB::purge($db_name);
        DB::reconnect($db_name);
        DB::setDefaultConnection($db_name);
        Config::set("client_id", 1);
        Config::set("client_connected", true);
    }
}
