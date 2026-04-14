<?php

namespace App\Http\Controllers;

use App\Mail\PolicyRequestNotification;
use App\Models\CompanyDetails;
use App\Models\Driver;
use App\Models\ForsBronze;
use App\Models\ForsGold;
use App\Models\ForsSilver;
use App\Models\Group;
use App\Models\PolicyAssignment;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ForsAssignPolicyController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('manage assign policy')) {
            $user = \Auth::user();

            // Grouped All Policies
            $allPolicies = ForsBronze::all()->groupBy('policy_type');

            // Only company-specific policies (Bronze, Silver, Gold)
            $companyPolicies = ForsBronze::where('companyname', $user->companyname)->get()->groupBy('policy_type');

            $fors = [
                'bronze' => $allPolicies->get('Bronze', collect()),
                'silver' => $allPolicies->get('Silver', collect()),
                'gold' => $allPolicies->get('Gold', collect()),
            ];

            $companyFors = [
                'bronze' => $companyPolicies->get('Bronze', collect()),
                'silver' => $companyPolicies->get('Silver', collect()),
                'gold' => $companyPolicies->get('Gold', collect()),
            ];

            // Filter drivers for company role
            if ($user->hasRole('Companies')) {
                $drivers = Driver::where('companyName', $user->companyname)->get();
            } else {
                $drivers = Driver::all();
            }

            return view('fors.assignpolicy.index', compact('fors', 'companyFors', 'drivers', 'user'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function assignPolicies(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'drivers' => 'required|array',
            'drivers.*' => 'exists:drivers,id',
            'selected_bronze_policies' => 'nullable|array',
            'selected_silver_policies' => 'nullable|array',
            'selected_gold_policies' => 'nullable|array',
        ]);

        $driverIds = $validated['drivers'];
        $bronzePolicies = $validated['selected_bronze_policies'] ?? [];
        $silverPolicies = $validated['selected_silver_policies'] ?? [];
        $goldPolicies = $validated['selected_gold_policies'] ?? [];

        $userId = auth()->id(); // Get the logged-in user's ID

        foreach ($driverIds as $driverId) {
            $driver = Driver::find($driverId);

            // Attach selected bronze policies with user ID
            if (! empty($bronzePolicies)) {
                foreach ($bronzePolicies as $policyId) {
                    $driver->bronze_policies()->syncWithoutDetaching([
                        $policyId => ['assigned_by' => $userId],
                    ]);
                }
            }

            // Attach selected silver policies with user ID
            if (! empty($silverPolicies)) {
                foreach ($silverPolicies as $policyId) {
                    $driver->fors_silvers()->syncWithoutDetaching([
                        $policyId => ['assigned_by' => $userId],
                    ]);
                }
            }

            // Attach selected gold policies with user ID
            if (! empty($goldPolicies)) {
                foreach ($goldPolicies as $policyId) {
                    $driver->fors_gold()->syncWithoutDetaching([
                        $policyId => ['assigned_by' => $userId],
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', __('Policies assigned successfully.'));
    }

public function step2(Request $request)
    {
        $selectedPolicies = json_decode($request->input('selected_policies'), true);
        $user = auth()->user();

        // Check user role
        if ($user->hasRole('companys') || $user->hasRole('PTC manager')) {
            $companies = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->get();
        } else {
            $companies = CompanyDetails::where('id', $user->companyname)->where('company_status', 'Active')->orderBy('name', 'asc')->get();
        }

         $bronzeIds = collect($selectedPolicies['bronze'] ?? [])->pluck('id')->toArray();
    $silverIds = collect($selectedPolicies['silver'] ?? [])->pluck('id')->toArray();
    $goldIds   = collect($selectedPolicies['gold'] ?? [])->pluck('id')->toArray();

        $policies = [
            'bronze' => ForsBronze::where('policy_type', 'Bronze')
                ->whereIn('id', $bronzeIds)->get(),
            'silver' => ForsBronze::where('policy_type', 'Silver')
                ->whereIn('id', $silverIds)->get(),
            'gold' => ForsBronze::where('policy_type', 'Gold')
                ->whereIn('id', $goldIds)->get(),
        ];

        // Fetch groups based on the company IDs
        $groups = [];
        if (!empty($companies)) {
            $companyIds = $companies->pluck('id');
            $groups = Group::whereIn('company_id', $companyIds)->get();
        }

        return view('fors.assignpolicy.next', compact('policies', 'companies', 'selectedPolicies', 'groups'));
    }
    // public function getDrivers($companyId)
    // {

    //     // Fetch drivers based on the company ID
    //     $drivers = Driver::where('companyName', $companyId)->get(['id', 'name']);

    //     return response()->json($drivers);
    // }

    public function getGroupsByCompany(Request $request)
    {
        $companyId = $request->company_id;
        $user = \Auth::user();

        // User vehicle group ids
        $userGroupIds = is_array($user->driver_group_id)
            ? $user->driver_group_id
            : json_decode($user->driver_group_id, true);

        if (! is_array($userGroupIds)) {
            $userGroupIds = [$user->driver_group_id];
        }

        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {

            $groups = \App\Models\Group::where('company_id', $companyId)
                ->get();

        } else {

            // 🚫 Prevent accessing other company
            if ($companyId != $user->companyname) {
                return response()->json(['groups' => []]);
            }

            $groups = \App\Models\Group::where('company_id', $companyId)
                ->whereIn('id', $userGroupIds ?? [])
                ->get();
        }

        return response()->json($groups);
    }

    public function getDriversByGroups(Request $request)
    {
        $groupIds = explode(',', $request->group_ids);

        $user = \Auth::user();

        $depotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
        if (! is_array($depotIds)) {
            $depotIds = [$user->depot_id];
        }

        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {

            $drivers = \App\Models\Driver::with('group')
                ->where('driver_status', 'Active')
                ->whereIn('group_id', $groupIds)
                ->get(['id', 'name', 'group_id']);

        } else {

            $drivers = \App\Models\Driver::with('group')
                ->where('driver_status', 'Active')
                ->whereIn('group_id', $groupIds)
                ->whereIn('depot_id', $depotIds)
                ->get(['id', 'name', 'group_id']);
        }

        return response()->json($drivers);
    }

    // public function assignPolicy(Request $request)
    // {
    //     $validated = $request->validate([
    //         'company_id' => 'required|exists:company_details,id',
    //         'drivers' => 'required|array',
    //         'policies' => 'required|array',
    //     ]);

    //     $companyId = $validated['company_id'];
    //     $driverIds = $validated['drivers'];
    //     $policies = $validated['policies'];
    //     $assignedBy = auth()->id(); // Get the ID of the currently logged-in user
    //     $currentDate = \Carbon\Carbon::now();
    //     $nextReviewDate = $currentDate->copy()->addYear()->subDay();

    //     // Retrieve the company name
    //     $company = \App\Models\CompanyDetails::find($companyId);
    //     $companyName = $company ? $company->name : 'Unknown Company';

    //     try {
    //         foreach ($policies as $type => $policyNames) {
    //             foreach ($policyNames as $policyName) {
    //                 $policyId = null;
    //                 $description = null;
    //                 $version = '1.0'; // Default version

    //                 switch ($type) {
    //                     case 'bronze':
    //                         $policy = ForsBronze::where('bronze_policy_name', $policyName)->first();
    //                         if ($policy) {
    //                             $policyId = $policy->id;
    //                             $description = $policy->bronze_policy_description;
    //                         }
    //                         break;
    //                     case 'silver':
    //                         $policy = ForsSilver::where('silver_policy_name', $policyName)->first();
    //                         if ($policy) {
    //                             $policyId = $policy->id;
    //                             $description = $policy->silver_policy_description;
    //                         }
    //                         break;
    //                     case 'gold':
    //                         $policy = ForsGold::where('gold_policy_name', $policyName)->first();
    //                         if ($policy) {
    //                             $policyId = $policy->id;
    //                             $description = $policy->gold_policy_description;
    //                         }
    //                         break;
    //                 }

    //                 if ($policyId) {
    //                     // Replace the {companyname} tag in the description
    //                     if ($description) {
    //                         $description = str_replace('{companyname}', $companyName, $description);
    //                     }

    //                     // Get the latest version for this policy and company
    //                     $latestAssignment = PolicyAssignment::where('policy_id', $policyId)
    //                         ->where('company_id', $companyId)
    //                         ->orderBy('policy_version', 'desc')
    //                         ->first();
    //                     if ($latestAssignment) {
    //                         $version = $this->incrementVersion($latestAssignment->policy_version);
    //                     }

    //                     // Assign the policy to all selected drivers with the determined version
    //                     foreach ($driverIds as $driverId) {
    //                         PolicyAssignment::create([
    //                             'driver_id' => $driverId,
    //                             'policy_type' => $type,
    //                             'policy_id' => $policyId,
    //                             'company_id' => $companyId,
    //                             'status' => 'Pending',
    //                             'policy_version' => $version,
    //                             'assigned_by' => $assignedBy,
    //                             'description' => $description,
    //                             'reviewed_on' => $currentDate->format('d/m/Y'),
    //                             'next_review_date' => $nextReviewDate->format('d/m/Y')
    //                         ]);
    //                     }
    //                 }
    //             }
    //         }

    //         return response()->json(['success' => true]);

    //     } catch (\Exception $e) {
    //         return response()->json(['success' => false, 'message' => 'Failed to assign policies.']);
    //     }
    // }

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    protected function sendNotification($title, $message, $driverIds)
    {
        // Retrieve FCM tokens for the specified drivers
        $tokens = \App\Models\Driver::whereIn('id', $driverIds)->pluck('device_token')->toArray();

        $this->notificationService->send([
            'title' => $title,
            'message' => $message,
            'tokens' => $tokens, // Pass the FCM tokens
            'target' => 'mobile_app', // Customize if needed
        ]);
        // Save notification to the database
        $this->saveNotificationToDatabase($title, $message, $driverIds);
    }

    protected function saveNotificationToDatabase($title, $message, $driverIds)
    {
        foreach ($driverIds as $driverId) {
            \App\Models\DriverNotification::create([
                'driver_id' => $driverId,
                'title' => $title,
                'message' => $message,
                'key' => 2,
            ]);
        }
    }

    public function assignPolicy(Request $request)
{
    $validated = $request->validate([
        'company_id' => 'required|exists:company_details,id',
        'drivers' => 'required|array',
        'policies' => 'required|array',
    ]);

    $companyId = $validated['company_id'];
    $driverIds = $validated['drivers'];
    $policies = $validated['policies'];
    $assignedBy = auth()->id(); // Get the ID of the currently logged-in user
    $currentDate = \Carbon\Carbon::now();
    $nextReviewDate = $currentDate->copy()->addYear()->subDay();

    // Retrieve the company name
    $company = \App\Models\CompanyDetails::find($companyId);
    $companyName = $company ? $company->name : 'Unknown Company';

    // Collect policy names for notification
    $policyNames = [];
    try {
        foreach ($policies as $type => $policyNamesArray) {
            foreach ($policyNamesArray as $policyData) {
                $policyId = $policyData['id'] ?? null;
    $policyName = $policyData['name'] ?? null;

                $description = null;
                $version = '1.0'; // Default version

               switch ($type) {
                    case 'bronze':
                        $policy = ForsBronze::find($policyId);
                        if ($policy) {
                            $policyId = $policy->id;
                            $description = $policy->bronze_policy_description;
                        }
                        break;
                    case 'silver':
                       $policy = ForsBronze::find($policyId);
                        if ($policy) {
                            $policyId = $policy->id;
                            $description = $policy->bronze_policy_description;
                        }
                        break;
                    case 'gold':
                        $policy = ForsBronze::find($policyId);
                        if ($policy) {
                            $policyId = $policy->id;
                            $description = $policy->bronze_policy_description;
                        }
                        break;
                }

                if ($policyId) {
                    // Replace the {companyname} tag in the description
                    if ($description) {
                        $description = str_replace('{companyname}', $companyName, $description);
                    }

                    // Get the latest version for this policy and company
                    $latestAssignment = PolicyAssignment::where('policy_id', $policyId)
                        ->where('company_id', $companyId)
                        ->orderBy('policy_version', 'desc')
                        ->first();
                    if ($latestAssignment) {
                        $version = $this->incrementVersion($latestAssignment->policy_version);
                    }

                    // Assign the policy to all selected drivers with the determined version
                    foreach ($driverIds as $driverId) {
                        PolicyAssignment::create([
                            'driver_id' => $driverId,
                            'policy_type' => $type,
                            'policy_id' => $policyId,
                            'company_id' => $companyId,
                            'status' => 'Pending',
                            'policy_version' => $version,
                            'assigned_by' => $assignedBy,
                            'description' => $description,
                            'reviewed_on' => $currentDate->format('d/m/Y'),
                            'next_review_date' => $nextReviewDate->format('d/m/Y')
                        ]);
                    }

                    // Collect policy names for notification
                    $policyNames[] = $policyName;
                }
            }
        }

        // Send notification to the mobile app
        $policyNamesString = implode(', ', $policyNames);
        $this->sendNotification(
            'Policies Assigned',
            "The following policies have been assigned: {$policyNamesString}.",
            $driverIds
        );

        return response()->json(['success' => true]);

    } catch (\Exception $e) {
        \Log::error('Policy assignment failed: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Failed to assign policies.', 'error' => $e->getMessage()]);
    }
}

    //     public function reassignPolicy(Request $request)
    //     {
    //     // Validate the request data
    //     $validated = $request->validate([
    //         'assignment_id' => 'required|exists:policy_assignments,id',
    //     ]);

    //     $assignmentId = $validated['assignment_id'];
    //     $assignedBy = auth()->id();

    //     try {
    //         // Find the policy assignment by ID
    //         $policyAssignment = PolicyAssignment::find($assignmentId);

    //         if (!$policyAssignment) {
    //                 return response()->json(['success' => false, 'message' => 'Policy assignment not found.']);
    //         }

    //         // Update the policy assignment record
    //         $policyAssignment->update([
    //             'status' => 'Reassigned',
    //             'assigned_by' => $assignedBy
    //         ]);

    //             return response()->json(['success' => true, 'message' => 'Policy Reassigned successfully.']);

    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //             return response()->json(['success' => false, 'message' => $e->validator->errors()->first()]);
    //     } catch (\Exception $e) {
    //             return response()->json(['success' => false, 'message' => 'Failed to reassign policy. ' . $e->getMessage()]);
    //     }
    // }

    public function reassignPolicy(Request $request)
    {
        $validated = $request->validate([
            'assignment_id' => 'required|exists:policy_assignments,id',
        ]);

        $assignmentId = $validated['assignment_id'];
        $assignedBy = auth()->id();

        try {
            $policyAssignment = PolicyAssignment::findOrFail($assignmentId);

            // ✅ Allow only Decline or Completed to be reassigned
            if (! in_array($policyAssignment->status, ['Decline', 'Accept'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This policy cannot be reassigned.',
                ]);
            }

            // ✅ Update status
            $policyAssignment->update([
                'status' => 'Reassigned',
                'assigned_by' => $assignedBy,
            ]);

            // Driver notification
            $driverId = $policyAssignment->driver_id;
            $driver = Driver::find($driverId);

            $policyName = optional($policyAssignment->policy)->bronze_policy_name ?? 'Policy';

            if ($driver) {
                $this->sendNotification(
                    'Policy Reassigned',
                    "Your policy {$policyName} has been reassigned successfully.",
                    [$driverId]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Policy reassigned successfully.',
            ]);

        } catch (\Exception $e) {

            // ❌ Do NOT revert status blindly
            \Log::error('Policy reassign failed', [
                'assignment_id' => $assignmentId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reassign policy.',
            ]);
        }
    }

    /**
     * Increment the policy version.
     *
     * @param  string  $currentVersion
     * @return string
     */
    private function incrementVersion($currentVersion)
    {
        $parts = explode('.', $currentVersion);

        // Ensure there are exactly two parts for major and minor versions
        if (count($parts) == 2) {
            $major = intval($parts[0]);
            $minor = intval($parts[1]);

            // Increment the minor version
            $minor++;

            // Check if the minor version has reached 10
            if ($minor >= 10) {
                // Reset minor version to 0 and increment major version
                $minor = 0;
                $major++;
            }

            // Return the new version string
            return $major.'.'.str_pad($minor, 1, '0', STR_PAD_LEFT);
        }

        // Fallback to default version
        return '1.0';
    }

    public function viewAssignPolicyindex(Request $request)
    {
        $user = \Auth::user();
        $depotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
        if (! is_array($depotIds)) {
            $depotIds = [$user->depot_id];
        }

        $driverGroupIds = is_array($user->driver_group_id)
                       ? $user->driver_group_id
                       : json_decode($user->driver_group_id, true);

        if (! is_array($driverGroupIds)) {
            $driverGroupIds = [$user->driver_group_id];
        }

        $selectedDepotId = $request->input('depot_id');
             $selectedGroupId = $request->input('group_id');

        if ($user->can('manage assign policy')) {
            $query = PolicyAssignment::with(['driver', 'company'])
                ->whereHas('company', function ($q) {
                    $q->where('company_status', 'Active'); // Only include assignments where the company is active
                });

            // Determine the company ID for filtering
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                // For roles that should see all companies
                if ($request->has('company_id') && $request->company_id) {
                    $query->where('company_id', $request->company_id);
                }

                $query->whereHas('driver', function ($q) use ($selectedDepotId, $selectedGroupId) {

                    if (! empty($selectedDepotId)) {
                        $q->where('depot_id', $selectedDepotId);
                    }

                    if (!empty($selectedGroupId)) {
        $q->where('group_id', $selectedGroupId); // ✅ ADD THIS
    }
                });

            } else {
                // For other roles, apply filtering based on the user's associated company
                $query->where('company_id', $user->companyname)
                    ->whereHas('driver', function ($q) use ($depotIds, $driverGroupIds, $selectedDepotId, $selectedGroupId) {

                        // Role based restriction
                        $q->whereIn('depot_id', $depotIds)
                            ->whereIn('group_id', $driverGroupIds);

                        // Depot filter
                        if (! empty($selectedDepotId)) {
                            $q->where('depot_id', $selectedDepotId);
                        }

                        // Driver filter
                        if (!empty($selectedGroupId)) {
            $q->where('group_id', $selectedGroupId); // ✅ ADD THIS
        }
                    });
            }

            if ($request->has('policy_id') && $request->policy_id && $request->policy_id != 'all') {
                $query->where('policy_id', $request->policy_id);
            }

            if ($request->has('policy_version') && $request->policy_version) {
                $query->where('policy_version', $request->policy_version);
            }

            // Add status filter
            if ($request->has('status') && $request->status != 'all') {
                $query->where('status', $request->status);
            }

            $policyAssignments = $query->orderBy('id', 'desc')->get();

            // Resolve policy names
            $policyAssignments->each(function ($assignment) {
                $assignment->policy_name = $this->getPolicyName($assignment->policy_id);
            });

            // Get companies and policy names for filtering
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                $companies = CompanyDetails::where('company_status', 'Active')->get(); // Only Active companies
            } else {
                $userCompanyId = $user->companyname; // Get the company ID of the logged-in user
                $companies = CompanyDetails::where('id', $userCompanyId)->where('company_status', 'Active')->get(); // Fetch only the user's active company
            }

            // Fetch policy IDs and policy versions for the selected company
            $policyNames = [];
            $policyVersions = []; // Initialize the variable

            if ($request->has('company_id') && $request->company_id) {
                $policyIds = PolicyAssignment::where('company_id', $request->company_id)
                    ->pluck('policy_id');

                foreach ($policyIds as $policyId) {
                    $policyNames[$policyId] = $this->getPolicyName($policyId);
                }

                // Fetch unique policy versions and sort them as version numbers
                $query = PolicyAssignment::where('company_id', $request->company_id);

                if ($request->has('policy_id') && $request->policy_id && $request->policy_id != 'all') {
                    $query->where('policy_id', $request->policy_id);
                }

                $policyVersions = $query->pluck('policy_version')
                    ->unique()
                    ->sort(function ($a, $b) {
                        return version_compare($a, $b);
                    })
                    ->mapWithKeys(function ($version) {
                        return [$version => $version];
                    });
            }

            // Fetch unique status values
            $statuses = PolicyAssignment::distinct()->pluck('status')->toArray();
            $companyDetails = \App\Models\CompanyDetails::find(Auth::user()->companyname);

            $depotsQuery = \App\Models\Depot::orderBy('name', 'asc');
            if (! $user->hasRole('company') && ! $user->hasRole('PTC manager')) {
                $depotsQuery->whereIn('id', $depotIds);
            }
            $depots = $depotsQuery->get();

            $groupsQuery = \App\Models\Group::orderBy('name', 'asc');

            if (! $user->hasRole('company') && ! $user->hasRole('PTC manager')) {
                $groupsQuery->whereIn('id', $driverGroupIds);
            }

            $groups = $groupsQuery->get();

            return view('fors.assignpolicy.viewpolicy.view', compact('policyAssignments', 'companies', 'policyNames', 'policyVersions', 'statuses', 'companyDetails', 'depots', 'groups'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function exportAssignPolicy(Request $request)
    {
        $user = \Auth::user();

        $depotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
        if (! is_array($depotIds)) {
            $depotIds = [$user->depot_id];
        }

        $driverGroupIds = is_array($user->driver_group_id) ? $user->driver_group_id : json_decode($user->driver_group_id, true);
        if (! is_array($driverGroupIds)) {
            $driverGroupIds = [$user->driver_group_id];
        }

        $selectedDepotId = $request->input('depot_id');
            $selectedGroupId = $request->input('group_id');

        if (! $user->can('manage assign policy')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = PolicyAssignment::with(['driver', 'company', 'creator'])
            ->whereHas('company', function ($q) {
                $q->where('company_status', 'Active');
            });

        // Role based company filter
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
            if ($request->company_id) {
                $query->where('company_id', $request->company_id);
            }

              $query->whereHas('driver', function ($q) use ($selectedDepotId, $selectedGroupId) {

        if (!empty($selectedDepotId)) {
            $q->where('depot_id', $selectedDepotId);
        }

        if (!empty($selectedGroupId)) {
            $q->where('group_id', $selectedGroupId);
        }
    });

        } else {
            $query->where('company_id', $user->companyname)
                ->whereHas('driver', function ($q) use ($depotIds, $driverGroupIds, $selectedDepotId, $selectedGroupId) {

                    $q->whereIn('depot_id', $depotIds)
                        ->whereIn('group_id', $driverGroupIds);

            if (!empty($selectedDepotId)) {
                $q->where('depot_id', $selectedDepotId);
            }

            if (!empty($selectedGroupId)) {
                $q->where('group_id', $selectedGroupId);
            }
                });
        }

        // Filters
        if ($request->policy_id && $request->policy_id != 'all') {
            $query->where('policy_id', $request->policy_id);
        }

        if ($request->policy_version) {
            $query->where('policy_version', $request->policy_version);
        }

        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $assignments = $query->get();

        // Prepare export data
        $exportData = $assignments->map(function ($row) {
            return [
                'Company' => strtoupper($row->company->name ?? ''),
                'Driver' => $row->driver->name ?? '',
                'Policy Name' => $this->getPolicyName($row->policy_id),
                'Policy Version' => $row->policy_version,
                'Status' => $row->status,
                'Duration' => $row->duration,
                'Release Date' => $row->reviewed_on,

                'Comment' => $row->comment ?? '',
                'Assigned By' => $row->creator->username ?? '',

            ];
        });

        if ($exportData->isEmpty()) {
            return redirect()->back()->with('error', 'No data available for export');
        }

        // CSV Download
        $filename = 'policy_assignments_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($exportData) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_keys($exportData->first()));
            foreach ($exportData as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename);
    }

    public function getPolicyVersions(Request $request)
    {
    $user = \Auth::user(); // ✅ ADD THIS

    // ✅ company resolve karo
    if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
        $companyId = $request->input('company_id');
    } else {
        $companyId = $user->companyname; // ✅ auto for other users
    }

        if (! $companyId) {
            return response()->json(['policy_versions' => []]);
        }

    $query = PolicyAssignment::where('company_id', $companyId);

    // ✅ optional: policy_id filter (already tamara JS ma che)
    if ($request->has('policy_id') && $request->policy_id && $request->policy_id != 'all') {
        $query->where('policy_id', $request->policy_id);
    }

    // ✅ role based restriction (same as index)
    if (!($user->hasRole('company') || $user->hasRole('PTC manager'))) {

        $depotIds = is_array($user->depot_id)
            ? $user->depot_id
            : json_decode($user->depot_id, true);

        $driverGroupIds = is_array($user->driver_group_id)
            ? $user->driver_group_id
            : json_decode($user->driver_group_id, true);

        $query->whereHas('driver', function ($q) use ($depotIds, $driverGroupIds) {
            $q->whereIn('depot_id', $depotIds)
              ->whereIn('group_id', $driverGroupIds);
        });
    }

    $policyVersions = $query->pluck('policy_version')
            ->unique()
        ->sort(function ($a, $b) {
            return version_compare($a, $b);
        })
            ->mapWithKeys(function ($version) {
            return [$version => $version];
            });

        return response()->json(['policy_versions' => $policyVersions]);
    }

    public function getPolicyNamesList(Request $request)
    {
    $user = \Auth::user();

    // ✅ IMPORTANT: company resolve karo
    if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
        $companyId = $request->input('company_id');
    } else {
        // ✅ other user → auto company
        $companyId = $user->companyname;
    }

        $policyNames = [];

        if ($companyId) {

        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {

            $policyIds = PolicyAssignment::where('company_id', $companyId)
                ->pluck('policy_id');

        } else {

            $depotIds = is_array($user->depot_id)
                ? $user->depot_id
                : json_decode($user->depot_id, true);

            $driverGroupIds = is_array($user->driver_group_id)
                ? $user->driver_group_id
                : json_decode($user->driver_group_id, true);

            $policyIds = PolicyAssignment::where('company_id', $companyId)
                ->whereHas('driver', function ($q) use ($depotIds, $driverGroupIds) {
                    $q->whereIn('depot_id', $depotIds)
                      ->whereIn('group_id', $driverGroupIds);
                })
                ->pluck('policy_id');
        }

            foreach ($policyIds as $policyId) {
                $policyNames[$policyId] = $this->getPolicyName($policyId);
            }
        }

        return response()->json(['policy_names' => $policyNames]);
    }

    private function getPolicyName($policyId)
    {
        if (ForsBronze::where('id', $policyId)->exists()) {
            return ForsBronze::where('id', $policyId)->first()->bronze_policy_name;
        } elseif (ForsSilver::where('id', $policyId)->exists()) {
            return ForsSilver::where('id', $policyId)->first()->silver_policy_name;
        } elseif (ForsGold::where('id', $policyId)->exists()) {
            return ForsGold::where('id', $policyId)->first()->gold_policy_name;
        }

        return 'Unknown Policy';
    }

    public function downloadPdf(Request $request)
    {
        if (\Auth::user()->can('manage assign policy')) {

            $user = \Auth::user();

        // ✅ Resolve companyId (IMPORTANT FIX)
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
            $companyId = $request->company_id;
        } else {
            $companyId = $user->companyname;
        }

        // Depot & Group
            $depotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
        if (!is_array($depotIds)) {
                $depotIds = [$user->depot_id];
            }

            $driverGroupIds = is_array($user->driver_group_id) ? $user->driver_group_id : json_decode($user->driver_group_id, true);
        if (!is_array($driverGroupIds)) {
                $driverGroupIds = [$user->driver_group_id];
            }

        $selectedDepotId = $request->input('depot_id');
        $selectedGroupId = $request->input('group_id');

        // ✅ Build Query
            $query = PolicyAssignment::with(['driver', 'company', 'creator'])
                ->whereHas('company', function ($q) {
                $q->where('company_status', 'Active');
                });

            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {

            if ($companyId) {
                $query->where('company_id', $companyId);
                }

            $query->whereHas('driver', function ($q) use ($selectedDepotId, $selectedGroupId) {
                if (!empty($selectedDepotId)) {
                    $q->where('depot_id', $selectedDepotId);
                }

                if (!empty($selectedGroupId)) {
                    $q->where('group_id', $selectedGroupId);
                }
            });

            } else {

            $query->where('company_id', $companyId)
                ->whereHas('driver', function ($q) use ($depotIds, $driverGroupIds, $selectedDepotId, $selectedGroupId) {

                        $q->whereIn('depot_id', $depotIds)
                            ->whereIn('group_id', $driverGroupIds);

                    if (!empty($selectedDepotId)) {
                        $q->where('depot_id', $selectedDepotId);
                    }

                    if (!empty($selectedGroupId)) {
                        $q->where('group_id', $selectedGroupId);
                    }
                    });
            }

        // Filters
            if ($request->has('policy_id') && $request->policy_id && $request->policy_id != 'all') {
                $query->where('policy_id', $request->policy_id);
            }

            if ($request->has('policy_version') && $request->policy_version) {
                $query->where('policy_version', $request->policy_version);
            }

        // Execute
            $policyAssignments = $query->get();

        // ✅ Get company name once
        $companyName = \App\Models\CompanyDetails::find($companyId)->name ?? 'Default Company';

        // ✅ Map policy data
        $policyAssignments->each(function ($assignment) use ($companyName) {

                $policyName = $this->getPolicyName($assignment->policy_id);

                $policyDescription = str_replace('{companyname}', $companyName, $assignment->description);

                $assignment->policy_name = $policyName;
                $assignment->description = $policyDescription;
            });

        // Group by policy
            $groupedPolicyAssignments = $policyAssignments->groupBy('policy_id');

            // Get companies and policy names for filtering
            $companies = CompanyDetails::all();

        // Policy IDs
            $policyIds = $policyAssignments->pluck('policy_id')->unique();

        // Policy Names
            $policyNames = [];
        if ($companyId) {
                foreach ($policyIds as $policyId) {
                    $policyNames[$policyId] = $this->getPolicyName($policyId);
                }
            }

        // Policy Versions
            $selectedVersion = $request->input('policy_version');

            $policyVersionsQuery = PolicyAssignment::select('policy_id', 'policy_version', 'reviewed_on', 'next_review_date', 'assigned_by')
                ->whereIn('policy_id', $policyIds)
                ->groupBy('policy_id', 'policy_version');

            if ($selectedVersion) {
                $policyVersionsQuery->where('policy_version', '<=', $selectedVersion);
            }

            $policyVersions = $policyVersionsQuery->get();

        // Logo
            $settings = \App\Models\Utility::settings();
            $company_logo = \App\Models\Utility::getValByName('company_logo');

        $imagePath = storage_path('/uploads/logo/' . (!empty($company_logo) ? $company_logo : '5-logo-dark.png'));

            if (file_exists($imagePath)) {
                $imageData = base64_encode(file_get_contents($imagePath));
            $img = 'data:image/png;base64,' . $imageData;
            } else {
            $img = '';
            }

        // File Name
        $fileName = 'Policy.pdf';

            if ($request->has('policy_id') && $request->policy_id && $request->policy_id != 'all') {
                $policyName = $this->getPolicyName($request->policy_id);
            $policyVersion = $selectedVersion ?: optional($policyVersions->first())->policy_version;
                $fileName = "{$policyName}(v{$policyVersion}).pdf";
            }

        // PDF Load
            $pdf = Pdf::loadView('fors.assignpolicy.viewpolicy.template', [
                'groupedPolicyAssignments' => $groupedPolicyAssignments,
                'companies' => $companies,
                'policyNames' => $policyNames,
                'companyName' => $companyName,
                'policyAssignments' => $policyAssignments,
                'policyVersions' => $policyVersions,
            'img' => $img,
            'settings' => $settings,
            ]);

            return $pdf->download($fileName);

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function show()
    {

    }

    public function sendEmailForAssignPolicyRequest(Request $request)
    {
        // Validate only Send Email form
        $request->validate([
            'selected_policies' => 'required',
            'operator_name' => 'required|string',
        ]);

        $selectedPolicies = json_decode($request->selected_policies, true);
      $allPolicies = [];

foreach ($selectedPolicies as $category => $policies) {
    foreach ($policies as $policy) {

        if (is_array($policy)) {
            $policy = implode(', ', $policy);
        }

        // 🔥 ID remove karo
        if (str_contains($policy, ',')) {
            $parts = explode(',', $policy, 2);
            $policy = trim($parts[1]); // only name
        }

        $allPolicies[] = $policy;
    }
}

        $operatorName = is_array($request->operator_name)
    ? implode(', ', $request->operator_name)
    : $request->operator_name;
    
    $companyName = auth()->user()->company->name ?? 'N/A';

if (is_array($companyName)) {
    $companyName = implode(', ', $companyName);
}

        try {
        Mail::to('office@ptctransport.co.uk')
    ->bcc('krushnsarvaiya25@gmail.com')
    ->send(new PolicyRequestNotification(
        $operatorName,
        $companyName,
        $allPolicies
    ));
   
    
    } catch (\Exception $e) {
        // Catch mail sending errors
        return redirect()->back()->with('error', 'Failed to send email: ' . $e->getMessage());
    }

        return redirect()->back()->with('success', 'Policy request email has been sent.');
    }
}
