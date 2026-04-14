<?php

namespace App\Http\Controllers;

use App\Models\ForsGold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForsGoldController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('manage fors')) {

            $goldPolicies = ForsGold::all();

            return view('fors.gold.index', compact('goldPolicies'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        $user = \Auth::user();
        if ($user->can('manage fors')) {

            return view('fors.gold.create');
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
                    'gold_policy_name' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $gold = new ForsGold();
            $gold->gold_policy_name = $request->gold_policy_name;
            $gold->save();

            return redirect()->route('fors.gold.index')->with('success', __('Gold Policy successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(ForsGold $forsGold)
    {
        return view('fors.gold.edit', compact('forsGold'));
    }

    public function update(Request $request, ForsGold $forsGold)
    {
        if (\Auth::user()->can('manage fors')) {
            $validator = \Validator::make(
                $request->all(), [
                    'gold_policy_name' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $forsGold->gold_policy_name = $request->gold_policy_name;
            $forsGold->save();

            return redirect()->route('fors.gold.index')->with('success', __('Gold Policy successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function GoldPolicy_descriptionStore($id, Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $forsGold = ForsGold::find($id);

            $forsGold->gold_policy_description = $request->gold_policy_description;
            $forsGold->save();

            return response()->json(
                [
                    'is_success' => true,
                    'success' => __('Gold Policy description successfully saved!'),
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
            $forsGold = ForsGold::find($id);

            // Check if $forsGold is null
            if (! $forsGold) {
                return redirect()->back()->with('error', __('forsGold not found.'));
            }

            $acceptedDrivers = \Illuminate\Support\Facades\DB::table('driver_gold_policy')
                ->join('drivers', 'driver_gold_policy.driver_id', '=', 'drivers.id')
                ->join('company_details', 'drivers.companyName', '=', 'company_details.id') // Adjust based on actual relationship
                ->where('fors_gold_id', $id)
                ->where('driver_gold_policy.status', 'Accept')
                ->get([
                    'driver_gold_policy.driver_id',
                    'driver_gold_policy.driver_signature',
                    'drivers.name',
                    'company_details.name as companyName', // Fetch the company name
                ]);

            $declinedDrivers = \Illuminate\Support\Facades\DB::table('driver_gold_policy')
                ->join('drivers', 'driver_gold_policy.driver_id', '=', 'drivers.id')
                ->join('company_details', 'drivers.companyName', '=', 'company_details.id') // Adjust based on actual relationship
                ->where('fors_gold_id', $id)
                ->where('driver_gold_policy.status', 'Decline')
                ->get(['drivers.name as declinedDriverName', 'company_details.name as companyName']);

            return view('fors.gold.show', compact('forsGold', 'acceptedDrivers', 'declinedDrivers'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(ForsGold $forsGold)
    {
        if (\Auth::user()->can('manage fors')) {
            $forsGold->delete();

            return redirect()->route('fors.gold.index')->with('success', __('Gold Policy successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function Goldassign($id)
    {
        $goldPolicy = ForsGold::findOrFail($id);
        $drivers = \App\Models\Driver::all(); // Fetch the list of drivers

        // Fetch assigned drivers (assuming there's a relationship defined in the BronzePolicy model)
        $assignedDrivers = $goldPolicy->drivers; // Adjust this line based on your actual relationships
        $assignedDriverIds = $assignedDrivers->pluck('id')->toArray();

        return view('fors.gold.assign', compact('goldPolicy', 'drivers', 'assignedDriverIds'));
    }

    public function GoldassignPolicy(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'driver_ids.*' => 'required|exists:drivers,id',
        ]);

        // Retrieve the policy and driver IDs
        $goldPolicy = ForsGold::findOrFail($id);
        $driverIds = $request->input('driver_ids');

        // Attach multiple drivers to the policy
        $goldPolicy->drivers()->sync($driverIds);

        // Redirect back with a success message
        return redirect()->route('fors.gold.index')->with('success', 'Gold Policy assigned successfully.');
    }
}
