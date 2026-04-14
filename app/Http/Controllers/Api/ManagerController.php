<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyDetails;
use App\Models\Depot;
use App\Models\Driver;
use App\Models\Contract_attachment;
use Carbon\Carbon;
use Google\Service\ContainerAnalysis\Details;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Utility;
use App\Models\vehicleDetails;
use Illuminate\Support\Facades\Storage;
use App\Models\Fleet;



class ManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function getCompanyData(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Fetch the company details by the ID stored in the authenticated user's record
        $company = CompanyDetails::find($user->companyname);

        if (!$company) {
            return response()->json(['status' => 0, 'error' => 'Company not found'], 404);
        }

        // Convert user's depot_id to an array (ensure it's an array)
        $userDepotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
        
        $userDriverGroupIds = is_array($user->driver_group_id)
    ? $user->driver_group_id
    : json_decode($user->driver_group_id, true);

$userVehicleGroupIds = is_array($user->vehicle_group_id)
    ? $user->vehicle_group_id
    : json_decode($user->vehicle_group_id, true);
    

        // Fetch counts for related data
        $driverCount = Driver::where('companyName', $company->id)
            ->whereIn('driver_status', ['Active'])
            ->when(!empty($userDriverGroupIds), function ($query) use ($userDriverGroupIds) {
        $query->whereIn('group_id', $userDriverGroupIds);
    })
            ->whereIn('depot_id', $userDepotIds) // Filter by depot_id
            ->count();

 $vehicleCount = \App\Models\Vehicles::where('companyName', $company->id)
        ->whereHas('vehicleDetails', function ($query) use ($userDepotIds,$userVehicleGroupIds) {
            $query->whereIn('depot_id', $userDepotIds)
            ->when(!empty($userVehicleGroupIds), function ($q) use ($userVehicleGroupIds) {
                  $q->whereIn('group_id', $userVehicleGroupIds);
              })
                  ->where(function ($q) {
                      $q->whereNull('vehicle_status')
                        ->orWhere('vehicle_status', '')
                        ->orWhere('vehicle_status', 'not like', 'Archive%');
                  });
        })
        ->count();
        $operatingCentreCount = Depot::where('companyName', $company->id)->whereIn('id', $userDepotIds)->count();
      $workAroundStoreCount = \App\Models\WorkAroundStore::where('company_id', $company->id)
    ->whereIn('operating_centres', $userDepotIds)

    // ✅ Driver group match (MANDATORY)
    ->when(!empty($userDriverGroupIds), function ($query) use ($userDriverGroupIds) {
        $query->whereHas('driver', function ($q) use ($userDriverGroupIds) {
            $q->whereIn('group_id', $userDriverGroupIds);
        });
    })

    // ✅ Vehicle group match (MANDATORY)
    ->when(!empty($userVehicleGroupIds), function ($query) use ($userVehicleGroupIds) {
        $query->whereHas('vehicle.vehicleDetail', function ($q) use ($userVehicleGroupIds) {
            $q->whereIn('group_id', $userVehicleGroupIds);
        });
    })

    // ✅ Active types
    ->whereHas('types', function ($query) {
        $query->where('company_status', 'Active');
    })

    ->count();

        // Get driver IDs who belong to the user's depot(s)
        $driverIds = Driver::where('companyName', $company->id)
            ->whereIn('depot_id', $userDepotIds)
            ->when(!empty($userDriverGroupIds), function ($query) use ($userDriverGroupIds) {
        $query->whereIn('group_id', $userDriverGroupIds);
    })
            ->pluck('id');


        // Count the number of training assignments where companyName is from the Training model
       $driverTrainingCount = \App\Models\TrainingDriverAssign::whereHas('training', function ($query) use ($company) {
            $query->where('companyName', $company->id);
        })
        ->whereIn('driver_id', $driverIds) // Filter by driver_id matching depot_id
        ->count();

        // Fetch the access level associated with the company (if any)
        $accessLevel = \App\Models\AppAccessLevel::where('company_id', $company->id)->first();

        // Define all possible access types
        $allManagerAccessTypes = [
            "Driver", "Vehicle", "Walkaround", "Operating Centre", "Training", "Forward Planner"
        ];

        // Prepare the access data as true/false
        $managerAccess = [];


                // If access level exists, populate the access data
        if ($accessLevel && is_array($accessLevel->manager_access)) {
            $managerAccessArray = $accessLevel->manager_access ?? [];

            foreach ($allManagerAccessTypes as $access) {
                $managerAccess[$access] = in_array($access, $managerAccessArray);
            }
            $allStatus = true; // All access types are based on manager_access
        } else {
            // If no access level exists or manager_access is null, set all values to false
            foreach ($allManagerAccessTypes as $access) {
                $managerAccess[$access] = false;
            }
            $allStatus = false; // All access types are false when no valid access
        }

        // Add 'all_status' within the 'manager_access' response
        $managerAccess['all_status'] = $allStatus;

        // Return the data as JSON
        return response()->json([
            'status' => 1,
            'walkaround_preference' => $user->walkaround_preference,
            'driver_count' => $driverCount,
            'vehicle_count' => $vehicleCount,
            'operating_center_count' => $operatingCentreCount,
            'walkaround_count' => $workAroundStoreCount,
            'training_count' => $driverTrainingCount,
            'manager_access' => $managerAccess,
        ]);
    }


// public function getDriverVehicleOPCData(Request $request)
// {
//     // Get the authenticated user
//     $user = Auth::user();

//     // Fetch the company details by the ID stored in the authenticated user's record
//     $company = CompanyDetails::find($user->companyname);

//     if (! $company) {
//         return response()->json(['status' => 0, 'error' => 'Company not found'], 404);
//     }

//     // Fetch multiple records for related data
//     $drivers = Driver::where('companyName', $company->id)->whereIn('driver_status', ['Active', 'InActive'])->with('group')->get(); // Eager load 'group'
//     $vehicles = \App\Models\Vehicles::where('companyName', $company->id)->get();
//     $operatingCentres = Depot::where('companyName', $company->id)->get();
//     $plannervehicles = \App\Models\vehicleDetails::where('companyName', $company->id)->get();

//     // Fetch VehicleDetails for each vehicle
//     $vehicleDetails = \App\Models\vehicleDetails::whereIn('vehicle_id', $vehicles->pluck('id'))->with('group')->get()->keyBy('vehicle_id');

//     // Return the data as JSON
//     return response()->json([
//         'status' => 1,
//         'drivers' => $drivers->map(function ($driver) {
//             return [
//                 'id' => $driver->id,
//                 'name' => ucwords(strtolower($driver->name)),
//                 'licence_no' => $driver->driver_licence_no,
//                 'status' => $driver->driver_status,
//                 'group_name' => $driver->group ? $driver->group->name : 'Unknown', // Fallback if no group found
//             ];
//         }),
//         'vehicles' => $vehicles->map(function ($vehicle) use ($vehicleDetails) {
//             $details = $vehicleDetails->get($vehicle->id);

//             // Determine correct registration number
//             $registrationNumber = 'N/A';
//             if ($vehicle) {
//                 if ($vehicle->vehicle_type == 'Trailer') {
//                     $registrationNumber = $details->vehicle_nick_name ?? 'No Vehicle ID';
//                 } else {
//                     $registrationNumber = $vehicle->registrations ?? 'No Registration';
//                 }
//             }

//             return [
//                 'id' => $vehicle->id,
//                 'registration_number' => $registrationNumber,
//                 'make' => $vehicle ? $vehicle->make : 'Unknown', // Fallback if no details found
//                 'group_name' => $details->group ? $details->group->name : 'Unknown',
//                 'vehicle_type' => $vehicle->vehicle_type ?? 'Null',
//             ];
//         }),
//         'plannervehicle' => $plannervehicles->map(function ($vehicle) use ($vehicleDetails) {

//             // Determine correct registration number for planner vehicles
//              $registrationNumber = 'N/A';
//              if ($vehicle->vehicle && $vehicle->vehicle->vehicle_type == 'Trailer') {
//                  $registrationNumber = $vehicle->vehicle_nick_name ?? 'No Vehicle ID';
//              } else {
//                  $registrationNumber = $vehicle->registrationNumber ?? 'No Registration';
//              }

//             return [
//                 'planner_vehicle_id' => $vehicle->id,
//                 'registration_number' => $registrationNumber,
//             ];
//         }),
//         'operating_centres' => $operatingCentres->map(function ($operatingCentre) {
//             return [
//                 'id' => $operatingCentre->id,
//                 'name' => $operatingCentre->name,
//                 'operating_centre' => $operatingCentre->operating_centre,
//                 'status' => $operatingCentre->status,
//             ];
//         }),
//     ]);
// }

