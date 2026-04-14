<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CompanyDetails;
use App\Models\Pcn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\PcnExport;
use Maatwebsite\Excel\Facades\Excel;

class PCNController extends Controller
{
public function index(Request $request)
{
    if (\Auth::user()->can('manage pcn')) {
        $loggedInUser = \Auth::user();
        $companyName = $loggedInUser->companyname;

        // Retrieve filter inputs
        $selectedCompanyId = $request->input('company_id');
        $selectedIssuingAuthority = $request->input('issuing_authority');
        $selectedDepotId = $request->input('depot_id');
        $fromDate = $request->input('from_date');
            $selectedGroupId = $request->input('group_id');
        $toDate = $request->input('to_date');
        $status = $request->input('status');
        $pcnQuery = Pcn::query();

            // Ensure depot IDs are properly handled
            $depotIds = is_array($loggedInUser->depot_id) ? $loggedInUser->depot_id : json_decode($loggedInUser->depot_id, true);
            if (! is_array($depotIds)) {
                $depotIds = [$loggedInUser->depot_id];
            }

            $vehicleGroupIds = is_array($loggedInUser->vehicle_group_id)
                    ? $loggedInUser->vehicle_group_id
                    : json_decode($loggedInUser->vehicle_group_id, true);

            if (! is_array($vehicleGroupIds)) {
                $vehicleGroupIds = [$loggedInUser->vehicle_group_id];
            }

        $pcn = null;
        $depots = [];
        if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
            $pcn = Pcn::with(['types'])
            ->whereHas('types', function ($q) {
            $q->where('company_status', 'Active'); // Only include assignments where the company is active
        })
                ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('company_id', $selectedCompanyId);
                })
                ->when($selectedIssuingAuthority, function ($query) use ($selectedIssuingAuthority) {
                    return $query->where('issuing_authority', $selectedIssuingAuthority);
                })
                ->when($selectedDepotId, function ($query) use ($selectedDepotId) {
                    return $query->where('depot_id', $selectedDepotId);
                })
                    ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                        return $query->whereHas('vehicle.vehicleDetail', function ($q) use ($selectedGroupId) {
                            $q->where('group_id', $selectedGroupId);
                        });
                    })
                ->when($fromDate, function ($query) use ($fromDate) {
                    return $query->whereDate('notice_date', '>=', $fromDate);
                })
                ->when($toDate, function ($query) use ($toDate) {
                    return $query->whereDate('notice_date', '<=', $toDate);
                    })->when($status, fn ($q) => $q->where('status', $status))
                ->orderBy('id', 'desc')->get();
        } else {
            $pcn = Pcn::with(['types'])
            ->whereHas('types', function ($q) {
                $q->where('company_status', 'Active'); // Only include assignments where the company is active
            })
                    ->where('company_id', $companyName)->whereIn('depot_id', $depotIds)
                ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('company_id', $selectedCompanyId);
                })
                    ->whereHas('vehicle.vehicleDetail', function ($q) use ($vehicleGroupIds, $selectedGroupId) {
                        $q->whereIn('group_id', $vehicleGroupIds);

                        if ($selectedGroupId) {
                            $q->where('group_id', $selectedGroupId);
                        }
                    })
                ->when($selectedIssuingAuthority, function ($query) use ($selectedIssuingAuthority) {
                    return $query->where('issuing_authority', $selectedIssuingAuthority);
                })
                ->when($selectedDepotId, function ($query) use ($selectedDepotId) {
                    return $query->where('depot_id', $selectedDepotId);
                })
                ->when($fromDate, function ($query) use ($fromDate) {
                    return $query->whereDate('notice_date', '>=', $fromDate);
                })
                ->when($toDate, function ($query) use ($toDate) {
                    return $query->whereDate('notice_date', '<=', $toDate);
                    })->when($status, fn ($q) => $q->where('status', $status))
                ->orderBy('id', 'desc')->get();
        }

        // Calculate totals
        $totalCount = $pcn->count();
        $totalFineAmount = $pcn->sum('fine_amount');
        $totalDeductionAmount = $pcn->sum('deduction_amount');
        $totalFineAndDeduction = $totalFineAmount + $totalDeductionAmount;

        $driverCounts = $pcn->groupBy('driver_name')->map->count();
        $mostFrequentDriver = $driverCounts->isNotEmpty() ? $driverCounts->sortDesc()->keys()->first() : null;
        $mostFrequentDriverCount = $driverCounts->isNotEmpty() ? $driverCounts->max() : 0;

        $companies = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->get();

            if ($selectedCompanyId || $selectedIssuingAuthority || $selectedDepotId || $selectedGroupId || $fromDate || $toDate) {
            session(['filters_applied' => true]);
        } else {
            session(['filters_applied' => false]);
        }

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

        return view('pcn.index', compact(
            'pcn',
            'companies',
            'depots',
            'selectedCompanyId',
            'selectedDepotId',
            'selectedIssuingAuthority',
            'totalCount',
            'totalFineAmount',
            'totalDeductionAmount',
            'totalFineAndDeduction',
             'mostFrequentDriver',
                'mostFrequentDriverCount', 'status', 'depots',
                'groups'
        ));
    } else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}

