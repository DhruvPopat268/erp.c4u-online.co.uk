<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmailLogsExport implements FromCollection, WithHeadingRow, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        if ($this->data->isEmpty()) {
            return collect([]);
        }

        return $this->data->map(function ($datas, $index) {
            return [
                $index + 1, // Index number (1-based)
                $datas->email,
                $datas->subject,
                $datas->status,
                Carbon::parse($datas->send_at)->format('d/m/Y'), // Convert to dd/mm/yyyy format
                $datas->creator ? $datas->creator->name : 'N/A', // Get the creator's name
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Email',
            'Subject',
            'Status',
            'Sent At',
            'Sent By',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $rowCount = $sheet->getHighestRow();

        for ($row = 2; $row <= $rowCount; $row++) {
            $cellValue = $sheet->getCell('D'.$row)->getValue();
            if ($cellValue === 'FAILED') {
                $sheet->getStyle('D'.$row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFFF0000'], // Red color
                    ],
                    'font' => [
                        'color' => ['argb' => 'FFFFFFFF'], // White text
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
            } elseif ($cellValue === 'SEND') {
                $sheet->getStyle('D'.$row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => '008000'], // Green color
                    ],
                    'font' => [
                        'color' => ['argb' => 'FFFFFFFF'], // White text
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
            }
        }
    }
}
