<?php

namespace App\Http\Controllers;

use App\Models\CompanyDetails;
use App\Models\Depot;
use Illuminate\Http\Request;

class DepotController extends Controller
{
    public function index(Request $request)
    {

        if (\Auth::user()->can('manage depot')) {
            $loggedInUser = \Auth::user();

            // Retrieve the company name of the user
            $companyName = $loggedInUser->companyname;
            
              // Handle multiple depot IDs (convert stored JSON to array if needed)
        $depotIds = is_array($loggedInUser->depot_id) ? $loggedInUser->depot_id : json_decode($loggedInUser->depot_id, true);
        if (!is_array($depotIds)) {
            $depotIds = [$loggedInUser->depot_id]; // Ensure it remains an array
        }

            // Retrieve the selected company ID from the request
             $selectedCompanyId = $request->input('company_id');

            // Retrieve contracts based on the user's role
            $contracts = null;
            if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
            // If the user has the 'company' role, show all data with active company status
            $contracts = Depot::with(['types', 'creator'])
                ->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })
                ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('companyName', $selectedCompanyId);
                })
                ->get();
            } else {
                // If the user doesn't have the 'company' role, only show contracts associated with the user's company
                $contracts = Depot::where('companyname', $companyName)
                ->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })
                ->with(['types', 'creator'])
                ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->whereIn('id', $depotIds)
                ->get();
            }

        // Retrieve all companies with active status for the dropdown filter
        $companies = CompanyDetails::where('company_status', 'Active')
            ->orderBy('name', 'asc')
            ->get();

            // Return the view with the contracts
            return view('depot.index', compact('contracts','companies'));
        } else {
            // If the user doesn't have the permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function create()
    {
        $user = \Auth::user();
        if ($user->can('manage depot')) {

            // Check if the user is a super admin
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                // Fetch all company names
                $contractTypes = CompanyDetails::where('company_status', 'Active')->pluck('name', 'id');
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

            return view('depot.create', compact('contractTypes'));
        } else {
            // If user doesn't have permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create depot')) {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    'companyName' => 'required',
                    'licence_number' => 'nullable',
                    'traffic_area' => 'nullable',
                    'continuation_date' => 'nullable|date_format:Y-m-d',
                    'transport_manager_name' => 'nullable',
                    'status' => 'required|in:Active,Inactive',
                    'operating_centre' => 'required',
                    'vehicles' => 'nullable',
                    'trailers' => 'nullable',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

// $continuation_date = \DateTime::createFromFormat('Y-m-d', $request->continuation_date);

// if ($continuation_date !== false) {
//     $formatted_date = $continuation_date->format('d/m/Y');
// } else {
//     // Handle the error, e.g., set a default value or return an error message
//     $formatted_date = null; // or some other fallback
// }

        $continuation_date = $request->continuation_date ? \Carbon\Carbon::createFromFormat('Y-m-d', $request->continuation_date)->format('d/m/Y') : null;


            $types = new Depot();
            $types->name = $request->name;
            $types->companyName = $request->companyName;
            $types->licence_number = $request->licence_number;
            $types->traffic_area = $request->traffic_area;
            $types->continuation_date = $continuation_date;
            $types->transport_manager_name = $request->transport_manager_name;
            $types->status = $request->status;
            $types->operating_centre = $request->operating_centre;
            $types->vehicles = $request->vehicles ?? '';
            $types->trailers = $request->trailers ?? '';
            $types->created_by = \Auth::user()->creatorId();
            $types->created_username = \Auth::user()->id;
            $types->save();

            return redirect()->route('depot.index')->with([
                'success' => __('Operating Centre successfully created.'),
                'showVehicleModal' => true,
            ]);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(Depot $depot)
    {
        $user = \Auth::user();
        if ($user->can('manage depot')) {

            // Check if the user is a super admin
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                // Fetch all company names
                $contractTypes = CompanyDetails::where('company_status', 'Active')->pluck('name', 'id');
            } else {
                // Fetch the company name for the logged-in user
                $contractTypes = CompanyDetails::where('created_by', '=', $user->creatorId())->where('company_status', 'Active')
                    ->where('id', '=', $user->companyname)
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

            return view('depot.edit', compact('depot', 'contractTypes'));
        } else {
            // If user doesn't have permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

public function update(Request $request, Depot $depot)
{
    if (\Auth::user()->can('edit depot')) {
        $validator = \Validator::make(
            $request->all(), [
                'name' => 'required',
                'companyName' => 'required',
                'licence_number' => 'nullable',
                'traffic_area' => 'nullable',
                'continuation_date' => 'nullable|date_format:Y-m-d',
                'transport_manager_name' => 'nullable',
                'status' => 'required|in:Active,Inactive',
                'operating_centre' => 'required',
                'vehicles' => 'nullable',
                'trailers' => 'nullable',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        // Convert the date format from Y-m-d to d/m/Y if it's not null
        $continuation_date = $request->continuation_date ? \Carbon\Carbon::createFromFormat('Y-m-d', $request->continuation_date)->format('d/m/Y') : null;

        $depot->name = $request->name;
        $depot->companyName = $request->companyName;
        $depot->licence_number = $request->licence_number;
        $depot->traffic_area = $request->traffic_area;
        $depot->continuation_date = $continuation_date;
        $depot->transport_manager_name = $request->transport_manager_name;
        $depot->status = $request->status;
        $depot->operating_centre = $request->operating_centre;
        $depot->vehicles = $request->vehicles;
        $depot->trailers = $request->trailers;
        $depot->created_by = \Auth::user()->creatorId();
        $depot->created_username = \Auth::user()->id;
        $depot->save();

        return redirect()->back()->with('success', __('Depot Type successfully updated.'));
    } else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}

public function importFile()
{
    if (\Auth::user()->can('create depot')) {
        return view('depot.import');
    } else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}

public function import(Request $request)
{
    $rules = [
        'file' => 'required',
    ];

    $validator = \Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        $messages = $validator->getMessageBag();
        return redirect()->back()->with('error', $messages->first());
    }

    $depot = (new \App\Imports\DepotImport)->toArray($request->file('file'))[0];
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
        if (!$companyDetails) {
            // If companyName not found, store error message and skip saving this record
            $errorArray[] = [
                'error' => 'Company name "'.$companyName.'" not found',
                'data' => $items,
            ];
            continue; // Skip this record and move to the next one
        }

        // Create and save depot record with additional columns
        $depotService = new Depot();
        $depotService->name = $items[0] ?? null; // Column for 'name'
        $depotService->companyName = $companyDetails->id; // Assign company_id instead of companyName
        // $depotService->licence_number = $items[2] ?? null; // Column for 'licence_number'
        // $depotService->traffic_area = $items[3] ?? null; // Column for 'traffic_area'
        // $depotService->continuation_date = $items[4] ?? null; // Column for 'continuation_date'
        // $depotService->transport_manager_name = $items[5] ?? null; // Column for 'transport_manager_name'
        $depotService->status = $items[2] ?? null; // Column for 'status'
        $depotService->operating_centre = $items[3] ?? null; // Column for 'operating_centre'
        $depotService->vehicles = $items[4] ?? null; // Column for 'vehicles'
        $depotService->trailers = $items[5] ?? null; // Column for 'trailers'
        $depotService->created_by = \Auth::user()->creatorId(); // Set created_by
        $depotService->created_username = \Auth::user()->id; // Set created_username, if available

        $depotService->save();
        $successCount++;
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

    return redirect()->route('depot.index')->with($data['status'], $data['msg']);
}


    public function destroy(Depot $depot)
    {

        $depot->delete();

        return redirect()->back()->with('success', __('Depot Type successfully deleted.'));

    }
}
