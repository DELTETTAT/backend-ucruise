<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\HomeController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\{Allowances, CarersNoshowTimer, CompanyAddresse, Holiday, Invoice, PriceBook, PriceTableData, Rating, Reschedule, RideSetting, Schedule, ScheduleCarer, ScheduleCarerRelocation, ScheduleCarerStatus, ScheduleStatus, ScheduleTask, ShiftTypes, SubUser, Teams, User, Vehicle, DailyScheduleCarer, DailySchedule};
use Carbon\Carbon;
use DB;
use App\Models\EmailAddressForAttendanceAndLeave;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Mail\RelocationRequestEmail;
use Mail;
use App\Models\Reason;


class ScheduleController extends Controller
{
    protected $home;

    public function __construct(HomeController $home)
    {
        $this->home = $home;
    }

    /**
     * @OA\Post(
     * path="/uc/api/schedules",
     * operationId="schedulesAll",
     * tags={"Employee"},
     * summary="Schedule list",
     *   security={ {"Bearer": {} }},
     * description="Schedule list",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"date"},
     *               @OA\Property(property="date", type="date"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The schedules data listed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The schedules data listed successfully.",
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
    public function schedulesAll(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date|date_format:Y-m-d'
            ]);


            $user_id = auth('sanctum')->user()->id;
            $user_ids = array($user_id);
            $dates = array();
            array_push($dates, $request->date);

            $schedules = $this->getWeeklyScheduleInfo($user_ids, $dates, 2, "all");

            return response()->json(['success' => true, "schedules" => $schedules, "message" => "The schedules data listed successfully"], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //************************ Shift change requests listing *****************************

    /**
     * @OA\Post(
     * path="/uc/api/shiftChange-requests",
     * operationId="shiftChangeRequests",
     * tags={"Employee"},
     * summary="Shift Change Request",
     *   security={ {"Bearer": {} }},
     * description="Shift Change Request",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"type"},
     *               @OA\Property(property="type", type="text",description="temporary or permanent"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The shifchange Requests listed successfully data listed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The shifchange Requests listed successfully data listed successfully",
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


    public function shiftChangeRequests(Request $request)
    {
        try {

            $request->validate([
                'type' => 'required|in:temporary,permanent'
            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

            if ($user->hasRole('carer')) {
                if ($request->type == 'permanent') {

                    $this->data['shiftChangeRequests'] = Reschedule::where('user_id', $user_id)
                        ->where('date', '<=', $endOfMonth)
                        ->where('date', '>=', $startOfMonth)
                        ->orderBy('created_at', 'desc')
                        ->with(['reason','user'])
                        ->get();
                } else if ($request->type == 'temporary') {

                    $this->data['shiftChangeRequests'] = ScheduleCarerRelocation::where('date', '<=', $endOfMonth)
                        ->where('staff_id', $user_id)
                        ->where('date', '>=', $startOfMonth)
                        ->orderBy('created_at', 'desc')
                        ->with(['reason', 'user'])
                        ->get();
                }

                return response()->json(['success' => true, "data" => $this->data, "message" => "The ShiftChangeRequest listed successfully"], 200);
            }
            return response()->json(['success' => false,  "message" => "User is not an employee"], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    //**************************Shift relocation requests ********************************

    /**
     * @OA\Post(
     * path="/uc/api/shift-relocation-request",
     * operationId="shiftRelocationRequest",
     * tags={"Employee"},
     * summary="Shift Relocation Request",
     *   security={ {"Bearer": {} }},
     * description="Shift Relocation Request",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"address","date","type","latitude","longitude"},
     *               @OA\Property(property="date", type="text"),
     *               @OA\Property(property="type", type="text", description="1:Both, 2:Pick, 3:Drop"),
     *               @OA\Property(property="address", type="text"),
     *               @OA\Property(property="latitude", type="text"),
     *               @OA\Property(property="longitude", type="text"),
     *               @OA\Property(property="reason_id", type="text"),
     *               @OA\Property(property="text", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Request Submitted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Request Submitted successfully.",
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


    public function shiftRelocationRequest(Request $request)
    {
        try {

            $request->validate([

                'address' => 'required',
                'date' => 'required|date|date_format:Y-m-d',
                'type' => 'required|integer',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'reason_id' => 'integer|nullable',
                'text' => 'nullable',
            ]);

            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);

            if ($user->hasRole('carer')) {


                $relocation_request = new ScheduleCarerRelocation();
                //$relocation_request->schedule_id = $request->schedule_id;
                $relocation_request->staff_id = $user_id;
                $relocation_request->date = $request->date;
                $relocation_request->status = 0;
                $relocation_request->shift_type = $request->type;
                $relocation_request->temp_address = $request->address;
                $relocation_request->temp_latitude = $request->latitude;
                $relocation_request->temp_longitude = $request->longitude;
                $relocation_request->reason_id = $request->reason_id == 0 ? NULL : $request->reason_id;
                $relocation_request->text = $request->text;
                $relocation_request->save();


                 //////////////////   send email for Request Relocation

                $reason_type = Reason::find($request->reason_id);

                $emailData = [
                    'user_name' => $user->first_name." ".$user->last_name,
                    'date' => $request->date,
                    'reason' => isset($reason_type->message) ? $reason_type->message : "",
                    'text' => $request->text,
                    'location' => $request->address,
                    'shift_type' => $request->type,
                ];

                $emails = EmailAddressForAttendanceAndLeave::where('type',0)->get();

                foreach ($emails as $key => $email) {
                     Mail::to($email->email)->send(new RelocationRequestEmail($emailData));
                }

                //////////////////   send email for Request Relocation end


                return response()->json(['success' => true, "message" => "Request Submitted successfully"], 200);
            } else {
                return response()->json(['success' => false, "message" => "Unauthorised user"], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }



    //************************** Cancel ride by the employee *******************************

    /**
     * @OA\Post(
     * path="/uc/api/cancelRide",
     * operationId="cancelRide",
     * tags={"Employee"},
     * summary="Cancel Ride",
     *   security={ {"Bearer": {} }},
     * description="Cancel Ride",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"schedule_id","staff_id","date","type"},
     *               @OA\Property(property="schedule_id", type="text"),
     *               @OA\Property(property="staff_id", type="text"),
     *               @OA\Property(property="date", type="text"),
     *               @OA\Property(property="type", type="text"),
     *               @OA\Property(property="cancel_reason_id", type="text"),
     *               @OA\Property(property="cancel_message", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Request Submitted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Request Submitted successfully.",
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

    public function cancelRide(Request $request)
    {
        try {
            $request->validate([
                'schedule_id' => 'required|integer',
                'staff_id' => 'required|integer',
                'date' => 'required|date',
                'type' => 'required',
                'cancel_message' => 'string|nullable',
                'cancel_reason_id' => 'nullable|integer',

            ]);
            $formattedDate = date('Y-m-d', strtotime($request->date));
            if (!$formattedDate) {
                return response()->json(['success' => false, 'message' => 'Not a valid date'], 500);
            }


            $schedule_carer = ScheduleCarer::with('user')->where('schedule_id', $request->schedule_id)->where('carer_id', $request->staff_id)->where('shift_type', $request->type)->first();
            if (!$schedule_carer) {
                return response()->json(['success' => false, "message" => "Staff does not exist in this ride"], 500);
            }
            $carer_first_name = @$schedule_carer->user->first_name;

            $schedule_carer_status = ScheduleCarerStatus::where(['schedule_carer_id' => $schedule_carer->id, 'date' => $formattedDate])->first();
            if (!$schedule_carer_status) {
                $schedule_carer_status = new ScheduleCarerStatus();
            }
            $schedule_carer_status->schedule_carer_id = $schedule_carer->id;
            $schedule_carer_status->date = $formattedDate;
            $schedule_carer_status->status_id = 4;
            $schedule_carer_status->cancel_reason_id = $request->cancel_reason_id == 0 ? NULL : $request->cancel_reason_id;
            $schedule_carer_status->cancel_message = $request->cancel_message;
            $schedule_carer_status->save();
            $schedule = Schedule::find($request->schedule_id);
            $schedule_driver = SubUser::find($schedule->driver_id);
            if ($schedule_driver && $schedule_driver->fcm_id) {
                $title = 'Ride cancelled';
                $body = $carer_first_name . ' has canceled the ride.';
                @$this->sendPushNotification($schedule_driver->fcm_id, $title, $body);
            }
            return response()->json(['success' => true, "message" => "Ride cancelled successfully"], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //************************ start ride by the driver *********************************

    /**
     * @OA\Post(
     * path="/uc/api/startRide",
     * operationId="startRide",
     * tags={"Driver"},
     * summary="Start Ride",
     *   security={ {"Bearer": {} }},
     * description="Start Ride",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"schedule_id","type","date"},
     *               @OA\Property(property="schedule_id", type="text"),
     *               @OA\Property(property="date", type="text"),
     *               @OA\Property(property="type", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Request Submitted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Request Submitted successfully.",
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

    public function startRide(Request $request)
    {
        try {
            $request->validate([
                'schedule_id' => 'required|integer',
                'date' => 'required',
                'type' => 'required|in:pick,drop',
            ]);
            if (date('Y-m-d', strtotime($request->date)) != date('Y-m-d')) {
                return response()->json(['success' => false,  "message" => "Ride Cannot be started"], 500);
            }
            $schedule_status = ScheduleStatus::where(['schedule_id' => $request->schedule_id, 'date' => date('Y-m-d'), 'type' => $request->type])->first();
            if (!$schedule_status) {
                $schedule_status = new ScheduleStatus();
                @$this->generateInvoice($request->schedule_id, $request->type);
            }
            $schedule_status->schedule_id = $request->schedule_id;
            $schedule_status->date = date('Y-m-d');
            $schedule_status->status_id = 6;
            $schedule_status->start_time = Carbon::now('Asia/Kolkata')->toTimeString();
            $schedule_status->type = $request->type;
            $schedule_status->save();

            $schedule_carers = ScheduleCarer::with('user')->where('schedule_id', $request->schedule_id)->where('shift_type', $request->type)->get();
            foreach ($schedule_carers as $key => $value) {
                //new code
                $fcmToken = @$value->user->fcm_id;
                //end of new code
                $schedule_carer_status = ScheduleCarerStatus::where(['schedule_carer_id' => $value->id, 'date' => date('Y-m-d')])->first();
                if (!$schedule_carer_status) {
                    $schedule_carer_status = new ScheduleCarerStatus();
                    $schedule_carer_status->schedule_carer_id = $value->id;
                    $schedule_carer_status->date = date('Y-m-d');
                    $schedule_carer_status->status_id = 1;
                    $schedule_carer_status->otp = random_int(1000, 9999);
                    $schedule_carer_status->save();
                    //new code
                    $title = 'Ride Initiated';
                    $body = 'Your ride service has been initiated, and your driver is presently on your way. (OTP : ' . $schedule_carer_status->otp . ')';
                    @$this->sendPushNotification($fcmToken, $title, $body);
                    //end of new code
                }
            }

            $data = $this->home->getScheduleById($request->schedule_id, $request->type);


            return response()->json(['success' => true, 'data' => @$data['all_schedule'][0], "message" => "Ride started successfully"], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //************************* Pick staff by the driver ***********************************

    /**
     * @OA\Post(
     * path="/uc/api/pickStaff",
     * operationId="pickStaff",
     * tags={"Driver"},
     * summary="Pick Staff",
     *   security={ {"Bearer": {} }},
     * description="Pick Staff",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"otp","schedule_id","staff_id","type"},
     *               @OA\Property(property="otp", type="text"),
     *               @OA\Property(property="schedule_id", type="text"),
     *               @OA\Property(property="staff_id", type="text"),
     *               @OA\Property(property="type", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Request Submitted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Request Submitted successfully.",
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

    public function pickStaff(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "otp" => "required",
            ]);



            if ($validator->fails()) {
                return response()->json(['success' => false, "message" => "Please enter OTP to pick staff"], 500);
            }
            $request->validate([
                'schedule_id' => 'required|integer',
                'staff_id' => 'required|integer',
                //'date' => 'required|date',
                'type' => 'required',
            ]);

            $schedule_carer = ScheduleCarer::with('user')->where('schedule_id', $request->schedule_id)->where('carer_id', $request->staff_id)->where('shift_type', $request->type)->first();
            if (!$schedule_carer) {
                return response()->json(['success' => false, "message" => "Staff does not exist in this ride"], 500);
            }
            $schedule_carer_status = ScheduleCarerStatus::where(['schedule_carer_id' => $schedule_carer->id, 'date' => date('Y-m-d')])->first();

            if (!$schedule_carer_status) {
                return response()->json(['success' => false, "message" => "The ride has not started yet"], 500);
            }
            if ($schedule_carer_status->otp != $request->otp) {
                return response()->json(['success' => false, "message" => "OTP does not match. Please enter the correct OTP."], 500);
            }
            // new
            $fcmToken = @$schedule_carer->user->fcm_id;
            $title = "Picked successfully";
            $body = "You have been successfully picked up by your driver.";
            $schedule_carer_status->status_id = 2;
            $schedule_carer_status->start_time = Carbon::now('Asia/Kolkata')->toTimeString();
            $schedule_carer_status->save();
            @$this->sendPushNotification($fcmToken, $title, $body);
            //end of new code


            $schedule_status = ScheduleStatus::where(['schedule_id' => $request->schedule_id, 'date' => date('Y-m-d'), 'type' => $request->type])->first();
            if ($schedule_status) {
                $schedule_status->status_id = 7;
                $schedule_status->save();
            }

            $data = $this->home->getScheduleById($request->schedule_id, $request->type);

            return response()->json(['success' => true, 'data' => @$data['all_schedule'][0], "message" => "Staff picked successfully"], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //********************* Drop staff by the driver ***********************************

    /**
     * @OA\Post(
     * path="/uc/api/dropStaff",
     * operationId="dropStaff",
     * tags={"Driver"},
     * summary="Drop Staff",
     *   security={ {"Bearer": {} }},
     * description="Pick Staff",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"schedule_id","staff_id","type"},
     *               @OA\Property(property="schedule_id", type="text"),
     *               @OA\Property(property="staff_id", type="text"),
     *               @OA\Property(property="type", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Request Submitted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Request Submitted successfully.",
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

    public function dropStaff(Request $request)
    {
        try {
            $request->validate([
                'schedule_id' => 'required|integer',
                'staff_id' => 'required|integer',
                //'date' => 'required|date',
                'type' => 'required',
            ]);
            $schedule_carer = ScheduleCarer::with('user')->where('schedule_id', $request->schedule_id)->where('carer_id', $request->staff_id)->where('shift_type', $request->type)->first();
            if (!$schedule_carer) {
                return response()->json(['success' => false, "message" => "Staff does not exist in this ride"], 500);
            }

            $schedule_carer_status = ScheduleCarerStatus::where(['schedule_carer_id' => $schedule_carer->id, 'date' => date('Y-m-d')])->first();
            if (!$schedule_carer_status) {
                return response()->json(['success' => false, "message" => "The ride has not started yet"], 500);
            }
            $schedule_carer_status->status_id = 3;
            $schedule_carer_status->end_time = Carbon::now('Asia/Kolkata')->toTimeString();
            $schedule_carer_status->save();

            $schedule_carers = ScheduleCarer::with('user')->where('schedule_id', $request->schedule_id)->where('shift_type', $request->type)->get();
            foreach ($schedule_carers as $key => $value) {
                //new code
                $fcmToken = @$value->user->fcm_id;
                //end of new code
                $schedule_carer_status = ScheduleCarerStatus::where(['schedule_carer_id' => $value->id, 'date' => date('Y-m-d')])->whereNotIn('status_id', [3, 4, 5, 11])->first();
                if ($schedule_carer_status) {

                    $data = $this->home->getScheduleById($request->schedule_id, 'drop');

                    $title = 'Dropped successfully';
                    $body = 'You have been successfully dropped off by your driver.';
                    @$this->sendPushNotification($fcmToken, $title, $body);
                    return response()->json(['success' => true, 'data' => @$data['all_schedule'][0], "message" => "Staff dropped successfully"], 200);
                }
                //return response()->json(['success' => true, "message" => "Staff dropped successfully"], 200);
                if (date('Y-m-d', strtotime($value->temp_date)) == date('Y-m-d')) {

                    $value->temp_date = null;
                    $value->temp_lat = null;
                    $value->temp_long = null;
                    $value->temp_address = null;
                    $value->save();
                }
            }
            $schedule_status = ScheduleStatus::where(['schedule_id' => $request->schedule_id, 'date' => date('Y-m-d'), 'type' => $request->type])->first();
            if ($schedule_status) {
                $schedule_status->status_id = 8;
                $schedule_status->end_time = Carbon::now('Asia/Kolkata')->toTimeString();
                $schedule_status->save();
                $data = $this->home->getScheduleById($request->schedule_id, 'drop');
                @$this->changeInvoiceStatus($request->schedule_id, $request->type, date('Y-m-d'));
                return response()->json(['success' => true, 'data' => @$data['all_schedule'][0], "message" => "Ride completed successfully"], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    //*************************** Complete ride by the driver *****************************

    /**
     * @OA\Post(
     * path="/uc/api/completeRide",
     * operationId="completeRide",
     * tags={"Driver"},
     * summary="Complete Ride",
     *   security={ {"Bearer": {} }},
     * description="Complete Ride",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"schedule_id","type"},
     *               @OA\Property(property="schedule_id", type="text"),
     *
     *               @OA\Property(property="type", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Request Submitted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Request Submitted successfully.",
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

    public function completeRide(Request $request)
    {
        try {
            $request->validate([
                'schedule_id' => 'required|integer',
                //'staff_id' => 'required|integer',
                //'date' => 'required|date',
                'type' => 'required'
                //'cancel_reason_id' => 'required|integer',
            ]);

            $schedule_status = ScheduleStatus::where(['schedule_id' => $request->schedule_id,  'date' => date('Y-m-d'), 'type' => $request->type])->first();
            if ($schedule_status) {
                $schedule_status->status_id = 8;
                $schedule_status->end_time = Carbon::now('Asia/Kolkata')->toTimeString();
                $schedule_status->save();
                @$this->changeInvoiceStatus($request->schedule_id, $request->type, date('Y-m-d'));
                $schedule_carers = ScheduleCarer::with('user')->where('schedule_id', $request->schedule_id)->where('shift_type', $request->type)->get();
                //dd($schedule_carers->toArray());
                foreach ($schedule_carers as $key => $value) {
                    $fcmToken = @$value->user->fcm_id;
                    $schedule_carer_status = ScheduleCarerStatus::where(['schedule_carer_id' => $value->id, 'date' => date('Y-m-d')])->whereNotIn('status_id', [3, 4, 5, 11])->first();
                    if ($schedule_carer_status) {
                        $schedule_carer_status->status_id = 3;
                        $schedule_carer_status->end_time = Carbon::now('Asia/Kolkata')->toTimeString();
                        $schedule_carer_status->save();
                        $title = "Ride completed successfully";
                        $body = "Your ride has been completed.";
                        @$this->sendPushNotification($fcmToken, $title, $body);
                    }
                    if (date('Y-m-d', strtotime($value->temp_date)) == date('Y-m-d')) {

                        $value->temp_date = null;
                        $value->temp_lat = null;
                        $value->temp_long = null;
                        $value->temp_address = null;
                        $value->save();
                    }
                }

                return response()->json(['success' => true, "message" => "Ride completed successfully"], 200);
            }

            return response()->json(['success' => true, "message" => "Ride cancelled successfully"], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //**************************  Teams **************************************************


    /**
     * @OA\Get(
     * path="/uc/api/team",
     * operationId="teams",
     * tags={"Employee"},
     * summary="Team",
     *   security={ {"Bearer": {} }},
     * description="Team",

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

    public function teams()
    {
        try {

            $sub_user_id = auth('sanctum')->user()->id;
            $sub_user = SubUser::find($sub_user_id);
            $user_id = User::where('email', $sub_user->email)->pluck('id')->first();

            if ($sub_user->hasRole('carer')) {

                $employeeTeams = Teams::where('staff', 'LIKE', "%{$user_id}%")->get();
                $this->data['teams'] = $employeeTeams->map(function ($team) {
                    $staffIds = explode(',', $team->staff);
                    $staffInfo = User::whereIn('id', $staffIds)->get();
                    $staffData = $staffInfo->map(function ($staff) {
                        $subUser = SubUser::where('email', $staff->email)->with('roles')->first();
                        return $subUser;
                    });

                    $team->staff = $staffData->toArray();

                    return $team;
                });
                return response()->json(['success' => true,  'data' => $this->data], 200);
            } else {
                return response()->json(['success' => false, "message" => "User is not employee"], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //***********************  Reset ride ************************************************

    /**
     * @OA\Post(
     * path="/uc/api/resetRide",
     * operationId="resetRide",
     * tags={"Employee"},
     * summary="Reset Ride",
     *   security={ {"Bearer": {} }},
     * description="Reset Ride",

     *      @OA\Response(
     *          response=201,
     *          description="Ride reset successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Ride reset successfully.",
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

    public function resetRide()
    {
        try {
            ScheduleStatus::where('date', date('Y-m-d'))->delete();
            ScheduleCarerStatus::where('date', date('Y-m-d'))->delete();
            Invoice::where('date', date('Y-m-d'))->delete();
            CarersNoshowTimer::where('date', date('Y-m-d'))->delete();
            return response()->json(['success' => true,  'message' => 'Ride reset successfully'], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //********************* No show by the driver *****************************************

    /**
     * @OA\Post(
     * path="/uc/api/noShow",
     * operationId="noShow",
     * tags={"Driver"},
     * summary="No Show",
     *   security={ {"Bearer": {} }},
     * description="No Show",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"schedule_id","staff_id","type"},
     *               @OA\Property(property="schedule_id", type="text"),
     *               @OA\Property(property="staff_id", type="text"),
     *               @OA\Property(property="type", type="text"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=200,
     *          description="No Show marked successfully.",
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


    public function noShow(Request $request)
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            if ($user->hasRole('driver')) {
                $request->validate([
                    'schedule_id' => 'required|integer',
                    'staff_id' => 'required|integer',
                    'type' => 'required|in:pick,drop',
                ]);

                $rideSettings  = RideSetting::first();
                $noshowCount = $rideSettings->noshow_count;
                $showType = $rideSettings->noshow_frequency;
                $showAlltype = $rideSettings->all_noshow;

                $schedule_carer = ScheduleCarer::with('user')->where('schedule_id', $request->schedule_id)
                    ->where('carer_id', $request->staff_id)
                    ->where('shift_type', $request->type)
                    ->first();

                if (!$schedule_carer) {
                    return response()->json(['success' => false, "message" => "Staff does not exist in this ride"], 500);
                }
                $fcmToken = @$schedule_carer->user->fcm_id;

                $schedule_carer_status = ScheduleCarerStatus::where([
                    'schedule_carer_id' => $schedule_carer->id,
                    'date' => date('Y-m-d'),
                ])->first();

                if (!$schedule_carer_status) {
                    return response()->json(['success' => false, "message" => "The ride has not started yet"], 500);
                }

                $schedule_carer_status->status_id = 5;
                $schedule_carer_status->carer_id = $request->staff_id;
                $schedule_carer_status->save();


                $today = Carbon::today();
                // For Month
                if($showType =='monthly'){
                    $startDate = $today->copy()->startOfMonth()->toDateString();
                    $endDate = $today->copy()->endOfMonth()->toDateString();
                }

                // For week
                if($showType =='weekly'){
                    $startDate   = $today->copy()->startOfWeek()->toDateString();
                    $endDate     = $today->copy()->endOfWeek()->toDateString();
                }

                // Fore years
                if($showType =='yearly'){
                    $startDate   = $today->copy()->startOfYear()->toDateString();
                    $endDate     = $today->copy()->endOfYear()->toDateString();
                }

                // Count how many times this carer already has status_id = 5 this month
                $existingCount = ScheduleCarerStatus::where('carer_id', $request->staff_id)
                    ->where('status_id', 5)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->count();


                if ($existingCount >= $noshowCount) {
                    // Be sure this is the correct user ID
                    $user = User::find($request->staff_id);
                    $subuser = SubUser::find($request->staff_id);
                    if ($user) {
                        $user->no_show = "Yes";
                        $user->save();
                        // sub users table
                        $subuser->no_show = "Yes";
                        $subuser->save();

                        DB::table('update_employee_histories')->insert([
                            'employee_id' => $request->staff_id,
                            'updated_by' => $user_id,
                            'date' => now()->format('Y-m-d'),
                            'time' => now()->format('H:i:s'),
                            'notes' => 'Marked as no show based on '.$showType.'. Total time absent in schedule: '.($existingCount + 1),
                            'changed' => 'Marked as no show due to exceeding absence limit based on '.$showType,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }


                $title = 'Marked Absent';
                $body = 'The driver has marked you as absent.';
                @$this->sendPushNotification($fcmToken, $title, $body);

                $data = $this->home->getScheduleById($request->schedule_id, $request->type);

                return response()->json(['success' => true, 'data' => @$data['all_schedule'][0], "message" => "No Show marked successfully"], 200);
            } else {
                return response()->json(['success' => false, "message" => "User is not a driver"], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    //************************ Rate ride by the employee *********************************

    /**
     * @OA\Post(
     * path="/uc/api/rate-ride",
     * operationId="rideRating",
     * tags={"Employee"},
     * summary="Rate Ride",
     *   security={ {"Bearer": {} }},
     * description="Rate Ride",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"schedule_id","type","rating"},
     *               @OA\Property(property="schedule_id", type="text"),
     *               @OA\Property(property="type", type="text"),
     *               @OA\Property(property="rating", type="text"),
     *               @OA\Property(property="reason_id", type="text"),
     *               @OA\Property(property="comment", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=200,
     *          description="Ride rated successfully.",
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
    public function rideRating(Request $request)
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            if ($user->hasRole('carer')) {
                $request->validate([
                    'schedule_id' => 'required|integer',
                    'type' => 'required|in:pick,drop',
                    'rating' => 'required|integer|min:1|max:5',

                ]);
                $schedule = Schedule::where('id', $request->schedule_id)->first();
                if (!$schedule) {
                    return response()->json(['success' => false, "message" => "Invalid schedule"], 500);
                }
                $driver_id = $schedule->driver_id;

                $schedule_carer = ScheduleCarer::where('schedule_id', $request->schedule_id)->where('carer_id', $user->id)->where('shift_type', $request->type)->first();
                if (!$schedule_carer) {
                    return response()->json(['success' => false, "message" => "Staff does not exist in this ride"], 500);
                }
                $schedule_carer_status = ScheduleCarerStatus::where(['schedule_carer_id' => $schedule_carer->id, 'date' => date('Y-m-d')])->first();

                if (!$schedule_carer_status) {
                    return response()->json(['success' => false, "message" => "The ride has not started yet"], 500);
                }
                $schedule_carer_rating = new Rating();
                $schedule_carer_rating->schedule_carer_id = $schedule_carer->id;
                $schedule_carer_rating->driver_id = $driver_id;
                $schedule_carer_rating->date = date('Y-m-d');
                $schedule_carer_rating->rate = $request->rating;
                $schedule_carer_rating->reason_id = $request->reason_id == 0 ? NULL : $request->reason_id;
                $schedule_carer_rating->comment = $request->comment;
                $schedule_carer_rating->save();
                return response()->json(['success' => true, "message" => "Successfully rated the driver"], 200);
            } else {
                return response()->json(['success' => false, "message" => "User is not a employee"], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/uc/api/employeeRideInfo",
     * operationId="employeeRideInfo",
     * tags={"Employee"},
     * summary="Employee ride details",
     *   security={ {"Bearer": {} }},
     * description="Employee ride details",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"schedule_id","type"},
     *               @OA\Property(property="schedule_id", type="text"),
     *               @OA\Property(property="type", type="text"),
     *
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=200,
     *          description="Ride rated successfully.",
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
    public function employeeRideInfo(Request $request)
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);


            if ($request->has('type')) {
                if ($request->type == '1') {
                    $request->merge(['type' => 'pick']);
                } elseif ($request->type == '2') {
                    $request->merge(['type' => 'drop']);
                }
            }

            if ($user) {
                $request->validate([
                    'schedule_id' => 'required|integer',
                    'type' => 'required|in:pick,drop'
                ]);

               $date=$request->date;
               $data = $this->home->getScheduleById($request->schedule_id, $request->type,$date);

                return response()->json(['success' => true, 'data' => @$data['all_schedule'][0]], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Unauthorised user'], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }


    /**
     * @OA\Post(
     * path="/uc/api/employeeRideInfoDaily",
     * operationId="employeeRideInfoDaily",
     * tags={"Employee"},
     * summary="Employee ride details",
     *   security={ {"Bearer": {} }},
     * description="Employee ride details",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"schedule_id","type"},
     *               @OA\Property(property="schedule_id", type="text"),
     *               @OA\Property(property="type", type="text"),
     *
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=200,
     *          description="Ride rated successfully.",
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


    public function employeeRideInfoDaily(Request $request){
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            if ($user) {
                $request->validate([
                    'schedule_id' => 'required|integer',
                    'type' => 'required|in:pick,drop'

                ]);
               $date=$request->date;
                $data = $this->home->getScheduleByIdDaily($request->schedule_id, $request->type,$date);

                return response()->json(['success' => true, 'data' => @$data['all_schedule'][0]], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Unauthorised user'], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }




    //****************** Vehicle, schedule_type and driver info for adding schedule *******/
    /**
     * @OA\Get(
     * path="/uc/api/scheduleData",
     * operationId="scheduleData",
     * tags={"Ucruise Schedule"},
     * summary="List all data for Schedule",
     *   security={ {"Bearer": {} }},
     * description="List all data for Schedule",
     *      @OA\Response(
     *          response=201,
     *          description="Schedule data listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Schedule data listed successfully",
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
    public function scheduleData()
    {
        $d = date('Y-m-d');
        $this->data['drivers'] = SubUser::with('vehicle')->whereHas("roles", function ($q) {
            $q->where("name", "driver");
        })
            ->where('close_account', 1)
            ->leftJoin('sub_user_addresses', function ($join) use ($d) {
                $join->on('sub_users.id', '=', 'sub_user_addresses.sub_user_id')
                    ->whereDate('sub_user_addresses.start_date', '<=', $d)
                    ->where(function ($query) use ($d) {
                        $query->whereDate('sub_user_addresses.end_date', '>', $d)
                            ->orWhereNull('sub_user_addresses.end_date');
                    });
            })
            ->whereNotNull('sub_user_addresses.latitude')
            ->whereNotNull('sub_user_addresses.longitude')
            ->orderBy("sub_users.id", "DESC")
            ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address')
            ->get();
        $this->data['employees'] = SubUser::whereHas("roles", function ($q) {
            $q->where("name", "carer");
        })
            ->where('close_account', 1)
            ->leftJoin('sub_user_addresses', function ($join) use ($d) {
                $join->on('sub_users.id', '=', 'sub_user_addresses.sub_user_id')
                    ->whereDate('sub_user_addresses.start_date', '<=', $d)
                    ->where(function ($query) use ($d) {
                        $query->whereDate('sub_user_addresses.end_date', '>', $d)
                            ->orWhereNull('sub_user_addresses.end_date');
                    });
            })
            ->whereNotNull('sub_user_addresses.latitude')
            ->whereNotNull('sub_user_addresses.longitude')
            ->orderBy("sub_users.id", "DESC")
            ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address')
            ->get();

        $this->data['companyLocation'] = CompanyAddresse::whereNull('end_date')->first();
        $this->data['shiftType'] = ShiftTypes::get();
        $this->data['holidays'] = Holiday::orderBy('date', 'DESC')->get();
        $this->data['rideSetting'] = RideSetting::first();
        $this->data['priceBook'] = PriceBook::orderBy('id', 'DESC')
            ->has('priceBookData')
            ->get();
        return response()->json([
            'success' => true,
            'data' => @$this->data,
            'message' => 'Schedule data listed successfully',
        ], 200);
    }


    //******************************* add schedule api *************************/
    /**
     * @OA\Post(
     *     path="/uc/api/addSchedule",
     *     operationId="addSchedule",
     *     tags={"Ucruise Schedule"},
     *     summary="Add schedule",
     *     security={{"Bearer": {}}},
     *     description="Add schedule",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="object",
     *                     description="Your description here"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully added schedule .",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully added schedule .",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */


