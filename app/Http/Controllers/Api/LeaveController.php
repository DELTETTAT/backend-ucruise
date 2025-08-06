<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Leave;
use App\Models\ScheduleCarer;
use App\Models\SubUser;
use App\Models\Reason;
use App\Models\EmailAddressForAttendanceAndLeave;
use App\Models\TeamManager;
use Illuminate\Http\Request;
use App\Mail\LeaveApplyEmail;
use Mail;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    protected $staff;
    public function __construct(StaffController $staff)
    {
        $this->staff = $staff;
    }
    //**************************************** Apply for leave api ******************* */
    /**
     * @OA\Post(
     * path="/uc/api/leave/apply",
     * operationId="applyForLeave",
     * tags={"Employee"},
     * summary="Leave Apply",
     *   security={ {"Bearer": {} }},
     * description="Leave Apply",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"start_date","type"},
     *               @OA\Property(property="start_date", type="date"),
     *               @OA\Property(property="end_date", type="date"),
     *               @OA\Property(property="type", type="integer", description="1:Full Leave, 2:Morning Half 3:Evening Half"),
     *               @OA\Property(property="leave_message", type="text"),
     *               @OA\Property(property="leave_type", type="string"),
     *               @OA\Property(property="reason_id", type="text"),
     *               @OA\Property(property="emergency_leave", type="integer", description="1 => yes, 0 => no"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The leave request submitted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The leave request submitted successfully.",
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
    public function applyForLeave(Request $request)
    {
        try {

            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);

            if ($user->hasRole('carer')) {
                $request->validate([
                    'start_date' => 'required|date',
                    'end_date' => 'date|nullable',
                    'type' => 'required|integer',
                    'reason_id' => 'integer|nullable',
                    'leave_message' => 'nullable|string',
                    'leave_type' => 'nullable|string',
                    'emergency_leave' => 'nullable|integer',
                    'content' => 'nullable',
                ]);

                $start_date = date('Y-m-d', strtotime($request->start_date));

                if ($request->end_date) {
                    $end_date = date('Y-m-d', strtotime($request->end_date));
                }else {
                    $end_date = $start_date;
                }
                $leave = Leave::create([
                    'staff_id' =>  auth('sanctum')->user()->id,
                    // 'schedule_id' => $request->schedule_id,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'type' => $request->type,
                    'status' => 0,
                    'leave_type' => $request->leave_type ?? 'casual_leave',
                    'reason_id' => $request->reason_id == 0 ? NULL : $request->reason_id,
                    'text' => $request->leave_message,
                    'emergency_leave' => $request->emergency_leave ?? 0,
                    'email_content' => $request->content ?? null,
                ]);

                $reason_type = Reason::find($request->reason_id);

                $database = base64_encode(DB::connection()->getDatabaseName());
                $userId = base64_encode($user->id);
                $accept = base64_encode("1");
                $decline = base64_encode("2");
                $leaveId = base64_encode($leave->id);

                $acceptUrl = url('api/handleLeaveAction/'.$userId.'/'.$accept.'/'.$database.'/'.$leaveId);
                $declineUrl = url('api/handleLeaveAction/'.$userId.'/'.$decline.'/'.$database.'/'.$leaveId);


                // Add accept/decline buttons to email content
                $actionButtons = '
                    <div style="margin: 20px 0; text-align: left;">
                        <a href="'. $acceptUrl .'" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px; font-weight: semibold; font-size: 14px;">Approve Leave</a>
                        <a href="'. $declineUrl .'" style="background-color: #f44336; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: semibold; font-size: 14px;">Reject Leave</a>
                    </div>
                ';

                $emailData = [
                    'user_name' => $user->first_name." ".$user->last_name,
                    'start_date' => $start_date,
                    'end_date' => $request->end_date,
                    'type' => isset($reason_type->message) ? $reason_type->message : "",
                    'reason' => $request->leave_message,
                    'action_buttons' => $actionButtons,
                    'custom_content' => $request->content ?? '',
                ];

                // $emails = EmailAddressForAttendanceAndLeave::where('type',0)->get();

                // foreach ($emails as $key => $email) {

                //     info('..'.$email);
                //      Mail::to($email->email)->send(new LeaveApplyEmail($emailData));
                // }

                // **** find Manager And Team Leader   ***** //
                $getManagerList = TeamManager::with(['employees','teams.teamLeader', 'teams.teamMembers.user'])->get();
                     //$team_manager_id = null;

                    if ($getManagerList) {
                       foreach ($getManagerList as $key => $manager) {
                        foreach ($manager->teams as $key => $team) {
                            if ($team->team_leader == $user_id) {
                                $team_manager_id =  $manager->id;
                                if (!empty($manager->employees)) {
                                    $managerEmail =  optional($manager->employees->first())->email;
                                }
                                if (!empty($team->teamLeader)) {
                                    $teamLeaderEmail =  optional($team->teamLeader)->email;
                                }


                                $team__id =   $team->id;
                                break 2;
                            }else {

                                foreach ($team->teamMembers as $key => $member) {

                                      if (isset($member->user) && $member->user->id == $user_id) {
                                            $team_manager_id =  $manager->id;
                                            $team__id =   $team->id;

                                            if (!empty($manager->employees)) {
                                                $managerEmail =  optional($manager->employees->first())->email;
                                            }
                                            if (!empty($team->teamLeader)) {
                                                $teamLeaderEmail =  optional($team->teamLeader)->email;
                                            }
                                            break 2;
                                      }
                                }
                            }


                        }
                    }
                    }else {
                        return response()->json(['status' => false, 'message' => 'Not Found Data']);
                    }


                // **** find Manager And Team Leader  end ***** //

                $emails = EmailAddressForAttendanceAndLeave::where('type', 0)->get();
                $fromName = $user->first_name." ".$user->last_name;

                if ($emails->isNotEmpty()) {
                    $toEmail = $emails->first()->email;

                    $ccEmails = $emails->skip(1)->pluck('email')->toArray();

                     if (!empty($managerEmail)) {
                        $ccEmails[] = $managerEmail;
                     }
                     if (!empty($teamLeaderEmail)) {
                        $ccEmails[] = $teamLeaderEmail;

                     }

                    // Remove duplicates just in case
                    $ccEmails = array_unique(array_filter($ccEmails, function($email) {
                        return !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
                    }));
                    $ccEmails = array_values($ccEmails);
                    
                    // Mail::to($toEmail)
                    //     ->cc($ccEmails)
                    //     ->send(new LeaveApplyEmail($emailData, $fromName));
                    if (!empty($toEmail)) {
                        $mail = Mail::to($toEmail);
                        if (!empty($ccEmails)) {
                            $mail->cc($ccEmails);
                        }
                        $mail->send(new LeaveApplyEmail($emailData, $fromName));
                    }
                }

                return response()->json(['success' => true, 'message' => 'Leave request submitted successfully'], 200);

            }
            return response()->json(['success' => false, 'message' => 'User is not an employee'], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //******************************* Get leave requests api*********************** */

    /**
     * @OA\Get(
     * path="/uc/api/leave-requests",
     * operationId="previousLeaveRequest",
     * tags={"Employee"},
     * summary="Previous Leave Request",
     *   security={ {"Bearer": {} }},
     * description="Previous Leave Request",

     *      @OA\Response(
     *          response=201,
     *          description="List Data.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="List Data.",
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

    public function previousLeaveRequest()
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            if ($user->hasRole('carer')) {
                $previousLeaveRequests = Leave::with(['reason','user:id,first_name,last_name,employement_type'])->where('staff_id', $user_id)->whereYear('start_date', '=', date('Y'))->get();
                if ($previousLeaveRequests) {
                    $previousLeaveRequests = @$previousLeaveRequests->sortByDesc('created_at')->values();
                    $this->data['previousLeaveRequests'] = $previousLeaveRequests;
                }
                return response()->json(['success' => true, "data" => $this->data], 200);
            }
            return response()->json(['success' => false, "message" => "User is not an employee"], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

      /**
     * @OA\Get(
     * path="/uc/api/employeeDriverInfo",
     * operationId="employeeDriverInfo",
     * tags={"Home"},
     * summary="Driver Employee info",
     *   security={ {"Bearer": {} }},
     * description="Driver Employee info",

     *      @OA\Response(
     *          response=201,
     *          description="Details listed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Details listed successfully",
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
    public function employeeDriverInfo()
    {
            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            if ($user) {
                $this->data['user'] = $this->staff->getDriverEmpoyeeById($user->id);
                return response()->json(['success' => true, "data" => $this->data], 200);
            }
            return response()->json(['success' => false, "message" => "User is not an employee"], 401);

    }


     /**
     * @OA\Get(
     * path="/uc/api/leavetypes",
     * operationId="leavetypes",
     * tags={"Employee"},
     * summary="Get Leave Types Request",
     *   security={ {"Bearer": {} }},
     * description="Get Leave Types Request",
     *      @OA\Response(
     *          response=201,
     *          description="Leave Types Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Leave Types Get Successfully",
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

     public function leaveTypes(){
        try {

            $user_id = auth('sanctum')->user()->id;
            $month = now()->format('m'); // Just month (e.g., 05)
            $year = now()->format('Y');
            $emergency_leave = Leave::where('staff_id', $user_id)
                                ->where('emergency_leave', 1)
                                ->where('status',1)
                                ->where(function($query) use ($month, $year) {
                                    $query->where(function($q) use ($month, $year) {
                                        $q->whereMonth('start_date', $month)
                                        ->whereYear('start_date', $year);
                                    })->orWhere(function($q) use ($month, $year) {
                                        $q->whereMonth('end_date', $month)
                                        ->whereYear('end_date', $year);
                                    });
                                })
                                ->first();
            if (!empty($emergency_leave)) {
                $emergency_leave = 1;   // yes taken
            }else {
                $emergency_leave = 0;  // no taken
            }


            $leave_types = [
                'medical_leave',
                'casual_leave',
                'maternity_leave',
                'bereavement_leave',
                'wedding_leave',
                'paternity_leave',
            ];

            return response()->json([
                'data' => $leave_types,
                'message' => "Leave Type List",
                'emergency_leave' => $emergency_leave
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
     }



}
