<?php




namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SeparationSheetExport implements FromArray, WithTitle, WithStyles
{
    protected $data;

    public function __construct(array $data){
        $this->data = $data;
    }

    public function array(): array
    {
        //return $this->data;
         $headerRow1 = [
            'S. No.',
            'Emp. Code',
            'Name',
            'Domain',
            'Location',
            'Separation type',
            'Notice Served date (DD-MMM-YY)',
            'Last working date (DD-MMM-YY)',
            'Reason',
            'Description Of Reason',
            'salary process',
            'GOOD for Rehire',
            'Remarks',
        ];
        $rows[] = $headerRow1;


        foreach ($this->data as $index => $employee) {
             $rows[]  = [
                 $index + 1,
                 $employee['unique_id'],
                 $employee['Name'],
                 $employee['Domain'],
                 $employee['Location'],
                 $employee['Separation_type'],
                 $employee['Notice_served_date'],
                 $employee['Last_working_date'],
                 $employee['Reason'],
                 $employee['Description_Of_reason'],
                 $employee['salary_process'],
                 $employee['GOOD_for_rehire'],
                 $employee['Remarks'],
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Separation';
    }


    public function styles(Worksheet $sheet){
            $lastRow = 1 + count($this->data);
            $highestColumn = $sheet->getHighestColumn();

            $sheet->getRowDimension(1)->setRowHeight(50);
            $sheet->getColumnDimension('J')->setWidth(45);
            $columns = ['B','C','D','E','F','K','L'];
            foreach ($columns as $col) {
                $sheet->getColumnDimension($col)->setWidth(20);
            }
            $columns2 = ['G','H','I'];
            foreach ($columns2 as $col) {
                $sheet->getColumnDimension($col)->setWidth(25);
            }

            $sheet->getStyle('A1:M1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F4B084'); //  light orange
            $sheet->getStyle('J1:J100')->getAlignment()->setWrapText(true);

             $sheet->getStyle("A1:M{$lastRow}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

            $sheet->getStyle("A1:M1")->getFont()->setBold(true);

            // Center align for all cells (including data)
            $sheet->getStyle("A1:{$highestColumn}{$lastRow}")
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $sheet->getStyle("J2:J{$lastRow}")
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $sheet->getStyle("A1:M{$lastRow}")
                ->getAlignment()
                ->setWrapText(true); // <-- IMPORTANT
    }


}









?>
