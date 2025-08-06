<?php
// app/Console/Commands/RunSalaryImport.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\EmployeeSalary;

class RunSalaryImport extends Command
{
    protected $signature = 'salary:import';
    protected $description = 'Run the salary import process hourly';

    public function handle()
    {
        DB::transaction(function () {
            $importedSalaries = DB::table('import_employees_salary_from_excels')->get();

            if ($importedSalaries->isEmpty()) {
                $this->info('No salary data to process at '.now());
                \Log::info('No salary data to process at '.now());
                return;
            }

            $createdRecords = [];
            $errors = [];

            foreach ($importedSalaries as $imported) {
                try {
                    // Assuming you have access to your calculation logic here
                    $breakdown = $this->calculateSalaryBreakdown(
                        $imported->salary, 
                        $imported->epf_type
                    );

                    if (!$breakdown) {
                        Log::error('Failed to calculate salary breakdown for employee ID: '.$imported->employee_id);
                    }

                    $newSalaryRecord = EmployeeSalary::create([
                            'employee_id' => @$imported->employee_id,
                            'basic' => @$breakdown['basic'],
                            'hra' => @$breakdown['hra'],
                            'medical' => @$breakdown['medical'],
                            'conveyance' => @$breakdown['conveyance'],
                            'bonus' => @$breakdown['bonus'],
                            'gross_salary' => @$breakdown['gross_salary'],
                            'professional_tax' => @$breakdown['professional_tax'],
                            'epf_employee' => @$breakdown['epf_employee'],
                            'esi_employee' => @$breakdown['esi_employee'],
                            'take_home' => @$breakdown['take_home'],
                            'epf_employer' => @$breakdown['epf_employer'],
                            'esi_employer' => @$breakdown['esi_employer'],
                            'total_package_salary' => @$breakdown['total_package_salary'],
                            'increment_from_date' => now()->format('Y-m-d'),
                            'increment_to_date' => null,
                            'is_active' => 1,
                            'epf_type' => @$imported->epf_type,
                            'reason' => 'Bulk import salary calculation'
                        ]);

                    $createdRecords[] = $newSalaryRecord->id;
                } catch (\Exception $e) {
                    $errors[] = [
                        'employee_id' => $imported->employee_id,
                        'error' => $e->getMessage()
                    ];
                    $this->error("Error processing employee {$imported->employee_id}: ".$e->getMessage());
                }
            }

            if (empty($errors)) {
                DB::table('import_employees_salary_from_excels')->truncate();
                $this->info("Successfully processed ".count($createdRecords)." salary records");
                \Log::info("Successfully processed ".count($createdRecords)." salary records");
            } else {
                $this->warn("Completed with ".count($errors)." errors");
                \Log::warning("Salary import completed with ".count($errors)." errors");
            }
        });
    }

    protected function calculateSalaryBreakdown($salary, $epfType)
    {
        try {
            // Basic input validations
            if (!is_numeric($salary) || $salary <= 0) {
                return false;
                throw new \InvalidArgumentException("Salary must be a positive number.");
            }

            if (!in_array($epfType, [1, 2, 3])) {
                 return false;
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
            return false;
        }
    }
}


?>