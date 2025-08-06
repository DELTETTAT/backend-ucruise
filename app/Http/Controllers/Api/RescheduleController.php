<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reschedule;
use App\Models\Schedule;
use App\Models\ScheduleCarer;
use App\Models\ScheduleCarerComplaint;
use App\Models\ScheduleCarerStatus;
use App\Models\SubUser;
use App\Models\User;
use App\Models\Reason;
use App\Models\EmailAddressForAttendanceAndLeave;
use App\Mail\RescheduleRequestEmail;
use App\Models\CapRequest;
use Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
class RescheduleController extends Controller
{

    //******************************** Store Reschedule requests api ********************* */
    /**
     * @OA\Post(
     * path="/uc/api/reschedule",
     * operationId="store",
     * tags={"Employee"},
     * summary="Reschedule Request",
     *   security={ {"Bearer": {} }},
     * description="Reschedule Request",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"date", "address","latitude","longitude"},
     *               @OA\Property(property="date", type="text"),
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
     *          description="Reschedule Request Submitted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Reschedule Request Submitted successfully.",
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
    public function store(Request $request)
{
    try {
        $user_id = auth('sanctum')->user()->id;
        $user = SubUser::find($user_id);

        if ($user->hasRole('carer')) {
            $request->validate([
                'date' => 'required|date|date_format:Y-m-d',
                'address' => 'required',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'reason_id' => 'integer|nullable',
                'text' => 'nullable',
            ]);

            $todayDate = Carbon::today()->toDateString();
            $requestDate = Carbon::parse($request->date)->toDateString();

            if ($todayDate === $requestDate) {
                return response()->json([
                    'status' => false,
                    'message' => 'The request date cannot be today. Please select a future date.',
                ], 400);
            }

            $scheduleExists = DB::table('schedule_carers')
            ->join('schedules', 'schedule_carers.schedule_id', '=', 'schedules.id')
            ->where('schedule_carers.carer_id', $user_id)
            ->where(function ($query) use ($requestDate) {
                $query->where('schedules.date', '<=', $requestDate)
                    ->where('schedules.end_date', '>=', $requestDate);
            })
            ->exists();

            if (!$scheduleExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have any current schedule for the requested date.',
                ], 400);
            }


            $reschedule = Reschedule::where('user_id', $user_id)->where('date', $requestDate)->first();

            if ($reschedule) {
                if ($reschedule->status == 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A reschedule request for this date already exists. Please select a different date.',
                    ], 400);
                }

                // Update the existing entry
                $reschedule->date = $request->date;
                $reschedule->address = $request->address;
                $reschedule->latitude = $request->latitude;
                $reschedule->longitude = $request->longitude;
                $reschedule->reason_id = $request->reason_id == 0 ? NULL : $request->reason_id;
                $reschedule->text = $request->text;
                $reschedule->status = 0;
                $reschedule->save();

                //////////////////   send email for reschedule
                $reason_type = Reason::find($request->reason_id);

                $emailData = [
                    'user_name' => $user->first_name." ".$user->last_name,
                    'date' => $request->date,
                    'reason' => isset($reason_type->message) ? $reason_type->message : "",
                    'text' => $request->text,
                    'location' => $request->address,
                ];

                $emails = EmailAddressForAttendanceAndLeave::where('type',0)->get();

                foreach ($emails as $key => $email) {
                     Mail::to($email->email)->send(new RescheduleRequestEmail($emailData));
                }
                //////////////////   send email for reschedule

                return response()->json(['success' => true, "message" => "Reschedule Request Updated successfully"], 200);
            } else {
                // Create a new entry if none exists
                $reschedule = new Reschedule();
                $reschedule->user_id = $user_id;
                $reschedule->date = $request->date;
                $reschedule->address = $request->address;
                $reschedule->latitude = $request->latitude;
                $reschedule->longitude = $request->longitude;
                $reschedule->reason_id = $request->reason_id == 0 ? NULL : $request->reason_id;
                $reschedule->text = $request->text;
                $reschedule->status = 0;
                $reschedule->save();

                //////////////////   send email for reschedule
                $reason_type = Reason::find($request->reason_id);

                $emailData = [
                    'user_name' => $user->first_name." ".$user->last_name,
                    'date' => $request->date,
                    'reason' => isset($reason_type->message) ? $reason_type->message : "",
                    'text' => $request->text,
                    'location' => $request->address,
                ];

                $emails = EmailAddressForAttendanceAndLeave::where('type',0)->get();

                foreach ($emails as $key => $email) {
                     Mail::to($email->email)->send(new RescheduleRequestEmail($emailData));
                }
                //////////////////   send email for reschedule

                return response()->json(['success' => true, "message" => "Reschedule Request Submitted successfully"], 200);
            }
        } else {
            return response()->json(['success' => false, "message" => "User is not an employee"], 401);
        }
    } catch (\Throwable $th) {
        return response()->json([
            'success' => false,
            'message' => $th->getMessage()
        ], 500);
    }
}



    //******************************** add Cap requests api ********************* */
    /**
     * @OA\Post(
     * path="/uc/api/caprequest",
     * operationId="caprequest",
     * tags={"Employee"},
     * summary="caprequest Request",
     *   security={ {"Bearer": {} }},
     * description="caprequest Request",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"date", "address","latitude","longitude"},
     *               @OA\Property(property="date", type="text"),
     *               @OA\Property(property="address", type="text"),
     *               @OA\Property(property="latitude", type="text"),
     *               @OA\Property(property="longitude", type="text"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Reschedule Request Submitted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Reschedule Request Submitted successfully.",
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

     public function caprequest(Request $request){

            try {
                $user_id = auth('sanctum')->user()->id;
                $user = SubUser::find($user_id);
                $reschedule = new  CapRequest;
                $reschedule->user_id = $user_id;
                $reschedule->from_date = $request->date;
                $reschedule->address = $request->address;
                $reschedule->latitude = $request->latitude;
                $reschedule->longitude = $request->longitude;
                $reschedule->status = 0;
                $reschedule->save();
                return response()->json(['success' => true, "message" => "Cap request added successfully"], 200);
            } catch (\Throwable $th) {
                return response()->json(['success' => false, "message" => $th->getMessage()], 500);
            }

     }


    //******************************** List Cap requests api ********************* */
     