public function getDriverVehicleOPCData(Request $request)
{
    // Get the authenticated user
    $user = Auth::user();

    // Fetch the company details by the ID stored in the authenticated user's record
    $company = CompanyDetails::find($user->companyname);

    if (!$company) {
        return response()->json(['status' => 0, 'error' => 'Company not found'], 404);
    }

    // Convert user's depot_id to an array
    $userDepotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
    $userDriverGroupIds = is_array($user->driver_group_id)
    ? $user->driver_group_id
    : json_decode($user->driver_group_id, true);

$userVehicleGroupIds = is_array($user->vehicle_group_id)
    ? $user->vehicle_group_id
    : json_decode($user->vehicle_group_id, true);
    

    // Fetch multiple records for related data (filtered by depot_id)
    $drivers = Driver::where('companyName', $company->id)
        ->whereIn('driver_status', ['Active'])
        ->whereIn('depot_id', $userDepotIds)
         ->when(!empty($userDriverGroupIds), function ($query) use ($userDriverGroupIds) {
        $query->whereIn('group_id', $userDriverGroupIds);
    })
        ->with('group')
        ->get();

    // Fetch vehicle details and filter vehicles based on depot_id from VehicleDetails
     $vehicles = \App\Models\Vehicles::where('companyName', $company->id)
        ->whereHas('vehicleDetails', function ($q) use ($userDepotIds, $userVehicleGroupIds) {
            $q->whereIn('depot_id', $userDepotIds)
            ->when(!empty($userVehicleGroupIds), function ($subQ) use ($userVehicleGroupIds) {
              $subQ->whereIn('group_id', $userVehicleGroupIds);
          })
              ->where(function ($subQ) {
                  $subQ->whereNull('vehicle_status')
                       ->orWhere('vehicle_status', '')
                       ->orWhere('vehicle_status', 'not like', 'Archive%');
              });
        })
        ->get();

    // Fetch VehicleDetails for the vehicles
   $vehicleDetails = \App\Models\VehicleDetails::whereIn('vehicle_id', $vehicles->pluck('id'))
        ->whereIn('depot_id', $userDepotIds)
        ->when(!empty($userVehicleGroupIds), function ($query) use ($userVehicleGroupIds) {
        $query->whereIn('group_id', $userVehicleGroupIds);
    })
        ->where(function ($q) {
            $q->whereNull('vehicle_status')
              ->orWhere('vehicle_status', '')
              ->orWhere('vehicle_status', 'not like', 'Archive%');
        })
        ->with('group', 'depot')
        ->get()
        ->keyBy('vehicle_id');

    // Fetch planner vehicles filtered by depot_id
    $plannervehicles = \App\Models\VehicleDetails::where('companyName', $company->id)
        ->whereIn('depot_id', $userDepotIds)
        ->when(!empty($userVehicleGroupIds), function ($query) use ($userVehicleGroupIds) {
        $query->whereIn('group_id', $userVehicleGroupIds);
    })
        ->where(function ($q) {
            $q->whereNull('vehicle_status')
              ->orWhere('vehicle_status', '')
              ->orWhere('vehicle_status', 'not like', 'Archive%');
        })
        ->get();

    // Fetch only the depots assigned to the user
    $operatingCentres = Depot::where('companyName', $company->id)
        ->whereIn('id', $userDepotIds)
        ->get();

    // Return the data as JSON
    return response()->json([
        'status' => 1,

        'drivers' => $drivers->map(function ($driver) {
            return [
                'id' => $driver->id,
                'name' => ucwords(strtolower($driver->name)),
                'licence_no' => $driver->driver_licence_no,
                'status' => $driver->driver_status,
                'group_name' => $driver->group ? $driver->group->name : 'Unknown',
                'depot_name' => $driver->depot ? $driver->depot->name : 'Null',
            ];
        }),
        'vehicles' => $vehicles->map(function ($vehicle) use ($vehicleDetails) {
            $details = $vehicleDetails->get($vehicle->id);

            // Determine correct registration number
            $registrationNumber = 'N/A';
            if ($vehicle) {
                if ($vehicle->vehicle_type == 'Trailer') {
                    $registrationNumber = $details->vehicle_nick_name ?? 'No Vehicle ID';
                } else {
                    $registrationNumber = $vehicle->registrations ?? 'No Registration';
                }
            }

            return [
                'id' => $vehicle->id,
                'registration_number' => $registrationNumber,
                'vehicle_registration_number' => $vehicle->registrations ?? 'No Registration',
                'make' => $vehicle ? $vehicle->make : 'Unknown',
                'group_name' => $details->group ? $details->group->name : 'Unknown',
                'vehicle_type' => $vehicle->vehicle_type ?? 'Null',
                'depot_name' => $details->depot ? $details->depot->name : 'Null',
            ];
        }),
        'plannervehicle' => $plannervehicles->map(function ($vehicle) {
            // Determine correct registration number for planner vehicles
            $registrationNumber = 'N/A';
            if ($vehicle->vehicle && $vehicle->vehicle->vehicle_type == 'Trailer') {
                $registrationNumber = $vehicle->vehicle_nick_name ?? 'No Vehicle ID';
            } else {
                $registrationNumber = $vehicle->registrationNumber ?? 'No Registration';
            }

            return [
                'planner_vehicle_id' => $vehicle->id,
                'registration_number' => $registrationNumber,
            ];
        }),
        'operating_centres' => $operatingCentres->map(function ($operatingCentre) {
            return [
                'id' => $operatingCentre->id,
                'name' => $operatingCentre->name,
                'operating_centre' => $operatingCentre->operating_centre,
                'status' => $operatingCentre->status,
            ];
        }),
    ]);
}

