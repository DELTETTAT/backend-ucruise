<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Leave;
use Illuminate\Support\Facades\Config;
use DB;

class LeaveacceptdeclineController extends Controller
{
    public function handleLeaveAction($employeeId, $action, $database, $leaveId)
    {
        try {
            $employeeId = base64_decode($employeeId);
            $action = base64_decode($action);
            $database = base64_decode($database);
            $leaveId = base64_decode($leaveId);

            $this->connectToDatabase($database);

            $leave = Leave::where('id', $leaveId)
                ->where('staff_id', $employeeId)
                ->first();

            if (!$leave) {
                $message = "Leave request not found";
                return view('email.leaveapplyemail', ['statusMessage' => $message, 'dataform' => null, 'emailData' => null]);
            }

            if ($leave->status == 1) {
                $message = "This leave request has already been approved";
            } elseif ($leave->status == 2) {
                $message = "This leave request has already been rejected";
            } else {
                if ($action == '1') {
                    $leave->status = 1;
                    $leave->save();
                    $message = "Leave request approved successfully";
                } else {
                    $dataform = [
                        'db_name' => $database,
                        'employee_id' => $employeeId,
                        'leave_id' => $leaveId,
                    ];
                    return view('email.leaveapplyemail', ['statusMessage' => null, 'dataform' => $dataform, 'emailData' => null]);
                }
            }

            return view('email.leaveapplyemail', ['statusMessage' => $message, 'dataform' => null, 'emailData' => null]);
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            return view('email.leaveapplyemail', ['statusMessage' => $message, 'dataform' => null, 'emailData' => null]);
        }
    }


    public function leaveRejected(Request $request)
    {
        try {
            $this->connectToDatabase($request->db_name);

            $leave = Leave::where('id', $request->leave_id)
                        ->where('staff_id', $request->employee_id)
                        ->firstOrFail();

            $leave->status = 2; // Rejected
            $leave->text = $leave->text . "\nRejected Reason: " . $request->reason;
            $leave->save();

            return view('email.leaveapplyemail', [
                'statusMessage' => "Leave request has been rejected",
                'dataform' => null,
                'emailData' => null
            ]);

        } catch (\Throwable $th) {
            return view('email.leaveapplyemail', [
                'statusMessage' => "Error: " . $th->getMessage(),
                'dataform' => null,
                'emailData' => null
            ]);
        }
    }


    // Reuse your existing connectToDatabase method
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
}