public function pcnDataexport(Request $request)
{
    if (! Auth::user()->can('manage pcn')) {
        return response()->json(['error' => 'Permission denied.'], 403);
    }

    $loggedInUser = Auth::user();

    // Get filters
    $selectedCompanyId = $request->query('company_id');
    $selectedDepotId = $request->query('depot_id');
    $selectedIssuingAuthority = $request->query('issuing_authority');
    $selectedGroupId = $request->query('group_id');
    $fromDate = $request->query('from_date');
    $toDate = $request->query('to_date');

    // Depot IDs
    $depotIds = is_array($loggedInUser->depot_id)
        ? $loggedInUser->depot_id
        : json_decode($loggedInUser->depot_id, true);

    if (!is_array($depotIds)) {
        $depotIds = [$loggedInUser->depot_id];
    }

    // Group IDs
    $vehicleGroupIds = is_array($loggedInUser->vehicle_group_id)
        ? $loggedInUser->vehicle_group_id
        : json_decode($loggedInUser->vehicle_group_id, true);

    if (!is_array($vehicleGroupIds)) {
        $vehicleGroupIds = [$loggedInUser->vehicle_group_id];
    }

    $pcnQuery = Pcn::with(['types'])
        ->whereHas('types', function ($q) {
        $q->where('company_status', 'Active');
    });

    // Role logic
    if (!($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager'))) {
        $pcnQuery->where('company_id', $loggedInUser->companyname)
                 ->whereIn('depot_id', $depotIds)
                 ->whereHas('vehicle.vehicleDetail', function ($q) use ($vehicleGroupIds, $selectedGroupId) {
                     $q->whereIn('group_id', $vehicleGroupIds);

                     if ($selectedGroupId) {
                         $q->where('group_id', $selectedGroupId);
                     }
                 });
    } else {
        // Company role group filter
        if ($selectedGroupId) {
            $pcnQuery->whereHas('vehicle.vehicleDetail', function ($q) use ($selectedGroupId) {
                $q->where('group_id', $selectedGroupId);
            });
        }
    }

    // Filters
    if ($selectedCompanyId) {
        $pcnQuery->where('company_id', $selectedCompanyId);
    }

    if ($selectedIssuingAuthority) {
        $pcnQuery->where('issuing_authority', $selectedIssuingAuthority);
    }

    if ($selectedDepotId) {
        $pcnQuery->where('depot_id', $selectedDepotId);
    }

    if ($fromDate) {
        $pcnQuery->whereDate('notice_date', '>=', $fromDate);
    }

    if ($toDate) {
        $pcnQuery->whereDate('notice_date', '<=', $toDate);
    }

    $pcn = $pcnQuery->get();

    if ($pcn->isEmpty()) {
        return back()->with('error', 'No data found for export.');
    }

    return Excel::download(new PcnExport($pcn), 'PCN.xlsx');
}

public function getDepots($companyId)
{
    $user = Auth::user();

    // Fetch the company details to check if the company is active
    $company = \App\Models\CompanyDetails::where('id', $companyId)->first();

        if (! $company || $company->company_status !== 'Active') {
        // If company is not found or company_status is not 'Active', return an error message
        return response()->json(['error' => 'Company is not active or does not exist'], 404);
    }

    if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
        // For 'company' and 'PTC manager', show depots based on the selected company
        $depots = \App\Models\Depot::where('companyName', $companyId)->get();
    } else {
        // For other users, show depots only for the company they belong to
        $depots = \App\Models\Depot::where('companyName', $user->companyname)->get();
    }

    return response()->json($depots);
}

public function create()
{
    $user = \Auth::user();
    if (\Auth::user()->can('create pcn')) {
        // Fetch companies and depots

        // Check if the user is a super admin
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
    // Fetch all company names and depots as objects
    $companies = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->get();
    $depots = \App\Models\Depot::orderBy('name', 'asc')->get();
} else {
    // Fetch the company name for the logged-in user as objects
    $companies = CompanyDetails::where('created_by', '=', $user->creatorId())
        ->where('id', '=', $user->companyname)->where('company_status', 'Active')
        ->get(); // Get full model objects
        $depots = \App\Models\Depot::where('companyName', '=', $user->companyname)
        ->orderBy('name', 'asc')
        ->get();
    }


        // Return the create view
        return view('pcn.create', compact('companies', 'depots'));
    } else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}



