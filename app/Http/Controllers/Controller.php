<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $data;

    /**
     * Contructor Class
     *
     * @void
     */
    public function __construct()
    {
        $this->data = array();
    }

    /**
     * Method to connect to other DB
     *
     * @return response()
     */
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

    public function successResponse($data = [], $message = '')
    {
        return response()->json(['message' => $message, 'data' => $data], Response::HTTP_OK);
    }
    public function warningResponse($data = [], $message = '')
    {
        return response()->json([
            'status' => 'warning',
            'message' => $message,
            'data' => $data
        ], Response::HTTP_BAD_REQUEST);
    }
    /**
     * All request error response return
     *
     * @param array $message
     * @return \Illuminate\Http\JsonResponse
     */

    public function errorResponse($message = 'Something went wrong')
    {
        return response()->json(['message' => $message], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * All request validation error response return
     *
     * @param array $message
     * @return \Illuminate\Http\JsonResponse
     */

    public function validationErrorResponse($message = 'Something went wrong')
    {
        return response()->json(['message' => $message, 'data' => []], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
