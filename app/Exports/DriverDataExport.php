<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DriverDataExport implements FromCollection, WithHeadings, WithStyles
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
                ucwords(strtolower($datas->name)),
                $datas->types ? $datas->types->name : 'N/A',
                $datas->depot ? $datas->depot->name : 'N/A',
                $datas->group ? $datas->group->name : 'N/A',
                $datas->driver_status,
                $datas->consent_form_status,
                $datas->ni_number,
                $datas->post_code,
                $datas->contact_no,
                $datas->contact_email,
                $datas->driver_dob,
                $datas->driver_address,
                $datas->driver_licence_no,
                $datas->driver_licence_status,
                $datas->driver_licence_expiry,
                $datas->cpc_status,
                $datas->cpc_validto,
                $datas->tacho_card_no,
                $datas->tacho_card_valid_from,
                $datas->tacho_card_status,
                $datas->tacho_card_valid_to,
                $datas->latest_lc_check,
                $datas->next_lc_check,
                $datas->driverUser ? $datas->driverUser->username : 'N/A', // Add the username from DriverUser model
                $datas->driverUser && ! empty($datas->driverUser->last_login_at) &&
                 $datas->driverUser->last_login_at != '0000-00-00 00:00:00'
              ? \Carbon\Carbon::parse($datas->driverUser->last_login_at)->format('d/m/Y H:i')
                  : '-',

            ];
        });
    }

    public function headings(): array
    {
        return [
            'Driver Name',
            'Operator Company Name',
            'Depot Name',
            'Driver Group',
            'Driver Status',
            'Consent Form Status',
            'Driver Number',
            'Post Code',
            'Contact No',
            'Contact Email',
            'Driver DOB',
            'Driver Address',
            'Driver Licence No',
            'Driver Licence Status',
            'Driver Licence Expiry',
            'CPC Status',
            'CPC Valid To',
            'Tacho Card No',
            'Tacho Card Valid From',
            'Tacho Card Status',
            'Tacho Card Valid To',
            'Latest LC Check',
            'Next Lc Check',
            'Username',  // Add the new column for username
            'Driver Last login At',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Apply bold styling to the heading row
        $sheet->getStyle('A1:Y1')->getFont()->setBold(true);

        // Ensure contact number column is treated as text
        $sheet->getStyle('G')->getNumberFormat()->setFormatCode('@');

        // Conditional formatting for driver_licence_status, cpc_status, and tacho_card_status columns
        foreach ($this->data as $index => $data) {
            $rowIndex = $index + 2; // Adjusting for the heading row

            // Apply colors to driver_licence_status
            $this->applyStatusColor($sheet, 'L'.$rowIndex, $data->driver_licence_status);

            // Apply colors to cpc_status
            $this->applyStatusColor($sheet, 'N'.$rowIndex, $data->cpc_status);

            // Apply colors to tacho_card_status
            $this->applyStatusColor($sheet, 'R'.$rowIndex, $data->tacho_card_status);
        }
    }

    private function applyStatusColor($sheet, $cell, $status)
    {
        switch ($status) {
            case 'EXPIRING SOON':
                $sheet->getStyle($cell)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFA500'); // Orange
                break;
            case 'EXPIRED':
                $sheet->getStyle($cell)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFF0000'); // Red
                break;
        }
    }
}
