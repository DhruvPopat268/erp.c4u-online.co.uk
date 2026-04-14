<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;

class VehicleImport implements ToCollection
{
    use Importable;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        return $collection; // You can process the collection here if needed
    }
}