public function updateWalkaroundPreference(Request $request)
{
    // Get the authenticated user
    $user = Auth::user();

    // Validate the request
    $validator = Validator::make($request->all(), [
        'walkaround_preference' => 'required|integer|in:0,1,2',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 0, 'error' => $validator->errors()], 422);
    }

    // Map numeric values to their corresponding preference
    $preferences = [
        0 => 'notification',
        1 => 'email',
        2 => 'both'
    ];

    // Update walkaround_preference
    $user->walkaround_preference = $preferences[$request->walkaround_preference];
    $user->save();

    return response()->json(['status' => 1, 'message' => 'Walkaround preference updated successfully']);
}




     public function getDriverDetailsData(Request $request)
{
    // Validate the request to ensure driverId is provided
    $request->validate([
        'driverId' => 'required|integer|exists:drivers,id',
    ]);

    $driverId = $request->input('driverId');

    // Get the authenticated user
    $user = Auth::user();

    // Fetch the company details by the ID stored in the authenticated user's record
    $company = CompanyDetails::find($user->companyname);

    if (! $company) {
        return response()->json(['status' => 0, 'error' => 'Company not found'], 404);
    }

    // Fetch the specific driver by ID
    $driver = Driver::where('companyName', $company->id)->where('id', $driverId)->first();

    if (! $driver) {
        return response()->json(['status' => 0, 'error' => 'Driver not found'], 404);
    }

    // Fetch driver attachments
    $attachments = \App\Models\DriverAttachments::where('driver_id', $driverId)->first();

    // Define base URL for image files
    $baseImageUrl = url('storage/');

    // Extract image URLs from the attachments
    $imageUrls = [
        'license_front' => $attachments && $attachments->license_front ? $baseImageUrl.'/'.$attachments->license_front : null,
        'license_back' => $attachments && $attachments->license_back ? $baseImageUrl.'/'.$attachments->license_back : null,
        'cpc_card_front' => $attachments && $attachments->cpc_card_front ? $baseImageUrl.'/'.$attachments->cpc_card_front : null,
        'cpc_card_back' => $attachments && $attachments->cpc_card_back ? $baseImageUrl.'/'.$attachments->cpc_card_back : null,
        'tacho_card_front' => $attachments && $attachments->tacho_card_front ? $baseImageUrl.'/'.$attachments->tacho_card_front : null,
        'tacho_card_back' => $attachments && $attachments->tacho_card_back ? $baseImageUrl.'/'.$attachments->tacho_card_back : null,
        'mpqc_card_front' => $attachments && $attachments->mpqc_card_front ? $baseImageUrl.'/'.$attachments->mpqc_card_front : null,
        'mpqc_card_back' => $attachments && $attachments->mpqc_card_back ? $baseImageUrl.'/'.$attachments->mpqc_card_back : null,
        'levelD_card_front' => $attachments && $attachments->levelD_card_front ? $baseImageUrl.'/'.$attachments->levelD_card_front : null,
        'levelD_card_back' => $attachments && $attachments->levelD_card_back ? $baseImageUrl.'/'.$attachments->levelD_card_back : null,
        'one_card_front' => $attachments && $attachments->one_card_front ? $baseImageUrl.'/'.$attachments->one_card_front : null,
        'one_card_back' => $attachments && $attachments->one_card_back ? $baseImageUrl.'/'.$attachments->one_card_back : null,
        'additional_cards' => $attachments && $attachments->additional_cards
                ? array_map(fn($path, $index) => ["additional_cards" => $baseImageUrl.'/'.$path], json_decode($attachments->additional_cards, true), array_keys(json_decode($attachments->additional_cards, true)))
            : [], // Ensure additional_cards is an array of URLs
    ];

    // Encode the driver ID
    $encodedId = base64_encode($driver->id);

    // Define the base URL for driver details view
    $baseUrl = url('/driver/pdf/data/'.$encodedId);

    // Decode the JSON data from the endorsements column
    $endorsements = json_decode($driver->endorsements, true);

    // Initialize variables for latest penalty points and offence code counts
    $latestPenaltyPoints = 0;
    $offenceCodes = [];

    if (is_array($endorsements)) {
        // Get the latest penalty points value
        $latestPenaltyPoints = array_reduce($endorsements, function ($carry, $endorsement) {
            return isset($endorsement['penaltyPoints']) ? max($carry, $endorsement['penaltyPoints']) : $carry;
        }, 0);

        // Collect unique offence codes
        foreach ($endorsements as $endorsement) {
            if (isset($endorsement['offenceCode'])) {
                $offenceCodes[] = $endorsement['offenceCode'];
            }
        }
    }

    // Count unique offence codes
    $uniqueOffenceCodeCount = count(array_unique($offenceCodes));

    // Return the specific driver details as JSON
    return response()->json([
        'status' => 1,
        'driver' => [
            'id' => $driver->id,
            'name' => $driver->name,
            'email' => $driver->contact_email,
            'mobile' => $driver->contact_no,
            'company_name' => $company->name,
            'licence_no' => $driver->driver_licence_no,
            'issue_no' => $driver->token_issue_number,
            'licence_valid_from' => $driver->token_valid_from_date,
            'licence_valid_to' => $driver->driver_licence_expiry,
            'gender' => $driver->gender,
            'dob' => $driver->driver_dob,
            'address' => $driver->driver_address.', '.$driver->post_code,
            'licence_status' => $driver->driver_licence_status,
            'licence_type' => $driver->licence_type,
            'tacho_card_no' => $driver->tacho_card_no,
            'tacho_card_valid_from' => $driver->tacho_card_valid_from,
            'tacho_card_valid_to' => $driver->tacho_card_valid_to,
            'cpc_valid_from' => $driver->dqc_issue_date,
            'cpc_valid_to' => $driver->cpc_validto,
            'pdf_url' => $baseUrl, // URL to view driver details with encoded ID
            'penalty_points' => $latestPenaltyPoints,
            'total_offence_code' => $uniqueOffenceCodeCount,
                            'last_lc_date' => $driver->latest_lc_check,
            'attachments' => $imageUrls, // Include the image URLs
        ],
    ]);
}


    public function getVehicleDetailsData(Request $request)
{
    // Validate the incoming request data
    $request->validate([
        'vehicleId' => 'required|integer|exists:vehicles,id',
    ]);

    // Get the vehicleId from the request
    $vehicleId = $request->input('vehicleId');

    // Get the authenticated user
    $user = Auth::user();

    // Fetch the company details by the ID stored in the authenticated user's record
    $company = CompanyDetails::find($user->companyname);

    if (! $company) {
        return response()->json(['status' => 0, 'error' => 'Company not found'], 404);
    }

    // Fetch a specific vehicle by its ID for the given company, including related vehicle details
    $vehicle = \App\Models\Vehicles::where('companyName', $company->id)
        ->where('id', $vehicleId)
        ->with('details') // Eager load the VehicleDetails
        ->first();

    if (! $vehicle) {
        return response()->json(['status' => 0, 'error' => 'Vehicle not found'], 404);
    }

    // Get the vehicleDetails ID
    $vehicleDetailsId = $vehicle->details->id;

    // Fetch the count of Contract_attachment records associated with the vehicleDetails ID
    $contractAttachmentCount = Contract_attachment::where('contract_id', $vehicleDetailsId)->count();

    // Fetch Contract_attachment records and generate URLs
    $attachments = Contract_attachment::where('contract_id', $vehicleDetailsId)
        ->get()
        ->map(function ($attachment) {
            // Assuming the file is stored in the 'public' disk
            return [
                'file_url' => Storage::url('image_attechment/' . $attachment->files),
                                'file_name' => $attachment->files,

            ];
        });

    // Helper function to handle `"-"` values
    $convertDashToNull = function ($value) {
        return $value === '-' ? null : $value;
    };

    // Fetch related VehiclesAnnualTest records
    $annualTests = \App\Models\VehiclesAnnualTest::where('vehicle_id', $vehicleId)
    ->with('defects') // Make sure defects relation is loaded
     ->orderBy('completed_date', 'desc')
    ->get()
    ->map(function ($test) use ($convertDashToNull) {
        // Group defects by type
        $groupedDefects = $test->defects->groupBy(function ($defect) {
            return strtoupper($defect->type);
        });

        // Type to title mapping
        $typeTitleMap = [
            'MAJOR' => 'Repair immediately (major defects)',
            'FAIL' => 'Repair immediately (major defects)',
            'PRS' => 'Repair immediately (major defects)',
            'ADVISORY' => 'Monitor and repair if necessary (advisories)',
            'DANGEROUS' => 'Do not drive until repaired (dangerous defects)',
            'MINOR' => 'Repair as soon as possible (minor defects)',
        ];

        // Create defect list by type with joined text values
        $defectsFormatted = collect($typeTitleMap)->map(function ($title, $type) use ($groupedDefects) {
            if (!isset($groupedDefects[$type])) {
                return null;
            }

            return [
                'defect_title' => $title,
                'defect_value' => $groupedDefects[$type]->pluck('text')->implode(' $ '),
            ];
        })->filter()->values();

        return [
            'test_date' => $convertDashToNull($test->completed_date) ? Carbon::parse($test->completed_date)->format('d/m/Y') : null,
            'test_result' => $test->test_result,
            'test_certificate_number' => $test->mot_test_number,
            'test_expiry_date' => $convertDashToNull($test->expiry_date) ? Carbon::createFromFormat('Y-m-d', $test->expiry_date)->format('d/m/Y') : null,
             'mileage' => $test->odometer_value . ' ' . ($test->odometer_unit ?? ''),
            'test_location' => $test->location,
            'defect' => $defectsFormatted,
        ];
    });

    // Format dates or keep as null
    $formattedDates = [
        'registration_date' => $convertDashToNull($vehicle->registration_date) ? Carbon::parse($vehicle->registration_date)->format('d/m/Y') : null,
        'annual_test_expiry_date' => $convertDashToNull($vehicle->annual_test_expiry_date) ? Carbon::parse($vehicle->annual_test_expiry_date)->format('d/m/Y') : null,
        'taxDueDate' => $convertDashToNull($vehicle->details->taxDueDate) ? Carbon::parse($vehicle->details->taxDueDate)->format('d/m/Y') : null,
        'motExpiryDate' => $convertDashToNull($vehicle->details->motExpiryDate) ? Carbon::parse($vehicle->details->motExpiryDate)->format('d/m/Y') : null,
        'dateOfLastV5CIssued' => $convertDashToNull($vehicle->details->dateOfLastV5CIssued) ? Carbon::parse($vehicle->details->dateOfLastV5CIssued)->format('d/m/Y') : null,
        'PMI_due' => $convertDashToNull($vehicle->details->PMI_due) ? Carbon::parse($vehicle->details->PMI_due)->format('d/m/Y') : null,
        'brake_test_due' => $convertDashToNull($vehicle->details->brake_test_due) ? Carbon::parse($vehicle->details->brake_test_due)->format('d/m/Y') : null,
        'tacho_calibration' => $convertDashToNull($vehicle->details->tacho_calibration) ? Carbon::parse($vehicle->details->tacho_calibration)->format('d/m/Y') : null,
        'date_of_inspection' => $convertDashToNull($vehicle->details->date_of_inspection) ? Carbon::parse($vehicle->details->date_of_inspection)->format('d/m/Y') : null,
        'dvs_pss_permit_expiry' => $convertDashToNull($vehicle->details->dvs_pss_permit_expiry) ? Carbon::parse($vehicle->details->dvs_pss_permit_expiry)->format('d/m/Y') : null,
        'insurance' => $convertDashToNull($vehicle->details->insurance) ? Carbon::parse($vehicle->details->insurance)->format('d/m/Y') : null,
    ];
$insuranceType = json_decode($vehicle->details->insurance_type, true);

    // Format VehicleDetails if available
    $vehicleDetails = $vehicle->details ? [
        'vehicle_attachment_count' => $contractAttachmentCount,
        'attachments' => $attachments, // Include attachments data
        'registration_number' => $vehicle->details->registrationNumber,
        'company_name' => $company->name,
        'make' => ($vehicle->make === "Details Not Provide By DVLA" || $vehicle->make === null) ? 'N/A' : $vehicle->make,
        'tax_status' => ($vehicle->details->taxStatus === "Details Not Provide By DVLA" || $vehicle->details->taxStatus === null) ? 'N/A' : $vehicle->details->taxStatus,
        'taxduedate' => $formattedDates['taxDueDate'] ?? 'N/A',
        'mot_status' => ($vehicle->details->motStatus === "Details Not Provide By DVLA" || $vehicle->details->motStatus === null) ? 'N/A' : $vehicle->details->motStatus,
        'yearofmanufacture' => ($vehicle->details->yearOfManufacture === "Details Not Provide By DVLA" || $vehicle->details->yearOfManufacture === null) ? 'N/A' : $vehicle->details->yearOfManufacture,
        'enginecapacity' => ($vehicle->details->engineCapacity === "Details Not Provide By DVLA" || $vehicle->details->engineCapacity === null) ? 'N/A' : $vehicle->details->engineCapacity,
        'co2emissions' => ($vehicle->details->co2Emissions === "Details Not Provide By DVLA" || $vehicle->details->co2Emissions === null) ? 'N/A' : $vehicle->details->co2Emissions,
        'insurance_type' => is_array($insuranceType) ? implode(',', $insuranceType) : ($insuranceType ?? 'N/A'),
        'insurance' => $formattedDates['insurance'] ?? 'N/A',
        'pmi_due' => $formattedDates['PMI_due'] ?? 'N/A',
        'brake_test_due' => $formattedDates['brake_test_due'] ?? 'N/A',
        'odometer_reading' => $vehicle->details->odometer_reading  ?? 'N/A',
        'fueltype' => ($vehicle->details->fuelType === "Details Not Provide By DVLA" || $vehicle->details->fuelType === null) ? 'N/A' : $vehicle->details->fuelType,
        'markedforexport' => ($vehicle->details->markedForExport === "Details Not Provide By DVLA" || $vehicle->details->markedForExport === null) ? 'N/A' : $vehicle->details->markedForExport,
        'colour' => ($vehicle->details->colour === "Details Not Provide By DVLA" || $vehicle->details->colour === null) ? 'N/A' : $vehicle->details->colour,
        'typeapproval' => ($vehicle->details->typeApproval === "Details Not Provide By DVLA" || $vehicle->details->typeApproval === null) ? 'N/A' : $vehicle->details->typeApproval,
        'revenueweight' => ($vehicle->details->revenueWeight === "Details Not Provide By DVLA" || $vehicle->details->revenueWeight === null) ? 'N/A' : $vehicle->details->revenueWeight,
        'eurostatus' => ($vehicle->details->euroStatus === "Details Not Provide By DVLA" || $vehicle->details->euroStatus === null) ? 'N/A' : $vehicle->details->euroStatus,
        'dateoflastv5cissued' => $formattedDates['dateOfLastV5CIssued'] ?? 'N/A',
        'motexpirydate' => $formattedDates['motExpiryDate'] ?? 'N/A',
        'wheelplan' => ($vehicle->details->wheelplan === "Details Not Provide By DVLA" || $vehicle->details->wheelplan === null) ? 'N/A' : $vehicle->details->wheelplan,
        'monthoffirstregistration' => ($vehicle->details->monthOfFirstRegistration === "Details Not Provide By DVLA" || $vehicle->details->monthOfFirstRegistration === null) ? 'N/A' : $vehicle->details->monthOfFirstRegistration,
        'tacho_calibration' => $formattedDates['tacho_calibration'] ?? 'N/A',
        'dvs_pss_permit_expiry' => $formattedDates['dvs_pss_permit_expiry'] ?? 'N/A',
        'date_of_inspection' => $formattedDates['date_of_inspection'] ?? 'N/A',
    ] : null;

    // Return the data as JSON
    return response()->json([
        'status' => 1,
        'vehicle_enquiry' => $vehicleDetails, // Include VehicleDetails data separately
        'vehicle_annual' => [
            'id' => $vehicle->id,
            'registration_number' => $vehicle->registrations,
            'make' => ($vehicle->make === "Details Not Provide By DVLA" || $vehicle->make === null) ? 'N/A' : $vehicle->make,
            'model' => ($vehicle->model === "Details Not Provide By DVLA" || $vehicle->model === null) ? 'N/A' : $vehicle->model,
            'registration_date' => $formattedDates['registration_date'] ?? 'N/A',
            'annual_test_expiry_date' => $formattedDates['annual_test_expiry_date'] ?? 'N/A',
            'annual_tests' => $annualTests, // Include VehiclesAnnualTest data
        ],
    ]);
}


    public function getOperatingDetailsData(Request $request)
    {
        // Validate the request to ensure driverId is provided
        $request->validate([
            'operatingId' => 'required|integer|exists:depots,id',
        ]);

        $operatingId = $request->input('operatingId');

        // Get the authenticated user
        $user = Auth::user();

        // Fetch the company details by the ID stored in the authenticated user's record
        $company = CompanyDetails::find($user->companyname);

        if (! $company) {
            return response()->json(['status' => 0, 'error' => 'Company not found'], 404);
        }

        // Fetch the specific driver by ID
        $operating = Depot::where('companyName', $company->id)->where('id', $operatingId)->first();

        if (! $operating) {
            return response()->json(['status' => 0, 'error' => 'Driver not found'], 404);
        }

        // Return the specific driver details as JSON
        return response()->json([
            'status' => 1,

                'name' => $operating->name,
                'company_name' => $company->name,
                'licence_no' => $operating->licence_number,
                'traffic_area' => $operating->traffic_area,
                'continuation_date' => $operating->continuation_date,
                'transport_manager_name' => $operating->transport_manager_name,
                'operating_status' => $operating->status,
                'operating_centre' => $operating->operating_centre,
                'vehicles' => $operating->vehicles,
                'trailers' => $operating->trailers,

        ]);
    }

     public function getManagerProfile(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();
        $userEmail = $user->email;

        // Fetch the company details where the operator_email JSON field contains the user's email
        $company = CompanyDetails::whereJsonContains('operator_email', $userEmail)->first();

        if (! $company) {
            return response()->json(['status' => 0, 'error' => 'Manager not found'], 404);
        }
        
        $userDepotIds = is_array($user->depot_id)
    ? $user->depot_id
    : json_decode($user->depot_id, true);

    

        // Fetch depots related to the company
        $userDepotIds = is_array($user->depot_id)
    ? $user->depot_id
    : json_decode($user->depot_id, true);

// ✅ Only user's depots
$depots = Depot::where('companyName', $company->id)
    ->whereIn('id', $userDepotIds)
    ->get();

// ✅ Sum from assigned depots only
$totalVehicles = $depots->sum('vehicles');
$totalTrailers = $depots->sum('trailers');

        // Decode the JSON arrays
        $names = json_decode($company->operator_name, true);
        $emails = json_decode($company->operator_email, true);
        $device = json_decode($company->device, true);
        $operator_phone = json_decode($company->operator_phone, true);
        $operator_dob = json_decode($company->operator_dob, true);
        $status = json_decode($company->status, true);
        $compliance = json_decode($company->compliance, true);

        // Find the index of the logged-in user's email
        $index = array_search($userEmail, $emails);

        // Handle case where email is not found in the array
        if ($index === false || ! isset($names[$index])) {
            return response()->json(['status' => 0, 'error' => 'Profile not found'], 404);
        }

        // Return the company details as JSON with the matched name and email
        return response()->json([
            'status' => 1,
            'profile' => [
                'company_name' => $company->name,
                'name' => $names[$index],
                'email' => $emails[$index],
                'device' => $device[$index],
                'operator_phone' => $operator_phone[$index],
'operator_dob' => isset($operator_dob[$index]) ? $operator_dob[$index] : null,
                'status' => $status[$index],
                'compliance' => $compliance[$index],
                'total_authorisation_vehicles' => $totalVehicles,
                'total_trailers' => $totalTrailers,

            ],
        ]);
    }

     public function uploadDriverAttachment(Request $request)
{
    // Validate request data
    $validator = Validator::make($request->all(), [
        'driver_id' => 'required|exists:drivers,id',
        'license_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'license_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'cpc_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'cpc_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'tacho_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'tacho_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'mpqc_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'mpqc_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'levelD_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'levelD_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'one_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'one_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_cards.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 0,'error' => $validator->errors()], 400);
    }

    // Retrieve driver_id from the request
    $driverId = $request->input('driver_id');

    // Retrieve or create driver attachment record
    $driverAttachment = \App\Models\DriverAttachments::where('driver_id', $driverId)->first();

    if (! $driverAttachment) {
        $driverAttachment = new \App\Models\DriverAttachments();
        $driverAttachment->driver_id = $driverId;
    }

    // Handle file uploads
    if ($request->hasFile('license_front')) {
        $frontPath = $request->file('license_front')->store('driver_attachments/licenses-image/front');
        $driverAttachment->license_front = $frontPath;
    }

    if ($request->hasFile('license_back')) {
        $backPath = $request->file('license_back')->store('driver_attachments/licenses-image/back');
        $driverAttachment->license_back = $backPath;
    }

    if ($request->hasFile('cpc_card_front')) {
        $CpcCardfrontPath = $request->file('cpc_card_front')->store('driver_attachments/CPC-Card-image/front');
        $driverAttachment->cpc_card_front = $CpcCardfrontPath;
    }

    if ($request->hasFile('cpc_card_back')) {
        $CpcCardbackPath = $request->file('cpc_card_back')->store('driver_attachments/CPC-Card-image/back');
        $driverAttachment->cpc_card_back = $CpcCardbackPath;
    }

    if ($request->hasFile('tacho_card_front')) {
        $TachoCardfrontPath = $request->file('tacho_card_front')->store('driver_attachments/Tacho-Card-image/front');
        $driverAttachment->tacho_card_front = $TachoCardfrontPath;
    }

    if ($request->hasFile('tacho_card_back')) {
        $TachoCardbackPath = $request->file('tacho_card_back')->store('driver_attachments/Tacho-Card-image/back');
        $driverAttachment->tacho_card_back = $TachoCardbackPath;
    }

    if ($request->hasFile('mpqc_card_front')) {
        $MPQCCardfrontPath = $request->file('mpqc_card_front')->store('driver_attachments/MPQC-Card-image/front');
        $driverAttachment->mpqc_card_front = $MPQCCardfrontPath;
    }

    if ($request->hasFile('mpqc_card_back')) {
        $MPQCCardbackPath = $request->file('mpqc_card_back')->store('driver_attachments/MPQC-Card-image/back');
        $driverAttachment->mpqc_card_back = $MPQCCardbackPath;
    }

    if ($request->hasFile('levelD_card_front')) {
        $levelDCardfrontPath = $request->file('levelD_card_front')->store('driver_attachments/levelD-Card-image/front');
        $driverAttachment->levelD_card_front = $levelDCardfrontPath;
    }

    if ($request->hasFile('levelD_card_back')) {
        $levelDCardbackPath = $request->file('levelD_card_back')->store('driver_attachments/levelD-Card-image/back');
        $driverAttachment->levelD_card_back = $levelDCardbackPath;
    }

    if ($request->hasFile('one_card_front')) {
        $OneCardfrontPath = $request->file('one_card_front')->store('driver_attachments/One-Card-image/front');
        $driverAttachment->one_card_front = $OneCardfrontPath;
    }

    if ($request->hasFile('one_card_back')) {
        $OneCardbackPath = $request->file('one_card_back')->store('driver_attachments/One-Card-image/back');
        $driverAttachment->one_card_back = $OneCardbackPath;
    }

    if ($request->hasFile('additional_cards')) {
        $existingPaths = $driverAttachment->additional_cards ? json_decode($driverAttachment->additional_cards, true) : [];
        $newPaths = [];

        foreach ($request->file('additional_cards') as $file) {
            $path = $file->store('driver_attachments/additional_card_images');
            $newPaths[] = $path;
        }

        // Combine old and new paths
        $allPaths = array_merge($existingPaths, $newPaths);
        $driverAttachment->additional_cards = json_encode($allPaths); // Store paths as JSON
    }

    $driverAttachment->save();

    return response()->json([
        'status' => 1,
        'message' => 'Driver File Upload successful',
    ]);
}

