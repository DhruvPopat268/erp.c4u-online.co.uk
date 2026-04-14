<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WeeklyEmailDataExport implements FromCollection, WithHeadings, WithStyles
{
    protected $weeklyEmail;

    public function __construct(Collection $weeklyEmail)
    {
        $this->weeklyEmail = $weeklyEmail;
    }

    public function collection()
    {
        if ($this->weeklyEmail->isEmpty()) {
            return collect([]);
        }

        // Use the data passed from the controller
        return $this->weeklyEmail->map(function ($datas) {
            // Decode the JSON string for files
            $files = json_decode($datas->files, true);

            return [
                'account_no' => $datas->companyDetails ? $datas->companyDetails->account_no : 'N/A',
                'company_name' => $datas->types ? $datas->types->name : 'N/A',
                'files_count' => is_array($files) ? count($files) : 0,
                'status' => $datas->status,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Account ID',
            'Company Name',
            'Attachment Files Count',
            'Email Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        foreach ($this->weeklyEmail as $index => $weeklyEmail) {
            $cell = 'D'.($index + 2); // Adjusting for the heading row
            if ($weeklyEmail->status === 'FAILED') {
                $sheet->getStyle($cell)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => Color::COLOR_WHITE],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => 'FFFF0000', // Red background
                        ],
                    ],
                ]);
            }
        }
    }
}
