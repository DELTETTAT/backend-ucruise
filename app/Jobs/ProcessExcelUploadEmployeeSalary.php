<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\EmployeeSalary;
use DB;

class ProcessExcelUploadEmployeeSalary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $child_DB;
    public function __construct($child_DB)
    {
        $this->child_DB = $child_DB;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
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
                                    'increment_to_date' => now()->addYear()->format('Y-m-d'),
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
                            info('truncate...............');
                        }

                        DB::commit();

                    \Log::error('Process done successfully'.time());

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error fetching imported salaries: ' . $e->getMessage());
            }


    }



    public function insideCalculate($salary, $epfType){
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


}