public function uploadVehicleAttachment(Request $request)
{
    try {
        // Validate the request
        $request->validate([
            'vehicle_id' => 'required|integer|exists:vehicles,id',
            'file' => 'required|file',
        ]);

        $vehicleId = $request->input('vehicle_id');
        $vehicle = \App\Models\Vehicles::find($vehicleId);
        if (!$vehicle) {
            return response()->json(['status' => 0,'error' => 'Vehicle not found.'], 404);
        }

        // Find the VehicleDetails record associated with this vehicle
        $vehicleDetails = \App\Models\vehicleDetails::where('vehicle_id', $vehicleId)->first();
        if (!$vehicleDetails) {
            return response()->json(['status' => 0,'error' => 'Vehicle details not found.'], 404);
        }

        // Handle file upload
        $file = $request->file('file');
        $fileSize = $file->getSize();
        $fileName = $vehicleId . '_' . $file->getClientOriginalName();
        $filePath = 'image_attechment/' . $fileName;

        // Check and update storage limit
        $userId = \Auth::user()->creatorId();
        $result = Utility::updateStorageLimit($userId, $fileSize);
        if ($result !== 1) {
            return response()->json(['status' => 0,'error' => 'Storage limit exceeded or update failed.'], 400);
        }

        // Upload file
        $uploadResult = Utility::upload_file($request, 'file', $fileName, 'image_attechment/', []);
        if ($uploadResult['flag'] !== 1) {
            return response()->json(['status' => 0,'error' => $uploadResult['msg']], 400);
        }

        // Save to Contract_attachment
        $fileRecord = \App\Models\Contract_attachment::create([
            'contract_id' => $vehicleDetails->id, // Assuming 'contract_id' in Contract_attachment refers to VehicleDetails ID
            'user_id' => \Auth::user()->id,
            'files' => $fileName,
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Vehicle File Upload successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json(['status' => 0,'error' => 'An error occurred: '], 500);
    }
}

public function getpreviewwalkaroundManager(Request $request)
{
    // Validate the optional vehicle_id, page, and uploaded_date input
    $request->validate([
        'vehicle_id' => 'nullable|integer', // Make vehicle_id optional
        'page' => 'nullable|integer|min:1', // Validate the page number
        'date' => ['nullable', 'regex:/\d{2}\/\d{2}\/\d{4}/'], // Validate the uploaded_date as dd/MM/yyyy
    ]);

    // Get the logged-in driver user
    $user = Auth::user();
    

    // Ensure the driver user is logged in
    if (!$user) {
        return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
    }

        $userDepotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
        
        $userDriverGroupIds = is_array($user->driver_group_id)
    ? $user->driver_group_id
    : json_decode($user->driver_group_id, true);

$userVehicleGroupIds = is_array($user->vehicle_group_id)
    ? $user->vehicle_group_id
    : json_decode($user->vehicle_group_id, true);


    // Build the query for WorkAroundStore
    $query = \App\Models\WorkAroundStore::whereHas('driver', function ($q) use ($user, $userDriverGroupIds) {
        $q->where('companyName', $user->companyname)
          ->where('step', 'done')

          // ✅ Driver group filter
          ->when(!empty($userDriverGroupIds), function ($subQ) use ($userDriverGroupIds) {
              $subQ->whereIn('group_id', $userDriverGroupIds);
          });
    })
    ->whereIn('operating_centres', $userDepotIds)

    // ✅ Vehicle group filter (MANDATORY)
    ->when(!empty($userVehicleGroupIds), function ($query) use ($userVehicleGroupIds) {
        $query->whereHas('vehicle.vehicleDetail', function ($q) use ($userVehicleGroupIds) {
            $q->whereIn('group_id', $userVehicleGroupIds);
        });
    })->whereIn('operating_centres', $userDepotIds);

    // Apply driver_id filter if provided
    if ($request->has('driver_id') && $request->input('driver_id')) {
        $query->where('driver_id', $request->input('driver_id'));
    }

    // Apply vehicle_id filter if provided
    if ($request->has('vehicle_id')) {
        $query->where('vehicle_id', $request->input('vehicle_id'));
    }

    // Apply uploaded_date filter if provided
    if ($request->has('date')) {
        try {
            $inputDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('date'))
                ->format('d/m/Y');
            $query->where('uploaded_date', 'like', $inputDate . '%');
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'error' => 'Invalid date format'], 400);
        }
    }

    // Order by uploaded_date in descending order
    $query->orderBy('created_at', 'desc');

    // Check if the page parameter is provided
    $page = $request->input('page');

    if ($page) {
        $workAroundStores = $query->with(['vehicle', 'driver'])
            ->paginate(30, [
                'id',
                'uploaded_date',
                'vehicle_id',
                'driver_id',
                'defects_count',
                'duration'
            ]);
    } else {
        $workAroundStores = $query->with(['vehicle', 'driver'])
            ->get([
                'id',
                'uploaded_date',
                'vehicle_id',
                'driver_id',
                'defects_count',
                'duration'
            ]);
    }

    // Map the result to include vehicle registration number, driver name, and defect status
    $result = $workAroundStores->map(function($item) {
        $isShortDuration = false;
        if ($item->duration) {
            $durationString = $item->duration;
            $totalSeconds = 0;

            if (preg_match('/(\d+)\s*min/', $durationString, $minutesMatch)) {
                $totalSeconds += (int)$minutesMatch[1] * 60; // Convert minutes to seconds
            }

            if (preg_match('/(\d+)\s*sec/', $durationString, $secondsMatch)) {
                $totalSeconds += (int)$secondsMatch[1]; // Add seconds
            }

            // Check if the total duration is less than 10 minutes (600 seconds)
            $isShortDuration = $totalSeconds < 600;
        }

        $vehicleDisplay = 'N/A';
            if ($item->vehicle) {
                if ($item->vehicle->vehicle_type == 'Trailer') {
                    $vehicleDisplay = $item->vehicle->vehicleDetail->vehicle_nick_name ?? 'No Vehicle ID';
                } else {
                    $vehicleDisplay = $item->vehicle->registrations ?? 'No Registration';
                }
            }

        return [
            'id' => $item->id,
            'driver_id' => $item->driver_id,
            'uploaded_date' => $item->uploaded_date,
            'defects_count' => $item->defects_count,
            'vehicle' => $vehicleDisplay,
            'driver_name' => $item->driver ? $item->driver->name : 'N/A',
            'defect_status' => is_null($item->defects_count) || $item->defects_count == 0 ? 'Completed' : 'Not-completed',
            'duration' => $item->duration,
            'is_short_duration' => $isShortDuration ? 1 : 0, // Add the new parameter
        ];
    });

    // Return the paginated or all data as JSON
    return response()->json([
        'status' => 1,
        'data' => $result,
    ], 200);
}






    public function getpreviewwalkaroundDetailsManager(Request $request)
    {
        // Validate the input to ensure 'id' and 'driver_id' are provided and valid
        $request->validate([
            'id' => 'required|integer|exists:work_around_stores,id', // Validate that 'id' exists in WorkAroundStore table
            'driver_id' => 'required|integer|exists:drivers,id', // Ensure driver_id is provided and exists
        ]);

        // Get the logged-in driver user
        $user = Auth::user();

        // Ensure the driver user is logged in
        if (!$user) {
            return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
        }

        // Fetch the corresponding driver record using the DriverUser's company name and provided driver_id
        $driver = Driver::where('companyName', $user->companyname) // Adjust this field name if necessary
                        ->where('id', $request->input('driver_id'))
                        ->first();

        if (!$driver) {
            return response()->json(['status' => 0, 'error' => 'Driver not found'], 404);
        }

        // Get the ID from the request
        $id = $request->input('id');

        // Fetch the specific WorkAroundStore record
        $workAroundStore = \App\Models\WorkAroundStore::where('id', $id)
            ->where('driver_id', $driver->id) // Ensure the record belongs to the logged-in driver
            ->with(['vehicle.vehicleDetail', 'driver', 'types', 'workAroundQuestionAnswers']) // Eager load related models including workAroundQuestionAnswers
            ->first();

        // Check if the WorkAroundStore record exists
        if (!$workAroundStore) {
            return response()->json(['status' => 0, 'error' => 'WorkAroundStore record not found'], 404);
        }

        // Format the vehicle details
        // $vehicleDetails = $workAroundStore->vehicle
        //     ? $workAroundStore->vehicle->registrations . ' (' . $workAroundStore->vehicle->vehicleDetail->make . ')'
        //     : 'N/A';

         $vehicleDetails = 'N/A';
    if ($workAroundStore->vehicle) {
        $make = $workAroundStore->vehicle->vehicleDetail->make ?? 'Unknown Make';

        if ($workAroundStore->vehicle->vehicle_type == 'Trailer') {
            $vehicleId = $workAroundStore->vehicle->vehicleDetail->vehicle_nick_name ?? 'No Vehicle ID';
            $vehicleDetails = "{$vehicleId} ({$make})";
        } else {
            $registration = $workAroundStore->vehicle->registrations ?? 'No Registration';
            $vehicleDetails = "{$registration} ({$make})";
        }
    }

        // Initialize workAroundQuestionAnswers if it's null
        $workAroundQuestionAnswers = $workAroundStore->workAroundQuestionAnswers ?? collect();

        // Separate question answers into two parts
        $questionsWithNullValues = $workAroundQuestionAnswers->filter(function ($answer) {
            return is_null($answer->image) && is_null($answer->reason);
        });

        $defectsWithValues = $workAroundQuestionAnswers->filter(function ($answer) {
            return !is_null($answer->image) || !is_null($answer->reason);
        });

        // Encode the driver ID
        $encodedId = base64_encode($workAroundStore->id);

        // Define the base URL for driver details view
        $baseUrl = url('/walkAround/pdf/'.$encodedId);



        // Format the details for response
        $details = [
            'walkaround_id' => $workAroundStore->id,
            'pdf' => $baseUrl,
            'company' => $workAroundStore->types->name ?? 'N/A', // Ensure 'name' field exists in Types model
            'vehicle' => $vehicleDetails,
            'driver_name' => $workAroundStore->driver ? $workAroundStore->driver->name : 'N/A', // Ensure this field exists in Driver model
            'profile' => $workAroundStore->profile->name ?? 'N/A', // Ensure 'name' field exists in Profile model
            'date' => $workAroundStore->uploaded_date,
            'fuel_type' => $workAroundStore->fuel_level,
            'adblue_level' => $workAroundStore->adblue_level,
            'odometer' => $workAroundStore->speedo_odometer,
            'rectified' => $workAroundStore->rectified ?? 0,
            'defects' => $defectsWithValues->count(), // Add count of defects with values

            'non_defects_question' => $questionsWithNullValues->values()->map(function ($item, $index) {
                return [
                    'index' => $index + 1, // Add index starting from 1
                    'question' => $item->question->name, // Assuming 'name' is the correct field
                ];
            })->toArray(), // Convert to array
            'defects_question' => $defectsWithValues->map(function ($item,$index) {
                return [
                    'index' => $index + 1, // Add index starting from 1
                    'question' => $item->question->name,
        'image' => url('storage/' . $item->image), // Generate full URL for the image
                    'vehicle' => $item->workAroundStore->vehicle->registrations,
                ];
            })->values()->toArray(), // Convert to array and reset keys

        ];

        // Return the details as JSON
        return response()->json([
            'status' => 1,
            'data' => $details,
        ], 200);
    }

    public function getdefectwalkaroundDetailsManager(Request $request)
    {
        // Get the logged-in driver user
        $user = Auth::user();

        // Ensure the user is logged in
        if (!$user) {
            return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
        }

        // Fetch the corresponding driver record using the DriverUser's company name
        $driver = Driver::where('companyName', $user->companyname) // Adjust this field name if necessary
            ->first();

        if (!$driver) {
            return response()->json(['status' => 0, 'error' => 'Driver not found'], 404);
        }

        // Validate and get the WorkAroundStore ID from the request
        $workAroundStoreId = $request->input('workaround_store_id');

        if (!$workAroundStoreId) {
            return response()->json(['status' => 0, 'error' => 'WorkAroundStore ID is required'], 400);
        }

        // // Fetch the WorkAroundStore record with the given ID
        // $workAroundStore = \App\Models\WorkAroundStore::where('id', $workAroundStoreId)
        //     ->where('driver_id', $driver->id)
        //     ->first();


        // Fetch the WorkAroundStore record with the given ID
        $workAroundStore = \App\Models\WorkAroundStore::where('id', $workAroundStoreId)
            ->first();

        if (!$workAroundStore) {
            return response()->json(['status' => 0, 'error' => 'WorkAroundStore not found or unauthorized'], 404);
        }

        // Fetch defect questions related to the WorkAroundStore ID
        $defectQuestions = \App\Models\WorkAroundQuestionAnswerStore::where('workaround_store_id', $workAroundStoreId)
            ->where(function ($query) {
                $query->whereNotNull('image')
                      ->orWhereNotNull('reason')
                      ->orWhereNotNull('rectified_date');
            })
            ->with(['question', 'defectHistory']) // Eager load question and defect history relationships
            ->get(['id', 'reason', 'image', 'question_id', 'rectified_date']); // Fetch relevant columns

        // Fetch the uploaded date and vehicle registration number
        $uploadedDate = $workAroundStore->uploaded_date ?? 'N/A'; // Default to 'N/A' if uploaded_date is null
        // $registrationNumber = $workAroundStore->vehicle->registrations ?? 'N/A'; // Default to 'N/A' if registration_number is null
         $registrationNumber = 'N/A';
    if ($workAroundStore->vehicle) {
        if ($workAroundStore->vehicle->vehicle_type == 'Trailer') {
            $registrationNumber = $workAroundStore->vehicle->vehicleDetail->vehicle_nick_name ?? 'No Vehicle ID';
        } else {
            $registrationNumber = $workAroundStore->vehicle->registrations ?? 'No Registration';
        }
    }

        // Prepare result data
        $result = $defectQuestions->map(function ($item) use ($uploadedDate, $registrationNumber) {
            // Determine the reason to show
            $rectifiedReason = $item->defectHistory->reason ?? $item->reason;

             // Determine the final image to show
        $finalImage = null; // Default to null

        if ($item->defectHistory) {
            $finalImage = $item->defectHistory->image ? url('storage/' . $item->defectHistory->image) : null;
        }

        if (!$finalImage && $item->image) {
            $finalImage = url('storage/' . $item->image);
        }

            // Determine rectified status
            $status = $item->rectified_date ? 'Rectified' : 'Not-Rectified';

            return [
                'id' => $item->id,
                'reason' => $rectifiedReason, // Use rectified reason if available
                'question_id' => $item->question_id,
                'name' => $item->question->name ?? 'N/A', // Add name from WorkAroundQuestion
                'uploaded_date' => $uploadedDate, // Add uploaded_date
                'registration_number' => $registrationNumber,
                'image' => $finalImage, // Check for null
                'status' => $status, // Include rectified status
            ];
        })->values(); // Reset keys for indexed array

        // Return the result with defect questions including rectified data
        return response()->json([
            'status' => 1,
            'defect_questions' => $result,
        ], 200);
    }

    public function storedefectwalkaroundRectifieldManager(Request $request)
{
    // Get the logged-in driver user
    $user = Auth::user();

    // Ensure the driver user is logged in
    if (!$user) {
        return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
    }

    // Fetch the corresponding driver record using the DriverUser's ID
    $driver = Driver::where('companyName', $user->companyname)->first();

    if (!$driver) {
        return response()->json(['status' => 0, 'error' => 'Driver not found'], 404);
    }

    // Validate the incoming request data
   $validator = Validator::make($request->all(), [
    'workaround_question_answer_id' => 'required|exists:work_around_question_answer_stores,id',
    'problem_type' => 'required|string',
    'problem_solution' => 'required|string',
    'third_party' => 'required|string',
    'rectified_date' => 'required|date_format:d/m/Y H:i:s',
    'defect_options' => 'required|string',
    'rectified_signature' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5000',
    'reason' => 'nullable|string',
    'image' => 'nullable|string',
    'rectified_images.*' => 'nullable|mimes:jpeg,png,jpg,pdf|max:5000',
]);

if ($validator->fails()) {
    return response()->json(['status' => 0, 'error' => $validator->errors()], 400);
}

// Retrieve the validated data correctly
$validated = $validator->validated();


    // Fetch the WorkAroundQuestionAnswerStore record with the given ID
    $workAroundQAStore = \App\Models\WorkAroundQuestionAnswerStore::find($validated['workaround_question_answer_id']);

    if (!$workAroundQAStore) {
        return response()->json(['status' => 0, 'error' => 'WorkAroundQuestionAnswerStore record not found'], 404);
    }

    // Handle rectified_signature file upload if provided
    $rectifiedSignaturePath = null;
    if ($request->hasFile('rectified_signature')) {
        // Store the signature in the desired directory
        $rectifiedSignaturePath = $request->file('rectified_signature')->store('walkaround_rectified_signatures', 'local');
    } else {
        $rectifiedSignaturePath = null; // If not provided, keep null
    }

    // Update the WorkAroundQuestionAnswerStore record with the provided data
    $workAroundQAStore->update([
        'problem_type' => $validated['problem_type'],
        'problem_solution' => $validated['problem_solution'],
        'third_party' => $validated['third_party'],
        'defect_options' => $validated['defect_options'],
        'rectified_signature' => $rectifiedSignaturePath,
        'rectified_username' => $user->username,
        'rectified_date' => $validated['rectified_date'],
    ]);

    // Fetch the corresponding WorkAroundStore using work_around_stores_id
    $workAroundStore = \App\Models\WorkAroundStore::where('id', $workAroundQAStore->workaround_store_id)->first();

    if ($workAroundStore) {
        // Only update rectified if defect_count is greater than 0
        if ($workAroundStore->defects_count > 0) {
            $newDefectValue = max(0, $workAroundStore->defects_count - 1); // Ensure defect count doesn't go below 0
            $newRectifiedValue = $workAroundStore->rectified + 1;

            // Update the WorkAroundStore record
            $workAroundStore->update([
                'defects_count' => $newDefectValue,
                'rectified' => $newRectifiedValue,
            ]);
        }
    } else {
        return response()->json(['status' => 0, 'error' => 'WorkAroundStore record not found'], 404);
    }

    // Insert the 'reason' and 'image' values into WorkAroundDefectsHistories
    \App\Models\WorkAroundDefectsHistories::create([
        'workaround_question_answer_id' => $workAroundQAStore->id,
        'reason' => $workAroundQAStore->reason,
        'image' => $workAroundQAStore->image,
    ]);

    // Update the WorkAroundQuestionAnswerStore record to set 'reason' and 'image' to null
    $workAroundQAStore->update([
        'reason' => null,
        'image' => null,
    ]);

    // **Handle Multiple Rectified Images**
    if ($request->hasFile('rectified_images')) {
        foreach ($request->file('rectified_images') as $image) {
            $imagePath = $image->store('walkaround_rectified_images', 'local');

            \App\Models\WorkAroundRectifiedImages::create([
                'answer_id' => $workAroundQAStore->id,
                'image_path' => $imagePath,
            ]);
        }
    }

    // Return success response
    return response()->json([
        'status' => 1,
        'message' => 'Defect details updated, rectified, and history logged successfully with images',
    ], 200);
}

