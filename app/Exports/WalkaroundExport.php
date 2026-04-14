<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WalkaroundExport implements FromCollection, WithHeadings, WithStyles
{
    protected $walkaroundData;

    public function __construct($walkaroundData)
    {
        $this->walkaroundData = $walkaroundData;
    }

    public function collection()
    {
        return $this->walkaroundData->map(function ($walkaround) {
            return [
                'Driver' => $walkaround->driver ? strtoupper($walkaround->driver->name) : 'N/A',
                'Depot' => $walkaround->depot ? $walkaround->depot->name : 'N/A',
                // 'Vehicle' => $walkaround->vehicle ? ($walkaround->vehicle->registrations ?? 'No Registration') . ' - ' . ($walkaround->vehicle->vehicleDetail->make ?? 'No Make') : 'N/A',
                 'Vehicle' => $walkaround->vehicle 
                ? ($walkaround->vehicle->vehicle_type == 'Trailer' 
                    ? (($walkaround->vehicle->vehicleDetail->vehicle_nick_name ?? 'No Vehicle ID') . ' - ' . ($walkaround->vehicle->vehicleDetail->make ?? 'No Make')) 
                    : (($walkaround->vehicle->registrations ?? 'No Registration') . ' - ' . ($walkaround->vehicle->vehicleDetail->make ?? 'No Make'))) 
                : 'N/A',
                'Walkaround Date' => $walkaround->uploaded_date ?? 'N/A',
                'Duration' => $walkaround->duration ?? '0',
                'Defects' => $walkaround->defects_count !== null ? (string) $walkaround->defects_count : '0',
                'Rectified' => $walkaround->rectified ?? '0',
            ];
        });
    }

    public function headings(): array
    {
        return ['Driver', 'Depot', 'Vehicle', 'Walkaround Date', 'Duration', 'Defects', 'Rectified'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Applies bold formatting to the first row (header)
        ];
    }
}