    /**
     * @OA\Get(
     * path="/uc/api/cablistRequest",
     * operationId="cablistRequest",
     * tags={"Employee"},
     * summary="Show cab list Request",
     *   security={ {"Bearer": {} }},
     * description="Show cab list Request",
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Status of the cab request (0=Pending, 1=Approved, 2=Rejected, 3=Submitted, 4=Waiting)",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1, 2, 3, 4}
     *         )
     *     ),
     *      @OA\Response(
     *          response=201,
     *          description="Cab list listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Cab list listed successfully",
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


    public function cablistRequest(Request $request){
        try {

            $caplistQuery =Caprequest::with('user:id,first_name,email');
            if ($request->has('status') && $request->input('status') != 'all') {
                $caplistQuery->where('status', $request->input('status'));
            }
            $caplist = $caplistQuery->get();
            return response()->json([
                'data' => $caplist,
                'success' => true, 
                'message' => 'Cap request list'
            ]);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, "message" => $th->getMessage()], 500);
        }

    }


    //******************************** Update Cap requests api ********************* */
    /**
     * @OA\Post(
     * path="/uc/api/caprequestUpdate",
     * operationId="caprequestUpdate",
     * tags={"Employee"},
     * summary="caprequest Request update",
     *   security={ {"Bearer": {} }},
     * description="caprequest Request update",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *             type="object",
     *             required={"id","user_id","address","latitude","longitude","status"},
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="latitude", type="string"),
     *             @OA\Property(property="longitude", type="string"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="date", type="string", format="date"),
     *             @OA\Property(property="reason", type="string"),
     *             )
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Reschedule Request Submitted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Reschedule Request Submitted successfully.",
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
    
    public function caprequestUpdate(Request $request){

        try {
            $request->validate([
                'id' => 'required|integer|min:1',
                'user_id' => 'required|integer',
                'address' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
                'status' => 'nullable|integer|in:0,1,2,3,4',
            ]);

            $statusMap = [
                0 => 'Pending',
                1 => 'Approved',
                2 => 'Rejected',
                3 => 'Submitted',
                4 => 'Waiting',
            ];

            if($request->has('id')){
                $caprequest = Caprequest::find($request->input('id'));
                if (!$caprequest) {
                    return response()->json(['success' => false, "message" => "Cap request not found"], 404);
                }
               $userId =  $caprequest->user_id;
               $caprequest = Caprequest::where('user_id', $userId)->latest()->first();
               $subuser = SubUser::find($userId);
               $user = User::find($userId);
               $noshow  = $user->no_show;
               $statusCode = $request->input('status');
            
               if($noshow =="Yes" && $statusCode ==1){
                    $user->no_show = "No";
                    $subuser->no_show = "No";
                    $user->save();
                    $subuser->save();
               }


               if($caprequest && $subuser){
                    $caprequest->address = $request->input('address');
                    $caprequest->latitude = $request->input('latitude');
                    $caprequest->longitude = $request->input('longitude');
                    $caprequest->status =  $statusCode;
                    $caprequest->save();

                    $emailData = [
                        'user_name' => $subuser->first_name." ".$subuser->last_name,
                        'date' => $request->date,
                        'reason' => isset($request->reason) ? $request->reason : "",
                        'text' => $request->reason,
                        'location' => $request->address,
                        'status' => $statusMap[$statusCode] ?? 'Unknown'
                    ];

                    Mail::send('email.capRequest', ['emailData' => $emailData], function ($message) use ($subuser) {
                        $message->to($subuser->email)
                                ->subject('Your Reschedule Request');
                    });
                    return response()->json(['success' => true, 'message' => 'Cap request updated successfully']);
               }else{
                     return response()->json(['success' => false, "message" => "user's data not found"], 404);
               }
               
            }

        } catch (\Throwable $th) {
            return response()->json(['success' => false, "message" => $th->getMessage()], 500);
        }
    }


    //*************************** Change Complaint status api ****************************/
    /**
     * @OA\Post(
     * path="/uc/api/closeComplaint",
     * operationId="closeComplaint",
     * tags={"Employee"},
     * summary="Change complaint status",
     *   security={ {"Bearer": {} }},
     * description="Change complaint status",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="text")
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Complaint status changed succesfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Complaint status changed succesfully.",
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


    public function closeComplaint(Request $request)
    {
        try {

            $request->validate([
                'id' => 'required|exists:schedule_carer_complaints,id'
            ]);
            $scheduleCarerComplaint = ScheduleCarerComplaint::where('id', $request->id)->first();
            if ($scheduleCarerComplaint) {
                $scheduleCarerComplaint->status = 0;
                $scheduleCarerComplaint->save();
            }

            return response()->json(['success' => true, "message" => "Complaint status changed successfully"], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, "message" => $th->getMessage()], 500);
        }
    }



    //*********************************** Store Complaints api *************************** */


