<?php

namespace App\Http\Controllers;

use App\Models\CompanyDetails;
use App\Models\vehicleType;
use App\Models\WorkAroundContact;
use App\Models\WorkAroundProfile;
use App\Models\WorkAroundQuestion;
use App\Models\WorkAroundQuestionAnswerStore;
use App\Models\WorkAroundStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkAroundController extends Controller
{
    // public function vehicletypeindex()
    // {
    //     if (\Auth::user()->can('manage vehicletype')) {

    //          $vehicleType = \App\Models\vehicleType::all();

    //         return view('workAround.vehicleType.index', compact('vehicleType'));

    //     } else {
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    // public function vehicletypecreate()
    // {
    //     $user = \Auth::user();
    //     if ($user->can('manage vehicletype')) {

    //         return view('workAround.vehicleType.create');
    //     } else {
    //         // If user doesn't have permission, redirect back with an error message
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    // public function vehicletypestore(Request $request)
    // {
    //     if (\Auth::user()->can('create vehicletype')) {
    //         $validator = Validator::make(
    //             $request->all(), [
    //                 'name' => 'required',
    //             ]
    //         );
    //         if ($validator->fails()) {
    //             $messages = $validator->getMessageBag();

    //             return redirect()->back()->with('error', $messages->first());
    //         }

    //         $vehicleType = new \App\Models\vehicleType();
    //         $vehicleType->name = $request->name;
    //         $vehicleType->save();

    //         return redirect()->route('vehicle.type.index')->with('success', __('Vehicle Type successfully created.'));
    //     } else {
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    // public function vehicletypeedit(vehicleType $vehicleType)
    // {
    //     return view('workAround.vehicleType.edit', compact('vehicleType'));
    // }

    // public function vehicletypeupdate(Request $request, vehicleType $vehicleType)
    // {
    //     if (\Auth::user()->can('manage vehicletype')) {
    //         $validator = \Validator::make(
    //             $request->all(), [
    //                 'name' => 'required',
    //             ]
    //         );

    //         if ($validator->fails()) {
    //             $messages = $validator->getMessageBag();

    //             return redirect()->back()->with('error', $messages->first());
    //         }

    //         // Updating the vehicle type name
    //         $vehicleType->name = $request->name;
    //         $vehicleType->save();

    //         return redirect()->route('vehicle.type.index')->with('success', __('Vehicle Type successfully updated.'));
    //     } else {
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    // public function vehicletypedelete(vehicleType $vehicleType)
    // {
    // if (\Auth::user()->can('manage vehicletype')) {
    //     $vehicleType->delete();

    //     return redirect()->route('vehicle.type.index')->with('success', __('Vehicle Type successfully deleted.'));
    // } else {
    //     return redirect()->back()->with('error', __('Permission denied.'));
    // }
    // }

    public function questionindex()
    {
        if (\Auth::user()->can('manage question')) {

            $question = \App\Models\WorkAroundQuestion::all();

            return view('workAround.question.index', compact('question'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function questioncreate()
    {
        $user = \Auth::user();
        if ($user->can('create question')) {

            return view('workAround.question.create');
        } else {
            // If user doesn't have permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function questionstore(Request $request)
    {
        if (\Auth::user()->can('create question')) {
            $validator = Validator::make(
                $request->all(), [
                    'name' => 'nullable',
                    'description' => 'nullable',
                    'question_type' => 'nullable',
                    'select_reasonimage' => 'nullable',
                    'defect_options' => 'nullable|array',  // Validate as an array
                    'other_defect' => 'nullable|string|max:255', // Validate the text input
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $question = new \App\Models\WorkAroundQuestion();
            $question->name = $request->name;
            $question->description = $request->description;
            $question->question_type = $request->question_type;
            $question->select_reasonimage = $request->select_reasonimage;
            // Handle defect options
            $defectOptions = $request->defect_options ?? [];

            // If 'Other' is selected, add the text value to defectOptions
            if ($request->filled('other_defect')) {
                $defectOptions[] = $request->other_defect;
            }

            // Store the selected options as a serialized string or JSON
            $question->defect_options = json_encode($defectOptions);

            $question->save();

            return redirect()->route('question.index')->with('success', __('Question successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function questionsedit(WorkAroundQuestion $question)
    {
        return view('workAround.question.edit', compact('question'));
    }

    public function questionsupdate(Request $request, WorkAroundQuestion $question)
    {
        if (\Auth::user()->can('edit question')) {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'nullable',
                    'description' => 'nullable',
                    'question_type' => 'nullable',
                    'select_reasonimage' => 'nullable',
                    'defect_options' => 'nullable|array',  // Validate as an array
                    'other_defect' => 'nullable|string|max:255', // Validate the text input
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $question->name = $request->name;
            $question->description = $request->description;
            $question->question_type = $request->question_type;
            $question->select_reasonimage = $request->select_reasonimage;
            // Handle defect options
            $defectOptions = $request->defect_options ?? [];

            // If 'Other' is selected, add the text value to defectOptions
            if ($request->filled('other_defect')) {
                $defectOptions[] = $request->other_defect;
            }

            // Store the selected options as a serialized string or JSON
            $question->defect_options = json_encode($defectOptions);

            $question->save();

            return redirect()->route('question.index')->with('success', __('Question successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function questionsdelete(WorkAroundQuestion $question)
    {
        if (\Auth::user()->can('delete question')) {

            \App\Models\WorkAroundProfileDetails::where('work_around_question_id', $question->id)->delete();

            \App\Models\WorkAroundQuestionAnswerStore::where('question_id', $question->id)->delete();

            $question->delete();

            return redirect()->route('question.index')->with('success', __('Question successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function profileindex(Request $request)
    {
        if (\Auth::user()->can('manage profile')) {
            $profile = \App\Models\WorkAroundProfile::all();

            $loggedInUser = \Auth::user();
            $companyName = $loggedInUser->companyname; // Company name of the logged-in user
            $selectedCompanyId = $request->input('company_id');

            // Retrieve profile based on the user's role
            $profile = null;
            if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
                // If the user has the 'company' role, show all data with pagination
                $profile = WorkAroundProfile::with(['types'])
                    ->whereHas('types', function ($q) {
                        $q->where('company_status', 'Active'); // Only include assignments where the company is active
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })->get();
            } else {
                // If the user doesn't have the 'company' role, only show profile associated with the user's company with pagination
                $profile = WorkAroundProfile::where('company_id', $companyName)
                    ->with(['types'])
                    ->whereHas('types', function ($q) {
                        $q->where('company_status', 'Active'); // Only include assignments where the company is active
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->get();
            }

            // Retrieve the company details based on the user's company name
            $companies = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->get();

            return view('workAround.profile.index', compact('profile', 'companies'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function profilecreate()
    {
        $user = \Auth::user();
        if ($user->can('create profile')) {

            // Check if the user is a super admin
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                // Fetch all company names
                $contractTypes = \App\Models\CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->pluck('name', 'id');
            } else {
                // Fetch the company name for the logged-in user
                $contractTypes = \App\Models\CompanyDetails::where('created_by', '=', $user->creatorId())
                    ->where('id', '=', $user->companyname)->where('company_status', 'Active')
                    ->pluck('name', 'id');

                // Check if the user creating the new user is directly associated with a company
                // If not, remove the company name from the list
                if ($user->companyname) {
                    $contractTypes = \App\Models\CompanyDetails::where('id', '=', $user->companyname)->where('company_status', 'Active')
                        ->pluck('name', 'id');
                } else {
                    $contractTypes = [];
                }
            }

            return view('workAround.profile.create', compact('contractTypes'));
        } else {
            // If user doesn't have permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function profilestore(Request $request)
    {
        if (\Auth::user()->can('create profile')) {
            $validator = Validator::make(
                $request->all(), [
                    'name' => 'nullable',
                    'company_id' => 'required|exists:company_details,id',
                    'mobile_app_enabled' => 'nullable',
                    'description' => 'nullable',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $profile = new \App\Models\WorkAroundProfile();
            $profile->name = $request->name;
            $profile->company_id = $request->company_id; // Save company_id
            $profile->description = $request->description;
            $profile->mobile_app_enabled = $request->mobile_app_enabled;
            $profile->save();

            return redirect()->route('profile.index')->with('success', __('Profile successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function profileedit(WorkAroundProfile $profile)
    {
        $user = \Auth::user();
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
            $contractTypes = \App\Models\CompanyDetails::where('created_by', '=', \Auth::user()->creatorId())->orderBy('name', 'asc')->where('company_status', 'Active')->get()->pluck('name', 'id');

        } else {
            // Fetch the company name for the logged-in user
            $contractTypes = \App\Models\CompanyDetails::where('created_by', '=', $user->creatorId())
                ->where('id', '=', $user->companyname)->where('company_status', 'Active')
                ->pluck('name', 'id');

            // Check if the user creating the new user is directly associated with a company
            // If not, remove the company name from the list
            if ($user->companyname) {
                $contractTypes = \App\Models\CompanyDetails::where('id', '=', $user->companyname)->where('company_status', 'Active')
                    ->pluck('name', 'id');
            } else {
                $contractTypes = [];
            }
        }

        return view('workAround.profile.edit', compact('profile', 'contractTypes'));
    }

    public function profileupdate(Request $request, WorkAroundProfile $profile)
    {
        if (\Auth::user()->can('edit profile')) {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'nullable',
                    'company_id' => 'required|exists:company_details,id',
                    'mobile_app_enabled' => 'nullable',
                    'description' => 'nullable',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $profile->name = $request->name;
            $profile->company_id = $request->company_id; // Save company_id
            $profile->description = $request->description;
            $profile->mobile_app_enabled = $request->mobile_app_enabled;
            $profile->save();

            return redirect()->back()->with('success', __('Profile successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function profiledelete(WorkAroundProfile $profile)
    {
        if (\Auth::user()->can('delete profile')) {
            $profile->delete();

            return redirect()->back()->with('success', __('Profile successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function profileshow($id)
    {
        if (\Auth::user()->can('show profile')) {
            $user = \Auth::user();

            // Allowed vehicle statuses
            $allowedStatuses = ['Owned', 'Rented', 'Leased', 'Contract Hire', 'Depot Transfer'];

            // If the user is an admin or PTC manager, show all profiles
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                $profile = WorkAroundProfile::findOrFail($id);

                // Fetch all vehicles for company & PTC managers (no depot restriction)
                $vehicles = \App\Models\Vehicles::where('companyName', $profile->company_id)
                    ->whereHas('vehicleDetail', function ($query) use ($allowedStatuses) {
                        $query->whereIn('vehicle_status', $allowedStatuses);
                    })
                    ->with(['vehicleDetail' => function ($query) {
                        $query->select('id', 'vehicle_id', 'group_id', 'make', 'depot_id', 'vehicle_status', 'vehicle_nick_name');
                    }])
                    ->get();

                $vehicleGroups = \App\Models\VehicleGroup::where('company_id', $profile->company_id)
                    ->orderBy('name', 'asc')
                    ->get();
            } else {
                // If the user has another role, check if they belong to the company
                $profile = WorkAroundProfile::where('company_id', $user->companyname)
                    ->where('id', $id)
                    ->first();

                if (! $profile) {
                    return redirect()->back()->with('error', __('Profile not found for your company.'));
                }

                // Convert JSON depot ids
                $userDepotIds = is_array($user->depot_id) ? $user->depot_id : json_decode($user->depot_id, true);
                if (! is_array($userDepotIds)) {
                    $userDepotIds = [$user->depot_id];
                }

                // Convert JSON vehicle group ids
                $vehicleGroupIds = is_array($user->vehicle_group_id)
                                    ? $user->vehicle_group_id
                                    : json_decode($user->vehicle_group_id, true);

                if (! is_array($vehicleGroupIds)) {
                    $vehicleGroupIds = [$user->vehicle_group_id];
                }

                // Fetch vehicles based on depot_id AND vehicle_group_id
                $vehicles = \App\Models\Vehicles::where('companyName', $profile->company_id)
                    ->whereHas('vehicleDetail', function ($query) use ($userDepotIds, $vehicleGroupIds, $allowedStatuses) {
                        $query->whereIn('depot_id', $userDepotIds)
                            ->whereIn('group_id', $vehicleGroupIds)
                            ->whereIn('vehicle_status', $allowedStatuses);
                    })
                    ->with(['vehicleDetail' => function ($query) {
                        $query->select('id', 'vehicle_id', 'group_id', 'make', 'depot_id', 'vehicle_status', 'vehicle_nick_name');
                    }])
                    ->get();

                $vehicleGroups = \App\Models\VehicleGroup::where('company_id', $profile->company_id)
                    ->whereIn('id', $vehicleGroupIds)
                    ->orderBy('name', 'asc')
                    ->get();
            }

            if ($profile->types->company_status === 'InActive') {
                return redirect()->back()->with('error', __('Your company is not Active.'));
            }

            // Fetch all available questions
            $availableQuestions = WorkAroundQuestion::all();

            // Get the IDs of selected vehicles and questions for this profile
            $selectedVehicles = \App\Models\WorkAroundProfileDetails::where('work_around_profile_id', $id)
                ->pluck('vehicle_id')
                ->toArray();

            $selectedQuestions = \App\Models\WorkAroundProfileDetails::where('work_around_profile_id', $id)
                ->pluck('work_around_question_id')
                ->toArray();

            return view('workAround.profile.show', compact('profile', 'vehicles', 'vehicleGroups', 'availableQuestions', 'selectedVehicles', 'selectedQuestions'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function assign(Request $request, $id)
    {
        $profile = WorkAroundProfile::findOrFail($id);

        if ($profile->types->company_status === 'InActive') {
            return redirect()->back()->with('error', __('Your company is not Active.'));
        }

        $user = \Auth::user();
        $newSelectedVehicles = $request->input('selected_vehicles', []);
        $newSelectedQuestions = $request->input('available_questions', []);

        if (empty($newSelectedQuestions)) {
            return redirect()->back()->with('error', __('Your Profile is Not Submitted. Please select at least one question.'));
        }

        // Fetch all existing profile details
        $existingProfileDetails = \App\Models\WorkAroundProfileDetails::where('work_around_profile_id', $profile->id)->get();

        // If user is NOT a company or PTC manager, restrict their modifications
        if (! $user->hasRole('company') && ! $user->hasRole('PTC manager')) {
            $userDepotIds = json_decode($user->depot_id, true);

            foreach ($existingProfileDetails as $detail) {
                $vehicle = \App\Models\Vehicles::find($detail->vehicle_id);

                if ($vehicle && $vehicle->vehicleDetail && in_array($vehicle->vehicleDetail->depot_id, $userDepotIds)) {
                    // Only remove assignments that belong to this user's depot, keeping company/PTC assignments
                    if (! in_array($detail->vehicle_id, $newSelectedVehicles) || ! in_array($detail->work_around_question_id, $newSelectedQuestions)) {
                        $detail->delete();
                    }
                }
            }
        } else {
            // If user is a company or PTC manager, allow full assignment modifications
            foreach ($existingProfileDetails as $detail) {
                if (! in_array($detail->vehicle_id, $newSelectedVehicles) || ! in_array($detail->work_around_question_id, $newSelectedQuestions)) {
                    $detail->delete();
                }
            }
        }

        // Add new selections
        foreach ($newSelectedVehicles as $vehicleId) {
            foreach ($newSelectedQuestions as $questionId) {
                $existingDetail = $existingProfileDetails->firstWhere(function ($detail) use ($vehicleId, $questionId) {
                    return $detail->vehicle_id == $vehicleId && $detail->work_around_question_id == $questionId;
                });

                if (! $existingDetail) {
                    \App\Models\WorkAroundProfileDetails::create([
                        'company_id' => $profile->company_id,
                        'vehicle_id' => $vehicleId,
                        'work_around_question_id' => $questionId,
                        'work_around_profile_id' => $profile->id,
                    ]);
                }
            }
        }

        return redirect()->route('profile.index', $profile->id)->with('success', __('Profile Submit Data successfully.'));
    }

    public function contactbookindex(Request $request)
    {
        if (\Auth::user()->can('manage contactbook')) {

            $contactbook = \App\Models\WorkAroundContact::all();

            $loggedInUser = \Auth::user();
            $companyName = $loggedInUser->companyname; // Company name of the logged-in user
            $selectedCompanyId = $request->input('company_id');

            // Retrieve profile based on the user's role
            $contactbook = null;
            if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
                // If the user has the 'company' role, show all data with pagination
                $contactbook = WorkAroundContact::with(['types'])
                    ->whereHas('types', function ($q) {
                        $q->where('company_status', 'Active'); // Only include assignments where the company is active
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })->get();
            } else {
                // If the user doesn't have the 'company' role, only show profile associated with the user's company with pagination
                $contactbook = WorkAroundContact::where('company_id', $companyName)
                    ->with(['types'])
                    ->whereHas('types', function ($q) {
                        $q->where('company_status', 'Active'); // Only include assignments where the company is active
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->get();
            }

            // Retrieve the company details based on the user's company name
            $companies = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->get();

            return view('workAround.contactbook.index', compact('contactbook', 'companies'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function contactbookcreate()
    {
        $user = \Auth::user();
        if ($user->can('create contactbook')) {

            // Check if the user is a super admin
            if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
                // Fetch all company names
                $contractTypes = \App\Models\CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->pluck('name', 'id');
            } else {
                // Fetch the company name for the logged-in user
                $contractTypes = \App\Models\CompanyDetails::where('created_by', '=', $user->creatorId())
                    ->where('id', '=', $user->companyname)->where('company_status', 'Active')
                    ->pluck('name', 'id');

                // Check if the user creating the new user is directly associated with a company
                // If not, remove the company name from the list
                if ($user->companyname) {
                    $contractTypes = \App\Models\CompanyDetails::where('id', '=', $user->companyname)->where('company_status', 'Active')
                        ->pluck('name', 'id');
                } else {
                    $contractTypes = [];
                }
            }

            return view('workAround.contactbook.create', compact('contractTypes'));
        } else {
            // If user doesn't have permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function contactbookstore(Request $request)
    {
        if (\Auth::user()->can('create contactbook')) {
            $validator = Validator::make(
                $request->all(), [
                    'name' => 'nullable',
                    'company_id' => 'required|exists:company_details,id',
                    'mobile_no' => 'nullable',
                    'address' => 'nullable',
                    'designation' => 'nullable',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $contactbook = new \App\Models\WorkAroundContact();
            $contactbook->name = $request->name;
            $contactbook->company_id = $request->company_id; // Save company_id
            $contactbook->mobile_no = $request->mobile_no;
            $contactbook->address = $request->address;
            $contactbook->designation = $request->designation;
            $contactbook->created_by = \Auth::user()->id;
            $contactbook->save();

            return redirect()->route('contactbook.index')->with('success', __('Contact Book successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function contactbookedit(WorkAroundContact $contactbook)
    {
        $user = \Auth::user();
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
            $contractTypes = \App\Models\CompanyDetails::where('created_by', '=', \Auth::user()->creatorId())->where('company_status', 'Active')->orderBy('name', 'asc')->get()->pluck('name', 'id');
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

        return view('workAround.contactbook.edit', compact('contactbook', 'contractTypes'));
    }

    public function contactbookupdate(Request $request, WorkAroundContact $contactbook)
    {
        if (\Auth::user()->can('edit contactbook')) {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'nullable',
                    'company_id' => 'required|exists:company_details,id',
                    'mobile_no' => 'nullable',
                    'address' => 'nullable',
                    'designation' => 'nullable',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $contactbook->name = $request->name;
            $contactbook->company_id = $request->company_id; // Save company_id
            $contactbook->mobile_no = $request->mobile_no;
            $contactbook->address = $request->address;
            $contactbook->designation = $request->designation;
            $contactbook->created_by = \Auth::user()->id;
            $contactbook->save();

            return redirect()->back()->with('success', __('Contact Book successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function contactbookdelete(WorkAroundContact $contactbook)
    {
        if (\Auth::user()->can('delete contactbook')) {
            $contactbook->delete();

            return redirect()->back()->with('success', __('Contact Book successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getDriversByCompanyAndDepot(Request $request)
    {
        $loggedInUser = \Auth::user();
        $companyId = $request->input('company_id');
        $depotIds = $request->input('depot_ids', []);
        $groupId = $request->input('group_id');

        if (($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) && !$companyId) {
            return response()->json([]);
        }

        $query = \App\Models\Driver::where('driver_status', 'Active');

        if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {

            $query->where('companyName', $companyId);

            if (! empty($depotIds)) {
                $query->whereIn('depot_id', $depotIds);
            }

            if (! empty($groupId)) {
                $query->where('group_id', $groupId);
            }

        }
        // ================= OTHER USERS =================
        else {

            $userDepotIds = is_array($loggedInUser->depot_id)
                ? $loggedInUser->depot_id
                : json_decode($loggedInUser->depot_id, true);

            if (! is_array($userDepotIds)) {
                $userDepotIds = [$loggedInUser->depot_id];
            }

            $driverGroupIds = is_array($loggedInUser->driver_group_id)
                ? $loggedInUser->driver_group_id
                : json_decode($loggedInUser->driver_group_id, true);

            if (! is_array($driverGroupIds)) {
                $driverGroupIds = [$loggedInUser->driver_group_id];
            }

            $query->where('companyName', $loggedInUser->companyname);

            // ✅ Depot logic (priority: selected depot > user depot)
            if (! empty($depotIds)) {
                $query->whereIn('depot_id', $depotIds);
            } else {
                $query->whereIn('depot_id', $userDepotIds);
            }

            // ✅ Group logic (priority: selected group > user group)
            if (! empty($groupId)) {
                $query->where('group_id', $groupId);
            } else {
                $query->whereIn('group_id', $driverGroupIds);
            }
        }

        $drivers = $query->orderBy('name', 'asc')->get(['id', 'name']);

        return response()->json($drivers);
    }

    public function getVehiclesByCompanyAndDepot(Request $request)
    {
        $loggedInUser = \Auth::user();
        $companyId = $request->input('company_id');
        $depotIds = $request->input('depot_ids', []);

        if (($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) && !$companyId) {
            return response()->json([]);
        }

        $query = \App\Models\Vehicles::query();

        $query->join('vehicle_details', 'vehicles.id', '=', 'vehicle_details.vehicle_id');

        if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {

            $query->where('vehicle_details.companyName', $companyId);

            if (! empty($depotIds)) {
                $query->whereIn('vehicle_details.depot_id', $depotIds);
            }

        } else {

            $userDepotIds = is_array($loggedInUser->depot_id)
                ? $loggedInUser->depot_id
                : json_decode($loggedInUser->depot_id, true);

            if (! is_array($userDepotIds)) {
                $userDepotIds = [$loggedInUser->depot_id];
            }

            $vehicleGroupIds = is_array($loggedInUser->vehicle_group_id)
                ? $loggedInUser->vehicle_group_id
                : json_decode($loggedInUser->vehicle_group_id, true);

            if (! is_array($vehicleGroupIds)) {
                $vehicleGroupIds = [$loggedInUser->vehicle_group_id];
            }

            $query->where('vehicle_details.companyName', $loggedInUser->companyname)
                ->whereIn('vehicle_details.depot_id', $userDepotIds)
                ->whereIn('vehicle_details.group_id', $vehicleGroupIds);
        }

        $vehicles = $query->select('vehicles.id', 'vehicles.registrations')->get();

        return response()->json($vehicles);
    }

    public function viewworkaroundindex(Request $request)
    {
        
        if (\Auth::user()->can('manage viewwalkaround')) {
            $loggedInUser = \Auth::user();
            $companyName = $loggedInUser->companyname;

            // Ensure depot IDs are properly handled
            $depotIds = is_array($loggedInUser->depot_id) ? $loggedInUser->depot_id : json_decode($loggedInUser->depot_id, true);
            if (! is_array($depotIds)) {
                $depotIds = [$loggedInUser->depot_id];
            }

            $selectedCompanyId = $request->input('company_id');
            $selectedDepotIds = $request->input('depot_id');
            $selectedDriverId = $request->input('driver_id') ?? request('driver_id');
            $selectedVehicleId = $request->input('vehicle_id') ?? request('vehicle_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $selectedGroupId = $request->input('group_id');
            $filter = $request->input('filter');
            $issueFilter = $request->input('issue_filter');
            
             try {
                $startDateFormatted = $startDate ? \Carbon\Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay()->format('Y-m-d H:i:s') : null;
                $endDateFormatted = $endDate ? \Carbon\Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay()->format('Y-m-d H:i:s') : null;
            } catch (\Exception $e) {
                return redirect()->route('viewworkaround.index')->with('error', __('Invalid date format. Please use yyyy-mm-dd.'));
            }

            
            $walkaround = collect();
            $skipQuery = false;

if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {

    if (
        !$selectedCompanyId &&
        empty($selectedDepotIds) &&
        !$selectedDriverId &&
        !$selectedVehicleId &&
        !$selectedGroupId
    ) {
        $skipQuery = true;
    }
}

if (!$skipQuery) {
            if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
                $walkaround = WorkAroundStore::with(['types', 'driver', 'vehicle.vehicleDetail', 'depot', 'workAroundQuestionAnswers'])
                    ->whereHas('types', function ($q) {
                        $q->where('company_status', 'Active');
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
    return $query->where('company_id', $selectedCompanyId);
})
                    ->when(! empty($selectedDepotIds), function ($query) use ($selectedDepotIds) {
                        return $query->where('operating_centres', $selectedDepotIds);
                    })
                    ->when($selectedDriverId, function ($query) use ($selectedDriverId) {
                        return $query->where('driver_id', $selectedDriverId);
                    })
                    ->when($selectedVehicleId, function ($query) use ($selectedVehicleId) {
                        return $query->where('vehicle_id', $selectedVehicleId);
                    })->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                        return $query->whereHas('driver', function ($q) use ($selectedGroupId) {
                            $q->where('group_id', $selectedGroupId);
                        });
                    })
                    ->when($issueFilter == 'defect', function ($query) {
    return $query->where('defects_count', '>', 0);
})

->when($issueFilter == 'rectified', function ($query) {
    return $query->where('rectified', '>', 0);
})->when($startDateFormatted && $endDateFormatted, function ($query) use ($startDateFormatted, $endDateFormatted) {
    return $query->whereBetween('created_at', [$startDateFormatted, $endDateFormatted]);
})
                    ->orderBy('id', 'desc')
                    ->get();
              } else {

                $driverGroupIds = is_array($loggedInUser->driver_group_id)
                    ? $loggedInUser->driver_group_id
                    : json_decode($loggedInUser->driver_group_id, true);

                if (! is_array($driverGroupIds)) {
                    $driverGroupIds = [$loggedInUser->driver_group_id];
                }

                $vehicleGroupIds = is_array($loggedInUser->vehicle_group_id)
                    ? $loggedInUser->vehicle_group_id
                    : json_decode($loggedInUser->vehicle_group_id, true);

                if (! is_array($vehicleGroupIds)) {
                    $vehicleGroupIds = [$loggedInUser->vehicle_group_id];
                }

                $walkaround = WorkAroundStore::with(['types', 'driver', 'vehicle.vehicleDetail', 'depot', 'workAroundQuestionAnswers'])

                    ->whereHas('types', function ($q) {
                        $q->where('company_status', 'Active');
                    })

                    ->where('company_id', $loggedInUser->companyname)

                    ->whereIn('operating_centres', $depotIds)->when(!empty($selectedDepotIds), function ($query) use ($selectedDepotIds) {
    return $query->where('operating_centres', $selectedDepotIds);
}, function ($query) use ($depotIds) {
    return $query->whereIn('operating_centres', $depotIds);
})
                    ->whereHas('driver', function ($q) use ($driverGroupIds, $selectedGroupId) {

                        if (! empty($selectedGroupId)) {
                            $q->where('group_id', $selectedGroupId);
                        } else {
                        $q->whereIn('group_id', $driverGroupIds);
                        }

                    })

                    ->whereHas('vehicle.vehicleDetail', function ($q) use ($vehicleGroupIds) {
                        $q->whereIn('group_id', $vehicleGroupIds);
                    })

                    ->when($selectedDriverId, function ($query) use ($selectedDriverId) {
                        return $query->where('driver_id', $selectedDriverId);
                    })

                    ->when($selectedVehicleId, function ($query) use ($selectedVehicleId) {
                        return $query->where('vehicle_id', $selectedVehicleId);
                    })
                    ->when($issueFilter == 'defect', function ($query) {
    return $query->where('defects_count', '>', 0);
})

->when($issueFilter == 'rectified', function ($query) {
    return $query->where('rectified', '>', 0);
})->when($startDateFormatted && $endDateFormatted, function ($query) use ($startDateFormatted, $endDateFormatted) {
    return $query->whereBetween('created_at', [$startDateFormatted, $endDateFormatted]);
})

                    ->orderBy('id', 'desc')
                    ->get();
            }
            
}

            if ($filter) {
                switch ($filter) {
                    case 'pending':
                        $walkaround = $walkaround->whereNull('uploaded_date');
                        break;

                    case 'completed':
                        $walkaround = $walkaround->whereNotNull('uploaded_date');
                        break;

                    case 'defects_found':
                        $walkaround = $walkaround->where('defects_count', '>', 0);
                        break;

                    case 'rectified':
                        $walkaround = $walkaround->where('rectified', '>', 0);
                        break;

                    case 'total':
                    default:
                        // show all
                        break;
                }
            }

            // Use company ID instead of name to get details
            $companyDetails = CompanyDetails::where('id', $selectedCompanyId )->where('company_status', 'Active')->first();
            $companies = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->get();

            $depotsQuery = \App\Models\Depot::orderBy('name', 'asc');
            if (! $loggedInUser->hasRole('company') && ! $loggedInUser->hasRole('PTC manager')) {
                $depotsQuery->whereIn('id', $depotIds);
            }
            $depots = $depotsQuery->get();

            $groupsQuery = \App\Models\Group::orderBy('name', 'asc');

            if (! $loggedInUser->hasRole('company') && ! $loggedInUser->hasRole('PTC manager')) {
                $groupsQuery->whereIn('id', $driverGroupIds);
            }

            $groups = $groupsQuery->get();

            $selectedDriverName = null;

            if ($request->driver_id) {
                $selectedDriverName = \App\Models\Driver::where('id', $request->driver_id)->value('name');
            }

           return view('workAround.viewworkAround.index', compact(
            'walkaround',
            'companies',
            'selectedCompanyId',
            'selectedDriverId',
            'selectedVehicleId',
            'depots',
            'groups'
        ));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

        public function viewworkaroundfilter(Request $request)
    {
        if (\Auth::user()->can('manage viewwalkaround')) {
            $loggedInUser = \Auth::user();
            $companyName = $loggedInUser->companyname; // Company name of the logged-in user
            $selectedCompanyId = $request->input('company_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $selectedDepotIds = (array) $request->input('depot_id');
            $selectedDriverId = $request->input('driver_id');
            $selectedVehicleId = $request->input('vehicle_id');
            $selectedGroupId = $request->input('group_id');

            $depotIds = is_array($loggedInUser->depot_id) ? $loggedInUser->depot_id : json_decode($loggedInUser->depot_id, true);

            if (! is_array($depotIds)) {
                $depotIds = [$loggedInUser->depot_id];
            }

            // Retrieve the depots based on selected company
            $depots = collect();
            if ($selectedCompanyId) {
                $depots = \App\Models\Depot::where('companyName', $selectedCompanyId)->orderBy('name', 'asc')->get();
            }

            // Initialize the walkaround variable
            $walkaround = collect(); // Use an empty collection to handle no data case

            // Format dates
            try {
                $startDateFormatted = $startDate ? \Carbon\Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay()->format('Y-m-d H:i:s') : null;
                $endDateFormatted = $endDate ? \Carbon\Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay()->format('Y-m-d H:i:s') : null;
            } catch (\Exception $e) {
                return redirect()->route('viewworkaround.index')->with('error', __('Invalid date format. Please use yyyy-mm-dd.'));
            }

            // Check if 'from date' is greater than 'to date'
            if ($startDate && $endDate && \Carbon\Carbon::parse($startDate)->gt(\Carbon\Carbon::parse($endDate))) {
                return redirect()->route('viewworkaround.index')->with('error', __('From Date cannot be greater than To Date.'));
            }

            $query = WorkAroundStore::with([
                'types', 'driver', 'vehicle.vehicleDetail', 'depot',
                'workAroundQuestionAnswers' => function ($query) {
                    $query->where('status', 'No');
                },
            ])->whereHas('types', function ($q) {
                $q->where('company_status', 'Active'); // Only include assignments where the company is active
            })->orderBy('created_at', 'desc');

            // Handle filtering based on roles
            if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
                // For "company" and "PTC manager" roles, show all company data if no specific company is selected
                if ($selectedCompanyId) {
                    $query->where('company_id', $selectedCompanyId);
                }

                if (! empty($selectedGroupId)) {
                    $query->whereHas('driver', function ($q) use ($selectedGroupId) {
                        $q->where('group_id', $selectedGroupId);
                    });
                }
            } else {
                // For other roles, restrict to the logged-in user's company
                $driverGroupIds = is_array($loggedInUser->driver_group_id) ? $loggedInUser->driver_group_id : json_decode($loggedInUser->driver_group_id, true);

                if (! is_array($driverGroupIds)) {
                    $driverGroupIds = [$loggedInUser->driver_group_id];
                }

                $vehicleGroupIds = is_array($loggedInUser->vehicle_group_id)
                    ? $loggedInUser->vehicle_group_id
                    : json_decode($loggedInUser->vehicle_group_id, true);

                if (! is_array($vehicleGroupIds)) {
                    $vehicleGroupIds = [$loggedInUser->vehicle_group_id];
                }

                $query->where('company_id', $loggedInUser->companyname)
                    ->whereHas('driver', function ($q) use ($driverGroupIds, $selectedGroupId) {

                        if (! empty($selectedGroupId)) {
                            $q->where('group_id', $selectedGroupId);
                        } else {
                            $q->whereIn('group_id', $driverGroupIds);
                        }

                    })
                    ->whereHas('vehicle.vehicleDetail', function ($q) use ($vehicleGroupIds) {
                        $q->whereIn('group_id', $vehicleGroupIds);
                    });

            }

            // Apply depot filter
            if (! empty($selectedDepotIds)) {
                $query->whereIn('operating_centres', $selectedDepotIds);
            }

            if (! empty($selectedVehicleId)) {
                $query->where('vehicle_id', $selectedVehicleId);
            }

            if (! empty($selectedDriverId)) {
                $query->where('driver_id', $selectedDriverId);
            }

            // Apply date filter for all roles
            if ($startDateFormatted && $endDateFormatted) {
                $query->whereBetween('created_at', [$startDateFormatted, $endDateFormatted]);
            }

            $walkaround = $query->get();

            // Count defects for each WorkAroundStore
            foreach ($walkaround as $walkaroundItem) {
                $walkaroundItem->defect_count = $walkaroundItem->workAroundQuestionAnswers->count();
            }

            // Retrieve the company details based on the user's company name
            $companyDetails = CompanyDetails::where('name', $companyName)->first();

            // Retrieve all companies for the dropdown filter (for 'company' and 'PTC manager' roles)
            $companies = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->get();

            $depotsQuery = \App\Models\Depot::orderBy('name', 'asc');
            if (! $loggedInUser->hasRole('company') && ! $loggedInUser->hasRole('PTC manager')) {
                $depotsQuery->whereIn('id', $depotIds);
            }
            $depots = $depotsQuery->get();

            $groupsQuery = \App\Models\Group::orderBy('name', 'asc');

            if (! $loggedInUser->hasRole('company') && ! $loggedInUser->hasRole('PTC manager')) {
                $groupsQuery->whereIn('id', $driverGroupIds);
            }

            $groups = $groupsQuery->get();

            $selectedDriverName = null;

            if ($request->driver_id) {
                $selectedDriverName = \App\Models\Driver::where('id', $request->driver_id)->value('name');
            }

            return view('workAround.viewworkAround.index', compact('walkaround', 'companyDetails', 'companies', 'selectedCompanyId', 'startDate', 'endDate', 'depots', 'selectedDriverId', 'selectedVehicleId', 'depots', 'groups', 'selectedDriverName'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function viewworkaroundshow($id)
    {
        $user = \Auth::user();

        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {

            $walkaround = \App\Models\WorkAroundStore::with([
                'driver',
                'depot',
                'vehicle',
                'vehicle.vehicleDetail',
                'profile',
                'workAroundQuestionAnswers.question',
            ])->findOrFail($id);

        } else {

            // Convert depot ids
            $depotIds = is_array($user->depot_id)
                ? $user->depot_id
                : json_decode($user->depot_id, true);

            if (! is_array($depotIds)) {
                $depotIds = [$user->depot_id];
            }

            // Convert vehicle group ids
            $vehicleGroupIds = is_array($user->vehicle_group_id)
                ? $user->vehicle_group_id
                : json_decode($user->vehicle_group_id, true);

            if (! is_array($vehicleGroupIds)) {
                $vehicleGroupIds = [$user->vehicle_group_id];
            }

            // Convert driver group ids
            $driverGroupIds = is_array($user->driver_group_id)
                ? $user->driver_group_id
                : json_decode($user->driver_group_id, true);

            if (! is_array($driverGroupIds)) {
                $driverGroupIds = [$user->driver_group_id];
            }

            $walkaround = \App\Models\WorkAroundStore::where('id', $id)
                ->where('company_id', $user->companyname)
                ->whereIn('operating_centres', $depotIds)

                ->whereHas('driver', function ($q) use ($driverGroupIds) {
                    $q->whereIn('group_id', $driverGroupIds);
                })

                ->whereHas('vehicle.vehicleDetail', function ($q) use ($vehicleGroupIds) {
                    $q->whereIn('group_id', $vehicleGroupIds);
                })

                ->with([
                    'driver',
                    'depot',
                    'vehicle',
                    'vehicle.vehicleDetail',
                    'profile',
                    'workAroundQuestionAnswers.question',
                ])
                ->first();

            if (! $walkaround) {
                return redirect()->route('viewworkaround.index')
                    ->with('error', __('You are not authorized to view this Walkaround record.'));
            }
        }

        if ($walkaround->types->company_status === 'InActive') {
            return redirect()->route('viewworkaround.index')
                ->with('error', __('This company is currently inactive.'));
        }

        $defectCount = $walkaround->workAroundQuestionAnswers->filter(function ($answer) {
            return $answer->image || $answer->reason;
        })->count();

        return view('workAround.viewworkAround.show', compact('walkaround', 'defectCount'));
    }

    public function getDefectOptions(Request $request)
    {
        $answer = \App\Models\WorkAroundQuestionAnswerStore::with('question')->find($request->answer_id);

        if (! $answer || ! $answer->question) {
            return response()->json(['success' => false, 'message' => 'Defect not found']);
        }

        // Decode JSON format defects_options
        $defectsOptions = json_decode($answer->question->defect_options, true);

        return response()->json([
            'success' => true,
            'defects_options' => json_decode($answer->question->defect_options ?? '[]', true),
            'question_name' => $answer->question->name ?? 'N/A',
            'reason' => $answer->reason ?? 'No reason provided',
        ]);
    }

    public function markRectified(Request $request)
    {
        $request->validate([
            'answer_id' => 'required|exists:work_around_question_answer_stores,id',
            'problem_type' => 'nullable',
            'rectified_images.*' => 'nullable|mimes:jpeg,png,jpg,pdf|max:5000',
            'defect_options' => 'nullable',
            'problem_solution' => 'nullable',
            'third_party' => 'nullable',
            'rectified_date' => 'nullable|date_format:d/m/Y H:i:s',
        ]);

        $answer = WorkAroundQuestionAnswerStore::findOrFail($request->answer_id);
        $answer->problem_type = $request->problem_type;
        $answer->defect_options = $request->defect_options;
        $answer->problem_solution = $request->problem_solution;
        $answer->third_party = $request->third_party;

        $answer->rectified_date = $request->rectified_date
            ? Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $request->rectified_date)->format('d/m/Y H:i:s')
            : now()->format('d/m/Y H:i:s');

        $answer->rectified_username = auth()->user()->username;
        $answer->save();

        if ($request->hasFile('rectified_images')) {
            foreach ($request->file('rectified_images') as $image) {
                $imagePath = $image->store('walkaround_rectified_images', 'local');
                \App\Models\WorkAroundRectifiedImages::create([
                    'answer_id' => $answer->id,
                    'image_path' => $imagePath,
                ]);
            }
        }

        $workAroundStore = \App\Models\WorkAroundStore::where('id', $answer->workaround_store_id)->first();
        if ($workAroundStore) {
            if ($workAroundStore->defects_count > 0) {
                $workAroundStore->update([
                    'defects_count' => max(0, $workAroundStore->defects_count - 1),
                    'rectified' => $workAroundStore->rectified + 1,
                ]);
            }
        } else {
            return response()->json(['status' => 0, 'error' => 'WorkAroundStore record not found'], 404);
        }

        \App\Models\WorkAroundDefectsHistories::create([
            'workaround_question_answer_id' => $answer->id,
            'reason' => $answer->reason,
            'image' => $answer->image,
        ]);

        $answer->update([
            'reason' => null,
            'image' => null,
        ]);

        return redirect()->back()->with('success', 'Defect marked as rectified.');
    }

    public function updateStatus(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'answer_id' => 'required|exists:work_around_question_answer_stores,id',
            'status' => 'required|in:Yes,No',
        ]);

        // Find the answer by ID
        $answer = \App\Models\WorkAroundQuestionAnswerStore::findOrFail($request->answer_id);

        // Update the status
        $answer->status = $request->status;

        // Set image and reason to null
        $answer->image = null;
        $answer->reason = null;

        // Save the updated answer
        $answer->save();

        // Update defect count in WorkAroundStore model
        $walkaround = $answer->workAroundStore; // Assuming you have a relation defined
        $walkaround->defects_count -= 1; // Decrement defect count
        $walkaround->save();

        return redirect()->back()->with('success', 'Status updated successfully.');
    }



    public function exportWalkaroundData(Request $request)
    {
        if (! \Auth::user()->can('manage viewwalkaround')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $loggedInUser = \Auth::user();
        $selectedCompanyId = $request->input('company_id');
        $selectedDepotIds = (array) $request->input('depot_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $selectedDriverId = $request->input('driver_id');
        $selectedVehicleId = $request->input('vehicle_id');
        $selectedGroupId = $request->input('group_id');
        $issueFilter = $request->input('issue_filter');

        $depotIds = is_array($loggedInUser->depot_id) ? $loggedInUser->depot_id : json_decode($loggedInUser->depot_id, true);
        if (! is_array($depotIds)) {
            $depotIds = [$loggedInUser->depot_id]; // Ensure it remains an array
        }

        // Format dates
        try {
            $startDateFormatted = $startDate ? \Carbon\Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay() : null;
            $endDateFormatted = $endDate ? \Carbon\Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay() : null;
        } catch (\Exception $e) {
            return redirect()->route('viewworkaround.index')->with('error', __('Invalid date format. Please use yyyy-mm-dd.'));
        }

        $query = WorkAroundStore::with(['types', 'driver', 'vehicle.vehicleDetail', 'depot', 'workAroundQuestionAnswers'])
            ->whereHas('types', function ($q) {
                $q->where('company_status', 'Active');
            })
            ->when($issueFilter == 'defect', function ($query) {
    return $query->where('defects_count', '>', 0);
})

->when($issueFilter == 'rectified', function ($query) {
    return $query->where('rectified', '>', 0);
})->orderBy('created_at', 'desc');

        // Handle filtering based on roles
        if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
            if ($selectedCompanyId) {
                $query->where('company_id', $selectedCompanyId);
            }

            if (! empty($selectedGroupId)) {
                $query->whereHas('driver', function ($q) use ($selectedGroupId) {
                    $q->where('group_id', $selectedGroupId);
                });
            }
        } else {
            $driverGroupIds = is_array($loggedInUser->driver_group_id)
    ? $loggedInUser->driver_group_id
    : json_decode($loggedInUser->driver_group_id, true);

            if (! is_array($driverGroupIds)) {
                $driverGroupIds = [$loggedInUser->driver_group_id];
            }

            $vehicleGroupIds = is_array($loggedInUser->vehicle_group_id)
                ? $loggedInUser->vehicle_group_id
                : json_decode($loggedInUser->vehicle_group_id, true);

            if (! is_array($vehicleGroupIds)) {
                $vehicleGroupIds = [$loggedInUser->vehicle_group_id];
            }

            $query->where('company_id', $loggedInUser->companyname)
                ->whereIn('operating_centres', ! empty($selectedDepotIds) ? $selectedDepotIds : $depotIds)
                ->whereHas('driver', function ($q) use ($driverGroupIds, $selectedGroupId) {

                    if (! empty($selectedGroupId)) {
                        $q->where('group_id', $selectedGroupId);
                    } else {
                    $q->whereIn('group_id', $driverGroupIds);
                    }

                })
                ->whereHas('vehicle.vehicleDetail', function ($q) use ($vehicleGroupIds) {
                    $q->whereIn('group_id', $vehicleGroupIds);
                });
        }

        if (! empty($selectedDepotIds)) {
            $query->whereIn('operating_centres', $selectedDepotIds);
        }

        if (! empty($selectedDriverId)) {
            $query->where('driver_id', $selectedDriverId);
        }

        if (! empty($selectedVehicleId)) {
            $query->where('vehicle_id', $selectedVehicleId);
        }

        if ($startDateFormatted && $endDateFormatted) {
            $query->whereBetween('created_at', [$startDateFormatted, $endDateFormatted]);
        }

        $walkaroundData = $query->get();

        // Determine company name for file naming
        if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
            $companyName = $selectedCompanyId ? (\App\Models\CompanyDetails::find($selectedCompanyId)->name ?? 'Not Found') : 'Not Found';
        } else {
            $companyName = \App\Models\CompanyDetails::find($loggedInUser->companyname)->name ?? 'Not Found';
        }

        // Generate file name dynamically
        $fileName = $companyName.'_Walkaround_Data.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\WalkaroundExport($walkaroundData), $fileName);
    }

    public function exportWalkaroundPdf(Request $request)
    {
        if (! \Auth::user()->can('manage viewwalkaround')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $loggedInUser = \Auth::user();
        $selectedCompanyId = $request->input('company_id');
        $selectedDepotIds = (array) $request->input('depot_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $selectedDriverId = $request->input('driver_id');
        $selectedVehicleId = $request->input('vehicle_id');
        $selectedGroupId = $request->input('group_id');
        $issueFilter = $request->input('issue_filter');

        $depotIds = is_array($loggedInUser->depot_id) ? $loggedInUser->depot_id : json_decode($loggedInUser->depot_id, true);
        if (! is_array($depotIds)) {
            $depotIds = [$loggedInUser->depot_id]; // Ensure it remains an array
        }

        // Format dates
        try {
            $startDateFormatted = $startDate ? \Carbon\Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay() : null;
            $endDateFormatted = $endDate ? \Carbon\Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay() : null;
        } catch (\Exception $e) {
            return redirect()->route('viewworkaround.index')->with('error', __('Invalid date format. Please use yyyy-mm-dd.'));
        }

        $query = WorkAroundStore::with(['types', 'driver', 'vehicle.vehicleDetail', 'depot', 'workAroundQuestionAnswers'])
            ->whereHas('types', function ($q) {
                $q->where('company_status', 'Active');
            })
            ->when($issueFilter == 'defect', function ($query) {
    return $query->where('defects_count', '>', 0);
})

->when($issueFilter == 'rectified', function ($query) {
    return $query->where('rectified', '>', 0);
})->orderBy('created_at', 'desc');

        // Handle filtering based on roles
        if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
            if ($selectedCompanyId) {
                $query->where('company_id', $selectedCompanyId);
            }
            if (! empty($selectedGroupId)) {
                $query->whereHas('driver', function ($q) use ($selectedGroupId) {
                    $q->where('group_id', $selectedGroupId);
                });
            }
        } else {
            $driverGroupIds = is_array($loggedInUser->driver_group_id)
    ? $loggedInUser->driver_group_id
    : json_decode($loggedInUser->driver_group_id, true);

            if (! is_array($driverGroupIds)) {
                $driverGroupIds = [$loggedInUser->driver_group_id];
            }

            $vehicleGroupIds = is_array($loggedInUser->vehicle_group_id)
                ? $loggedInUser->vehicle_group_id
                : json_decode($loggedInUser->vehicle_group_id, true);

            if (! is_array($vehicleGroupIds)) {
                $vehicleGroupIds = [$loggedInUser->vehicle_group_id];
            }

            $query->where('company_id', $loggedInUser->companyname)
                ->whereIn('operating_centres', ! empty($selectedDepotIds) ? $selectedDepotIds : $depotIds)
                ->whereHas('driver', function ($q) use ($driverGroupIds, $selectedGroupId) {

                    if (! empty($selectedGroupId)) {
                        $q->where('group_id', $selectedGroupId);
                    } else {
                    $q->whereIn('group_id', $driverGroupIds);
                    }

                })
                ->whereHas('vehicle.vehicleDetail', function ($q) use ($vehicleGroupIds) {
                    $q->whereIn('group_id', $vehicleGroupIds);
                });
        }

        if (! empty($selectedDepotIds)) {
            $query->whereIn('operating_centres', $selectedDepotIds);
        }
        if (! empty($selectedDriverId)) {
            $query->where('driver_id', $selectedDriverId);
        }

        if (! empty($selectedVehicleId)) {
            $query->where('vehicle_id', $selectedVehicleId);
        }

        if ($startDateFormatted && $endDateFormatted) {
            $query->whereBetween('created_at', [$startDateFormatted, $endDateFormatted]);
        }

        $walkaroundData = $query->get();

        // Fetch company logo
        $company_logo = \App\Models\Utility::getValByName('company_logo');
        $imagePath = storage_path('/uploads/logo/'.(! empty($company_logo) ? $company_logo : '5-logo-dark.png'));

        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $img = 'data:image/png;base64,'.$imageData;
        } else {
            \Log::error('Image file does not exist: '.$imagePath);
            $img = ''; // Fallback or default image if necessary
        }

        // Determine company name for file naming
        if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
            $companyName = $selectedCompanyId ? (\App\Models\CompanyDetails::find($selectedCompanyId)->name ?? 'Not Found') : 'Not Found';
        } else {
            $companyName = \App\Models\CompanyDetails::find($loggedInUser->companyname)->name ?? 'Not Found';
        }

        // Generate file name dynamically
        $fileName = $companyName.'_Walkaround_Data.pdf';

        // Load PDF View
        $pdf = \PDF::loadView('workAround.viewworkAround.pdftemplate', compact('walkaroundData', 'img'));

        return $pdf->download($fileName);
    }

    public function downloadWorkAround($slug)
    {
        // Decode the slug to get the Walkaround ID
        $id = base64_decode($slug);

        // Ensure the decoded ID is valid
        if (! is_numeric($id)) {
            return redirect()->back()->with('error', __('Invalid Walkaround ID.'));
        }

        // Fetch the Walkaround record by ID
        $workAround = \App\Models\WorkAroundStore::with('workAroundQuestionAnswers')->find($id);

        if (! $workAround) {
            return redirect()->back()->with('error', __('Walkaround not found.'));
        }

        // Fetch defects where either image or reason is not null, and rectified_date is null (Not Rectified)
        $notRectifiedDefects = $workAround->workAroundQuestionAnswers->filter(function ($answer) {
            return (! is_null($answer->image) || ! is_null($answer->reason)) && is_null($answer->rectified_date);
        });

        // Fetch defects where both image and reason are null, but rectified_date is not null (Rectified)
        $rectifiedDefects = $workAround->workAroundQuestionAnswers->filter(function ($answer) {
            return is_null($answer->image) && is_null($answer->reason) && ! is_null($answer->rectified_date);
        });

        // Fetch `Problem Description` from `WorkAroundDefectsHistories` for rectified defects
        foreach ($rectifiedDefects as $defect) {
            // Check if there is a related history entry for this defect
            $history = \App\Models\WorkAroundDefectsHistories::where('workaround_question_answer_id', $defect->id)->first();

            // If found, replace the defect's problem description with the one from the history
            if ($history) {
                $defect->reason = $history->reason;
            }
        }

        // Fetch signature image for rectified defects
        foreach ($rectifiedDefects as $defect) {
            $signaturePath = storage_path($defect->rectified_signature ?? 'default.png');
            if (file_exists($signaturePath)) {
                $signatureData = base64_encode(file_get_contents($signaturePath));
                $defect->rectified_signature = 'data:image/png;base64,'.$signatureData;
            } else {
                $defect->rectified_signature = ''; // Fallback image
            }
        }

        foreach ($rectifiedDefects as $defect) {
            $defect->vehicle_registration = $defect->workAroundStore->vehicle->registrations ?? 'No Registration';
        }

        foreach ($notRectifiedDefects as $defect) {
            $defect->vehicle_registration = $defect->workAroundStore->vehicle->registrations ?? 'No Registration';
        }

        // Get the count of defects
        $defectCount = $workAround->workAroundQuestionAnswers->filter(function ($answer) {
            return $answer->image || $answer->reason;
        })->count();

        $passedChecks = $workAround->workAroundQuestionAnswers->filter(function ($answer) {
            // Check if both image and reason are null and rectified_date is null
            return is_null($answer->image) &&
                   is_null($answer->reason) &&
                   is_null($answer->rectified_date);
        });
        // Fetch other necessary settings
        $settings = \App\Models\Utility::settings();
        $company_logo = \App\Models\Utility::getValByName('company_logo');
        $imagePath = storage_path('/uploads/logo/'.(isset($company_logo) && ! empty($company_logo) ? $company_logo : '5-logo-dark.png'));

        // Check if the image file exists
        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $img = 'data:image/png;base64,'.$imageData;
        } else {
            \Log::error('Image file does not exist: '.$imagePath);
            $img = ''; // Fallback image
        }

        // Fetch signature image
        $signaturePath = storage_path(($workAround->signature ?? 'abcd.png'));
        if (file_exists($signaturePath)) {
            $signatureData = base64_encode(file_get_contents($signaturePath));
            $signatureImg = 'data:image/png;base64,'.$signatureData;
        } else {
            \Log::error('Signature file does not exist: '.$signaturePath);
            $signatureImg = ''; // Fallback image
        }

        // Fetch image
        $image = ('public/uploads/logo/accept.png');
        if (file_exists($image)) {
            $rightImageData = base64_encode(file_get_contents($image));
            $rightImage = 'data:image/png;base64,'.$rightImageData;
        } else {
            \Log::error('rightImage file does not exist: '.$image);
            $rightImage = ''; // Fallback image
        }

        $view = view('workAround.viewworkAround.template', compact('img', 'settings', 'workAround', 'defectCount', 'passedChecks', 'signatureImg', 'rightImage', 'rectifiedDefects', 'notRectifiedDefects'))->render();

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($view)
            ->setOptions(['defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);

        $filename = $workAround->driver->name.'_Walkaround Detail.pdf';

        return $pdf->download($filename);
    }
}
