<?php

namespace App\Http\Controllers\Api\SalarySetting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TaxInformation;
use App\Models\PayrollSchedule;
use App\Models\UpdateSystemSetupHistory;
use Illuminate\Support\Facades\DB;


class TaxInformationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


     /**
     * @OA\Get(
     * path="/uc/api/salary_setting/tax_infomation/index",
     * operationId="get tax information",
     * tags={"Salary Setting"},
     * summary="getting tax information",
     *   security={ {"Bearer": {} }},
     * description="getting tax information",
     *      @OA\Response(
     *          response=201,
     *          description="Tax Information Get successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Tax Information Get successfully",
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

             $taxInformation = TaxInformation::get();

             return $this->successResponse(
                $taxInformation,
                "Tax Information"
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

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


     /**
     * @OA\Post(
     * path="/uc/api/salary_setting/tax_infomation/store",
     * operationId="store tax information",
     * tags={"Salary Setting"},
     * summary="store tax information",
     *   security={ {"Bearer": {} }},
     *    description="store tax information",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               @OA\Property(property="pan", type="string", description="define PAN number"),
     *               @OA\Property(property="tan", type="string", description="define TAN number"),
     *               @OA\Property(property="tds_circle_code", type="string", description="define TDS circle code"),
     *              @OA\Property(
     *                 property="tax_payment_frequency",
     *                 type="string",
     *                 description="define tax payment frequency",
     *                 enum={"Monthly", "Quarterly", "Yearly"},
     *                 default="Monthly"
     *             ),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Role Updated successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Role Updated successfully",
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
                'pan' => 'required|string|max:10',
                'tan' => 'required|string|max:10',
                'tds_circle_code' => 'nullable|string',
                'tax_payment_frequency' => 'nullable|in:Monthly,Quarterly,Yearly',
            ]);

            $TaxInformation = TaxInformation::first();

            $user = auth('sanctum')->user();

            if ($TaxInformation) {

                // Track changes for each field
                foreach ($validatedData as $field => $newValue) {
                    $oldValue = $TaxInformation->$field;
                    if ($oldValue != $newValue) {
                        $this->trackTaxInfoChange($field, $oldValue, $newValue);
                    }
                }

                $TaxInformation->update($validatedData);
                $message = "Tax Information Updated Successfully";                
            } else {

                // Track creation of all fields
                foreach ($validatedData as $field => $value) {
                    $this->trackTaxInfoChange($field, null, $value, 'Tax Information Created');
                }

                TaxInformation::create($validatedData);
                $message = "Tax Information Created Successfully";
            }

            return $this->successResponse([], $message);

          } catch (\Throwable $th) {
             return $this->errorResponse($th->getMessage());
          }
    }

    /**
     * Log changes to tax info in history table
     */
    private function trackTaxInfoChange($field, $oldValue, $newValue, $note = null)
    {
        // Skip if values are the same
        if ($oldValue == $newValue) {
            return;
        }

        // Format field name for display
        $fieldName = ucfirst(str_replace('_', ' ', $field));

        // Format null/empty values
        $oldValue = is_null($oldValue) ? 'empty' : $oldValue;
        $newValue = is_null($newValue) ? 'empty' : $newValue;

        $changedDescription = "$fieldName changed from '$oldValue' to '$newValue'";

        try {
            UpdateSystemSetupHistory::create([
                'employee_id' => auth('sanctum')->user()->id,
                'date' => now()->format('Y-m-d'),
                'time' => now()->format('H:i:s'),
                'updated_by' => auth('sanctum')->user()->id,
                'notes' => $note ?? 'Tax info updated',
                'changed' => $changedDescription,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to record tax info change history: ' . $e->getMessage());
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
