<?php

namespace App\Http\Controllers\Api\EmployeeSalary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubUser;
use Validator;
use Carbon\Carbon;
use App\Models\EmployeeSalary;
use App\Models\HrmsCalenderAttendance;
use App\Models\EmployeeAttendance;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\HrmsTimeAndShift;
use App\Models\UpdateEmployeeHistory;
use DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use App\Models\TeamManager;
use App\Models\EmployeesUnderOfManager;
use App\Models\HRMSTEAM;
class EployeeSalaryCalculation extends Controller
{
    /**
     * @OA\Post(
     *     path="/uc/api/salary_calculation/calculate_salary",
     *     operationId="calculateSalary",
     *     tags={"Salary Calculation"},
     *     summary="Calculate salary based on EPF type",
     *     security={{"Bearer": {}}},
     *     description="Pass salary and EPF condition (1=Deducted by employee, 2=Employer pays both, 3=EPF not applicable)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"salary", "epf_type"},
     *                 @OA\Property(property="salary", type="number", format="float", example=20000, description="Gross salary"),
     *                 @OA\Property(property="epf_type", type="integer", enum={1,2,3}, example=1, description="EPF option: 1=Deducted by employee, 2=Employer pays both, 3=Not applicable")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Salary calculation successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="basic", type="number", example=10500),
     *             @OA\Property(property="hra", type="number", example=5500),
     *             @OA\Property(property="medical", type="number", example=2100),
     *             @OA\Property(property="conveyance", type="number", example=1500),
     *             @OA\Property(property="bonus", type="number", example=400),
     *             @OA\Property(property="gross_salary", type="number", example=20000),
     *             @OA\Property(property="professional_tax", type="number", example=200),
     *             @OA\Property(property="epf_employee", type="number", example=1250),
     *             @OA\Property(property="esi_employee", type="number", example=150),
     *             @OA\Property(property="take_home", type="number", example=18400),
     *             @OA\Property(property="epf_employer", type="number", example=1250),
     *             @OA\Property(property="esi_employer", type="number", example=400),
     *             @OA\Property(property="total_package_salary", type="number", example=21650)
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid EPF option"),
     *     @OA\Response(response=422, description="Validation Error"),
     *     @OA\Response(response=404, description="Resource Not Found")
     * )
     */



    public function calculate(Request $request)
    {
        try {
            // Validate incoming request
            $validated = $request->validate([
                'salary' => 'required|numeric|min:1',
                'epf_type' => 'required|in:1,2,3'
            ]);

            // Extract validated inputs
            $salary = $validated['salary'];
            $epfType = $validated['epf_type'];

            // Perform salary calculation
            $calculatedSalary = $this->insideCalculate($salary, $epfType);

            return response()->json([
                'status' => true,
                'message' => 'Salary calculated successfully.',
                'data' => $calculatedSalary
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while calculating salary.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



     /**
     * @OA\Post(
     *     path="/uc/api/salary_calculation/fetchUser_salary",
     *     operationId="fetchUserSalary",
     *     tags={"Salary Calculation"},
     *     summary="send employee salary as per epmlouee id",
     *     security={{"Bearer": {}}},
     *     description="Pass employee id ",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"employee_id"},
     *                 @OA\Property(property="employee_id", type="number", description="employee id"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Salary fetched successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="basic", type="number", example=10500),
     *             @OA\Property(property="hra", type="number", example=5500),
     *             @OA\Property(property="medical", type="number", example=2100),
     *             @OA\Property(property="conveyance", type="number", example=1500),
     *             @OA\Property(property="bonus", type="number", example=400),
     *             @OA\Property(property="gross_salary", type="number", example=20000),
     *             @OA\Property(property="professional_tax", type="number", example=200),
     *             @OA\Property(property="epf_employee", type="number", example=1250),
     *             @OA\Property(property="esi_employee", type="number", example=150),
     *             @OA\Property(property="take_home", type="number", example=18400),
     *             @OA\Property(property="epf_employer", type="number", example=1250),
     *             @OA\Property(property="esi_employer", type="number", example=400),
     *             @OA\Property(property="total_package_salary", type="number", example=21650)
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid EPF option"),
     *     @OA\Response(response=422, description="Validation Error"),
     *     @OA\Response(response=404, description="Resource Not Found")
     * )
     */

        public function fetchUserSalary(Request $request){

                $validator = Validator::make($request->all(), [
                    'employee_id' => 'required|exists:sub_users,id',

                ]);

                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }

                $employee_id = $request->input('employee_id');

                // $employee = SubUser::where('id', $employee_id)->with('salary')->first();

                $employee = SubUser::where('id', $employee_id)
                ->with(['salary' => function ($query) {
                    $query->where('is_active', 1);
                }])
                ->first();


                if (!$employee && $employee->salary == null) {
                    return response()->json(['error' => 'Employee not found'], 404);
                }

                $slararies = $employee?->salary ?? [];


                return $this->successResponse( $slararies , "Employee salaries fetched Successfully");


                return response()->json([
                    'basic' => $basic,
                    'hra' => $hra,
                    'medical' => $medical,
                    'conveyance' => $conveyance,
                    'bonus' => $bonus,
                    'gross_salary' => $gross,
                    'professional_tax' => $professional_tax,
                    'epf_employee' => $epf_employee,
                    'esi_employee' => $esi_employee,
                    'take_home' => $take_home,
                    'epf_employer' => $epf_employer,
                    'esi_employer' => $esi_employer,
                    'total_package_salary' => $total_package,
                ]);
        }


     // function for add new increment

           /**
             * @OA\Post(
             *     path="/uc/api/salary_calculation/add_user_increment",
             *     operationId="addUserIncrement",
             *     tags={"Salary Calculation"},
             *     summary="Add a new salary increment for an employee",
             *     security={{"Bearer": {}}},
             *     description="Creates a new salary record for the employee with breakdown and deactivates the previous one.
             *                  Prevents adding overlapping or duplicate date ranges for salary increments.",
             *     @OA\RequestBody(
             *         required=true,
             *         @OA\MediaType(
             *             mediaType="multipart/form-data",
             *             @OA\Schema(
             *                 type="object",
             *                 required={
             *                     "employee_id",
             *                     "increment_from_date",
             *                     "increment_to_date",
             *                     "new_salary",
             *                     "pf_type"
             *                 },
             *                 @OA\Property(
             *                     property="employee_id",
             *                     type="integer",
             *                     example=2596,
             *                     description="ID of the employee (must exist in sub_users table)"
             *                 ),
             *                 @OA\Property(
             *                     property="increment_from_date",
             *                     type="string",
             *                     format="date",
             *                     example="2025-07-01",
             *                     description="Start date for the increment (no overlaps allowed)"
             *                 ),
             *                 @OA\Property(
             *                     property="increment_to_date",
             *                     type="string",
             *                     format="date",
             *                     example="2026-06-30",
             *                     description="End date for the increment (no overlaps allowed)"
             *                 ),
             *                 @OA\Property(
             *                     property="new_salary",
             *                     type="number",
             *                     format="float",
             *                     example=35000,
             *                     description="New gross salary after increment"
             *                 ),
             *                 @OA\Property(
             *                     property="increment_reason",
             *                     type="string",
             *                     maxLength=255,
             *                     example="Annual Performance Review",
             *                     description="Reason for the increment (optional)"
             *                 ),
             *                 @OA\Property(
             *                     property="pf_type",
             *                     type="integer",
             *                     enum={1,2,3},
             *                     example=1,
             *                     description="EPF type: 1=Employee Deducted, 2=Employer Pays Both, 3=Not Applicable"
             *                 )
             *             )
             *         )
             *     ),
             *     @OA\Response(
             *         response=201,
             *         description="User increment added successfully.",
             *         @OA\JsonContent(
             *             @OA\Property(property="status", type="boolean", example=true),
             *             @OA\Property(property="message", type="string", example="User increment added successfully."),
             *             @OA\Property(
             *                 property="data",
             *                 type="object",
             *                 @OA\Property(property="basic", type="number", example=15000),
             *                 @OA\Property(property="hra", type="number", example=8000),
             *                 @OA\Property(property="medical", type="number", example=1250),
             *                 @OA\Property(property="conveyance", type="number", example=4000),
             *                 @OA\Property(property="bonus", type="number", example=2750),
             *                 @OA\Property(property="gross_salary", type="number", example=35000),
             *                 @OA\Property(property="professional_tax", type="number", example=200),
             *                 @OA\Property(property="epf_employee", type="number", example=1800),
             *                 @OA\Property(property="esi_employee", type="number", example=0),
             *                 @OA\Property(property="take_home", type="number", example=31500),
             *                 @OA\Property(property="epf_employer", type="number", example=1800),
             *                 @OA\Property(property="esi_employer", type="number", example=0),
             *                 @OA\Property(property="total_package_salary", type="number", example=38600)
             *             )
             *         )
             *     ),
             *     @OA\Response(
             *         response=409,
             *         description="Another salary record already exists in this date range.",
             *         @OA\JsonContent(
             *             @OA\Property(property="status", type="boolean", example=false),
             *             @OA\Property(property="message", type="string", example="Another salary record already exists in this date range.")
             *         )
             *     ),
             *     @OA\Response(
             *         response=422,
             *         description="Validation failed",
             *         @OA\JsonContent(
             *             @OA\Property(property="status", type="boolean", example=false),
             *             @OA\Property(property="message", type="string", example="Validation failed."),
             *             @OA\Property(property="errors", type="object")
             *         )
             *     ),
             *     @OA\Response(
             *         response=500,
             *         description="Error occurred while adding increment",
             *         @OA\JsonContent(
             *             @OA\Property(property="status", type="boolean", example=false),
             *             @OA\Property(property="message", type="string", example="Error occurred while adding increment."),
             *             @OA\Property(property="error", type="string", example="SQLSTATE[...]: ...")
             *         )
             *     )
             * )
             */

        public function addUserIncrement(Request $request)
        {
            try {
                $validator = Validator::make($request->all(), [
                    'employee_id' => 'required|exists:sub_users,id',
                    'pf_type' => 'required|in:1,2,3',
                    'increment_to_date' => 'required|date|date_format:Y-m-d|after:increment_from_date',
                    'increment_from_date' => 'required|date|date_format:Y-m-d',
                    'new_salary' => 'required|numeric|min:1',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Validation failed.',
                        'errors' => $validator->errors()
                    ], 422);
                }

                $employeeId = $request->employee_id;
                $newSalary = $request->new_salary;
                $incrementDateTo = $request->increment_to_date;
                $incrementDateFrom = $request->increment_from_date;
                $reason = $request->increment_reason ?? null;
                $pfType = $request->pf_type;

                // 🚫 Check for overlapping date ranges
                // $overlappingRecord = EmployeeSalary::where('employee_id', $employeeId)
                //     ->where(function ($query) use ($incrementDateFrom, $incrementDateTo) {
                //         $query->whereBetween('increment_from_date', [$incrementDateFrom, $incrementDateTo])
                //             ->orWhereBetween('increment_to_date', [$incrementDateFrom, $incrementDateTo])
                //             ->orWhere(function ($q) use ($incrementDateFrom, $incrementDateTo) {
                //                 $q->where('increment_from_date', '<=', $incrementDateFrom)
                //                 ->where('increment_to_date', '>=', $incrementDateTo);
                //             });
                //     })
                //     ->first();

                // if ($overlappingRecord) {
                //     return response()->json([
                //         'status' => false,
                //         'message' => 'Another salary record already exists in this date range.',
                //     ], 409);
                // }

                // Calculate new salary breakdown
                $breakdown = $this->insideCalculate($newSalary, $pfType);

                // Fetch the latest salary record for this employee
                $latestSalaryRecord = EmployeeSalary::where('employee_id', $employeeId)
                    ->orderBy('increment_from_date', 'desc')
                    ->first();

                $isActive = 0;

                // If new increment date is more recent than the latest, make it active
                if (!$latestSalaryRecord || $incrementDateFrom > $latestSalaryRecord->increment_from_date) {
                    $isActive = 1;

                    // Deactivate all existing salary records
                    EmployeeSalary::where('employee_id', $employeeId)->update(['is_active' => 0]);
                }

                // Save new salary record
                $newSalaryRecord = EmployeeSalary::create([
                    'employee_id' => $employeeId,
                    'basic' => $breakdown['basic'],
                    'hra' => $breakdown['hra'],
                    'medical' => $breakdown['medical'],
                    'conveyance' => $breakdown['conveyance'],
                    'bonus' => $breakdown['bonus'],
                    'gross_salary' => $breakdown['gross_salary'],
                    'professional_tax' => $breakdown['professional_tax'],
                    'epf_employee' => $breakdown['epf_employee'],
                    'esi_employee' => $breakdown['esi_employee'],
                    'take_home' => $breakdown['take_home'],
                    'epf_employer' => $breakdown['epf_employer'],
                    'esi_employer' => $breakdown['esi_employer'],
                    'total_package_salary' => $breakdown['total_package_salary'],
                    'increment_from_date' => $incrementDateFrom,
                    'increment_to_date' => $incrementDateTo,
                    'is_active' => $isActive,
                    'reason' => $reason
                ]);

                 $user = auth('sanctum')->user();
                UpdateEmployeeHistory::create([
                    'employee_id' => $employeeId,
                    'updated_by' => $user ? $user->id : null,
                    'date' => now()->format('Y-m-d'),
                    'time' => now()->format('H:i:s'),
                    'notes' => 'Salary Increment Added',
                    'changed' => "Salary Increment Details:\n"
                            . "Previous Salary: " . ($latestSalaryRecord ? number_format($latestSalaryRecord->gross_salary) : 'Not set') . "\n"
                            . "New Salary: " . number_format($breakdown['gross_salary']) . "\n"
                            . "Effective Date: " . $incrementDateFrom . " to " . $incrementDateTo . "\n"
                            . "Reason: " . ($reason ?? 'Not specified'),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'User increment added successfully.',
                    'data' => $breakdown
                ], 201);

            } catch (\Throwable $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error occurred while adding increment.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }



     // increment history for particular employee

       /**
         * @OA\Post(
         *     path="/uc/api/salary_calculation/increment_salaries_list",
         *     operationId="incrementSalariesList",
         *     tags={"Salary Calculation"},
         *     summary="Fetch increment history of an employee",
         *     security={{"Bearer": {}}},
         *     description="Returns a list of past increments for a given employee based on their salary history.",
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\MediaType(
         *             mediaType="multipart/form-data",
         *             @OA\Schema(
         *                 type="object",
         *                 required={"employee_id"},
         *                 @OA\Property(
         *                     property="employee_id",
         *                     type="integer",
         *                     example=2596,
         *                     description="Employee ID (must exist in sub_users table)"
         *                 )
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Increment history fetched successfully",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Increment history fetched successfully"),
         *             @OA\Property(
         *                 property="data",
         *                 type="array",
         *                 @OA\Items(
         *                     @OA\Property(property="date", type="string", example="02-07-2025"),
         *                     @OA\Property(property="previous_salary", type="number", example=30000),
         *                     @OA\Property(property="increment_percent", type="string", example="10%"),
         *                     @OA\Property(property="increment_amount", type="number", example=3000),
         *                     @OA\Property(property="new_salary", type="number", example=33000)
         *                 )
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=404,
         *         description="No salary history found for this employee."
         *     ),
         *     @OA\Response(
         *         response=422,
         *         description="Validation failed"
         *     )
         * )
         */


 

   public function incrementSalariesList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:sub_users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $employee_id = $request->input('employee_id');

        $salaries = EmployeeSalary::where('employee_id', $employee_id)
            ->orderBy('increment_from_date', 'asc')
            ->get();

        if ($salaries->isEmpty()) {
            return response()->json([
                'message' => 'No salary history found for this employee.',
                'data' => []
            ], 200);
        }

        $history = [];

        // First record (initial salary)
        $firstSalary = $salaries->first();
        $fromDate = \Carbon\Carbon::parse($firstSalary->increment_from_date);
        $toDate = \Carbon\Carbon::parse($firstSalary->increment_to_date);

        $history[] = [
            'from_date' => $fromDate->format('d-m-Y'),
            'to_date' => $toDate ? $toDate->format('d-m-Y') : null,
            'previous_salary' => null,
            'increment_percent' => null,
            'increment_amount' => null,
            'salary_id'=> $firstSalary->id,
            'new_salary' => (float)$firstSalary->gross_salary,
            'status' => 'initial',
            'salary_components' => [
                'basic' => (float)$firstSalary->basic,
                'hra' => (float)$firstSalary->hra,
                'medical' => (float)$firstSalary->medical,
                'conveyance' => (float)$firstSalary->conveyance,
                'bonus' => (float)$firstSalary->bonus,
                'gross_salary'=> (float)$firstSalary->gross_salary,
                'professional_tax' => (float)$firstSalary->professional_tax,
                'epf_employee' => (float)$firstSalary->epf_employee,
                'epf_employer' => (float)$firstSalary->epf_employer,
                'esi_employee'=> (float)$firstSalary->esi_employee,
                'esi_employer'=> (float)$firstSalary->esi_employer,
                'take_home' => (float)$firstSalary->take_home,
                'total_package' => (float)$firstSalary->total_package_salary
            ]
        ];

        // Subsequent records
        for ($i = 1; $i < count($salaries); $i++) {
            $prev = $salaries[$i - 1];
            $curr = $salaries[$i];

            $previous_salary = (float)$prev->gross_salary;
            $new_salary = (float)$curr->gross_salary;
            $increment_amount = $new_salary - $previous_salary;
            $increment_percent = $previous_salary > 0
                ? round(($increment_amount / $previous_salary) * 100, 2) . '%'
                : 'N/A';

            $fromDate = \Carbon\Carbon::parse($curr->increment_from_date);
            $toDate = \Carbon\Carbon::parse($curr->increment_to_date);

            $status = ($i == count($salaries) - 1) ? 'active' : 'completed';

            $history[] = [
                'from_date' => $fromDate->format('d-m-Y'),
                'to_date' => $toDate ? $toDate->format('d-m-Y') : null,
                'previous_salary' => $previous_salary,
                'increment_percent' => $increment_percent,
                'increment_amount' => $increment_amount,
                'salary_id'=> $curr->id,
                'new_salary' => $new_salary,
                'status' => $status,
                'salary_components' => [
                    'basic' => (float)$curr->basic,
                    'hra' => (float)$curr->hra,
                    'medical' => (float)$curr->medical,
                    'conveyance' => (float)$curr->conveyance,
                    'bonus' => (float)$curr->bonus,
                    'gross_salary'=> (float)$curr->gross_salary,
                    'professional_tax' => (float)$curr->professional_tax,
                    'epf_employee' => (float)$curr->epf_employee,
                    'epf_employer' => (float)$curr->epf_employer,
                    'esi_employee'=> (float)$curr->esi_employee,
                    'esi_employer'=> (float)$curr->esi_employer,
                    'take_home' => (float)$curr->take_home,
                    'total_package' => (float)$curr->total_package_salary,
                    // Include component-wise increments if needed
                    'basic_increment' => (float)$curr->basic - (float)$prev->basic,
                    'hra_increment' => (float)$curr->hra - (float)$prev->hra,
                    // Add other component increments as needed
                ]
            ];
        }

        // Sort history: active → completed → initial
        $sortedHistory = collect($history)
            ->sortBy(function ($item) {
                return match ($item['status']) {
                    'active' => 1,
                    'completed' => 2,
                    'initial' => 3,
                    default => 4
                };
            })
            ->values()
            ->toArray();

        return response()->json([
            'message' => 'Increment history with salary components fetched successfully',
            'data' => $sortedHistory
        ]);
    }




      // increment salary history along with salary calculation

       /**
         * @OA\Post(
         *     path="/uc/api/salary_calculation/increment_salaries_history_Breakdowns",
         *     operationId="incrementSalariesHistoryWithBreakdowns",
         *     tags={"Salary Calculation"},
         *     summary="Get salary increment history with breakdowns for an employee",
         *     security={{"Bearer": {}}},
         *     description="Fetches the salary increment history for a given employee, including salary breakdowns and status (active, completed, initial).",
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\MediaType(
         *             mediaType="application/json",
         *             @OA\Schema(
         *                 type="object",
         *                 required={"employee_id"},
         *                 @OA\Property(
         *                     property="employee_id",
         *                     type="integer",
         *                     example=2596,
         *                     description="ID of the employee (must exist in sub_users table)"
         *                 )
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Increment history fetched successfully",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Increment history fetched successfully"),
         *             @OA\Property(
         *                 property="data",
         *                 type="array",
         *                 @OA\Items(
         *                     type="object",
         *                     @OA\Property(property="from_date", type="string", example="01 Jun 2024"),
         *                     @OA\Property(property="to_date", type="string", example="01 Jun 2025"),
         *                     @OA\Property(property="previous_salary", type="number", example=30000),
         *                     @OA\Property(property="increment_percent", type="string", example="10%"),
         *                     @OA\Property(property="increment_amount", type="number", example=3000),
         *                     @OA\Property(property="new_salary", type="number", example=33000),
         *                     @OA\Property(property="status", type="string", example="active"),
         *                     @OA\Property(
         *                         property="breakdown",
         *                         type="object",
         *                         @OA\Property(property="basic", type="number", example=15000),
         *                         @OA\Property(property="hra", type="number", example=4500),
         *                         @OA\Property(property="medical", type="number", example=3000),
         *                         @OA\Property(property="conveyance", type="number", example=2000),
         *                         @OA\Property(property="bonus", type="number", example=6500),
         *                         @OA\Property(property="gross_salary", type="number", example=33000),
         *                         @OA\Property(property="professional_tax", type="number", example=200),
         *                         @OA\Property(property="epf_employee", type="number", example=1800),
         *                         @OA\Property(property="epf_employer", type="number", example=1800),
         *                         @OA\Property(property="esi_employee", type="number", example=250),
         *                         @OA\Property(property="esi_employer", type="number", example=1050),
         *                         @OA\Property(property="total_package", type="number", example=36800)
         *                     )
         *                 )
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=422,
         *         description="Validation failed",
         *         @OA\JsonContent(
         *             @OA\Property(property="status", type="boolean", example=false),
         *             @OA\Property(property="message", type="string", example="Validation failed."),
         *             @OA\Property(property="errors", type="object")
         *         )
         *     ),
         *     @OA\Response(
         *         response=404,
         *         description="No salary history found for this employee.",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="No salary history found for this employee."),
         *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
         *         )
         *     )
         * )
         */



    public function incrementSalariesHistoryWithBreakdowns(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:sub_users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $employee_id = $request->input('employee_id');

        //  Fetch employee details (correct field names)
        $employee = SubUser::where('id', $employee_id)
            ->select('first_name', 'middle_name', 'last_name', 'display_name', 'unique_id', 'employement_type')
            ->first();

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found',
                'data' => []
            ], 404);
        }

