<?php

namespace App\Http\Controllers;

use App\Models\CompanyDetails;
use App\Models\Fleet;
use App\Models\FleetPlannerReminder;
use App\Models\vehicleDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    // public function index(Request $request)
    // {
    //     if (\Auth::user()->can('manage depot')) {
    //         $user = \Auth::user();

    //         if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
    //             $fleet = \App\Models\Fleet::all();
    //         } else {
    //             $fleet = \App\Models\Fleet::where('company_id', $user->companyname)->get();
    //         }

    //         // Prepare events for the calendar using next_reminder_date from FleetPlannerReminder model
    //         $events = $fleet->flatMap(function ($item) {
    //             // Get all reminders for the current fleet
    //             $reminders = $item->reminders;

    //             // Map each reminder to an event
    //             return $reminders->map(function ($reminder) use ($item) {

    //                 $vehicleDetails = $item->vehicle; // Assuming relation `vehicleDetails` exists in Fleet model

    //                 // Extract registration number if available
    //                 $registrationNumber = $vehicleDetails ? $vehicleDetails->registrationNumber : 'N/A';
    //                 // Set the event title
    //                 $eventTitle = $item->planner_type; // Default planner type as title

    //                 // Set the color based on the title
    //                 if ($eventTitle === 'Tacho Calibration') {
    //                     $eventColor = 'green'; // Green color for Tacho Calibration
    //                 } elseif ($eventTitle === 'DVS/PSS Permit Expiry') {
    //                     $eventColor = 'blue'; // Blue color for DVS/PSS Permit Expiry
    //                 } elseif ($eventTitle === 'Insurance') {
    // $eventColor = '#c0c102'; // Yellow color for Insurance
    //                 }elseif ($eventTitle === 'Brake Test Due') {
    // $eventColor = '#aa38c1'; // Yellow color for Insurance
    //                 }
    //                 else {
    //                     $eventColor = '#3788d8'; // Default color for other events
    //                 }

    //                 $redirectUrl = route('fleet.show', ['fleet' => $item->id]); // Ensure the fleet ID is passed

    //                 return [
    //                     'title' => $eventTitle,
    //                     'start' => $reminder->next_reminder_date ? \Carbon\Carbon::parse($reminder->next_reminder_date)->format('Y-m-d') : null,
    //                     'color' => $eventColor,
    //                     'status' => $reminder->status,
    //                     'redirectUrl' => $redirectUrl,
    //                     'id' => $reminder->id,
    //                     'comment' => $reminder->comment,
    //                     'odometer_reading' => $reminder->odometer_reading,
    //                     'parts_cost' => $reminder->parts_cost,  // Add parts cost
    //                 'labour_cost' => $reminder->labour_cost,  // Add labour cost
    //                 'tyre_cost' => $reminder->tyre_cost,  // Add tyre cost
    //                 'total_cost' => $reminder->total_cost,
    //                 'registration_number' => $registrationNumber,
    //                 ];
    //             });
    //         });

    //         return view('fleet.index', compact('fleet', 'events'));
    //     } else {
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    public function index(Request $request)
    {
        if (\Auth::user()->can('manage planner')) {
            $user = \Auth::user();

            // Retrieve filter inputs
            $selectedCompanyId = $request->input('company_id');
            $selectedVehicleId = $request->input('vehicle_id');
            $selectedPlannerType = $request->input('planner_type'); // New planner type filter
            $selectedDepotIds = (array) $request->input('depot_id');
            $selectedVehicleGroupId = $request->input('group_id');

            $depotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
            if (! is_array($depotIds)) {
                $depotIds = [$user->depot_id]; // Ensure it remains an array
            }

            $vehicleGroupIds = is_array($user->vehicle_group_id)
    ? $user->vehicle_group_id
    : json_decode($user->vehicle_group_id, true);

if (! is_array($vehicleGroupIds)) {
    $vehicleGroupIds = [$user->vehicle_group_id];
}


            // Retrieve the fleet data based on roles and filters
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                // Retrieve fleets for company or PTC manager, only if the company's status is "Active"
                $fleet = \App\Models\Fleet::with(['reminders', 'company', 'vehicle'])->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('company_id', $selectedCompanyId);
                })->when($selectedVehicleId, function ($query) use ($selectedVehicleId) {
                    return $query->where('vehicle_id', $selectedVehicleId);
                })
                    ->when(! empty($selectedDepotIds), function ($query) use ($selectedDepotIds) {
                        $query->whereHas('vehicle', function ($q) use ($selectedDepotIds) {
                            $q->whereIn('depot_id', $selectedDepotIds);
                        });
                    })
                    ->when($selectedVehicleGroupId, function ($query) use ($selectedVehicleGroupId) {
                        $query->whereHas('vehicle', function ($q) use ($selectedVehicleGroupId) {
                            $q->where('group_id', $selectedVehicleGroupId);
                        });
                    })
                    ->when($selectedPlannerType, function ($query) use ($selectedPlannerType) {
                    if ($selectedPlannerType === 'Other') {
                        // List of known types
                        $knownTypes = [
                            'Tacho Calibration', 'DVS/PSS Permit Expiry', 'PMI Due', 'Brake Test Due',
                            'Insurance', 'Road Tax', 'MOT', 'Fridge Service', 'Fridge Calibration',
                            'Tail lift', 'Loler',
                        ];

                        return $query->whereNotIn('planner_type', $knownTypes);
                    } else {
                        return $query->where('planner_type', $selectedPlannerType);
                    }
                })

                // Add check for company status as "Active"
                    ->whereHas('company', function ($query) {
                        $query->where('company_status', 'Active'); // Ensure only companies with "Active" status are included
                    })
                    ->get();

                // Retrieve vehicles belonging to the selected company, only if the company's status is "Active"
                $vehicles = \App\Models\vehicleDetails::where('companyName', $selectedCompanyId)
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active'); // Ensure only vehicles from active companies are included
                    })
                    ->get();
            } else {
                // Retrieve fleets for other roles, filtered by the user's company, only if the company's status is "Active"
                $fleet = \App\Models\Fleet::with(['reminders', 'company', 'vehicle'])->where('company_id', $user->companyname)
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->whereHas('vehicle', function ($query) use ($depotIds, $vehicleGroupIds, $selectedDepotIds, $selectedVehicleGroupId) {

        $query->whereIn('depot_id', $depotIds)
                            ->whereIn('group_id', $vehicleGroupIds)
                            ->when(! empty($selectedDepotIds), function ($q) use ($selectedDepotIds) {
                                $q->whereIn('depot_id', $selectedDepotIds);
                            })
                            ->when($selectedVehicleGroupId, function ($q) use ($selectedVehicleGroupId) {
                                $q->where('group_id', $selectedVehicleGroupId);
                            });

                    })
                    ->when($selectedVehicleId, function ($query) use ($selectedVehicleId) {
                        return $query->where('vehicle_id', $selectedVehicleId);
                    })
                    ->when($selectedPlannerType, function ($query) use ($selectedPlannerType) {
                        if ($selectedPlannerType === 'Other') {
                            // List of known types
                            $knownTypes = [
                                'Tacho Calibration', 'DVS/PSS Permit Expiry', 'PMI Due', 'Brake Test Due',
                                'Insurance', 'Road Tax', 'MOT', 'Fridge Service', 'Fridge Calibration',
                                'Tail lift', 'Loler',
                            ];

                            return $query->whereNotIn('planner_type', $knownTypes);
                        } else {
                            return $query->where('planner_type', $selectedPlannerType);
                        }
                    })

                    // Add check for company status as "Active"
                    ->whereHas('company', function ($query) {
                        $query->where('company_status', 'Active'); // Ensure only companies with "Active" status are included
                    })
                    ->get();

                // Retrieve vehicles for the logged-in user's company, only if the company's status is "Active"
                $vehicles = \App\Models\vehicleDetails::where('companyName', $user->companyname)->whereIn('depot_id', $depotIds)->whereIn('group_id', $vehicleGroupIds)
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active'); // Ensure only vehicles from active companies are included
                    })
                    ->get();
            }

            // Prepare events for the calendar
            $events = $fleet->flatMap(function ($item) {
                // Get all reminders for the current fleet
                $reminders = $item->reminders;

                // Map each reminder to an event
                return $reminders->map(function ($reminder) use ($item) {

                    $vehicleDetails = $item->vehicle; // Assuming relation `vehicleDetails` exists in Fleet model
                    $companyName = $item->company ? $item->company->name : 'Unknown Company';

                    // Extract registration number if available
                    $registrationNumber = $vehicleDetails ? $vehicleDetails->registrationNumber : 'N/A';
                    // Set the event title
                    $eventTitle = $item->planner_type; // Default planner type as title

                    // // Set the color based on the title
                    // if ($eventTitle === 'Tacho Calibration') {
                    //     $eventColor = '#b9788a'; // Green color for Tacho Calibration
                    // } elseif ($eventTitle === 'DVS/PSS Permit Expiry') {
                    //     $eventColor = '#9d9de5'; // Blue color for DVS/PSS Permit Expiry
                    // } elseif ($eventTitle === 'Insurance') {
                    //     $eventColor = '#c0c102'; // Yellow color for Insurance
                    // } elseif ($eventTitle === 'Brake Test Due') {
                    //     $eventColor = '#c464d7'; // Purple color for Brake Test Due
                    // } elseif ($eventTitle === 'Road Tax') {
                    //     $eventColor = '#d59436'; // Orange color for Road Tax
                    // } elseif ($eventTitle === 'MOT') {
                    //     $eventColor = '#6c757d'; // Grey color for MOT
                    // } elseif ($eventTitle === 'Fridge Service') {
                    //     $eventColor = '#008080'; // Grey color for MOT
                    // } elseif ($eventTitle === 'Fridge Calibration') {
                    //     $eventColor = '#506F5A'; // Grey color for MOT
                    // } elseif ($eventTitle === 'Tail lift') {
                    //     $eventColor = '#A4C5B7'; // Grey color for MOT
                    // } elseif ($eventTitle === 'Loler') {
                    //     $eventColor = '#ec7063'; // Grey color for MOT
                    // } else {
                    //     $eventColor = '#3788d8'; // Default color for other events
                    // }

                    switch ($eventTitle) {
                        case 'Tacho Calibration':
                            $eventColor = $reminder->status === 'Completed' ? '#cbb9be' : '#b9788a';
                            break;
                        case 'DVS/PSS Permit Expiry':
                            $eventColor = $reminder->status === 'Completed' ? '#b6b6d9' : '#9d9de5';
                            break;
                        case 'PMI Due':
                            $eventColor = $reminder->status === 'Completed' ? '#78cfbe' : '#05b794';
                            break;
                        case 'Insurance':
                            $eventColor = $reminder->status === 'Completed' ? '#cfcf79' : '#c0c102';
                            break;
                        case 'Brake Test Due':
                            $eventColor = $reminder->status === 'Completed' ? '#cc8dd9' : '#c464d7';
                            break;
                        case 'Road Tax':
                            $eventColor = $reminder->status === 'Completed' ? '#c5a678' : '#d59436';
                            break;
                        case 'MOT':
                            $eventColor = $reminder->status === 'Completed' ? '#898e93' : '#6c757d';
                            break;
                        case 'Fridge Service':
                            $eventColor = $reminder->status === 'Completed' ? '#509595' : '#008080';
                            break;
                        case 'Fridge Calibration':
                            $eventColor = $reminder->status === 'Completed' ? '#8fa396' : '#506F5A';
                            break;
                        case 'Tail lift':
                            $eventColor = $reminder->status === 'Completed' ? '#c5e1d5' : '#A4C5B7';
                            break;
                        case 'Loler':
                            $eventColor = $reminder->status === 'Completed' ? '#e5afaa' : '#ec7063';
                            break;
                        default:
                            $eventColor = $reminder->status === 'Completed' ? '#7eaddb' : '#3788d8';
                            break;
                    }

                    $redirectUrl = route('fleet.show', ['reminder' => $reminder->id]);
                    $reminderredirectUrl = route('reminder.show', ['reminderId' => $reminder->id]); // Add the reminderredirectUrl
                    $historyUrl = route('planner.history.show', ['id' => $reminder->id]);

                    $statusIcon = null;
                    if ($reminder->status === 'Completed') { // Change this condition as needed
                        $statusIcon = '<i class="fas fa-check-square" style="color: #ffffff;font-size: 15px;"></i>'; // Green dot icon for completed reminders
                    }

                    return [
                        'title' => $eventTitle,
                        'start' => $reminder->next_reminder_date ? \Carbon\Carbon::parse($reminder->next_reminder_date)->format('Y-m-d') : null,
                        'color' => $eventColor,
                        'status' => $reminder->status,
                        'status_icon' => $statusIcon,
                        'redirectUrl' => $redirectUrl,
                        'reminderredirectUrl' => $reminderredirectUrl,
                        'historyUrl' => $historyUrl,
                        'id' => $reminder->id,
                        'comment' => $reminder->comment,
                        'odometer_reading' => $reminder->odometer_reading,
                        'parts_cost' => $reminder->parts_cost,  // Add parts cost
                        'labour_cost' => $reminder->labour_cost,  // Add labour cost
                        'tyre_cost' => $reminder->tyre_cost,  // Add tyre cost
                        'total_cost' => $reminder->total_cost,
                        'registration_number' => $registrationNumber,
                        'company_name' => $companyName,
                    ];
                });
            });

            // Retrieve all companies for the dropdown filter, only if the company status is "Active"
            $companies = \App\Models\CompanyDetails::where('company_status', 'Active')->orderBy('name', 'asc')->get();

            $depotsQuery = \App\Models\Depot::orderBy('name', 'asc');
            $groupsQuery = \App\Models\VehicleGroup::orderBy('name', 'asc');

            if (! $user->hasRole('company') && ! $user->hasRole('PTC manager')) {

                $depotsQuery->whereIn('id', $depotIds);
                $groupsQuery->whereIn('id', $vehicleGroupIds);
            }

            $depots = $depotsQuery->get();
            $groups = $groupsQuery->get();

            // Define planner types for the dropdown
            $plannerTypes = [
                '' => 'All Planner Types',
                'Tacho Calibration' => 'Tacho Calibration',
                'DVS/PSS Permit Expiry' => 'DVS/PSS Permit Expiry',
                'PMI Due' => 'PMI Due',
                'Brake Test Due' => 'Brake Test Due',
                'Insurance' => 'Insurance',
                'Road Tax' => 'Road Tax',
                'MOT' => 'MOT',
                'Other' => 'Other',

            ];

            return view('fleet.index', compact('fleet', 'events', 'companies', 'vehicles', 'plannerTypes', 'depots',
                'groups'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function updateStatus(Request $request, $reminderId)
    {
        try {
            // Find the FleetPlannerReminder by ID
            $reminder = FleetPlannerReminder::findOrFail($reminderId);

            // Fetch the last tyre depth record for the vehicle
            $lastTyreDepth = \App\Models\FleetTyreDepth::where('vehicle_id', $reminder->fleet->vehicle_id)->latest()->first();

            // Validate the tyre depth values against lastTyreDepth
            $validated = $request->validate([
                'ns_depth_1' => 'nullable|numeric',
                'ns_depth_2' => 'nullable|numeric',
                'ns_depth_3' => 'nullable|numeric',
                'ns_depth_4' => 'nullable|numeric',
                'ns_depth_5' => 'nullable|numeric',
                'ns_depth_6' => 'nullable|numeric',
                'os_depth_1' => 'nullable|numeric',
                'os_depth_2' => 'nullable|numeric',
                'os_depth_3' => 'nullable|numeric',
                'os_depth_4' => 'nullable|numeric',
                'os_depth_5' => 'nullable|numeric',
                'os_depth_6' => 'nullable|numeric',
                'files' => 'nullable|array',
                'files.*' => 'file|max:5000',
                'comment' => 'nullable|string',
                'parts' => 'nullable|numeric',
                'labour' => 'nullable|numeric',
                'tyre_cost' => 'nullable|numeric',
                'total_cost' => 'nullable|numeric',
                'type_of_service' => 'nullable|string',
                'service_test_value' => 'nullable|numeric',
                'secondary_1_test_value' => 'nullable|numeric',
                'secondary_2_test_value' => 'nullable|numeric',
                'parking_test_value' => 'nullable|numeric',
                'confirmation_comment' => 'nullable|string',
                'tyre_depth_comment' => 'nullable|string',
                'odometer_reading' => 'nullable|string',
            ]);

            // Update the status of the FleetPlannerReminder
            $reminder->parts_cost = $request->parts;
            $reminder->labour_cost = $request->labour;
            $reminder->tyre_cost = $request->tyre_cost;
            $reminder->total_cost = $request->total_cost;
            $reminder->type_of_service = $request->type_of_service;
            $reminder->service_test_value = $request->service_test_value;
            $reminder->secondary_1_test_value = $request->secondary_1_test_value;
            $reminder->secondary_2_test_value = $request->secondary_2_test_value;
            $reminder->parking_test_value = $request->parking_test_value;
            $reminder->confirmation_comment = $request->confirmation_comment;
            $reminder->tyre_depth_comment = $request->tyre_depth_comment;
            $reminder->odometer_reading = $request->odometer_reading;
            $reminder->updated_by = \Auth::user()->id;
            $reminder->status = 'Completed';
            $reminder->save();

            // Save Tyre Depth data
            $tyreDepth = new \App\Models\FleetTyreDepth([
                'ns_depth_1' => $request->ns_depth_1,
                'ns_depth_2' => $request->ns_depth_2,
                'ns_depth_3' => $request->ns_depth_3,
                'ns_depth_4' => $request->ns_depth_4,
                'ns_depth_5' => $request->ns_depth_5,
                'ns_depth_6' => $request->ns_depth_6,
                'os_depth_1' => $request->os_depth_1,
                'os_depth_2' => $request->os_depth_2,
                'os_depth_3' => $request->os_depth_3,
                'os_depth_4' => $request->os_depth_4,
                'os_depth_5' => $request->os_depth_5,
                'os_depth_6' => $request->os_depth_6,
                'vehicle_id' => $reminder->fleet->vehicle_id,
            ]);
            $reminder->tyreDepth()->save($tyreDepth);

            // Save File Uploads
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $path = $file->storeAs('fleet/planner', $originalName, 'local');
                    \App\Models\FleetFileUpload::create([
                        'fleet_planner_reminder_id' => $reminder->id,
                        'file_path' => $path,
                    ]);
                }
            }

            // Check if the FleetPlannerReminder has a fleet_planner_id
            if ($reminder->fleet_planner_id) {
                // Fetch the related Fleet model using fleet_planner_id
                $fleet = \App\Models\Fleet::where('id', $reminder->fleet_planner_id)->first();

                if ($fleet && $fleet->vehicle_id) {

                    // Update the VehicleDetails model
                    $vehicle = \App\Models\vehicleDetails::find($fleet->vehicle_id);
                    if ($vehicle) {

                        $formattedReminderDate = \Carbon\Carbon::parse($reminder->next_reminder_date)->format('d-m-Y');

                        // Update the appropriate field based on the planner_type value
                        if ($fleet->planner_type === 'Tacho Calibration') {
                            $vehicle->tacho_calibration = $reminder->next_reminder_date;
                        } elseif ($fleet->planner_type === 'DVS/PSS Permit Expiry') {
                            $vehicle->dvs_pss_permit_expiry = $reminder->next_reminder_date;
                        } elseif ($fleet->planner_type === 'Insurance') {
                            $vehicle->insurance = $reminder->next_reminder_date;
                        } elseif ($fleet->planner_type === 'PMI Due') {
                            $vehicle->PMI_due = $formattedReminderDate; // Store in d-m-Y format
                        } elseif ($fleet->planner_type === 'Brake Test Due') {
                            $vehicle->brake_test_due = $reminder->next_reminder_date;
                        } elseif ($fleet->planner_type === 'Fridge Service') {
                            $vehicle->fridge_service = $reminder->next_reminder_date;
                        } elseif ($fleet->planner_type === 'Fridge Calibration') {
                            $vehicle->fridge_calibration = $reminder->next_reminder_date;
                        } elseif ($fleet->planner_type === 'Tail lift') {
                            $vehicle->tail_lift = $reminder->next_reminder_date;
                        } elseif ($fleet->planner_type === 'Loler') {
                            $vehicle->loler = $reminder->next_reminder_date;
                        }
                        $vehicle->save();

                        // Find the next FleetPlannerReminder with the same fleet_planner_id
                        $nextReminder = FleetPlannerReminder::where('fleet_planner_id', $reminder->fleet_planner_id)
                            ->where('id', '>', $reminder->id)
                            ->orderBy('id', 'asc')
                            ->first();

                        if ($nextReminder) {

                            $formattedNextReminderDate = \Carbon\Carbon::parse($nextReminder->next_reminder_date)->format('d-m-Y');

                            if ($fleet->planner_type === 'Tacho Calibration') {
                                $vehicle->tacho_calibration = $nextReminder->next_reminder_date;
                            } elseif ($fleet->planner_type === 'DVS/PSS Permit Expiry') {
                                $vehicle->dvs_pss_permit_expiry = $nextReminder->next_reminder_date;
                            } elseif ($fleet->planner_type === 'Insurance') {
                                $vehicle->insurance = $nextReminder->next_reminder_date;
                            } elseif ($fleet->planner_type === 'PMI Due') {
                                $vehicle->PMI_due = $formattedNextReminderDate;
                            } elseif ($fleet->planner_type === 'Brake Test Due') {
                                $vehicle->brake_test_due = $nextReminder->next_reminder_date;
                            } elseif ($fleet->planner_type === 'Fridge Service') {
                                $vehicle->fridge_service = $nextReminder->next_reminder_date;
                            } elseif ($fleet->planner_type === 'Fridge Calibration') {
                                $vehicle->fridge_calibration = $nextReminder->next_reminder_date;
                            } elseif ($fleet->planner_type === 'Tail lift') {
                                $vehicle->tail_lift = $nextReminder->next_reminder_date;
                            } elseif ($fleet->planner_type === 'Loler') {
                                $vehicle->loler = $nextReminder->next_reminder_date;
                            }
                            $vehicle->save();

                        } else {

                            // No next pending reminder — auto create new year reminders
                            $newFleet = \App\Models\Fleet::create([
                                'company_id'   => $fleet->company_id,
                                'vehicle_id'   => $fleet->vehicle_id,
                                'planner_type' => $fleet->planner_type,
                                'start_date'   => \Carbon\Carbon::parse($reminder->next_reminder_date)->addWeeks($fleet->every)->format('Y-m-d'),
                                'end_date'     => \Carbon\Carbon::parse($reminder->next_reminder_date)->addWeeks($fleet->every)->addYear()->format('Y-m-d'),
                                'every'        => $fleet->every,
                                'interval'     => $fleet->interval,
                                'created_by'   => \Auth::user()->id,
                            ]);

                            $this->generateReminders($newFleet);

                            // get first new pending reminder
                            $firstNewReminder = FleetPlannerReminder::where('fleet_planner_id', $newFleet->id)
                                ->orderBy('next_reminder_date', 'asc')
                                ->first();

                            if ($firstNewReminder) {
                                if ($fleet->planner_type === 'Tacho Calibration') {
                                    $vehicle->tacho_calibration = $firstNewReminder->next_reminder_date;
                                } elseif ($fleet->planner_type === 'DVS/PSS Permit Expiry') {
                                    $vehicle->dvs_pss_permit_expiry = $firstNewReminder->next_reminder_date;
                                } elseif ($fleet->planner_type === 'Insurance') {
                                    $vehicle->insurance = $firstNewReminder->next_reminder_date;
                                } elseif ($fleet->planner_type === 'PMI Due') {
                                    $vehicle->PMI_due = \Carbon\Carbon::parse($firstNewReminder->next_reminder_date)->format('d-m-Y');
                                } elseif ($fleet->planner_type === 'Brake Test Due') {
                                    $vehicle->brake_test_due = $firstNewReminder->next_reminder_date;
                                } elseif ($fleet->planner_type === 'Fridge Service') {
                                    $vehicle->fridge_service = $firstNewReminder->next_reminder_date;
                                } elseif ($fleet->planner_type === 'Fridge Calibration') {
                                    $vehicle->fridge_calibration = $firstNewReminder->next_reminder_date;
                                } elseif ($fleet->planner_type === 'Tail lift') {
                                    $vehicle->tail_lift = $firstNewReminder->next_reminder_date;
                                } elseif ($fleet->planner_type === 'Loler') {
                                    $vehicle->loler = $firstNewReminder->next_reminder_date;
                                }
                                $vehicle->save();
                            }
                        }
                    }
                }
            }

            return redirect()->route('fleet.index')->with('success', 'Operation completed successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', __('Please valid Tyre Depth value'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }

    public function updateEvent(Request $request, $id)
    {
        // Validate incoming request
        $validated = $request->validate([
            'comment' => 'nullable|string',
            'odometer_reading' => 'nullable|numeric',
            'total_cost' => 'nullable|numeric|min:0',
            'files.*' => 'nullable|file|mimes:jpeg,png,pdf,doc,docx,txt,zip|max:5000', // Add file validation
        ]);

        // Find the event by its ID
        $reminder = FleetPlannerReminder::findOrFail($id);

        // Update comment and odometer reading
        $reminder->comment = $request->input('comment', $reminder->comment);
        $reminder->odometer_reading = $request->input('odometer_reading', $reminder->odometer_reading);

        $reminder->total_cost = $request->input('total_cost', $reminder->total_cost);
        $reminder->updated_by = \Auth::user()->id;
        $reminder->status = 'Completed';

        // Save the updated data
        $reminder->save();

        // Save File Uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $originalName = $file->getClientOriginalName();
                $path = $file->storeAs('fleet/planner', $originalName, 'local');
                \App\Models\FleetFileUpload::create([
                    'fleet_planner_reminder_id' => $reminder->id,
                    'file_path' => $path,
                ]);
            }
        }

        // Check if the FleetPlannerReminder has a fleet_planner_id
        if ($reminder->fleet_planner_id) {
            // Fetch the related Fleet model using fleet_planner_id
            $fleet = \App\Models\Fleet::where('id', $reminder->fleet_planner_id)->first();

            if ($fleet && $fleet->vehicle_id) {
                // Update the VehicleDetails model
                $vehicle = \App\Models\vehicleDetails::find($fleet->vehicle_id);
                if ($vehicle) {

                    $formattedReminderDate = \Carbon\Carbon::parse($reminder->next_reminder_date)->format('d-m-Y');

                    // Update the appropriate field based on the planner_type value
                    if ($fleet->planner_type === 'Tacho Calibration') {
                        $vehicle->tacho_calibration = $reminder->next_reminder_date;
                    } elseif ($fleet->planner_type === 'DVS/PSS Permit Expiry') {
                        $vehicle->dvs_pss_permit_expiry = $reminder->next_reminder_date;
                    } elseif ($fleet->planner_type === 'Insurance') {
                        $vehicle->insurance = $reminder->next_reminder_date;
                    } elseif ($fleet->planner_type === 'PMI Due') {
                        $vehicle->PMI_due = $formattedReminderDate;
                    } elseif ($fleet->planner_type === 'Brake Test Due') {
                        $vehicle->brake_test_due = $reminder->next_reminder_date;
                    } elseif ($fleet->planner_type === 'Fridge Service') {
                        $vehicle->fridge_service = $reminder->next_reminder_date;

                    } elseif ($fleet->planner_type === 'Fridge Calibration') {
                        $vehicle->fridge_calibration = $reminder->next_reminder_date;

                    } elseif ($fleet->planner_type === 'Tail lift') {
                        $vehicle->tail_lift = $reminder->next_reminder_date;
                    } elseif ($fleet->planner_type === 'Loler') {
                        $vehicle->loler = $reminder->next_reminder_date;
                    }
                    $vehicle->save();

                    // Find the next FleetPlannerReminder with the same fleet_planner_id
                    $nextReminder = FleetPlannerReminder::where('fleet_planner_id', $reminder->fleet_planner_id)
                        ->where('id', '>', $reminder->id) // Ensure it's the next reminder
                        ->orderBy('id', 'asc')
                        ->first();

                    if ($nextReminder) {

                        $formattedNextReminderDate = \Carbon\Carbon::parse($nextReminder->next_reminder_date)->format('d-m-Y');

                        // Update the appropriate field with the next reminder's next_reminder_date
                        if ($fleet->planner_type === 'Tacho Calibration') {
                            $vehicle->tacho_calibration = $nextReminder->next_reminder_date;
                        } elseif ($fleet->planner_type === 'DVS/PSS Permit Expiry') {
                            $vehicle->dvs_pss_permit_expiry = $nextReminder->next_reminder_date;
                        } elseif ($fleet->planner_type === 'Insurance') {
                            $vehicle->insurance = $nextReminder->next_reminder_date;
                        } elseif ($fleet->planner_type === 'PMI Due') {
                            $vehicle->PMI_due = $formattedNextReminderDate;
                        } elseif ($fleet->planner_type === 'Brake Test Due') {
                            $vehicle->brake_test_due = $nextReminder->next_reminder_date;
                        } elseif ($fleet->planner_type === 'Fridge Service') {
                            $vehicle->fridge_service = $nextReminder->next_reminder_date;

                        } elseif ($fleet->planner_type === 'Fridge Calibration') {
                            $vehicle->fridge_calibration = $nextReminder->next_reminder_date;

                        } elseif ($fleet->planner_type === 'Tail lift') {
                            $vehicle->tail_lift = $nextReminder->next_reminder_date;
                        } elseif ($fleet->planner_type === 'Loler') {
                            $vehicle->loler = $nextReminder->next_reminder_date;
                        }
                        $vehicle->save();
                    }
                }
            }
        }

        session()->flash('success', 'Changes saved successfully.');

        return response()->json(['success' => true]);
    }

    public function showUpdateStatusPage($id)
    {
        $reminder = FleetPlannerReminder::findOrFail($id);

        // Get the specific vehicle_id related to the reminder
        $vehicle_id = $reminder->fleet->vehicle_id;

        // Fetch the last tyre depth record for this specific vehicle
        $lastTyreDepth = \App\Models\FleetTyreDepth::where('vehicle_id', $vehicle_id)
            ->latest()
            ->first(); // Gets the most recent tyre depth record for this vehicle

        return view('fleet.form.create', compact('reminder', 'lastTyreDepth'));
    }

    public function checkAllVehiclesTaxDue()
    {
        // Retrieve all vehicles from the VehicleDetails model
        $vehicles = \App\Models\vehicleDetails::all();

        // Iterate through each vehicle
        foreach ($vehicles as $vehicleDetails) {
            // Check if the company associated with this vehicle is Active
            $companyDetails = \App\Models\CompanyDetails::find($vehicleDetails->companyName);

            if ($companyDetails && $companyDetails->company_status === 'Active') {
                // Check if the taxDueDate exists for this vehicleDetails
                if ($vehicleDetails->taxDueDate) {
                    $taxDueDate = Carbon::createFromFormat('d F Y', $vehicleDetails->taxDueDate)->format('Y-m-d');

                    // Check if a Fleet entry already exists with the same taxDueDate
                    $existingFleetEntry = \App\Models\Fleet::where('vehicle_id', $vehicleDetails->id)
                        ->where('start_date', $taxDueDate)
                        ->first();

                    if (! $existingFleetEntry) {
                        // Create a new Fleet reminder for the taxDueDate
                        $fleetReminderData = [
                            'start_date' => $taxDueDate,
                            'end_date' => $taxDueDate, // Same as taxDueDate for daily reminder
                            'company_id' => $vehicleDetails->companyName, // Assuming companyName is stored in VehicleDetails
                            'planner_type' => 'Road Tax',
                            'vehicle_id' => $vehicleDetails->id, // Save vehicleDetails modal id even if vehicle_id is 0
                            'every' => 1,
                            'interval' => 'Day',
                            'created_by' => $vehicleDetails->created_by,
                        ];

                        $fleetReminder = \App\Models\Fleet::create($fleetReminderData);
                        $this->generateReminders($fleetReminder);
                    }
                }

                // Ensure that the related Vehicle model exists for this vehicleDetails (skip if vehicle_id is 0)
                if ($vehicleDetails->vehicle_id > 0) {
                    $vehicle = \App\Models\Vehicles::find($vehicleDetails->vehicle_id);

                    // Process the `annual_test_expiry_date` if it exists
                    if ($vehicle && $vehicle->annual_test_expiry_date) {
                        $annualTestExpiryDate = $vehicle->annual_test_expiry_date;

                        // Check if a Fleet entry already exists with the same annual_test_expiry_date
                        $existingAnnualTestReminder = \App\Models\Fleet::where('vehicle_id', $vehicleDetails->id)
                            ->where('start_date', $annualTestExpiryDate)
                            ->first();

                        if (! $existingAnnualTestReminder) {
                            // Create a new Fleet reminder for the annual_test_expiry_date
                            $annualTestReminderData = [
                                'start_date' => $annualTestExpiryDate,
                                'end_date' => $annualTestExpiryDate, // Same date as reminder
                                'company_id' => $vehicleDetails->companyName,
                                'planner_type' => 'MOT',
                                'vehicle_id' => $vehicleDetails->id, // Save vehicleDetails modal id
                                'every' => 1,
                                'interval' => 'Day',
                                'created_by' => $vehicleDetails->created_by,
                            ];

                            $fleetMot = \App\Models\Fleet::create($annualTestReminderData);
                            $this->generateReminders($fleetMot);
                        }
                    }
                }
            }
        }

        // Return a response once all vehicles have been processed
        return response()->json([
            'success' => true,
            'message' => 'Fleet reminders have been created for all applicable vehicles and annual test expiries.',
        ], 200);
    }

    private function generateReminders($fleet)
    {

        // Update existing reminders for the fleet_planner_id
        $existingReminders = \App\Models\FleetPlannerReminder::where('fleet_planner_id', $fleet->id)->get();

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

    public function getVehiclesByCompany($companyId)
    {
        $vehicles = \App\Models\vehicleDetails::where('companyName', $companyId)
            ->whereHas('types', function ($query) {
                $query->where('company_status', 'Active');
            })
            ->pluck('registrationNumber', 'id');

        return response()->json($vehicles);
    }

 public function getVehicleGroupsByCompany(Request $request)
    {
        $companyId = $request->input('company_id');
        $user = \Auth::user();

    // User vehicle group ids
    $userGroupIds = is_array($user->vehicle_group_id)
        ? $user->vehicle_group_id
        : json_decode($user->vehicle_group_id, true);

        if (! is_array($userGroupIds)) {
        $userGroupIds = [$user->vehicle_group_id];
    }

    if ($user->hasRole('company') || $user->hasRole('PTC manager')) {

        $groups = \App\Models\VehicleGroup::where('company_id', $companyId)
            ->pluck('name', 'id');

    } else {

            // 🚫 Prevent accessing other company
        if ($companyId != $user->companyname) {
            return response()->json(['groups' => []]);
        }

        $groups = \App\Models\VehicleGroup::where('company_id', $companyId)
            ->whereIn('id', $userGroupIds ?? [])
            ->pluck('name', 'id');
    }

        return response()->json(['groups' => $groups]);
    }

    public function getVehicleByGroup(Request $request)
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
            $vehicles = \App\Models\vehicleDetails::where('companyName', $companyId)
                ->whereIn('group_id', $groupIds)
                ->pluck('registrationNumber', 'id');
        } else {
            $vehicles = \App\Models\vehicleDetails::where('companyName', $companyId)
                ->whereIn('group_id', $groupIds)->whereIn('depot_id', $depotIds)
                ->pluck('registrationNumber', 'id');
        }

        return response()->json(['vehicles' => $vehicles]);
    }

    public function getVehiclesByDepotGroup(Request $request)
    {
        $depotId = $request->depot_id;
        $groupId = $request->group_id;
        $companyId = $request->company_id;

        $vehicles = \App\Models\vehicleDetails::query();

        if ($companyId) {
            $vehicles->where('companyName', $companyId);
        }

        if ($depotId) {
            $vehicles->where('depot_id', $depotId);
        }

        if ($groupId) {
            $vehicles->where('group_id', $groupId);
        }

        $vehicles = $vehicles->select('id', 'registrationNumber', 'vehicle_nick_name')->get();

        return response()->json($vehicles);
    }

    public function otherreminderedit(FleetPlannerReminder $reminder)
    {
        $user = \Auth::user();
        if ($user->can('manage planner')) {

            return view('fleet.otherreminderedit', compact('reminder'));
        } else {
            // If user doesn't have permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function otherreminderupdate(Request $request, FleetPlannerReminder $reminder)
    {
        if (\Auth::user()->can('manage planner')) {
            $validator = \Validator::make(
                $request->all(), [
                    'next_reminder_date' => 'required|date',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // Convert the date format from Y-m-d to d/m/Y if it's not null
            $newReminderDate = \Carbon\Carbon::parse($request->next_reminder_date)->format('Y-m-d'); // Ensure no time part
            $oldReminderDate = \Carbon\Carbon::parse($reminder->next_reminder_date)->format('Y-m-d'); // Ensure no time part

            // Convert to Carbon instances before calculating the difference
            $newReminderDateCarbon = \Carbon\Carbon::parse($newReminderDate);
            $oldReminderDateCarbon = \Carbon\Carbon::parse($oldReminderDate);

            // Update the vehicle status in the FleetPlannerReminder table
            $reminder->next_reminder_date = $newReminderDate;
            $reminder->updated_by = \Auth::user()->id;
            $reminder->save();

            // Get the Fleet instance and related vehicleDetails
            $fleet = $reminder->fleet;

            // Ensure the vehicleDetails is loaded using vehicle_id
            $vehicleDetails = \App\Models\vehicleDetails::where('id', $fleet->vehicle_id)->first();

            // Check if the vehicleDetails exists
            if ($vehicleDetails) {
                // Check planner_type and update the corresponding field in vehicleDetails
                if ($fleet->planner_type == 'Tacho Calibration') {
                    $vehicleDetails->update([
                        'tacho_calibration' => $newReminderDate, // Save only the date
                    ]);
                } elseif ($fleet->planner_type == 'DVS/PSS Permit Expiry') {
                    $vehicleDetails->update([
                        'dvs_pss_permit_expiry' => $newReminderDate, // Save only the date
                    ]);
                } elseif ($fleet->planner_type == 'Insurance') {
                    $vehicleDetails->update([
                        'insurance' => $newReminderDate, // Save only the date
                    ]);
                }

                // Save the changes to the vehicleDetails
                $vehicleDetails->save();
            } else {
                return redirect()->back()->with('error', __('Vehicle details not found.'));
            }

            // Update future reminders' next_reminder_date
            $reminders = \App\Models\FleetPlannerReminder::where('fleet_planner_id', $fleet->id)
                ->where('next_reminder_date', '>', $newReminderDate)
                ->orderBy('next_reminder_date')
                ->get();

            foreach ($reminders as $futureReminder) {
                // Convert future reminder date to Carbon instance
                $futureReminderDate = \Carbon\Carbon::parse($futureReminder->next_reminder_date);

                // Calculate the date difference in days
                $dateDifference = $oldReminderDateCarbon->diffInDays($newReminderDateCarbon);

                if ($newReminderDateCarbon < $oldReminderDateCarbon) {
                    $futureDate = $futureReminderDate->subDays($dateDifference)->format('Y-m-d'); // Format date without time
                } else {
                    $futureDate = $futureReminderDate->addDays($dateDifference)->format('Y-m-d'); // Format date without time
                }

                // Update the future reminder date
                $futureReminder->next_reminder_date = $futureDate;
                $futureReminder->save();
            }

            return redirect()->back()->with('success', __('Reminder updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function remindershow($reminderId)
    {
         $user = \Auth::user();

        // Find the reminder by its ID
        $reminder = \App\Models\FleetPlannerReminder::findOrFail($reminderId);

        if ($reminder->status !== 'Pending') {
            return redirect()->back()->with('error', __('You are already completed.'));
        }

        // Get the fleet associated with the reminder
        $fleet = $reminder->fleet;

        // Check if the fleet's associated company has an Active status
        $company = \App\Models\CompanyDetails::where('id', $fleet->company_id)
            ->where('company_status', 'Active')
            ->first();

        if (! $company) {
            // If the company is not active, redirect back with an error message
            return redirect()->back()->with('error', __('The associated company is not active.'));
        }

        // Load all reminders for this fleet
        $reminders = $fleet->reminders;

        // Fetch the vehicle ID from the fleet
       $vehicle = \App\Models\vehicleDetails::find($fleet->vehicle_id);

    if (! $vehicle) {
        return redirect()->back()->with('error', __('Vehicle not found.'));
    }

        // Fetch the last tyre depth record for this specific vehicle
        $lastTyreDepth = null;
        if ($fleet->vehicle_id) {
            $lastTyreDepth = \App\Models\FleetTyreDepth::where('vehicle_id', $fleet->vehicle_id)
                ->latest()
                ->first();
        }

        if (! ($user->hasRole('company') || $user->hasRole('PTC manager'))) {

        // Company restriction
        if ($fleet->company_id != $user->companyname) {
            return redirect()->back()->with('error', __('You are not allowed to access this company reminder.'));
        }

        // Depot restriction
        $userDepotIds = is_array($user->depot_id)
            ? $user->depot_id
            : json_decode($user->depot_id, true);

        if (!is_array($userDepotIds)) {
            $userDepotIds = [$user->depot_id];
        }

        if (! in_array($vehicle->depot_id, $userDepotIds)) {
            return redirect()->back()->with('error', __('You are not allowed to access this depot reminder.'));
        }

        // Vehicle group restriction
        $userGroupIds = is_array($user->vehicle_group_id)
            ? $user->vehicle_group_id
            : json_decode($user->vehicle_group_id, true);

        if (!is_array($userGroupIds)) {
            $userGroupIds = [$user->vehicle_group_id];
        }

        if (! in_array($vehicle->group_id, $userGroupIds)) {
            return redirect()->back()->with('error', __('You are not allowed to access this vehicle group reminder.'));
        }
    }

        // Return the view with all the required data
        return view('fleet.remindershow', compact('fleet', 'reminders', 'lastTyreDepth', 'reminder'));
    }

    public function create()
    {
        $user = \Auth::user();
        if ($user->can('manage planner')) {

            // Check if the user is a super admin
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                // Fetch all company names
                $contractTypes = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->pluck('name', 'id');

            } else {
                // Fetch the company name for the logged-in user
                $contractTypes = CompanyDetails::orderBy('name', 'asc')->where('created_by', '=', $user->creatorId())
                    ->where('id', '=', $user->companyname)->where('company_status', 'Active')
                    ->pluck('name', 'id');

                // Check if the user creating the new user is directly associated with a company
                // If not, remove the company name from the list
                if ($user->companyname) {
                    $contractTypes = CompanyDetails::orderBy('name', 'asc')->where('id', '=', $user->companyname)->where('company_status', 'Active')
                        ->pluck('name', 'id');
                } else {
                    $contractTypes = [];
                }
            }
            // Fetch all vehicles with active company

            return view('fleet.create', compact('contractTypes'));
        } else {
            // If user doesn't have permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('manage planner')) {
            $validator = \Validator::make($request->all(), [
                'company_id' => 'required|exists:company_details,id',
                'vehicle_ids' => 'required|array|min:1',
                'vehicle_ids.*' => 'exists:vehicle_details,id',
                'start_date' => 'required|date',
                'planner_type' => 'required|string|max:255',
                'every' => 'required|integer|min:1',
                'interval' => 'required|in:Day,Week,Month',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $plannerType = strtolower(trim($request->planner_type));
            $notAllowedPlannerTypes = [
                'tacho calibration',
                'dvs/pss permit expiry',
                'pmi due',
                'brake test due',
                'insurance',
                'road tax',
                'mot',
                'fridge service',
                'tail lift',
                'loler',
            ];

            if (in_array($plannerType, $notAllowedPlannerTypes)) {
                return redirect()->back()->with('error', __('This planner type is not allowed.'));
            }

            foreach ($request->vehicle_ids as $vehicleId) {
                $fleet = new \App\Models\Fleet();
                $fleet->company_id = $request->company_id;
                $fleet->vehicle_id = $vehicleId;
                $fleet->planner_type = $request->planner_type;
                $fleet->start_date = $request->start_date;
                $fleet->end_date = \Carbon\Carbon::parse($request->start_date)->addYear()->format('Y-m-d');
                $fleet->every = $request->every;
                $fleet->interval = $request->interval;
                $fleet->created_by = \Auth::user()->id;
                $fleet->save();

                $startDate = \Carbon\Carbon::parse($fleet->start_date);
                $endDate = \Carbon\Carbon::parse($fleet->end_date);
                $nextReminderDate = $startDate;

                while ($nextReminderDate <= $endDate) {
                    \App\Models\FleetPlannerReminder::create([
                        'fleet_planner_id' => $fleet->id,
                        'next_reminder_date' => $nextReminderDate,
                        'status' => 'Pending',
                    ]);

                    switch ($request->interval) {
                        case 'Day':
                            $nextReminderDate = $nextReminderDate->addDays($request->every);
                            break;
                        case 'Week':
                            $nextReminderDate = $nextReminderDate->addWeeks($request->every);
                            break;
                        case 'Month':
                            $nextReminderDate = $nextReminderDate->addMonths($request->every);
                            break;
                    }
                }
            }

            return redirect()->route('fleet.index')->with('success', __('Planner successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show($reminderId)
    {
         $user = \Auth::user();
        // Find the reminder by its ID
        $reminder = \App\Models\FleetPlannerReminder::findOrFail($reminderId);

        // Get the fleet associated with the reminder
        $fleet = $reminder->fleet;

        // Check if the fleet's associated company has an Active status
        $company = \App\Models\CompanyDetails::where('id', $fleet->company_id)
            ->where('company_status', 'Active')
            ->first();

        if (! $company) {
            // If the company is not active, redirect back with an error message
            return redirect()->back()->with('error', __('The associated company is not active.'));
        }

        // Load all reminders for this fleet
        $reminders = $fleet->reminders;

        $reminders = $fleet->reminders()->orderBy('next_reminder_date', 'asc')->get();

        // Find the first reminder that is Pending
        $firstPendingReminder = $reminders->first(function ($r) {
            return $r->status === 'Pending';
        });

        // Fetch the vehicle ID from the fleet
       $vehicle = \App\Models\vehicleDetails::find($fleet->vehicle_id);

    if (! $vehicle) {
        return redirect()->back()->with('error', __('Vehicle not found.'));
    }

        // Fetch the last tyre depth record for this specific vehicle
        $lastTyreDepth = null;
        if ($fleet->vehicle_id) {
            $lastTyreDepth = \App\Models\FleetTyreDepth::where('vehicle_id', $fleet->vehicle_id)
                ->latest()
                ->first();
        }

        if (! ($user->hasRole('company') || $user->hasRole('PTC manager'))) {

        // ---- Company restriction
        if ($fleet->company_id != $user->companyname) {
            return redirect()->back()->with('error', __('You are not allowed to access this company data.'));
        }

        // ---- Depot restriction
        $userDepotIds = is_array($user->depot_id)
            ? $user->depot_id
            : json_decode($user->depot_id, true);

        if (!is_array($userDepotIds)) {
            $userDepotIds = [$user->depot_id];
        }

        if (! in_array($vehicle->depot_id, $userDepotIds)) {
            return redirect()->back()->with('error', __('You are not allowed to access this depot data.'));
        }

        // ---- Vehicle group restriction
        $userGroupIds = is_array($user->vehicle_group_id)
            ? $user->vehicle_group_id
            : json_decode($user->vehicle_group_id, true);

        if (!is_array($userGroupIds)) {
            $userGroupIds = [$user->vehicle_group_id];
        }

        if (! in_array($vehicle->group_id, $userGroupIds)) {
            return redirect()->back()->with('error', __('You are not allowed to access this vehicle group data.'));
        }
    }

        // Return the view with all the required data
        return view('fleet.show', compact('fleet', 'reminders', 'lastTyreDepth', 'reminder', 'firstPendingReminder'));
    }

    public function reminderedit(FleetPlannerReminder $reminder)
    {
        $user = \Auth::user();
        if ($user->can('manage depot')) {

            return view('fleet.reminderedit', compact('reminder'));
        } else {
            // If user doesn't have permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function reminderupdate(Request $request, FleetPlannerReminder $reminder)
    {
        if (\Auth::user()->can('manage planner')) {
            $validator = \Validator::make(
                $request->all(), [
                    'next_reminder_date' => 'required|date',
                    'archive_reason' => 'nullable|string',
                    'archive_other' => 'nullable|string|max:255', // Handle "Other" text if applicable
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // Convert the date format from Y-m-d to d/m/Y if it's not null
            $newReminderDate = \Carbon\Carbon::parse($request->next_reminder_date);
            $oldReminderDate = \Carbon\Carbon::parse($reminder->next_reminder_date);
            $newReminderDateYmd = \Carbon\Carbon::parse($request->next_reminder_date)->format('Y-m-d');

            $newReminderDateDmy = \Carbon\Carbon::parse($request->next_reminder_date)->format('d-m-Y');

            // Archive logic - Update vehicle status based on archive_reason
            if ($request->archive_reason == 'Other' && $request->archive_other) {
                $reminder->vehicle_status = $request->archive_other;  // Store the custom "Other" text
            } elseif ($request->archive_reason) {
                $reminder->vehicle_status = $request->archive_reason;  // Store selected archive reason
            } else {
                $reminder->vehicle_status = null;  // Clear if no reason is selected
            }

            // Update the vehicle status in the FleetPlannerReminder table
            $reminder->next_reminder_date = $newReminderDate;
            $reminder->comment = $request->comment;
            $reminder->updated_by = \Auth::user()->id;
            $reminder->reminder_status = 'Done';
            $reminder->save();

            // Update vehicleDetails status
            $fleet = $reminder->fleet;

            if ($fleet && $fleet->vehicle) {
                $vehicleDetails = $fleet->vehicle;

                if ($vehicleDetails) {
                    // Update vehicle status only if archive_reason is not "On Time"
                    if ($request->archive_reason != 'On time') {
                        $vehicleDetails->vehicle_status = $reminder->vehicle_status;
                    }

                    // âœ… New Logic: Update specific due dates based on planner type
                    if ($fleet->planner_type == 'PMI Due') {
                        $vehicleDetails->PMI_due = $newReminderDateDmy;
                    } elseif ($fleet->planner_type == 'Brake Test Due') {
                        $vehicleDetails->brake_test_due = $newReminderDateYmd;
                    }

                    $vehicleDetails->save();
                }
            }

            // New Logic: Delete all future reminders if vehicle status is "Sold" or "Scrapped"
            if (in_array($request->archive_reason, ['Sold', 'Scrapped'])) {
                // Find the vehicle_id associated with this fleet
                $vehicleId = $fleet->vehicle_id;

                if ($vehicleId) {
                    // Delete all future reminders in all planners where vehicle_id matches and date > newReminderDate
                    \App\Models\FleetPlannerReminder::whereHas('fleet', function ($query) use ($vehicleId) {
                        $query->where('vehicle_id', $vehicleId);
                    })
                        ->where('next_reminder_date', '>', $newReminderDate) // Use updated next_reminder_date
                        ->delete();
                }

                return redirect()->back()->with('success', __('Reminder updated and future reminders deleted due to vehicle status: '.$request->archive_reason));
            }

            // Update future reminders' next_reminder_date
            $reminders = \App\Models\FleetPlannerReminder::where('fleet_planner_id', $fleet->id)
                ->where('next_reminder_date', '>', $newReminderDate)
                ->orderBy('next_reminder_date')
                ->get();

            if ($fleet && $fleet->end_date) {
                $endDate = \Carbon\Carbon::parse($fleet->end_date);

                foreach ($reminders as $futureReminder) {
                    $dateDifference = $oldReminderDate->diffInDays($newReminderDate);

                    if ($newReminderDate < $oldReminderDate) {
                        $futureDate = \Carbon\Carbon::parse($futureReminder->next_reminder_date)->subDays($dateDifference);
                    } else {
                        $futureDate = \Carbon\Carbon::parse($futureReminder->next_reminder_date)->addDays($dateDifference);
                    }

                    // Ensure the next_reminder_date does not exceed the fleet's end_date
                    if ($futureDate <= $endDate) {
                        $futureReminder->next_reminder_date = $futureDate;
                        $futureReminder->save();
                    } else {
                        // Optionally, you can delete reminders that exceed end_date
                        $futureReminder->delete();
                    }
                }
            }

            return redirect()->back()->with('success', __('Reminder updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function reminderdestroy(FleetPlannerReminder $reminder)
    {

        $reminder->delete();

        return redirect()->back()->with('success', __('Reminder successfully deleted.'));

    }

    public function exportCalendar(Request $request)
    {
        $user = \Auth::user();

        // Determine company ID based on role
        $selectedCompanyId = ($user->hasRole('company') || $user->hasRole('PTC manager'))
            ? $request->input('company_id')
            : $user->companyname;

        // Retrieve inputs
        $selectedVehicleId = $request->input('vehicle_id');
        $selectedPlannerType = $request->input('planner_type');
        $selectedDepotId = $request->input('depot_id');
$selectedGroupId = $request->input('group_id');
        $selectedYear = $request->input('year');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Get company name for the file
        $companyName = CompanyDetails::where('id', $selectedCompanyId)->value('name') ?: 'Company';

        // Determine file name based on filter type
        if (! empty($fromDate) && ! empty($toDate)) {
            $fileName = sprintf('%s_Forward_Planner_%s_to_%s.xlsx',
                $companyName,
                \Carbon\Carbon::parse($fromDate)->format('d-m-Y'),
                \Carbon\Carbon::parse($toDate)->format('d-m-Y')
            );
        } else {
            $fileName = sprintf('%s_%s_Forward_Planner.xlsx', $companyName, $selectedYear);
        }

        // Determine depot-wise filter for non-company users
        $depotIds = [];
        if (! $user->hasRole('company') && ! $user->hasRole('PTC manager')) {
            $depotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
            if (! is_array($depotIds)) {
            $depotIds = [$user->depot_id];
        }
    }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CalendarEventsExport(
                $selectedCompanyId,
                $selectedVehicleId,
                $selectedPlannerType,
                $selectedYear,
                $fromDate,
                $toDate,
                $depotIds ,// passed to the export
                $selectedDepotId,
    $selectedGroupId
            ),
            $fileName
        );
    }

    public function pdfCalendar(Request $request)
    {
        $user = \Auth::user();

        $selectedCompanyId = $user->hasRole('company') || $user->hasRole('PTC manager')
            ? $request->input('company_id')
            : $user->companyname;
$selectedVehicleId = $request->input('vehicle_id');
$selectedPlannerType = $request->input('planner_type');
$selectedDepotId = $request->input('depot_id');
$selectedGroupId = $request->input('group_id');
        $selectedYear = $request->input('year');
        $fromDate = $request->input('from_date'); // New From Date
        $toDate = $request->input('to_date'); // New To Date

        // Ensure from_date and to_date are not empty and valid, else use year-based range
        if (! empty($fromDate) && ! empty($toDate)) {
            $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', $fromDate);
            $endDate = \Carbon\Carbon::createFromFormat('Y-m-d', $toDate);
        } else {
            // Default to full year range if no from/to dates are provided
            $startDate = \Carbon\Carbon::create($selectedYear, 1, 1);
            $endDate = \Carbon\Carbon::create($selectedYear, 12, 31);
        }

        $dates = \Carbon\CarbonPeriod::create($startDate, '1 week', $endDate);

        // Group dates by quarter (1 Jan - 30 Apr, 1 May - 31 Aug, 1 Sep - 31 Dec)
        $groupedDates = [
            'group1' => [],
            'group2' => [],
            'group3' => [],
        ];

        foreach ($dates as $date) {
            if ($date->month <= 4) {
                $groupedDates['group1'][] = $date;
            } elseif ($date->month <= 8) {
                $groupedDates['group2'][] = $date;
            } else {
                $groupedDates['group3'][] = $date;
            }
        }

        // Handle depot ID extraction
        $depotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
        if (! is_array($depotIds)) {
            $depotIds = [$user->depot_id];
    }

        // Fetch fleets with reminders (filter by depot if not company/PTC manager)
         $fleets = Fleet::when($selectedCompanyId, fn ($q) => $q->where('company_id', $selectedCompanyId))

->when($selectedVehicleId, fn ($q) => $q->where('vehicle_id', $selectedVehicleId))

->when($selectedPlannerType, fn ($q) => $q->where('planner_type', $selectedPlannerType))

->when($selectedDepotId, function ($q) use ($selectedDepotId) {
    $q->whereHas('vehicle', function ($sub) use ($selectedDepotId) {
        $sub->where('depot_id', $selectedDepotId);
    });
})

->when($selectedGroupId, function ($q) use ($selectedGroupId) {
    $q->whereHas('vehicle', function ($sub) use ($selectedGroupId) {
        $sub->where('group_id', $selectedGroupId);
    });
})
            ->when(! $user->hasRole('company') && ! $user->hasRole('PTC manager'), function ($q) use ($depotIds) {
                $q->whereHas('vehicle', function ($subQuery) use ($depotIds) {
                    $subQuery->whereIn('depot_id', $depotIds);
                });
        })
            ->with(['reminders' => fn ($q) => $q->whereBetween('next_reminder_date', [$startDate, $endDate])])
            ->with(['vehicle']) // Ensure vehicle is eager loaded
            ->get();

        // Filter vehicle details accordingly
        $vehicleDetailsQuery = vehicleDetails::where('companyName', $selectedCompanyId);
        if (! $user->hasRole('company') && ! $user->hasRole('PTC manager')) {
            $vehicleDetailsQuery->whereIn('depot_id', $depotIds);
        }
        $vehicleDetails = $vehicleDetailsQuery->get();

        $companyName = \App\Models\CompanyDetails::where('id', $selectedCompanyId)->value('name');

        $company_logo = \App\Models\Utility::getValByName('company_logo');
        $imagePath = storage_path('/uploads/logo/'.(! empty($company_logo) ? $company_logo : '5-logo-dark.png'));

        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $img = 'data:image/png;base64,'.$imageData;
        } else {
            \Log::error('Image file does not exist: '.$imagePath);
            $img = '';
        }
        // Group fleets by vehicle_id to avoid duplicate rows for the same vehicle_id
        $groupedFleets = $fleets->groupBy('vehicle_id'); // This groups by vehicle_id

        if (! empty($fromDate) && ! empty($toDate)) {
            $fileName = sprintf('%s_Forward_Planner_%s to %s.pdf',
                $companyName,
                \Carbon\Carbon::parse($fromDate)->format('d-m-Y'),
                \Carbon\Carbon::parse($toDate)->format('d-m-Y')
            );
        } else {
            $fileName = sprintf('%s_Forward_Planner_%s.pdf', $companyName, $selectedYear);
        }

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('fleet.template', compact('groupedFleets', 'selectedYear', 'groupedDates', 'vehicleDetails', 'companyName', 'img', 'toDate', 'fromDate'))
            ->setPaper('a4', 'landscape');

        return $pdf->download(sprintf(
            $fileName
        ));

    }

    public function historyindex(Request $request)
    {
        $user = \Auth::user();
        if (\Auth::user()->can('manage planner')) {

            $selectedCompanyId = $request->input('company_id');
            $selectedDepotId   = $request->input('depot_id');
        $selectedGroupId   = $request->input('group_id');


             // Handle multiple depot IDs (convert stored JSON to array if needed)
            $depotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
            if (! is_array($depotIds)) {
                $depotIds = [$user->depot_id]; // Ensure it remains an array
            }

            $vehicleGroupIds = is_array($user->vehicle_group_id)
    ? $user->vehicle_group_id
    : json_decode($user->vehicle_group_id, true);

            if (! is_array($vehicleGroupIds)) {
                $vehicleGroupIds = [$user->vehicle_group_id];
            }

            // Fetch only records where status is 'completed'
            $query = FleetPlannerReminder::where('status', 'Completed')
                ->with(['fleet', 'fleet.vehicle', 'fleet.company'])->whereHas('fleet.company', function ($q) {
                    $q->where('company_status', 'Active'); // Filter by Active company status
                });

            // Check if the user is an admin or PTC manager
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                $query->whereHas('fleet.vehicle', function ($v) use ($selectedDepotId, $selectedGroupId) {

                if ($selectedDepotId) {
                    $v->where('depot_id', $selectedDepotId);
                }

                if ($selectedGroupId) {
                    $v->where('group_id', $selectedGroupId);
                }
            });
                if ($selectedCompanyId) {
                    $query->whereHas('fleet', function ($q) use ($selectedCompanyId) {
                        $q->where('company_id', $selectedCompanyId);
                    });
                }

                // Get filtered results
                $plannerreminder = $query->orderBy('id', 'desc')->get();
            } else {
                // Fetch only if the specific record belongs to the logged-in user's company
                 $query->whereHas('fleet', function ($q) use ($user) {
                $q->where('company_id', $user->companyname);
            });

            $query->whereHas('fleet.vehicle', function ($v) use ($depotIds, $vehicleGroupIds, $selectedDepotId, $selectedGroupId) {

                        $v->whereIn('depot_id', $depotIds)
                          ->whereIn('group_id', $vehicleGroupIds);

                if ($selectedDepotId) {
                    $v->where('depot_id', $selectedDepotId);
                }

                if ($selectedGroupId) {
                    $v->where('group_id', $selectedGroupId);
                }
            });

            $plannerreminder = $query->orderBy('id', 'desc')->get();
            }
            $companies = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->get();

             $depotsQuery = \App\Models\Depot::orderBy('name', 'asc');
            if (! $user->hasRole('company') && ! $user->hasRole('PTC manager')) {
                $depotsQuery->whereIn('id', $depotIds);
            }
            $depots = $depotsQuery->get();

             $groupsQuery = \App\Models\VehicleGroup::orderBy('name', 'asc');

            if (! $user->hasRole('company') && ! $user->hasRole('PTC manager')) {
                $groupsQuery->whereIn('id', $vehicleGroupIds);
            }

            $groups = $groupsQuery->get();

            // Pass the filtered data to the view
            return view('fleet.history.index', compact('plannerreminder', 'companies','depots','groups'));
        } else {
            // If user doesn't have permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function historyshow($id)
    {
        // Get the current authenticated user
        $user = \Auth::user();

         // Handle multiple depot IDs (convert stored JSON to array if needed)
            $depotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
            if (! is_array($depotIds)) {
                $depotIds = [$user->depot_id]; // Ensure it remains an array
            }

            $vehicleGroupIds = is_array($user->vehicle_group_id)
    ? $user->vehicle_group_id
    : json_decode($user->vehicle_group_id, true);

            if (! is_array($vehicleGroupIds)) {
                $vehicleGroupIds = [$user->vehicle_group_id];
            }


        // Base query for FleetPlannerReminder with relationships
        $query = FleetPlannerReminder::where('status', 'Completed')->with(['fleet', 'fleet.vehicle', 'fileUploads'])->whereHas('fleet.company', function ($q) {
            $q->where('company_status', 'Active'); // Filter by Active company status
        }); // Load 'files' relationship

        // Check if the user is an admin or PTC manager
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
            // Fetch the record only if it belongs to the user's company
            $plannerreminder = $query->where('id', $id)->first();
        } else {
            // Fetch only if the specific record belongs to the logged-in user's company
            $plannerreminder = $query->where('id', $id)->whereHas('fleet', function ($q) use ($user, $depotIds, $vehicleGroupIds) {
                $q->where('company_id', $user->companyname)->whereHas('vehicle', function ($v) use ($depotIds, $vehicleGroupIds) {
                        $v->whereIn('depot_id', $depotIds)
                          ->whereIn('group_id', $vehicleGroupIds);
                    });
            })->first();

        }

        if (! $plannerreminder) {
        return redirect()->back()->with('error', __('You are not allowed to view this history record.'));
    }

        // Pass data to the view
        return view('fleet.history.show', compact('plannerreminder'));
    }

    public function plannerdocumentsedit(\App\Models\FleetFileUpload $planner_file)
    {
        if (\Auth::user()->can('manage planner')) {
            return view('fleet.editfilename', compact('planner_file'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function plannerdocumentsupdate(Request $request, $id)
    {
        if (! \Auth::user()->can('manage planner')) {
            return redirect()->back()->with('error', 'Permission Denied.');
        }

        $plannerFile = \App\Models\FleetFileUpload::findOrFail($id);

        $newName = trim($request->input('image_name'));
        if (empty($newName)) {
            return redirect()->back()->with('error', 'File name is required.');
        }

        $oldPath = $plannerFile->file_path; // e.g. fleet/planner/abc123.pdf
        $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
        $newFileName = $newName.'.'.$extension;
        $newRelativePath = dirname($oldPath).'/'.$newFileName; // fleet/planner/newname.pdf

        $oldFullPath = storage_path($oldPath);
        $newFullPath = storage_path($newRelativePath);

        \Log::info('Renaming from '.$oldFullPath.' to '.$newFullPath);

        if (file_exists($oldFullPath)) {
            rename($oldFullPath, $newFullPath);

            $plannerFile->file_path = $newRelativePath; // Store relative path, not full
            $plannerFile->save();

            return redirect()->back()->with('success', 'File name updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Original file not found. Path: '.$oldFullPath);
        }
    }
}
