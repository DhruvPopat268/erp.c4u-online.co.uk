<?php

namespace App\Exports;

use App\Models\DriverAPILog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet; // Import the correct class

class DriverApiLogsExport implements FromCollection, WithHeadings, WithStyles
{
    protected $companyId;
    protected $userId;
      protected $fromDate;
    protected $toDate;

 public function __construct($companyId = null, $userId = null, $fromDate = null, $toDate = null)
    {
        $this->companyId = $companyId;
        $this->userId = $userId;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    public function collection()
    {
        $query = DriverAPILog::with('companyDetails', 'creator');

        // Filter by company ID if provided
        if ($this->companyId) {
            $query->where('companyName', $this->companyId);
        }

        // Filter by user ID if provided
        if ($this->userId) {
            $query->where('created', $this->userId);
        }
        
        if ($this->fromDate) {
        $query->whereDate('created_at', '>=', $this->fromDate);
    }

    // Filter by To Date if provided
    if ($this->toDate) {
        $query->whereDate('created_at', '<=', $this->toDate);
    }

        // Retrieve the filtered logs
return $query->get()->map(function ($log) {
    // Default value
    $creator = 'Automation'; // Default for 1.1

    if (is_numeric($log->created) && $log->creator) { 
        // If 'created' is an ID, get the username
        $creator = $log->creator->username;
    } elseif ($log->created === 'Auto Generator') {
        // If 'created' is 'Auto Generator', keep it as it is
        $creator = 'Auto Generator';
    }

    return [
        $log->licence_no,
        optional($log->drivers)->name ?? 'N/A',
        optional(optional($log->drivers)->depot)->name ?? 'N/A',
        optional($log->companyDetails)->name ? ucwords(strtolower($log->companyDetails->name)) : '',
        $log->last_lc_check,
        $creator, // Handle creator dynamically
    ];
});
    }

    public function headings(): array
    {
        return [
            'Driver Licence Number',
            'Driver Name',
            'Depot Name',
            'Company Name',
            'Last LC Check',
            'User',
        ];
    }

    public function styles(Worksheet $sheet)  // Use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
    {
        // Set bold style for the first row (headings)
        $sheet->getStyle('1:1')->getFont()->setBold(true);

        return [];
    }
}