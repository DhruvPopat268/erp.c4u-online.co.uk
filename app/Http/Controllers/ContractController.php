<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\CompanyDetails;
use App\Models\Contract;
use App\Models\Contract_attachment;
use App\Models\ContractComment;
use App\Models\ContractNotes;
use App\Models\ContractType;
use App\Models\Project;
use App\Models\User;
use App\Models\UserDefualtView;
use App\Models\Utility;
use App\Models\vehicleDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContractController extends Controller
{
    // public function index(Request $request)
    // {
    //     // Check if the user has the 'manage contract' permission
    //     if (\Auth::user()->can('manage vehicle')) {
    //         $loggedInUser = \Auth::user();

    //         // Retrieve the company name of the user
    //         $companyName = $loggedInUser->companyname;

    //          $selectedCompanyId = $request->input('company_id');

    //         // Retrieve contracts based on the user's role
    //         $contracts = null;
    //         if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
    //             // If the user has the 'company' role, show all data
    //             $contracts = VehicleDetails::with(['types', 'vehicle'])->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
    //                 return $query->where('companyName', $selectedCompanyId);
    //             })
    //             ->get();
    //         } else {
    //             // If the user doesn't have the 'company' role, only show contracts associated with the user's company
    //             $contracts = VehicleDetails::where('companyname', $companyName)
    //                 ->with(['types', 'vehicle'])
    //               ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
    //                     return $query->where('companyName', $selectedCompanyId);
    //                 })
    //             ->get();
    //         }

    //         // Retrieve all companies for the dropdown filter
    //     $companies = CompanyDetails::orderBy('name', 'asc')->get();

    //         // Return the view with the contracts
    //         return view('contract.index', compact('contracts','companies'));
    //     } else {
    //         // If the user doesn't have the permission, redirect back with an error message
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    //     public function index(Request $request)
    //     {
    //         // Check if the user has the 'manage vehicle' permission
    //         if (\Auth::user()->can('manage vehicle')) {
    //             $loggedInUser = \Auth::user();

    //             // Retrieve the company name of the user
    //             $companyName = $loggedInUser->companyname;

    //             // Handle multiple depot IDs (convert stored JSON to array if needed)
    //         $depotIds = is_array($loggedInUser->depot_id) ? $loggedInUser->depot_id : json_decode($loggedInUser->depot_id, true);
    //         if (!is_array($depotIds)) {
    //             $depotIds = [$loggedInUser->depot_id]; // Ensure it remains an array
    //         }

    //             $selectedCompanyId = $request->input('company_id');
    //             $selectedDepotIds = (array) $request->input('depot_id'); // Ensures it is always an array
    //             $selectedFilterColumn = $request->input('filter_column'); // Column to filter by
    //             $selectedFilterValue = $request->input('filter_value'); // Expiry or Expiry Soon
    //                          $selectedVehicleStatus = $request->input('vehicle_status');

    //             // Define the date range for "Expiry Soon" (e.g., within the next 15 days)
    //             $expirySoonDate = now()->addDays(15); // 15 days from now
    //             $today = now(); // Current date

    //             // Retrieve contracts based on the user's role
    //             $contracts = null;
    //             if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
    //                 // If the user has the 'company' role, show all data
    //                 $contracts = vehicleDetails::with(['types','creator', 'vehicle','depot'])
    //                     ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
    //                         return $query->where('companyName', $selectedCompanyId);
    //                     })->when($selectedVehicleStatus, function ($q) use ($selectedVehicleStatus) {
    //     if (strtolower($selectedVehicleStatus) === 'archive') {
    //         return $q->where(function ($subQ) {
    //             $subQ->where('vehicle_status', 'Archive')
    //                  ->orWhere('vehicle_status', 'like', 'Archive%');
    //         });
    //     }
    //     if ($selectedVehicleStatus === 'active_status') {
    //     // Active means any of the following statuses OR blank ('')
    //     return $q->where(function ($subQ) {
    //         $subQ->whereIn('vehicle_status', [
    //             'Owned',
    //             'Rented',
    //             'Leased',
    //             'Contract Hire',
    //             'Depot Transfer',
    //         ])
    //         ->orWhere('vehicle_status', ''); // Include blank value
    //     });
    // }
    //     return $q->where('vehicle_status', $selectedVehicleStatus);
    // })

    // ->when(!empty($selectedDepotIds), function ($query) use ($selectedDepotIds) {
    //                         return $query->whereIn('depot_id', $selectedDepotIds);
    //                     })
    //                     ->whereHas('types', function ($query) {
    //                         $query->where('company_status', 'Active'); // Only show data with 'Active' company status
    //                     })
    //                     ->when($selectedFilterColumn && $selectedFilterValue, function ($query) use ($selectedFilterColumn, $selectedFilterValue, $expirySoonDate, $today) {
    //                         if ($selectedFilterColumn && $selectedFilterValue) {
    //                             // Filtering logic for 'taxDueDate' and 'PMI_due'
    //                             if ($selectedFilterColumn == 'taxDueDate') {
    //                                 // Check if the selected filter value is 'expiry' or 'expiry_soon'
    //                                 if ($selectedFilterValue == 'expiry') {
    //                                     return $query->whereDate(\DB::raw('STR_TO_DATE(taxDueDate, "%d %M %Y")'), '<', $today); // Expired
    //                                 }
    //                                 if ($selectedFilterValue == 'expiry_soon') {
    //                                     return $query->whereDate(\DB::raw('STR_TO_DATE(taxDueDate, "%d %M %Y")'), '>=', $today)
    //                                               ->whereDate(\DB::raw('STR_TO_DATE(taxDueDate, "%d %M %Y")'), '<=', $expirySoonDate); // Expiry soon (within 15 days)
    //                                 }
    //                             }
    //                             // PMI_due filter logic
    //                             if ($selectedFilterColumn == 'PMI_due') {
    //                                 // Convert 'PMI_due' from 'DD-MM-YYYY' format to a valid date for comparison
    //                                 if ($selectedFilterValue == 'expiry') {
    //                                     return $query->whereDate(\DB::raw('STR_TO_DATE(PMI_due, "%d-%m-%Y")'), '<', $today); // Expired
    //                                 }
    //                                 if ($selectedFilterValue == 'expiry_soon') {
    //                                     return $query->whereDate(\DB::raw('STR_TO_DATE(PMI_due, "%d-%m-%Y")'), '>=', $today)
    //                                               ->whereDate(\DB::raw('STR_TO_DATE(PMI_due, "%d-%m-%Y")'), '<=', $expirySoonDate); // Expiry soon (within 15 days)
    //                                 }
    //                             }
    //                             // Filtering logic for new date fields (already in 'YYYY-MM-DD' format)
    //                             if (in_array($selectedFilterColumn, ['tacho_calibration', 'dvs_pss_permit_expiry', 'insurance', 'brake_test_due', 'annual_test_expiry_date'])) {
    //                                 // For fields like 'tacho_calibration', 'insurance', etc., they are stored in 'YYYY-MM-DD' format
    //                                 if ($selectedFilterColumn == 'annual_test_expiry_date') {
    //                                     if ($selectedFilterValue == 'expiry') {
    //                                         return $query->whereHas('vehicle', function ($q) use ($today) {
    //                                             $q->whereDate('annual_test_expiry_date', '<', $today); // Expired
    //                                         });
    //                                     }
    //                                     if ($selectedFilterValue == 'expiry_soon') {
    //                                         return $query->whereHas('vehicle', function ($q) use ($expirySoonDate, $today) {
    //                                             $q->whereDate('annual_test_expiry_date', '>=', $today)
    //                                               ->whereDate('annual_test_expiry_date', '<=', $expirySoonDate); // Expiry soon (within 15 days)
    //                                         });
    //                                     }
    //                                 }
    //                                 // Existing filter logic for 'tacho_calibration', 'insurance', etc.
    //                                 if ($selectedFilterValue == 'expiry') {
    //                                     return $query->whereDate($selectedFilterColumn, '<', $today); // Expired
    //                                 }
    //                                 if ($selectedFilterValue == 'expiry_soon') {
    //                                     return $query->whereDate($selectedFilterColumn, '>=', $today)
    //                                               ->whereDate($selectedFilterColumn, '<=', $expirySoonDate); // Expiry soon (within 15 days)
    //                                 }
    //                             }
    //                         }
    //                     })
    //                     ->get();
    //             } else {
    //                 // If the user doesn't have the 'company' role, only show contracts associated with the user's company
    //                   // If the user doesn't have the 'company' role, only show contracts associated with the user's company
    //     $contracts = vehicleDetails::with(['types', 'creator', 'vehicle', 'depot'])
    //         ->where('companyname', $companyName) // Logged-in user's company
    //         ->whereIn('depot_id', $depotIds)     // Logged-in user's depots
    //         ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
    //             return $query->where('companyName', $selectedCompanyId);
    //         })
    //         ->when($selectedVehicleStatus, function ($q) use ($selectedVehicleStatus, $companyName, $depotIds) {
    //             // ðŸ”¹ Always limit to logged-in company + depot in the vehicle_status filter itself
    //             $q->where('companyname', $companyName)
    //               ->whereIn('depot_id', $depotIds);

    //             if (strtolower($selectedVehicleStatus) === 'archive') {
    //                 // Match "Archive" and "Archive anything"
    //                 return $q->where(function ($subQ) {
    //                     $subQ->where('vehicle_status', 'Archive')
    //                          ->orWhere('vehicle_status', 'like', 'Archive%');
    //                 });
    //             }

    //             if ($selectedVehicleStatus === 'active_status') {
    //                 // Active means any of the following statuses OR blank ('')
    //                 return $q->where(function ($subQ) {
    //                     $subQ->whereIn('vehicle_status', [
    //                         'Owned',
    //                         'Rented',
    //                         'Leased',
    //                         'Contract Hire',
    //                         'Depot Transfer',
    //                     ])
    //                     ->orWhere('vehicle_status', ''); // Include blank value
    //                 });
    //             }

    //             // Default match
    //             return $q->where('vehicle_status', $selectedVehicleStatus);
    //         })
    //         ->whereHas('types', function ($query) {
    //             $query->where('company_status', 'Active'); // Only show data with 'Active' company status
    //         })
    //                     ->when($selectedFilterColumn && $selectedFilterValue, function ($query) use ($selectedFilterColumn, $selectedFilterValue, $expirySoonDate, $today) {
    //                         if ($selectedFilterColumn && $selectedFilterValue) {
    //                             // Filtering logic for 'taxDueDate' and 'PMI_due'
    //                             if ($selectedFilterColumn == 'taxDueDate') {
    //                                 // Check if the selected filter value is 'expiry' or 'expiry_soon'
    //                                 if ($selectedFilterValue == 'expiry') {
    //                                     return $query->whereDate(\DB::raw('STR_TO_DATE(taxDueDate, "%d %M %Y")'), '<', $today); // Expired
    //                                 }
    //                                 if ($selectedFilterValue == 'expiry_soon') {
    //                                     return $query->whereDate(\DB::raw('STR_TO_DATE(taxDueDate, "%d %M %Y")'), '>=', $today)
    //                                               ->whereDate(\DB::raw('STR_TO_DATE(taxDueDate, "%d %M %Y")'), '<=', $expirySoonDate); // Expiry soon (within 15 days)
    //                                 }
    //                             }
    //                             // PMI_due filter logic
    //                             if ($selectedFilterColumn == 'PMI_due') {
    //                                 // Convert 'PMI_due' from 'DD-MM-YYYY' format to a valid date for comparison
    //                                 if ($selectedFilterValue == 'expiry') {
    //                                     return $query->whereDate(\DB::raw('STR_TO_DATE(PMI_due, "%d-%m-%Y")'), '<', $today); // Expired
    //                                 }
    //                                 if ($selectedFilterValue == 'expiry_soon') {
    //                                     return $query->whereDate(\DB::raw('STR_TO_DATE(PMI_due, "%d-%m-%Y")'), '>=', $today)
    //                                               ->whereDate(\DB::raw('STR_TO_DATE(PMI_due, "%d-%m-%Y")'), '<=', $expirySoonDate); // Expiry soon (within 15 days)
    //                                 }
    //                             }
    //                             // Filtering logic for new date fields (already in 'YYYY-MM-DD' format)
    //                             if (in_array($selectedFilterColumn, ['tacho_calibration', 'dvs_pss_permit_expiry', 'insurance', 'brake_test_due', 'annual_test_expiry_date'])) {
    //                                 // For fields like 'tacho_calibration', 'insurance', etc., they are stored in 'YYYY-MM-DD' format
    //                                 if ($selectedFilterColumn == 'annual_test_expiry_date') {
    //                                     if ($selectedFilterValue == 'expiry') {
    //                                         return $query->whereHas('vehicle', function ($q) use ($today) {
    //                                             $q->whereDate('annual_test_expiry_date', '<', $today); // Expired
    //                                         });
    //                                     }
    //                                     if ($selectedFilterValue == 'expiry_soon') {
    //                                         return $query->whereHas('vehicle', function ($q) use ($expirySoonDate, $today) {
    //                                             $q->whereDate('annual_test_expiry_date', '>=', $today)
    //                                               ->whereDate('annual_test_expiry_date', '<=', $expirySoonDate); // Expiry soon (within 15 days)
    //                                         });
    //                                     }
    //                                 }
    //                                 // Existing filter logic for 'tacho_calibration', 'insurance', etc.
    //                                 if ($selectedFilterValue == 'expiry') {
    //                                     return $query->whereDate($selectedFilterColumn, '<', $today); // Expired
    //                                 }
    //                                 if ($selectedFilterValue == 'expiry_soon') {
    //                                     return $query->whereDate($selectedFilterColumn, '>=', $today)
    //                                               ->whereDate($selectedFilterColumn, '<=', $expirySoonDate); // Expiry soon (within 15 days)
    //                                 }
    //                             }
    //                         }
    //                     })
    //                     ->get();
    //             }

    //           // Retrieve companies for dropdown (All companies for company/PTC manager, otherwise user's company)
    //         $companiesQuery = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active');
    //         if (!$loggedInUser->hasRole('company') && !$loggedInUser->hasRole('PTC manager')) {
    //             $companiesQuery->where('name', $companyName);
    //         }
    //         $companies = $companiesQuery->get();

    //         // Retrieve depots for dropdown (All depots for company/PTC manager, otherwise user's assigned depots)
    //         $depotsQuery = \App\Models\Depot::orderBy('name', 'asc');
    //         if (!$loggedInUser->hasRole('company') && !$loggedInUser->hasRole('PTC manager')) {
    //             $depotsQuery->whereIn('id', $depotIds);
    //         }
    //         $depots = $depotsQuery->get();

    //             // Return the view with the contracts
    //             return view('contract.index', compact('contracts','companies','depots'));
    //         } else {
    //             // If the user doesn't have the permission, redirect back with an error message
    //             return redirect()->back()->with('error', __('Permission denied.'));
    //         }
    //     }

    public function index(Request $request)
    {
        // Check if the user has the 'manage vehicle' permission
        if (\Auth::user()->can('manage vehicle')) {
            $loggedInUser = \Auth::user();

            // Retrieve the company name of the user
            $companyName = $loggedInUser->companyname;

            // Handle multiple depot IDs (convert stored JSON to array if needed)
            $depotIds = is_array($loggedInUser->depot_id) ? $loggedInUser->depot_id : json_decode($loggedInUser->depot_id, true);
            if (! is_array($depotIds)) {
                $depotIds = [$loggedInUser->depot_id]; // Ensure it remains an array
            }

            $vehicleGroupIds = is_array($loggedInUser->vehicle_group_id)
                ? $loggedInUser->vehicle_group_id
                  : json_decode($loggedInUser->vehicle_group_id, true);

            if (! is_array($vehicleGroupIds)) {
                $vehicleGroupIds = [$loggedInUser->vehicle_group_id];
            }

            $selectedCompanyId = $request->input('company_id');
            $selectedDepotIds = (array) $request->input('depot_id'); // Ensures it is always an array
            $selectedFilterColumn = $request->input('filter_column'); // Column to filter by
            $selectedFilterValue = $request->input('filter_value'); // Expiry or Expiry Soon
            $selectedVehicleStatus = $request->input('vehicle_status');
            $selectedGroupId = $request->input('group_id');
            // Define the date range for "Expiry Soon" (e.g., within the next 15 days)
            $expirySoonDate = now()->addDays(15); // 15 days from now
            $today = now(); // Current date

            // Retrieve contracts based on the user's role
            $contracts = null;
            if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
                // If the user has the 'company' role, show all data
                $contracts = vehicleDetails::with(['types', 'creator', 'vehicle', 'depot', 'group'])->when(! $selectedVehicleStatus, function ($q) {
                    $q->where(function ($subQ) {
                        $subQ->whereNull('vehicle_status')
                            ->orWhere('vehicle_status', '')
                            ->orWhere('vehicle_status', 'not like', 'Archive%');
                    });
                })

                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->when($selectedVehicleStatus, function ($q) use ($selectedVehicleStatus) {
                        if (strtolower($selectedVehicleStatus) === 'archive') {
                            return $q->where(function ($subQ) {
                                $subQ->where('vehicle_status', 'Archive')
                                    ->orWhere('vehicle_status', 'like', 'Archive%');
                            });
                        }
                        if ($selectedVehicleStatus === 'active_status') {
                            // Active means any of the following statuses OR blank ('')
                            return $q->where(function ($subQ) {
                                $subQ->whereIn('vehicle_status', [
                                    'Owned',
                                    'Rented',
                                    'Leased',
                                    'Contract Hire',
                                    'Depot Transfer',
                                ])
                                    ->orWhere('vehicle_status', ''); // Include blank value
                            });
                        }

                        return $q->where('vehicle_status', $selectedVehicleStatus);
                    })
                    ->when(! empty($selectedDepotIds), function ($query) use ($selectedDepotIds) {
                        return $query->whereIn('depot_id', $selectedDepotIds);
                    })
                    ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                return $query->where('group_id',$selectedGroupId);
            })
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active'); // Only show data with 'Active' company status
                    })

                    ->when($selectedFilterColumn && $selectedFilterValue, function ($query) use ($selectedFilterColumn, $selectedFilterValue) {
                        $statusMap = [
                            'tacho_calibration' => 'tacho_status',
                            'dvs_pss_permit_expiry' => 'dvs_pss_status',
                            'insurance' => 'insurance_status',
                            'PMI_due' => 'PMI_status',
                            'brake_test_due' => 'brake_test_status',
                            'taxDueDate' => 'taxDueDate_status',
                            'annual_test_expiry_date' => 'annual_test_status',
                        ];

                        $statusValueMap = [
                            'expiry' => 'EXPIRED',
                            'expiry_soon' => 'EXPIRING SOON',

                        ];

                        if (array_key_exists($selectedFilterColumn, $statusMap) && array_key_exists($selectedFilterValue, $statusValueMap)) {
                            $statusColumn = $statusMap[$selectedFilterColumn];
                            $statusValue = $statusValueMap[$selectedFilterValue];

                            // handle special case where column exists in another table
                            if ($selectedFilterColumn === 'annual_test_expiry_date') {
                                return $query->whereHas('vehicle', function ($q) use ($statusValue) {
                                    $q->whereRaw('LOWER(annual_test_status) = ?', [strtolower($statusValue)]);
                                });
                            }

                            return $query->whereRaw("LOWER($statusColumn) = ?", [strtolower($statusValue)]);
                        }

                        return $query;
                    })
                    ->get();
            } else {
                // If the user doesn't have the 'company' role, only show contracts associated with the user's company
                // If the user doesn't have the 'company' role, only show contracts associated with the user's company
                $contracts = vehicleDetails::with(['types', 'creator', 'vehicle', 'depot'])
                    ->where('companyname', $companyName) // Logged-in user's company
                    ->whereIn('depot_id', $depotIds)     // Logged-in user's depots
                    ->whereIn('group_id', $vehicleGroupIds)
                    ->when(! $selectedVehicleStatus, function ($q) {
                        $q->where(function ($subQ) {
                            $subQ->whereNull('vehicle_status')
                                ->orWhere('vehicle_status', '')
                                ->orWhere('vehicle_status', 'not like', 'Archive%');
                        });
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })
                     ->when(!empty($selectedDepotIds), function ($query) use ($selectedDepotIds) {
                return $query->whereIn('depot_id',$selectedDepotIds);
            })

            ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                return $query->where('group_id',$selectedGroupId);
            })
                    ->when($selectedVehicleStatus, function ($q) use ($selectedVehicleStatus, $companyName, $depotIds) {
                        // ðŸ”¹ Always limit to logged-in company + depot in the vehicle_status filter itself
                        $q->where('companyname', $companyName)
                            ->whereIn('depot_id', $depotIds);

                        if (strtolower($selectedVehicleStatus) === 'archive') {
                            // Match "Archive" and "Archive anything"
                            return $q->where(function ($subQ) {
                                $subQ->where('vehicle_status', 'Archive')
                                    ->orWhere('vehicle_status', 'like', 'Archive%');
                            });
                        }

                        if ($selectedVehicleStatus === 'active_status') {
                            // Active means any of the following statuses OR blank ('')
                            return $q->where(function ($subQ) {
                                $subQ->whereIn('vehicle_status', [
                                    'Owned',
                                    'Rented',
                                    'Leased',
                                    'Contract Hire',
                                    'Depot Transfer',
                                ])
                                    ->orWhere('vehicle_status', ''); // Include blank value
                            });
                        }

                        // Default match
                        return $q->where('vehicle_status', $selectedVehicleStatus);
                    })
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active'); // Only show data with 'Active' company status
                    })
                    ->when($selectedFilterColumn && $selectedFilterValue, function ($query) use ($selectedFilterColumn, $selectedFilterValue) {
                        $statusMap = [
                            'tacho_calibration' => 'tacho_status',
                            'dvs_pss_permit_expiry' => 'dvs_pss_status',
                            'insurance' => 'insurance_status',
                            'PMI_due' => 'PMI_status',
                            'brake_test_due' => 'brake_test_status',
                            'taxDueDate' => 'taxDueDate_status',
                            'annual_test_expiry_date' => 'annual_test_status',
                        ];

                        $statusValueMap = [
                            'expiry' => 'EXPIRED',
                            'expiry_soon' => 'EXPIRING SOON',
                        ];

                        if (array_key_exists($selectedFilterColumn, $statusMap) && array_key_exists($selectedFilterValue, $statusValueMap)) {
                            $statusColumn = $statusMap[$selectedFilterColumn];
                            $statusValue = $statusValueMap[$selectedFilterValue];

                            // handle special case where column exists in another table
                            if ($selectedFilterColumn === 'annual_test_expiry_date') {
                                return $query->whereHas('vehicle', function ($q) use ($statusValue) {
                                    $q->whereRaw('LOWER(annual_test_status) = ?', [strtolower($statusValue)]);
                                });
                            }

                            return $query->whereRaw("LOWER($statusColumn) = ?", [strtolower($statusValue)]);
                        }

                        return $query;
                    })
                    ->get();
            }

            // Retrieve companies for dropdown (All companies for company/PTC manager, otherwise user's company)
            $companiesQuery = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active');
            if (! $loggedInUser->hasRole('company') && ! $loggedInUser->hasRole('PTC manager')) {
                $companiesQuery->where('name', $companyName);
            }
            $companies = $companiesQuery->get();

            // Retrieve depots for dropdown (All depots for company/PTC manager, otherwise user's assigned depots)
            $depotsQuery = \App\Models\Depot::orderBy('name', 'asc');
            if (! $loggedInUser->hasRole('company') && ! $loggedInUser->hasRole('PTC manager')) {
                $depotsQuery->whereIn('id', $depotIds);
            }
            $depots = $depotsQuery->get();

             $groupsQuery = \App\Models\VehicleGroup::orderBy('name', 'asc');

            if (! $loggedInUser->hasRole('company') && ! $loggedInUser->hasRole('PTC manager')) {
                $groupsQuery->whereIn('id', $vehicleGroupIds);
            }

            $groups = $groupsQuery->get();

            // Return the view with the contracts
            return view('contract.index', compact('contracts', 'companies', 'depots','groups'));
        } else {
            // If the user doesn't have the permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // public function create()
    // {
    //     $user = \Auth::user();

    //     // Check if the user is a super admin
    //     if ($user->hasRole('company')) {
    //         // Fetch all company names
    //         $contractTypes = CompanyDetails::pluck('name', 'id');
    //     } else {
    //         // Fetch the company name for the logged-in user
    //         $contractTypes = CompanyDetails::where('created_by', '=', $user->creatorId())
    //             ->where('id', '=', $user->companyname)
    //             ->pluck('name', 'id');
    //     }

    //     return view('contract.create', compact('contractTypes'));

    // }

    public function create()
    {
        $user = \Auth::user();
        if ($user->can('manage vehicle')) {

            // Check if the user is a super admin
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                // Fetch all company names
                $contractTypes = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->pluck('name', 'id');
            } else {
                // Fetch the company name for the logged-in user
                $contractTypes = CompanyDetails::where('created_by', '=', $user->creatorId())
                    ->where('id', '=', $user->companyname)->where('company_status', 'Active')
                    ->pluck('name', 'id');

                // Check if the user creating the new user is directly associated with a company
                // If not, remove the company name from the list
                if ($user->companyname) {
                    $contractTypes = CompanyDetails::where('id', '=', $user->companyname)->where('company_status', 'Active')
                        ->pluck('name', 'id');
                } else {
                    $contractTypes = [];
                }
            }

            return view('contract.create', compact('contractTypes'));
        } else {
            // If user doesn't have permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function trailercreate()
    {
        $user = \Auth::user();
        if ($user->can('manage vehicle')) {

            // Check if the user is a super admin
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                // Fetch all company names
                $contractTypes = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->pluck('name', 'id');
            } else {
                // Fetch the company name for the logged-in user
                $contractTypes = CompanyDetails::where('created_by', '=', $user->creatorId())
                    ->where('id', '=', $user->companyname)->where('company_status', 'Active')
                    ->pluck('name', 'id');

                // Check if the user creating the new user is directly associated with a company
                // If not, remove the company name from the list
                if ($user->companyname) {
                    $contractTypes = CompanyDetails::where('id', '=', $user->companyname)->where('company_status', 'Active')
                        ->pluck('name', 'id');
                } else {
                    $contractTypes = [];
                }
            }

            return view('contract.trailer.create', compact('contractTypes'));
        } else {
            // If user doesn't have permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getVehicleGroupsByCompany($companyId)
    {
        $user = \Auth::user();

        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {

            // company & ptc manager → all groups
            $groups = \App\Models\VehicleGroup::where('company_id', $companyId)
                ->pluck('name', 'id');

        } else {

            // other users → only assigned groups
            $vehicleGroupIds = is_array($user->vehicle_group_id)
                ? $user->vehicle_group_id
                : json_decode($user->vehicle_group_id, true);

            $groups = \App\Models\VehicleGroup::where('company_id', $companyId)
                ->whereIn('id', $vehicleGroupIds ?? [])
                ->pluck('name', 'id');
        }

        return response()->json($groups);
    }

    // public function store(Request $request)
    // {
    //     if (\Auth::user()->can('create vehicle')) {
    //         $apiKey1 = 'CS8NuPQICs60CPb1Xb4cS6MG9KGIVuQ47ONeJXu6'; // API key for first API
    //         $apiKey2 = '7gqmPTWnf02zZ5oidVhgRaCVLH2EAqUA1ytOdFSt'; // API key for second API
    //         $apiUrl1 = 'https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles';
    //         $apiUrl2 = 'https://beta.check-mot.service.gov.uk/trade/vehicles/annual-tests'; // Change this to the URL of your second API

    //         $requestData1 = [
    //             'registrationNumber' => $request->input('registrationNumber'),
    //             'tacho_calibration' => $request->input('tacho_calibration'),
    //         ];

    //         $api1Success = false;
    //         $api2Success = false;

    //         try {
    //             $response = Http::withHeaders([
    //                 'x-api-key' => $apiKey1,
    //             ])->post($apiUrl1, $requestData1);

    //             $responseData = $response->json();
    //             $registration = ['registration_number' => $request->input('registration_number')];

    //             // Handle successful response from API 1
    //             $api1Success = true;

    //             // Parse relevant data from the response for API 1
    //             $vehicleData1 = [
    //                 'created_by' => \Auth::user()->id,
    //                 'companyName' => $request->input('companyName'),
    //                 'tacho_calibration' => $request->input('tacho_calibration'),
    //                 'dvs_pss_permit_expiry' => $request->input('dvs_pss_permit_expiry'),
    //                 'insurance_type' => $request->input('insurance_type') === 'other' ? $request->input('insurance_other') : $request->input('insurance_type'),
    //                 'insurance' => $request->input('insurance'),
    //                 'PMI_due' => $request->input('PMI_due'),
    //                 'PMI_intervals' => $request->input('PMI_intervals'),
    //                 'date_of_inspection' => $request->input('date_of_inspection'),
    //                 'odometer_reading' => $request->input('odometer_reading'),
    //                 'brake_test_due' => $request->input('brake_test_due'),
    //                 'vehicle_status' => $request->input('combined_status'),
    //                                     // 'group_id' => $request->input('group_id'),
    //                 'registrationNumber' => $responseData['registrationNumber'],
    //                 'taxStatus' => $responseData['taxStatus'] ?? null,
    //                 'taxDueDate' => isset($responseData['taxDueDate']) ? date('d F Y', strtotime($responseData['taxDueDate'])) : null,
    //                 'motStatus' => $responseData['motStatus'] ?? null,
    //                 'make' => $responseData['make'] ?? null,
    //                 'yearOfManufacture' => $responseData['yearOfManufacture'] ?? null,
    //                 'engineCapacity' => $responseData['engineCapacity'] ?? null,
    //                 'co2Emissions' => $responseData['co2Emissions'] ?? null,
    //                 'fuelType' => $responseData['fuelType'] ?? null,
    //                 'markedForExport' => $responseData['markedForExport'] ?? null,
    //                 'colour' => $responseData['colour'] ?? null,
    //                 'typeApproval' => $responseData['typeApproval'] ?? null,
    //                 'revenueWeight' => $responseData['revenueWeight'] ?? null,
    //                 'euroStatus' => $responseData['euroStatus'] ?? null,
    //                 'dateOfLastV5CIssued' => $responseData['dateOfLastV5CIssued'] ?? null,
    //                 'motExpiryDate' => $responseData['motExpiryDate'] ?? null,
    //                 'wheelplan' => $responseData['wheelplan'] ?? null,
    //                 'monthOfFirstRegistration' => $responseData['monthOfFirstRegistration'] ?? null,
    //                 'created_by' => \Auth::user()->id
    //             ];

    //             // Update or create data in vehicleDetails model for API 1
    //             $vehicleDetailsModel1 = VehicleDetails::updateOrCreate(
    //                 ['registrationNumber' => $vehicleData1['registrationNumber']],
    //                 $vehicleData1 // Data to update or insert
    //             );

    //             // Handle the second API call only if the first one was successful
    //             if ($api1Success) {
    //                 // Code for API 2 call...
    //                 $registrations = $request->input('registrationNumber');

    //                 $response2 = Http::withHeaders([
    //                     'x-api-key' => $apiKey2,
    //                 ])->get("$apiUrl2?registrations=$registrations"); // Modify this to suit your second API call

    //                 $responseData2 = $response2->json();

    //                 if (! empty($responseData2)) {
    //                     // Extracting data from the first item in the array, assuming it contains relevant data
    //                     $responseVehicle = $responseData2[0];

    //                     // Parse relevant data from the response for API 2
    //                     $vehicleData2 = [
    //                         'created_by' => \Auth::user()->id,
    //                         'companyName' => $request->input('companyName'),
    //                         'registrations' => $responseVehicle['registration'] ?? null,
    //                         'make' => $responseVehicle['make'] ?? null,
    //                         'model' => $responseVehicle['model'] ?? null,
    //                         'vehicle_type' => $responseVehicle['vehicleType'] ?? null,
    //                         'registration_date' => $responseVehicle['registrationDate'] ?? null,
    //                         'annual_test_expiry_date' => $responseVehicle['annualTestExpiryDate'] ?? null,
    //                     ];

    //                     // Update or create data in vehicleDetails model for API 2
    //                     $vehicleDetailsModel2 = \App\Models\Vehicles::updateOrCreate(
    //                         ['registrations' => $vehicleData2['registrations']],
    //                         $vehicleData2 // Data to update or insert
    //                     );

    //                     $vehicleDetailsModel1->vehicle_id = $vehicleDetailsModel2->id;
    //                     $vehicleDetailsModel1->save();

    //                     // Save annual tests data
    //                     if (isset($responseVehicle['annualTests']) && is_array($responseVehicle['annualTests'])) {
    //                         foreach ($responseVehicle['annualTests'] as $test) {
    //                             $annualTest = [
    //                                 'companyName' => $request->input('companyName') ?? null,
    //                                 'vehicle_id' => $vehicleDetailsModel2->id,
    //                                 'test_date' => $test['testDate'],
    //                                 'test_type' => $test['testType'],
    //                                 'test_result' => $test['testResult'],
    //                                 'test_certificate_number' => $test['testCertificateNumber'],
    //                                 'expiry_date' => isset($test['expiryDate']) ? $test['expiryDate'] : null,
    //                                 'number_of_defects_test' => $test['numberOfDefectsAtTest'],
    //                                 'number_of_advisory_defects_test' => $test['numberOfAdvisoryDefectsAtTest'],
    //                             ];

    //                             // Save annual test
    //                             $annualTestModel = \App\Models\VehiclesAnnualTest::updateOrCreate($annualTest);

    //                             // Save defects data
    //                             if (isset($test['defects']) && is_array($test['defects'])) {
    //                                 foreach ($test['defects'] as $defect) {
    //                                     $defectData = [
    //                                         'companyName' => $request->input('companyName') ?? null,
    //                                         'vehicle_id' => $vehicleDetailsModel2->id,
    //                                         'annual_test_id' => $annualTestModel->id,
    //                                         'failure_item_no' => $defect['failureItemNo'],
    //                                         'failure_reason' => $defect['failureReason'],
    //                                         'severity_code' => $defect['severityCode'],
    //                                         'severity_description' => $defect['severityDescription'],
    //                                     ];
    //                                     \App\Models\VehiclesAnnualTestDefect::updateOrCreate($defectData);
    //                                 }
    //                             }
    //                         }
    //                     }

    //                     // Set the flag for successful API 2 call
    //                     $api2Success = true;
    //                 }
    //             }

    //             // Check if both API calls were successful and redirect with appropriate message
    //             if ($api1Success && $api2Success) {
    //                 return redirect()->route('contract.index')->with('success', __('Vehicle Data successfully created!'));
    //             } elseif ($api1Success) {
    //                 // Redirect to a view with a modal for vehicle addition choice
    //                 return redirect()->route('contract.index')->with('showDriverModal', true);
    //             } elseif ($api2Success) {
    //                 return redirect()->route('contract.index')->with('success', __('Vehicle successfully created!'));
    //             } else {
    //                 return redirect()->route('contract.index')->with('error', __('Failed to fetch vehicle details from both APIs'));
    //             }
    //         } catch (\Exception $e) {
    //             // Log the error or handle it appropriately
    //             return redirect()->route('contract.index')->with('error', __('Failed to fetch vehicle details from APIs').': '.$e->getMessage());
    //         }
    //     } else {
    //         // If the user doesn't have permission, redirect with an error message
    //         return redirect()->route('contract.index')->with('error', __('Permission denied.'));
    //     }
    // }
    
     public function getVehicleGroupByCompanyFilter(Request $request)
    {
        $companyId = $request->input('company_id');
        $user = \Auth::user();

        if ($companyId) {

            if ($user->type === 'company' || $user->type === 'PTC manager') {

                // Show all groups of selected company
                $groups = \App\Models\VehicleGroup::where('company_id', $companyId)
                    ->orderBy('name', 'asc')
                    ->get(['id', 'name']);

            } else {

                // Show only assigned groups
                $groupIds = $user->vehicle_group_id;

                // Convert to array safely
                if (is_string($groupIds)) {
                    $groupIds = json_decode($groupIds, true) ?? explode(',', $groupIds);
                } elseif (is_int($groupIds)) {
                    $groupIds = [$groupIds];
                } elseif (! is_array($groupIds)) {
                    $groupIds = [];
                }

                $groups = \App\Models\VehicleGroup::where('company_id', $companyId)
                    ->whereIn('id', $groupIds)
                    ->orderBy('name', 'asc')
                    ->get(['id', 'name']);
            }

        } else {

            $groups = [];
        }

        return response()->json($groups);
    }

    private function getDVSAAccessToken()
    {
        $response = Http::asForm()->post('https://login.microsoftonline.com/a455b827-244f-4c97-b5b4-ce5d13b4d00c/oauth2/v2.0/token', [
            'grant_type' => 'client_credentials',
            'client_id' => 'e3bfee1c-d31c-428b-bdfe-bb9be902d652',
            'client_secret' => 'REMOVED_SECRET',
            'scope' => 'https://tapi.dvsa.gov.uk/.default',
        ]);

        return $response['access_token'] ?? null;
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create vehicle')) {

            $existingVehicle = VehicleDetails::where('companyName', $request->input('companyName'))
                ->where('registrationNumber', $request->input('registrationNumber'))
                ->first();

            if ($existingVehicle) {
                return redirect()->route('contract.index')->with('error', __('This company and registration number already exist.'));
            }

            $apiKey1 = 'CS8NuPQICs60CPb1Xb4cS6MG9KGIVuQ47ONeJXu6'; // API key for first API
            $apiKey2 = 'dmVdeybS8M99rT3PrZ6iw8VZvP5gR6la3wSy2Mld'; // API key for second API
            $apiUrl1 = 'https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles';
            // $apiUrl2 = 'https://beta.check-mot.service.gov.uk/trade/vehicles/annual-tests'; // Change this to the URL of your second API

            $requestData1 = [
                'registrationNumber' => $request->input('registrationNumber'),
                'tacho_calibration' => $request->input('tacho_calibration'),
            ];

            $api1Success = false;
            $api2Success = false;

            try {
                $response = Http::withHeaders([
                    'x-api-key' => $apiKey1,
                ])->post($apiUrl1, $requestData1);

                $responseData = $response->json();
                $registration = ['registration_number' => $request->input('registration_number')];

                // Handle successful response from API 1
                $api1Success = true;

                // Parse relevant data from the response for API 1
                $vehicleData1 = [
                    'created_by' => \Auth::user()->id,
                    'companyName' => $request->input('companyName'),
                    'vehicle_nick_name' => $request->input('vehicle_nick_name') ?? $request->input('registrationNumber'),
                    'tacho_calibration' => $request->input('tacho_calibration'),
                    'dvs_pss_permit_expiry' => $request->input('dvs_pss_permit_expiry'),
                    'insurance_type' => json_encode($request->input('insurance_type', [])),

                    'insurance' => $request->input('insurance'),
                    'PMI_due' => $request->input('PMI_due'),
                    'PMI_intervals' => $request->input('PMI_intervals'),
                    // 'fridge_service' => $request->input('fridge_service'),
                    // 'fridge_service_interval' => $request->input('fridge_service_interval'),
                    // 'fridge_calibration' => $request->input('fridge_calibration'),
                    // 'fridge_calibration_interval' => $request->input('fridge_calibration_interval'),
                    // 'tail_lift' => $request->input('tail_lift'),
                    // 'tail_lift_interval' => $request->input('tail_lift_interval'),
                    // 'loler' => $request->input('loler'),
                    // 'loler_interval' => $request->input('loler_interval'),
                    'date_of_inspection' => $request->input('date_of_inspection'),
                    'odometer_reading' => $request->input('odometer_reading'),
                    'brake_test_due' => $request->input('PMI_due') ? \Carbon\Carbon::parse($request->input('PMI_due'))->format('Y-m-d') : null,  // Formatting brake_test_due to Y-m-d
                    'vehicle_status' => $request->input('vehicle_status'),
                    'group_id' => $request->input('group_id'),
                    'depot_id' => $request->input('depot_id'),
                    'registrationNumber' => $responseData['registrationNumber'],
                    'taxStatus' => $responseData['taxStatus'] ?? null,
                    'taxDueDate' => isset($responseData['taxDueDate']) ? date('d F Y', strtotime($responseData['taxDueDate'])) : null,
                    'motStatus' => $responseData['motStatus'] ?? null,
                    'make' => $responseData['make'] ?? null,
                    'yearOfManufacture' => $responseData['yearOfManufacture'] ?? null,
                    'engineCapacity' => $responseData['engineCapacity'] ?? null,
                    'co2Emissions' => $responseData['co2Emissions'] ?? null,
                    'fuelType' => $responseData['fuelType'] ?? null,
                    'markedForExport' => $responseData['markedForExport'] ?? null,
                    'colour' => $responseData['colour'] ?? null,
                    'typeApproval' => $responseData['typeApproval'] ?? null,
                    'revenueWeight' => $responseData['revenueWeight'] ?? null,
                    'euroStatus' => $responseData['euroStatus'] ?? null,
                    'dateOfLastV5CIssued' => $responseData['dateOfLastV5CIssued'] ?? null,
                    'motExpiryDate' => $responseData['motExpiryDate'] ?? null,
                    'wheelplan' => $responseData['wheelplan'] ?? null,
                    'monthOfFirstRegistration' => $responseData['monthOfFirstRegistration'] ?? null,
                    'created_by' => \Auth::user()->id,
                ];

                // Update or create data in vehicleDetails model for API 1
                $vehicleDetailsModel1 = VehicleDetails::updateOrCreate(
                    ['registrationNumber' => $vehicleData1['registrationNumber']],
                    $vehicleData1 // Data to update or insert
                );

                if ($request->filled('PMI_due') && $request->filled('date_of_inspection')) {
                    $fleetPMIData = [
                        'start_date' => $request->input('PMI_due'),
                        'end_date' => \Carbon\Carbon::parse($request->input('PMI_due'))->addYear()->toDateString(),
                        'company_id' => $request->input('companyName'),
                        'planner_type' => 'PMI Due',
                        'vehicle_id' => $vehicleDetailsModel1->id,
                        'every' => $request->input('PMI_intervals'),
                        'interval' => 'Week',
                        'created_by' => \Auth::user()->id,
                    ];
                    $fleetPMI = \App\Models\Fleet::create($fleetPMIData);
                    $this->generateReminders($fleetPMI);
                }

                if ($request->filled('PMI_due') && $request->filled('date_of_inspection')) {
                    $fleetBrakeData = [
                        'start_date' => $request->input('PMI_due'),
                        'end_date' => \Carbon\Carbon::parse($request->input('PMI_due'))->addYear()->toDateString(),
                        'company_id' => $request->input('companyName'),
                        'planner_type' => 'Brake Test Due',
                        'vehicle_id' => $vehicleDetailsModel1->id,
                        'every' => $request->input('PMI_intervals'),
                        'interval' => 'Week',
                        'created_by' => \Auth::user()->id,
                    ];
                    $fleetBrake = \App\Models\Fleet::create($fleetBrakeData);
                    $this->generateReminders($fleetBrake);
                }

                // if ($request->filled('fridge_service')) {
                //     $fleetFridge = [
                //         'start_date' => $request->input('fridge_service'),
                //         'end_date' => \Carbon\Carbon::parse($request->input('fridge_service'))->addYear()->toDateString(),
                //         'company_id' => $request->input('companyName'),
                //         'planner_type' => 'Fridge Service',
                //         'vehicle_id' => $vehicleDetailsModel1->id,
                //         'every' => $request->input('fridge_service_interval'),
                //         'interval' => 'Day',
                //         'created_by' => \Auth::user()->id,
                //     ];
                //     $fleetFridgeModel  = \App\Models\Fleet::create($fleetFridge);
                //     $this->generateReminders($fleetFridgeModel);
                // }

                // if ($request->filled('fridge_calibration')) {
                //     $fleetFridgeCalibration = [
                //         'start_date' => $request->input('fridge_calibration'),
                //         'end_date' => \Carbon\Carbon::parse($request->input('fridge_calibration'))->addYear()->toDateString(),
                //         'company_id' => $request->input('companyName'),
                //         'planner_type' => 'Fridge Calibration',
                //         'vehicle_id' => $vehicleDetailsModel1->id,
                //         'every' => $request->input('fridge_calibration_interval'),
                //         'interval' => 'Day',
                //         'created_by' => \Auth::user()->id,
                //     ];
                //     $fleetFridgeCalibrationModel  = \App\Models\Fleet::create($fleetFridgeCalibration);
                //     $this->generateReminders($fleetFridgeCalibrationModel);
                // }

                // if ($request->filled('tail_lift')) {
                //     $fleetTailLift = [
                //         'start_date' => $request->input('tail_lift'),
                //         'end_date' => \Carbon\Carbon::parse($request->input('tail_lift'))->addYear()->toDateString(),
                //         'company_id' => $request->input('companyName'),
                //         'planner_type' => 'Tail lift',
                //         'vehicle_id' => $vehicleDetailsModel1->id,
                //         'every' => $request->input('tail_lift_interval'),
                //         'interval' => 'Day',
                //         'created_by' => \Auth::user()->id,
                //     ];
                //     $fleetTailLiftModel  = \App\Models\Fleet::create($fleetTailLift);
                //     $this->generateReminders($fleetTailLiftModel);
                // }

                // if ($request->filled('loler')) {
                //     $fleetLoler = [
                //         'start_date' => $request->input('loler'),
                //         'end_date' => \Carbon\Carbon::parse($request->input('loler'))->addYear()->toDateString(),
                //         'company_id' => $request->input('companyName'),
                //         'planner_type' => 'Loler',
                //         'vehicle_id' => $vehicleDetailsModel1->id,
                //         'every' => $request->input('loler_interval'),
                //         'interval' => 'Day',
                //         'created_by' => \Auth::user()->id,
                //     ];
                //     $LolerModel  = \App\Models\Fleet::create($fleetLoler);
                //     $this->generateReminders($LolerModel);
                // }

                if ($request->filled('tacho_calibration')) {
                    $fleetTachoData = [
                        'start_date' => $request->input('tacho_calibration'),
                        'end_date' => \Carbon\Carbon::parse($request->input('tacho_calibration'))->addYears(2)->toDateString(),
                        'company_id' => $request->input('companyName'),
                        'planner_type' => 'Tacho Calibration',
                        'vehicle_id' => $vehicleDetailsModel1->id,
                        'every' => 24,
                        'interval' => 'Month',
                        'created_by' => \Auth::user()->id,
                    ];
                    $fleetTacho = \App\Models\Fleet::create($fleetTachoData);
                    $this->generateReminders($fleetTacho);
                }

                if ($request->filled('dvs_pss_permit_expiry')) {
                    $fleetDVSPSS = [
                        'start_date' => $request->input('dvs_pss_permit_expiry'),
                        'end_date' => \Carbon\Carbon::parse($request->input('dvs_pss_permit_expiry'))->addYears(5)->toDateString(),
                        'company_id' => $request->input('companyName'),
                        'planner_type' => 'DVS/PSS Permit Expiry',
                        'vehicle_id' => $vehicleDetailsModel1->id,
                        'every' => 60,
                        'interval' => 'Month',
                        'created_by' => \Auth::user()->id,
                    ];
                    $fleetDVS = \App\Models\Fleet::create($fleetDVSPSS);
                    $this->generateReminders($fleetDVS);
                }

                if ($request->filled('insurance')) {
                    $fleetinsurance = [
                        'start_date' => $request->input('insurance'),
                        'end_date' => \Carbon\Carbon::parse($request->input('insurance'))->addYears()->toDateString(),
                        'company_id' => $request->input('companyName'),
                        'planner_type' => 'Insurance',
                        'vehicle_id' => $vehicleDetailsModel1->id,
                        'every' => 12,
                        'interval' => 'Month',
                        'created_by' => \Auth::user()->id,
                    ];
                    $fleetins = \App\Models\Fleet::create($fleetinsurance);
                    $this->generateReminders($fleetins);
                }

                // Handle the second API call only if the first one was successful
                if ($api1Success) {
                    $accessToken = $this->getDVSAAccessToken();

                    if ($accessToken) {
                        // Step 2: Call API 2 with token and registration
                        $registration = $request->input('registrationNumber');
                        $response2 = Http::withHeaders([
                            'x-api-key' => $apiKey2,
                            'Authorization' => 'Bearer '.$accessToken,
                        ])->get("https://history.mot.api.gov.uk/v1/trade/vehicles/registration/$registration");

                        $responseVehicle = $response2->json();
                        // Log::info('API 2 Response:', $responseVehicle);

                        if (! empty($responseVehicle)) {
                            // Step 3: Save Vehicle Data
                            $vehicleData2 = [
                                'created_by' => \Auth::user()->id,
                                'companyName' => $request->input('companyName'),
                                'registrations' => $responseVehicle['registration'] ?? null,
                                'make' => $responseVehicle['make'] ?? null,
                                'model' => $responseVehicle['model'] ?? null,
                                'first_used_date' => $responseVehicle['firstUsedDate'] ?? null,
                                'fuel_type' => $responseVehicle['fuelType'] ?? null,
                                'primary_colour' => $responseVehicle['primaryColour'] ?? null,
                                'registration_date' => $responseVehicle['registrationDate'] ?? null,
                                'vehicle_type' => 'HGV',
                                'manufacture_date' => $responseVehicle['manufactureDate'] ?? null,
                                'engine_size' => $responseVehicle['engineSize'] ?? null,
                                'has_outstanding_recall' => $responseVehicle['hasOutstandingRecall'] ?? null,
                            ];

                            $vehicleDetailsModel2 = \App\Models\Vehicles::updateOrCreate(
                                ['registrations' => $vehicleData2['registrations']],
                                $vehicleData2
                            );

                            $vehicleDetailsModel1->vehicle_id = $vehicleDetailsModel2->id;
                            $vehicleDetailsModel1->save();

                            // Step 4: Save MOT Tests
                            // Step 4: Handle MOT Data (Both Types)
if (isset($responseVehicle['motTests']) && is_array($responseVehicle['motTests'])) {

    // ✅ Case 1: Full MOT History Available

    $firstExpiryDate = $responseVehicle['motTests'][0]['expiryDate'] ?? null;
    if ($firstExpiryDate) {
        $vehicleDetailsModel2->annual_test_expiry_date = $firstExpiryDate;
        $vehicleDetailsModel2->save();
    }

    $motTestsReversed = array_reverse($responseVehicle['motTests']);

    foreach ($motTestsReversed as $test) {

        $annualTest = [
            'companyName' => $request->input('companyName') ?? null,
            'vehicle_id' => $vehicleDetailsModel2->id,
            'mot_test_number' => $test['motTestNumber'] ?? null,
            'completed_date' => $test['completedDate'] ?? null,
            'expiry_date' => $test['expiryDate'] ?? null,
            'odometer_value' => $test['odometerValue'] ?? null,
            'odometer_unit' => $test['odometerUnit'] ?? null,
            'odometer_result_type' => $test['odometerResultType'] ?? null,
            'test_result' => $test['testResult'] ?? null,
            'data_source' => $test['dataSource'] ?? null,
            'location' => $test['location'] ?? null,
        ];

        $annualTestModel = \App\Models\VehiclesAnnualTest::updateOrCreate($annualTest);

        // Defects
        if (!empty($test['defects'])) {
            foreach ($test['defects'] as $defect) {
                $defectData = [
                    'companyName' => $request->input('companyName') ?? null,
                    'vehicle_id' => $vehicleDetailsModel2->id,
                    'annual_test_id' => $annualTestModel->id,
                    'dangerous' => isset($defect['dangerous']) ? ($defect['dangerous'] ? 'true' : 'false') : null,
                    'text' => $defect['text'] ?? null,
                    'type' => $defect['type'] ?? null,
                ];

                \App\Models\VehiclesAnnualTestDefect::updateOrCreate($defectData);
            }
        }
    }

} else {

    // ✅ Case 2: No MOT history (New Vehicle type response)

    if (isset($responseVehicle['motTestDueDate'])) {

        // Save directly in Vehicles table
        $vehicleDetailsModel2->annual_test_expiry_date = $responseVehicle['motTestDueDate'];
        $vehicleDetailsModel2->save();

    }
}

                            // Set the flag for successful API 2 call
                            $api2Success = true;
                        }
                    }
                }

                // Check if both API calls were successful and redirect with appropriate message
                if ($api1Success && $api2Success) {
                    return redirect()->route('contract.index')->with('success', __('Vehicle Data successfully created!'));
                } elseif ($api1Success) {
                    // Redirect to a view with a modal for vehicle addition choice
                    return redirect()->route('contract.index')->with('showDriverModal', true);
                } elseif ($api2Success) {
                    return redirect()->route('contract.index')->with('success', __('Vehicle successfully created!'));
                } else {
                    return redirect()->route('contract.index')->with('error', __('Failed to fetch vehicle details from both APIs'));
                }
            } catch (\Exception $e) {
                // Log the error or handle it appropriately
                return redirect()->route('contract.index')->with('error', __('Failed to fetch vehicle details from APIs').': '.$e->getMessage());
            }
        } else {
            // If the user doesn't have permission, redirect with an error message
            return redirect()->route('contract.index')->with('error', __('Permission denied.'));
        }
    }

    public function trailerstore(Request $request)
    {
        if (\Auth::user()->can('create vehicle')) {
            $existingVehicle = VehicleDetails::where('companyName', $request->input('companyName'))
                ->where('registrationNumber', $request->input('registrationNumber'))
                ->first();

            if ($existingVehicle) {
                return redirect()->route('contract.index')->with('error', __('This company and registration number already exist.'));
            }

            $apiKey2 = 'dmVdeybS8M99rT3PrZ6iw8VZvP5gR6la3wSy2Mld'; // API key for second API

            try {
                $registrations = $request->input('registrationNumber');

                $accessToken = $this->getDVSAAccessToken(); // Ensure you implement this method

                if (! $accessToken) {
                    return redirect()->route('contract.index')->with('error', __('Failed to retrieve access token from DVSA API.'));
                }

                $response2 = Http::withHeaders([
                    'x-api-key' => $apiKey2,
                    'Authorization' => 'Bearer '.$accessToken,
                ])->get("https://history.mot.api.gov.uk/v1/trade/vehicles/registration/$registrations");

                $responseData2 = $response2->json();
                // \Log::info('API 2 Response:', $responseData2);

                // $vehicleType = $responseData2['vehicleType'] ?? 'Trailer';

                // // Validate that vehicle_type is "Trailer"
                // if ($vehicleType !== 'Trailer') {
                //     return redirect()->route('contract.index')->with('error', __('Only Trailers are allowed.'));
                // }

                $vehicleData1 = [
                    'created_by' => \Auth::user()->id,
                    'companyName' => $request->input('companyName'),
                    'vehicle_nick_name' => $request->input('vehicle_nick_name') ?? $request->input('registrationNumber'),
                    'insurance_type' => json_encode($request->input('insurance_type', [])),
                    'insurance' => $request->input('insurance'),
                    'odometer_reading' => $request->input('odometer_reading', 0),
                    'PMI_due' => $request->input('PMI_due'),
                    'PMI_intervals' => $request->input('PMI_intervals'),
                    'date_of_inspection' => $request->input('date_of_inspection'),
                    'brake_test_due' => $request->input('PMI_due') ? \Carbon\Carbon::parse($request->input('PMI_due'))->format('Y-m-d') : null,
                    'vehicle_status' => $request->input('vehicle_status'),
                    'group_id' => $request->input('group_id'),
                    'depot_id' => $request->input('depot_id'),
                    'registrationNumber' => $request->input('registrationNumber'),
                ];

                // Save vehicle data in VehicleDetails model
                $vehicleDetailsModel1 = VehicleDetails::updateOrCreate(
                    ['registrationNumber' => $vehicleData1['registrationNumber']],
                    $vehicleData1
                );

                if ($request->filled('PMI_due') && $request->filled('date_of_inspection')) {
                    $fleetPMIData = [
                        'start_date' => $request->input('PMI_due'),
                        'end_date' => \Carbon\Carbon::parse($request->input('PMI_due'))->addYear()->toDateString(),
                        'company_id' => $request->input('companyName'),
                        'planner_type' => 'PMI Due',
                        'vehicle_id' => $vehicleDetailsModel1->id,
                        'every' => $request->input('PMI_intervals'),
                        'interval' => 'Week',
                        'created_by' => \Auth::user()->id,
                    ];
                    $fleetPMI = \App\Models\Fleet::create($fleetPMIData);
                    $this->generateReminders($fleetPMI);
                }

                if ($request->filled('PMI_due') && $request->filled('date_of_inspection')) {
                    $fleetBrakeData = [
                        'start_date' => $request->input('PMI_due'),
                        'end_date' => \Carbon\Carbon::parse($request->input('PMI_due'))->addYear()->toDateString(),
                        'company_id' => $request->input('companyName'),
                        'planner_type' => 'Brake Test Due',
                        'vehicle_id' => $vehicleDetailsModel1->id,
                        'every' => $request->input('PMI_intervals'),
                        'interval' => 'Week',
                        'created_by' => \Auth::user()->id,
                    ];
                    $fleetBrake = \App\Models\Fleet::create($fleetBrakeData);
                    $this->generateReminders($fleetBrake);
                }

                if ($request->filled('insurance')) {
                    $fleetinsurance = [
                        'start_date' => $request->input('insurance'),
                        'end_date' => \Carbon\Carbon::parse($request->input('insurance'))->addYears()->toDateString(),
                        'company_id' => $request->input('companyName'),
                        'planner_type' => 'Insurance',
                        'vehicle_id' => $vehicleDetailsModel1->id,
                        'every' => 12,
                        'interval' => 'Month',
                        'created_by' => \Auth::user()->id,
                    ];
                    $fleetins = \App\Models\Fleet::create($fleetinsurance);
                    $this->generateReminders($fleetins);
                }

                if (! empty($responseData2)) {
                    // Extracting data from the first item in the array, assuming it contains relevant data
                    $responseVehicle = $responseData2;

                    $vehicleData2 = [

                        'created_by' => \Auth::user()->id,
                        'companyName' => $request->input('companyName'),
                        'registrations' => $responseVehicle['registration'] ?? null,
                        'make' => $responseVehicle['make'] ?? null,
                        'model' => $responseVehicle['model'] ?? null,
                        'first_used_date' => $responseVehicle['firstUsedDate'] ?? null,
                        'fuel_type' => $responseVehicle['fuelType'] ?? null,
                        'primary_colour' => $responseVehicle['primaryColour'] ?? null,
                        'registration_date' => $responseVehicle['registrationDate'] ?? null,
                        'vehicle_type' => 'Trailer',
                        'manufacture_date' => $responseVehicle['manufactureDate'] ?? null,
                        'engine_size' => $responseVehicle['engineSize'] ?? null,
                        'has_outstanding_recall' => $responseVehicle['hasOutstandingRecall'] ?? null,
                    ];

                    // Update or create data in vehicleDetails model for API 2
                    $vehicleDetailsModel2 = \App\Models\Vehicles::updateOrCreate(
                        ['registrations' => $vehicleData2['registrations']],
                        $vehicleData2
                    );

                    $vehicleDetailsModel1->vehicle_id = $vehicleDetailsModel2->id;
                    $vehicleDetailsModel1->save();

                    // Save annual tests data
                   if (isset($responseVehicle['motTests']) && is_array($responseVehicle['motTests'])) {

    // ===== CASE 1: MOT HISTORY =====
    $firstExpiryDate = $responseVehicle['motTests'][0]['expiryDate'] ?? null;

    if ($firstExpiryDate) {
        $vehicleDetailsModel2->annual_test_expiry_date = $firstExpiryDate;
        $vehicleDetailsModel2->save();
    }

    $motTestsReversed = array_reverse($responseVehicle['motTests']);

    foreach ($motTestsReversed as $test) {

        $annualTest = [
            'companyName' => $request->input('companyName') ?? null,
            'vehicle_id' => $vehicleDetailsModel2->id,
            'mot_test_number' => $test['motTestNumber'] ?? null,
            'completed_date' => $test['completedDate'] ?? null,
            'expiry_date' => $test['expiryDate'] ?? null,
            'odometer_value' => $test['odometerValue'] ?? null,
            'odometer_unit' => $test['odometerUnit'] ?? null,
            'odometer_result_type' => $test['odometerResultType'] ?? null,
            'test_result' => $test['testResult'] ?? null,
            'data_source' => $test['dataSource'] ?? null,
            'location' => $test['location'] ?? null,
        ];

        $annualTestModel = \App\Models\VehiclesAnnualTest::updateOrCreate(
            [
                'vehicle_id' => $vehicleDetailsModel2->id,
                'mot_test_number' => $test['motTestNumber'],
            ],
            $annualTest
        );

        // ✅ Defects ONLY here
        if (!empty($test['defects']) && is_array($test['defects'])) {
            foreach ($test['defects'] as $defect) {

                \App\Models\VehiclesAnnualTestDefect::updateOrCreate(
                    [
                        'annual_test_id' => $annualTestModel->id,
                        'text' => $defect['text'] ?? null,
                    ],
                    [
                        'companyName' => $request->input('companyName') ?? null,
                        'vehicle_id' => $vehicleDetailsModel2->id,
                        'annual_test_id' => $annualTestModel->id,
                        'dangerous' => isset($defect['dangerous']) ? ($defect['dangerous'] ? 'true' : 'false') : null,
                        'text' => $defect['text'] ?? null,
                        'type' => $defect['type'] ?? null,
                    ]
                );
            }
        }
    }

} else {

    // ===== CASE 2: ONLY motTestDueDate =====
    if (isset($responseVehicle['motTestDueDate'])) {

        // ✅ Save expiry directly
        $vehicleDetailsModel2->annual_test_expiry_date = $responseVehicle['motTestDueDate'];
        $vehicleDetailsModel2->save();
    }
}

                    return redirect()->route('contract.index')->with('success', __('Vehicle Data successfully created!'));
                } else {
                    return redirect()->route('contract.index')->with('error', __('Failed to fetch vehicle details from the API.'));
                }
            } catch (\Exception $e) {
                return redirect()->route('contract.index')->with('error', __('Failed to fetch vehicle details from API').': '.$e->getMessage());
            }
        } else {
            return redirect()->route('contract.index')->with('error', __('Permission denied.'));
        }
    }

    private function generateReminders($fleet)
    {
        $startDate = \Carbon\Carbon::parse($fleet->start_date);
        $endDate = \Carbon\Carbon::parse($fleet->end_date);
        $nextReminderDate = $startDate;

        while ($nextReminderDate <= $endDate) {
            \App\Models\FleetPlannerReminder::create([
                'fleet_planner_id' => $fleet->id,
                'next_reminder_date' => $nextReminderDate->toDateString(),
                'status' => 'Pending',
            ]);

            switch ($fleet->interval) {
                case 'Day':
                    $nextReminderDate = $nextReminderDate->addDays($fleet->every);
                    break;
                case 'Week':
                    $nextReminderDate = $nextReminderDate->addWeeks($fleet->every);
                    break;
                case 'Month':
                    $nextReminderDate = $nextReminderDate->addMonths($fleet->every);
                    break;
            }
        }
    }

    // public function updateAllData()
    // {
    //     try {
    //         $apiKey1 = 'CS8NuPQICs60CPb1Xb4cS6MG9KGIVuQ47ONeJXu6'; // Replace with your API key
    //         $apiUrl1 = 'https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles';

    //         $vehicles = \App\Models\vehicleDetails::all(); // Fetch all vehicles or registrations

    //         foreach ($vehicles as $vehicle) {
    //             $requestData1 = [
    //                 'registrationNumber' => $vehicle->registrationNumber,
    //             ];

    //             // Make API call similar to your store method's logic
    //             $response = Http::withHeaders([
    //                 'x-api-key' => $apiKey1,
    //             ])->post($apiUrl1, $requestData1);

    //             $responseData = $response->json();

    //             // Handle response as per your application's logic

    //             // Example update in database if needed:
    //             $vehicle->update([
    //                 'created_by' => \Auth::user()->id,
    //                 'taxStatus' => $responseData['taxStatus'] ?? null,
    //                 'taxDueDate' => isset($responseData['taxDueDate']) ? date('d F Y', strtotime($responseData['taxDueDate'])) : null,
    //                 'motStatus' => $responseData['motStatus'] ?? null,
    //                 'make' => $responseData['make'] ?? null,
    //                 'yearOfManufacture' => $responseData['yearOfManufacture'] ?? null,
    //                 'engineCapacity' => $responseData['engineCapacity'] ?? null,
    //                 'co2Emissions' => $responseData['co2Emissions'] ?? null,
    //                 'fuelType' => $responseData['fuelType'] ?? null,
    //                 'markedForExport' => $responseData['markedForExport'] ?? null,
    //                 'colour' => $responseData['colour'] ?? null,
    //                 'typeApproval' => $responseData['typeApproval'] ?? null,
    //                 'revenueWeight' => $responseData['revenueWeight'] ?? null,
    //                 'euroStatus' => $responseData['euroStatus'] ?? null,
    //                 'dateOfLastV5CIssued' => $responseData['dateOfLastV5CIssued'] ?? null,
    //                 'motExpiryDate' => $responseData['motExpiryDate'] ?? null,
    //                 'wheelplan' => $responseData['wheelplan'] ?? null,
    //                 'monthOfFirstRegistration' => $responseData['monthOfFirstRegistration'] ?? null,
    //             ]);
    //         }

    //         // Redirect back after processing
    //         return redirect()->back()->with('success', 'All vehicle data updated successfully.');

    //     } catch (\Exception $e) {

    //         // Redirect back with error message
    //         return redirect()->back()->with('error', 'Failed to update vehicle data. Please try again.');
    //     }
    // }

    // public function updateAllData(Request $request)
    // {
    //     if (\Auth::user()->can('create vehicle')) {
    //         try {
    //             // Update data using API 1
    //             $this->updateAllData1();

    //             // Update data using API 2
    //             $this->updateAllData2();

    //             return redirect()->back()->with('success', 'All vehicle data updated successfully.');

    //         } catch (\Exception $e) {
    //             // Log the error or handle it appropriately
    //             return response()->json(['success' => false, 'message' => 'Failed to update vehicle details from APIs: ' . $e->getMessage()]);
    //         }

    //     } else {
    // return redirect()->back()->with('error', 'Permission denied.');

    //     }
    // }

    public function updateData1(Request $request)
    {
        if (\Auth::user()->can('create vehicle')) {
            try {
                $user = \Auth::user();
                // ✅ vehicle group ids
            $vehicleGroupIds = is_array($user->vehicle_group_id)
                ? $user->vehicle_group_id
                : json_decode($user->vehicle_group_id, true);

            if (! is_array($vehicleGroupIds)) {
                $vehicleGroupIds = [$user->vehicle_group_id];
            }

                $isAdminOrPTCManager = $user->hasRole('company') || $user->hasRole('PTC manager');

                // Validate input
                $request->validate([
                    'from_number' => 'required|integer',
                    'to_number' => 'required|integer|gte:from_number',
                ]);

                $from = $request->from_number;
                $to = $request->to_number;

                // Update data using API 1
                if ($isAdminOrPTCManager) {
                    $this->updateAllData1($from, $to);
                } else {
                    // Update vehicles for the user's company only
                    $this->updateAllDataForCompany($user->companyname,$vehicleGroupIds, $from, $to);
                }

                return redirect()->back()->with('success', 'Data from API 1 updated successfully.');

            } catch (\Exception $e) {
                \Log::error('API 1 update failed', ['error' => $e->getMessage()]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update vehicle details from API 1: '.$e->getMessage(),
                ]);
            }

        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function updateData2(Request $request)
    {
        if (\Auth::user()->can('create vehicle')) {
            try {
                $user = \Auth::user();
                 // ✅ vehicle group ids
            $vehicleGroupIds = is_array($user->vehicle_group_id)
                ? $user->vehicle_group_id
                : json_decode($user->vehicle_group_id, true);

            if (! is_array($vehicleGroupIds)) {
                $vehicleGroupIds = [$user->vehicle_group_id];
            }

                $isAdminOrPTCManager = $user->hasRole('company') || $user->hasRole('PTC manager');

                // Validate input
                $request->validate([
                    'from_number' => 'required|integer',
                    'to_number' => 'required|integer|gte:from_number',
                ]);

                $from = $request->from_number;
                $to = $request->to_number;

                // Pass the range to your update functions
                if ($isAdminOrPTCManager) {
                    $this->updateAllData2($from, $to); // Admins get all vehicles
                } else {
                    $this->updateAllData2ForCompany($user->companyname, $vehicleGroupIds, $from, $to); // Filtered by company
                }

                return redirect()->back()->with('success', 'Data from API 2 updated successfully.');

            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Failed to update vehicle details from API 2: '.$e->getMessage());
            }
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    private function updateAllDataForCompany($companyName,$vehicleGroupIds, $from = null, $to = null)
    {
        $apiKey1 = 'CS8NuPQICs60CPb1Xb4cS6MG9KGIVuQ47ONeJXu6';
        $apiUrl1 = 'https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles';

        $query = \App\Models\vehicleDetails::where('companyName', $companyName)->whereIn('group_id', $vehicleGroupIds);

        if ($from !== null && $to !== null) {
            $query->whereBetween('id', [$from, $to]);
        }

        $vehicles = $query->get();

        foreach ($vehicles as $vehicle) {
            $registrationNumber = $vehicle->registrationNumber;

            $response = Http::withHeaders([
                'x-api-key' => $apiKey1,
            ])->post($apiUrl1, ['registrationNumber' => $registrationNumber]);

            $responseData1 = $response->json();

            if (isset($responseData1['registrationNumber'])) {
                $vehicleData1 = [
                    'registrationNumber' => $responseData1['registrationNumber'],
                    'taxStatus' => $responseData1['taxStatus'] ?? null,
                    'taxDueDate' => isset($responseData1['taxDueDate']) ? date('d F Y', strtotime($responseData1['taxDueDate'])) : null,
                    'motStatus' => $responseData1['motStatus'] ?? null,
                    'make' => $responseData1['make'] ?? null,
                    'yearOfManufacture' => $responseData1['yearOfManufacture'] ?? null,
                    'engineCapacity' => $responseData1['engineCapacity'] ?? null,
                    'co2Emissions' => $responseData1['co2Emissions'] ?? null,
                    'fuelType' => $responseData1['fuelType'] ?? null,
                    'markedForExport' => $responseData1['markedForExport'] ?? null,
                    'colour' => $responseData1['colour'] ?? null,
                    'typeApproval' => $responseData1['typeApproval'] ?? null,
                    'revenueWeight' => $responseData1['revenueWeight'] ?? null,
                    'euroStatus' => $responseData1['euroStatus'] ?? null,
                    'dateOfLastV5CIssued' => $responseData1['dateOfLastV5CIssued'] ?? null,
                    'motExpiryDate' => $responseData1['motExpiryDate'] ?? null,
                    'wheelplan' => $responseData1['wheelplan'] ?? null,
                    'monthOfFirstRegistration' => $responseData1['monthOfFirstRegistration'] ?? null,
                    'created_by' => \Auth::user()->id,
                ];

                \App\Models\vehicleDetails::updateOrCreate(
                    ['registrationNumber' => $vehicleData1['registrationNumber'], 'companyName' => $companyName],
                    $vehicleData1
                );
            }
        }
    }

    private function updateAllData2ForCompany($companyName, $vehicleGroupIds, $from = null, $to = null)
    {
        $apiKey2 = 'dmVdeybS8M99rT3PrZ6iw8VZvP5gR6la3wSy2Mld'; // API key for second API
        $apiUrl2 = 'https://history.mot.api.gov.uk/v1/trade/vehicles/registration';

        // Fetch vehicles for the specified company only
        $query = \App\Models\vehicleDetails::where('companyName', $companyName)->whereIn('group_id', $vehicleGroupIds);

        if ($from !== null && $to !== null) {
            $query->whereBetween('id', [$from, $to]);
        }

        $vehicles = $query->get();

        foreach ($vehicles as $vehicle) {
            $registrationNumber = $vehicle->registrationNumber;

            // Log fetched registration number
            // \Log::info("Fetching data for registration number: {$registrationNumber}");

            $accessToken = $this->getDVSAAccessToken(); // Make sure this method returns a valid token

            if (! $accessToken) {
                return redirect()->route('contract.index')->with('error', __('Failed to retrieve access token from DVSA API.'));
            }

            // Second API call
            $response2 = Http::withHeaders([
                'x-api-key' => $apiKey2,
                'Authorization' => 'Bearer '.$accessToken,
            ])->get("$apiUrl2/$registrationNumber");

            // Log API response
            // \Log::info("API Response for registration number {$registrationNumber}: ", $response2->json());

            $responseData2 = $response2->json();

            if (! empty($responseData2) && isset($responseData2['registration'])) {
                $responseVehicle = $responseData2;

                // Update or create Vehicles model
                $vehicleData2 = [
                    'companyName' => $vehicle->companyName,
                    'registrations' => $responseVehicle['registration'] ?? null,
                    'make' => $responseVehicle['make'] ?? null,
                    'model' => $responseVehicle['model'] ?? null,
                    'first_used_date' => $responseVehicle['firstUsedDate'] ?? null,
                    'fuel_type' => $responseVehicle['fuelType'] ?? null,
                    'primary_colour' => $responseVehicle['primaryColour'] ?? null,
                    'registration_date' => $responseVehicle['registrationDate'] ?? null,
                    'manufacture_date' => $responseVehicle['manufactureDate'] ?? null,
                    'engine_size' => $responseVehicle['engineSize'] ?? null,
                    'has_outstanding_recall' => $responseVehicle['hasOutstandingRecall'] ?? null,
                ];

                $vehicleDetailsModel2 = \App\Models\Vehicles::updateOrCreate(
                    ['registrations' => $vehicleData2['registrations'], 'companyName' => $companyName],
                    $vehicleData2
                );

                // Link API 1 and API 2 data if needed
                $vehicleDetailsModel1 = \App\Models\vehicleDetails::where('registrationNumber', $registrationNumber)->first();
                if ($vehicleDetailsModel1) {
                    $vehicleDetailsModel1->vehicle_id = $vehicleDetailsModel2->id;
                    $vehicleDetailsModel1->save();
                }

               // ✅ Handle both cases (motTests + motTestDueDate)

if (isset($responseVehicle['motTests']) && is_array($responseVehicle['motTests']) && count($responseVehicle['motTests']) > 0) {

    // ===== CASE 1: MOT HISTORY =====
    $firstExpiryDate = $responseVehicle['motTests'][0]['expiryDate'] ?? null;

    if ($firstExpiryDate) {
        $vehicleDetailsModel2->annual_test_expiry_date = $firstExpiryDate;
        $vehicleDetailsModel2->save();
    }

    $motTestsReversed = array_reverse($responseVehicle['motTests']);

    foreach ($motTestsReversed as $test) {

        $annualTest = [
            'companyName' => $vehicle->companyName ?? null,
            'vehicle_id' => $vehicleDetailsModel2->id,
            'mot_test_number' => $test['motTestNumber'] ?? null,
            'completed_date' => $test['completedDate'] ?? null,
            'expiry_date' => $test['expiryDate'] ?? null,
            'odometer_value' => $test['odometerValue'] ?? null,
            'odometer_unit' => $test['odometerUnit'] ?? null,
            'odometer_result_type' => $test['odometerResultType'] ?? null,
            'test_result' => $test['testResult'] ?? null,
            'data_source' => $test['dataSource'] ?? null,
            'location' => $test['location'] ?? null,
        ];

        $annualTestModel = \App\Models\VehiclesAnnualTest::updateOrCreate(
            [
                'vehicle_id' => $vehicleDetailsModel2->id,
                'mot_test_number' => $test['motTestNumber'],
            ],
            $annualTest
        );

        // ✅ Defects ONLY here
        if (!empty($test['defects']) && is_array($test['defects'])) {
            foreach ($test['defects'] as $defect) {

                \App\Models\VehiclesAnnualTestDefect::updateOrCreate(
                    [
                        'annual_test_id' => $annualTestModel->id,
                        'text' => $defect['text'] ?? null,
                    ],
                    [
                        'companyName' => $vehicle->companyName ?? null,
                        'vehicle_id' => $vehicleDetailsModel2->id,
                        'annual_test_id' => $annualTestModel->id,
                        'dangerous' => isset($defect['dangerous']) ? ($defect['dangerous'] ? 'true' : 'false') : null,
                        'text' => $defect['text'] ?? null,
                        'type' => $defect['type'] ?? null,
                    ]
                );
            }
        }
    }

} else {

    // ===== CASE 2: ONLY motTestDueDate =====
    if (isset($responseVehicle['motTestDueDate'])) {

        $vehicleDetailsModel2->annual_test_expiry_date = $responseVehicle['motTestDueDate'];
        $vehicleDetailsModel2->save();
    }
}

            } else {
                // Handle case where $responseData2 is empty or does not contain expected data
                \Log::error("API 2 response did not contain expected data structure for registration number: {$registrationNumber}");
            }
        }
    }

    // Before change Cron Create
    // private function updateAllData1($from = null, $to = null)
    // {
    //     $apiKey1 = 'CS8NuPQICs60CPb1Xb4cS6MG9KGIVuQ47ONeJXu6';
    //     $apiUrl1 = 'https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles';

    //     $query = \App\Models\vehicleDetails::query();

    //     if ($from !== null && $to !== null) {
    //         $query->whereBetween('id', [$from, $to]);
    //     }

    //     $vehicles = $query->get();

    //     foreach ($vehicles as $vehicle) {
    //         $registrationNumber = $vehicle->registrationNumber;
    //         $companyName = $vehicle->companyName;

    //         $response = Http::withHeaders([
    //             'x-api-key' => $apiKey1,
    //         ])->post($apiUrl1, ['registrationNumber' => $registrationNumber]);

    //         $responseData1 = $response->json();

    //         if (isset($responseData1['registrationNumber'])) {
    //             $vehicleData1 = [
    //                 'registrationNumber' => $responseData1['registrationNumber'],
    //                 'taxStatus' => $responseData1['taxStatus'] ?? null,
    //                 'taxDueDate' => isset($responseData1['taxDueDate']) ? date('d F Y', strtotime($responseData1['taxDueDate'])) : null,
    //                 'motStatus' => $responseData1['motStatus'] ?? null,
    //                 'make' => $responseData1['make'] ?? null,
    //                 'yearOfManufacture' => $responseData1['yearOfManufacture'] ?? null,
    //                 'engineCapacity' => $responseData1['engineCapacity'] ?? null,
    //                 'co2Emissions' => $responseData1['co2Emissions'] ?? null,
    //                 'fuelType' => $responseData1['fuelType'] ?? null,
    //                 'markedForExport' => $responseData1['markedForExport'] ?? null,
    //                 'colour' => $responseData1['colour'] ?? null,
    //                 'typeApproval' => $responseData1['typeApproval'] ?? null,
    //                 'revenueWeight' => $responseData1['revenueWeight'] ?? null,
    //                 'euroStatus' => $responseData1['euroStatus'] ?? null,
    //                 'dateOfLastV5CIssued' => $responseData1['dateOfLastV5CIssued'] ?? null,
    //                 'motExpiryDate' => $responseData1['motExpiryDate'] ?? null,
    //                 'wheelplan' => $responseData1['wheelplan'] ?? null,
    //                 'monthOfFirstRegistration' => $responseData1['monthOfFirstRegistration'] ?? null,
    //                 'created_by' => \Auth::user()->id,
    //             ];

    //             \App\Models\vehicleDetails::updateOrCreate(
    //                 ['registrationNumber' => $vehicleData1['registrationNumber'], 'companyName' => $companyName],
    //                 $vehicleData1
    //             );
    //             \Log::info('Vehicle updated successfully from API 1', [
    //                 'registrationNumber' => $vehicleData1['registrationNumber'],
    //                 'companyName' => $companyName,
    //             ]);
    //         }
    //     }
    // }

    // private function updateAllData2($from = null, $to = null)
    // {
    //     $query = \App\Models\vehicleDetails::query();

    //     if ($from !== null && $to !== null) {
    //         $query->whereBetween('id', [$from, $to]);
    //     }

    //     $vehicles = $query->get();
    //     $apiKey2 = 'dmVdeybS8M99rT3PrZ6iw8VZvP5gR6la3wSy2Mld'; // API key for second API
    //     $apiUrl2 = 'https://history.mot.api.gov.uk/v1/trade/vehicles/registration';

    //     foreach ($vehicles as $vehicle) {
    //         $registrationNumber = $vehicle->registrationNumber;
    //         $companyName = $vehicle->companyName;

    //         $accessToken = $this->getDVSAAccessToken(); // Make sure this method returns a valid token

    //         if (!$accessToken) {
    //             \Log::error("Failed to retrieve DVSA access token.");
    //             continue;
    //         }

    //         // Second API call
    //         $response2 = Http::withHeaders([
    //             'x-api-key' => $apiKey2,
    //             'Authorization' => 'Bearer ' . $accessToken,
    //         ])->get("$apiUrl2/$registrationNumber");

    //         $responseData2 = $response2->json();

    //         // Validate API response and handle errors
    //         if (!empty($responseData2) && isset($responseData2['registration'])) {
    //             $responseVehicle = $responseData2;

    //             // Update or create Vehicles model
    //             $vehicleData2 = [

    //                 'companyName' => $companyName,
    //                 'registrations' => $responseVehicle['registration'] ?? null,
    //                 'make' => $responseVehicle['make'] ?? null,
    //                 'model' => $responseVehicle['model'] ?? null,
    //                 'first_used_date' => $responseVehicle['firstUsedDate'] ?? null,
    //                 'fuel_type' => $responseVehicle['fuelType'] ?? null,
    //                 'primary_colour' => $responseVehicle['primaryColour'] ?? null,
    //                 'registration_date' => $responseVehicle['registrationDate'] ?? null,
    //                 'manufacture_date' => $responseVehicle['manufactureDate'] ?? null,
    //                 'engine_size' => $responseVehicle['engineSize'] ?? null,
    //                 'has_outstanding_recall' => $responseVehicle['hasOutstandingRecall'] ?? null,
    //             ];

    //             $vehicleDetailsModel2 = \App\Models\Vehicles::updateOrCreate(
    //                 ['registrations' => $vehicleData2['registrations'], 'companyName' => $companyName],
    //                 $vehicleData2
    //             );

    //             // Link API 1 and API 2 data if needed
    //             $vehicleDetailsModel1 = \App\Models\vehicleDetails::where('registrationNumber', $registrationNumber)->first();
    //             if ($vehicleDetailsModel1) {
    //                 $vehicleDetailsModel1->vehicle_id = $vehicleDetailsModel2->id;
    //                 $vehicleDetailsModel1->save();
    //             }

    //             // Handle annual tests
    //             if (isset($responseVehicle['motTests']) && is_array($responseVehicle['motTests']) && count($responseVehicle['motTests']) > 0) {

    //                 // Save first expiry_date from the original (non-reversed) motTests array
    //                 $firstExpiryDate = $responseVehicle['motTests'][0]['expiryDate'] ?? null;
    //                 if ($firstExpiryDate) {
    //                     $vehicleDetailsModel2->annual_test_expiry_date = $firstExpiryDate;
    //                     $vehicleDetailsModel2->save();
    //                 }

    //                 // Reverse motTests array before processing
    //                 $motTestsReversed = array_reverse($responseVehicle['motTests']);

    //                 foreach ($motTestsReversed as $test) {
    //                     $annualTest = [
    //                         'companyName' => $companyName ?? null,
    //                         'vehicle_id' => $vehicleDetailsModel2->id,
    //                         'mot_test_number' => $test['motTestNumber'] ?? null,
    //                         'completed_date' => $test['completedDate'] ?? null,
    //                         'expiry_date' => $test['expiryDate'] ?? null,
    //                         'odometer_value' => $test['odometerValue'] ?? null,
    //                         'odometer_unit' => $test['odometerUnit'] ?? null,
    //                         'odometer_result_type' => $test['odometerResultType'] ?? null,
    //                         'test_result' => $test['testResult'] ?? null,
    //                         'data_source' => $test['dataSource'] ?? null,
    //                         'location' => $test['location'] ?? null,
    //                     ];

    //                     // Update or create VehiclesAnnualTest model
    //                     // $annualTestModel = \App\Models\VehiclesAnnualTest::updateOrCreate(
    //                     //     ['vehicle_id' => $vehicleDetailsModel2->id, 'test_date' => $test['testDate']],
    //                     //     $annualTest
    //                     // );
    //                     $annualTestModel = \App\Models\VehiclesAnnualTest::updateOrCreate(
    //                         [
    //                             'vehicle_id' => $vehicleDetailsModel2->id,
    //                             'mot_test_number' => $test['motTestNumber'],
    //                         ],
    //                         $annualTest
    //                     );

    //                     // Handle defects
    //                     if (isset($test['defects']) && is_array($test['defects'])) {
    //                         foreach ($test['defects'] as $defect) {
    //                             $defectData = [
    //                                 'companyName' => $companyName ?? null,
    //                                 'vehicle_id' => $vehicleDetailsModel2->id,
    //                                 'annual_test_id' => $annualTestModel->id,
    //                                 'dangerous' => isset($defect['dangerous']) ? ($defect['dangerous'] ? 'true' : 'false') : null,
    //                                 'text' => $defect['text'] ?? null,
    //                                 'type' => $defect['type'] ?? null,
    //                             ];

    //                             // Update or create VehiclesAnnualTestDefect model
    //                             // \App\Models\VehiclesAnnualTestDefect::updateOrCreate(
    //                             //     ['annual_test_id' => $annualTestModel->id, 'failure_item_no' => $defect['failureItemNo']],
    //                             //     $defectData
    //                             // );
    //                             \App\Models\VehiclesAnnualTestDefect::updateOrCreate(
    //                                 [
    //                                     'annual_test_id' => $annualTestModel->id,
    //                                     'text' => $defect['text'] ?? null,

    //                                 ],
    //                                 $defectData
    //                             );
    //                         }
    //                     }
    //                 }
    //             }
    //         } else {
    //             // Handle case where $responseData2 is empty or does not contain expected data
    //             \Log::error('API 2 response did not contain expected data structure for registration number: ' . $registrationNumber);
    //             // You may choose to throw an exception or log this issue as per your application's logic
    //         }
    //     }
    // }

    public function processPendingData1()
    {
       

        try {
            $this->updateAllData1(); // Run API logic only for cron_status = 'Pending'

            return response()->json([
                'success' => true,
                'message' => 'Pending vehicle records processed successfully from API 1.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Processing Pending vehicles failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process Pending vehicle records: '.$e->getMessage(),
            ]);
        }
    }

    public function processPendingData2()
    {
       

        try {
            // 🔹 Run API 2 logic only for records where cron_status = 'Pending'
            $this->updateAllData2();

            return response()->json([
                'success' => true,
                'message' => 'Pending vehicle records processed successfully from API 2.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Processing Pending vehicles failed from API 2', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process Pending vehicle records from API 2: '.$e->getMessage(),
            ]);
        }
    }

    private function updateAllData1($from = null, $to = null)
    {
        $apiKey1 = 'CS8NuPQICs60CPb1Xb4cS6MG9KGIVuQ47ONeJXu6';
        $apiUrl1 = 'https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles';

        $query = \App\Models\vehicleDetails::query();

        // ✅ Limit records by ID range
        if ($from !== null && $to !== null) {
            $query->whereBetween('id', [$from, $to]);
        }

        // ✅ Only process records where cron_status = 'Pending'
        $query->where('cron_status', 'Pending');

        if (! $query->exists()) {
            \Log::info('No Pending Vehicles Found (API 1)');

            return;
        }

        $vehicles = $query->orderBy('id')
            ->limit(100)
            ->get();

        foreach ($vehicles as $vehicle) {
            $registrationNumber = $vehicle->registrationNumber;
            $companyName = $vehicle->companyName;

            try {
                $response = Http::withHeaders([
                    'x-api-key' => $apiKey1,
                ])->post($apiUrl1, ['registrationNumber' => $registrationNumber]);

                $responseData1 = $response->json();

                if (isset($responseData1['registrationNumber'])) {
                    $vehicleData1 = [
                        'registrationNumber' => $responseData1['registrationNumber'],
                        'taxStatus' => $responseData1['taxStatus'] ?? null,
                        'taxDueDate' => isset($responseData1['taxDueDate']) ? date('d F Y', strtotime($responseData1['taxDueDate'])) : null,
                        'motStatus' => $responseData1['motStatus'] ?? null,
                        'make' => $responseData1['make'] ?? null,
                        'yearOfManufacture' => $responseData1['yearOfManufacture'] ?? null,
                        'engineCapacity' => $responseData1['engineCapacity'] ?? null,
                        'co2Emissions' => $responseData1['co2Emissions'] ?? null,
                        'fuelType' => $responseData1['fuelType'] ?? null,
                        'markedForExport' => $responseData1['markedForExport'] ?? null,
                        'colour' => $responseData1['colour'] ?? null,
                        'typeApproval' => $responseData1['typeApproval'] ?? null,
                        'revenueWeight' => $responseData1['revenueWeight'] ?? null,
                        'euroStatus' => $responseData1['euroStatus'] ?? null,
                        'dateOfLastV5CIssued' => $responseData1['dateOfLastV5CIssued'] ?? null,
                        'motExpiryDate' => $responseData1['motExpiryDate'] ?? null,
                        'wheelplan' => $responseData1['wheelplan'] ?? null,
                        'monthOfFirstRegistration' => $responseData1['monthOfFirstRegistration'] ?? null,

                        'cron_status' => 'Completed', // ✅ mark as Completed
                    ];

                    // ✅ Update record for the same registrationNumber & companyName
                    \App\Models\vehicleDetails::updateOrCreate(
                        [
                            'registrationNumber' => $vehicleData1['registrationNumber'],
                            'companyName' => $companyName,
                        ],
                        $vehicleData1
                    );

                    \Log::info('Vehicle updated successfully from API 1', [
                        'registrationNumber' => $vehicleData1['registrationNumber'],
                        'companyName' => $companyName,
                    ]);
                } else {
                    // ❌ Invalid response — mark as Failed
                    $vehicle->update(['cron_status' => 'Failed']);
                }
            } catch (\Exception $e) {
                // ❌ API call failed — mark as Failed
                $vehicle->update(['cron_status' => 'Failed']);
                \Log::error('API 1 update failed for '.$registrationNumber, ['error' => $e->getMessage()]);
            }
        }
    }

    private function updateAllData2($from = null, $to = null)
    {

        $apiKey2 = 'dmVdeybS8M99rT3PrZ6iw8VZvP5gR6la3wSy2Mld';
        $apiUrl2 = 'https://history.mot.api.gov.uk/v1/trade/vehicles/registration';

        $vehicleQuery = \App\Models\Vehicles::query();

        // ✅ CASE 1 → Manual Range (vehicleDetails IDs)
        if ($from !== null && $to !== null) {

            $vehicleIds = \App\Models\vehicleDetails::whereBetween('id', [$from, $to])
                ->pluck('vehicle_id')
                ->filter();

            if ($vehicleIds->isEmpty()) {
                \Log::error('No vehicle IDs found from vehicleDetails range');

                return;
            }

            $vehicleQuery->whereIn('id', $vehicleIds);
        }

        // ✅ CASE 2 → Cron / Full Pending Run
        $vehicleQuery->whereRaw("TRIM(LOWER(cron_status)) = 'pending'");

        if (! $vehicleQuery->exists()) {
            \Log::info('No Pending Vehicles Found');

            return;
        }

        $vehicles = $vehicleQuery->orderBy('id')
            ->limit(100)
            ->get();

        foreach ($vehicles as $vehicle) {
            $registrationNumber = $vehicle->registrations; // ✅ use Vehicles model field
            $companyName = $vehicle->companyName;

            try {
                $accessToken = $this->getDVSAAccessToken();

                if (! $accessToken) {
                    \Log::error('Failed to retrieve DVSA access token.');
                    $vehicle->update(['cron_status' => 'Failed']);

                    continue;
                }

                // API 2 call
                $response2 = Http::withHeaders([
                    'x-api-key' => $apiKey2,
                    'Authorization' => 'Bearer '.$accessToken,
                ])->get("$apiUrl2/$registrationNumber");

                $responseData2 = $response2->json();

                if (! empty($responseData2) && isset($responseData2['registration'])) {
                    $responseVehicle = $responseData2;

                    // ✅ Update Vehicles data
                    $vehicleData2 = [
                        'make' => $responseVehicle['make'] ?? null,
                        'model' => $responseVehicle['model'] ?? null,
                        'first_used_date' => $responseVehicle['firstUsedDate'] ?? null,
                        'fuel_type' => $responseVehicle['fuelType'] ?? null,
                        'primary_colour' => $responseVehicle['primaryColour'] ?? null,
                        'registration_date' => $responseVehicle['registrationDate'] ?? null,
                        'manufacture_date' => $responseVehicle['manufactureDate'] ?? null,
                        'engine_size' => $responseVehicle['engineSize'] ?? null,
                        'has_outstanding_recall' => $responseVehicle['hasOutstandingRecall'] ?? null,
                        'cron_status' => 'Completed', // ✅ Mark as Completed after success
                    ];

                    $vehicle->update($vehicleData2);

                    // Link with vehicleDetails model if exists
                    $vehicleDetails = \App\Models\vehicleDetails::where('registrationNumber', $registrationNumber)->first();
                    if ($vehicleDetails) {
                        $vehicleDetails->vehicle_id = $vehicle->id;
                        $vehicleDetails->save();
                    }

                    // Annual Test logic
                    if (isset($responseVehicle['motTests']) && is_array($responseVehicle['motTests']) && count($responseVehicle['motTests']) > 0) {

                        // ===== CASE 1 =====
                        $firstExpiryDate = $responseVehicle['motTests'][0]['expiryDate'] ?? null;

                        if ($firstExpiryDate) {
                            $vehicle->update(['annual_test_expiry_date' => $firstExpiryDate]);
                        }

                        $motTestsReversed = array_reverse($responseVehicle['motTests']);

                        foreach ($motTestsReversed as $test) {

                            $annualTest = [
                                'companyName' => $companyName ?? null,
                                'vehicle_id' => $vehicle->id,
                                'mot_test_number' => $test['motTestNumber'] ?? null,
                                'completed_date' => $test['completedDate'] ?? null,
                                'expiry_date' => $test['expiryDate'] ?? null,
                                'odometer_value' => $test['odometerValue'] ?? null,
                                'odometer_unit' => $test['odometerUnit'] ?? null,
                                'odometer_result_type' => $test['odometerResultType'] ?? null,
                                'test_result' => $test['testResult'] ?? null,
                                'data_source' => $test['dataSource'] ?? null,
                                'location' => $test['location'] ?? null,
                            ];

                            $annualTestModel = \App\Models\VehiclesAnnualTest::updateOrCreate(
                                [
                                    'vehicle_id' => $vehicle->id,
                                    'mot_test_number' => $test['motTestNumber'],
                                ],
                                $annualTest
                            );

                            // ✅ Defects ONLY here
                            if (! empty($test['defects']) && is_array($test['defects'])) {
                                foreach ($test['defects'] as $defect) {

                                    \App\Models\VehiclesAnnualTestDefect::updateOrCreate(
                                        [
                                            'annual_test_id' => $annualTestModel->id,
                                            'text' => $defect['text'] ?? null,
                                        ],
                                        [
                                            'companyName' => $companyName ?? null,
                                            'vehicle_id' => $vehicle->id,
                                            'annual_test_id' => $annualTestModel->id,
                                            'dangerous' => isset($defect['dangerous']) ? ($defect['dangerous'] ? 'true' : 'false') : null,
                                            'text' => $defect['text'] ?? null,
                                            'type' => $defect['type'] ?? null,
                                        ]
                                    );
                                }
                            }
                        }

                    } else {

                        // ===== CASE 2 =====
                        if (isset($responseVehicle['motTestDueDate'])) {

                            $vehicle->update([
                                'annual_test_expiry_date' => $responseVehicle['motTestDueDate'],
                            ]);

                        }
                    }

                    \Log::info('Vehicle updated successfully from API 2', [
                        'registrationNumber' => $registrationNumber,
                        'companyName' => $companyName,
                    ]);
                } else {
                    // ❌ Invalid response
                    $vehicle->update(['cron_status' => 'Failed']);
                    \Log::error("API 2 invalid response for: $registrationNumber");
                }
            } catch (\Exception $e) {
                $vehicle->update(['cron_status' => 'Failed']);
                \Log::error('API 2 update failed for '.$registrationNumber, [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function updateAllCronStatus()
    {
        try {
            // Update both models
            $vehiclesUpdated = \App\Models\Vehicles::query()->update(['cron_status' => 'Pending']);
            $vehicleDetailsUpdated = \App\Models\vehicleDetails::query()->update(['cron_status' => 'Pending']);

            return response()->json([
                'success' => true,
                'message' => 'Cron status updated successfully for all records.',
                'vehicles_updated' => $vehiclesUpdated,
                'vehicle_details_updated' => $vehicleDetailsUpdated,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating cron_status for Vehicles and VehicleDetails', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update cron_status: '.$e->getMessage(),
            ]);
        }
    }

    // public function updateAllData()
    //     {
    //         try {
    //             // Fetch all vehicles or adjust as needed
    //             $vehicles = \App\Models\vehicleDetails::all();

    //             foreach ($vehicles as $vehicle) {
    //                 // Example: API integration to update vehicle data based on registration number using POST request
    //                 $apiKey1 = 'CS8NuPQICs60CPb1Xb4cS6MG9KGIVuQ47ONeJXu6'; // API key for first API
    //                 $apiKey2 = '7gqmPTWnf02zZ5oidVhgRaCVLH2EAqUA1ytOdFSt'; // API key for second API
    //                 $apiUrl1 = 'https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles';
    //                 $apiUrl2 = 'https://beta.check-mot.service.gov.uk/trade/vehicles/annual-tests'; // URL for second API

    //                 $requestData1 = [
    //                     'registrationNumber' => $vehicle->registrationNumber,
    //                 ];
    //                 try {
    //                     $response1 = Http::withHeaders([
    //                         'x-api-key' => $apiKey1,
    //                     ])->post($apiUrl1, $requestData1);

    //                     $responseData1 = $response1->json();

    //                     // Handle response and update vehicle details from first API
    //                     if ($response1->successful()) {
    //                         $vehicle->taxStatus = $responseData1['taxStatus'] ?? null;
    //                         $vehicle->taxDueDate = isset($responseData1['taxDueDate']) ? date('d F Y', strtotime($responseData1['taxDueDate'])) : null;
    //                         $vehicle->motStatus = $responseData1['motStatus'] ?? null;
    //                         $vehicle->make = $responseData1['make'] ?? null;
    //                         $vehicle->yearOfManufacture = $responseData1['yearOfManufacture'] ?? null;
    //                         $vehicle->engineCapacity = $responseData1['engineCapacity'] ?? null;
    //                         $vehicle->co2Emissions = $responseData1['co2Emissions'] ?? null;
    //                         $vehicle->fuelType = $responseData1['fuelType'] ?? null;
    //                         $vehicle->markedForExport = $responseData1['markedForExport'] ?? null;
    //                         $vehicle->colour = $responseData1['colour'] ?? null;
    //                         $vehicle->typeApproval = $responseData1['typeApproval'] ?? null;
    //                         $vehicle->revenueWeight = $responseData1['revenueWeight'] ?? null;
    //                         $vehicle->euroStatus = $responseData1['euroStatus'] ?? null;
    //                         $vehicle->dateOfLastV5CIssued = $responseData1['dateOfLastV5CIssued'] ?? null;
    //                         $vehicle->motExpiryDate = $responseData1['motExpiryDate'] ?? null;
    //                         $vehicle->wheelplan = $responseData1['wheelplan'] ?? null;
    //                         $vehicle->monthOfFirstRegistration = $responseData1['monthOfFirstRegistration'] ?? null;
    //                         $vehicle->save();
    //                     } else {
    //                     $errorMessages[] = 'First API call failed for registration number: ' . $vehicle->registrationNumber;
    //                     continue; // Skip to the next vehicle
    //                 }

    //                     // Handle the second API call only if the first one was successful
    //                     $response2 = Http::withHeaders([
    //                         'x-api-key' => $apiKey2,
    //                     ])->get("$apiUrl2?registrations={$vehicle->registrationNumber}"); // Modify this to suit your second API call

    //                     $responseData2 = $response2->json();

    //                     if ($response2->successful() && ! empty($responseData2)) {
    //                         $responseVehicle = $responseData2[0];

    //                         // Parse relevant data from the response for API 2
    //                         $vehicleData2 = [
    //                             'make' => $responseVehicle['make'] ?? null,
    //                             'model' => $responseVehicle['model'] ?? null,
    //                             'vehicle_type' => $responseVehicle['vehicleType'] ?? null,
    //                             'registration_date' => $responseVehicle['registrationDate'] ?? null,
    //                             'annual_test_expiry_date' => $responseVehicle['annualTestExpiryDate'] ?? null,
    //                         ];

    //                         // Update or create data in the Vehicles model
    //                         $vehicleDetailsModel2 = \App\Models\Vehicles::updateOrCreate(
    //                             ['registrations' => $vehicle->registrationNumber],
    //                             $vehicleData2
    //                         );

    //                         // Link the updated vehicle record with the vehicle details
    //                         $vehicle->vehicle_id = $vehicleDetailsModel2->id;
    //                         $vehicle->save();

    //                         // Save annual tests data
    //                       if (isset($responseVehicle['annualTests']) && is_array($responseVehicle['annualTests'])) {
    //     foreach ($responseVehicle['annualTests'] as $test) {
    //         $annualTest = [
    //             'companyName' => $vehicle->companyName ?? null,
    //             'vehicle_id' => $vehicleDetailsModel2->id,
    //             'test_date' => isset($test['testDate']) ? $test['testDate'] : null,
    //             'test_type' => $test['testType'],
    //             'test_result' => $test['testResult'],
    //             'test_certificate_number' => $test['testCertificateNumber'],
    //             'expiry_date' => isset($test['expiryDate']) ? $test['expiryDate'] : null,
    //             'number_of_defects_test' => $test['numberOfDefectsAtTest'],
    //             'number_of_advisory_defects_test' => $test['numberOfAdvisoryDefectsAtTest'],
    //         ];

    //         // Save annual test
    //         $annualTestModel = \App\Models\VehiclesAnnualTest::updateOrCreate(
    //             ['vehicle_id' => $vehicleDetailsModel2->id], // Ensure correct identification
    //             $annualTest
    //         );

    //         // Save defects data
    //         if (isset($test['defects']) && is_array($test['defects'])) {
    //             foreach ($test['defects'] as $defect) {
    //                 $defectData = [
    //                     'vehicle_id' => $vehicle->id,
    //                     'annual_test_id' => $annualTestModel->id,
    //                     'failure_item_no' => $defect['failureItemNo'],
    //                     'failure_reason' => $defect['failureReason'],
    //                     'severity_code' => $defect['severityCode'],
    //                     'severity_description' => $defect['severityDescription'],
    //                 ];
    //                 \App\Models\VehiclesAnnualTestDefect::updateOrCreate($defectData);
    //             }
    //         }
    //     }
    // }
    //                      $successMessages[] = 'Vehicle data updated successfully for registration number: ';
    //                 } else {
    //                     $errorMessages[] = 'Second API call failed for registration number: ' . $vehicle->registrationNumber;
    //                     \Log::error('Failed to update vehicle data from second API for registration number: ' . $vehicle->registrationNumber);
    //                 }
    //             } catch (\Exception $e) {
    //                 $errorMessages[] = 'Exception for registration number ' . $vehicle->registrationNumber . ': ' . $e->getMessage();
    //                 \Log::error('Exception during vehicle data update for registration number ' . $vehicle->registrationNumber . ': ' . $e->getMessage());
    //                 continue; // Skip to the next vehicle
    //             }
    //         }

    //         if (!empty($errorMessages)) {
    //             return redirect()->back()->with('error', implode(', ', $errorMessages));
    //         }

    //         return redirect()->back()->with('success', implode(', ', $successMessages));
    //     } catch (\Exception $e) {
    //         \Log::error('Exception during vehicle data update: ' . $e->getMessage());
    //         return redirect()->back()->with('error', 'Failed to update vehicle data. Exception during vehicle data update: ' . $e->getMessage());
    //     }
    // }

    public function show($id)
    {
        // Get the logged-in user
        $user = \Auth::user();

        // Check if the user has permission to 'show vehicle'
        if ($user->can('show vehicle')) {
            // Check if the user is an admin or PTC manager (to allow access to all vehicles)
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                // For admin and PTC manager, fetch vehicle data from all companies
                $contract = vehicleDetails::find($id);

                $vehicle = \App\Models\Vehicles::find($contract->vehicle_id);
                $annualTests = \App\Models\VehiclesAnnualTest::where('vehicle_id', $contract->vehicle_id)->get();
                // $annualDefects = \App\Models\VehiclesAnnualTestDefect::where('vehicle_id', $contract->vehicle_id)->get();

                foreach ($annualTests as $test) {
                    $test->defects = \App\Models\VehiclesAnnualTestDefect::where('annual_test_id', $test->id)->get();
                }

                if ($contract->types->company_status === 'InActive') {
                    return redirect()->back()->with('error', __('Your company is not Active.'));
                }

                return view('contract.show', compact('contract', 'vehicle', 'annualTests'));

            } else {
                // For other roles, only fetch vehicles from the logged-in user's company
                $contract = vehicleDetails::where('companyName', $user->companyname)->find($id);

                if (! $contract) {
                    return redirect()->back()->with('error', __('Contract not found for your company.'));
                }

                if ($contract->types->company_status === 'InActive') {
                    return redirect()->back()->with('error', __('Your company is not Active.'));
                }

                $userVehicleGroupIds = is_array($user->vehicle_group_id)
        ? $user->vehicle_group_id
        : json_decode($user->vehicle_group_id, true);

    if (!in_array($contract->group_id, $userVehicleGroupIds ?? [])) {
        return redirect()->back()->with('error', __('You are not allowed to view this vehicle (group restriction).'));
    }

     $userDepotIds = is_array($user->depot_id)
        ? $user->depot_id
        : json_decode($user->depot_id, true);

    if (!in_array($contract->depot_id, $userDepotIds ?? [])) {
        return redirect()->back()->with('error', __('You are not allowed to view this vehicle (depot restriction).'));
    }

                $vehicle = \App\Models\Vehicles::find($contract->vehicle_id);
                $annualTests = \App\Models\VehiclesAnnualTest::where('vehicle_id', $contract->vehicle_id)->get();

                foreach ($annualTests as $test) {
                    $test->defects = \App\Models\VehiclesAnnualTestDefect::where('annual_test_id', $test->id)->get();
                }

                return view('contract.show', compact('contract', 'vehicle', 'annualTests'));
            }

        } else {
            // Permission check failed
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function trailershow($id)
    {
        // Get the logged-in user
        $user = \Auth::user();

        // Check if the user has permission to 'show vehicle'
        if ($user->can('show vehicle')) {
            // Check if the user is an admin or PTC manager (to allow access to all vehicles)
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                // For admin and PTC manager, fetch vehicle data from all companies
                $contract = vehicleDetails::find($id);

                $vehicle = \App\Models\Vehicles::find($contract->vehicle_id);
                $annualTests = \App\Models\VehiclesAnnualTest::where('vehicle_id', $contract->vehicle_id)->get();
                // $annualDefects = \App\Models\VehiclesAnnualTestDefect::where('vehicle_id', $contract->vehicle_id)->get();

                foreach ($annualTests as $test) {
                    $test->defects = \App\Models\VehiclesAnnualTestDefect::where('annual_test_id', $test->id)->get();
                }

                if ($contract->types->company_status === 'InActive') {
                    return redirect()->back()->with('error', __('Your company is not Active.'));
                }

                return view('contract.trailer.show', compact('contract', 'vehicle', 'annualTests'));

            } else {
                // For other roles, only fetch vehicles from the logged-in user's company
                $contract = vehicleDetails::where('companyName', $user->companyname)->find($id);

                if (! $contract) {
                    return redirect()->back()->with('error', __('Contract not found for your company.'));
                }

                if ($contract->types->company_status === 'InActive') {
                    return redirect()->back()->with('error', __('Your company is not Active.'));
                }

                  $userVehicleGroupIds = is_array($user->vehicle_group_id)
        ? $user->vehicle_group_id
        : json_decode($user->vehicle_group_id, true);

    if (!in_array($contract->group_id, $userVehicleGroupIds ?? [])) {
        return redirect()->back()->with('error', __('You are not allowed to view this vehicle (group restriction).'));
    }

     $userDepotIds = is_array($user->depot_id)
        ? $user->depot_id
        : json_decode($user->depot_id, true);

    if (!in_array($contract->depot_id, $userDepotIds ?? [])) {
        return redirect()->back()->with('error', __('You are not allowed to view this vehicle (depot restriction).'));
    }

                $vehicle = \App\Models\Vehicles::find($contract->vehicle_id);
                $annualTests = \App\Models\VehiclesAnnualTest::where('vehicle_id', $contract->vehicle_id)->get();

                foreach ($annualTests as $test) {
                    $test->defects = \App\Models\VehiclesAnnualTestDefect::where('annual_test_id', $test->id)->get();
                }

                return view('contract.trailer.show', compact('contract', 'vehicle', 'annualTests'));
            }

        } else {
            // Permission check failed
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function printContract($id)
    {
        $contract = vehicleDetails::findOrFail($id);
        $settings = Utility::settings();

        $vehicle = \App\Models\Vehicles::find($contract->vehicle_id);
        $annualTests = \App\Models\VehiclesAnnualTest::where('vehicle_id', $contract->vehicle_id)->get();
        // $annualDefects = \App\Models\VehiclesAnnualTestDefect::where('vehicle_id', $contract->vehicle_id)->get();

        foreach ($annualTests as $test) {
            $test->defects = \App\Models\VehiclesAnnualTestDefect::where('annual_test_id', $test->id)->get();
        }

        // $client   = $contract->clients->first();
        //Set your logo
        $logo = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo');
        $img = asset($logo.'/'.(isset($company_logo) && ! empty($company_logo) ? $company_logo : 'logo-dark.png'));

        if ($contract) {
            $color = '#'.$settings['invoice_color'];
            $font_color = Utility::getFontColor($color);

            return view('contract.preview', compact('contract', 'color', 'img', 'settings', 'font_color', 'vehicle', 'annualTests'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    //         public function edit(vehicleDetails $contract)
    //     {
    //         $user = \Auth::user();
    //                     if ($user->hasRole('company') || $user->hasRole('PTC manager')) {

    //         $contractTypes = CompanyDetails::where('created_by', '=', \Auth::user()->creatorId())->orderBy('name', 'asc')->where('company_status', 'Active')->get()->pluck('name', 'id');
    //                     } else {
    //                                 $contractTypes = CompanyDetails::where('id', '=', $user->companyname)->where('company_status', 'Active')->orderBy('name', 'asc')->get()->pluck('name', 'id');
    // }

    //         // Initialize variables for archive reason and text
    //         $archiveReason = null;
    //         $archiveOtherText = null;

    //         // Check if the vehicle_status contains "Archive"
    //         if (strpos($contract->vehicle_status, 'Archive') !== false) {
    //             // Split the vehicle_status to get the reason
    //             preg_match('/Archive\s*\((.*)\)/', $contract->vehicle_status, $matches);
    //             if (isset($matches[1])) {
    //                 $reason = trim($matches[1]); // Get the text within parentheses
    //                 // Check if the reason matches any predefined reasons
    //                 $validReasons = ['Sold', 'Scrapped', 'Write off', 'In repair'];
    //                 if (in_array($reason, $validReasons)) {
    //                     $archiveReason = $reason; // Set the valid reason
    //                 } else {
    //                     $archiveReason = 'Other'; // Otherwise, default to 'Other'
    //                     $archiveOtherText = $reason; // Store the other text for editing
    //                 }
    //             }
    //             $contract->vehicle_status = 'Archive'; // Set the dropdown value to 'Archive'
    //         }

    //         return view('contract.edit', compact('contractTypes', 'contract', 'archiveReason', 'archiveOtherText'));
    //     }

    public function edit(vehicleDetails $contract)
    {
        $user = \Auth::user();
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {

            $contractTypes = CompanyDetails::where('created_by', '=', \Auth::user()->creatorId())->orderBy('name', 'asc')->where('company_status', 'Active')->get()->pluck('name', 'id');
        } else {
            $contractTypes = CompanyDetails::where('id', '=', $user->companyname)->where('company_status', 'Active')->orderBy('name', 'asc')->get()->pluck('name', 'id');
        }

           /* -----------------------------------------------------
           CHECK FLEET PLANNER LOGIC FOR MULTIPLE TYPES
           ----------------------------------------------------- */

        $plannerTypes = [
            'Tacho Calibration',
            'DVS/PSS Permit Expiry',
            'Insurance',
            'PMI Due', // ⭐ NEW
        ];

        $fieldMap = [
            'Tacho Calibration' => 'tacho_calibration',
            'DVS/PSS Permit Expiry' => 'dvs_pss_permit_expiry',
            'Insurance' => 'insurance',
            'PMI Due' => 'PMI_due', // ⭐ NEW
        ];

        $editFlags = [];
        // \Log::info("========== Fleet Planner Multi-Type Debug ==========");

        foreach ($plannerTypes as $type) {

            $fieldName = $fieldMap[$type];
            $contractValue = $contract->{$fieldName};

            //  \Log::info("Checking Planner Type: {$type}");
            //    \Log::info("Vehicle Field ({$fieldName}): " . ($contractValue ?? 'NULL'));

            $canEdit = false;

            if ($contractValue) {

                // 1) Fleet by vehicle + planner type
                $fleet = \App\Models\Fleet::where('vehicle_id', $contract->id)
                    ->where('planner_type', $type)
                    ->orderBy('id', 'DESC')
                    ->first();

                // \Log::info("Fleet Result for {$type}:", [
                //     'exists' => $fleet ? true : false,
                //     'fleet_id' => $fleet->id ?? null,
                // ]);

                if ($fleet) {

                    // 2) Latest reminder for this planner
                    $latestDate = \App\Models\FleetPlannerReminder::where('fleet_planner_id', $fleet->id)
                        ->orderBy('next_reminder_date', 'desc')->where('status', 'Completed')
                        ->value('next_reminder_date');

                    //   \Log::info("Latest COMPLETED Reminder Date for {$type}: " . ($latestDate ?? 'NULL'));

                    if ($latestDate &&
                        \Carbon\Carbon::parse($latestDate)->isSameDay($contractValue)
                    ) {
                        $canEdit = true;
                    }
                }
            }

            $editFlags[$type] = $canEdit;

            // \Log::info("Can Edit {$type}? => " . ($canEdit ? 'YES' : 'NO'));
            //  \Log::info("-----------------------------------------------------");
        }

        // \Log::info("========== Fleet Planner Multi-Type Debug END ==========");

        // Initialize variables for archive reason and text
        $archiveReason = null;
        $archiveOtherText = null;

        // Check if the vehicle_status contains "Archive"
        if (strpos($contract->vehicle_status, 'Archive') !== false) {
            // Split the vehicle_status to get the reason
            preg_match('/Archive\s*\((.*)\)/', $contract->vehicle_status, $matches);
            if (isset($matches[1])) {
                $reason = trim($matches[1]); // Get the text within parentheses
                // Check if the reason matches any predefined reasons
                $validReasons = ['Sold', 'Scrapped', 'Write off', 'In repair'];
                if (in_array($reason, $validReasons)) {
                    $archiveReason = $reason; // Set the valid reason
                } else {
                    $archiveReason = 'Other'; // Otherwise, default to 'Other'
                    $archiveOtherText = $reason; // Store the other text for editing
                }
            }
            $contract->vehicle_status = 'Archive'; // Set the dropdown value to 'Archive'
        }

        return view('contract.edit', compact('contractTypes', 'contract', 'archiveReason', 'archiveOtherText', 'editFlags'));
    }

    // 24/12/2025
    // public function update(Request $request, vehicleDetails $contract)
    // {
    //     if (\Auth::user()->can('edit vehicle')) {
    //         $rules = [
    //                             'vehicle_nick_name' => 'nullable',
    //             'tacho_calibration' => 'nullable',
    //             'dvs_pss_permit_expiry' => 'nullable',
    //             'insurance_type' => 'nullable|array',
    //             'insurance_type.*' => 'nullable|string|in:Motor Insurance',
    //             'insurance' => 'nullable',
    //             'PMI_intervals' => 'nullable|in:1,2,3,4,5,6,7,8,9,10',
    //             'PMI_due' => 'nullable',
    //             'date_of_inspection' => 'nullable',
    //             'odometer_reading' => 'nullable',

    //              'vehicle_status' => 'nullable', // Add this line
    //         'archive_reason' => 'nullable', // Add this line
    //         'archive_other_text' => 'nullable', // Add this line
    //         'companyName' => 'required',
    //         'group_id' => 'nullable',
    //         'depot_id' => 'nullable',
    //         'taxDueDate' => 'nullable|date_format:Y-m-d',
    //             'annual_test_expiry_date' => 'nullable|date', // New validation rule for mot_expiry
    //         ];

    //         $validator = \Validator::make($request->all(), $rules);

    //         if ($validator->fails()) {
    //             $messages = $validator->getMessageBag();

    //             return redirect()->route('contract.index')->with('error', $messages->first());
    //         }

    //             $isArchived = Str::startsWith($request->vehicle_status, 'Archive');

    // // Check for blocked updates on archived vehicles
    // if ($isArchived) {
    //     $blockedFields = ['tacho_calibration', 'dvs_pss_permit_expiry', 'insurance','PMI_due','date_of_inspection','fridge_service','fridge_calibration','tail_lift','loler'];
    //     $attemptedFields = [];

    //     foreach ($blockedFields as $field) {
    //         if ($request->filled($field) && $request->$field !== $contract->$field) {
    //             $attemptedFields[] = $field;
    //         }
    //     }

    //     if (!empty($attemptedFields)) {
    //         return redirect()->back()->with('error', 'This vehicle is currently archived. Please update the vehicle status before modifying reminder dates.');
    //     }
    // }

    // if ($isArchived) {
    //   // Step 1: Get IDs of Fleet entries for this vehicle
    //   $fleetIds = \App\Models\Fleet::where('vehicle_id', $contract->id)->pluck('id');

    //   if ($fleetIds->isNotEmpty()) {
    //       // Step 2: Delete only Pending FleetPlannerReminders linked to those Fleets
    //       \App\Models\FleetPlannerReminder::whereIn('fleet_planner_id', $fleetIds)
    //           ->where('status', 'Pending')
    //           ->delete();
    //   }
    // }

    //         $contract->vehicle_nick_name = $request->vehicle_nick_name ?? $contract->registrationNumber;

    //             if (!$isArchived) {
    //         $contract->tacho_calibration = $request->tacho_calibration;
    //         $contract->dvs_pss_permit_expiry = $request->dvs_pss_permit_expiry;

    //          $selectedInsurance = $request->insurance_type ?? [];
    //     $contract->insurance_type = json_encode($selectedInsurance);

    //         $contract->insurance = $request->insurance;
    //         $contract->PMI_intervals = $request->PMI_intervals;
    //         $contract->PMI_due = $request->PMI_due;
    //         $contract->date_of_inspection = $request->date_of_inspection;

    //         $contract->brake_test_due = $request->PMI_due ? \Carbon\Carbon::parse($request->PMI_due)->format('Y-m-d') : null;

    //             }

    //          $selectedInsurance = $request->insurance_type ?? [];
    //     $contract->insurance_type = json_encode($selectedInsurance);

    //         $contract->odometer_reading = $request->odometer_reading;
    //         $contract->brake_test_due = $request->PMI_due ? \Carbon\Carbon::parse($request->PMI_due)->format('Y-m-d') : null;
    //         $contract->companyName = $request->companyName;
    //         $contract->group_id = $request->group_id;
    //         $contract->depot_id = $request->depot_id;
    //         $contract->created_by = \Auth::user()->id;
    //          $contract->taxDueDate = $request->filled('taxDueDate')
    //         ? \Carbon\Carbon::createFromFormat('Y-m-d', $request->taxDueDate)->format('d F Y')
    //         : null;

    //         if ($request->vehicle_status == 'Archive') {
    //         $archiveReason = $request->archive_reason;
    //         $archiveOtherText = $request->archive_other_text;

    //         if ($archiveReason == 'Other' && $archiveOtherText) {
    //             $contract->vehicle_status = "Archive ($archiveOtherText)"; // Combine other text
    //         } elseif ($archiveReason) {
    //             $contract->vehicle_status = "Archive ($archiveReason)"; // Use selected reason
    //         } else {
    //             $contract->vehicle_status = "Archive"; // Default to Archive if no reason provided
    //         }
    //     } else {
    //         $contract->vehicle_status = $request->vehicle_status; // Regular status
    //     }

    //         $contract->save();

    //       if ($contract->vehicle) {
    //             $contract->vehicle->companyName = $request->companyName;
    //             // New: Updating mot_expiry in the related vehicle
    //             $contract->vehicle->annual_test_expiry_date = $request->annual_test_expiry_date;
    //             $contract->vehicle->save();
    //         }

    // // PMI Due Reminder
    // if (!$isArchived && $request->filled('PMI_due') && $request->filled('date_of_inspection')) {
    // $fleetPMIData = [
    //     'planner_type' => 'PMI Due',
    //     'vehicle_id' => $contract->id,
    // ];

    // $fleetPMI = \App\Models\Fleet::updateOrCreate(
    //     $fleetPMIData,
    //     [
    //         'start_date' => \Carbon\Carbon::parse($request->input('PMI_due'))->format('Y-m-d'),
    //         'end_date' => \Carbon\Carbon::parse($request->input('PMI_due'))->addYear()->toDateString(),
    //         'company_id' => $request->input('companyName'),
    //         'every' => $request->input('PMI_intervals'),
    //         'interval' => 'Week',
    //         'created_by' => \Auth::user()->id,
    //     ]
    // );

    // if ($fleetPMI->wasRecentlyCreated) {
    //     $this->generateReminders($fleetPMI);
    // }
    // }

    // // Brake Test Due Reminder
    // if (!$isArchived && $request->filled('PMI_due') && $request->filled('date_of_inspection')) {
    // $fleetBrakeData = [
    //     'planner_type' => 'Brake Test Due',
    //     'vehicle_id' => $contract->id,
    // ];

    // $fleetBrake = \App\Models\Fleet::updateOrCreate(
    //     $fleetBrakeData,
    //     [
    //         'start_date' => \Carbon\Carbon::parse($request->input('PMI_due'))->format('Y-m-d'),
    //         'end_date' => \Carbon\Carbon::parse($request->input('PMI_due'))->addYear()->toDateString(),
    //         'company_id' => $request->input('companyName'),
    //         'every' => $request->input('PMI_intervals'),
    //         'interval' => 'Week',
    //         'created_by' => \Auth::user()->id,
    //     ]
    // );

    // if ($fleetBrake->wasRecentlyCreated) {
    //     $this->generateReminders($fleetBrake);
    // }
    // }

    // // if (!$isArchived && $request->filled('fridge_service')) {
    // // $fleetFridgeData = [
    // //     'planner_type' => 'Fridge Service',
    // //     'vehicle_id' => $contract->id,
    // // ];

    // // $fleetFridge = \App\Models\Fleet::updateOrCreate(
    // //     $fleetFridgeData,
    // //     [
    // //         'start_date' => $request->input('fridge_service'),
    // //         'end_date' => \Carbon\Carbon::parse($request->input('fridge_service'))->addYear()->toDateString(),
    // //         'company_id' => $request->input('companyName'),
    // //         'every' => $request->input('fridge_service_interval'),
    // //         'interval' => 'Day',
    // //         'created_by' => \Auth::user()->id,
    // //     ]
    // // );

    // // if ($fleetFridge->wasRecentlyCreated) {
    // //     $this->generateReminders($fleetFridge);
    // // }
    // // }

    // // if (!$isArchived && $request->filled('fridge_calibration')) {
    // // $fleetFridgeCalibrationData = [
    // //     'planner_type' => 'Fridge Calibration',
    // //     'vehicle_id' => $contract->id,
    // // ];

    // // $fleetFridgeCalibration = \App\Models\Fleet::updateOrCreate(
    // //     $fleetFridgeCalibrationData,
    // //     [
    // //         'start_date' => $request->input('fridge_calibration'),
    // //         'end_date' => \Carbon\Carbon::parse($request->input('fridge_calibration'))->addYear()->toDateString(),
    // //         'company_id' => $request->input('companyName'),
    // //         'every' => $request->input('fridge_calibration_interval'),
    // //         'interval' => 'Day',
    // //         'created_by' => \Auth::user()->id,
    // //     ]
    // // );

    // // if ($fleetFridgeCalibration->wasRecentlyCreated) {
    // //     $this->generateReminders($fleetFridgeCalibration);
    // // }
    // // }

    // // if (!$isArchived && $request->filled('tail_lift')) {
    // // $fleetTailLiftData = [
    // //     'planner_type' => 'Tail lift',
    // //     'vehicle_id' => $contract->id,
    // // ];

    // // $fleetTailLift = \App\Models\Fleet::updateOrCreate(
    // //     $fleetTailLiftData,
    // //     [
    // //         'start_date' => $request->input('tail_lift'),
    // //         'end_date' => \Carbon\Carbon::parse($request->input('tail_lift'))->addYear()->toDateString(),
    // //         'company_id' => $request->input('companyName'),
    // //         'every' => $request->input('tail_lift_interval'),
    // //         'interval' => 'Day',
    // //         'created_by' => \Auth::user()->id,
    // //     ]
    // // );

    // // if ($fleetTailLift->wasRecentlyCreated) {
    // //     $this->generateReminders($fleetTailLift);
    // // }
    // // }

    // // if (!$isArchived && $request->filled('loler')) {
    // //     $fleetLolerData = [
    // //         'planner_type' => 'Loler',
    // //         'vehicle_id' => $contract->id,
    // //     ];

    // //     $fleetLoler = \App\Models\Fleet::updateOrCreate(
    // //         $fleetLolerData,
    // //         [
    // //             'start_date' => $request->input('loler'),
    // //             'end_date' => \Carbon\Carbon::parse($request->input('loler'))->addYear()->toDateString(),
    // //             'company_id' => $request->input('companyName'),
    // //             'every' => $request->input('loler_interval'),
    // //             'interval' => 'Day',
    // //             'created_by' => \Auth::user()->id,
    // //         ]
    // //     );

    // //     if ($fleetLoler->wasRecentlyCreated) {
    // //         $this->generateReminders($fleetLoler);
    // // }
    // // }

    // // Tacho Calibration Reminder
    // if (!$isArchived && $request->filled('tacho_calibration')) {
    // $fleetTachoData = [
    //     'planner_type' => 'Tacho Calibration',
    //     'vehicle_id' => $contract->id,
    // ];

    // $fleetTacho = \App\Models\Fleet::updateOrCreate(
    //     $fleetTachoData,
    //     [
    //         'start_date' => $request->input('tacho_calibration'),
    //         'end_date' => \Carbon\Carbon::parse($request->input('tacho_calibration'))->addYears(2)->toDateString(),
    //         'company_id' => $request->input('companyName'),
    //         'every' => 24,
    //         'interval' => 'Month',
    //         'created_by' => \Auth::user()->id,
    //     ]
    // );

    // if ($fleetTacho->wasRecentlyCreated) {
    //     $this->generateReminders($fleetTacho);
    // }
    // }

    // // DVS/PSS Permit Expiry Reminder
    // if (!$isArchived && $request->filled('dvs_pss_permit_expiry')) {
    // $fleetDVSPSS = [
    //     'planner_type' => 'DVS/PSS Permit Expiry',
    //     'vehicle_id' => $contract->id,
    // ];

    // $fleetDVS = \App\Models\Fleet::updateOrCreate(
    //     $fleetDVSPSS,
    //     [
    //         'start_date' => $request->input('dvs_pss_permit_expiry'),
    //         'end_date' => \Carbon\Carbon::parse($request->input('dvs_pss_permit_expiry'))->addYears(5)->toDateString(),
    //         'company_id' => $request->input('companyName'),
    //         'every' => 60,
    //         'interval' => 'Month',
    //         'created_by' => \Auth::user()->id,
    //     ]
    // );

    // if ($fleetDVS->wasRecentlyCreated) {
    //     $this->generateReminders($fleetDVS);
    // }
    // }

    // // Insurance Reminder
    // if (!$isArchived && $request->filled('insurance')) {
    // $fleetInsurance = [
    //     'planner_type' => 'Insurance',
    //     'vehicle_id' => $contract->id,
    // ];

    // $fleetIns = \App\Models\Fleet::updateOrCreate(
    //     $fleetInsurance,
    //     [
    //         'start_date' => $request->input('insurance'),
    //         'end_date' => \Carbon\Carbon::parse($request->input('insurance'))->addYears(1)->toDateString(),
    //         'company_id' => $request->input('companyName'),
    //         'every' => 12,
    //         'interval' => 'Month',
    //         'created_by' => \Auth::user()->id,
    //     ]
    // );

    // if ($fleetIns->wasRecentlyCreated) {
    //     $this->generateReminders($fleetIns);
    // }
    // }

    //         return redirect()->back()->with('success', __('Vehicle  successfully updated.'));
    //     } else {
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    public function update(Request $request, vehicleDetails $contract)
    {
        if (! \Auth::user()->can('edit vehicle')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $rules = [
            'vehicle_nick_name' => 'nullable',
            'tacho_calibration' => 'nullable',
            'dvs_pss_permit_expiry' => 'nullable',
            'insurance_type' => 'nullable|array',
            'insurance_type.*' => 'nullable|string|in:Motor Insurance',
            'insurance' => 'nullable',
            'PMI_intervals' => 'nullable|in:1,2,3,4,5,6,7,8,9,10',
            'PMI_due' => 'nullable',
            'date_of_inspection' => 'nullable',
            'odometer_reading' => 'nullable',
            'vehicle_status' => 'nullable',
            'archive_reason' => 'nullable',
            'archive_other_text' => 'nullable',
            'companyName' => 'required',
            'group_id' => 'nullable',
            'depot_id' => 'nullable',
            'taxDueDate' => 'nullable|date_format:Y-m-d',
            'annual_test_expiry_date' => 'nullable|date',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route('contract.index')->with('error', $validator->errors()->first());
        }

        $isArchived = Str::startsWith($request->vehicle_status, 'Archive');

        // Block reminder changes on archived vehicles
        if ($isArchived) {
            $blockedFields = [
                'tacho_calibration', 'dvs_pss_permit_expiry', 'insurance',
                'PMI_due', 'date_of_inspection', 'fridge_service',
                'fridge_calibration', 'tail_lift', 'loler',
            ];

            foreach ($blockedFields as $field) {
                if ($request->filled($field) && $request->$field !== $contract->$field) {
                    return redirect()->back()->with('error', 'This vehicle is archived. Update status before modifying reminder dates.');
                }
            }
        }

        // Delete pending reminders if archived
        if ($isArchived) {
            $fleetIds = \App\Models\Fleet::where('vehicle_id', $contract->id)->pluck('id');
            if ($fleetIds->isNotEmpty()) {
                \App\Models\FleetPlannerReminder::whereIn('fleet_planner_id', $fleetIds)
                    ->where('status', 'Pending')
                    ->delete();
            }
        }

        // 🟩 STORE ORIGINAL VALUES BEFORE SAVE (IMPORTANT)
        $originalPMI = $contract->PMI_due;
        $originalInspect = $contract->date_of_inspection;
        $oldTacho = $contract->tacho_calibration;
        $oldDvsPss = $contract->dvs_pss_permit_expiry;
        $oldInsurance = $contract->insurance;

        // -----------------------------
        // UPDATE VEHICLE DETAILS
        // -----------------------------
        $contract->vehicle_nick_name = $request->vehicle_nick_name ?? $contract->registrationNumber;

        if (! $isArchived) {
            $contract->tacho_calibration = $request->tacho_calibration;
            $contract->dvs_pss_permit_expiry = $request->dvs_pss_permit_expiry;
            $contract->insurance = $request->insurance;
            $contract->PMI_intervals = $request->PMI_intervals;
            $contract->PMI_due = $request->PMI_due;
            $contract->date_of_inspection = $request->date_of_inspection;
            $contract->brake_test_due = $request->PMI_due ? Carbon::parse($request->PMI_due)->format('Y-m-d') : null;
        }

        $contract->insurance_type = json_encode($request->insurance_type ?? []);
        $contract->odometer_reading = $request->odometer_reading;
        $contract->companyName = $request->companyName;
        $contract->group_id = $request->group_id;
        $contract->depot_id = $request->depot_id;
        $contract->created_by = \Auth::user()->id;

        $contract->taxDueDate = $request->filled('taxDueDate')
            ? Carbon::createFromFormat('Y-m-d', $request->taxDueDate)->format('d F Y')
            : null;

        // Archive status handling
        if ($request->vehicle_status == 'Archive') {
            if ($request->archive_reason == 'Other' && $request->archive_other_text) {
                $contract->vehicle_status = "Archive ({$request->archive_other_text})";
            } elseif ($request->archive_reason) {
                $contract->vehicle_status = "Archive ({$request->archive_reason})";
            } else {
                $contract->vehicle_status = 'Archive';
            }
        } else {
            $contract->vehicle_status = $request->vehicle_status;
        }

        $contract->save();

        // Update related vehicle
        if ($contract->vehicle) {
            $contract->vehicle->companyName = $request->companyName;
            $contract->vehicle->annual_test_expiry_date = $request->annual_test_expiry_date;
            $contract->vehicle->save();
        }

        // 🟩 NEW VALUES AFTER SAVE
        $newPMI = $contract->PMI_due;
        $newInspect = $contract->date_of_inspection;

        // 🟩 FIXED CHANGE DETECTION
        $shouldCreateReminder =
            ($newPMI !== $originalPMI) ||
            ($newInspect !== $originalInspect);

        // ============================================================
        // CREATE REMINDERS — ONLY WHEN PMI OR INSPECTION CHANGES
        // ============================================================
        if (
            ! $isArchived &&
            $request->filled('PMI_due') &&
            $request->filled('date_of_inspection') &&
            $shouldCreateReminder
        ) {
            // PMI Due
            $fleetPMI = \App\Models\Fleet::create([
                'planner_type' => 'PMI Due',
                'vehicle_id' => $contract->id,
                'start_date' => Carbon::parse($newPMI)->format('Y-m-d'),
                'end_date' => Carbon::parse($newPMI)->addYear()->toDateString(),
                'company_id' => $request->companyName,
                'every' => $request->PMI_intervals,
                'interval' => 'Week',
                'created_by' => \Auth::id(),
            ]);
            $this->generateReminders($fleetPMI);

            // Brake Test Due
            $fleetBrake = \App\Models\Fleet::create([
                'planner_type' => 'Brake Test Due',
                'vehicle_id' => $contract->id,
                'start_date' => Carbon::parse($newPMI)->format('Y-m-d'),
                'end_date' => Carbon::parse($newPMI)->addYear()->toDateString(),
                'company_id' => $request->companyName,
                'every' => $request->PMI_intervals,
                'interval' => 'Week',
                'created_by' => \Auth::id(),
            ]);
            $this->generateReminders($fleetBrake);
        }

        // ======================
        // OTHER REMINDERS NORMAL
        // ======================

        // ----------------------
        // Tacho Calibration
        // ----------------------
        if (! $isArchived && $request->filled('tacho_calibration')) {

            $newDate = Carbon::parse($request->tacho_calibration)->format('Y-m-d');
            $oldDate = $oldTacho ? Carbon::parse($oldTacho)->format('Y-m-d') : null;

            if ($newDate !== $oldDate) {

                $fleetTacho = \App\Models\Fleet::create([
                    'planner_type' => 'Tacho Calibration',
                    'vehicle_id' => $contract->id,
                    'start_date' => $newDate,
                    'end_date' => Carbon::parse($newDate)->addYears(2)->toDateString(),
                    'company_id' => $request->companyName,
                    'every' => 24,
                    'interval' => 'Month',
                    'created_by' => \Auth::id(),
                ]);

                if ($fleetTacho->wasRecentlyCreated) {
                    $this->generateReminders($fleetTacho);
                }
            }
        }

        // ----------------------
        // DVS/PSS Permit Expiry
        // ----------------------
        if (! $isArchived && $request->filled('dvs_pss_permit_expiry')) {

            $newDate = Carbon::parse($request->dvs_pss_permit_expiry)->format('Y-m-d');
            $oldDate = $oldDvsPss ? Carbon::parse($oldDvsPss)->format('Y-m-d') : null;

            if ($newDate !== $oldDate) {

                $fleetDVS = \App\Models\Fleet::create([
                    'planner_type' => 'DVS/PSS Permit Expiry',
                    'vehicle_id' => $contract->id,
                    'start_date' => $newDate,
                    'end_date' => Carbon::parse($newDate)->addYears(5)->toDateString(),
                    'company_id' => $request->companyName,
                    'every' => 60,
                    'interval' => 'Month',
                    'created_by' => \Auth::id(),
                ]);

                if ($fleetDVS->wasRecentlyCreated) {
                    $this->generateReminders($fleetDVS);
                }
            }
        }

        // ----------------------
        // Insurance
        // ----------------------
        if (! $isArchived && $request->filled('insurance')) {

            $newDate = Carbon::parse($request->insurance)->format('Y-m-d');
            $oldDate = $oldInsurance ? Carbon::parse($oldInsurance)->format('Y-m-d') : null;

            if ($newDate !== $oldDate) {

                $fleetIns = \App\Models\Fleet::create([
                    'planner_type' => 'Insurance',
                    'vehicle_id' => $contract->id,
                    'start_date' => $newDate,
                    'end_date' => Carbon::parse($newDate)->addYear()->toDateString(),
                    'company_id' => $request->companyName,
                    'every' => 12,
                    'interval' => 'Month',
                    'created_by' => \Auth::id(),
                ]);

                if ($fleetIns->wasRecentlyCreated) {
                    $this->generateReminders($fleetIns);
                }
            }
        }

        return redirect()->back()->with('success', __('Vehicle successfully updated.'));
    }

    //     public function traileredit(vehicleDetails $contract)
    //     {
    //           $user = \Auth::user();
    //                     if ($user->hasRole('company') || $user->hasRole('PTC manager')) {

    //           $contractTypes = CompanyDetails::where('created_by', '=', \Auth::user()->creatorId())->orderBy('name', 'asc')->where('company_status', 'Active')->get()->pluck('name', 'id');
    //                     } else {
    //                                 $contractTypes = CompanyDetails::where('created_by', '=', \Auth::user()->creatorId())->where('id', '=', $user->companyname)->where('company_status', 'Active')->orderBy('name', 'asc')->get()->pluck('name', 'id');
    //          }

    //         // Initialize variables for archive reason and text
    //         $archiveReason = null;
    //         $archiveOtherText = null;

    //         // Check if the vehicle_status contains "Archive"
    //         if (strpos($contract->vehicle_status, 'Archive') !== false) {
    //             // Split the vehicle_status to get the reason
    //             preg_match('/Archive\s*\((.*)\)/', $contract->vehicle_status, $matches);
    //             if (isset($matches[1])) {
    //                 $reason = trim($matches[1]); // Get the text within parentheses
    //                 // Check if the reason matches any predefined reasons
    //                 $validReasons = ['Sold', 'Scrapped', 'Write off', 'In repair'];
    //                 if (in_array($reason, $validReasons)) {
    //                     $archiveReason = $reason; // Set the valid reason
    //                 } else {
    //                     $archiveReason = 'Other'; // Otherwise, default to 'Other'
    //                     $archiveOtherText = $reason; // Store the other text for editing
    //                 }
    //             }
    //             $contract->vehicle_status = 'Archive'; // Set the dropdown value to 'Archive'
    //         }

    //         return view('contract.trailer.edit', compact('contractTypes', 'contract', 'archiveReason', 'archiveOtherText'));
    //     }

    //     public function trailerupdate(Request $request, vehicleDetails $contract)
    //     {
    //         if (\Auth::user()->can('edit vehicle')) {
    //             $rules = [
    //                                 'vehicle_nick_name' => 'nullable',
    //                 'insurance_type' => 'nullable|array',
    //                 'insurance_type.*' => 'nullable|string|in:Motor Insurance',
    //                 'insurance' => 'nullable',
    //                 'PMI_intervals' => 'nullable|in:1,2,3,4,5,6,7,8,9,10',
    //                 'PMI_due' => 'nullable',
    //                 'date_of_inspection' => 'nullable',
    //                 'odometer_reading' => 'nullable',

    //                  'vehicle_status' => 'nullable', // Add this line
    //             'archive_reason' => 'nullable', // Add this line
    //             'archive_other_text' => 'nullable', // Add this line
    //             'companyName' => 'required',
    //             'group_id' => 'nullable',
    //             'depot_id' => 'nullable',
    //                 'annual_test_expiry_date' => 'nullable|date', // New validation rule for mot_expiry
    //             ];

    //             $validator = \Validator::make($request->all(), $rules);

    //             if ($validator->fails()) {
    //                 $messages = $validator->getMessageBag();

    //                 return redirect()->route('contract.index')->with('error', $messages->first());
    //             }

    //             $isArchived = Str::startsWith($request->vehicle_status, 'Archive');

    // // Check for blocked updates on archived vehicles
    // if ($isArchived) {
    //     $blockedFields = ['tacho_calibration', 'dvs_pss_permit_expiry', 'insurance','PMI_due','date_of_inspection','fridge_service','fridge_calibration','tail_lift','loler'];
    //     $attemptedFields = [];

    //     foreach ($blockedFields as $field) {
    //         if ($request->filled($field) && $request->$field !== $contract->$field) {
    //             $attemptedFields[] = $field;
    //         }
    //     }

    //     if (!empty($attemptedFields)) {
    //         return redirect()->back()->with('error', 'This vehicle is currently archived. Please update the vehicle status before modifying reminder dates.');
    //     }
    // }

    // if ($isArchived) {
    //   // Step 1: Get IDs of Fleet entries for this vehicle
    //   $fleetIds = \App\Models\Fleet::where('vehicle_id', $contract->id)->pluck('id');

    //   if ($fleetIds->isNotEmpty()) {
    //       // Step 2: Delete only Pending FleetPlannerReminders linked to those Fleets
    //       \App\Models\FleetPlannerReminder::whereIn('fleet_planner_id', $fleetIds)
    //           ->where('status', 'Pending')
    //           ->delete();
    //   }
    // }

    //             $contract->vehicle_nick_name = $request->vehicle_nick_name ?? $contract->registrationNumber;

    //              $selectedInsurance = $request->insurance_type ?? [];
    //         $contract->insurance_type = json_encode($selectedInsurance);
    //         if (!$isArchived) {

    //             $contract->insurance = $request->insurance;
    //             $contract->PMI_intervals = $request->PMI_intervals;
    //             $contract->PMI_due = $request->PMI_due;
    //             $contract->date_of_inspection = $request->date_of_inspection;
    //             $contract->brake_test_due = $request->PMI_due ? \Carbon\Carbon::parse($request->PMI_due)->format('Y-m-d') : null;
    //         }
    //             $contract->odometer_reading = $request->odometer_reading;
    //             $contract->companyName = $request->companyName;
    //             $contract->group_id = $request->group_id;
    //             $contract->depot_id = $request->depot_id;
    //             $contract->created_by = \Auth::user()->id;

    //             if ($request->vehicle_status == 'Archive') {
    //             $archiveReason = $request->archive_reason;
    //             $archiveOtherText = $request->archive_other_text;

    //             if ($archiveReason == 'Other' && $archiveOtherText) {
    //                 $contract->vehicle_status = "Archive ($archiveOtherText)"; // Combine other text
    //             } elseif ($archiveReason) {
    //                 $contract->vehicle_status = "Archive ($archiveReason)"; // Use selected reason
    //             } else {
    //                 $contract->vehicle_status = "Archive"; // Default to Archive if no reason provided
    //             }
    //         } else {
    //             $contract->vehicle_status = $request->vehicle_status; // Regular status
    //         }

    //             $contract->save();

    //           if ($contract->vehicle) {
    //                 $contract->vehicle->companyName = $request->companyName;
    //                 // New: Updating mot_expiry in the related vehicle
    //                 $contract->vehicle->annual_test_expiry_date = $request->annual_test_expiry_date;
    //                 $contract->vehicle->save();
    //             }

    // // PMI Due Reminder
    // if (!$isArchived && $request->filled('PMI_due') && $request->filled('date_of_inspection')) {
    //     $fleetPMIData = [
    //         'planner_type' => 'PMI Due',
    //         'vehicle_id' => $contract->id,
    //     ];

    //     $fleetPMI = \App\Models\Fleet::updateOrCreate(
    //         $fleetPMIData,
    //         [
    //             'start_date' => $request->input('PMI_due'),
    //             'end_date' => \Carbon\Carbon::parse($request->input('PMI_due'))->addYear()->toDateString(),
    //             'company_id' => $request->input('companyName'),
    //             'every' => $request->input('PMI_intervals'),
    //             'interval' => 'Week',
    //             'created_by' => \Auth::user()->id,
    //         ]
    //     );

    //     if ($fleetPMI->wasRecentlyCreated) {
    //         $this->generateReminders($fleetPMI);
    //     }
    // }

    // // Brake Test Due Reminder
    // if (!$isArchived && $request->filled('PMI_due') && $request->filled('date_of_inspection')) {
    //     $fleetBrakeData = [
    //         'planner_type' => 'Brake Test Due',
    //         'vehicle_id' => $contract->id,
    //     ];

    //     $fleetBrake = \App\Models\Fleet::updateOrCreate(
    //         $fleetBrakeData,
    //         [
    //             'start_date' => $request->input('PMI_due'),
    //             'end_date' => \Carbon\Carbon::parse($request->input('PMI_due'))->addYear()->toDateString(),
    //             'company_id' => $request->input('companyName'),
    //             'every' => $request->input('PMI_intervals'),
    //             'interval' => 'Week',
    //             'created_by' => \Auth::user()->id,
    //         ]
    //     );

    //     if ($fleetBrake->wasRecentlyCreated) {
    //         $this->generateReminders($fleetBrake);
    //     }
    // }

    // // Insurance Reminder
    // if (!$isArchived && $request->filled('insurance')) {
    //     $fleetInsurance = [
    //         'planner_type' => 'Insurance',
    //         'vehicle_id' => $contract->id,
    //     ];

    //     $fleetIns = \App\Models\Fleet::updateOrCreate(
    //         $fleetInsurance,
    //         [
    //             'start_date' => $request->input('insurance'),
    //             'end_date' => \Carbon\Carbon::parse($request->input('insurance'))->addYears(1)->toDateString(),
    //             'company_id' => $request->input('companyName'),
    //             'every' => 12,
    //             'interval' => 'Month',
    //             'created_by' => \Auth::user()->id,
    //         ]
    //     );

    //     if ($fleetIns->wasRecentlyCreated) {
    //         $this->generateReminders($fleetIns);
    //     }
    // }

    //             return redirect()->back()->with('success', __('Vehicle  successfully updated.'));
    //         } else {
    //             return redirect()->back()->with('error', __('Permission denied.'));
    //         }
    //     }

    public function traileredit(vehicleDetails $contract)
    {
        $user = \Auth::user();
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {

            $contractTypes = CompanyDetails::where('created_by', '=', \Auth::user()->creatorId())->orderBy('name', 'asc')->where('company_status', 'Active')->get()->pluck('name', 'id');
        } else {
            $contractTypes = CompanyDetails::where('created_by', '=', \Auth::user()->creatorId())->where('id', '=', $user->companyname)->where('company_status', 'Active')->orderBy('name', 'asc')->get()->pluck('name', 'id');
        }

        /* -----------------------------------------------------
    CHECK FLEET PLANNER LOGIC FOR MULTIPLE TYPES
         - ---------------------------------------------------- */

        $plannerTypes = [
            'Insurance',
            'PMI Due', // ⭐ NEW
        ];

        $fieldMap = [
            'Insurance' => 'insurance',
            'PMI Due' => 'PMI_due', // ⭐ NEW
        ];

        $editFlags = [];
        // \Log::info("========== Fleet Planner Multi-Type Debug ==========");

        foreach ($plannerTypes as $type) {

            $fieldName = $fieldMap[$type];
            $contractValue = $contract->{$fieldName};

            // \Log::info("Checking Planner Type: {$type}");
            // \Log::info("Vehicle Field ({$fieldName}): " . ($contractValue ?? 'NULL'));

            $canEdit = false;

            if ($contractValue) {

                // 1) Fleet by vehicle + planner type
                $fleet = \App\Models\Fleet::where('vehicle_id', $contract->id)
                    ->where('planner_type', $type)
                    ->orderBy('id', 'DESC')
                    ->first();

                // \Log::info("Fleet Result for {$type}:", [
                //     'exists' => $fleet ? true : false,
                //     'fleet_id' => $fleet->id ?? null,
                // ]);

                if ($fleet) {

                    // 2) Latest reminder for this planner
                    $latestDate = \App\Models\FleetPlannerReminder::where('fleet_planner_id', $fleet->id)
                        ->orderBy('next_reminder_date', 'desc')->where('status', 'Completed')
                        ->value('next_reminder_date');

                    // \Log::info("Latest COMPLETED Reminder Date for {$type}: " . ($latestDate ?? 'NULL'));

                    if ($latestDate &&
                        \Carbon\Carbon::parse($latestDate)->isSameDay($contractValue)
                    ) {
                        $canEdit = true;
                    }
                }
            }

            $editFlags[$type] = $canEdit;

            // \Log::info("Can Edit {$type}? => " . ($canEdit ? 'YES' : 'NO'));
            // \Log::info("-----------------------------------------------------");
        }

        // \Log::info("========== Fleet Planner Multi-Type Debug END ==========");

        // Initialize variables for archive reason and text
        $archiveReason = null;
        $archiveOtherText = null;

        // Check if the vehicle_status contains "Archive"
        if (strpos($contract->vehicle_status, 'Archive') !== false) {
            // Split the vehicle_status to get the reason
            preg_match('/Archive\s*\((.*)\)/', $contract->vehicle_status, $matches);
            if (isset($matches[1])) {
                $reason = trim($matches[1]); // Get the text within parentheses
                // Check if the reason matches any predefined reasons
                $validReasons = ['Sold', 'Scrapped', 'Write off', 'In repair'];
                if (in_array($reason, $validReasons)) {
                    $archiveReason = $reason; // Set the valid reason
                } else {
                    $archiveReason = 'Other'; // Otherwise, default to 'Other'
                    $archiveOtherText = $reason; // Store the other text for editing
                }
            }
            $contract->vehicle_status = 'Archive'; // Set the dropdown value to 'Archive'
        }

        return view('contract.trailer.edit', compact('contractTypes', 'contract', 'archiveReason', 'archiveOtherText', 'editFlags'));
    }

    public function trailerupdate(Request $request, vehicleDetails $contract)
    {
        if (\Auth::user()->can('edit vehicle')) {
            $rules = [
                'vehicle_nick_name' => 'nullable',
                'insurance_type' => 'nullable|array',
                'insurance_type.*' => 'nullable|string|in:Motor Insurance',
                'insurance' => 'nullable',
                'PMI_intervals' => 'nullable|in:1,2,3,4,5,6,7,8,9,10',
                'PMI_due' => 'nullable',
                'date_of_inspection' => 'nullable',
                'odometer_reading' => 'nullable',

                'vehicle_status' => 'nullable', // Add this line
                'archive_reason' => 'nullable', // Add this line
                'archive_other_text' => 'nullable', // Add this line
                'companyName' => 'required',
                'group_id' => 'nullable',
                'depot_id' => 'nullable',
                'annual_test_expiry_date' => 'nullable|date', // New validation rule for mot_expiry
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('contract.index')->with('error', $messages->first());
            }

            $isArchived = Str::startsWith($request->vehicle_status, 'Archive');

            // Check for blocked updates on archived vehicles
            if ($isArchived) {
                $blockedFields = ['tacho_calibration', 'dvs_pss_permit_expiry', 'insurance', 'PMI_due', 'date_of_inspection', 'fridge_service', 'fridge_calibration', 'tail_lift', 'loler'];
                $attemptedFields = [];

                foreach ($blockedFields as $field) {
                    if ($request->filled($field) && $request->$field !== $contract->$field) {
                        $attemptedFields[] = $field;
                    }
                }

                if (! empty($attemptedFields)) {
                    return redirect()->back()->with('error', 'This vehicle is currently archived. Please update the vehicle status before modifying reminder dates.');
                }
            }

            if ($isArchived) {
                // Step 1: Get IDs of Fleet entries for this vehicle
                $fleetIds = \App\Models\Fleet::where('vehicle_id', $contract->id)->pluck('id');

                if ($fleetIds->isNotEmpty()) {
                    // Step 2: Delete only Pending FleetPlannerReminders linked to those Fleets
                    \App\Models\FleetPlannerReminder::whereIn('fleet_planner_id', $fleetIds)
                        ->where('status', 'Pending')
                        ->delete();
                }
            }

            // 🟩 STORE ORIGINAL VALUES BEFORE SAVE (IMPORTANT)
            $originalPMI = $contract->PMI_due;
            $originalInspect = $contract->date_of_inspection;
            $oldInsurance = $contract->insurance;

            $contract->vehicle_nick_name = $request->vehicle_nick_name ?? $contract->registrationNumber;

            $selectedInsurance = $request->insurance_type ?? [];
            $contract->insurance_type = json_encode($selectedInsurance);
            if (! $isArchived) {

                $contract->insurance = $request->insurance;
                $contract->PMI_intervals = $request->PMI_intervals;
                $contract->PMI_due = $request->PMI_due;
                $contract->date_of_inspection = $request->date_of_inspection;
                $contract->brake_test_due = $request->PMI_due ? \Carbon\Carbon::parse($request->PMI_due)->format('Y-m-d') : null;
            }
            $contract->odometer_reading = $request->odometer_reading;
            $contract->companyName = $request->companyName;
            $contract->group_id = $request->group_id;
            $contract->depot_id = $request->depot_id;
            $contract->created_by = \Auth::user()->id;

            if ($request->vehicle_status == 'Archive') {
                $archiveReason = $request->archive_reason;
                $archiveOtherText = $request->archive_other_text;

                if ($archiveReason == 'Other' && $archiveOtherText) {
                    $contract->vehicle_status = "Archive ($archiveOtherText)"; // Combine other text
                } elseif ($archiveReason) {
                    $contract->vehicle_status = "Archive ($archiveReason)"; // Use selected reason
                } else {
                    $contract->vehicle_status = 'Archive'; // Default to Archive if no reason provided
                }
            } else {
                $contract->vehicle_status = $request->vehicle_status; // Regular status
            }

            $contract->save();

            if ($contract->vehicle) {
                $contract->vehicle->companyName = $request->companyName;
                // New: Updating mot_expiry in the related vehicle
                $contract->vehicle->annual_test_expiry_date = $request->annual_test_expiry_date;
                $contract->vehicle->save();
            }

            // 🟩 NEW VALUES AFTER SAVE
            $newPMI = $contract->PMI_due;
            $newInspect = $contract->date_of_inspection;

            // 🟩 FIXED CHANGE DETECTION
            $shouldCreateReminder =
                ($newPMI !== $originalPMI) ||
                ($newInspect !== $originalInspect);

            // ============================================================
            // CREATE REMINDERS — ONLY WHEN PMI OR INSPECTION CHANGES
            // ============================================================
            if (
                ! $isArchived &&
                $request->filled('PMI_due') &&
                $request->filled('date_of_inspection') &&
                $shouldCreateReminder
            ) {
                // PMI Due
                $fleetPMI = \App\Models\Fleet::create([
                    'planner_type' => 'PMI Due',
                    'vehicle_id' => $contract->id,
                    'start_date' => Carbon::parse($newPMI)->format('Y-m-d'),
                    'end_date' => Carbon::parse($newPMI)->addYear()->toDateString(),
                    'company_id' => $request->companyName,
                    'every' => $request->PMI_intervals,
                    'interval' => 'Week',
                    'created_by' => \Auth::id(),
                ]);
                $this->generateReminders($fleetPMI);

                // Brake Test Due
                $fleetBrake = \App\Models\Fleet::create([
                    'planner_type' => 'Brake Test Due',
                    'vehicle_id' => $contract->id,
                    'start_date' => Carbon::parse($newPMI)->format('Y-m-d'),
                    'end_date' => Carbon::parse($newPMI)->addYear()->toDateString(),
                    'company_id' => $request->companyName,
                    'every' => $request->PMI_intervals,
                    'interval' => 'Week',
                    'created_by' => \Auth::id(),
                ]);
                $this->generateReminders($fleetBrake);
            }

            // ----------------------
            // Insurance
            // ----------------------
            if (! $isArchived && $request->filled('insurance')) {

                $newDate = Carbon::parse($request->insurance)->format('Y-m-d');
                $oldDate = $oldInsurance ? Carbon::parse($oldInsurance)->format('Y-m-d') : null;

                if ($newDate !== $oldDate) {

                    $fleetIns = \App\Models\Fleet::create([
                        'planner_type' => 'Insurance',
                        'vehicle_id' => $contract->id,
                        'start_date' => $newDate,
                        'end_date' => Carbon::parse($newDate)->addYear()->toDateString(),
                        'company_id' => $request->companyName,
                        'every' => 12,
                        'interval' => 'Month',
                        'created_by' => \Auth::id(),
                    ]);

                    if ($fleetIns->wasRecentlyCreated) {
                        $this->generateReminders($fleetIns);
                    }
                }
            }

            return redirect()->back()->with('success', __('Vehicle  successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function importFile()
    {
        if (\Auth::user()->can('create depot')) {
            return view('contract.import');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function import(Request $request)
    {
        // Check user role
        $user = \Auth::user();

        // Only Admin or PTC Manager can import for all companies
        $isAdminOrPTCManager = $user->hasRole('company') || $user->hasRole('PTC manager');

        // 🔒 User allowed vehicle groups (for non-admin users)
    $userVehicleGroupIds = is_array($user->vehicle_group_id)
        ? $user->vehicle_group_id
        : json_decode($user->vehicle_group_id, true);

    if (! is_array($userVehicleGroupIds)) {
        $userVehicleGroupIds = [$user->vehicle_group_id];
    }

        $rules = [
            'file' => 'required',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $depot = (new \App\Imports\VehicleImport)->toArray($request->file('file'))[0];
        $totalProduct = count($depot) - 1;
        $errorArray = [];
        $successCount = 0;

        foreach ($depot as $key => $items) {
            // Skip header row
            if ($key === 0) {
                continue;
            }

            // Lookup CompanyDetails based on companyName
            $companyName = $items[1] ?? null; // Adjust index according to the column position
            $companyDetails = \App\Models\CompanyDetails::where('name', $companyName)->first();
            if (! $companyDetails) {
                $errorArray[] = [
                    'error' => 'Company name "'.$companyName.'" not found',
                    'data' => $items,
                ];

                continue;
            }

            // Check if user is allowed to import for this company
            if (! $isAdminOrPTCManager && $user->companyname != $companyDetails->id) {
                $errorArray[] = [
                    'error' => 'You are not authorized to import data for company "'.$companyName.'"',
                    'data' => $items,
                ];

                continue;
            }

            // Lookup Depot based on depot name
            $depotName = $items[10] ?? null; // Assuming depot column index is 10
            $depotDetails = \App\Models\Depot::where('name', $depotName)
                ->where('companyName', $companyDetails->id) // Ensure depot belongs to the same company
                ->first();

            if (! $depotDetails) {
                $errorArray[] = [
                    'error' => 'Depot name "'.$depotName.'" not found for company "'.$companyName.'"',
                    'data' => $items,
                ];

                continue;
            }

            // Convert user's depot_id from array to individual values
            $userDepotIds = json_decode($user->depot_id, true); // Convert to array
            if (! is_array($userDepotIds)) {
                $userDepotIds = [$user->depot_id];
            } // Ensure it's an array

            // Check if user is allowed to import for this depot
            if (! $isAdminOrPTCManager && ! in_array($depotDetails->id, $userDepotIds)) {
                $errorArray[] = ['error' => 'Unauthorized for depot: '.$depotName, 'data' => $items];

                continue;
            }

            $vehicleGroupName = $items[9] ?? null; // Group column index

            // Lookup Group based on group name
            $group = \App\Models\VehicleGroup::where('name', $vehicleGroupName)
                ->where('company_id', $companyDetails->id) // Ensure the group belongs to the same company
                ->first();

            if (! $group) {
                // If group name is not found, store error message and set group_id to null
                $errorArray[] = [
                    'error' => 'Group name "'.$vehicleGroupName.'" not found for company "'.$companyName.'"',
                    'data' => $items,
                ];
                $group_id = null; // Set group_id to null if the group is not found
            } else {
                // If group is found, use its ID
                $group_id = $group->id;
            }

            // 🔒 Restrict group for non-admin users
        if (! $isAdminOrPTCManager && ! in_array($group->id, $userVehicleGroupIds)) {
            $errorArray[] = ['error' => 'Unauthorized vehicle group "'.$vehicleGroupName.'"', 'data' => $items];
            continue;
        }


            // Check for existing Vehicles record
            $vehicleService = \App\Models\Vehicles::where('registrations', $items[0])
                ->where('companyName', $companyDetails->id)
                ->first();

            if ($vehicleService) {
                $vehicleService->created_by = \Auth::user()->id;
            } else {
                $vehicleService = new \App\Models\Vehicles();
                $vehicleService->registrations = $items[0] ?? null;
                $vehicleService->companyName = $companyDetails->id;
                $vehicleService->created_by = \Auth::user()->id;
                $vehicleService->save();
            }

            $vehicleId = $vehicleService->id;

            // Check if vehicleDetails already exists with the same registrationNumber and companyName
            $existingVehicleDetails = \App\Models\vehicleDetails::where('registrationNumber', $vehicleService->registrations)
                ->where('companyName', $companyDetails->id)
                ->first();

            if ($existingVehicleDetails) {
                $errorArray[] = [
                    'error' => 'Duplicate vehicle details for registration "'.$vehicleService->registrations.'" and company "'.$companyDetails->name.'"',
                    'data' => $items,
                ];

                continue; // Skip duplicate entries
            }

            // Create new vehicleDetails record
            $vehicleDetails = new \App\Models\vehicleDetails();
            $vehicleDetails->companyName = $companyDetails->id;
            $vehicleDetails->depot_id = $depotDetails->id; // Save depot association
            $vehicleDetails->registrationNumber = $vehicleService->registrations;
            $vehicleDetails->vehicle_id = $vehicleId;
            $vehicleDetails->created_by = \Auth::user()->id;

            // Assign the new fields and convert dates to 'Y-m-d' format
            $vehicleDetails->tacho_calibration = $this->formatDate($items[2] ?? null);
            $vehicleDetails->dvs_pss_permit_expiry = $this->formatDate($items[3] ?? null);
            $vehicleDetails->insurance = $this->formatDate($items[4] ?? null);
            $vehicleDetails->date_of_inspection = $this->formatDate($items[5] ?? null);
            $vehicleDetails->odometer_reading = $items[7] ?? null;
            $vehicleDetails->insurance_type = $items[8] ?? null;
            $vehicleType = $items[11] ?? null; // Just for checking, not saving

            // Set vehicle_type in Vehicles model
            if (strtolower(trim($vehicleType)) === 't') {
                $vehicleService->vehicle_type = 'Trailer';
            } else {
                $vehicleService->vehicle_type = 'HGV';
            }

            $vehicleService->save();

            // // If vehicle type is 'T' or 't', skip saving fridge fields
            // if (strtolower(trim($vehicleType)) === 't') {
            //     $vehicleDetails->fridge_service = null;
            //     $vehicleDetails->fridge_service_interval = null;
            // } else {
            //     $vehicleDetails->fridge_service = $this->formatDate($items[11] ?? null);
            //     $fridgeServiceInterval = $items[12] ?? null;

            //     // If fridge_service is null, set interval to null
            //     if (empty($vehicleDetails->fridge_service)) {
            //         $fridgeServiceInterval = null;
            //     }

            //     // If interval is null, set fridge_service to null
            //     if (empty($fridgeServiceInterval)) {
            //         $vehicleDetails->fridge_service = null;
            //     }

            //     if ($fridgeServiceInterval > 365) {
            //         $errorArray[] = [
            //             'error' => 'Fridge service interval exceeds 365 days',
            //             'data' => $items,
            //         ];
            //         continue;
            //     }

            //     $vehicleDetails->fridge_service_interval = $fridgeServiceInterval;
            // }

            //         // If vehicle type is 'T' or 't', skip saving fridge fields
            //         if (strtolower(trim($vehicleType)) === 't') {
            //             $vehicleDetails->fridge_calibration = null;
            //             $vehicleDetails->fridge_calibration_interval = null;
            //         } else {
            //             $vehicleDetails->fridge_calibration = $this->formatDate($items[13] ?? null);
            //             $fridgeCalibrationInterval = $items[14] ?? null;
            //                     // If fridge_service is null, set interval to null
            //              if (empty($vehicleDetails->fridge_calibration)) {
            //                  $fridgeCalibrationInterval = null;
            //              }

            //              // If interval is null, set fridge_service to null
            //              if (empty($fridgeCalibrationInterval)) {
            //                  $vehicleDetails->fridge_calibration = null;
            //              }

            //             if ($fridgeCalibrationInterval > 365) {
            //                 $errorArray[] = [
            //                     'error' => 'Fridge calibration interval exceeds 365 days',
            //                     'data' => $items,
            //                 ];
            //                 continue;
            //             }
            //             $vehicleDetails->fridge_calibration_interval = $fridgeCalibrationInterval;
            //         }        // If vehicle type is 'T' or 't', skip saving fridge fields
            //         if (strtolower(trim($vehicleType)) === 't') {
            //             $vehicleDetails->tail_lift = null;
            //             $vehicleDetails->tail_lift_interval = null;
            //          } else {
            //             $vehicleDetails->tail_lift = $this->formatDate($items[15] ?? null);
            //             $tailLiftLolerInterval = $items[16] ?? null;

            //              // If tail_lift_loler is null, set interval to null
            //              if (empty($vehicleDetails->tail_lift)) {
            //                  $tailLiftLolerInterval = null;
            //              }

            //              // If interval is null, set tail_lift_loler to null
            //              if (empty($tailLiftLolerInterval)) {
            //                  $vehicleDetails->tail_lift = null;
            //              }
            //             if ($tailLiftLolerInterval > 365) {
            //                 $errorArray[] = [
            //                     'error' => 'Tail lift interval exceeds 365 days',
            //                     'data' => $items,
            //                 ];
            //                 continue;
            //             }
            //             $vehicleDetails->tail_lift_interval = $tailLiftLolerInterval;
            //         }

            //          if (strtolower(trim($vehicleType)) === 't') {
            //             $vehicleDetails->loler = null;
            //             $vehicleDetails->loler_interval = null;
            //          } else {
            //             $vehicleDetails->loler = $this->formatDate($items[17] ?? null);
            //             $tailLiftLolerInterval = $items[18] ?? null;

            //              // If tail_lift_loler is null, set interval to null
            //              if (empty($vehicleDetails->loler)) {
            //                  $tailLiftLolerInterval = null;
            //              }

            //              // If interval is null, set tail_lift_loler to null
            //              if (empty($tailLiftLolerInterval)) {
            //                  $vehicleDetails->loler = null;
            //              }
            //             if ($tailLiftLolerInterval > 365) {
            //                 $errorArray[] = [
            //                     'error' => 'Loler interval exceeds 365 days',
            //                     'data' => $items,
            //                 ];
            //                 continue;
            //             }
            //             $vehicleDetails->loler_interval = $tailLiftLolerInterval;
            //         }

            $vehicleDetails->group_id = $group_id;
            $date_of_inspection = $this->formatDate($items[5] ?? null);

            // Calculate PMI_Due based on PMI_intervals (in weeks)
            if ($date_of_inspection === null) {
                // If date_of_inspection is null, PMI interval and related fields should be null
                $vehicleDetails->PMI_intervals = null;
                $vehicleDetails->PMI_Due = null;
                $vehicleDetails->brake_test_due = null;
            } else {
                $pmi_intervals = $items[6] ?? null;
                $vehicleDetails->PMI_intervals = $pmi_intervals;

                if ($pmi_intervals) {
                    // Calculate PMI Due date and format in d-m-Y
                    $pmi_due_date = \Carbon\Carbon::createFromFormat('Y-m-d', $date_of_inspection)
                        ->addWeeks((int) $pmi_intervals)
                        ->format('d-m-Y'); // PMI_Due in d-m-Y format

                    // Calculate brake test due date and format in Y-m-d
                    $brake_test_due_date = \Carbon\Carbon::createFromFormat('Y-m-d', $date_of_inspection)
                        ->addWeeks((int) $pmi_intervals)
                        ->format('Y-m-d'); // brake_test_due in Y-m-d format

                    // Save the values to the vehicle details
                    $vehicleDetails->PMI_Due = $pmi_due_date;
                    $vehicleDetails->brake_test_due = $brake_test_due_date;
                } else {
                    // Default to null if no interval or date
                    $vehicleDetails->PMI_Due = null;
                    $vehicleDetails->brake_test_due = null;
                }
            }

            $today = \Carbon\Carbon::today();
            // Check tacho_calibration
            if ($vehicleDetails->tacho_calibration && \Carbon\Carbon::parse($vehicleDetails->tacho_calibration)->isBefore($today)) {
                $vehicleDetails->tacho_calibration = null; // Set to null if the date is in the past
            }

            // Check dvs_pss_permit_expiry
            if ($vehicleDetails->dvs_pss_permit_expiry && \Carbon\Carbon::parse($vehicleDetails->dvs_pss_permit_expiry)->isBefore($today)) {
                $vehicleDetails->dvs_pss_permit_expiry = null; // Set to null if the date is in the past
            }

            // Check insurance
            if ($vehicleDetails->insurance && \Carbon\Carbon::parse($vehicleDetails->insurance)->isBefore($today)) {
                $vehicleDetails->insurance = null; // Set to null if the date is in the past
            }

            $vehicleDetails->save();
            $successCount++;

            // Create reminders if needed (PMI, Brake Test, Tacho, DVS/PSS, Insurance)
            if ($vehicleDetails->PMI_Due && $vehicleDetails->date_of_inspection) {
                $fleetPMIData = [
                    'start_date' => $vehicleDetails->PMI_Due,
                    'end_date' => \Carbon\Carbon::parse($vehicleDetails->PMI_Due)->addYear()->toDateString(),
                    'company_id' => $companyDetails->id,
                    'planner_type' => 'PMI Due',
                    'vehicle_id' => $vehicleDetails->id,
                    'every' => $vehicleDetails->PMI_intervals,
                    'interval' => 'Week',
                    'created_by' => \Auth::user()->id,
                ];
                $fleetPMI = \App\Models\Fleet::create($fleetPMIData);
                $this->generateReminders($fleetPMI);

                // Create reminder for Brake Test Due
                $fleetBrakeData = [
                    'start_date' => $vehicleDetails->PMI_Due,
                    'end_date' => \Carbon\Carbon::parse($vehicleDetails->PMI_Due)->addYear()->toDateString(),
                    'company_id' => $companyDetails->id,
                    'planner_type' => 'Brake Test Due',
                    'vehicle_id' => $vehicleDetails->id,
                    'every' => $vehicleDetails->PMI_intervals,
                    'interval' => 'Week',
                    'created_by' => \Auth::user()->id,
                ];
                $fleetBrake = \App\Models\Fleet::create($fleetBrakeData);
                $this->generateReminders($fleetBrake);
            }

            // if ($vehicleDetails->fridge_service) {
            //     $fleetFridge = [
            //         'start_date' => $vehicleDetails->fridge_service,
            //         'end_date' => \Carbon\Carbon::parse($vehicleDetails->fridge_service)->addYear()->toDateString(),
            //         'company_id' => $companyDetails->id,
            //         'planner_type' => 'Fridge Service',
            //         'vehicle_id' => $vehicleDetails->id,
            //         'every' => $vehicleDetails->fridge_service_interval,
            //         'interval' => 'Day',
            //         'created_by' => \Auth::user()->id,
            //     ];
            //     $fleetFridgeModel = \App\Models\Fleet::create($fleetFridge);
            //     $this->generateReminders($fleetFridgeModel);
            // }

            // if ($vehicleDetails->fridge_calibration) {
            //     $fleetFridgeCalibration = [
            //         'start_date' => $vehicleDetails->fridge_calibration,
            //         'end_date' => \Carbon\Carbon::parse($vehicleDetails->fridge_calibration)->addYear()->toDateString(),
            //         'company_id' => $companyDetails->id,
            //         'planner_type' => 'Fridge Calibration',
            //         'vehicle_id' => $vehicleDetails->id,
            //         'every' => $vehicleDetails->fridge_calibration_interval,
            //         'interval' => 'Day',
            //         'created_by' => \Auth::user()->id,
            //     ];
            //     $fleetFridgeCalibrationModel = \App\Models\Fleet::create($fleetFridgeCalibration);
            //     $this->generateReminders($fleetFridgeCalibrationModel);
            // }

            // if ($vehicleDetails->tail_lift) {
            //     $fleetTailLift = [
            //         'start_date' => $vehicleDetails->tail_lift,
            //         'end_date' => \Carbon\Carbon::parse($vehicleDetails->tail_lift)->addYear()->toDateString(),
            //         'company_id' => $companyDetails->id,
            //         'planner_type' => 'Tail lift',
            //         'vehicle_id' => $vehicleDetails->id,
            //         'every' => $vehicleDetails->tail_lift_interval,
            //         'interval' => 'Day',
            //         'created_by' => \Auth::user()->id,
            //     ];
            //     $fleetTailLiftModel = \App\Models\Fleet::create($fleetTailLift);
            //     $this->generateReminders($fleetTailLiftModel);
            // }

            // if ($vehicleDetails->loler) {
            //     $fleetLoler = [
            //         'start_date' => $vehicleDetails->loler,
            //         'end_date' => \Carbon\Carbon::parse($vehicleDetails->loler)->addYear()->toDateString(),
            //         'company_id' => $companyDetails->id,
            //         'planner_type' => 'Loler',
            //         'vehicle_id' => $vehicleDetails->id,
            //         'every' => $vehicleDetails->loler_interval,
            //         'interval' => 'Day',
            //         'created_by' => \Auth::user()->id,
            //     ];
            //     $fleetLolerModel = \App\Models\Fleet::create($fleetLoler);
            //     $this->generateReminders($fleetLolerModel);
            // }

            if ($vehicleDetails->tacho_calibration) {
                // Create reminder for Tacho Calibration
                $fleetTachoData = [
                    'start_date' => $vehicleDetails->tacho_calibration,
                    'end_date' => \Carbon\Carbon::parse($vehicleDetails->tacho_calibration)->addYears(2)->toDateString(),
                    'company_id' => $companyDetails->id,
                    'planner_type' => 'Tacho Calibration',
                    'vehicle_id' => $vehicleDetails->id,
                    'every' => 24,
                    'interval' => 'Month',
                    'created_by' => \Auth::user()->id,
                ];
                $fleetTacho = \App\Models\Fleet::create($fleetTachoData);
                $this->generateReminders($fleetTacho);
            }

            if ($vehicleDetails->dvs_pss_permit_expiry) {
                // Create reminder for DVS/PSS Permit Expiry
                $fleetDVSPSS = [
                    'start_date' => $vehicleDetails->dvs_pss_permit_expiry,
                    'end_date' => \Carbon\Carbon::parse($vehicleDetails->dvs_pss_permit_expiry)->addYears(5)->toDateString(),
                    'company_id' => $companyDetails->id,
                    'planner_type' => 'DVS/PSS Permit Expiry',
                    'vehicle_id' => $vehicleDetails->id,
                    'every' => 60,
                    'interval' => 'Month',
                    'created_by' => \Auth::user()->id,
                ];
                $fleetDVS = \App\Models\Fleet::create($fleetDVSPSS);
                $this->generateReminders($fleetDVS);
            }

            if ($vehicleDetails->insurance) {
                // Create reminder for Insurance
                $fleetinsurance = [
                    'start_date' => $vehicleDetails->insurance,
                    'end_date' => \Carbon\Carbon::parse($vehicleDetails->insurance)->addYears(1)->toDateString(),
                    'company_id' => $companyDetails->id,
                    'planner_type' => 'Insurance',
                    'vehicle_id' => $vehicleDetails->id,
                    'every' => 12,
                    'interval' => 'Month',
                    'created_by' => \Auth::user()->id,
                ];
                $fleetins = \App\Models\Fleet::create($fleetinsurance);
                $this->generateReminders($fleetins);
            }

            if ($vehicleDetails->taxDueDate) {
                // Create reminder for Tax Due Date
                $fleetinsurance = [
                    'start_date' => $vehicleDetails->taxDueDate,
                    'end_date' => $vehicleDetails->taxDueDate,
                    'company_id' => $companyDetails->id,
                    'planner_type' => 'Tax Due Date',
                    'vehicle_id' => $vehicleDetails->id,
                    'every' => 0,
                    'interval' => null,
                    'created_by' => \Auth::user()->id,
                ];
                $fleetins = \App\Models\Fleet::create($fleetinsurance);
                $this->generateReminders($fleetins);
            }
        }

        // Prepare response
        if (empty($errorArray)) {
            $data['status'] = 'success';
            $data['msg'] = __('All records successfully imported');
        } else {
            $data['status'] = 'error';
            $data['msg'] = count($errorArray).' '.__('Record(s) failed to import out of').' '.$totalProduct.' '.__('record(s)');
            \Session::put('errorArray', $errorArray);
        }

        return redirect()->route('contract.index')->with($data['status'], $data['msg']);
    }

    /**
     * Format input date to Y-m-d format.
     *
     * @param  string|null  $date
     * @return string|null
     */
    private function formatDate($date)
    {
        if (! $date) {
            return null; // Return null if the date is empty
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null; // Return null if date conversion fails
        }
    }

    public function destroy(vehicleDetails $contract)
    {
        if (\Auth::user()->can('delete vehicle')) {
            $vehicle = \App\Models\Vehicles::find($contract->vehicle_id);

            if ($vehicle) {
                // Delete associated VehiclesAnnualTest records
                \App\Models\VehiclesAnnualTest::where('vehicle_id', $vehicle->id)->delete();
                \App\Models\VehiclesAnnualTestDefect::where('vehicle_id', $vehicle->id)->delete();

                // Delete Vehicles record
                $vehicle->delete();

                // Delete vehicleDetails record
                $contract->delete();

                return redirect()->back()->with('success', __('Vehicle successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Vehicle not found'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function description($id)
    {
        $contract = Contract::find($id);

        return view('contract.description', compact('contract'));
    }

    public function grid()
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client') {
            if (\Auth::user()->type == 'company') {
                $contracts = Contract::where('created_by', '=', \Auth::user()->creatorId())->get();
            } else {
                $contracts = Contract::where('client_name', '=', \Auth::user()->id)->get();
            }

            /*   $defualtView         = new UserDefualtView();
               $defualtView->route  = \Request::route()->getName();
               $defualtView->module = 'contract';
               $defualtView->view   = 'grid';
               User::userDefualtView($defualtView);*/
            return view('contract.grid', compact('contracts'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    // public function fileUpload($id, Request $request)
    // {

    //     if(\Auth::user()->type == 'company' || \Auth::user()->type == 'client' )
    //     {

    //         $contract = Contract::find($id);
    //         $request->validate(['file' => 'required']);

    //         //storage limit
    //         $image_size = $request->file('file')->getSize();

    //         $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);

    //         if($result==1)
    //         {

    //             $files = $id . $request->file->getClientOriginalName();
    //             $file   = Contract_attachment::create(
    //                 [
    //                     'contract_id' => $request->contract_id,
    //                     'user_id' => \Auth::user()->id,
    //                     'files' => $files,
    //                 ]
    //             );

    //              $request->file->storeAs('contract_attechment', $files);

    //             $dir = 'contract_attechment/';
    //              $files = $request->file->getClientOriginalName();
    //             $path = Utility::upload_file($request,'file',$files,$dir,[]);
    //             if($path['flag'] == 1){
    //                 $file = $path['url'];
    //             }
    //             else{

    //                 return redirect()->back()->with('error', __($path['msg']));
    //             }
    //             $return               = [];
    //             $return['is_success'] = true;
    //             $return['download']   = route(
    //                 'contracts.file.download', [
    //                     $contract->id,
    //                     $file->id,
    //                 ]
    //             );

    //             $return['delete']     = route(
    //                 'contracts.file.delete', [
    //                     $contract->id,
    //                     $file->id,
    //                 ]
    //             );
    //         }else{

    //             $return               = [];
    //             $return['is_success'] = true;
    //             $return['status'] =1;
    //             $return['success_msg'] = ((isset($result) && $result!=1) ? '<br> <span class="text-danger">' . $result . '</span>' : '');
    //         }

    //         return response()->json($return);
    //     }
    //     else
    //     {
    //         return response()->json(
    //             [
    //                 'is_success' => false,
    //                 'error' => __('Permission Denied.'),
    //             ], 401
    //         );
    //     }

    // }

    public function fileUpload($id, Request $request)
    {

        $contract = vehicleDetails::find($id);
        $request->validate(['file' => 'required']);
        $file_path = 'image_attechment/'.$contract->file;
        $image_size = $request->file('file')->getSize();
        $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
        if ($result == 1) {
            Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);
            $files = $id.$request->file->getClientOriginalName();
            $dir = 'image_attechment/';
            // $files = $request->file->getClientOriginalName();
            $path = Utility::upload_file($request, 'file', $files, $dir, []);
            if ($path['flag'] == 1) {
                $file = $path['url'];
            } else {
                return redirect()->back()->with('error', __($path['msg']));
            }

            // $request->file->storeAs('contract_attechment', $files);
            $file = Contract_attachment::create(
                [
                    'contract_id' => $request->contract_id,
                    'user_id' => \Auth::user()->id,
                    'files' => $files,
                ]
            );
        }

        $return = [];
        $return['is_success'] = true;
        $return['download'] = route(
            'contracts.file.download',
            [
                $contract->id,
                $file->id,
            ]
        );

        $return['delete'] = route(
            'contracts.file.delete',
            [
                $contract->id,
                $file->id,
            ]
        );

        return response()->json($return);

    }

    public function fileDownload($id, $file_id)
    {

        $contract = vehicleDetails::find($id);
        if (\Auth::user()->type == 'company') {
            $file = Contract_attachment::find($file_id);
            if ($file) {
                $file_path = storage_path('image_attechment/'.$file->files);

                return \Response::download(
                    $file_path, $file->files, [
                        'Content-Length: '.filesize($file_path),
                    ]
                );
            } else {
                return redirect()->back()->with('error', __('File is not exist.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function fileDelete($id, $file_id)
    {
        $contract = vehicleDetails::find($id);

        $file = Contract_attachment::find($file_id);
        if ($file) {
            $path = storage_path('image_attechment/'.$file->files);
            if (file_exists($path)) {
                \File::delete($path);
            }
            $file->delete();

            return redirect()->back()->with('success', __('Vehicle file successfully deleted.'));

        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('File is not exist.'),
                ], 200
            );
        }

    }

    public function contract_status_edit(Request $request, $id)
    {
        // dd($request->all());
        $contract = Contract::find($id);
        $contract->status = $request->status;
        $contract->save();

    }

    public function commentStore(Request $request, $id)
    {
        $contract = new ContractComment();
        $contract->comment = $request->comment;
        $contract->contract_id = $request->id;
        $contract->user_id = \Auth::user()->id;
        $contract->save();
        // dd($contract);

        return redirect()->back()->with('success', __('comments successfully created!').((isset($smtp_error)) ? '<br> <span class="text-danger">'.$smtp_error.'</span>' : ''))->with('status', 'comments');

    }
    //    public function contract_descriptionStore($id, Request $request)
    //    {
    //        if(\Auth::user()->type == 'company')
    //        {
    //            $contract        =Contract::find($id);
    //            $contract->contract_description = $request->contract_description;
    //            $contract->save();
    //            return redirect()->back()->with('success', __('Contact Description successfully saved.'));
    //
    //        }
    //        else
    //        {
    //            return redirect()->back()->with('error', __('Permission denied'));
    //
    //        }
    //    }

    public function contract_descriptionStore($id, Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $contract = Contract::find($id);
            if ($contract->created_by == \Auth::user()->creatorId()) {
                $contract->contract_description = $request->contract_description;
                $contract->save();

                return response()->json(
                    [
                        'is_success' => true,
                        'success' => __('Contract description successfully saved!'),
                    ], 200
                );
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ], 401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ], 401
            );
        }
    }

    public function commentDestroy($id)
    {
        $contract = ContractComment::find($id);

        $contract->delete();

        return redirect()->back()->with('success', __('Comment successfully deleted!'));

    }

    public function noteStore($id, Request $request)
    {
        $contract = Contract::find($id);
        $notes = new ContractNotes();
        $notes->contract_id = $contract->id;
        $notes->notes = $request->notes;
        $notes->user_id = \Auth::user()->id;
        $notes->save();

        return redirect()->back()->with('success', __('Note successfully saved.'));

    }

    public function noteDestroy($id)
    {
        $contract = ContractNotes::find($id);
        $contract->delete();

        return redirect()->back()->with('success', __('Note successfully deleted!'));

    }

    public function clientwiseproject($id)
    {
        $projects = Project::where('client_id', $id)->get();

        $users = [];
        foreach ($projects as $key => $value) {
            $users[] = [
                'id' => $value->id,
                'name' => $value->project_name,
            ];

        }
        // dd($users);

        return \Response::json($users);
    }

    // public function printContract($id)
    // {
    //     $contract = vehicleDetails::findOrFail($id);
    //     $settings = Utility::settings();

    //     // $client   = $contract->clients->first();
    //     //Set your logo
    //     $logo = asset(Storage::url('uploads/logo/'));
    //     $company_logo = Utility::getValByName('company_logo');
    //     $img = asset($logo.'/'.(isset($company_logo) && ! empty($company_logo) ? $company_logo : 'logo-dark.png'));

    //     if ($contract) {
    //         $color = '#'.$settings['invoice_color'];
    //         $font_color = Utility::getFontColor($color);

    //         return view('contract.preview', compact('contract', 'color', 'img', 'settings', 'font_color'));
    //     } else {
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    public function copycontract($id)
    {
        $contract = Contract::find($id);
        $clients = User::where('type', '=', 'Client')->get()->pluck('name', 'id');
        $contractTypes = ContractType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $project = Project::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('title', 'id');
        $date = $contract->start_date.' to '.$contract->end_date;
        $contract->setAttribute('date', $date);

        return view('contract.copy', compact('contract', 'contractTypes', 'clients', 'project'));

    }

    public function copycontractstore(Request $request)
    {

        if (\Auth::user()->type == 'company') {
            $rules = [
                'client' => 'required',
                'subject' => 'required',
                'project_id' => 'required',
                'type' => 'required',
                'value' => 'required',
                'status' => 'Pending',
                'start_date' => 'required',
                'end_date' => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('contract.index')->with('error', $messages->first());
            }
            // $date = explode(' to ', $request->date);
            $contract = new Contract();
            $contract->client_name = $request->client;
            $contract->subject = $request->subject;
            $contract->project_id = implode(',', $request->project_id);
            $contract->type = $request->type;
            $contract->value = $request->value;
            $contract->start_date = $request->start_date;
            $contract->end_date = $request->end_date;
            $contract->description = $request->description;
            $contract->created_by = \Auth::user()->creatorId();
            $contract->save();

            //Send Email
            $setings = Utility::settings();
            if ($setings['new_contract'] == 1) {

                $client = \App\Models\User::find($request->client);
                $contractArr = [
                    'contract_subject' => $request->subject,
                    'contract_client' => $client->name,
                    'contract_value' => \Auth::user()->priceFormat($request->value),
                    'contract_start_date' => \Auth::user()->dateFormat($request->start_date),
                    'contract_end_date' => \Auth::user()->dateFormat($request->end_date),
                    'contract_description' => $request->description,
                ];

                // Send Email
                $resp = Utility::sendEmailTemplate('new_contract', [$client->id => $client->email], $contractArr);

                return redirect()->route('contract.index')->with('success', __('Contract successfully created.').(($resp['is_success'] == false && ! empty($resp['error'])) ? '<br> <span class="text-danger">'.$resp['error'].'</span>' : ''));

            }

            //Slack Notification
            $setting = Utility::settings(\Auth::user()->creatorId());
            if (isset($setting['contract_notification']) && $setting['contract_notification'] == 1) {
                $msg = $request->subject.' '.__('created by').' '.\Auth::user()->name.'.';
                Utility::send_slack_msg($msg);
            }

            //Telegram Notification
            $setting = Utility::settings(\Auth::user()->creatorId());
            if (isset($setting['telegram_contract_notification']) && $setting['telegram_contract_notification'] == 1) {
                $msg = $request->subject.' '.__('created by').' '.\Auth::user()->name.'.';
                Utility::send_telegram_msg($msg);
            }

            return redirect()->route('contract.index')->with('success', __('Contract successfully created.'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    //               public function store(Request $request)
    //     {
    //         if (\Auth::user()->can('create vehicle')) {
    //             $apiKey1 = 'CS8NuPQICs60CPb1Xb4cS6MG9KGIVuQ47ONeJXu6'; // API key for first API
    //             $apiKey2 = '7gqmPTWnf02zZ5oidVhgRaCVLH2EAqUA1ytOdFSt'; // API key for second API
    //             $apiUrl1 = 'https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles';
    //             $apiUrl2 = 'https://beta.check-mot.service.gov.uk/trade/vehicles/annual-tests'; // Change this to the URL of your second API

    //             $requestData1 = [
    //                 'registrationNumber' => $request->input('registrationNumber'),
    //                 'tacho_calibration' => $request->input('tacho_calibration'),
    //             ];

    //             $api1Success = false;
    //             $api2Success = false;

    //             try {
    //                 $response = Http::withHeaders([
    //                     'x-api-key' => $apiKey1,
    //                 ])->post($apiUrl1, $requestData1);

    //                 $responseData = $response->json();
    //                 $registration = ['registration_number' => $request->input('registration_number')];

    //                 // Handle successful response from API 1
    //                 $api1Success = true;

    //                 // Parse relevant data from the response for API 1
    //                 $vehicleData1 = [
    //                     'created_by' => \Auth::user()->id,
    //                     'companyName' => $request->input('companyName'),
    //                     'tacho_calibration' => $request->input('tacho_calibration'),
    //                     'dvs_pss_permit_expiry' => $request->input('dvs_pss_permit_expiry'),
    //                     'insurance_type' => $request->input('insurance_type') === 'other' ? $request->input('insurance_other') : $request->input('insurance_type'),
    //                     'insurance' => $request->input('insurance'),
    //                     'PMI_due' => $request->input('PMI_due'),
    //                     'PMI_intervals' => $request->input('PMI_intervals'),
    //                     'date_of_inspection' => $request->input('date_of_inspection'),
    //                     'odometer_reading' => $request->input('odometer_reading'),
    //                     'brake_test_due' => $request->input('PMI_due'),
    //                     'vehicle_status' => $request->input('combined_status'),
    //                     'group_id' => $request->input('group_id'),
    //                     'registrationNumber' => $responseData['registrationNumber'],
    //                     'taxStatus' => $responseData['taxStatus'] ?? null,
    //                     'taxDueDate' => isset($responseData['taxDueDate']) ? date('d F Y', strtotime($responseData['taxDueDate'])) : null,
    //                     'motStatus' => $responseData['motStatus'] ?? null,
    //                     'make' => $responseData['make'] ?? null,
    //                     'yearOfManufacture' => $responseData['yearOfManufacture'] ?? null,
    //                     'engineCapacity' => $responseData['engineCapacity'] ?? null,
    //                     'co2Emissions' => $responseData['co2Emissions'] ?? null,
    //                     'fuelType' => $responseData['fuelType'] ?? null,
    //                     'markedForExport' => $responseData['markedForExport'] ?? null,
    //                     'colour' => $responseData['colour'] ?? null,
    //                     'typeApproval' => $responseData['typeApproval'] ?? null,
    //                     'revenueWeight' => $responseData['revenueWeight'] ?? null,
    //                     'euroStatus' => $responseData['euroStatus'] ?? null,
    //                     'dateOfLastV5CIssued' => $responseData['dateOfLastV5CIssued'] ?? null,
    //                     'motExpiryDate' => $responseData['motExpiryDate'] ?? null,
    //                     'wheelplan' => $responseData['wheelplan'] ?? null,
    //                     'monthOfFirstRegistration' => $responseData['monthOfFirstRegistration'] ?? null,
    //                     'created_by' => \Auth::user()->id
    //                 ];

    //                 // Update or create data in vehicleDetails model for API 1
    //                 $vehicleDetailsModel1 = VehicleDetails::updateOrCreate(
    //                     ['registrationNumber' => $vehicleData1['registrationNumber']],
    //                     $vehicleData1 // Data to update or insert
    //                 );

    //                 if ($request->filled('PMI_due') && $request->filled('date_of_inspection')) {
    //                     $fleetPMIData = [
    //                         'start_date' => $request->input('date_of_inspection'),
    //                         'end_date' => \Carbon\Carbon::parse($request->input('date_of_inspection'))->addYear()->toDateString(),
    //                         'company_id' => $request->input('companyName'),
    //                         'planner_type' => 'PMI Due',
    //                         'vehicle_id' => $vehicleDetailsModel1->id,
    //                         'every' => $request->input('PMI_intervals'),
    //                         'interval' => 'Week',
    //                         'created_by' => \Auth::user()->id,
    //                     ];
    //                     $fleetPMI = \App\Models\Fleet::create($fleetPMIData);
    //                     $this->generateReminders($fleetPMI);
    //                 }

    //                 if ($request->filled('PMI_due') && $request->filled('date_of_inspection')) {
    //                     $fleetBrakeData = [
    //                         'start_date' => $request->input('date_of_inspection'),
    //                         'end_date' => \Carbon\Carbon::parse($request->input('date_of_inspection'))->addYear()->toDateString(),
    //                         'company_id' => $request->input('companyName'),
    //                         'planner_type' => 'Brake Test Due',
    //                         'vehicle_id' => $vehicleDetailsModel1->id,
    //                         'every' => $request->input('PMI_intervals'),
    //                         'interval' => 'Week',
    //                         'created_by' => \Auth::user()->id,
    //                     ];
    //                     $fleetBrake = \App\Models\Fleet::create($fleetBrakeData);
    //                     $this->generateReminders($fleetBrake);
    //                 }

    //                 if ($request->filled('tacho_calibration')) {
    //                     $fleetTachoData = [
    //                         'start_date' => $request->input('tacho_calibration'),
    //                         'end_date' => \Carbon\Carbon::parse($request->input('tacho_calibration'))->addYears(2)->toDateString(),
    //                         'company_id' => $request->input('companyName'),
    //                         'planner_type' => 'Tacho Calibration',
    //                         'vehicle_id' => $vehicleDetailsModel1->id,
    //                         'every' => 24,
    //                         'interval' => 'Month',
    //                         'created_by' => \Auth::user()->id,
    //                     ];
    //                     $fleetTacho = \App\Models\Fleet::create($fleetTachoData);
    //                     $this->generateReminders($fleetTacho);
    //                 }

    //                 if ($request->filled('dvs_pss_permit_expiry')) {
    //                     $fleetDVSPSS = [
    //                         'start_date' => $request->input('dvs_pss_permit_expiry'),
    //                         'end_date' => \Carbon\Carbon::parse($request->input('dvs_pss_permit_expiry'))->addYears(5)->toDateString(),
    //                         'company_id' => $request->input('companyName'),
    //                         'planner_type' => 'DVS/PSS Permit Expiry',
    //                         'vehicle_id' => $vehicleDetailsModel1->id,
    //                         'every' => 60,
    //                         'interval' => 'Month',
    //                         'created_by' => \Auth::user()->id,
    //                     ];
    //                     $fleetDVS = \App\Models\Fleet::create($fleetDVSPSS);
    //                     $this->generateReminders($fleetDVS);
    //                 }

    //                 if ($request->filled('insurance')) {
    //                     $fleetinsurance = [
    //                         'start_date' => $request->input('insurance'),
    //                         'end_date' => \Carbon\Carbon::parse($request->input('insurance'))->addYears()->toDateString(),
    //                         'company_id' => $request->input('companyName'),
    //                         'planner_type' => 'Insurance',
    //                         'vehicle_id' => $vehicleDetailsModel1->id,
    //                         'every' => 12,
    //                         'interval' => 'Month',
    //                         'created_by' => \Auth::user()->id,
    //                     ];
    //                     $fleetins = \App\Models\Fleet::create($fleetinsurance);
    //                     $this->generateReminders($fleetins);
    //                 }

    //                 // Handle the second API call only if the first one was successful
    //                 if ($api1Success) {
    //                     // Code for API 2 call...
    //                     $registrations = $request->input('registrationNumber');

    //                     $response2 = Http::withHeaders([
    //                         'x-api-key' => $apiKey2,
    //                     ])->get("$apiUrl2?registrations=$registrations"); // Modify this to suit your second API call

    //                     $responseData2 = $response2->json();

    //                     if (! empty($responseData2)) {
    //                         // Extracting data from the first item in the array, assuming it contains relevant data
    //                         $responseVehicle = $responseData2[0];

    //                         // Parse relevant data from the response for API 2
    //                         $vehicleData2 = [
    //                             'created_by' => \Auth::user()->id,
    //                             'companyName' => $request->input('companyName'),
    //                             'registrations' => $responseVehicle['registration'] ?? null,
    //                             'make' => $responseVehicle['make'] ?? null,
    //                             'model' => $responseVehicle['model'] ?? null,
    //                             'vehicle_type' => $responseVehicle['vehicleType'] ?? null,
    //                             'registration_date' => $responseVehicle['registrationDate'] ?? null,
    //                             'annual_test_expiry_date' => $responseVehicle['annualTestExpiryDate'] ?? null,
    //                         ];

    //                         // Update or create data in vehicleDetails model for API 2
    //                         $vehicleDetailsModel2 = \App\Models\Vehicles::updateOrCreate(
    //                             ['registrations' => $vehicleData2['registrations']],
    //                             $vehicleData2 // Data to update or insert
    //                         );

    //                         $vehicleDetailsModel1->vehicle_id = $vehicleDetailsModel2->id;
    //                         $vehicleDetailsModel1->save();

    //                         // Save annual tests data
    //                         if (isset($responseVehicle['annualTests']) && is_array($responseVehicle['annualTests'])) {
    //                             foreach ($responseVehicle['annualTests'] as $test) {
    //                                 $annualTest = [
    //                                     'companyName' => $request->input('companyName') ?? null,
    //                                     'vehicle_id' => $vehicleDetailsModel2->id,
    //                                     'test_date' => $test['testDate'],
    //                                     'test_type' => $test['testType'],
    //                                     'test_result' => $test['testResult'],
    //                                     'test_certificate_number' => $test['testCertificateNumber'],
    //                                     'expiry_date' => isset($test['expiryDate']) ? $test['expiryDate'] : null,
    //                                     'number_of_defects_test' => $test['numberOfDefectsAtTest'],
    //                                     'number_of_advisory_defects_test' => $test['numberOfAdvisoryDefectsAtTest'],
    //                                 ];

    //                                 // Save annual test
    //                                 $annualTestModel = \App\Models\VehiclesAnnualTest::updateOrCreate($annualTest);

    //                                 // Save defects data
    //                                 if (isset($test['defects']) && is_array($test['defects'])) {
    //                                     foreach ($test['defects'] as $defect) {
    //                                         $defectData = [
    //                                             'companyName' => $request->input('companyName') ?? null,
    //                                             'vehicle_id' => $vehicleDetailsModel2->id,
    //                                             'annual_test_id' => $annualTestModel->id,
    //                                             'failure_item_no' => $defect['failureItemNo'],
    //                                             'failure_reason' => $defect['failureReason'],
    //                                             'severity_code' => $defect['severityCode'],
    //                                             'severity_description' => $defect['severityDescription'],
    //                                         ];
    //                                         \App\Models\VehiclesAnnualTestDefect::updateOrCreate($defectData);
    //                                     }
    //                                 }
    //                             }
    //                         }

    //                         // Set the flag for successful API 2 call
    //                         $api2Success = true;
    //                     }
    //                 }

    //                 // Check if both API calls were successful and redirect with appropriate message
    //                 if ($api1Success && $api2Success) {
    //                     return redirect()->route('contract.index')->with('success', __('Vehicle Data successfully created!'));
    //                 } elseif ($api1Success) {
    //                     // Redirect to a view with a modal for vehicle addition choice
    //                     return redirect()->route('contract.index')->with('showDriverModal', true);
    //                 } elseif ($api2Success) {
    //                     return redirect()->route('contract.index')->with('success', __('Vehicle successfully created!'));
    //                 } else {
    //                     return redirect()->route('contract.index')->with('error', __('Failed to fetch vehicle details from both APIs'));
    //                 }
    //             } catch (\Exception $e) {
    //                 // Log the error or handle it appropriately
    //                 return redirect()->route('contract.index')->with('error', __('Failed to fetch vehicle details from APIs').': '.$e->getMessage());
    //             }
    //         } else {
    //             // If the user doesn't have permission, redirect with an error message
    //             return redirect()->route('contract.index')->with('error', __('Permission denied.'));
    //         }
    //     }

    //         private function generateReminders($fleet)
    // {
    //     $startDate = \Carbon\Carbon::parse($fleet->start_date);
    //     $endDate = \Carbon\Carbon::parse($fleet->end_date);
    //     $nextReminderDate = $startDate;

    //     while ($nextReminderDate <= $endDate) {
    //         \App\Models\FleetPlannerReminder::create([
    //             'fleet_planner_id' => $fleet->id,
    //             'next_reminder_date' => $nextReminderDate->toDateString(),
    //             'status' => 'Pending',
    //         ]);

    //         switch ($fleet->interval) {
    //             case 'Day':
    //                 $nextReminderDate = $nextReminderDate->addDays($fleet->every);
    //                 break;
    //             case 'Week':
    //                 $nextReminderDate = $nextReminderDate->addWeeks($fleet->every);
    //                 break;
    //             case 'Month':
    //                 $nextReminderDate = $nextReminderDate->addMonths($fleet->every);
    //                 break;
    //         }
    //     }
    // }

    public function sendmailContract($id, Request $request)
    {

        $contract = Contract::find($id);
        $contractArr = [
            'contract_id' => $contract->id,
        ];
        $setings = Utility::settings();
        if ($setings['new_contract'] == 1) {

            $client = User::find($contract->client_name);

            $estArr = [
                'email' => $client->email,
                'contract_subject' => $contract->subject,
                'contract_client' => $client->name,
                'contract_start_date' => $contract->start_date,
                'contract_end_date' => $contract->end_date,
            ];
            $resp = Utility::sendEmailTemplate('new_contract', [$client->id => $client->email], $estArr);

            return redirect()->route('contract.show', $contract->id)->with('success', __('Email Send successfully!').(($resp['is_success'] == false && ! empty($resp['error'])) ? '<br> <span class="text-danger">'.$resp['error'].'</span>' : ''));
        }
    }

    public function signature($id)
    {
        $contract = Contract::find($id);

        return view('contract.signature', compact('contract'));

    }

    public function signatureStore(Request $request)
    {
        $contract = Contract::find($request->contract_id);

        if (\Auth::user()->type == 'company') {
            $contract->company_signature = $request->company_signature;
        }
        if (\Auth::user()->type == 'client') {
            $contract->client_signature = $request->client_signature;
        }

        $contract->save();

        return response()->json(
            [
                'Success' => true,
                'message' => __('Contract Signed successfully'),
            ], 200
        );

    }

    public function pdffromcontract($contract_id)
    {
        $id = \Illuminate\Support\Facades\Crypt::decrypt($contract_id);

        $contract = vehicleDetails::findOrFail($id);
        $vehicle = \App\Models\Vehicles::find($contract->vehicle_id);
        $annualTests = \App\Models\VehiclesAnnualTest::where('vehicle_id', $contract->vehicle_id)->get();
        // $annualDefects = \App\Models\VehiclesAnnualTestDefect::where('vehicle_id', $contract->vehicle_id)->get();

        foreach ($annualTests as $test) {
            $test->defects = \App\Models\VehiclesAnnualTestDefect::where('annual_test_id', $test->id)->get();
        }

        return view('contract.template', compact('contract', 'vehicle', 'annualTests'));

    }

    protected $notificationService;

    public function __construct(\App\Notifications\WorkAroundCompleteNotification $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    protected function sendNotificationToOperators($title, $message, $companyId, $depotId, $groupId)
    {
        // Retrieve operator FCM tokens from Users with "Notification" or "Both" preference
        $operators = \App\Models\User::where('companyname', $companyId)
            ->whereIn('walkaround_preference', ['notification', 'both']) // <-- Only notification & both
            ->whereJsonContains('depot_id', (string) $depotId)
            ->whereJsonContains('vehicle_group_id', (string) $groupId)
            ->get();

        $operatorTokens = $operators->pluck('operator_tokens')->filter()->toArray(); // Removes null/empty tokens

        \Log::info("Attempting to send notification to operators for company ID: {$companyId}");

        if (! empty($operatorTokens)) {
            \Log::info('Found operator tokens: '.implode(', ', $operatorTokens));

            // Save the notification in the database only if at least one valid recipient exists
            $notification = new \App\Models\WorkAroundNotification();
            $notification->company_id = $companyId;
            $notification->title = $title;
            $notification->message = $message;
            $notification->depot_id = $depotId; // Save depot_id from Driver model
            $notification->key = 1;
            $notification->save();

            // Send notification
            $this->notificationService->send([
                'title' => $title,
                'message' => $message,
                'tokens' => $operatorTokens,
                'target' => 'mobile_app',
            ]);
        } else {
            \Log::warning("No valid operator tokens found for company ID: {$companyId}");
        }
    }

    public function sendReminderEmails()
    {
        $today = \Carbon\Carbon::today();

        // Define reminder intervals
        $reminderIntervals = [15, 8, 7, 6, 5, 4, 3, 2, 1];

        // Generate reminder dates for each interval
        $reminderDates = [];
        $reminderTachoother = [];
        $reminderPMIDue = [];

        foreach ($reminderIntervals as $days) {
            $reminderDates[] = $today->copy()->addDays($days)->format('d F Y'); // For taxDueDate
            $reminderTachoother[] = $today->copy()->addDays($days)->format('Y-m-d'); // For other dates
            $reminderPMIDue[] = $today->copy()->addDays($days)->format('d-m-Y'); // For PMI_due
        }

        // Get vehicles with relevant due dates in the next 15 days
        $vehicleDetails = \App\Models\vehicleDetails::whereIn('taxDueDate', $reminderDates)
            ->orWhereIn('tacho_calibration', $reminderTachoother)
            ->orWhereIn('dvs_pss_permit_expiry', $reminderTachoother)
            ->orWhereIn('insurance', $reminderTachoother)
            ->orWhereIn('brake_test_due', $reminderTachoother)
            ->orWhereIn('PMI_due', $reminderPMIDue)
            ->get();

        // Prepare an array to store emails grouped by company email
        $emails = [];

        // Group vehicle details by company email
        foreach ($vehicleDetails as $vehicle) {
            $email = $vehicle->types->email;
            $companyName = $vehicle->types->name;

            if (! isset($emails[$email])) {
                $emails[$email] = [
                    'companyName' => $companyName,
                    'reminders' => [],
                ];
            }

            // Initialize the reminder array for each registration number
            if (! isset($emails[$email]['reminders'][$vehicle->registrationNumber])) {
                $emails[$email]['reminders'][$vehicle->registrationNumber] = [
                    'taxDueDate' => null,
                    'taxStatus' => null,
                    'annualTestDueDate' => null,
                    'tacho_calibration' => null,
                    'dvs_pss_permit_expiry' => null,
                    'insurance' => null,
                    'brake_test_due' => null,
                    'PMI_due' => null,
                ];
            }

            // Check each date and add reminder only if it's in the reminder dates
            if (in_array($vehicle->taxDueDate, $reminderDates)) {
                $taxDueDateCarbon = \Carbon\Carbon::createFromFormat('d F Y', $vehicle->taxDueDate);
                $emails[$email]['reminders'][$vehicle->registrationNumber]['taxDueDate'] = $taxDueDateCarbon->format('d/m/Y');
                $emails[$email]['reminders'][$vehicle->registrationNumber]['taxStatus'] = $vehicle->taxStatus;
            }

            if (in_array($vehicle->tacho_calibration, $reminderTachoother)) {
                $tachoCalibrationCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $vehicle->tacho_calibration);
                $emails[$email]['reminders'][$vehicle->registrationNumber]['tacho_calibration'] = $tachoCalibrationCarbon->format('d/m/Y');
            }

            if (in_array($vehicle->dvs_pss_permit_expiry, $reminderTachoother)) {
                $dvsPssPermitExpiryCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $vehicle->dvs_pss_permit_expiry);
                $emails[$email]['reminders'][$vehicle->registrationNumber]['dvs_pss_permit_expiry'] = $dvsPssPermitExpiryCarbon->format('d/m/Y');
            }

            if (in_array($vehicle->insurance, $reminderTachoother)) {
                $insuranceCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $vehicle->insurance);
                $emails[$email]['reminders'][$vehicle->registrationNumber]['insurance'] = $insuranceCarbon->format('d/m/Y');
            }

            // if (in_array($vehicle->date_of_inspection, $reminderTachoother)) {
            //     $dateinspectionCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $vehicle->date_of_inspection);
            //     $emails[$email]['reminders'][$vehicle->registrationNumber]['date_of_inspection'] = $dateinspectionCarbon->format('d/m/Y');
            // }

            if (in_array($vehicle->brake_test_due, $reminderTachoother)) {
                $brakeTestCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $vehicle->brake_test_due);
                $emails[$email]['reminders'][$vehicle->registrationNumber]['brake_test_due'] = $brakeTestCarbon->format('d/m/Y');
            }

            if (in_array($vehicle->PMI_due, $reminderPMIDue)) {
                $PMICarbon = \Carbon\Carbon::createFromFormat('d-m-Y', $vehicle->PMI_due);
                $emails[$email]['reminders'][$vehicle->registrationNumber]['PMI_due'] = $PMICarbon->format('d/m/Y');
            }
        }

        // Get vehicles with annual test due date in the next 15 days
        $vehicles = \App\Models\Vehicles::whereIn('annual_test_expiry_date', $reminderTachoother)->get();

        // Group vehicles by company email and merge with vehicleDetails reminders
        foreach ($vehicles as $vehicle) {
            $email = $vehicle->types->email;
            $companyName = $vehicle->types->name;

            if (! isset($emails[$email])) {
                $emails[$email] = [
                    'companyName' => $companyName,
                    'reminders' => [],
                ];
            }

            // Add annual test due date reminder
            if (in_array($vehicle->annual_test_expiry_date, $reminderTachoother)) {
                $annualTestDueDateCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $vehicle->annual_test_expiry_date);
                $emails[$email]['reminders'][$vehicle->registrations]['annual_test_expiry_date'] = $annualTestDueDateCarbon->format('d/m/Y');
            }
        }

        // Send emails and notifications to each company
        foreach ($emails as $email => $data) {
            // Check the company status before sending reminder
            $company = \App\Models\CompanyDetails::where('name', $data['companyName'])->first();

            if ($company && $company->company_status === 'Active') { // Change 'active' to the status you want
                // Send email
                \Mail::to($email)->send(new \App\Mail\VehicleReminder($data));

                // Loop through each vehicle's reminders for the registration number
                foreach ($data['reminders'] as $registrationNumber => $reminder) {
                    $mergedReminders = []; // Collect reminder names for this vehicle

                    foreach ($reminder as $reminderKey => $reminderDate) {
                        if ($reminderDate && isset($reminderNamesMap[$reminderKey])) {
                            $friendlyReminderName = $reminderNamesMap[$reminderKey];
                            $mergedReminders[] = $friendlyReminderName; // Add the reminder to the list
                        }
                    }

                    // If there are reminders for this vehicle, send a merged notification
                    if (! empty($mergedReminders)) {
                        $remindersString = implode(', ', $mergedReminders); // Combine reminder names
                        $depotId = $vehicleDetails->depot_id ?? null;

                        $this->sendNotificationToOperators(
                            'Vehicle Reminder',
                            "Important $remindersString reminder for Registration Number: $registrationNumber",
                            $company->id,
                            $depotId
                        );

                        \Log::info("Merged notification sent for $remindersString to company {$data['companyName']} for vehicle $registrationNumber.");
                    }
                }
            } else {
                \Log::info("Skipped reminder for company {$data['companyName']} as it is not active.");
            }
        }

        // Return a JSON response or redirect indicating the operation was successful
        return response()->json(['message' => 'Reminders sent successfully!'], 200);
    }

    // First API: Check and Save Reminders
    public function checkAndSaveReminders()
    {
        $today = \Carbon\Carbon::today();

        // Define reminder intervals
        $reminderIntervals = [15, 8, 7, 6, 5, 4, 3, 2, 1];

        // Generate reminder dates
        $reminderDates = [];
        $reminderTachoother = [];
        $reminderPMIDue = [];

        foreach ($reminderIntervals as $days) {
            $reminderDates[] = $today->copy()->addDays($days)->format('d F Y');
            $reminderTachoother[] = $today->copy()->addDays($days)->format('Y-m-d');
            $reminderPMIDue[] = $today->copy()->addDays($days)->format('d-m-Y');
        }

        // Get vehicles with active company only
        $vehicleDetails = \App\Models\vehicleDetails::whereHas('types', function ($query) {
            $query->where('company_status', 'Active');
        })
            ->where(function ($query) use ($reminderDates, $reminderTachoother, $reminderPMIDue) {
                $query->whereIn('taxDueDate', $reminderDates)
                    ->orWhereIn('tacho_calibration', $reminderTachoother)
                    ->orWhereIn('dvs_pss_permit_expiry', $reminderTachoother)
                    ->orWhereIn('insurance', $reminderTachoother)
                    ->orWhereIn('brake_test_due', $reminderTachoother)
                    ->orWhereIn('PMI_due', $reminderPMIDue);
            })
            ->get();

        foreach ($vehicleDetails as $vehicle) {

            // âœ… Skip if VehicleDetails status is 'Archive'
            if (\Illuminate\Support\Str::startsWith($vehicle->vehicle_status, 'Archive')) {
                continue;
            }
            $companyName = $vehicle->types->name;

            $reminderData = [];

            if (in_array($vehicle->taxDueDate, $reminderDates)) {
                $taxDueDateCarbon = \Carbon\Carbon::createFromFormat('d F Y', $vehicle->taxDueDate);
                $reminderData[] = ['type' => 'Road Tax', 'parameter' => 'taxDueDate', 'date' => $taxDueDateCarbon->format('Y-m-d')];
            }

            if (in_array($vehicle->tacho_calibration, $reminderTachoother)) {
                $tachoCalibrationCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $vehicle->tacho_calibration);
                $reminderData[] = ['type' => 'Tacho Calibration', 'parameter' => 'tacho_calibration', 'date' => $tachoCalibrationCarbon->format('Y-m-d')];
            }

            if (in_array($vehicle->dvs_pss_permit_expiry, $reminderTachoother)) {
                $dvsPssPermitExpiryCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $vehicle->dvs_pss_permit_expiry);
                $reminderData[] = ['type' => 'DVS PSS Permit Expiry', 'parameter' => 'dvs_pss_permit_expiry', 'date' => $dvsPssPermitExpiryCarbon->format('Y-m-d')];
            }

            if (in_array($vehicle->insurance, $reminderTachoother)) {
                $insuranceCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $vehicle->insurance);
                $reminderData[] = ['type' => 'Insurance', 'parameter' => 'insurance', 'date' => $insuranceCarbon->format('Y-m-d')];
            }

            if (in_array($vehicle->brake_test_due, $reminderTachoother)) {
                $brakeTestCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $vehicle->brake_test_due);
                $reminderData[] = ['type' => 'Brake Test Due', 'parameter' => 'brake_test_due', 'date' => $brakeTestCarbon->format('Y-m-d')];
            }

            if (in_array($vehicle->PMI_due, $reminderPMIDue)) {
                $PMICarbon = \Carbon\Carbon::createFromFormat('d-m-Y', $vehicle->PMI_due);
                $reminderData[] = ['type' => 'PMI Due', 'parameter' => 'PMI_due', 'date' => $PMICarbon->format('Y-m-d')];
            }

            // Save reminders in VehicleReminder table
            foreach ($reminderData as $reminder) {
                \App\Models\VehicleReminderLog::create([
                    'vehicle_id' => $vehicle->id,
                    'reminder_type' => $reminder['type'],
                    'reminder_parameter' => $reminder['parameter'],
                    'registration_number' => $vehicle->registrationNumber,
                    'company_id' => $vehicle->companyName,
                    'reminder_date' => $reminder['date'],
                    'status' => 'Pending',
                ]);
            }
        }

        $vehicles = \App\Models\Vehicles::whereHas('types', function ($query) {
            $query->where('company_status', 'Active');
        })
            ->whereIn('annual_test_expiry_date', $reminderTachoother)
            ->with('vehicleDetail') // Ensure we load related VehicleDetails
            ->get();

        // Group vehicles by company email and merge with vehicleDetails reminders
        foreach ($vehicles as $vehicle) {
            $email = $vehicle->types->email;
            $companyName = $vehicle->types->name;

            if (! isset($emails[$email])) {
                $emails[$email] = [
                    'companyName' => $companyName,
                    'reminders' => [],
                ];
            }

            // Add annual test due date reminder
            if (in_array($vehicle->annual_test_expiry_date, $reminderTachoother)) {
                $annualTestDueDateCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $vehicle->annual_test_expiry_date);

                // Get the corresponding VehicleDetails ID
                $vehicleDetails = $vehicle->vehicleDetail; // Ensure relationship exists

                if (! $vehicleDetails) {
                    // \Log::warning("Skipping vehicle ID {$vehicle->id} - No matching VehicleDetails found.");
                    continue; // Skip if no matching VehicleDetails
                }

                // skip if VehicleDetails status is 'Archive'
                if (\Illuminate\Support\Str::startsWith($vehicleDetails->vehicle_status, 'Archive')) {
                    continue;
                }

                // Save MOT Reminder **without checking for duplicates**
                \App\Models\VehicleReminderLog::create([
                    'vehicle_id' => $vehicleDetails->id, // Use VehicleDetails ID
                    'registration_number' => $vehicleDetails->registrationNumber,
                    'company_id' => $vehicle->companyName,
                    'reminder_type' => 'MOT',
                    'reminder_parameter' => 'annual_test_expiry_date',
                    'reminder_date' => $vehicle->annual_test_expiry_date,
                    'status' => 'Pending',
                ]);

                // \Log::info("MOT Reminder saved for Vehicle ID {$vehicleDetails->id}");
            }
        }

        return response()->json(['message' => 'Reminders sent successfully!'], 200);
    }

    // Separate API: Send Emails and Notifications
    // public function sendPendingReminders()
    // {
    //     $pendingReminders = \App\Models\VehicleReminderLog::whereIn('status', ['Pending', 'Failed'])->get();
    //     $emails = [];

    //     foreach ($pendingReminders as $reminder) {
    //         $vehicle = \App\Models\vehicleDetails::find($reminder->vehicle_id);
    //         if (!$vehicle) {
    //             $reminder->update(['status' => 'Failed']); // Mark as failed if vehicle is missing
    //             continue;
    //         }

    //         $email = $vehicle->types->email;
    //         $companyName = $vehicle->types->name;

    //         if (!isset($emails[$email])) {
    //             $emails[$email] = [
    //                 'companyName' => $companyName,
    //                 'reminders' => [],
    //                 'reminder_ids' => [],
    //             ];
    //         }

    //         // Format the date to DD/MM/YYYY
    //         $formattedReminderDate = $reminder->reminder_date
    //             ? Carbon::parse($reminder->reminder_date)->format('d/m/Y')
    //             : '-';

    //         $emails[$email]['reminders'][$vehicle->registrationNumber][$reminder->reminder_parameter] = $formattedReminderDate;

    //         if ($reminder->reminder_parameter === 'taxDueDate') {
    //             $emails[$email]['reminders'][$vehicle->registrationNumber]['taxStatus'] = $vehicle->taxStatus ?? '-';
    //         }

    //         $emails[$email]['reminder_ids'][] = $reminder->id;
    //     }

    //     foreach ($emails as $mainEmail => $data) {
    //         $company = \App\Models\CompanyDetails::where('name', $data['companyName'])->first();
    //         if ($company && $company->company_status === 'Active') {
    //             // Collect all recipient emails: main email + operator emails
    //             $recipientEmails = [$mainEmail];

    //             if (!empty($company->operator_email)) {
    //                 $operatorEmails = json_decode($company->operator_email, true);
    //                 if (is_array($operatorEmails)) {
    //                     $recipientEmails = array_merge($recipientEmails, $operatorEmails);
    //                 }
    //             }

    //             try {
    //                 foreach ($recipientEmails as $recipientEmail) {
    //                     \Mail::to($recipientEmail)->send(new \App\Mail\VehicleReminder($data));
    //                 }

    //                 // Mark reminders as 'Sent' after a successful email
    //                 \App\Models\VehicleReminderLog::whereIn('id', $data['reminder_ids'])->update(['status' => 'Sent']);

    //                 foreach ($data['reminders'] as $registrationNumber => $reminder) {
    //                     $mergedReminders = [];

    //                     foreach ($reminder as $reminderKey => $reminderDate) {
    //                         if ($reminderDate) {
    //                             $mergedReminders[] = ucfirst(str_replace('_', ' ', $reminderKey));
    //                         }
    //                     }

    //                     if (!empty($mergedReminders)) {
    //                         $remindersString = implode(', ', $mergedReminders);

    //                         // Fetch the correct depot_id
    //                         $vehicle = \App\Models\VehicleDetails::where('registrationNumber', $registrationNumber)->first();
    //                         $depotId = $vehicle ? $vehicle->depot_id : null;

    //                         $this->sendNotificationToOperators(
    //                             'Vehicle Reminder',
    //                             "Important $remindersString reminder for Registration Number: $registrationNumber",
    //                             $company->id,
    //                             $depotId
    //                         );
    //                     }
    //                 }
    //             } catch (\Exception $e) {
    //                 \App\Models\VehicleReminderLog::whereIn('id', $data['reminder_ids'])->update(['status' => 'Failed']);
    //                  \Log::error("Failed to send reminder email to $mainEmail. Error: " . $e->getMessage());
    //             }
    //         } else {
    //             \App\Models\VehicleReminderLog::whereIn('id', $data['reminder_ids'])->update(['status' => 'Failed']);
    //              \Log::warning("Company {$data['companyName']} is inactive, reminders not sent to $mainEmail.");
    //         }
    //     }

    //     return response()->json(['message' => 'Pending reminders processed successfully!'], 200);
    // }

    private function getOperatorEmails($companyId, $depotId, $vehicleGroupId)
{
    return \App\Models\User::where('companyname', $companyId)
        ->whereJsonContains('depot_id', (string) $depotId)
        ->whereJsonContains('vehicle_group_id', (string) $vehicleGroupId)
        ->pluck('email')
        ->toArray();
}


    public function sendPendingReminders()
    {
        $pendingReminders = \App\Models\VehicleReminderLog::whereIn('status', ['Pending', 'Failed'])->get();
        $emails = [];

        $successCount = 0; // âœ… Count of successfully sent reminders
        $failedCount = 0;  // âœ… Count of failed reminders

        foreach ($pendingReminders as $reminder) {
            $vehicle = \App\Models\vehicleDetails::find($reminder->vehicle_id);
            if (! $vehicle) {
                $reminder->update(['status' => 'Failed']);
                $failedCount++;

                continue;
            }

            $email = $vehicle->types->email;
            $companyName = $vehicle->types->name;

            if (! isset($emails[$email])) {
                $emails[$email] = [
                    'companyName' => $companyName,
                    'reminders' => [],
                    'reminder_ids' => [],
                    'depot_id' => $vehicle->depot_id,
                    'group_id' => $vehicle->group_id,
                ];
            }

            $formattedReminderDate = $reminder->reminder_date
                ? Carbon::parse($reminder->reminder_date)->format('d/m/Y')
                : '-';

            $emails[$email]['reminders'][$vehicle->registrationNumber][$reminder->reminder_parameter] = $formattedReminderDate;

            if ($reminder->reminder_parameter === 'taxDueDate') {
                $emails[$email]['reminders'][$vehicle->registrationNumber]['taxStatus'] = $vehicle->taxStatus ?? '-';
            }

            $emails[$email]['reminder_ids'][] = $reminder->id;
        }

        foreach ($emails as $mainEmail => $data) {
            $company = \App\Models\CompanyDetails::where('name', $data['companyName'])->first();
            if ($company && $company->company_status === 'Active') {
                $recipientEmails = [$mainEmail];

                // if (! empty($company->operator_email)) {
                //     $operatorEmails = json_decode($company->operator_email, true);
                //     if (is_array($operatorEmails)) {
                //         $recipientEmails = array_merge($recipientEmails, $operatorEmails);
                //     }
                // }

                $operatorEmails = $this->getOperatorEmails(
                    $company->id,
                    $data['depot_id'],
                    $data['group_id']
                );

                $recipientEmails = array_merge($recipientEmails, $operatorEmails);
                $recipientEmails = array_unique($recipientEmails);

                $emailSent = false; // âœ… track if at least one email sent successfully

                foreach ($recipientEmails as $recipientEmail) {
                    try {
                        \Mail::to($recipientEmail)->send(new \App\Mail\VehicleReminder($data));
                        //                          \Log::info('Vehicle Reminder Email (TEST MODE)', [
                        //     'email' => $recipientEmail,
                        //     'type' => ($recipientEmail == $mainEmail) ? 'Company' : 'Operator/Manager',
                        //     'company' => $data['companyName'],
                        //     'reminders_count' => count($data['reminder_ids']),
                        // ]);
                        $emailSent = true;
                    } catch (\Exception $e) {
                        \Log::error("Failed to send reminder email to $recipientEmail. Error: ".$e->getMessage());
                    }
                }

                if ($emailSent) {
                    \App\Models\VehicleReminderLog::whereIn('id', $data['reminder_ids'])->update(['status' => 'Sent']);
                    $successCount += count($data['reminder_ids']); // âœ… Add to success count

                    foreach ($data['reminders'] as $registrationNumber => $reminder) {
                        $mergedReminders = [];

                        foreach ($reminder as $reminderKey => $reminderDate) {
                            if ($reminderDate) {
                                $mergedReminders[] = ucfirst(str_replace('_', ' ', $reminderKey));
                            }
                        }

                        if (! empty($mergedReminders)) {
                            $remindersString = implode(', ', $mergedReminders);

                            $vehicle = \App\Models\vehicleDetails::where('registrationNumber', $registrationNumber)->first();
                            $depotId = $vehicle ? $vehicle->depot_id : null;

                            $this->sendNotificationToOperators(
                                'Vehicle Reminder',
                                "Important $remindersString reminder for Registration Number: $registrationNumber",
                                $company->id,
                                $depotId,
                                $vehicle->group_id
                            );
                        }
                    }
                } else {
                    \App\Models\VehicleReminderLog::whereIn('id', $data['reminder_ids'])->update(['status' => 'Failed']);
                    $failedCount += count($data['reminder_ids']); // âœ… Add to failed count
                }
            } else {
                \App\Models\VehicleReminderLog::whereIn('id', $data['reminder_ids'])->update(['status' => 'Failed']);
                $failedCount += count($data['reminder_ids']); // âœ… Add to failed count
                // \Log::warning("Company {$data['companyName']} is inactive, reminders not sent to $mainEmail.");
            }
        }

        return response()->json([
            'message' => 'Pending reminders processed successfully!',
            'success_count' => $successCount,
            'failed_count' => $failedCount,
        ], 200);
    }

    public function saveOverdueReminders()
    {
        $today = \Carbon\Carbon::today();

        // Define overdue interval (only exactly 7 days overdue)
        $overdueDays = 7;

        // Calculate the exact overdue date
        $overdueDate = $today->copy()->subDays($overdueDays)->format('d F Y'); // Tax Due Date
        $overdueTachoother = $today->copy()->subDays($overdueDays)->format('Y-m-d'); // Other dates
        $overduePMIDue = $today->copy()->subDays($overdueDays)->format('d-m-Y'); // PMI_due

        // Fetch vehicle details with active company only
        $vehicleDetails = \App\Models\vehicleDetails::whereHas('types', function ($query) {
            $query->where('company_status', 'Active');
        })
            ->where(function ($query) use ($overdueDate, $overdueTachoother, $overduePMIDue) {
                $query->where('taxDueDate', '=', $overdueDate)
                    ->orWhere('tacho_calibration', '=', $overdueTachoother)
                    ->orWhere('dvs_pss_permit_expiry', '=', $overdueTachoother)
                    ->orWhere('insurance', '=', $overdueTachoother)
                    ->orWhere('brake_test_due', '=', $overdueTachoother)
                    ->orWhere('PMI_due', '=', $overduePMIDue);
            })
            ->get();

        // Get vehicles with overdue annual test expiry date (only 7 days overdue)
        $vehicles = \App\Models\Vehicles::whereHas('types', function ($query) {
            $query->where('company_status', 'Active');
        })
            ->where('annual_test_expiry_date', '=', $overdueTachoother)->with('vehicleDetail')->get();

        foreach ($vehicleDetails as $vehicle) {
            // skip tatus starts with 'Archive'
            if (\Illuminate\Support\Str::startsWith($vehicle->vehicle_status, 'Archive')) {
                continue;
            }

            $companyName = $vehicle->types->name;

            $reminderData = [];

            if ($vehicle->taxDueDate == $overdueDate) {
                $taxDueDateCarbon = \Carbon\Carbon::createFromFormat('d F Y', $vehicle->taxDueDate);
                $reminderData[] = ['type' => 'Road Tax', 'parameter' => 'taxDueDate', 'date' => $taxDueDateCarbon->format('Y-m-d')];

            }

            if ($vehicle->tacho_calibration == $overdueTachoother) {
                $tachoCalibrationCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $vehicle->tacho_calibration);
                $reminderData[] = ['type' => 'Tacho Calibration', 'parameter' => 'tacho_calibration', 'date' => $tachoCalibrationCarbon->format('Y-m-d')];
            }

            if ($vehicle->dvs_pss_permit_expiry == $overdueTachoother) {
                $dvsPssPermitExpiryCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $vehicle->dvs_pss_permit_expiry);
                $reminderData[] = ['type' => 'DVS PSS Permit Expiry', 'parameter' => 'dvs_pss_permit_expiry', 'date' => $dvsPssPermitExpiryCarbon->format('Y-m-d')];
            }

            if ($vehicle->insurance == $overdueTachoother) {
                $insuranceCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $vehicle->insurance);
                $reminderData[] = ['type' => 'Insurance', 'parameter' => 'insurance', 'date' => $insuranceCarbon->format('Y-m-d')];
            }

            if ($vehicle->brake_test_due == $overdueTachoother) {
                $brakeTestCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $vehicle->brake_test_due);
                $reminderData[] = ['type' => 'Brake Test Due', 'parameter' => 'brake_test_due', 'date' => $brakeTestCarbon->format('Y-m-d')];
            }

            if ($vehicle->PMI_due == $overduePMIDue) {
                $PMICarbon = \Carbon\Carbon::createFromFormat('d-m-Y', $vehicle->PMI_due);
                $reminderData[] = ['type' => 'PMI Due', 'parameter' => 'PMI_due', 'date' => $PMICarbon->format('Y-m-d')];
            }

            // Save reminders in VehicleReminder table
            foreach ($reminderData as $reminder) {
                \App\Models\VehicleOverdueReminderLog::create([
                    'vehicle_id' => $vehicle->id,
                    'reminder_type' => $reminder['type'],
                    'reminder_parameter' => $reminder['parameter'],
                    'registration_number' => $vehicle->registrationNumber,
                    'company_id' => $vehicle->companyName,
                    'overdue_date' => $reminder['date'],
                    'status' => 'Pending',
                ]);
            }
        }

        foreach ($vehicles as $vehicle) {
            $companyName = $vehicle->types->name;

            if ($vehicle->annual_test_expiry_date == $overdueTachoother) {
                $annualTestDueDateCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $vehicle->annual_test_expiry_date);

                // Get the corresponding VehicleDetails ID
                $vehicleDetails = $vehicle->vehicleDetail; // Ensure relationship exists

                if (! $vehicleDetails) {
                    // \Log::warning("Skipping vehicle ID {$vehicle->id} - No matching VehicleDetails found.");
                    continue; // Skip if no matching VehicleDetails
                }

                // âœ… Skip if VehicleDetails status starts with 'Archive'
                if (\Illuminate\Support\Str::startsWith($vehicleDetails->vehicle_status, 'Archive')) {
                    continue;
                }

                // Save MOT Reminder **without checking for duplicates**
                \App\Models\VehicleOverdueReminderLog::create([
                    'vehicle_id' => $vehicleDetails->id, // Use VehicleDetails ID
                    'registration_number' => $vehicleDetails->registrationNumber,
                    'company_id' => $vehicle->companyName,
                    'reminder_type' => 'MOT',
                    'reminder_parameter' => 'annual_test_expiry_date',
                    'overdue_date' => $vehicle->annual_test_expiry_date,
                    'status' => 'Pending',
                ]);
            }
        }

        return response()->json(['message' => 'Overdue reminders saved successfully!'], 200);
    }

    public function sendOverdueReminderEmails()
    {
        $pendingReminders = \App\Models\VehicleOverdueReminderLog::whereIn('status', ['Pending', 'Failed'])->get();
        $emails = [];

        foreach ($pendingReminders as $reminder) {
            $vehicle = \App\Models\vehicleDetails::find($reminder->vehicle_id);
            if (! $vehicle) {
                $reminder->update(['status' => 'Failed']);

                continue;
            }

            $email = $vehicle->types->email;
            $companyName = $vehicle->types->name;

            if (! isset($emails[$email])) {
                $emails[$email] = [
                    'companyName' => $companyName,
                    'overdue' => [],
                    'reminder_ids' => [],
                    'depot_id' => $vehicle->depot_id,
                    'group_id' => $vehicle->group_id,
                ];
            }

            // Format the date to DD/MM/YYYY
            $formattedReminderDate = $reminder->overdue_date
                ? Carbon::parse($reminder->overdue_date)->format('d/m/Y')
                : '-';

            $emails[$email]['overdue'][$vehicle->registrationNumber][$reminder->reminder_parameter] = $formattedReminderDate;

            if ($reminder->reminder_parameter === 'taxDueDate') {
                $emails[$email]['overdue'][$vehicle->registrationNumber]['taxStatus'] = $vehicle->taxStatus ?? '-';
            }

            $emails[$email]['reminder_ids'][] = $reminder->id;
        }

        foreach ($emails as $mainEmail => $data) {
            $company = \App\Models\CompanyDetails::where('name', $data['companyName'])->first();
            if ($company && $company->company_status === 'Active') {
                // Get all recipients: main email + operator emails
                $recipientEmails = [$mainEmail];

                // if (! empty($company->operator_email)) {
                //     $operatorEmails = json_decode($company->operator_email, true);
                //     if (is_array($operatorEmails)) {
                //         $recipientEmails = array_merge($recipientEmails, $operatorEmails);
                //     }
                // }

                $operatorEmails = $this->getOperatorEmails(
                    $company->id,
                    $data['depot_id'],
                    $data['group_id']
                );

                $recipientEmails = array_merge($recipientEmails, $operatorEmails);
                $recipientEmails = array_unique($recipientEmails);

                try {
                    foreach ($recipientEmails as $recipientEmail) {
                        \Mail::to($recipientEmail)->send(new \App\Mail\OverdueVehicleReminder($data));
                        //                         \Log::info('Overdue Vehicle Reminder (TEST MODE)', [
                        //     'email' => $recipientEmail,
                        //     'type' => ($recipientEmail == $mainEmail) ? 'Company' : 'Operator/Manager',
                        //     'company' => $data['companyName'],
                        //     'reminders_count' => count($data['reminder_ids']),
                        // ]);
                    }

                    \App\Models\VehicleOverdueReminderLog::whereIn('id', $data['reminder_ids'])->update(['status' => 'Sent']);
                } catch (\Exception $e) {
                    \App\Models\VehicleOverdueReminderLog::whereIn('id', $data['reminder_ids'])->update(['status' => 'Failed']);
                    // \Log::error("Failed to send overdue reminder to $mainEmail. Error: " . $e->getMessage());
                }
            } else {
                \App\Models\VehicleOverdueReminderLog::whereIn('id', $data['reminder_ids'])->update(['status' => 'Failed']);
                // \Log::warning("Company {$data['companyName']} is inactive. Overdue reminders not sent to $mainEmail.");
            }
        }

        return response()->json(['message' => 'Overdue reminder emails sent successfully!'], 200);
    }

    public function sendOverdueEmails()
    {
        $today = \Carbon\Carbon::today();

        // Define overdue interval (only exactly 7 days overdue)
        $overdueDays = 7;

        // Calculate the exact overdue date
        $overdueDate = $today->copy()->subDays($overdueDays)->format('d F Y'); // Tax Due Date
        $overdueTachoother = $today->copy()->subDays($overdueDays)->format('Y-m-d'); // Other dates
        $overduePMIDue = $today->copy()->subDays($overdueDays)->format('d-m-Y'); // PMI_due

        // Get vehicles where the due date is **exactly** 7 days overdue
        $vehicleDetails = \App\Models\vehicleDetails::where('taxDueDate', '=', $overdueDate)
            ->orWhere('tacho_calibration', '=', $overdueTachoother)
            ->orWhere('dvs_pss_permit_expiry', '=', $overdueTachoother)
            ->orWhere('insurance', '=', $overdueTachoother)
            ->orWhere('brake_test_due', '=', $overdueTachoother)
            ->orWhere('PMI_due', '=', $overduePMIDue)
            ->get();

        // Get vehicles with overdue annual test expiry date (only 7 days overdue)
        $vehicles = \App\Models\Vehicles::where('annual_test_expiry_date', '=', $overdueTachoother)->get();

        $emails = [];

        foreach ($vehicleDetails as $vehicle) {
            $email = $vehicle->types->email;
            $companyName = $vehicle->types->name;

            if (! isset($emails[$email])) {
                $emails[$email] = [
                    'companyName' => $companyName,
                    'overdue' => [],
                ];
            }

            if (! isset($emails[$email]['overdue'][$vehicle->registrationNumber])) {
                $emails[$email]['overdue'][$vehicle->registrationNumber] = [];
            }

            if ($vehicle->taxDueDate == $overdueDate) {
                $emails[$email]['overdue'][$vehicle->registrationNumber]['taxDueDate'] = \Carbon\Carbon::parse($vehicle->taxDueDate)->format('d/m/Y');
            }

            if ($vehicle->tacho_calibration == $overdueTachoother) {
                $emails[$email]['overdue'][$vehicle->registrationNumber]['tacho_calibration'] = \Carbon\Carbon::parse($vehicle->tacho_calibration)->format('d/m/Y');
            }

            if ($vehicle->dvs_pss_permit_expiry == $overdueTachoother) {
                $emails[$email]['overdue'][$vehicle->registrationNumber]['dvs_pss_permit_expiry'] = \Carbon\Carbon::parse($vehicle->dvs_pss_permit_expiry)->format('d/m/Y');
            }

            if ($vehicle->insurance == $overdueTachoother) {
                $emails[$email]['overdue'][$vehicle->registrationNumber]['insurance'] = \Carbon\Carbon::parse($vehicle->insurance)->format('d/m/Y');
            }

            if ($vehicle->brake_test_due == $overdueTachoother) {
                $emails[$email]['overdue'][$vehicle->registrationNumber]['brake_test_due'] = \Carbon\Carbon::parse($vehicle->brake_test_due)->format('d/m/Y');
            }

            if ($vehicle->PMI_due == $overduePMIDue) {
                $emails[$email]['overdue'][$vehicle->registrationNumber]['PMI_due'] = \Carbon\Carbon::parse($vehicle->PMI_due)->format('d/m/Y');
            }
        }

        foreach ($vehicles as $vehicle) {
            $email = $vehicle->types->email;
            $companyName = $vehicle->types->name;

            if (! isset($emails[$email])) {
                $emails[$email] = [
                    'companyName' => $companyName,
                    'overdue' => [],
                ];
            }

            if (! isset($emails[$email]['overdue'][$vehicle->registrations])) {
                $emails[$email]['overdue'][$vehicle->registrations] = [];
            }

            if ($vehicle->annual_test_expiry_date == $overdueTachoother) {
                $emails[$email]['overdue'][$vehicle->registrations]['annual_test_expiry_date'] = \Carbon\Carbon::parse($vehicle->annual_test_expiry_date)->format('d/m/Y');
            }
        }

        foreach ($emails as $email => $data) {
            $company = \App\Models\CompanyDetails::where('name', $data['companyName'])->first();
            if ($company && $company->company_status === 'Active') {
                \Mail::to($email)->send(new \App\Mail\OverdueVehicleReminder($data));
                \Log::info("Overdue reminder sent to {$data['companyName']} for vehicles.");
            }
        }

        return response()->json(['message' => 'Overdue reminders sent successfully!'], 200);
    }

    public function deleteSentReminders()
    {
        // Delete 'Sent' and 'Failed' reminders from VehicleReminderLog
        $deletedReminders = \App\Models\VehicleReminderLog::whereIn('status', ['Sent', 'Failed'])->delete();

        // Delete 'Sent' and 'Failed' reminders from VehicleOverdueReminderLog
        $deletedOverdueReminders = \App\Models\VehicleOverdueReminderLog::whereIn('status', ['Sent', 'Failed'])->delete();

        $deletedDriverReminders = \App\Models\DriverReminderLog::whereIn('status', ['Sent', 'Failed'])->delete();
        $deletedDriverAutomationReminders = \App\Models\AutomationEmailLog::whereIn('status', ['Sent', 'Failed'])->delete();

        return response()->json([
            'message' => 'Sent and Failed reminders deleted successfully!',
            'deleted_vehicle_reminders' => $deletedReminders,
            'deleted_overdue_reminders' => $deletedOverdueReminders,
            'deleted_driver_reminders' => $deletedDriverReminders,
            'deleted_driver_automation_reminders' => $deletedDriverAutomationReminders,
        ], 200);
    }

 public function vehicleDataexport(Request $request)
    {
        $loggedInUser = \Auth::user();
        $companyName = $loggedInUser->companyname; // Company name of the logged-in user

        // Handle multiple depot IDs (convert stored JSON to array if needed)
        $depotIds = is_array($loggedInUser->depot_id) ? $loggedInUser->depot_id : json_decode($loggedInUser->depot_id, true);
        if (! is_array($depotIds)) {
            $depotIds = [$loggedInUser->depot_id]; // Ensure it remains an array
        }

                // Handle driver group restriction
$userGroupIds = is_array($loggedInUser->vehicle_group_id)
    ? $loggedInUser->vehicle_group_id
    : json_decode($loggedInUser->vehicle_group_id, true);

if (!is_array($userGroupIds)) {
    $userGroupIds = [];
}

        // Get the filters from the request
        $selectedCompanyId = $request->input('company_id');
        $selectedFilterColumn = $request->input('filter_column');
        $selectedFilterValue = $request->input('filter_value');
         $selectedDepotIds = $request->input('depot_id');
        $selectedVehicleStatus = $request->input('vehicle_status'); // <-- new filter
        $selectedIds = $request->has('ids') ? explode(',', $request->input('ids')) : [];
        $selectedGroupId = $request->input('group_id');

        // Define the date range for "Expiry Soon" (e.g., within the next 15 days)
        $expirySoonDate = now()->addDays(15); // 15 days from now
        $today = now(); // Current date

        // Prepare the query based on the role and filters
        if (\Auth::user()->hasRole('company') || \Auth::user()->hasRole('PTC manager')) {
            $dataQuery = \App\Models\vehicleDetails::with('types', 'vehicle', 'depot')
             ->when(! empty($selectedDepotIds), function ($query) use ($selectedDepotIds) {
                    return $query->where('depot_id', $selectedDepotIds);
                })
                ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                return $query->where('group_id',$selectedGroupId);
            })
                ->when(! $selectedVehicleStatus, function ($q) {
                    $q->where(function ($subQ) {
                        $subQ->whereNull('vehicle_status')
                            ->orWhere('vehicle_status', '')
                            ->orWhere('vehicle_status', 'not like', 'Archive%');
                    });
                });

            // Apply the company filter
            if ($selectedCompanyId) {
                $dataQuery->where('companyName', $selectedCompanyId);
            }

            if ($selectedDepotIds) {
                $dataQuery->where('depot_id', $selectedDepotIds);
            }

            if ($selectedVehicleStatus) {
                if (strtolower($selectedVehicleStatus) === 'archive') {
                    $dataQuery->where(function ($q) {
                        $q->where('vehicle_status', 'Archive')
                            ->orWhere('vehicle_status', 'like', 'Archive%');
                    });
                } else {
                    $dataQuery->where('vehicle_status', $selectedVehicleStatus);
                }
            }

            if (! empty($selectedIds)) {
                $dataQuery->whereIn('id', $selectedIds);
            }

            // Apply the company status filter to ensure only 'Active' companies are included
            $dataQuery->whereHas('types', function ($query) {
                $query->where('company_status', 'Active'); // Only 'Active' company status
            });

            // Apply the filter column and value
            if ($selectedFilterColumn && $selectedFilterValue) {
                $dataQuery->when($selectedFilterColumn && $selectedFilterValue, function ($query) use ($selectedFilterColumn, $selectedFilterValue, $expirySoonDate, $today) {
                    if ($selectedFilterColumn == 'taxDueDate') {
                        if ($selectedFilterValue == 'expiry') {
                            return $query->whereDate(\DB::raw('STR_TO_DATE(taxDueDate, "%d %M %Y")'), '<', $today); // Expired
                        }
                        if ($selectedFilterValue == 'expiry_soon') {
                            return $query->whereDate(\DB::raw('STR_TO_DATE(taxDueDate, "%d %M %Y")'), '>=', $today)
                                ->whereDate(\DB::raw('STR_TO_DATE(taxDueDate, "%d %M %Y")'), '<=', $expirySoonDate); // Expiry soon
                        }
                    }

                    if ($selectedFilterColumn == 'PMI_due') {
                        if ($selectedFilterValue == 'expiry') {
                            return $query->whereDate(\DB::raw('STR_TO_DATE(PMI_due, "%d-%m-%Y")'), '<', $today); // Expired
                        }
                        if ($selectedFilterValue == 'expiry_soon') {
                            return $query->whereDate(\DB::raw('STR_TO_DATE(PMI_due, "%d-%m-%Y")'), '>=', $today)
                                ->whereDate(\DB::raw('STR_TO_DATE(PMI_due, "%d-%m-%Y")'), '<=', $expirySoonDate); // Expiry soon
                        }
                    }

                    if (in_array($selectedFilterColumn, ['tacho_calibration', 'dvs_pss_permit_expiry', 'insurance', 'brake_test_due', 'annual_test_expiry_date'])) {
                        if ($selectedFilterColumn == 'annual_test_expiry_date') {
                            if ($selectedFilterValue == 'expiry') {
                                return $query->whereHas('vehicle', function ($q) use ($today) {
                                    $q->whereDate('annual_test_expiry_date', '<', $today); // Expired
                                });
                            }
                            if ($selectedFilterValue == 'expiry_soon') {
                                return $query->whereHas('vehicle', function ($q) use ($expirySoonDate, $today) {
                                    $q->whereDate('annual_test_expiry_date', '>=', $today)
                                        ->whereDate('annual_test_expiry_date', '<=', $expirySoonDate); // Expiry soon (within 15 days)
                                });
                            }
                        }
                        if ($selectedFilterValue == 'expiry') {
                            return $query->whereDate($selectedFilterColumn, '<', $today); // Expired
                        }
                        if ($selectedFilterValue == 'expiry_soon') {
                            return $query->whereDate($selectedFilterColumn, '>=', $today)
                                ->whereDate($selectedFilterColumn, '<=', $expirySoonDate); // Expiry soon
                        }
                    }
                });
            }

            // Get the filtered data
            $data = $dataQuery->get();
        } else {
            // If the user doesn't have the 'company' role, filter only by the user's company
            $data = \App\Models\vehicleDetails::with('types', 'vehicle', 'depot')
                ->where('companyName', \Auth::user()->companyname)
                ->whereIn('depot_id', $depotIds)->whereIn('group_id', $userGroupIds)
                 ->when(! empty($selectedDepotIds), function ($query) use ($selectedDepotIds) {
                    return $query->where('depot_id', $selectedDepotIds);
                })
                ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                return $query->where('group_id',$selectedGroupId);
            })
                ->when(! $selectedVehicleStatus, function ($q) {
                    $q->where(function ($subQ) {
                        $subQ->whereNull('vehicle_status')
                            ->orWhere('vehicle_status', '')
                            ->orWhere('vehicle_status', 'not like', 'Archive%');
                    });
                })
                ->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active'); // Only 'Active' company status
                })
                ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('companyName', $selectedCompanyId);
                })->when($selectedVehicleStatus, function ($q) use ($selectedVehicleStatus) {
                    if (strtolower($selectedVehicleStatus) === 'archive') {
                        return $q->where(function ($subQ) {
                            $subQ->where('vehicle_status', 'Archive')
                                ->orWhere('vehicle_status', 'like', 'Archive%');
                        });
                    }

                    return $q->where('vehicle_status', $selectedVehicleStatus);
                })
                ->when(! empty($selectedIds), function ($query) use ($selectedIds) {   // âœ… added for selected checkboxes
                    return $query->whereIn('id', $selectedIds);
                })->when($selectedFilterColumn && $selectedFilterValue, function ($query) use ($selectedFilterColumn, $selectedFilterValue, $expirySoonDate, $today) {
                    if ($selectedFilterColumn == 'taxDueDate') {
                        if ($selectedFilterValue == 'expiry') {
                            return $query->whereDate(\DB::raw('STR_TO_DATE(taxDueDate, "%d %M %Y")'), '<', $today); // Expired
                        }
                        if ($selectedFilterValue == 'expiry_soon') {
                            return $query->whereDate(\DB::raw('STR_TO_DATE(taxDueDate, "%d %M %Y")'), '>=', $today)
                                ->whereDate(\DB::raw('STR_TO_DATE(taxDueDate, "%d %M %Y")'), '<=', $expirySoonDate); // Expiry soon
                        }
                    }

                    if ($selectedFilterColumn == 'PMI_due') {
                        if ($selectedFilterValue == 'expiry') {
                            return $query->whereDate(\DB::raw('STR_TO_DATE(PMI_due, "%d-%m-%Y")'), '<', $today); // Expired
                        }
                        if ($selectedFilterValue == 'expiry_soon') {
                            return $query->whereDate(\DB::raw('STR_TO_DATE(PMI_due, "%d-%m-%Y")'), '>=', $today)
                                ->whereDate(\DB::raw('STR_TO_DATE(PMI_due, "%d-%m-%Y")'), '<=', $expirySoonDate); // Expiry soon
                        }
                    }

                    if (in_array($selectedFilterColumn, ['tacho_calibration', 'dvs_pss_permit_expiry', 'insurance', 'brake_test_due', 'annual_test_expiry_date'])) {
                        if ($selectedFilterColumn == 'annual_test_expiry_date') {
                            if ($selectedFilterValue == 'expiry') {
                                return $query->whereHas('vehicle', function ($q) use ($today) {
                                    $q->whereDate('annual_test_expiry_date', '<', $today); // Expired
                                });
                            }
                            if ($selectedFilterValue == 'expiry_soon') {
                                return $query->whereHas('vehicle', function ($q) use ($expirySoonDate, $today) {
                                    $q->whereDate('annual_test_expiry_date', '>=', $today)
                                        ->whereDate('annual_test_expiry_date', '<=', $expirySoonDate); // Expiry soon (within 15 days)
                                });
                            }
                        }
                        if ($selectedFilterValue == 'expiry') {
                            return $query->whereDate($selectedFilterColumn, '<', $today); // Expired
                        }
                        if ($selectedFilterValue == 'expiry_soon') {
                            return $query->whereDate($selectedFilterColumn, '>=', $today)
                                ->whereDate($selectedFilterColumn, '<=', $expirySoonDate); // Expiry soon
                        }
                    }
                })
                ->get();
        }

        // Adjust the export logic as per your requirement
        $name = 'Vehicle Data_'.date('d-m-Y');

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\VehicleDataExport($data), $name.'.xlsx');
    }


    public function updateVehicleStatus()
    {
        // Get current date
        $currentDate = Carbon::now();

        // Get all vehicle details and vehicles
        $vehicleDetails = vehicleDetails::all();
        $vehicles = \App\Models\Vehicles::all();

        // Update vehicle details statuses
        foreach ($vehicleDetails as $vehicleDetail) {
            // Update Tacho Status
            $vehicleDetail->tacho_status = $this->updateStatus($vehicleDetail->tacho_calibration, $currentDate);

            // Update DVS PSS Permit Status
            $vehicleDetail->dvs_pss_status = $this->updateStatus($vehicleDetail->dvs_pss_permit_expiry, $currentDate);

            // Update Insurance Status
            $vehicleDetail->insurance_status = $this->updateStatus($vehicleDetail->insurance, $currentDate);

            // Update PMI Status
            $vehicleDetail->PMI_status = $this->updateStatus($vehicleDetail->PMI_due, $currentDate);

            // Update Brake Test Status
            $vehicleDetail->brake_test_status = $this->updateStatus($vehicleDetail->brake_test_due, $currentDate);

            // Update Tax Due Date Status
            $vehicleDetail->taxDueDate_status = $this->updateStatus($vehicleDetail->taxDueDate, $currentDate);

            // Save changes to vehicleDetails
            $vehicleDetail->save();
        }

        // Update vehicles statuses
        foreach ($vehicles as $vehicle) {
            // Update Annual Test Status
            $vehicle->annual_test_status = $this->updateStatus($vehicle->annual_test_expiry_date, $currentDate);

            // Save changes to vehicles
            $vehicle->save();
        }

        return response()->json(['message' => 'Statuses updated successfully'], 200);
    }

    /**
     * Function to determine the status based on date.
     */
    //   private function updateStatus($date, $currentDate)
    // {
    //     // If the date is null or '-', return 'VALID'
    //     if (is_null($date) || $date === '-') {
    //         return 'VALID';
    //     }

    //     // Parse the date
    //     $date = Carbon::parse($date);

    //     // Calculate the difference in days (negative if already expired)
    //     $daysDifference = $currentDate->diffInDays($date, false);

    //     // If the difference is 0 or less than or equal to 15, mark as "EXPIRING SOON"
    //     if ($daysDifference >= 0 && $daysDifference < 15) {
    //         return 'EXPIRING SOON';
    //     }

    //     // If the difference is negative (past date), mark as "EXPIRED"
    //     if ($daysDifference < 0) {
    //         return 'EXPIRED';
    //     }

    //     return 'VALID';
    // }

    private function updateStatus($date, $currentDate)
    {
        if (empty($date) || $date === '-' || $date === '0000-00-00') {
            return 'VALID';
        }

        // Possible date formats across your fields
        $formats = [
            'Y-m-d',    // 2026-11-14
            'd/m/Y',    // 14/11/2026
            'm/d/Y',    // 11/14/2026
            'Y/m/d',    // 2026/11/14
            'd-m-Y',    // 14-11-2026
            'm-d-Y',    // 11-14-2026
            'd M Y',    // 12 Nov 2025  ✅ for road tax
            'd F Y',    // 12 November 2025 (in case full month name used)
        ];

        $parsedDate = null;

        foreach ($formats as $format) {
            try {
                $parsedDate = Carbon::createFromFormat($format, trim($date));
                break;
            } catch (\Exception $e) {
                continue;
            }
        }

        // Fallback: try generic parse
        if (! $parsedDate) {
            try {
                $parsedDate = Carbon::parse($date);
            } catch (\Exception $e) {
                return 'VALID';
            }
        }

        // Calculate difference in days (negative if expired)
        $daysDifference = $currentDate->diffInDays($parsedDate, false);

        if ($daysDifference < 0) {
            return 'EXPIRED';
        } elseif ($daysDifference <= 15) {
            return 'EXPIRING SOON';
        } else {
            return 'VALID';
        }
    }

    public function selectedvehicleedit($ids)
    {
        $idsArray = explode(',', $ids);
        $vehicles = vehicleDetails::whereIn('id', $idsArray)->get();
        $selectedCompanyId = optional($vehicles->first())->companyName;

        $archiveReason = null;
        $archiveOtherText = null;

        return view('contract.selected_vehicle', compact('vehicles', 'idsArray', 'selectedCompanyId', 'archiveReason', 'archiveOtherText'));
    }

    public function selectedvehicleupdate(Request $request, $ids)
    {
        $idsArray = explode(',', $ids);

        // Only keep fields that have a non-empty value
        $updateData = array_filter($request->only([
            'vehicle_status',
            'group_id',
            'depot_id',
        ]), function ($value) {
            return ! is_null($value) && $value !== '';
        });

        foreach ($idsArray as $vehicleId) {
            $vehicles = vehicleDetails::find($vehicleId);
            if ($vehicles && \Auth::user()->can('edit vehicle') && ! empty($updateData)) {
                $vehicles->update($updateData);
            }
        }

        return redirect()->back()->with('success', 'Selected drivers updated successfully.');
    }

    public function deleteSelectedVehicle(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No vehicles selected.']);
        }

        try {
            $vehicleDetails = \App\Models\vehicleDetails::whereIn('id', $ids)->get();

            foreach ($vehicleDetails as $detail) {
                $vehicleId = $detail->vehicle_id;

                if (! $vehicleId) {
                    throw new \Exception("vehicleDetails ID {$detail->id} has no linked vehicle_id");
                }

                $vehicle = \App\Models\Vehicles::find($vehicleId);

                if ($vehicle) {
                    \App\Models\DeletedVehicleLog::create([
                        'vehicle_registrationnumber' => $vehicle->registrations ?? '',
                        'vehicle_id' => $detail->id,
                        'company_id' => $vehicle->companyName ?? '',
                        'deleted_by' => \Auth::user()->id,
                    ]);

                    // Delete related records
                    \App\Models\VehiclesAnnualTestDefect::where('vehicle_id', $vehicleId)->delete();
                    \App\Models\VehiclesAnnualTest::where('vehicle_id', $vehicleId)->delete();

                    $vehicle->delete();
                }

                $detail->delete();
            }

            return response()->json(['success' => true, 'message' => 'Selected vehicles deleted successfully.']);
        } catch (\Exception $e) {
            // Return full error message for debugging
            return response()->json([
                'success' => false,
                'message' => 'Error deleting selected vehicles.',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}
