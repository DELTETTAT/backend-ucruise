<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;


class AttendanceSheetExport implements FromArray, WithTitle, WithStyles, WithEvents  //FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $data;
    protected $month;

    public function __construct(array $data, $month){
        $this->data = $data;
        $this->month = Carbon::parse($month)->format('Y-m');//now()->format('Y-m');
    }

    public function array(): array
    {
        $rows = [];
        $startDate = Carbon::parse($this->month . '-01')->startOfMonth();
        $endDate = Carbon::parse($this->month . '-01')->endOfMonth();

        // First Header Row: Titles
        $headerRow1 = [
            'S. No.',
            'Emp. Code',
            'Emp. Name',
            'Total Paid Days',
            'Present',
            'Count Of ML',
            'Count Of WL',
            'Count Of BL',
            'Count Of CL',
            'Count Of PL',
            'Half Day',
            'Count Of UPL',
            'Count Of Unpaid Half Day',
            'Count Of EC',
            'Company Holiday',
        ];

        // Second Header Row: Day Names
        //$headerRow2 = array_fill(0, count($headerRow1), ''); // Empty cells under fixed headers

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dayLabel = $current->format('j-M-y');
            $dayName = strtoupper($current->format('D')); // SUN, MON, etc.
            // $headerRow1[] = $dayLabel;
            // $headerRow2[] = $dayName;
            $headerRow1[] = $dayLabel . "\n" . $dayName;
            $current->addDay();
        }

        // Add both header rows

        $rows[] = $headerRow1;
        //$rows[] = $headerRow2;

        // Data rows
        foreach ($this->data as $index => $employee) {
            $row = [
                 $index + 1,
                 $employee['unique_id'],
                 $employee['Name'],
                 //$employee['TotalPaidDays'],
                 ' ',
                 //$employee['Present'],
                 ' ',
                 $employee['MedicalLeave'],  //Count Of ML
                 $employee['WenddingLeave'],  //Count Of WL
                 $employee['BereavementLeave'],  //Count Of BL
                 $employee['CasualLeave'],  //Count Of CL
                 //$employee['PaidLeave'],   // Count Of PL
                 ' ',
                 //$employee['halfday'],     // Half Day
                 ' ',
                 //$employee['UnpaidLeave'],   // Count Of UPL
                 ' ',
                 $employee['UnpaidHalfDay'],   // Count Of Unpaid Half Day
                      "0",                          // Count Of EC
                 //$employee['CompanyHolidays'],
                 ' ',

            ];

            $current = $startDate->copy();


            while ($current->lte($endDate)) {
                if ($current->gt(now())) {
                    $current->addDay();
                    continue; // future date skip
                }

                $row[] = $employee['daily'][$current->format('Y-m-d')] ?? '';
                $current->addDay();
            }

            $rows[] = $row;
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Attendance';
    }

    public function styles(Worksheet $sheet){
        // Row heights
        $sheet->getRowDimension(1)->setRowHeight(40); // thoda zyada height for wrapText
        $lastRow = 1 + count($this->data);

        // Wrap text for header row
        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$highestColumn}1")
              ->getAlignment()
              ->setWrapText(true)
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
              ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Center align for all cells (including data)
        $sheet->getStyle("A1:{$highestColumn}{$lastRow}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

         // Left align for 'Emp. Name' column (C)
        $sheet->getStyle("C1:C{$lastRow}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Font bold for headers
        $sheet->getStyle("A1:{$highestColumn}1")->getFont()->setBold(true);

         // Set 'Emp. Name' column width
        $sheet->getColumnDimension('C')->setWidth(25); // Adjust as needed
        // Ensure you're getting the last column as a letter (e.g. 'Z', 'AA')
        $lastColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        $startColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString('P');

        // Loop from column P to highest column
        for ($colIndex = $startColIndex; $colIndex <= $lastColIndex; $colIndex++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($colLetter)->setWidth(10);
        }

        // Background Colors (same as before)

           $sheet->getStyle('A1:Z1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F4E78'); // dark blue
           $sheet->getStyle("D1:D{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('808080'); // dark grey
           $sheet->getStyle("E1:O{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F4B084'); // light orange
           $sheet->getStyle("P1:{$highestColumn}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9D9D9'); // light grey

        // Borders
        $sheet->getStyle("A1:{$highestColumn}{$lastRow}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);


        return [];
    }



    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = count($this->data) + 1;

                // First remove all existing formulas
                for ($row = 2; $row <= $lastRow; $row++) {
                    $sheet->setCellValue("D{$row}", null);
                    $sheet->setCellValue("E{$row}", null);
                }

                // Then add new formulas
                for ($row = 2; $row <= $lastRow; $row++) {
                    // Total Paid Days formula (updated as per your requirement)
                    $totalPaidFormula = "=E{$row}+J{$row}+K{$row}+N{$row}+O{$row}+(COUNTIF(P{$row}:AV{$row},\"UHD\")/2)";
                    $sheet->setCellValue("D{$row}", $totalPaidFormula);

                    // Count of Present formula
                    $presentCountFormula = "=COUNTIF(P{$row}:AV{$row},\"P\")";
                    $sheet->setCellValue("E{$row}", $presentCountFormula);

                    // Count of Company Holiday formula
                    $CompanyHolidayCountFormula = "=COUNTIF(P{$row}:AV{$row},\"CH\")";
                    $sheet->setCellValue("O{$row}", $CompanyHolidayCountFormula);

                    // Count of Paid Leave formula
                    $paidLeaveCountFormula = "=COUNTIF(P{$row}:AV{$row},\"PL\")";
                    $sheet->setCellValue("J{$row}", $paidLeaveCountFormula);

                    // Count of Paid Half Days formula
                    $paidHalfDayCountFormula = "=COUNTIF(P{$row}:AV{$row},\"HD\")";
                    $sheet->setCellValue("K{$row}", $paidHalfDayCountFormula);

                    // Count of Un Paid Half Days formula
                    $unPaidHalfDayCountFormula = "=COUNTIF(P{$row}:AV{$row},\"UHD\")";
                    $sheet->setCellValue("M{$row}", $unPaidHalfDayCountFormula);

                    // Count of Un paid leaves formula
                    $unPaidLeaveCountFormula = "=COUNTIF(P{$row}:AV{$row},\"UPL\")";
                    $sheet->setCellValue("L{$row}", $unPaidLeaveCountFormula);
                }

                // Force Excel to recalculate
                $sheet->setSelectedCells("A1");

                // Protection settings
                $sheet->getStyle("D2:O{$lastRow}")->getProtection()->setLocked(true);
$sheet->getStyle("P2:AV{$lastRow}")->getProtection()->setLocked(false);

// 2. THEN enable protection
$sheet->getProtection()->setSheet(true);

                $dropdownOptions = '"PL,P,ABS,HD,UHD,CH"';

                for ($col = 'P'; $col !== 'AW'; $col++) {
                    for ($row = 2; $row <= $lastRow; $row++) {
                        $cell = $col . $row;
                        // 👇 Check if the cell has value
                        if (trim((string)$sheet->getCell($cell)->getValue()) !== '') {
                            $validation = $sheet->getCell($cell)->getDataValidation();
                            $validation->setType(DataValidation::TYPE_LIST);
                            $validation->setFormula1($dropdownOptions);
                            $validation->setShowDropDown(true);
                        }
                    }
                }

            }
        ];
    }



}
