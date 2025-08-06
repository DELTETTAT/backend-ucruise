<?php

namespace App\Http\Controllers\Api\Projects;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubProject;

class SubProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Post(
     * path="/uc/api/sub_project/index",
     * operationId="sub_project getting",
     * tags={"Projects"},
     * summary="Get sub_project",
     *   security={ {"Bearer": {} }},
     * description="Get sub_project",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="search", type="string", description="Search by name"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="sub_project Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="sub_project Get Successfully",
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
            //  $validated = $request->validate([
            //     'project_id' => 'required|integer',
            //  ]);

            //$get_sub_projects =  SubProject::with('minProjects')->where('project_id', $validated['project_id'])->get();
            $get_sub_projects =  SubProject::with('minProjects')
                ->when($request->search, function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%');
                })
                ->orderBy('id', 'desc')
            ->get();

            return $this->successResponse(
                $get_sub_projects,
                "Sub Project List"
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
     * path="/uc/api/sub_project/store",
     * operationId="sub project store",
     * tags={"Projects"},
     * summary="Store Sub Projects",
     *   security={ {"Bearer": {} }},
     * description="Store Sub Projects",
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
     *          description="Sub Projects Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Sub Projects Created Successfully",
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
                'name' => 'required|string',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'priority' => 'nullable|integer',
               // 'project_id' => 'required|integer',
            ]);

            $validatedData['created_by'] = auth('sanctum')->user()->id;
            SubProject::create($validatedData);

            return $this->successResponse(
                [],
                "Sub Project Created Successfully"
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
     * path="/uc/api/sub_project/edit/{id}",
     * operationId="edit_sub_project",
     * tags={"Projects"},
     * summary="Edit Sub Projects Request",
     *   security={ {"Bearer": {} }},
     * description="Edit Sub Projects Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Sub Projects Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Sub Projects Edited Successfully",
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
            $getSubProject = SubProject::find($id);

            if (!$getSubProject) {
                return $this->errorResponse("The given data is not found");
            }

            return $this->successResponse(
                $getSubProject,
                "Get Sub Project"
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
     * path="/uc/api/sub_project/update/{id}",
     * operationId="UpdateSubProject",
     * tags={"Projects"},
     * summary="Update Project",
     *   security={ {"Bearer": {} }},
     * description="Update Sub Project",
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      description="Sub Project ID",
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
     *          description="Sub Project Update Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Sub Project Update Successfully",
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
                'priority' => 'required|integer',
            ]);

            $supProject = SubProject::find($id);
            if (!$supProject) {
                 return $this->errorResponse("The given data is not found");
            }

            $validatedData['created_by'] = auth('sanctum')->user()->id;
            $supProject->update($validatedData);

            return $this->successResponse(
                $supProject,
                "Sub Project Updated Successfully"
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
     * path="/uc/api/sub_project/delete/{id}",
     * operationId="deletesub_project",
     * tags={"Projects"},
     * summary="Delete Sub Projects Request",
     *   security={ {"Bearer": {} }},
     * description="Delete Sub Projects Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Sub Projects Deleted Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Sub Projects Deleted Successfully",
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
            $getProject = SubProject::find($id);

            if ($getProject) {
                $getProject->delete();

            }else {
                return $this->errorResponse("The given data is not found");
            }

            return $this->successResponse(
                [],
                "Sub Project Deleted SuccessFully"
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }
}
