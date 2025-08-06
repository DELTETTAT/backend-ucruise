<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class RunMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for update the clients database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //$clients = Client::get();
        $clients = User::whereNotNull('database_name')->get();
        foreach ($clients as $key => $client) {
           
            if ($client) {

                // $this->info($client->database_name);
                $database_name =  $client->database_name;

                $default = [
                    "driver" => env("DB_CONNECTION", "mysql"),
                    "host" => env("DB_HOST"),
                    "port" => env("DB_PORT"),
                    "database" => $database_name,
                    "username" => env("DB_USERNAME"),
                    "password" => env("DB_PASSWORD"),
                    "charset" => "utf8mb4",
                    "collation" => "utf8mb4_unicode_ci",
                    "prefix" => "",
                    "prefix_indexes" => true,
                    "strict" => false,
                    "engine" => null,
                ];

                Config::set("database.connections.$database_name", $default);
                Config::set("client_id", 1);
                Config::set("client_connected", true);
                DB::setDefaultConnection($database_name);
                DB::purge($database_name);


                Artisan::call('migrate', ['--database' => $database_name]);

                DB::disconnect($database_name);
                $this->info("migrate database end: {$database_name}!");
            }
        }
    }
}