        //  Combine name fields
        $fullName = $employee->display_name
            ?? trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name}");

        //  Get salary records
        $salaries = EmployeeSalary::where('employee_id', $employee_id)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($salaries->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No salary history found for this employee.',
                'data' => []
            ], 404);
        }

        $history = [];

        for ($i = 1; $i < count($salaries); $i++) {
            $prev = $salaries[$i - 1];
            $curr = $salaries[$i];

            $previous_salary = (float) $prev->gross_salary;
            $new_salary = (float) $curr->gross_salary;
            $increment_amount = $new_salary - $previous_salary;
            $increment_percent = $previous_salary > 0 ? round(($increment_amount / $previous_salary) * 100, 2) . '%' : 'N/A';

            $fromDate = \Carbon\Carbon::parse($curr->created_at);
            $toDate = match (strtolower($curr->duration)) {
                'annual' => $fromDate->copy()->addMonths(12),
                'midyear' => $fromDate->copy()->addMonths(6),
                default => null
            };

            //  Determine status
            $status = match (true) {
                $i == count($salaries) - 1 => 'active',
                $i == 1 => 'initial',
                default => 'completed'
            };

            //  Use existing salary breakdown
            $breakdown = [
                'basic' => $curr->basic,
                'hra' => $curr->hra,
                'medical' => $curr->medical,
                'conveyance' => $curr->conveyance,
                'bonus' => $curr->bonus,
                'gross_salary' => $curr->gross_salary,
                'professional_tax' => $curr->professional_tax,
                'epf_employee' => $curr->epf_employee,
                'esi_employee' => $curr->esi_employee,
                'take_home' => $curr->take_home,
                'epf_employer' => $curr->epf_employer,
                'esi_employer' => $curr->esi_employer,
                'total_package_salary' => $curr->total_package_salary,
            ];

            $history[] = [
                'from_date' => $fromDate->format('d M Y'),
                'to_date' => $toDate ? $toDate->format('d M Y') : null,
                'previous_salary' => $previous_salary,
                'increment_percent' => $increment_percent,
                'increment_amount' => $increment_amount,
                'new_salary' => $new_salary,
                'status' => $status,
                'breakdown' => $breakdown,
            ];
        }

        //  Sort history: Active → Completed → Initial
        $historySorted = collect($history)->sortBy(function ($item) {
            return match ($item['status']) {
                'active' => 1,
                'completed' => 2,
                'initial' => 3,
                default => 4
            };
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Increment history fetched successfully',
            'employee_info' => [
                'name' => $fullName,
                'employee_id' => $employee->unique_id, // ✅ corrected
                'current_position' => $employee->employement_type,
                'current_salary' => $salaries->last()->gross_salary ?? 0,
            ],
            'data' => $historySorted
        ]);
    }


    // inside controller calculations

    public function insideCalculate($salary, $epfType)
    {
        try {
            // Basic input validations
            if (!is_numeric($salary) || $salary <= 0) {
                throw new \InvalidArgumentException("Salary must be a positive number.");
            }

            if (!in_array($epfType, [1, 2, 3])) {
                throw new \InvalidArgumentException("Invalid EPF type. Accepted values are 1, 2, or 3.");
            }

            // Basic calculation
            $basic = match (true) {
                $salary < 21000 => 10500,
                $salary < 25000 => 11500,
                $salary < 30000 => 12500,
                $salary < 33000 => 13500,
                $salary < 35000 => 14000,
                $salary < 40000 => 15000,
                default => $salary * 0.45
            };

            // HRA
            $hra = round(match (true) {
                $salary == 10500 => 0,
                $salary < 15001 => $salary - $basic,
                $salary < 30000 => $basic * 0.4,
                $salary < 50000 => $basic * 0.45,
                default => $basic * 0.5
            });

            // Medical Allowance
            $medical = round(match (true) {
                $salary > 18999.99 => $basic * 0.2,
                default => 0,
            });

            // Conveyance Allowance (strict order!)
            $conveyance = round(match (true) {
                $salary > 25000 => $basic * 0.15,
                $salary > 30000 => $basic * 0.3,
                $salary > 35000 => $basic * 0.4,
                default => 0
            });

            // BONUS = Total Salary - sum of other components
            $bonus = $salary - $basic - $hra - $medical - $conveyance;

            // GROSS = sum of all earnings
            $gross = $basic + $hra + $medical + $conveyance + $bonus;

            // Professional Tax
            $ptax = $salary > (250000 / 12) ? 200 : 0;

            // EPF Logic
            if ($epfType == 1) {
                $epf_employee = round(min($basic, 15000) * 0.12);
                $epf_employer = $epf_employee;
            } elseif ($epfType == 2) {
                $epf_employee = 0;
                $epf_employer = round(min($basic, 15000) * 0.12) * 2;
            } else {
                $epf_employee = 0;
                $epf_employer = 0;
            }

            // ESI Contributions
            $esi_employee = round($salary > 20999.99 ? 0 : $salary * 0.0075, 2);
            $esi_employer = round($salary > 20999.99 ? 0 : $salary * 0.0325, 2);

            // Net Salary
            $take_home = $gross - $epf_employee - $ptax - $esi_employee;

            // Total Package
            $total_package = $gross + $epf_employer + $esi_employer;

            // Return final salary structure
            return [
                'basic' => $basic,
                'hra' => $hra,
                'medical' => $medical,
                'conveyance' => $conveyance,
                'bonus' => $bonus,
                'gross_salary' => $gross,
                'professional_tax' => $ptax,
                'epf_employee' => $epf_employee,
                'esi_employee' => $esi_employee,
                'take_home' => $take_home,
                'epf_employer' => $epf_employer,
                'esi_employer' => $esi_employer,
                'total_package_salary' => $total_package,
            ];
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Salary calculation failed: ' . $e->getMessage()
            ], 400);
        }
    }


    // function for payroll summary

    /**
 * @OA\Post(
 *     path="/uc/api/salary_calculation/payroll_summary",
 *     operationId="payrollSummary",
 *     tags={"Salary Calculation"},
 *     summary="Get payroll summary for an employee (last month)",
 *     security={{"Bearer": {}}},
 *     description="Fetches payroll summary of the previous month including gross pay, deduction, leave count, status, and total pay.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"employee_id"},
 *                 @OA\Property(
 *                     property="employee_id",
 *                     type="integer",
 *                     example=2334,
 *                     description="Employee ID (must exist in sub_users)"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Payroll summary fetched successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Payroll summary fetched successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="payroll_period", type="string", example="Jun 01, 2025 - Jun 30, 2025"),
 *                 @OA\Property(property="total_leaves_taken", type="integer", example=2),
 *                 @OA\Property(property="pay_day", type="string", example="Jul 10, 2025"),
 *                 @OA\Property(property="status", type="string", example="Paid"),
 *                 @OA\Property(property="gross_pay", type="number", format="float", example=37000),
 *                 @OA\Property(property="deduction", type="number", format="float", example=2150.50),
 *                 @OA\Property(property="total_pay", type="number", format="float", example=34849.50)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation failed",
 *         @OA\JsonContent(
 *             @OA\Property(property="errors", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Attendance or salary record not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="No attendance data found")
 *         )
 *     )
 * )
 */



 public function payrollSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:sub_users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $employeeId = $request->employee_id;

        // Get last month
        $lastMonth = now()->subMonth()->format('Y-m'); // e.g. "2025-06"
        $anyDateLastMonth = $lastMonth . '-03'; // Arbitrary day in month for attendance

        // 1. Get Attendance
        $attendanceData = $this->getEmployeeMonthAttendence($employeeId, $anyDateLastMonth);
        $attendance = $attendanceData ?? null;

        if (!$attendance) {
            return response()->json(['message' => 'No attendance data found'], 200);
        }

        // 2. Get latest active salary
        $salaryRecord = EmployeeSalary::where('employee_id', $employeeId)
            ->where('is_active', 1)
            ->latest('created_at')
            ->first();

        if (!$salaryRecord) {
            return response()->json(['message' => 'Salary record not found'], 200);
        }


        //   $grossPay = (float)$salaryRecord->gross_salary;
        //     $epf_employee = $salaryRecord->epf_employee?$salaryRecord->epf_employee:0;
        //     $tax = ($grossPay > 21000) ? 200 : 0;
        //     $maindeduct=$tax+$epf_employee;
        //     $exactPayableAmount = $grossPay-$maindeduct;
        //     $bonus = 0;

        //     $workingDays = $attendanceData['working_days'] ?? 0;
        //     $monthDays =$attendanceData['total_days'];
        //     $perDayPay = $grossPay / max($monthDays, 1);

        // $needtocutout =  $attendanceData['unpaidleave']+$attendanceData['absent'];
        
        //     $absentDeduction = round($perDayPay * $needtocutout, 2);
        //     $govermenttax= $tax;
        //     $emppf= $epf_employee;
        //     $totalDeduction = $absentDeduction+ $govermenttax+$emppf;
        //     $totalPay = round($grossPay - $totalDeduction + $bonus, 2);

                   [
                        $grossPay,  
                        $epf_employee ,
                        $tax ,
                        $maindeduct,
                        $exactPayableAmount,
                        $bonus ,
                        $workingDays ,
                        $monthDays ,
                        $perDayPay ,
                        $needtocutout,
                        $absentDeduction ,
                        $govermenttax,
                        $emppf,
                        $totalDeduction,
                        $totalPay ,
                    ]= $this->basicSalaryCalculation($salaryRecord, $attendanceData);


        // 4. Payroll period (1st to last of previous month)
        $payrollStart = now()->subMonth()->startOfMonth()->format('M d, Y');
        $payrollEnd = now()->subMonth()->endOfMonth()->format('M d, Y');

        // 5. Pay Day (assumed 10th of current month)
        $payDayDate = now()->startOfMonth()->addDays(9);
        $payDay = $payDayDate->format('M d, Y');

        // 6. Dynamic Status
        $status = now()->gt($payDayDate) ? 'Paid' : 'Pending';

        return $this->successResponse(
            [
                'payroll_period' => "$payrollStart - $payrollEnd",
                'total_leaves_taken' =>$needtocutout,
                'pay_day' => $payDay,
                'status' => $status,
                'gross_pay' => $grossPay,
                'tax' => $tax, // ✅ Added tax as separate field
                'deduction' => $totalDeduction, // ✅ Includes tax
                'total_pay' => $totalPay
            ],
            "Payroll summary fetched successfully"
        );
    }


