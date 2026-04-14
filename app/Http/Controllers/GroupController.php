<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CompanyDetails;
use App\Models\Group;
use App\Models\VehicleGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('manage driver')) {
            $loggedInUser = \Auth::user();
            $companyName = $loggedInUser->companyname; // Company name of the logged-in user

            // Retrieve the selected company ID from the request
            $selectedCompanyId = $request->input('company_id');
            $driverGroupIds = is_array($loggedInUser->driver_group_id)
                ? $loggedInUser->driver_group_id
                : json_decode($loggedInUser->driver_group_id, true);

            if (! is_array($driverGroupIds)) {
                $driverGroupIds = [$loggedInUser->driver_group_id];
            }

            // Retrieve group based on the user's role
            $group = null;
            if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
                // If the user has the 'company' role, show all data with Active company status
                $group = Group::with(['types', 'creator'])
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->get();
            } else {
                // If the user doesn't have the 'company' role, only show data associated with the user's Active company
                $group = Group::where('company_id', $companyName)->whereIn('id', $driverGroupIds)
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->with(['types', 'creator'])
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->get();
            }

            // Retrieve the company details based on the user's company name with Active status
            $companyDetails = CompanyDetails::where('name', $companyName)
                ->where('company_status', 'Active')
                ->first();

            // Retrieve all companies with Active status for the dropdown filter
            $companies = CompanyDetails::where('company_status', 'Active')->get();

            // Return the view with the group, company details, and companies list
            return view('group.index', compact('group', 'companyDetails', 'companies'));
        } else {
            // If the user doesn't have the permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


public function create()
{
    $user = \Auth::user();
    if ($user->can('manage driver')) {

        // Check if the user is a super admin
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
            // Fetch all company names
            $group = CompanyDetails::where('company_status', 'Active')->orderBy('name', 'asc')->pluck('name', 'id');
        } else {
            // Fetch the company name for the logged-in user
            $group = CompanyDetails::where('created_by', '=', $user->creatorId())
                ->where('id', '=', $user->companyname)->where('company_status', 'Active')
                ->pluck('name', 'id');

            // Check if the user creating the new user is directly associated with a company
            // If not, remove the company name from the list
            if ($user->companyname) {
                $group = CompanyDetails::where('id', '=', $user->companyname)->where('company_status', 'Active')
                    ->pluck('name', 'id');
            } else {
                $group = [];
            }
        }

        return view('group.create', compact('group'));
    } else {
        // If user doesn't have permission, redirect back with an error message
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}

public function store(Request $request)
{
    if (\Auth::user()->can('manage driver')) {
        $validator = \Validator::make(
            $request->all(), [
                'name' => 'required',
                'company_id' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        // Check if the group with the same company_id and name already exists
        $existingGroup = Group::where('company_id', $request->company_id)
            ->where('name', $request->name)
            ->first();

        if ($existingGroup) {
            return redirect()->back()->with('error', __('Group with the same company and name already exists.'));
        }

        $group = new Group();
        $group->name = $request->name;
        $group->company_id = $request->company_id;
        $group->created_by = \Auth::user()->id;
        $group->save();

        return redirect()->route('group.index')->with([
            'success' => __('Group successfully created.')
        ]);
    } else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}

public function edit(Group $group)
{
    $user = \Auth::user();
    if ($user->can('manage driver')) {

        // Check if the user is a super admin
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
            // Fetch all company names
            $companies = CompanyDetails::where('company_status', 'Active')->orderBy('name', 'asc')->pluck('name', 'id');
        } else {
            // Fetch the company name for the logged-in user
            $companies = CompanyDetails::where('created_by', '=', $user->creatorId())
                ->where('id', '=', $user->companyname)->where('company_status', 'Active')
                ->pluck('name', 'id');

            // Check if the user creating the new user is directly associated with a company
            // If not, remove the company name from the list
            if ($user->companyname) {
                $companies = CompanyDetails::where('id', '=', $user->companyname)->where('company_status', 'Active')
                    ->pluck('name', 'id');
            } else {
                $companies = [];
            }
        }

        return view('group.edit', compact('group','companies'));
    } else {
        // If user doesn't have permission, redirect back with an error message
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}

public function update(Request $request, Group $group)
{
if (\Auth::user()->can('manage driver')) {
    $validator = \Validator::make(
        $request->all(), [
            'name' => 'required',
            'company_id' => 'required',

        ]
    );

    if ($validator->fails()) {
        $messages = $validator->getMessageBag();
        return redirect()->back()->with('error', $messages->first());
    }

    // Check if the group with the same company_id and name already exists
        $existingGroup = Group::where('company_id', $request->company_id)
            ->where('name', $request->name)
            ->first();

        if ($existingGroup) {
            return redirect()->back()->with('error', __('Group with the same company and name already exists.'));
        }


    $group->name = $request->name;
    $group->company_id = $request->company_id;
    $group->created_by = \Auth::user()->id;
    $group->save();

    return redirect()->back()->with('success', __('Group successfully updated.'));
} else {
    return redirect()->back()->with('error', __('Permission denied.'));
}
}

public function destroy(Group $group)
{

    $group->delete();

    return redirect()->back()->with('success', __('Group successfully deleted.'));

}

public function vehicleindex(Request $request)
{
    if (\Auth::user()->can('manage vehicle')) {
        $loggedInUser = \Auth::user();
        $companyName = $loggedInUser->companyname; // Company name of the logged-in user

            // Retrieve the selected company ID from the request
            $selectedCompanyId = $request->input('company_id');

             $vehicleGroupIds = is_array($loggedInUser->vehicle_group_id)
                ? $loggedInUser->vehicle_group_id
                : json_decode($loggedInUser->vehicle_group_id, true);

            if (! is_array($vehicleGroupIds)) {
                $vehicleGroupIds = [$loggedInUser->vehicle_group_id];
            }

            // Retrieve group based on the user's role
            $group = null;
        if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
            // If the user has the 'company' role, show all data with pagination
                $group = \App\Models\VehicleGroup::with(['types', 'creator'])
                ->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->get();
        } else {
            // If the user doesn't have the 'company' role, only show contracts associated with the user's company with pagination
            $group = \App\Models\VehicleGroup::where('company_id', $companyName)->whereIn('id', $vehicleGroupIds)
                ->with(['types', 'creator'])
                ->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                ->get();
        }

        // Retrieve the company details based on the user's company name
        $companyDetails = CompanyDetails::where('name', $companyName)->where('company_status', 'Active')->first();

            // Retrieve all companies for the dropdown filter
            $companies = CompanyDetails::where('company_status', 'Active')->get();

                // Return the view with the group, company details, and companies list
                return view('group.vehicle.index', compact('group', 'companyDetails', 'companies'));
    } else {
        // If the user doesn't have the permission, redirect back with an error message
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}

public function vehiclecreate()
{
    $user = \Auth::user();
    if ($user->can('manage vehicle')) {

        // Check if the user is a super admin
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
            // Fetch all company names
            $group = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->pluck('name', 'id');
        } else {
            // Fetch the company name for the logged-in user
            $group = CompanyDetails::where('created_by', '=', $user->creatorId())
                ->where('id', '=', $user->companyname)->where('company_status', 'Active')
                ->pluck('name', 'id');

            // Check if the user creating the new user is directly associated with a company
            // If not, remove the company name from the list
            if ($user->companyname) {
                $group = CompanyDetails::where('id', '=', $user->companyname)->where('company_status', 'Active')
                    ->pluck('name', 'id');
            } else {
                $group = [];
            }
        }

        return view('group.vehicle.create', compact('group'));
    } else {
        // If user doesn't have permission, redirect back with an error message
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}

public function vehiclestore(Request $request)
{
    if (\Auth::user()->can('manage vehicle')) {
        $validator = \Validator::make(
            $request->all(), [
                'name' => 'required',
                'company_id' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        // Check if the group with the same company_id and name already exists
        $existingGroup = VehicleGroup::where('company_id', $request->company_id)
            ->where('name', $request->name)
            ->first();

        if ($existingGroup) {
            return redirect()->back()->with('error', __('Group with the same company and name already exists.'));
        }

        $group = new \App\Models\VehicleGroup();
        $group->name = $request->name;
        $group->company_id = $request->company_id;
        $group->created_by = \Auth::user()->id;
        $group->save();

        return redirect()->route('vehicle.group.index')->with([
            'success' => __('Group successfully created.')
        ]);
    } else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}

public function vehicleedit(VehicleGroup $group)
{
    $user = \Auth::user();
    if ($user->can('manage vehicle')) {

        // Check if the user is a super admin
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
            // Fetch all company names
            $companies = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->pluck('name', 'id');
        } else {
            // Fetch the company name for the logged-in user
            $companies = CompanyDetails::where('created_by', '=', $user->creatorId())
                ->where('id', '=', $user->companyname)->where('company_status', 'Active')
                ->pluck('name', 'id');

            // Check if the user creating the new user is directly associated with a company
            // If not, remove the company name from the list
            if ($user->companyname) {
                $companies = CompanyDetails::where('id', '=', $user->companyname)->where('company_status', 'Active')
                    ->pluck('name', 'id');
            } else {
                $companies = [];
            }
        }

        return view('group.vehicle.edit', compact('group', 'companies'));
    } else {
        // If user doesn't have permission, redirect back with an error message
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}

public function vehicleupdate(Request $request, VehicleGroup $group)
{
if (\Auth::user()->can('manage vehicle')) {
    $validator = \Validator::make(
        $request->all(), [
            'name' => 'required',
            'company_id' => 'required',

        ]
    );

    if ($validator->fails()) {
        $messages = $validator->getMessageBag();
        return redirect()->back()->with('error', $messages->first());
    }

            // Check if the group with the same company_id and name already exists
        $existingGroup = VehicleGroup::where('company_id', $request->company_id)
            ->where('name', $request->name)
            ->first();

        if ($existingGroup) {
            return redirect()->back()->with('error', __('Group with the same company and name already exists.'));
        }


    $group->name = $request->name;
    $group->company_id = $request->company_id;
    $group->created_by = \Auth::user()->id;
    $group->save();

    return redirect()->back()->with('success', __('Group successfully updated.'));
} else {
    return redirect()->back()->with('error', __('Permission denied.'));
}
}

public function vehicledestroy(VehicleGroup $group)
{

    $group->delete();

    return redirect()->back()->with('success', __('Group successfully deleted.'));

}
}
