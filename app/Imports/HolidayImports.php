<?php

namespace App\Imports;

use App\Models\Holiday;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class HolidayImports implements ToCollection
{
    /**
    * @param Collection $collection
    */

    public function rules(): array
    {
        return [
            '*.holiday' => 'required',
            '*.holiday_name' => 'required|string',
            '*.holiday_description' => 'nullable',
        ];
    }


    function excelDateToString($excelDate)
    {
        if (is_numeric($excelDate)) {
            // Excel's epoch starts at 1899-12-30
            $unixDate = ($excelDate - 25569) * 86400;
            return gmdate('Y-m-d', $unixDate);
        }
        // Agar already string hai, to format check karo
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $excelDate)) {
            return $excelDate;
        }
        return null;
    }



    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $holidayDate = excelDateToString($row['holiday']);
            $existingHoliday = DB::table('holidays')->where('date', $holidayDate)->first();
            $existingHoliday = DB::table('holidays')->insert([
                'date' => $holidayDate,
                'name' => $row['holiday_name'],
                'description' => $row['holiday_description'] ?? null,
            ]);

        }




    }
}
