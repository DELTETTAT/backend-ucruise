<?php

namespace App\Http\Controllers\Api\Projects;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ProjectTaskRequest;
use App\Models\ProjectTask;
use App\Models\HrmsProject;
use App\Models\SubUser;
use App\Models\SubProject;

class ProjectTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     /**
     * @OA\Post(
     * path="/uc/api/project_task/index",
     * operationId="project task getting",
     * tags={"Projects"},
     * summary="Get Projects Task",
     *   security={ {"Bearer": {} }},
     * description="Get Projects Task",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="min_project_id", type="integer"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Projects Task Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Projects Task Get Successfully",
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
    public function index(Request $request)
    {
        try {

            $validatedData = $request->validate([
                'min_project_id' => 'required|integer'
            ]);

            $projectTasks = ProjectTask::where('min_project_id',$validatedData['min_project_id'])->get();

            return $this->successResponse(
                $projectTasks,
                "Project Task List"
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
     * path="/uc/api/project_task/store",
     * operationId="project task store",
     * tags={"Projects"},
     * summary="Store Projects Task",
     *   security={ {"Bearer": {} }},
     * description="Store Projects Task",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="description", type="text"),
     *              @OA\Property(property="start_date", type="date"),
     *              @OA\Property(property="end_date", type="date"),
     *              @OA\Property(property="priority", type="integer", description="'0 => Low, 1 => Medium, 2 => High'"),
     *              @OA\Property(property="min_project_id", type="integer"),
     *              @OA\Property(property="assigned_id", type="integer"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Projects Task Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Projects Task Created Successfully",
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

    public function store(ProjectTaskRequest $request)
    {
        try {

            $validatedData = $request->validated();

            $validatedData['created_by'] = auth('sanctum')->user()->id;

            ProjectTask::create($validatedData);

            return $this->successResponse( [],'Task Created Successfully');

        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage());
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
     * path="/uc/api/project_task/edit/{id}",
     * operationId="edit_project_task",
     * tags={"Projects"},
     * summary="Edit Projects Task Request",
     *   security={ {"Bearer": {} }},
     * description="Edit Projects Task Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Projects Task Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Projects Task Edited Successfully",
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
            $getProjectTask = ProjectTask::find($id);

            if (!$getProjectTask) {
                return $this->errorResponse("The given data is not found");
            }

            return $this->successResponse(
                $getProjectTask,
                "Get Project Task"
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
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
     * path="/uc/api/project_task/update/{id}",
     * operationId="Updateproject_task",
     * tags={"Projects"},
     * summary="Update Project Task",
     *   security={ {"Bearer": {} }},
     * description="Update Project Task",
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      description="Project Task ID",
     *      @OA\Schema(type="string")
     * ),
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="description", type="text"),
     *              @OA\Property(property="start_date", type="date"),
     *              @OA\Property(property="end_date", type="date"),
     *              @OA\Property(property="priority", type="integer", description="'0 => Low, 1 => Medium, 2 => High'"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Project Task Update Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Project Task Update Successfully",
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


    public function update(Request $request, $id)
    {
        try {

            $validatedData = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'assigned_id' => 'nullable|integer',
                'priority' => 'required|integer',
                'status' => 'nullable|integer',
            ]);

            $getProjectTask = ProjectTask::find($id);

            if (!$getProjectTask) {
                return $this->errorResponse("The given data is not found");
            }

            $validatedData['created_by'] = auth('sanctum')->user()->id;

            $getProjectTask->update($validatedData);

            return $this->successResponse(
                $getProjectTask,
                "Project Task Updated Successfully"
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
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
     * path="/uc/api/project_task/delete/{id}",
     * operationId="deleteprojecttask",
     * tags={"Projects"},
     * summary="Delete Projects Task Request",
     *   security={ {"Bearer": {} }},
     * description="Delete Projects Task Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Projects Task Deleted Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Projects Task Deleted Successfully",
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
    public function destroy($id)
    {
        try {
            $getProjectTask = ProjectTask::find($id);

            if ($getProjectTask) {
                $getProjectTask->delete();

            }else {
                return $this->errorResponse("The given data is not found");
            }


            return $this->successResponse(
                [],
                "Project Task Deleted SuccessFully"
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }



     /**
     * @OA\Post(
     * path="/uc/api/project_task/update_status",
     * operationId="Updateproject_task_status",
     * tags={"Projects"},
     * summary="Update Project Task",
     *   security={ {"Bearer": {} }},
     * description="Update Project Task Status",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="task_id", type="integer"),
     *              @OA\Property(property="status", type="integer", description="0 => Not Started, 1 => In Progress, 2 => Done, 3 => On Hold, 4 => Close"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Project Task Status Updated Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Project Task Status Updated Successfully",
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

      public function updateStatus(Request $request){
           try {
               $validatedData = $request->validate([
                'task_id' => 'required|integer',
                'status' => 'required|integer|in:0,1,2,3,4',
               ]);

              $projectTask = ProjectTask::find($validatedData['task_id']);
              if (!$projectTask) {
                return $this->errorResponse("The given data is not found");
              }

              $projectTask->status = $validatedData['status'];
              $projectTask->save();

              return $this->successResponse(
                [],
                "Task Droped Successfully"
              );

           } catch (\Throwable $th) {
              return $this->errorResponse($th->getMessage());
           }
      }


    /**
     * @OA\Post(
     * path="/uc/api/project_task/assignee_employee_list",
     * operationId="getassignee_employee_list",
     * tags={"Projects"},
     * summary="Get Projects Request",
     *   security={ {"Bearer": {} }},
     * description="Get assignee employees Request",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="project_id", type="integer"),
     *            ),
     *        ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Projects assignee employees Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Projects assignee employees Get Successfully",
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


     public function assigneeEmployeesList(Request $request){
         try {
            //    $validatedData = $request->validate([
                //    'project_id' => 'required|integer'
            //    ]);

            //    $project = HrmsProject::find($validatedData['project_id']);
            //    $assignee_employees = $project->assignees;
            //    if ($assignee_employees) {
                   $employees = SubUser::select('id','first_name','last_name','unique_id','email')->get();
            //    }else {
                // $employees = [];
            //    }


               return $this->successResponse(
                  $employees,
                  "Assignee Employees List"
               );

         } catch (\Throwable $th) {
             return $this->errorResponse($th->getMessage());
         }
     }



/**
 * @OA\Post(
 *     path="/uc/api/project_task/employeeProjectCount",
 *     operationId="employeeProjectCount",
 *     tags={"Projects"},
 *     summary="Get project status counts for an employee (admin can specify employee_id)",
 *     security={ {"Bearer": {} }},
 *     description="Returns the count of projects by status for the specified employee (admin) or the authenticated user.",
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="employee_id", type="integer", description="Employee ID (admin only, optional)")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Project status counts",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(response=400, description="Bad request"),
 * )
 */