public function getvehiclelistManager(Request $request)
{
    // Get the logged-in driver user
    $user = Auth::user();

    // Ensure the driver user is logged in
    if (!$user) {
        return response()->json(['status' => 0,'error' => 'Unauthorized'], 401);
    }

    // Fetch the corresponding driver record using the DriverUser's ID
    $driver = Driver::where('companyName', $user->companyname) // Adjust this field name if necessary
    ->where('id', $request->input('driver_id'))
    ->first();

    if (!$driver) {
        return response()->json(['status' => 0,'error' => 'Driver not found'], 404);
    }

    // Fetch the company associated with the driver
    $company = $driver->companyDetails;

    if (!$company) {
        return response()->json(['status' => 0,'error' => 'Company not found'], 404);
    }

    // Fetch the vehicles associated with the company and their details using eager loading
    $vehicles = \App\Models\Vehicles::with('vehicleDetail') // Eager load vehicle details
                ->where('companyName', $company->id)
                ->get(['id', 'registrations']);

    // Transform the vehicles to include the make from vehicleDetails
    $vehicleList = $vehicles->map(function ($vehicle) {
        return [
            'id' => $vehicle->id,
            'registration' => $vehicle->registrations,
            'make' => $vehicle->vehicleDetail->make ?? 'Unknown',  // Include make from vehicleDetails
        ];
    });

    // Return the company name and vehicle registration numbers with details as JSON
    return response()->json([
        'status' => 1,
        'company_name' => $company->name,
        'vehicles' => $vehicleList
    ], 200);
}

