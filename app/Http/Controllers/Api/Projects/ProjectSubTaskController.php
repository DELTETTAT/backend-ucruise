<?php

namespace App\Http\Controllers\Api\Projects;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HrmsSubTask;

class ProjectSubTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


      /**
     * @OA\Post(
     * path="/uc/api/project_sub_task/index",
     * operationId="project Sub task getting",
     * tags={"Projects"},
     * summary="Get Projects Sub Task",
     *   security={ {"Bearer": {} }},
     * description="Get Projects Sub Task",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="task_id", type="integer"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Projects Sub Task Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Projects Sub Task Get Successfully",
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
    //     try {
    //         $validatedData = $request->validate([
    //             'task_id' => 'required|integer'
    //         ]);

    //         $getSubTask = HrmsSubTask::where('task_id', $validatedData['task_id'])->get();

    //         return $this->successResponse(
    //             $getSubTask,
    //             "Sub Task List"
    //         );

    //     } catch (\Throwable $th) {
    //         return $this->errorResponse($th->getMessage());
    //     }
    // }

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
     * path="/uc/api/project_sub_task/store",
     * operationId="project sub task store",
     * tags={"Projects"},
     * summary="Store Projects Sub Task",
     *   security={ {"Bearer": {} }},
     * description="Store Projects Sub Task",
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
     *              @OA\Property(property="task_id", type="integer"),
     *              @OA\Property(property="assigned_id", type="integer"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Projects Sub Task Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Projects Sub Task Created Successfully",
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
    // public function store(Request $request)
    // {
    //     try {
    //         $validatedData = $request->validate([
    //             'name' => 'required|string',
    //             'description' => 'nullable|string',
    //             'start_date' => 'required|date',
    //             'end_date' => 'nullable|date',
    //             'priority' => 'nullable|integer',
    //             'task_id' => 'required|integer',
    //             'assigned_id' => 'nullable|integer',
    //         ]);


    //         HrmsSubTask::create($validatedData);

    //         return $this->successResponse(
    //             [],
    //             "Sub Task Created Successfully"
    //         );
    //     } catch (\Throwable $th) {
    //         return $this->errorResponse($th->getMessage());
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
