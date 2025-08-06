<?php

namespace App\Http\Controllers\Api\Projects;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HrmsProject;
use App\Models\SubUser;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     * path="/uc/api/project/index",
     * operationId="getproject",
     * tags={"Projects"},
     * summary="Get Projects Request",
     *   security={ {"Bearer": {} }},
     * description="Get Projects Request",
     *      @OA\Response(
     *          response=201,
     *          description="Projects Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Projects Get Successfully",
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
            $getProjects = HrmsProject::all();
            //  return $getProjects = HrmsProject::with(['subProject.minProjects' => function ($query) {
            //                             $query->withCount('projectTask');
            //                         }])->get();

            return    $getProjects = HrmsProject::with([
                'subProject' => function ($q) {
                    $q->withCount([
                        'minProjects', // Total min projects
                        'minProjects as min_projects_status_0_count' => function ($query) {
                            $query->where('status', 0); // Not Started
                        },
                        'minProjects as min_projects_status_1_count' => function ($query) {
                            $query->where('status', 1); // In Progress
                        },
                        'minProjects as min_projects_status_2_count' => function ($query) {
                            $query->where('status', 2); // Completed
                        },
                    ])->with([
                        'minProjects' => function ($query) {
                            $query->withCount([
                                'projectTask as task_status_0_count' => function ($q) {
                                    $q->where('status', 0); // Not Started
                                },
                                'projectTask as task_status_1_count' => function ($q) {
                                    $q->where('status', 1); // In Progress
                                },
                                'projectTask as task_status_2_count' => function ($q) {
                                    $q->where('status', 2); // Completed
                                },
                            ]);
                        }
                    ]);
                }
            ])->get();



            foreach ($getProjects as $key => $project) {

                if ($project->assignees) {

                    if (!is_array($project->assignees) && !empty($project->assignees)) {
                        $project->assignees = array_map('intval', explode(',', $project->assignees));
                    }

                    $project->assignee_employees = SubUser::select('id', 'first_name', 'last_name', 'unique_id', 'email')->whereIn('id', $project->assignees)->get();
                } else {
                    $project->assignee_employees = [];
                }
            }

            return $this->successResponse(
                $getProjects,
                "Projects List"
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
     * path="/uc/api/project/store",
     * operationId="projectStore",
     * tags={"Projects"},
     * summary="Create Project",
     *   security={ {"Bearer": {} }},
     * description="Create Project",
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
     *              @OA\Property(
     *                       property="assignees",
     *                       type="array",
     *                       @OA\Items(type="integer")
     *                   )
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Project Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Project Created Successfully",
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

            $validatedData = $request->validate([
                'project_title' => 'required|string|max:65535',
                'description'   => 'nullable|string',
                'priority'      => 'nullable|in:0,1,2',
                'assignees'     => 'nullable',
                'start_date'    => 'nullable|date',
                'end_date'      => 'nullable|date|after_or_equal:start_date',
            ]);

            if (!is_array($validatedData['assignees']) && !empty($validatedData['assignees'])) {
                $validatedData['assignees'] = array_map('intval', explode(',', $validatedData['assignees']));
            }

            $user_id = auth('sanctum')->user()->id;
            $validatedData['admin_id'] =  $user_id;
            $project = HrmsProject::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully.',
                'data'    => $project
            ]);
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
     * path="/uc/api/project/edit/{id}",
     * operationId="editproject",
     * tags={"Projects"},
     * summary="Edit Projects Request",
     *   security={ {"Bearer": {} }},
     * description="Edit Projects Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Projects Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Projects Edited Successfully",
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
            $getProject = HrmsProject::find($id);

            return $this->successResponse(
                $getProject,
                "Get Project"
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
     * path="/uc/api/project/update/{id}",
     * operationId="UpdateProject",
     * tags={"Projects"},
     * summary="Update Project",
     *   security={ {"Bearer": {} }},
     * description="Update Project",
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      description="Project ID",
     *      @OA\Schema(type="string")
     * ),
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
     *              @OA\Property(
     *                   property="assignees",
     *                  type="array",
     *                  @OA\Items(type="integer")
     *             )
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Project Update Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Project Update Successfully",
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
                'project_title' => 'required|string|max:65535',
                'description'   => 'nullable|string',
                'priority'      => 'nullable|in:0,1,2',
                'assignees'     => 'nullable',
                'start_date'    => 'nullable|date',
                'end_date'      => 'nullable|date|after_or_equal:start_date',
            ]);

            if (!is_array($validatedData['assignees']) && !empty($validatedData['assignees'])) {
                $validatedData['assignees'] = array_map('intval', explode(',', $validatedData['assignees']));
            }
            $user_id = auth('sanctum')->user()->id;
            $validatedData['admin_id'] =  $user_id;
            $getProject = HrmsProject::find($id);

            if (!$getProject) {
                return $this->errorResponse("The given data is not found");
            }

            $getProject->update($validatedData);

            return $this->successResponse(
                $getProject,
                "Project Updated Successfully"
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
     * path="/uc/api/project/delete/{id}",
     * operationId="deleteproject",
     * tags={"Projects"},
     * summary="Delete Projects Request",
     *   security={ {"Bearer": {} }},
     * description="Delete Projects Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Projects Deleted Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Projects Deleted Successfully",
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
            $getProject = HrmsProject::find($id);

            if (!$getProject) {
                return $this->errorResponse("The given data is not found");
            }
            $getProject->delete();

            return $this->successResponse(
                [],
                "Project Deleted SuccessFully"
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }
}
