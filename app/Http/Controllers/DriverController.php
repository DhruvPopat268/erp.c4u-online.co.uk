<?php

namespace App\Http\Controllers;

use App\Imports\DriverImport;
use App\Mail\AutomationEmail;
use App\Mail\DriverUserWithForm;
use App\Models\CompanyDetails;
use App\Models\Cpc;
use App\Models\Dqc;
use App\Models\Driver;
use App\Models\Endorsement;
use App\Models\Entitlement;
use App\Models\TachoCard;
use App\Models\Token;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Calculation\DateTime;
use ZipArchive;

class DriverController extends Controller
{
    //   public function index()
    // {
    //     if (\Auth::user()->can('manage driver')) {
    //         $loggedInUser = \Auth::user();
    //         $companyName = $loggedInUser->companyname; // Company name of the logged-in user

    //         // Retrieve contracts based on the user's role
    //         $contracts = null;
    //         if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
    //             // If the user has the 'company' role, show all data with pagination
    //             $contracts = Driver::with(['types', 'creator', 'driverUser'])->get();
    //         } else {
    //             // If the user doesn't have the 'company' role, only show contracts associated with the user's company with pagination
    //             $contracts = Driver::where('companyname', $companyName)
    //                 ->with(['types', 'creator', 'driverUser'])
    //                 ->get();
    //         }

    //         // Retrieve the company details based on the user's company name
    //         $companyDetails = CompanyDetails::where('name', $companyName)->first();

