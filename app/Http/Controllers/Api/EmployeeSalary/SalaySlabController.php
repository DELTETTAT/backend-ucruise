<?php

namespace App\Http\Controllers\Api\EmployeeSalary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\HrmsEmployeeSalarySlab;

class SalaySlabController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    /**
     * @OA\Get(
     * path="/uc/api/salary_slab/index",
     * operationId="getSalarySlab",
     * tags={"Employee Salary Slabs"},
     * summary="Get Salary Slabs Request",
     *   security={ {"Bearer": {} }},
     * description="Get Salary Slabs Request",
     *      @OA\Response(
     *          response=201,
     *          description="Salary Slabs Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Salary Slabs Get Successfully",
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
              $salaySlab = HrmsEmployeeSalarySlab::all();

              return $this->successResponse(
                  $salaySlab,
                  "Salary Slabs List"
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
     * path="/uc/api/salary_slab/store",
     * operationId="storeSalarySlab",
     * tags={"Employee Salary Slabs"},
     * summary="Store Salary Slab Request",
     *   security={ {"Bearer": {} }},
     * description="Store Salary Slab Request",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              required={"experience_level", "salary"},
     *              @OA\Property(property="experience_level", type="string"),
     *              @OA\Property(property="salary", type="number", format="float"),
     *              @OA\Property(property="year_experience", type="number", format="float"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Salary Slab Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Salary Slab Created Successfully",
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
                 $validated = Validator::make($request->all(), [
                         'experience_level' => 'required|string',
                         'salary' => 'required',
                 ]);

                 HrmsEmployeeSalarySlab::create($request->all());

                 return $this->successResponse(
                    [],
                    "Salary Slab Created Successfully"
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
