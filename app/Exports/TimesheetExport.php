<?php


namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TimesheetExport implements WithMultipleSheets
    {
        protected $attendanceData;
       // protected $codesData;
        protected $separationData;
        protected $date;

        public function __construct(array $attendanceData, array $separationData, $date)
        {
            $this->attendanceData = $attendanceData;
         //   $this->codesData = $codesData;
            $this->separationData = $separationData;
            $this->date = $date;
        }

        public function sheets(): array
        {
            return [
                new CodesSheetExport(),
                new AttendanceSheetExport($this->attendanceData,$this->date),
                new SeparationSheetExport($this->separationData),
            ];
        }
    }



?>
