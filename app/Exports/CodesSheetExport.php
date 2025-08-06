<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CodesSheetExport implements FromArray, WithTitle, WithHeadings, WithStyles
{
    // protected $data;

    // public function __construct(array $data){
    //     $this->data = $data;
    // }

    public function array(): array
    {
       // return $this->data;
       return [
            [
                'S. No.' => 1,
                'Code' => "P",
                'Description' => "Present",
                'Reason' => "",
            ],
             [
                'S. No.' => 2,
                'Code' => "UPL",
                'Description' => "Unpaid Planned leave",
                'Reason' => "In case of no leave balance/or RM can mark it for unplanned approved leave."."\n". " This will only deduct the leave balance and salary of the day",
            ],
             [
                'S. No.' => 3,
                'Code' => "PL",
                'Description' => "Paid Leave",
                'Reason' => "in case of preapproved leave. Only leave balance will be deducted",
            ],
            [
                'S. No.' => 4,
                'Code' => "HD",
                'Description' => "Paid Half Day",
                'Reason' => "in case of preapproved Half day. Only leave balance will be deducted",
            ],
            [
                'S. No.' => 5,
                'Code' => "UHD",
                'Description' => "Unpaid Half day",
                'Reason' => "In case of no leave balance/or RM can mark it for unplanned approved Half day."."\n". "This will only deduct the leave balance and salary of the half day.",
            ],
            [
                'S. No.' => 6,
                'Code' => "ABS",
                'Description' => "Absent",
                'Reason' => "Absent without information from work. Salary,Leave Balance & the appraisal will hamper for each ABS. ",
            ],
            [
                'S. No.' => 7,
                'Code' => "CH",
                'Description' => "Company Holiday",
                'Reason' => "when company is closed for every one",
            ],
            [
                'S. No.' => 8,
                'Code' => "EC",
                'Description' => "Employee Compesation",
                'Reason' => "when we need to add their OT for extra day worked.",
            ],
            [
                'S. No.' => 9,
                'Code' => "ML",
                'Description' => "Parenting leaves",
                'Reason' => "in case of ML/PTL",
            ],
            [
                'S. No.' => 10,
                'Code' => "WL",
                'Description' => "Wedding Leave",
                'Reason' => "in case of self wedding (Only in healthcare)",
            ],
            [
                'S. No.' => 11,
                'Code' => "BL",
                'Description' => "Bereavement Leaves",
                'Reason' => "The unfortunate demise of an immediate family member not extended family. Only 3 days.  (Only in healthcare)",
            ],
            [
                'S. No.' => 12,
                'Code' => "CL",
                'Description' => "Celebration Leave",
                'Reason' => "Birthday of Spouse/ kids/ parents/ fiancée/ fiancé/ Girlfriend/ Boyfriend/ self or Self weddinganniversary."."\n". " Prior approval of 5days. Can be availedonce a year (Only in healthcare)",
            ],
        ];

    }

    public function title(): string
    {
        return 'Codes';
    }


     public function headings(): array
    {
         return [
            'S. No.',
            'Code',
            'Description',
            'Reason',
         ];
    }

    public function styles(Worksheet $sheet){

        $sheet->getStyle("A1:C1")->getFont()->setBold(true);
        $sheet->getStyle("B1:B13")->getFont()->setBold(true);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getRowDimension(13)->setRowHeight(30);


        $sheet->getStyle("A1:D1")
              ->getAlignment()
              ->setWrapText(true)
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
              ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $sheet->getStyle("A1:A13")
        ->getAlignment()
        ->setWrapText(true)
        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle("B1:B13")
        ->getAlignment()
        ->setWrapText(true)
        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // $sheet->getColumnDimension('D')->setAutoSize(false);
        // $sheet->getColumnDimension('D')->setWidth(135);
        // $sheet->getStyle('D')->getAlignment()->setWrapText(false);
        $sheet->getColumnDimension('D')->setWidth(135);
        $sheet->getStyle('D1:D100')->getAlignment()->setWrapText(true);

        // Borders
        $sheet->getStyle("A1:D13")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
    }


}

?>
