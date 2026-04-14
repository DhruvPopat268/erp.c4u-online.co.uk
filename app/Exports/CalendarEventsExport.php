<?php

namespace App\Exports;

use App\Models\Fleet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class CalendarEventsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $companyId;
    protected $vehicleId;
    protected $plannerType;
     protected $year;
     protected $fromDate;
     protected $toDate;
     protected $depotIds;
     protected $selectedDepotId;
protected $selectedGroupId;

public function __construct($companyId, $vehicleId, $plannerType, $year = null, $fromDate = null, $toDate = null, $depotIds = [], $selectedDepotId,
    $selectedGroupId)
    {
        $this->companyId = $companyId;
        $this->vehicleId = $vehicleId;
        $this->plannerType = $plannerType;
        $this->year = $year;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    $this->depotIds = $depotIds;
    $this->selectedDepotId = $selectedDepotId;
    $this->selectedGroupId = $selectedGroupId;
    }


    public function collection()
    {
    $query = Fleet::with(['reminders' => function ($query) {
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('next_reminder_date', [$this->fromDate, $this->toDate]);
        } else {
            $query->whereYear('next_reminder_date', $this->year);
        }
    }, 'vehicle', 'company'])
    ->when($this->companyId, function ($query) {
                return $query->where('company_id', $this->companyId);
            })
            ->when($this->selectedDepotId, function ($q) {
    $q->whereHas('vehicle', function ($sub) {
        $sub->where('depot_id', $this->selectedDepotId);
    });
})

->when($this->selectedGroupId, function ($q) {
    $q->whereHas('vehicle', function ($sub) {
        $sub->where('group_id', $this->selectedGroupId);
    });
})
            ->when($this->vehicleId, function ($query) {
                return $query->where('vehicle_id', $this->vehicleId);
            })
            ->when($this->plannerType, function ($query) {
                return $query->where('planner_type', $this->plannerType);
            })
            ->whereHas('reminders', function ($query) {
                if ($this->fromDate && $this->toDate) {
                    $query->whereBetween('next_reminder_date', [$this->fromDate, $this->toDate]);
                } else {
                    $query->whereYear('next_reminder_date', $this->year);
                }
    });

        // // Log the filtered fleets and their reminders for debugging
        // \Log::info('Exporting fleet reminders for the current year', [
        //     'currentYear' => $currentYear,
        //     'fleets' => $fleets->map(function ($fleet) {
        //         return [
        //             'company_name' => $fleet->company ? $fleet->company->name : 'N/A',
        //             'vehicle_registration_number' => $fleet->vehicle->registrationNumber,
        //             'reminders' => $fleet->reminders->map(function ($reminder) {
        //                 return [
        //                     'planner_type' => $reminder->planner_type,
        //                     'next_reminder_date' => $reminder->next_reminder_date,
        //                     'status' => $reminder->status,
        //                 ];
        //             }),
        //         ];
        //     }),
        // ]);

        // Add depot filter only if depotIds is not empty
    if (!empty($this->depotIds)) {
        $query->whereHas('vehicle', function ($q) {
            $q->whereIn('depot_id', $this->depotIds);
        });
    }
     if (!empty($this->vehicleGroupIds)) {

            $query->whereHas('vehicle', function ($q) {

                $q->whereIn('group_id', $this->vehicleGroupIds);

            });

        }


    return $query->get();
    }

    public function headings(): array
    {
        return [
            'Company Name',
            'Vehicle Registration Number',
            'Planner Type',
            'Reminder Date',
            'Status',
            'Interval'
        ];
    }

    public function map($fleet): array
    {
        return $fleet->reminders->map(function($reminder) use ($fleet) {

            $formattedDate = Carbon::parse($reminder->next_reminder_date)->format('d/m/Y');

            $intervalValue = '-';
        if (isset($fleet->every) && isset($fleet->interval)) {
            $intervalValue = $fleet->every . ' ' . $fleet->interval;
        }

            return [
                $fleet->company ? $fleet->company->name : 'N/A',
                $fleet->vehicle->registrationNumber,
                $fleet->planner_type,
                $formattedDate,
                $reminder->status,
                $intervalValue,
            ];
        })->toArray();
    }


public function styles(Worksheet $sheet)
{
    // Apply bold style to the first row (headings)
    $styleArray = [
        1 => [
            'font' => [
                'bold' => true,
            ],
        ],
    ];

    // Apply conditional background color to the Planner Type column (Column C)
    $rows = $sheet->getRowIterator();
    foreach ($rows as $rowIndex => $row) {
        $plannerType = $sheet->getCell('C' . $rowIndex)->getValue(); // Column C is "Planner Type"
        $status = $sheet->getCell('E' . $rowIndex)->getValue(); // Column E is "Status"

        // Set colors based on planner type
        switch ($plannerType) {
            case 'Road Tax':
                $sheet->getStyle('C' . $rowIndex)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('d59436'); // Road Tax color
                break;
            case 'MOT':
                $sheet->getStyle('C' . $rowIndex)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('6c757d'); // MOT color
                break;
            case 'PMI Due':
                $sheet->getStyle('C' . $rowIndex)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('3788d8'); // PMI Due color
                break;
            case 'Brake Test Due':
                $sheet->getStyle('C' . $rowIndex)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('aa38c1'); // Brake Test Due color
                break;
            case 'Tacho Calibration':
                $sheet->getStyle('C' . $rowIndex)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('008000'); // Tacho Calibration color (Green)
                break;
            case 'DVS/PSS Permit Expiry':
                $sheet->getStyle('C' . $rowIndex)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('0000FF'); // DVS/PSS Permit Expiry color (Blue)
                break;
            case 'Insurance':
                $sheet->getStyle('C' . $rowIndex)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('c0c102'); // Insurance color (Yellow)
                break;
            default:
                // No color if planner type doesn't match any condition
                break;
        }

        // Apply a green color for "Completed" status (Column E)
        if ($status === 'Completed') {
            $sheet->getStyle('E' . $rowIndex)
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('28a745'); // Green color for Completed status
        }
    }

    return $styleArray;
}


}
