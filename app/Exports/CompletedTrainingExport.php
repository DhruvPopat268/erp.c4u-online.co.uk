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


class CompletedTrainingExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $completedTraining;

    public function __construct($completedTraining)
    {
        $this->completedTraining = $completedTraining;
    }

    public function collection()
    {
        return $this->completedTraining;
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

    public function map($completed): array
    {
        return [
            $completed->driver->name,
            $completed->driver->contact_email,
            $completed->driver->contact_no,
            $completed->driver->group->name,
            $completed->training->trainingType->name,
            $completed->training->trainingCourse->name,
            \Carbon\Carbon::parse($completed->training->from_date)->format('d/m/Y'),
            \Carbon\Carbon::parse($completed->training->to_date)->format('d/m/Y'),
            $completed->status,
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
        return 'Completed Trainings'; // Optional: Set the title of the worksheet
    }
}