public function updateDefect(Request $request)
{
    // Validate incoming request
    $request->validate([
        'id' => 'required|exists:work_around_question_answer_stores,id',
        'status' => 'required|string', // Ensure status is a string
    ]);

    // Find the answer by ID
    $answer = \App\Models\WorkAroundQuestionAnswerStore::findOrFail($request->id); // Changed to use 'id' from the request

    // Update the status
    $answer->status = $request->status;

    // Set image and reason to null
    $answer->image = null;
    $answer->reason = null;

    // Save the updated answer
    $answer->save();

    // Update defect count in WorkAroundStore model
    $walkaround = $answer->workAroundStore; // Assuming you have a relation defined
    if ($walkaround) {
        $walkaround->defects_count = max(0, $walkaround->defects_count - 1); // Ensure defect count doesn't go negative
        $walkaround->save();
    }

    return response()->json([
        'status' => 1,
        'message' => 'Status updated successfully', // Fixed typo 'messasge'
    ], 200);
}

public function getManagerNotification(Request $request)
{
    // Get the logged-in driver user
    $user = Auth::user();

    // Ensure the driver user is logged in
    if (!$user) {
        return response()->json(['status' => 0,'error' => 'Unauthorized'], 401);
    }

    $depotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
        if (!is_array($depotIds)) {
            $depotIds = [$user->depot_id]; // Ensure it remains an array
        }

    // Fetch the company associated with the driver
    $company = $user->types;

    if (!$company) {
        return response()->json(['status' => 0,'error' => 'Company not found'], 404);
    }

    // Fetch the vehicles associated with the company and their details using eager loading
    $notification = \App\Models\WorkAroundNotification::where('company_id', $company->id)->whereIn('depot_id', $depotIds)
    ->orderBy('created_at', 'desc') // or use 'id' if you don't have 'created_at' field
        ->take(30)
                ->get(['id', 'message','title','key']);

    // Transform the vehicles to include the make from vehicleDetails
    $notificationList = $notification->map(function ($notifications) {
        return [
            'id' => $notifications->id,
            'key' => $notifications->key,
            'title' => $notifications->title,
            'message ' => $notifications->message,
        ];
    });

    $notificationcount =  $notificationList->count();
    $managerApp = \App\Models\AppVersion::where('type', 'manager')->first();

    // Return the company name and vehicle registration numbers with details as JSON
    return response()->json([
        'status' => 1,
        'company_name' => $company->name,
         'app_version' => optional($managerApp)->version,
        'maintenance' => optional($managerApp)->maintenance_mode ? true : false,
        'total_notification' => $notificationcount,
        'notification' => $notificationList
    ], 200);
}

