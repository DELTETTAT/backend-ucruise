<?php

namespace App\Http\Controllers\Api\Hrms\Team;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeamManager;
use App\Models\HrmsTeam;
use App\Http\Requests\TeamManagerRequest;

class TeamManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    /**
     * @OA\Get(
     * path="/uc/api/hrms_team/team_manager/index",
     * operationId="get Team Manager data",
     * tags={"Hrms Teams"},
     * summary="getting Team Managerg data",
     *   security={ {"Bearer": {} }},
     * description="getting Team Manager data",
     *      @OA\Response(
     *          response=201,
     *          description="Team Manager data Get successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Team Manager data Get successfully",
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
            $listTeamManager = TeamManager::with('employees')->paginate(TeamManager::PAGINATE);

            return $this->successResponse(
                $listTeamManager,
                "Team Manager List"
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
     *     path="/uc/api/hrms_team/team_manager/store",
     *     operationId="hrmsteammanager",
     *     tags={"Hrms Teams"},
     *     summary="Submit hrms team manager data",
     *     security={{"Bearer": {}}},
     *     description="Store Team Manager.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", description="Enter name"),
     *                 @OA\Property(property="description", type="string", description="Description of the team manager"),
     *                 @OA\Property(property="location", type="string", description="Manager's location"),
     *                 @OA\Property(property="latitude", type="string", description="Manager's latitude"),
     *                 @OA\Property(property="longitude", type="string", description="Manager's longitude"),
     *                 @OA\Property(
     *                     property="employee_id[]",
     *                     type="array",
     *                     @OA\Items(type="integer"),
     *                     description="Array of employee IDs"
     *                 ),
     *                 @OA\Property(
     *                   property="team_attendance_access",
     *                   type="integer",
     *                  enum={0, 1},
     *                 description="1 => Permission, 0 => No permission"
     *              )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Hrms Team Manager created successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Hrms Team Manager created successfully."),
     *             @OA\Property(property="template", type="object", description="Details of the created Hrms Team Manager.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Validation error."),
     *             @OA\Property(property="details", type="object", description="Validation errors.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid request.")
     *         )
     *     )
     * )
     */
    public function store(TeamManagerRequest $request)
    {
        try {

           $validatedData = $request->validated();
           $employeeIds = $request->employee_id;

            if (is_array($employeeIds)) {
                if (count($employeeIds) === 1 && is_string($employeeIds[0]) && str_contains($employeeIds[0], ',')) {
                    $employeeIds = explode(',', $employeeIds[0]);
                }
            } else {
                $employeeIds = explode(',', $employeeIds);
            }

            $employeeIds = array_map('intval', $employeeIds);


            $teamManager = TeamManager::create($validatedData);

            // Attach multiple employees
            if (isset($request->employee_id)) {
                 $pivotData = [];
                 foreach ($employeeIds as $id) {
                    $pivotData[$id] = [
                        'team_attendance_access' => $validatedData['team_attendance_access'] ?? null
                    ];
                 }

                 $teamManager->employees()->attach($pivotData);
            }


            return $this->successResponse(
                [],
                "Team Manager Created Successfully"
            );

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


    /**
     * @OA\get(
     * path="/uc/api/hrms_team/team_manager/edit/{id}",
     * operationId="edit team manager",
     * tags={"Hrms Teams"},
     * summary="Edit Team Manager Request",
     *   security={ {"Bearer": {} }},
     * description="Edit Team Manager Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Team Manager Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Team Manager Edited Successfully",
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
             $getTeamManager = TeamManager::with('employees')->find($id);

             if (!$getTeamManager) {
                return $this->errorResponse("The given data is not found");
             }

             return $this->successResponse(
                $getTeamManager,
                "Team Manager Data"
             );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMesssage());
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
     * @OA\Post(
     *     path="/uc/api/hrms_team/team_manager/update/{id}",
     *     operationId="updatedhrmsteammanager",
     *     tags={"Hrms Teams"},
     *     summary="Updated hrms team manager data",
     *     security={{"Bearer": {}}},
     *     description="Updated Team Manager.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", description="Enter name"),
     *                 @OA\Property(property="description", type="string", description="Description of the team manager"),
     *                 @OA\Property(property="location", type="string", description="Manager's location"),
     *                 @OA\Property(property="latitude", type="string", description="Manager's latitude"),
     *                 @OA\Property(property="longitude", type="string", description="Manager's longitude"),
     *                 @OA\Property(
     *                     property="employee_id",
     *                     type="array",
     *                     @OA\Items(type="integer"),
     *                     description="Array of employee IDs"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Hrms Team Manager Updated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Hrms Team Manager Updated successfully."),
     *             @OA\Property(property="template", type="object", description="Details of the Updated Hrms Team Manager.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Validation error."),
     *             @OA\Property(property="details", type="object", description="Validation errors.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid request.")
     *         )
     *     )
     * )
     */

    public function update(TeamManagerRequest $request, $id)
    {
        try {

            $validatedData = $request->validated();

            $getTeamManager = TeamManager::find($id);

            if (!$getTeamManager) {
                return $this->errorResponse("The given data is not found");
            }

            // Extract and sanitize employee_id
            $employeeIds = $validatedData['employee_id'] ?? [];

            if (is_array($employeeIds)) {
                if (count($employeeIds) === 1 && is_string($employeeIds[0]) && str_contains($employeeIds[0], ',')) {
                    $employeeIds = explode(',', $employeeIds[0]);
                }
            } else {
                $employeeIds = explode(',', $employeeIds);
            }

            $employeeIds = array_map('intval', $employeeIds);

            // Remove employee_id from validated data before update
            unset($validatedData['employee_id']);

            // Update team manager
            $getTeamManager->update($validatedData);

            // Sync employees (replace old with new)
            $getTeamManager->employees()->sync($employeeIds);

            return $this->successResponse(
                $getTeamManager,
                "Team Manager Updated Successfully"
            );

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage()); // Typo fix: getMesssage -> getMessage
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    /**
     * @OA\Delete(
     *     path="/uc/api/hrms_team/team_manager/destroy/{id}",
     *     operationId="deleteteam_manager",
     *     tags={"Hrms Teams"},
     *     summary="Delete Team Manager Request",
     *     security={ {"Bearer": {} }},
     *     description="Delete Team Manager Request",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Team Manager deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Team Manager deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource Not Found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Team Manager not found.")
     *         )
     *     )
     * )
     */


    public function destroy($id)
    {
        try {

            $getTeamManager = TeamManager::find($id);

            if ($getTeamManager) {

                $getTeamManager->delete();

                $relatedTeams =  HrmsTeam::where('team_manager_id', $id)->get();

                foreach ($relatedTeams as $key => $Team) {
                    $Team->delete();
                }

                return $this->successResponse(
                    [],
                    "Team Manager Deleted Successfully"
                );
            }else {
                return $this->errorResponse("The given data is not found");
            }
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }
}
