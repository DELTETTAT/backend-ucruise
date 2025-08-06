<?php

namespace App\Http\Controllers\Api\SalarySetting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SalarySetting;
use App\Models\UpdateSystemSetupHistory;

class SalarySettingController extends Controller
{

    /**
     * @OA\Post(
     *     path="/uc/api/salary_setting/salary_setting/store",
     *     summary="Create or update salary setting",
     *     tags={"Salary Setting"},
     *     operationId="storeSalarySetting",
     *     security={{"Bearer":{}}},
     *      @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               @OA\Property(property="notice_period_days", type="integer", description="Number of days for notice period after employee resignation"),
     *               @OA\Property(property="salary_process_after_in_days", type="integer", description="Number of days after notice period completion to process salary"),
     *               @OA\Property(property="clear_salary", type="integer", description="Salary is cleared every month if set, 1 for clear, 0 for not clear"),
     *               @OA\Property(property="hold_one_month_salary", type="integer", description="0 = hold one month salary, 1 = process salary without hold"),
     *               @OA\Property(property="clear_salary_after_notice", type="integer", description="0 = Do not clear salary after notice, 1 = Clear salary after notice"),
     *               @OA\Property(property="salary_status_for_month_hours", type="integer", description="0 = Salary calculated on monthly basis, 1 = Based on working hours"),
     *            ),
     *        ),
     *    ),
     *     @OA\Response(
     *          response=201,
     *          description="Salary setting data Get successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Salary setting data Get successfully",
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
        // Validate request
        $validated = $request->validate([
            'notice_period_days' => 'nullable|integer',
            'salary_process_after_in_days' => 'nullable|integer',
            'clear_salary' => 'nullable|integer|in:0,1', // every month paid salary
            'hold_one_month_salary' => 'nullable|in:0,1', // 0 = on hold, 1 = process
            'clear_salary_after_notice' => 'nullable|in:0,1', // 0 = no, 1 = yes
            'salary_status_for_month_hours' => 'nullable|in:0,1', // 0 = month, 1 = hours
        ]);

        // Always update the first (or only) record
        $salarySetting = SalarySetting::first();
        $user = auth('sanctum')->user();
        if ($salarySetting) {
            // Log individual field changes
            foreach ($validated as $field => $newValue) {
                $oldValue = $salarySetting->$field;
                $this->trackSalarySettingChange($field, $oldValue, $newValue);
            }

            $salarySetting->update($validated);
           // $message = "Salary Setting Updated Successfully";
        } else {
            foreach ($validated as $field => $value) {
                $this->trackSalarySettingChange($field, null, $value, 'Salary Setting Created');
            }
            $salarySetting = SalarySetting::create($validated);
            // $message = "Salary Setting Created Successfully";
        }

        // Return JSON Response
        return response()->json([
            'success' => true,
            'data' => $salarySetting,
            'message' => 'Salary setting saved successfully.'
        ], 201);
    }

    private function trackSalarySettingChange($field, $oldValue, $newValue, $note = null)
{
    if ($oldValue == $newValue) {
        return;
    }

    // Friendly field labels
    $fieldLabels = [
        'notice_period_days' => 'Notice Period Days',
        'salary_process_after_in_days' => 'Salary Process After (Days)',
        'clear_salary' => 'Clear Salary',
        'hold_one_month_salary' => 'Hold One Month Salary',
        'clear_salary_after_notice' => 'Clear Salary After Notice',
        'salary_status_for_month_hours' => 'Salary Type (Month/Hours)',
    ];

    // Human-readable boolean value labels per field
    $valueLabels = [
        'hold_one_month_salary' => [0 => 'On Hold', 1 => 'Processed'],
        'clear_salary_after_notice' => [0 => 'Not Cleared', 1 => 'Cleared'],
        'salary_status_for_month_hours' => [0 => 'Month', 1 => 'Hours'],
    ];

    $fieldName = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));

    // Format values using labels if applicable
    if (array_key_exists($field, $valueLabels)) {
        $oldValue = $valueLabels[$field][$oldValue] ?? $oldValue;
        $newValue = $valueLabels[$field][$newValue] ?? $newValue;
    }

    $oldValueFormatted = is_null($oldValue) ? 'empty' : $oldValue;
    $newValueFormatted = is_null($newValue) ? 'empty' : $newValue;

    $changedDescription = "$fieldName changed from '$oldValueFormatted' to '$newValueFormatted'";

    try {
        UpdateSystemSetupHistory::create([
            'employee_id' => auth('sanctum')->user()->id,
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'updated_by' => auth('sanctum')->user()->id,
            'notes' => $note ?? "Salary Setting Updated - $fieldName",
            'changed' => $changedDescription,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    } catch (\Exception $e) {
        \Log::error('Failed to record salary setting history: ' . $e->getMessage());
    }
}



    /**
     * @OA\Get(
     *     path="/uc/api/salary_setting/salary_setting/show",
     *     summary="Get the current salary setting",
     *     tags={"Salary Setting"},
     *     operationId="showSalarySetting",
     *     security={{"Bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Salary setting fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Salary setting fetched successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Salary setting not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Salary setting not found.")
     *         )
     *     )
     * )
     */
    public function show()
    {
        $salarySetting = SalarySetting::first();
        if ($salarySetting) {
            return response()->json([
                'success' => true,
                'data' => $salarySetting,
                'message' => 'Salary setting fetched successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Salary setting not found.'
            ], 404);
        }
    }
}