//summary for ongoing month 

   /**
 * @OA\Post(
 *     path="/uc/api/salary_calculation/ongoing_payrun_summary",
 *     operationId="OngoingPayrunSummary",
 *     tags={"Salary Calculation"},
 *     summary="Get payroll summary for an employee (current month)",
 *     security={{"Bearer": {}}},
 *     description="Fetches payroll summary of the current month including gross pay, deduction, leave count, status, and total pay.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"employee_id"},
 *                 @OA\Property(
 *                     property="employee_id",
 *                     type="integer",
 *                     example=2334,
 *                     description="Employee ID (must exist in sub_users)"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Payroll summary fetched successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Payroll summary fetched successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="payroll_period", type="string", example="Jun 01, 2025 - Jun 30, 2025"),
 *                 @OA\Property(property="total_leaves_taken", type="integer", example=2),
 *                 @OA\Property(property="pay_day", type="string", example="Jul 10, 2025"),
 *                 @OA\Property(property="status", type="string", example="Paid"),
 *                 @OA\Property(property="gross_pay", type="number", format="float", example=37000),
 *                 @OA\Property(property="deduction", type="number", format="float", example=2150.50),
 *                 @OA\Property(property="total_pay", type="number", format="float", example=34849.50)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation failed",
 *         @OA\JsonContent(
 *             @OA\Property(property="errors", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Attendance or salary record not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="No attendance data found")
 *         )
 *     )
 * )
 */


 public function OngoingPayrunSummary(Request $request)
    { 
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:sub_users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $employeeId = $request->employee_id;
        $inputYearMonth = $request->input('year_month');

        
        if ($inputYearMonth && preg_match('/^\d{4}-\d{2}$/', $inputYearMonth)) { 
           $date = Carbon::createFromFormat('Y-m-d', $inputYearMonth . '-01');
         
        }else { 
            $date = now();
        }
 
        // Get current month
        $lastMonth =$date->format('Y-m'); // e.g. "2025-06"
        $anyDateLastMonth = $lastMonth . '-01'; // Arbitrary day in month for attendance

        $payrollStart =$date->copy()->startOfMonth()->format('M d, Y');  // "Jul 01, 2025"
        $payrollEnd =$date->copy()->endOfMonth()->format('M d, Y'); 

        $payDayDate =$date->addMonth()->startOfMonth()->addDays(9);  // August 10, 2025
        $payDay = $payDayDate->format('M d, Y');   

        // 6. Dynamic Status
        $status =$date->gt($payDayDate) ? 'Paid' : 'Pending';

        // 1. Get Attendance
        $attendanceData = $this->getEmployeeMonthAttendence($employeeId, $anyDateLastMonth);
        $attendance = $attendanceData ?? null;

        if (!$attendance) {
            return response()->json(['message' => 'No attendance data found'], 200);
        }

        // 2. Get latest active salary
        $salaryRecord = EmployeeSalary::where('employee_id', $employeeId)
            ->where('is_active', 1)
            ->latest('created_at')
            ->first();

        if (!$salaryRecord) {
            return response()->json(['message' => 'Salary record not found'], 200);
        }


            $grossPay = (float)$salaryRecord->gross_salary;
            $epf_employee = $salaryRecord->epf_employee?$salaryRecord->epf_employee:0;
            $tax = ($grossPay > 21000) ? 200 : 0;
            $maindeduct=$tax+$epf_employee;
            $exactPayableAmount = $grossPay-$maindeduct;
            $bonus = 0;

            $workingDays = $attendanceData['working_days'] ?? 0;
            $monthDays =$attendanceData['total_days'];
            $perDayPay = $grossPay / max($monthDays, 1);
            $needtocutout =  $attendanceData['unpaidleave']+$attendanceData['absent'];
            $absentDeduction = round($perDayPay * $needtocutout, 2);
            $todayday =  Carbon::now()->day;
            $tillsalary  = $perDayPay*$todayday; //
            if($absentDeduction<$tillsalary)
            {
              $finalgettingsalary =   $tillsalary-$absentDeduction;
            }else{
                $finalgettingsalary = 0;
            } 
            

            $govermenttax= $tax;
            $emppf= $epf_employee;
            $totalDeduction = $absentDeduction+ $govermenttax+$emppf;
            $totalPay = round($grossPay - $totalDeduction + $bonus, 2);


        

        return $this->successResponse(
            [ 
                'payroll_period' => "$payrollStart - $payrollEnd",
                'pay_day' => $payDay,
                'status' => 'UnPaid',
                'gross_pay' => $grossPay,
                'till_days'=>  $todayday,
                'till_taking_leave'=>$needtocutout,
                'till_salary'=>number_format($tillsalary, 2),  
                'till_absent_deduction'=>number_format($absentDeduction, 2), 
                'finalgettingsalary'=>number_format($finalgettingsalary, 2),
            ],
            "Payroll summary fetched successfully"
        ); 
    }
 // if change in above function then need to change this as well 
     protected function OngoingPayrunSummaryFunctionUseInside( $employee)
    {  
       
        $attendanceData['working_days'] = 0;
        $attendanceData['total_days']=0;
        $attendanceData['unpaidleave']=0;
        $attendanceData['absent']=0;
        $bigArray=[];

        $date = now();
        
        // Get current month
        $lastMonth =$date->format('Y-m'); // e.g. "2025-06"
        $anyDateLastMonth = $lastMonth . '-01'; // Arbitrary day in month for attendance

        $payrollStart =$date->copy()->startOfMonth()->format('M d, Y');  // "Jul 01, 2025"
        $payrollEnd =$date->copy()->endOfMonth()->format('M d, Y'); 

        $payDayDate =$date->addMonth()->startOfMonth()->addDays(9);  // August 10, 2025
        $payDay = $payDayDate->format('M d, Y');   

        // 6. Dynamic Status
        $status =$date->gt($payDayDate) ? 'Paid' : 'Pending';
     
        foreach ($employee as $emp) { 
            if(isset($emp->id)){ 
            $employeeId = $emp->id;
            $attendanceData = $this->getEmployeeMonthAttendence($employeeId, $anyDateLastMonth);
            $attendance = $attendanceData ?? null;

            // if (!$attendance) {
            //     return response()->json(['message' => 'No attendance data found'], 200);
            // }

            // 2. Get latest active salary
            $salaryRecord = EmployeeSalary::where('employee_id', $employeeId)
                ->where('is_active', 1)
                ->latest('created_at')
                ->first();

            if ($salaryRecord) {
                $grossPay = (float)$salaryRecord->gross_salary;
                    $epf_employee = $salaryRecord->epf_employee?$salaryRecord->epf_employee:0;
                    $tax = ($grossPay > 21000) ? 200 : 0;
                    $maindeduct=$tax+$epf_employee;
                    $exactPayableAmount = $grossPay-$maindeduct;
                    $bonus = 0;

                    $workingDays = @$attendanceData['working_days'] ?? 0;
                    $monthDays =@$attendanceData['total_days'];
                    $perDayPay = $grossPay / max($monthDays, 1);
                    $needtocutout =  $attendanceData['unpaidleave']+$attendanceData['absent'];
                    $absentDeduction = round($perDayPay * $needtocutout, 2);
                    $todayday =  Carbon::now()->day;
                    $tillsalary  = $perDayPay*$todayday; //
                    if($absentDeduction<$tillsalary)
                    {
                    $finalgettingsalary =   $tillsalary-$absentDeduction;
                    }else{
                        $finalgettingsalary = 0;
                    } 
                    

                    $govermenttax= $tax;
                    $emppf= $epf_employee;
                    $totalDeduction = $absentDeduction+ $govermenttax+$emppf;
                    $totalPay = round($grossPay - $totalDeduction + $bonus, 2);


                

            $bigArray[] = 
                    [ 
                        'employee_name'=> $emp->first_name.' '.$emp->last_name,
                        'payroll_period' => "$payrollStart - $payrollEnd",
                        'pay_day' => $payDay,
                        'status' => 'UnPaid',
                        'gross_pay' => $grossPay,
                        'till_days'=>  $todayday,
                        'till_taking_leave'=>$needtocutout,
                        'till_salary'=>number_format($tillsalary, 2),  
                        'till_absent_deduction'=>number_format($absentDeduction, 2), 
                        'finalgettingsalary'=>number_format($finalgettingsalary, 2),
                    ];
                     }
            }


         
        }
     
        return $bigArray;
       
    }



    // api for payroll history chart

   /**
 * @OA\Post(
 *     path="/uc/api/salary_calculation/payroll_history_chart",
 *     operationId="payrollSummaryChart",
 *     tags={"Salary Calculation"},
 *     summary="Get payroll summary for an employee (last month)",
 *     security={{"Bearer": {}}},
 *     description="Fetches payroll summary of the previous month including gross pay, deduction, leave count, status, and total pay.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"employee_id"},
 *                 @OA\Property(
 *                     property="employee_id",
 *                     type="integer",
 *                     example=2334,
 *                     description="Employee ID (must exist in sub_users)"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Payroll summary fetched successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Payroll summary fetched successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="payroll_period", type="string", example="Jun 01, 2025 - Jun 30, 2025"),
 *                 @OA\Property(property="total_leaves_taken", type="integer", example=2),
 *                 @OA\Property(property="pay_day", type="string", example="Jul 10, 2025"),
 *                 @OA\Property(property="status", type="string", example="Paid"),
 *                 @OA\Property(property="gross_pay", type="number", format="float", example=37000),
 *                 @OA\Property(property="deduction", type="number", format="float", example=2150.50),
 *                 @OA\Property(property="total_pay", type="number", format="float", example=34849.50)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation failed",
 *         @OA\JsonContent(
 *             @OA\Property(property="errors", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Attendance or salary record not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="No attendance data found")
 *         )
 *     )
 * )
 */


