<?php

namespace App\Http\Controllers\Api\Hrms\Quiz\Reason;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ReasonRequest;
use App\Models\HrmsReason;
use App\Models\HrmsSubReason;
use App\Models\ReasonType;
use App\Models\Reason;
use App\Http\Resources\Reason\ReasonResource;
use App\Http\Resources\Reason\ReasonCollection;

class HrmsReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\get(
     * path="/uc/api/reason/index",
     * operationId="getreason",
     * tags={"reasons categories"},
     * summary="Get Candidates Request",
     *   security={ {"Bearer": {} }},
     * description="Get reasons Request",
     *      @OA\Response(
     *          response=201,
     *          description="reasons Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="reasons Get Successfully",
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

            $getReasons = HrmsReason::with('subCategories')->get();
            //$getReasons = ReasonType::with('reasons')->get();
             return $this->successResponse(
                new ReasonCollection($getReasons),
                "Reason List"
             );
        } catch (\Exception $ex) {
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
     * path="/uc/api/reason/store",
     * operationId="storereason",
     * tags={"reasons categories"},
     * summary="Store reason Request",
     *   security={ {"Bearer": {} }},
     * description="Store reason Request",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="reason_type", type="string"),
     *              @OA\Property(property="reason_title", type="array",
     *                   @OA\Items(type="string")
     *                ),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="reasons Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="reasons Created Successfully",
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
    public function store(ReasonRequest $request)
    {
        try {
            $validated = $request->validated();
            $reasonType = $validated['reason_type'];
            $validated['reason_title'];
            $subCategories = is_array($validated['reason_title']) ? $validated['reason_title'] : explode(',',$validated['reason_title']);
                if(!empty($reasonType)){
                    $reason = HrmsReason::create(['title_of_reason'=>$reasonType]);
                    foreach ($subCategories as  $categorie) {
                        HrmsSubReason:: create([
                            'reason_id' => $reason->id,
                            'sub_categories' => $categorie
                        ]);
                    }
                }else{
                    return response()->json(['message' => 'Reason Type Not Found'], 404);
                }
            
            return $this->successResponse( [], "Reason Created Successfully");

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
     * path="/uc/api/reason/edit/{id}",
     * operationId="editreason",
     * tags={"reasons categories"},
     * summary="Edit reason Request",
     *   security={ {"Bearer": {} }},
     * description="Edit reason Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="reason Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="reason Edited Successfully",
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

            $getReason = HrmsReason::with('subCategories')->where('id', $id)->first();
           //$getReason = ReasonType::with('reasons')->where('id', $id)->first();
            
            if ($getReason) {
                return $this->successResponse(
                    new ReasonResource($getReason),
                    "Get Reason"
                 );
            }else {
                return $this->errorResponse("the given data not found");
            }
             
        } catch (\Exception $ex) {
            return response()->json($ex->getMessage());
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
     * @OA\post(
     * path="/uc/api/reason/update/{id}",
     * operationId="updatereason",
     * tags={"reasons categories"},
     * summary="Update reason Request",
     *   security={ {"Bearer": {} }},
     * description="Store reason Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="reason_type", type="string"),
     *              @OA\Property(property="reason_title", type="array",
     *                   @OA\Items(type="string")
     *                ),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="reason Updated Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="reason Updated Successfully",
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
    public function update(ReasonRequest $request, $id)
    {
        try {

            $reason = HrmsReason::find($id);

            if ($reason) {
                $validated = $request->validated();
                $getReasons = HrmsSubReason::where('reason_id', $id)->get();
                $reason->title_of_reason = $validated['reason_type'];
                $reason->save();

                foreach ($getReasons as $getReason) {
                    $getReason->delete();
                }
                
                $subCategories = is_array($validated['reason_title']) ? $validated['reason_title'] : explode(',',$validated['reason_title']);
                
                if(!empty($subCategories)){
                    foreach ($subCategories as  $categorie) {
                        HrmsSubReason:: create([
                            'reason_id' => $reason->id,
                            'sub_categories' => $categorie
                        ]);
                    }
                }else{
                    return $this->errorResponse("the given data is not found");
                }

                return $this->successResponse( [],"Reason Updated Successfully");

            }else {
                return $this->errorResponse("the given data is not found");
            }
        } catch (\Exception $ex) {
            return $this->errorResponse($ex);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\delete(
     * path="/uc/api/reason/destroy/{id}",
     * operationId="deletereason",
     * tags={"reasons categories"},
     * summary="Delete reason Request",
     * security={ {"Bearer": {} }},
     * description="Delete reason Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response=200,
     *     description="reason Deleted Successfully",
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
            $getReason = HrmsReason::find($id);

            if ($getReason) {
                $getReason->delete();
                return $this->successResponse(
                    [],
                    "Reason Deleted Successfully"
                );
            }else {
                return $this->errorResponse("the given data is not found");
             }
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }
    

     /**
     * @OA\delete(
     * path="/uc/api/reason/subCategoryDelete/{id}",
     * operationId="deleteSubreason",
     * tags={"reasons categories"},
     * summary="Delete Sub reason Request",
     * security={ {"Bearer": {} }},
     * description="Delete Sub reason Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Sub Reason Deleted Successfully",
     *     @OA\JsonContent()
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found"
     * )
     * )
     */


     public function subCategoryDelete($id)
     {
         try {
             $getSubReason = HrmsSubReason::find($id);
 
             if ($getSubReason) {
                 $getSubReason->delete();
                 return $this->successResponse(
                     [],
                     "Reason Category Deleted Successfully"
                    );
             }else {
                return $this->errorResponse("the given data is not found");
             }
         } catch (\Exception $ex) {
             return $this->errorResponse($ex->getMessage());
         }
     }
}