public function deleteNotification(Request $request)
{
    // Validate the request to ensure the 'id' is provided
    $request->validate([
        'id' => 'required|integer|exists:work_around_notifications,id',
    ]);

    // Get the logged-in user (assuming authorization is required)
    $user = Auth::user();

    // Ensure the user is logged in
    if (!$user) {
        return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
    }

    // Find the notification by ID
    $notification = \App\Models\WorkAroundNotification::find($request->id);

    if (!$notification) {
        return response()->json(['status' => 0, 'error' => 'Notification not found'], 404);
    }

    // Optionally check if the notification belongs to the user's company
    $company = $user->types;

    if ($notification->company_id !== $company->id) {
        return response()->json(['status' => 0, 'error' => 'Unauthorized action'], 403);
    }

    // Delete the notification
    $notification->delete();

    // Get the updated notification count for the user's company
    $notificationCount = \App\Models\WorkAroundNotification::where('company_id', $company->id)->count();

    // Return a success response with the updated notification count
    return response()->json([
        'status' => 1,
        'message' => 'Notification deleted successfully',
        'notification_count' => $notificationCount,
    ], 200);
}

public function getManagerTrainingAssignments(Request $request)
{
    // Get the logged-in user
    $user = Auth::user();

    // Ensure the user is logged in
    if (!$user) {
        return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
    }

    // Get the user's assigned depots
    $userDepotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
    $userDriverGroupIds = is_array($user->driver_group_id)
    ? $user->driver_group_id
    : json_decode($user->driver_group_id, true);

    // Check if a driver ID is provided
    $driverId = $request->input('driver_id');
    $startDate = $request->input('from_date'); // Start date for the filter
    $endDate = $request->input('to_date'); // End date for the filter

    // Convert start and end date from dd/mm/yyyy to Y-m-d format
    if ($startDate) {
        $startDate = Carbon::createFromFormat('d/m/Y', $startDate)->format('Y-m-d');
    }
    if ($endDate) {
        $endDate = Carbon::createFromFormat('d/m/Y', $endDate)->format('Y-m-d');
    }

    // Validate if start_date is greater than end_date
    if ($startDate && $endDate && Carbon::parse($startDate)->gt(Carbon::parse($endDate))) {
        return response()->json(['status' => 0, 'error' => 'Start date cannot be greater than end date'], 400);
    }

    // Query drivers based on company and depot_id
    $driversQuery = Driver::where('companyName', $user->companyname)
        ->whereIn('depot_id', $userDepotIds)
        ->when(!empty($userDriverGroupIds), function ($query) use ($userDriverGroupIds) {
        $query->whereIn('group_id', $userDriverGroupIds);
    });

    if ($driverId) {
        // Fetch a specific driver only if it exists within allowed depots
        $driversQuery->where('id', $driverId);
    }

    // Get the drivers (either specific or all from the company within depot scope)
    $drivers = $driversQuery->get();

    if ($drivers->isEmpty()) {
        return response()->json(['status' => 0, 'error' => 'Driver(s) not found'], 404);
    }

    $trainingData = [];

    // Loop through each driver and fetch their training assignments
    foreach ($drivers as $driver) {
        // Fetch training assignments for the driver, with date filtering if provided
        $trainingAssignments = \App\Models\TrainingDriverAssign::where('driver_id', $driver->id)
            ->with(['training' => function ($query) use ($user, $startDate, $endDate) {
                $query->where('companyName', $user->companyname) // Ensure it's for the correct company
                    ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                        return $query->whereBetween('from_date', [$startDate, $endDate]);
                    })
                    ->with('trainingType', 'trainingCourse'); // Eager load relationships
            }])
            ->get();

        // Loop through the assignments and build the response data
        foreach ($trainingAssignments as $assignment) {
            $training = $assignment->training;

            if ($training) {
                $trainingData[] = [
                    'driver_id' => $driver->id, // Include driver ID
                    'driver_name' => $driver->name, // Include driver name if needed
                    'id' => $assignment->id,
                    'training_id' => $assignment->training_id,
                    'training_type' => $training->trainingType->name ?? null,
                    'training_course' => $training->trainingCourse->name ?? null,
                    'training_status' => $assignment->status,
                    'status' => $training->status ?? null,
                    'from_date' => $training->from_date ? Carbon::parse($training->from_date)->format('d/m/Y') : null,
                    'to_date' => $training->to_date ? Carbon::parse($training->to_date)->format('d/m/Y') : null,
                    'from_time' => $training->from_time ? Carbon::parse($training->from_time)->format('g:i A') : null,
                    'to_time' => $training->to_time ? Carbon::parse($training->to_time)->format('g:i A') : null,
                    'reason' => $assignment->reason,
                    'signature' => url('storage/' . $assignment->signature),
                    'file' => url('storage/' . $assignment->file),
                ];
            }
        }
    }

    // Return the training assignments as JSON
    return response()->json([
        'status' => 1,
        'training' => $trainingData,
    ], 200);
}




