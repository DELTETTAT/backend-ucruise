<?php

namespace App\Http\Controllers\Api\SalarySetting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\TDSSettingRequest;
use App\Models\TdsSetting;
use App\Models\UpdateSystemSetupHistory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class TDSSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */



     /**
     * @OA\Get(
     * path="/uc/api/salary_setting/tds_setting/index",
     * operationId="get TDS setting data",
     * tags={"Salary Setting"},
     * summary="getting TDS setting data",
     *   security={ {"Bearer": {} }},
     * description="getting TDS setting data",
     *      @OA\Response(
     *          response=201,
     *          description="TDS setting data Get successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="TDS setting data Get successfully",
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
              $getTdsSetting = TdsSetting::all();

              return $this->successResponse(
                $getTdsSetting,
                "TDS Data List"
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
     * path="/uc/api/salary_setting/tds_setting/store",
     * operationId="store TDS Setting",
     * tags={"Salary Setting"},
     * summary="store TDS Settinge ",
     *   security={ {"Bearer": {} }},
     *    description="store TDS Setting ",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               @OA\Property(property="tds_from", type="numeric", description="defin range for salary"),
     *               @OA\Property(property="tds_to", type="numeric", description="defin range for salary"),
     *               @OA\Property(
     *                     property="tds_type",
     *                     type="string",
     *                     description="PF Type (Percentage or Fixed)",
     *                     enum={"Percentage", "Fixed"}
     *                 ),
     *              @OA\Property(property="tds_value", type="string", description="defin TDS value"),
     *              @OA\Property(property="tds_enabled", type="integer", description="1 for enabled, 0 for disabled"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="TDS Setting Created successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="TDS Setting Created successfully",
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
    // public function store(TDSSettingRequest $request)
    // {
    //      try {

    //          $validatedData = $request->validated();
    //         //$validatedData['tds_enabled'] = $validatedData['tds_enable'] ?? 0; // Default to 0 if not set
    //          $user = auth('sanctum')->user();

    //         // TdsSetting::create($validatedData);

    //         // Check if it's multiple records
    //         if (isset($validatedData[0]) && is_array($validatedData[0])) {
    //             foreach ($validatedData as $tdsData) {
    //                 $tdsData['tds_enabled'] = $tdsData['tds_enable'] ?? 0;
    //                 $record = TdsSetting::create($tdsData);
                
    //                 // Simple history recording for each record
    //                 UpdateSystemSetupHistory::create([
    //                     'employee_id' => $user->id,
    //                     'updated_by' => $user->id,
    //                     'date' => now()->format('Y-m-d'),
    //                     'time' => now()->format('H:i:s'),
    //                     'notes' => 'TDS Setting Created',
    //                     'changed' => 'Created new TDS record',
    //                     'created_at' => now(),
    //                     'updated_at' => now()
    //                 ]);
    //             }
    //         } else {
    //             // Single record
    //             $validatedData['tds_enabled'] = $validatedData['tds_enable'] ?? 0;
    //             $record = TdsSetting::create($validatedData);
                
    //             // Simple history recording
    //             UpdateSystemSetupHistory::create([
    //                 'employee_id' => $user->id,
    //                 'updated_by' => $user->id,
    //                 'date' => now()->format('Y-m-d'),
    //                 'time' => now()->format('H:i:s'),
    //                 'notes' => 'TDS Setting Created',
    //                 'changed' => 'Created new TDS record',
    //                 'created_at' => now(),
    //                 'updated_at' => now()
    //             ]);
    //         }

    //          return $this->successResponse(
    //             [],
    //             "New TDS Created Successfully"
    //          );

    //      } catch (\Throwable $th) {
    //          return $this->errorResponse($th->getMessage());
    //      }
    // }
    public function store(Request $request)
    {
        try {
            $rawInput = $request->all();

            // Convert flat input like 0[tds_from] => value into structured array
            $structured = [];
            foreach ($rawInput as $key => $value) {
                if (Str::contains($key, '[') && Str::contains($key, ']')) {
                    $index = Str::before($key, '[');
                    $field = Str::between($key, '[', ']');
                    $structured[$index][$field] = $value;
                }
            }

            $input = !empty($structured) ? $structured : (is_array($rawInput) ? $rawInput : []);
            $data = isset($input[0]) && is_array($input[0]) ? $input : [$input];

            $errors = [];
            $validated = [];

            foreach ($data as $i => $item) {
                if (!is_array($item)) {
                    continue; // or handle error if needed
                }

                $validator = Validator::make($item, [
                    'tds_from' => 'required|numeric',
                    'tds_to' => [
                        'required',
                        'numeric',
                        function ($attribute, $value, $fail) use ($item) {
                            if (($item['tds_from'] ?? 0) >= $value) {
                                $fail('TDS From must be less than TDS To.');
                            }

                            $from = $item['tds_from'];
                            $to = $value;
                            $existing = TdsSetting::all();

                            foreach ($existing as $record) {
                                if ($from < $record->tds_to && $to > $record->tds_from) {
                                    $fail("This TDS range overlaps with existing range: {$record->tds_from} - {$record->tds_to}");
                                    break;
                                }
                            }
                        }
                    ],
                    'tds_type' => 'required|string|in:Percentage,Fixed',
                    'tds_value' => 'required|string',
                    'tds_enable' => 'nullable|integer',
                ]);

                if ($validator->fails()) {
                    $errors["$i"] = $validator->errors();
                } else {
                    $validated[] = $validator->validated();
                }
            }

            if (!empty($errors)) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $errors,
                ], 422);
            }

            $user = auth('sanctum')->user();

            foreach ($validated as $tdsData) {
                $tdsData['tds_enabled'] = $tdsData['tds_enable'] ?? 0;

                TdsSetting::create($tdsData);

                UpdateSystemSetupHistory::create([
                    'employee_id' => $user->id,
                    'updated_by' => $user->id,
                    'date' => now()->format('Y-m-d'),
                    'time' => now()->format('H:i:s'),
                    'notes' => 'TDS Setting Created',
                    'changed' => 'Created new TDS record',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            return $this->successResponse([], "New TDS Created Successfully");

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
     * path="/uc/api/salary_setting/tds_setting/edit/{id}",
     * operationId="edittds_setting",
     * tags={"Salary Setting"},
     * summary="Edit tds_setting Request",
     *   security={ {"Bearer": {} }},
     * description="Edit tds_setting Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="TDS Setting Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="TDS Setting Edited Successfully",
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
            $getTdsSetting = TdsSetting::find($id);

            if (!$getTdsSetting) {
                return $this->errorResponse("the given data is not found");
            }

            return $this->successResponse(
                $getTdsSetting,
                "TDS Setting Data"
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
     * path="/uc/api/salary_setting/tds_setting/update/{id}",
     * operationId="update TDS Setting",
     * tags={"Salary Setting"},
     * summary="update TDS Settinge ",
     *   security={ {"Bearer": {} }},
     *    description="update TDS Setting ",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               @OA\Property(property="tds_from", type="numeric", description="defin range for salary"),
     *               @OA\Property(property="tds_to", type="numeric", description="defin range for salary"),
     *               @OA\Property(
     *                     property="tds_type",
     *                     type="string",
     *                     description="PF Type (Percentage or Fixed)",
     *                     enum={"Percentage", "Fixed"}
     *                 ),
     *              @OA\Property(property="tds_value", type="string", description="defin TDS value"),
     *              @OA\Property(property="tds_enabled", type="integer", description="1 for enabled, 0 for disabled"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="TDS Setting Updated successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="TDS Setting Updated successfully",
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

              //$validatedData = $request->validated();

              $getTdsSettingData = TdsSetting::find($id);
              $user = auth('sanctum')->user();

              if (!$getTdsSettingData) {
                 return $this->errorResponse("The given data is not found");
              }

              // Get original values before update
              $originalValues = $getTdsSettingData->getOriginal();


              $getTdsSettingData->update($request->all());

              // Record changes after update (added lines)
              $this->recordTdsChanges($originalValues, $getTdsSettingData, $user);

              return $this->successResponse(
                $getTdsSettingData,
                "Updated TDS Setting Successfully"
              );


        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }


    private function recordTdsChanges($originalValues, $updatedData, $user)
    {
        $changes = [];
        $fieldsToIgnore = ['updated_at', 'created_at']; // Fields to exclude from tracking
        
        foreach ($updatedData->getAttributes() as $field => $newValue) {
            // Skip ignored fields
            if (in_array($field, $fieldsToIgnore)) {
                continue;
            }
            
            $oldValue = $originalValues[$field] ?? null;
            
            // Only track if value actually changed
            if ($oldValue != $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }

        if (!empty($changes)) {
            foreach ($changes as $field => $change) {
                UpdateSystemSetupHistory::create([
                    'employee_id' => $user->id,
                    'updated_by' => $user->id,
                    'date' => now()->format('Y-m-d'),
                    'time' => now()->format('H:i:s'),
                    'notes' => 'TDS Setting Updated - ' . $this->formatFieldName($field),
                    'changed' => $this->formatChangeDescription($field, $change['old'], $change['new']),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    private function formatFieldName($field)
    {
        return ucwords(str_replace('_', ' ', $field));
    }

    private function formatChangeDescription($field, $oldValue, $newValue)
    {
        $fieldName = $this->formatFieldName($field);
        $old = is_null($oldValue) ? 'empty' : $oldValue;
        $new = is_null($newValue) ? 'empty' : $newValue;
        
        return "$fieldName changed from $old to $new";
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */



      /**
     * @OA\Delete(
     *     path="/uc/api/salary_setting/tds_setting/destroy/{id}",
     *     operationId="deletetds_setting",
     *     tags={"Salary Setting"},
     *     summary="Delete TDS Setting Request",
     *     security={ {"Bearer": {} }},
     *     description="Delete TDS Setting Request",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="TDS Setting deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="TDS Setting deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource Not Found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="TDS Setting not found.")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
              $findTDSSetting = TdsSetting::find($id);

              if (!$findTDSSetting) {
                  return $this->errorResponse("the given data is not found");
              }

              // Record deletion history (added line)
              $this->recordTdsDeletion($id, auth('sanctum')->user());

              $findTDSSetting->delete();

              return $this->successResponse(
                [],
                "TDS Deleted Successfully"
              );

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

    // New private function added
    private function recordTdsDeletion($tdsId, $user)
    {
        UpdateSystemSetupHistory::create([
            'employee_id' => $user->id,
            'updated_by' => $user->id,
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'notes' => 'TDS Setting Deleted',
            'changed' => 'Deleted TDS setting ID: '.$tdsId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }





}
