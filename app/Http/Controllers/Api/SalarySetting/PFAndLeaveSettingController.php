<?php

namespace App\Http\Controllers\Api\SalarySetting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PfAndLeaveSetting;
use App\Http\Requests\PFSettingRequest;
use App\Models\PayrollSchedule;
use App\Models\UpdateSystemSetupHistory;

class PFAndLeaveSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     * path="/uc/api/salary_setting/pf_setting/index",
     * operationId="get PF setting data",
     * tags={"Salary Setting"},
     * summary="getting PF setting data",
     *   security={ {"Bearer": {} }},
     * description="getting PF setting data",
     *      @OA\Response(
     *          response=201,
     *          description="PF setting data Get successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="PF setting data Get successfully",
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
           $get_pf_data =  PfAndLeaveSetting::get();

           return $this->successResponse(
            $get_pf_data,
            "PF Setting Data"
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
     *     path="/uc/api/salary_setting/pf_setting/store",
     *     operationId="storePfAndLeaveSetting",
     *     tags={"Salary Setting"},
     *     summary="Store PF and Leave Settings",
     *     security={ {"Bearer": {}} },
     *     description="Store PF and Leave related settings such as PF enable, leave deduction, late day configuration, and deduction value.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"pf_enabled", "pf_type", "pf_value", "leave_deduction_enabled"},
     *                 @OA\Property(
     *                     property="pf_enabled",
     *                     type="boolean",
     *                     description="Enable or disable PF"
     *                 ),
     *                 @OA\Property(
     *                     property="pf_type",
     *                     type="string",
     *                     description="PF Type (Percentage or Fixed)",
     *                     enum={"Percentage", "Fixed"}
     *                 ),
     *                 @OA\Property(
     *                     property="pf_value",
     *                     type="string",
     *                     description="PF value (e.g., 10% or fixed amount)"
     *                 ),
     *                 @OA\Property(
     *                     property="leave_deduction_enabled",
     *                     type="boolean",
     *                     description="Enable or disable leave deduction"
     *                 ),
     *                 @OA\Property(
     *                     property="medical_half",
     *                     type="integer",
     *                     description="Medical allowed half-day leaves"
     *                 ),
     *                 @OA\Property(
     *                     property="medical_full",
     *                     type="integer",
     *                     description="Medical allowed full-day leaves"
     *                 ),
     *                 @OA\Property(
     *                     property="casual_half",
     *                     type="integer",
     *                     description="Medical allowed full-day leaves"
     *                 ),
     *                  @OA\Property(
     *                     property="casual_full",
     *                     type="integer",
     *                     description="Medical allowed full-day leaves"
     *                 ),
     *                  @OA\Property(
     *                     property="maternity_half",
     *                     type="integer",
     *                     description="Medical allowed full-day leaves"
     *                 ),
     *                  @OA\Property(
     *                     property="maternity_full",
     *                     type="integer",
     *                     description="Medical allowed full-day leaves"
     *                 ),
     *                  @OA\Property(
     *                     property="bereavement_half",
     *                     type="integer",
     *                     description="Medical allowed full-day leaves"
     *                 ),
     *                  @OA\Property(
     *                     property="bereavement_full",
     *                     type="integer",
     *                     description="Medical allowed full-day leaves"
     *                 ),
     *                  @OA\Property(
     *                     property="wedding_half",
     *                     type="integer",
     *                     description="Medical allowed full-day leaves"
     *                 ),
     *                  @OA\Property(
     *                     property="wedding_full",
     *                     type="integer",
     *                     description="Medical allowed full-day leaves"
     *                 ),
     *                  @OA\Property(
     *                     property="paternity_half",
     *                     type="integer",
     *                     description="Medical allowed full-day leaves"
     *                 ),
     *                  @OA\Property(
     *                     property="paternity_full",
     *                     type="integer",
     *                     description="Medical allowed full-day leaves"
     *                 ),
     *
     *                 @OA\Property(
     *                     property="late_day_count_enabled",
     *                     type="boolean",
     *                     description="Enable or disable late day leave count"
     *                 ),
     *                 @OA\Property(
     *                     property="late_day_max",
     *                     type="integer",
     *                     description="Maximum count of late days"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="PF and Leave Settings Stored Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Failed",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource Not Found"
     *     )
     * )
     */
    public function store(PFSettingRequest $request)
    {

        try {
            $validatedData = $request->validated();
            $user = auth('sanctum')->user();

            // Check if a record already exists (modify condition if it's per user/company)
            $pfSetting = PfAndLeaveSetting::first();

            $leave_deduction = [
                'medical_leave' => [
                    'half' => $validatedData['medical_half'],
                    'full' => $validatedData['medical_full'],
                ],
                'casual_leave' => [
                    'half' => $validatedData['casual_half'],
                    'full' => $validatedData['casual_full'],
                ],
                'maternity_leave' => [
                    'half' => $validatedData['maternity_half'],
                    'full' => $validatedData['maternity_full'],
                ],
                'bereavement_leave' => [
                    'half' => $validatedData['bereavement_half'],
                    'full' => $validatedData['bereavement_full'],
                ],
                'wedding_leave' => [
                    'half' => $validatedData['wedding_half'],
                    'full' => $validatedData['wedding_full'],
                ],
                'paternity_leave' => [
                    'half' => $validatedData['paternity_half'],
                    'full' => $validatedData['paternity_full'],
                ],
            ];

            $validatedData['leave_deduction'] = $leave_deduction;

            // if ($pfSetting) {
            //     $pfSetting->update($validatedData);
            //     $message = "PF Setting Updated Successfully";
            // } else {
            //     PfAndLeaveSetting::create($validatedData);
            //     $message = "PF Setting Stored Successfully";
            // }
             if ($pfSetting) {
            $originalValues = $pfSetting->toArray();
            $originalLeaveDeduction = $pfSetting->leave_deduction;
            $changedFields = [];

            // Check regular fields (non-leave deduction)
            foreach ($validatedData as $field => $newValue) {
                if ($field !== 'leave_deduction' && isset($originalValues[$field])) {
                    if ($originalValues[$field] != $newValue) {
                        $changedFields[$field] = [
                            'old' => $originalValues[$field],
                            'new' => $newValue
                        ];
                    }
                }
            }

            // Check leave deduction sub-fields
            foreach ($leave_deduction as $leaveType => $values) {
                foreach ($values as $subType => $newValue) {
                    if (isset($originalLeaveDeduction[$leaveType][$subType])) {
                        if ($originalLeaveDeduction[$leaveType][$subType] != $newValue) {
                            $fieldName = $leaveType.'_'.$subType;
                            $changedFields[$fieldName] = [
                                'old' => $originalLeaveDeduction[$leaveType][$subType],
                                'new' => $newValue
                            ];
                        }
                    }
                }
            }

            if (!empty($changedFields)) {
            $pfSetting->update($validatedData);
            $message = "PF Setting Updated Successfully";

            foreach ($changedFields as $field => $values) {
                UpdateSystemSetupHistory::create([
                    'employee_id' => $user->id,
                    'date' => now()->format('Y-m-d'),
                    'time' => now()->format('H:i:s'),
                    'updated_by' => $user->id,
                    'notes' => 'PF Setting Updated - '.ucwords(str_replace('_', ' ', $field)),
                    'changed' => ucwords(str_replace('_', ' ', $field))." changed from {$values['old']} to {$values['new']}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        } else {
            $message = "No changes detected in PF Settings.";
        }

        } else {
            PfAndLeaveSetting::create($validatedData);
            $message = "PF Setting Stored Successfully";
        }

            return $this->successResponse([], $message);

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





     /**
     * @OA\Post(
     * path="/uc/api/salary_setting/pf_setting/PayrollScheduleSetting",
     * operationId="store  payroll_schedule",
     * tags={"Salary Setting"},
     * summary="store payroll_schedule ",
     *   security={ {"Bearer": {} }},
     *    description="store payroll_schedule ",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               @OA\Property(property="actual_days_in_month", type="boolean", description="dgdgg"),
     *               @OA\Property(property="working_times_hours_in_month", type="boolean", description="define TAN number"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Payroll Schedule Setting Updated successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Payroll Schedule Setting Updated successfully",
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


     public function PayrollScheduleSetting(Request $request)
     {
           try {

             $validatedData = $request->validate([
                 'actual_days_in_month' => 'required|boolean',
                 'working_times_hours_in_month' => 'required|boolean',
             ]);

             $user = auth('sanctum')->user();
             $PayrollSchedule = PayrollSchedule::first();

             if ($PayrollSchedule) {
                // Record update history
                $this->recordPayrollScheduleChange($PayrollSchedule, $validatedData, $user, 'updated');
                $PayrollSchedule->update($validatedData);
                $message = "Payroll Schedule Setting Updated Successfully";
             } else {
                $PayrollSchedule = PayrollSchedule::create($validatedData);
                // Record creation history
                $this->recordPayrollScheduleChange($PayrollSchedule, $validatedData, $user, 'created');
                $message = "Payroll Schedule Setting Created Successfully";
             }

             return $this->successResponse([], $message);

           } catch (\Throwable $th) {
              return $this->errorResponse($th->getMessage());
           }
     }

     private function recordPayrollScheduleChange($payrollSchedule, $newData, $user, $action)
{
    if (!$user) return;

    $changes = [];
    $originalValues = $payrollSchedule->exists ? $payrollSchedule->getOriginal() : null;

    foreach ($newData as $field => $newValue) {
        $oldValue = $originalValues[$field] ?? null;
        
        if (!$originalValues || $oldValue != $newValue) {
            $fieldName = $this->formatFieldName($field);
            
            if ($action === 'created') {
                $changes[] = "{$fieldName} set to: " . $this->getBooleanDisplay($newValue);
            } else {
                $changes[] = "{$fieldName} changed from " . 
                            $this->getBooleanDisplay($oldValue) . " to " . 
                            $this->getBooleanDisplay($newValue);
            }
        }
    }

    if (!empty($changes)) {
        UpdateSystemSetupHistory::create([
            'employee_id' => $user->id,
            'updated_by' => $user->id,
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'notes' => 'Payroll Schedule ' . ucfirst($action),
            'changed' => implode('; ', $changes),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}

private function formatFieldName($field)
{
    return ucwords(str_replace('_', ' ', $field));
}

private function getBooleanDisplay($value)
{
    return $value ? 'Enabled' : 'Disabled';
}




    /**
     * @OA\Get(
     * path="/uc/api/salary_setting/pf_setting/getPayrollScheduleSetting",
     * operationId="getPayrollScheduleSetting",
     * tags={"Salary Setting"},
     * summary="getting PF setting data",
     *   security={ {"Bearer": {} }},
     * description="getting PF setting data",
     *      @OA\Response(
     *          response=201,
     *          description="Payroll schedule setting data Get successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Payroll schedule setting data Get successfully",
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
    public function getPayrollScheduleSetting()
    {
         try {
            $PayrollSchedule = PayrollSchedule::get();

           return $this->successResponse(
            $PayrollSchedule,
            "Payroll Schedule Data"
           );

         } catch (\Throwable $th) {
             return $this->errorResponse($th->getMessage());
         }
    }



     /**
     * @OA\Get(
     * path="/uc/api/salary_setting/pf_setting/leavetpye",
     * operationId="get leave type list",
     * tags={"Salary Setting"},
     * summary="getting leave type list",
     *   security={ {"Bearer": {} }},
     * description="getting leave type list",
     *      @OA\Response(
     *          response=201,
     *          description="leave type list Get successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="leave type list Get successfully",
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
    public function leavetpye()
    {
         try {

            $data = [
                'medical_leave',
                'casual_leave',
                'maternity_leave',
                'bereavement_leave',
                'wedding_leave',
                'paternity_leave',
            ];


           return $this->successResponse(
            $data,
            "Leave Type List"
           );

         } catch (\Throwable $th) {
             return $this->errorResponse($th->getMessage());
         }
    }


}