    public function addSchedule(Request $request)
    {
        try {

            $requestData = json_decode(@$request->data, true); // Convert to array

            // Define validation rules
            $rules = [
                'date' => 'required|date',
                'shift_type_id' => 'required|in:1,2,3',
                'pick_time' => 'required_if:shift_type_id,1,2|date_format:H:i',
                'drop_time' => 'required_if:shift_type_id,2,3|date_format:H:i',
                'driver_id' => 'required|exists:sub_users,id',
                'vehicle_id' => 'required|exists:vehicles,id',
                'scheduleLocation' => 'required',
                'scheduleCity' => 'required',
                'selectedLocationLat' => 'required',
                'selectedLocationLng' => 'required',
                'is_repeat' => 'nullable|boolean',
                'reacurrance' => 'required_if:is_repeat,1|in:daily,weekly,monthly',
                'repeat_days' => 'required_if:reacurrance,daily',
                'repeat_weeks' => 'required_if:reacurrance,weekly',
                'mon' => 'nullable|boolean',
                'tue' => 'nullable|boolean',
                'wed' => 'nullable|boolean',
                'thu' => 'nullable|boolean',
                'fri' => 'nullable|boolean',
                'sat' => 'nullable|boolean',
                'sun' => 'nullable|boolean',
                'repeat_months' => 'required_if:reacurrance,monthly',
                'repeat_day_of_month' => 'required_if:reacurrance,monthly',
                'end_date' => 'nullable|date|after_or_equal:date',
            ];

            // Create validator instance
            $validator = Validator::make($requestData, $rules);

            // Perform validation
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422); // Return validation errors
            }


            $request = json_decode($request->data);

            //check for holiday