    /**
     * @OA\Post(
     * path="/uc/api/store-complaints",
     * operationId="storeComplaint",
     * tags={"Employee"},
     * summary="Store Complaint",
     *   security={ {"Bearer": {} }},
     * description="Store Complaint",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"schedule_id", "schedule_type","driver_id" },
     *               @OA\Property(property="schedule_id", type="text"),
     *               @OA\Property(property="driver_id", type="text"),
     *               @OA\Property(property="reason_id", type="text"),
     *               @OA\Property(property="text", type="text"),
     *               @OA\Property(property="schedule_type", type="text", description="pick or drop"),
     *               @OA\Property(property="type", type="text", description="audio or video or image"),
     *               @OA\Property(property="file", type="file",  description="file is required if we have type"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Complaint submitted succesfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Complaint submitted succesfully.",
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


    public function storeComplaint(Request $request)
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            if ($user->hasRole('carer')) {


                $request->validate([
                    'schedule_id' => 'required|integer',
                    'schedule_type' => 'required|in:pick,drop',
                    'driver_id' => 'required|integer',
                    'reason_id' => 'integer|nullable',
                    'type' => 'nullable|in:image,audio,video',
                    'file' => 'nullable|file|required_with:type|mimes:jpeg,png,gif,mp3,mp4,avi,mov',
                ]);

                $schedule_carer = ScheduleCarer::where('schedule_id', $request->schedule_id)->where('carer_id', $user_id)->where('shift_type', $request->schedule_type)->first();
                if (!$schedule_carer) {
                    return response()->json(['success' => false, "message" => "Staff does not exist in this ride"], 500);
                }
                $schedule_driver = Schedule::where('id', $request->schedule_id)->where('driver_id', $request->driver_id)->exists();
                if (!$schedule_driver) {
                    return response()->json(['success' => false, "message" => "Driver does not exist in this ride"], 500);
                }
                $schedule_carer_status = ScheduleCarerStatus::where(['schedule_carer_id' => $schedule_carer->id, 'date' => date('Y-m-d')])->first();

                if (!$schedule_carer_status) {
                    return response()->json(['success' => false, "message" => "The ride has not started yet"], 500);
                }
                $complaint = new ScheduleCarerComplaint();
                $complaint->schedule_id = $request->schedule_id;
                $complaint->staff_id = $user_id;
                $complaint->schedule_type = $request->schedule_type;
                $complaint->driver_id = $request->driver_id;
                $complaint->reason_id = $request->reason_id == 0 ? NULL : $request->reason_id;
                $complaint->text = $request->text;
                $complaint->type = $request->type;
                $complaint->date = date('Y-m-d');

                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    // Additional check to ensure a file was uploaded
                    if ($file->isValid()) {
                        $filename = time() . '.' . $file->extension();
                        $filePath = public_path() . '/files/complaints/';

                        $file->move($filePath, $filename);

                        $complaint->image_path = ($request->type == 'image') ? $filename : null;
                        $complaint->audio_path = ($request->type == 'audio') ? $filename : null;
                        $complaint->video_path = ($request->type == 'video') ? $filename : null;
                    }
                }
                $complaint->status = 1; // Active status


                $complaint->save();

                return response()->json(['success' => true, "message" => "Complaint submitted successfully"], 200);
            } else {
                return response()->json(['success' => false, "message" => "User is not an employee"], 401);
            }
        } catch (\Throwable $th) {
            return response()->json(['success' => false, "message" => $th->getMessage()], 500);
        }
    }


    //***************************** Show Complaints api************************************* */

    /**
     * @OA\Get(
     * path="/uc/api/show-complaints",
     * operationId="showComplaint",
     * tags={"Employee"},
     * summary="Show Complaints",
     *   security={ {"Bearer": {} }},
     * description="Show Complaints",
     *      @OA\Response(
     *          response=201,
     *          description="Complaints listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Complaints listed successfully",
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
    public function showComplaint()
    {

        try {
            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            if ($user->hasRole('carer')) {
                $complaints = ScheduleCarerComplaint::with('reason')->whereYear('date', '=', date('Y'))->where('staff_id', $user_id)->get();
                if ($complaints) {
                    @$complaints->filePath = url('public/files/complaints/');
                    $complaints = @$complaints->sortByDesc('created_at')->values();
                    $this->data['complaints'] = $complaints;
                }
                return response()->json(['success' => true, 'data' => $this->data, "message" => "Complaints listed successfully"], 200);
            }
            return response()->json(['success' => false, "message" => "User is not an employee"], 401);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, "message" => $th->getMessage()], 500);
        }
    }

}