public function payrollHistoryChart(Request $request)
{  
      $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:sub_users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $employeeId = $request->employee_id;
         $currentYear = now()->year;

    // 1. Get Employee joining date
    $employee = SubUser::find($employeeId);
    if (!$employee || !$employee->doj) {
        return response()->json([
            'status' => false,
            'message' => 'Joining date not found for employee.'
        ], 404);
    }

    $joiningDate = \Carbon\Carbon::parse($employee->doj); // Actual joining date
    $salaryStartMonth = $joiningDate; // Joining month
    $currentMonth = now()->startOfMonth(); // Current month

    $months = [];

    // Loop through all months of the year (Jan–Dec)
    for ($m = 1; $m <= 12; $m++) {
        $monthName = \Carbon\Carbon::create()->month($m)->format('M'); // Jan, Feb, ...
        $year = now()->year;
        $monthKey = $monthName . ' ' . $year; // e.g., "Jul 2025"

        $monthDate = \Carbon\Carbon::createFromDate($year, $m, 1);

        if ($monthDate < $salaryStartMonth) {
            // Month is before joining → leave empty
            $months[$monthKey] = [];
            continue;
        }

        $months[$monthKey] = []; // Default empty for other months
    }

    // Loop from employee joining month to current month
     $workMonth = $salaryStartMonth->copy(); 
    while($workMonth <= $currentMonth) {
        
        // Payout date = 10th of next month
        $payoutDate = $workMonth->copy()->addMonth()->startOfMonth()->addDays(9);
        $payoutMonthKey = $payoutDate->format('M Y'); // e.g., "Jul 2025"

        // Check if salary is paid in DB
        $salaryRecord = EmployeeSalary::where('employee_id', $employeeId)
            ->where('is_active', 1)
            ->first();

        if ($salaryRecord && now()->gte($payoutDate)) {
            // Salary has been paid
            $grossPay = (float)$salaryRecord->gross_salary;

            // ✅ Apply tax upfront if gross > 21000
            $tax = ($grossPay > 21000) ? 200 : 0;
            $exactPayableAmount = $grossPay - $tax;

            // ✅ Bonus (you can fetch dynamically later, hardcoded here as 0)
            $bonus = 0;

            // Get attendance for work month
       
            $attendanceDate = $workMonth->copy()->format('Y-m-d');
                $attendanceData = $this->getEmployeeMonthAttendence($employeeId, $attendanceDate) ?? [
                'working_days' =>0,
                'holidays' => 0,
                'leavesbycompany' =>0,
                'absent'=>0,
                "present"=>0,
                "halfday"=>0,
                "unpaidleave"=>0,
                "paidleave"=>0,
                'total_days'=>0,
            
            ];

            // Calculate salary components
            // $grossPay = (float)$salaryRecord->gross_salary;
            // $epf_employee = $salaryRecord->epf_employee?$salaryRecord->epf_employee:0;
            // $tax = ($grossPay > 21000) ? 200 : 0;
            // $maindeduct=$tax+$epf_employee;
            // $exactPayableAmount = $grossPay-$maindeduct;
            // $bonus = 0;

            // $workingDays = $attendanceData['working_days'] ?? 0;
            // $monthDays =$attendanceData['total_days'];
            // $perDayPay = $grossPay / max($monthDays, 1);

            // $needtocutout =  $attendanceData['unpaidleave']+$attendanceData['absent'];
        
            // $absentDeduction = round($perDayPay * $needtocutout, 2);
            // $govermenttax= $tax;
            // $emppf= $epf_employee;
            // $totalDeduction = $absentDeduction+ $govermenttax+$emppf;
            // $totalPay = round($grossPay - $totalDeduction + $bonus, 2);
                 [
                        $grossPay,  
                        $epf_employee ,
                        $tax ,
                        $maindeduct,
                        $exactPayableAmount,
                        $bonus ,
                        $workingDays ,
                        $monthDays ,
                        $perDayPay ,
                        $needtocutout,
                        $absentDeduction ,
                        $govermenttax,
                        $emppf,
                        $totalDeduction,
                        $totalPay ,
                    ]= $this->basicSalaryCalculation($salaryRecord, $attendanceData);

                $months[$payoutMonthKey] = [
                    'gross_pay' => $grossPay,
                    'tax' => $tax, // ✅ Added tax to response
                    'bonuses' => $bonus, // ✅ Added bonus to response
                    'deduction' => $totalDeduction,
                    'total_pay' => $totalPay,

                ];
            }
        // Else leave the month as empty array []
      
             $workMonth->addMonth();
       
       
    }

    $currentYear = now()->year;
    $currentYearMonths = array_filter($months, function($key) use ($currentYear) {
        return str_ends_with($key, $currentYear);
    }, ARRAY_FILTER_USE_KEY);

    return response()->json([
        'status' => true,
        'message' => 'Monthly salary report generated successfully.',
        'data' =>  $currentYearMonths
    ]);
}





    //incode getemployee attendence data

    public function getEmployeeMonthAttendence($user_id,$month)
      {
       
        try {

            $shift_name = auth('sanctum')->user()->shift_type;
             $activity =[];
         
             $datefornumberofmonth = $month ? Carbon::parse($month) : now()->format('d-m-Y');
             $date = $month ? Carbon::parse($month)->format('m') : now()->format('m');

              $totalDaysInMonth = $datefornumberofmonth->daysInMonth;

              $startDate = $datefornumberofmonth->copy()->startOfMonth();
              $endDate = $startDate->copy()->endOfMonth();
             

                $weekOffs = []; // Store Saturdays & Sundays

                while ($startDate <= $endDate) {
                    if ($startDate->isWeekend()) { // Checks for Sat (6) or Sun (0)
                        $weekOffs[] = $startDate->format('Y-m-d (l)');
                    }
                    $startDate->addDay();
                }

                
            $weekofs =     count( $weekOffs);

                

             $startOfMonth = $month ? Carbon::parse($month)->startOfMonth()->toDateString() : now()->startOfMonth()->toDateString();
             $endOfMonth = $month ? Carbon::parse($month)->endOfMonth()->toDateString() : now()->endOfMonth()->toDateString();

               // employee attendance
             $employeeAttendance = HrmsCalenderAttendance:: where('user_id', $user_id)->whereMonth('date', $date)->pluck('status', 'date');
             //$employeattendenceact = EmployeeAttendance::where('user_id', $user_id)->whereMonth('date', $date)->get(['date', 'login_time', 'logout_time','ideal_time','production']);

            $employeattendenceact = EmployeeAttendance::where('user_id', $user_id)
                ->whereMonth('date', $date)
                ->get(['date', 'login_time', 'logout_time','ideal_time','production'])
                ->mapWithKeys(function ($item) {
                    return [$item->date => ['login_time' => $item->login_time, 'logout_time' => $item->logout_time, 'ideal_time' => $item->ideal_time, 'production' => $item->production]];
                });


               $employeattendenceact  = $employeattendenceact->toArray();


             $formatedData = [];

             $start = Carbon::parse($startOfMonth) ;

                // public holodays
             $publicHoliday = Holiday::whereMonth('date', $date)->pluck('name', 'date');
                // employee leaves

                $holidaycount =  count($publicHoliday);

             $totalLeaveByCompnay = $weekofs + $holidaycount;

                $totalWorkingDays = $totalDaysInMonth - $totalLeaveByCompnay; // my

            $leaves = Leave::where('staff_id', $user_id)
                ->where('status', 1)
                ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                        ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth]);
                })
                ->get();

                $leaveDates = [];

            foreach ($leaves as $leave) {
                $leaveStart = Carbon::parse($leave->start_date);
                $leaveEnd = Carbon::parse($leave->end_date);

                while ($leaveStart->lte($leaveEnd)) {
                    //if ($leaveStart->format('Y-m') === now()->format('Y-m')) {
                        $leaveDates[$leaveStart->format('Y-m-d')] =  $leave->leave_type;
                    //}

                    $leaveStart->addDay();
                }
            }

             $leaveDates;

            $pieChartData = [
                'present' => 0,
                'Absent' => 0,
                'public_holiday' => 0,
                'company_holiday' => 0,
                'leaves' => 0,

            ];

                if (empty($shift_name)) {
                    $shift = HrmsTimeAndShift::first();
                }else {
                    $shift = HrmsTimeAndShift::where('shift_name', $shift_name)->first();
                }

                $shiftDays = $shift->shift_days; // assuming it's like ['SUN' => 0, 'MON' => 1, ...]
                // Get company off-days (like SUN, SAT)
                $companyOffDays = collect($shiftDays)->filter(fn($value) => $value == "0")->keys()->toArray();


             while ($start->lte($endOfMonth)) {
                $date = $start->toDateString();
                if (isset($employeeAttendance[$date])) {
                    $status = $employeeAttendance[$date];
                    $flag = 1;
                    $pieChartData['present'] += 1;

                    $activity = $employeattendenceact[$date] ?? [];

                }elseif (isset($publicHoliday[$date])) {
                    $status = "public holiday";
                    $flag = 4;
                    $pieChartData['public_holiday'] += 1;
                    $activity = $employeattendenceact[$date] ?? [];
                }elseif (in_array(strtoupper($start->format('D')), $companyOffDays) && now()->toDateString() >= $date) {
                    $status = "company holiday";
                    $flag = 3;
                    $pieChartData['company_holiday'] += 1;
                    $activity = $employeattendenceact[$date] ?? [];
                }elseif (isset($leaveDates[$date])) {
                    $status = $leaveDates[$date];
                    $flag = 5;
                    $pieChartData['leaves'] += 1;
                    $activity = $employeattendenceact[$date] ?? [];
                }elseif (now()->toDateString() < $date) {
                    $status = "NA";
                    $flag = 6;
                }else {
                    $status = "Absent";
                    $flag = 2;
                    $pieChartData['Absent'] += 1;
                    $activity = $employeattendenceact[$date] ?? [];
                }

                $formatedData[] = [
                    'date' => $date,
                    'status' => $status,
                    'flag' => $flag,
                    'activity' => $activity
                ];

                $start->addDay();
             }


             $employee = HrmsCalenderAttendance::with('employee')
            ->where('user_id', $user_id)
            ->first();

           $employeeAttendanceCounts = HrmsCalenderAttendance::where('user_id', $user_id)
             ->whereBetween('date', [$startOfMonth, $endOfMonth])
             ->selectRaw('status, COUNT(*) as count')
             ->groupBy('status')
             ->get()
             ->pluck('count', 'status')
             ->toArray();

        //    if (($employeeAttendanceCounts['present'] ?? 0) == 0 &&
        //         ($employeeAttendanceCounts['halfday'] ?? 0) == 0 &&
        //         ($employeeAttendanceCounts['unpaidhalfday'] ?? 0) == 0
        //        ) {
        //         $employeeAttendanceCounts['absent'] = $totalDaysInMonth;
        //     }
            
        
            return [
                'total_days' => $totalDaysInMonth,
                'working_days' => $totalWorkingDays,
                'holidays' => $holidaycount,
                'leavesbycompany' => $totalLeaveByCompnay,
                'absent'=>@$employeeAttendanceCounts['absent']?$employeeAttendanceCounts['absent']:0,
                "present"=>@$employeeAttendanceCounts['present']?$employeeAttendanceCounts['present']:0,
                "halfday"=>@$employeeAttendanceCounts['halfday']?$employeeAttendanceCounts['halfday']:0,
                "unpaidleave"=>@$employeeAttendanceCounts['unpaidleave']?$employeeAttendanceCounts['unpaidleave']:0,
                "paidleave"=>@$employeeAttendanceCounts['paidleave']?$employeeAttendanceCounts['paidleave']:0,
                "total_leave"=>@$employeeAttendanceCounts['total_leave']?$employeeAttendanceCounts['total_leave']:0,
            ];

            //  return $pieChartData;



        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }

      }




// send valid years for salary sleep

      /**
     * @OA\Post(
     *     path="/uc/api/salary_calculation/salary_slip_years",
     *     operationId="salarySlipYears",
     *     tags={"Salary Calculation"},
     *     summary="Get list of years for salary slips from employee's joining date",
     *     security={{"Bearer": {}}},
     *     description="Fetches all years starting from the employee's joining year till the current year.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"employee_id"},
     *                 @OA\Property(
     *                     property="employee_id",
     *                     type="integer",
     *                     example=2596,
     *                     description="ID of the employee (must exist in sub_users table)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Years fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Years fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="integer", example=2023)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Joining date not found for employee.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Joining date not found for employee.")
     *         )
     *     )
     * )
     */




 public function salarySlipYears(Request $request)
{
    $validator = Validator::make($request->all(), [
        'employee_id' => 'required|exists:sub_users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors()
        ], 422);
    }

    $employeeId = $request->employee_id;

    // 1. Get Employee joining date
    $employee = SubUser::find($employeeId);
    if (!$employee || !$employee->doj) {
        return response()->json([
            'status' => false,
            'message' => 'Joining date not found for employee.'
        ], 404);
    }

    $joiningYear = \Carbon\Carbon::parse($employee->doj)->year; // e.g., 2021
    $currentYear = now()->year;

    $years = [];
    for ($year = $joiningYear; $year <= $currentYear; $year++) {
        $years[] = $year;
    }

    return response()->json([
        'status' => true,
        'message' => 'Years fetched successfully.',
        'data' => $years
    ]);
}




// send Salary slip data

      /**
     * @OA\Post(
     *     path="/uc/api/salary_calculation/get_salaryslip_data",
     *     operationId="getsalaryslipdata",
     *     tags={"Salary Calculation"},
     *     summary="Get list of  salary slips from employee's joining date",
     *     security={{"Bearer": {}}},
     *     description="Fetches all salaryslip data from the employee's joining year till the current year.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"employee_id"},
     *                 @OA\Property(
     *                     property="employee_id",
     *                     type="integer",
     *                     example=2596,
     *                     description="ID of the employee (must exist in sub_users table)"
     *                 ),
     *                   @OA\Property(
     *                     property="year",
     *                     type="string",
     *                     example="2025",
     *                     description="Year to display salary slip data"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="slip fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="slip fetched successfully."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Joining date not found for employee.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Joining date not found for employee.")
     *         )
     *     )
     * )
     */


 