public function store(Request $request)
{
    if (\Auth::user()->can('create pcn')) {
    // Validate the incoming request
    $validator = \Validator::make(
        $request->all(),
        [
            'vehicle_registration_number' => 'required|string|max:255',
            'violation_date' => 'required|date',
            'company_id' => 'required|exists:company_details,id',
            'driver_name' => 'required|string|max:255',
            'notice_date' => 'required|date',
            'location' => 'required|string|max:255',
            'issuing_authority' => 'nullable|string',
            'fine_amount' => 'required|numeric|min:0',
            'deduction_amount' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:Closed,Outstanding',
            'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'comments' => 'nullable|string',
            'vehicle_id' => 'nullable|exists:vehicles,id', // Validate the vehicle ID
                        'depot_id' => 'required|exists:depots,id',
                        'notice_number' => 'nullable|string',


        ]
    );

    if ($validator->fails()) {
        $messages = $validator->getMessageBag();
        return redirect()->back()->with('error', $messages->first());
    }

    // Fetch vehicle data first
    $registrationNumber = $request->input('vehicle_registration_number');
    $companyId = $request->input('company_id');

    // Call fetchVehicleData function logic
    $vehicle = \App\Models\Vehicles::where('registrations', $registrationNumber)
        ->where('companyName', $companyId)
        ->first();

    // if (!$vehicle) {
    //     return redirect()->back()->with('error', 'Vehicle not found or not associated with the selected company and depot.');
    // }

    $vehicleId = $request->input('vehicle_id');


    // Create a new PCN record
    $pcn = new Pcn();
    $pcn->vehicle_registration_number = $request->vehicle_registration_number;
    $pcn->violation_date = $request->violation_date;
    $pcn->company_id = $request->company_id;
    $pcn->driver_name = $request->driver_name;
    $pcn->notice_date = $request->notice_date;
    $pcn->location = $request->location;
    $pcn->issuing_authority = $request->issuing_authority;
    $pcn->fine_amount = $request->fine_amount;
    $pcn->deduction_amount = $request->deduction_amount;
    $pcn->notice_number = $request->notice_number;
    $pcn->status = $request->status;
    $pcn->vehicle_id = $vehicleId; // Save the vehicle ID in the PCN record
        $pcn->depot_id = $request->depot_id; // Save the depot ID in the PCN record

    $pcn->comments = $request->comments;
        $pcn->created_by = \Auth::user()->id;


    // Handle file upload for attachment
    if ($request->hasFile('attachments')) {
        $filePaths = [];
        foreach ($request->file('attachments') as $file) {
            $filePaths[] = $file->store('pcn/attachments', 'local');
        }
        $pcn->attachment = json_encode($filePaths); // Store as JSON
    }

    // Handle conditional fields based on issuing authority
    if ($request->issuing_authority === 'Local Council') {
        $pcn->type = $request->contravention_type === 'Other'
            ? "Other->{$request->other_contravention_type}"
            : $request->contravention_type;

        $pcn->action = $request->action === 'Appealed'
            ? "Appealed -> {$request->appealed_status}" .
                ($request->appealed_status === 'Lost' ? " -> {$request->lost_status}" : '')
            : $request->action;

    } elseif ($request->issuing_authority === 'Police') {
        $pcn->type = $request->offence_type === 'Other'
            ? "Other->{$request->other_offence_type}"
            : $request->offence_type;

        $pcn->action = $request->police_action === 'Other'
            ? "Other->{$request->other_police_action}"
            : $request->police_action;

    } elseif ($request->issuing_authority === 'DVSA') {
        $pcn->type = $request->dvsa_offence_type === 'Other'
            ? "Other->{$request->dvsa_other_offence_type}"
            : $request->dvsa_offence_type;

        $pcn->action = $request->dvsa_action === 'Other'
            ? "Other->{$request->other_action}"
            : $request->dvsa_action;
    }

    // Save the record to the database
    $pcn->save();

    // Redirect with success message
    return redirect()->route('pcn.index')->with('success', 'PCN created successfully.');

} else {
    return redirect()->back()->with('error', __('Permission denied.'));
}
}

public function edit($id)
{
    // Check if the user has permission to edit the PCN
    if (\Auth::user()->can('create pcn')) {
        // Fetch the PCN record by ID
        $pcn = Pcn::find($id);
        if (!$pcn) {
            return redirect()->route('pcn.index')->with('error', 'PCN record not found.');
        }

        // Fetch companies and depots
        $user = \Auth::user();

        // Return the edit view with the PCN data, companies, and depots
        return view('pcn.edit', compact('pcn'));
    } else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}

