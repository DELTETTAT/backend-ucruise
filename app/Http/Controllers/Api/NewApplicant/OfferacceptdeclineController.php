<?php

namespace App\Http\Controllers\Api\NewApplicant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mail;
use Illuminate\Support\Facades\Config;
use DB;
use App\Models\NewApplicant;
use App\Models\ApplicantOfferedHistory;

class OfferacceptdeclineController extends Controller
{

       ///
    public function offeracceptandDecline($applicantId, $acceptType, $database, $uniqueID){

        try {
            $applicantId = base64_decode($applicantId);
            $acceptType = base64_decode($acceptType);
            $database = base64_decode($database);
            $uniqueID = base64_decode($uniqueID);
           // $applicantemail = base64_decode($applicantemail);

            $this->connectToDatabase($database);

           //$newapplicant =  NewApplicant::find($applicantId);
           $newapplicant =  ApplicantOfferedHistory::where('unique_id',$uniqueID)->where('applicant_id',$applicantId)->latest()->first();
           $dataform = [
                'db_name' => $database,
                'applicant_id' => $applicantId,
                'unique_id' => $uniqueID,
            ];

        if (!$newapplicant) {
            $message = "Applicant not found";
            return view('email.offer_accept', ['message' => $message, 'dataform' => null]);
        }

        if($newapplicant->is_accept == "Accepted"){
            info('alredy accept'.$newapplicant->is_accept);
            $message = "Session Expired";
            return view('email.offer_accept', ['message' => $message, 'dataform' => null]);

        }elseif ($newapplicant->is_accept == "Not Accepted") {
            $message = "Session Expired";
            return view('email.offer_accept', ['message' => $message, 'dataform' => null]);
        }
        else{

            if($acceptType =='Accept'){
                $newapplicant->is_accept = 1;
                $newapplicant->save();
                $message = "Offer Request Accepted";
                return view('email.offer_accept', ['message' => $message, 'dataform' => null]);
            }else{
                // $newapplicant->is_accept = 2;
                // $newapplicant->save();
                $message = "Offer Request Declined";
                return view('email.offer_accept', ['message' => null, 'dataform' => $dataform]);
            }
        }


        } catch (\Throwable $th) {
            $message = $th->getMessage();
            return view('email.offer_accept', ['message' => $message, 'dataform' => null]);
        }

    }///

    public function connectToDatabase($database)
    {
        $default = [
            "driver" => env("DB_CONNECTION", "mysql"),
            "host" => env("DB_HOST"),
            "port" => env("DB_PORT"),
            "database" => $database,
            "username" => env("DB_USERNAME"),
            "password" => env("DB_PASSWORD"),
            "charset" => "utf8mb4",
            "collation" => "utf8mb4_unicode_ci",
            "prefix" => "",
            "prefix_indexes" => true,
            "strict" => false,
            "engine" => null,
        ];
        Config::set('database.connections.mysql', $default);
        DB::purge('mysql');

    }



    public function offeredRejected(Request $request){
        try {
            $applicant_id = $request->applicant_id;
            $db_name = $request->db_name;
            $unique_id = $request->unique_id;

            $this->connectToDatabase($db_name);

            $newapplicant =  ApplicantOfferedHistory::where('unique_id',$unique_id)->where('applicant_id',$applicant_id)->latest()->first();
            $newapplicant->is_accept = 2;
            $newapplicant->reason = $request->reason;
            $newapplicant->save();

            $message = "Offer Request Declined";
            return view('email.offer_accept', ['message' => $message, 'dataform' => null]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
    }
}
