<?php

namespace App\Exports;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CompanyDataExport implements FromCollection, WithHeadings, WithStyles
{
    protected $companyDetails;

    protected $depots;

    public function __construct(Collection $companyDetails, Collection $depots)
    {
        $this->companyDetails = $companyDetails;
        $this->depots = $depots;
    }

     public function collection()
{
    if ($this->companyDetails->isEmpty()) {
        return collect([]);
    }

    // Initialize a new collection to store expanded rows
    $exportData = collect();

    // Process each item in the collection
    foreach ($this->companyDetails as $item) {
        // Decode JSON arrays
        $operatorRoles = json_decode($item['operator_role'], true);
        $operatorDobs = json_decode($item['operator_dob'], true);
        $device = json_decode($item['device'], true);
        $operator_name = json_decode($item['operator_name'], true);
        $operator_phone = json_decode($item['operator_phone'], true);
        $status = json_decode($item['status'], true);
       
        $compliance = json_decode($item['compliance'], true);
        $operator_email = json_decode($item['operator_email'], true);

        // Ensure all arrays are of the same length
        $count = max(
            count($operatorRoles), 
            //count($operatorDobs), 
            count($device), 
            count($operator_name), 
            count($operator_phone), 
            count($status),
            count($compliance), 
            count($operator_email)
        );

        // Get depot data
        $depot = $this->depots->get($item['id']); // Assuming 'id' is the company_id

        // Create a new row for each pair of director_name and director_dob
        for ($i = 0; $i < $count; $i++) {
            $rawDob = Arr::get($operatorDobs, $i);
            $formattedDob = null;
            if ($rawDob) {
                try {
    $date = \DateTime::createFromFormat('d/m/Y', $rawDob);
    if ($date === false) {
        // If parsing failed, fallback to the raw date or handle as needed
        $formattedDob = $rawDob;
    } else {
        $formattedDob = $date->format('d/m/Y');
    }
} catch (Exception $e) {
    // Handle any unexpected exceptions
    $formattedDob = $rawDob;
}

            }

            $rowData = [
                'account_no' => $item['account_no'],
                'name' => $item['name'],
                'email' => $item['email'],
                'address' => $item['address'],
                'contact' => $item['contact'],
                'operator_role' => Arr::get($operatorRoles, $i), // Get array element safely
                'device' => Arr::get($device, $i),
                'operator_name' => Arr::get($operator_name, $i),
                'operator_phone' => Arr::get($operator_phone, $i),
                'operator_dob' => $formattedDob, // Use formatted date
                'status' => Arr::get($status, $i),
                'compliance' => Arr::get($compliance, $i),
                'operator_email' => Arr::get($operator_email, $i),
                'depot_name' => $depot ? $depot->name : null,
                'depot_operating_centre' => $depot ? $depot->operating_centre : null,
                'depot_vehicles' => $depot ? $depot->vehicles : null,
                'depot_trailers' => $depot ? $depot->trailers : null,
                'depot_status' => $depot ? $depot->status : null,
                   'fors_browse_policy' => $this->formatDate($item['fors_browse_policy'] ?? null),
    'fors_silver_policy' => $this->formatDate($item['fors_silver_policy'] ?? null),
    'fors_gold_policy' => $this->formatDate($item['fors_gold_policy'] ?? null),
            ];

            // Add the row to the export data collection
            $exportData->push($rowData);
        }
    }

    return $exportData;
}


    public function headings(): array
    {
        return [
            'Account ID',
            'Company Name',
            'Company Email',
            'Company Address',
            'Company Contact Number',
            'Manager Name',
            'Device',
            'Manager Name',
            'Manager Contact Number',
            'Manager DOB',
            'Status',
            'Compliance',
            'Manager Email',
            'Depot Name',
            'Operating Centre',
            'No Of Vehicles',
            'No of Trailers',
            'Depot Status',
            'FORS Browse Policy',
            'FORS Silver Policy',
            'FORS Gold Policy',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Apply bold styling to the heading row
        $sheet->getStyle('A1:W1')->getFont()->setBold(true);
    }
    
    private function formatDate($date)
{
    if (!$date) {
        return '-';
    }

    try {
        $formattedDate = \DateTime::createFromFormat('Y-m-d', $date); // Adjust format if needed
        return $formattedDate ? $formattedDate->format('d/m/Y') : $date;
    } catch (Exception $e) {
        return '-';
    }
}

}