            if ($this->isHoliday($request->date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected date is a holiday',
                ], 500);
            }

            $shiftTypeMap = [
                1 => 'pick',
                2 => 'pick-drop',
                3 => 'drop',
            ];

            // Get the mapped shift type string
            $shiftTypes = $shiftTypeMap[$request->shift_type_id] ?? null;

            if (!$shiftTypes) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid shift type provided.',
                ], 422);
            }

            // Check if a schedule with conflicting or duplicate shift_type already exists for the carer on the given date
            foreach ($request->carers as $carer) {
                if ($shiftTypes === 'pick-drop') {
                    // Check if a 'pick-drop' schedule already exists
                    $existingPickDropSchedules = ScheduleCarer::join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
                        ->where('schedule_carers.carer_id', $carer)
                        ->where('schedule_carers.shift_type', 'pick-drop')
                        ->where(function ($query) use ($request) {
                            $query->where('schedules.date', $request->date)
                                ->orWhere(function ($query) use ($request) {
                                    $query->where('schedules.end_date', '>=', $request->date)
                                          ->where('schedules.date', '<=', $request->date);
                                });
                        })
                        ->get();

                    if ($existingPickDropSchedules->isNotEmpty()) {
                        return response()->json([
                            'success' => false,
                            'message' => "A 'pick-drop' schedule already exists for carer on {$request->date}. Cannot create another 'pick-drop'.",
                        ], 422);
                    }

                    // Check if either 'pick' or 'drop' already exists for the carer on the given date
                    $conflictingSchedules = ScheduleCarer::join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
                        ->where('schedule_carers.carer_id', $carer)
                        ->whereIn('schedule_carers.shift_type', ['pick', 'drop'])
                        ->where(function ($query) use ($request) {
                            $query->where('schedules.date', $request->date)
                                ->orWhere(function ($query) use ($request) {
                                    $query->where('schedules.end_date', '>=', $request->date)
                                          ->where('schedules.date', '<=', $request->date);
                                });
                        })
                        ->get();

                    if ($conflictingSchedules->isNotEmpty()) {
                        foreach ($conflictingSchedules as $conflictingSchedule) {
                            $conflictType = $conflictingSchedule->shift_type;

                            if ($conflictType === 'drop') {
                                return response()->json([
                                    'success' => false,
                                    'message' => "A 'drop' schedule already exists for carer on {$request->date}.",
                                ], 422);
                            } elseif ($conflictType === 'pick') {
                                return response()->json([
                                    'success' => false,
                                    'message' => "A 'pick' schedule already exists for carer on {$request->date}. Cannot create 'pick-drop'.",
                                ], 422);
                            }
                        }
                    }
                } elseif ($shiftTypes === 'pick') {
                    $conflictingSchedules = ScheduleCarer::join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
                        ->where('schedule_carers.carer_id', $carer)
                        ->whereIn('schedule_carers.shift_type', ['pick', 'pick-drop'])
                        ->where(function ($query) use ($request) {
                            $query->where('schedules.date', $request->date)
                                ->orWhere(function ($query) use ($request) {
                                    $query->where('schedules.end_date', '>=', $request->date)
                                          ->where('schedules.date', '<=', $request->date);
                                });
                        })
                        ->get();

                    if ($conflictingSchedules->isNotEmpty()) {
                        foreach ($conflictingSchedules as $conflictingSchedule) {
                            $conflictType = $conflictingSchedule->shift_type;

                            if ($conflictType === 'pick') {
                                return response()->json([
                                    'success' => false,
                                    'message' => "A 'pick' schedule already exists for carer on {$request->date}.",
                                ], 422);
                            } elseif ($conflictType === 'pick-drop') {
                                return response()->json([
                                    'success' => false,
                                    'message' => "A 'pick-drop' schedule already exists for carer on {$request->date}. Cannot create 'drop'.",
                                ], 422);
                            }
                        }
                    }
                }
                elseif ($shiftTypes === 'drop') {
                    // Check for conflicting schedules
                    $conflictingSchedules = ScheduleCarer::join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
                        ->where('schedule_carers.carer_id', $carer)
                        ->whereIn('schedule_carers.shift_type', ['drop', 'pick-drop'])
                        ->where(function ($query) use ($request) {
                            $query->where('schedules.date', $request->date)
                                ->orWhere(function ($query) use ($request) {
                                    $query->where('schedules.end_date', '>=', $request->date)
                                          ->where('schedules.date', '<=', $request->date);
                                });
                        })
                        ->get();

                    if ($conflictingSchedules->isNotEmpty()) {
                        foreach ($conflictingSchedules as $conflictingSchedule) {
                            // Determine the type of conflict
                            $conflictType = $conflictingSchedule->shift_type;

                            if ($conflictType === 'drop') {
                                return response()->json([
                                    'success' => false,
                                    'message' => "A 'drop' schedule already exists for carer on {$request->date}.",
                                ], 422);
                            } elseif ($conflictType === 'pick-drop') {
                                return response()->json([
                                    'success' => false,
                                    'message' => "A 'pick-drop' schedule already exists for carer on {$request->date}. Cannot create 'drop'.",
                                ], 422);
                            }
                        }
                    }
                }

            }

            // Proceed with adding the schedule if no conflicts exist
            // ...

            // check driver if have multiple_schedule on 1
            $checkMultiple = DB::table('ride_settings')->first();

            // Check driver schedule or not
            // $checkDriverSchedule = Schedule::where('driver_id', $request->driver_id)
            //     ->where(function ($query) use ($request) {
            //         $query->where(function ($query) use ($request) {
            //             $query->where('date', '=', $request->date)
            //                 ->orWhere(function ($query) use ($request) {
            //                     $query->where('date', '<', $request->date)
            //                         ->where('is_repeat', 1)
            //                         ->where('end_date', '<=', $request->date);
            //                 });
            //         });
            //     })
            //     ->count();

            // $is_error = 0;
            // $is_end_error = 0;






            // if ($checkMultiple->multiple_schedule == 0 && $checkDriverSchedule > 0) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'You can add only one schedule at a time.',
            //     ], 500);
            // }




            if ($checkMultiple->multiple_schedule == 1) {

                $scData = $this->checkSchedule($request);
                $hours = ($checkMultiple->multiple_schedule_hr == null) ? 1 : $checkMultiple->multiple_schedule_hr;
                if ($checkMultiple->multiple_schedule_hr == 4 && @$scData['is_error'] == 1 ) {
                    return response()->json([
                        'success' => false,
                        'message' => "Schedule start time should be 50 minutes before or can add 50 minutes after existing driver schedule!",
                    ], 500);
                }
                if ($checkMultiple->multiple_schedule_hr == 4 && @$scData['is_end_error'] == 1) {
                    return response()->json([
                        'success' => false,
                        'message' => "Schedule start time should be 50 minutes before or can add 50 minutes after existing driver schedule!",
                    ], 500);
                }
                if (@$scData['is_error'] == 1) {
                    return response()->json([
                        'success' => false,
                        'message' => "Schedule start time should be {$hours} hour before or can add {$hours} hour after existing driver schedule!",
                    ], 500);
                }

                if (@$scData['is_end_error'] == 1) {
                    return response()->json([
                        'success' => false,
                        'message' => "Schedule end time should be {$hours} hour before or can add {$hours} hour after existing driver schedule!",
                    ], 500);
                }
            } else {
                $scData = $this->checkSchedule($request);
                // echo '<pre>';print_r($scData['schedules']);die;
                if ($scData['schedules']->count() > 2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can add only one schedule at a time.',
                    ], 500);
                }
            }

            if ($checkMultiple->female_safety == 1) {
                // Check male exist or not in schedule
                $checkData = DB::table('sub_users')->whereIn('id', $request->carers)
                    ->whereRaw('LOWER(gender) = ?', ['male'])
                    ->count();
                if ($checkData == 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only female employee can`t add, Please choose male employe also then can add!',
                        'status_code' => 419,
                    ], 500);
                }
            }

            // echo 'Continue to add schedule';die;

            $week_arr = array();
            $schedule = new Schedule();
            $schedule->date = $request->date;

          //  $schedule->start_time = $request->date . ' ' . @$request->pick_time . ':00';
            $schedule->start_time = $request->date . ' ' . ($request->pick_time ? $request->pick_time . ':00' : '00:00:00');
            $schedule->shift_finishes_next_day = $request->shift_type_id == 2 ? ($request->shift_finishes_next_day == 1 ? 1 : 0) : 0;

            //$schedule->end_time = $request->date . ' ' . @$request->drop_time . ':00';
            $schedule->end_time = $request->date . ' ' . ($request->drop_time ? $request->drop_time . ':00' : '00:00:00');
            $schedule->driver_id = $request->driver_id;
            $schedule->locality = $request->scheduleLocation;
            $schedule->city = $request->scheduleCity;
            $schedule->latitude = $request->selectedLocationLat;
            $schedule->longitude = $request->selectedLocationLng;
            $schedule->vehicle_id = $request->vehicle_id;
            $schedule->previous_day_pick = $request->previous_day_pick;
            $schedule->is_repeat = @$request->is_repeat == 1 ? 1 : 0;
            if (@$request->is_repeat == 1) {
                if ($request->reacurrance == "daily") {
                    $schedule->reacurrance = 0;
                    $schedule->repeat_time = $request->repeat_days;
                } else if ($request->reacurrance == "weekly") {
                    $schedule->reacurrance = 1;
                    $schedule->repeat_time = $request->repeat_weeks;
                    if (@$request->mon == 1) {
                        array_push($week_arr, "mon");
                    }
                    if (@$request->tue == 1) {
                        array_push($week_arr, "tue");
                    }
                    if (@$request->wed == 1) {
                        array_push($week_arr, "wed");
                    }
                    if (@$request->thu == 1) {
                        array_push($week_arr, "thu");
                    }
                    if (@$request->fri == 1) {
                        array_push($week_arr, "fri");
                    }
                    if (@$request->sat == 1) {
                        array_push($week_arr, "sat");
                    }
                    if (@$request->sun == 1) {
                        array_push($week_arr, "sun");
                    }
                    $schedule->occurs_on = json_encode($week_arr);
                } else if ($request->reacurrance == "monthly") {
                    $schedule->reacurrance = 2;
                    $schedule->repeat_time = $request->repeat_months;
                    $schedule->occurs_on = $request->repeat_day_of_month;
                }
                $schedule->end_date = $request->end_date;
            }

            if ($request->shift_type_id) {
                $schedule->shift_type_id = $request->shift_type_id;
            }
            if ($request->pricebook_id) {
                $schedule->pricebook_id = $request->pricebook_id;
            }
            $schedule->save();

            if ($request->shift_type_id == 2 || $request->shift_type_id == 1) {
                if ($request->carers) {
                    foreach ($request->carers as $carer) {
                        $scheduleCarers = new ScheduleCarer();
                        $scheduleCarers->schedule_id = $schedule->id;
                        $scheduleCarers->carer_id = $carer;
                        $scheduleCarers->shift_type = "pick";
                        $scheduleCarers->save();
                    }
                }
            }

            if ($request->shift_type_id == 2 || $request->shift_type_id == 3) {
                if ($request->carers) {

                    foreach ($request->carers as $carer) {
                        $scheduleCarers = new ScheduleCarer();
                        $scheduleCarers->schedule_id = $schedule->id;
                        $scheduleCarers->carer_id = $carer;
                        $scheduleCarers->shift_type = "drop";
                        $scheduleCarers->save();
                    }
                }
            }


            return response()->json([
                'success' => true,
                'data' => @$schedule,
                'message' => 'Schedule added successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

// New code for daily schedule create


    public function dailyaddSchedule(Request $request) {
        try {

            $requestData = json_decode(@$request->data, true); // Convert to array

            //info("md check ". print_r($requestData, true));

            // Define validation rules
            $rules = [
                'date' => 'required|date',
                'shift_type_id' => 'required|in:1,2,3',
                'pick_time' => 'required_if:shift_type_id,1,2|date_format:H:i',
                'drop_time' => 'required_if:shift_type_id,2,3|date_format:H:i',
                'driver_id' => 'required|exists:sub_users,id',
                'vehicle_id' => 'required|exists:vehicles,id',
                'scheduleLocation' => 'required',
                'scheduleCity' => 'required',
                'selectedLocationLat' => 'required',
                'selectedLocationLng' => 'required',
                'is_repeat' => 'nullable|boolean',
                'reacurrance' => 'required_if:is_repeat,1|in:daily,weekly,monthly',
                'repeat_days' => 'required_if:reacurrance,daily',
                'repeat_weeks' => 'required_if:reacurrance,weekly',
                'mon' => 'nullable|boolean',
                'tue' => 'nullable|boolean',
                'wed' => 'nullable|boolean',
                'thu' => 'nullable|boolean',
                'fri' => 'nullable|boolean',
                'sat' => 'nullable|boolean',
                'sun' => 'nullable|boolean',
                'repeat_months' => 'required_if:reacurrance,monthly',
                'repeat_day_of_month' => 'required_if:reacurrance,monthly',
                'end_date' => 'nullable|date|after_or_equal:date',
            ];

            // Create validator instance
            $validator = Validator::make($requestData, $rules);


            // Perform validation
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }


            $request = json_decode($request->data);


            //check for holiday

            if ($this-> isHoliday($request-> date)) {
                return response()-> json([
                    'success' => false,
                    'message' => 'The selected date is a holiday',
                ], 500);
            }

            $shiftTypeMap = [
                1 => 'pick',
                2 => 'pick-drop',
                3 => 'drop',
            ];

            // Get the mapped shift type string
            $shiftTypes = $shiftTypeMap[$request->shift_type_id] ?? null;

            if (!$shiftTypes) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid shift type provided.',
                ], 422);
            }

            // Check if a schedule with conflicting or duplicate shift_type already exists for the carer on the given date
            foreach($request-> carers as $carer) {
                if ($shiftTypes === 'pick-drop') {
                    // Check if a 'pick-drop' schedule already exists
                    $existingPickDropSchedules = DailyScheduleCarer::join('daily_schedules', 'daily_schedule_carers.schedule_id', '=', 'daily_schedules.id')-> where('daily_schedule_carers.carer_id', $carer)->where('daily_schedule_carers.shift_type', 'pick-drop')->where(function($query) use($request) {
                        $query->where('daily_schedules.date', $request-> date)->orWhere(function($query) use($request) {
                            $query->where('daily_schedules.end_date', '>=', $request-> date)->where('daily_schedules.date', '<=', $request-> date);
                        });
                    })-> get();

                    if ($existingPickDropSchedules->isNotEmpty()) {
                        return response()->json([
                            'success' => false,
                            'message' => "A 'pick-drop' schedule already exists for carer on {$request->date}. Cannot create another 'pick-drop'.",
                        ], 422);
                    }

                    // Check if either 'pick' or 'drop' already exists for the carer on the given date
                    $conflictingSchedules = DailyScheduleCarer::join('daily_schedules', 'daily_schedule_carers.schedule_id', '=', 'daily_schedules.id')->where('daily_schedule_carers.carer_id', $carer)-> whereIn('daily_schedule_carers.shift_type', ['pick', 'drop'])-> where(function($query) use($request) {
                        $query-> where('daily_schedules.date', $request->date)->orWhere(function($query) use($request) {
                            $query-> where('daily_schedules.end_date', '>=', $request-> date)->where('daily_schedules.date', '<=', $request-> date);
                        });
                    })-> get();

                    if ($conflictingSchedules-> isNotEmpty()) {
                        foreach($conflictingSchedules as $conflictingSchedule) {
                            $conflictType = $conflictingSchedule-> shift_type;

                            if ($conflictType === 'drop') {
                                return response()-> json([
                                    'success' => false,
                                    'message' => "A 'drop' schedule already exists for carer on {$request->date}.",
                                ], 422);
                            }
                            elseif($conflictType === 'pick') {
                                return response()-> json([
                                    'success' => false,
                                    'message' => "A 'pick' schedule already exists for carer on {$request->date}. Cannot create 'pick-drop'.",
                                ], 422);
                            }
                        }
                    }
                }
                elseif($shiftTypes === 'pick') {
                    $conflictingSchedules = DailyScheduleCarer::join('daily_schedules', 'daily_schedule_carers.schedule_id', '=', 'daily_schedules.id')->where('daily_schedule_carers.carer_id', $carer)->whereIn('daily_schedule_carers.shift_type', ['pick', 'pick-drop'])->where(function($query) use($request) {
                        $query->where('daily_schedules.date', $request->date)->orWhere(function($query) use($request) {
                            $query-> where('daily_schedules.end_date', '>=', $request->date)->where('daily_schedules.date', '<=', $request-> date);
                        });
                    })->get();

                    if ($conflictingSchedules->isNotEmpty()) {
                        foreach($conflictingSchedules as $conflictingSchedule) {
                            $conflictType = $conflictingSchedule-> shift_type;

                            if ($conflictType === 'pick') {
                                return response()-> json([
                                    'success' => false,
                                    'message' => "A 'pick' schedule already exists for carer on {$request->date}.",
                                ], 422);
                            }
                            elseif($conflictType === 'pick-drop') {
                                return response()->json([
                                    'success' => false,
                                    'message' => "A 'pick-drop' schedule already exists for carer on {$request->date}. Cannot create 'drop'.",
                                ], 422);
                            }
                        }
                    }
                }
                elseif($shiftTypes === 'drop') {
                    // Check for conflicting schedules
                    $conflictingSchedules = DailyScheduleCarer::join('daily_schedules', 'daily_schedule_carers.schedule_id', '=', 'daily_schedules.id')->where('daily_schedule_carers.carer_id', $carer)->whereIn('daily_schedule_carers.shift_type', ['drop', 'pick-drop'])->where(function($query) use($request) {
                        $query->where('daily_schedules.date', $request-> date)->orWhere(function($query) use($request) {
                            $query->where('daily_schedules.end_date', '>=', $request->date)->where('daily_schedules.date', '<=', $request->date);
                        });
                    })-> get();

                    if ($conflictingSchedules->isNotEmpty()) {
                        foreach($conflictingSchedules as $conflictingSchedule) {
                            // Determine the type of conflict
                            $conflictType = $conflictingSchedule->shift_type;

                            if ($conflictType === 'drop') {
                                return response()->json([
                                    'success' => false,
                                    'message' => "A 'drop' schedule already exists for carer on {$request->date}.",
                                ], 422);
                            }
                            elseif($conflictType === 'pick-drop') {
                                return response()->json([
                                    'success' => false,
                                    'message' => "A 'pick-drop' schedule already exists for carer on {$request->date}. Cannot create 'drop'.",
                                ], 422);
                            }
                        }
                    }
                }

            }

            // Proceed with adding the schedule if no conflicts exist
            // ...

            // check driver if have multiple_schedule on 1
            $checkMultiple = DB::table('ride_settings')->first();

            // Check driver schedule or not
            // $checkDriverSchedule = Schedule::where('driver_id', $request->driver_id)
            //     ->where(function ($query) use ($request) {
            //         $query->where(function ($query) use ($request) {
            //             $query->where('date', '=', $request->date)
            //                 ->orWhere(function ($query) use ($request) {
            //                     $query->where('date', '<', $request->date)
            //                         ->where('is_repeat', 1)
            //                         ->where('end_date', '<=', $request->date);
            //                 });
            //         });
            //     })
            //     ->count();

            // $is_error = 0;
            // $is_end_error = 0;






            // if ($checkMultiple->multiple_schedule == 0 && $checkDriverSchedule > 0) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'You can add only one schedule at a time.',
            //     ], 500);
            // }




            if ($checkMultiple->multiple_schedule == 1) {

                $scData = $this->dailycheckSchedule($request);
                $hours = ($checkMultiple->multiple_schedule_hr == null) ? 1 : $checkMultiple->multiple_schedule_hr;
                if ($checkMultiple-> multiple_schedule_hr == 4 && @$scData['is_error'] == 1) {
                    return response()->json([
                        'success' => false,
                        'message' => "Schedule start time should be 50 minutes before or can add 50 minutes after existing driver schedule!",
                    ], 500);
                }
                if ($checkMultiple->multiple_schedule_hr == 4 && @$scData['is_end_error'] == 1) {
                    return response()->json([
                        'success' => false,
                        'message' => "Schedule start time should be 50 minutes before or can add 50 minutes after existing driver schedule!",
                    ], 500);
                }
                if (@$scData['is_error'] == 1) {
                    return response()->json([
                        'success' => false,
                        'message' => "Schedule start time should be {$hours} hour before or can add {$hours} hour after existing driver schedule!",
                    ], 500);
                }

                if (@$scData['is_end_error'] == 1) {
                    return response()->json([
                        'success' => false,
                        'message' => "Schedule end time should be {$hours} hour before or can add {$hours} hour after existing driver schedule!",
                    ], 500);
                }
            } else {
                $scData = $this->checkSchedule($request);
                // echo '<pre>';print_r($scData['schedules']);die;
                if ($scData['schedules']->count() > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can add only one schedule at a time 0.',
                    ], 500);
                }
            }

            if ($checkMultiple->female_safety == 1) {
                // Check male exist or not in schedule
                $checkData = DB::table('sub_users')->whereIn('id', $request->carers)->whereRaw('LOWER(gender) = ?', ['male'])-> count();


                if ($checkData == 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only female employee can`t add, Please choose male employe also then can add!',
                    ], 500);
                }
            }

            // echo 'Continue to add schedule';die;

            $week_arr = array();
            $schedule = new DailySchedule();
            $schedule->date = $request->date;

            //  $schedule->start_time = $request->date . ' ' . @$request->pick_time . ':00';
            $schedule->start_time = $request->date.
            ' '.($request->pick_time ? $request->pick_time.
                ':00' : '00:00:00');
            $schedule->shift_finishes_next_day = $request->shift_type_id == 2 ? ($request->shift_finishes_next_day == 1 ? 1 : 0) : 0;

            //$schedule->end_time = $request->date . ' ' . @$request->drop_time . ':00';
            $schedule ->end_time = $request->date.
            ' '.($request->drop_time ? $request->drop_time.
                ':00' : '00:00:00');
            $schedule->driver_id = $request->driver_id;
            $schedule->locality = $request->scheduleLocation;
            $schedule->city = $request->scheduleCity;
            $schedule->latitude = $request->selectedLocationLat;
            $schedule->longitude = $request->selectedLocationLng;
            $schedule->vehicle_id = $request->vehicle_id;
            $schedule->previous_day_pick = $request->previous_day_pick;
            $schedule->is_repeat = @$request->is_repeat == 1 ? 1 : 0;
            if (@$request->is_repeat == 1) {
                if ($request->reacurrance == "daily") {
                    $schedule->reacurrance = 0;
                    $schedule->repeat_time = $request->repeat_days;
                } else if ($request->reacurrance == "weekly") {
                    $schedule->reacurrance = 1;
                    $schedule->repeat_time = $request->repeat_weeks;
                    if (@$request->mon == 1) {
                        array_push($week_arr, "mon");
                    }
                    if (@$request->tue == 1) {
                        array_push($week_arr, "tue");
                    }
                    if (@$request->wed == 1) {
                        array_push($week_arr, "wed");
                    }
                    if (@$request->thu == 1) {
                        array_push($week_arr, "thu");
                    }
                    if (@$request->fri == 1) {
                        array_push($week_arr, "fri");
                    }
                    if (@$request->sat == 1) {
                        array_push($week_arr, "sat");
                    }
                    if (@$request->sun == 1) {
                        array_push($week_arr, "sun");
                    }
                    $schedule->occurs_on = json_encode($week_arr);
                } else if ($request->reacurrance == "monthly") {
                    $schedule->reacurrance = 2;
                    $schedule->repeat_time = $request->repeat_months;
                    $schedule->occurs_on = $request->repeat_day_of_month;
                }
                $schedule->end_date = $request->end_date;
            }

            if ($request->shift_type_id) {
                $schedule->shift_type_id = $request->shift_type_id;
            }
            if ($request->pricebook_id) {
                $schedule->pricebook_id = $request->pricebook_id;
            }
            $schedule->save();


            if ($request->shift_type_id == 2 || $request->shift_type_id == 1) {
                if ($request->carers) {
                    foreach($request->carers as $carer) {
                        info("care save ". $carer);
                        $scheduleCarers = new DailyScheduleCarer();
                        $scheduleCarers->schedule_id = $schedule->id;
                        $scheduleCarers->carer_id = $carer;
                        $scheduleCarers->shift_type = "pick";
                        $scheduleCarers->save();
                    }
                }
            }

            if ($request->shift_type_id == 2 || $request->shift_type_id == 3) {
                if ($request->carers) {
                    foreach($request->carers as $carer) {
                        $scheduleCarers = new DailyScheduleCarer();
                        $scheduleCarers->schedule_id = $schedule->id;
                        $scheduleCarers->carer_id = $carer;
                        $scheduleCarers->shift_type = "drop";
                        $scheduleCarers->save();
                    }
                }
            }


            return response()->json([
                'success' => true,
                'data' => @$schedule,
                'message' => 'Schedule added successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


// end new code for daily schedule



    public function dailycheckSchedule($request)
    {

        $dates = [$request->date];
        $schedule_id_arr = array();

        $schedules = DailySchedule::where('driver_id', $request->driver_id);
        $holidays = Holiday::whereIn('date', $dates)->pluck('date');

        $schedules = $schedules->get();


        if($schedules){
            foreach ($schedules as $schedule) {
                $exc_dates = array();
                if ($schedule->excluded_dates) {
                    foreach (json_decode($schedule->excluded_dates) as $exc_date) {
                        array_push($exc_dates, Carbon::createFromFormat('Y-m-d', $exc_date));
                    }
                }
                //if ($schedule->reacurrance != 1 || $schedule->reacurrance == 2) {
                $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                $date = $current_date->copy()->format('Y-m-d');
                $day_name = $current_date->copy()->format('D');
                $checkArr = json_decode($schedule->occurs_on);
                if ((!empty($checkArr) && in_array(strtolower($day_name), json_decode($schedule->occurs_on)) && $schedule->reacurrance == 1) || $schedule->reacurrance == 0 || $schedule->reacurrance == null) {
                    if (in_array($date, $dates) && !$holidays->contains($date)) {
                        if ($schedule->shift_type_id == 2) {
                            $schedule->type = "pick and drop";
                            array_push($schedule_id_arr, $schedule->toArray());
                        } else if ($schedule->shift_type_id == 1) {
                            $schedule->type = "pick";
                            array_push($schedule_id_arr, $schedule->toArray());
                        } else if ($schedule->shift_type_id == 3) {
                            $schedule->type = "drop";
                            array_push($schedule_id_arr, $schedule->toArray());
                        }
                    }
                }

                if ($schedule->is_repeat == 1) {

                    if ($schedule->reacurrance == 1 || $schedule->reacurrance == 0) {
                        $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                        $scheduleDate = $current_date->copy();

                        $schedule->end_date = date('Y-m-d', strtotime($schedule->end_date . ' +1 day'));

                        while ($current_date->copy()->startOfWeek() <= $schedule->end_date) {
                            $current_date = $current_date->copy()->startOfWeek() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfWeek();
                            $endofthisweek = $current_date->copy()->endOfWeek();
                            if ($this->dates_in_ranges($current_date->copy()->format('Y-m-d'), $endofthisweek->copy()->format('Y-m-d'), $dates) == true) {
                                while ($current_date->copy() < $endofthisweek & $current_date->copy() < $schedule->end_date) {
                                    $date = $current_date->format('Y-m-d');
                                    $day_name = $current_date->copy()->format('D');
                                    // if (!in_array($current_date, $public_dates)) {
                                    if (!in_array($current_date, $exc_dates) && !$holidays->contains($date)) {
                                        $schedule->date = $current_date->copy()->format('Y-m-d');
                                        if (in_array($date, $dates)) {
                                            if ($schedule->shift_type_id == 2) {
                                                $schedule->type = "pick and drop";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            } else if ($schedule->shift_type_id == 1) {
                                                $schedule->type = "pick";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            } else if ($schedule->shift_type_id == 3) {
                                                $schedule->type = "drop";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            }
                                        }
                                    }

                                    $current_date = $current_date->copy()->addDay();
                                }
                                $current_date = $current_date->copy()->subDay();
                            }
                            $current_date = $current_date->copy()->addWeeks($schedule->repeat_time);
                        }
                    } else if ($schedule->reacurrance == 2) {
                        $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                        $scheduleDate = $current_date->copy();
                        while ($current_date->copy()->startOfMonth() <= $schedule->end_date) {
                            $current_date = $current_date->copy()->startOfMonth() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfMonth();
                            // $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                            $endofthismonth = $current_date->copy()->endOfMonth();
                            if ($this->dates_in_ranges($current_date->copy()->format('Y-m-d'), $endofthismonth->copy()->format('Y-m-d'), $dates) == true) {
                                while ($current_date->copy() < $endofthismonth & $current_date->copy() < $schedule->end_date) {
                                    $date = $current_date->format('Y-m-d');
                                    // if (!in_array($current_date, $public_dates)) {
                                    if (!in_array($current_date, $exc_dates) && !$holidays->contains($date)) {
                                        $schedule->date = $current_date->copy()->format('Y-m-d');
                                        if (in_array($date, $dates)) {
                                            if ($schedule->shift_type_id == 2) {
                                                $schedule->type = "pick and drop";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            } else if ($schedule->shift_type_id == 1) {
                                                $schedule->type = "pick";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            } else if ($schedule->shift_type_id == 3) {
                                                $schedule->type = "drop";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            }
                                        }

                                    }

                                    $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                                }
                                $current_date = $current_date->copy()->subDays($schedule->occurs_on);
                            }
                            $current_date = $current_date->copy()->addMonths($schedule->repeat_time);
                        }
                    }
                }
            }

        }

        // New code start
        $this->data['schedules'] = collect($schedule_id_arr);

        $rideSetting = DB::table('ride_settings')->first();
        if ($rideSetting->multiple_schedule_hr == 4) {
            // Set the number of minutes for multiple_schedule_hr
            $gap_in_minutes = 50; // Fixed 50 minutes gap

            foreach ($this->data['schedules'] as $driver) {
                $scheduleTime = date('H:i', strtotime($driver['start_time'])) . ':00';
                info( 'ethe' . $scheduleTime);
                $finalDate = $driver['date'] . ' ' . $scheduleTime;
                $start_time = strtotime($finalDate);

                if ($request->shift_type_id == 1) {
                    $current_time = strtotime($request->date . ' ' . $request->pick_time . ':00');
                    $gap_before_start = $start_time - ($gap_in_minutes * 60);
                    $gap_after_start = $start_time + ($gap_in_minutes * 60); // Add 50 minutes
                    if (($current_time >= $gap_before_start && $current_time <= $gap_after_start)) {
                $time_diff = abs($current_time - $start_time); // Get absolute difference
                if ($time_diff == $gap_in_minutes * 60) {
                    // If the gap is exactly 50 minutes, create schedule logic here
                    $this->data['is_error'] = 0;
                } else {
                    $this->data['is_error'] = 1; // If the gap is not 50 minutes, set error
                }
            }
                } elseif ($request->shift_type_id == 2 || $request->shift_type_id == 3) {
                    // Determine pick or drop time
                    $finalTime = @$request->pick_time ? $request->pick_time : $request->drop_time;
                    $current_time = strtotime($request->date . ' ' . $finalTime . ':00');

                    // Check 50 minutes before and after start time
                    $gap_before_start = $start_time - ($gap_in_minutes *60);
                    $gap_after_start = $start_time + ($gap_in_minutes *60);

                    // if ($current_time >= $gap_before_start && $current_time <= $gap_after_start) {
                    //     $is_error = 1;
                    // }
                    if (($current_time >= $gap_before_start && $current_time <= $gap_after_start)) {
                        $time_diff = abs($current_time - $start_time); // Get absolute difference
                        if ($time_diff == $gap_in_minutes * 60) {
                            // If the gap is exactly 50 minutes, create schedule logic here
                            $this->data['is_error'] = 0;
                        } else {
                            $this->data['is_error'] = 1; // If the gap is not 50 minutes, set error
                        }
                    }
                    // Check for end time logic
                    $scheduleEndTime = date('H:i', strtotime($driver['end_time'])) . ':00';
                    $endDate = $driver['date'] . ' ' . $scheduleEndTime;
                    $end_time = strtotime($endDate);

                    $gap_before_end = $end_time - ($gap_in_minutes *60);
                    $gap_after_end = $end_time + ($gap_in_minutes *60);

                    // if ($current_time >= $gap_before_end && $current_time <= $gap_after_end) {
                    //     $is_end_error = 1;
                    // }
                    if (($current_time >= $gap_before_start && $current_time <= $gap_after_start)) {
                        $time_diff = abs($current_time - $start_time); // Get absolute difference
                        if ($time_diff == $gap_in_minutes * 60) {
                            // If the gap is exactly 50 minutes, create schedule logic here
                            $this->data['is_error'] = 0;
                        } else {
                            $this->data['is_error'] = 1; // If the gap is not 50 minutes, set error
                        }
                    }
                }

                // Set error flags
                if (isset($is_error) && $is_error == 1) {
                    $this->data['is_error'] = 1;
                }

                if (isset($is_end_error) && $is_end_error == 1) {
                    $this->data['is_end_error'] = 1;
                }
            }

            return $this->data;
        }
        else{
        // $hours = ($rideSetting->multiple_schedule_hr == null) ? 1 : $rideSetting->multiple_schedule_hr;
        $hours = (in_array($rideSetting->multiple_schedule_hr, [1, 2, 3]))
        ? $rideSetting->multiple_schedule_hr
        : 1;
            $time = ($hours * 60) - 1;

            foreach ($this->data['schedules']  as $driver) {
                // Start time

                if ($request->shift_type_id == 1) {
                    $scheduleTime = date('H:i', strtotime($driver['start_time'])) . ':00';
                    $finalDate = $driver['date'] . ' ' . $scheduleTime;
                    $start_time = strtotime($finalDate);
                    // echo $finalDate;die;
                    // Current time
                    $current_time = strtotime($request->date . ' ' . @$request->pick_time . ':00');
                    $one_hour_before_start = $start_time - ($time * 60); // 59 minutes before start time
                    $one_hour_after_start = $start_time + ($time * 60); // 59 minutes after start time

                    if ($current_time >= $one_hour_before_start && $current_time <= $one_hour_after_start) {
                        $is_error = 1;
                    }
                } else if ($request->shift_type_id == 2 || $request->shift_type_id == 3) {

                    // if ($request->shift_type_id != 3) {
                    $scheduleTime = date('H:i', strtotime($driver['start_time'])) . ':00';
                    $finalDate = $driver['date'] . ' ' . $scheduleTime;
                    $start_time = strtotime($finalDate);
                    // echo $finalDate;die;
                    // Current time

                    if (@$request->pick_time) {
                        $finalTime = @$request->pick_time;
                    } else {
                        $finalTime = @$request->drop_time;
                    }


                    $current_time = strtotime($request->date . ' ' . @$finalTime . ':00');
                    $one_hour_before_start = $start_time - ($time * 60); // 59 minutes before start time
                    $one_hour_after_start = $start_time + ($time * 60); // 59 minutes after start time

                    if ($current_time >= $one_hour_before_start && $current_time <= $one_hour_after_start) {
                        $is_error = 1;
                    }
                    // }




                    $scheduleTime = date('H:i', strtotime($driver['end_time'])) . ':00';
                    $finalDate = $driver['date'] . ' ' . $scheduleTime;


                    $end_time = strtotime($finalDate);

                    // Current time

                    $current_time = strtotime($request->date . ' ' . @$finalTime . ':00');

                    $one_hour_before_end = $end_time - ($time * 60); // 59 minutes before start time
                    $one_hour_after_end = $end_time + ($time * 60); // 59 minutes after start time

                    // Check if the current time falls within the allowable range
                    if ($current_time >= $one_hour_before_end && $current_time <= $one_hour_after_end) {
                        $is_end_error = 1;
                    }
                }

                if (@$is_error == 1) {
                    $this->data['is_error'] = 1;
                }

                if (@$is_end_error == 1) {
                    $this->data['is_end_error'] = 1;
                }
            }

            return $this->data;
        }


    }




    //************ Check holiday  function ************************************** */
    public function isHoliday($date)
    {
        $holiday = Holiday::whereDate('date', $date)->first();
        return $holiday !== null;
    }


    //************** end of function ********************************************/

    public function checkSchedule($request)
    {

        $dates = [$request->date];
        $schedule_id_arr = array();



        $schedules = Schedule::where('driver_id', $request->driver_id);


        $holidays = Holiday::whereIn('date', $dates)->pluck('date');

        $schedules = $schedules->get();


        if($schedules){
            foreach ($schedules as $schedule) {
                $exc_dates = array();
                if ($schedule->excluded_dates) {
                    foreach (json_decode($schedule->excluded_dates) as $exc_date) {
                        array_push($exc_dates, Carbon::createFromFormat('Y-m-d', $exc_date));
                    }
                }
                //if ($schedule->reacurrance != 1 || $schedule->reacurrance == 2) {
                $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                $date = $current_date->copy()->format('Y-m-d');
                $day_name = $current_date->copy()->format('D');
                $checkArr = json_decode($schedule->occurs_on);
                if ((!empty($checkArr) && in_array(strtolower($day_name), json_decode($schedule->occurs_on)) && $schedule->reacurrance == 1) || $schedule->reacurrance == 0 || $schedule->reacurrance == null) {
                    if (in_array($date, $dates) && !$holidays->contains($date)) {
                        if ($schedule->shift_type_id == 2) {
                            $schedule->type = "pick and drop";
                            array_push($schedule_id_arr, $schedule->toArray());
                        } else if ($schedule->shift_type_id == 1) {
                            $schedule->type = "pick";
                            array_push($schedule_id_arr, $schedule->toArray());
                        } else if ($schedule->shift_type_id == 3) {
                            $schedule->type = "drop";
                            array_push($schedule_id_arr, $schedule->toArray());
                        }
                    }
                }
                // if ($date == $previous_date) {
                //     if ($schedule->shift_finishes_next_day == 1) {
                //         $schedule->type = "drop";
                //         array_push($schedule_id_arr, $schedule->toArray());
                //     }
                // }
                // }
                if ($schedule->is_repeat == 1) {
                    // if ($schedule->reacurrance == 0) {
                    //     $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date)->addDays($schedule->repeat_time);

                    //     $schedule->end_date = date('Y-m-d', strtotime($schedule->end_date . ' +1 day'));

                    //     while ($current_date <= $schedule->end_date) {
                    //         $date = $current_date->format('Y-m-d');
                    //         // if (!in_array($current_date, $public_dates)) {
                    //         if (!in_array($current_date, $exc_dates) && !$holidays->contains($date)) {
                    //             $schedule->date = $current_date->copy()->format('Y-m-d');
                    //             if (in_array($date, $dates)) {
                    //                 if ($schedule->shift_type_id == 2) {
                    //                     $schedule->type = "pick and drop";
                    //                     array_push($schedule_id_arr, $schedule->toArray());
                    //                 } else if ($schedule->shift_type_id == 1) {
                    //                     $schedule->type = "pick";
                    //                     array_push($schedule_id_arr, $schedule->toArray());
                    //                 } else if ($schedule->shift_type_id == 3) {
                    //                     $schedule->type = "drop";
                    //                     array_push($schedule_id_arr, $schedule->toArray());
                    //                 }
                    //             }

                    //             // if ($preDate == $previous_date && $date <= $preDate) {


                    //             //     if ($schedule->shift_finishes_next_day == 1 && $schedule->reacurrance == 0) {
                    //             //         error_reporting(0);
                    //             //         $schedule->type = "drop";

                    //             //         array_push($schedule_id_arr, $schedule->toArray());
                    //             //         $schedule->shift_finishes_next_day = 0;
                    //             //     }
                    //             // }

                    //             // else if ($date == $previous_date) {
                    //             //     if ($schedule->shift_finishes_next_day == 1) {
                    //             //         $schedule->type = "drop";
                    //             //         array_push($schedule_id_arr, $schedule->toArray());
                    //             //     }
                    //             // }
                    //         }
                    //         // }
                    //         $current_date = $current_date->addDays($schedule->repeat_time);
                    //     }
                    // }
                    if ($schedule->reacurrance == 1 || $schedule->reacurrance == 0) {
                        $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                        $scheduleDate = $current_date->copy();

                        $schedule->end_date = date('Y-m-d', strtotime($schedule->end_date . ' +1 day'));

                        while ($current_date->copy()->startOfWeek() <= $schedule->end_date) {
                            $current_date = $current_date->copy()->startOfWeek() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfWeek();
                            $endofthisweek = $current_date->copy()->endOfWeek();
                            if ($this->dates_in_ranges($current_date->copy()->format('Y-m-d'), $endofthisweek->copy()->format('Y-m-d'), $dates) == true) {
                                while ($current_date->copy() < $endofthisweek & $current_date->copy() < $schedule->end_date) {
                                    $date = $current_date->format('Y-m-d');
                                    $day_name = $current_date->copy()->format('D');
                                    // if (!in_array($current_date, $public_dates)) {
                                    if (!in_array($current_date, $exc_dates) && !$holidays->contains($date)) {
                                        $schedule->date = $current_date->copy()->format('Y-m-d');
                                        if (in_array($date, $dates)) {
                                            if ($schedule->shift_type_id == 2) {
                                                $schedule->type = "pick and drop";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            } else if ($schedule->shift_type_id == 1) {
                                                $schedule->type = "pick";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            } else if ($schedule->shift_type_id == 3) {
                                                $schedule->type = "drop";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            }
                                        }
                                        // else if ($date == $previous_date & in_array(strtolower($day_name), json_decode($schedule->occurs_on))) {
                                        //     if ($schedule->shift_finishes_next_day == 1) {
                                        //         $schedule->type = "drop";
                                        //         array_push($schedule_id_arr, $schedule->toArray());
                                        //     }
                                        // }
                                    }
                                    // }
                                    $current_date = $current_date->copy()->addDay();
                                }
                                $current_date = $current_date->copy()->subDay();
                            }
                            $current_date = $current_date->copy()->addWeeks($schedule->repeat_time);
                        }
                    } else if ($schedule->reacurrance == 2) {
                        $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                        $scheduleDate = $current_date->copy();
                        while ($current_date->copy()->startOfMonth() <= $schedule->end_date) {
                            $current_date = $current_date->copy()->startOfMonth() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfMonth();
                            // $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                            $endofthismonth = $current_date->copy()->endOfMonth();
                            if ($this->dates_in_ranges($current_date->copy()->format('Y-m-d'), $endofthismonth->copy()->format('Y-m-d'), $dates) == true) {
                                while ($current_date->copy() < $endofthismonth & $current_date->copy() < $schedule->end_date) {
                                    $date = $current_date->format('Y-m-d');
                                    // if (!in_array($current_date, $public_dates)) {
                                    if (!in_array($current_date, $exc_dates) && !$holidays->contains($date)) {
                                        $schedule->date = $current_date->copy()->format('Y-m-d');
                                        if (in_array($date, $dates)) {
                                            if ($schedule->shift_type_id == 2) {
                                                $schedule->type = "pick and drop";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            } else if ($schedule->shift_type_id == 1) {
                                                $schedule->type = "pick";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            } else if ($schedule->shift_type_id == 3) {
                                                $schedule->type = "drop";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            }
                                        }
                                        // else if ($date == $previous_date) {
                                        //     if ($schedule->shift_finishes_next_day == 1) {
                                        //         $schedule->type = "drop";
                                        //         array_push($schedule_id_arr, $schedule->toArray());
                                        //     }
                                        // }
                                    }
                                    // }
                                    $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                                }
                                $current_date = $current_date->copy()->subDays($schedule->occurs_on);
                            }
                            $current_date = $current_date->copy()->addMonths($schedule->repeat_time);
                        }
                    }
                }
            }

        }

        // New code start
        $this->data['schedules'] = collect($schedule_id_arr);
       // info('test' . $this->data['schedules']);
        // First, check if schedules are available

        // if ($this->data['schedules']->isEmpty()) {
        //     // Log when no schedules are found
        //     info('No schedules found. Checking last schedule of the driver.');
        // }
        //info( $this->data['schedules']);
        $rideSetting = DB::table('ride_settings')->first();
        if ($rideSetting->multiple_schedule_hr == 4) {
            // Set the number of minutes for multiple_schedule_hr
            $gap_in_minutes = 50; // Fixed 50 minutes gap

            foreach ($this->data['schedules'] as $driver) {
                $scheduleTime = date('H:i', strtotime($driver['start_time'])) . ':00';
              //  info( 'ethe' . $scheduleTime);
                $finalDate = $driver['date'] . ' ' . $scheduleTime;
                $start_time = strtotime($finalDate);

                if ($request->shift_type_id == 1) {
                    $current_time = strtotime($request->date . ' ' . $request->pick_time . ':00');
                    $gap_before_start = $start_time - ($gap_in_minutes * 60);
                    $gap_after_start = $start_time + ($gap_in_minutes * 60); // Add 50 minutes
                   // $current_time = strtotime($request->date . ' ' . @$request->pick_time . ':00');
                   // $one_hour_before_start = $start_time - ($time * 60); // 59 minutes before start time
                  //  $one_hour_after_start = $start_time + ($time * 60); // 59 minutes after start time
                    // Check if current time falls within 50 minutes after start time
                    // if ($current_time >=  $gap_before_start && $current_time <= $gap_after_start) {
                    //     $is_error = 1;
                    // }
                      if (($current_time >= $gap_before_start && $current_time <= $gap_after_start)) {
                $time_diff = abs($current_time - $start_time); // Get absolute difference
                if ($time_diff == $gap_in_minutes * 60) {
                    // If the gap is exactly 50 minutes, create schedule logic here
                    $this->data['is_error'] = 0;
                } else {
                    $this->data['is_error'] = 1; // If the gap is not 50 minutes, set error
                }
            }
                } elseif ($request->shift_type_id == 2 || $request->shift_type_id == 3) {
                    // Determine pick or drop time
                    $finalTime = @$request->pick_time ? $request->pick_time : $request->drop_time;
                    $current_time = strtotime($request->date . ' ' . $finalTime . ':00');

                    // Check 50 minutes before and after start time
                    $gap_before_start = $start_time - ($gap_in_minutes *60);
                    $gap_after_start = $start_time + ($gap_in_minutes *60);

                    // if ($current_time >= $gap_before_start && $current_time <= $gap_after_start) {
                    //     $is_error = 1;
                    // }
                    if (($current_time >= $gap_before_start && $current_time <= $gap_after_start)) {
                        $time_diff = abs($current_time - $start_time); // Get absolute difference
                        if ($time_diff == $gap_in_minutes * 60) {
                            // If the gap is exactly 50 minutes, create schedule logic here
                            $this->data['is_error'] = 0;
                        } else {
                            $this->data['is_error'] = 1; // If the gap is not 50 minutes, set error
                        }
                    }
                    // Check for end time logic
                    $scheduleEndTime = date('H:i', strtotime($driver['end_time'])) . ':00';
                    $endDate = $driver['date'] . ' ' . $scheduleEndTime;
                    $end_time = strtotime($endDate);

                    $gap_before_end = $end_time - ($gap_in_minutes *60);
                    $gap_after_end = $end_time + ($gap_in_minutes *60);

                    // if ($current_time >= $gap_before_end && $current_time <= $gap_after_end) {
                    //     $is_end_error = 1;
                    // }
                    if (($current_time >= $gap_before_start && $current_time <= $gap_after_start)) {
                        $time_diff = abs($current_time - $start_time); // Get absolute difference
                        if ($time_diff == $gap_in_minutes * 60) {
                            // If the gap is exactly 50 minutes, create schedule logic here
                            $this->data['is_error'] = 0;
                        } else {
                            $this->data['is_error'] = 1; // If the gap is not 50 minutes, set error
                        }
                    }
                }

                // Set error flags
                if (isset($is_error) && $is_error == 1) {
                    $this->data['is_error'] = 1;
                }

                if (isset($is_end_error) && $is_end_error == 1) {
                    $this->data['is_end_error'] = 1;
                }
            }

            return $this->data;
        }
        else{
        // $hours = ($rideSetting->multiple_schedule_hr == null) ? 1 : $rideSetting->multiple_schedule_hr;
        $hours = (in_array($rideSetting->multiple_schedule_hr, [1, 2, 3]))
        ? $rideSetting->multiple_schedule_hr
        : 1;
            $time = ($hours * 60) - 1;

            foreach ($this->data['schedules']  as $driver) {
                // Start time

                if ($request->shift_type_id == 1) {
                    $scheduleTime = date('H:i', strtotime($driver['start_time'])) . ':00';
                    $finalDate = $driver['date'] . ' ' . $scheduleTime;
                    $start_time = strtotime($finalDate);
                    // echo $finalDate;die;
                    // Current time
                    $current_time = strtotime($request->date . ' ' . @$request->pick_time . ':00');
                    $one_hour_before_start = $start_time - ($time * 60); // 59 minutes before start time
                    $one_hour_after_start = $start_time + ($time * 60); // 59 minutes after start time

                    if ($current_time >= $one_hour_before_start && $current_time <= $one_hour_after_start) {
                        $is_error = 1;
                    }
                } else if ($request->shift_type_id == 2 || $request->shift_type_id == 3) {

                    // if ($request->shift_type_id != 3) {
                    $scheduleTime = date('H:i', strtotime($driver['start_time'])) . ':00';
                    $finalDate = $driver['date'] . ' ' . $scheduleTime;
                    $start_time = strtotime($finalDate);
                    // echo $finalDate;die;
                    // Current time

                    if (@$request->pick_time) {
                        $finalTime = @$request->pick_time;
                    } else {
                        $finalTime = @$request->drop_time;
                    }


                    $current_time = strtotime($request->date . ' ' . @$finalTime . ':00');
                    $one_hour_before_start = $start_time - ($time * 60); // 59 minutes before start time
                    $one_hour_after_start = $start_time + ($time * 60); // 59 minutes after start time

                    if ($current_time >= $one_hour_before_start && $current_time <= $one_hour_after_start) {
                        $is_error = 1;
                    }
                    // }




                    $scheduleTime = date('H:i', strtotime($driver['end_time'])) . ':00';
                    $finalDate = $driver['date'] . ' ' . $scheduleTime;


                    $end_time = strtotime($finalDate);

                    // Current time

                    $current_time = strtotime($request->date . ' ' . @$finalTime . ':00');

                    // if ($request->shift_type_id == 3) {
                    //     $current_time = strtotime($request->date . ' ' . @$request->pick_time . ':00');
                    // } else {
                    //     $current_time = strtotime($request->date . ' ' . @$request->drop_time . ':00');
                    // }

                    $one_hour_before_end = $end_time - ($time * 60); // 59 minutes before start time
                    $one_hour_after_end = $end_time + ($time * 60); // 59 minutes after start time

                    // Check if the current time falls within the allowable range
                    if ($current_time >= $one_hour_before_end && $current_time <= $one_hour_after_end) {
                        $is_end_error = 1;
                    }
                }

                if (@$is_error == 1) {
                    $this->data['is_error'] = 1;
                }

                if (@$is_end_error == 1) {
                    $this->data['is_end_error'] = 1;
                }
            }

            return $this->data;
        }


    }

    public function checkSchedule_old($request)
    {

        $dates = [$request->date];
        $schedule_id_arr = array();



        $schedules = Schedule::where('driver_id', $request->driver_id);


        $holidays = Holiday::whereIn('date', $dates)->pluck('date');

        $schedules = $schedules->get();



        foreach ($schedules as $schedule) {
            $exc_dates = array();
            if ($schedule->excluded_dates) {
                foreach (json_decode($schedule->excluded_dates) as $exc_date) {
                    array_push($exc_dates, Carbon::createFromFormat('Y-m-d', $exc_date));
                }
            }

            $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
            $date = $current_date->copy()->format('Y-m-d');

            if ($schedule->is_repeat == 1) {
                if ($schedule->reacurrance == 0) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                    while ($current_date < $schedule->end_date) {
                        $date = $current_date->format('Y-m-d');
                        // if (!in_array($current_date, $public_dates)) {
                        if (!in_array($current_date, $exc_dates)) {
                            $schedule->date = $current_date->copy()->format('Y-m-d');

                            if ($schedule->shift_type_id == 2) {
                                $schedule->type = "pick and drop";
                                array_push($schedule_id_arr, $schedule->toArray());
                            } else if ($schedule->shift_type_id == 1) {
                                $schedule->type = "pick";
                                array_push($schedule_id_arr, $schedule->toArray());
                            } else if ($schedule->shift_type_id == 3) {
                                $schedule->type = "drop";
                                array_push($schedule_id_arr, $schedule->toArray());
                            }

                            // else if ($date == $previous_date) {
                            //     if ($schedule->shift_finishes_next_day == 1) {
                            //         $schedule->type = "drop";
                            //         array_push($schedule_id_arr, $schedule->toArray());
                            //     }
                            // }
                        }
                        // }
                        $current_date = $current_date->addDays($schedule->repeat_time);
                    }
                } else if ($schedule->reacurrance == 1) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                    $scheduleDate = $current_date->copy();
                    while ($current_date->copy()->startOfWeek() < $schedule->end_date) {
                        $current_date = $current_date->copy()->startOfWeek() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfWeek();
                        $endofthisweek = $current_date->copy()->endOfWeek();
                        if ($this->dates_in_ranges($current_date->copy()->format('Y-m-d'), $endofthisweek->copy()->format('Y-m-d'), $dates) == true) {
                            while ($current_date->copy() < $endofthisweek & $current_date->copy() < $schedule->end_date) {
                                $date = $current_date->format('Y-m-d');
                                $day_name = $current_date->copy()->format('D');
                                // if (!in_array($current_date, $public_dates)) {
                                if (!in_array($current_date, $exc_dates)) {
                                    $schedule->date = $current_date->copy()->format('Y-m-d');

                                    if ($schedule->shift_type_id == 2) {
                                        $schedule->type = "pick and drop";
                                        array_push($schedule_id_arr, $schedule->toArray());
                                    } else if ($schedule->shift_type_id == 1) {
                                        $schedule->type = "pick";
                                        array_push($schedule_id_arr, $schedule->toArray());
                                    } else if ($schedule->shift_type_id == 3) {
                                        $schedule->type = "drop";
                                        array_push($schedule_id_arr, $schedule->toArray());
                                    }

                                    // else if ($date == $previous_date & in_array(strtolower($day_name), json_decode($schedule->occurs_on))) {
                                    //     if ($schedule->shift_finishes_next_day == 1) {
                                    //         $schedule->type = "drop";
                                    //         array_push($schedule_id_arr, $schedule->toArray());
                                    //     }
                                    // }
                                }
                                // }
                                $current_date = $current_date->copy()->addDay();
                            }
                            $current_date = $current_date->copy()->subDay();
                        }
                        $current_date = $current_date->copy()->addWeeks($schedule->repeat_time);
                    }
                } else if ($schedule->reacurrance == 2) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                    $scheduleDate = $current_date->copy();
                    while ($current_date->copy()->startOfMonth() < $schedule->end_date) {
                        $current_date = $current_date->copy()->startOfMonth() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfMonth();
                        // $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                        $endofthismonth = $current_date->copy()->endOfMonth();
                        if ($this->dates_in_ranges($current_date->copy()->format('Y-m-d'), $endofthismonth->copy()->format('Y-m-d'), $dates) == true) {
                            while ($current_date->copy() < $endofthismonth & $current_date->copy() < $schedule->end_date) {
                                $date = $current_date->format('Y-m-d');
                                // if (!in_array($current_date, $public_dates)) {
                                if (!in_array($current_date, $exc_dates)) {
                                    $schedule->date = $current_date->copy()->format('Y-m-d');

                                    if ($schedule->shift_type_id == 2) {
                                        $schedule->type = "pick and drop";
                                        array_push($schedule_id_arr, $schedule->toArray());
                                    } else if ($schedule->shift_type_id == 1) {
                                        $schedule->type = "pick";
                                        array_push($schedule_id_arr, $schedule->toArray());
                                    } else if ($schedule->shift_type_id == 3) {
                                        $schedule->type = "drop";
                                        array_push($schedule_id_arr, $schedule->toArray());
                                    }

                                    // else if ($date == $previous_date) {
                                    //     if ($schedule->shift_finishes_next_day == 1) {
                                    //         $schedule->type = "drop";
                                    //         array_push($schedule_id_arr, $schedule->toArray());
                                    //     }
                                    // }
                                }
                                // }
                                $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                            }
                            $current_date = $current_date->copy()->subDays($schedule->occurs_on);
                        }
                        $current_date = $current_date->copy()->addMonths($schedule->repeat_time);
                    }
                }
            } else {
                array_push($schedule_id_arr, $schedule->toArray());
            }
        }


        // New code start
        $this->data['schedules'] = collect($schedule_id_arr);
        $rideSetting = DB::table('ride_settings')->first();
        $hours = ($rideSetting->multiple_schedule_hr == null) ? 1 : $rideSetting->multiple_schedule_hr;
        $time = ($hours * 60) - 1;

        foreach ($this->data['schedules']  as $driver) {
            // Start time

            if ($request->shift_type_id == 1) {
                $scheduleTime = date('H:i', strtotime($driver['start_time'])) . ':00';
                $finalDate = $driver['date'] . ' ' . $scheduleTime;
                $start_time = strtotime($finalDate);
                // echo $finalDate;die;
                // Current time
                $current_time = strtotime($request->date . ' ' . @$request->pick_time . ':00');
                $one_hour_before_start = $start_time - ($time * 60); // 59 minutes before start time
                $one_hour_after_start = $start_time + ($time * 60); // 59 minutes after start time

                if ($current_time >= $one_hour_before_start && $current_time <= $one_hour_after_start) {
                    $is_error = 1;
                }
            } else if ($request->shift_type_id == 2 || $request->shift_type_id == 3) {

                // if ($request->shift_type_id != 3) {
                $scheduleTime = date('H:i', strtotime($driver['start_time'])) . ':00';
                $finalDate = $driver['date'] . ' ' . $scheduleTime;
                $start_time = strtotime($finalDate);
                // echo $finalDate;die;
                // Current time

                if (@$request->pick_time) {
                    $finalTime = @$request->pick_time;
                } else {
                    $finalTime = @$request->drop_time;
                }


                $current_time = strtotime($request->date . ' ' . @$finalTime . ':00');
                $one_hour_before_start = $start_time - ($time * 60); // 59 minutes before start time
                $one_hour_after_start = $start_time + ($time * 60); // 59 minutes after start time

                if ($current_time >= $one_hour_before_start && $current_time <= $one_hour_after_start) {
                    $is_error = 1;
                }
                // }




                $scheduleTime = date('H:i', strtotime($driver['end_time'])) . ':00';
                $finalDate = $driver['date'] . ' ' . $scheduleTime;


                $end_time = strtotime($finalDate);

                // Current time

                $current_time = strtotime($request->date . ' ' . @$finalTime . ':00');

                // if ($request->shift_type_id == 3) {
                //     $current_time = strtotime($request->date . ' ' . @$request->pick_time . ':00');
                // } else {
                //     $current_time = strtotime($request->date . ' ' . @$request->drop_time . ':00');
                // }

                $one_hour_before_end = $end_time - ($time * 60); // 59 minutes before start time
                $one_hour_after_end = $end_time + ($time * 60); // 59 minutes after start time

                // Check if the current time falls within the allowable range
                if ($current_time >= $one_hour_before_end && $current_time <= $one_hour_after_end) {
                    $is_end_error = 1;
                }
            }

            if (@$is_error == 1) {
                $this->data['is_error'] = 1;
            }

            if (@$is_end_error == 1) {
                $this->data['is_end_error'] = 1;
            }
        }

        return $this->data;
    }


