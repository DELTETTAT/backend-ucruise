<?php

namespace App\Http\Controllers\Api\Hrms\Team;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\HrmsTeamRequest;
use App\Http\Resources\Hrms\Quiz\QuizLevel\QuizLevelCollection;
use App\Http\Resources\HrmsTeam\HrmsTeamResource;
use App\Http\Resources\HrmsTeam\HrmsTeamCollection;
use App\Models\HrmsTeam;
use App\Models\HrmsTeamMember;
use App\Models\User;
use App\Models\SubUser;
use App\Models\TeamManager;
use App\Models\EmployeesUnderOfManager;
use App\Models\EmployeeTeamManager;
use DB;
class HrmsTeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
        /**
     * @OA\Get(
     * path="/uc/api/hrms_team/index",
     * operationId="gethrmsteam",
     * tags={"Hrms Teams"},
     * summary="Get Hrms Team",
     *   security={ {"Bearer": {} }},
     * description="Get Hrms Team",
     *      @OA\Response(
     *          response=201,
     *          description="Hrms Teams Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Hrms Teams Get Successfully",
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
           // $getTeamList = HrmsTeam::paginate(HrmsTeam::PAGINATE);

           $user_id = auth('sanctum')->user()->id;
           $admin = 0;
           $is_manager = 0;

            // if view role admin view
           $getTeamMembers = SubUser::with('hrmsroles.viewrole')->find($user_id);
            if ($getTeamMembers) {
                $role_view = $getTeamMembers->hrmsroles[0]->viewrole->name;
               if ($role_view == 'Admin View') {
                   $admin = 1;
               }elseif ($role_view == 'Manager View') {   // manager view
                   $is_manager = 1;
               }elseif ($role_view == 'Manager Not Attendance View') {   // Manager Attendance View
                $is_manager = 1;
            }
           }
            ///  if Main Admin
           $auth_role = DB::table('role_user')->where('user_id',$user_id)->first();
           $employeeRole = DB::table('roles')->find($auth_role->role_id);
           if ($employeeRole->name == "admin") {
                $admin = 1;
           }

           if ($admin == 0) {
                if ($is_manager == 1 ) {
                    $team_manager = EmployeeTeamManager::where('employee_id', $user_id)->first();
                    if ($team_manager) {
                        $team_manager_id = $team_manager->team_manager_id;
                    }
                }else {
                    $getManagerList = TeamManager::with(['employees','teams.teamLeader', 'teams.teamMembers.user'])->get();
                     //$team_manager_id = null;
                   // if($getManagerList){
                        foreach ($getManagerList as $key => $manager) {
                        foreach ($manager->teams as $key => $team) {
                            if (optional($team->team_leader == $user_id)) {
                                $team_manager_id =  $manager->id;
                                $team__id =   $team->id;
                                break 2;
                            }else {

                                foreach ($team->teamMembers as $key => $member) {

                                      if (optional($member->user->id == $user_id)) {
                                            $team_manager_id =  $manager->id;
                                            $team__id =   $team->id;
                                            break 2;
                                      }
                                }
                            }


                        }
                    }

                   // }
                    //return response()->json(['status' => false, 'message' => 'Not Found Data']);
                }
           }


            if ($admin == 1) {
                $getTeamList = TeamManager::with(['employees','teams.teamLeader', 'teams.teamMembers.user'])->get();
            }elseif ($is_manager == 1) {
                $getTeamList = TeamManager::with(['employees','teams.teamLeader', 'teams.teamMembers.user'])->where('id', $team_manager_id)->get();
            }else {
                $getTeamList = TeamManager::with(['employees','teams.teamLeader', 'teams.teamMembers.user'])->where('id', $team_manager_id)->get();

            }


         //  ***   employees_without_team  *** //
        foreach ($getTeamList as $manager) {
            $teamMemberIds = collect();
            $employeeIds = collect($manager->employees)->pluck('id')->toArray();
            $teamMemberIds = collect($employeeIds);

            $allEmployees = EmployeesUnderOfManager::with('employee')
                ->where('manager_id', $manager->id)
                ->get()
                ->pluck('employee')
                ->filter();

            // 2. All employee IDs in any team under this manager
            foreach ($manager->teams as $team) {
                $teamLeaderID = (int) $team->team_leader;
                $teamMemberIds->push($teamLeaderID);
                foreach ($team->teamMembers as $member) {
                    if ($member->user) {
                        $teamMemberIds->push($member->user->id);
                    }
                }
            }

            // 3. Employees under manager not in any team
            $employeesWithoutTeam = $allEmployees->filter(function($employee) use ($teamMemberIds) {
                return !$teamMemberIds->contains($employee->id);
            })->values();

            // 4. Attach to manager object
            $manager->employees_without_team = $employeesWithoutTeam;
        }


            return $this->successResponse(
               // new HrmsTeamCollection($getTeamList),
               $getTeamList,
                'Hrms Team list'
            );
        } catch (Exception $ex) {
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
     *     path="/uc/api/hrms_team/store",
     *     operationId="hrmsteams",
     *     tags={"Hrms Teams"},
     *     summary="Submit hrms team data",
     *     security={{"Bearer": {}}},
     *     description="Endpoint to process Quiz Level data.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="team_name", type="string", description="Team name "),
     *                 @OA\Property(property="description", type="text", description="Description of the team"),
     *                 @OA\Property(property="team_manager_id", type="integer", description="please define Team Manager ID"),
     *                 @OA\Property(property="team_leader", type="integer", description="please define Team Manager under employee ID"),
     *              @OA\Property(
     *                     property="members",
     *                     type="array",
     *                     @OA\Items(type="integer"),
     *                     description="Array of user IDs who are part of the team"
     *                 ),
     *                 @OA\Property(property="status", type="integer", description="Status of the team (1 for active, 0 for inactive)."),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Hrms Team created successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Hrms Team created successfully."),
     *             @OA\Property(property="template", type="object", description="Details of the created Hrms Team.")
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
    public function store(HrmsTeamRequest $request)
    {
        try {

            $data = $request->validated();

           if (!empty($data['members'])) {
                if (!is_array($data['members'])) {
                    // If it's not an array, try to handle it
                    $data['members'] = explode(',', $data['members']);
                }

                $data['members'] = array_filter(array_map('trim', $data['members']));

                if (empty($data['members']) || (count($data['members']) === 1 && in_array($data['members'][0], ["0", ""]))) {
                    return response()->json(['message' => 'Team member not found, at least select one.'], 404);
                }

                $data['members'] = json_encode($data['members'] ?? []);
           }

            $Team = HrmsTeam::create($data);

            // Create a new member instance
            if (!empty($data['members'])) {
                $data['members'] = json_decode($data['members'] ?? '[]', true);
            }

            if(!empty($data['members'])){
                foreach ($data['members'] as $members) {
                    HrmsTeamMember::create([
                        'member_id' =>$members,
                        'hrms_team_id' => $Team->id
                    ]);
                }
            }

            // Store team members and team leader in employees_under_of_managers table
            if (isset($data['team_manager_id'])) {
                $manager_id = $data['team_manager_id'];
                
                // Store team leader under manager if team leader exists
                if (isset($data['team_leader']) && !empty($data['team_leader'])) {
                    EmployeesUnderOfManager::updateOrCreate(
                        ['manager_id' => $manager_id, 'employee_id' => $data['team_leader']],
                        ['manager_id' => $manager_id, 'employee_id' => $data['team_leader']]
                    );
                }
                
                // Store all team members under manager
                if (!empty($data['members'])) {
                    foreach ($data['members'] as $member_id) {
                        EmployeesUnderOfManager::updateOrCreate(
                            ['manager_id' => $manager_id, 'employee_id' => $member_id],
                            ['manager_id' => $manager_id, 'employee_id' => $member_id]
                        );
                    }
                }
            }

            return $this->successResponse( $Team, 'Hrms Team created successfully');

        } catch (Exception $e) {

            return response()->json([
                'message' => 'Failed to create Team.',
                'error' => $e->getMessage(),
            ], 500);
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
     * @OA\Get(
     * path="/uc/api/hrms_team/edit/{id}",
     * operationId="edithrmsteam",
     * tags={"Hrms Teams"},
     * summary="Edit Hrms Team Request",
     * security={ {"Bearer": {} }},
     * description="Edit Hrms Team Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Hrms Team Edit Successfully",
     *     @OA\JsonContent()
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found"
     * )
     * )
     */
    public function edit($TeamId)
    {
        try {
           // $getTeamDetails = HrmsTeam::find($TeamId);
           $getTeamDetails = HrmsTeam::with('teamMembers.user')->find($TeamId);
            if (isset($getTeamDetails)) {
                return $this->successResponse(
                   // new HrmsTeamResource($getTeamDetails),
                     $getTeamDetails,
                    'Hrms Team details retrieved successfully'
                );
            } else {
                return $this->validationErrorResponse('the given data is not found');
            }
        } catch (Exception $ex) {
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
         * @OA\Post(
         *     path="/uc/api/hrms_team/update/{id}",
         *     operationId="updatehrmsteam",
         *     tags={"Hrms Teams"},
         *     summary="Update hrms team data",
         *     security={{"Bearer": {}}},
         *     description="Endpoint to process hrms team data.",
         *       @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         @OA\Schema(type="integer")
         *       ),
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\MediaType(
         *             mediaType="multipart/form-data",
         *             @OA\Schema(
         *                 type="object",
         *                 @OA\Property(property="team_name", type="string", description="Team name "),
         *                 @OA\Property(property="description", type="text", description="Description of the team"),
         *                 @OA\Property(property="team_leader", type="integer", description="Define team leader Id"),
         *                 @OA\Property(property="team_manager_id", type="integer", description="please define Team Manager ID"),
         *                 @OA\Property(
         *                     property="members",
         *                     type="array",
         *                     @OA\Items(type="integer"),
         *                     description="Array of user IDs who are part of the team"
         *                 ),
         *                 @OA\Property(property="status", type="integer", description="Status of the team (1 for active, 0 for inactive)."),
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=201,
         *         description="quiz level updated successfully.",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="message", type="string", example="quiz level update successfully."),
         *             @OA\Property(property="template", type="object", description="Details of the updated quiz level."),
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

         public function update(HrmsTeamRequest $request, $TeamId)
         {
             try {

                $findTeamDetail = HrmsTeam::find($TeamId);

                 if (!empty($findTeamDetail)) {
                     $validated = $request->validated();
                     if (!empty($validated['members'])) {
                            if (!is_array($validated['members'])) {
                                $validated['members'] = explode(',', $validated['members']);
                            }
                     }

                      $updated = $findTeamDetail->update($validated);

                     if($updated){
                        HrmsTeamMember::where('hrms_team_id', $findTeamDetail->id)->delete();
                        // if (!empty($findTeamDetail)) {
                        //     $data['members'] = json_decode($validated['members'] ?? '[]', true);
                        // }

                        if(!empty($validated['members'])){
                            foreach ($validated['members'] as $members) {
                                HrmsTeamMember::create([
                                    'member_id' =>$members,
                                    'hrms_team_id' => $findTeamDetail->id
                                ]);
                            }
                        }
                        // Update employees_under_of_managers table
                        if (isset($validated['team_manager_id'])) {
                            $manager_id = $validated['team_manager_id'];
                            
                            // Remove existing relationships for this team's members and leader
                            $existingMembers = HrmsTeamMember::where('hrms_team_id', $findTeamDetail->id)->pluck('member_id')->toArray();
                            if ($findTeamDetail->team_leader) {
                                $existingMembers[] = $findTeamDetail->team_leader;
                            }
                            
                            if (!empty($existingMembers)) {
                                EmployeesUnderOfManager::where('manager_id', $findTeamDetail->team_manager_id)
                                    ->whereIn('employee_id', $existingMembers)
                                    ->delete();
                            }
                            
                            // Store team leader under manager if team leader exists
                            if (isset($validated['team_leader']) && !empty($validated['team_leader'])) {
                                EmployeesUnderOfManager::updateOrCreate(
                                    ['manager_id' => $manager_id, 'employee_id' => $validated['team_leader']],
                                    ['manager_id' => $manager_id, 'employee_id' => $validated['team_leader']]
                                );
                            }
                            
                            // Store all team members under manager
                            if (!empty($validated['members'])) {
                                foreach ($validated['members'] as $member_id) {
                                    EmployeesUnderOfManager::updateOrCreate(
                                        ['manager_id' => $manager_id, 'employee_id' => $member_id],
                                        ['manager_id' => $manager_id, 'employee_id' => $member_id]
                                    );
                                }
                            }
                        }
                     }

                     return $this->successResponse(
                         new HrmsTeamResource($findTeamDetail),
                         'Hrms Team updated Successfully'
                     );

                 } else {
                     return $this->validationErrorResponse("the given data is not found");
                 }
             } catch (Exception $ex) {
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
     * @OA\Delete(
     * path="/uc/api/hrms_team/destroy/{id}",
     * operationId="deletehrmsteam",
     * tags={"Hrms Teams"},
     * summary="Delete Hrms Team Request",
     * security={ {"Bearer": {} }},
     * description="Delete Hrms Team Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Team Deleted Successfully",
     *     @OA\JsonContent()
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found"
     * )
     * )
     */
    public function destroy($TeamId)
    {
        try {
            $getTeamDetails = HrmsTeam::find($TeamId);
            if (isset($getTeamDetails)) {
                $getTeamDetails->delete();
                return $this->successResponse(
                    [],
                    'Team Removed Sucessfully'
                );
            } else {
                return $this->validationErrorResponse('the given data is not found');
            }
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }


/**
 * @OA\Get(
 * path="/uc/api/hrms_team/all_teams",
 * operationId="getAllHrmsTeams",
 * tags={"Hrms Teams"},
 * summary="Get All Hrms Teams",
 * security={ {"Bearer": {} }},
 * description="Get All Hrms Teams",
 *      @OA\Response(
 *          response=201,
 *          description="Hrms Teams Retrieved Successfully",
 *          @OA\JsonContent()
 *       ),
 *      @OA\Response(
 *          response=200,
 *          description="Hrms Teams Retrieved Successfully",
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
    public function all_teams()
    {
        try {
            $getAllTeams = HrmsTeam::all();

            //return $this->successResponse($getAllTeams,'All Hrms Teams');

            $getTeamList = HrmsTeam::with('teamMembers.user')->paginate(HrmsTeam::PAGINATE);
            return $this->successResponse( new HrmsTeamCollection($getTeamList), 'All Hrms Team list');

        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }


    /**
     * @OA\Post(
     * path="/uc/api/hrms_team/staffFilter",
     * operationId="staffFilter",
     * tags={"Hrms Teams"},
     * summary="List staff",
     *   security={ {"Bearer": {} }},
     * description="List staff",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               @OA\Property(property="search", type="text"),
     *               @OA\Property(property="archive_unarchive", type="text", description="0:unarchived_staff, 1:archived_staff"),
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="staff listed successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Staff listed successfully",
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

     public function staffFilter(Request $request)
     {
        try {

            $search = $request->input('search');
            $query = User::select('id', 'first_name', 'last_name', 'email', 'phone', 'employement_type', 'profile_image')
            ->whereNotNull('employement_type');

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', '%' . $search . '%')
                      ->orWhere('last_name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            $data = $query->get();

              return response()->json([
                'success' => true,
                'data' => $data,
                'employee_image_url' => url('public/images'),
                'message' => 'Employees listed successfully'
            ], 200);

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }

     }




     /**
     * @OA\Get(
     * path="/uc/api/hrms_team/managerList",
     * operationId="getgetManagers",
     * tags={"Hrms Teams"},
     * summary="Get All Managers Teams",
     * security={ {"Bearer": {} }},
     * description="Get All Managers Teams",
     *      @OA\Response(
     *          response=201,
     *          description="Managers Teams Retrieved Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Managers Teams Retrieved Successfully",
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


     public function managerList(){
           try {

            $getManagers = SubUser::whereHas('hrmsroles.viewrole', function ($query) {
                $query->whereIn('name', ['Manager View', 'Admin View']);
            })->with(['hrmsroles.viewrole' => function ($query) {
                $query->whereIn('name', ['Manager View', 'Admin View']);
            }])->get();

            return $this->successResponse(
                $getManagers,
                "Manager List"
            );

           } catch (\Throwable $th) {
                return $this->errorResponse($th->getMessage());
           }
     }


    /**
     * @OA\Get(
     * path="/uc/api/hrms_team/teamLeaderList",
     * operationId="getteamLeader",
     * tags={"Hrms Teams"},
     * summary="Get All Team Leader Teams",
     * security={ {"Bearer": {} }},
     * description="Get All Managers Teams",
     *      @OA\Response(
     *          response=201,
     *          description="Team Leader Teams Retrieved Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Team Leader Teams Retrieved Successfully",
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


     public function teamLeaderList(){
        try {

        //$assignedLeaderinTeams = HrmsTeam::pluck('team_manager_id')->toArray();
         $getTeamLeader = SubUser::whereHas('hrmsroles.viewrole', function ($query) {
             $query->where('name', 'Team Leader View');
         })->with(['hrmsroles.viewrole' => function ($query) {
             $query->where('name', 'Team Leader View');
         }])->get();

         return $this->successResponse(
             $getTeamLeader,
             "Team Leader List"
         );

        } catch (\Throwable $th) {
             return $this->errorResponse($th->getMessage());
        }
  }


    /**
     * @OA\Get(
     * path="/uc/api/hrms_team/teamMemberList",
     * operationId="teamMemberList",
     * tags={"Hrms Teams"},
     * summary="Get All Team Member Teams",
     * security={ {"Bearer": {} }},
     * description="Get All Team Members Teams",
     *      @OA\Response(
     *          response=201,
     *          description="Team Team Members Retrieved Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Team Team Members Retrieved Successfully",
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


     public function teamMemberList(){
        try {

        // $assignedMemberinTeams = HrmsTeamMember::pluck('member_id')->toArray();
         $getTeamMembers = SubUser::whereHas('hrmsroles.viewrole', function ($query) {
             $query->where('name', 'Employee View');
         })
        // ->whereNotIn('id', $assignedMemberinTeams)
         ->with(['hrmsroles.viewrole' => function ($query) {
             $query->where('name', 'Employee View');
         }])->get();

         return $this->successResponse(
             $getTeamMembers,
             "Member List"
         );

        } catch (\Throwable $th) {
             return $this->errorResponse($th->getMessage());
        }
   }



    /**
     * @OA\Post(
     * path="/uc/api/hrms_team/team_list_accourding_manager",
     * operationId="getTeamsaccourdingtoManager",
     * tags={"Hrms Teams"},
     * summary="Get All Hrms Teams",
     * security={ {"Bearer": {} }},
     * description="Get All Hrms Teams",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               @OA\Property(property="manager_id", type="integer"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Teams List Accounding To Manager Retrieved Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Teams List Accounding To Manager Retrieved Successfully",
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
        public function teamListAccourdingManager(Request $request)
        {
            try {

                if (isset($request->manager_id)) {
                    $getAllTeams = HrmsTeam::select('id','team_name')->where('team_manager_id',$request->manager_id)->get();
                }else {
                    $getAllTeams = HrmsTeam::select('id','team_name')->get();
                }


                if ($getAllTeams) {
                    return response()->json([
                        'status' => true,
                        'data' => $getAllTeams,
                        'message' => "Team List Accourding to Manager",
                    ],200);
                }else {
                    return response()->json([
                        'status' => false,
                        'message' => "Not Found Data",
                    ],404);
                }


            } catch (Exception $ex) {
                return $this->errorResponse($ex->getMessage());
            }
        }





     /**
     * @OA\Post(
     * path="/uc/api/hrms_team/add_manager_member",
     * operationId="addManagerMember",
     * tags={"Hrms Teams"},
     * summary="Add Manager Member",
     * security={ {"Bearer": {} }},
     * description="Add Manager Member",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               @OA\Property(property="manager_id", type="integer"),
     *               @OA\Property(property="employee_id", type="integer"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Add MAnager Members Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Add MAnager Members Successfully",
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



    public function addManagerMember(Request $request)
    {
        try {

            $manager_id = $request->manager_id;
            $employee_id = $request->employee_id;

            EmployeesUnderOfManager::updateOrCreate(
                ['manager_id' => $manager_id, 'employee_id' => $employee_id],
                ['manager_id' => $manager_id, 'employee_id' => $employee_id]
            );

            return response()->json([
                'status' => true,
                'message' => "Manager Member Added Successfully",
            ], 200);
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }










}
