<?php

namespace App\Exports;

use App\Models\TrainingDriverAssign;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DeclineTrainingExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $DeclineTraining;

    public function __construct($DeclineTraining)
    {
        $this->DeclineTraining = $DeclineTraining;
    }

    public function collection()
    {
        return $this->DeclineTraining;
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

    public function map($decline): array
    {
        return [
            $decline->driver->name,
            $decline->driver->contact_email,
            $decline->driver->contact_no,
            $decline->driver->group->name,
            $decline->training->trainingType->name,
            $decline->training->trainingCourse->name,
            \Carbon\Carbon::parse($decline->training->from_date)->format('d/m/Y'),
            \Carbon\Carbon::parse($decline->training->to_date)->format('d/m/Y'),
            $decline->status,
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
        return 'Decline Trainings'; // Optional: Set the title of the worksheet
    }
}