// Schedule Position status Enable and Disable

    /**
     * @OA\Post(
     * path="/uc/api/schedulePositionStatus",
     * operationId="PositionStatus",
     * tags={"Ucruise Schedule"},
     * summary="position schedule status",
     *   security={ {"Bearer": {} }},
     * description="position schedule status",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id", "position_status"},
     *               @OA\Property(property="id", type="text"),
     *               @OA\Property(property="position_status", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Schedule data listed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Schedule data listed successfully.",
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

    public function schedulePositionStatus(Request $request){

        try {
            $validatedData = $request->validate([
                'id' => 'required',
                'position_status' => 'required'
            ]);
            $schedule = Schedule::find($request->id);
            if($schedule){
                $schedule->position_status = $request->position_status;
                $schedule->save();
                return response()->json([
                    'success' => true,
                    'message' => 'position statusupdated successfully',
                ], 200);
            }

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);

        }

    }



    // Carer Position change

    /**
     * @OA\Post(
     * path="/uc/api/carerPositionchange",
     * operationId="PositionCarer",
     * tags={"Ucruise Schedule"},
     * summary="position carer status",
     *   security={ {"Bearer": {} }},
     * description="position carer status",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id", "position","schedule_id"},
     *               @OA\Property(property="id", type="text"),
     *               @OA\Property(property="position", type="text"),
     *               @OA\Property(property="schedule_id", type="text"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Carer data listed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Carer data listed successfully.",
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

    public function carerPositionchange(Request $request){

         try {

            $validatedData = $request->validate([
                'id' => 'required',
                'schedule_id' => 'required'
            ]);

           $scheduleCare = ScheduleCarer::where('id',$request->id)->where('schedule_id', $request->schedule_id)->first();
           if($scheduleCare){
                $scheduleCare->position = $request->position;
                $scheduleCare->save();
                return response()->json([
                    'success' => true,
                    'message' => 'position status updated successfully',
                ], 200);
           }


         } catch (\Throwable $th) {

            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);

         }

    }


    //*************************** Edit Schedule listing api **************************/


    /**
     * @OA\Post(
     * path="/uc/api/editScheduleData",
     * operationId="editScheduleData",
     * tags={"Ucruise Schedule"},
     * summary="Edit schedule data",
     *   security={ {"Bearer": {} }},
     * description="Edit schedule data",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id", "date"},
     *               @OA\Property(property="id", type="text"),
     *               @OA\Property(property="date", type="text"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Schedule data listed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Schedule data listed successfully.",
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
    public function editScheduleData(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required',
                'date' => 'required|date_format:Y-m-d'
            ]);
            $d = $request->date;
            $id = $request->id;
            $this->data['company_details'] = CompanyAddresse::whereDate('start_date', '<=', $d)
                ->where(function ($query) use ($d) {
                    $query->whereDate('end_date', '>', $d)
                        ->orWhereNull('end_date');
                })->first();

            // $this->data['schedule'] = Schedule::with('shiftType')->with('driver')->with('pricebook')->with('vehicle')->with(['carers' => function ($q) use ($d,$id) {
            //     $schedule = Schedule::find($id);
            //     if ($schedule && $schedule->position_status == 0) {
            //         $q->orderBy('position', 'asc');
            //     }
            //     $q->whereDoesntHave('carerStatus', function ($statusQuery) use ($d) {
            //         $statusQuery->where('date', $d);
            //         $statusQuery->where('status_id', 4);
            //     });
            // }])->find($id);

            $this->data['schedule'] = Schedule::with([
                'shiftType',
                'driver',
                'pricebook',
                'vehicle',
                'carers.usersdata:id,unique_id,first_name,last_name,email,phone,latitude,longitude,office_distance', // Load carers and their related user
                'carers' => function ($q) use ($d, $id) {
                    $schedule = Schedule::find($id);
                    if ($schedule && $schedule->position_status == 0) {
                        $q->orderBy('position', 'asc');
                    }
                    $q->whereDoesntHave('carerStatus', function ($statusQuery) use ($d) {
                        $statusQuery->where('date', $d);
                        $statusQuery->where('status_id', 4);
                    });
                }
            ])->find($id);

            $schedule = Schedule::find($id);
            $maxEndDate = null;

            if ($schedule) {
                // Find all schedules with the same parent_id or where this schedule is the parent
                $allSchedules = Schedule::where('schedule_parent_id', $schedule->schedule_parent_id ?: $schedule->id)
                    ->orWhere('id', $schedule->schedule_parent_id)
                    ->get();

                // Get the maximum end_date
               // $maxEndDate = $allSchedules->max('end_date');
               if ($allSchedules->isNotEmpty()) {
                $maxEndDate = $allSchedules->max('end_date');
            } else {
                $maxEndDate = $schedule->end_date;
            }
            }

            // Get additional details
            $this->data['max_end_date'] = $maxEndDate;
            $this->data['priceBook'] = PriceBook::orderBy('id', 'DESC')
                ->has('priceBookData')
                ->get();
            $this->data['rideSetting'] = RideSetting::first();
            $this->data['shiftTypes'] = ShiftTypes::get();

            // $this->data['all_drivers'] = SubUser::whereHas("roles", function ($q) {
            //     $q->where("name", "driver");
            // })->where('close_account', 1)
            //     ->leftJoin('sub_user_addresses', function ($join) use ($d) {
            //         $join->on('sub_users.id', '=', 'sub_user_addresses.sub_user_id')
            //             ->whereDate('sub_user_addresses.start_date', '<=', $d)
            //             ->where(function ($query) use ($d) {
            //                 $query->whereDate('sub_user_addresses.end_date', '>', $d)
            //                     ->orWhereNull('sub_user_addresses.end_date');
            //             });
            //     })
            //     ->leftJoin('vehicles', 'sub_users.id', '=', 'vehicles.driver_id')
            //     ->whereNotNull('sub_user_addresses.latitude')
            //     ->whereNotNull('sub_user_addresses.longitude')
            //     ->orderBy("sub_users.id", "DESC")
            //     ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address', 'vehicles.*')
            //     ->get();

            // $this->data['all_employees'] = SubUser::whereHas("roles", function ($q) {
            //     $q->whereIn("name", ['carer', 'archived_staff']);
            // })
            //     ->leftJoin('sub_user_addresses', function ($join) use ($d) {
            //         $join->on('sub_users.id', '=', 'sub_user_addresses.sub_user_id')
            //             ->whereDate('sub_user_addresses.start_date', '<=', $d)
            //             ->where(function ($query) use ($d) {
            //                 $query->whereDate('sub_user_addresses.end_date', '>', $d)
            //                     ->orWhereNull('sub_user_addresses.end_date');
            //             });
            //     })
            //     ->whereNotNull('sub_user_addresses.latitude')
            //     ->whereNotNull('sub_user_addresses.longitude')
            //     ->orderBy("sub_users.id", "DESC")
            //     ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.longitude')
            //     ->get();


            return response()->json([
                'success' => true,
                'data' => @$this->data,
                'message' => 'Schedule updated successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }



    /**
     * @OA\Post(
     * path="/uc/api/dailyeditScheduleData",
     * operationId="dailyeditScheduleData",
     * tags={"Ucruise Schedule"},
     * summary="Daily Edit schedule data",
     *   security={ {"Bearer": {} }},
     * description="Daily Edit schedule data",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id", "date"},
     *               @OA\Property(property="id", type="text"),
     *               @OA\Property(property="date", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Schedule data listed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Schedule data listed successfully.",
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


     public function dailyeditScheduleData(Request $request){

        try {

            $validatedData = $request->validate([
                'id' => 'required',
                'date' => 'required|date_format:Y-m-d'
            ]);
            $d = $request->date;
            $id = $request->id;
            $this->data['company_details'] = CompanyAddresse::whereDate('start_date', '<=', $d)
                ->where(function ($query) use ($d) {
                    $query->whereDate('end_date', '>', $d)
                        ->orWhereNull('end_date');
                })->first();

            $this->data['schedule'] = DailySchedule::with('shiftType')->with('driver')->with('pricebook')->with('vehicle')->with(['carers' => function ($q) use ($d,$id) {
                $schedule = DailySchedule::find($id);
                if ($schedule && $schedule->position_status == 0) {
                    $q->orderBy('position', 'asc');
                }
                $q->whereDoesntHave('carerStatus', function ($statusQuery) use ($d) {
                    $statusQuery->where('date', $d);
                    $statusQuery->where('status_id', 4);
                });
            }])->find($id);

            $schedule = DailySchedule::find($id);
            $maxEndDate = null;

            if ($schedule) {
                // Find all schedules with the same parent_id or where this schedule is the parent
                $allSchedules = DailySchedule::where('schedule_parent_id', $schedule->schedule_parent_id ?: $schedule->id)
                    ->orWhere('id', $schedule->schedule_parent_id)
                    ->get();

                // Get the maximum end_date
               // $maxEndDate = $allSchedules->max('end_date');
               if ($allSchedules->isNotEmpty()) {
                $maxEndDate = $allSchedules->max('end_date');
            } else {
                $maxEndDate = $schedule->end_date;
            }
            }

            // Get additional details
            $this->data['max_end_date'] = $maxEndDate;
            $this->data['priceBook'] = PriceBook::orderBy('id', 'DESC')
                ->has('priceBookData')
                ->get();
            $this->data['rideSetting'] = RideSetting::first();
            $this->data['shiftTypes'] = ShiftTypes::get();
            $this->data['all_drivers'] = SubUser::whereHas("roles", function ($q) {
                $q->where("name", "driver");
            })->where('close_account', 1)
                ->leftJoin('sub_user_addresses', function ($join) use ($d) {
                    $join->on('sub_users.id', '=', 'sub_user_addresses.sub_user_id')
                        ->whereDate('sub_user_addresses.start_date', '<=', $d)
                        ->where(function ($query) use ($d) {
                            $query->whereDate('sub_user_addresses.end_date', '>', $d)
                                ->orWhereNull('sub_user_addresses.end_date');
                        });
                })
                ->leftJoin('vehicles', 'sub_users.id', '=', 'vehicles.driver_id')
                ->whereNotNull('sub_user_addresses.latitude')
                ->whereNotNull('sub_user_addresses.longitude')
                ->orderBy("sub_users.id", "DESC")
                ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.address', 'vehicles.*')
                ->get();

            $this->data['all_employees'] = SubUser::whereHas("roles", function ($q) {
                $q->whereIn("name", ['carer', 'archived_staff']);
            })
                ->leftJoin('sub_user_addresses', function ($join) use ($d) {
                    $join->on('sub_users.id', '=', 'sub_user_addresses.sub_user_id')
                        ->whereDate('sub_user_addresses.start_date', '<=', $d)
                        ->where(function ($query) use ($d) {
                            $query->whereDate('sub_user_addresses.end_date', '>', $d)
                                ->orWhereNull('sub_user_addresses.end_date');
                        });
                })
                ->whereNotNull('sub_user_addresses.latitude')
                ->whereNotNull('sub_user_addresses.longitude')
                ->orderBy("sub_users.id", "DESC")
                ->select('sub_users.*', 'sub_user_addresses.latitude', 'sub_user_addresses.longitude', 'sub_user_addresses.longitude')
                ->get();
            return response()->json([
                'success' => true,
                'data' => @$this->data,
                'message' => 'Schedule updated successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
            'file' => $th->getFile()
            ], 500);
        }

     }

       /**
     * @OA\Post(
     * path="/uc/api/serach_employee",
     * operationId="serachEmployee",
     * tags={"Ucruise Schedule"},
     * summary="search employee data",
     *   security={ {"Bearer": {} }},
     * description="search employee data",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"search"},
     *               @OA\Property(
    *                     property="search",
    *                     type="string",
    *                     example="emp name",
    *                 ),
    *                 @OA\Property(
    *                     property="shiftType",
    *                     type="string",
    *                 ),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="driver  listed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="driver listed successfully.",
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


    public function serachEmployee(Request $request){
        try {
               $validatedData = $request->validate([
                'search' => 'required',
                'shiftType' => 'required|numeric|in:1,2,3',
            ]);
            $search = $request->search;
            $shiftType=$request->shiftType;
            if($shiftType==1){
                $shiftType="pick";
            }else if($shiftType==2){
                $shiftType="pick-drop";
            }else if($shiftType==2){
                $shiftType="drop";
            }else{
                 $shiftType="";
            }



            $d = date('Y-m-d');

            // first check is there conflict

            $conflictingCarerIds = ScheduleCarer::join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
                ->where(function ($query) use ( $d) {
                    $query->where('schedules.date',  $d)
                        ->orWhere(function ($q) use ( $d) {
                            $q->where('schedules.end_date', '>=',  $d)
                                ->where('schedules.date', '<=',  $d);
                        });
                })
                ->when($shiftType === 'pick-drop', function ($query) {
                    $query->whereIn('schedule_carers.shift_type', ['pick', 'drop', 'pick-drop']);
                })
                ->when($shiftType === 'pick', function ($query) {
                    $query->whereIn('schedule_carers.shift_type', ['pick', 'pick-drop']);
                })
                ->when($shiftType === 'drop', function ($query) {
                    $query->whereIn('schedule_carers.shift_type', ['drop', 'pick-drop']);
                })
                ->pluck('schedule_carers.carer_id')
                ->toArray();



            $searchEmployee=  SubUser::whereHas("roles", function ($q) {
                    $q->whereIn("name", ['carer', 'archived_staff']);
                })
                 ->when($conflictingCarerIds, function ($query) use ($conflictingCarerIds) {
                    $query->whereNotIn('sub_users.id', $conflictingCarerIds);
                })
                ->when(request('search'), function ($query, $search) {
                    $query->where('sub_users.first_name', 'like', $search . '%');
                })
                ->leftJoin('sub_user_addresses', function ($join) use ($d) {
                    $join->on('sub_users.id', '=', 'sub_user_addresses.sub_user_id')
                        ->whereDate('sub_user_addresses.start_date', '<=', $d)
                        ->where(function ($query) use ($d) {
                            $query->whereDate('sub_user_addresses.end_date', '>', $d)
                                ->orWhereNull('sub_user_addresses.end_date');
                        });
                })
                ->whereNotNull('sub_user_addresses.latitude')
                ->whereNotNull('sub_user_addresses.longitude')
                ->orderBy("sub_users.id", "DESC")
                ->select(
                    'sub_users.*',
                    'sub_user_addresses.latitude',
                    'sub_user_addresses.longitude',
                    'sub_user_addresses.address' // added address for consistency
                )
                ->get();
              return response()->json([
                'success' => true,
                'data' => @$searchEmployee,
                'message' => 'employees fatched successfully',
            ], 200);
        }catch (\Throwable $th) {
                return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }


    }



    //***************************Driver search api **************************/


    /**
     * @OA\Post(
     * path="/uc/api/serach_driver",
     * operationId="serachDriver",
     * tags={"Ucruise Schedule"},
     * summary="Edit schedule data",
     *   security={ {"Bearer": {} }},
     * description="Edit schedule data",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"search"},
     *               @OA\Property(property="search", type="text"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="driver  listed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="driver listed successfully.",
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


    public function serachDriver(Request $request){
        try {
               $validatedData = $request->validate([
                'search' => 'required',
            ]);
            $search = $request->search;
            $d = date('Y-m-d');
            // $serachedDriver= SubUser::whereHas("roles", function ($q) {
            //     $q->where("name", "driver");
            // })
            // ->where('close_account', 1)
            // ->when(request('search'), function ($query, $search) {
            //     $query->where('sub_users.first_name', 'like', $search . '%');
            // })
            // ->leftJoin('sub_user_addresses', function ($join) use ($d) {
            //     $join->on('sub_users.id', '=', 'sub_user_addresses.sub_user_id')
            //         ->whereDate('sub_user_addresses.start_date', '<=', $d)
            //         ->where(function ($query) use ($d) {
            //             $query->whereDate('sub_user_addresses.end_date', '>', $d)
            //                 ->orWhereNull('sub_user_addresses.end_date');
            //         });
            // })
            // ->leftJoin('vehicles', 'sub_users.id', '=', 'vehicles.driver_id')
            // ->whereNotNull('sub_user_addresses.latitude')
            // ->whereNotNull('sub_user_addresses.longitude')
            // ->orderBy("sub_users.id", "DESC")
            // ->select(
            //     'sub_users.*',
            //     'sub_user_addresses.latitude',
            //     'sub_user_addresses.longitude',
            //     'sub_user_addresses.address',
            //     'vehicles.*'
            // )
            // ->get();
            $searchedDriver = SubUser::with('vehicle')
            ->whereHas("roles", fn($q) => $q->where("name", "driver"))
            ->where('close_account', 1)
            ->when(request('search'), fn($q, $search) => $q->where('first_name', 'like', $search . '%'))
            ->orderBy('id', 'desc')
            ->get();

              return response()->json([
                'success' => true,
                'data' => @$searchedDriver,
                'message' => 'Driver fatched successfully',
            ], 200);
        }catch (\Throwable $th) {
                return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }


    }


    // public function updateSchedule(Request $request)
    // {
    //     try {

    //         //new code
    //         $requestData = json_decode(@$request->data, true); // Convert to array
    //         if ($requestData === null) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Invalid JSON',
    //             ], 500);
    //         }


    //         // Define validation rules
    //         $rules = [
    //             'id' => 'required',
    //             'current_date' => 'required',
    //             'date' => 'required|date',
    //             'shift_type_id' => 'required|in:1,2,3',
    //             'pick_time' => 'required_if:shift_type_id,1,2',
    //             'drop_time' => 'required_if:shift_type_id,2,3',
    //             'driver_id' => 'required|exists:sub_users,id',
    //             'pricebook_id' => 'required|exists:price_books,id',
    //             'vehicle_id' => 'required|exists:vehicles,id',
    //             'scheduleLocation' => 'required',
    //             'scheduleCity' => 'required',
    //             'selectedLocationLat' => 'required',
    //             'selectedLocationLng' => 'required',
    //             'is_repeat' => 'nullable|boolean',
    //             'reacurrance' => 'required_if:is_repeat,1',
    //             'repeat_days' => 'required_if:reacurrance,0',
    //             'repeat_weeks' => 'required_if:reacurrance,1',
    //             'mon' => 'nullable|boolean',
    //             'tue' => 'nullable|boolean',
    //             'wed' => 'nullable|boolean',
    //             'thu' => 'nullable|boolean',
    //             'fri' => 'nullable|boolean',
    //             'sat' => 'nullable|boolean',
    //             'sun' => 'nullable|boolean',
    //             'repeat_months' => 'required_if:reacurrance,2',
    //             'repeat_day_of_month' => 'required_if:reacurrance,2',
    //             'end_date' => 'nullable|date|after_or_equal:date',
    //         ];

    //         // Create validator instance
    //         $validator = Validator::make($requestData, $rules);

    //         // Perform validation
    //         if ($validator->fails()) {
    //             return response()->json(['errors' => $validator->errors()], 422); // Return validation errors
    //         }

    //         $request = json_decode($request->data);


    //         //end of new code

    //         $week_arr = array();
    //         $excluded_dates = array();
    //         $schedule_check = Schedule::find($request->id);
    //         // if ($schedule_check->driver_id == $request->driver_id) {
    //         if ($schedule_check) {
    //             $excluded_dates = json_decode($schedule_check->excluded_dates);
    //             if ($schedule_check->is_repeat == 1) {
    //                 if (date('Y-m-d', strtotime($request->current_date)) != $schedule_check->date) {
    //                     if ($schedule_check->schedule_parent_id == NULL) {
    //                         if (@$request->apply_to_future == 1) {
    //                             $schedule = new Schedule();
    //                             $date = date('Y-m-d', strtotime($request->current_date));
    //                             $schedule->date = date('Y-m-d', strtotime($date));
    //                             $schedule->end_date = $schedule_check->end_date;
    //                             $schedule_check->end_date = date('Y-m-d', strtotime($request->current_date . ' - 1 days'));
    //                             $schedule_check->save();
    //                             $schedule->is_repeat = $schedule_check->is_repeat;
    //                             $schedule->reacurrance = $schedule_check->reacurrance;
    //                             $schedule->repeat_time = $schedule_check->repeat_time;
    //                             $schedule->occurs_on = $schedule_check->occurs_on;
    //                         } else {
    //                             if ($request->driver_id == $schedule_check->driver_id) {
    //                                 $schedule = new Schedule();
    //                                 $schedule->schedule_parent_id = $request->id;
    //                                 // $schedule->driver_id = $request->driver_id;
    //                                 // $schedule->vehicle_id = $request->vehicle_id;
    //                                 $schedule->date = date('Y-m-d', strtotime($request->current_date));
    //                                 if ($excluded_dates === NULL) {
    //                                     $excluded_dates = array();
    //                                 }
    //                                 array_push($excluded_dates,  date('Y-m-d', strtotime($request->current_date)));
    //                                 $schedule_check->excluded_dates = $excluded_dates;
    //                                 $schedule_check->save();
    //                             } else {

    //                                 return response()->json([
    //                                     'success' => false,
    //                                     'message' => 'Driver cannot be updated for one day in repeating'
    //                                 ], 400);
    //                             }
    //                         }
    //                     } else {
    //                         $schedule = $schedule_check;
    //                     }
    //                 } else {
    //                     if (@$request->apply_to_future == 1) {
    //                         $schedule = $schedule_check;
    //                     } else {
    //                         $schedule = new Schedule();
    //                         $schedule->date =  date('Y-m-d', strtotime($request->current_date));
    //                         //$schedule->end_date = date('Y-m-d', strtotime($request->current_date));
    //                         $date = date('Y-m-d', strtotime($request->current_date));
    //                         $schedule_check->date = date('Y-m-d', strtotime($date . ' + 1 days'));
    //                         $schedule_check->save();
    //                     }
    //                 }
    //             } else {
    //                 $schedule = $schedule_check;
    //             }
    //         }

    //         $schedule->driver_id = $request->driver_id;
    //         $schedule->vehicle_id = $request->vehicle_id;
    //         $schedule->shift_finishes_next_day = $request->shift_type_id == 2 ? ($request->shift_finishes_next_day ? 1 : 0) : 0;
    //         if ($request->shift_type_id == 1) {
    //             // For shift type 1, store only the pick time
    //             $schedule->start_time = $request->date . ' ' . date('H:i', strtotime(@$request->pick_time)) . ':00';
    //             $schedule->end_time = null;
    //         } elseif ($request->shift_type_id == 2) {
    //             // For shift type 2, store both pick and drop times
    //             $schedule->start_time = $request->date . ' ' . date('H:i', strtotime(@$request->pick_time)) . ':00';
    //             $schedule->end_time = $request->date . ' ' . date('H:i', strtotime(@$request->drop_time)) . ':00';
    //         } elseif ($request->shift_type_id == 3) {
    //             // For shift type 3, store only the drop time
    //             $schedule->start_time = null;
    //             $schedule->end_time = $request->date . ' ' . date('H:i', strtotime(@$request->drop_time)) . ':00';
    //         }

    //         if ($request->shift_type_id) {
    //             $schedule->shift_type_id = $request->shift_type_id;
    //         }
    //         if ($request->pricebook_id) {
    //             $schedule->pricebook_id = $request->pricebook_id;
    //         }
    //         $schedule->longitude = $request->selectedLocationLng;
    //         $schedule->latitude = $request->selectedLocationLat;
    //         $schedule->city = $request->scheduleCity;
    //         $schedule->locality = $request->scheduleLocation;
    //         $schedule->save();
    //         //         $future_date = '2024-11-22';
    //         //         if ($request->current_date && $future_date) {
    //         //             // Create new schedules for the current date and the future date (23-11-2024)
    //         //             $currentDate = \Carbon\Carbon::parse($request->current_date); // parse the current date
    //         //             $future_date = \Carbon\Carbon::parse($future_date); // parse the future date

    //         // // Initialize an array to store new schedule dates
    //         // $newSchedules = [];

    //         // // Loop through the days from the current date to the future date
    //         // while ($currentDate->lt($future_date)) {
    //         //     // Add the formatted date to the array
    //         //     $newSchedules[] = $currentDate->format('Y-m-d');

    //         //     // Increment the date by one day
    //         //     $currentDate->addDay();
    //         // }

    //         // // Add the future date to the array as well
    //         // $newSchedules[] = $future_date->format('Y-m-d');
    //         //            // $newSchedules = ['21-11-2024', '22-11-2024', $future_date];

    //         //             // Loop through new schedules
    //         //             foreach ($newSchedules as $newDate) {
    //         //                 info('new user' . $newDate);
    //         //                 info('future date' . $request->current_date);
    //         //                 // if ($newDate == \Carbon\Carbon::parse($request->current_date)->format('Y-m-d')) {
    //         //                 //     info('Skipping current date');
    //         //                 //     continue; // Skip the current date
    //         //                 // }

    //         //                 $schedule = new Schedule();
    //         //                 $schedule->date = date('Y-m-d', strtotime($newDate));
    //         //                 $schedule->end_date = $schedule_check->end_date;
    //         //                 $schedule->schedule_parent_id = $request->id;
    //         //                 $schedule->driver_id = $request->driver_id;
    //         //                 $schedule->vehicle_id = $request->vehicle_id;
    //         //                 $schedule->shift_type_id = $request->shift_type_id;
    //         //                 $schedule->pricebook_id = $request->pricebook_id;
    //         //                 $schedule->longitude = $request->selectedLocationLng;
    //         //                 $schedule->latitude = $request->selectedLocationLat;
    //         //                 $schedule->city = $request->scheduleCity;
    //         //                 $schedule->locality = $request->scheduleLocation;

    //         //                 // Save the schedule
    //         //                 $schedule->save();

    //         //                 // Add this new date to the excluded_dates array
    //         //                 array_push($excluded_dates, date('Y-m-d', strtotime($newDate)));


    //         //                 // $scheduleParentExists = Schedule::where('id', $schedule_check->id)
    //         //                 // ->whereNotNull('schedule_parent_id')
    //         //                 // ->exists();

    //         //                 // if ($scheduleParentExists) {
    //         //                 //  ScheduleCarer::where('schedule_id', $schedule_check->id)
    //         //                 //            ->whereNotIn('carer_id', $request->carers)
    //         //                 //            ->delete();
    //         //                 //    }
    //         //                 if ($request->shift_type_id == 2 || $request->shift_type_id == 3) {
    //         //                     foreach ($request->carers as $carer) {
    //         //                         $existingCarer = ScheduleCarer::where('schedule_id', $schedule->id)
    //         //                             ->where('carer_id', $carer)->where('shift_type', 'drop')
    //         //                             ->first();
    //         //                         if (!$existingCarer) {
    //         //                             $scheduleCarers = new ScheduleCarer();
    //         //                             $scheduleCarers->schedule_id = $schedule->id;
    //         //                             $scheduleCarers->carer_id = $carer;
    //         //                             $scheduleCarers->shift_type = 'drop';
    //         //                             $scheduleCarers->save();
    //         //                         }
    //         //                     }
    //         //                 }
    //         //                 if ($request->shift_type_id == 1 || $request->shift_type_id == 2) {
    //         //                     foreach ($request->carers as $carer) {
    //         //                         $existingCarer = ScheduleCarer::where('schedule_id', $schedule->id)
    //         //                             ->where('carer_id', $carer)->where('shift_type', 'pick')
    //         //                             ->first();
    //         //                         if (!$existingCarer) {
    //         //                             $scheduleCarers = new ScheduleCarer();
    //         //                             $scheduleCarers->schedule_id = $schedule->id;
    //         //                             $scheduleCarers->carer_id = $carer;
    //         //                             $scheduleCarers->shift_type = 'pick';
    //         //                             $scheduleCarers->save();
    //         //                         }
    //         //                     }
    //         //                 }
    //         //             }

    //         //             // Update the parent schedule's excluded dates
    //         //             $schedule_check->excluded_dates = $excluded_dates;
    //         //             $schedule_check->save();
    //         //         }

    //         // ScheduleCarer::where('schedule_id', $schedule_check->id)
    //         //     ->whereNotIn('carer_id', $request->carers)
    //         //     ->delete();
    //         $scheduleParentExists = Schedule::where('id', $schedule_check->id)
    //             ->whereNotNull('schedule_parent_id')
    //             ->exists();

    //         if ($scheduleParentExists) {
    //             ScheduleCarer::where('schedule_id', $schedule_check->id)
    //                 ->whereNotIn('carer_id', $request->carers)
    //                 ->delete();
    //         }

    //         if ($request->shift_type_id == 2 || $request->shift_type_id == 3) {
    //             foreach ($request->carers as $carer) {
    //                 $existingCarer = ScheduleCarer::where('schedule_id', $schedule->id)
    //                     ->where('carer_id', $carer)->where('shift_type', 'drop')
    //                     ->first();
    //                 if (!$existingCarer) {
    //                     $scheduleCarers = new ScheduleCarer();
    //                     $scheduleCarers->schedule_id = $schedule->id;
    //                     $scheduleCarers->carer_id = $carer;
    //                     $scheduleCarers->shift_type = 'drop';
    //                     $scheduleCarers->save();
    //                 }
    //             }
    //         }
    //         if ($request->shift_type_id == 1 || $request->shift_type_id == 2) {
    //             foreach ($request->carers as $carer) {
    //                 $existingCarer = ScheduleCarer::where('schedule_id', $schedule->id)
    //                     ->where('carer_id', $carer)->where('shift_type', 'pick')
    //                     ->first();
    //                 if (!$existingCarer) {
    //                     $scheduleCarers = new ScheduleCarer();
    //                     $scheduleCarers->schedule_id = $schedule->id;
    //                     $scheduleCarers->carer_id = $carer;
    //                     $scheduleCarers->shift_type = 'pick';
    //                     $scheduleCarers->save();
    //                 }
    //             }
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Schedule updated successfully',
    //         ], 200);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $th->getMessage()
    //         ], 500);
    //     }
    // }
    //************************* List all schedules api ****************************/


    /**
     * @OA\Post(
     * path="/uc/api/listAllSchedules",
     * operationId="listAllSchedules",
     * tags={"Ucruise Schedule"},
     * summary="list all Schedule",
     *   security={ {"Bearer": {} }},
     * description="list all Schedule",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"flag"},
     *               @OA\Property(property="calendarDate", type="text"),
     *               @OA\Property(property="flag", type="text",description="1-weekly, 2-fornightly, 3-daily"),
     *               @OA\Property(property="driver_staff", type="text",description="1-Driver , 2-Staff"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="List all schedule",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="List all schedule",
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

    public function listAllSchedules(Request $request)
    {

        try {


            $this->data['flag'] = $flag = @$request->flag;
            $this->data['driver_staff'] = $request->driver_staff ? $request->driver_staff : 1;
            $employee_shift = $request->employee_shift ?? null;


           // if (@$request->calendarDate) {
            //  $providedDate = date('Y-m-d', strtotime($request->calendarDate)); // Example date
            $calendarDate = @$request->calendarDate;
            $specifiedYear = $calendarDate ? date('Y', strtotime($calendarDate)) : now()->year;
          // } else {
            // $providedDate = $calendarDate ? date("$specifiedYear-m-d", strtotime($calendarDate)) : date("$specifiedYear-m-d", strtotime("today"));
            //   $providedDate = date('Y-m-d', strtotime("today")); // Example date
          //  }
            $providedDate = $calendarDate ? date("$specifiedYear-m-d", strtotime($calendarDate)) : date("$specifiedYear-m-d", strtotime("today"));
            if ($flag == 3) {
                $startDate = new \DateTime($providedDate);
                $endDate = clone $startDate;

            }
            else{
            $plusDays = 6;
            if ($flag == 2) {
                $plusDays = 13;
            }
            $weekNumber = date('W', strtotime($providedDate));
          //  $year = 2024;      // Example year get data

            $firstDayOfYear = new \DateTime("$specifiedYear-01-01");

            // $startDate = clone $firstDayOfYear;
            // $startDate->modify("+" . ($weekNumber - 1) . " weeks");
            // $startDate->modify('this monday');
            // $endDate = clone $startDate;

            // $endDate->modify('+' . $plusDays . ' days');

            // $currentDate = clone $startDate;
            // $currentDate = strtotime($currentDate->format('Y-m-d'));
            // // echo $endDate->format('Y-m-d');
            // $endDate = strtotime($endDate->format('Y-m-d'));
            $startDate = new \DateTime($providedDate);
            $startDate->modify('this week');
            if ($startDate->format('Y') > $specifiedYear) {
                $startDate = $firstDayOfYear->modify("+" . ($weekNumber - 1) . " weeks");
                $startDate->modify('this monday');
            }
            //$startDate = $firstDayOfYear->modify("+" . ($weekNumber - 1) . " weeks")->modify('this monday');

            // if ($startDate > new \DateTimeImmutable($providedDate)) {
            //     $startDate = $startDate->modify('last monday');
            // }
            $endDate = clone $startDate;
            $endDate->modify('+' . $plusDays . ' days');

            }
            $currentDate = strtotime($startDate->format('Y-m-d'));
            $endDate = strtotime($endDate->format('Y-m-d'));
            $previous_date = date('Y-m-d', strtotime('-1 day', $currentDate));

            $days =  [];
            while ($currentDate <= $endDate) {

                $checkholiday = DB::table('holidays')->whereDate('date', date("Y-m-d", $currentDate))->where('status', 1)->where('type', 'holiday')->count();
                $is_holiday = 0;
                if ($checkholiday > 0) {
                    $is_holiday = 1;
                }

                $dayData = [

                    'date' => date("Y-m-d", $currentDate),

                    'day' => date("D", $currentDate),
                    'is_holiday' => $is_holiday,

                ];

                $days[] = $dayData;

                $currentDate = strtotime("+1 day", $currentDate);
            }


            $this->data['days'] = $days;
            $userIds = $request->user_ids ?? [];

            if ($employee_shift != "all") {
                   $driver_staff = (int) $request->driver_staff;
                    if ($driver_staff == 2) {
                        $filterdUserId = SubUser::where('employee_shift', $employee_shift)->pluck('id');
                        info('staffId...'.json_encode($filterdUserId));
                    }else {
                        $UsersId = SubUser::where('employee_shift', $employee_shift)->pluck('id');
                        $ScheduleID = ScheduleCarer::whereIn('carer_id', $UsersId)->pluck('schedule_id');
                        $filterdUserId = Schedule::whereIn('id', $ScheduleID)->pluck('driver_id');
                        info('driverId...'.json_encode($filterdUserId));
                    }
            }


            $query = SubUser::select('id', 'first_name', 'email', 'phone', 'profile_image','employee_shift');

            if ($this->data['driver_staff'] == 1) {
                $query->whereHas("roles", function ($q) {
                    $q->whereIn("name", ["driver"]);
                })->with("vehicle");;
            } else if ($this->data['driver_staff'] == 2) {
                $query->whereHas("roles", function ($q) {
                    $q->whereIn("name", ['carer']);
                })
                ->whereHas("addresses", function ($q) {
                    $q->whereNotNull('longitude')
                    ->whereNotNull('latitude');
                })
                ->orderBy("id", "DESC");
            }

            if (!empty($userIds)) {
                $query->whereIn('id', $userIds);
            }


            if ($employee_shift != "all") {
                if (!empty($filterdUserId)) {
                    $query->whereIn('id', $filterdUserId);
                }
            }


            // $this->data['users'] = $query->get();


            $perPage = $request->input('per_page', 5); // Default to 10 if not specified
            $users = $query->paginate($perPage);

            $this->data['users'] = $users->getCollection();
            $this->data['pagination'] = [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem()
            ];
            // Get user schedules
            foreach ($this->data['users'] as $user) {

                $userDay = [];
                foreach ($days as $day) {
                    unset($day['is_holiday']);

                    $dailySchedule = $this->getWeeklyScheduleInfos([$user->id], $day, $request->driver_staff, $request->shift_type_id, $previous_date);
                    $stData = $this->structuredData($dailySchedule, $request->driver_staff) ?: [];

                    $userDay[] = [
                        'schedule' =>  $stData,
                    ];
            }


                //$user->schedules = $userDay;
                $user->schedules = $this->finalStructuredData($userDay)['schedules'];
            //   $userSchedules = $this->finalStructuredData($userDay)['schedules'];

            }

            // echo '<pre>';print_r($this->data);die;
            return response()->json([
                'success' => true,
                'data' => @$this->data,
                'message' => 'schedule data'

            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ], 500);
        }
    }


    // Daily schedule list start




    /**
     * @OA\Post(
     * path="/uc/api/dailylistAllSchedules",
     * operationId="dailylistAllSchedules",
     * tags={"Ucruise Schedule"},
     * summary="list all daily Schedule",
     *   security={ {"Bearer": {} }},
     * description="list all daily Schedule",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"flag"},
     *               @OA\Property(property="calendarDate", type="text"),
     *               @OA\Property(property="flag", type="text",description="1-weekly, 2-fornightly, 3-daily"),
     *               @OA\Property(property="driver_staff", type="text",description="1-Driver , 2-Staff"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="List all daily schedule",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="List all daily schedule",
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

    public function dailylistAllSchedules(Request $request)
    {

        try {

            $this->data['flag'] = $flag = @$request->flag;
            $this->data['driver_staff'] = $request->driver_staff ? $request->driver_staff : 1;

            $calendarDate = @$request->calendarDate;
            $specifiedYear = $calendarDate ? date('Y', strtotime($calendarDate)) : now()->year;
            $providedDate = $calendarDate ? date("$specifiedYear-m-d", strtotime($calendarDate)) : date("$specifiedYear-m-d", strtotime("today"));
            if ($flag == 3) {
                $startDate = new \DateTime($providedDate);
                $endDate = clone $startDate;
            }
            else{
            $plusDays = 6;
            if ($flag == 2) {
                $plusDays = 13;
            }
            $weekNumber = date('W', strtotime($providedDate));
            $firstDayOfYear = new \DateTime("$specifiedYear-01-01");

            $startDate = new \DateTime($providedDate);
            $startDate->modify('this week');
            if ($startDate->format('Y') > $specifiedYear) {
                $startDate = $firstDayOfYear->modify("+" . ($weekNumber - 1) . " weeks");
                $startDate->modify('this monday');
            }
            $endDate = clone $startDate;
            $endDate->modify('+' . $plusDays . ' days');

            }
            $currentDate = strtotime($startDate->format('Y-m-d'));
            $endDate = strtotime($endDate->format('Y-m-d'));
            $previous_date = date('Y-m-d', strtotime('-1 day', $currentDate));

            $days =  [];
            while ($currentDate <= $endDate) {
                $checkholiday = DB::table('holidays')->whereDate('date', date("Y-m-d", $currentDate))->where('status', 1)->where('type', 'holiday')->count();
                $is_holiday = 0;
                if ($checkholiday > 0) {
                    $is_holiday = 1;
                }

                $dayData = [
                    'date' => date("Y-m-d", $currentDate),
                    'day' => date("D", $currentDate),
                    'is_holiday' => $is_holiday,
                ];

                $days[] = $dayData;
                $currentDate = strtotime("+1 day", $currentDate);
            }


            $this->data['days'] = $days;
            $userIds = $request->user_ids ?? [];
            $query = SubUser::select('id', 'first_name', 'email', 'phone', 'profile_image');

            if ($this->data['driver_staff'] == 1) {
                $query->whereHas("roles", function ($q) {
                    $q->whereIn("name", ["driver"]);
                })->with("vehicle");;
            } else if ($this->data['driver_staff'] == 2) {
                $query->whereHas("roles", function ($q) {
                    $q->whereIn("name", ['carer']);
                })
                ->whereHas("addresses", function ($q) {
                    $q->whereNotNull('longitude')
                    ->whereNotNull('latitude');
                })
                ->orderBy("id", "DESC");
            }

            if (!empty($userIds)) {
                $query->whereIn('id', $userIds);
            }


            $perPage = $request->input('per_page', 5); // Default to 10 if not specified
            $users = $query->paginate($perPage);

            $this->data['users'] = $users->getCollection();
            $this->data['pagination'] = [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem()
            ];
            // Get user schedules
            foreach ($this->data['users'] as $user) {

                $userDay = [];
                foreach ($days as $day) {
                    unset($day['is_holiday']);

                    $dailySchedule = $this->dailygetWeeklyScheduleInfos([$user->id], $day, $request->driver_staff, $request->shift_type_id, $previous_date);
                   // $stData = $this->structuredData($dailySchedule, $request->driver_staff) ?: [];
                    $stData = $this->dailystructuredData($dailySchedule, $request->driver_staff) ?: [];

                    $userDay[] = [
                        'schedule' =>  $stData,
                    ];
                }
                $user->schedules = $this->finalStructuredData($userDay)['schedules'];
            }
            return response()->json([
                'success' => true,
                'data' => @$this->data,
                'message' => 'schedule data'

            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ], 500);
        }
    }


    // Daily schedule lis end




    public function finalStructuredData($inputArrayOld)
    {
        //error_reporting(0);
        $inputArray = [
            'schedules' => $inputArrayOld,
        ];
        //dd($inputArray['schedules']);

        // echo '<pre>';print_r($inputArray);die;

        $count = count($inputArray['schedules']);

        $temptArray = [];

        for ($index = 1; $index < $count; $index++) {
            // dd($inputArray['schedules'][$index - 1]['schedule']);
            $currentSchedule = &$inputArray['schedules'][$index]['schedule']; // Use reference to update the original array

            //echo '<pre>';print_r($currentSchedule);die;
            $previousSchedule = $inputArray['schedules'][$index - 1]['schedule'];
            if ($this->checkEmptyOrTrue($currentSchedule, $previousSchedule) == 1) {
                for ($subIndex = 0; $subIndex < count($previousSchedule); $subIndex++) {
                    if (isset($previousSchedule[$subIndex]['nextDay']) && $previousSchedule[$subIndex]['nextDay'] == 1) {
                        $previousSchedule[$subIndex]['nextDay'] = 0;
                        $temptArray[] = $previousSchedule[$subIndex];
                        //unset($currentSchedule[0]);
                    } else {
                        if (count($currentSchedule) > 0) {

                            if (isset($currentSchedule[0])) {
                                $temptArray[] = $currentSchedule[0];
                                unset($currentSchedule[0]);
                            } else {
                                $temptArray[] = [];
                            }
                        } else {
                            $temptArray[] = [];
                        }
                    }
                }

                if (count($currentSchedule) > 0) {
                    foreach ($currentSchedule as $key => $value) {
                        $temptArray[] = $value;
                    }
                }

                // Update the original array with the modified schedule
                $inputArray['schedules'][$index]['schedule'] = $temptArray;
                $temptArray = [];
            }
        }

        return $inputArray;
    }


    function checkEmptyOrTrue($data, $previousSchedule)
    {

        for ($subIndex = 0; $subIndex < count($previousSchedule); $subIndex++) {
            if (@$previousSchedule[$subIndex]['nextDay'] == 1) {
                return 1;
            }
        }

        return 0;
    }


    public function structuredData($dailySchedule, $type)
    {

        //return $dailySchedule;
        $finalArray = [];
        foreach ($dailySchedule as $key => $data) {

            $shiftType =  DB::table('shift_types')->where('id', $data['shift_type_id'])->first();

            $start_time = date('H:i:s', strtotime($data['start_time']));
            $end_time = date('H:i:s', strtotime($data['end_time']));
            $start_date = date('Y-m-d', strtotime($data['date']));
            $end_date = ($data['end_date'] == '0000-00-00') ? '0000-00-00' : date('Y-m-d', strtotime($data['end_date']));
            $finalArray[$key]['scheduleId'] = $data['id'];
            $finalArray[$key]['type'] = $data['type'];
            $finalArray[$key]['time'] = $start_time;
            $finalArray[$key]['date'] = $start_date;
            $finalArray[$key]['end_date'] = $end_date;
            $finalArray[$key]['end'] = $end_time;
            $finalArray[$key]['nextDay'] = $data['shift_finishes_next_day']  ? 1 : 0;
            $finalArray[$key]['employees'] = @$this->getScheduleEmployees($data['id']);
            $finalArray[$key]['driver'] = @$this->getScheduleDriver($data['id']);
            $finalArray[$key]['shiftType'] = @$shiftType;
            $finalArray[$key]['is_repeat'] = $data['is_repeat'];
        }

        if ($finalArray) {
            usort($finalArray, function ($a, $b) {
                $dateTimeA = new \DateTime($a['time']);
                $dateTimeB = new \DateTime($b['time']);

                return $dateTimeA <=> $dateTimeB;
            });
        }

        return  $finalArray;
    }


    public function dailystructuredData($dailySchedule, $type)
    {

        //return $dailySchedule;
        $finalArray = [];
        foreach ($dailySchedule as $key => $data) {

            $shiftType =  DB::table('shift_types')->where('id', $data['shift_type_id'])->first();

            $start_time = date('H:i:s', strtotime($data['start_time']));
            $end_time = date('H:i:s', strtotime($data['end_time']));
            $start_date = date('Y-m-d', strtotime($data['date']));
            $end_date = ($data['end_date'] == '0000-00-00') ? '0000-00-00' : date('Y-m-d', strtotime($data['end_date']));
            $finalArray[$key]['scheduleId'] = $data['id'];
            $finalArray[$key]['type'] = $data['type'];
            $finalArray[$key]['time'] = $start_time;
            $finalArray[$key]['date'] = $start_date;
            $finalArray[$key]['end_date'] = $end_date;
            $finalArray[$key]['end'] = $end_time;
            $finalArray[$key]['nextDay'] = $data['shift_finishes_next_day']  ? 1 : 0;
            $finalArray[$key]['employees'] = @$this->dailygetScheduleEmployees($data['id']);
            $finalArray[$key]['driver'] = @$this->getScheduleDriver($data['id']);
            $finalArray[$key]['shiftType'] = @$shiftType;
            $finalArray[$key]['is_repeat'] = $data['is_repeat'];
        }

        if ($finalArray) {
            usort($finalArray, function ($a, $b) {
                $dateTimeA = new \DateTime($a['time']);
                $dateTimeB = new \DateTime($b['time']);

                return $dateTimeA <=> $dateTimeB;
            });
        }

        return  $finalArray;
    }


    //********************* Function to get the schedule employees **********/
    public function getScheduleEmployees($scheduleId)
    {
        $schedule_carers = DB::table('schedule_carers')

            ->join('sub_users', 'schedule_carers.carer_id', '=', 'sub_users.id')
            ->where('schedule_carers.schedule_id', $scheduleId)
            ->select(
                'sub_users.first_name',
                'sub_users.profile_image',
                'sub_users.shift_type as subuser_shift_type', // alias for clarity
                'schedule_carers.shift_type as shift_type' // alias for clarity
            )
            ->get();
        return $schedule_carers;
    }



    public function dailygetScheduleEmployees($scheduleId)
    {
        $schedule_carers = DB::table('daily_schedule_carers')

            ->join('sub_users', 'daily_schedule_carers.carer_id', '=', 'sub_users.id')
            ->where('daily_schedule_carers.schedule_id', $scheduleId)
            ->select(
                'sub_users.first_name',
                'sub_users.profile_image',
                'sub_users.shift_type as subuser_shift_type', // alias for clarity
                'daily_schedule_carers.shift_type as shift_type' // alias for clarity
            )
            ->get();
        return $schedule_carers;
    }

    //************************** Function to get schedule driver ********************/
    public function getScheduleDriver($scheduleId)
    {
        $schedule = Schedule::find($scheduleId);
        $driver = SubUser::where('id', $schedule->driver_id)->first();
        return $driver->first_name;
    }

    //***************************** Function to get schedule info ******************/

    public function getWeeklyScheduleInfos($user_ids, $dates, $clientStaff, $shift_type_id, $preDate)
    {
       // info($dates);
        $schedule_id_arr = array();

        $previous_date = Carbon::createFromFormat('Y-m-d', min($dates))->subDay()->format('Y-m-d');

        $schedules = Schedule::where(function ($query) use ($dates, $previous_date) {
            $query->where(function ($query) use ($dates) {
                $query->whereIn('date', $dates);

                $query->exists();
            });
            $query->orwhere(function ($query) {
                $query->where('is_repeat', 1);
                $query->where('end_date', '>=', now());
            });
            $query->orwhere(function ($query) use ($dates) {
                $query->where('is_repeat', 1);
                $query->where('end_date', '>=', min($dates));
                $query->where('end_date', '<=', max($dates));
            });
            $query->orwhere(function ($query) use ($previous_date) {
                $query->where('date', $previous_date);
                $query->where('shift_finishes_next_day', 1);
            });
        });

        if ($clientStaff) {
            if ($clientStaff == 2) {
                $schedules = $schedules->whereHas('carers', function ($q) use ($user_ids) {

                    $q->whereIn('carer_id', $user_ids);
                });
                $schedules = $schedules->whereNotNull('driver_id');
                // $leaves = Leave::where('status', 'Approved')->whereIn('date', $dates)->whereIn('staff_id', $user_ids)->pluck('date', 'staff_id');
            } else {
                $schedules = $schedules->whereIn('driver_id', $user_ids);
            }
        }


        // Old code
        // $schedules = $schedules->with('shiftType')->with('driver')->with(['carers' => function ($q) {
        //     $q->with('user');

        // }]);

        // New code

        $schedules = $schedules->with('shiftType')

            ->with('vehicle');
        // ->with(['carers' => function ($q) {
        // $q->with('user');
        // // ->groupBy('carer_id');
        // }]);


        if ($shift_type_id) {
            if ($shift_type_id != "all") {
                $schedules = $schedules->where('shift_type_id', $shift_type_id);
            }
        }

        $holidays = Holiday::whereIn('date', $dates)->pluck('date');

        $schedules = $schedules->get();


        // array_push($dates, $previous_date);

        foreach ($schedules as $schedule) {
            $exc_dates = array();
            if ($schedule->excluded_dates) {
                foreach (json_decode($schedule->excluded_dates) as $exc_date) {
                    array_push($exc_dates, Carbon::createFromFormat('Y-m-d', $exc_date));
                }
            }
            //if ($schedule->reacurrance != 1 || $schedule->reacurrance == 2) {
            $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
            $date = $current_date->copy()->format('Y-m-d');



            $day_name = $current_date->copy()->format('D');
            $checkArr = json_decode($schedule->occurs_on);

            $previous_date = $current_date->copy()->subDay()->format('Y-m-d');
            if ($holidays->contains($previous_date)) {
                info('Skipping schedule for ' . $current_date->format('Y-m-d') . ' because the previous date (' . $previous_date . ') is a holiday.');
                continue;
            }
            if ((!empty($checkArr) && in_array(strtolower($day_name), json_decode($schedule->occurs_on)) && $schedule->reacurrance == 1) || $schedule->reacurrance == 0 || $schedule->reacurrance == null) {

                if (in_array($date, $dates) && !$holidays->contains($date )) {
                    if ($schedule->shift_type_id == 2) {
                        $schedule->type = "pick and drop";
                        array_push($schedule_id_arr, $schedule->toArray());
                    } else if ($schedule->shift_type_id == 1) {
                        $schedule->type = "pick";
                        array_push($schedule_id_arr, $schedule->toArray());
                    } else if ($schedule->shift_type_id == 3) {
                        $schedule->type = "drop";
                        array_push($schedule_id_arr, $schedule->toArray());
                    }
                }
            }
            // if ($date == $previous_date) {
            //     if ($schedule->shift_finishes_next_day == 1) {
            //         $schedule->type = "drop";
            //         array_push($schedule_id_arr, $schedule->toArray());
            //     }
            // }
            // }
            if ($schedule->is_repeat == 1) {
                if ($schedule->reacurrance == 0) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date)->addDays($schedule->repeat_time);

                    $schedule->end_date = date('Y-m-d', strtotime($schedule->end_date . ' +1 day'));

                    while ($current_date <= $schedule->end_date) {
                        $date = $current_date->format('Y-m-d');


                        // if (!in_array($current_date, $public_dates)) {
                        if (!in_array($current_date, $exc_dates) && !$holidays->contains($date) ) {

                            $previous_date = $current_date->copy()->subDay()->format('Y-m-d');
                            if ($holidays->contains($previous_date)) {

                                 info('1Skipping repeating schedule for ' . $current_date->format('Y-m-d') . ' because the previous date (' . $previous_date . ') is a holiday.');
                                $current_date->addDays($schedule->repeat_time);
                                continue;
                            }

                            $schedule->date = $current_date->copy()->format('Y-m-d');

                            if (in_array($date, $dates)) {
                               // info('holidays----------fdfdfdfdf-------'. $holidays);
                              // $holiday = Holiday::whereIn('date', $dates)->pluck('date');
                                if ($schedule->shift_type_id == 2) {
                                    $schedule->type = "pick and drop";
                                    array_push($schedule_id_arr, $schedule->toArray());
                                } else if ($schedule->shift_type_id == 1) {
                                    $schedule->type = "pick";
                                    array_push($schedule_id_arr, $schedule->toArray());
                                }
                                else if ($schedule->shift_type_id == 3 ) {

                              if($schedule->previous_day_pick == 1){
                                $holidays = Holiday::pluck('date');

                                   $previous_date = $current_date->copy()->subDay()->format('Y-m-d');

                                    $holidays = $holidays->map(function($holiday) {
                                    return Carbon::parse($holiday)->format('Y-m-d');  // Convert to string if it's not
                                    });
                                    if (!$holidays->contains($previous_date)) {
                                        $schedule->type = "drop";
                                        array_push($schedule_id_arr, $schedule->toArray());
                                    }
                                    else {
                                        info('Skipping "drop" schedule for ' . $current_date->format('Y-m-d') . ' because the previous date (' . $previous_date . ') is a holiday.');
                                    }
                                }
                                else{
                                    $schedule->type = "drop";
                                        array_push($schedule_id_arr, $schedule->toArray());
                                }
                                }

                                //new code
                                $day_name = $current_date->copy()->format('D');
                                if ($day_name == 'Mon') {

                                    if ($schedule->shift_finishes_next_day == 1) {
                                        $schedule->type = "drop";
                                        $schedule->shift_finishes_next_day = 0;
                                        array_push($schedule_id_arr, $schedule->toArray());
                                        array_pop($schedule_id_arr);
                                    }
                                }
                            }

                            // if ($preDate == $previous_date && $date <= $preDate) {


                            //     if ($schedule->shift_finishes_next_day == 1 && $schedule->reacurrance == 0) {
                            //         error_reporting(0);
                            //         $schedule->type = "drop";

                            //         array_push($schedule_id_arr, $schedule->toArray());
                            //         $schedule->shift_finishes_next_day = 0;
                            //     }
                            // }

                            // else if ($date == $previous_date) {
                            //     if ($schedule->shift_finishes_next_day == 1) {
                            //         $schedule->type = "drop";
                            //         array_push($schedule_id_arr, $schedule->toArray());
                            //     }
                            // }
                        }

                        $current_date = $current_date->addDays($schedule->repeat_time);
                    }
                } else if ($schedule->reacurrance == 1) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                    $scheduleDate = $current_date->copy();

                    $schedule->end_date = date('Y-m-d', strtotime($schedule->end_date . ' +1 day'));

                    $exclude_date = [];
                    if (@$schedule->excluded_dates) {
                        $exclude_date = json_decode($schedule->excluded_dates);
                    }
                    while ($current_date->copy()->startOfWeek() <= $schedule->end_date) {
                        $current_date = $current_date->copy()->startOfWeek() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfWeek();
                        $endofthisweek = $current_date->copy()->endOfWeek();
                        if ($this->dates_in_ranges($current_date->copy()->format('Y-m-d'), $endofthisweek->copy()->format('Y-m-d'), $dates) == true) {
                            while ($current_date->copy() < $endofthisweek & $current_date->copy() < $schedule->end_date) {
                                $date = $current_date->format('Y-m-d');
                                $day_name = $current_date->copy()->format('D');
                                // if (!in_array($current_date, $public_dates)) {
                                if (!in_array($date, $exclude_date) && !$holidays->contains($date)) {
                                    $previous_date = $current_date->copy()->subDay()->format('Y-m-d');
                                    if ($holidays->contains($previous_date)) {

                                        // info('1Skipping repeating schedule for ' . $current_date->format('Y-m-d') . ' because the previous date (' . $previous_date . ') is a holiday.');
                                        $current_date->addWeeks($schedule->repeat_time);
                                        continue;
                                    }
                                    $schedule->date = $current_date->copy()->format('Y-m-d');
                                    if (in_array($date, $dates) & in_array(strtolower($day_name), json_decode($schedule->occurs_on))) {
                                        if ($schedule->shift_type_id == 2) {
                                            $schedule->type = "pick and drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        } else if ($schedule->shift_type_id == 1) {
                                            $schedule->type = "pick";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        } else if ($schedule->shift_type_id == 3) {
                                            if($schedule->previous_day_pick == 1){
                                $holidays = Holiday::pluck('date');

                                   $previous_date = $current_date->copy()->subDay()->format('Y-m-d');

                                    $holidays = $holidays->map(function($holiday) {
                                    return Carbon::parse($holiday)->format('Y-m-d');  // Convert to string if it's not
                                    });
                                    if (!$holidays->contains($previous_date)) {
                                        $schedule->type = "drop";
                                        array_push($schedule_id_arr, $schedule->toArray());
                                    }
                                    else {
                                        //info('Skipping "drop" schedule for ' . $current_date->format('Y-m-d') . ' because the previous date (' . $previous_date . ') is a holiday.');
                                    }
                                }
                                else{
                                    $schedule->type = "drop";
                                        array_push($schedule_id_arr, $schedule->toArray());
                                }
                                           // $schedule->type = "drop";
                                           // array_push($schedule_id_arr, $schedule->toArray());
                                        }

                                        // $day_name = $current_date->copy()->format('D');
                                        // if ($day_name == 'Mon') {
                                        //     // Move the current date to Monday


                                        //     // Adjust the schedule date if the shift finishes the next day
                                        //     if ($schedule->shift_finishes_next_day == 1) {
                                        //         $schedule->type = "drop";
                                        //         $schedule->shift_finishes_next_day = 0;
                                        //         array_push($schedule_id_arr, $schedule->toArray());

                                        //     }
                                        // }
                                    } else {
                                        // New code
                                        if (in_array($date, $dates)) {
                                            $day_name = $current_date->copy()->format('D');
                                            if ($day_name == 'Mon') {
                                                // Move the current date to Monday


                                                // Adjust the schedule date if the shift finishes the next day
                                                if ($schedule->shift_finishes_next_day == 1) {
                                                    $schedule->type = "drop";
                                                    $schedule->shift_finishes_next_day = 0;
                                                    array_push($schedule_id_arr, $schedule->toArray());
                                                    array_pop($schedule_id_arr);
                                                }
                                            }
                                        }
                                    }

                                    // else if ($date == $previous_date & in_array(strtolower($day_name), json_decode($schedule->occurs_on))) {
                                    //     if ($schedule->shift_finishes_next_day == 1) {
                                    //         $schedule->type = "drop";
                                    //         array_push($schedule_id_arr, $schedule->toArray());
                                    //     }
                                    // }
                                }
                                // }
                                $current_date = $current_date->copy()->addDay();
                            }
                            $current_date = $current_date->copy()->subDay();
                        }
                        $current_date = $current_date->copy()->addWeeks($schedule->repeat_time);
                    }
                } else if ($schedule->reacurrance == 2) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                    $scheduleDate = $current_date->copy();
                    while ($current_date->copy()->startOfMonth() <= $schedule->end_date) {
                        $current_date = $current_date->copy()->startOfMonth() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfMonth();
                        // $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                        $endofthismonth = $current_date->copy()->endOfMonth();
                        if ($this->dates_in_ranges($current_date->copy()->format('Y-m-d'), $endofthismonth->copy()->format('Y-m-d'), $dates) == true) {
                            while ($current_date->copy() < $endofthismonth & $current_date->copy() < $schedule->end_date) {
                                $date = $current_date->format('Y-m-d');
                                // if (!in_array($current_date, $public_dates)) {
                                if (!in_array($current_date, $exc_dates) && !$holidays->contains($date)) {
                                    $schedule->date = $current_date->copy()->format('Y-m-d');
                                    if (in_array($date, $dates)) {
                                        if ($schedule->shift_type_id == 2) {
                                            $schedule->type = "pick and drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        } else if ($schedule->shift_type_id == 1) {
                                            $schedule->type = "pick";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        } else if ($schedule->shift_type_id == 3) {
                                            $schedule->type = "drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        }
                                    }
                                    // else if ($date == $previous_date) {
                                    //     if ($schedule->shift_finishes_next_day == 1) {
                                    //         $schedule->type = "drop";
                                    //         array_push($schedule_id_arr, $schedule->toArray());
                                    //     }
                                    // }
                                }
                                // }
                                $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                            }
                            $current_date = $current_date->copy()->subDays($schedule->occurs_on);
                        }
                        $current_date = $current_date->copy()->addMonths($schedule->repeat_time);
                    }
                }
            }
        }

        $this->data['schedules'] = collect($schedule_id_arr);


        return $this->data['schedules'];
    }





    public function dailygetWeeklyScheduleInfos($user_ids, $dates, $clientStaff, $shift_type_id, $preDate)
    {

        $schedule_id_arr = array();
        $previous_date = Carbon::createFromFormat('Y-m-d', min($dates))->subDay()->format('Y-m-d');
        $schedules = DailySchedule::where(function ($query) use ($dates, $previous_date) {
            $query->where(function ($query) use ($dates) {
                $query->whereIn('date', $dates);

                $query->exists();
            });
            $query->orwhere(function ($query) {
                $query->where('is_repeat', 1);
                $query->where('end_date', '>=', now());
            });
            $query->orwhere(function ($query) use ($dates) {
                $query->where('is_repeat', 1);
                $query->where('end_date', '>=', min($dates));
                $query->where('end_date', '<=', max($dates));
            });
            $query->orwhere(function ($query) use ($previous_date) {
                $query->where('date', $previous_date);
                $query->where('shift_finishes_next_day', 1);
            });
        });

        if ($clientStaff) {
            if ($clientStaff == 2) {
                $schedules = $schedules->whereHas('carers', function ($q) use ($user_ids) {

                    $q->whereIn('carer_id', $user_ids);
                });
                $schedules = $schedules->whereNotNull('driver_id');
            } else {
                $schedules = $schedules->whereIn('driver_id', $user_ids);
            }
        }

        $schedules = $schedules->with('shiftType')
            ->with('vehicle');
        if ($shift_type_id) {
            if ($shift_type_id != "all") {
                $schedules = $schedules->where('shift_type_id', $shift_type_id);
            }
        }

        $holidays = Holiday::whereIn('date', $dates)->pluck('date');
        $schedules = $schedules->get();

        foreach ($schedules as $schedule) {
            $exc_dates = array();
            if ($schedule->excluded_dates) {
                foreach (json_decode($schedule->excluded_dates) as $exc_date) {
                    array_push($exc_dates, Carbon::createFromFormat('Y-m-d', $exc_date));
                }
            }

            $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
            $date = $current_date->copy()->format('Y-m-d');

            $day_name = $current_date->copy()->format('D');
            $checkArr = json_decode($schedule->occurs_on);

            $previous_date = $current_date->copy()->subDay()->format('Y-m-d');
            if ($holidays->contains($previous_date)) {
                //info('Skipping schedule for ' . $current_date->format('Y-m-d') . ' because the previous date (' . $previous_date . ') is a holiday.');
                continue;
            }
            if ((!empty($checkArr) && in_array(strtolower($day_name), json_decode($schedule->occurs_on)) && $schedule->reacurrance == 1) || $schedule->reacurrance == 0 || $schedule->reacurrance == null) {

                if (in_array($date, $dates) && !$holidays->contains($date )) {
                    if ($schedule->shift_type_id == 2) {
                        $schedule->type = "pick and drop";
                        array_push($schedule_id_arr, $schedule->toArray());
                    } else if ($schedule->shift_type_id == 1) {
                        $schedule->type = "pick";
                        array_push($schedule_id_arr, $schedule->toArray());
                    } else if ($schedule->shift_type_id == 3) {
                        $schedule->type = "drop";
                        array_push($schedule_id_arr, $schedule->toArray());
                    }
                }
            }
            if ($schedule->is_repeat == 1) {
                if ($schedule->reacurrance == 0) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date)->addDays($schedule->repeat_time);
                    $schedule->end_date = date('Y-m-d', strtotime($schedule->end_date . ' +1 day'));
                    while ($current_date <= $schedule->end_date) {
                        $date = $current_date->format('Y-m-d');
                        if (!in_array($current_date, $exc_dates) && !$holidays->contains($date) ) {

                            $previous_date = $current_date->copy()->subDay()->format('Y-m-d');
                            if ($holidays->contains($previous_date)) {
                                $current_date->addDays($schedule->repeat_time);
                                continue;
                            }
                            $schedule->date = $current_date->copy()->format('Y-m-d');

                            if (in_array($date, $dates)) {
                                if ($schedule->shift_type_id == 2) {
                                    $schedule->type = "pick and drop";
                                    array_push($schedule_id_arr, $schedule->toArray());
                                } else if ($schedule->shift_type_id == 1) {
                                    $schedule->type = "pick";
                                    array_push($schedule_id_arr, $schedule->toArray());
                                }
                                else if ($schedule->shift_type_id == 3 ) {

                                    if($schedule->previous_day_pick == 1){
                                        $holidays = Holiday::pluck('date');
                                        $previous_date = $current_date->copy()->subDay()->format('Y-m-d');

                                            $holidays = $holidays->map(function($holiday) {
                                            return Carbon::parse($holiday)->format('Y-m-d');  // Convert to string if it's not
                                            });
                                            if (!$holidays->contains($previous_date)) {
                                                $schedule->type = "drop";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            }
                                            else {
                                                info('Skipping "drop" schedule for ' . $current_date->format('Y-m-d') . ' because the previous date (' . $previous_date . ') is a holiday.');
                                            }
                                        }
                                        else{
                                            $schedule->type = "drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        }
                                    }

                                //new code
                                $day_name = $current_date->copy()->format('D');
                                if ($day_name == 'Mon') {

                                    if ($schedule->shift_finishes_next_day == 1) {
                                        $schedule->type = "drop";
                                        $schedule->shift_finishes_next_day = 0;
                                        array_push($schedule_id_arr, $schedule->toArray());
                                        array_pop($schedule_id_arr);
                                    }
                                }
                            }
                        }

                        $current_date = $current_date->addDays($schedule->repeat_time);
                    }
                } else if ($schedule->reacurrance == 1) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                    $scheduleDate = $current_date->copy();

                    $schedule->end_date = date('Y-m-d', strtotime($schedule->end_date . ' +1 day'));

                    $exclude_date = [];
                    if (@$schedule->excluded_dates) {
                        $exclude_date = json_decode($schedule->excluded_dates);
                    }
                    while ($current_date->copy()->startOfWeek() <= $schedule->end_date) {
                        $current_date = $current_date->copy()->startOfWeek() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfWeek();
                        $endofthisweek = $current_date->copy()->endOfWeek();
                        if ($this->dates_in_ranges($current_date->copy()->format('Y-m-d'), $endofthisweek->copy()->format('Y-m-d'), $dates) == true) {
                            while ($current_date->copy() < $endofthisweek & $current_date->copy() < $schedule->end_date) {
                                $date = $current_date->format('Y-m-d');
                                $day_name = $current_date->copy()->format('D');
                                if (!in_array($date, $exclude_date) && !$holidays->contains($date)) {
                                    $previous_date = $current_date->copy()->subDay()->format('Y-m-d');
                                    if ($holidays->contains($previous_date)) {
                                        $current_date->addWeeks($schedule->repeat_time);
                                        continue;
                                    }
                                    $schedule->date = $current_date->copy()->format('Y-m-d');
                                    if (in_array($date, $dates) & in_array(strtolower($day_name), json_decode($schedule->occurs_on))) {
                                        if ($schedule->shift_type_id == 2) {
                                            $schedule->type = "pick and drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        } else if ($schedule->shift_type_id == 1) {
                                            $schedule->type = "pick";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        } else if ($schedule->shift_type_id == 3) {
                                            if($schedule->previous_day_pick == 1){
                                $holidays = Holiday::pluck('date');

                                   $previous_date = $current_date->copy()->subDay()->format('Y-m-d');

                                    $holidays = $holidays->map(function($holiday) {
                                    return Carbon::parse($holiday)->format('Y-m-d');
                                    });
                                    if (!$holidays->contains($previous_date)) {
                                        $schedule->type = "drop";
                                        array_push($schedule_id_arr, $schedule->toArray());
                                    }
                                    else {
                                        info('Skipping "drop" schedule for ' . $current_date->format('Y-m-d') . ' because the previous date (' . $previous_date . ') is a holiday.');
                                    }
                                }
                                else{
                                    $schedule->type = "drop";
                                        array_push($schedule_id_arr, $schedule->toArray());
                                }

                                        }


                                    } else {
                                        // New code
                                        if (in_array($date, $dates)) {
                                            $day_name = $current_date->copy()->format('D');
                                            if ($day_name == 'Mon') {
                                                if ($schedule->shift_finishes_next_day == 1) {
                                                    $schedule->type = "drop";
                                                    $schedule->shift_finishes_next_day = 0;
                                                    array_push($schedule_id_arr, $schedule->toArray());
                                                    array_pop($schedule_id_arr);
                                                }
                                            }
                                        }
                                    }
                                }
                                $current_date = $current_date->copy()->addDay();
                            }
                            $current_date = $current_date->copy()->subDay();
                        }
                        $current_date = $current_date->copy()->addWeeks($schedule->repeat_time);
                    }
                } else if ($schedule->reacurrance == 2) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                    $scheduleDate = $current_date->copy();
                    while ($current_date->copy()->startOfMonth() <= $schedule->end_date) {
                        $current_date = $current_date->copy()->startOfMonth() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfMonth();
                        $endofthismonth = $current_date->copy()->endOfMonth();
                        if ($this->dates_in_ranges($current_date->copy()->format('Y-m-d'), $endofthismonth->copy()->format('Y-m-d'), $dates) == true) {
                            while ($current_date->copy() < $endofthismonth & $current_date->copy() < $schedule->end_date) {
                                $date = $current_date->format('Y-m-d');
                                // if (!in_array($current_date, $public_dates)) {
                                if (!in_array($current_date, $exc_dates) && !$holidays->contains($date)) {
                                    $schedule->date = $current_date->copy()->format('Y-m-d');
                                    if (in_array($date, $dates)) {
                                        if ($schedule->shift_type_id == 2) {
                                            $schedule->type = "pick and drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        } else if ($schedule->shift_type_id == 1) {
                                            $schedule->type = "pick";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        } else if ($schedule->shift_type_id == 3) {
                                            $schedule->type = "drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        }
                                    }
                                }

                                $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                            }
                            $current_date = $current_date->copy()->subDays($schedule->occurs_on);
                        }
                        $current_date = $current_date->copy()->addMonths($schedule->repeat_time);
                    }
                }
            }
        }

        $this->data['schedules'] = collect($schedule_id_arr);


        return $this->data['schedules'];
    }




    function dates_in_ranges(string $start_date, string $end_date, array $dates): bool
    {
        foreach ($dates as $date) {
            if ($date >= $start_date & $date <= $end_date) {
                return true;
            }
        }
        return false;
    }

    //********************* Function to send the push notification **************************/


    public function sendPushNotification($firebaseToken, $title, $body)
    {
        $projectId = env('FIREBASE_PROJECT_ID');
        $serviceAccountPath = base_path(env('FIREBASE_SERVICE_ACCOUNT_PATH'));
        // Check if the service account file exists
        if (!File::exists($serviceAccountPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Service account file does not exist: ' . $serviceAccountPath
            ], 500);
        }
        try {
            // Initialize OAuth2 credentials
            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
            $credentials = new ServiceAccountCredentials($scopes, $serviceAccountPath);
            $accessToken = $credentials->fetchAuthToken()['access_token'];

            // Set up the Guzzle client
            $client = new Client();
            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            // Prepare the notification message
            $message = [
                "message" => [
                    "token" => $firebaseToken,
                    "notification" => [
                        "title" => $title,
                        "body" => $body,
                    ]
                ]
            ];
            // Send the notification
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $message, // Use 'json' instead of 'body' for automatic JSON encoding
            ]);

            // Return success response
            return response(['success' => true, 'message' => 'Notification sent successfully'], 200);
        } catch (RequestException $e) {
            // This block catches any Guzzle-specific request errors, such as 400 or 500 HTTP status codes.

            if ($e->hasResponse()) {
                // Return a more detailed error message
                return response(['success' => false, 'message' => 'FCM token is no longer registered.'], 400);
            } else {
                return response(['success' => false, 'message' => 'Something went wrong'], 400);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //******************************** For Invoice ******************************** */

    public function generateInvoice($id, $type)
    {
        $schedule = Schedule::find($id);
        if ($schedule) {
            $invoice = new Invoice();
            $invoice->schedule_id = $id;
            $invoice->driver_id = $schedule->driver_id;
            $invoice->type = $type;
            $invoice->pricebook_id = $schedule->pricebook_id;
            $currentDate = Carbon::now('Asia/Kolkata');
            $invoice->fare = @$this->getFare($currentDate, $schedule->pricebook_id);
            $invoice->date = $currentDate->toDateString();
            $invoice->start_time = Carbon::now('Asia/Kolkata')->toTimeString();
            $invoice->ride_status = 6;
            $invoice->save();
        }
    }
    public function getFare($date, $pricebook)
    {
        $status = '';

        $holidayExists = DB::table('holidays')->whereDate('date', $date)->exists();

        if ($holidayExists) {
            $status = 'Public Holiday';
        } elseif($date->isWeekday()) {
            $status = 'Weekdays (mon- fri)';
        } elseif ($date->isSaturday()) {
            $status = 'saturday';
        } elseif ($date->isSunday()) {
            $status = 'sunday';
        }

        $total = 0;
        $priceBookData = PriceTableData::where('price_book_id', $pricebook)->where('day_of_week', $status)->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)->first();
        if ($priceBookData) {
            $total = $priceBookData->per_ride;
        } else {
            $priceBookData = PriceTableData::where('day_of_week', 'DEFAULT')->first();
            $total = $priceBookData->per_hour;
        }
        return $total;
    }
    public function changeInvoiceStatus($id, $type, $date)
    {
        $invoice = Invoice::where('schedule_id', $id)->where('type', $type)->where('date', $date)->first();
        $invoice->end_time = Carbon::now('Asia/Kolkata')->toTimeString();
        $invoice->is_included = 1;
        $invoice->ride_status = 8;
        $invoice->update();
    }

    //*********************** Delete schedule api *************************/

    /**
     * @OA\Post(
     * path="/uc/api/deleteSchedule",
     * operationId="deleteSchedule",
     * tags={"Ucruise Schedule"},
     * summary="Delete Schedule",
     *   security={ {"Bearer": {} }},
     * description="Delete Schedule",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={ "id"},
     *               @OA\Property(property="id", type="text"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Schedule  deleted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Schedule deleted successfully.",
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
    // public function deleteSchedule(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'id' => 'required'
    //         ]);
    //         $scheduleId= $request->id;
    //         $childSchedules = Schedule::where('schedule_parent_id', $scheduleId)->delete();

    //         $schedule = Schedule::where('id', $request->id)->delete();

    //         if ($schedule) {
    //             return response()->json([
    //                 'status' => true,
    //                 'data' => [],
    //                 'message' => 'Schedule deleted successfully.',
    //             ], 200);
    //         }
    //         return response()->json([
    //             'status' => false,
    //             'data' => [],
    //             'message' => 'Schedule deletion unsuccessful',
    //         ], 500);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $th->getMessage()
    //         ], 500);
    //     }
    // }
    public function deleteSchedule(Request $request)
{
    try {
        // Validate the request data
        $validatedData = $request->validate([
            'id' => 'required|exists:schedules,id',
        ]);
        $schedule = Schedule::find($request->id);
        if (!$schedule) {
            return response()->json([
                'status' => false,
                'message' => 'Schedule not found.',
            ], 404);
        }
        $scheduleId=$request->id;
        // Function to delete schedule and its associations
        $deleteScheduleWithAssociations = function($scheduleId) {

            ScheduleCarer::where('schedule_id', $scheduleId)->delete();

            Schedule::where('id', $scheduleId)->delete();

        };

        // If the schedule is part of a group, delete all schedules in the group
        if ($schedule->schedule_parent_id) {
            $groupSchedules = Schedule::where('schedule_parent_id', $schedule->schedule_parent_id)
                ->orWhere('id', $schedule->schedule_parent_id)
                ->get();

            if ($groupSchedules->isNotEmpty()) {
                foreach ($groupSchedules as $groupSchedule) {
                    $deleteScheduleWithAssociations($groupSchedule->id);
                }
            } else {
                $deleteScheduleWithAssociations($schedule->id);
            }
        } else {
            // Handle the case where there is no schedule_group_id
            $childSchedules = Schedule::where('schedule_parent_id', $schedule->id)->get();
            foreach ($childSchedules as $childSchedule) {
                $deleteScheduleWithAssociations($childSchedule->id);
            }
            // Delete the current schedule
            $deleteScheduleWithAssociations($schedule->id);
        }

        // Return success response
        return response()->json([
            'status' => true,
            'data' => [],
            'message' => 'Schedule deleted successfully.',
        ], 200);
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage(),
        ], 500);
    }
}


    //*************************** Update Future Schedule api ********************************/
    /**
     * @OA\Post(
     *     path="/uc/api/updatefutureSchedule",
     *     operationId="updatefutureSchedule",
     *     tags={"Ucruise Schedule"},
     *     summary="Update schedule",
     *     security={{"Bearer": {}}},
     *     description="Update schedule",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="object",
     *                     description="Your description here"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully updated future schedule .",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated future schedule .",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */



    public function updatefutureSchedule(Request $request)
    {
        try {


            $validatedData = $request->validate([
                'id' => 'required|exists:schedules,id',
                'date' => 'required|date',
                'future_date' => 'required|date',
            ]);
            //$request = json_decode($request->data);
          //  $requestData = json_decode(@$request->data, true); // Convert to array
                    // if ($requestData === null) {
                    //     return response()->json([
                    //         'success' => false,
                    //         'message' => 'Invalid JSON',
                    //     ], 500);
                    // }


                    // Define validation rules
                    // $rules = [
                    //     'shift_type_id' => 'required|in:1,2,3',
                    //     'pick_time' => 'required_if:shift_type_id,1,2',
                    //     'drop_time' => 'required_if:shift_type_id,2,3',
                    //     'driver_id' => 'required|exists:sub_users,id',
                    //     'pricebook_id' => 'required|exists:price_books,id',
                    //     'vehicle_id' => 'required|exists:vehicles,id',
                    //     'scheduleLocation' => 'required',
                    //     'scheduleCity' => 'required',
                    //     'selectedLocationLat' => 'required',
                    //     'selectedLocationLng' => 'required',
                    //     'is_repeat' => 'nullable|boolean',
                    //     'reacurrance' => 'required_if:is_repeat,1',
                    //     'repeat_days' => 'required_if:reacurrance,0',
                    //     'repeat_weeks' => 'required_if:reacurrance,1',
                    //     'mon' => 'nullable|boolean',
                    //     'tue' => 'nullable|boolean',
                    //     'wed' => 'nullable|boolean',
                    //     'thu' => 'nullable|boolean',
                    //     'fri' => 'nullable|boolean',
                    //     'sat' => 'nullable|boolean',
                    //     'sun' => 'nullable|boolean',
                    //     'repeat_months' => 'required_if:reacurrance,2',
                    //     'repeat_day_of_month' => 'required_if:reacurrance,2',
                    //     'end_date' => 'nullable|date|after_or_equal:date',

                    // ];
                    // $validator = Validator::make($rules);

                    //         // Perform validation
                    //         if ($validator->fails()) {
                    //             return response()->json(['errors' => $validator->errors()], 422); // Return validation errors
                    //         }

            $schedule = Schedule::find($request->id);
            if (!$schedule) {
                return response()->json([
                    'status' => false,
                    'message' => 'Schedule not found.',
                ], 404);
            }
            $todayDate = Carbon::today()->toDateString();
            $requestDate = Carbon::parse($request->date)->toDateString();

            // Check if today's date is the same as the request date
            if ($requestDate <= $todayDate) {
                return response()->json([
                    'status' => false,
                    'message' => 'The request date cannot be today or a past date. Please select a future date.',
                ], 400);
            }
            $Data = json_decode($request->data);
            if (!$Data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid data format.',
                ], 400);
            }

            $shiftTypeMap = [
                1 => 'pick',
                2 => 'pick-drop',
                3 => 'drop',
            ];

            //Get the mapped shift type string
            //$shiftTypes = $shiftTypeMap[$Data['shift_type_id']] ?? null;
            $shiftTypes = $shiftTypeMap[$Data->shift_type_id] ?? null;
            if (!$shiftTypes) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid shift type provided.',
                ], 422);
            }

           // Check if a schedule with conflicting or duplicate shift_type already exists for the carer on the given date
            foreach ($Data->carers as $carer) {

                if ($Data->is_repeat == 1) {
                    // Check if any existing schedule overlaps with the repeat schedule's date range
                    $conflictingSchedules = ScheduleCarer::join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
                        ->where('schedule_carers.carer_id', $carer)
                        ->where('schedule_carers.carer_id', '!=', $carer)
                        ->where(function ($query) use ($Data) {
                            $query->whereBetween('schedules.date', [$Data->current_date, $Data->end_date])
                                  ->orWhereBetween('schedules.end_date', [$Data->current_date, $Data->end_date])
                                  ->orWhere(function($q) use ($Data) {
                                      $q->where('schedules.date', '<=', $Data->current_date)
                                        ->where('schedules.end_date', '>=', $Data->end_date);
                                  });
                        })
                        ->get();

                    if ($conflictingSchedules->isNotEmpty()) {
                        return response()->json([
                            'success' => false,
                            'message' => "Conflicting schedules exist for the client between {$Data->current_date} and {$Data->end_date}. Cannot create a repeat schedule.",
                        ], 422);
                    }
                }

                // $existingScheduleQuery = ScheduleCarer::join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
                //     ->where('schedule_carers.carer_id', $carer)
                //     ->where('schedules.date', $request->date);

                if ($shiftTypes === 'pick-drop') {
                    info('here2');

                    // Check if a 'pick-drop' schedule already exists
                    $existingPickDropSchedules = ScheduleCarer::join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
                        ->where('schedule_carers.carer_id', '!=', $carer)
                        ->where('schedule_carers.carer_id', $carer)
                       // ->where('schedules.date', $Data->current_date)
                        ->where('schedule_carers.shift_type', 'pick-drop')
                        ->where(function ($query) use ($Data) {
                            $query->whereBetween('schedules.date', [$Data->current_date, $Data->end_date])
                                  ->orWhereBetween('schedules.end_date', [$Data->current_date, $Data->end_date])
                                  ->orWhere(function($q) use ($Data) {
                                      $q->where('schedules.date', '<=', $Data->current_date)
                                        ->where('schedules.end_date', '>=', $Data->end_date);
                                  });
                        })
                        ->get();

                    if ($existingPickDropSchedules->isNotEmpty()) {
                        info('here3');
                        return response()->json([
                            'success' => false,
                            'message' => "A 'pick-drop' schedule already exists for client on {$Data->current_date}. Cannot create another 'pick-drop'.",
                        ], 422);
                    }

                    // Check if either 'pick' or 'drop' already exists for the carer on the given date
                    $conflictingSchedules = ScheduleCarer::join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
                        ->where('schedule_carers.carer_id', '!=', $carer)
                        ->where('schedule_carers.carer_id', $carer)
                       // ->where('schedules.date', $Data->current_date)
                        ->whereIn('schedule_carers.shift_type', ['pick', 'drop'])
                        ->where(function ($query) use ($Data) {
                            $query->whereBetween('schedules.date', [$Data->current_date, $Data->end_date])
                                  ->orWhereBetween('schedules.end_date', [$Data->current_date, $Data->end_date])
                                  ->orWhere(function($q) use ($Data) {
                                      $q->where('schedules.date', '<=', $Data->current_date)
                                        ->where('schedules.end_date', '>=', $Data->end_date);
                                  });
                        })
                        ->get();

                    info('here4');
                    if ($conflictingSchedules->isNotEmpty()) {
                        foreach ($conflictingSchedules as $conflictingSchedule) {
                            $conflictType = $conflictingSchedule->shift_type;

                            if ($conflictType === 'drop') {
                                info('here5');
                                return response()->json([
                                    'success' => false,
                                    'message' => "A 'drop' schedule already exists for carer on {$Data->current_date} 1S.",
                                ], 422);
                            } elseif ($conflictType === 'pick') {
                                return response()->json([
                                    'success' => false,
                                    'message' => "A 'pick' schedule already exists for client on {$Data->current_date}. Cannot create 'pick-drop'.",
                                ], 422);
                            }
                        }
                    }
                }
                 elseif ($shiftTypes === 'pick') {
                    info('here6');
                    $conflictingSchedules = ScheduleCarer::join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
                    ->where('schedule_carers.carer_id', $carer)
                    ->where('schedule_carers.carer_id', '!=', $carer)
                  //  ->where('schedules.date', $Data->current_date)
                    ->whereIn('schedule_carers.shift_type', ['pick', 'pick-drop'])
                    ->where(function ($query) use ($Data) {
                        $query->whereBetween('schedules.date', [$Data->current_date, $Data->end_date])
                              ->orWhereBetween('schedules.end_date', [$Data->current_date, $Data->end_date])
                              ->orWhere(function($q) use ($Data) {
                                  $q->where('schedules.date', '<=', $Data->current_date)
                                    ->where('schedules.end_date', '>=', $Data->end_date);
                              });
                    })
                    ->get();

                if ($conflictingSchedules->isNotEmpty()) {
                    foreach ($conflictingSchedules as $conflictingSchedule) {
                        // Determine the type of conflict
                        $conflictType = $conflictingSchedule->shift_type;

                        if ($conflictType === 'pick') {
                            return response()->json([
                                'success' => false,
                                'message' => "A 'pick' schedule already exists for carer on {$Data->current_date}.",
                            ], 422);
                        } elseif ($conflictType === 'pick-drop') {
                            info('here8');
                            return response()->json([
                                'success' => false,
                                'message' => "A 'pick-drop' schedule already exists for carer on {$Data->current_date}. Cannot create 'drop'.",
                            ], 422);
                        }
                    }
                }
                } elseif ($shiftTypes === 'drop') {
                    info('here9');
                    // Check if a 'pick-drop' already exists
                   // Check for conflicting schedules

               $conflictingSchedules = ScheduleCarer::join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
                ->where('schedule_carers.carer_id', $carer)
                ->where('schedule_carers.carer_id', '!=', $carer)
               // ->where('schedules.date', $Data->current_date)
                ->whereIn('schedule_carers.shift_type', ['drop', 'pick-drop'])
                ->where(function ($query) use ($Data) {
                    $query->whereBetween('schedules.date', [$Data->current_date, $Data->end_date])
                          ->orWhereBetween('schedules.end_date', [$Data->current_date, $Data->end_date])
                          ->orWhere(function($q) use ($Data) {
                              $q->where('schedules.date', '<=', $Data->current_date)
                                ->where('schedules.end_date', '>=', $Data->end_date);
                          });
                })
                ->get();

            if ($conflictingSchedules->isNotEmpty()) {
                foreach ($conflictingSchedules as $conflictingSchedule) {
                    // Determine the type of conflict
                    $conflictType = $conflictingSchedule->shift_type;

                    if ($conflictType === 'drop') {
                        return response()->json([
                            'success' => false,
                            'message' => "A 'drop' schedule already exists for carer on {$Data->current_date}.",
                        ], 422);
                    } elseif ($conflictType === 'pick-drop') {
                        return response()->json([
                            'success' => false,
                            'message' => "A 'pick-drop' schedule already exists for carer on {$Data->current_date}. Cannot create 'drop'.",
                        ], 422);
                    }
                }
            }


                    // Check if the same 'drop' already exists

                }
            }


            // Dynamically get schedule start and end times
            $scheduleStartDate = Carbon::parse($schedule->date);
            $scheduleEndDate = Carbon::parse($schedule->end_date);



            $providedDate = Carbon::parse($request->date);
            info('provifed' . $providedDate);
            $futureDate = Carbon::parse($request->future_date);
            info('provifed' . $futureDate);

            if ($futureDate < $providedDate) {
               return response()->json([
                   'status' => false,
                   'message' => 'The future date cannot be earlier than the provided date.',
               ], 400);
           }
            $startDateToDelete = $providedDate;
            $endDateToDelete = $futureDate;


            // Check if the update can proceed




           $providedDate = Carbon::parse($request->date);
           $futureDate = Carbon::parse($request->future_date);
           $startDate = Carbon::parse($schedule->date);
           $endDate = Carbon::parse($schedule->end_date)->endOfDay();
           $scheduleGroupId = $schedule->schedule_parent_id;

          // $scheduleGroupId = $schedule->schedule_group_id;

           $scheduleGroupId = $schedule->schedule_parent_id == null ? $schedule->id : $schedule->schedule_parent_id;
