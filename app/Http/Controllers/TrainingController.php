<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\TrainingCourse;
use App\Models\TrainingType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class TrainingController extends Controller
{
    public function trainingTypeindex(Request $request)
    {
        if (\Auth::user()->can('manage training types')) {
            $user = \Auth::user();
            // Retrieve the company name of the user
            $companyName = $user->companyname;

            // Retrieve the selected company ID from the request
            $selectedCompanyId = $request->input('company_id');

            // Check if the user has the "company" or "PTC manager" role
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                $trainingType = TrainingType::with(['types', 'creator'])
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })->get();
            } else {
                // Show only training types belonging to the user's company
                $trainingType = TrainingType::where('company_id', $user->companyname)
                    ->with(['types', 'creator'])
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })->get();
            }

            $companies = \App\Models\CompanyDetails::where('company_status', 'Active')
                ->orderBy('name', 'asc')
                ->get();

            return view('training.trainingtype.index', compact('trainingType', 'companies'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function trainingTypecreate()
    {
        $user = \Auth::user();
        if (\Auth::user()->can('create training types')) {
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                // Fetch all company names
                $contractTypes = \App\Models\CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->pluck('name', 'id');
            } else {
                // Fetch the company name for the logged-in user
                $contractTypes = \App\Models\CompanyDetails::where('created_by', '=', $user->creatorId())
                    ->where('id', '=', $user->companyname)->orderBy('name', 'asc')->where('company_status', 'Active')
                    ->pluck('name', 'id');

                // Check if the user creating the new user is directly associated with a company
                // If not, remove the company name from the list
                if ($user->companyname) {
                    $contractTypes = \App\Models\CompanyDetails::where('id', '=', $user->companyname)->orderBy('name', 'asc')->where('company_status', 'Active')
                        ->pluck('name', 'id');
                } else {
                    $contractTypes = [];
                }
            }

            return view('training.trainingtype.create', compact('contractTypes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function trainingTypestore(Request $request)
    {
        if (\Auth::user()->can('create training types')) {

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

            $training = new TrainingType();
            $training->name = $request->name;
            $training->company_id = $request->company_id;
            $training->created_by = \Auth::user()->id;
            $training->save();

            return redirect()->route('trainingTypes.index')->with('success', __('Training Type successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show($id)
    {
        try {
            $traId = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Training Not Found.'));
        }
        $traId = Crypt::decrypt($id);
        $training = Training::find($traId);
        $performance = Training::$performance;
        $status = Training::$Status;

        return view('training.show', compact('training', 'performance', 'status'));
    }

    public function trainingTypeedit(TrainingType $trainingTypes)
    {
        $user = \Auth::user();
        if (\Auth::user()->can('edit training types')) {
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {

                $contractTypes = \App\Models\CompanyDetails::where('company_status', 'Active')->orderBy('name', 'asc')->get()->pluck('name', 'id');

            } else {
                $contractTypes = \App\Models\CompanyDetails::where('created_by', '=', \Auth::user()->creatorId())->where('id', '=', $user->companyname)->orderBy('name', 'asc')->get()->pluck('name', 'id');
            }

            return view('training.trainingtype.edit', compact('trainingTypes', 'contractTypes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function trainingTypeupdate(Request $request, TrainingType $trainingTypes)
    {
        if (\Auth::user()->can('edit training types')) {

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

            $trainingTypes->name = $request->name;
            $trainingTypes->company_id = $request->company_id;
            $trainingTypes->created_by = \Auth::user()->id;
            $trainingTypes->save();

            return redirect()->route('trainingTypes.index')->with('success', __('Training Type successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function trainingTypedestroy(TrainingType $trainingTypes)
    {
        if (\Auth::user()->can('delete training types')) {

            $trainingTypes->delete();

            return redirect()->route('trainingTypes.index')->with('success', __('Training Type successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function trainingcourseindex($id)
    {
        if (\Auth::user()->can('manage training course')) {
            $trainingTypes = TrainingType::with('creator', 'TrainingCourse')->findOrFail($id);

            return view('training.course.index', compact('trainingTypes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function trainingcoursecreate($trainingtype_id)
    {
        if (\Auth::user()->can('create training course')) {
            $trainingType = TrainingType::findOrFail($trainingtype_id); // Fetch the TrainingType by ID

            return view('training.course.create', compact('trainingType')); // Pass it to the view
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function trainingcoursestore(Request $request, $trainingtype_id)
    {
        if (\Auth::user()->can('create training course')) {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    'duration' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $training = new TrainingCourse();
            $training->name = $request->name;
            $training->duration = $request->duration;
            $training->trainingtype_id = $trainingtype_id;  // Save the trainingtype_id
            $training->created_by = \Auth::user()->id;
            $training->save();

            return redirect()->route('trainingTypes.course.index', $trainingtype_id)
                ->with('success', __('Training Course successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function trainingcourseedit(TrainingCourse $trainingcourse)
    {

        if (\Auth::user()->can('edit training course')) {
            return view('training.course.edit', compact('trainingcourse'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function trainingcourseupdate(Request $request, TrainingCourse $trainingcourse)
    {
        if (\Auth::user()->can('edit training course')) {

            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    'duration' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $trainingcourse->name = $request->name;
            $trainingcourse->duration = $request->duration;
            $trainingcourse->created_by = \Auth::user()->id;
            $trainingcourse->save();

            return redirect()->route('trainingTypes.course.index', $trainingcourse->trainingtype_id)->with('success', __('Training Course successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function trainingcoursedestroy(TrainingCourse $trainingcourse)
    {
        if (\Auth::user()->can('delete training course')) {

            $trainingcourse->delete();

            return redirect()->route('trainingTypes.course.index', $trainingcourse->trainingtype_id)->with('success', __('Training Course successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function index(Request $request)
    {
        if (\Auth::user()->can('manage trainings')) {
            $user = \Auth::user();
            $selectedCompanyId = $request->input('company_id');
            $selectedDepotId = $request->input('depot_id');
            $selectedGroupId = $request->input('group_id');
            $selectedMonth = $request->input('month', \Carbon\Carbon::now()->month); // Get selected month or use current month
            $selectedYear = $request->input('year', \Carbon\Carbon::now()->year); // Get selected year or use current year

            $depotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
            if (! is_array($depotIds)) {
                $depotIds = [$user->depot_id]; // Ensure it remains an array
            }
            $driverGroupIds = json_decode($user->driver_group_id, true);

            // Check user roles to determine which trainings to retrieve
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                // Retrieve all training data for company and PTC manager roles
                $trainings = \App\Models\Training::with('trainingCourse', 'trainingDriverAssigns.driver')
                    ->whereHas('company', function ($query) {
                        // Check if company has 'Active' status
                        $query->where('company_status', 'Active');
                    })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })
                    ->when($selectedDepotId, function ($query) use ($selectedDepotId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedDepotId) {
                            $q->where('depot_id', $selectedDepotId);
                        });
                    })
                    ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedGroupId) {
                            $q->where('group_id', $selectedGroupId);
                        });
                    })
                    ->get();
            } else {

                // For other roles, retrieve only trainings related to the user's company
                $trainings = \App\Models\Training::with('trainingCourse', 'trainingDriverAssigns.driver')
                    ->where('companyName', $user->companyname) // Assuming company_id links training to a company
                    // Depot Filter
                    ->whereHas('trainingDriverAssigns.driver', function ($q) use ($depotIds) {
                        $q->whereIn('depot_id', $depotIds);
                    })

        // Driver Group Filter
                    ->when($driverGroupIds, function ($query) use ($driverGroupIds) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($driverGroupIds) {
                            $q->whereIn('group_id', $driverGroupIds);
                        });
                    })

                    ->whereHas('company', function ($query) {
                        // Check if company has 'Active' status
                        $query->where('company_status', 'Active');
                    })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->when($selectedDepotId, function ($query) use ($selectedDepotId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedDepotId) {
                            $q->where('depot_id', $selectedDepotId);
                        });
                    })
                    ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedGroupId) {
                            $q->where('group_id', $selectedGroupId);
                        });
                    })->get();
            }

            // Prepare the calendar events
            $events = $trainings->map(function ($training) use ($user, $depotIds, $driverGroupIds, $selectedDepotId,
                $selectedGroupId) {

                $drivers = $training->trainingDriverAssigns
                    ->filter(function ($assignment) use ($user, $depotIds, $driverGroupIds,
                        $selectedDepotId,
                        $selectedGroupId) {

                        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {

                            $driver = $assignment->driver;

                            if (! $driver) {
                                return false;
                            }

                            // 🔥 APPLY FILTER HERE ALSO
                            if ($selectedDepotId && $driver->depot_id != $selectedDepotId) {
                                return false;
                            }

                            if ($selectedGroupId && $driver->group_id != $selectedGroupId) {
                                return false;
                            }

                            return true;
                        }

                        $driver = $assignment->driver;

                        if (! $driver) {
                            return false;
                        }

                        if (! in_array($driver->depot_id, $depotIds)) {
                            return false;
                        }

                        if (! empty($driverGroupIds) && ! in_array($driver->group_id, $driverGroupIds)) {
                            return false;
                        }

                        // 🔥 ALSO APPLY HERE
                        if ($selectedDepotId && $driver->depot_id != $selectedDepotId) {
                            return false;
                        }

                        if ($selectedGroupId && $driver->group_id != $selectedGroupId) {
                            return false;
                        }

                        return true;
                    })
                    ->map(function ($assignment) {
                        return $assignment->driver->name ?? 'No Driver Name';
                    })
                    ->values();

                $fromDate = \Carbon\Carbon::parse($training->from_date)->format('Y-m-d');
                $endDate = \Carbon\Carbon::parse($training->to_date)->addDay()->format('Y-m-d');

                return [
                    'title' => $training->trainingCourse->name ?? null,
                    'start' => $fromDate,
                    'end' => $endDate,
                    'description' => $training->trainingType->name,
                    'drivers' => $drivers->toArray(),
                    'company' => $training->company->name,
                    'status' => $training->status,
                ];

            });

            // Count drivers for the current year with status filter
            $completestatusCount = $trainings->flatMap(function ($training) use ($user, $depotIds, $driverGroupIds,
                $selectedDepotId,
                $selectedGroupId) {

                return $training->trainingDriverAssigns->filter(function ($assignment) use ($user, $depotIds, $driverGroupIds,
                    $selectedDepotId,
                    $selectedGroupId) {

                    $assignmentDate = \Carbon\Carbon::parse($assignment->from_date);

                    if ($assignmentDate->year != \Carbon\Carbon::now()->year) {
                        return false;
                    }

                    if ($assignment->status != 'Complete') {
                        return false;
                    }

                    $driver = $assignment->driver;

                    if (! $driver) {
                        return false;
                    }

                    if (! ($user->hasRole('company') || $user->hasRole('PTC manager'))) {

                        if (! in_array($driver->depot_id, $depotIds)) {
                            return false;
                    }

                        if (! empty($driverGroupIds) && ! in_array($driver->group_id, $driverGroupIds)) {
                            return false;
                        }
                    }

                    // ✅ Selected filters (IMPORTANT)
                    if ($selectedDepotId && $driver->depot_id != $selectedDepotId) {
                        return false;
                    }

                    if ($selectedGroupId && $driver->group_id != $selectedGroupId) {
                        return false;
                    }

                    return true;

                });

            })->count();

            // Count drivers for the current year with status filter
            $PendingstatusCount = $trainings->flatMap(function ($training) use ($user, $depotIds, $driverGroupIds, $selectedGroupId, $selectedDepotId) {

                return $training->trainingDriverAssigns->filter(function ($assignment) use ($user, $depotIds, $driverGroupIds, $selectedGroupId, $selectedDepotId) {

                    $assignmentDate = \Carbon\Carbon::parse($assignment->from_date);

                    if ($assignmentDate->year != \Carbon\Carbon::now()->year) {
                        return false;
                    }

                    if ($assignment->status != 'Pending') {
                        return false;
                    }

                    $driver = $assignment->driver;

                    if (! $driver) {
                        return false;
                    }

                    if (! ($user->hasRole('company') || $user->hasRole('PTC manager'))) {

                        if (! in_array($driver->depot_id, $depotIds)) {
                            return false;
                    }

                        if (! empty($driverGroupIds) && ! in_array($driver->group_id, $driverGroupIds)) {
                            return false;
                        }
                    }

                    // ✅ Selected filters (IMPORTANT)
                    if ($selectedDepotId && $driver->depot_id != $selectedDepotId) {
                        return false;
                    }

                    if ($selectedGroupId && $driver->group_id != $selectedGroupId) {
                        return false;
                    }

                    return true;

                });

            })->count();

            $DeclinestatusCount = $trainings->flatMap(function ($training) use ($user, $depotIds, $driverGroupIds, $selectedGroupId, $selectedDepotId) {

                return $training->trainingDriverAssigns->filter(function ($assignment) use ($user, $depotIds, $driverGroupIds, $selectedGroupId, $selectedDepotId) {

                    $assignmentDate = \Carbon\Carbon::parse($assignment->from_date);

                    if ($assignmentDate->year != \Carbon\Carbon::now()->year) {
                        return false;
                    }

                    if ($assignment->status != 'Decline') {
                        return false;
                    }

                    $driver = $assignment->driver;

                    if (! $driver) {
                        return false;
                    }

                    if (! ($user->hasRole('company') || $user->hasRole('PTC manager'))) {

                        if (! in_array($driver->depot_id, $depotIds)) {
                            return false;
                    }

                        if (! empty($driverGroupIds) && ! in_array($driver->group_id, $driverGroupIds)) {
                            return false;
                        }
                    }

                    // ✅ Selected filters (IMPORTANT)
                    if ($selectedDepotId && $driver->depot_id != $selectedDepotId) {
                        return false;
                    }

                    if ($selectedGroupId && $driver->group_id != $selectedGroupId) {
                        return false;
                    }

                    return true;

                });

            })->count();

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

            $companies = \App\Models\CompanyDetails::where('company_status', 'Active')->orderBy('name', 'asc')->get();

            return view('training.index', compact('trainings', 'events', 'completestatusCount', 'PendingstatusCount', 'DeclinestatusCount', 'companies', 'depots',
                'groups')); // Pass counts to the view
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function getCurrentYearCompletedTraining(Request $request) // Add Request parameter
    {
        if (\Auth::user()->can('manage trainings')) {
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

            $selectedCompanyId = $request->input('company_id');
            $selectedFromDate = $request->input('from_date');
            $selectedToDate = $request->input('to_date');
            $selectedDepotId = $request->input('depot_id');
            $selectedGroupId = $request->input('group_id');

            // Retrieve all training data for company and PTC manager roles
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                $trainings = \App\Models\Training::with('trainingDriverAssigns')
                    ->whereHas('company', function ($query) {
                        // Check if company has 'Active' status
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedFromDate, function ($query) use ($selectedFromDate) {
                        return $query->where('from_date', '>=', $selectedFromDate); // Changed to filter from date
                    })
                    ->when($selectedToDate, function ($query) use ($selectedToDate) {
                        return $query->where('to_date', '<=', $selectedToDate); // Changed to filter to date
                    })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })
                    ->when($selectedDepotId, function ($query) use ($selectedDepotId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedDepotId) {
                            $q->where('depot_id', $selectedDepotId);
                        });
                    })
                    ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedGroupId) {
                            $q->where('group_id', $selectedGroupId);
                        });
                    })
                    ->get();
            } else {
                // For other roles, retrieve only trainings related to the user's company
                $trainings = \App\Models\Training::with([
                    'trainingDriverAssigns' => function ($q) use ($depotIds, $driverGroupIds) {
                        $q->whereHas('driver', function ($driver) use ($depotIds, $driverGroupIds) {
                            $driver->whereIn('depot_id', $depotIds)
                                ->whereIn('group_id', $driverGroupIds);
                        });
                    },
                    'trainingDriverAssigns.driver',
                ])
                    ->where('companyName', $user->companyname) // Assuming company_id links training to a company
                    ->whereHas('company', function ($query) {
                        // Check if company has 'Active' status
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedFromDate, function ($query) use ($selectedFromDate) {
                        return $query->where('from_date', '>=', $selectedFromDate); // Changed to filter from date
                    })
                    ->when($selectedToDate, function ($query) use ($selectedToDate) {
                        return $query->where('to_date', '<=', $selectedToDate); // Changed to filter to date
                    })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->when($selectedDepotId, function ($query) use ($selectedDepotId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedDepotId) {
                            $q->where('depot_id', $selectedDepotId);
                        });
                    })
                    ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedGroupId) {
                            $q->where('group_id', $selectedGroupId);
                        });
                    })
                    ->get();
            }

            // Filter to get only current year completed assignments
            $completedTraining = $trainings->flatMap(function ($training) use ($selectedGroupId, $selectedDepotId) {

                return $training->trainingDriverAssigns->filter(function ($assignment) use ($selectedGroupId, $selectedDepotId) {

                    $assignmentDate = \Carbon\Carbon::parse($assignment->from_date);

                    if ($assignmentDate->year != \Carbon\Carbon::now()->year) {
                        return false;
                    }

                    if ($assignment->status != 'Complete') {
                        return false;
                    }

                    // Depot filter
                    if ($selectedDepotId && $assignment->driver && $assignment->driver->depot_id != $selectedDepotId) {
                        return false;
                    }

                    // Group filter
                    if ($selectedGroupId && $assignment->driver && $assignment->driver->group_id != $selectedGroupId) {
                        return false;
                    }

                    return true;
                });

            });

            $companies = \App\Models\CompanyDetails::where('company_status', 'Active')->orderBy('name', 'asc')->get();

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

            return view('training.currentyear.current_year_completed', compact('completedTraining', 'companies', 'depots', 'groups'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function getCurrentYearPendingTraining(Request $request)
    {
        if (\Auth::user()->can('manage trainings')) {
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
            $selectedCompanyId = $request->input('company_id');
            $selectedFromDate = $request->input('from_date');
            $selectedToDate = $request->input('to_date');
            $selectedDepotId = $request->input('depot_id');
            $selectedGroupId = $request->input('group_id');

            // Retrieve all training data for company and PTC manager roles
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                $trainings = \App\Models\Training::with('trainingDriverAssigns')
                    ->whereHas('company', function ($query) {
                        // Check if company has 'Active' status
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedFromDate, function ($query) use ($selectedFromDate) {
                        return $query->where('from_date', '>=', $selectedFromDate); // Changed to filter from date
                    })
                    ->when($selectedToDate, function ($query) use ($selectedToDate) {
                        return $query->where('to_date', '<=', $selectedToDate); // Changed to filter to date
                    })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->when($selectedDepotId, function ($query) use ($selectedDepotId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedDepotId) {
                            $q->where('depot_id', $selectedDepotId);
                        });
                    })
                    ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedGroupId) {
                            $q->where('group_id', $selectedGroupId);
                        });
                    })
                    ->get();
            } else {
                // For other roles, retrieve only trainings related to the user's company
                $trainings = \App\Models\Training::with([
                    'trainingDriverAssigns' => function ($q) use ($depotIds, $driverGroupIds) {
                        $q->whereHas('driver', function ($driver) use ($depotIds, $driverGroupIds) {
                            $driver->whereIn('depot_id', $depotIds)
                                ->whereIn('group_id', $driverGroupIds);
                        });
                    },
                    'trainingDriverAssigns.driver',
                ])
                    ->where('companyName', $user->companyname)
                    ->whereHas('company', function ($query) {
                        // Check if company has 'Active' status
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedFromDate, function ($query) use ($selectedFromDate) {
                        return $query->where('from_date', '>=', $selectedFromDate); // Changed to filter from date
                    })
                    ->when($selectedToDate, function ($query) use ($selectedToDate) {
                        return $query->where('to_date', '<=', $selectedToDate); // Changed to filter to date
                    })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->when($selectedDepotId, function ($query) use ($selectedDepotId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedDepotId) {
                            $q->where('depot_id', $selectedDepotId);
                        });
                    })
                    ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedGroupId) {
                            $q->where('group_id', $selectedGroupId);
                        });
                    })
                    ->get();
            }

            // Filter to get only current year Pending assignments
            $PendingTraining = $trainings->flatMap(function ($training) use ($selectedGroupId, $selectedDepotId) {

                return $training->trainingDriverAssigns->filter(function ($assignment) use ($selectedGroupId, $selectedDepotId) {

                    $assignmentDate = \Carbon\Carbon::parse($assignment->from_date);

                    if ($assignmentDate->year != \Carbon\Carbon::now()->year) {
                        return false;
                    }

                    if ($assignment->status != 'Pending') {
                        return false;
                    }

                    if ($selectedGroupId && $assignment->driver->group_id != $selectedGroupId) {
                        return false;
                    }

                    if ($selectedDepotId && $assignment->driver->depot_id != $selectedDepotId) {
                        return false;
                    }

                    return true;
                });

            });

            $companies = \App\Models\CompanyDetails::where('company_status', 'Active')->orderBy('name', 'asc')->get();

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

            return view('training.currentyear.current_year_Pending', compact('PendingTraining', 'companies', 'depots', 'groups'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function getCurrentYearDeclineTraining(Request $request)
    {
        if (\Auth::user()->can('manage trainings')) {
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

            $selectedCompanyId = $request->input('company_id');
            $selectedFromDate = $request->input('from_date');
            $selectedToDate = $request->input('to_date');
            $selectedDepotId = $request->input('depot_id');
            $selectedGroupId = $request->input('group_id');

            // Retrieve all training data for company and PTC manager roles
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                $trainings = \App\Models\Training::with('trainingDriverAssigns')
                    ->whereHas('company', function ($query) {
                        // Check if company has 'Active' status
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedFromDate, function ($query) use ($selectedFromDate) {
                        return $query->where('from_date', '>=', $selectedFromDate); // Changed to filter from date
                    })
                    ->when($selectedToDate, function ($query) use ($selectedToDate) {
                        return $query->where('to_date', '<=', $selectedToDate); // Changed to filter to date
                    })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->when($selectedDepotId, function ($query) use ($selectedDepotId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedDepotId) {
                            $q->where('depot_id', $selectedDepotId);
                        });
                    })
                    ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedGroupId) {
                            $q->where('group_id', $selectedGroupId);
                        });
                    })
                    ->get();

            } else {
                // For other roles, retrieve only trainings related to the user's company
                $trainings = \App\Models\Training::with([
                    'trainingDriverAssigns' => function ($q) use ($depotIds, $driverGroupIds) {
                        $q->whereHas('driver', function ($driver) use ($depotIds, $driverGroupIds) {
                            $driver->whereIn('depot_id', $depotIds)
                                ->whereIn('group_id', $driverGroupIds);
                        });
                    },
                    'trainingDriverAssigns.driver',
                ])
                    ->where('companyName', $user->companyname)
                    ->whereHas('company', function ($query) {
                        // Check if company has 'Active' status
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedFromDate, function ($query) use ($selectedFromDate) {
                        return $query->where('from_date', '>=', $selectedFromDate); // Changed to filter from date
                    })
                    ->when($selectedToDate, function ($query) use ($selectedToDate) {
                        return $query->where('to_date', '<=', $selectedToDate); // Changed to filter to date
                    })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->when($selectedDepotId, function ($query) use ($selectedDepotId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedDepotId) {
                            $q->where('depot_id', $selectedDepotId);
                        });
                    })
                    ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedGroupId) {
                            $q->where('group_id', $selectedGroupId);
                        });
                    })
                    ->get();
            }

            // Filter to get only current year Pending assignments
            $DeclineTraining = $trainings->flatMap(function ($training) use ($selectedDepotId, $selectedGroupId) {

                return $training->trainingDriverAssigns->filter(function ($assignment) use ($selectedDepotId, $selectedGroupId) {

                    $assignmentDate = \Carbon\Carbon::parse($assignment->from_date);

                    if ($assignmentDate->year != \Carbon\Carbon::now()->year) {
                        return false;
                    }

                    if ($assignment->status != 'Decline') {
                        return false;
                    }

                    // Depot filter
                    if ($selectedDepotId && $assignment->driver && $assignment->driver->depot_id != $selectedDepotId) {
                        return false;
                    }

                    // Group filter
                    if ($selectedGroupId && $assignment->driver && $assignment->driver->group_id != $selectedGroupId) {
                        return false;
                    }

                    return true;
                });

            });

            $companies = \App\Models\CompanyDetails::where('company_status', 'Active')->orderBy('name', 'asc')->get();

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

            return view('training.currentyear.current_year_Decline', compact('DeclineTraining', 'companies', 'depots', 'groups'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function exportCompletedTraining(Request $request)
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
        $selectedCompanyId = $request->input('company_id');
        $selectedFromDate = $request->input('from_date');
        $selectedToDate = $request->input('to_date');
        $selectedDepotId = $request->input('depot_id');
        $selectedGroupId = $request->input('group_id');

        // Retrieve all training data for company and PTC manager roles
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
            $trainings = \App\Models\Training::with('trainingDriverAssigns')
                ->whereHas('company', function ($query) {
                    // Check if company has 'Active' status
                    $query->where('company_status', 'Active');
                })
                ->when($selectedFromDate, function ($query) use ($selectedFromDate) {
                    return $query->where('from_date', '>=', $selectedFromDate); // Changed to filter from date
                })
                ->when($selectedToDate, function ($query) use ($selectedToDate) {
                    return $query->where('to_date', '<=', $selectedToDate); // Changed to filter to date
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('companyName', $selectedCompanyId);
                })
                ->when($selectedDepotId, function ($query) use ($selectedDepotId) {
                    $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedDepotId) {
                        $q->where('depot_id', $selectedDepotId);
                    });
                })
                ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                    $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedGroupId) {
                        $q->where('group_id', $selectedGroupId);
                    });
                })->get();
        } else {
            // For other roles, retrieve only trainings related to the user's company
            $trainings = \App\Models\Training::with([
                'trainingDriverAssigns' => function ($q) use ($depotIds, $driverGroupIds) {
                    $q->whereHas('driver', function ($driver) use ($depotIds, $driverGroupIds) {
                        $driver->whereIn('depot_id', $depotIds)
                            ->whereIn('group_id', $driverGroupIds);
                    });
                },
                'trainingDriverAssigns.driver',
            ])
                ->where('companyName', $user->companyname)
                ->whereHas('company', function ($query) {
                    // Check if company has 'Active' status
                    $query->where('company_status', 'Active');
                })
                ->when($selectedFromDate, function ($query) use ($selectedFromDate) {
                    return $query->where('from_date', '>=', $selectedFromDate); // Changed to filter from date
                })
                ->when($selectedToDate, function ($query) use ($selectedToDate) {
                    return $query->where('to_date', '<=', $selectedToDate); // Changed to filter to date
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('companyName', $selectedCompanyId);
                })
                ->when($selectedDepotId, function ($query) use ($selectedDepotId) {
                    $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedDepotId) {
                        $q->where('depot_id', $selectedDepotId);
                    });
                })
                ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                    $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedGroupId) {
                        $q->where('group_id', $selectedGroupId);
                    });
                })->get();
        }

        // Filter to get only current year completed assignments
        $completedTraining = $trainings->flatMap(function ($training) use ($selectedDepotId, $selectedGroupId) {

            return $training->trainingDriverAssigns->filter(function ($assignment) use ($selectedDepotId, $selectedGroupId) {

                $assignmentDate = \Carbon\Carbon::parse($assignment->from_date);

                if ($assignmentDate->year != \Carbon\Carbon::now()->year) {
                    return false;
                }

                if ($assignment->status != 'Complete') {
                    return false;
                }

                if ($selectedDepotId && $assignment->driver && $assignment->driver->depot_id != $selectedDepotId) {
                    return false;
                }

                if ($selectedGroupId && $assignment->driver && $assignment->driver->group_id != $selectedGroupId) {
                    return false;
                }

                return true;
            });

        });

        return \Excel::download(new \App\Exports\CompletedTrainingExport($completedTraining), 'completed_training.xlsx');
    }

    public function exportPendingTraining(Request $request)
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
        $selectedFromDate = $request->input('from_date');
        $selectedToDate = $request->input('to_date');
        $selectedCompanyId = $request->input('company_id'); // Retrieve selected company ID
        $selectedDepotId = $request->input('depot_id');
        $selectedGroupId = $request->input('group_id');

        // Retrieve all training data for company and PTC manager roles
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
            $trainings = \App\Models\Training::with('trainingDriverAssigns')
                ->whereHas('company', function ($query) {
                    // Check if company has 'Active' status
                    $query->where('company_status', 'Active');
                })
                ->when($selectedFromDate, function ($query) use ($selectedFromDate) {
                    return $query->where('from_date', '>=', $selectedFromDate); // Filter from date
                })
                ->when($selectedToDate, function ($query) use ($selectedToDate) {
                    return $query->where('to_date', '<=', $selectedToDate); // Filter to date
                })
                ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('companyName', $selectedCompanyId); // Filter by company
                })
                ->when($selectedDepotId, function ($query) use ($selectedDepotId) {
                    $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedDepotId) {
                        $q->where('depot_id', $selectedDepotId);
                    });
                })
                ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                    $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedGroupId) {
                        $q->where('group_id', $selectedGroupId);
                    });
                })
                ->get();
        } else {
            // For other roles, retrieve only trainings related to the user's company
            $trainings = \App\Models\Training::with([
                'trainingDriverAssigns' => function ($q) use ($depotIds, $driverGroupIds) {
                    $q->whereHas('driver', function ($driver) use ($depotIds, $driverGroupIds) {
                        $driver->whereIn('depot_id', $depotIds)
                            ->whereIn('group_id', $driverGroupIds);
                    });
                },
                'trainingDriverAssigns.driver',
            ])
                ->where('companyName', $user->companyname)
                ->whereHas('company', function ($query) {
                    // Check if company has 'Active' status
                    $query->where('company_status', 'Active');
                })
                ->when($selectedFromDate, function ($query) use ($selectedFromDate) {
                    return $query->where('from_date', '>=', $selectedFromDate); // Filter from date
                })
                ->when($selectedToDate, function ($query) use ($selectedToDate) {
                    return $query->where('to_date', '<=', $selectedToDate); // Filter to date
                })
                ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('companyName', $selectedCompanyId); // Filter by company
                })
                ->when($selectedDepotId, function ($query) use ($selectedDepotId) {
                    $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedDepotId) {
                        $q->where('depot_id', $selectedDepotId);
                    });
                })
                ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                    $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedGroupId) {
                        $q->where('group_id', $selectedGroupId);
                    });
                })
                ->get();
        }

        // Filter to get only current year Pending assignments
        $PendingTraining = $trainings->flatMap(function ($training) use ($selectedDepotId, $selectedGroupId) {

            return $training->trainingDriverAssigns->filter(function ($assignment) use ($selectedDepotId, $selectedGroupId) {

                $assignmentDate = \Carbon\Carbon::parse($assignment->from_date);

                if ($assignmentDate->year != \Carbon\Carbon::now()->year) {
                    return false;
                }

                if ($assignment->status != 'Pending') {
                    return false;
                }

                if ($selectedDepotId && $assignment->driver && $assignment->driver->depot_id != $selectedDepotId) {
                    return false;
                }

                if ($selectedGroupId && $assignment->driver && $assignment->driver->group_id != $selectedGroupId) {
                    return false;
                }

                return true;
            });

        });

        return \Excel::download(new \App\Exports\PendingTrainingExport($PendingTraining), 'pending_training.xlsx');
    }

    public function exportDeclineTraining(Request $request)
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
        $selectedCompanyId = $request->input('company_id');

        $selectedFromDate = $request->input('from_date');
        $selectedToDate = $request->input('to_date');
        $selectedDepotId = $request->input('depot_id');
        $selectedGroupId = $request->input('group_id');

        // Retrieve all training data for company and PTC manager roles
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
            $trainings = \App\Models\Training::with('trainingDriverAssigns')
                ->whereHas('company', function ($query) {
                    // Check if company has 'Active' status
                    $query->where('company_status', 'Active');
                })
                ->when($selectedFromDate, function ($query) use ($selectedFromDate) {
                    return $query->where('from_date', '>=', $selectedFromDate); // Changed to filter from date
                })
                ->when($selectedToDate, function ($query) use ($selectedToDate) {
                    return $query->where('to_date', '<=', $selectedToDate); // Changed to filter to date
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('companyName', $selectedCompanyId);
                })
                ->when($selectedDepotId, function ($query) use ($selectedDepotId) {
                    $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedDepotId) {
                        $q->where('depot_id', $selectedDepotId);
                    });
                })
                ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                    $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedGroupId) {
                        $q->where('group_id', $selectedGroupId);
                    });
                })->get();
        } else {
            // For other roles, retrieve only trainings related to the user's company
            $trainings = \App\Models\Training::with([
                'trainingDriverAssigns' => function ($q) use ($depotIds, $driverGroupIds) {
                    $q->whereHas('driver', function ($driver) use ($depotIds, $driverGroupIds) {
                        $driver->whereIn('depot_id', $depotIds)
                            ->whereIn('group_id', $driverGroupIds);
                    });
                },
                'trainingDriverAssigns.driver',
            ])
                ->where('companyName', $user->companyname)
                ->whereHas('company', function ($query) {
                    // Check if company has 'Active' status
                    $query->where('company_status', 'Active');
                })
                ->when($selectedFromDate, function ($query) use ($selectedFromDate) {
                    return $query->where('from_date', '>=', $selectedFromDate); // Changed to filter from date
                })
                ->when($selectedToDate, function ($query) use ($selectedToDate) {
                    return $query->where('to_date', '<=', $selectedToDate); // Changed to filter to date
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('companyName', $selectedCompanyId);
                })
                ->when($selectedDepotId, function ($query) use ($selectedDepotId) {
                    $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedDepotId) {
                        $q->where('depot_id', $selectedDepotId);
                    });
                })
                ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                    $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedGroupId) {
                        $q->where('group_id', $selectedGroupId);
                    });
                })->get();
        }

        // Filter to get only current year completed assignments
        $DeclineTraining = $trainings->flatMap(function ($training) use ($selectedDepotId, $selectedGroupId) {

            return $training->trainingDriverAssigns->filter(function ($assignment) use ($selectedDepotId, $selectedGroupId) {

                $assignmentDate = \Carbon\Carbon::parse($assignment->from_date);

                if ($assignmentDate->year != \Carbon\Carbon::now()->year) {
                    return false;
                }

                if ($assignment->status != 'Decline') {
                    return false;
                }

                if ($selectedDepotId && $assignment->driver && $assignment->driver->depot_id != $selectedDepotId) {
                    return false;
                }

                if ($selectedGroupId && $assignment->driver && $assignment->driver->group_id != $selectedGroupId) {
                    return false;
                }

                return true;
            });

        });

        return \Excel::download(new \App\Exports\DeclineTrainingExport($DeclineTraining), 'decline_training.xlsx');
    }

    public function create()
    {
        $user = \Auth::user();

        if ($user->can('create trainings')) {

            // Check if the user is a super admin
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                // Fetch all company names
                $contractTypes = \App\Models\CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->pluck('name', 'id');
                $groups = \App\Models\Group::pluck('name', 'id'); // Fetch all groups
                $trainingTypes = TrainingType::pluck('name', 'id');

            } else {
                // Fetch the company name for the logged-in user
                $contractTypes = \App\Models\CompanyDetails::where('created_by', '=', $user->creatorId())
                    ->where('id', '=', $user->companyname)->where('company_status', 'Active')
                    ->pluck('name', 'id');

                // Check if the user creating the new user is directly associated with a company
                if ($user->companyname) {
                    $contractTypes = \App\Models\CompanyDetails::where('id', '=', $user->companyname)->where('company_status', 'Active')
                        ->pluck('name', 'id');
                } else {
                    $contractTypes = [];
                }

                // Fetch groups associated with the user's company
                $groups = \App\Models\Group::pluck('name', 'id');
                $trainingTypes = TrainingType::where('company_id', $user->companyname)->pluck('name', 'id');

            }

            return view('training.create', compact('trainingTypes', 'contractTypes', 'groups'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getGroupsByCompany(Request $request)
    {
        $companyId = $request->input('company_id');
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
                ->pluck('name', 'id');

        } else {

            // 🚫 Prevent accessing other company
            if ($companyId != $user->companyname) {
                return response()->json(['groups' => []]);
            }

            $groups = \App\Models\Group::where('company_id', $companyId)
                ->whereIn('id', $userGroupIds ?? [])
                ->pluck('name', 'id');
        }

        return response()->json(['groups' => $groups]);
    }

    public function getDriversByGroup(Request $request)
    {
        $companyId = $request->input('company_id');
        $groupIds = $request->input('group_id');

        $user = \Auth::user();

        $depotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
        if (! is_array($depotIds)) {
            $depotIds = [$user->depot_id]; // Ensure it remains an array
        }

        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {

            // Fetch drivers based on the selected company and group IDs
            $drivers = \App\Models\Driver::where('companyName', $companyId)->where('driver_status', 'Active')
                ->whereIn('group_id', $groupIds)
                ->pluck('name', 'id');
        } else {
            $drivers = \App\Models\Driver::where('companyName', $companyId)->where('driver_status', 'Active')
                ->whereIn('group_id', $groupIds)->whereIn('depot_id', $depotIds)
                ->pluck('name', 'id');
        }

        return response()->json(['drivers' => $drivers]);
    }

    public function getCoursesByType(Request $request)
    {
        $trainingTypeId = $request->input('training_type_id');

        // Assuming you have a relationship defined in TrainingType model
        $courses = TrainingType::find($trainingTypeId)->TrainingCourse; // Adjust as per your relationship

        return response()->json(['courses' => $courses]);
    }

    protected $notificationService;

    public function __construct(\App\Services\NotificationService $notificationService)
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
                'key' => 3,
            ]);
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create trainings')) {
            $validator = \Validator::make($request->all(), [
                'training_type_id' => 'required|exists:training_types,id',
                'training_course_id' => 'required|exists:training_courses,id',
                'companyName' => 'required|exists:company_details,id',
                'from_date' => 'required|date',
                'to_date' => 'required|date|after_or_equal:from_date',
                'from_time' => 'required',
                'to_time' => 'required',
                'driver_id' => 'required|array',
                'description' => 'required|string',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $driverIds = $request->driver_id;

            // Convert dates from Y-m-d to d/m/Y
            $fromDate = \Carbon\Carbon::createFromFormat('Y-m-d', $request->from_date)->format('Y-m-d');
            $toDate = \Carbon\Carbon::createFromFormat('Y-m-d', $request->to_date)->format('Y-m-d');

            // Check if the training dates are in the future
            if (\Carbon\Carbon::now()->isAfter(\Carbon\Carbon::createFromFormat('Y-m-d', $fromDate))) {
                return redirect()->back()->with('error', __('The training start date must be a future date.'));
            }

            // Retrieve the duration from the TrainingCourse model
            $TrainingCoursefind = \App\Models\TrainingCourse::find($request->training_course_id);
            $maxDurationDays = $TrainingCoursefind->duration; // Assuming 'duration' is the field name
            \Log::warning("maxDurationDays: $maxDurationDays");

            // Calculate the duration in days between from_date and to_date
            $durationInDays = \Carbon\Carbon::createFromFormat('Y-m-d', $toDate)->diffInDays(\Carbon\Carbon::createFromFormat('Y-m-d', $fromDate)) + 1; // +1 to include end date

            // Check if from_date and to_date are equal
            if ($fromDate === $toDate) {
                $durationInDays = 1; // Set duration to 1 if the dates are equal
            }

            \Log::warning("durationInDays: $durationInDays");

            if ((int) $durationInDays !== (int) $maxDurationDays) {
                return redirect()->back()->with('error', __('The training duration must be exactly '.$maxDurationDays.' days.'));
            }

            $nextTrainingDate = \Carbon\Carbon::createFromFormat('Y-m-d', $fromDate)->addYears(5)->format('Y-m-d');

            $skippedRecords = [];
            $assignedDrivers = []; // To keep track of assigned drivers

            // Check if any driver is already assigned to another training on the same date
            foreach ($request->driver_id as $driverId) {
                // Check for existing training assignments
                // $existingTraining = \App\Models\TrainingDriverAssign::where('driver_id', $driverId)
                //     ->whereHas('training', function ($query) use ($fromDate, $toDate) {
                //         $query->where(function ($q) use ($fromDate, $toDate) {
                //             $q->where(function ($q1) use ($fromDate, $toDate) {
                //                 // New range starts before existing and ends after it starts (overlap)
                //                 $q1->where('from_date', '<=', $toDate)
                //                   ->where('to_date', '>=', $fromDate);
                //             });
                //         });
                //     })
                //     ->exists();

                // if ($existingTraining) {
                //     // Fetch the driver's name
                //     $driverName = \App\Models\Driver::find($driverId)->name;
                //     $skippedRecords[] = [
                //         'error' => "Driver {$driverName} is already assigned to a training during this date range.",
                //     ];
                //     continue; // Skip to the next driver
                // }

                // Check if the driver is already assigned to the same training type and course
                $latestTraining = \App\Models\TrainingDriverAssign::where('driver_id', $driverId)
                    ->whereHas('training', function ($query) use ($request) {
                        $query->where('training_type_id', $request->training_type_id)
                            ->where('training_course_id', $request->training_course_id);
                    })
                    ->orderBy('to_date', 'desc') // Get the latest training assignment
                    ->first();

                // If there is an existing training, check the next training date
                if ($latestTraining) {
                    $latestNextTrainingDate = $latestTraining->training->next_training_date;

                    // Compare with formatted from_date
                    if ($latestNextTrainingDate > $fromDate) {
                        $driverName = \App\Models\Driver::find($driverId)->name;
                        $skippedRecords[] = [
                            'error' => "Driver {$driverName} already assigned to the same training course before the next training date.",
                        ];

                        continue; // Skip to the next driver
                    }
                }

                // If no conflicts, assign the driver and store in the array
                $assignedDrivers[] = $driverId;
            }

            // Store the data if any drivers are assigned
            if (! empty($assignedDrivers)) {
                $training = new \App\Models\Training(); // Assuming you have a Training model
                $training->training_type_id = $request->training_type_id;
                $training->training_course_id = $request->training_course_id;
                $training->description = $request->description;
                $training->companyName = $request->companyName;
                $training->from_date = $fromDate; // Save in dd/mm/yyyy format
                $training->to_date = $toDate; // Save in dd/mm/yyyy format
                $training->from_time = $request->from_time;
                $training->to_time = $request->to_time;
                $training->next_training_date = $nextTrainingDate;
                $training->status = 'Fresh';
                $training->created_by = \Auth::user()->id;
                $training->save();

                // Assign drivers
                foreach ($assignedDrivers as $driverId) {
                    \App\Models\TrainingDriverAssign::create([
                        'training_id' => $training->id,
                        'driver_id' => $driverId,
                        'status' => 'Pending',
                        'from_date' => $fromDate,
                        'to_date' => $toDate,
                    ]);

                    // Fetch the driver and check if the email exists
                    $driver = \App\Models\Driver::find($driverId);

                    if (! empty($driver->contact_email)) {

                        $startDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $fromDate.' '.$request->from_time)->format('Ymd\THis\Z');
                        $endDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $toDate.' '.$request->to_time)->format('Ymd\THis\Z');
                        // Create the ICS file content
                        $icsContent = "BEGIN:VCALENDAR\r\n";
                        $icsContent .= "VERSION:2.0\r\n";
                        $icsContent .= "CALSCALE:GREGORIAN\r\n";
                        $icsContent .= "BEGIN:VEVENT\r\n";
                        $icsContent .= 'UID:'.uniqid()."@yourdomain.com\r\n"; // Unique ID
                        $icsContent .= 'DTSTAMP:'.now()->format('Ymd\THis\Z')."\r\n"; // Creation timestamp
                        $icsContent .= 'DTSTART:'.$startDateTime."\r\n"; // Start date and time
                        $icsContent .= 'DTEND:'.$endDateTime."\r\n"; // End date and time
                        $icsContent .= 'SUMMARY:Training Session - '.$training->trainingCourse->name."\r\n"; // Training course summary
                        $icsContent .= 'DESCRIPTION:Training session for driver '.$driver->name."\r\n"; // Description
                        $icsContent .= "END:VEVENT\r\n";
                        $icsContent .= "END:VCALENDAR\r\n";

                        // Save the ICS file temporarily
                        $icsDirectory = storage_path('app/public/trainings/');

                        // Check if the directory exists, if not, create it
                        if (! \File::exists($icsDirectory)) {
                            \File::makeDirectory($icsDirectory, 0755, true);
                        }

                        $icsFilePath = $icsDirectory.'training_'.$training->id.'_'.$driverId.'.ics';
                        \File::put($icsFilePath, $icsContent);

                        // Send email with the ICS attachment
                        \Mail::to($driver->contact_email)->send(new \App\Mail\TrainingAssigned($driver, $training, $fromDate, $toDate, $icsFilePath));
                    } else {
                        // Optionally, log or handle the case where no email is found
                        \Log::warning("Driver {$driver->name} (ID: {$driverId}) does not have an email address.");

                    }
                }
            }

            $trainingType = \App\Models\TrainingType::find($request->training_type_id)->name;
            $trainingCourse = \App\Models\TrainingCourse::find($request->training_course_id)->name;

            $this->sendNotification(
                'Training Assigned',
                "The  training type ({$trainingType}) and course ({$trainingCourse}) has been assigned.",
                $driverIds
            );

            // Store skipped records in session
            if (! empty($skippedRecords)) {
                session(['errorArray' => $skippedRecords]);
            }

            return redirect()->route('training.index')->with('success', __('Training successfully assigned.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getEvents()
    {
        $events = Training::all()->map(function ($training) {
            return [
                'id' => $training->id,
                'title' => $training->title,
                'from_date' => $training->from_date,
                'to_date' => $training->to_date,
            ];
        });

        return response()->json($events);
    }

    public function getTrainingCourses(Request $request)
    {
        $trainingTypeId = $request->input('training_type_id');

        $courses = \App\Models\TrainingCourse::where('trainingtype_id', $trainingTypeId)->get();

        return response()->json($courses);
    }

    public function traininghistorytindex(Request $request)
    {
        if (\Auth::user()->can('manage view training')) {
            $user = \Auth::user();
            $depotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
            if (! is_array($depotIds)) {
                $depotIds = [$user->depot_id];
            }

            $driverGroupIds = json_decode($user->driver_group_id, true);
            if (! is_array($driverGroupIds)) {
                $driverGroupIds = [$user->driver_group_id];
            }
            $selectedCompanyId = $request->input('company_id');
            $selectedTrainingTypeId = $request->input('training_type_id');
            $selectedTrainingCourseId = $request->input('training_course_id'); // Get the selected training course
            $status = $request->input('status');
            $selectedDepotId = $request->input('depot_id');
            $selectedGroupId = $request->input('group_id');

            // Fetch the trainings based on the selected filters
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                $trainings = \App\Models\Training::with([
                    'trainingCourse',
                    'trainingType',
                    'company',
                    'creator',
                    'trainingDriverAssigns' => function ($q) use ($selectedDepotId, $selectedGroupId, $status) {

                        if ($status) {
                            $q->where('status', $status);
                        }

                        $q->whereHas('driver', function ($driver) use ($selectedDepotId, $selectedGroupId) {

                            if ($selectedDepotId) {
                                $driver->where('depot_id', $selectedDepotId);
                            }

                            if ($selectedGroupId) {
                                $driver->where('group_id', $selectedGroupId);
                            }

                        });

                    },
                    'trainingDriverAssigns.driver',
                ])
                    ->whereHas('company', function ($query) {
                        // Check if company has 'Active' status
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })
                    ->when($selectedTrainingTypeId, function ($query) use ($selectedTrainingTypeId) {
                        return $query->where('training_type_id', $selectedTrainingTypeId);
                    })
                    ->when($selectedTrainingCourseId, function ($query) use ($selectedTrainingCourseId) {
                        return $query->where('training_course_id', $selectedTrainingCourseId);
                    })
                    ->when($status, function ($query) use ($status) {
                        return $query->whereHas('trainingDriverAssigns', function ($q) use ($status) {
                            $q->where('status', $status);
                        });
                    })
                    ->when($selectedDepotId, function ($query) use ($selectedDepotId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedDepotId) {
                            $q->where('depot_id', $selectedDepotId);
                        });
                    })
                    ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedGroupId) {
                            $q->where('group_id', $selectedGroupId);
                        });
                    })
                    ->orderBy('id', 'desc')->get();
                $trainingTypes = \App\Models\TrainingType::all();
            } else {
                $trainings = \App\Models\Training::with([
                    'trainingCourse',
                    'trainingType',
                    'company',
                    'creator',
                    'trainingDriverAssigns' => function ($q) use ($depotIds, $driverGroupIds) {
                        $q->whereHas('driver', function ($driver) use ($depotIds, $driverGroupIds) {
                            $driver->whereIn('depot_id', $depotIds)
                                ->whereIn('group_id', $driverGroupIds);
                        });
                    },
                    'trainingDriverAssigns.driver',
                ])
                    ->where('companyName', $user->companyname)
                    ->whereHas('company', function ($query) {
                        // Check if company has 'Active' status
                        $query->where('company_status', 'Active');
                    })

                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })
                    ->when($selectedTrainingTypeId, function ($query) use ($selectedTrainingTypeId) {
                        return $query->where('training_type_id', $selectedTrainingTypeId);
                    })
                    ->when($selectedTrainingCourseId, function ($query) use ($selectedTrainingCourseId) {
                        return $query->where('training_course_id', $selectedTrainingCourseId); // Filter by training course
                    })->when($status, function ($query) use ($status) {
                        return $query->whereHas('trainingDriverAssigns', function ($q) use ($status) {
                            $q->where('status', $status);
                        });
                    })->when($selectedDepotId, function ($query) use ($selectedDepotId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedDepotId) {
                            $q->where('depot_id', $selectedDepotId);
                        });
                    })
                    ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                        $query->whereHas('trainingDriverAssigns.driver', function ($q) use ($selectedGroupId) {
                            $q->where('group_id', $selectedGroupId);
                        });
                    })
                    ->get();
                $trainingTypes = \App\Models\TrainingType::whereHas('types', function ($query) use ($user) {
                    $query->where('company_id', $user->companyname); // Filter by logged-in user's company
                })->orderBy('id', 'desc')->get();
            }

            $companies = \App\Models\CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->get();
            $trainingTypes = \App\Models\TrainingType::all();
            // Fetch training courses based on selected training type
            $trainingCourses = [];
            if ($selectedTrainingTypeId) {
                $trainingCourses = \App\Models\TrainingCourse::where('trainingtype_id', $selectedTrainingTypeId)->get();
            }

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

            return view('training.history.index', compact('trainings', 'companies', 'trainingTypes', 'trainingCourses', 'selectedTrainingCourseId', 'groups', 'depots'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function traininghistorytedit(Training $training)
    {
        if (\Auth::user()->can('manage view training')) {
            return view('training.history.edit', compact('training'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function traininghistorytstore(Request $request)
    {
        if (\Auth::user()->can('manage view training')) {
            $validator = \Validator::make($request->all(), [
                'training_type_id' => 'required',
                'training_course_id' => 'required',
                'from_date' => 'required|date',
                'to_date' => 'required|date|after_or_equal:from_date',
                'from_time' => 'required',
                'to_time' => 'required',
                'description' => 'required',
                'driver_id' => 'required|exists:drivers,id', // Required single driver ID
                'companyName' => 'nullable|exists:company_details,id',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // Convert dates from Y-m-d to d/m/Y
            $fromDate = \Carbon\Carbon::createFromFormat('Y-m-d', $request->from_date);
            $toDate = \Carbon\Carbon::createFromFormat('Y-m-d', $request->to_date);
            $nextTrainingDate = $fromDate->copy()->addYears(5)->format('Y-m-d');
            try {
                $fromDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $fromDate->format('Y-m-d').' '.$request->from_time);
                $toDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $toDate->format('Y-m-d').' '.$request->to_time);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Invalid time format.');
            }

            // Validate DateTime instances
            if (! ($fromDateTime instanceof \Carbon\Carbon) || ! ($toDateTime instanceof \Carbon\Carbon)) {
                return redirect()->back()->with('error', 'Invalid time format.');
            }

            // // Check that from_date_time is before or equal to to_date_time
            // if ($fromDateTime->gt($toDateTime)) {
            //     return redirect()->back()->with('error', 'From time must be before or equal to To time.');
            // }

            // Check if the driver already has a training with the same date range
            $existingTraining = \App\Models\TrainingDriverAssign::where('driver_id', $request->driver_id)
                ->where('from_date', $fromDate->format('Y-m-d'))
                ->where('to_date', $toDate->format('Y-m-d'))
                ->exists();

            if ($existingTraining) {
                // Fetch the driver's name
                $driver = \App\Models\Driver::find($request->driver_id);
                $driverName = $driver ? $driver->name : 'Unknown Driver'; // Default to 'Unknown Driver' if not found

                return redirect()->back()->with('error', __('Driver :name is already assigned to a training during this date range.', ['name' => $driverName]));
            }

            $TrainingCoursefind = \App\Models\TrainingCourse::find($request->training_course_id);
            $maxDurationDays = $TrainingCoursefind->duration; // Assuming 'duration' is the field name
            // \Log::warning("maxDurationDays: $maxDurationDays");

            // Calculate the duration in days between from_date and to_date
            $durationInDays = $toDate->diffInDays($fromDate) + 1; // +1 to include end date

            // Check if from_date and to_date are equal
            if ($fromDate === $toDate) {
                $durationInDays = 1; // Set duration to 1 if the dates are equal
            }

            // \Log::warning("durationInDays: $durationInDays");

            if ((int) $durationInDays !== (int) $maxDurationDays) {
                return redirect()->back()->with('error', __('The training duration must be exactly '.$maxDurationDays.' days.'));
            }

            // Get the driver ID directly
            $driverId = $request->driver_id;

            // Check if the driver is already assigned to another training on the same date
            $existingTraining = \App\Models\TrainingDriverAssign::where('driver_id', $driverId)
                ->whereHas('training', function ($query) use ($fromDate, $toDate) {
                    $query->where(function ($q) use ($fromDate, $toDate) {
                        $q->whereBetween('from_date', [$fromDate, $toDate])
                            ->orWhereBetween('to_date', [$fromDate, $toDate])
                            ->orWhere(function ($q2) use ($fromDate, $toDate) {
                                $q2->where('from_date', '<=', $fromDate)
                                    ->where('to_date', '>=', $toDate);
                            });
                    });
                })
                ->exists();

            if ($existingTraining) {
                // Fetch the driver's name
                $driverName = \App\Models\Driver::find($driverId)->name; // Assuming you have a Driver model with a 'name' field

                return redirect()->back()->with('error', __('Driver :name is already assigned to a training during this date range.', ['name' => $driverName]));
            }

            // Create new training record
            $training = Training::create([
                'training_type_id' => $request->training_type_id,
                'training_course_id' => $request->training_course_id,
                'description' => $request->description,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'from_time' => $request->from_time,
                'to_time' => $request->to_time,
                'next_training_date' => $nextTrainingDate,
                'companyName' => $request->companyName,
                'status' => 'Reassign',
                'created_by' => \Auth::user()->id,
            ]);

            // Create training assignment for the single driver
            \App\Models\TrainingDriverAssign::create([
                'training_id' => $training->id,
                'driver_id' => $driverId,
                'status' => 'Pending',
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ]);

            // Fetch the driver and check if the email exists
            $driver = \App\Models\Driver::find($driverId);

            if (! empty($driver->contact_email)) {

                $startDateTime = $fromDateTime->format('Ymd\THis\Z');
                $endDateTime = $toDateTime->format('Ymd\THis\Z');
                // Create the ICS file content
                $icsContent = "BEGIN:VCALENDAR\r\n";
                $icsContent .= "VERSION:2.0\r\n";
                $icsContent .= "CALSCALE:GREGORIAN\r\n";
                $icsContent .= "BEGIN:VEVENT\r\n";
                $icsContent .= 'UID:'.uniqid()."@yourdomain.com\r\n"; // Unique ID
                $icsContent .= 'DTSTAMP:'.now()->format('Ymd\THis\Z')."\r\n"; // Creation timestamp
                $icsContent .= 'DTSTART:'.$startDateTime."\r\n"; // Start date and time
                $icsContent .= 'DTEND:'.$endDateTime."\r\n"; // End date and time
                $icsContent .= 'SUMMARY:Training Session - '.$training->trainingCourse->name."\r\n"; // Training course summary
                $icsContent .= 'DESCRIPTION:Training session for driver '.$driver->name."\r\n"; // Description
                $icsContent .= "END:VEVENT\r\n";
                $icsContent .= "END:VCALENDAR\r\n";

                // Save the ICS file temporarily
                $icsDirectory = storage_path('app/public/trainings/');

                // Check if the directory exists, if not, create it
                if (! \File::exists($icsDirectory)) {
                    \File::makeDirectory($icsDirectory, 0755, true);
                }

                $icsFilePath = $icsDirectory.'training_'.$training->id.'_'.$driverId.'.ics';
                \File::put($icsFilePath, $icsContent);

                // Send email with the ICS attachment
                \Mail::to($driver->contact_email)->send(new \App\Mail\TrainingAssigned($driver, $training, $fromDate, $toDate, $icsFilePath));
            } else {
                // Optionally, log or handle the case where no email is found
                \Log::warning("Driver {$driver->name} (ID: {$driverId}) does not have an email address.");
            }

            $trainingType = \App\Models\TrainingType::find($request->training_type_id)->name;
            $trainingCourse = \App\Models\TrainingCourse::find($request->training_course_id)->name;

            $this->sendNotification(
                'Training Reassigned',
                "The  training type ({$trainingType}) and course ({$trainingCourse}) has been assigned.",
                [$driverId] // Assuming you want to notify the specific driver
            );

            return redirect()->back()->with('success', __('Training Reassign successfully'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function updateStatus(Request $request)
    {
        $training = Training::find($request->id);
        $training->performance = $request->performance;
        $training->status = $request->status;
        $training->remarks = $request->remarks;
        $training->save();

        return redirect()->route('training.index')->with('success', __('Training status successfully updated.'));
    }
}
