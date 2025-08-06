<?php

namespace App\Http\Controllers\Api\Performances;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\PerformanceRequest;
use App\Http\Resources\Performance\TasksResource;
use App\Http\Resources\Performance\TasksCollection;
use App\Models\HrmsTask;
use App\Models\HrmsSubTask;
use App\Models\HrmsProject;
use App\Models\HrmsTeam;
use Carbon\Carbon;


class PerformanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Post(
     * path="/uc/api/performances/index",
     * operationId="task",
     * tags={"Performances"},
     * summary="Get Performances Request",
     *   security={ {"Bearer": {} }},
     *    description="Get Performances Request",
     *      @OA\Response(
     *          response=201,
     *          description="Performances Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Performances Get Successfully",
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

    // public function index(Request $request)
    // {

    //      try {

    //         $this->data['hrms_team'] = HrmsTeam::with('teamTask')->get();

    //       // $this->data['hrms_team_task'] = HrmsTeam::with('teamTask','teamLeader','teamMembers.user','teamManager')->get();
    //         //$startDate = $request->month ? Carbon::parse($request->month)->startOfMonth()->format('Y-m-d\TH:i:s') : now()->startOfMonth()->format('Y-m-d\TH:i:s');
    //         //$endDate = $request->month ? Carbon::parse($request->month)->endOfMonth()->format('Y-m-d\TH:i:s') : now()->endOfMonth()->format('Y-m-d\TH:i:s');

    //         // return $tasks = HrmsTask::with('project.admin', 'assignee', 'subTasks','teamLeader','teamManager')
    //         //     ->orderBy('id', 'desc')
    //         //     ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
    //         //         return $query->whereBetween('start_date', [$startDate, $endDate]);
    //         //     })
    //         //     ->paginate(HrmsTask::PAGINATE);

    //         // foreach ($tasks as $task) {
    //         //     $task->assigned_users = $task->assignedUsers();
    //         // }
    //        // return $this->successResponse(new TasksCollection($tasks),"Task List");


    //     return response()->json([
    //         'status'  => true,
    //         'data'=> $this->data,
    //         'message' => 'Task List',
    //     ]);
    //     } catch (\Exception $ex) {
    //         return $this->errorResponse($ex->getMessage());
    //     }
    // }

    public function index(){

    }





    /**
     * @OA\get(
     * path="/uc/api/performances/teamProjects/{id}",
     * operationId="teamProjects",
     * tags={"Performances"},
     * summary="Team Projects",
     *   security={ {"Bearer": {} }},
     * description="Team Projects",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Performances Team Projects",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Performances Team Projects",
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

    //  public function teamProjects($id){

    //     try {
    //     $this->data['hrms_team_task'] = HrmsTask::with('project','teamLeader','teamManager','subTasks','teamMembers.user')
    //     ->where('hrms_team_id', $id)
    //     ->get();

    //         return response()->json([
    //             'status'  => true,
    //             'data'=> $this->data,
    //             'message' => 'Task List',
    //         ]);

    //         } catch (\Exception $ex) {
    //             return $this->errorResponse($ex->getMessage());
    //         }

    //  }

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
     * path="/uc/api/performances/store",
     * operationId="performances",
     * tags={"Performances"},
     * summary="Store Performances",
     *   security={ {"Bearer": {} }},
     * description="Store Performances",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="assignee_id", type="integer"),
     *              @OA\Property(property="assigned_id", type="string"),
     *              @OA\Property(property="start_date", type="string"),
     *              @OA\Property(property="end_date", type="string"),
     *              @OA\Property(property="project_title", type="text"),
     *              @OA\Property(property="priority", type="string", description="'0 => Low, 1 => Medium, 2 => High'"),
     *              @OA\Property(property="description", type="text"),
     *              @OA\Property(property="sub_task", type="array",
     *                   @OA\Items(type="string")
     *                ),
     *             @OA\Property(property="team_manager_id", type="string"),
     *             @OA\Property(property="team_leader", type="string"),
     *             @OA\Property(property="hrms_project_id", type="string"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Job Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Job Created Successfully",
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

    // public function store(PerformanceRequest $request)
    // {

    //     try {

    //         $validatedData = $request->validated();

    //         if (isset($validatedData['sub_task'])) {
    //             if (!is_array($validatedData['sub_task'])) {
    //                 $validatedData['sub_task'] = explode(',', $validatedData['sub_task']);
    //             }
    //         }

    //         $hrmstask  = HrmsTask::create($validatedData);
    //         // Create a new SubTask instance
    //         if(!empty($validatedData['sub_task'])){
    //             foreach ($validatedData['sub_task'] as $task) {
    //                 HrmsSubTask::create([
    //                     'name' =>$task,
    //                     'task_id' => $hrmstask->id
    //                 ]);
    //             }
    //         }

    //         return $this->successResponse( [],'Performance Created Successfully');

    //     } catch (\Exception $ex) {
    //         return $this->errorResponse($ex->getMessage());
    //     }

    // }

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
     * path="/uc/api/performances/edit/{id}",
     * operationId="edit_performances",
     * tags={"Performances"},
     * summary="Edit performances",
     *   security={ {"Bearer": {} }},
     * description="Edit performances",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Performances Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Performances Edited Successfully",
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


    // public function edit($id)
    // {
    //     try {

    //         $getPerformanceDetails = HrmsTask::with('assignee', 'subTasks')->find($id);
    //         $getPerformanceDetails->assigned_users = $getPerformanceDetails->assignedUsers();

    //         if ($getPerformanceDetails) {
    //             return $this->successResponse(new TasksResource($getPerformanceDetails),"Task Details");
    //         }else {
    //             return $this->validationErrorResponse("the given data is not found");
    //         }
    //     } catch (\Exception $ex) {
    //         return $this->errroResponse($ex->getMessage());
    //     }
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */



    /**
     * @OA\Post(
     * path="/uc/api/performances/update/{id}",
     * operationId="Update performances",
     * tags={"Performances"},
     * summary="Store Performances",
     *   security={ {"Bearer": {} }},
     * description="Store Performances",
     *      @OA\Parameter(name="id", in="path", required=true,
     *      @OA\Schema(type="integer")
     *     ),
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="assignee_id", type="integer"),
     *              @OA\Property(property="assigned_id", type="string"),
     *              @OA\Property(property="start_date", type="string"),
     *              @OA\Property(property="end_date", type="string"),
     *              @OA\Property(property="project_title", type="text"),
     *              @OA\Property(property="priority", type="string", description="'0 => Low, 1 => Medium, 2 => High'"),
     *              @OA\Property(property="status", type="string", description="'0 => To Do, 1 => In Progress, 2 => Completed'"),
     *              @OA\Property(property="description", type="text"),
     *              @OA\Property(property="sub_task", type="array",
     *                   @OA\Items(type="string")
     *                ),
     *             @OA\Property(property="team_manager_id", type="string"),
     *             @OA\Property(property="team_leader", type="string"),
     *             @OA\Property(property="hrms_project_id", type="string"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Job Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Job Created Successfully",
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




    // public function update(PerformanceRequest $request, $id)
    // {

    //    try {

    //         $hrmstask = HrmsTask::find($id);

    //         if($hrmstask){
    //             $validatedData = $request->validated();

    //             if (isset($validatedData['sub_task'])) {
    //                 if (!is_array($validatedData['sub_task'])) {
    //                     $validatedData['sub_task'] = explode(',', $validatedData['sub_task']);
    //                 }
    //             }

    //             $hrmstask->update($validatedData);

    //             // Delete existing sub-tasks
    //             HrmsSubTask::where('task_id', $hrmstask->id)->delete();

    //             // Create a new SubTask instance
    //             if(!empty($validatedData['sub_task'])){
    //                 foreach ($validatedData['sub_task'] as $task) {
    //                     HrmsSubTask::create([
    //                         'name' =>$task,
    //                         'task_id' => $hrmstask->id
    //                     ]);
    //                 }
    //             }

    //             return $this->successResponse( new TasksResource($hrmstask), "Task Updated Successfully");

    //         }else{
    //             return $this->validationErrorResponse("the given data is not found");
    //         }

    //    } catch (\Throwable $th) {

    //         return $this->errorResponse($th->getMessage());
    //    }
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\delete(
     * path="/uc/api/performances/destroy/{id}",
     * operationId="performances delete",
     * tags={"Performances"},
     * summary="Delete task Request",
     * security={ {"Bearer": {} }},
     * description="Delete Task Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Task Deleted Successfully",
     *     @OA\JsonContent()
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found"
     * )
     * )
     */

    // public function destroy($id)
    // {

    //     try {
    //         $hrmsTask = HrmsTask::find($id);
    //         if($hrmsTask){
    //             $hrmsTask->delete();
    //             return $this->successResponse([],'Task Removed Sucessfully');
    //         }

    //     } catch (\Throwable $th) {
    //         return $this->errorResponse($th->getMessage());
    //     }
    // }


    /**
     * @OA\Post(
     * path="/uc/api/performances/projectCreate",
     * operationId="projectCreate",
     * tags={"Performances"},
     * summary="projectCreate Performances",
     *   security={ {"Bearer": {} }},
     * description="projectCreate Performances",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="project_title", type="text"),
     *              @OA\Property(property="description", type="text"),
     *              @OA\Property(property="priority", type="string", description="'0 => Low, 1 => Medium, 2 => High'"),
     *              @OA\Property(property="start_date", type="string"),
     *              @OA\Property(property="end_date", type="string"),
     *              @OA\Property(property="status", type="string", description="'0 => To Do, 1 => In Progress, 2 => Completed'"),
     *              @OA\Property(property="completed", type="string"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Job Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Job Created Successfully",
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


    //  public function projectCreate(Request $request){


    //     try {

    //         $validatedData = $request->validate([
    //             'project_title' => 'nullable|string|max:65535',
    //             'description'   => 'nullable|string',
    //             'status'        => 'nullable|in:0,1,2',
    //             'priority'      => 'nullable|in:0,1,2',
    //             'completed'     => 'nullable|string|max:255',
    //             'start_date'    => 'nullable|date',
    //             'end_date'      => 'nullable|date|after_or_equal:start_date',
    //         ]);
    //         $user_id = auth('sanctum')->user()->id;
    //         $validatedData['admin_id'] =  $user_id;
    //         $project = HrmsProject::create($validatedData);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Project created successfully.',
    //             'data'    => $project
    //         ]);

    //     } catch (\Throwable $th) {
    //         return $this->errorResponse($th->getMessage());
    //     }

    // }


    /**
     * @OA\delete(
     * path="/uc/api/performances/projectDestroy/{id}",
     * operationId="performances projectDestroy",
     * tags={"Performances"},
     * summary="project Destroy Request",
     * security={ {"Bearer": {} }},
     * description="Delete Project Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Project Deleted Successfully",
     *     @OA\JsonContent()
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found"
     * )
     * )
     */

    //  public function projectDestroy($id){

    //     try {
    //         $hrmsProject = HrmsProject::find($id);
    //         if($hrmsProject){
    //             $hrmsProject->delete();
    //             return $this->successResponse([],'Project Removed Sucessfully');
    //         }

    //     } catch (\Throwable $th) {
    //         return $this->errorResponse($th->getMessage());
    //     }

    //  }



    /**
     * @OA\get(
     * path="/uc/api/performances/editProject/{id}",
     * operationId="editProject",
     * tags={"Performances"},
     * summary="Edit Projects",
     *   security={ {"Bearer": {} }},
     * description="Edit Project",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Project Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Project Edited Successfully",
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

    //  public function editProject($id){

    //     try {

    //         $editproject = HrmsProject::find($id);

    //         if ($editproject) {
    //            // return $this->successResponse(new TasksResource($editproject),"Project Details");
    //             return $this->successResponse($editproject,'Project Details');
    //         }else {
    //             return $this->validationErrorResponse("the given data is not found");
    //         }
    //     } catch (\Exception $ex) {
    //         return $this->errroResponse($ex->getMessage());
    //     }

    //  }



    /**
     * @OA\Post(
     * path="/uc/api/performances/projectUpdate/{id}",
     * operationId="projectUpdate",
     * tags={"Performances"},
     * summary="projectUpdate Performances",
     *   security={ {"Bearer": {} }},
     * description="projectUpdate Performances",
     *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         required=true,
     *        @OA\Schema(type="integer")
     *     ),
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="project_title", type="text"),
     *              @OA\Property(property="description", type="text"),
     *              @OA\Property(property="priority", type="string", description="'0 => Low, 1 => Medium, 2 => High'"),
     *              @OA\Property(property="start_date", type="string"),
     *              @OA\Property(property="end_date", type="string"),
     *              @OA\Property(property="status", type="string", description="'0 => To Do, 1 => In Progress, 2 => Completed'"),
     *              @OA\Property(property="completed", type="string"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Job Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Job Created Successfully",
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

    //  public function projectUpdate(Request $request, $id){

    //     try {
    //        $project = HrmsProject::find($id);
    //         if($project){
    //             $validatedData = $request->validate([
    //                 'project_title' => 'nullable|string|max:65535',
    //                 'description'   => 'nullable|string',
    //                 'status'        => 'nullable|in:0,1,2',
    //                 'priority'      => 'nullable|in:0,1,2',
    //                 'completed'     => 'nullable|string|max:255',
    //                 'start_date'    => 'nullable|date',
    //                 'end_date'      => 'nullable|date|after_or_equal:start_date',
    //             ]);
    //             $user_id = auth('sanctum')->user()->id;
    //             $validatedData['admin_id'] =  $user_id;
    //             $project =    $project->update($validatedData);

    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Project updated successfully.',
    //                 'data'    => $project
    //             ]);
    //         }else{
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Project not found',
    //                 'data'    => []
    //             ]);
    //         }

    //     } catch (\Throwable $th) {
    //         return $this->errorResponse($th->getMessage());
    //     }

    //  }


    /**
     * @OA\get(
     * path="/uc/api/performances/projectList",
     * operationId="projectList",
     * tags={"Performances"},
     * summary="Get Project List Request",
     *   security={ {"Bearer": {} }},
     * description="Get Project List Request",
     *      @OA\Response(
     *          response=201,
     *          description="Project List Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Project List Get Successfully",
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

    // public function projectList(Request $request){

    //     try {
    //         $project = HrmsProject::with('admin')->get();
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Projects list data.',
    //             'data'    => $project
    //         ]);

    //     } catch (\Throwable $th) {
    //         return $this->errorResponse($th->getMessage());
    //     }

    // }



    /**
     * @OA\Post(
     * path="/uc/api/performances/projectCalanderList",
     * operationId="calander",
     * tags={"Performances"},
     * summary="Get Calander Request",
     *   security={ {"Bearer": {} }},
     * description="Get Calander Request",
     *    @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                  @OA\Property(property="flag", type="integer", description="1 => For Weekly, 2 => Monthly",  default=1),
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *          response=201,
     *          description="Calander Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Calander Get Successfully",
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


    // public function projectCalanderList(Request $request){

    //     $flag = $request->input('flag');
    //     $dateArray = $this->getDateArrayByFlag($flag);

    //     return HrmsTeam::with('teamTask')->get();


    // }


    // function getDateArrayByFlag($flag) {
    //     $dates = [];

    //     if ($flag == 1) {
    //         // Weekly - get dates for current week (Monday to Sunday)
    //         $startOfWeek = new \DateTime();
    //         $startOfWeek->modify('monday this week');

    //         for ($i = 0; $i < 7; $i++) {
    //             $dates[] = $startOfWeek->format('Y-m-d');
    //             $startOfWeek->modify('+1 day');
    //         }
    //     } elseif ($flag == 2) {
    //         // Monthly - get dates for the current month
    //         $startOfMonth = new \DateTime('first day of this month');
    //         $endOfMonth = new \DateTime('last day of this month');

    //         while ($startOfMonth <= $endOfMonth) {
    //             $dates[] = $startOfMonth->format('Y-m-d');
    //             $startOfMonth->modify('+1 day');
    //         }
    //     }

    //     return $dates;
    // }


}
