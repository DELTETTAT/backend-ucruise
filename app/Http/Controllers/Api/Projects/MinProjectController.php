<?php

namespace App\Http\Controllers\Api\Projects;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MinProject;

class MinProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * path="/uc/api/min_project/store",
     * operationId="Min project store",
     * tags={"Projects"},
     * summary="Store Min Projects",
     *   security={ {"Bearer": {} }},
     * description="Store Min Projects",
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
     *              @OA\Property(property="sub_project_id", type="integer"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Min Projects Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Min Projects Created Successfully",
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
                'priority' => 'required|integer',
                'sub_project_id' => 'required|integer',
            ]);

            $validatedData['created_by'] = auth('sanctum')->user()->id;
            MinProject::create($validatedData);

            return $this->successResponse(
                [],
                "Min Project Created Successfully"
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
     * path="/uc/api/min_project/edit/{id}",
     * operationId="edit_min_project",
     * tags={"Projects"},
     * summary="Edit Min Projects Request",
     *   security={ {"Bearer": {} }},
     * description="Edit Min Projects Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Min Projects Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Min Projects Edited Successfully",
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
            $getMinProject = MinProject::find($id);

            if (!$getMinProject) {
                return $this->errorResponse("The given data is not found");
            }

            return $this->successResponse(
                $getMinProject,
                "Get Min Project"
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
     * path="/uc/api/min_project/update/{id}",
     * operationId="UpdateminProject",
     * tags={"Projects"},
     * summary="Update Project",
     *   security={ {"Bearer": {} }},
     * description="Update Min Project",
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      description="Min Project ID",
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
     *          description="Min Project Update Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Min Project Update Successfully",
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
                'priority' => 'nullable|integer',
            ]);

            $getMinProject = MinProject::find($id);
            if (!$getMinProject) {
                return $this->errorResponse("The given data is not found");
            }
            $validatedData['created_by'] = auth('sanctum')->user()->id;
            $getMinProject->update($validatedData);

            return $this->successResponse(
                $getMinProject,
                "Min Project Updated Successfully"
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
     * path="/uc/api/min_project/delete/{id}",
     * operationId="minprojectdeleted",
     * tags={"Projects"},
     * summary="Delete Min Projects Request",
     *   security={ {"Bearer": {} }},
     * description="Delete Min Projects Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Min Projects Deleted Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Min Projects Deleted Successfully",
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
            $getMinProject = MinProject::find($id);

            if ($getMinProject) {
                $getMinProject->delete();

            }else {
                return $this->errorResponse("The given data is not found");
            }


            return $this->successResponse(
                [],
                "Min Project Deleted SuccessFully"
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }
}