public function employeeProjectCount(Request $request)
{
    $user = auth('sanctum')->user();
    if (!$user) {
        return $this->errorResponse('Unauthorized', 401);
    }

    $employeeId = $request->input('employee_id',$user->id);

    $counts = [
        'not_started' => ProjectTask::where('assigned_id', $employeeId)->where('status', 0)->count(),
        'in_progress' => ProjectTask::where('assigned_id', $employeeId)->where('status', 1)->count(),
        'completed'   => ProjectTask::where('assigned_id', $employeeId)->where('status', 2)->count(),
        'total'       => ProjectTask::where('assigned_id', $employeeId)->count(),
    ];

    return $this->successResponse($counts, "Employee project status counts");
}

 

    /**
     * @OA\Get(
     * path="/uc/api/project_task/adminDashboardProjectCount",
     * operationId="adminDashboardProjectCount",
     * tags={"Projects"},
     * summary="Admin Dashboard Project and Task Status Count",
     *   security={ {"Bearer": {} }},
     * description="Returns main project count, task status counts, and their percentages.",
     *      @OA\Response(
     *          response=200,
     *          description="Admin Dashboard Project and Task Status Count",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     * )
     */


    public function adminDashboardProjectCount(Request $request)
    {
        try {
            //Main project count
            $mainProjectCount = SubProject::count();

            //All tasks assigned to main projects
            $totalTasks = ProjectTask::count();
            $notStarted = ProjectTask::where('status', 0)->count();
            $inProgress = ProjectTask::where('status', 1)->count();
            $completed  = ProjectTask::where('status', 2)->count();

            //Calculate percentages
            $percent = function($count) use ($totalTasks) {
                return $totalTasks > 0 ? round(($count / $totalTasks) * 100, 2) : 0;
            };

            $data = [
                'main_project_count' => $mainProjectCount,
                'total_tasks'        => $totalTasks,
                'not_started'        => $notStarted,
                'in_progress'        => $inProgress,
                'completed'          => $completed,
                'percent_not_started'=> $percent($notStarted),
                'percent_in_progress'=> $percent($inProgress),
                'percent_completed'  => $percent($completed),
            ];

            return $this->successResponse($data, "Admin Dashboard Project and Task Status Count");
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }


}
