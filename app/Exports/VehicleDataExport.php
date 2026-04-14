<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VehicleDataExport implements FromCollection, WithHeadings, WithStyles
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



        // Use the data passed from the controller
        return $this->data->map(function ($datas) {
            return [
                $datas->types ? $datas->types->name : 'N/A',
                $datas->depot ? $datas->depot->name : 'N/A',
                $datas->group ? $datas->group->name : 'N/A',
                $datas->registrationNumber,
                $datas->make,
                $datas->vehicle ? $datas->vehicle->model : 'N/A', // Fetch mot value from Vehicle model
                $this->formatDate($datas->taxDueDate),
                $datas->vehicle ? Carbon::parse($datas->vehicle->annual_test_expiry_date)->format('d/m/Y') : '-',
                $this->formatDate($datas->tacho_calibration),
                $this->formatDate($datas->dvs_pss_permit_expiry),
                $datas->insurance_type,
                $this->formatDate($datas->insurance),
                $this->formatDate($datas->PMI_due),
                $this->formatDate($datas->brake_test_due),
                $this->formatDate($datas->date_of_inspection),

                $datas->odometer_reading,
                $datas->vehicle_status,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Company Name',
            'Depot Name',
            'Vehicle Group',
            'Vehicle Registration Number',
            'Make',
            'Model',
            'Road Tax',
            'Mot',
            'Tacho Calibration',
            'DVS/PSS Permit Expiry',
            'Insurance Type',
            'Insurance',
            'PMI Due',
            'Brake Test Due',
            'Date Of Inspection',
            'Odometer Reading',
            'Vehicle Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Apply bold styling to the heading row
        $sheet->getStyle('A1:V1')->getFont()->setBold(true);

    }

    private function formatDate($date)
    {
        if ($date === '-') {
            return '-';
        }

        return $date ? Carbon::parse($date)->format('d/m/Y') : '';
    }
}