    //         // Return the view with the contracts and company details
    //         return view('driver.index', compact('contracts', 'companyDetails'));
    //     } else {
    //         // If the user doesn't have the permission, redirect back with an error message
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    public function index(Request $request)
    {
        if (\Auth::user()->can('manage driver')) {
            $loggedInUser = \Auth::user();
            $companyName = $loggedInUser->companyname; // Company name of the logged-in user

            // Handle multiple depot IDs (convert stored JSON to array if needed)
            $depotIds = is_array($loggedInUser->depot_id) ? $loggedInUser->depot_id : json_decode($loggedInUser->depot_id, true);
            if (! is_array($depotIds)) {
                $depotIds = [$loggedInUser->depot_id]; // Ensure it remains an array
            }

            // Handle multiple driver group IDs
            $driverGroupIds = is_array($loggedInUser->driver_group_id)
                ? $loggedInUser->driver_group_id
                : json_decode($loggedInUser->driver_group_id, true);

            if (! is_array($driverGroupIds)) {
                $driverGroupIds = [$loggedInUser->driver_group_id];
            }

            // Retrieve filters from the request
            $selectedCompanyId = $request->input('company_id');
            $selectedDepotIds = (array) $request->input('depot_id'); // Ensures it is always an array
            $selectedDriverStatus = $request->input('driver_status', 'Active');
            $selectedCpcStatus = $request->input('cpc_status');
            $selectedTachoCardStatus = $request->input('tacho_card_status');
            $selectedDriverGroupId = $request->input('group_id');

            // Retrieve contracts based on the user's role
            if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
                // If user is 'company' or 'PTC manager', show all data
                $contracts = Driver::with(['types', 'creator', 'driverUser', 'group', 'depot'])
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })
                    ->when(! empty($selectedDepotIds), function ($query) use ($selectedDepotIds) {
                        return $query->whereIn('depot_id', $selectedDepotIds);
                    })
                    ->when($selectedDriverGroupId, function ($query) use ($selectedDriverGroupId) {
                        return $query->where('group_id', $selectedDriverGroupId);
                    })
                    ->when($selectedDriverStatus, function ($query) use ($selectedDriverStatus) {
                        return $query->where('driver_status', $selectedDriverStatus);
                    })
                    ->when($selectedCpcStatus, function ($query) use ($selectedCpcStatus) {
                        return $query->where('cpc_status', $selectedCpcStatus);  // Apply filter for cpc_status
                    })
                    ->when($selectedTachoCardStatus, function ($query) use ($selectedTachoCardStatus) {
                        return $query->where('tacho_card_status', $selectedTachoCardStatus);  // Apply filter for tacho_card_status
                    })
                    ->get();
            } else {
                if (empty($depotIds) || empty($driverGroupIds)) {
                    $contracts = collect(); // no data
                } else {
                    // If user is NOT 'company' or 'PTC manager', show only their assigned depots
                    $contracts = Driver::where('companyname', $companyName)
                        ->whereIn('depot_id', $depotIds) // Filter by multiple depots
                        ->when(! empty($driverGroupIds), function ($query) use ($driverGroupIds) {
                            $query->whereIn('group_id', $driverGroupIds);
                        })
                        ->with(['types', 'creator', 'driverUser', 'group', 'depot'])
                        ->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->when(! empty($selectedDepotIds), function ($query) use ($selectedDepotIds) {
                            return $query->whereIn('depot_id', $selectedDepotIds);
                        })
                        ->when($selectedDriverGroupId, function ($query) use ($selectedDriverGroupId) {
                            return $query->where('group_id', $selectedDriverGroupId);
                        })
                        ->when($selectedDriverStatus, function ($query) use ($selectedDriverStatus) {
                            return $query->where('driver_status', $selectedDriverStatus);
                        })
                        ->when($selectedCpcStatus, function ($query) use ($selectedCpcStatus) {
                            return $query->where('cpc_status', $selectedCpcStatus);
                        })
                        ->when($selectedTachoCardStatus, function ($query) use ($selectedTachoCardStatus) {
                            return $query->where('tacho_card_status', $selectedTachoCardStatus);
                        })
                        ->get();
                }
            }

            // Retrieve companies for dropdown (All companies for company/PTC manager, otherwise user's company)
            $companiesQuery = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active');
            if (! $loggedInUser->hasRole('company') && ! $loggedInUser->hasRole('PTC manager')) {
                $companiesQuery->where('name', $companyName);
            }
            $companies = $companiesQuery->get();

            // Retrieve depots for dropdown (All depots for company/PTC manager, otherwise user's assigned depots)
            $depotsQuery = \App\Models\Depot::orderBy('name', 'asc');
            if (! $loggedInUser->hasRole('company') && ! $loggedInUser->hasRole('PTC manager')) {
                $depotsQuery->whereIn('id', $depotIds);
            }
            $depots = $depotsQuery->get();

            // Retrieve groups for dropdown
            $groupsQuery = \App\Models\Group::orderBy('name', 'asc');

            if (! $loggedInUser->hasRole('company') && ! $loggedInUser->hasRole('PTC manager')) {
                $groupsQuery->whereIn('id', $driverGroupIds);
            }

            $groups = $groupsQuery->get();

            // Return the view with data
            return view('driver.index', compact('contracts', 'companies', 'depots', 'groups'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getDepotsByCompanyFilter(Request $request)
    {
        $companyId = $request->input('company_id');

        if ($companyId) {
            $depots = \App\Models\Depot::where('companyName', $companyId)->orderBy('name', 'asc')->get();
        } else {
            $depots = []; // Return empty if no company is selected
        }

        return response()->json($depots);
    }

    public function getGroupByCompanyFilter(Request $request)
    {
        $companyId = $request->input('company_id');
        $user = \Auth::user();

        if ($companyId) {

            if ($user->type === 'company' || $user->type === 'PTC manager') {

                // Show all groups of selected company
                $groups = \App\Models\Group::where('company_id', $companyId)
                    ->orderBy('name', 'asc')
                    ->get(['id', 'name']);

            } else {

                // Show only assigned groups
                $groupIds = $user->driver_group_id;

                // Convert to array safely
                if (is_string($groupIds)) {
                    $groupIds = json_decode($groupIds, true) ?? explode(',', $groupIds);
                } elseif (is_int($groupIds)) {
                    $groupIds = [$groupIds];
                } elseif (! is_array($groupIds)) {
                    $groupIds = [];
                }

                $groups = \App\Models\Group::where('company_id', $companyId)
                    ->whereIn('id', $groupIds)
                    ->orderBy('name', 'asc')
                    ->get(['id', 'name']);
            }

        } else {

            $groups = [];
        }

        return response()->json($groups);
    }

    public function create()
    {
        $user = \Auth::user();
        if ($user->can('manage driver')) {

            // Check if the user is a super admin
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

            return view('driver.create', compact('contractTypes'));
        } else {
            // If user doesn't have permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // public function store(Request $request)
    // {
    //     if (\Auth::user()->can('create driver')) {
    //         $validator = \Validator::make(
    //             $request->all(), [
    //                 'name' => 'required',
    //                 'companyName' => 'required',
    //                 'driver_status' => 'required|in:Active,InActive,Archive',
    //                 'ni_number' => 'nullable',
    //                 'post_code' => 'nullable',
    //                 'contact_no' => 'nullable',
    //                 'contact_email' => 'nullable',
    //                 'driver_dob' => 'nullable|date_format:Y-m-d',
    //                 'driver_age' => 'nullable',
    //                 'driver_address' => 'nullable',
    //                 'driver_licence_no' => 'nullable',
    //                 'driver_licence_status' => 'nullable',
    //                 'driver_licence_expiry' => 'nullable|date_format:Y-m-d',
    //                 'cpc_status' => 'nullable',
    //                 'cpc_validto' => 'nullable|date_format:Y-m-d',
    //                 'tacho_card_no' => 'nullable',
    //                 'tacho_card_status' => 'nullable',
    //                 'tacho_card_valid_from' => 'nullable|date_format:Y-m-d',
    //                 'tacho_card_valid_to' => 'nullable|date_format:Y-m-d',
    //                 'lc_check_status' => 'nullable',
    //                 'latest_lc_check' => 'nullable|date_format:Y-m-d',
    //                 'comment' => 'nullable',

    //             ]
    //         );
    //         if ($validator->fails()) {
    //             $messages = $validator->getMessageBag();

    //             return redirect()->back()->with('error', $messages->first());
    //         }

    //         // Format the contact number
    //         $formattedContactNo = $this->formatUKPhoneNumber($request->contact_no);

    //         $driverDOB = $request->driver_dob ? \Carbon\Carbon::createFromFormat('Y-m-d', $request->driver_dob)->format('d/m/Y') : null;
    //         $driverLicenceExpiry = $request->driver_licence_expiry ? \Carbon\Carbon::createFromFormat('Y-m-d', $request->driver_licence_expiry)->format('d/m/Y') : null;
    //         $cpcValidTo = $request->cpc_validto ? \Carbon\Carbon::createFromFormat('Y-m-d', $request->cpc_validto)->format('d/m/Y') : null;
    //         $tachoCardValidTo = $request->tacho_card_valid_to ? \Carbon\Carbon::createFromFormat('Y-m-d', $request->tacho_card_valid_to)->format('d/m/Y') : null;
    //         $tachoCardValidFrom = $request->tacho_card_valid_from ? \Carbon\Carbon::createFromFormat('Y-m-d', $request->tacho_card_valid_from)->format('d/m/Y') : null;
    //         $latestLcCheck = $request->latest_lc_check ? \Carbon\Carbon::createFromFormat('Y-m-d', $request->latest_lc_check)->format('d/m/Y') : null;

    //         $types = new Driver();
    //         $types->name = $request->name;
    //         $types->companyName = $request->companyName;
    //         $types->driver_status = $request->driver_status;
    //         $types->ni_number = $request->ni_number;
    //         $types->post_code = $request->post_code;
    //         $types->contact_no = $formattedContactNo;
    //         $types->contact_email = $request->contact_email;
    //         $types->driver_dob = $driverDOB;
    //         $types->driver_age = $request->driver_age;
    //         $types->driver_address = $request->driver_address;
    //         $types->driver_licence_no = $request->driver_licence_no;
    //         // Check if driver_licence_expiry is null, set driver_licence_status to "-"
    //         $types->driver_licence_expiry = $driverLicenceExpiry;
    //         $types->driver_licence_status = $driverLicenceExpiry ? $request->driver_licence_status : '-';

    //         $types->cpc_validto = $cpcValidTo;
    //         $types->cpc_status = $cpcValidTo ? $request->cpc_status : '-';

    //         $types->tacho_card_no = $request->tacho_card_no ?? null;
    //         $types->tacho_card_valid_from = $tachoCardValidFrom ?? null;
    //         $types->tacho_card_valid_to = $tachoCardValidTo ?? null;
    //         $types->tacho_card_status = $tachoCardValidTo ? $request->tacho_card_status : '-';

    //         $types->lc_check_status = $request->lc_check_status ?? '-';
    //         $types->latest_lc_check = $latestLcCheck;
    //         $types->comment = $request->comment;
    //         $types->created_by = \Auth::user()->id;
    //         $types->save();

    //         return redirect()->route('driver.index')->with('success', __('Driver successfully created.'));
    //     } else {
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    public function getGroupsByCompany($companyId)
    {
        $user = \Auth::user();

        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {

            // company & ptc manager → all groups
            $groups = \App\Models\Group::where('company_id', $companyId)
                ->pluck('name', 'id');

        } else {

            // other users → only assigned groups
            $groupIds = is_array($user->driver_group_id)
                ? $user->driver_group_id
                : json_decode($user->driver_group_id, true);

            $groups = \App\Models\Group::where('company_id', $companyId)
                ->whereIn('id', $groupIds ?? [])
                ->pluck('name', 'id');
        }

        return response()->json($groups);
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create driver')) {
            $validator = \Validator::make(
                $request->all(), [
                    'drivingLicenceNumber' => 'required|string|unique:drivers,driver_licence_no,NULL,id,companyName,'.$request->input('companyName'),
                    'companyName' => 'required|integer|exists:company_details,id',
                    'ni_number' => 'nullable|string',
                    'contact_no' => 'nullable|string',
                    'contact_email' => 'nullable|email',
                    'driver_status' => 'required|in:Active,InActive,Archive',
                    'group_id' => 'required|string',
                    'depot_id' => 'required|string',
                    'depot_access_status' => 'required|in:Yes,No',
                    'driver_dob' => 'nullable|date_format:Y-m-d',
                    'first_names' => 'nullable|string',
                    'last_name' => 'nullable|string',
                    'automation' => 'nullable|in:Yes,No',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $drivingLicenceNumber = strtoupper($request->input('drivingLicenceNumber')); // Convert to uppercase
            $companyDetailId = $request->input('companyName');
            $niNumber = $request->input('ni_number');
            $contactNo = $request->input('contact_no');
            $contactEmail = $request->input('contact_email');
            $driverStatus = $request->input('driver_status');
            $group_id = $request->input('group_id'); // Get the Group modal ID from the request
            $depot_id = $request->input('depot_id');
            $firstName = $request->input('first_names');
            $driverDob = $request->input('driver_dob');
            $lastName = $request->input('last_name');
            $automation = $request->input('automation');
            $depot_access_status = $request->input('depot_access_status');
            $loggedInUserId = \Auth::id();

            // Format the contact number
            $formattedContactNo = $this->formatUKPhoneNumber($contactNo);
            $driverAge = $driverDob ? $this->calculateAgeDriver($driverDob) : null;
            $formattedDriverDob = $this->formatDateToDDMMYYYY($driverDob);
            $fullName = trim($firstName.' '.$lastName);
            $company = CompanyDetails::find($companyDetailId);
            $companyName = $company->name ?? ''; // Assuming the name is in the 'name' column

            // Extract the first 3 characters of the name
            $namePart = strtolower(substr($firstName, 0, 3));
            // Generate 3 random alphanumeric characters
            $lastNamePart = strtolower(substr($lastName, 0, 3));
            $companyPart = strtolower(substr($companyName, 0, 3));
            [$day, $month] = explode('/', $formattedDriverDob);
            // Create the username
            $username = $lastNamePart.$companyPart.$day.$month;
            // Generate the password
            $firstNamePart = substr($firstName, 0, 4); // First 4 characters of the name
            $lastTwoOfLicence = substr($drivingLicenceNumber, -2); // Last 2 characters of the licence number
            // $password = ucfirst(strtolower($firstNamePart)) . '@' . strtolower($lastTwoOfLicence); // e.g., 'Jasv@me'
            $password = 12345;
            // Hash the password
            $hashedPassword = bcrypt($password);

            $driver = Driver::create(
                [
                    'driver_licence_no' => $drivingLicenceNumber,
                    'companyName' => $companyDetailId,
                    'ni_number' => $niNumber,
                    'contact_no' => $formattedContactNo,
                    'contact_email' => $contactEmail,
                    'driver_dob' => $formattedDriverDob,
                    'driver_age' => $driverAge,
                    'name' => $fullName,
                    'first_names' => $firstName,
                    'last_name' => $lastName,
                    'created_by' => $loggedInUserId, // Add created_by
                    'group_id' => $group_id, // Save the Group modal ID
                    'depot_id' => $depot_id,
                    'automation' => $automation,
                    'depot_access_status' => $depot_access_status,

                ]
            );

            // Save username and password in DriverUser model
            \App\Models\DriverUser::updateOrCreate(
                ['driver_id' => $driver->id],
                [
                    'username' => $username,
                    'password' => $hashedPassword,
                    'created_by' => $loggedInUserId,
                ]
            );

            try {
                // Attempt to send the email
                \Mail::to($contactEmail)->send(new DriverUserWithForm($username, $password, $contactEmail, $driver));
                \Log::info('Email sent successfully', ['email' => $contactEmail]);
            } catch (\Exception $e) {
                // Log the error and the email that failed
                \Log::error('Failed to send email', [
                    'email' => $contactEmail,
                    'error' => $e->getMessage(),
                ]);
            }

                return redirect()->route('driver.index')->with('success', __('Driver successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    //       public function apistore(Request $request)
    //     {
    //         if (\Auth::user()->can('create driver')) {
    //             $request->validate([
    //                 'drivingLicenceNumber' => 'required|string',
    //                 'companyName' => 'required|integer|exists:company_details,id',
    //                 'ni_number' => 'nullable|string',
    //                 'contact_no' => 'nullable|string',
    //                 'contact_email' => 'nullable|email',
    //                 'driver_status' => 'required|in:Active,InActive,Archive',
    //                                 'group_id' => 'required|string', // Validate group_id if necessary

    //             ]);

    //             $drivingLicenceNumber = $request->input('drivingLicenceNumber');
    //             $companyDetailId = $request->input('companyName');
    //             $niNumber = $request->input('ni_number');
    //             $contactNo = $request->input('contact_no');
    //             $contactEmail = $request->input('contact_email');
    //             $driverStatus = $request->input('driver_status');
    //                         $group_id = $request->input('group_id'); // Get the Group modal ID from the request
    //             $token = $this->getToken();
    //             $loggedInUserId = \Auth::id();

    //             // Format the contact number
    //             $formattedContactNo = $this->formatUKPhoneNumber($contactNo);

    //             $response = Http::withHeaders([
    //                 'x-api-key' => 'HUxGk2P6SR7qOPb6LUoMrQUYG0oQXRG3CBs1QyZ2',
    //                 'Authorization' => $token,
    //             ])->post('https://driver-vehicle-licensing.api.gov.uk/full-driver-enquiry/v1/driving-licences/retrieve', [
    //                 'drivingLicenceNumber' => $drivingLicenceNumber,
    //                 'includeCPC' => true,
    //                 'includeTacho' => true,
    //                 'acceptPartialResponse' => 'true',
    //             ]);

    //             if ($response->successful()) {
    //                 $data = $response->json();

    //                 // Calculate age from date of birth
    //                 $driverDob = $data['driver']['dateOfBirth'] ?? null;
    //                 $driverAge = $driverDob ? $this->calculateAgeDriver($driverDob) : null;

    //                 // Format dates
    //                 $formattedDriverDob = $this->formatDateToDDMMYYYY($driverDob);
    //                 $formattedFromDate = $this->formatDateToDDMMYYYY($data['entitlement'][0]['fromDate'] ?? null);
    //                 $formattedExpiryDate = $this->formatDateToDDMMYYYY($data['entitlement'][0]['expiryDate'] ?? null);
    //                 $formattedValidFromDate = $this->formatDateToDDMMYYYY($data['token']['validFromDate'] ?? null);
    //                 $formattedValidToDate = $this->formatDateToDDMMYYYY($data['token']['validToDate'] ?? null);
    //                 $formattedCardExpiryDate = $this->formatDateToDDMMYYYY($data['holder']['tachoCards'][0]['cardExpiryDate'] ?? null);
    //                 $formattedCardStartOfValidityDate = $this->formatDateToDDMMYYYY($data['holder']['tachoCards'][0]['cardStartOfValidityDate'] ?? null);
    //                 // $formattedLgvValidTo = $this->formatDateToDDMMYYYY($data['cpc']['cpcs'][0]['lgvValidTo'] ?? null);
    //                 // Find the latest CPC date
    //                 $latestLgvValidTo = null;
    //                 if (isset($data['cpc']) && is_array($data['cpc']['cpcs'])) {
    //                     foreach ($data['cpc']['cpcs'] as $cpc) {
    //                         $lgvValidTo = $cpc['lgvValidTo'] ?? null;
    //                         if ($lgvValidTo && ($latestLgvValidTo === null || $lgvValidTo > $latestLgvValidTo)) {
    //                             $latestLgvValidTo = $lgvValidTo;
    //                         }
    //                     }
    //                 }
    //                 $formattedLgvValidTo = $this->formatDateToDDMMYYYY($latestLgvValidTo);

    //                 $formattedIssueDate = $this->formatDateToDDMMYYYY($data['dqc']['dqcs'][0]['issueDate'] ?? null);

    //                 $fullName = trim(($data['driver']['firstNames'] ?? '').' '.($data['driver']['lastName'] ?? ''));
    //                  $lastName = $data['driver']['lastName'] ?? '';

    //                   $company = CompanyDetails::find($companyDetailId);
    //             $companyName = $company->name ?? ''; // Assuming the name is in the 'name' column

    //                 // Concatenate address line1 and line5
    //                 $addressLine1 = $data['driver']['address']['unstructuredAddress']['line1'] ?? '';
    //                 $addressLine2 = $data['driver']['address']['unstructuredAddress']['line2'] ?? '';
    //                 $addressLine3 = $data['driver']['address']['unstructuredAddress']['line3'] ?? '';
    //                 $addressLine4 = $data['driver']['address']['unstructuredAddress']['line4'] ?? '';
    //                 $addressLine5 = $data['driver']['address']['unstructuredAddress']['line5'] ?? '';
    //                 $fullAddress = trim($addressLine1.' '.$addressLine2.' '.$addressLine3.' '.$addressLine4.' '.$addressLine5);

    //                 // Determine the licence check interval based on endorsements
    //                 $penaltyPoints = 0;
    //                 if (isset($data['endorsements']) && is_array($data['endorsements'])) {
    //                     foreach ($data['endorsements'] as $endorsement) {
    //                         if (isset($endorsement['penaltyPoints'])) {
    //                             $penaltyPoints = max($penaltyPoints, $endorsement['penaltyPoints']);
    //                         }
    //                     }
    //                 }
    //                 $checkInterval = $this->calculateCheckInterval($penaltyPoints);

    //                 // Get current date and time in UK timezone
    //                 $latestLcCheck = Carbon::now('Europe/London')->format('d/m/Y H:i:s');

    //                 // Calculate next_lc_check
    //                // Calculate next_lc_check
    // $nextLcValidUntil = null;
    // if ($penaltyPoints < 5) {
    //     $nextLcValidUntil = Carbon::createFromFormat('d/m/Y H:i:s', $latestLcCheck)
    //         ->addMonths(3)
    //         ->format('d/m/Y');
    // } else {
    //     $nextLcValidUntil = Carbon::createFromFormat('d/m/Y H:i:s', $latestLcCheck)
    //         ->addMonths()
    //         ->format('d/m/Y');
    // }

    // // Generate username and password
    // $name = preg_replace('/\s+/', '', $fullName); // Remove spaces from name

    // // Extract the first 3 characters of the name
    // $namePart = strtolower(substr($name, 0, 3));

    // // Generate 3 random alphanumeric characters
    // $lastNamePart = strtolower(substr($lastName, 0, 3));

    // $companyPart = strtolower(substr($companyName, 0, 3));

    // list($day, $month) = explode('/', $formattedDriverDob);

    // // Create the username
    // $username = $lastNamePart . $companyPart . $day . $month;

    // // Generate the password
    // $firstNamePart = substr($name, 0, 4); // First 4 characters of the name
    // $lastTwoOfLicence = substr($drivingLicenceNumber, -2); // Last 2 characters of the licence number
    // // $password = ucfirst(strtolower($firstNamePart)) . '@' . strtolower($lastTwoOfLicence); // e.g., 'Jasv@me'

    //                 $password = 12345;

    // // Hash the password
    // $hashedPassword = bcrypt($password);

    //                 // Save driver details
    //                 $driver = Driver::updateOrCreate(
    //                     ['driver_licence_no' => $data['driver']['drivingLicenceNumber'],'companyName' => $companyDetailId,],

    //                     [

    //                         'ni_number' => $niNumber,
    //                         'contact_no' => $formattedContactNo,
    //                         'contact_email' => $contactEmail,
    //                         'driver_age' => $driverAge,
    //                         'name' => $fullName,
    //                         'last_name' => $data['driver']['lastName'] ?? null,
    //                         'gender' => $data['driver']['gender'] ?? null,
    //                         'first_names' => $data['driver']['firstNames'] ?? null,
    //                         'driver_dob' => $formattedDriverDob,
    //                         'driver_address' => $fullAddress,
    //                         'address_line1' => $addressLine1,
    //                         'address_line2' => $addressLine2,
    //                         'address_line3' => $addressLine3,
    //                         'address_line4' => $addressLine4,
    //                         'address_line5' => $addressLine5,
    //                         'driver_status' => $driverStatus,
    //                         'post_code' => $data['driver']['address']['unstructuredAddress']['postcode'] ?? null,
    //                         'licence_type' => $data['licence']['type'] ?? null,
    //                         'driver_licence_status' => $data['licence']['status'] ?? null,
    //                         'tacho_card_no' => $data['holder']['tachoCards'][0]['cardNumber'] ?? null,
    //                         'tacho_card_valid_to' => $formattedCardExpiryDate,
    //                         'tacho_card_valid_from' => $formattedCardStartOfValidityDate,
    //                         'token_issue_number' => $data['token']['issueNumber'] ?? null,
    //                         'token_valid_from_date' => $formattedValidFromDate,
    //                         'driver_licence_expiry' => $formattedValidToDate,
    //                         'cpc_validto' => $formattedLgvValidTo, // Save latest LGV valid to date
    //                         'dqc_issue_date' => $formattedIssueDate,
    //                         'endorsement_penalty_points' => $data['endorsements'][0]['penaltyPoints'] ?? null,
    //                         'endorsement_offence_code' => $data['endorsements'][0]['offenceCode'] ?? null,
    //                         'endorsement_offence_legal_literal' => $data['endorsements'][0]['offenceLegalLiteral'] ?? null,
    //                         'endorsement_offence_date' => $data['endorsements'][0]['offenceDate'] ?? null,
    //                         'endorsement_conviction_date' => $data['endorsements'][0]['convictionDate'] ?? null,
    //                         'endorsements' => json_encode($data['endorsements'] ?? []), // Save endorsements as JSON
    //                         'current_licence_check_interval' => $checkInterval,
    //                         'latest_lc_check' => $latestLcCheck, // Add the latest license check date and time
    //                         'next_lc_check' => $nextLcValidUntil,
    //                         'created_by' => $loggedInUserId, // Add created_by
    //                                                 'group_id' => $group_id, // Save the Group modal ID

    //                     ]
    //                 );

    //                 // Save entitlements
    //                 foreach ($data['entitlement'] ?? [] as $entitlement) {
    //                     // Convert the restrictions array to JSON
    //                     $restrictions = json_encode($entitlement['restrictions'] ?? []);

    //                     // Ensure unique dates are assigned
    //                     $fromDate = isset($entitlement['fromDate']) ? $this->formatDateToDDMMYYYY($entitlement['fromDate']) : null;
    //                     $expiryDate = isset($entitlement['expiryDate']) ? $this->formatDateToDDMMYYYY($entitlement['expiryDate']) : null;

    //                     // Use the correct from_date and expiry_date for each entitlement
    //                     Entitlement::updateOrCreate(
    //                         [
    //                             'driver_id' => $driver->id,
    //                             'category_code' => $entitlement['categoryCode'],
    //                             'from_date' => $fromDate,
    //                             'expiry_date' => $expiryDate,
    //                         ],
    //                         [
    //                             'category_legal_literal' => $entitlement['categoryLegalLiteral'] ?? null,
    //                             'category_type' => $entitlement['categoryType'] ?? null,
    //                             'restrictions' => $restrictions,
    //                         ]
    //                     );
    //                 }

    //             // Save username and password in DriverUser model
    //               \App\Models\DriverUser::updateOrCreate(
    //                     ['driver_id' => $driver->id],
    //                     [
    //                         'username' => $username,
    //                         'password' => $hashedPassword ,
    //                     ]
    //                 );

    //                 // Increment API call count for the company
    //                 $company = CompanyDetails::find($companyDetailId);
    //                 if ($company) {
    //                     $company->increment('api_call_count');
    //                 }

    //          // Log the data
    //                 \App\Models\DriverAPILog::create([
    //                     'companyName' => $companyDetailId,
    //                     'created' => $loggedInUserId,
    //                     'last_lc_check' => $latestLcCheck,
    //                     'licence_no' => $data['driver']['drivingLicenceNumber'],
    //                 ]);

    //                  \Log::info('Sending email to contact.', [
    //                     'contact_email' => $contactEmail,
    //                     'username' => $username,
    //                 ]);

    //                 // Send email to the contact email with username and password
    //                 \Mail::to($contactEmail)->send(new \App\Mail\DriverUser($username, $password, $contactEmail));

    //                 return redirect()->route('driver.index')->with('success', __('Driver successfully created.'));
    //             } else {
    //                 // Get error message from the API response
    //                 $errorMessage = $response->json('error.message', 'For the given drivingLicenceNumber field, no driver record could be found.');

    //                 // Redirect with error message
    //                 return redirect()->route('driver.index')
    //                     ->withErrors(['api_error' => $errorMessage]);

    //             }

    //             return response()->json(['error' => 'Failed to retrieve driver data'], 400);
    //         } else {
    //             return redirect()->back()->with('error', __('Permission denied.'));
    //         }
    //     }

    //           public function store(Request $request)
    //     {
    //         if (\Auth::user()->can('create driver')) {
    //             $request->validate([
    //                 'drivingLicenceNumber' => 'required|string',
    //                 'companyName' => 'required|integer|exists:company_details,id',
    //                 'ni_number' => 'nullable|string',
    //                 'contact_no' => 'nullable|string',
    //                 'contact_email' => 'nullable|email',
    //                 'driver_status' => 'required|in:Active,InActive,Archive',
    //                                 'group_id' => 'required|string', // Validate group_id if necessary
    //                                  'automation' => 'required|in:Yes,No',
    //             ]);

    //             $drivingLicenceNumber = $request->input('drivingLicenceNumber');
    //             $companyDetailId = $request->input('companyName');
    //             $niNumber = $request->input('ni_number');
    //             $contactNo = $request->input('contact_no');
    //             $contactEmail = $request->input('contact_email');
    //             $driverStatus = $request->input('driver_status');
    //                         $group_id = $request->input('group_id'); // Get the Group modal ID from the request
    //                          $automation = $request->input('automation');
    //             $token = $this->getToken();
    //             $loggedInUserId = \Auth::id();

    //             // Format the contact number
    //             $formattedContactNo = $this->formatUKPhoneNumber($contactNo);

    //             $response = Http::withHeaders([
    //                 'x-api-key' => 'HUxGk2P6SR7qOPb6LUoMrQUYG0oQXRG3CBs1QyZ2',
    //                 'Authorization' => $token,
    //             ])->post('https://driver-vehicle-licensing.api.gov.uk/full-driver-enquiry/v1/driving-licences/retrieve', [
    //                 'drivingLicenceNumber' => $drivingLicenceNumber,
    //                 'includeCPC' => true,
    //                 'includeTacho' => true,
    //                 'acceptPartialResponse' => 'true',
    //             ]);

    //             if ($response->successful()) {
    //                 $data = $response->json();

    //                 // Calculate age from date of birth
    //                 $driverDob = $data['driver']['dateOfBirth'] ?? null;
    //                 $driverAge = $driverDob ? $this->calculateAgeDriver($driverDob) : null;

    //                 // Format dates
    //                 $formattedDriverDob = $this->formatDateToDDMMYYYY($driverDob);
    //                 $formattedFromDate = $this->formatDateToDDMMYYYY($data['entitlement'][0]['fromDate'] ?? null);
    //                 $formattedExpiryDate = $this->formatDateToDDMMYYYY($data['entitlement'][0]['expiryDate'] ?? null);
    //                 $formattedValidFromDate = $this->formatDateToDDMMYYYY($data['token']['validFromDate'] ?? null);
    //                 $formattedValidToDate = $this->formatDateToDDMMYYYY($data['token']['validToDate'] ?? null);
    //                 $formattedCardExpiryDate = $this->formatDateToDDMMYYYY($data['holder']['tachoCards'][0]['cardExpiryDate'] ?? null);
    //                 $formattedCardStartOfValidityDate = $this->formatDateToDDMMYYYY($data['holder']['tachoCards'][0]['cardStartOfValidityDate'] ?? null);
    //                 // $formattedLgvValidTo = $this->formatDateToDDMMYYYY($data['cpc']['cpcs'][0]['lgvValidTo'] ?? null);
    //                 // Find the latest CPC date
    //                 $latestLgvValidTo = null;
    //                 if (isset($data['cpc']) && is_array($data['cpc']['cpcs'])) {
    //                     foreach ($data['cpc']['cpcs'] as $cpc) {
    //                         $lgvValidTo = $cpc['lgvValidTo'] ?? null;
    //                         if ($lgvValidTo && ($latestLgvValidTo === null || $lgvValidTo > $latestLgvValidTo)) {
    //                             $latestLgvValidTo = $lgvValidTo;
    //                         }
    //                     }
    //                 }
    //                 $formattedLgvValidTo = $this->formatDateToDDMMYYYY($latestLgvValidTo);

    //                 $formattedIssueDate = $this->formatDateToDDMMYYYY($data['dqc']['dqcs'][0]['issueDate'] ?? null);

    //                 $fullName = trim(($data['driver']['firstNames'] ?? '').' '.($data['driver']['lastName'] ?? ''));
    //                  $lastName = $data['driver']['lastName'] ?? '';

    //                   $company = CompanyDetails::find($companyDetailId);
    //             $companyName = $company->name ?? ''; // Assuming the name is in the 'name' column

    //                 // Concatenate address line1 and line5
    //                 $addressLine1 = $data['driver']['address']['unstructuredAddress']['line1'] ?? '';
    //                 $addressLine2 = $data['driver']['address']['unstructuredAddress']['line2'] ?? '';
    //                 $addressLine3 = $data['driver']['address']['unstructuredAddress']['line3'] ?? '';
    //                 $addressLine4 = $data['driver']['address']['unstructuredAddress']['line4'] ?? '';
    //                 $addressLine5 = $data['driver']['address']['unstructuredAddress']['line5'] ?? '';
    //                 $fullAddress = trim($addressLine1.' '.$addressLine2.' '.$addressLine3.' '.$addressLine4.' '.$addressLine5);

    //                 // Determine the licence check interval based on endorsements
    //                 $penaltyPoints = 0;
    //                 if (isset($data['endorsements']) && is_array($data['endorsements'])) {
    //                     foreach ($data['endorsements'] as $endorsement) {
    //                         if (isset($endorsement['penaltyPoints'])) {
    //                             $penaltyPoints = max($penaltyPoints, $endorsement['penaltyPoints']);
    //                         }
    //                     }
    //                 }
    //                 $checkInterval = $this->calculateCheckInterval($penaltyPoints);

    //                 // Get current date and time in UK timezone
    //                 $latestLcCheck = Carbon::now('Europe/London')->format('d/m/Y H:i:s');

    //                 // Calculate next_lc_check
    // Calculate next_lc_check
    // $nextLcValidUntil = null;
    // if ($penaltyPoints < 5) {
    //     $nextLcValidUntil = Carbon::createFromFormat('d/m/Y H:i:s', $latestLcCheck)
    //         ->addMonths(3)
    //         ->format('d/m/Y');
    // } else {
    //     $nextLcValidUntil = Carbon::createFromFormat('d/m/Y H:i:s', $latestLcCheck)
    //         ->addMonths()
    //         ->format('d/m/Y');
    // }

    // // Generate username and password
    // $name = preg_replace('/\s+/', '', $fullName); // Remove spaces from name

    // // Extract the first 3 characters of the name
    // $namePart = strtolower(substr($name, 0, 3));

    // // Generate 3 random alphanumeric characters
    // $lastNamePart = strtolower(substr($lastName, 0, 3));

    // $companyPart = strtolower(substr($companyName, 0, 3));

    // list($day, $month) = explode('/', $formattedDriverDob);

    // // Create the username
    // $username = $namePart . $lastNamePart . $companyPart . $day . $month;

    // // Generate the password
    // $firstNamePart = substr($name, 0, 4); // First 4 characters of the name
    // $lastTwoOfLicence = substr($drivingLicenceNumber, -2); // Last 2 characters of the licence number
    // $password = ucfirst(strtolower($firstNamePart)) . '@' . strtolower($lastTwoOfLicence); // e.g., 'Jasv@me'

    // // Hash the password
    // $hashedPassword = bcrypt($password);

    //                 // Save driver details
    //                 $driver = Driver::updateOrCreate(
    //                     ['driver_licence_no' => $data['driver']['drivingLicenceNumber'],'companyName' => $companyDetailId,],

    //                     [

    //                         'ni_number' => $niNumber,
    //                         'contact_no' => $formattedContactNo,
    //                         'contact_email' => $contactEmail,
    //                         'driver_age' => $driverAge,
    //                         'name' => $fullName,
    //                         'last_name' => $data['driver']['lastName'] ?? null,
    //                         'gender' => $data['driver']['gender'] ?? null,
    //                         'first_names' => $data['driver']['firstNames'] ?? null,
    //                         'driver_dob' => $formattedDriverDob,
    //                         'driver_address' => $fullAddress,
    //                         'address_line1' => $addressLine1,
    //                         'address_line2' => $addressLine2,
    //                         'address_line3' => $addressLine3,
    //                         'address_line4' => $addressLine4,
    //                         'address_line5' => $addressLine5,
    //                         'driver_status' => $driverStatus,
    //                         'post_code' => $data['driver']['address']['unstructuredAddress']['postcode'] ?? null,
    //                         'licence_type' => $data['licence']['type'] ?? null,
    //                         'driver_licence_status' => $data['licence']['status'] ?? null,
    //                         'tacho_card_no' => $data['holder']['tachoCards'][0]['cardNumber'] ?? null,
    //                         'tacho_card_valid_to' => $formattedCardExpiryDate,
    //                         'tacho_card_valid_from' => $formattedCardStartOfValidityDate,
    //                         'token_issue_number' => $data['token']['issueNumber'] ?? null,
    //                         'token_valid_from_date' => $formattedValidFromDate,
    //                         'driver_licence_expiry' => $formattedValidToDate,
    //                         'cpc_validto' => $formattedLgvValidTo, // Save latest LGV valid to date
    //                         'dqc_issue_date' => $formattedIssueDate,
    //                         'endorsement_penalty_points' => $data['endorsements'][0]['penaltyPoints'] ?? null,
    //                         'endorsement_offence_code' => $data['endorsements'][0]['offenceCode'] ?? null,
    //                         'endorsement_offence_legal_literal' => $data['endorsements'][0]['offenceLegalLiteral'] ?? null,
    //                         'endorsement_offence_date' => $data['endorsements'][0]['offenceDate'] ?? null,
    //                         'endorsement_conviction_date' => $data['endorsements'][0]['convictionDate'] ?? null,
    //                         'endorsements' => json_encode($data['endorsements'] ?? []), // Save endorsements as JSON
    //                         'current_licence_check_interval' => $checkInterval,
    //                         'latest_lc_check' => $latestLcCheck, // Add the latest license check date and time
    //                         'next_lc_check' => $nextLcValidUntil,
    //                         'created_by' => $loggedInUserId, // Add created_by
    //                                                 'group_id' => $group_id, // Save the Group modal ID
    //                                                 'automation' => $automation,

    //                     ]
    //                 );

    //                             // Save driver details
    //                 $dupliacatdriver = \App\Models\DuplicateDriver::create(

    //                     [
    //                         'driver_modal_id' => $driver->id,
    //                         'driver_licence_no' => $data['driver']['drivingLicenceNumber'],
    //                         'companyName' => $companyDetailId,
    //                         'ni_number' => $niNumber,
    //                         'contact_no' => $formattedContactNo,
    //                         'contact_email' => $contactEmail,
    //                         'driver_age' => $driverAge,
    //                         'name' => $fullName,
    //                         'last_name' => $data['driver']['lastName'] ?? null,
    //                         'gender' => $data['driver']['gender'] ?? null,
    //                         'first_names' => $data['driver']['firstNames'] ?? null,
    //                         'driver_dob' => $formattedDriverDob,
    //                         'driver_address' => $fullAddress,
    //                         'address_line1' => $addressLine1,
    //                         'address_line2' => $addressLine2,
    //                         'address_line3' => $addressLine3,
    //                         'address_line4' => $addressLine4,
    //                         'address_line5' => $addressLine5,
    //                         'driver_status' => $driverStatus,
    //                         'post_code' => $data['driver']['address']['unstructuredAddress']['postcode'] ?? null,
    //                         'licence_type' => $data['licence']['type'] ?? null,
    //                         'driver_licence_status' => $data['licence']['status'] ?? null,
    //                         'tacho_card_no' => $data['holder']['tachoCards'][0]['cardNumber'] ?? null,
    //                         'tacho_card_valid_to' => $formattedCardExpiryDate,
    //                         'tacho_card_valid_from' => $formattedCardStartOfValidityDate,
    //                         'token_issue_number' => $data['token']['issueNumber'] ?? null,
    //                         'token_valid_from_date' => $formattedValidFromDate,
    //                         'driver_licence_expiry' => $formattedValidToDate,
    //                         'cpc_validto' => $formattedLgvValidTo, // Save latest LGV valid to date
    //                         'dqc_issue_date' => $formattedIssueDate,
    //                         'endorsement_penalty_points' => $data['endorsements'][0]['penaltyPoints'] ?? null,
    //                         'endorsement_offence_code' => $data['endorsements'][0]['offenceCode'] ?? null,
    //                         'endorsement_offence_legal_literal' => $data['endorsements'][0]['offenceLegalLiteral'] ?? null,
    //                         'endorsement_offence_date' => $data['endorsements'][0]['offenceDate'] ?? null,
    //                         'endorsement_conviction_date' => $data['endorsements'][0]['convictionDate'] ?? null,
    //                         'endorsements' => json_encode($data['endorsements'] ?? []), // Save endorsements as JSON
    //                         'current_licence_check_interval' => $checkInterval,
    //                         'latest_lc_check' => $latestLcCheck, // Add the latest license check date and time
    //                        'next_lc_check' => $nextLcValidUntil,
    //                         'created_by' => $loggedInUserId, // Add created_by
    //                                                 'group_id' => $group_id, // Save the Group modal ID
    //                                                 'automation' => $automation,

    //                     ]
    //                 );

    //                 // Save entitlements
    //                 foreach ($data['entitlement'] ?? [] as $entitlement) {
    //                     // Convert the restrictions array to JSON
    //                     $restrictions = json_encode($entitlement['restrictions'] ?? []);

    //                     // Ensure unique dates are assigned
    //                     $fromDate = isset($entitlement['fromDate']) ? $this->formatDateToDDMMYYYY($entitlement['fromDate']) : null;
    //                     $expiryDate = isset($entitlement['expiryDate']) ? $this->formatDateToDDMMYYYY($entitlement['expiryDate']) : null;

    //                     // Use the correct from_date and expiry_date for each entitlement
    //                     Entitlement::updateOrCreate(
    //                         [
    //                             'driver_id' => $driver->id,
    //                             'category_code' => $entitlement['categoryCode'],
    //                             'from_date' => $fromDate,
    //                             'expiry_date' => $expiryDate,
    //                         ],
    //                         [
    //                             'category_legal_literal' => $entitlement['categoryLegalLiteral'] ?? null,
    //                             'category_type' => $entitlement['categoryType'] ?? null,
    //                             'restrictions' => $restrictions,
    //                         ]
    //                     );
    //                 }

    //                                  // Save entitlements
    //                  foreach ($data['entitlement'] ?? [] as $entitlement) {
    //                     // Convert the restrictions array to JSON
    //                     $restrictions = json_encode($entitlement['restrictions'] ?? []);

    //                     // Ensure unique dates are assigned
    //                     $fromDate = isset($entitlement['fromDate']) ? $this->formatDateToDDMMYYYY($entitlement['fromDate']) : null;
    //                     $expiryDate = isset($entitlement['expiryDate']) ? $this->formatDateToDDMMYYYY($entitlement['expiryDate']) : null;

    //                     // Use the correct from_date and expiry_date for each entitlement
    //                     \App\Models\DuplicateEntitlement::create(
    //                       [
    //                             'driver_id' => $driver->id,
    //                             'category_code' => $entitlement['categoryCode'],
    //                             'from_date' => $fromDate,
    //                             'expiry_date' => $expiryDate,
    //                             'category_legal_literal' => $entitlement['categoryLegalLiteral'] ?? null,
    //                             'category_type' => $entitlement['categoryType'] ?? null,
    //                             'restrictions' => $restrictions,
    //                         ]
    //                     );
    //                 }

    //             // Save username and password in DriverUser model
    //               \App\Models\DriverUser::updateOrCreate(
    //                     ['driver_id' => $driver->id],
    //                     [
    //                         'username' => $username,
    //                         'password' => $hashedPassword ,
    //                     ]
    //                 );

    //                 // Increment API call count for the company
    //                 $company = CompanyDetails::find($companyDetailId);
    //                 if ($company) {
    //                     $company->increment('api_call_count');
    //                 }

    //          // Log the data
    //                 \App\Models\DriverAPILog::create([
    //                     'companyName' => $companyDetailId,
    //                     'created' => $loggedInUserId,
    //                     'last_lc_check' => $latestLcCheck,
    //                     'licence_no' => $data['driver']['drivingLicenceNumber'],
    //                 ]);

    //                  \Log::info('Sending email to contact.', [
    //                     'contact_email' => $contactEmail,
    //                     'username' => $username,
    //                 ]);

    //                 // Send email to the contact email with username and password
    //                 \Mail::to($contactEmail)->send(new \App\Mail\DriverUser($username, $password, $contactEmail));

    //                 return redirect()->route('driver.index')->with('success', __('Driver successfully created.'));
    //             } else {
    //                 // Get error message from the API response
    //                 $errorMessage = $response->json('error.message', 'For the given drivingLicenceNumber field, no driver record could be found.');

    //                 // Redirect with error message
    //                 return redirect()->route('driver.index')
    //                     ->withErrors(['api_error' => $errorMessage]);

    //             }

    //             return response()->json(['error' => 'Failed to retrieve driver data'], 400);
    //         } else {
    //             return redirect()->back()->with('error', __('Permission denied.'));
    //         }
    //     }

    public function updateSpecific($id)
    {
        try {
            $driver = Driver::findOrFail($id);

            if ($driver->driver_status !== 'Active') {
                return redirect()->back()->with('error', 'Your Driver is not Active.');
            }

            $companyName = $driver->companyName; // Assuming the Driver model has a company_name field
            $company = $driver->companyDetails;

            if (! $company) {
                return redirect()->back()->with('error', 'Company details not found.');
            }

            // ✅ Block API call if payment_type or coins is NULL
            if (is_null($company->payment_type) || is_null($company->coins)) {
                return redirect()->back()->with('error', 'Company payment configuration is missing. Please update payment type or coins.');
            }

            // ✅ For Prepaid companies, ensure they have coins left
            if ($company->payment_type === 'Prepaid' && $company->coins <= 0) {
                return redirect()->back()->with('error', 'No API calls left. Please recharge coins.');
            }

            $token = $this->getToken();

            $response = Http::withHeaders([
                'x-api-key' => 'n0LdnbbBTm8KAxSsIFvdFaOsn4lYeGC78dNjvTkq',
                'Authorization' => $token,
            ])->post('https://driver-vehicle-licensing.api.gov.uk/full-driver-enquiry/v1/driving-licences/retrieve', [
                'drivingLicenceNumber' => $driver->driver_licence_no,
                'includeCPC' => true,
                'includeTacho' => true,
                'acceptPartialResponse' => 'true',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Calculate age from date of birth
                $driverDob = $data['driver']['dateOfBirth'] ?? null;
                $driverAge = $driverDob ? $this->calculateAgeDriver($driverDob) : null;

                // Format dates
                $formattedDriverDob = $this->formatDateToDDMMYYYY($driverDob);
                $formattedFromDate = $this->formatDateToDDMMYYYY($data['entitlement'][0]['fromDate'] ?? null);
                $formattedExpiryDate = $this->formatDateToDDMMYYYY($data['entitlement'][0]['expiryDate'] ?? null);
                $formattedValidFromDate = $this->formatDateToDDMMYYYY($data['token']['validFromDate'] ?? null);
                $formattedValidToDate = $this->formatDateToDDMMYYYY($data['token']['validToDate'] ?? null);
                $formattedCardExpiryDate = $this->formatDateToDDMMYYYY($data['holder']['tachoCards'][0]['cardExpiryDate'] ?? null);
                $formattedCardStartOfValidityDate = $this->formatDateToDDMMYYYY($data['holder']['tachoCards'][0]['cardStartOfValidityDate'] ?? null);

                // Determine the latest CPC date
                $latestLgvValidTo = null;
                if (isset($data['cpc']) && is_array($data['cpc']['cpcs'])) {
                    foreach ($data['cpc']['cpcs'] as $cpc) {
                        $lgvValidTo = $cpc['lgvValidTo'] ?? null;
                        if ($lgvValidTo && ($latestLgvValidTo === null || $lgvValidTo > $latestLgvValidTo)) {
                            $latestLgvValidTo = $lgvValidTo;
                        }
                    }
                }
                $formattedLgvValidTo = $this->formatDateToDDMMYYYY($latestLgvValidTo);

                $formattedIssueDate = $this->formatDateToDDMMYYYY($data['dqc']['dqcs'][0]['issueDate'] ?? null);

                $fullName = trim(($data['driver']['firstNames'] ?? '').' '.($data['driver']['lastName'] ?? ''));
                $lastName = $data['driver']['lastName'] ?? '';
                $addressLine1 = $data['driver']['address']['unstructuredAddress']['line1'] ?? '';
                $addressLine2 = $data['driver']['address']['unstructuredAddress']['line2'] ?? '';
                $addressLine3 = $data['driver']['address']['unstructuredAddress']['line3'] ?? '';
                $addressLine4 = $data['driver']['address']['unstructuredAddress']['line4'] ?? '';
                $addressLine5 = $data['driver']['address']['unstructuredAddress']['line5'] ?? '';
                $fullAddress = trim($addressLine1.' '.$addressLine2.' '.$addressLine3.' '.$addressLine4.' '.$addressLine5);

                // Determine the licence check interval based on endorsements
                $penaltyPoints = 0;
                if (isset($data['endorsements']) && is_array($data['endorsements'])) {
                    foreach ($data['endorsements'] as $endorsement) {
                        if (isset($endorsement['penaltyPoints'])) {
                            $penaltyPoints = max($penaltyPoints, $endorsement['penaltyPoints']);
                        }
                    }
                }
                $checkInterval = $this->calculateCheckInterval($penaltyPoints);

                // Get current date and time in UK timezone
                $latestLcCheck = Carbon::now('Europe/London')->format('d/m/Y H:i:s');

                // Calculate next_lc_check
                $nextLcValidUntil = null;
                if ($penaltyPoints < 5) {
                    $nextLcValidUntil = Carbon::createFromFormat('d/m/Y H:i:s', $latestLcCheck)
                        ->addMonths(3)
                        ->format('d/m/Y');
                } else {
                    $nextLcValidUntil = Carbon::createFromFormat('d/m/Y H:i:s', $latestLcCheck)
                        ->addMonths()
                        ->format('d/m/Y');
                }

                // Save driver details
                $driver->update([
                    'driver_age' => $driverAge,
                    'name' => $fullName,
                    'last_name' => $data['driver']['lastName'] ?? null,
                    'gender' => $data['driver']['gender'] ?? null,
                    'first_names' => $data['driver']['firstNames'] ?? null,
                    'driver_dob' => $formattedDriverDob,
                    'driver_address' => $fullAddress,
                    'address_line1' => $addressLine1,
                    'address_line2' => $addressLine2,
                    'address_line3' => $addressLine3,
                    'address_line4' => $addressLine4,
                    'address_line5' => $addressLine5,
                    'post_code' => $data['driver']['address']['unstructuredAddress']['postcode'] ?? null,
                    'licence_type' => $data['licence']['type'] ?? null,
                    'driver_licence_status' => $data['licence']['status'] ?? null,
                    'tacho_card_no' => $data['holder']['tachoCards'][0]['cardNumber'] ?? null,
                    'tacho_card_valid_to' => $formattedCardExpiryDate,
                    'tacho_card_valid_from' => $formattedCardStartOfValidityDate,
                    'token_issue_number' => $data['token']['issueNumber'] ?? null,
                    'token_valid_from_date' => $formattedValidFromDate,
                    'driver_licence_expiry' => $formattedValidToDate,
                    'cpc_validto' => $formattedLgvValidTo, // Save latest LGV valid to date
                    'dqc_issue_date' => $formattedIssueDate,
                    'endorsement_penalty_points' => $data['endorsements'][0]['penaltyPoints'] ?? null,
                    'endorsement_offence_code' => $data['endorsements'][0]['offenceCode'] ?? null,
                    'endorsement_offence_legal_literal' => $data['endorsements'][0]['offenceLegalLiteral'] ?? null,
                    'endorsement_offence_date' => $data['endorsements'][0]['offenceDate'] ?? null,
                    'endorsement_conviction_date' => $data['endorsements'][0]['convictionDate'] ?? null,
                    'endorsements' => json_encode($data['endorsements'] ?? []), // Save endorsements as JSON
                    'current_licence_check_interval' => $checkInterval,
                    'latest_lc_check' => $latestLcCheck, // Add the latest license check date and time
                    'next_lc_check' => $nextLcValidUntil,
                    'created_by' => \Auth::id(),
                ]);

                $dupliacatdriver = \App\Models\DuplicateDriver::create([
                    'driver_modal_id' => $driver->id,
                    'driver_licence_no' => $data['driver']['drivingLicenceNumber'],
                    'companyName' => $driver->companyName,
                    'ni_number' => $driver->ni_number,
                    'contact_no' => $driver->contact_no,
                    'contact_email' => $driver->contact_email,
                    'automation' => $driver->automation,
                    'driver_age' => $driverAge,
                    'name' => $fullName,
                    'last_name' => $data['driver']['lastName'] ?? null,
                    'gender' => $data['driver']['gender'] ?? null,
                    'first_names' => $data['driver']['firstNames'] ?? null,
                    'driver_dob' => $formattedDriverDob,
                    'driver_address' => $fullAddress,
                    'address_line1' => $addressLine1,
                    'address_line2' => $addressLine2,
                    'address_line3' => $addressLine3,
                    'address_line4' => $addressLine4,
                    'address_line5' => $addressLine5,
                    'driver_status' => $driver->driver_status,
                    'post_code' => $data['driver']['address']['unstructuredAddress']['postcode'] ?? null,
                    'licence_type' => $data['licence']['type'] ?? null,
                    'driver_licence_status' => $data['licence']['status'] ?? null,
                    'tacho_card_no' => $data['holder']['tachoCards'][0]['cardNumber'] ?? null,
                    'tacho_card_valid_to' => $formattedCardExpiryDate,
                    'tacho_card_valid_from' => $formattedCardStartOfValidityDate,
                    'token_issue_number' => $data['token']['issueNumber'] ?? null,
                    'token_valid_from_date' => $formattedValidFromDate,
                    'driver_licence_expiry' => $formattedValidToDate,
                    'cpc_validto' => $formattedLgvValidTo, // Save latest LGV valid to date
                    'dqc_issue_date' => $formattedIssueDate,
                    'endorsement_penalty_points' => $data['endorsements'][0]['penaltyPoints'] ?? null,
                    'endorsement_offence_code' => $data['endorsements'][0]['offenceCode'] ?? null,
                    'endorsement_offence_legal_literal' => $data['endorsements'][0]['offenceLegalLiteral'] ?? null,
                    'endorsement_offence_date' => $data['endorsements'][0]['offenceDate'] ?? null,
                    'endorsement_conviction_date' => $data['endorsements'][0]['convictionDate'] ?? null,
                    'endorsements' => json_encode($data['endorsements'] ?? []), // Save endorsements as JSON
                    'current_licence_check_interval' => $checkInterval,
                    'latest_lc_check' => $latestLcCheck, // Add the latest license check date and time
                    'next_lc_check' => $nextLcValidUntil,
                    'created_by' => \Auth::id(),
                ]);

                // Save entitlements
                foreach ($data['entitlement'] ?? [] as $entitlement) {
                    // Convert the restrictions array to JSON
                    $restrictions = json_encode($entitlement['restrictions'] ?? []);

                    // Ensure unique dates are assigned
                    $fromDate = isset($entitlement['fromDate']) ? $this->formatDateToDDMMYYYY($entitlement['fromDate']) : null;
                    $expiryDate = isset($entitlement['expiryDate']) ? $this->formatDateToDDMMYYYY($entitlement['expiryDate']) : null;

                    // Use the correct from_date and expiry_date for each entitlement
                    Entitlement::updateOrCreate(
                        [
                            'driver_id' => $driver->id,
                            'category_code' => $entitlement['categoryCode'],
                            'from_date' => $fromDate,
                            'expiry_date' => $expiryDate,
                        ],
                        [
                            'category_legal_literal' => $entitlement['categoryLegalLiteral'] ?? null,
                            'category_type' => $entitlement['categoryType'] ?? null,
                            'restrictions' => $restrictions,
                        ]
                    );
                }

                // Save entitlements
                foreach ($data['entitlement'] ?? [] as $entitlement) {
                    // Convert the restrictions array to JSON
                    $restrictions = json_encode($entitlement['restrictions'] ?? []);

                    // Ensure unique dates are assigned
                    $fromDate = isset($entitlement['fromDate']) ? $this->formatDateToDDMMYYYY($entitlement['fromDate']) : null;
                    $expiryDate = isset($entitlement['expiryDate']) ? $this->formatDateToDDMMYYYY($entitlement['expiryDate']) : null;

                    // Use the correct from_date and expiry_date for each entitlement
                    \App\Models\DuplicateEntitlement::create(
                        [
                            'duplicate_driver_id' => $dupliacatdriver->id,
                            'driver_modal_id' => $dupliacatdriver->driver_modal_id,
                            'category_code' => $entitlement['categoryCode'],
                            'from_date' => $fromDate,
                            'expiry_date' => $expiryDate,
                            'category_legal_literal' => $entitlement['categoryLegalLiteral'] ?? null,
                            'category_type' => $entitlement['categoryType'] ?? null,
                            'restrictions' => $restrictions,
                        ]
                    );
                }

                // Generate username and password only if they don't already exist
                $driverUser = \App\Models\DriverUser::firstOrNew(['driver_id' => $driver->id]);
                $company = $driver->companyDetails->name ?? 'No Company Name';
                // Only generate username and password if they are empty
                if (empty($driverUser->username) || empty($driverUser->password)) {
                    $name = preg_replace('/\s+/', '', $fullName); // Remove spaces from name

                    // Extract the first 3 characters of the name
                    $namePart = strtolower(substr($name, 0, 3));
                    $lastNamePart = strtolower(substr($lastName, 0, 3));
                    $companyPart = strtolower(substr($company, 0, 3));

                    [$day, $month] = explode('/', $formattedDriverDob);

                    // Create the username
                    $username = $lastNamePart.$companyPart.$day.$month;

                    // Generate the password
                    $firstNamePart = substr($name, 0, 4); // First 4 characters of the name
                    $lastTwoOfLicence = substr($driver->driver_licence_no, -2); // Last 2 characters of the licence number
                    // $password = ucfirst(strtolower($firstNamePart)) . '@' . strtolower($lastTwoOfLicence); // e.g., 'Jasv@me'
                    $password = 12345;

                    // Hash the password
                    $hashedPassword = bcrypt($password);

                    // Set the username and hashed password
                    $driverUser->username = $username; // Assign generated username
                    $driverUser->password = $hashedPassword; // Assign hashed password
                    $driverUser->created_by = \Auth::id();
                    \Mail::to($driver->contact_email)->send(new \App\Mail\DriverUser($username, $password, $driver->contact_email));

                }

                $driverUser->save(); // Save DriverUser

                // Find the CompanyDetails record and increment api_call_count
                $companyDetails = CompanyDetails::where('id', $companyName)->first();

                if (! $companyDetails) {
                    return redirect()->back()->with('error', 'CompanyDetails record not found.');
                }

                // Decrement coins only if Prepaid and not unlimited (-1)
                if ($companyDetails->payment_type === 'Prepaid' && $companyDetails->coins !== -1) {
                    $companyDetails->coins -= 1;
                    $companyDetails->save();
                }

                // Increment api_call_count
                $companyDetails->increment('api_call_count');

                // Log the data
                \App\Models\DriverAPILog::create([
                    'companyName' => $driver->companyName,
                    'created' => \Auth::id(),
                    'last_lc_check' => $latestLcCheck,
                    'licence_no' => $driver->driver_licence_no,
                    'driver_id' => $driver->id,
                ]);

                // Return success response
                return redirect()->back()->with('success', 'Driver details for '.$fullName.' updated successfully.');
            } else {
                return redirect()->back()->with('error', 'There is no driver record available, or no data was found for this driving licence number.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred: '.$e->getMessage());
        }
    }

    private function calculateCheckInterval($penaltyPoints)
    {
        if ($penaltyPoints > 5) {
            return '1 months';
        } else {
            return '3 months';
        }
    }

    private function calculateAgeDriver($dob)
    {
        $dob = \Carbon\Carbon::parse($dob);

        return $dob->age;
    }

    private function formatDateToDDMMYYYY($date)
    {
        return $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : null;
    }

    private function formatUKPhoneNumber($number)
    {
        if ($number === null) {
            return null; // Return null if input is null
        }

        // Remove all spaces, hyphens, and parentheses
        $cleaned = preg_replace('/[\s\-\(\)]+/', '', $number);

        // Add +44 prefix if not already present
        if (! preg_match('/^\+44/', $cleaned)) {
            // Remove leading 0 if present
            $cleaned = preg_replace('/^0/', '', $cleaned);
            $cleaned = '+44'.$cleaned;
        }

        return $cleaned;
    }

    //     public function driverApiLogs(Request $request)
    //     {
    //         // Fetch companies for the dropdown
    //         $companies = CompanyDetails::all();

    //         // Get the selected company ID from request
    //         $companyId = $request->input('company_id');

    //         // Fetch API logs filtered by company ID if provided
    //       $apiLogs = \App\Models\DriverAPILog::when($companyId, function ($query, $companyId) {
    //     return $query->where('companyName', $companyId);
    // })
    // ->orderBy('created_at', 'desc')
    // ->get();

    //         // Calculate the total API call count for all companies
    //         $totalApiCallCount = CompanyDetails::sum('api_call_count');

    //         // Fetch the selected company's API call count and name if a company is selected
    //         $selectedCompanyApiCallCount = 0;
    //         $selectedCompanyName = '';
    //         if ($companyId) {
    //             $selectedCompany = CompanyDetails::find($companyId);
    //             $selectedCompanyApiCallCount = $selectedCompany ? $selectedCompany->api_call_count : 0;
    //             $selectedCompanyName = $selectedCompany ? $selectedCompany->name : '';
    //         }

    //         return view('driver.apilogs', compact('companies', 'apiLogs', 'totalApiCallCount', 'selectedCompanyApiCallCount', 'selectedCompanyName'));
    //     }

    public function driverApiLogs(Request $request)
    {
        $companies = CompanyDetails::all();
        $users = \App\Models\User::whereNotIn('username', ['client', 'accountant', 'Super Admin', 'company'])->get();

        $companyId = $request->input('company_id');
        $userId = $request->input('created');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Fetch API logs filtered by company ID and user ID if provided
        $apiLogs = \App\Models\DriverAPILog::with('drivers', 'creator', 'companyDetails')->when($companyId, function ($query, $companyId) {
            return $query->where('companyName', $companyId);
        })
            ->when($userId, function ($query, $userId) {
                return $query->where('created', $userId);
            })
            ->when($fromDate, function ($query, $fromDate) {
                return $query->whereDate('created_at', '>=', $fromDate);
            })
            ->when($toDate, function ($query, $toDate) {
                return $query->whereDate('created_at', '<=', $toDate);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $totalApiCallCount = CompanyDetails::sum('api_call_count');

        $selectedCompanyApiCallCount = 0;
        $selectedCompanyName = '';
        $selectedUserApiCallCount = 0;
        $selectedUserName = '';

        if ($companyId) {
            $selectedCompany = CompanyDetails::find($companyId);
            $selectedCompanyApiCallCount = $selectedCompany ? $selectedCompany->api_call_count : 0;
            $selectedCompanyName = $selectedCompany ? $selectedCompany->name : '';

            // Count the API logs for the selected company and user
            if ($userId) {
                $selectedUser = \App\Models\User::find($userId);
                $selectedUserApiCallCount = \App\Models\DriverAPILog::where('companyName', $companyId)
                    ->when($userId, fn ($q) => $q->where('created', $userId))
                    ->when($fromDate, fn ($q) => $q->whereDate('created_at', '>=', $fromDate))
                    ->when($toDate, fn ($q) => $q->whereDate('created_at', '<=', $toDate))
                    ->count();
                $selectedUserName = $selectedUser ? $selectedUser->username : '';
            }
        } elseif ($userId) {
            $selectedUser = \App\Models\User::find($userId);
            $selectedUserApiCallCount = \App\Models\DriverAPILog::where('created', $userId)
                ->when($fromDate, fn ($q) => $q->whereDate('created_at', '>=', $fromDate))
                ->when($toDate, fn ($q) => $q->whereDate('created_at', '<=', $toDate))
                ->count();
            $selectedUserName = $selectedUser ? $selectedUser->username : '';
        }

        return view('driver.apilogs', compact(
            'companies', 'users', 'apiLogs', 'totalApiCallCount',
            'selectedCompanyApiCallCount', 'selectedCompanyName',
            'selectedUserApiCallCount', 'selectedUserName',
            'fromDate', 'toDate'
        ));
    }

    public function deleteLogs(Request $request)
    {
        // Validate the request to ensure at least one filter is provided
        $request->validate([
            'company_id' => 'nullable|exists:company_details,id',
            'created' => 'nullable',
        ]);

        $query = \App\Models\DriverAPILog::query();

        // Apply filters based on the request
        if ($request->has('company_id') && $request->company_id) {
            $query->where('companyName', $request->company_id);
        }

        if ($request->has('created') && $request->created) {
            $query->where('created', $request->created);

        }

        // Get the number of logs to be deleted
        $deletedLogsCount = $query->count();

        // Delete the filtered logs
        $query->delete();

        // Optionally update the API call count for the affected company
        if ($request->has('company_id') && $request->company_id) {
            $company = CompanyDetails::find($request->company_id);

            if ($company) {
                $newApiCallCount = max(0, $company->api_call_count - $deletedLogsCount); // Ensure it doesn't go below 0
                $company->update(['api_call_count' => $newApiCallCount]);
            }
        }

        // Return back with a success message
        return redirect()->back()->with(
            'success',
            $deletedLogsCount.' API log(s) deleted successfully.'
        );
    }

    public function driverlogExport()
    {
        $companyId = request('company_id');
        $userId = request('created'); // Get the user ID if provided
        $fromDate = request('from_date'); // Get the From Date
        $toDate = request('to_date');     // Get the To Date
        $fileName = 'Total Driver API Logs.xlsx'; // Default file name

        if ($companyId) {
            $company = \App\Models\CompanyDetails::find($companyId);
            if ($company && $company->company_status === 'Active') {
                $companyName = $company->name;
                $fileName = "({$companyName}) Driver API Logs.xlsx";
            } else {
                return redirect()->back()->with('error', __('Selected company is not active.'));
            }
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\DriverApiLogsExport($companyId, $userId, $fromDate, $toDate), // Pass userId along with companyId
            $fileName
        );
    }

    public function importFile()
    {
        if (\Auth::user()->can('create driver')) {
            return view('driver.import');
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

        $user = \Auth::user(); // Get logged-in user
        $allowedRoles = ['company', 'PTC manager']; // Roles with full import access

        $drivers = (new DriverImport)->toArray($request->file('file'))[0];
        $totalProduct = count($drivers) - 1;
        $errorArray = [];
        $successCount = 0;

        foreach ($drivers as $key => $items) {
            // Skip header row
            if ($key === 0) {
                continue;
            }

            // Lookup CompanyDetails based on companyName
            $companyName = $items[0] ?? null;
            $companyDetails = \App\Models\CompanyDetails::where('name', $companyName)->first();
            if (! $companyDetails) {
                $errorArray[] = [
                    'error' => 'Company name "'.$companyName.'" not found',
                    'data' => $items,
                ];

                continue;
            }

            // Lookup Depot based on depot name
            $depotName = $items[10] ?? null; // Assuming depot name is in column 10
            $depot = \App\Models\Depot::where('name', $depotName)
                ->where('companyName', $companyDetails->id) // Ensure it's the same company
                ->first();

            if (! $depot) {
                $errorArray[] = [
                    'error' => 'Depot name "'.$depotName.'" not found for company "'.$companyName.'"',
                    'data' => $items,
                ];

                continue;
            }

            // **Permission Check**: Skip data if the user is not a manager and is outside the allowed company or depot
            if (! in_array($user->type, $allowedRoles)) {
                $userCompanyId = $user->companyname;
                $userDepots = json_decode($user->depot_id, true) ?? []; // Convert JSON to array

                if ($userCompanyId != $companyDetails->id || ! in_array($depot->id, $userDepots)) {
                    $errorArray[] = [
                        'error' => 'Unauthorized import: You do not have access to company "'.$companyName.'" and depot "'.$depotName.'"',
                        'data' => $items,
                    ];

                    continue;
                }
            }

            // Check if 'driver_status' is not null before assigning
            $driverStatus = $items[2] ?? null; // Column for 'driver_status'
            if ($driverStatus === null) {
                $errorArray[] = [
                    'error' => 'Driver status is missing',
                    'data' => $items,
                ];

                continue;
            }

            // Extract fields from Excel
            $driverLicenceNo = $items[1] ?? null;
            $dob = $items[3] ?? null;
            $firstName = $items[4] ?? null;
            $lastName = $items[5] ?? null;
            $fullName = trim($firstName.' '.$lastName); // Merge first and last name
            $niNumber = $items[6] ?? null;
            $contactNo = $this->formatUKPhoneNumber($items[7] ?? null);
            $contactEmail = $items[8] ?? null;
            $driverGroupName = $items[9] ?? null;

            // Convert DOB to dd/mm/yyyy format
            $username = null;
            if (! empty($dob)) {
                try {
                    $formattedDriverDob = Carbon::parse($dob)->format('d/m/Y');
                    [$day, $month] = explode('/', $formattedDriverDob);

                    // Generate username if DOB is available
                    $lastNamePart = strtolower(substr($lastName, 0, 3));
                    $companyPart = strtolower(substr($companyName, 0, 3));
                    $username = $lastNamePart.$companyPart.$day.$month;
                } catch (\Exception $e) {
                    $errorArray[] = [
                        'error' => 'Invalid DOB format for "'.$fullName.'"',
                        'data' => $items,
                    ];

                    continue;
                }
            } else {
                $formattedDriverDob = null; // Set DOB as null if missing
                $username = '-';
            }

            // Lookup Group based on group name
            $groupQuery = \App\Models\Group::where('name', $driverGroupName)
                ->where('company_id', $companyDetails->id);

            if (! in_array($user->type, $allowedRoles)) {

                $userGroupIds = is_array($user->driver_group_id)
                    ? $user->driver_group_id
                    : json_decode($user->driver_group_id, true);

                $groupQuery->whereIn('id', $userGroupIds ?? []);
            }

            $group = $groupQuery->first();

            if (! $group) {
                $errorArray[] = [
                    'error' => 'Unauthorized or invalid group "'.$driverGroupName.'" for company "'.$companyName.'"',
                    'data' => $items,
                ];

                continue;
            }

            // Check for existing driver record
            $driverService = \App\Models\Driver::where('driver_licence_no', $driverLicenceNo)
                ->where('companyName', $companyDetails->id) // Use company ID for comparison
                ->first();

            if ($driverService) {
                // Update existing record
                $driverService->driver_status = $driverStatus;
                $driverService->driver_dob = $formattedDriverDob;
                $driverService->name = $fullName;
                $driverService->ni_number = $niNumber;
                $driverService->contact_no = $contactNo;
                $driverService->contact_email = $contactEmail;
                $driverService->group_id = $group->id;
                $driverService->depot_id = $depot->id;
                $driverService->save();
                $successCount++;
            } else {
                // Create new record
                $newDriver = new \App\Models\Driver();
                $newDriver->companyName = $companyDetails->id;
                $newDriver->driver_licence_no = $driverLicenceNo;
                $newDriver->driver_status = $driverStatus;
                $newDriver->driver_dob = $formattedDriverDob;
                $newDriver->name = $fullName;
                $newDriver->ni_number = $niNumber;
                $newDriver->contact_no = $contactNo;
                $newDriver->contact_email = $contactEmail;
                $newDriver->group_id = $group->id;
                $newDriver->depot_id = $depot->id;
                $newDriver->automation = 'No';
                $newDriver->created_by = \Auth::user()->id;
                $newDriver->save();

                // Create new DriverUser
                \App\Models\DriverUser::create([
                    'driver_id' => $newDriver->id,
                    'username' => $username,
                    'password' => bcrypt('12345'), // Encrypt password
                ]);
            }

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

        return redirect()->route('driver.index')->with($data['status'], $data['msg']);
    }

    private function parseDate($date)
    {
        if (empty($date) || $date === '-') {
            return null;
        }

        // Check if date is in mm/dd/yyyy format and convert it to DateTime object
        $dateTime = \DateTime::createFromFormat('m/d/Y', $date);
        if ($dateTime) {
            return $dateTime->format('d/m/Y');
        }

        // Fallback to try converting Excel date if it's a float
        if (is_numeric($date)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)->format('d/m/Y');
        }

        return null;
    }

    //  public function downloadDriverInfo($id)
    // {

    //     $driver = Driver::with('entitlements')->findOrFail($id);

    //     if (! $driver) {
    //         return redirect()->back()->with('error', __('Driver not found.'));
    //     }

    //     $settings = \App\Models\Utility::settings();
    //     $company_logo = \App\Models\Utility::getValByName('company_logo');
    //     $imagePath = storage_path('/uploads/logo/'.(isset($company_logo) && ! empty($company_logo) ? $company_logo : '5-logo-dark.png'));

    //     if (file_exists($imagePath)) {
    //         $imageData = base64_encode(file_get_contents($imagePath));
    //         $img = 'data:image/png;base64,'.$imageData;
    //     } else {
    //         \Log::error('Image file does not exist: '.$imagePath);
    //         $img = ''; // Fallback or default image if necessary
    //     }

    //     // Decode the JSON data
    // $endorsements = json_decode($driver->endorsements, true);

    // // Initialize variables
    // $firstPenaltyPoints = '0';
    // $offenceCodes = [];

    // // Loop through the endorsements
    // foreach ($endorsements as $endorsement) {
    //     // Check for penaltyPoints and set the first value
    //     if (isset($endorsement['penaltyPoints']) && $firstPenaltyPoints === '0') {
    //         $firstPenaltyPoints = $endorsement['penaltyPoints'];
    //     }

    //     // Collect offenceCodes
    //     if (isset($endorsement['offenceCode'])) {
    //         $offenceCodes[] = $endorsement['offenceCode'];
    //     }
    // }
    // $endorsements = $driver->endorsements ? json_decode($driver->endorsements, true) : [];

    // // Count unique offenceCodes
    // $uniqueOffenceCodeCount = count(array_unique($offenceCodes));

    //     $view = view('driver.driverinfotemplate', compact('driver', 'img', 'settings','firstPenaltyPoints', 'uniqueOffenceCodeCount','endorsements'))->render();

    //     $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($view)
    //         ->setOptions(['defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);

    //     $filename = 'Driver Information ('.$driver->name.').pdf';

    //     return $pdf->stream($filename);
    // }

    public function downloadDriverInfo($slug)
    {
        // Decode the slug to get the driver ID
        $id = base64_decode($slug);

        // Ensure the decoded ID is valid
        if (! is_numeric($id)) {
            return redirect()->back()->with('error', __('Invalid driver ID.'));
        }

        // Find the driver by ID
        $driver = Driver::with('entitlements')->findOrFail($id);

        if (! $driver) {
            return redirect()->back()->with('error', __('Driver not found.'));
        }

        $settings = \App\Models\Utility::settings();
        $company_logo = \App\Models\Utility::getValByName('company_logo');
        $imagePath = storage_path('/uploads/logo/'.(isset($company_logo) && ! empty($company_logo) ? $company_logo : '5-logo-dark.png'));

        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $img = 'data:image/png;base64,'.$imageData;
        } else {
            \Log::error('Image file does not exist: '.$imagePath);
            $img = ''; // Fallback or default image if necessary
        }

        // Decode the JSON data
        $endorsements = json_decode($driver->endorsements, true);

        // Initialize variables
        $firstPenaltyPoints = '0';
        $offenceCodes = [];

        // Loop through the endorsements
        foreach ($endorsements as $endorsement) {
            // Check for penaltyPoints and set the first value
            if (isset($endorsement['penaltyPoints']) && $firstPenaltyPoints === '0') {
                $firstPenaltyPoints = $endorsement['penaltyPoints'];
            }

            // Collect offenceCodes
            if (isset($endorsement['offenceCode'])) {
                $offenceCodes[] = $endorsement['offenceCode'];
            }
        }
        $endorsements = $driver->endorsements ? json_decode($driver->endorsements, true) : [];

        // Count unique offenceCodes
        $uniqueOffenceCodeCount = count(array_unique($offenceCodes));

        $view = view('driver.driverinfotemplate', compact('driver', 'img', 'settings', 'firstPenaltyPoints', 'uniqueOffenceCodeCount', 'endorsements'))->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($view)
            ->setOptions(['defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);

        $filename = 'Driver Information ('.$driver->name.').pdf';

        return $pdf->stream($filename);
    }

    public function updateDriverContentValidUntil(Request $request, $id)
    {
        $request->validate([
            'valid_until' => 'required|date',
        ]);

        $driver = Driver::findOrFail($id);

        // Convert the input date from Y-m-d to dd/mm/yyyy
        $validUntilDate = \Carbon\Carbon::createFromFormat('Y-m-d', $request->input('valid_until'))->format('d/m/Y');
        // $checkIntervalDate = \Carbon\Carbon::createFromFormat('d/m/Y', $validUntilDate)->addMonths(3)->format('d/m/Y');
        // $checkIntervalDate = '3 months';

        $driver->consent_valid = $validUntilDate;
        // $driver->current_licence_check_interval = $checkIntervalDate;
        $driver->save();

        return redirect()->route('driver.show', $id)->with('success', 'Driver information updated successfully.');
    }

    public function edit(Driver $driver)
    {
        $user = \Auth::user();
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {

            $contractTypes = \App\Models\CompanyDetails::where('created_by', '=', \Auth::user()->creatorId())->where('company_status', 'Active')->orderBy('name', 'asc')->get()->pluck('name', 'id');

        } else {
            $contractTypes = CompanyDetails::where('id', '=', $user->companyname)->orderBy('name', 'asc')->get()->pluck('name', 'id');
        }

        return view('driver.edit', compact('driver', 'contractTypes'));

    }

    public function update(Request $request, Driver $driver)
    {
        if (\Auth::user()->can('edit driver')) {
            $validator = \Validator::make(
                $request->all(), [
                    // 'name' => 'required',
                    'companyName' => 'required',
                    'driver_status' => 'required|in:Active,InActive,Archive',
                    'ni_number' => 'nullable',
                    // 'post_code' => 'nullable',
                    'contact_no' => 'nullable',
                    'contact_email' => 'nullable',
                    'group_id' => 'nullable',
                    'depot_id' => 'nullable',
                    'automation' => 'nullable|in:Yes,No',
                    'depot_access_status' => 'required|in:Yes,No',
                    'driver_dob' => 'nullable|date_format:Y-m-d',
                    'first_names' => 'nullable|string',
                    'last_name' => 'nullable|string',
                    'token_valid_from_date' => 'nullable|date_format:Y-m-d',
                    'driver_licence_expiry' => 'nullable|date_format:Y-m-d',
                    'tacho_card_valid_from' => 'nullable|date_format:Y-m-d',
                    'tacho_card_valid_to' => 'nullable|date_format:Y-m-d',
                    'dqc_issue_date' => 'nullable|date_format:Y-m-d',
                    'cpc_validto' => 'nullable|date_format:Y-m-d',
                    // 'driver_dob' => 'nullable|date_format:Y-m-d',
                    // 'driver_age' => 'nullable',
                    // 'driver_address' => 'nullable',
                    'driver_licence_no' => 'nullable',
                    // 'driver_licence_status' => 'nullable',
                    // 'driver_licence_expiry' => 'nullable|date_format:Y-m-d',
                    // 'cpc_status' => 'nullable',
                    // 'cpc_validto' => 'nullable|date_format:Y-m-d',
                    // 'tacho_card_no' => 'nullable',
                    // 'tacho_card_status' => 'nullable',
                    // 'tacho_card_valid_from' => 'nullable|date_format:Y-m-d',
                    // 'tacho_card_valid_to' => 'nullable|date_format:Y-m-d',
                    // 'lc_check_status' => 'nullable',
                    // 'latest_lc_check' => 'nullable|date_format:Y-m-d',
                    // 'comment' => 'nullable',
                    'username' => [
                        'nullable',
                        'string',
                        'max:255',
                        \Illuminate\Validation\Rule::unique('driver_users', 'username')->ignore($driver->driverUser->id ?? null),
                    ],
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $formattedContactNo = $this->formatUKPhoneNumber($request->contact_no);

            $formattedDriverDob = $this->formatDateToDDMMYYYY($request->driver_dob);
            $formattedTokenValidFromDate = $request->token_valid_from_date ? \Carbon\Carbon::createFromFormat('Y-m-d', $request->token_valid_from_date)->format('d/m/Y') : null;

            $driverLicenceExpiry = $request->driver_licence_expiry ? \Carbon\Carbon::createFromFormat('Y-m-d', $request->driver_licence_expiry)->format('d/m/Y') : null;
            $cpcValidTo = $request->cpc_validto ? \Carbon\Carbon::createFromFormat('Y-m-d', $request->cpc_validto)->format('d/m/Y') : null;
            $tachoCardValidTo = $request->tacho_card_valid_to ? \Carbon\Carbon::createFromFormat('Y-m-d', $request->tacho_card_valid_to)->format('d/m/Y') : null;
            $tachoCardValidFrom = $request->tacho_card_valid_from ? \Carbon\Carbon::createFromFormat('Y-m-d', $request->tacho_card_valid_from)->format('d/m/Y') : null;
            $dqcissueDate = $request->dqc_issue_date ? \Carbon\Carbon::createFromFormat('Y-m-d', $request->dqc_issue_date)->format('d/m/Y') : null;
            $fullName = trim($request->first_names.' '.$request->last_name);

            // $driver->name = $request->name;
            $driver->companyName = $request->companyName;
            $driver->driver_status = $request->driver_status;
            $driver->ni_number = $request->ni_number;
            // $driver->post_code = $request->post_code;
            $driver->contact_no = $formattedContactNo; // Assign formatted contact number
            $driver->contact_email = $request->contact_email;
            $driver->driver_dob = $formattedDriverDob;
            $driver->name = $fullName;
            $driver->first_names = $request->first_names;
            $driver->last_name = $request->last_name;
            $driver->token_valid_from_date = $formattedTokenValidFromDate;
            $driver->driver_licence_expiry = $driverLicenceExpiry;
            // $driver->driver_age = $request->driver_age;
            // $driver->driver_address = $request->driver_address;
            $driver->driver_licence_no = $request->driver_licence_no;
            // $driver->driver_licence_status = $driverLicenceExpiry ? $request->driver_licence_status : '-';
            $driver->cpc_validto = $cpcValidTo;
            $driver->dqc_issue_date = $dqcissueDate;
            // $driver->cpc_status = $cpcValidTo ? $request->cpc_status : '-';
            // $driver->tacho_card_no = $request->tacho_card_no ?? null;
            $driver->tacho_card_valid_from = $tachoCardValidFrom ?? null;
            $driver->tacho_card_valid_to = $tachoCardValidTo ?? null;
            // $driver->tacho_card_status = $tachoCardValidTo ? $request->tacho_card_status : '-';
            // $driver->lc_check_status = $request->lc_check_status ?? null;
            // $driver->latest_lc_check = $latestLcCheck;
            // $driver->comment = $request->comment;
            $driver->group_id = $request->group_id;  // Save selected group modal ID
            $driver->depot_id = $request->depot_id;
            $driver->automation = $request->automation;
            $driver->depot_access_status = $request->depot_access_status;

            $driver->automation = $request->automation;
            $driver->consent_form_status = $request->consent_form_status;

            // $driver->created_by = \Auth::user()->id;
            $driver->save();

            $driverUser = $driver->driverUser; // Get the related DriverUser model
            if ($driverUser) {
                $driverUser->username = $request->username; // Update username
                $driverUser->save();
            } else {
                // Handle case where DriverUser does not exist (optional)
                $driverUser = new \App\Models\DriverUser();
                $driverUser->driver_id = $driver->id;
                $driverUser->username = $request->username;
                $driverUser->save();
            }

            return redirect()->back()->with('success', __('Driver Data successfully updated.'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show($id)
    {
        // Get the current authenticated user
        $user = \Auth::user();

        // Check if the user is an admin or PTC manager
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {

            // If the user is an admin or PTC manager, fetch the driver regardless of the company
            $driver = Driver::with('attachments', 'entitlements')->findOrFail($id);
        } else {
            // If the user is not an admin or PTC manager, filter by the logged-in user's company
            $driver = Driver::where('companyName', $user->companyname)
                ->with('attachments', 'entitlements')
                ->find($id);
            if (! $driver) {
                return redirect()->back()->with('error', __('Driver not found for your company.'));
            }

            $userGroupIds = is_array($user->driver_group_id)
            ? $user->driver_group_id
            : json_decode($user->driver_group_id, true);

            if (! in_array($driver->group_id, $userGroupIds ?? [])) {
                return redirect()->back()->with('error', __('You are not allowed to view this driver (group restriction).'));
            }

            // ============================
            // DEPOT restriction
            // ============================
            $userDepotIds = is_array($user->depot_id)
                ? $user->depot_id
                : json_decode($user->depot_id, true);

            if (! in_array($driver->depot_id, $userDepotIds ?? [])) {
                return redirect()->back()->with('error', __('You are not allowed to view this driver (depot restriction).'));
            }
        }

        // Check if the driver's company_status is "InActive"
        if ($driver->types->company_status === 'InActive') {
            return redirect()->back()->with('error', __('Your company is not Active.'));
        }

        // Decode the JSON data
        $endorsements = json_decode($driver->endorsements, true) ?? [];

        // Initialize variables
        $firstPenaltyPoints = '0';
        $offenceCodes = [];

        // Loop through the endorsements
        foreach ($endorsements as $endorsement) {
            // Check for penaltyPoints and set the first value
            if (isset($endorsement['penaltyPoints']) && $firstPenaltyPoints === '0') {
                $firstPenaltyPoints = $endorsement['penaltyPoints'];
            }

            // Collect offenceCodes
            if (isset($endorsement['offenceCode'])) {
                $offenceCodes[] = $endorsement['offenceCode'];
            }
        }
        // $endorsements = $driver->endorsements ? json_decode($driver->endorsements, true) : [];

        // Count unique offenceCodes
        $uniqueOffenceCodeCount = count(array_unique($offenceCodes));

        return view('driver.show', compact('driver', 'firstPenaltyPoints', 'uniqueOffenceCodeCount', 'endorsements'));
    }

    public function showEntitlements($id)
    {
        $driver = Driver::with('entitlements')->findOrFail($id);

        $settings = \App\Models\Utility::settings();
        $company_logo = \App\Models\Utility::getValByName('company_logo');
        $imagePath = storage_path('/uploads/logo/'.(isset($company_logo) && ! empty($company_logo) ? $company_logo : 'logo-dark.png'));

        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $img = 'data:image/png;base64,'.$imageData;
        } else {
            \Log::error('Image file does not exist: '.$imagePath);
            $img = ''; // Fallback or default image if necessary
        }

        return view('driver.driver_entitlements', compact('driver', 'img', 'settings'));
    }

    public function printContract($id)
    {
        $driver = Driver::with('attachments')->findOrFail($id);
        $settings = \App\Models\Utility::settings();

        // Set your logo
        $logo = asset(\Illuminate\Support\Facades\Storage::url('uploads/logo/'));
        $company_logo = \App\Models\Utility::getValByName('company_logo');
        $img = asset($logo.'/'.(isset($company_logo) && ! empty($company_logo) ? $company_logo : 'logo-dark.png'));

        if ($driver) {
            // Fetch license_front and license_back attachments
            $license_front = $driver->attachments->where('name', 'license_front')->first();
            $license_back = $driver->attachments->where('name', 'license_back')->first();
            $cpc_card_front = $driver->attachments->where('name', 'cpc_card_front')->first();
            $cpc_card_back = $driver->attachments->where('name', 'cpc_card_back')->first();
            $tacho_card_front = $driver->attachments->where('name', 'tacho_card_front')->first();
            $tacho_card_back = $driver->attachments->where('name', 'tacho_card_back')->first();
            $mpqc_card_front = $driver->attachments->where('name', 'mpqc_card_front')->first();
            $mpqc_card_back = $driver->attachments->where('name', 'mpqc_card_back')->first();
            $levelD_card_front = $driver->attachments->where('name', 'levelD_card_front')->first();
            $levelD_card_back = $driver->attachments->where('name', 'levelD_card_back')->first();
            $one_card_front = $driver->attachments->where('name', 'one_card_front')->first();
            $one_card_back = $driver->attachments->where('name', 'one_card_back')->first();

            // Generate URLs for the images
            $license_front_url = $license_front ? asset(\Illuminate\Support\Facades\Storage::url($license_front->path)) : null;
            $license_back_url = $license_back ? asset(\Illuminate\Support\Facades\Storage::url($license_back->path)) : null;
            $cpc_card_front_url = $cpc_card_front ? asset(\Illuminate\Support\Facades\Storage::url($cpc_card_front->path)) : null;
            $cpc_card_back_url = $cpc_card_back ? asset(\Illuminate\Support\Facades\Storage::url($cpc_card_back->path)) : null;
            $tacho_card_front_url = $tacho_card_front ? asset(\Illuminate\Support\Facades\Storage::url($tacho_card_front->path)) : null;
            $tacho_card_back_url = $tacho_card_back ? asset(\Illuminate\Support\Facades\Storage::url($tacho_card_back->path)) : null;
            $mpqc_card_front_url = $mpqc_card_front ? asset(\Illuminate\Support\Facades\Storage::url($mpqc_card_front->path)) : null;
            $mpqc_card_back_url = $mpqc_card_back ? asset(\Illuminate\Support\Facades\Storage::url($mpqc_card_back->path)) : null;
            $levelD_card_front_url = $levelD_card_front ? asset(\Illuminate\Support\Facades\Storage::url($levelD_card_front->path)) : null;
            $levelD_card_back_url = $levelD_card_back ? asset(\Illuminate\Support\Facades\Storage::url($levelD_card_back->path)) : null;
            $one_card_front_url = $one_card_front ? asset(\Illuminate\Support\Facades\Storage::url($one_card_front->path)) : null;
            $one_card_back_url = $one_card_back ? asset(\Illuminate\Support\Facades\Storage::url($one_card_back->path)) : null;

            $color = '#'.$settings['invoice_color'];
            $font_color = \App\Models\Utility::getFontColor($color);

            return view('driver.preview', compact('driver', 'color', 'img', 'settings', 'font_color', 'license_front_url', 'license_back_url'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function downloadAllImagesAsZip($driverId)
    {
        $driver = Driver::findOrFail($driverId);

        // Collect image URLs and their corresponding names
        $imageUrls = [];
        $imageNames = [];
        $count = 1;

        foreach ($driver->attachments ?? [] as $attachment) {
            if (! empty($attachment->license_front) && file_exists(storage_path($attachment->license_front))) {
                $imageUrls[] = storage_path($attachment->license_front);
                $imageNames[] = 'Driving License Front image.jpg';
            }
            if (! empty($attachment->license_back) && file_exists(storage_path($attachment->license_back))) {
                $imageUrls[] = storage_path($attachment->license_back);
                $imageNames[] = 'Driving License Back image.jpg';
            }
            if (! empty($attachment->cpc_card_front) && file_exists(storage_path($attachment->cpc_card_front))) {
                $imageUrls[] = storage_path($attachment->cpc_card_front);
                $imageNames[] = 'CPC Card Front image.jpg';
            }
            if (! empty($attachment->cpc_card_back) && file_exists(storage_path($attachment->cpc_card_back))) {
                $imageUrls[] = storage_path($attachment->cpc_card_back);
                $imageNames[] = 'CPC Card Back image.jpg';
            }
            if (! empty($attachment->tacho_card_front) && file_exists(storage_path($attachment->tacho_card_front))) {
                $imageUrls[] = storage_path($attachment->tacho_card_front);
                $imageNames[] = 'Tacho Card Front image.jpg';
            }
            if (! empty($attachment->tacho_card_back) && file_exists(storage_path($attachment->tacho_card_back))) {
                $imageUrls[] = storage_path($attachment->tacho_card_back);
                $imageNames[] = 'Tacho Card Back image.jpg';
            }
            if (! empty($attachment->mpqc_card_front) && file_exists(storage_path($attachment->mpqc_card_front))) {
                $imageUrls[] = storage_path($attachment->mpqc_card_front);
                $imageNames[] = 'MPQC Card Front image.jpg';
            }
            if (! empty($attachment->mpqc_card_back) && file_exists(storage_path($attachment->mpqc_card_back))) {
                $imageUrls[] = storage_path($attachment->mpqc_card_back);
                $imageNames[] = 'MPQC Card Back image.jpg';
            }
            if (! empty($attachment->levelD_card_front) && file_exists(storage_path($attachment->levelD_card_front))) {
                $imageUrls[] = storage_path($attachment->levelD_card_front);
                $imageNames[] = 'levelD Card Front image.jpg';
            }
            if (! empty($attachment->levelD_card_back) && file_exists(storage_path($attachment->levelD_card_back))) {
                $imageUrls[] = storage_path($attachment->levelD_card_back);
                $imageNames[] = 'levelD Card Back image.jpg';
            }
            if (! empty($attachment->one_card_front) && file_exists(storage_path($attachment->one_card_front))) {
                $imageUrls[] = storage_path($attachment->one_card_front);
                $imageNames[] = 'One Card Front image.jpg';
            }
            if (! empty($attachment->one_card_back) && file_exists(storage_path($attachment->one_card_back))) {
                $imageUrls[] = storage_path($attachment->one_card_back);
                $imageNames[] = 'One Card Back image.jpg';
            }
            if (! empty($attachment->additional_cards)) {
                $additionalCards = json_decode($attachment->additional_cards, true);
                if (is_array($additionalCards)) {
                    foreach ($additionalCards as $card) {
                        if (file_exists(storage_path($card))) {
                            $imageUrls[] = storage_path($card);
                            $imageNames[] = 'Additional Card_'.$count.'.jpg';
                            $count++;
                        }
                    }
                }
            }
        }

        $zipFileName = ucwords(strtolower($driver->name)).'_Documents'.'.zip';
        $zipFilePath = storage_path('app/'.$zipFileName); // Adjust path as needed
        $zip = new ZipArchive();

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($imageUrls as $key => $imageUrl) {
                $zip->addFile($imageUrl, $imageNames[$key]);
            }
            $zip->close();

            if (file_exists($zipFilePath)) {
                // Download the zip file
                return response()->download($zipFilePath)->deleteFileAfterSend(true);
            } else {
                return redirect()->back()->withErrors(['error' => 'Zip file could not be created.']);
            }
        } else {
            return redirect()->back()->withErrors(['error' => 'Failed to create zip file.']);
        }
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|exists:drivers,id',
            'license_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'license_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'cpc_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'cpc_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'tacho_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'tacho_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'mpqc_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'mpqc_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'levelD_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'levelD_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'one_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'one_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'additional_cards.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',

        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'File must be less than 10MB and in one of the following formats: jpeg, png, jpg');
        }

        $driverAttachment = \App\Models\DriverAttachments::where('driver_id', $request->driver_id)->first();

        if (! $driverAttachment) {
            $driverAttachment = new \App\Models\DriverAttachments();
            $driverAttachment->driver_id = $request->driver_id;
        }

        if ($request->hasFile('license_front')) {
            $frontPath = $request->file('license_front')->store('driver_attachments/licenses-image/front');
            $driverAttachment->license_front = $frontPath;
        }

        if ($request->hasFile('license_back')) {
            $backPath = $request->file('license_back')->store('driver_attachments/licenses-image/back');
            $driverAttachment->license_back = $backPath;
        }

        if ($request->hasFile('cpc_card_front')) {
            $CpcCardfrontPath = $request->file('cpc_card_front')->store('driver_attachments/CPC-Card-image/front');
            $driverAttachment->cpc_card_front = $CpcCardfrontPath;
        }

        if ($request->hasFile('cpc_card_back')) {
            $CpcCardbackPath = $request->file('cpc_card_back')->store('driver_attachments/CPC-Card-image/back');
            $driverAttachment->cpc_card_back = $CpcCardbackPath;
        }

        if ($request->hasFile('tacho_card_front')) {
            $TachoCardfrontPath = $request->file('tacho_card_front')->store('driver_attachments/Tacho-Card-image/front');
            $driverAttachment->tacho_card_front = $TachoCardfrontPath;
        }

        if ($request->hasFile('tacho_card_back')) {
            $TachoCardbackPath = $request->file('tacho_card_back')->store('driver_attachments/Tacho-Card-image/back');
            $driverAttachment->tacho_card_back = $TachoCardbackPath;
        }
        if ($request->hasFile('mpqc_card_front')) {
            $MPQCCardfrontPath = $request->file('mpqc_card_front')->store('driver_attachments/MPQC-Card-image/front');
            $driverAttachment->mpqc_card_front = $MPQCCardfrontPath;
        }

        if ($request->hasFile('mpqc_card_back')) {
            $MPQCCardbackPath = $request->file('mpqc_card_back')->store('driver_attachments/MPQC-Card-image/back');
            $driverAttachment->mpqc_card_back = $MPQCCardbackPath;
        }

        if ($request->hasFile('levelD_card_front')) {
            $levelDCardfrontPath = $request->file('levelD_card_front')->store('driver_attachments/levelD-Card-image/front');
            $driverAttachment->levelD_card_front = $levelDCardfrontPath;
        }

        if ($request->hasFile('levelD_card_back')) {
            $levelDCardbackPath = $request->file('levelD_card_back')->store('driver_attachments/levelD-Card-image/back');
            $driverAttachment->levelD_card_back = $levelDCardbackPath;
        }
        if ($request->hasFile('one_card_front')) {
            $OneCardfrontPath = $request->file('one_card_front')->store('driver_attachments/One-Card-image/front');
            $driverAttachment->one_card_front = $OneCardfrontPath;
        }

        if ($request->hasFile('one_card_back')) {
            $OneCardbackPath = $request->file('one_card_back')->store('driver_attachments/One-Card-image/back');
            $driverAttachment->one_card_back = $OneCardbackPath;
        }
        // if ($request->hasFile('additional_cards')) {
        //     $paths = [];
        //     foreach ($request->file('additional_cards') as $file) {
        //         $path = $file->store('driver_attachments/additional_card_images');
        //         $paths[] = $path;
        //     }
        //     $driverAttachment->additional_cards = json_encode($paths); // Store paths as JSON
        // }

        if ($request->hasFile('additional_cards')) {
            $existingPaths = $driverAttachment->additional_cards ? json_decode($driverAttachment->additional_cards, true) : [];
            $newPaths = [];

            foreach ($request->file('additional_cards') as $file) {
                $path = $file->store('driver_attachments/additional_card_images');
                $newPaths[] = $path;
            }

            // Combine old and new paths
            $allPaths = array_merge($existingPaths, $newPaths);
            $driverAttachment->additional_cards = json_encode($allPaths); // Store paths as JSON
        }

        $driverAttachment->created_by = \Auth::user()->id;
        $driverAttachment->save();

        return redirect()->back()->with('success', __('Card Attachments uploaded successfully.'));

    }

    public function destroy(Driver $driver)
    {
        if (\Auth::user()->can('manage driver')) {
            $driver->delete();

            return redirect()->back()->with('success', __('Driver Data successfully deleted.'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function sendReminders()
    {
        // Define the date thresholds for 90 days, 60 days, and 30 days from today
        $now = \Carbon\Carbon::now();
        $dates = [
            '90_DAYS' => $now->copy()->addDays(90)->format('d/m/Y'),
            '60_DAYS' => $now->copy()->addDays(60)->format('d/m/Y'),
            '30_DAYS' => $now->copy()->addDays(30)->format('d/m/Y'),
        ];

        // Fetch drivers whose licenses are expiring in 90, 60, or 30 days or have already expired
        $drivers = \App\Models\Driver::where(function ($query) use ($dates) {
            $query->where(function ($q) use ($dates) {
                $q->whereDate('driver_licence_expiry', '=', $dates['90_DAYS'])
                    ->orWhereDate('driver_licence_expiry', '=', $dates['60_DAYS'])
                    ->orWhereDate('driver_licence_expiry', '=', $dates['30_DAYS']);
            })
                ->orWhere(function ($q) use ($dates) {
                    $q->whereDate('cpc_validto', '=', $dates['90_DAYS'])
                        ->orWhereDate('cpc_validto', '=', $dates['60_DAYS'])
                        ->orWhereDate('cpc_validto', '=', $dates['30_DAYS']);
                })
                ->orWhere(function ($q) use ($dates) {
                    $q->whereDate('tacho_card_valid_to', '=', $dates['90_DAYS'])
                        ->orWhereDate('tacho_card_valid_to', '=', $dates['60_DAYS'])
                        ->orWhereDate('tacho_card_valid_to', '=', $dates['30_DAYS']);
                });
        })
            ->with(['companyDetails' => function ($query) {
                $query->where('company_status', 'Active'); // Filter by Active company status
            }]) // Eager load only active company details
            ->get();

        // Group drivers by their associated company
        $driversByCompany = $drivers->groupBy('companyDetails.id');

        // Iterate over each company and prepare data for emails
        foreach ($driversByCompany as $companyId => $drivers) {
            // Fetch company details
            $companyDetails = \App\Models\CompanyDetails::find($companyId);

            // Check if company details exist and if the status is Active
            if (! $companyDetails || $companyDetails->company_status !== 'Active') {
                continue; // Skip to the next iteration if company details are not valid or not active
            }

            // Prepare data for company email
            $companyData = [
                'companyName' => $companyDetails->name,
                'drivers' => $drivers,
            ];

            // Filter out drivers with null or '-' values before sending emails
            $driversToSend = $drivers->filter(function ($driver) use ($dates) {
                return
                    ($driver->driver_licence_expiry != null && $driver->driver_licence_expiry != '-' &&
                        ($driver->driver_licence_expiry == $dates['90_DAYS'] ||
                         $driver->driver_licence_expiry == $dates['60_DAYS'] ||
                         $driver->driver_licence_expiry == $dates['30_DAYS'])) ||
                    ($driver->cpc_validto != null && $driver->cpc_validto != '-' &&
                        ($driver->cpc_validto == $dates['90_DAYS'] ||
                         $driver->cpc_validto == $dates['60_DAYS'] ||
                         $driver->cpc_validto == $dates['30_DAYS'])) ||
                    ($driver->tacho_card_valid_to != null && $driver->tacho_card_valid_to != '-' &&
                        ($driver->tacho_card_valid_to == $dates['90_DAYS'] ||
                         $driver->tacho_card_valid_to == $dates['60_DAYS'] ||
                         $driver->tacho_card_valid_to == $dates['30_DAYS']));
            });

            if ($driversToSend->isNotEmpty()) {
                // Send summary email to company
                \Mail::to($companyDetails->email)->send(new \App\Mail\DriverLicenseReminderToCompany($companyData, $dates));

                // Send individual reminder emails to drivers
                foreach ($driversToSend as $driver) {
                    \Mail::to($driver->contact_email)->send(new \App\Mail\DriverLicenseReminder($driver, $dates));
                }
            }
        }

        return response()->json(['message' => 'Reminders sent successfully!'], 200);
    }

    public function saveReminders()
    {
        $now = Carbon::now();
        $dates = [
            '90_DAYS' => $now->copy()->addDays(90)->format('d/m/Y'),
            '60_DAYS' => $now->copy()->addDays(60)->format('d/m/Y'),
            '30_DAYS' => $now->copy()->addDays(30)->format('d/m/Y'),
        ];

        $drivers = \App\Models\Driver::where(function ($query) use ($dates) {
            $query->whereIn('driver_licence_expiry', $dates)
                ->orWhereIn('cpc_validto', $dates)
                ->orWhereIn('tacho_card_valid_to', $dates);
        })
            ->whereHas('companyDetails', function ($query) {
                $query->where('company_status', 'Active');
            })
            ->get();

        foreach ($drivers as $driver) {
            // Check and save separate reminders
            if (in_array($driver->driver_licence_expiry, $dates)) {
                \App\Models\DriverReminderLog::updateOrCreate(
                    [
                        'driver_id' => $driver->id,
                        'company_id' => $driver->companyName,
                        'reminder_type' => 'Driver Licence Expiry',
                        'reminder_date' => $driver->driver_licence_expiry,
                    ],
                    [
                        'status' => 'Pending',
                        'reminder_parameter' => 'driver_licence_expiry',
                    ]
                );
            }

            if (in_array($driver->cpc_validto, $dates)) {
                \App\Models\DriverReminderLog::updateOrCreate(
                    [
                        'driver_id' => $driver->id,
                        'company_id' => $driver->companyName,
                        'reminder_type' => 'CPC Card Expiry',
                        'reminder_date' => $driver->cpc_validto,
                    ],
                    [
                        'status' => 'Pending',
                        'reminder_parameter' => 'cpc_validto',
                    ]
                );
            }

            if (in_array($driver->tacho_card_valid_to, $dates)) {
                \App\Models\DriverReminderLog::updateOrCreate(
                    [
                        'driver_id' => $driver->id,
                        'company_id' => $driver->companyName,
                        'reminder_type' => 'Tacho Card Expiry',
                        'reminder_date' => $driver->tacho_card_valid_to,
                    ],
                    [
                        'status' => 'Pending',
                        'reminder_parameter' => 'tacho_card_valid_to',
                    ]
                );
            }
        }

        return response()->json(['message' => 'Reminders saved successfully!'], 200);
    }

    // API 2: Send reminders from DriverReminderLog
    public function sendPendingDriverReminders()
    {
        $reminders = \App\Models\DriverReminderLog::whereIn('status', ['Pending', 'Failed'])->get();

        // Group reminders by driver_id
        $groupedReminders = $reminders->groupBy('driver_id');

        foreach ($groupedReminders as $driverId => $reminders) {
            $driver = \App\Models\Driver::find($driverId);
            if (! $driver || empty($driver->contact_email)) {
                continue;
            }

            // Prepare email data
            $emailData = [
                'driverName' => $driver->name,
                'licenseNumber' => $driver->driver_licence_no,
                'tachoCardNo' => $driver->tacho_card_no,
                'companyName' => $driver->types->name,
                'cpcCard' => null,
                'expiryDates' => [
                    'driver_licence_expiry' => null,
                    'cpc_validto' => null,
                    'tacho_card_valid_to' => null,
                ],
            ];

            foreach ($reminders as $reminder) {
                $reminderType = $reminder->reminder_type;
                $reminderValue = $reminder->reminder_date;

                if ($reminderType == 'Driver Licence Expiry') {
                    $emailData['expiryDates']['driver_licence_expiry'] = $reminderValue;
                } elseif ($reminderType == 'CPC Card Expiry') {
                    $emailData['expiryDates']['cpc_validto'] = $reminderValue;
                    $emailData['cpcCard'] = $reminderValue;
                } elseif ($reminderType == 'Tacho Card Expiry') {
                    $emailData['expiryDates']['tacho_card_valid_to'] = $reminderValue;
                }
            }

            try {
                // Send emails
                \Mail::to($driver->types->email)->send(new \App\Mail\DriverLicenseReminderToCompany($emailData));
                \Mail::to($driver->contact_email)->send(new \App\Mail\DriverLicenseReminder($emailData));

                // If emails sent successfully, mark reminders as "Sent"
                \App\Models\DriverReminderLog::whereIn('id', $reminders->pluck('id'))->update(['status' => 'Sent']);
            } catch (\Exception $e) {
                // Log error for debugging
                \Log::error('Failed to send driver reminder emails: '.$e->getMessage());

                // If email sending fails, mark status as "Failed"
                \App\Models\DriverReminderLog::whereIn('id', $reminders->pluck('id'))->update(['status' => 'Failed']);
            }
        }

        return response()->json(['message' => 'Reminders Sent successfully!'], 200);
    }

    public function driverDataExport(Request $request)
    {
        $loggedInUser = \Auth::user();
        $companyName = $loggedInUser->companyname; // Company name of the logged-in user

        // Handle multiple depot IDs (convert stored JSON to array if needed)
        $depotIds = is_array($loggedInUser->depot_id) ? $loggedInUser->depot_id : json_decode($loggedInUser->depot_id, true);
        if (! is_array($depotIds)) {
            $depotIds = [$loggedInUser->depot_id]; // Ensure it remains an array
        }

        // Handle driver group restriction
        $userGroupIds = is_array($loggedInUser->driver_group_id)
            ? $loggedInUser->driver_group_id
            : json_decode($loggedInUser->driver_group_id, true);

        if (! is_array($userGroupIds)) {
            $userGroupIds = [];
        }

        // Retrieve the filters for export
        $selectedCompanyId = $request->input('company_id');
        $selectedDriverStatus = $request->input('driver_status');
        $selectedDepotIds = $request->input('depot_id');
 $selectedGroupId = $request->input('group_id');

        // default Active if not selected
        if (empty($selectedDriverStatus)) {
            $selectedDriverStatus = 'Active';
        }

        if (empty($selectedDepotIds)) {
            $selectedDepotIds = [];   // always ensure array
        } elseif (! is_array($selectedDepotIds)) {
            // support comma-separated or single values
            $selectedDepotIds = explode(',', $selectedDepotIds);
        }

        $selectedCpcStatus = $request->input('cpc_status');
        $selectedTachoCardStatus = $request->input('tacho_card_status');
        $selectedIds = $request->has('ids') ? explode(',', $request->input('ids')) : [];

        if ((\Auth::user()->hasRole('company')) || \Auth::user()->hasRole('PTC manager')) {
            // Fetch all data from the Driver model for super admin, ensuring company status is Active
            $data = \App\Models\Driver::with(['types', 'companyDetails', 'group', 'depot', 'driverUser'])
                ->whereHas('companyDetails', function ($query) {
                    $query->where('company_status', 'Active');
                })
                ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('companyName', $selectedCompanyId);
                })
                ->when(! empty($selectedDepotIds), function ($query) use ($selectedDepotIds) {
                    return $query->whereIn('depot_id', $selectedDepotIds);
                })
                ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                return $query->where('group_id',$selectedGroupId);
                })
                ->when($selectedDriverStatus, function ($query) use ($selectedDriverStatus) {
                    return $query->where('driver_status', $selectedDriverStatus);
                })
                ->when($selectedCpcStatus, function ($query) use ($selectedCpcStatus) {
                    return $query->where('cpc_status', $selectedCpcStatus);
                })
                ->when($selectedTachoCardStatus, function ($query) use ($selectedTachoCardStatus) {
                    return $query->where('tacho_card_status', $selectedTachoCardStatus);
                })->when(! empty($selectedIds), function ($query) use ($selectedIds) {   // ✅ added for selected checkboxes
                    return $query->whereIn('id', $selectedIds);
                })->get();
        } else {
            // Fetch only the drivers associated with the authenticated user's company, ensuring company status is Active
            $data = \App\Models\Driver::with(['types', 'companyDetails', 'depot', 'group', 'driverUser'])
                ->where('companyName', \Auth::user()->companyname)
                ->whereIn('depot_id', $depotIds)
                ->whereIn('group_id', $userGroupIds)
                ->whereHas('companyDetails', function ($query) {
                    $query->where('company_status', 'Active');
                })
                 ->when(!empty($selectedDepotIds), function ($query) use ($selectedDepotIds) {
                return $query->whereIn('depot_id',$selectedDepotIds);
            })

            ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                return $query->where('group_id',$selectedGroupId);
                })
                ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('companyName', $selectedCompanyId);
                })
                ->when($selectedDriverStatus, function ($query) use ($selectedDriverStatus) {
                    return $query->where('driver_status', $selectedDriverStatus);
                })
                ->when($selectedCpcStatus, function ($query) use ($selectedCpcStatus) {
                    return $query->where('cpc_status', $selectedCpcStatus);
                })
                ->when($selectedTachoCardStatus, function ($query) use ($selectedTachoCardStatus) {
                    return $query->where('tacho_card_status', $selectedTachoCardStatus);
                })
                ->when(! empty($selectedIds), function ($query) use ($selectedIds) {   // ✅ added for selected checkboxes
                    return $query->whereIn('id', $selectedIds);
                })->get();
        }

        // Adjust the export logic as per your requirement
        $name = 'Driver Data_'.date('d-m-Y');

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DriverDataExport($data), $name.'.xlsx');
    }

    public function calculateAge()
    {
        $drivers = Driver::all();

        // Loop through each driver
        foreach ($drivers as $driver) {
            // Check if driver_dob is not empty
            if (! empty($driver->driver_dob)) {
                $dob = \Carbon\Carbon::createFromFormat('d/m/Y', $driver->driver_dob);
                $now = \Carbon\Carbon::now();
                $age = $dob->diffInYears($now);

                // Update driver_age field
                $driver->driver_age = $age;
            } else {
                // Set driver_age to null if driver_dob is empty
                $driver->driver_age = null;
            }
            $driver->save();
        }

        return response()->json(['message' => 'Driver ages updated successfully']);
    }

    public function updateExpiredStatus(Request $request)
    {
        $today = \Carbon\Carbon::today();

        $expiryThresholds = [
            30 => 'EXPIRING SOON',   // Within 30 days
            60 => 'EXPIRING SOON',   // Within 60 days
            90 => 'EXPIRING SOON',   // Within 90 days
        ];

        // Fetch all drivers
        $drivers = Driver::all();

        foreach ($drivers as $driver) {
            // // Parse the driver_licence_expiry date assuming dd/mm/yyyy format
            // $licenceExpiryDate = \DateTime::createFromFormat('d/m/Y', $driver->driver_licence_expiry);

            // Parse the cpc_validto date assuming dd/mm/yyyy format
            $cpcValidtoDate = \DateTime::createFromFormat('d/m/Y', $driver->cpc_validto);

            $tachoCardValidToDate = \DateTime::createFromFormat('d/m/Y', $driver->tacho_card_valid_to);

            // // Determine licence status
            // if ($licenceExpiryDate) {
            //     $licenceDaysDifference = \Carbon\Carbon::parse($licenceExpiryDate)->diffInDays($today);

            //     if ($licenceExpiryDate < $today) {
            //         $driver->driver_licence_status = 'EXPIRED';
            //     } elseif ($licenceDaysDifference <= 90 && $licenceDaysDifference >= 0) {
            //         $driver->driver_licence_status = 'EXPIRING SOON';
            //     } else {
            //         $driver->driver_licence_status = 'VALID';
            //     }
            // } else {
            //     $driver->driver_licence_status = '-';
            // }

            // Determine CPC status
            if ($cpcValidtoDate) {
                $cpcDaysDifference = \Carbon\Carbon::parse($cpcValidtoDate)->diffInDays($today);

                if ($cpcValidtoDate < $today) {
                    $driver->cpc_status = 'EXPIRED';
                } elseif ($cpcDaysDifference <= 90 && $cpcDaysDifference >= 0) {
                    $driver->cpc_status = 'EXPIRING SOON';
                } else {
                    $driver->cpc_status = 'VALID';
                }
            } else {
                $driver->cpc_status = '-';
            }

            // Determine Tacho card status
            if ($tachoCardValidToDate) {
                $tachoDaysDifference = \Carbon\Carbon::parse($tachoCardValidToDate)->diffInDays($today);

                if ($tachoCardValidToDate < $today) {
                    $driver->tacho_card_status = 'EXPIRED';
                } elseif ($tachoDaysDifference <= 90 && $tachoDaysDifference >= 0) {
                    $driver->tacho_card_status = 'EXPIRING SOON';
                } else {
                    $driver->tacho_card_status = 'VALID';
                }
            } else {
                $driver->tacho_card_status = '-';
            }

            // Save the updated statuses
            $driver->save();
        }

        return response()->json(['message' => 'Driver licence statuses updated successfully.']);
    }

    public function sendMedicalInsuranceReminders()
    {
        // Calculate the date thresholds
        $currentDate = now();

        // Medical intervals (in years)
        $medicalIntervals = [45, 50, 55, 60, 65];

        // Reminder date (3 months from now)
        $reminderDate = $currentDate->copy()->addMonths(3);

        // Fetch drivers based on the intervals
        foreach ($medicalIntervals as $interval) {
            // Calculate birthdate threshold
            $birthdateThreshold = $reminderDate->copy()->subYears($interval);

            // Format birthdate threshold for comparison in dd/mm/yyyy format
            $birthdateThresholdFormatted = $birthdateThreshold->format('d/m/Y');

            // Log to check which drivers are fetched
            // \Log::info('Fetching drivers with birthdate threshold: '.$birthdateThresholdFormatted);

            // Fetch drivers whose dob is exactly the threshold date and are active or inactive
            $drivers = Driver::where('driver_dob', '=', $birthdateThresholdFormatted)
                ->whereIn('driver_status', ['Active', 'InActive'])
                ->get();

            // Log to check fetched drivers
            // \Log::info('Found '.count($drivers).' drivers for interval '.$interval);

            // Group drivers by their company
            $driversByCompany = $drivers->groupBy('companyName');

            // Send reminders to the drivers
            foreach ($driversByCompany as $companyId => $companyDrivers) {
                // Retrieve company details for sending reminder to the company
                $company = \App\Models\CompanyDetails::find($companyId);
                if ($company && $company->company_status === 'Active') {
                    if ($company->email) {
                        // Send email to company with drivers' names
                        \Mail::to($company->email)->send(new \App\Mail\MedicalInsuranceReminderToCompany($company, $companyDrivers->toArray()));
                    }

                    // Send individual reminder emails to drivers
                    foreach ($companyDrivers as $driver) {
                        if ($driver->contact_email) {
                            \Mail::to($driver->contact_email)->send(new \App\Mail\MedicalInsuranceReminder($driver));
                        }
                    }
                }
            }
        }

        // Additional logic for ages from 67 to 100
        for ($age = 67; $age <= 100; $age++) {
            $birthdateThreshold = $reminderDate->copy()->subYears($age);

            // Format birthdate threshold for comparison in dd/mm/yyyy format
            $birthdateThresholdFormatted = $birthdateThreshold->format('d/m/Y');

            // Log to check which drivers are fetched
            // \Log::info('Fetching drivers with birthdate threshold: '.$birthdateThresholdFormatted);

            // Fetch drivers whose dob is exactly the threshold date and are active or inactive
            $drivers = Driver::where('driver_dob', '=', $birthdateThresholdFormatted)
                ->whereIn('driver_status', ['Active', 'InActive'])
                ->get();

            // Log to check fetched drivers
            // \Log::info('Found '.count($drivers).' drivers for age '.$age);

            if ($drivers->isEmpty()) {
                continue; // Skip to the next age if no drivers are found
            }

            // Group drivers by their company
            $driversByCompany = $drivers->groupBy('companyName');

            // Send reminders to the drivers
            foreach ($driversByCompany as $companyId => $companyDrivers) {
                // Retrieve company details for sending reminder to the company
                $company = \App\Models\CompanyDetails::find($companyId);
                if ($company && $company->company_status === 'Active') {
                    if ($company->email) {
                        // Send email to company with drivers' names
                        \Mail::to($company->email)->send(new \App\Mail\MedicalInsuranceReminderToCompany($company, $companyDrivers->toArray()));
                    }

                    // Send individual reminder emails to drivers
                    foreach ($companyDrivers as $driver) {
                        if ($driver->contact_email) {
                            \Mail::to($driver->contact_email)->send(new \App\Mail\MedicalInsuranceReminder($driver));
                        }
                    }
                }
            }
        }

        return response()->json(['message' => 'Medical reminders sent successfully']);
    }

    private function getToken()
    {
        // Attempt to retrieve token from cache
        $token = Cache::get('api_token');

        // If token is not available or expired, fetch a new one
        if (! $token) {
            $response = Http::post('https://driver-vehicle-licensing.api.gov.uk/thirdparty-access/v1/authenticate', [
                'userName' => 'paramounttransportconsultantsltd',
                'password' => 'PtC@2026',
            ]);

            if ($response->successful()) {
                $token = $response->json()['id-token'];
                // Store token in cache for 1 hour
                Cache::put('api_token', $token, now()->addHours(1));
            } else {
                throw new \Exception('Authentication failed');
            }
        }

        return $token;
    }

    // public function retrieveDriverData(Request $request)
    // {
    //     if (\Auth::user()->can('create driver')) {
    //         $request->validate([
    //             'drivingLicenceNumber' => 'required|string',
    //             'companyName' => 'required|integer|exists:company_details,id',
    //             'ni_number' => 'nullable|string',
    //             'contact_no' => 'nullable|string',
    //             'contact_email' => 'nullable|email',
    //         ]);

    //         $drivingLicenceNumber = $request->input('drivingLicenceNumber');
    //         $companyDetailId = $request->input('companyName');
    //         $niNumber = $request->input('ni_number');
    //         $contactNo = $request->input('contact_no');
    //         $contactEmail = $request->input('contact_email');
    //         $token = $this->getToken();

    //         // Format the contact number
    //         $formattedContactNo = $this->formatUKPhoneNumber($contactNo);

    //         $response = Http::withHeaders([
    //             'x-api-key' => 'HUxGk2P6SR7qOPb6LUoMrQUYG0oQXRG3CBs1QyZ2',
    //             'Authorization' => $token,
    //         ])->post('https://driver-vehicle-licensing.api.gov.uk/full-driver-enquiry/v1/driving-licences/retrieve', [
    //             'drivingLicenceNumber' => $drivingLicenceNumber,
    //             'includeCPC' => true,
    //             'includeTacho' => true,
    //             'acceptPartialResponse' => 'true',
    //         ]);

    //         if ($response->successful()) {
    //             $data = $response->json();

    //             // Calculate age from date of birth
    //             $driverDob = $data['driver']['dateOfBirth'] ?? null;
    //             $driverAge = $driverDob ? $this->calculateAgeDriver($driverDob) : null;

    //             // Save driver details
    //             $driver = Driver::updateOrCreate(
    //                 ['driver_licence_no' => $data['driver']['drivingLicenceNumber']],
    //                 [
    //                     'companyName' => $companyDetailId, // Save the company_detail_id
    //                     'ni_number' => $niNumber,
    //                     'contact_no' => $formattedContactNo,
    //                     'contact_email' => $contactEmail,
    //                     'driver_age' => $driverAge,
    //                     'last_name' => $data['driver']['lastName'] ?? null,
    //                     'gender' => $data['driver']['gender'] ?? null,
    //                     'first_names' => $data['driver']['firstNames'] ?? null,
    //                     'driver_dob' => $driverDob,
    //                     'address_line1' => $data['driver']['address']['unstructuredAddress']['line1'] ?? null,
    //                     'address_line5' => $data['driver']['address']['unstructuredAddress']['line5'] ?? null,
    //                     'post_code' => $data['driver']['address']['unstructuredAddress']['postcode'] ?? null,
    //                     'licence_type' => $data['licence']['type'] ?? null,
    //                     'driver_licence_status' => $data['licence']['status'] ?? null,
    //                 ]
    //             );

    //             // Save endorsements
    //             foreach ($data['endorsements'] ?? [] as $endorsement) {
    //                 Endorsement::updateOrCreate(
    //                     ['driver_id' => $driver->id, 'offence_code' => $endorsement['offenceCode']],
    //                     [
    //                         'penalty_points' => $endorsement['penaltyPoints'] ?? null,
    //                         'offence_legal_literal' => $endorsement['offenceLegalLiteral'] ?? null,
    //                         'offence_date' => $endorsement['offenceDate'] ?? null,
    //                         'conviction_date' => $endorsement['convictionDate'] ?? null,
    //                     ]
    //                 );
    //             }

    //             // Save entitlements
    //             foreach ($data['entitlement'] ?? [] as $entitlement) {
    //                 Entitlement::updateOrCreate(
    //                     ['driver_id' => $driver->id, 'category_code' => $entitlement['categoryCode']],
    //                     [
    //                         'category_legal_literal' => $entitlement['categoryLegalLiteral'] ?? null,
    //                         'category_type' => $entitlement['categoryType'] ?? null,
    //                         'from_date' => $entitlement['fromDate'] ?? null,
    //                         'expiry_date' => $entitlement['expiryDate'] ?? null,
    //                         'restrictions' => json_encode($entitlement['restrictions'] ?? []),
    //                     ]
    //                 );
    //             }

    //             // Save tacho cards
    //             foreach ($data['holder']['tachoCards'] ?? [] as $tachoCard) {
    //                 TachoCard::updateOrCreate(
    //                     ['driver_id' => $driver->id, 'tacho_card_no' => $tachoCard['cardNumber']],
    //                     [
    //                         'card_status' => $tachoCard['cardStatus'] ?? null,
    //                         'tacho_card_valid_to' => $tachoCard['cardExpiryDate'] ?? null,
    //                         'tacho_card_valid_from' => $tachoCard['cardStartOfValidityDate'] ?? null,
    //                     ]
    //                 );
    //             }

    //             // Save CPCs
    //             foreach ($data['cpc']['cpcs'] ?? [] as $cpc) {
    //                 Cpc::updateOrCreate(
    //                     ['driver_id' => $driver->id, 'cpc_validto' => $cpc['lgvValidTo']],
    //                     [
    //                         'cpc_validto' => $cpc['lgvValidTo'] ?? null,
    //                     ]
    //                 );
    //             }

    //             // Save DQCs
    //             foreach ($data['dqc']['dqcs'] ?? [] as $dqc) {
    //                 Dqc::updateOrCreate(
    //                     ['driver_id' => $driver->id, 'issue_date' => $dqc['issueDate']],
    //                     [
    //                         'issue_date' => $dqc['issueDate'] ?? null,
    //                     ]
    //                 );
    //             }

    //             // Save token data
    //             if (isset($data['token'])) {
    //                 Token::updateOrCreate(
    //                     ['driver_id' => $driver->id, 'issue_number' => $data['token']['issueNumber']], // Assuming issue number is unique per driver
    //                     [
    //                         'driver_id' => $driver->id,
    //                         'issue_number' => $data['token']['issueNumber'],
    //                         'valid_from_date' => $data['token']['validFromDate'],
    //                         'driver_licence_expiry' => $data['token']['validToDate'],
    //                     ]
    //                 );
    //             }

    //             return redirect()->route('driver.index')->with('success', __('Driver successfully created.'));
    //         }

    //         return response()->json(['error' => 'Failed to retrieve driver data'], 400);
    //     } else {
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    public function historyindex($id)
    {
        // Check if the driver exists
        $driver = Driver::with('duplicateDrivers', 'creator')->findOrFail($id);

        // Check if the company status is active
        $companyDetails = CompanyDetails::where('company_status', 'Active')->get();

        // If no active companies, return error message
        if ($companyDetails->isEmpty()) {
            return redirect()->back()->with('error', 'No active companies found.');
        }

        // If no company is found for the given driver, return error
        $driverCompany = $driver->company; // Assuming there's a company relationship

        if (! $driverCompany || $driverCompany->company_status !== 'Active') {
            return redirect()->back()->with('error', 'Company status is not active.');
        }

        return view('driver.history.index', compact('driver', 'companyDetails'));
    }

    public function historyshow($id)
    {
        // Retrieve the driver along with related company details
        $driver = \App\Models\DuplicateDriver::with('entitlements', 'companyDetails')->findOrFail($id);

        // Check if the company_status is active
        if ($driver->companyDetails && $driver->companyDetails->company_status !== 'Active') {
            // If not active, return an error message or redirect
            return redirect()->back()->with('error', 'Driver is not associated with an active company.');
        }

        // Decode the JSON data
        $endorsements = json_decode($driver->endorsements, true);

        // Initialize variables
        $firstPenaltyPoints = '0';
        $offenceCodes = [];

        // Loop through the endorsements
        foreach ($endorsements as $endorsement) {
            // Check for penaltyPoints and set the first value
            if (isset($endorsement['penaltyPoints']) && $firstPenaltyPoints === '0') {
                $firstPenaltyPoints = $endorsement['penaltyPoints'];
            }

            // Collect offenceCodes
            if (isset($endorsement['offenceCode'])) {
                $offenceCodes[] = $endorsement['offenceCode'];
            }
        }

        // Count unique offenceCodes
        $uniqueOffenceCodeCount = count(array_unique($offenceCodes));

        return view('driver.history.show', compact('driver', 'firstPenaltyPoints', 'uniqueOffenceCodeCount', 'endorsements'));
    }

    public function historydownloadDriverInfo($slug)
    {
        // Decode the slug to get the driver ID
        $id = base64_decode($slug);

        // Ensure the decoded ID is valid
        if (! is_numeric($id)) {
            return redirect()->back()->with('error', __('Invalid driver ID.'));
        }

        // Find the driver by ID
        $driver = \App\Models\DuplicateDriver::with('entitlements')->findOrFail($id);

        if (! $driver) {
            return redirect()->back()->with('error', __('Driver not found.'));
        }

        $settings = \App\Models\Utility::settings();
        $company_logo = \App\Models\Utility::getValByName('company_logo');
        $imagePath = storage_path('/uploads/logo/'.(isset($company_logo) && ! empty($company_logo) ? $company_logo : '5-logo-dark.png'));

        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $img = 'data:image/png;base64,'.$imageData;
        } else {
            \Log::error('Image file does not exist: '.$imagePath);
            $img = ''; // Fallback or default image if necessary
        }

        // Decode the JSON data
        $endorsements = json_decode($driver->endorsements, true);

        // Initialize variables
        $firstPenaltyPoints = '0';
        $offenceCodes = [];

        // Loop through the endorsements
        foreach ($endorsements as $endorsement) {
            // Check for penaltyPoints and set the first value
            if (isset($endorsement['penaltyPoints']) && $firstPenaltyPoints === '0') {
                $firstPenaltyPoints = $endorsement['penaltyPoints'];
            }

            // Collect offenceCodes
            if (isset($endorsement['offenceCode'])) {
                $offenceCodes[] = $endorsement['offenceCode'];
            }
        }
        $endorsements = $driver->endorsements ? json_decode($driver->endorsements, true) : [];

        // Count unique offenceCodes
        $uniqueOffenceCodeCount = count(array_unique($offenceCodes));

        $view = view('driver.history.template', compact('driver', 'img', 'settings', 'firstPenaltyPoints', 'uniqueOffenceCodeCount', 'endorsements'))->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($view)
            ->setOptions(['defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);

        $filename = 'Driver Information ('.$driver->name.').pdf';

        return $pdf->stream($filename);
    }
    //     public function automationupdateAll(Request $request )
    //     {
    //         try {
    //         // Retrieve all drivers
    //         $drivers = Driver::all();
    //           // Set the current date
    //         $currentDate = Carbon::now()->format('d/m/Y'); // Ensure the date format is consistent
    //         // \Log::info('Current date: ' . $currentDate); // Log current date for debugging

    //         foreach ($drivers as $driver) {
    //             // Check if latest_lc_check is null
    //             if ($driver->latest_lc_check === null) {
    //                 // \Log::info('Driver ' . $driver->id . ' has a null latest_lc_check.');
    //                 continue; // Skip if there's no latest_lc_check
    //             }

    //             // Log the value of latest_lc_check for debugging
    //             // \Log::info('Driver ' . $driver->id . ' latest_lc_check: ' . $driver->latest_lc_check);

    //             // Parse the latest_lc_check using the correct format
    //             $latestLcCheck = Carbon::createFromFormat('d/m/Y H:i:s', $driver->latest_lc_check)->format('d/m/Y');

    //             // Log the correctly formatted latest_lc_check for debugging
    //             // \Log::info('Formatted latest_lc_check for driver ' . $driver->id . ': ' . $latestLcCheck);

    //             // Check if the driver's latest_lc_check is the current date and automation is "Yes"
    //             if ($latestLcCheck <= $currentDate && $driver->automation === 'Yes') {
    //                                 // \Log::info('Driver ' . $driver->id . ' latest_lc_check matches the current date.');

    //             $companyName = $driver->companyName; // Assuming the Driver model has a company_name field

    //             $token = $this->getToken();

    //             $response = Http::withHeaders([
    //                 'x-api-key' => 'HUxGk2P6SR7qOPb6LUoMrQUYG0oQXRG3CBs1QyZ2',
    //                 'Authorization' => $token,
    //             ])->post('https://driver-vehicle-licensing.api.gov.uk/full-driver-enquiry/v1/driving-licences/retrieve', [
    //                 'drivingLicenceNumber' => $driver->driver_licence_no,
    //                 'includeCPC' => true,
    //                 'includeTacho' => true,
    //                 'acceptPartialResponse' => 'true',
    //             ]);

    //             if ($response->successful()) {
    //                 $data = $response->json();

    //                 // Calculate age from date of birth
    //                 $driverDob = $data['driver']['dateOfBirth'] ?? null;
    //                 $driverAge = $driverDob ? $this->calculateAgeDriver($driverDob) : null;

    //                 // Format dates
    //                 $formattedDriverDob = $this->formatDateToDDMMYYYY($driverDob);
    //                 $formattedFromDate = $this->formatDateToDDMMYYYY($data['entitlement'][0]['fromDate'] ?? null);
    //                 $formattedExpiryDate = $this->formatDateToDDMMYYYY($data['entitlement'][0]['expiryDate'] ?? null);
    //                 $formattedValidFromDate = $this->formatDateToDDMMYYYY($data['token']['validFromDate'] ?? null);
    //                 $formattedValidToDate = $this->formatDateToDDMMYYYY($data['token']['validToDate'] ?? null);
    //                 $formattedCardExpiryDate = $this->formatDateToDDMMYYYY($data['holder']['tachoCards'][0]['cardExpiryDate'] ?? null);
    //                 $formattedCardStartOfValidityDate = $this->formatDateToDDMMYYYY($data['holder']['tachoCards'][0]['cardStartOfValidityDate'] ?? null);

    //                 // Determine the latest CPC date
    //                 $latestLgvValidTo = null;
    //                 if (isset($data['cpc']) && is_array($data['cpc']['cpcs'])) {
    //                         foreach ($data['cpc']['cpcs'] as $cpc) {
    //                             $lgvValidTo = $cpc['lgvValidTo'] ?? null;
    //                             if ($lgvValidTo && ($latestLgvValidTo === null || $lgvValidTo > $latestLgvValidTo)) {
    //                                 $latestLgvValidTo = $lgvValidTo;
    //                             }
    //                         }
    //                     }
    //                 $formattedLgvValidTo = $this->formatDateToDDMMYYYY($latestLgvValidTo);

    //                 $formattedIssueDate = $this->formatDateToDDMMYYYY($data['dqc']['dqcs'][0]['issueDate'] ?? null);

    //                 $fullName = trim(($data['driver']['firstNames'] ?? '') . ' ' . ($data['driver']['lastName'] ?? ''));
    //                 $addressLine1 = $data['driver']['address']['unstructuredAddress']['line1'] ?? '';
    //                 $addressLine2 = $data['driver']['address']['unstructuredAddress']['line2'] ?? '';
    //                 $addressLine3 = $data['driver']['address']['unstructuredAddress']['line3'] ?? '';
    //                 $addressLine4 = $data['driver']['address']['unstructuredAddress']['line4'] ?? '';
    //                 $addressLine5 = $data['driver']['address']['unstructuredAddress']['line5'] ?? '';
    //                 $fullAddress = trim($addressLine1 . ' ' . $addressLine2 . ' ' . $addressLine3 . ' ' . $addressLine4 . ' ' . $addressLine5);

    //                 // Determine the licence check interval based on endorsements
    //                 $penaltyPoints = 0;
    //                 if (isset($data['endorsements']) && is_array($data['endorsements'])) {
    //                     foreach ($data['endorsements'] as $endorsement) {
    //                         if (isset($endorsement['penaltyPoints'])) {
    //                             $penaltyPoints = max($penaltyPoints, $endorsement['penaltyPoints']);
    //                         }
    //                     }
    //                 }
    //                 $checkInterval = $this->calculateCheckInterval($penaltyPoints);

    //                 // Get current date and time in UK timezone
    //                 $latestLcCheck = Carbon::now('Europe/London')->format('d/m/Y H:i:s');

    //  // Calculate next_lc_check
    //                 $nextLcValidUntil = null;
    //                 if ($penaltyPoints < 5) {
    //                     $nextLcValidUntil = Carbon::createFromFormat('d/m/Y H:i:s', $latestLcCheck)
    //                         ->addMonths(3)
    //                         ->format('d/m/Y');
    //                 } else {
    //                     $nextLcValidUntil = Carbon::createFromFormat('d/m/Y H:i:s', $latestLcCheck)
    //                         ->addMonths()
    //                         ->format('d/m/Y');
    //                 }

    //                 // Save driver details
    //                 $driver->update([
    //                     'driver_age' => $driverAge,
    //                     'name' => $fullName,
    //                     'last_name' => $data['driver']['lastName'] ?? null,
    //                     'gender' => $data['driver']['gender'] ?? null,
    //                     'first_names' => $data['driver']['firstNames'] ?? null,
    //                     'driver_dob' => $formattedDriverDob,
    //                     'driver_address' => $fullAddress,
    //                     'address_line1' => $addressLine1,
    //                     'address_line2' => $addressLine2,
    //                     'address_line3' => $addressLine3,
    //                     'address_line4' => $addressLine4,
    //                     'address_line5' => $addressLine5,
    //                     'post_code' => $data['driver']['address']['unstructuredAddress']['postcode'] ?? null,
    //                     'licence_type' => $data['licence']['type'] ?? null,
    //                     'driver_licence_status' => $data['licence']['status'] ?? null,
    //                     'tacho_card_no' => $data['holder']['tachoCards'][0]['cardNumber'] ?? null,
    //                     'tacho_card_valid_to' => $formattedCardExpiryDate,
    //                     'tacho_card_valid_from' => $formattedCardStartOfValidityDate,
    //                     'token_issue_number' => $data['token']['issueNumber'] ?? null,
    //                     'token_valid_from_date' => $formattedValidFromDate,
    //                     'driver_licence_expiry' => $formattedValidToDate,
    //                     'cpc_validto' => $formattedLgvValidTo, // Save latest LGV valid to date
    //                     'dqc_issue_date' => $formattedIssueDate,
    //                     'endorsement_penalty_points' => $data['endorsements'][0]['penaltyPoints'] ?? null,
    //                     'endorsement_offence_code' => $data['endorsements'][0]['offenceCode'] ?? null,
    //                     'endorsement_offence_legal_literal' => $data['endorsements'][0]['offenceLegalLiteral'] ?? null,
    //                     'endorsement_offence_date' => $data['endorsements'][0]['offenceDate'] ?? null,
    //                     'endorsement_conviction_date' => $data['endorsements'][0]['convictionDate'] ?? null,
    //                     'endorsements' => json_encode($data['endorsements'] ?? []), // Save endorsements as JSON
    //                     'current_licence_check_interval' => $checkInterval,
    //                     'latest_lc_check' => $latestLcCheck, // Add the latest license check date and time
    //                     'next_lc_check' => $nextLcValidUntil,
    //                         'created_by' => 1.1,
    //                 ]);

    //                     // Create a duplicate driver entry
    //                     $duplicateDriver = \App\Models\DuplicateDriver::create([
    //                     'driver_modal_id' => $driver->id,
    //                         'driver_licence_no' => $data['driver']['drivingLicenceNumber'],
    //                         'companyName' => $driver->companyName,
    //                         'ni_number' => $driver->ni_number,
    //                         'contact_no' => $driver->contact_no,
    //                         'contact_email' => $driver->contact_email,
    //                         'driver_age' => $driverAge,
    //                         'name' => $fullName,
    //                         'last_name' => $data['driver']['lastName'] ?? null,
    //                         'gender' => $data['driver']['gender'] ?? null,
    //                         'first_names' => $data['driver']['firstNames'] ?? null,
    //                         'driver_dob' => $formattedDriverDob,
    //                         'driver_address' => $fullAddress,
    //                         'address_line1' => $addressLine1,
    //                         'address_line2' => $addressLine2,
    //                         'address_line3' => $addressLine3,
    //                         'address_line4' => $addressLine4,
    //                         'address_line5' => $addressLine5,
    //                         'driver_status' => $driver->driver_status,
    //                         'post_code' => $data['driver']['address']['unstructuredAddress']['postcode'] ?? null,
    //                         'licence_type' => $data['licence']['type'] ?? null,
    //                         'driver_licence_status' => $data['licence']['status'] ?? null,
    //                         'tacho_card_no' => $data['holder']['tachoCards'][0]['cardNumber'] ?? null,
    //                         'tacho_card_valid_to' => $formattedCardExpiryDate,
    //                         'tacho_card_valid_from' => $formattedCardStartOfValidityDate,
    //                         'token_issue_number' => $data['token']['issueNumber'] ?? null,
    //                         'token_valid_from_date' => $formattedValidFromDate,
    //                         'driver_licence_expiry' => $formattedValidToDate,
    //                         'cpc_validto' => $formattedLgvValidTo,
    //                         'dqc_issue_date' => $formattedIssueDate,
    //                         'endorsement_penalty_points' => $data['endorsements'][0]['penaltyPoints'] ?? null,
    //                         'endorsement_offence_code' => $data['endorsements'][0]['offenceCode'] ?? null,
    //                         'endorsement_offence_legal_literal' => $data['endorsements'][0]['offenceLegalLiteral'] ?? null,
    //                         'endorsement_offence_date' => $data['endorsements'][0]['offenceDate'] ?? null,
    //                         'endorsement_conviction_date' => $data['endorsements'][0]['convictionDate'] ?? null,
    //                         'endorsements' => json_encode($data['endorsements'] ?? []), // Save endorsements as JSON
    //                         'current_licence_check_interval' => $checkInterval,
    //                         'latest_lc_check' => $latestLcCheck, // Add the latest license check date and time
    //                         'next_lc_check' => $nextLcValidUntil,
    //                         'created_by' => 1.1,
    //                 ]);

    //                 // Save entitlements
    //                 foreach ($data['entitlement'] ?? [] as $entitlement) {
    //                     // Convert the restrictions array to JSON
    //                     $restrictions = json_encode($entitlement['restrictions'] ?? []);

    //                     // Ensure unique dates are assigned
    //                     $fromDate = isset($entitlement['fromDate']) ? $this->formatDateToDDMMYYYY($entitlement['fromDate']) : null;
    //                     $expiryDate = isset($entitlement['expiryDate']) ? $this->formatDateToDDMMYYYY($entitlement['expiryDate']) : null;

    //                     // Use the correct from_date and expiry_date for each entitlement
    //                     Entitlement::updateOrCreate(
    //                         [
    //                             'driver_id' => $driver->id,
    //                                 'category_code' => $entitlement['categoryCode'],
    //                                 'from_date' => $fromDate,
    //                                 'expiry_date' => $expiryDate,
    //                             ],
    //                             [
    //                                 'category_legal_literal' => $entitlement['categoryLegalLiteral'] ?? null,
    //                                 'category_type' => $entitlement['categoryType'] ?? null,
    //                                 'restrictions' => $restrictions,
    //                             ]
    //                     );
    //                 }

    //                 // Save entitlements
    //                 foreach ($data['entitlement'] ?? [] as $entitlement) {
    //                     // Convert the restrictions array to JSON
    //                     $restrictions = json_encode($entitlement['restrictions'] ?? []);

    //                     // Ensure unique dates are assigned
    //                     $fromDate = isset($entitlement['fromDate']) ? $this->formatDateToDDMMYYYY($entitlement['fromDate']) : null;
    //                     $expiryDate = isset($entitlement['expiryDate']) ? $this->formatDateToDDMMYYYY($entitlement['expiryDate']) : null;

    //                     // Use the correct from_date and expiry_date for each entitlement
    //                     \App\Models\DuplicateEntitlement::create(
    //                         [
    //                             'driver_id' => $driver->id,
    //                                 'category_code' => $entitlement['categoryCode'],
    //                                 'from_date' => $fromDate,
    //                                 'expiry_date' => $expiryDate,
    //                                 'category_legal_literal' => $entitlement['categoryLegalLiteral'] ?? null,
    //                                 'category_type' => $entitlement['categoryType'] ?? null,
    //                                 'restrictions' => $restrictions,
    //                             ]
    //                     );
    //                 }

    //                 // Find the CompanyDetails record and increment api_call_count
    //                 $companyDetails = CompanyDetails::where('id', $companyName)->first();

    //                 if (!$companyDetails) {
    //                     return redirect()->back()->with('error', 'CompanyDetails record not found.');
    //                 }

    //                 // Increment api_call_count
    //                 $companyDetails->increment('api_call_count');

    //                  // Log the data
    //                     \App\Models\DriverAPILog::create([
    //                         'companyName' => $driver->companyName,
    //                         'created' => \Auth::id(),
    //                         'last_lc_check' => $latestLcCheck,
    //                         'licence_no' => $driver->driver_licence_no,
    //                     ]);

    //                 } else {
    //                     // Log or handle the error if the response is not successful
    //                     // \Log::error("Failed to retrieve data for driver: {$driver->driver_licence_no}, Status: {$response->status()}, Message: {$response->body()}");
    //                 }
    //             } else {
    //                 // \Log::info("Skipping driver: {$driver->driver_licence_no} due to conditions not met.");
    //             }
    //         }

    //         return response()->json(['message' => 'All eligible drivers updated successfully.'], 200);
    //     } catch (\Exception $e) {
    //         \Log::error('Error updating drivers: ' . $e->getMessage());
    //         return response()->json(['message' => 'Error updating drivers.'], 500);
    //     }
    // }

    // public function automationupdateAll(Request $request)
    // {
    //     try {
    //         // Retrieve the current date
    //         $currentDate = Carbon::now()->format('d/m/Y'); // Ensure date format is consistent
    //         // \Log::info('Current date: ' . $currentDate); // Log for debugging

    //         $drivers = Driver::where('driver_status', 'Active')
    //             ->where('consent_form_status', 'Yes')
    //             ->where('automation', 'Yes')
    //             ->whereHas('company', function ($query) {
    //                     $query->where('lc_check_status', 'Enable') // Company lc_check_status = Enable
    //                     ->where('company_status', 'Active'); // Company lc_check_status = Enable
    //             })
    //             ->get() // Fetch all drivers for manual date filtering
    //             ->filter(function ($driver) use ($currentDate) {
    //                 // Ensure next_lc_check is not null
    //                 if (!$driver->next_lc_check) {
    //                     return false;
    //                 }

    //                 // Parse next_lc_check from dd/mm/yyyy format
    //                 try {
    //                     $nextLcCheck = Carbon::createFromFormat('d/m/Y', $driver->next_lc_check);
    //                     $currentDateParsed = Carbon::createFromFormat('d/m/Y', $currentDate);
    //                 } catch (\Exception $e) {
    //                      \Log::error('Date parsing error for driver ID: ' . $driver->id);
    //                     return false;
    //                 }

    //                 // Check if next_lc_check is current or past date
    //                 return $nextLcCheck->lessThanOrEqualTo($currentDateParsed);
    //             });

    //                 $companyGroups = $drivers->groupBy(function ($driver) {
    //                     return $driver->company->id; // Group drivers by company ID
    //                 });

    //                 $companyGroups->each(function ($driversInCompany, $companyId) {
    //                     $companyDetails = $driversInCompany->first()->company; // Get company details from the first driver in the group

    //                     if ($companyDetails && !empty($companyDetails->email)) {
    //                         // Format the current month and year as "Dec '24"
    //                         $currentMonthYear = date("M 'y");

    //                         // Prepare a list of drivers for the company
    //                         $driversList = $driversInCompany->map(function ($driver) {
    //                             // Create the slug for driver PDF download link
    //                             $slug = base64_encode($driver->id);

    //                             return [
    //                                 'name' => $driver->name,
    //                                 'slug' => $slug, // Add slug to driver data for download link
    //                             ];
    //                         });

    //                         // Prepare email data
    //                         $emailData = [
    //                             'companyName' => $companyDetails->name ?? 'Unknown Company',
    //                             'currentMonthYear' => $currentMonthYear,
    //                             'drivers' => $driversList,
    //                         ];

    //                         // Send the email once to the company
    //                         \Mail::to($companyDetails->email)->send(new AutomationEmail($emailData));

    //                         \Log::info('Email sent to company: ' . $companyDetails->email . ' for company ID: ' . $companyId);
    //                     } else {
    //                         \Log::warning('Company email not available for company ID: ' . $companyId);
    //                     }
    //                 });

    //         // Log the count of eligible drivers
    //         \Log::info('Eligible Drivers Count: ' . $drivers->count());

    //         foreach ($drivers as $driver) {
    //             \Log::info('Driver ID: ' . $driver->id . ' - Eligible for Update.');

    //             $companyName = $driver->companyName; // Assuming the Driver model has a company_name field

    //             $token = $this->getToken();

    //             $response = Http::withHeaders([
    //                 'x-api-key' => 'HUxGk2P6SR7qOPb6LUoMrQUYG0oQXRG3CBs1QyZ2',
    //                 'Authorization' => $token,
    //             ])->post('https://driver-vehicle-licensing.api.gov.uk/full-driver-enquiry/v1/driving-licences/retrieve', [
    //                 'drivingLicenceNumber' => $driver->driver_licence_no,
    //                 'includeCPC' => true,
    //                 'includeTacho' => true,
    //                 'acceptPartialResponse' => 'true',
    //             ]);

    //             if ($response->successful()) {
    //                 $data = $response->json();

    //                 // Calculate age from date of birth
    //                 $driverDob = $data['driver']['dateOfBirth'] ?? null;
    //                 $driverAge = $driverDob ? $this->calculateAgeDriver($driverDob) : null;

    //                 // Format dates
    //                 $formattedDriverDob = $this->formatDateToDDMMYYYY($driverDob);
    //                 $formattedFromDate = $this->formatDateToDDMMYYYY($data['entitlement'][0]['fromDate'] ?? null);
    //                 $formattedExpiryDate = $this->formatDateToDDMMYYYY($data['entitlement'][0]['expiryDate'] ?? null);
    //                 $formattedValidFromDate = $this->formatDateToDDMMYYYY($data['token']['validFromDate'] ?? null);
    //                 $formattedValidToDate = $this->formatDateToDDMMYYYY($data['token']['validToDate'] ?? null);
    //                 $formattedCardExpiryDate = $this->formatDateToDDMMYYYY($data['holder']['tachoCards'][0]['cardExpiryDate'] ?? null);
    //                 $formattedCardStartOfValidityDate = $this->formatDateToDDMMYYYY($data['holder']['tachoCards'][0]['cardStartOfValidityDate'] ?? null);

    //                 // Determine the latest CPC date
    //                 $latestLgvValidTo = null;
    //                 if (isset($data['cpc']) && is_array($data['cpc']['cpcs'])) {
    //                     foreach ($data['cpc']['cpcs'] as $cpc) {
    //                         $lgvValidTo = $cpc['lgvValidTo'] ?? null;
    //                         if ($lgvValidTo && ($latestLgvValidTo === null || $lgvValidTo > $latestLgvValidTo)) {
    //                             $latestLgvValidTo = $lgvValidTo;
    //                         }
    //                     }
    //                 }
    //                 $formattedLgvValidTo = $this->formatDateToDDMMYYYY($latestLgvValidTo);

    //                 $formattedIssueDate = $this->formatDateToDDMMYYYY($data['dqc']['dqcs'][0]['issueDate'] ?? null);

    //                 $fullName = trim(($data['driver']['firstNames'] ?? '') . ' ' . ($data['driver']['lastName'] ?? ''));
    //                 $addressLine1 = $data['driver']['address']['unstructuredAddress']['line1'] ?? '';
    //                 $addressLine2 = $data['driver']['address']['unstructuredAddress']['line2'] ?? '';
    //                 $addressLine3 = $data['driver']['address']['unstructuredAddress']['line3'] ?? '';
    //                 $addressLine4 = $data['driver']['address']['unstructuredAddress']['line4'] ?? '';
    //                 $addressLine5 = $data['driver']['address']['unstructuredAddress']['line5'] ?? '';
    //                 $fullAddress = trim($addressLine1 . ' ' . $addressLine2 . ' ' . $addressLine3 . ' ' . $addressLine4 . ' ' . $addressLine5);

    //                 // Determine the licence check interval based on endorsements
    //                 $penaltyPoints = 0;
    //                 if (isset($data['endorsements']) && is_array($data['endorsements'])) {
    //                     foreach ($data['endorsements'] as $endorsement) {
    //                         if (isset($endorsement['penaltyPoints'])) {
    //                             $penaltyPoints = max($penaltyPoints, $endorsement['penaltyPoints']);
    //                         }
    //                     }
    //                 }
    //                 $checkInterval = $this->calculateCheckInterval($penaltyPoints);

    //                 // Get current date and time in UK timezone
    //                 $latestLcCheck = Carbon::now('Europe/London')->format('d/m/Y H:i:s');

    //                 // Calculate next_lc_check
    //                 $nextLcValidUntil = null;
    //                 if ($penaltyPoints < 5) {
    //                     $nextLcValidUntil = Carbon::createFromFormat('d/m/Y H:i:s', $latestLcCheck)
    //                         ->addMonths(3)
    //                         ->format('d/m/Y');
    //                 } else {
    //                     $nextLcValidUntil = Carbon::createFromFormat('d/m/Y H:i:s', $latestLcCheck)
    //                         ->addMonths()
    //                         ->format('d/m/Y');
    //                 }

    //                 // Save driver details
    //                 $driver->update([
    //                     'driver_age' => $driverAge,
    //                     'name' => $fullName,
    //                     'last_name' => $data['driver']['lastName'] ?? null,
    //                     'gender' => $data['driver']['gender'] ?? null,
    //                     'first_names' => $data['driver']['firstNames'] ?? null,
    //                     'driver_dob' => $formattedDriverDob,
    //                     'driver_address' => $fullAddress,
    //                     'address_line1' => $addressLine1,
    //                     'address_line2' => $addressLine2,
    //                     'address_line3' => $addressLine3,
    //                     'address_line4' => $addressLine4,
    //                     'address_line5' => $addressLine5,
    //                     'post_code' => $data['driver']['address']['unstructuredAddress']['postcode'] ?? null,
    //                     'licence_type' => $data['licence']['type'] ?? null,
    //                     'driver_licence_status' => $data['licence']['status'] ?? null,
    //                     'tacho_card_no' => $data['holder']['tachoCards'][0]['cardNumber'] ?? null,
    //                     'tacho_card_valid_to' => $formattedCardExpiryDate,
    //                     'tacho_card_valid_from' => $formattedCardStartOfValidityDate,
    //                     'token_issue_number' => $data['token']['issueNumber'] ?? null,
    //                     'token_valid_from_date' => $formattedValidFromDate,
    //                     'driver_licence_expiry' => $formattedValidToDate,
    //                     'cpc_validto' => $formattedLgvValidTo, // Save latest LGV valid to date
    //                     'dqc_issue_date' => $formattedIssueDate,
    //                     'endorsement_penalty_points' => $data['endorsements'][0]['penaltyPoints'] ?? null,
    //                     'endorsement_offence_code' => $data['endorsements'][0]['offenceCode'] ?? null,
    //                     'endorsement_offence_legal_literal' => $data['endorsements'][0]['offenceLegalLiteral'] ?? null,
    //                     'endorsement_offence_date' => $data['endorsements'][0]['offenceDate'] ?? null,
    //                     'endorsement_conviction_date' => $data['endorsements'][0]['convictionDate'] ?? null,
    //                     'endorsements' => json_encode($data['endorsements'] ?? []), // Save endorsements as JSON
    //                     'current_licence_check_interval' => $checkInterval,
    //                     'latest_lc_check' => $latestLcCheck, // Add the latest license check date and time
    //                     'next_lc_check' => $nextLcValidUntil,
    //                     'created_by' => 1.1,
    //                 ]);

    //                 // Create a duplicate driver entry
    //                 $duplicateDriver = \App\Models\DuplicateDriver::create([
    //                     'driver_modal_id' => $driver->id,
    //                     'driver_licence_no' => $data['driver']['drivingLicenceNumber'],
    //                     'companyName' => $driver->companyName,
    //                     'ni_number' => $driver->ni_number,
    //                     'contact_no' => $driver->contact_no,
    //                     'contact_email' => $driver->contact_email,
    //                     'driver_age' => $driverAge,
    //                     'name' => $fullName,
    //                     'last_name' => $data['driver']['lastName'] ?? null,
    //                     'gender' => $data['driver']['gender'] ?? null,
    //                     'first_names' => $data['driver']['firstNames'] ?? null,
    //                     'driver_dob' => $formattedDriverDob,
    //                     'driver_address' => $fullAddress,
    //                     'address_line1' => $addressLine1,
    //                     'address_line2' => $addressLine2,
    //                     'address_line3' => $addressLine3,
    //                     'address_line4' => $addressLine4,
    //                     'address_line5' => $addressLine5,
    //                     'driver_status' => $driver->driver_status,
    //                     'post_code' => $data['driver']['address']['unstructuredAddress']['postcode'] ?? null,
    //                     'licence_type' => $data['licence']['type'] ?? null,
    //                     'driver_licence_status' => $data['licence']['status'] ?? null,
    //                     'tacho_card_no' => $data['holder']['tachoCards'][0]['cardNumber'] ?? null,
    //                     'tacho_card_valid_to' => $formattedCardExpiryDate,
    //                     'tacho_card_valid_from' => $formattedCardStartOfValidityDate,
    //                     'token_issue_number' => $data['token']['issueNumber'] ?? null,
    //                     'token_valid_from_date' => $formattedValidFromDate,
    //                     'driver_licence_expiry' => $formattedValidToDate,
    //                     'cpc_validto' => $formattedLgvValidTo,
    //                     'dqc_issue_date' => $formattedIssueDate,
    //                     'endorsement_penalty_points' => $data['endorsements'][0]['penaltyPoints'] ?? null,
    //                     'endorsement_offence_code' => $data['endorsements'][0]['offenceCode'] ?? null,
    //                     'endorsement_offence_legal_literal' => $data['endorsements'][0]['offenceLegalLiteral'] ?? null,
    //                     'endorsement_offence_date' => $data['endorsements'][0]['offenceDate'] ?? null,
    //                     'endorsement_conviction_date' => $data['endorsements'][0]['convictionDate'] ?? null,
    //                     'endorsements' => json_encode($data['endorsements'] ?? []), // Save endorsements as JSON
    //                     'current_licence_check_interval' => $checkInterval,
    //                     'latest_lc_check' => $latestLcCheck, // Add the latest license check date and time
    //                     'next_lc_check' => $nextLcValidUntil,
    //                     'created_by' => 1.1,
    //                 ]);

    //             // Save entitlements
    //             foreach ($data['entitlement'] ?? [] as $entitlement) {
    //                 // Convert the restrictions array to JSON
    //                 $restrictions = json_encode($entitlement['restrictions'] ?? []);

    //                 // Ensure unique dates are assigned
    //                 $fromDate = isset($entitlement['fromDate']) ? $this->formatDateToDDMMYYYY($entitlement['fromDate']) : null;
    //                 $expiryDate = isset($entitlement['expiryDate']) ? $this->formatDateToDDMMYYYY($entitlement['expiryDate']) : null;

    //                 // Use the correct from_date and expiry_date for each entitlement
    //                 Entitlement::updateOrCreate(
    //                     [
    //                         'driver_id' => $driver->id,
    //                             'category_code' => $entitlement['categoryCode'],
    //                             'from_date' => $fromDate,
    //                             'expiry_date' => $expiryDate,
    //                         ],
    //                         [
    //                             'category_legal_literal' => $entitlement['categoryLegalLiteral'] ?? null,
    //                             'category_type' => $entitlement['categoryType'] ?? null,
    //                             'restrictions' => $restrictions,
    //                         ]
    //                 );
    //             }

    //             // Save entitlements
    //             foreach ($data['entitlement'] ?? [] as $entitlement) {
    //                 // Convert the restrictions array to JSON
    //                 $restrictions = json_encode($entitlement['restrictions'] ?? []);

    //                 // Ensure unique dates are assigned
    //                 $fromDate = isset($entitlement['fromDate']) ? $this->formatDateToDDMMYYYY($entitlement['fromDate']) : null;
    //                 $expiryDate = isset($entitlement['expiryDate']) ? $this->formatDateToDDMMYYYY($entitlement['expiryDate']) : null;

    //                 // Use the correct from_date and expiry_date for each entitlement
    //                 \App\Models\DuplicateEntitlement::create(
    //                     [
    //                         'duplicate_driver_id' => $duplicateDriver->id,
    //                     'driver_modal_id' => $duplicateDriver->driver_modal_id,
    //                             'category_code' => $entitlement['categoryCode'],
    //                             'from_date' => $fromDate,
    //                             'expiry_date' => $expiryDate,
    //                             'category_legal_literal' => $entitlement['categoryLegalLiteral'] ?? null,
    //                             'category_type' => $entitlement['categoryType'] ?? null,
    //                             'restrictions' => $restrictions,
    //                         ]
    //                 );
    //             }

    //             // Find the CompanyDetails record and increment api_call_count
    //             $companyDetails = CompanyDetails::where('id', $companyName)->first();

    //             if (!$companyDetails) {
    //                 return redirect()->back()->with('error', 'CompanyDetails record not found.');
    //             }

    //             // Increment api_call_count
    //             $companyDetails->increment('api_call_count');

    //              // Log the data
    //                 \App\Models\DriverAPILog::create([
    //                     'companyName' => $driver->companyName,
    //                     'created' => 1.1,
    //                     'last_lc_check' => $latestLcCheck,
    //                     'licence_no' => $driver->driver_licence_no,
    //                     'driver_id' => $driver->id,
    //                 ]);

    //             } else {
    //                 // Log or handle the error if the response is not successful
    //                 // \Log::error("Failed to retrieve data for driver: {$driver->driver_licence_no}, Status: {$response->status()}, Message: {$response->body()}");
    //             }

    //         }

    //     return response()->json(['message' => 'All eligible drivers updated successfully.'], 200);
    //     } catch (\Exception $e) {
    //     \Log::error('Error updating drivers: ' . $e->getMessage());
    //     return response()->json(['message' => 'Error updating drivers.'], 500);
    //     }
    // }

    public function automationupdateAll(Request $request)
    {
        try {
            $currentDate = Carbon::now()->format('d/m/Y');

            $drivers = Driver::where('driver_status', 'Active')
                ->where('consent_form_status', 'Yes')
                ->where('automation', 'Yes')
                ->whereHas('company', function ($query) {
                    $query->where('lc_check_status', 'Enable')
                        ->where('company_status', 'Active');
                })
                ->get()
                ->filter(function ($driver) use ($currentDate) {
                    if (! $driver->next_lc_check) {
                        return false;
                    }

                    try {
                        $nextLcCheck = Carbon::createFromFormat('d/m/Y', $driver->next_lc_check);
                        $currentDateParsed = Carbon::createFromFormat('d/m/Y', $currentDate);
                    } catch (\Exception $e) {
                        \Log::error('Date parsing error for driver ID: '.$driver->id);

                        return false;
                    }

                    return $nextLcCheck->lessThanOrEqualTo($currentDateParsed);
                });

            \Log::info('Eligible Drivers Count: '.$drivers->count());

            $processedDrivers = collect(); // Important

            foreach ($drivers as $driver) {
                \Log::info('Processing Driver ID: '.$driver->id);

                $companyName = $driver->companyName;

                $company = $driver->companyDetails;

                if (! $company) {
                    \Log::warning('Company details not found for driver ID: '.$driver->id);

                    continue; // Skip this driver
                }

                // Prepaid coins check
                // ✅ Payment type or coins null check
                if (is_null($company->payment_type) || is_null($company->coins)) {
                    \Log::warning('Skipping driver ID: '.$driver->id.' because payment_type or coins is NULL. (Company: '.($company->name ?? 'Unknown').')');

                    continue; // Skip this driver completely
                }

                // ✅ Prepaid coins check (only if not null)
                if ($company->payment_type === 'Prepaid' && ($company->coins <= 0)) {
                    \Log::warning('No coins left for driver ID: '.$driver->id.' (Company: '.$company->name.')');

                    continue; // Skip this driver
                }

                $token = $this->getToken();

                $response = Http::withHeaders([
                    'x-api-key' => 'n0LdnbbBTm8KAxSsIFvdFaOsn4lYeGC78dNjvTkq',
                    'Authorization' => $token,
                ])->post('https://driver-vehicle-licensing.api.gov.uk/full-driver-enquiry/v1/driving-licences/retrieve', [
                    'drivingLicenceNumber' => $driver->driver_licence_no,
                    'includeCPC' => true,
                    'includeTacho' => true,
                    'acceptPartialResponse' => 'true',
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    $driverDob = $data['driver']['dateOfBirth'] ?? null;
                    $driverAge = $driverDob ? $this->calculateAgeDriver($driverDob) : null;

                    $formattedDriverDob = $this->formatDateToDDMMYYYY($driverDob);
                    $formattedFromDate = $this->formatDateToDDMMYYYY($data['entitlement'][0]['fromDate'] ?? null);
                    $formattedExpiryDate = $this->formatDateToDDMMYYYY($data['entitlement'][0]['expiryDate'] ?? null);
                    $formattedValidFromDate = $this->formatDateToDDMMYYYY($data['token']['validFromDate'] ?? null);
                    $formattedValidToDate = $this->formatDateToDDMMYYYY($data['token']['validToDate'] ?? null);
                    $formattedCardExpiryDate = $this->formatDateToDDMMYYYY($data['holder']['tachoCards'][0]['cardExpiryDate'] ?? null);
                    $formattedCardStartOfValidityDate = $this->formatDateToDDMMYYYY($data['holder']['tachoCards'][0]['cardStartOfValidityDate'] ?? null);

                    $latestLgvValidTo = null;
                    if (isset($data['cpc']) && is_array($data['cpc']['cpcs'])) {
                        foreach ($data['cpc']['cpcs'] as $cpc) {
                            $lgvValidTo = $cpc['lgvValidTo'] ?? null;
                            if ($lgvValidTo && ($latestLgvValidTo === null || $lgvValidTo > $latestLgvValidTo)) {
                                $latestLgvValidTo = $lgvValidTo;
                            }
                        }
                    }
                    $formattedLgvValidTo = $this->formatDateToDDMMYYYY($latestLgvValidTo);
                    $formattedIssueDate = $this->formatDateToDDMMYYYY($data['dqc']['dqcs'][0]['issueDate'] ?? null);

                    $fullName = trim(($data['driver']['firstNames'] ?? '').' '.($data['driver']['lastName'] ?? ''));
                    $addressLine1 = $data['driver']['address']['unstructuredAddress']['line1'] ?? '';
                    $addressLine2 = $data['driver']['address']['unstructuredAddress']['line2'] ?? '';
                    $addressLine3 = $data['driver']['address']['unstructuredAddress']['line3'] ?? '';
                    $addressLine4 = $data['driver']['address']['unstructuredAddress']['line4'] ?? '';
                    $addressLine5 = $data['driver']['address']['unstructuredAddress']['line5'] ?? '';
                    $fullAddress = trim($addressLine1.' '.$addressLine2.' '.$addressLine3.' '.$addressLine4.' '.$addressLine5);

                    // Determine the licence check interval based on endorsements
                    $penaltyPoints = 0;
                    if (isset($data['endorsements']) && is_array($data['endorsements'])) {
                        foreach ($data['endorsements'] as $endorsement) {
                            if (isset($endorsement['penaltyPoints'])) {
                                $penaltyPoints = max($penaltyPoints, $endorsement['penaltyPoints']);
                            }
                        }
                    }
                    $checkInterval = $this->calculateCheckInterval($penaltyPoints);

                    // Get current date and time in UK timezone
                    $latestLcCheck = Carbon::now('Europe/London')->format('d/m/Y H:i:s');

                    // Calculate next_lc_check
                    $nextLcValidUntil = null;
                    if ($penaltyPoints < 5) {
                        $nextLcValidUntil = Carbon::createFromFormat('d/m/Y H:i:s', $latestLcCheck)
                            ->addMonths(3)
                            ->format('d/m/Y');
                    } else {
                        $nextLcValidUntil = Carbon::createFromFormat('d/m/Y H:i:s', $latestLcCheck)
                            ->addMonths()
                            ->format('d/m/Y');
                    }

                    // Save driver details
                    $driver->update([
                        'driver_age' => $driverAge,
                        'name' => $fullName,
                        'last_name' => $data['driver']['lastName'] ?? null,
                        'gender' => $data['driver']['gender'] ?? null,
                        'first_names' => $data['driver']['firstNames'] ?? null,
                        'driver_dob' => $formattedDriverDob,
                        'driver_address' => $fullAddress,
                        'address_line1' => $addressLine1,
                        'address_line2' => $addressLine2,
                        'address_line3' => $addressLine3,
                        'address_line4' => $addressLine4,
                        'address_line5' => $addressLine5,
                        'post_code' => $data['driver']['address']['unstructuredAddress']['postcode'] ?? null,
                        'licence_type' => $data['licence']['type'] ?? null,
                        'driver_licence_status' => $data['licence']['status'] ?? null,
                        'tacho_card_no' => $data['holder']['tachoCards'][0]['cardNumber'] ?? null,
                        'tacho_card_valid_to' => $formattedCardExpiryDate,
                        'tacho_card_valid_from' => $formattedCardStartOfValidityDate,
                        'token_issue_number' => $data['token']['issueNumber'] ?? null,
                        'token_valid_from_date' => $formattedValidFromDate,
                        'driver_licence_expiry' => $formattedValidToDate,
                        'cpc_validto' => $formattedLgvValidTo, // Save latest LGV valid to date
                        'dqc_issue_date' => $formattedIssueDate,
                        'endorsement_penalty_points' => $data['endorsements'][0]['penaltyPoints'] ?? null,
                        'endorsement_offence_code' => $data['endorsements'][0]['offenceCode'] ?? null,
                        'endorsement_offence_legal_literal' => $data['endorsements'][0]['offenceLegalLiteral'] ?? null,
                        'endorsement_offence_date' => $data['endorsements'][0]['offenceDate'] ?? null,
                        'endorsement_conviction_date' => $data['endorsements'][0]['convictionDate'] ?? null,
                        'endorsements' => json_encode($data['endorsements'] ?? []), // Save endorsements as JSON
                        'current_licence_check_interval' => $checkInterval,
                        'latest_lc_check' => $latestLcCheck, // Add the latest license check date and time
                        'next_lc_check' => $nextLcValidUntil,
                        'created_by' => 1.1,
                    ]);

                    // Create a duplicate driver entry
                    $duplicateDriver = \App\Models\DuplicateDriver::create([
                        'driver_modal_id' => $driver->id,
                        'driver_licence_no' => $data['driver']['drivingLicenceNumber'],
                        'companyName' => $driver->companyName,
                        'ni_number' => $driver->ni_number,
                        'contact_no' => $driver->contact_no,
                        'contact_email' => $driver->contact_email,
                        'driver_age' => $driverAge,
                        'name' => $fullName,
                        'last_name' => $data['driver']['lastName'] ?? null,
                        'gender' => $data['driver']['gender'] ?? null,
                        'first_names' => $data['driver']['firstNames'] ?? null,
                        'driver_dob' => $formattedDriverDob,
                        'driver_address' => $fullAddress,
                        'address_line1' => $addressLine1,
                        'address_line2' => $addressLine2,
                        'address_line3' => $addressLine3,
                        'address_line4' => $addressLine4,
                        'address_line5' => $addressLine5,
                        'driver_status' => $driver->driver_status,
                        'post_code' => $data['driver']['address']['unstructuredAddress']['postcode'] ?? null,
                        'licence_type' => $data['licence']['type'] ?? null,
                        'driver_licence_status' => $data['licence']['status'] ?? null,
                        'tacho_card_no' => $data['holder']['tachoCards'][0]['cardNumber'] ?? null,
                        'tacho_card_valid_to' => $formattedCardExpiryDate,
                        'tacho_card_valid_from' => $formattedCardStartOfValidityDate,
                        'token_issue_number' => $data['token']['issueNumber'] ?? null,
                        'token_valid_from_date' => $formattedValidFromDate,
                        'driver_licence_expiry' => $formattedValidToDate,
                        'cpc_validto' => $formattedLgvValidTo,
                        'dqc_issue_date' => $formattedIssueDate,
                        'endorsement_penalty_points' => $data['endorsements'][0]['penaltyPoints'] ?? null,
                        'endorsement_offence_code' => $data['endorsements'][0]['offenceCode'] ?? null,
                        'endorsement_offence_legal_literal' => $data['endorsements'][0]['offenceLegalLiteral'] ?? null,
                        'endorsement_offence_date' => $data['endorsements'][0]['offenceDate'] ?? null,
                        'endorsement_conviction_date' => $data['endorsements'][0]['convictionDate'] ?? null,
                        'endorsements' => json_encode($data['endorsements'] ?? []),
                        'current_licence_check_interval' => $checkInterval,
                        'latest_lc_check' => $latestLcCheck,
                        'next_lc_check' => $nextLcValidUntil,
                        'created_by' => 1.1,
                    ]);

                    $processedDrivers->push($driver); // ✅ Add to processed list

                    foreach ($data['entitlement'] ?? [] as $entitlement) {
                        // Convert the restrictions array to JSON
                        $restrictions = json_encode($entitlement['restrictions'] ?? []);

                        // Ensure unique dates are assigned
                        $fromDate = isset($entitlement['fromDate']) ? $this->formatDateToDDMMYYYY($entitlement['fromDate']) : null;
                        $expiryDate = isset($entitlement['expiryDate']) ? $this->formatDateToDDMMYYYY($entitlement['expiryDate']) : null;

                        // Use the correct from_date and expiry_date for each entitlement
                        Entitlement::updateOrCreate(
                            [
                                'driver_id' => $driver->id,
                                'category_code' => $entitlement['categoryCode'],
                                'from_date' => $fromDate,
                                'expiry_date' => $expiryDate,
                            ],
                            [
                                'category_legal_literal' => $entitlement['categoryLegalLiteral'] ?? null,
                                'category_type' => $entitlement['categoryType'] ?? null,
                                'restrictions' => $restrictions,
                            ]
                        );
                    }

                    // Save entitlements
                    foreach ($data['entitlement'] ?? [] as $entitlement) {
                        // Convert the restrictions array to JSON
                        $restrictions = json_encode($entitlement['restrictions'] ?? []);

                        // Ensure unique dates are assigned
                        $fromDate = isset($entitlement['fromDate']) ? $this->formatDateToDDMMYYYY($entitlement['fromDate']) : null;
                        $expiryDate = isset($entitlement['expiryDate']) ? $this->formatDateToDDMMYYYY($entitlement['expiryDate']) : null;

                        // Use the correct from_date and expiry_date for each entitlement
                        \App\Models\DuplicateEntitlement::create(
                            [
                                'duplicate_driver_id' => $duplicateDriver->id,
                                'driver_modal_id' => $duplicateDriver->driver_modal_id,
                                'category_code' => $entitlement['categoryCode'],
                                'from_date' => $fromDate,
                                'expiry_date' => $expiryDate,
                                'category_legal_literal' => $entitlement['categoryLegalLiteral'] ?? null,
                                'category_type' => $entitlement['categoryType'] ?? null,
                                'restrictions' => $restrictions,
                            ]
                        );
                    }
                    // Find the CompanyDetails record and increment api_call_count
                    $companyDetails = CompanyDetails::where('id', $companyName)->first();

                    if (! $companyDetails) {
                        return redirect()->back()->with('error', 'CompanyDetails record not found.');
                    }

                    // Decrement coins only if Prepaid and not unlimited (-1)
                    if ($companyDetails->payment_type === 'Prepaid' && $companyDetails->coins !== -1) {
                        $companyDetails->coins -= 1;
                        $companyDetails->save();
                    }

                    // Increment api_call_count
                    $companyDetails->increment('api_call_count');
                    \App\Models\DriverAPILog::create([
                        'companyName' => $driver->companyName,
                        'created' => 1.1,
                        'last_lc_check' => $latestLcCheck,
                        'licence_no' => $driver->driver_licence_no,
                        'driver_id' => $driver->id,
                    ]);
                }
            }

            \Log::info('Processed Drivers for Email Count: '.$processedDrivers->count());

            $companyGroups = $processedDrivers->groupBy(function ($driver) {
                return $driver->company->id ?? null;
            });

            $companyGroups->each(function ($driversInCompany, $companyId) {
                $companyDetails = $driversInCompany->first()->company;

                if ($companyDetails && ! empty($companyDetails->email)) {
                    $currentMonthYear = date('F Y'); // Full month + short year

                    $driversList = $driversInCompany->map(function ($driver) {
                        return [
                            'name' => $driver->name,
                            'slug' => base64_encode($driver->id),
                        ];
                    });

                    $emailData = [
                        'companyName' => $companyDetails->name ?? 'Unknown Company',
                        'currentMonthYear' => $currentMonthYear,
                        'drivers' => $driversList,
                    ];

                    $subject = "{$companyDetails->name} {$currentMonthYear} License Checks";

                    // For company email
                    $recipientName = $companyDetails->name ?? 'Valued Customer';
                    $body = view('emails.driver_automation', [
                        'emailData' => $emailData,
                        'recipientName' => $recipientName,
                    ])->render();

                    \App\Models\AutomationEmailLog::create([
                        'driver_id' => null,
                        'company_id' => $companyDetails->id,
                        'user_id' => null,
                        'email' => $companyDetails->email,
                        'subject' => $subject,
                        'body' => $body,
                        'type' => 'company',
                    ]);

                    // ✅ Driver emails (only that driver)
                    foreach ($driversInCompany as $driver) {
                        if (! empty($driver->contact_email)) {

                            $driverEmailData = [
                                'companyName' => $companyDetails->name ?? 'Unknown Company',
                                'currentMonthYear' => $currentMonthYear,
                                'drivers' => [
                                    [
                                        'name' => $driver->name,
                                        'slug' => base64_encode($driver->id),
                                    ],
                                ],
                            ];

                            $recipientName = $driver->name;

                            $body = view('emails.driver_automation', [
                                'emailData' => $driverEmailData,
                                'recipientName' => $recipientName,
                            ])->render();

                            \App\Models\AutomationEmailLog::create([
                                'driver_id' => $driver->id,
                                'company_id' => $companyDetails->id,
                                'user_id' => null,
                                'email' => $driver->contact_email,
                                'subject' => $subject,
                                'body' => $body,
                                'type' => 'driver',
                            ]);
                        }
                    }

                    // 3. Manager emails (only if depot matches)
                  // ✅ Manager-wise correct mapping (FIXED)

$managerDriverMap = [];

foreach ($driversInCompany as $driver) {

    $driverDepotId = (string) $driver->depot_id;
    $driverGroupId = (string) $driver->group_id;

    $managers = \App\Models\User::where('delete_status', 1)
        ->where('companyname', $companyDetails->id)
        ->whereJsonContains('depot_id', $driverDepotId)
        ->whereJsonContains('driver_group_id', $driverGroupId)
        ->get();

    foreach ($managers as $manager) {

        if (!isset($managerDriverMap[$manager->id])) {
            $managerDriverMap[$manager->id] = [
                'manager' => $manager,
                'drivers' => []
            ];
        }

        // duplicate driver avoid
        if (!collect($managerDriverMap[$manager->id]['drivers'])->pluck('id')->contains($driver->id)) {
            $managerDriverMap[$manager->id]['drivers'][] = $driver;
        }
    }
}


// ✅ હવે manager પ્રમાણે email create
foreach ($managerDriverMap as $managerId => $data) {

    $manager = $data['manager'];
    $driversList = $data['drivers'];

    if (!empty($manager->email) && count($driversList) > 0) {

        $currentMonthYear = date('F Y');

        $driverEmailList = collect($driversList)->map(function ($driver) {
            return [
                'name' => $driver->name,
                'slug' => base64_encode($driver->id),
            ];
        });

        $emailData = [
            'companyName' => $companyDetails->name ?? 'Unknown Company',
            'currentMonthYear' => $currentMonthYear,
            'drivers' => $driverEmailList,
        ];

        $subject = "{$companyDetails->name} {$currentMonthYear} License Checks";

        $recipientName = $manager->username;

        $body = view('emails.driver_automation', [
            'emailData' => $emailData,
            'recipientName' => $recipientName,
        ])->render();

        \App\Models\AutomationEmailLog::create([
            'driver_id' => null,
            'company_id' => $companyDetails->id,
            'user_id' => $manager->id,
            'email' => $manager->email,
            'subject' => $subject,
            'body' => $body,
            'type' => 'manager',
        ]);
    }
}

                    \Log::info('Automation email sent to: '.$companyDetails->email);
                } else {
                    \Log::warning('No email found for company ID: '.$companyId);
                }
            });

            return response()->json(['message' => 'All eligible drivers updated and emails save successfully.'], 200);

        } catch (\Exception $e) {
            \Log::error('Automation Update Failed: '.$e->getMessage());

            return response()->json(['message' => 'Error updating drivers.'], 500);
        }
    }

    public function sendEmailsautomation()
    {
        try {
            // Fetch only pending emails (skip Failed explicitly)
            $emails = \App\Models\AutomationEmailLog::whereIn('status', ['Pending', 'Failed'])->get();

            if ($emails->isEmpty()) {
                return response()->json([
                    'message' => 'No emails found to send.',
                    'count' => 0,
                ], 200);
            }

            $sentCount = 0;
            $failedCount = 0;

            foreach ($emails as $emailLog) {
                try {
                    // ✅ Use $emailLog (not $log)
                    \Mail::to($emailLog->email)->bcc('testprayosha@gmail.com')->send(
                        new \App\Mail\AutomationEmail($emailLog->subject, $emailLog->body)
                    );

                    // Mark as Sent
                    $emailLog->update(['status' => 'Sent']);
                    $sentCount++;

                    \Log::info("✅ Email sent to: {$emailLog->email}");

                } catch (\Exception $e) {
                    // Mark as Failed
                    $emailLog->update(['status' => 'Failed']);
                    $failedCount++;

                    \Log::error("❌ Email failed for {$emailLog->email}: ".$e->getMessage());
                }
            }

            return response()->json([
                'message' => 'Email sending completed.',
                'sent' => $sentCount,
                'failed' => $failedCount,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Email sending API failed: '.$e->getMessage());

            return response()->json([
                'message' => 'Error sending emails.',
            ], 500);
        }
    }

    public function updateConsentStatus()
    {
        try {
            // Get today's date in 'dd/mm/yyyy' format
            $currentDate = Carbon::now()->format('d/m/Y');

            // Fetch drivers where consent_valid matches today's date
            $drivers = Driver::where('consent_valid', $currentDate)->get();

            // Update the consent_form_status to 'Expiry' for matching drivers
            foreach ($drivers as $driver) {
                $driver->update(['consent_form_status' => 'Expiry']);
            }

            // Response
            return response()->json([
                'success' => true,
                'message' => count($drivers).' driver(s) updated to Expiry.',
            ]);
        } catch (\Exception $e) {
            // Handle errors
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
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
                'key' => 4,
            ]);
        }
    }

    public function checkAndSendWorkAroundNotifications()
    {
        $currentTime = now(); // Get current time
        $thresholdTime = $currentTime->copy()->subMinutes(30); // Calculate threshold time

        // Log current time and threshold time
        // \Log::info("Current Time: {$currentTime->toDateTimeString()}");
        // \Log::info("Threshold Time (30 min ago): {$thresholdTime->toDateTimeString()}");

        // Fetch records where uploaded_date is NULL
        $workAroundStores = \App\Models\WorkAroundStore::whereNull('uploaded_date')->get();

        // \Log::info("Found {$workAroundStores->count()} records eligible for notification.");

        $notificationsSent = 0;

        foreach ($workAroundStores as $workAroundStore) {
            // \Log::info("Checking WorkAroundStore ID: {$workAroundStore->id}, Start Date: {$workAroundStore->start_date}");

            // Parse the start_date correctly
            try {
                $startDate = Carbon::createFromFormat('d/m/Y H:i:s', $workAroundStore->start_date);
                // \Log::info("Parsed Start Date: {$startDate->toDateTimeString()}");
            } catch (\Exception $e) {
                // \Log::error("Invalid date format for WorkAroundStore ID: {$workAroundStore->id}. Error: " . $e->getMessage());
                continue; // Skip if date parsing fails
            }

            // Ensure the start_date is strictly older than the threshold time
            if ($startDate->lt($thresholdTime)) { // '<' ensures OLDER than threshold
                $driver = $workAroundStore->driver;

                if ($driver && ! empty($driver->device_token)) { // Check if driver has a device token
                    // \Log::info("Sending delayed notification to driver ID: {$driver->id}");

                    $this->sendNotification(
                        'Walkaround Reminder',
                        'Your walkaround check is still open! Click here to complete it.',
                        [$driver->id] // Pass driver ID as an array
                    );

                    $notificationsSent++;
                } else {
                }
            } else {
                // \Log::info("Skipping WorkAroundStore ID: {$workAroundStore->id} - Start Date is not older than 30 minutes.");
            }
        }

        \Log::info("Total Notifications Sent: {$notificationsSent}");

        return response()->json([
            'status' => 1,
            'message' => "Notifications sent to {$notificationsSent} drivers.",
        ], 200);
    }

    public function selectededit($ids)
    {
        $idsArray = explode(',', $ids);
        $drivers = Driver::whereIn('id', $idsArray)->get();
        $selectedCompanyId = optional($drivers->first())->companyName;

        return view('driver.selected_driver', compact('drivers', 'idsArray', 'selectedCompanyId'));
    }

    public function selectedupdate(Request $request, $ids)
    {
        $idsArray = explode(',', $ids);

        // Only keep fields that have a non-empty value
        $updateData = array_filter($request->only([
            'driver_status',
            'group_id',
            'depot_id',
            'automation',
            'depot_access_status',
        ]), function ($value) {
            return ! is_null($value) && $value !== '';
        });

        foreach ($idsArray as $driverId) {
            $driver = Driver::find($driverId);
            if ($driver && \Auth::user()->can('edit driver') && ! empty($updateData)) {
                $driver->update($updateData);
            }
        }

        return redirect()->back()->with('success', 'Selected drivers updated successfully.');
    }

    public function deleteSelected(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No drivers selected.']);
        }

        try {
            $drivers = Driver::whereIn('id', $ids)->get();

            foreach ($drivers as $driver) {
                \App\Models\DeletedDriverLog::create([
                    'driver_name' => $driver->name ?? '',
                    'driver_id' => $driver->id,
                    'company_id' => $driver->companyName ?? '',
                    'deleted_by' => \Auth::user()->id,
                ]);
            }

            Driver::whereIn('id', $ids)->delete();

            return response()->json(['success' => true, 'message' => 'Selected drivers deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting selected drivers.']);
        }
    }
}
