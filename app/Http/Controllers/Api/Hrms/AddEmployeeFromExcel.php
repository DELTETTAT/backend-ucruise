<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Events\ExcelUploaded;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\ImportUsersFromExcel;
use App\Imports\ValidateUsersExcel;
use App\Imports\ImportDriversFromExcel;
use App\Models\EmployeeSalary;
use App\Models\User;
use App\Models\SubUserAddresse;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Models\ExcelUploadStaging;
use App\Models\ExcelUploadError;
use App\Models\StoreWrongAddressFromExcelSheet;
use Maatwebsite\Excel\Validators\ValidationException;
use DB;
use App\Jobs\ValidateExcelUpload;

class AddEmployeeFromExcel extends Controller
{


     /**
     * @OA\Post(
     *     path="/uc/api/validateUsersExcel",
     *     operationId="validateUsersExcel",
     *     tags={"HRMS Employee"},
     *     summary="Validate Users Excel",
     *     security={ {"Bearer": {} }},
     *     description="Validate Users Excel",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="file", type="string",format="binary", description="please fill the excel file"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Validate Users Excel",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Validate Users Excel",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */


     public function validateUsersExcel(Request $request){
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv',
            ]);

            $child_DB = DB::connection()->getDatabaseName();

            $data = [
                'child_DB' => $child_DB,
                'database_path' => env("DB_HOST"),
                'database_username' => env("DB_USERNAME"),
                'database_password' => env("DB_PASSWORD"),
                'default_db' => env("DB_DATABASE"),
                'company_name' => auth('sanctum')->user()->company_name,
            ];

            try {
                Excel::import(new ValidateUsersExcel($data), $request->file('file'));
            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                // $failures = $e->failures();
                // $messages = [];
                // foreach ($failures as $failure) {
                //     $messages[] = "Row {$failure->row()} ({$failure->attribute()}): " . implode(', ', $failure->errors());
                // }
                $messages = array_map(
                    fn($failure) => "Row {$failure->row()} ({$failure->attribute()}): " . implode(', ', $failure->errors()),
                    $e->failures()
                );
                return response()->json([
                    'status' => false,
                    'message' => $messages,
                ], 422);
            } catch (\Exception $e) {
                // This will catch your heading error and return 422
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return response()->json([
                'status' => true,
                'message' => "Excel Sheet Validted Successfully",
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }



    /**
     * @OA\Post(
     *     path="/uc/api/addUsersFromExcel",
     *     operationId="addUsersFromExcel",
     *     tags={"HRMS Employee"},
     *     summary="add Users From Excel",
     *     security={ {"Bearer": {} }},
     *     description="add Users From Excel",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="file", type="string",format="binary", description="please fill the excel file"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="add Users From Excel",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="add Users From Excel",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */




    public function addUsersFromExcel(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv',
            ]);

            $child_DB = DB::connection()->getDatabaseName();

            $data = [
                'child_DB' => $child_DB,
                'database_path' => env("DB_HOST"),
                'database_username' => env("DB_USERNAME"),
                'database_password' => env("DB_PASSWORD"),
                'default_db' => env("DB_DATABASE"),
                'company_name' => auth('sanctum')->user()->company_name,
            ];

            try {
                // $this->connectDB(env("DB_DATABASE"));
                Excel::import(new ImportUsersFromExcel($data), $request->file('file'));
                 // Excel::queueImport(new ImportUsersFromExcel($data), $request->file('file'))->onQueue('imports')->allOnQueue('imports');
            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                $failures = $e->failures();
                $messages = [];
                foreach ($failures as $failure) {
                    $messages[] = "Row {$failure->row()} ({$failure->attribute()}): " . implode(', ', $failure->errors());
                }
                return response()->json([
                    'status' => false,
                    'message' => $messages,
                ], 422);
            } catch (\Exception $e) {
                // This will catch your heading error and return 422
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

             //$this->getFromImportEmployeeSalaryFromExcelsStoreThenDelete();
            // $this->storeLatLogAddressForExcelEmployee();

            return response()->json([
                'status' => true,
                'message' => "Users Added Successfully",
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


     /**
     * @OA\Post(
     *     path="/uc/api/addUsersFromExcelNewMethod",
     *     operationId="addUsersFromExcelNewMethod",
     *     tags={"HRMS Employee"},
     *     summary="add Users From Excel NewMethod",
     *     security={ {"Bearer": {} }},
     *     description="add Users From Excel",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="file", type="string",format="binary", description="please fill the excel file"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="add Users From Excel",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="add Users From Excel",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */


    // public function addUsersFromExcelNewMethod(Request $request){
    //     try {
    //         $request->validate([
    //             'file' => 'required|mimes:xlsx,xls,csv',
    //         ]);

    //         $uploadId = Str::uuid(); // Unique ID for each upload
    //         $file = $request->file('file');
    //         $spreadsheet = IOFactory::load($file);
    //         $sheet = $spreadsheet->getActiveSheet();
    //         $rows = $sheet->toArray(null, true, true, true); // Raw rows
    //         $header = array_shift($rows); // First row = headers

    //         $chunkSize = 100;
    //         $batches = array_chunk($rows, $chunkSize);

    //         foreach ($batches as $index => $batch) {
    //             $formattedBatch = [];

    //             foreach ($batch as $row) {
    //                 $formattedRow = [];
    //                 // Check: skip if sr_no is not set or empty
    //                 if (!isset($row['A']) || trim($row['A']) === '') {
    //                     continue; // skip this row
    //                 }

    //                 foreach ($header as $key => $heading) {
    //                     $formattedRow[strtolower(str_replace(' ', '_', $heading))] = $row[$key];
    //                 }

    //                 $formattedBatch[] = $formattedRow;
    //             }

    //             if (!empty($formattedBatch)) {
    //                     ExcelUploadStaging::create([
    //                     'upload_id' => $uploadId,
    //                     'batch_number' => $index + 1,
    //                     'data' => json_encode($formattedBatch),
    //                     'status' => 'pending',
    //                 ]);
    //             }

    //         }

    //         event(new ExcelUploaded($uploadId));
    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Excel data staged successfully.',
    //             'upload_id' => $uploadId,
    //         ]);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $th->getMessage()
    //         ], 500);
    //     }
    // }



    public function addUsersFromExcelNewMethod(Request $request){
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv',
            ]);

            $uploadId = Str::uuid();
            $file = $request->file('file');
            $fileName = $uploadId . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('uploads', $fileName);

            //event(new ExcelUploaded($uploadId,$fileName));
            $databaseInfo = [
                'child_DB' => DB::connection()->getDatabaseName(),
                'company_name' => auth('sanctum')->user()->company_name,
                'uploadId' => $uploadId,
            ];
            $defaultDB = env("DB_DATABASE");
            $this->connectDB($defaultDB);
            //ProcessExcelUpload::dispatch($event->uploadId,$databaseInfo)->onQueue('default');
            // $filePath = storage_path('app/uploads/' . $event->fileName);
            //$fileName =  $event->fileName;
            ValidateExcelUpload::dispatch($databaseInfo, $fileName)->onQueue('default');
            return response()->json([
                'status' => true,
                'message' => 'Excel data staged successfully.',
                'upload_id' => $uploadId,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }



     /**
     * @OA\Post(
     *     path="/uc/api/addDriversFromExcel",
     *     operationId="addDriversFromExcel",
     *     tags={"HRMS Employee"},
     *     summary="add Drivers From Excel",
     *     security={ {"Bearer": {} }},
     *     description="add drivers From Excel",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="file", type="string",format="binary", description="please fill the excel file"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="add drivers From Excel",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="add drivers From Excel",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function addDriversFromExcel(Request $request)
    {

        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv',
            ]);

            $child_DB = DB::connection()->getDatabaseName();

            $data = [
                'child_DB' => $child_DB,
                'database_path' => env("DB_HOST"),
                'database_username' => env("DB_USERNAME"),
                'database_password' => env("DB_PASSWORD"),
                'default_db' => env("DB_DATABASE"),
                'company_name' => auth('sanctum')->user()->company_name,
            ];

            try {
                Excel::import(new ImportDriversFromExcel($data), $request->file('file'));

            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                $failures = $e->failures();
                $messages = [];
                foreach ($failures as $failure) {
                    $messages[] = "Row {$failure->row()} ({$failure->attribute()}): " . implode(', ', $failure->errors());
                }
                return response()->json([
                    'status' => false,
                    'message' => $messages,
                ], 422);
            } catch (\Exception $e) {
                // This will catch your heading error and return 422
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return response()->json([
                'status' => true,
                'message' => "Uploading Drivers in background",
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }




    // public function insideCalculate($salary, $epfType)
    // {
    //     try {
    //         // Basic input validations
    //         if (!is_numeric($salary) || $salary <= 0) {
    //             throw new \InvalidArgumentException("Salary must be a positive number.");
    //         }

    //         if (!in_array($epfType, [1, 2, 3])) {
    //             throw new \InvalidArgumentException("Invalid EPF type. Accepted values are 1, 2, or 3.");
    //         }

    //         // Basic calculation
    //         $basic = match (true) {
    //             $salary < 21000 => 10500,
    //             $salary < 25000 => 11500,
    //             $salary < 30000 => 12500,
    //             $salary < 33000 => 13500,
    //             $salary < 35000 => 14000,
    //             $salary < 40000 => 15000,
    //             default => $salary * 0.45
    //         };

    //         // HRA
    //         $hra = round(match (true) {
    //             $salary == 10500 => 0,
    //             $salary < 15001 => $salary - $basic,
    //             $salary < 30000 => $basic * 0.4,
    //             $salary < 50000 => $basic * 0.45,
    //             default => $basic * 0.5
    //         });

    //         // Medical Allowance
    //         $medical = round(match (true) {
    //             $salary > 18999.99 => $basic * 0.2,
    //             default => 0,
    //         });

    //         // Conveyance Allowance (strict order!)
    //         $conveyance = round(match (true) {
    //             $salary > 25000 => $basic * 0.15,
    //             $salary > 30000 => $basic * 0.3,
    //             $salary > 35000 => $basic * 0.4,
    //             default => 0
    //         });

    //         // BONUS = Total Salary - sum of other components
    //         $bonus = $salary - $basic - $hra - $medical - $conveyance;

    //         // GROSS = sum of all earnings
    //         $gross = $basic + $hra + $medical + $conveyance + $bonus;

    //         // Professional Tax
    //         $ptax = $salary > (250000 / 12) ? 200 : 0;

    //         // EPF Logic
    //         if ($epfType == 1) {
    //             $epf_employee = round(min($basic, 15000) * 0.12);
    //             $epf_employer = $epf_employee;
    //         } elseif ($epfType == 2) {
    //             $epf_employee = 0;
    //             $epf_employer = round(min($basic, 15000) * 0.12) * 2;
    //         } else {
    //             $epf_employee = 0;
    //             $epf_employer = 0;
    //         }

    //         // ESI Contributions
    //         $esi_employee = round($salary > 20999.99 ? 0 : $salary * 0.0075, 2);
    //         $esi_employer = round($salary > 20999.99 ? 0 : $salary * 0.0325, 2);

    //         // Net Salary
    //         $take_home = $gross - $epf_employee - $ptax - $esi_employee;

    //         // Total Package
    //         $total_package = $gross + $epf_employer + $esi_employer;

    //         // Return final salary structure
    //         return [
    //             'basic' => $basic,
    //             'hra' => $hra,
    //             'medical' => $medical,
    //             'conveyance' => $conveyance,
    //             'bonus' => $bonus,
    //             'gross_salary' => $gross,
    //             'professional_tax' => $ptax,
    //             'epf_employee' => $epf_employee,
    //             'esi_employee' => $esi_employee,
    //             'take_home' => $take_home,
    //             'epf_employer' => $epf_employer,
    //             'esi_employer' => $esi_employer,
    //             'total_package_salary' => $total_package,
    //         ];
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Salary calculation failed: ' . $e->getMessage()
    //         ], 400);
    //     }
    // }


    // public function getFromImportEmployeeSalaryFromExcelsStoreThenDelete(){
    //         DB::beginTransaction();

    //         try {
    //                     $importedSalaries = DB::table('import_employees_salary_from_excels')->get();

    //                     if ($importedSalaries->isEmpty()) {
    //                     \Log::error('No salary data found to process'.time());
    //                         return response()->json([
    //                             'status' => false,
    //                             'message' => 'No salary data found to process'
    //                         ], 404);
    //                     }

    //                     $createdRecords = [];
    //                     $errors = [];

    //                     foreach ($importedSalaries as $imported) {
    //                         try {
    //                             $breakdown = $this->insideCalculate($imported->salary, $imported->epf_type);

    //                             $newSalaryRecord = EmployeeSalary::create([
    //                                 'employee_id' => $imported->employee_id,
    //                                 'basic' => $breakdown['basic'],
    //                                 'hra' => $breakdown['hra'],
    //                                 'medical' => $breakdown['medical'],
    //                                 'conveyance' => $breakdown['conveyance'],
    //                                 'bonus' => $breakdown['bonus'],
    //                                 'gross_salary' => $breakdown['gross_salary'],
    //                                 'professional_tax' => $breakdown['professional_tax'],
    //                                 'epf_employee' => $breakdown['epf_employee'],
    //                                 'esi_employee' => $breakdown['esi_employee'],
    //                                 'take_home' => $breakdown['take_home'],
    //                                 'epf_employer' => $breakdown['epf_employer'],
    //                                 'esi_employer' => $breakdown['esi_employer'],
    //                                 'total_package_salary' => $breakdown['total_package_salary'],
    //                                 'increment_from_date' => now()->format('Y-m-d'),
    //                                 'increment_to_date' => now()->addYear()->format('Y-m-d'),
    //                                 'is_active' => 1,
    //                                 'epf_type' => $imported->epf_type,
    //                                 'reason' => 'Bulk import salary calculation'
    //                             ]);

    //                             $createdRecords[] = $newSalaryRecord->id;

    //                         } catch (\Exception $e) {
    //                             $errors[] = [
    //                                 'employee_id' => $imported->employee_id,
    //                                 'error' => $e->getMessage()
    //                             ];
    //                         }
    //                     }

    //                     // Only truncate if all records processed successfully
    //                     if (empty($errors)) {
    //                         DB::table('import_employees_salary_from_excels')->truncate();
    //                     }

    //                     DB::commit();

    //                 \Log::error('Process done successfully'.time());

    //         } catch (\Exception $e) {
    //             DB::rollBack();
    //             \Log::error('Error fetching imported salaries: ' . $e->getMessage());
    //         }
    // }





    /**
     * @OA\Post(
     *     path="/uc/api/getUploadExcelResponse",
     *     operationId="getUploadExcelResponse",
     *     tags={"HRMS Employee"},
     *     summary="get Upload Excel Response",
     *     security={ {"Bearer": {} }},
     *     description="get Upload Excel Response",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="upload_Id", type="string", description="excel file uploadId"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="get Upload Excel Response",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="get Upload Excel Response",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */



    public function getUploadExcelResponse(Request $request){
        try {
            $request->validate([
                'upload_Id' => 'required'
            ]);

            $validationErrors = ExcelUploadError::where('upload_Id',$request->upload_Id)->get();
            if ($validationErrors->isNotEmpty()) {
                 return response()->json([
                    'status' => 'validation',
                    'message' => 'Validation errors found in uploaded data.',
                    'errors' => $validationErrors->pluck('errors'),
                ]);
            }else {
                $storedData = ExcelUploadStaging::where('upload_Id',$request->upload_Id)->get();
                $total = $storedData->count();
                $doneCount = $storedData->where('status', 'done')->count();
                $pendingCount = $storedData->where('status', 'pending')->count();
                $faildCount = $storedData->where('status', 'failed')->count();
                $processingCount = $storedData->where('status', 'processing')->count();

                // Avoid division by zero
                if ($storedData->isNotEmpty()) {
                      if ($total > 0) {
                            $donePercentage = round(($doneCount / $total) * 100, 2);
                            $pendingPercentage = round(($pendingCount / $total) * 100, 2);

                            // Decide final status
                            if ($donePercentage === 100.00) {
                                $status = 'done';
                            } elseif ($processingCount > 0) {
                                $status = 'processing';
                            } elseif ($pendingCount === $total) {
                                $status = 'pending';
                            } elseif ($faildCount > 0) {
                                $status = 'faild';
                            }else {
                                $status = 'unknown';
                            }
                            return response()->json([
                                'status' => $status,
                                'total' => $total,
                                'Faild' => $faildCount,
                                'done' => $doneCount,
                                'pending' => $pendingCount,
                                'done_percentage' => $donePercentage,
                                'pending_percentage' => $pendingPercentage,
                            ]);
                        }else {
                            return response()->json([
                                'status' => 'notfound',
                                'message' => "Not Found Data"
                            ]);
                        }
                }else {
                    return response()->json([
                                'status' => 'notfound',
                                'message' => "Not Found"
                            ]);
                }

            }

            return response()->json([
                'status' => true,
            ]);


        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }



     /**
     * @OA\Post(
     *     path="/uc/api/getWrongAddressNotification",
     *     operationId="getWrongAddressNotification",
     *     tags={"HRMS Employee"},
     *     summary="get Wrong Address Notification",
     *     security={ {"Bearer": {} }},
     *     description="get Wrong Address Notification",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="upload_Id", type="string", description="excel file uploadId"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="get Wrong Address Notification",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="get Wrong AddressNotification",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */


     public function getWrongAddressNotification(){
         try {
                $addressData = StoreWrongAddressFromExcelSheet::with('user')->get();

                return response()->json([
                    'status' => true,
                    'data' => $addressData
                ]);
         } catch (\Throwable $th) {
               return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
         }
     }


}
