<?php

namespace App\Http\Controllers\Api\CronJobs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\SubUser;
use App\Models\HrmsTimeAndShift;
use App\Models\Resignation;
use App\Models\EmployeeSeparation;


class CronjobsController extends Controller
{
    /**
     * @OA\post(
     * path="/uc/api/cronjobs/accountCloseDeactive",
     * operationId="accountCloseDeactive",
     * tags={"Cron jobs"},
     * summary="Get accountClose Request",
     *   security={ {"Bearer": {} }},
     *    description="Get accountClose Request",
     *      @OA\Response(
     *          response=201,
     *          description="accountClose Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="accountClose Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */


    public function accountCloseDeactive(Request $request){

        $dbs = ['UC_logisticllp', 'UC_unify_test','UC_unifytechsolutions']; //live db multiple
        foreach ($dbs as $db) {
            $this->connectDB($db);
            $resignations = Resignation::get()->toArray();

            if (empty($resignations)) {
                info("No resignations in DB: $db — skipping.");
                continue;
            }

            foreach( $resignations as  $resignation){
                $user = User::where('id', $resignation['user_id'])->first();
                if ($user) {

                    if ($user->status != 3) {
                        continue; // Skip to next resignation
                    }

                    if($user->status ==3){

                        $employeeSeparation = EmployeeSeparation::where('user_id', $resignation['user_id'])->latest()->first();

                        if ($employeeSeparation && Carbon::parse($employeeSeparation->last_working_date)->isToday()) {

                            $user = User::find($resignation['user_id']);
                            $subuser = SubUser::find($resignation['user_id']);
                            if($user && $user->close_account != 0) {
                                $user->close_account = 0;
                                $user->save();
                                // sub users table
                                $subuser->close_account = 0;
                                $subuser->save();
                            }
              
                        }

                    }
                }
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