//info($scheduleGroupId);
           $schedules = Schedule::where(function ($query) use ($scheduleGroupId) {
               $query->where('schedule_parent_id', $scheduleGroupId)->orWhere('id', $scheduleGroupId);
           })->get()->groupBy('schedule_parent_id');


           //info("main schedules ". $schedules);
          // info("main schedules ". $schedules->flatten()->count());


           if($schedules->flatten()->count() >1){

               foreach ($schedules as $groupedSchedules) {

                   foreach ($groupedSchedules as $key=>$schedule) {

                       info("second date ". $groupedSchedules);

                       $startDate = Carbon::parse($schedule->date)->startOfDay();  // Normalize startDate to midnight
                       $endDate = Carbon::parse($schedule->end_date)->endOfDay();   // Normalize endDate to end of day
                       $providedDate = $providedDate->startOfDay();  // Normalize providedDate to midnight
                       $futureDate = $futureDate->endOfDay();        // Normalize futureDate to end of day

                       // 1. Skip schedules that end before the provided date (range starts)

                       if ($endDate->lt($providedDate)) {
                           continue; // This schedule ends before the range, skip it
                       }

                       // 2. Skip schedules that start after the future date (range ends)
                       if ($startDate->gt($futureDate)) {
                           continue; // This schedule starts after the range, skip it
                       }


                       if ($providedDate->gte($startDate) && $providedDate->lte($endDate) && $futureDate->lte($endDate)) {

                           info("Start");
                           // clieck on first shift
                           if ($providedDate->isSameDay($startDate) && $futureDate->between($startDate, $endDate) && !$futureDate->isSameDay($endDate)){
                           info("Start 1");
                               $schedule->date = $futureDate->copy()->addDay()->format('Y-m-d');
                               $schedule->save();
                               $this->createnewShiftgivenDate($request->data, $providedDate, $futureDate, $scheduleParentId=null);

                               return response()->json([
                                   'status' => true,
                                   'message' => 'Schedule has been updated and split successfully.',
                               ], 200);

                           }

                           if ($providedDate->isSameDay($startDate) && $futureDate->isSameDay($endDate)){
                               info("delete 02");
                               $schedule->carers()->delete();

                               $schedule->delete();
                               $this->createnewShiftgivenDate($request->data, $providedDate, $futureDate, $scheduleParentId=null);

                               return response()->json([
                                   'status' => true,
                                   'message' => 'Schedule has been updated and split successfully.',
                               ], 200);

                           }

                           if($providedDate->between($startDate, $endDate) && $futureDate->isSameDay($endDate)){

                               info("Start 2");
                               $schedule->end_date = $providedDate->copy()->subDay()->format('Y-m-d');
                               $schedule->save();

                               $this->createnewShiftgivenDate($request->data, $providedDate, $futureDate, $scheduleParentId=null);

                               return response()->json([
                                   'status' => true,
                                   'message' => 'Schedule has been updated and split successfully.',
                               ], 200);

                           }


                           if ($providedDate->gt($startDate) && $futureDate->lt($endDate)) {

                              info("Start 3");

                               info($providedDate ."=". $startDate  ."=". $futureDate  ."=". $endDate);

                               $schedule->end_date = $providedDate->copy()->subDay()->format('Y-m-d');
                               $schedule->save();

                               $newSchedule = $schedule->replicate();
                               $newSchedule->start_time = $futureDate->copy()->subDay()->format('Y-m-d'). ' ' . Carbon::parse($schedule->start_time)->format('H:i:s');
                               $newSchedule->end_date = $endDate->format('Y-m-d');
                               $newSchedule->date = $futureDate->copy()->addDay()->format('Y-m-d');
                               $newSchedule->schedule_parent_id = $schedule->schedule_parent_id;

                               $newSchedule->save();


                               $scheduleCarers = ScheduleCarer::where('schedule_id', $schedule->id)->get();
                               foreach ($scheduleCarers as $carer) {
                                   $newCarer = $carer->replicate();
                                   $newCarer->schedule_id = $newSchedule->id;
                                   $newCarer->save();
                               }


                               $this->createnewShiftgivenDate($request->data, $providedDate, $futureDate, $scheduleParentId=null);

                               return response()->json([
                                   'status' => true,
                                   'message' => 'Schedule has been updated and split successfully',
                               ], 200);

                           }


                       }else{

                         // If schedules touch more than 2 schedules

                               info("All schedule date 2".$schedules);

                               $scheduleParentId  = "";

                               foreach ($schedules as $groupedSchedules) {

                                   foreach ($groupedSchedules as $key => $schedule) {


                                       info("Processing schedule " . $key);
                                       $scheduleParentId= $schedule->schedule_parent_id;

                                       $startDate = Carbon::parse($schedule->date)->startOfDay();
                                       $endDate = Carbon::parse($schedule->end_date)->endOfDay();
                                       $currentProvidedDate = $providedDate->copy()->startOfDay();
                                       $currentFutureDate = $futureDate->copy()->endOfDay();

                                       if ($endDate->lt($currentProvidedDate)) {
                                           continue; // This schedule ends before the range, skip it
                                       }

                                       // 2. Skip schedules that start after the future date (range ends)
                                       if ($startDate->gt($currentFutureDate)) {
                                           continue; // This schedule starts after the range, skip it
                                       }

                                       // 3. If the schedule falls entirely between providedDate and futureDate, delete it
                                       if ($startDate->between($currentProvidedDate, $currentFutureDate) && $endDate->between($currentProvidedDate, $currentFutureDate)) {
                                           info("Schedule deletion between " . $currentProvidedDate->toDateString() . " and " . $currentFutureDate->toDateString());


                                           $schedule->carers()->delete();

                                           $schedule->delete();
                                           continue;
                                       }


                                       if ($endDate->isSameDay($currentProvidedDate) && $currentFutureDate->gt($endDate)) {
                                           info("Schedule adjustment 1: Changing end date to " . $currentProvidedDate->toDateString());
                                           $schedule->end_date = $currentProvidedDate->subDay()->toDateString();
                                           $schedule->save();

                                       }elseif($currentProvidedDate->gt($startDate) && $endDate->lt($currentFutureDate)) {
                                           info("Schedule adjustment 2: Changing start date to " . $currentFutureDate->toDateString());
                                           $schedule->end_date = $currentProvidedDate->subDay()->toDateString(); // Adjust start date
                                           $schedule->save();
                                       }


                                       if ($startDate->isSameDay($currentProvidedDate) && $endDate->isSameDay($currentProvidedDate) && $currentFutureDate->gt($endDate)) {
                                           info("delete 1.1");

                                           $schedule->carers()->delete();
                                           $schedule->delete();
                                       }


                                       if ($startDate->isSameDay($currentFutureDate) && $endDate->gt($currentFutureDate)) {
                                           info("Schedule adjustment 3: Changing start date to " . $currentFutureDate->toDateString());
                                           $schedule->date = $currentFutureDate->addDay()->toDateString(); // Adjust start date
                                           $schedule->save();

                                       }else if ($startDate->lt($currentFutureDate) && $endDate->gt($currentFutureDate)) {
                                       info("Schedule adjustment 4: Changing start date to " . $currentFutureDate->toDateString());
                                       $schedule->date = $currentFutureDate->addDay()->toDateString(); // Adjust start date
                                       $schedule->save();
                                       }

                                       // 2. If the schedule starts on the future date but continues after it
                                       if ($startDate->isSameDay($currentFutureDate) && $endDate->isSameDay($currentFutureDate)) {
                                           info("Delete future date is same start date and end date same");

                                           $schedule->carers()->delete();

                                           $schedule->delete();
                                       }

                                       // 4. If the schedule starts on the future date less then but continues after it
                                       // if($currentProvidedDate->gt($startDate) && $endDate->lt($currentFutureDate)) {
                                       //     info("Schedule adjustment 5: Changing start date to " . $currentFutureDate->toDateString());
                                       //     $schedule->end_date = $currentProvidedDate->subDay()->toDateString(); // Adjust start date
                                       //     $schedule->save();
                                       // }

                                       // 2.2. If the schedule starts and ends on the future date
                                       if ($startDate->isSameDay($currentFutureDate) && $endDate->isSameDay($currentFutureDate)) {
                                           info("delete 2.2");

                                           $schedule->carers()->delete();

                                           $schedule->delete();
                                       }
                                   }

                               }

                               $this->createnewShiftgivenDate($request->data, $providedDate, $futureDate, $scheduleParentId);
                               return response()->json([
                                   'status' => true,
                                   'message' => 'Schedule has been updated and split successfully',
                               ], 200);
                       }

                   }
               }


               return response()->json([
                   'status' => true,
                   'message' => 'Schedule has been updated and split successfully 1.',
               ], 200);


           }else{

               info("else future update");
               // clieck on first shift
               if ($providedDate->isSameDay($startDate) && $futureDate->between($startDate, $endDate)){

                   info("else future update 1");

                //    $schedule->date = $futureDate->copy()->addDay()->format('Y-m-d');
                //    $schedule->save();
                   $schedule->delete();
                   $this->createnewShiftgivenDate($request->data, $providedDate, $futureDate, $scheduleParentId=null);

                   return response()->json([
                       'status' => true,
                       'message' => 'Schedule has been updated and split successfully.',
                   ], 200);

               }

               if($providedDate->between($startDate, $endDate) && $futureDate->isSameDay($endDate)){

                   info("else future update 2");
                   $schedule->end_date = $providedDate->copy()->subDay()->format('Y-m-d');
                   $schedule->save();

                   $this->createnewShiftgivenDate($request->data, $providedDate, $futureDate, $scheduleParentId=null);

                   return response()->json([
                       'status' => true,
                       'message' => 'Schedule has been updated and split successfully.',
                   ], 200);

               }


               if ($providedDate->gt($startDate) && $providedDate->lt($endDate) && $futureDate->lt($endDate)) {

                   //info($providedDate ."=". $startDate  ."=". $futureDate  ."=". $endDate);


                   $schedule->end_date = $providedDate->copy()->subDay()->format('Y-m-d');
                   $schedule->save();

                   info("else 1.3");

                   $newSchedule = $schedule->replicate();
                   $newSchedule->start_time = $futureDate->copy()->subDay()->format('Y-m-d'). ' ' . Carbon::parse($schedule->start_time)->format('H:i:s');
                   $newSchedule->end_date = $endDate->format('Y-m-d');
                   $newSchedule->date = $futureDate->copy()->addDay()->format('Y-m-d');
                   $newSchedule->schedule_parent_id = $schedule->id;


                  if ($newSchedule->schedule_parent_id == null) {
                       $newSchedule->schedule_parent_id = $schedule->id;
                   } else {
                       $newSchedule->schedule_parent_id = $newSchedule->schedule_parent_id;
                   }
                   $newSchedule->save();




                   $scheduleCarers = ScheduleCarer::where('schedule_id', $schedule->id)->get();
                   foreach ($scheduleCarers as $carer) {
                       $newCarer = $carer->replicate();
                       $newCarer->schedule_id = $newSchedule->id;
                       $newCarer->save();
                   }


                 //  $data = $request->input('data');



                   $this->createnewShiftgivenDate($request->data, $providedDate, $futureDate, $scheduleParentId=null);

                   return response()->json([
                       'status' => true,
                       'message' => 'Schedule has been updated and split successfully',
                   ], 200);


               }else{

                   return response()->json([
                       'status' => false,
                       'message' => 'The provided date or future date is not within the schedule\'s range.',
                   ], 400);
               }
          }

        } catch (\Throwable $th) {

           info("here sql injection");
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

  //*************************** Update Schedule api ********************************/
    /**
     * @OA\Post(
     *     path="/uc/api/updateSchedule",
     *     operationId="updateSchedule",
     *     tags={"Ucruise Schedule"},
     *     summary="Update schedule",
     *     security={{"Bearer": {}}},
     *     description="Update schedule",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="object",
     *                     description="Your description here"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully updated schedule .",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated schedule .",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */





public function updateSchedule(Request $request){

    try {

        // $userData = $this->checkUserSchedulePermissions('You have no permission to update schedule');
        // if ($userData['status'] === false) {
        //     return response()->json($userData, 403);
        // }
        $validatedData = $request->validate([
            'id' => 'required|exists:schedules,id',
            'date' => 'required|date',
        ]);

            //      $rules = [

            //     'shift_type_id' => 'required|in:1,2,3',
            //     'pick_time' => 'required_if:shift_type_id,1,2',
            //     'drop_time' => 'required_if:shift_type_id,2,3',
            //     'driver_id' => 'required|exists:sub_users,id',
            //     'pricebook_id' => 'required|exists:price_books,id',
            //     'vehicle_id' => 'required|exists:vehicles,id',
            //     'scheduleLocation' => 'required',
            //     'scheduleCity' => 'required',
            //     'selectedLocationLat' => 'required',
            //     'selectedLocationLng' => 'required',
            //     'is_repeat' => 'nullable|boolean',
            //     'reacurrance' => 'required_if:is_repeat,1',
            //     'repeat_days' => 'required_if:reacurrance,0',
            //     'repeat_weeks' => 'required_if:reacurrance,1',
            //     'mon' => 'nullable|boolean',
            //     'tue' => 'nullable|boolean',
            //     'wed' => 'nullable|boolean',
            //     'thu' => 'nullable|boolean',
            //     'fri' => 'nullable|boolean',
            //     'sat' => 'nullable|boolean',
            //     'sun' => 'nullable|boolean',
            //     'repeat_months' => 'required_if:reacurrance,2',
            //     'repeat_day_of_month' => 'required_if:reacurrance,2',
            //     'end_date' => 'nullable|date|after_or_equal:date',
            // ];

            // $validator = Validator::make($request->all(), $rules);

            //     if ($validator->fails()) {
            //         return response()->json(['errors' => $validator->errors()], 422); // Return validation errors
            //     }

        $schedule = Schedule::find($request->id);

        if (!$schedule) {
            return response()->json([
                'status' => false,
                'message' => 'Schedule not found.',
            ], 404);
        }

            $todayDate = Carbon::today()->toDateString();
            $requestDate = Carbon::parse($request->date)->toDateString();


            if ($requestDate <= $todayDate) {
                return response()->json([
                    'status' => false,
                    'message' => 'The request date cannot be today or a past date. Please select a future date.',
                ], 400);
            }
            $Data = json_decode($request->data);
            if (!$Data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid data format.',
                ], 400);
            }

            $shiftTypeMap = [
                1 => 'pick',
                2 => 'pick-drop',
                3 => 'drop',
            ];

            //Get the mapped shift type string
            //$shiftTypes = $shiftTypeMap[$Data['shift_type_id']] ?? null;
            $shiftTypes = $shiftTypeMap[$Data->shift_type_id] ?? null;
            if (!$shiftTypes) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid shift type provided.',
                ], 422);
            }

           // Check if a schedule with conflicting or duplicate shift_type already exists for the carer on the given date
            foreach ($Data->carers as $carer) {

                if ($Data->is_repeat == 1) {
                    // Check if any existing schedule overlaps with the repeat schedule's date range
                    $conflictingSchedules = ScheduleCarer::join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
                        ->where('schedule_carers.carer_id', $carer)
                        ->where('schedule_carers.carer_id', '!=', $carer)
                        ->where(function ($query) use ($Data) {
                            $query->whereBetween('schedules.date', [$Data->current_date, $Data->end_date])
                                  ->orWhereBetween('schedules.end_date', [$Data->current_date, $Data->end_date])
                                  ->orWhere(function($q) use ($Data) {
                                      $q->where('schedules.date', '<=', $Data->current_date)
                                        ->where('schedules.end_date', '>=', $Data->end_date);
                                  });
                        })
                        ->get();

                    if ($conflictingSchedules->isNotEmpty()) {
                        return response()->json([
                            'success' => false,
                            'message' => "Conflicting schedules exist for the client between {$Data->current_date} and {$Data->end_date}. Cannot create a repeat schedule.",
                        ], 422);
                    }
                }

                // $existingScheduleQuery = ScheduleCarer::join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
                //     ->where('schedule_carers.carer_id', $carer)
                //     ->where('schedules.date', $request->date);

                if ($shiftTypes === 'pick-drop') {
                    info('here2');

                    // Check if a 'pick-drop' schedule already exists
                    $existingPickDropSchedules = ScheduleCarer::join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
                        ->where('schedule_carers.carer_id', '!=', $carer)
                        ->where('schedule_carers.carer_id', $carer)
                        //->where('schedules.date', $Data->current_date)
                        ->where('schedule_carers.shift_type', 'pick-drop')
                        ->where(function ($query) use ($Data) {
                            $query->whereBetween('schedules.date', [$Data->current_date, $Data->end_date])
                                  ->orWhereBetween('schedules.end_date', [$Data->current_date, $Data->end_date])
                                  ->orWhere(function($q) use ($Data) {
                                      $q->where('schedules.date', '<=', $Data->current_date)
                                        ->where('schedules.end_date', '>=', $Data->end_date);
                                  });
                        })
                        ->get();

                    if ($existingPickDropSchedules->isNotEmpty()) {
                        info('here3');
                        return response()->json([
                            'success' => false,
                            'message' => "A 'pick-drop' schedule already exists for client on {$Data->current_date}. Cannot create another 'pick-drop'.",
                        ], 422);
                    }

                    // Check if either 'pick' or 'drop' already exists for the carer on the given date
                    $conflictingSchedules = ScheduleCarer::join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
                        ->where('schedule_carers.carer_id', '!=', $carer)
                        ->where('schedule_carers.carer_id', $carer)
                       // ->where('schedules.date', $Data->current_date)
                        ->whereIn('schedule_carers.shift_type', ['pick', 'drop'])
                        ->where(function ($query) use ($Data) {
                            $query->whereBetween('schedules.date', [$Data->current_date, $Data->end_date])
                                  ->orWhereBetween('schedules.end_date', [$Data->current_date, $Data->end_date])
                                  ->orWhere(function($q) use ($Data) {
                                      $q->where('schedules.date', '<=', $Data->current_date)
                                        ->where('schedules.end_date', '>=', $Data->end_date);
                                  });
                        })
                        ->get();

                    info('here4');
                    if ($conflictingSchedules->isNotEmpty()) {
                        foreach ($conflictingSchedules as $conflictingSchedule) {
                            $conflictType = $conflictingSchedule->shift_type;

                            if ($conflictType === 'drop') {
                                info('here5');
                                return response()->json([
                                    'success' => false,
                                    'message' => "A 'drop' schedule already exists for carer on {$Data->current_date}.",
                                ], 422);
                            } elseif ($conflictType === 'pick') {
                                return response()->json([
                                    'success' => false,
                                    'message' => "A 'pick' schedule already exists for client on {$Data->current_date}. Cannot create 'pick-drop'.",
                                ], 422);
                            }
                        }
                    }
                }
                 elseif ($shiftTypes === 'pick') {
                    info('here6');
                    $conflictingSchedules = ScheduleCarer::join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
                   // ->where('schedule_carers.carer_id', $carer)
                    ->where('schedule_carers.carer_id', '!=', $carer)
                    ->where('schedule_carers.carer_id', $carer)
                   // ->where('schedules.date', $Data->current_date)
                    ->whereIn('schedule_carers.shift_type', ['pick', 'pick-drop'])
                    ->where(function ($query) use ($Data) {
                        $query->whereBetween('schedules.date', [$Data->current_date, $Data->end_date])
                              ->orWhereBetween('schedules.end_date', [$Data->current_date, $Data->end_date])
                              ->orWhere(function($q) use ($Data) {
                                  $q->where('schedules.date', '<=', $Data->current_date)
                                    ->where('schedules.end_date', '>=', $Data->end_date);
                              });
                    })
                    ->get();

                if ($conflictingSchedules->isNotEmpty()) {
                    foreach ($conflictingSchedules as $conflictingSchedule) {
                        // Determine the type of conflict
                        $conflictType = $conflictingSchedule->shift_type;

                        if ($conflictType === 'pick') {
                            return response()->json([
                                'success' => false,
                                'message' => "A 'pick' schedule already exists for carer on {$Data->current_date}.",
                            ], 422);
                        } elseif ($conflictType === 'pick-drop') {
                            info('here8');
                            return response()->json([
                                'success' => false,
                                'message' => "A 'pick-drop' schedule already exists for carer on {$Data->current_date}. Cannot create 'drop'.",
                            ], 422);
                        }
                    }
                }
                } elseif ($shiftTypes === 'drop') {
                    info('here9');
                    // Check if a 'pick-drop' already exists
                   // Check for conflicting schedules

               $conflictingSchedules = ScheduleCarer::join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
               // ->where('schedule_carers.carer_id', $carer)
                ->where('schedule_carers.carer_id', '!=', $carer)
                ->where('schedule_carers.carer_id', $carer)
               // ->where('schedules.date', $Data->current_date)
                ->whereIn('schedule_carers.shift_type', ['drop', 'pick-drop'])
                ->where(function ($query) use ($Data) {
                    $query->whereBetween('schedules.date', [$Data->current_date, $Data->end_date])
                          ->orWhereBetween('schedules.end_date', [$Data->current_date, $Data->end_date])
                          ->orWhere(function($q) use ($Data) {
                              $q->where('schedules.date', '<=', $Data->current_date)
                                ->where('schedules.end_date', '>=', $Data->end_date);
                          });
                })
                ->get();

            if ($conflictingSchedules->isNotEmpty()) {
                foreach ($conflictingSchedules as $conflictingSchedule) {
                    // Determine the type of conflict
                    $conflictType = $conflictingSchedule->shift_type;

                    if ($conflictType === 'drop') {
                        return response()->json([
                            'success' => false,
                            'message' => "A 'drop' schedule already exists for carer on {$Data->current_date}.",
                        ], 422);
                    } elseif ($conflictType === 'pick-drop') {
                        return response()->json([
                            'success' => false,
                            'message' => "A 'pick-drop' schedule already exists for carer on {$Data->current_date}. Cannot create 'drop'.",
                        ], 422);
                    }
                }
            }


                    // Check if the same 'drop' already exists

                }
            }
        // $invoiceExists = Invoice::whereRaw("FIND_IN_SET(?, schedule_id)", [$request->id])
        // ->where('invoice_created', 1)
        // ->exists();


        // $invoicedate = Invoice::whereRaw("FIND_IN_SET(?, schedule_id)", [$request->id])
        // ->where('invoice_created', 1)
        // ->get();
        // $firstInvoice = $invoicedate->first();

        // Dynamically get schedule start and end times
        $scheduleStartDate = Carbon::parse($schedule->date);
        $scheduleEndDate = Carbon::parse($schedule->end_date);



        $providedDate = Carbon::parse($request->date);
        $futureDate = Carbon::parse($request->future_date);

        $startDateToDelete = $providedDate;
        $endDateToDelete = $futureDate;



        // Check if the deletion can proceed



        // $invoiceExists = Invoice::whereRaw("FIND_IN_SET(?, schedule_id)", [$request->id])->where('invoice_created', 1)->exists();

        // if ($invoiceExists) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Cannot update this schedule because it is associated with an invoice.',
        //     ], 400);
        // }


        $splitDate = Carbon::parse($request->date);
        $startDate = Carbon::parse($schedule->date)->startOfDay();
        $endDate = Carbon::parse($schedule->end_date)->endOfDay();
        $futureDate = Carbon::parse($request->date);



       // $requestData = json_decode($request->data);

            if ($splitDate->isSameDay($startDate) && $schedule->end_date == '0000-00-00') {

                info(" test 1");

                $this->updateParticularSchedulgivenDate($request->data, $splitDate);

                return response()->json([
                    'status' => true,
                    'message' => 'Schedule and its related clients and carers deleted successfully.',
                ], 200);
            }


            if ($splitDate->isSameDay($startDate) && $splitDate->isSameDay($endDate)) {

                info(" test 2");

                $this->updateParticularSchedulgivenDate($request->data, $splitDate);

                return response()->json([
                    'status' => true,
                    'message' => 'Schedule  updated successfully',
                ], 200);
            }


            if ($splitDate->isSameDay($startDate)) {

                info(" test 3");

                // $schedule->start_time = $splitDate->copy()->addDay()->format('Y-m-d') . ' ' . Carbon::parse($schedule->start_time)->format('H:i:s');
                // $schedule->date = $splitDate->copy()->addDay()->format('Y-m-d');
                // $schedule->save();
                $schedule->delete();

                // Here create a new schedule first on clicke shift
                $this->createnewShiftgivenDate($request->data, $splitDate, $futureDate, $scheduleParentId=null);

                return response()->json([
                    'status' => true,
                    'message' => 'Schedule shift updated successfully ',
                    'data' => $schedule
                ], 200);
            }

            if ($splitDate->isSameDay($endDate)) {

                info(" test 4");

                $schedule->end_date = $splitDate->copy()->subDay()->format('Y-m-d');
                $schedule->save();

                // Here create a new schedule last on clicke shift
                $this->createnewShiftgivenDate($request->data, $splitDate, $futureDate, $scheduleParentId=null);

                return response()->json([
                    'status' => true,
                    'message' => 'Schedule shift updated successfully',
                    'data' => $schedule
                ], 200);
            }


        if ($splitDate->between($startDate, $endDate)) {

            info(" test 5");

            $schedule->end_date = $splitDate->copy()->subDay()->format('Y-m-d');
            $schedule->save();

            $newSchedule = $schedule->replicate();
            $newSchedule->start_time = $splitDate->copy()->addDay()->format('Y-m-d') . ' ' . Carbon::parse($schedule->start_time)->format('H:i:s');
            $newSchedule->end_date = $endDate->format('Y-m-d');
            $newSchedule->date = $splitDate->copy()->addDay()->format('Y-m-d');
           // $newSchedule->schedule_group_id = $schedule->id;

            if ($newSchedule->schedule_parent_id == null) {
                $newSchedule->schedule_parent_id = $schedule->id; // Inherit original schedule's ID if current is 0
            } else {
                // If the new schedule has a group ID, keep it unchanged
                $newSchedule->schedule_parent_id = $newSchedule->schedule_parent_id; // This line is actually redundant
            }
            $newSchedule->save();



            $scheduleCarers = ScheduleCarer::where('schedule_id', $schedule->id)->get();
            foreach ($scheduleCarers as $carer) {
                $newCarer = $carer->replicate();
                $newCarer->schedule_id = $newSchedule->id;
                $newCarer->save();
            }


            // create new schedule as given start date and end date
            $this->createnewShiftgivenDate($request->data, $splitDate, $futureDate, $scheduleParentId=null);

            return response()->json([
                'status' => true,
                'message' => 'Schedule and its related clients and carers successfully split.',
                'data' => [
                    'updated_schedule' => $schedule,
                    'new_schedule' => @$newSchedule,
                ]
            ], 200);

        } else {
            return response()->json([
                'status' => false,
                'message' => 'The provided date is not within the schedule\'s range.',
            ], 400);
        }



    } catch (\Throwable $th) {


        if ($th->getCode() == "23000") {
            // Handle the foreign key constraint violation error
            return response()->json([
                'status' => false,
                'message' => 'Foreign key constraint update carier fields',
                'error' => $th->getMessage()
            ], 400);
        }

        return response()->json([
            'status' => false,
            'message' => $th->getMessage(),
        ], 500);
    }

 }