// public function getManagerPlanner(Request $request)
// {
//     // Get the logged-in user
//     $user = Auth::user();

//     // Ensure the user is logged in
//     if (!$user) {
//         return response()->json(['status' => 0,'error' => 'Unauthorized'], 401);
//     }

//     // Retrieve the company name associated with the user (from the Fleet model)
//     $company = Fleet::where('company_id', $user->companyname)->first(); // Adjust 'company_id' if necessary

//     // If no company is found, return an error or default response
//     if (!$company) {
//         return response()->json(['status' => 0,'error' => 'Company not found'], 404);
//     }

//     // Start the base query for Fleet model
//     $query = Fleet::where('company_id', $user->companyname) // Adjust 'company_id' if needed
//         ->select('fleets.vehicle_id', 'fleets.id as fleet_id', 'planner_type');  // Explicitly specify the table for vehicle_id

//     // If a vehicle_id is provided in the request, filter by it
//     if ($request->has('vehicle_id') && !empty($request->vehicle_id)) {
//         $query->where('fleets.vehicle_id', $request->vehicle_id);  // Explicitly reference fleets.vehicle_id
//     }

//     // If a planner_type is provided in the request, filter by it
//     if ($request->has('planner_type') && !empty($request->planner_type)) {
//         $query->where('planner_type', $request->planner_type);
//     }

//     // Join with vehicleDetails to get the registrationNumber
//     $query->leftJoin('vehicle_details', 'vehicle_details.id', '=', 'fleets.vehicle_id')
//           ->addSelect('vehicle_details.registrationNumber'); // Select the registrationNumber from vehicle_details

//     // Retrieve the planner data
//     $plannerData = $query->get();

//     // Add date filters for next_reminder_date (from_date to to_date)
//     if ($request->has('from_date') && !empty($request->from_date) && $request->has('to_date') && !empty($request->to_date)) {
//         $fromDate = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay();
//         $toDate = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay();

//         // Validate: from_date must be less than or equal to to_date
//             if ($fromDate->greaterThan($toDate)) {
//                 return response()->json(['status' => 0,'error' => 'From Date must be less than or equal to To Date.'], 422);
//             }

//         // Filter the planner reminders by the next_reminder_date within the date range
//         $plannerRemindersData = $plannerData->flatMap(function ($planner) use ($fromDate, $toDate) {
//             // Find all planner reminders based on fleet_planner_id
//             $plannerReminders = \App\Models\FleetPlannerReminder::where('fleet_planner_id', $planner->fleet_id)
//                 ->whereBetween('next_reminder_date', [$fromDate->toDateString(), $toDate->toDateString()])->orderBy('next_reminder_date', 'asc')
//                 ->get();

//             // Map each planner reminder to include the planner_type and vehicle registration modal
//             return $plannerReminders->map(function ($reminder) use ($planner) {
//                 return [
//                     'id' => $reminder->id,
//                     'vehicle_id' => $planner->vehicle_id,
//                     'planner_type' => $planner->planner_type,  // Add planner_type inside the reminder object
//                     'next_reminder_date' => Carbon::parse($reminder->next_reminder_date)->format('d/m/Y'),  // Format the date as dd/mm/yyyy
//                     'status' => $reminder->status,
//                     'registration_number' => $planner->registrationNumber,
//                 ];
//             });
//         });
//     } else {
//         // If no date filters are provided, return all planner reminders
//         $plannerRemindersData = $plannerData->flatMap(function ($planner) {
//             // Find all planner reminders based on fleet_planner_id
//             $plannerReminders = \App\Models\FleetPlannerReminder::where('fleet_planner_id', $planner->fleet_id)->get();

//             // Map each planner reminder to include the planner_type and vehicle registration modal
//             return $plannerReminders->map(function ($reminder) use ($planner) {
//                 return [
//                     'id' => $reminder->id,
//                     'vehicle_id' => $planner->vehicle_id,
//                     'planner_type' => $planner->planner_type,  // Add planner_type inside the reminder object
//                     'next_reminder_date' => Carbon::parse($reminder->next_reminder_date)->format('d/m/Y'),  // Format the date as dd/mm/yyyy
//                     'status' => $reminder->status,
//                     'registration_number' => $planner->registrationNumber,
//                 ];
//             });
//         });
//     }

//     // Return the planner reminders in the desired format
//     return response()->json([
//         'status' => 1,
//         'planner' => $plannerRemindersData,
//     ], 200);
// }

public function getManagerPlanner(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
    }

    $company = Fleet::where('company_id', $user->companyname)->first();

    if (!$company) {
        return response()->json(['status' => 0, 'error' => 'Company not found'], 404);
    }

    // Ensure depot_id is an array
    $userDepotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
    $userVehicleGroupIds = is_array($user->vehicle_group_id)
    ? $user->vehicle_group_id
    : json_decode($user->vehicle_group_id, true);

    if (!$userDepotIds) {
        return response()->json(['status' => 0, 'error' => 'No assigned depots found'], 403);
    }

    // Fetch only vehicles in assigned depots
    $query = Fleet::where('fleets.company_id', $user->companyname)
        ->select(
            'fleets.vehicle_id',
            'fleets.id as fleet_id',
            'planner_type',
            'vehicle_details.registrationNumber',
            'vehicle_details.vehicle_nick_name',
            'vehicles.vehicle_type',
            'vehicle_details.depot_id'
        )
        ->join('vehicle_details', 'vehicle_details.id', '=', 'fleets.vehicle_id') // Link to vehicle details
        ->join('vehicles', 'vehicles.id', '=', 'vehicle_details.vehicle_id') // Link to vehicle type
        ->whereIn('vehicle_details.depot_id', $userDepotIds)
        ->when(!empty($userVehicleGroupIds), function ($query) use ($userVehicleGroupIds) {
        $query->whereIn('vehicle_details.group_id', $userVehicleGroupIds);
    });

    if ($request->has('vehicle_id') && !empty($request->vehicle_id)) {
        $query->where('fleets.vehicle_id', $request->vehicle_id);
    }

    if ($request->has('planner_type') && !empty($request->planner_type)) {
        $query->where('planner_type', $request->planner_type);
    }

    $plannerData = $query->get();

    // Date filtering
    $fromDate = $toDate = null;
    if ($request->has('from_date') && $request->has('to_date') && !empty($request->from_date) && !empty($request->to_date)) {
        $fromDate = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay();
        $toDate = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay();

        if ($fromDate->greaterThan($toDate)) {
            return response()->json(['status' => 0, 'error' => 'From Date must be less than or equal to To Date.'], 422);
        }
    }

    // Fetch planner reminders only for vehicles in the assigned depots
    $plannerRemindersData = $plannerData->flatMap(function ($planner) use ($fromDate, $toDate) {
        $query = \App\Models\FleetPlannerReminder::where('fleet_planner_id', $planner->fleet_id);

        if ($fromDate && $toDate) {
            $query->whereBetween('next_reminder_date', [$fromDate->toDateString(), $toDate->toDateString()]);
        }

        return $query->get()->map(function ($reminder) use ($planner) {
            // Assign registration number based on vehicle type
            if ($planner->vehicle_type === 'Trailer') {
                $registrationNumber = $planner->vehicle_nick_name ?? 'No Vehicle Nickname';
            } else {
                $registrationNumber = $planner->registrationNumber ?? 'No Registration';
            }

            return [
                'id' => $reminder->id,
                'vehicle_id' => $planner->vehicle_id,
                'planner_type' => $planner->planner_type,
                'next_reminder_date' => Carbon::parse($reminder->next_reminder_date)->format('d/m/Y'),
                'status' => $reminder->status,
                'registration_number' => $registrationNumber,
                'depot_id' => $planner->depot_id, // Include depot ID in response
            ];
        });
    });

    // Sort: Completed reminders first, then by next_reminder_date ascending
    $sortedReminders = $plannerRemindersData->sortBy(function ($reminder) {
        return [
            $reminder['status'] !== 'Pending' ? 1 : 0, // Completed first (0), others (1)
            Carbon::createFromFormat('d/m/Y', $reminder['next_reminder_date']), // Then sort by date
        ];
    })->values(); // Re-index the collection after sorting

    return response()->json([
        'status' => 1,
        'planner' => $sortedReminders,
    ], 200);
}


public function uploadprofileimage(Request $request)
{
    // Get the logged-in driver user
    $user = Auth::user();

    // Ensure the driver user is logged in
    if (!$user) {
        return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
    }

    // Validate the request to ensure an image is provided
    $request->validate([
        'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:5000', // Adjust size as needed
    ]);

    // Handle the uploaded image
    if ($request->hasFile('avatar')) {
        // Get the original filename without extension
        $filename = pathinfo($request->file('avatar')->getClientOriginalName(), PATHINFO_FILENAME);

        // Get the file extension
        $extension = $request->file('avatar')->getClientOriginalExtension();

        // Build the new filename
        $fileNameToStore = $filename . '_' . time() . '.' . $extension;

        // Define the storage path
        $path = 'uploads/avatar';

        // Store the file with the constructed filename
        $request->file('avatar')->storeAs($path, $fileNameToStore, 'local');

        // Save only the filename to the user's model
        $user->avatar = $fileNameToStore; // Save only the filename
        $user->save();

        // Generate the full URL for the file
        $fileUrl = asset('storage/' . $path . '/' . $fileNameToStore); // Using storage link

        return response()->json([
            'status' => 1,
            'message' => 'Profile Image Uploaded Successfully',
            'profile_url' => $fileUrl, // Full URL of the uploaded image
        ], 200);
    }

    return response()->json(['status' => 0, 'error' => 'No image provided'], 400);
}


}
