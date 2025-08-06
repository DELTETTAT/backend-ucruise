<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use App\Imports\ValidateUsersExcel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\ExcelUploadStaging;
use App\Models\ExcelUploadError;
use App\Jobs\ProcessExcelUpload;
use DB;
use Maatwebsite\Excel\Facades\Excel;

class ValidateExcelUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $fileName;
    public $databaseInfo;
    public function __construct($databaseInfo,$fileName,)
    {
        $this->fileName = $fileName;
        $this->databaseInfo = $databaseInfo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

             //DB::setDefaultConnection($this->databaseInfo['child_DB']);
             $this->connectDB($this->databaseInfo['child_DB']);
             //Excel::import(new ValidateUsersExcel($this->databaseInfo), 'uploads/' . $this->fileName, 'local');

             Excel::import(new ValidateUsersExcel($this->databaseInfo),'uploads/' . $this->fileName,'local',\Maatwebsite\Excel\Excel::XLSX,
                [
                    'disableTransactions' => true,
                ]
             );

                // $file =  storage_path('app/uploads/' . $this->fileName);
                $file =  storage_path('app/public/uploads/' . $this->fileName);
                $uploadId = $this->databaseInfo['uploadId'];
                $spreadsheet = IOFactory::load($file);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray(null, true, true, true); // Raw rows
                $header = array_shift($rows); // First row = headers
                $chunkSize = 100;
                $batches = array_chunk($rows, $chunkSize);

                foreach ($batches as $index => $batch) {
                    $formattedBatch = [];

                    foreach ($batch as $row) {
                        $formattedRow = [];
                        // Check: skip if sr_no is not set or empty
                        if (!isset($row['A']) || trim($row['A']) === '') {
                            continue; // skip this row
                        }

                        foreach ($header as $key => $heading) {
                            $formattedRow[strtolower(str_replace(' ', '_', $heading))] = $row[$key];
                        }

                        $formattedBatch[] = $formattedRow;
                    }

                    if (!empty($formattedBatch)) {
                            ExcelUploadStaging::create([
                            'upload_id' => $uploadId,
                            'batch_number' => $index + 1,
                            'data' => json_encode($formattedBatch),
                            'status' => 'pending',
                        ]);
                    }

                }

                ProcessExcelUpload::dispatch($uploadId,$this->databaseInfo)->onQueue('default');
                info('sucesssssssss..........');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
             $failures = $e->failures();
                $messages = [];
                foreach ($failures as $failure) {
                    $messages[] = "Row {$failure->row()} ({$failure->attribute()}): " . implode(', ', $failure->errors());
                }
                $errors = array_chunk($messages, 100);
                info('fail.......'.json_encode($messages));
                ExcelUploadError::create([
                     'upload_id' => $this->databaseInfo['uploadId'],
                     'errors' => json_encode($messages),
                ]);
        }catch (\Exception $e) {
            // ye handle karega aapke manual exception ko like: "Duplicate emails found..."
            info('custom error.......' . $e->getMessage());

            ExcelUploadError::create([
                'upload_id' => $this->databaseInfo['uploadId'],
                'errors' => json_encode([$e->getMessage()]),
            ]);
        }
    }


     public function connectDB($db_name)
    {

        $default = [
            "driver" => env("DB_CONNECTION", "mysql"),
            "host" => env("DB_HOST"),
            "port" => env("DB_PORT"),
            "database" => $db_name,
            "username" => env("DB_USERNAME"),
            "password" => env("DB_PASSWORD"),
            "charset" => "utf8mb4",
            "collation" => "utf8mb4_unicode_ci",
            "prefix" => "",
            "prefix_indexes" => true,
            "strict" => false,
            "engine" => null,
        ];
        Config::set("database.connections.$db_name", $default);
        DB::purge($db_name);
        DB::reconnect($db_name);
        DB::setDefaultConnection($db_name);
        Config::set("client_id", 1);
        Config::set("client_connected", true);
    }









}
