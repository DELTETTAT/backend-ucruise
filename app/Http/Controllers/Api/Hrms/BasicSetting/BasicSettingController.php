<?php

namespace App\Http\Controllers\Api\Hrms\BasicSetting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReturningCandidateEligibility;
use App\Models\EmailAddressForAttendanceAndLeave;

class BasicSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\get(
     * path="/uc/api/basic_setting/index",
     * operationId="basicsettingget",
     * tags={"Basic Setting"},
     * summary="Get Basic Setting Request",
     *   security={ {"Bearer": {} }},
     * description="Get Basic Setting Request",
     *      @OA\Response(
     *          response=201,
     *          description="Basic Setting Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Basic Setting Get Successfully",
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
    public function index()
    {
        try {
               $data = [];
               $applicantReturnDays = ReturningCandidateEligibility::first();
               $attendance_emails = EmailAddressForAttendanceAndLeave::where('type',1)->get();
               $leave_emails = EmailAddressForAttendanceAndLeave::where('type',0)->get();

               if ($applicantReturnDays) {
                   $data['days'] = $applicantReturnDays->returning_days;
               }else {
                   $data['days'] = null;
               }
              // $data['days'] = $applicantReturnDays->returning_days;
               $data['attendance_emails'] = $attendance_emails;
               $data['leave_emails'] = $leave_emails;

               return $this->successResponse(
                   $data,
                   "Basic Setting Data"
               );

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */



    /**
     * @OA\Post(
     *     path="/uc/api/basic_setting/store",
     *     operationId="storeBasicSetting",
     *     tags={"Basic Setting"},
     *     summary="Store Basic Setting with Emails",
     *     security={{"Bearer": {}}},
     *     description="Stores the returning candidate eligibility days and email addresses for attendance and leave reports.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="day",
     *                     type="integer",
     *                     example=29,
     *                     description="Number of days after which candidate is eligible to return"
     *                 ),
     *                 @OA\Property(
     *                     property="attendance",
     *                     type="object",
     *                     @OA\Property(
     *                         property="cc",
     *                         type="array",
     *                         @OA\Items(type="string", format="email", example="cc1@example.com")
     *                     ),
     *                 ),
     *                 @OA\Property(
     *                     property="leave",
     *                     type="object",
     *                     @OA\Property(
     *                         property="cc",
     *                         type="array",
     *                         @OA\Items(type="string", format="email", example="leavecc1@example.com")
     *                     ),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Basic Setting Created Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Settings saved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */

    public function store(Request $request)
    {
        try {

                $validated = $request->validate([
                    'day' => 'required|integer|min:1|max:365',
                ]);


                // Save Day
                $exists_records = ReturningCandidateEligibility::get();
                if ($exists_records) {
                    foreach ($exists_records as $key => $record) {
                        $oldDayRecord = ReturningCandidateEligibility::first();
                        $oldDay = $oldDayRecord ? $oldDayRecord->returning_days : null;
                           $record->delete();
                    }
                }
                ReturningCandidateEligibility::create([
                    'returning_days' => $request->day
                ]);

                $exists_records_emails = EmailAddressForAttendanceAndLeave::get();
                if ($exists_records_emails) {
                    foreach ($exists_records_emails as $key => $emailRecord) {
                        $emailRecord->delete();
                    }
                }
                // Email storing logic
                foreach (['attendance' => 1, 'leave' => 0] as $key => $typeValue) {
                        if (!empty($request->$key)) {
                            foreach ($request->$key['cc'] as $email) {
                                EmailAddressForAttendanceAndLeave::create([
                                    'email' => $email,
                                    'type' => $typeValue
                                ]);
                            }
                        }

                }

                // SIMPLE HISTORY SAVING
                $user = auth('sanctum')->user();
                if ($user && $oldDay != $request->day) {
                    \DB::table('update_system_setup_histories')->insert([
                        'employee_id' => $user->id,
                        'date' => now()->format('Y-m-d'),
                        'time' => now()->format('H:i:s'),
                        'updated_by' => $user->id,
                        'notes' => 'Holiday settings updated',
                        'changed' => 'Updated returning days from ' . ($oldDay ?? 'empty') . ' to ' . $request->day,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }


                return response()->json(['message' => 'Settings saved successfully']);
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