public static function updateParticularSchedulgivenDate($requestData, $splitDate){

     $request = json_decode($requestData);

        $apply_to_future = 1;
        $schedule_check = Schedule::find($request->id);

        if ($schedule_check) {
            if ($schedule_check->is_repeat == 1) {

                // In this condition creating issue
                if (date('Y-m-d', strtotime($request->date)) != $schedule_check->date) {

                    if ($schedule_check->schedule_parent_id == NULL) {
                        if ($apply_to_future == 1) {
                            $schedule = $schedule_check;
                            $date = date('Y-m-d', strtotime($request->current_date));
                            $schedule->date = date('Y-m-d', strtotime($date));
                            $schedule->end_date = $schedule_check->end_date;
                            $schedule_check->end_date = date('Y-m-d', strtotime($request->current_date . ' - 1 days'));
                            $schedule_check->longitude = @$request->longitude ?? null;
                            $schedule_check->latitude = @$request->latitude ?? null;
                           // $schedule_check->position_status = @$request->position_status ?? 1;
                            $schedule_check->save();
                            $schedule->is_repeat = $schedule_check->is_repeat;
                            $schedule->reacurrance = $schedule_check->reacurrance;
                            $schedule->repeat_time = $schedule_check->repeat_time;
                            $schedule->occurs_on = $schedule_check->occurs_on;
                            $schedule->previous_day_pick = $schedule_check->previous_day_pick;
                        }
                    } else {
                        $schedule = $schedule_check;
                    }
                } else {
                    if ($apply_to_future == 1) {
                        $schedule = $schedule_check;
                    }
                }
            } else {
                $schedule = $schedule_check;
            }

            // Added wekly code for days update
            $schedule->is_repeat = $request->is_repeat ? 1 : 0;
            if ($request->is_repeat) {

                if ($request->reacurrance == 0) {
                    $schedule->reacurrance = 0;
                    $schedule->repeat_time = $request->repeat_days;
                } else if ($request->reacurrance == 1) {

                    $schedule->reacurrance = 1;
                    $schedule->repeat_time = $request->repeat_weeks;

                    $days_of_week = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
                    $week_arr = [];
                    // Loop through each day and check if it's set to "on"
                    foreach ($days_of_week as $day) {
                        if (isset($request->$day) && $request->$day === 'on') {
                            array_push($week_arr, $day);
                        }
                    }

                    $schedule->occurs_on = json_encode($week_arr);

                } else if ($request->reacurrance == 2) {
                    $schedule->reacurrance = 2;
                    $schedule->repeat_time = $request->repeat_months;
                    $schedule->occurs_on = $request->repeat_day_of_month;
                }
                $schedule->end_date = $request->date;
            }
             // Added wekly code for days update

            $schedule->shift_finishes_next_day = @$request->shift_finishes_next_day ? 1 : 0;
           // $schedule->start_time =  $request->start_time;
           $schedule->previous_day_pick = @$request->previous_day_pick;
           if ($request->shift_type_id != 2) {
          //  $schedule->start_time = $request->date . ' ' . date('H:i', strtotime(@$request->pick_time)) . ':00';
          $schedule->start_time = $request->date . ' ' .
          (isset($request->pick_time) && !empty($request->pick_time)
        ? date('H:i', strtotime($request->pick_time)) . ':00'
        : '00:00:00');
            }

            $end_date = $request->date;

           // $clientTimes = is_array($request->clientTimes) ? $request->clientTimes : (array) $request->clientTimes;
          //  $carerTimes = is_array($request->carerTimes) ? $request->carerTimes : (array) $request->carerTimes;


            // $clientIds = array_filter(array_column($clientTimes, 'client_id')); // Filter out null values
            // $carerIds = array_filter(array_column($carerTimes, 'carer_id')); // Filter out null values

            // // Update vacant_shift based on the presence of clients and carers
            // if (empty($clientIds) && empty($carerIds)) {
            //     $schedule->vacant_shift = 1; // No clients and no carers
            // } elseif (!empty($clientIds) && empty($carerIds)) {
            //     $schedule->vacant_shift = 0; // Clients present, no carers
            // } elseif (!empty($carerIds) && empty($clientIds)) {
            //     $schedule->vacant_shift = 1; // Carers present, no clients
            // } else {
            //     $schedule->vacant_shift = 0; // Both clients and carers are present
            // }
            if ($schedule->shift_finishes_next_day == 1) {
                $end_date = date('Y-m-d', strtotime($end_date . ' +1 day'));
            }

            //$schedule->end_time = $request->end_time;
            if ($request->shift_type_id != 1) {

             //   $schedule->end_time = $request->date . ' ' . date('H:i', strtotime(@$request->drop_time)) . ':00';
             $schedule->end_time = $request->date . ' ' .
          (isset($request->drop_time) && !empty($request->drop_time)
        ? date('H:i', strtotime($request->drop_time)) . ':00'
        : '00:00:00');
            }
            $schedule->break_time_in_minutes = $request->break_time_in_minutes;
            $schedule->date = $request->date;

            // Added end date of scheduele
            $schedule->end_date = $request->date;
           // $schedule->end_date = $request->end_date;
            $schedule->address =  $request->address;
            $schedule->apartment_no = $request->apartment_no;
            $schedule->is_drop_off_address = @$request->is_drop_off_address ? 1 : 0;
            if (@$request->is_drop_off_address) {
                $schedule->drop_off_address = @$request->drop_off_address;
                $schedule->drop_off_apartment_no = @$request->drop_off_apartment_no;
            }
            $schedule->mileage = $request->mileage;
           // if (@$request->shift_types) {
                $schedule->shift_type_id = $request->shift_type_id;
            //}
            if (@$request->allowance_id) {
                $schedule->allowance_id = $request->allowance_id;
            }
            $schedule->additional_cost = $request->additional_cost;
            $schedule->ignore_staff_count = $request->ignore_staff_count ==='on' ? 1 : 0;
            $schedule->confirmation_required = $request->confirmation_required ==='on' ? 1 : 0;

            $schedule->add_to_job_board = @$request->add_to_job_board ? 1 : 0;
            if (@$request->add_to_job_board) {
                $schedule->shift_assignment = @$request->shift_assignment;
                $schedule->team_id = @$request->teams;
                $schedule->language_id = @$request->languages;
                $schedule->compliance_id = @$request->compliance;
                $schedule->competency_id = @$request->competencies;
                $schedule->kpi_id = @$request->kpi;
                $schedule->distance_from_shift_location = @$request->distance_from_shift_location;
            } else {
                $schedule->notify_carer = @$request->notify_carer ? 1 : 0;
            }
            $schedule->instructions = @$request->instructions;
            //$schedule->longitude = @$request->longitude ?? null;
         //   $schedule->latitude = @$request->latitude ?? null;



            $schedule->longitude = @$request->selectedLocationLng ?? null;
               $schedule->latitude = @$request->selectedLocationLat ?? null;
               $schedule->driver_id = $request->driver_id;
            $schedule->vehicle_id = $request->vehicle_id;
          $schedule->pricebook_id = $request->pricebook_id;
             $schedule->city = $request->scheduleCity;
          $schedule->locality = $request->scheduleLocation;
            $schedule->save();
            ScheduleCarer::where('schedule_id', $schedule->id)->delete();

            if (@$request->add_to_job_board == 0) {
                if (@$request->carers) {

                    if ($request->shift_type_id == 2 || $request->shift_type_id == 3) {
                        foreach ($request->carers as $carer) {
                            $existingCarer = ScheduleCarer::where('schedule_id', $schedule->id)
                                ->where('carer_id', $carer)->where('shift_type', 'drop')
                                ->first();
                            if (!$existingCarer) {
                                $scheduleCarers = new ScheduleCarer();
                                $scheduleCarers->schedule_id = $schedule->id;
                                $scheduleCarers->carer_id = $carer;
                                $scheduleCarers->shift_type = 'drop';
                                $scheduleCarers->save();
                            }
                        }
                    }
                   if ($request->shift_type_id == 1 || $request->shift_type_id == 2) {
                        foreach ($request->carers as $carer) {
                            $existingCarer = ScheduleCarer::where('schedule_id', $schedule->id)
                                ->where('carer_id', $carer)->where('shift_type', 'pick')
                                ->first();
                            if (!$existingCarer) {
                                $scheduleCarers = new ScheduleCarer();
                                $scheduleCarers->schedule_id = $schedule->id;
                                $scheduleCarers->carer_id = $carer;
                                $scheduleCarers->shift_type = 'pick';
                                $scheduleCarers->save();
                            }
                        }
                    }
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Schedule updated succesffully.'

        ], 200);

}

public static function createnewShiftgivenDate($requestData, $splitDate, $futureDate, $scheduleParentId=null){

        $request = json_decode($requestData, true);

         // $request =$requestData;
           // if (!is_object($requestData) && !is_array($requestData)) {
           //     // Ensure it's in the correct format
           //     $request = json_decode($requestData, true); // Decode only if it's a string
           // }
           $week_arr = array();

        //   info($requestData);
        //   foreach ($request->clientTimes as $clientTime) {

               $schedule = new Schedule();

               $schedule->date = $splitDate;
              // $schedule->shift_finishes_next_day = $request->shift_finishes_next_day ? 1 : 0;
              $schedule->shift_finishes_next_day = isset($request['shift_finishes_next_day']) && $request['shift_finishes_next_day'] ? 1 : 0;
               $end_date =  date('Y-m-d', strtotime($splitDate));
              // $schedule->start_time = $request['start_time'];
              //$schedule->start_time = $request->['date'] . ' ' . date('H:i', strtotime(@$request->['pick_time'])) . ':00';
              if ($request['shift_type_id'] != 3) {
            //  $schedule->start_time = $request['date'] . ' ' . date('H:i', strtotime($request['pick_time'])) . ':00';
            $schedule->start_time = $request['date'] . ' ' .
           ($request['pick_time'] && !empty($request['pick_time'])
        ? date('H:i', strtotime($request['pick_time'])) . ':00'
        : '00:00:00');
              }
               $end_date1 =  date('Y-m-d', strtotime($futureDate));

               if ($schedule->shift_finishes_next_day == 1) {
                   $end_date = date('Y-m-d', strtotime($splitDate . ' +1 day'));
               }

               //$schedule->end_time = $request['end_time'];
              // $schedule->end_time = $request->['date'] . ' ' . date('H:i', strtotime(@$request->['drop_time'])) . ':00';
            //  $schedule->end_time = $request['date'] . ' ' . date('H:i', strtotime($request['drop_time'])) . ':00';
              if ($request['shift_type_id'] != 1) {
              //  $schedule->end_time = $request['date'] . ' ' . date('H:i', strtotime($request['drop_time'])) . ':00';
              $schedule->end_time = $request['date'] . ' ' .
              ($request['drop_time'] && !empty($request['drop_time'])
           ? date('H:i', strtotime($request['drop_time'])) . ':00'
           : '00:00:00');
            }
               $schedule->break_time_in_minutes = $request['break_time_in_minutes'];
               $schedule->is_repeat = $request['is_repeat'] ? 1 : 0;
               if ($request['is_repeat']) {
                   if ($request['reacurrance'] == 0) {
                       $schedule->reacurrance = 0;
                       $schedule->repeat_time = $request['repeat_days'];
                   } else if ($request['reacurrance'] == 1) {
                       $schedule->reacurrance = 1;
                       $schedule->repeat_time = $request['repeat_weeks'];
                    //    if ($request['mon']) {
                    //        array_push($week_arr, "mon");
                    //    }
                    //    if ($request['tue']) {
                    //        array_push($week_arr, "tue");
                    //    }
                    //    if ($request['wed']) {
                    //        array_push($week_arr, "wed");
                    //    }
                    //    if ($request['thu']) {
                    //        array_push($week_arr, "thu");
                    //    }
                    //    if ($request['fri']) {
                    //        array_push($week_arr, "fri");
                    //    }
                    //    if ($request['sat']) {
                    //        array_push($week_arr, "sat");
                    //    }
                    //    if ($request['sun']) {
                    //        array_push($week_arr, "sun");
                    //    }
                    if (!empty($request['occurs_on'])) {
                        // Decode the JSON string from the payload
                        $week_arr = json_decode($request['occurs_on'], true);
                    }

                    // If additional days logic is needed
                    $days_of_week = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
                    foreach ($days_of_week as $day) {
                        if (!empty($request[$day]) && !in_array($day, $week_arr)) {
                            $week_arr[] = $day; // Add unique day
                        }
                    }

                       $schedule->occurs_on = json_encode($week_arr);
                   } else if ($request['reacurrance'] == 2) {
                       $schedule->reacurrance = 2;
                       $schedule->repeat_time = $request['repeat_time'];
                       $schedule->occurs_on = $request['repeat_day_of_month'];
                   }
                   $schedule->end_date = $end_date1; //$request->reacurrance_end_time;
               }
               $schedule->address = $request['address'];
               $schedule->apartment_no = $request['apartment_no'];
               $schedule->previous_day_pick = $request['previous_day_pick'];
               // $schedule->is_drop_off_address = 0;  //$request->is_drop_off_address ?? 0;
               // if ($request->is_drop_off_address) {
               //     $schedule->drop_off_address = $request->drop_off_address;
               //     $schedule->drop_off_apartment_no = $request->drop_off_apartment_no;
               // }

               $schedule->mileage = $request['mileage'];
              // if (isset($request['shift_type_id'])) {
                   $schedule->shift_type_id = $request['shift_type_id'];
              // }
               if ($request['allowance_id']) {
                   $schedule->allowance_id = $request['allowance_id'];
               }
               $schedule->additional_cost = $request['additional_cost'];
               $schedule->ignore_staff_count = ($request['ignore_staff_count'] === 'on') ? 1 : 0;
               $schedule->confirmation_required = ($request['confirmation_required'] === 'on') ? 1 : 0;

             //  $schedule->instructions = $request->instructions;
               // if ($schedule->schedule_group_id == 0 && $schedule->schedule_group_id > 0) {
               //     $schedule->schedule_group_id = $schedule->schedule_group_id ?? $request->id;
               // }
            //    $existingSchedule = Schedule::find($request['id']);

            //    if ($existingSchedule && $existingSchedule->schedule_parent_id > 0) {
            //        $schedule->schedule_parent_id = $existingSchedule->schedule_parent_id;
            //    } else {
            //        $schedule->schedule_parent_id = $scheduleParentId ?? ($schedule->id ?? $request['id']);
            //    }
          //  if (isset($request['schedule_parent_id']) && $request['schedule_parent_id'] > 0) {
                // Check if the provided 'schedule_parent_id' exists in the 'schedule_parent_id' column of any schedule
                $parentScheduleExists = Schedule::where('schedule_parent_id', $request['id'])->first();

                if ($parentScheduleExists) {

                    // If the parent schedule ID exists in any schedule's 'schedule_parent_id', store it
                    $schedule->schedule_parent_id =$parentScheduleExists->schedule_parent_id;
                } else {

                    $schedule->schedule_parent_id = @$request['schedule_parent_id'];;
                }

          //  } else {
                // If no 'schedule_parent_id' is provided in the request, use the new schedule's own ID
              // $schedule->schedule_parent_id = @$request['schedule_parent_id']; // Using the new schedule's own ID
            //}

               $schedule->longitude = @$request['selectedLocationLng'] ?? null;
               $schedule->latitude = @$request['selectedLocationLat'] ?? null;
               $schedule->driver_id = $request['driver_id'];
            $schedule->vehicle_id = $request['vehicle_id'];
          $schedule->pricebook_id = $request['pricebook_id'];
             $schedule->city = $request['scheduleCity'];
          $schedule->locality = $request['scheduleLocation'];
               $schedule->save();


               // if ($schedule->add_to_job_board == 0) {
               //     if ($request->carerTimes) {
               //         foreach ($request->carerTimes as $carerTime) {
               //             $scheduleCarers = new ScheduleCarer();
               //             $scheduleCarers->schedule_id = $schedule->id;
               //             foreach ($carerTime as $key => $value) {
               //                 if ($key == "carer_id") {
               //                     //$scheduleCarers->carer_id = $value ?? null;
               //                     $scheduleCarers->carer_id = empty($value) ? null : $value;
               //                 } else if ($key == "start_time") {
               //                     $scheduleCarers->start_time = $request->date . ' ' . $value . ':00';
               //                 } else if ($key == "end_time") {
               //                     $scheduleCarers->end_time = $request->date . ' ' . $value . ':00';
               //                 } else if ($key == "pay_group_id") {
               //                    // $scheduleCarers->pay_group_id  = $value ?? null;
               //                   // $scheduleCarers->pay_group_id = empty($value) ? null : $value;
               //                   $scheduleCarers->pay_group_id = !empty($value) && Paygroup::find($value) ? $value : null;
               //                 }
               //             }
               //             $scheduleCarers->save();
               //         }
               //     }

               // }
               if ($request['shift_type_id'] == 2 || $request['shift_type_id'] == 3) {
                   foreach ($request['carers'] as $carer) {
                       $existingCarer = ScheduleCarer::where('schedule_id', $schedule->id)
                           ->where('carer_id', $carer)->where('shift_type', 'drop')
                           ->first();
                       if (!$existingCarer) {
                           $scheduleCarers = new ScheduleCarer();
                           $scheduleCarers->schedule_id = $schedule->id;
                           $scheduleCarers->carer_id = $carer;
                           $scheduleCarers->shift_type = 'drop';
                           $scheduleCarers->save();
                       }
                   }
               }
              if ($request['shift_type_id'] == 1 || $request['shift_type_id'] == 2) {
                   foreach ($request['carers'] as $carer) {
                       $existingCarer = ScheduleCarer::where('schedule_id', $schedule->id)
                           ->where('carer_id', $carer)->where('shift_type', 'pick')
                           ->first();
                       if (!$existingCarer) {
                           $scheduleCarers = new ScheduleCarer();
                           $scheduleCarers->schedule_id = $schedule->id;
                           $scheduleCarers->carer_id = $carer;
                           $scheduleCarers->shift_type = 'pick';
                           $scheduleCarers->save();
                       }
                   }
               }



           //}

       return response()->json([
           'status' => true,
           'data' => $schedule,
           'message' => 'You have successfully addedd schedule.'

       ], 200);
   }

  /**
     * @OA\Post(
     * path="/uc/api/deletefutureSchedule",
     * operationId="deletefutureSchedule",
     * tags={"Schedules"},
     * summary="Delete schedule",
     *   security={ {"Bearer": {} }},
     * description="Delete schedule",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id","date","future_date"},
     *               @OA\Property(property="id", type="text"),
     *               @OA\Property(property="date", type="date"),
     *               @OA\Property(property="future_date", type="date"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Schedule deleted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Schedule deleted successfully.",
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





                public function deletefutureSchedule(Request $request){

                    try {

                        $validatedData = $request->validate([
                            'id' => 'required|exists:schedules,id',
                            'date' => 'required|date',
                            'future_date' => 'required|date',
                        ]);

                        //$request = json_decode($request->data);
                        $schedule = Schedule::find($request->id);
                        if (!$schedule) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Schedule not found.',
                            ], 404);
                        }

                        $todayDate = Carbon::today()->toDateString();
                        $requestDate = Carbon::parse($request->date)->toDateString();


                        if ($requestDate <= $todayDate) {
                            return response()->json([
                                'status' => false,
                                'message' => 'The request date cannot be today or a past date. Please select a future date.',
                            ], 400);
                        }

                        // Dynamically get schedule start and end times
                        $scheduleStartDate = Carbon::parse($schedule->date);
                        $scheduleEndDate = Carbon::parse($schedule->end_date);



                        $providedDate = Carbon::parse($request->date);
                        $futureDate = Carbon::parse($request->future_date);
                        if ($futureDate < $providedDate) {
                            return response()->json([
                                'status' => false,
                                'message' => 'The future date cannot be earlier than the provided date.',
                            ], 400);
                        }
                        $startDateToDelete = $providedDate;
                        $endDateToDelete = $futureDate;
                        //$providedDate = Carbon::parse('2024-10-21');
                        $providedDate = Carbon::parse($request->date);
                        $futureDate = Carbon::parse($request->future_date);
                        $startDate = Carbon::parse($schedule->date);
                        $endDate = Carbon::parse($schedule->end_date)->endOfDay();

                        $scheduleGroupId = $schedule->schedule_parent_id;

                        // $scheduleGroupId = $schedule->schedule_group_id;

                        $scheduleGroupId = $schedule->schedule_parent_id == null ? $schedule->id : $schedule->schedule_parent_id;

                        $schedules = Schedule::where(function ($query) use ($scheduleGroupId) {
                            $query->where('schedule_parent_id', $scheduleGroupId)->orWhere('id', $scheduleGroupId);
                        })->get()->groupBy('schedule_parent_id');


                        if($schedules->flatten()->count() >1){

                            info("working on it multiple");

                            foreach ($schedules as $groupedSchedules) {

                                foreach ($groupedSchedules as $key=>$schedule) {

                                    info("second delete ". $groupedSchedules);

                                    $startDate = Carbon::parse($schedule->date)->startOfDay();
                                    $endDate = Carbon::parse($schedule->end_date)->endOfDay();
                                    $providedDate = $providedDate->startOfDay();
                                    $futureDate = $futureDate->endOfDay();

                                    if ($endDate->lt($providedDate)) {
                                        continue; // This schedule ends before the range, skip it
                                    }

                                    if ($startDate->gt($futureDate)) {
                                        continue; // This schedule starts after the range, skip it
                                    }

                                    if ($providedDate->gte($startDate) && $providedDate->lte($endDate) && $futureDate->lte($endDate)) {

                                        info("delete if 1");

                                        if ($providedDate->isSameDay($startDate) && $futureDate->between($startDate, $endDate) && !$futureDate->isSameDay($endDate)){

                                            info("delete 01");

                                            $schedule->date = $futureDate->copy()->addDay()->format('Y-m-d');
                                            $schedule->save();



                                            return response()->json([
                                                'status' => true,
                                                'message' => 'Schedule has been deleted and split successfully.',
                                            ], 200);
                                        }

                                        if ($providedDate->isSameDay($startDate) && $futureDate->isSameDay($endDate)){
                                            info("delete 02");

                                            $schedule->carers()->delete();
                                            $schedule->delete();
                                            return response()->json([
                                                'status' => true,
                                                'message' => 'Schedule has been deleted and split successfully.',
                                            ], 200);
                                        }

                                        if($providedDate->between($startDate, $endDate) && $futureDate->isSameDay($endDate)){
                                            info("delete 03");
                                            $schedule->end_date = $providedDate->copy()->subDay()->format('Y-m-d');
                                            $schedule->save();



                                            return response()->json([
                                                'status' => true,
                                                'message' => 'Schedule has been deleted and split successfully.',
                                            ], 200);

                                        }

                                        if ($providedDate->gt($startDate) && $futureDate->lt($endDate)) {

                                        info("Start 3");
                                            $schedule->end_date = $providedDate->copy()->subDay()->format('Y-m-d');
                                            $schedule->save();

                                            $newSchedule = $schedule->replicate();
                                            $newSchedule->start_time = $futureDate->copy()->subDay()->format('Y-m-d'). ' ' . Carbon::parse($schedule->start_time)->format('H:i:s');
                                            $newSchedule->end_date = $endDate->format('Y-m-d');
                                            $newSchedule->date = $futureDate->copy()->addDay()->format('Y-m-d');
                                            $newSchedule->schedule_parent_id = $schedule->id;
                                            $newSchedule->save();


                                            $scheduleCarers = ScheduleCarer::where('schedule_id', $schedule->id)->get();
                                            foreach ($scheduleCarers as $carer) {
                                                $newCarer = $carer->replicate();
                                                $newCarer->schedule_id = $newSchedule->id;
                                                $newCarer->save();
                                            }





                                            return response()->json([
                                                'status' => true,
                                                'message' => 'Schedule has been deleted and split successfully',
                                            ], 200);


                                        }


                                    }else{

                                    // If schedules touch more than 2 schedules
                                        foreach ($schedules as $groupedSchedules) {

                                            foreach ($groupedSchedules as $key => $schedule) {

                                                info("start else 2");

                                                $startDate = Carbon::parse($schedule->date)->startOfDay();
                                                $endDate = Carbon::parse($schedule->end_date)->endOfDay();
                                                $currentProvidedDate = $providedDate->copy()->startOfDay();
                                                $currentFutureDate = $futureDate->copy()->endOfDay();

                                                if ($endDate->lt($currentProvidedDate)) {
                                                    continue; // This schedule ends before the range, skip it
                                                }
                                                // 2. Skip schedules that start after the future date (range ends)
                                                if ($startDate->gt($currentFutureDate)) {
                                                    continue; // This schedule starts after the range, skip it
                                                }

                                                // 1. If the schedule falls entirely between providedDate and futureDate, delete it
                                                if ($startDate->between($currentProvidedDate, $currentFutureDate) && $endDate->between($currentProvidedDate, $currentFutureDate)) {

                                                    $schedule->carers()->delete();

                                                    $schedule->delete();



                                                    continue;
                                                }

                                                // 2. If the schedule ends exactly on the provided date and continues beyond the future date
                                                if ($endDate->isSameDay($currentProvidedDate) && $currentFutureDate->gt($endDate)) {
                                                    $schedule->end_date = $currentProvidedDate->subDay()->toDateString();
                                                    $schedule->save();




                                                }elseif($currentProvidedDate->gt($startDate) && $endDate->lt($currentFutureDate)) {
                                                    info("delete 0.1");
                                                    $schedule->end_date = $currentProvidedDate->subDay()->toDateString();

                                                    Schedule_Cancel::where('schedule_id', $schedule->id)
                                                    ->where(function($query) use ($currentProvidedDate, $currentFutureDate) {
                                                        $query->where('date', $currentProvidedDate->format('Y-m-d'))
                                                            ->orWhere('date', $currentFutureDate->format('Y-m-d'))
                                                            ->orWhereBetween('date', [$currentProvidedDate->format('Y-m-d'), $currentFutureDate->format('Y-m-d')]);
                                                    })
                                                    ->delete();
                                                }

                                                // 3 If the schedule ends exactly on the provided and start date is same date and continues beyond the future date then delete
                                                if ($startDate->isSameDay($currentProvidedDate) && $endDate->isSameDay($currentProvidedDate) && $currentFutureDate->gt($endDate)) {
                                                    info("delete 1.1");

                                                    $schedule->carers()->delete();

                                                    $schedule->delete();

                                                }

                                                // 4. If the schedule starts on the future date but continues after it
                                                if ($startDate->isSameDay($currentFutureDate) && $endDate->gt($currentFutureDate)) {
                                                    info("Schedule adjustment 3: Changing start date to " . $currentFutureDate->toDateString());
                                                    $schedule->date = $currentFutureDate->addDay()->toDateString(); // Adjust start date
                                                    $schedule->save();



                                                }else if ($startDate->lt($currentFutureDate) && $endDate->gt($currentFutureDate)) {
                                                info("Schedule adjustment 4: Changing start date to " . $currentFutureDate->toDateString());
                                                $schedule->date = $currentFutureDate->addDay()->toDateString(); // Adjust start date
                                                $schedule->save();




                                                }

                                                // 5. If the schedule starts on the future date but continues after it
                                                if ($startDate->isSameDay($currentFutureDate) && $endDate->isSameDay($currentFutureDate)) {

                                                    $schedule->carers()->delete();

                                                    $schedule->delete();
                                                }
                                                if ($startDate->isSameDay($currentFutureDate) && $endDate->isSameDay($currentFutureDate)) {

                                                    $schedule->carers()->delete();

                                                    $schedule->delete();
                                                }
                                            }

                                        }

                                        return response()->json([
                                            'status' => true,
                                            'message' => 'Schedule has been updated and split successfully',
                                        ], 200);
                                    }

                                }
                            }


                            return response()->json([
                                'status' => true,
                                'message' => 'Schedule has been updated and split successfully 1.',
                            ], 200);


                        }else{

                            // clieck on first shift
                            if ($providedDate->isSameDay($startDate) && $futureDate->between($startDate, $endDate)){

                                info("Okay I am working 1");
                                $schedule->date = $futureDate->copy()->addDay()->format('Y-m-d');
                                $schedule->save();




                                return response()->json([
                                    'status' => true,
                                    'message' => 'Schedule has been updated and split successfully.',
                                ], 200);

                            }

                            if($providedDate->between($startDate, $endDate) && $futureDate->isSameDay($endDate)){

                                info("Okay I am working 2");

                                $schedule->end_date = $providedDate->copy()->subDay()->format('Y-m-d');
                                $schedule->save();


                                return response()->json([
                                    'status' => true,
                                    'message' => 'Schedule has been updated and split successfully.',
                                ], 200);

                            }


                            if ($providedDate->gt($startDate) && $providedDate->lt($endDate) && $futureDate->lt($endDate)) {

                                info("Okay I am working 31");

                                $schedule->end_date = $providedDate->copy()->subDay()->format('Y-m-d');
                                $schedule->save();



                                $newSchedule = $schedule->replicate();
                                $newSchedule->start_time = $futureDate->copy()->subDay()->format('Y-m-d'). ' ' . Carbon::parse($schedule->start_time)->format('H:i:s');
                                $newSchedule->end_date = $endDate->format('Y-m-d');
                                $newSchedule->date = $futureDate->copy()->addDay()->format('Y-m-d');
                                $newSchedule->schedule_parent_id = $schedule->id;


                                if ($newSchedule->schedule_parent_id == null) {
                                    $newSchedule->schedule_parent_id = $schedule->id;
                                } else {
                                    $newSchedule->schedule_parent_id = $newSchedule->schedule_parent_id;
                                }
                                $newSchedule->save();



                                $scheduleCarers = ScheduleCarer::where('schedule_id', $schedule->id)->get();
                                foreach ($scheduleCarers as $carer) {
                                    $newCarer = $carer->replicate();
                                    $newCarer->schedule_id = $newSchedule->id;
                                    $newCarer->save();
                                }


                                return response()->json([
                                    'status' => true,
                                    'message' => 'Schedule has been updated and split successfully',
                                ], 200);


                            }else{

                                return response()->json([
                                    'status' => false,
                                    'message' => 'The provided date or future date is not within the schedule\'s range.',
                                ], 400);
                            }

                        }

                    } catch (\Throwable $th) {

                        info("here sql injection");
                        return response()->json([
                            'status' => false,
                            'message' => $th->getMessage(),
                        ], 500);
                    }


                }
                    /**
                         * @OA\Post(
                         * path="/uc/api/deleteparticularSchedule",
                         * operationId="deleteparticularSchedule",
                         * tags={"Schedules"},
                         * summary="Delete schedule",
                         *   security={ {"Bearer": {} }},
                         * description="Delete schedule",
                         *     @OA\RequestBody(
                         *         @OA\JsonContent(),
                         *         @OA\MediaType(
                         *            mediaType="multipart/form-data",
                         *            @OA\Schema(
                         *               type="object",
                         *               required={"id","date"},
                         *               @OA\Property(property="id", type="text"),
                         *  @OA\Property(property="date", type="date"),
                         *            ),
                         *        ),
                         *    ),
                         *      @OA\Response(
                         *          response=201,
                         *          description="Schedule deleted successfully.",
                         *          @OA\JsonContent()
                         *       ),
                         *      @OA\Response(
                         *          response=200,
                         *          description="Schedule deleted successfully.",
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

                        public function deleteparticularSchedule(Request $request)
                        {
                            try {
                                $validatedData = $request->validate([
                                    'id' => 'required|exists:schedules,id',
                                    'date' => 'required|date',
                                ]);

                                $schedule = Schedule::find($request->id);

                                if (!$schedule) {
                                    return response()->json([
                                        'status' => false,
                                        'message' => 'Schedule not found.',
                                    ], 404);
                                }

                                $todayDate = Carbon::today()->toDateString();
                                $requestDate = Carbon::parse($request->date)->toDateString();


                                if ($requestDate <= $todayDate) {
                                    return response()->json([
                                        'status' => false,
                                        'message' => 'The request date cannot be today or a past date. Please select a future date.',
                                    ], 400);
                                }






                                $scheduleStartDate = Carbon::parse($schedule->date);
                                $scheduleEndDate = Carbon::parse($schedule->end_date);



                                //$providedDate = Carbon::parse('2024-10-25');
                                $providedDate = Carbon::parse($request->date);
                                $futureDate = Carbon::parse($request->future_date);

                                $startDateToDelete = $providedDate;
                                $endDateToDelete = $providedDate; //$futureDate;



                                info($schedule->start_time);


                                $splitDate = Carbon::parse($request->date);
                                $cleanStartTime = trim(str_replace(' :', '', $schedule->date));
                               // $startDate = Carbon::parse($schedule->start_time)->startOfDay();
                               $startDate = Carbon::parse($cleanStartTime)->startOfDay();

                               info('dsdsds');
                                $endDate = Carbon::parse($schedule->end_date)->endOfDay();

                                if ($splitDate->isSameDay($startDate) && $schedule->end_date == null) {

                                    ScheduleCarer::where('schedule_id', $schedule->id)->delete();

                                    $schedule->delete();
                                    return response()->json([
                                        'status' => true,
                                        'message' => 'Schedule and its related clients and carers deleted successfully.',
                                    ], 200);
                                }

                                if ($splitDate->isSameDay($startDate)) {

                                    info("split start date 1 ". $startDate);

                                    $schedule->start_time = $splitDate->copy()->addDay()->format('Y-m-d') . ' ' . Carbon::parse($schedule->date)->format('H:i:s');
                                    //$schedule->start_time = $splitDate->copy()->addDay()->format('Y-m-d');
                                    $schedule->date = $splitDate->copy()->addDay()->format('Y-m-d');

                                    $schedule->save();
                                    ScheduleCarer::where('schedule_id', $schedule->id)->delete();
                                    $schedule->delete();

                                    return response()->json([
                                        'status' => true,
                                        'message' => 'Schedule shift deleted successfully ',
                                        'data' => $schedule
                                    ], 200);
                                }
                                if ($splitDate->isSameDay($endDate)) {

                                    info("here end date ".$endDate);

                                    $schedule->end_date = $splitDate->copy()->subDay()->format('Y-m-d');

                                    $schedule->save();
                                    ScheduleCarer::where('schedule_id', $schedule->id)->delete();
                                    $schedule->delete();

                                    return response()->json([
                                        'status' => true,
                                        'message' => 'Schedule shift deleted successfully',
                                        'data' => $schedule
                                    ], 200);
                                }
                                if ($splitDate->isSameDay($startDate) && $splitDate->isSameDay($endDate)) {
                                    ScheduleCarer::where('schedule_id', $schedule->id)->delete();
                                    $schedule->delete();

                                    return response()->json([
                                        'status' => true,
                                        'message' => 'Schedule  deleted successfully.',
                                    ], 200);
                                }

                                if ($schedule->schedule_parent_id != null) {



                                    $relatedSchedule = Schedule::where('id', $schedule->schedule_parent_id)->first();

                                    if (!$relatedSchedule) {


                                        ScheduleCarer::where('schedule_id', $schedule->id)->delete();

                                        $schedule->delete();

                                        return response()->json([
                                            'status' => true,
                                            'message' => 'Schedule and its related clients and carers deleted successfully ',
                                        ], 200);
                                    }
                                }


                                if ($splitDate->between($startDate, $endDate)) {

                                    info("between test");

                                    if ($splitDate->isSameDay($startDate) || $splitDate->isSameDay($endDate)) {
                                        ScheduleCarer::where('schedule_id', $schedule->id)->delete();
                                        $schedule->delete();

                                        return response()->json([
                                            'status' => false,
                                            'message' => 'Cannot split the schedule on the exact start or end date.',
                                        ], 400);
                                    }

                                    $schedule->end_date = $splitDate->copy()->subDay()->format('Y-m-d');
                                    $schedule->save();



                                    $newSchedule = $schedule->replicate();
                                    $newSchedule->start_time = $splitDate->copy()->addDay()->format('Y-m-d') . ' ' . Carbon::parse($schedule->start_time)->format('H:i:s');
                                // $newSchedule->start_time = $splitDate->copy()->addDay()->format('Y-m-d');
                                    $newSchedule->end_date = $endDate->format('Y-m-d');
                                    $newSchedule->date = $splitDate->copy()->addDay()->format('Y-m-d');
                                    //$newSchedule->schedule_group_id = $schedule->id;

                                    if ($newSchedule->schedule_parent_id == null) {
                                        $newSchedule->schedule_parent_id = $schedule->id; // Inherit original schedule's ID if current is 0
                                    } else {
                                        // If the new schedule has a group ID, keep it unchanged
                                        $newSchedule->schedule_parent_id = $newSchedule->schedule_parent_id; // This line is actually redundant
                                    }
                                    $newSchedule->save();



                                    $scheduleCarers = ScheduleCarer::where('schedule_id', $schedule->id)->get();
                                    foreach ($scheduleCarers as $carer) {
                                        $newCarer = $carer->replicate();
                                        $newCarer->schedule_id = $newSchedule->id;
                                        $newCarer->save();
                                    }



                                    return response()->json([
                                        'status' => true,
                                        'message' => 'Schedule and its related clients and carers successfully split.',
                                        'data' => [
                                            'updated_schedule' => $schedule,
                                            'new_schedule' => $newSchedule,
                                        ]
                                    ], 200);
                                } else {
                                    return response()->json([
                                        'status' => false,
                                        'message' => 'The provided date is not within the schedule\'s range.',
                                    ], 400);
                                }
                            } catch (\Throwable $th) {
                                return response()->json([
                                    'status' => false,
                                    'message' => $th->getMessage(),
                                ], 500);
                            }
                        }



                         //***************************  Extend Schedule api ********************************/
      /**
     * @OA\Post(
     * path="/uc/api/extendSchedule",
     * operationId="extendSchedule",
     * tags={"Ucruise Schedule"},
     * summary="Extend Schedule",
     *   security={ {"Bearer": {} }},
     * description="Extend Schedule",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={ "id","end_date"},
     *               @OA\Property(property="id", type="text"),
     *               @OA\Property(property="end_date", type="date"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Schedule  extended successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Schedule extented successfully.",
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



                    public function extendSchedule(Request $request)
                        {
                            try {
                                // Validate the incoming request
                                $request->validate([
                                    'id' => 'required|exists:schedules,id',
                                    'end_date' => 'required|date', // Ensure end_date is valid and in the future
                                ]);

                                // Find the schedule
                                $schedule = Schedule::find($request->id);
                                $todayDate = Carbon::today()->toDateString();
                                $requestDate = Carbon::parse($request->end_date)->toDateString();

                                if ($todayDate === $requestDate) {
                                    return response()->json([
                                        'status' => false,
                                        'message' => 'The request date cannot be today. Please select a future date.',
                                    ], 400);
                                }

                                // Case 1: If the schedule itself has a `schedule_parent_id`
                                if ($schedule->schedule_parent_id) {
                                    // Find all schedules with the same `schedule_parent_id`
                                    $relatedSchedules = Schedule::where('schedule_parent_id', $schedule->schedule_parent_id)->get();

                                    if ($relatedSchedules->isNotEmpty()) {
                                        // Get the schedule with the maximum `end_date`
                                        $maxEndDateSchedule = $relatedSchedules->sortByDesc('end_date')->first();

                                        // Update the `end_date` of the schedule with the maximum `end_date`
                                        $maxEndDateSchedule->end_date = $request->end_date;
                                        $maxEndDateSchedule->save();

                                        return response()->json([
                                            'message' => 'Schedule extended successfully.',
                                            'data' => $maxEndDateSchedule,
                                            'status'=> true,
                                        ], 200);
                                    }
                                }

                                // Case 2: If the given ID is used as a `schedule_parent_id` in other schedules
                                $childSchedules = Schedule::where('schedule_parent_id', $schedule->id)->get();

                                if ($childSchedules->isNotEmpty()) {
                                    // Get the schedule with the maximum `end_date`
                                    $maxEndDateSchedule = $childSchedules->sortByDesc('end_date')->first();

                                    // Update the `end_date` of the schedule with the maximum `end_date`
                                    $maxEndDateSchedule->end_date = $request->end_date;
                                    $maxEndDateSchedule->save();

                                    return response()->json([
                                        'message' => 'Schedule extended successfully.',
                                        'data' => $maxEndDateSchedule,
                                        'status'=> true,
                                    ], 200);
                                }

                                // Case 3: If no child schedules exist and the schedule has no `schedule_parent_id`
                                $schedule->end_date = $request->end_date;
                                $schedule->save();

                                return response()->json([
                                    'message' => 'Schedule extended successfully.',
                                    'data' => $schedule,
                                    'status'=> true,
                                ], 200);
                            } catch (\Exception $e) {
                                // Handle any exceptions
                                return response()->json([
                                    'status' => false,
                                    'message' => 'An error occurred while extending the schedule.',
                                    'error' => $e->getMessage(),
                                ], 500);
                            }
                        }
  /**
     * @OA\Post(
     * path="/uc/api/listusers",
     * operationId="listusers",
     * tags={"Ucruise Schedule"},
     * summary="listusers",
     *   security={ {"Bearer": {} }},
     * description="listusers",
     *      @OA\Response(
     *          response=201,
     *          description="Schedule  listed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Schedule listed successfully.",
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

     public function listusers(Request $request)
     {
         // Get the 'driver_staff' value from the request or default to 0 if not set
         $driverStaff = $request->input('driver_staff', 0);

         // Initialize the query
         $query = SubUser::with('roles');

         // Apply role-based filtering based on the 'driver_staff' value
         if ($driverStaff == 1) {
             $query->whereHas("roles", function ($q) {
                 $q->whereIn("name", ["driver"]);
             });
         } elseif ($driverStaff == 2) {
             $query->whereHas("roles", function ($q) {
                 $q->whereIn("name", ["carer"]);
             })->orderBy("id", "DESC");
         } else {
             $query->whereHas("roles", function ($q) {
                 $q->whereNotIn("name", ["admin"]);
             });
         }

         // Execute the query and get the results
         $users = $query->get();

         // Return the users as a JSON response
         return response()->json($users);
    }


    /**
     * @OA\Post(
     *     path="/uc/api/addMultipleSchedule",
     *     operationId="addMultipleSchedule",
     *     tags={"Ucruise Schedule"},
     *     summary="Add multiple schedule",
     *     security={{"Bearer": {}}},
     *     description="Add multiple schedule",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="object",
     *                     description="Your description here"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully added schedule .",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully added schedule .",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */

    public function addMultipleSchedule(Request $request){

        try {

            $schedules = $request->all();
            foreach ($schedules as $scheduleData) {

                $individualRequest = new Request([
                    'data' => json_encode($scheduleData)
                ]);
                $data = $this->addSchedule($individualRequest);
            }
            return response()->json(['message' => 'All schedules added successfully.']);

        } catch (\Throwable $th) {
           return $this->errorResponse($th->getMessage());
        }

    }

}
