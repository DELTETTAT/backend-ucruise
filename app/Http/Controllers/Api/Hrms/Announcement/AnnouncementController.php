<?php

namespace App\Http\Controllers\Api\Hrms\Announcement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\HrmsAnnouncementRequest;
use App\Http\Resources\Announcement\AnnouncementCollection;
use App\Models\HrmsAnnouncement;
use App\Models\Reminder;
use App\Http\Resources\Announcement\AnnouncementResource;
use DB;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     * path="/uc/api/announcement/index",
     * operationId="getAnnouncement",
     * tags={"Hrms Announcement"},
     * summary="Get Announcement Request",
     *   security={ {"Bearer": {} }},
     * description="Get Announcement Request",
     *      @OA\Response(
     *          response=201,
     *          description="Announcement Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Announcement Get Successfully",
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

            $getAnnouncementList = HrmsAnnouncement::paginate(HrmsAnnouncement::PAGINATE);
            return $this->successResponse(
                new AnnouncementCollection($getAnnouncementList),
                'Announcemrnt list'
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

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Post(
     * path="/uc/api/announcement/store",
     * operationId="storannouncement",
     * tags={"Hrms Announcement"},
     * summary="Store announcement Request",
     *   security={ {"Bearer": {} }},
     * description="Store announcement Request",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="send_to", type="string"),
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="description", type="text" ),
     *              @OA\Property(property="date", type="date" ),
     *              @OA\Property(property="subject", type="text" ),
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
    public function store(HrmsAnnouncementRequest $request)
    {

        try {
            $data = $request->validated();
            $announcement = HrmsAnnouncement::create($request->all());

            // Record creation history (added line)
            $this->recordAnnouncementCreation($announcement);

            return $this->successResponse(
                new AnnouncementResource($announcement),
                'Announcement created Successfully'
            );
        } catch (\Throwable $th) {
            return $this->errorResponse(['message'=>$th->getMessage(),
            'line' => $th->getLine(),
            'file' => $th->getFile(),
            'trace' => $th->getTraceAsString(),
            'message' => $th->getMessage()

        ]);
        }
    }

    private function recordAnnouncementCreation($announcement)
    {
        $user = auth('sanctum')->user();
        if (!$user) return;

        DB::table('update_system_setup_histories')->insert([
            'employee_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'updated_by' => $user->id,
            'notes' => 'Announcement created',
            'changed' => sprintf(
                "Created new announcement\nTitle: %s\nDescription: %s\nDate: %s",
                $announcement->title,
                $announcement->description,
                $announcement->date ?? 'Not set'  // Directly use the string date
            ),
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

    /**
     * @OA\delete(
     * path="/uc/api/announcement/destroy/{id}",
     * operationId="announcement delete",
     * tags={"Hrms Announcement"},
     * summary="Delete Announcement Request",
     * security={ {"Bearer": {} }},
     * description="Delete Announcement Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response="200",
     *     description="Announcement Deleted Successfully",
     *     @OA\JsonContent()
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found"
     * )
     * )
     */

    public function destroy($id)
    {
        try {
            $hrmsAnnouncement = HrmsAnnouncement::find($id);
            if($hrmsAnnouncement){

                // Get announcement details before deletion (added line)
                $announcementDetails = [
                    'title' => $hrmsAnnouncement->title,
                    'description' => $hrmsAnnouncement->description,
                    'created_at' => $hrmsAnnouncement->created_at
                ];

                $hrmsAnnouncement->delete();

                // Record deletion history (added line)
                $this->recordAnnouncementDeletion($announcementDetails);

                return $this->successResponse([],'Announcement Deleted Sucessfully');
            }

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

    // Private function added
    private function recordAnnouncementDeletion($announcementDetails)
    {
        $user = auth('sanctum')->user();
        if (!$user) return;

        DB::table('update_system_setup_histories')->insert([
            'employee_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'updated_by' => $user->id,
            'notes' => 'Announcement deleted',
            'changed' => sprintf(
                "Deleted announcement\nTitle: %s\nDescription: %s\nCreated at: %s",
                $announcementDetails['title'],
                $announcementDetails['description'],
                $announcementDetails['created_at']->format('Y-m-d H:i:s')
            ),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
