<?php

namespace App\Http\Controllers\Api\Designation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Designation;
use App\Models\UpdateSystemSetupHistory;
use App\Http\Requests\StoreDesignationRequest;
use App\Http\Resources\Designation\DesignationCollection;
use App\Http\Resources\Designation\DesignationResource;
use Exception;

class DesignationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */



    /**
     * @OA\Get(
     * path="/uc/api/designation/index",
     * operationId="getDesignation",
     * tags={"Designation"},
     * summary="Get designation Request",
     *   security={ {"Bearer": {} }},
     * description="Get designation Request",
     *      @OA\Response(
     *          response=201,
     *          description="Designation Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Designation Get Successfully",
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
            $getDesignationList = Designation::orderBy('id', 'desc')->get();
            return $this->successResponse(
                new DesignationCollection($getDesignationList),
                'Designation list'
            );
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */





    /**
     * @OA\Post(
     * path="/uc/api/designation/store",
     * operationId="storeDesignation",
     * tags={"Designation"},
     * summary="Store designation Request",
     *   security={ {"Bearer": {} }},
     * description="Store designation Request",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="description", type="string"),
     *              @OA\Property(property="image", type="string", format="binary"),
     *              @OA\Property(property="status", type="integer"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Designation Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Designation Created Successfully",
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

    public function store(StoreDesignationRequest $request)
    {
        try {
            $validated = $request->validated();
            $imgName = null;
            /**
             * handle the file upload
             */
            if ($request->hasFile('image')) {
                $imgName = time() . "_" . $request->file('image')->getClientOriginalName();
                $request->file('image')->move(public_path('designation'), $imgName);
            }

            $validated['image'] = $imgName;

            $designation = Designation::create($validated);

            // Record creation history
            $user = auth('sanctum')->user();
            $changes = [];
            $excludedFields = ['status']; // Fields to exclude from history

                foreach ($validated as $field => $value) {
                    if (in_array($field, $excludedFields)) {
                        continue; // Skip status field
                    }
                    
                    $label = ucwords(str_replace('_', ' ', $field));
                    
                    // Format the value for display
                    $displayValue = $field === 'image' 
                        ? ($value ? 'Image uploaded' : 'No image')
                        : ($value ?? 'Not set');

                    $changes[] = "$label: $displayValue";
                }

                UpdateSystemSetupHistory::create([
                    'employee_id' => $user->id,
                    'updated_by' => $user->id,
                    'date' => now()->format('Y-m-d'),
                    'time' => now()->format('H:i:s'),
                    'notes' => 'Designation Created: ' . ($designation->name ?? 'New Designation'),
                    'changed' => "New designation created:\n" . implode("\n", $changes),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            return $this->successResponse(
                new DesignationResource($designation),
                'Designation created Successfully'
            );
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    /**
     * @OA\get(
     * path="/uc/api/designation/edit/{id}",
     * operationId="editDesignation",
     * tags={"Designation"},
     * summary="Edit designation Request",
     *   security={ {"Bearer": {} }},
     * description="Edit designation Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Designation Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Designation Edited Successfully",
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
    public function edit($designationId)
    {
        try {
            $getDesignationDetails = Designation::find($designationId);
            if (isset($getDesignationDetails)) {
                return $this->successResponse(
                    new DesignationResource($getDesignationDetails),
                    'Designation Details'
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
     * @OA\post(
     * path="/uc/api/designation/update/{id}",
     * operationId="updateDesignation",
     * tags={"Designation"},
     * summary="Update designation Request",
     *   security={ {"Bearer": {} }},
     * description="Store designation Request",
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
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="description", type="string"),
     *              @OA\Property(property="image", type="string", format="binary"),
     *              @OA\Property(property="status", type="integer"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Designation Updated Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Designation Updated Successfully",
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
    public function update(StoreDesignationRequest $request, $designationId)
    {
        try {
            $findDesignationDetail = Designation::find($designationId);
            if (isset($findDesignationDetail)) {
                $validated = $request->validated();
                //Track original values before update
                $original = $findDesignationDetail->getOriginal();
                $changes = [];
                $ignoredFields = ['status'];
                /**
                 * handle the file upload
                 */
                if ($request->hasFile('image')) {
                    $imgName = time() . "_" . $request->file('image')->getClientOriginalName();
                    $request->file('image')->move(public_path('designation'), $imgName);
                    $validated['image'] = $imgName;
                }

                //Compare and track changed fields
                foreach ($validated as $field => $newValue) {
                    if (in_array($field, $ignoredFields)) continue;
                    
                    $oldValue = $original[$field] ?? null;
                    if ($oldValue != $newValue) {
                        $fieldName = ucwords(str_replace('_', ' ', $field));
                        $oldValFormatted = is_null($oldValue) ? 'empty' : $oldValue;
                        $newValFormatted = is_null($newValue) ? 'empty' : $newValue;
                        $changes[] = "$fieldName changed from '$oldValFormatted' to '$newValFormatted'";
                    }
                }

                //Save history if there are changes
                if (!empty($changes)) {
                    $user = auth('sanctum')->user();
                    UpdateSystemSetupHistory::create([
                        'employee_id' => $user->id,
                        'updated_by' => $user->id,
                        'date' => now()->format('Y-m-d'),
                        'time' => now()->format('H:i:s'),
                        'notes' => 'Designation Updated',
                        'changed' => "Updated designation details:\n" . implode('; ', $changes),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                $findDesignationDetail->update($validated);
                return $this->successResponse(
                    new DesignationResource($findDesignationDetail),
                    'Designation updated Successfully'
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
     *     path="/uc/api/designation/delete/{id}",
     *     operationId="deleteDesignation",
     *     tags={"Designation"},
     *     summary="Delete designation Request",
     *     security={ {"Bearer": {} }},
     *     description="Delete designation Request",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Designation deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Designation deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource Not Found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Designation not found.")
     *         )
     *     )
     * )
     */


    public function delete($designationId)
    {
        try {
            $getDesignationDetails = Designation::find($designationId);
            if (isset($getDesignationDetails)) {
                
                // Record deletion history
                $user = auth('sanctum')->user();
                $changes = [];
                $ignoredFields = ['created_at', 'updated_at', 'deleted_at', 'status', 'id'];

                foreach ($getDesignationDetails->getAttributes() as $field => $value) {
                    if (in_array($field, $ignoredFields)) continue;
                    
                    $label = ucwords(str_replace('_', ' ', $field));
                    
                    // Format values for better readability
                    $formattedValue = match(true) {
                        $field === 'image' => 'Image deleted',
                        is_null($value) => 'Not set',
                        default => $value
                    };
                    
                    $changes[] = "$label: $formattedValue";
                }

                UpdateSystemSetupHistory::create([
                    'employee_id' => $user->id,
                    'updated_by' => $user->id,
                    'date' => now()->format('Y-m-d'),
                    'time' => now()->format('H:i:s'),
                    'notes' => 'Designation Deleted: ' . ($getDesignationDetails->title ?? 'Untitled'),
                    'changed' => "Deleted designation details:\n" . implode("\n", $changes),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $getDesignationDetails->delete();
                return $this->successResponse(
                    [],
                    'Designation Removed Sucessfully'
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
     * path="/uc/api/designation/designationList",
     * operationId="getDesignationlist",
     * tags={"Designation"},
     * summary="Get designation list Request",
     *   security={ {"Bearer": {} }},
     * description="Get designation list Request",
     *      @OA\Response(
     *          response=201,
     *          description="Designations list Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Designations list Get Successfully",
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
    public function designationList() {
        try {
            $getDesignationList = Designation::all();
            return $this->successResponse([
                 new DesignationCollection($getDesignationList, false),
                 "All Designation List"
            ]);
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }
}