public function getSalarySlip(Request $request)
{

     $validator = Validator::make($request->all(), [
        'employee_id' => 'required|exists:sub_users,id',
        'year' => 'required|numeric',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors()
        ], 422);
    }

    $employeeId = $request->employee_id;
     $year = $request->year;

    // 1. Get Employee joining date
    $employee = SubUser::find($employeeId);
    if (!$employee || !$employee->doj) {
        return response()->json([
            'status' => false,
            'message' => 'Joining date not found for employee.'
        ], 404);
    }

    $joiningDate = \Carbon\Carbon::parse($employee->doj); // Actual joining date
    $salaryStartMonth = $joiningDate->copy()->startOfMonth(); // Joining month
    $currentMonth = now()->startOfMonth(); // Current month

    $months = [];

    // Loop through each month in the selected year
    $workMonth = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfMonth();
    while ($workMonth->year == $year && $workMonth <= $currentMonth) {
        // Skip if workMonth is before joining date
        if ($workMonth < $salaryStartMonth) {
            $workMonth->addMonth();
            continue;
        }

        // Payout date = 10th of next month
        $payoutDate = $workMonth->copy()->addMonth()->startOfMonth()->addDays(9);
        $salaryMonthKey = $workMonth->format('M Y'); // e.g., "May 2025"

      
        $monthYear = Carbon::createFromFormat('M Y', $salaryMonthKey);

        $startOfMonth = Carbon::create( $monthYear->year, $monthYear->month, 1)->startOfMonth()->addDay();;
        $endOfMonth = Carbon::create($monthYear->year, $monthYear->month, 1)->endOfMonth();

         $salaryRecord = EmployeeSalary::where('employee_id', $employeeId)
            ->where('increment_from_date', '<=', $endOfMonth)
             ->latest() // Orders by created_at DESC
             ->first();

        // Query to find applicable salary for that month
   

     

        if ($salaryRecord && now()->gte($payoutDate)) {
            // Salary has been paid
            $grossPay = (float)$salaryRecord->gross_salary;

            // ✅ Apply tax upfront if gross > 21000
            $tax = ($grossPay > 21000) ? 200 : 0;
            $exactPayableAmount = $grossPay - $tax;

            // ✅ Bonus (you can fetch dynamically later, hardcoded here as 0)
            $bonus = 0;

            // Get attendance for work month
            $attendanceDate = $workMonth->copy()->format('Y-m-d');
            $attendanceDate = $workMonth->copy()->format('Y-m-d');
                $attendanceData = $this->getEmployeeMonthAttendence($employeeId, $attendanceDate) ?? [
                'working_days' =>0,
                'holidays' => 0,
                'leavesbycompany' =>0,
                'absent'=>0,
                "present"=>0,
                "halfday"=>0,
                "unpaidleave"=>0,
                "paidleave"=>0,
                'total_days'=>0,
            
            ];

            // Calculate salary components
        //     $grossPay = (float)$salaryRecord->gross_salary;
        //     $epf_employee = $salaryRecord->epf_employee?$salaryRecord->epf_employee:0;
        //     $tax = ($grossPay > 21000) ? 200 : 0;
        //     $maindeduct=$tax+$epf_employee;
        //     $exactPayableAmount = $grossPay-$maindeduct;
        //     $bonus = 0;

        //     $workingDays = $attendanceData['working_days'] ?? 0;
        //     $monthDays =$attendanceData['total_days'];
        //     $perDayPay = $grossPay / max($monthDays, 1);

        //    $needtocutout =  $attendanceData['unpaidleave']+$attendanceData['absent'];
        
        //     $absentDeduction = round($perDayPay * $needtocutout, 2);
        //     $govermenttax= $tax;
        //     $emppf= $epf_employee;
        //     $totalDeduction = $absentDeduction+ $govermenttax+$emppf;
        //     $totalPay = round($grossPay - $totalDeduction + $bonus, 2);

                    [
                        $grossPay,  
                        $epf_employee ,
                        $tax ,
                        $maindeduct,
                        $exactPayableAmount,
                        $bonus ,
                        $workingDays ,
                        $monthDays ,
                        $perDayPay ,
                        $needtocutout,
                        $absentDeduction ,
                        $govermenttax,
                        $emppf,
                        $totalDeduction,
                        $totalPay ,
                    ]= $this->basicSalaryCalculation($salaryRecord, $attendanceData);

            // ✅ Add month data only if salary record exists
            $months[$salaryMonthKey] = [
                'monthNumber' => $workMonth->format('n'), 
                'payout_date' => $payoutDate->format('d M Y'), // 🆕 Payout date
                'gross_pay' =>  number_format($grossPay,2),
                'tax' =>  number_format($tax,2),
                'bonuses' =>  number_format($bonus, 2),
                'deduction' => number_format($totalDeduction,2),
                'total_pay' => number_format($totalPay, 2),
                'attendance_summary' => $attendanceData,
                'employee' => $employee,
                'salaryRecord' => $salaryRecord
            ];
        }

        $workMonth->addMonth();
    }

    return response()->json([
        'status' => true,
        'message' => 'Monthly salary report generated successfully.',
        'data' => $months
    ]);
}

    //salary of a month 

         /**
     * @OA\Post(
     *     path="/uc/api/salary_calculation/getsalary-slip-for-month",
     *     operationId="getsalaryslipformonth",
     *     tags={"Salary Calculation"},
     *     summary="Get list of  salary slips from employee's joining date",
     *     security={{"Bearer": {}}},
     *     description="Fetches all salaryslip data from the employee's joining year till the current year.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"employee_id"},
     *                 @OA\Property(
     *                     property="employee_id",
     *                     type="integer",
     *                     example=2596,
     *                     description="ID of the employee (must exist in sub_users table)"
     *                  ),
     *                 @OA\Property(
     *                     property="month",
     *                     type="string",
     *                     example=6,
     *                     description="month that want to salary slip data"
     *                 ),
     *                   @OA\Property(
     *                     property="year",
     *                     type="string",
     *                     example="2025",
     *                     description="Year to display salary slip data"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="slip fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="slip fetched successfully."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Joining date not found for employee.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Joining date not found for employee.")
     *         )
     *     )
     * )
     */



 public function getSalarySlipForMonth(Request $request)
    { 
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:sub_users,id',
            'year' => 'required|numeric',
            'month' => 'required|numeric|between:1,12' // Add month validation
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $employeeId = $request->employee_id;
        $year = $request->year;
        $month = $request->month; // Get requested month

        // Get Employee joining date
        $employee = SubUser::find($employeeId);
        if (!$employee || !$employee->doj) {
            return response()->json([
                'status' => false,
                'message' => 'Joining date not found for employee.'
            ], 404);
        }

        $joiningDate = \Carbon\Carbon::parse($employee->doj);
        $requestedMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $currentMonth = now()->startOfMonth();

        // Check if requested month is before joining
        if ($requestedMonth < $joiningDate->startOfMonth()) {
            return response()->json([
                'status' => false,
                'message' => 'Requested month is before employee joining date.'
            ], 404);
        }

        // Check if requested month is in future
        if ($requestedMonth > $currentMonth) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot generate salary slip for future months.'
            ], 400);
        }

        // Payout date = 10th of next month
        $payoutDate = $requestedMonth->copy()->addMonth()->startOfMonth()->addDays(9);
        $salaryMonthKey = $requestedMonth->format('M Y');

         $monthYear = Carbon::createFromFormat('M Y', $salaryMonthKey);

         $startOfMonth = Carbon::create( $monthYear->year, $monthYear->month, 1)->startOfMonth()->addDay();;
       $endOfMonth = Carbon::create($monthYear->year, $monthYear->month, 1)->endOfMonth();

         $salaryRecord = EmployeeSalary::where('employee_id', $employeeId)
            ->where('increment_from_date', '<=', $endOfMonth)
            ->latest() // Orders by created_at DESC
    ->first();

        // Fetch the salary record active during this month
        // $salaryRecord = EmployeeSalary::where('employee_id', $employeeId)
        //     ->where('is_active', 1)
        //     ->first();

        if (!$salaryRecord) {
            return response()->json([
                'status' => false,
                'message' => 'No active salary record found for this employee.'
            ], 404);
        }

        // Salary has been paid only if current date is after payout date
        $isPaid = now()->gte($payoutDate);
        
        $grossPay = (float)$salaryRecord->gross_salary;
        $tax = ($grossPay > 21000) ? 200 : 0;
        $exactPayableAmount = $grossPay - $tax;
        $bonus = 0;

        // Get attendance for requested month
        $attendanceDate = $requestedMonth->copy()->format('Y-m-d');
       
        // dd($attendanceDate);
        // $attendanceDate = $workMonth->copy()->format('Y-m-d');
            $attendanceData = $this->getEmployeeMonthAttendence($employeeId, $attendanceDate) ?? [
            'working_days' =>0,
            'holidays' => 0,
            'leavesbycompany' =>0,
            'absent'=>0,
            "present"=>0,
            "halfday"=>0,
            "unpaidleave"=>0,
            "paidleave"=>0,
            'total_days'=>0,
           'totallevaebemployee'=>0,
           'absentDeduction'=>0,
        ];

        // Calculate salary components
        $grossPay = (float)$salaryRecord->gross_salary;
        $epf_employee = $salaryRecord->epf_employee?$salaryRecord->epf_employee:0;
        $tax = ($grossPay > 21000) ? 200 : 0;
        $maindeduct=$tax+$epf_employee;
        $exactPayableAmount = $grossPay-$maindeduct;
        $bonus = 0;

        $workingDays = $attendanceData['working_days'] ?? 0;
         $monthDays =$attendanceData['total_days'];
        $perDayPay = $grossPay / max($monthDays, 1);

       $needtocutout =  $attendanceData['unpaidleave']+$attendanceData['absent'];
        $attendanceData['totallevaebemployee'] =  $needtocutout;
        $absentDeduction = round($perDayPay * $needtocutout, 2);
        $attendanceData['absentDeduction'] =$absentDeduction;
        $govermenttax= $tax;
        $emppf= $epf_employee;
        $totalDeduction = $absentDeduction+ $govermenttax+$emppf;
        $totalPay = $grossPay - $totalDeduction + $bonus;

        

        $responseData = [
            'payout_date' => $payoutDate->format('d M Y'),
            'gross_pay' => number_format($grossPay, 2),
            'tax' => number_format($tax, 2),
            'bonuses' => number_format($bonus, 2),
            'deduction' =>number_format($totalDeduction, 2),
            'total_pay' => number_format($totalPay, 2),
            'attendance_summary' => $attendanceData,
            'employee' => $employee,
            'salaryRecord' => $salaryRecord,
            'is_paid' => $isPaid,
            'month' => $salaryMonthKey
        ];

        return response()->json([
            'status' => true,
            'message' => 'Salary slip generated successfully.',
            'data' => $responseData
        ]);
    }



/* function that will run via cron and save data in employee salary after getting inputs  from import_employees_salary_from_excels
then delete the data from import_employees_salary_from_excels*/