public function update(Request $request, $id)
{
    // Check if the user has permission to update the PCN
    if (\Auth::user()->can('create pcn')) {
        // Validate the incoming request
        $validator = \Validator::make(
            $request->all(),
            [

                'notice_date' => 'required|date',
                'location' => 'required|string|max:255',
                'fine_amount' => 'required|numeric|min:0',
                'deduction_amount' => 'nullable|numeric|min:0',
                'status' => 'required|string|in:Closed,Outstanding',
                'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'comments' => 'nullable|string',
                'notice_number' => 'nullable|string',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        // Fetch the PCN record
        $pcn = Pcn::find($id);
        if (!$pcn) {
            return redirect()->back()->with('error', 'PCN record not found.');
        }


        // Update the PCN record
        $pcn->notice_date = $request->notice_date;
        $pcn->location = $request->location;
        $pcn->fine_amount = $request->fine_amount;
        $pcn->deduction_amount = $request->deduction_amount;
        $pcn->notice_number = $request->notice_number;
        $pcn->status = $request->status;
        $pcn->comments = $request->comments;

        // Handle file upload for attachment
        if ($request->hasFile('attachments')) {
            $existingAttachments = json_decode($pcn->attachment, true) ?? [];
            $newAttachments = [];

            foreach ($request->file('attachments') as $file) {
                $newAttachments[] = $file->store('pcn/attachments', 'local');
            }

            $allAttachments = array_merge($existingAttachments, $newAttachments);
            $pcn->attachment = json_encode($allAttachments);
        }


        // Save the updated record to the database
        $pcn->save();

        // Redirect with success message
        return redirect()->route('pcn.index')->with('success', 'PCN updated successfully.');

    } else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}

public function fetchVehicleData(Request $request)
    {
        $registrationNumber = $request->input('registration_number');
        $violationDate = $request->input('violation_date');
        $companyId = $request->input('company_id'); // Capture company_id
        $depotId = $request->input('depot_id'); // Capture depot_id

        // Convert violation date from d/m/Y to Y-m-d for easier comparison
        $formattedViolationDate = \Carbon\Carbon::createFromFormat('d/m/Y', $violationDate)->format('Y-m-d');

        // Fetch the vehicle data based on registration number and company_id
        $vehicle = \App\Models\Vehicles::where('registrations', $registrationNumber)

            ->where('companyName', $companyId) // Ensure the company matches
            ->first();

        if ($vehicle) {
            // Fetch all WorkAroundStore records based on vehicle_id, violation date, company_id, and depot_id
            $workAroundDataList = \App\Models\WorkAroundStore::where('vehicle_id', $vehicle->id)
                ->whereDate(\DB::raw('STR_TO_DATE(uploaded_date, "%d/%m/%Y %H:%i:%s")'), $formattedViolationDate) // Filter by violation date
                ->where('company_id', $companyId) // Ensure the company matches
                ->where('operating_centres', $depotId) // Ensure the depot matches
                ->get();

            // Prepare the response data
            $workAroundDataWithDrivers = [];
            foreach ($workAroundDataList as $workAroundData) {
                $driver = \App\Models\Driver::find($workAroundData->driver_id);
                $depot = \App\Models\Depot::find($workAroundData->operating_centres);

                $workAroundDataWithDrivers[] = [
                    'fuel_level' => $workAroundData->fuel_level,
                    'adblue_level' => $workAroundData->adblue_level,
                    'driver_name' => $driver ? $driver->name : 'No driver name',
                    'depot_name' => $depot ? $depot->name : 'No depot name',
                ];
            }

            return response()->json([
                'success' => true,
                'vehicle' => $vehicle,
                'workAroundData' => $workAroundDataWithDrivers,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No walkaround record found for the entered vehicle registration number.',
        ]);
    }

public function show($id)
    {
        // Get the current authenticated user
        $user = \Auth::user();

        // Base query for FleetPlannerReminder with relationships
        $query = Pcn::whereHas('types', function ($q) {
            $q->where('company_status', 'Active'); // Filter by Active company status
        }); // Load 'files' relationship

        // Check if the user is an admin or PTC manager
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
            // Fetch the record only if it belongs to the user's company
            $pcn = $query->findOrFail($id);
        } else {
            // Fetch only if the specific record belongs to the logged-in user's company
            $pcn = $query->whereHas('types', function ($q) use ($user) {
                $q->where('company_id', $user->companyname); // Ensure correct column name
            })->findOrFail($id);

        }

        // Pass data to the view
        return view('pcn.show', compact('pcn'));
    }

}
