<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PcnExport implements FromCollection, WithHeadings
{
    protected $pcnData;

    public function __construct($pcnData)
    {
        $this->pcnData = $pcnData;
    }

    public function collection()
    {
        return $this->pcnData->map(function ($pcn) {
            return [
                'Company Name' => $pcn->types->name ?? 'N/A',
                'Depot Name' => $pcn->depot->name ?? 'N/A',
                'Registration Number' => $pcn->vehicle_registration_number ?? 'N/A',
                'Driver Name' => $pcn->driver_name ?? 'N/A',
                                'Notice Number' => $pcn->notice_number ?? 'N/A',
                 'Notice Date' => \Carbon\Carbon::parse($pcn->notice_date)->format('d/m/Y'),
            'Violation Date' => \Carbon\Carbon::parse($pcn->violation_date)->format('d/m/Y'),
                'Location Of Contravention' => $pcn->location,
                'Issuing Authority' => $pcn->issuing_authority,
                'Type' => $pcn->type,
                'Issuing Authority Action' => $pcn->action,
                'Fine Amount' => $pcn->fine_amount,
                'Deduction Amount' => $pcn->deduction_amount,
                'Status' => $pcn->status,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Company Name',
                'Depot Name',
                'Registration Number',
                'Driver Name',
                'Notice Number',
                'Notice Date',
                'Violation Date',
                'Location Of Contravention',
                'Issuing Authority',
                'Type',
                'Issuing Authority Action',
                'Fine Amount',
                'Deduction Amount',
                'Status',
        ];
    }
}