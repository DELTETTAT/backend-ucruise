<?php

namespace App\Http\Controllers\Api\Hrms\BasicSetting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ReminderRequest;
use App\Models\HrmsReminder;
use App\Models\Reminder;
use App\Http\Resources\Reminders\ReminderResource;
use App\Http\Resources\Reminders\ReminderCollection;
use Carbon\Carbon;
use DB;
use App\Models\User;
use App\Jobs\reminderSendEmailJob;

class ReminderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    /**
     * @OA\get(
     * path="/uc/api/reminders/index",
     * operationId="getreminders",
     * tags={"Reminders"},
     * summary="Get reminders Request",
     *   security={ {"Bearer": {} }},
     * description="Get reminders Request",
     *      @OA\Response(
     *          response=201,
     *          description="reminders Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="reminders Get Successfully",
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

            $getReminders = HrmsReminder::all();

            return $this->successResponse(
                new ReminderCollection($getReminders),
                "Reminders List"
            );
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage());
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
     * path="/uc/api/reminders/store",
     * operationId="storreminders",
     * tags={"Reminders"},
     * summary="Store reminders Request",
     *   security={ {"Bearer": {} }},
     * description="Store reminders Request",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="target", type="string"),
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="description", type="text" ),
     *              @OA\Property(property="date", type="date" ),
     *              @OA\Property(property="type", type="text" ),
     *              @OA\Property(property="status", type="text" ),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="reminders Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="reminders Created Successfully",
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
    public function store(ReminderRequest $request)
    {
        try {
            $validated = $request->validated();

            $reminder = HrmsReminder::create($validated);

            // Record creation history (added line)
            $this->recordReminderCreation($reminder);

            return $this->successResponse([], "Reminder Created Successfully");
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }

   // Private function added
   private function recordReminderCreation($reminder)
    {
        $user = auth('sanctum')->user();
        if (!$user) return;

        $date = $reminder->date ? date('Y-m-d', strtotime($reminder->date)) : 'Not set';

        DB::table('update_system_setup_histories')->insert([
            'employee_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'updated_by' => $user->id,
            'notes' => 'Reminder created',
            'changed' => "Created new reminder\n"
                    . "Title: " . ($reminder->title ?? 'Not specified') . "\n"
                    . "Description: " . ($reminder->description ?? 'Not specified') . "\n"
                    . "Date: " . $date . "\n",
            'created_at' => now(),
            'updated_at' => now()
        ]);
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



    /**
     * @OA\get(
     * path="/uc/api/reminders/edit/{id}",
     * operationId="editreminders",
     * tags={"Reminders"},
     * summary="Edit reminders Request",
     *   security={ {"Bearer": {} }},
     * description="Edit reminders Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="reminders Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="reminders Edited Successfully",
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
    public function edit($id)
    {
        try {
            $getReminder = HrmsReminder::find($id);

            if ($getReminder) {
                return $this->successResponse(
                    new ReminderResource($getReminder),
                    "Get Reminder"
                );
            }else {
                return $this->errorResponse("the given data is not found");
            }
        } catch (\Throwable $th) {
            return $this->errorResponse($ex->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */



    /**
     * @OA\post(
     * path="/uc/api/reminders/update/{id}",
     * operationId="updatereminders",
     * tags={"Reminders"},
     * summary="Update reminders Request",
     *   security={ {"Bearer": {} }},
     * description="Store reminders Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="description", type="text"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="reminders Updated Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="reminders Updated Successfully",
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
    public function update(ReminderRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $getReminder = HrmsReminder::find($id);

            if ($getReminder) {
                $getReminder->update($validated);

                return $this->successResponse(
                    new ReminderResource($getReminder),
                    "Reminder Updated Successfully"
                );
            }else {
                return $this->errorResponse("the given data is not found");
            }
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */



    /**
     * @OA\delete(
     * path="/uc/api/reminders/destroy/{id}",
     * operationId="deletereminders",
     * tags={"Reminders"},
     * summary="Delete reminders Request",
     * security={ {"Bearer": {} }},
     * description="Delete reminders Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Reminders deleted successfully",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="message", type="string", example="Reminders deleted successfully.")
     *     )
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="error", type="string", example="Reminder not found.")
     *     )
     * )
     * )
     */

    public function destroy($id)
    {
        try {
            $getReminder = HrmsReminder::find($id);

            if ($getReminder) {
                // Store reminder details before deletion (added line)
                $reminderDetails = [
                    'title' => $getReminder->title,
                    'description' => $getReminder->description,
                    'date' => $getReminder->date // Make sure this field exists
                ];
                $getReminder->delete();

                // Record deletion history (added line)
                $this->recordReminderDeletion($reminderDetails);

                return $this->successResponse(
                    [],
                    "Reminder Deleted Successfully"
                );
            }else {
                return $this->errorResponse("the given data is not found");
            }
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }

    // Private function added
    private function recordReminderDeletion($reminderDetails)
    {
        $user = auth('sanctum')->user();
        if (!$user) return;
        // Format the date for logging
        $date = isset($reminderDetails['date']) && $reminderDetails['date']
        ? date('Y-m-d', strtotime($reminderDetails['date']))
        : 'Not set';

        DB::table('update_system_setup_histories')->insert([
            'employee_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'updated_by' => $user->id,
            'notes' => 'Reminder deleted',
            'changed' => "Deleted reminder\n"
                    . "Title: " . ($reminderDetails['title'] ?? 'Not specified') . "\n"
                    . "Description: " . ($reminderDetails['description'] ?? 'Not specified') . "\n"
                    . "Date: " . $date,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }



}
