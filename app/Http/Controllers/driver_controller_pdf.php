<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Driver;
use Barryvdh\DomPDF\Facade\Pdf;

class DriverController extends Controller
{
    /**
     * Download driver history as PDF
     */
    public function downloadHistoryPdf($slug)
    {
        try {
            // Decode the driver ID
            $driverId = base64_decode($slug);
            
            // Find the driver with all necessary relationships
            $driver = Driver::with(['types', 'entitlements'])->findOrFail($driverId);
            
            // Parse endorsements if it's JSON
            $endorsements = [];
            if ($driver->endorsements) {
                $endorsements = is_string($driver->endorsements) 
                    ? json_decode($driver->endorsements, true) 
                    : $driver->endorsements;
            }
            
            // Calculate penalty points and offense counts
            $firstPenaltyPoints = 0;
            $uniqueOffenceCodeCount = 0;
            
            if (is_array($endorsements) && count($endorsements) > 0) {
                $firstPenaltyPoints = $endorsements[0]['penaltyPoints'] ?? 0;
                $uniqueOffenceCodes = array_unique(array_column($endorsements, 'offenceCode'));
                $uniqueOffenceCodeCount = count($uniqueOffenceCodes);
            }
            
            // Prepare data for PDF
            $data = [
                'driver' => $driver,
                'endorsements' => $endorsements,
                'firstPenaltyPoints' => $firstPenaltyPoints,
                'uniqueOffenceCodeCount' => $uniqueOffenceCodeCount,
            ];
            
            // Load the PDF view
            $pdf = Pdf::loadView('driver.history_pdf', $data);
            
            // Set paper size and orientation
            $pdf->setPaper('A4', 'portrait');
            
            // Download the PDF
            return $pdf->download('driver_history_' . $driver->name . '_' . date('Y-m-d') . '.pdf');
            
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Driver PDF Download Error: ' . $e->getMessage());
            
            // Redirect back with error message
            return redirect()->back()->with('error', 'Failed to generate PDF. Please try again.');
        }
    }
}