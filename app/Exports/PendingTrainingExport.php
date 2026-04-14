<?php

namespace App\Exports;

use App\Models\TrainingDriverAssign;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PendingTrainingExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $PendingTraining;

    public function __construct($PendingTraining)
    {
        $this->PendingTraining = $PendingTraining;
    }

    public function collection()
    {
        return $this->PendingTraining;
    }

    public function headings(): array
    {
        return [
            'Driver Name',
            'Driver Email',
            'Driver Mobile No',
            'Driver Group',
            'Training Type',
            'Training Course',
            'From Date',
            'To Date',
            'Status',
        ];
    }

    public function map($pending): array
    {
        return [
            $pending->driver->name,
            $pending->driver->contact_email,
            $pending->driver->contact_no,
            $pending->driver->group->name,
            $pending->training->trainingType->name,
            $pending->training->trainingCourse->name,
            \Carbon\Carbon::parse($pending->training->from_date)->format('d/m/Y'),
            \Carbon\Carbon::parse($pending->training->to_date)->format('d/m/Y'),
            $pending->status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Set the first row (header row) to bold
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Pending Trainings'; // Optional: Set the title of the worksheet
    }
}
