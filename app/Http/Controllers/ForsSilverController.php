<?php

namespace App\Http\Controllers;

use App\Models\ForsSilver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForsSilverController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('manage fors')) {

            $silverPolicies = ForsSilver::all();

            return view('fors.silver.index', compact('silverPolicies'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        $user = \Auth::user();
        if ($user->can('manage fors')) {

            return view('fors.silver.create');
        } else {
            // If user doesn't have permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create fors')) {
            $validator = Validator::make(
                $request->all(), [
                    'silver_policy_name' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $silver = new ForsSilver();
            $silver->silver_policy_name = $request->silver_policy_name;
            $silver->save();

            return redirect()->route('fors.silver.index')->with('success', __('Silver Policy successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(ForsSilver $forsSilver)
    {
        return view('fors.silver.edit', compact('forsSilver'));
    }

    public function update(Request $request, ForsSilver $forsSilver)
    {
        if (\Auth::user()->can('manage fors')) {
            $validator = \Validator::make(
                $request->all(), [
                    'silver_policy_name' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $forsSilver->silver_policy_name = $request->silver_policy_name;
            $forsSilver->save();

            return redirect()->route('fors.silver.index')->with('success', __('Silver Policy successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function SilverPolicy_descriptionStore($id, Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $forsSilver = ForsSilver::find($id);

            $forsSilver->silver_policy_description = $request->silver_policy_description;
            $forsSilver->save();

            return response()->json(
                [
                    'is_success' => true,
                    'success' => __('Silver Policy description successfully saved!'),
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
    }

    public function show($id)
    {
        if (\Auth::user()->can('manage fors')) {
            $forsSilver = ForsSilver::find($id);

            // Check if $forsSilver is null
            if (! $forsSilver) {
                return redirect()->back()->with('error', __('ForsSilver not found.'));
            }

            $acceptedDrivers = \Illuminate\Support\Facades\DB::table('driver_silver_policy')
                ->join('drivers', 'driver_silver_policy.driver_id', '=', 'drivers.id')
                ->join('company_details', 'drivers.companyName', '=', 'company_details.id') // Adjust based on actual relationship
                ->where('fors_silver_id', $id)
                ->where('driver_silver_policy.status', 'Accept')
                ->get([
                    'driver_silver_policy.driver_id',
                    'driver_silver_policy.driver_signature',
                    'drivers.name',
                    'company_details.name as companyName', // Fetch the company name
                ]);

            $declinedDrivers = \Illuminate\Support\Facades\DB::table('driver_silver_policy')
                ->join('drivers', 'driver_silver_policy.driver_id', '=', 'drivers.id')
                ->join('company_details', 'drivers.companyName', '=', 'company_details.id') // Adjust based on actual relationship
                ->where('fors_silver_id', $id)
                ->where('driver_silver_policy.status', 'Decline')
                ->get(['drivers.name as declinedDriverName', 'company_details.name as companyName']);

            return view('fors.silver.show', compact('forsSilver', 'acceptedDrivers', 'declinedDrivers'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(ForsSilver $forsSilver)
    {
        if (\Auth::user()->can('manage fors')) {
            $forsSilver->delete();

            return redirect()->route('fors.silver.index')->with('success', __('Silver Policy successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function Silverassign($id)
    {
        $silverPolicy = ForsSilver::findOrFail($id);
        $drivers = \App\Models\Driver::all(); // Fetch the list of drivers

        // Fetch assigned drivers (assuming there's a relationship defined in the BronzePolicy model)
        $assignedDrivers = $silverPolicy->drivers; // Adjust this line based on your actual relationships
        $assignedDriverIds = $assignedDrivers->pluck('id')->toArray();

        return view('fors.silver.assign', compact('silverPolicy', 'drivers', 'assignedDriverIds'));
    }

    public function SilverassignPolicy(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'driver_ids.*' => 'required|exists:drivers,id',
        ]);

        // Retrieve the policy and driver IDs
        $silverPolicy = ForsSilver::findOrFail($id);
        $driverIds = $request->input('driver_ids');

        // Attach multiple drivers to the policy
        $silverPolicy->drivers()->sync($driverIds);

        // Redirect back with a success message
        return redirect()->route('fors.silver.index')->with('success', 'Silver Policy assigned successfully.');
    }
}
