<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AppAccessLevel;
use App\Models\CompanyDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicationAccessLevelController extends Controller
{
    public function index(Request $request)
    {

        if (\Auth::user()->can('manage company')) {
            $loggedInUser = \Auth::user();

            // Retrieve the company name of the user
            $companyName = $loggedInUser->companyname;

            // Retrieve the selected company ID from the request
             $selectedCompanyId = $request->input('company_id');

            // Retrieve contracts based on the user's role
            $accesslevel = null;
            if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
                // If the user has the 'company' role, show all data
                $accesslevel = AppAccessLevel::get();
            } else {
                // If the user doesn't have the 'company' role, only show contracts associated with the user's company
                $accesslevel = AppAccessLevel::where('companyname', $companyName)
                ->get();
            }

            // Retrieve all companies for the dropdown filter
            $companies = CompanyDetails::orderBy('name', 'asc')->get();

            // Return the view with the contracts
            return view('contract.app.accesslevel', compact('accesslevel','companies'));
        } else {
            // If the user doesn't have the permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function create()
    {
        $user = \Auth::user();
        if ($user->can('manage company')) {

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

            return view('contract.app.create', compact('contractTypes'));
        } else {
            // If user doesn't have permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('manage company')) {
            $validator = \Validator::make(
                $request->all(), [
                    'company_id' => 'required|exists:company_details,id',
                    'manager_access' => 'nullable|array',
                    'driver_access' => 'nullable|array',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

             // Check for existing company_id in AppAccessLevel
        $existingAccessLevel = \App\Models\AppAccessLevel::where('company_id', $request->company_id)->first();
        if ($existingAccessLevel) {
            return redirect()->back()->with('error', __('This Company already has an access level assigned.'));
        }

            // Store the access levels
            $accessLevel = new \App\Models\AppAccessLevel();
            $accessLevel->company_id = $request->company_id;
            $accessLevel->manager_access = $request->manager_access; // Array
            $accessLevel->driver_access = $request->driver_access;   // Array
            $accessLevel->created_by = \Auth::user()->id;
            $accessLevel->save();

            return redirect()->route('accesslevel.index')->with('success', __('Access Level successfully created.'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(AppAccessLevel $accesslevel)
    {
        $user = \Auth::user();
        if ($user->can('manage company')) {

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
                    $contractTypes = CompanyDetails::where('id', '=', $user->companyname)
                        ->pluck('name', 'id');
                } else {
                    $contractTypes = [];
                }
            }

            return view('contract.app.edit', compact('accesslevel', 'contractTypes'));
        } else {
            // If user doesn't have permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, AppAccessLevel $accesslevel)
{
    if (\Auth::user()->can('manage company')) {
        // Validate the input
        $validator = \Validator::make(
            $request->all(), [
                'company_id' => 'required|exists:company_details,id',
                'manager_access' => 'nullable|array',
                'driver_access' => 'nullable|array',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        // Check for existing company_id in AppAccessLevel excluding the current record
        $existingAccessLevel = \App\Models\AppAccessLevel::where('company_id', $request->company_id)
            ->where('id', '!=', $accesslevel->id) // Exclude the current record
            ->first();

        if ($existingAccessLevel) {
            return redirect()->back()->with('error', __('This Company already has an access level assigned.'));
        }

        // Update access level fields
        $accesslevel->company_id = $request->company_id;
        $accesslevel->manager_access = $request->manager_access; // Array of manager access
        $accesslevel->driver_access = $request->driver_access;   // Array of driver access
        $accesslevel->save();

        return redirect()->back()->with('success', __('Access Level successfully updated.'));
    } else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}

public function destroy(AppAccessLevel $accesslevel)
{

    $accesslevel->delete();

    return redirect()->back()->with('success', __('Access Level successfully deleted.'));

}


}