public function getFromImportEmployeeSalaryFromExcelsStoreThenDelete()
{
    DB::beginTransaction();

    try {
        $importedSalaries = DB::table('import_employees_salary_from_excels')->get();

        if ($importedSalaries->isEmpty()) {
         \Log::error('No salary data found to process'.time());
            return response()->json([
                'status' => false,
                'message' => 'No salary data found to process'
            ], 404);
        }

        $createdRecords = [];
        $errors = [];

        foreach ($importedSalaries as $imported) {
            try {
                $breakdown = $this->insideCalculate($imported->salary, $imported->epf_type);

                $newSalaryRecord = EmployeeSalary::create([
                    'employee_id' => $imported->employee_id,
                    'basic' => $breakdown['basic'],
                    'hra' => $breakdown['hra'],
                    'medical' => $breakdown['medical'],
                    'conveyance' => $breakdown['conveyance'],
                    'bonus' => $breakdown['bonus'],
                    'gross_salary' => $breakdown['gross_salary'],
                    'professional_tax' => $breakdown['professional_tax'],
                    'epf_employee' => $breakdown['epf_employee'],
                    'esi_employee' => $breakdown['esi_employee'],
                    'take_home' => $breakdown['take_home'],
                    'epf_employer' => $breakdown['epf_employer'],
                    'esi_employer' => $breakdown['esi_employer'],
                    'total_package_salary' => $breakdown['total_package_salary'],
                    'increment_from_date' => now()->format('Y-m-d'),
                    'increment_to_date' => null,
                    'is_active' => 1,
                    'epf_type' => $imported->epf_type,
                    'reason' => 'Bulk import salary calculation'
                ]);

                $createdRecords[] = $newSalaryRecord->id;

            } catch (\Exception $e) {
                $errors[] = [
                    'employee_id' => $imported->employee_id,
                    'error' => $e->getMessage()
                ];
            }
        }

        // Only truncate if all records processed successfully
         if (empty($errors)) {
            DB::table('import_employees_salary_from_excels')->truncate();
         }

        DB::commit();

      \Log::error('Process done successfully'.time());

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error fetching imported salaries: ' . $e->getMessage());
    }
}


    /**
     * @OA\Post(
     *     path="/uc/api/salary_calculation/download-salary-slip",
     *     operationId="downloadsalaryslip",
     *     tags={"Salary Calculation"},
     *     summary="download  salary slips from employee's joining date",
     *     security={{"Bearer": {}}},
     *     description="download salaryslip data from the employee's joining year till the current year.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"employee_id"},
     *                 @OA\Property(
     *                     property="employee_id",
     *                     type="integer",
     *                     example=2596,
     *                     description="ID of the employee (must exist in sub_users table)"
     *                 ),
     *                   @OA\Property(
     *                     property="month",
     *                     type="integer",
     *                     example=1,
     *                     description="Year to display salary slip data 1 to 12"
     *                 ),
     *                   @OA\Property(
     *                     property="year",
     *                     type="integer",
     *                     example=2025,
     *                     description="Year to display salary slip data"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="slip dowmolded successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="slip fetched successfully."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Joining date not found for employee.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Joining date not found for employee.")
     *         )
     *     )
     * )
     */


     public function downloadSalarySlipPdf(Request $request)
        { 

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:sub_users,id',
            'month' => 'required|numeric|between:1,12',
            'year' => 'required|numeric|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $employeeId = $request->employee_id;
        $month = $request->month;
        $year = $request->year;

        // Get the salary data for the requested month
        $salaryData = $this->getSalaryDataForMonth($employeeId, $month, $year);

        if (!$salaryData) {
            return response()->json([
                'status' => false,
                'message' => 'Salary record not found for the specified month.'
            ], 404);
        }


        $pdfData = [
                    'company_name' => $salaryData['employee']->company_name ?? 'Indi IT Solutions',
                    'employee_name' => $salaryData['employee']->first_name.' '.$salaryData['employee']->last_name,
                    'department' => $salaryData['employee']->employement_type ?? 'Web Development',
                    'emp_id' => $salaryData['employee']->unique_id ?? 'IIS-2013',
                    'designation' => $salaryData['employee']->designation ?? 'Software Engineer',
                    'email' => $salaryData['employee']->email ?? 'N/A',
                    'phone' => $salaryData['employee']->phone ?? 'N/A',
                    'dob' => $salaryData['employee']->dob ? date('d-M-Y', strtotime($salaryData['employee']->dob)) : 'N/A',
                    'doj' => $salaryData['employee']->doj ? date('d M Y', strtotime($salaryData['employee']->doj)) : '12 Jun 2023',
                    'period' => date('F Y', mktime(0, 0, 0, $month, 1, $year)),
                    'basic_salary' => number_format($salaryData['salaryRecord']->basic, 2),
                    'hra' => number_format($salaryData['salaryRecord']->hra, 2),
                    'conveyance' => number_format($salaryData['salaryRecord']->conveyance, 2),
                    'medical' => number_format($salaryData['salaryRecord']->medical, 2),
                    'special_allowance' => number_format($salaryData['salaryRecord']->bonus, 2),
                    'gross_salary' => number_format($salaryData['gross_pay'], 2),
                    'epf' => number_format($salaryData['salaryRecord']->epf_employee, 2),
                    'professional_tax' => number_format($salaryData['salaryRecord']->professional_tax, 2),
                    'tds' => number_format($salaryData['tax'], 2),
                    'total_deductions' => number_format($salaryData['deduction'], 2),
                    'net_pay' => number_format($salaryData['total_pay'], 2),
                    'amount_in_words' =>$salaryData['total_pay'],
                    'transaction_id' => 'SAL-'.$year.'-'.$employeeId.'-'.rand(100,999),
                    'generation_date' => date('d-M-Y'),
                    'leavededuction' => number_format($salaryData['leavededuction'], 2),
                    'govermentTax' => number_format($salaryData['govermentTax'], 2),
                    'emppf' => number_format($salaryData['emppf'], 2),
                    'days_worked' => $salaryData['attendance_summary']['working_days'] ?? 30,
                    'unpaidleave' => $salaryData['attendance_summary']['unpaidleave'] ?? 0,
                    'paidleave' => $salaryData['attendance_summary']['paidleave'] ?? 0,
                    'total_days'=> $salaryData['attendance_summary']['total_days'] ?? 30,
                    'company_website' => 'https://www.indiit.com',
                    'company_email' => 'hr@unifytechsolution.com',
                    'pan_number' => '',
                    'aadhaar_number' => '',
                    'account_number' => '',
                    'total_leaves' => $salaryData['total_leaves'],
                ];

        // return $pdfData;

        // Generate the PDF
        $pdf = Pdf::loadView('salary', $pdfData);

        $tempDirectory = storage_path('app/temp_salary_pdfs');
        $finalDirectory = public_path('salary_pdf');

        if (!File::exists($tempDirectory)) {
            File::makeDirectory($tempDirectory, 0755, true);
        }
        if (!File::exists($finalDirectory)) {
            File::makeDirectory($finalDirectory, 0755, true);
        }

        // Generate filename
        $filename = 'salary_'.strtolower(date('F_Y', mktime(0, 0, 0, $month, 1, $year))).'_'.$employeeId.'.pdf';

        // First save to temporary location
        $tempPath = $tempDirectory.'/'.$filename;
        $pdf->save($tempPath);

        // Then move to final location
        $finalPath = $finalDirectory.'/'.$filename;
        File::move($tempPath, $finalPath);
            $publicUrl = url('salary_pdf/'.$filename);
        // Download the file (won't delete from final location)
        return  $publicUrl;
        }

      protected function getSalaryDataForMonth($employeeId, $month, $year)
        {
        $workMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $payoutDate = $workMonth->copy()->addMonth()->startOfMonth()->addDays(9);
        $salaryMonthKey = $workMonth->format('M Y');

        // Get employee data
        $employee = SubUser::find($employeeId);
        if (!$employee || !$employee->doj) {
            return null;
        }

        // Check if the requested month is after joining date
        $joiningDate = \Carbon\Carbon::parse($employee->doj)->startOfMonth();
        if ($workMonth < $joiningDate) {
            return null;
        }

         $monthYear = Carbon::createFromFormat('M Y', $salaryMonthKey);

         $startOfMonth = Carbon::create( $monthYear->year, $monthYear->month, 1)->startOfMonth()->addDay();;
         $endOfMonth = Carbon::create($monthYear->year, $monthYear->month, 1)->endOfMonth();

         $salaryRecord = EmployeeSalary::where('employee_id', $employeeId)
            ->where('increment_from_date', '<=', $endOfMonth)
            ->latest() // Orders by created_at DESC
            ->first();
        
        // Get salary record
        // $salaryRecord = EmployeeSalary::where('employee_id', $employeeId)
        //     ->where('is_active', 1)
        //     ->first();

        if (!$salaryRecord) {
            return null;
        }

        // Get attendance data
        $attendanceDate = $workMonth->copy()->format('Y-m-d');
            $attendanceData = $this->getEmployeeMonthAttendence($employeeId, $attendanceDate) ?? [
            'working_days' =>0,
            'holidays' => 0,
            'leavesbycompany' =>0,
            'absent'=>0,
            "present"=>0,
            "halfday"=>0,
            "unpaidleave"=>0,
            "paidleave"=>0,
            'total_days'=>0,
        ];

        // Calculate salary components
        // $grossPay = (float)$salaryRecord->gross_salary;
        // $epf_employee = $salaryRecord->epf_employee?$salaryRecord->epf_employee:0;
        // $tax = ($grossPay > 21000) ? 200 : 0;
        // $maindeduct=$tax+$epf_employee;
        // $exactPayableAmount = $grossPay-$maindeduct;
        // $bonus = 0;
        // $workingDays = $attendanceData['working_days'] ?? 0;
        // $monthDays =$attendanceData['total_days'];
        // $perDayPay = $grossPay / max($monthDays, 1);
        // $needtocutout =  $attendanceData['unpaidleave']+$attendanceData['absent'];
        // $absentDeduction = round($perDayPay * $needtocutout, 2);
        // $govermenttax= $tax;
        // $emppf= $epf_employee;
        // $totalDeduction =  round($absentDeduction+ $govermenttax+$emppf, 2);
        // $totalPay = round($grossPay - $totalDeduction + $bonus, 2); 

                [
                        $grossPay,  
                        $epf_employee ,
                        $tax ,
                        $maindeduct,
                        $exactPayableAmount,
                        $bonus ,
                        $workingDays ,
                        $monthDays ,
                        $perDayPay ,
                        $needtocutout,
                        $absentDeduction ,
                        $govermenttax,
                        $emppf,
                        $totalDeduction,
                        $totalPay ,
                    ]= $this->basicSalaryCalculation($salaryRecord, $attendanceData);


            return [
                    'payout_date' => $payoutDate->format('d M Y'),
                    'gross_pay' => $grossPay,
                    'tax' => $tax,
                    'bonuses' => $bonus,
                    'leavededuction' => $absentDeduction,
                    'total_pay' => $totalPay,
                    'attendance_summary' => $attendanceData,
                    'employee' => $employee,
                    'salaryRecord' => $salaryRecord,
                    'deduction'=>$totalDeduction,
                    'govermentTax'=>$govermenttax,
                    'emppf'=>$emppf,
                    'total_leaves'=>$needtocutout,
            ];
        }







   // delete salary record 


 /**
 * @OA\Post(
 *     path="/uc/api/salary_calculation/delete-salary-record",
 *     operationId="deleteSalaryRecord",
 *     tags={"Salary Calculation"},
 *     summary="Delete salary record",
 *     description="Deletes a salary record but prevents deletion of active records",
 *     security={ {"Bearer": {} }},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"salary_id"},
 *             @OA\Property(property="salary_id", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Salary Deleted Successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Salary record deleted successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - Cannot delete active record",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Cannot delete active salary record"),
 *             @OA\Property(property="code", type="string", example="ACTIVE_RECORD")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Not Found",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Salary record not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Validation failed"),
 *             @OA\Property(property="errors", type="object", example={
 *                 "salary_id": {"The salary id field is required."}
 *             })
 *         )
 *     )
 * )
 */


    public function deleteSalaryRecord(Request $request)
    { 
        // Validate request
        $validator = Validator::make($request->all(), [
            'salary_id' => 'required|exists:employee_salaries,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $salaryRecord = EmployeeSalary::find($request->salary_id);

            // Check if the record is active
            if ($salaryRecord->is_active) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot delete active salary record',
                    'code' => 'ACTIVE_RECORD'
                ], 403);
            }

            // Additional check - ensure no future records depend on this
            // $hasFutureRecords = EmployeeSalary::where('employee_id', $salaryRecord->employee_id)
            //     ->where('increment_from_date', '>', $salaryRecord->increment_from_date)
            //     ->exists();

            // if ($hasFutureRecords) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Cannot delete salary record with dependent future records',
            //         'code' => 'HAS_DEPENDENTS'
            //     ], 403);
            // }

            // Soft delete the record
            $salaryRecord->delete();

            return response()->json([
                'status' => true,
                'message' => 'Salary record deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete salary record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

     ///////////////////////////////////download for multiple year //////////////////////////////////
    /**
     * @OA\Post(
     *     path="/uc/api/salary_calculation/download_salary_slip_pdf_year",
     *     operationId="downloadSalarySlipPdfYear",
     *     tags={"Salary Calculation"},
     *     summary="download salary slips from employee's joining date",
     *     security={{"Bearer": {}}},
     *     description="download salaryslip data from the employee's joining year till the current year.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"employee_id"},
     *                 @OA\Property(
     *                     property="employee_id",
     *                     type="integer",
     *                     example=2596,
     *                     description="ID of the employee (must exist in sub_users table)"
     *                 ),
     *                 @OA\Property(
     *                     property="months",
     *                     type="array",
     *                     @OA\Items(
     *                         type="integer",
     *                         example=1
     *                     ),
     *                     description="Months to display salary slip data (1-12)"
     *                 ),
     *                 @OA\Property(
     *                     property="year",
     *                     type="integer",
     *                     example=2025,
     *                     description="Year to display salary slip data"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="slip dowmolded successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="slip fetched successfully."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Joining date not found for employee.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Joining date not found for employee.")
     *         )
     *     )
     * )
     */
      public function downloadSalarySlipPdfYear(Request $request)
        { 
            // return $request->all();
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:sub_users,id',
            'year' => 'required|numeric',
            'months' => 'required|array',
            'months.*' => 'numeric|between:1,12'
          ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $employeeId = $request->employee_id;
        $year = $request->year;
        $months = $request->months; // Array of months (e.g., [1, 3, 6])
        

        // Get the salary data for the requested month
        $salaryData = $this->getSalaryDataForYear($employeeId,  $months, $year);

        if (!$salaryData) {
            return response()->json([
                'status' => false,
                'message' => 'Salary record not found for the specified month.'
            ], 404);
        }
            // Prepare data for PDF view
            $pdfData = [
                'company_name' => $salaryData[0]['employee']->company_name ?? 'Company Name',
                'employee_name' => $salaryData[0]['employee']->first_name.' '.$salaryData[0]['employee']->last_name,
                'employee' => $salaryData[0]['employee'],
                'salary_records' => $salaryData // Pass all records to the view
            ];
           
            // Generate the PDF
            $pdf = Pdf::loadView('multi_month_salary', $pdfData);
            
            $filename = 'salary_'.$employeeId.'_'.$year.'_'.implode('-', $months).'.pdf';
            $filePath = public_path('salary_pdf/'.$filename);
            
            // Save the PDF
            $pdf->save($filePath);
            
            return response()->json([
                'status' => true,
                'message' => 'Multi-month salary slip generated successfully',
                'download_url' => url('salary_pdf/'.$filename)
            ]);
        }

      protected function getSalaryDataForYear($employeeId, $months, $year)
        {  
             foreach ($months as $month) {
                $workMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
                $payoutDate = $workMonth->copy()->addMonth()->startOfMonth()->addDays(9);
                $salaryMonthKey = $workMonth->format('M Y');
                // Get employee data
                $employee = SubUser::find($employeeId);
                if (!$employee || !$employee->doj) {
                    return null;
                }

                // Check if the requested month is after joining date
                $joiningDate = \Carbon\Carbon::parse($employee->doj)->startOfMonth();
                if ($workMonth < $joiningDate) {
                    return null;
                }

                 $monthYear = Carbon::createFromFormat('M Y', $salaryMonthKey);

                 $startOfMonth = Carbon::create( $monthYear->year, $monthYear->month, 1)->startOfMonth()->addDay();;
                   $endOfMonth = Carbon::create($monthYear->year, $monthYear->month, 1)->endOfMonth();

                    $salaryRecord = EmployeeSalary::where('employee_id', $employeeId)
                        ->where('increment_from_date', '<=', $endOfMonth)
                        ->latest() // Orders by created_at DESC
                        ->first();

                // Get salary record
                // $salaryRecord = EmployeeSalary::where('employee_id', $employeeId)
                //     ->where('is_active', 1)
                //     ->first();

                if (!$salaryRecord) {
                    return null;
                }

                // Get attendance data
                $attendanceDate = $workMonth->copy()->format('Y-m-d');
                  $attendanceDate = $workMonth->copy()->format('Y-m-d');
                    $attendanceData = $this->getEmployeeMonthAttendence($employeeId, $attendanceDate) ?? [
                    'working_days' =>0,
                    'holidays' => 0,
                    'leavesbycompany' =>0,
                    'absent'=>0,
                    "present"=>0,
                    "halfday"=>0,
                    "unpaidleave"=>0,
                    "paidleave"=>0,
                    'total_days'=>0,
                
                ];

                // Calculate salary components
                // $grossPay = (float)$salaryRecord->gross_salary;
                // $epf_employee = $salaryRecord->epf_employee?$salaryRecord->epf_employee:0;
                // $tax = ($grossPay > 21000) ? 200 : 0;
                // $maindeduct=$tax+$epf_employee;
                // $exactPayableAmount = $grossPay-$maindeduct;
                // $bonus = 0;
                // $workingDays = $attendanceData['working_days'] ?? 0;
                // $monthDays =$attendanceData['total_days'];
                // $perDayPay = $grossPay / max($monthDays, 1);
                // $needtocutout =  $attendanceData['unpaidleave']+$attendanceData['absent'];
                // $absentDeduction = round($perDayPay * $needtocutout, 2);
                // $govermenttax= $tax;
                // $emppf= $epf_employee;
                // $totalDeduction =  $absentDeduction+ $govermenttax+$emppf;
                // $totalPay = $grossPay - $totalDeduction + $bonus;

                   [
                        $grossPay,  
                        $epf_employee ,
                        $tax ,
                        $maindeduct,
                        $exactPayableAmount,
                        $bonus ,
                        $workingDays ,
                        $monthDays ,
                        $perDayPay ,
                        $needtocutout,
                        $absentDeduction ,
                        $govermenttax,
                        $emppf,
                        $totalDeduction,
                        $totalPay ,
                    ]= $this->basicSalaryCalculation($salaryRecord, $attendanceData);

                $empty_array[] =  [
                    'payout_date' => $payoutDate->format('d M Y'),
                    'gross_pay' =>  number_format($grossPay, 2), // Format to two decimal places$grossPay,
                    'tax' =>  number_format($tax, 2), // Format to two decimal places$tax,
                    'bonuses' =>  number_format($bonus, 2), // Format to two decimal places$bonus,
                    'leavededuction' =>  number_format($absentDeduction, 2), // Format to two decimal places$absentDeduction,
                    'total_pay' => number_format($totalPay, 2),
                    'attendance_summary' => $attendanceData,
                    'employee' => $employee,
                    'salaryRecord' => $salaryRecord,
                    'deduction'=> number_format($totalDeduction, 2),
                    'govermentTax'=>$govermenttax,
                    'emppf'=>$emppf,
                    'total_leave'=>$needtocutout
                ];
            }
               
            return $empty_array;
        }



/***********************************Salary data as per manager team************************************************************/

 /**
     * @OA\Get(
     * path="/uc/api/Manager_salary_calculation/get_all_mangers_with_employee",
     * operationId="getAllMangersWithEmployee",
     * tags={"Salary Calculation"},
     * summary="get All Mangers With Employee",
     *   security={ {"Bearer": {} }},
     * description="get All Mangers With Employee",
     *      @OA\Response(
     *          response=201,
     *          description="List data",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="List data",
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

    public function getAllMangersWithEmployee()
    {  

        $getTeamList = TeamManager::with(['employees','teams.teamLeader', 'teams.teamMembers.user'])->get();

        $totalEmp=0;
        $mangerWithEmployee=[];
        $teamMemberIds=[];
        $teamMemberId_single=[];
        $all_emp=[];
        $myarray=[];

        foreach ($getTeamList as $manager)
        {   
                    // $teamMemberIds = collect();
                    // $employeeIds = collect($manager->employees)->pluck('id')->toArray();
                    // $teamMemberIds = collect($employeeIds);

                    // $allEmployees = EmployeesUnderOfManager::with('employee')
                    //     ->where('manager_id', $manager->id)
                    //     ->get()
                    //     ->pluck('employee')
                    //     ->filter();

                    // 2. All employee IDs in any team under this manager
                    $myarray=[];
                    foreach ($manager->teams as $team) {
                    
                        $teamLeaderID = (int) $team->team_leader;
                        $myarray[]=$teamLeaderID;
                        
                        foreach ($team->teamMembers as $member) {
                                $myarray[]=$member->member_id;
                        }
                    }
                    
                    
                    $teamMemberIds[$manager->name] = $myarray;
                    
                 
        }

        $all_emp= $teamMemberIds;

        $allEmployeeIds = array_unique(array_merge(...array_values($all_emp)));

        $valid_emp_ids = EmployeeSalary::whereIn('employee_id', $allEmployeeIds)
        ->pluck('employee_id')
        ->unique()
        ->toArray();

        // Get total count
        $totalEmp = count( $valid_emp_ids);
        //     $totalEmp=$totalEmployees;

            foreach ( $all_emp as $group => $ids) {
                $all_emp[$group] = array_values(array_intersect($ids, $valid_emp_ids));
            }
           
            $managerwise =  $this->managerTeamCostDetailsPerEmployee($all_emp);

            $collection = collect( $managerwise);

            $super_totals = [
                            'company_tillsalary' => number_format($collection->sum(function($manager) {
                                return (float)$manager['tillsalary'];
                            }), 2, '.', ''),
                            
                            'company_totalDeduction' => number_format($collection->sum(function($manager) {
                                return (float)$manager['totalDeduction'];
                            }), 2, '.', ''),
                            
                            'company_finalgettingsalary' => number_format($collection->sum(function($manager) {
                                return (float)$manager['finalgettingsalary'];
                            }), 2, '.', ''),
                            
                            'company_finalgovermenttax' => number_format($collection->sum(function($manager) {
                                return (float)($manager['finalgovermenttax'] ?? 0);
                            }), 2, '.', ''),
                            
                            'company_finalemppf' => number_format($collection->sum(function($manager) {
                                return (float)($manager['finalemppf'] ?? 0);
                            }), 2, '.', ''),
                            
                            'company_finalinsorance' => number_format($collection->sum(function($manager) {
                                return (float)($manager['finalinsorance'] ?? 0);
                            }), 2, '.', ''),
                            
                            'company_finalbounus' => number_format($collection->sum(function($manager) {
                                return (float)($manager['finalbounus'] ?? 0);
                            }), 2, '.', ''),
                            
                            'company_finalextraworkpay' => number_format($collection->sum(function($manager) {
                                return (float)($manager['finalextraworkpay'] ?? 0);
                            }), 2, '.', '')
                
            ];

            $payrollStart = now()->startOfMonth()->format('M d, Y');  // "Jul 01, 2025"
            $payrollEnd = now()->endOfMonth()->format('M d, Y'); 

            $payDayDate = now()->addMonth()->startOfMonth()->addDays(9);  // August 10, 2025
            $payDay = $payDayDate->format('M d, Y');   

            // 6. Dynamic Status
            $status = now()->gt($payDayDate) ? 'Paid' : 'Pending';

            return response()->json([
                        'status' => true,
                        'message' => 'Company cost as per manager team',
                        'data_manager_wise' => $managerwise,
                        'data_company_wise' => $super_totals,
                        'payroll_period' => "$payrollStart - $payrollEnd",
                        'pay_day' => $payDay,
                        'pay_status' => 'UnPaid',
                        'total_employees' => $totalEmp,
                    
                    ]);

    }



    public function managerTeamCostDetailsPerEmployee($mangerWithEmployee)
    { 
        $lastMonth = now()->format('Y-m'); // e.g. "2025-06"
        $anyDateLastMonth = $lastMonth . '-03'; // Arbitrary day in month for attendance
        $empty_array=[];
        foreach($mangerWithEmployee as $manager=>$employeesId){
            $empty_array[$manager]['tillsalary'] = "0.00";
            $empty_array[$manager]['totalDeduction'] = "0.00";
            $empty_array[$manager]['finalgettingsalary'] = "0.00";
            $empty_array[$manager]["finalgovermenttax"] = "0.00";
            $empty_array[$manager]["finalgovermenttax"]="0.00";
            $empty_array[$manager]["finalemppf"]="0.00";
            $empty_array[$manager]["finalinsorance"]="0.00";
            $empty_array[$manager]["finalbounus"]="0.00";
            $empty_array[$manager]["finalextraworkpay"]="0.00";
            $empty_array[$manager]["extra_work_pay"]="0.00";
            $empty_array[$manager]["status"]="N/A";
            $empty_array[$manager]["upcomming_payroll"]= null;

            
            
                    
            foreach($employeesId as $employeeId){
        

                        // 1. Get Attendance
                        $attendanceData = $this->getEmployeeMonthAttendence($employeeId, $anyDateLastMonth);
                        $attendance = $attendanceData ?? null;

                        if (!$attendance) {
                            $empty_array[$manager]=[
                                "tillsalary" => 0,
                                "totalDeduction" => 0,
                                "finalgettingsalary" => 0,
                                "finalgovermenttax" => 0,
                                "finalgovermenttax"=>0,
                                "finalemppf"=>0,
                                "finalinsorance"=>0,
                                "finalbounus"=>0,
                                "finalextraworkpay"=>0,
                                "extra_work_pay"=>0.00,
                                "status"=>"Panding",
                                "upcomming_payroll"=>null,
                            ];
                        }

                        // 2. Get latest active salary
                        $salaryRecord = EmployeeSalary::where('employee_id', $employeeId)
                            ->where('is_active', 1)
                            ->latest('created_at')
                            ->first();

                        if (!$salaryRecord) {
                 
                        }else{
                            
                            $grossPay = (float)$salaryRecord->gross_salary;
                            $epf_employee = $salaryRecord->epf_employee?$salaryRecord->epf_employee:0;
                            $tax = ($grossPay > 21000) ? 200 : 0;
                            $maindeduct=$tax+$epf_employee;
                            $exactPayableAmount = $grossPay-$maindeduct;
                            $bonus =$salaryRecord->bonus;

                            $workingDays = $attendanceData['working_days'] ?? 0;
                            $monthDays =$attendanceData['total_days'];
                            $perDayPay = $grossPay / max($monthDays, 1);
                            $needtocutout =  $attendanceData['unpaidleave']+$attendanceData['absent'];
                            $absentDeduction = round($perDayPay * $needtocutout, 2); 
                            $todayday =  Carbon::now()->day;
                            $tillsalary  = $perDayPay*$todayday; // 
                        
                            if($absentDeduction<$tillsalary)
                            { 
                            $finalgettingsalary =   $tillsalary-$absentDeduction;
                            }else{
                                $finalgettingsalary = 0;
                            } 
                            

                            $govermenttax= $tax;
                            $emppf= $epf_employee;
                            $totalDeduction = $absentDeduction+ $govermenttax+$emppf;
                            $thismonthwillpay=$tillsalary-$totalDeduction;
                            $totalPay = round($grossPay - $totalDeduction , 2);
                        
                            $payrollStart = now()->startOfMonth()->format('M d, Y');  // "Jul 01, 2025"
                            $payrollEnd = now()->endOfMonth()->format('M d, Y'); 

                            $payDayDate = now()->addMonth()->startOfMonth()->addDays(9);  // August 10, 2025
                            $payDay = $payDayDate->format('M d, Y');   

                            // 6. Dynamic Status
                        $status = now()->gt($payDayDate) ? 'Paid' : 'Pending';

                        $ftill =  $empty_array[$manager]["tillsalary"]+$tillsalary;
                        $fabsent =  $empty_array[$manager]["totalDeduction"]+ $totalDeduction;
                        $fgeting = $empty_array[$manager]["finalgettingsalary"]+  $thismonthwillpay;
                        $fgovermenttax = $empty_array[$manager]["finalgovermenttax"]+  $govermenttax;
                        $femppf = $empty_array[$manager]["finalemppf"]+  $emppf;

                        $finsorance = $empty_array[$manager]["finalinsorance"]+ 0;
                        $fbonus =  $empty_array[$manager]["finalbounus"]+0;
                        $fextrawork = $empty_array[$manager]["finalextraworkpay"]+0;
                    
                    


                        $empty_array[$manager]["tillsalary"]= number_format($ftill, 2, '.', '');
                        $empty_array[$manager]["totalDeduction"]=number_format($fabsent, 2, '.', '');
                        $empty_array[$manager]["finalgettingsalary"]=number_format($fgeting, 2, '.', '');
                        $empty_array[$manager]["finalgovermenttax"]=number_format($fgovermenttax, 2, '.', '');
                        $empty_array[$manager]["finalemppf"]=number_format($femppf, 2, '.', '');
                        $empty_array[$manager]["finalinsorance"]=number_format(  $finsorance, 2, '.', '');
                        $empty_array[$manager]["finalbounus"]=number_format( $fbonus    , 2, '.', '');
                        $empty_array[$manager]["finalextraworkpay"]=number_format($fextrawork, 2, '.', '');
                        $empty_array[$manager]["extra_work_pay"]="0.00";
                        $empty_array[$manager]["status"]="Panding";
                        $empty_array[$manager]["upcomming_payroll"]= $payrollStart . ' - ' .  $payrollEnd;
                
                        
                        }

            }


        }
        return  $empty_array;
        // Get current month  
    }

    //function for calculate super total for comanay

     /**
     * @OA\Get(
     * path="/uc/api/Manager_salary_calculation/get_comapny_payroll_history_chart",
     * operationId="getcomapnypayrollhistorychart",
     * tags={"Salary Calculation"},
     * summary="get All Mangers With Employee",
     *   security={ {"Bearer": {} }},
     * description="get All Mangers With Employee",
     *      @OA\Response(
     *          response=201,
     *          description="List data",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="List data",
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

    public function getComapnyPayrollHistoryChart()
    {
        $allemployees = [];
        $myarray=[];
        $getTeamList =   TeamManager::with(['employees','teams.teamLeader', 'teams.teamMembers.user'])->get();

            foreach ($getTeamList as $manager) {  
            
            
            foreach ($manager->teams as $team) {
            
        
                         $teamLeaderID = (int) $team->team_leader;
                        $myarray[]= array("id"=>$teamLeaderID,"doj"=>$team->teamLeader->doj);
                        
                        foreach ($team->teamMembers as $member) { 
                            if ($member->user) { 
                                $myarray[]=  array("id"=>$member->user->id,"doj"=>$member->user->doj) ;
                            }
                        
                        }
                    }
            }
                

        $companypayrolldata =    $this->insideFunctionForGivingPayrolHistoryForAnEmployee( $myarray);
        return response()->json([
            'status' => true,
            'message' => 'Data fetched successfully',
            'data' => $companypayrolldata 
        ]);

        
    }

    
    public function insideFunctionForGivingPayrolHistoryForAnEmployee($employee)
    {  
       $holdingall=[];
       $monthlyTotals=[];
       foreach ($employee as $employee) {
           $employeeId = $employee['id'];
             $doj = $employee['doj'];
            $currentYear = now()->year;
            $joiningDate = \Carbon\Carbon::parse($doj); // Actual joining date
            $salaryStartMonth = $joiningDate; // Joining month
            $currentMonth = now()->startOfMonth(); // Current month

            $months = [];

            // Loop through all months of the year (Jan–Dec)
            for ($m = 1; $m <= 12; $m++) {
                $monthName = \Carbon\Carbon::create()->month($m)->format('M'); // Jan, Feb, ...
                $year = now()->year;
                $monthKey = $monthName . ' ' . $year; // e.g., "Jul 2025"

                $monthDate = \Carbon\Carbon::createFromDate($year, $m, 1);

                if ($monthDate < $salaryStartMonth) {
                    // Month is before joining → leave empty
                    $months[$monthKey] = [];
                    continue;
                }

                $months[$monthKey] = []; // Default empty for other months
            }

            // Loop from employee joining month to current month
            $workMonth = $salaryStartMonth->copy(); 
            while($workMonth <= $currentMonth) {
                
                // Payout date = 10th of next month
                $payoutDate = $workMonth->copy()->addMonth()->startOfMonth()->addDays(9);
                $payoutMonthKey = $payoutDate->format('M Y'); // e.g., "Jul 2025"

                // Check if salary is paid in DB
                $salaryRecord = EmployeeSalary::where('employee_id', $employeeId)
                    ->where('is_active', 1)
                    ->first();

                if ($salaryRecord && now()->gte($payoutDate)) {
                    // Salary has been paid
                    $grossPay = (float)$salaryRecord->gross_salary;

                    // ✅ Apply tax upfront if gross > 21000
                    $tax = ($grossPay > 21000) ? 200 : 0;
                    $exactPayableAmount = $grossPay - $tax;

                    // ✅ Bonus (you can fetch dynamically later, hardcoded here as 0)
                    $bonus = 0;

                    // Get attendance for work month
            
                    $attendanceDate = $workMonth->copy()->format('Y-m-d');
                        $attendanceData = $this->getEmployeeMonthAttendence($employeeId, $attendanceDate) ?? [
                        'working_days' =>0,
                        'holidays' => 0,
                        'leavesbycompany' =>0,
                        'absent'=>0,
                        "present"=>0,
                        "halfday"=>0,
                        "unpaidleave"=>0,
                        "paidleave"=>0,
                        'total_days'=>0,
                    
                    ];

                    [
                        $grossPay,  
                        $epf_employee ,
                        $tax ,
                        $maindeduct,
                        $exactPayableAmount,
                        $bonus ,
                        $workingDays ,
                        $monthDays ,
                        $perDayPay ,
                        $needtocutout,
                        $absentDeduction ,
                        $govermenttax,
                        $emppf,
                        $totalDeduction,
                        $totalPay ,
                    ]= $this->basicSalaryCalculation($salaryRecord, $attendanceData);
                  
                    // Calculate salary components
                    // $grossPay = (float)$salaryRecord->gross_salary;
                    // $epf_employee = $salaryRecord->epf_employee?$salaryRecord->epf_employee:0;
                    // $tax = ($grossPay > 21000) ? 200 : 0;
                    // $maindeduct=$tax+$epf_employee;
                    // $exactPayableAmount = $grossPay-$maindeduct;
                    // $bonus = 0;

                    // $workingDays = $attendanceData['working_days'] ?? 0;
                    // $monthDays =$attendanceData['total_days'];
                    // $perDayPay = $grossPay / max($monthDays, 1);

                    // $needtocutout =  $attendanceData['unpaidleave']+$attendanceData['absent'];
                
                    // $absentDeduction = round($perDayPay * $needtocutout, 2);
                    // $govermenttax= $tax;
                    // $emppf= $epf_employee;
                    // $totalDeduction = $absentDeduction+ $govermenttax+$emppf;
                    // $totalPay = round($grossPay - $totalDeduction + $bonus, 2);

                        $months[$payoutMonthKey] = [
                            'gross_pay' => $grossPay,
                            'tax' => $tax, //  Added tax to response
                            'bonuses' => $bonus, //  Added bonus to response
                            'deduction' => $totalDeduction,
                            'total_pay' => $totalPay,

                        ];
                    }
                // Else leave the month as empty array []
            
                    $workMonth->addMonth();
            
            
            }

            $currentYear = now()->year;
            $currentYearMonths = array_filter($months, function($key) use ($currentYear) {
                return str_ends_with($key, $currentYear);
            }, ARRAY_FILTER_USE_KEY);

            $holdingall[] = $currentYearMonths;

            
       } 
      foreach ($holdingall as $employeeData) {
                foreach ($employeeData as $month => $values) {
                    // Skip empty months
                    if (empty($values)) {
                        continue;
                    }

                    // Initialize month if not already set
                    if (!isset($monthlyTotals[$month])) {
                        $monthlyTotals[$month] = [
                            'gross_pay'   => 0,
                            'tax'         => 0,
                            'bonuses'     => 0,
                            'deduction'   => 0,
                            'total_pay'   => 0,
                        ];
                    }

                    // Add current employee's values to month totals
                    $monthlyTotals[$month]['gross_pay']   += $values['gross_pay'];
                    $monthlyTotals[$month]['tax']         += $values['tax'];
                    $monthlyTotals[$month]['bonuses']     += $values['bonuses'];
                    $monthlyTotals[$month]['deduction']   += $values['deduction'];
                    $monthlyTotals[$month]['total_pay']   += $values['total_pay'];
                }
            }

            return $monthlyTotals;
       
    }


// api for payrun history page

    /**
     * @OA\Post(
     *     path="/uc/api/Manager_salary_calculation/pay_run_history_so_far",
     *     operationId="payrunHistorySoFar",
     *     tags={"Salary Calculation"},
     *     summary="Get list of  salary slips from employee's joining date",
     *     security={{"Bearer": {}}},
     *     description="Fetches all salaryslip data from the employee's joining year till the current year.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"employee_id"},
     *                 @OA\Property(
     *                     property="employee_id",
     *                     type="integer",
     *                     example=2596,
     *                     description="ID of the employee (must exist in sub_users table)"
     *                  ),
     *                   @OA\Property(
     *                     property="year",
     *                     type="string",
     *                     example="2025",
     *                     description="Year to display salary slip data"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="slip fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="slip fetched successfully."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Joining date not found for employee.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Joining date not found for employee.")
     *         )
     *     )
     * )
     */

    public function payrunHistorySoFar(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:sub_users,id',
            'year' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $employeeId = $request->employee_id;
        $year = $request->year;

        // 1. Get Employee joining date
        $employee = SubUser::find($employeeId);
        if (!$employee || !$employee->doj) {
            return response()->json([
                'status' => false,
                'message' => 'Joining date not found for employee.'
            ], 404);
        }

        $joiningDate = \Carbon\Carbon::parse($employee->doj); // Actual joining date
        $salaryStartMonth = $joiningDate->copy()->startOfMonth(); // Joining month
        $currentMonth = now()->startOfMonth()->addday(); // Current month

        $months = [];

        // Loop through each month in the selected year
        $workMonth = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfMonth();
        while ($workMonth->year == $year && $workMonth <= $currentMonth) {
            // Skip if workMonth is before joining date
            if ($workMonth < $salaryStartMonth) {
                $workMonth->addMonth();
                continue;
            }

            // Payout date = 10th of next month
            $payoutDate = $workMonth->copy()->addMonth()->startOfMonth()->addDays(9);
            $salaryMonthKey = $workMonth->format('M Y'); // e.g., "May 2025"

        
            $monthYear = Carbon::createFromFormat('M Y', $salaryMonthKey);

            $startOfMonth = Carbon::create( $monthYear->year, $monthYear->month, 1)->startOfMonth();
            $endOfMonth = Carbon::create($monthYear->year, $monthYear->month, 1)->endOfMonth();

             

            $salaryRecord = EmployeeSalary::where('employee_id', $employeeId)
                ->where('increment_from_date', '<=', $endOfMonth)
                ->latest() // Orders by created_at DESC
                ->first();

            // Query to find applicable salary for that month
    

        

            if ($salaryRecord && now()->gte($payoutDate)) {
                // Salary has been paid
                $grossPay = (float)$salaryRecord->gross_salary;

                // ✅ Apply tax upfront if gross > 21000
                $tax = ($grossPay > 21000) ? 200 : 0;
                $exactPayableAmount = $grossPay - $tax;

                // ✅ Bonus (you can fetch dynamically later, hardcoded here as 0)
                $bonus = 0;

                // Get attendance for work month
                $attendanceDate = $workMonth->copy()->format('Y-m-d');
                $attendanceDate = $workMonth->copy()->format('Y-m-d');
                    $attendanceData = $this->getEmployeeMonthAttendence($employeeId, $attendanceDate) ?? [
                    'working_days' =>0,
                    'holidays' => 0,
                    'leavesbycompany' =>0,
                    'absent'=>0,
                    "present"=>0,
                    "halfday"=>0,
                    "unpaidleave"=>0,
                    "paidleave"=>0,
                    'total_days'=>0,
                
                ]; 

        
                        [
                            $grossPay,  
                            $epf_employee ,
                            $tax ,
                            $maindeduct,
                            $exactPayableAmount,
                            $bonus ,
                            $workingDays ,
                            $monthDays ,
                            $perDayPay ,
                            $needtocutout,
                            $absentDeduction ,
                            $govermenttax,
                            $emppf,
                            $totalDeduction,
                            $totalPay ,
                        ]= $this->basicSalaryCalculation($salaryRecord, $attendanceData);

                // ✅ Add month data only if salary record exists
                $months["past"][$salaryMonthKey] = [
                    'monthNumber' => $workMonth->format('n'), 
                    'payout_date' => $payoutDate->format('d M Y'), // 🆕 Payout date
                    'gross_pay' =>  number_format($grossPay,2),
                    'tax' =>  number_format($tax,2),
                    'bonuses' =>  number_format($bonus, 2),
                    'deduction' => number_format($totalDeduction,2),
                    'total_pay' => number_format($totalPay, 2),
                    'salaryRecord' => $salaryRecord,
                    'status' => 'paid',
                    'employee' => $employee,
                    'unpaid_leave' => $attendanceData['unpaidleave']+$attendanceData['absent'],    
                    'payroll_period' =>$startOfMonth->format('M d, Y')."-".$endOfMonth->format('M d, Y'),
                     
                ];
            }

            $workMonth->addMonth();
        }

            // ✅ Add current month as "upcoming"
        $currentWorkMonth = now()->startOfMonth();
        $payoutDate = $currentWorkMonth->copy()->addMonth()->startOfMonth()->addDays(9);
        $salaryMonthKey = $currentWorkMonth->format('M Y');

        $monthYear = Carbon::createFromFormat('M Y', $salaryMonthKey);
        $startOfMonth = $monthYear->copy()->startOfMonth();
        $endOfMonth = $monthYear->copy()->endOfMonth();

        // Get employee salary record for current month
        $salaryRecord = EmployeeSalary::where('employee_id', $employeeId)
            ->where('increment_from_date', '<=', $endOfMonth)
            ->latest()
            ->first();

        if ($salaryRecord) {
            // Calculate tax upfront if gross > 21000
            $grossPay = (float)$salaryRecord->gross_salary;
            $tax = ($grossPay > 21000) ? 200 : 0;
            $bonus = 0;

            // Attendance details for current month
            $attendanceDate = $currentWorkMonth->copy()->format('Y-m-d');
            $attendanceData = $this->getEmployeeMonthAttendence($employeeId, $attendanceDate) ?? [
                'working_days' => 0,
                'holidays' => 0,
                'leavesbycompany' => 0,
                'absent' => 0,
                'present' => 0,
                'halfday' => 0,
                'unpaidleave' => 0,
                'paidleave' => 0,
                'total_days' => 0,
            ];

            [
                $grossPay,
                $epf_employee,
                $tax,
                $maindeduct,
                $exactPayableAmount,
                $bonus,
                $workingDays,
                $monthDays,
                $perDayPay,
                $needtocutout,
                $absentDeduction,
                $govermenttax,
                $emppf,
                $totalDeduction,
                $totalPay,
            ] = $this->basicSalaryCalculation($salaryRecord, $attendanceData);

            $months["upcoming"][$salaryMonthKey] = [
          
                'payout_date' => $payoutDate->format('d M Y'),
                'gross_pay' => number_format($grossPay, 2),
                'tax' => number_format($tax, 2),
                'bonuses' => number_format($bonus, 2),
                'deduction' => number_format($totalDeduction, 2),
                'total_pay' => number_format($totalPay, 2),
                'salaryRecord' => $salaryRecord,
                'status' => 'upcoming',
                'employee' => $employee,
                'unpaid_leave' => $attendanceData['unpaidleave'] + $attendanceData['absent'],
                'payroll_period' => $startOfMonth->format('M d, Y') . "-" . $endOfMonth->format('M d, Y'),
            ];
        }


        return response()->json([
            'status' => true,
            'message' => 'Monthly salary report generated successfully.',
            'data' => $months
        ]);
    }



       /**
 * @OA\Post(
 *     path="/uc/api/Manager_salary_calculation/employee_of_a_manager_and_there_cost",
 *     operationId="EmployeeOfManagerAndThereCost",
 *     tags={"Salary Calculation"},
 *     summary="Get payroll summary for an employee (current month)",
 *     security={{"Bearer": {}}},
 *     description="Fetches payroll summary of the current month including gross pay, deduction, leave count, status, and total pay.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"teamManagerName"},
 *                 @OA\Property(
 *                     property="teamManagerName",
 *                     type="string",
 *                     example="Tech_manager",
 *                     description="teamManager (must exist in team_magers table)"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Payroll summary fetched successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Payroll summary fetched successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="payroll_period", type="string", example="Jun 01, 2025 - Jun 30, 2025"),
 *                 @OA\Property(property="total_leaves_taken", type="integer", example=2),
 *                 @OA\Property(property="pay_day", type="string", example="Jul 10, 2025"),
 *                 @OA\Property(property="status", type="string", example="Paid"),
 *                 @OA\Property(property="gross_pay", type="number", format="float", example=37000),
 *                 @OA\Property(property="deduction", type="number", format="float", example=2150.50),
 *                 @OA\Property(property="total_pay", type="number", format="float", example=34849.50)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation failed",
 *         @OA\JsonContent(
 *             @OA\Property(property="errors", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Attendance or salary record not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="No attendance data found")
 *         )
 *     )
 * )
 */




// api for employee under a manager and there cost 

public function employeeOfAManagerAndThereCost(Request $request)
{   
        $validator = Validator::make($request->all(), [
            'teamManagerName' => 'required|exists:team_managers,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $teamManagerName = $request->teamManagerName;

            $totalEmp=0;
            $mangerWithEmployee=[];
            $teamMemberIds=[];
            $teamMemberId_single=[];
            $all_emp=[];
            $myarray=[];
            $teamLeaderData =[];
            $alldata=[];


             $getTeamList = TeamManager::with([
                    'employees',
                    'teams.teamLeader',
                    'teams.teamMembers.user'
                ])
                ->where('name', $teamManagerName)->first();

                if(!$getTeamList){
                    return response()->json([
                        'status' => false,
                        'message' => 'manager team not found',
                        'data' => [],                  
                    ]);
                }

        try{
            
        $getTeamList=  $getTeamList->teams;
        foreach ($getTeamList as $manager) {   
                $teamLeaderData =  $manager->teamLeader ;
                    $myarray[]= $teamLeaderData;
                    
                    foreach ($manager->teamMembers as $member) {
                    
                            $myarray[]=$member->user;
                    
                    }
            
                
                
            }
        $alldata= $this->OngoingPayrunSummaryFunctionUseInside($myarray);  
            
    
            
        return response()->json([
                    'status' => true,
                    'message' => 'manager team get successfully',
                    'data' =>  $alldata,                  
                ]);
            }catch(\Exception $e){

                    return response()->json([
                    'status' => false,
                    'message' => 'Failed due to'.$e->getMessage(),
                    'data' => [],                  
                     ]);
                }

          
    
}


    
// Helper function 

public function basicSalaryCalculation($salaryRecord,$attendanceData)
{
        $grossPay = (float)$salaryRecord->gross_salary;
        $epf_employee = $salaryRecord->epf_employee?$salaryRecord->epf_employee:0;
        $tax = ($grossPay > 21000) ? 200 : 0;
        $maindeduct=$tax+$epf_employee;
        $exactPayableAmount = $grossPay-$maindeduct;
        $bonus = 0;
        $workingDays = $attendanceData['working_days'] ?? 0;
        $monthDays =$attendanceData['total_days'];
        $perDayPay = $grossPay / max($monthDays, 1);
        $needtocutout =  $attendanceData['unpaidleave']+$attendanceData['absent'];
        $absentDeduction = round($perDayPay * $needtocutout, 2);
        $govermenttax= $tax;
        $emppf= $epf_employee;
        $totalDeduction =  round($absentDeduction+ $govermenttax+$emppf, 2);
        $totalPay = round($grossPay - $totalDeduction + $bonus, 2); 

        return [
            $grossPay,  
            $epf_employee ,
            $tax ,
            $maindeduct,
            $exactPayableAmount,
            $bonus ,
            $workingDays ,
            $monthDays ,
            $perDayPay ,
            $needtocutout,
            $absentDeduction ,
            $govermenttax,
            $emppf,
            $totalDeduction,
            $totalPay ,
        ];
}







}
