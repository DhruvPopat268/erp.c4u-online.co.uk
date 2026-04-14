<?php

namespace App\Http\Controllers;

use App\Models\CompanyDetails;
use App\Models\CustomField;
use App\Models\ExperienceCertificate;
use App\Models\GenerateOfferLetter;
use App\Models\JoiningLetter;
use App\Models\LoginDetail;
use App\Models\NOC;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserToDo;
use App\Models\Utility;
use Auth;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    //   public function index()
    //     {
    //         $user = \Auth::user();
    //         if (\Auth::user()->can('manage user')) {
    //             if (\Auth::user()->type == 'super admin') {
    //                 // Show users created by the current user (assuming `creatorId` is the field)
    //                 $users = User::where('created_by', '=', $user->id)->where('type', '=', 'company')->with(['currentPlan'])->get();
    //             } else {
    //                 // Show users created by the current user, excluding clients
    //                 $users = User::where('created_by', '=', $user->id)->where('type', '!=', 'client')->with(['currentPlan'])->get();
    //             }

    //             return view('user.index')->with('users', $users);
    //         } else {
    //             return redirect()->back();
    //         }
    //     }

    public function index(Request $request)
    {
        $user = \Auth::user();

        if ($user->can('manage user')) {

            // Retrieve the company name of the user
            $companyName = $user->companyname;
            $selectedCompanyId = $request->input('company_id');

            if ($user->type == 'super admin') {
                // Super Admin: Show users created by them with type 'company'
                $users = User::where('created_by', '=', $user->id)
                    ->where('type', '=', 'company')
                    ->with(['currentPlan'])
                    ->get();
            } elseif ($user->type == 'company') {
                // Company Role: Show all users except super admin, accountant, and client
                $users = User::whereRaw("BINARY type NOT IN ('super admin', 'accountant', 'client', 'company')")
                    ->with(['currentPlan'])->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyname', $selectedCompanyId);
                    })
                    ->get();
            }  elseif ($user->type == 'PTC manager') {
                // Company Role: Show all users except super admin, accountant, and client
                $users = User::whereNotIn('type', ['super admin', 'accountant', 'client', 'company', 'PTC Manager'])
                    ->with(['currentPlan'])->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyname', $selectedCompanyId);
                    })
                    ->get();
                    
            } else {
                // Other Roles: Show only users they created
                $users = User::where('created_by', '=', $user->id)
                    ->with(['currentPlan'])
                    ->get();
            }
            $companies = CompanyDetails::orderBy('name', 'asc')
                ->get();

            return view('user.index', compact('users', 'companies'));
        } else {
            return redirect()->back();
        }
    }

    public function getDepotByCompany($companyId)
    {
        $user = \Auth::user(); // Get logged-in user

        if ($user->type === 'company' || $user->type === 'PTC manager') {
            // Show all depots for Company and PTC Manager roles
            $depots = \App\Models\Depot::where('companyName', $companyId)->pluck('name', 'id')->map(fn ($name) => ucfirst(strtolower($name)));

        } else {
            // Show only depots assigned to the logged-in user
            $depotIds = $user->depot_id;

            if (is_string($depotIds)) {
                $depotIds = json_decode($depotIds, true) ?? explode(',', $depotIds);
            } elseif (is_int($depotIds)) {
                $depotIds = [$depotIds]; // Convert single ID to array
            } elseif (! is_array($depotIds)) {
                $depotIds = []; // Default to empty array
            }

            $depots = \App\Models\Depot::whereIn('id', $depotIds)->pluck('name', 'id')->map(fn ($name) => ucfirst(strtolower($name)));
        }

        return response()->json($depots);
    }

    public function getVehicleGroups($companyId)
{
    $user = \Auth::user();

    if ($user->type === 'company' || $user->type === 'PTC manager') {
        // Show all vehicle groups for selected company
        $groups = \App\Models\VehicleGroup::where('company_id', $companyId)
            ->pluck('name', 'id')
            ->map(fn ($name) => ucfirst(strtolower($name)));
    } else {
        // Show only assigned vehicle groups
        $groupIds = $user->vehicle_group_id; // JSON / array / int

        if (is_string($groupIds)) {
            $groupIds = json_decode($groupIds, true) ?? explode(',', $groupIds);
        } elseif (is_int($groupIds)) {
            $groupIds = [$groupIds];
        } elseif (!is_array($groupIds)) {
            $groupIds = [];
        }

        $groups = \App\Models\VehicleGroup::where('company_id', $companyId)
            ->whereIn('id', $groupIds)
            ->pluck('name', 'id')
            ->map(fn ($name) => ucfirst(strtolower($name)));
    }

    return response()->json($groups);
}

public function getDriverGroups($companyId)
{
    $user = \Auth::user();

    if ($user->type === 'company' || $user->type === 'PTC manager') {
        // Show all driver groups for selected company
        $groups = \App\Models\Group::where('company_id', $companyId)
            ->pluck('name', 'id')
            ->map(fn ($name) => ucfirst(strtolower($name)));
    } else {
        // Show only assigned driver groups
        $groupIds = $user->driver_group_id; // JSON / array / int

        if (is_string($groupIds)) {
            $groupIds = json_decode($groupIds, true) ?? explode(',', $groupIds);
        } elseif (is_int($groupIds)) {
            $groupIds = [$groupIds];
        } elseif (!is_array($groupIds)) {
            $groupIds = [];
        }

        $groups = \App\Models\Group::where('company_id', $companyId)
            ->whereIn('id', $groupIds)
            ->pluck('name', 'id')
            ->map(fn ($name) => ucfirst(strtolower($name)));
    }

    return response()->json($groups);
}



    public function create()
    {
        $user = \Auth::user();

        $excludedRoles = ['super admin', 'customer', 'vender', 'company', 'accountant', 'client', 'ptc manager'];

        // Check if the user is a super admin
        if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
            // Fetch all company names
            $companyName = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')
                ->pluck('name', 'id');

            $roles = Role::whereRaw("BINARY name NOT IN ('".implode("','", $excludedRoles)."')")
                ->pluck('name', 'id');
        } else {
            // Fetch the company name for the logged-in user
            $companyName = CompanyDetails::where('created_by', '=', $user->creatorId())
                ->where('id', '=', $user->companyname)->where('company_status', 'Active')
                ->pluck('name', 'id');

            $roles = Role::where('created_by', '=', $user->id)
                ->whereRaw("BINARY name NOT IN ('".implode("','", $excludedRoles)."')")
                ->pluck('name', 'id');
        }

        // Fetch other necessary data
        $customFields = CustomField::where('created_by', '=', $user->creatorId())->where('module', '=', 'user')->get();

        // Check if the user has permission to create a user
        if ($user->can('create user')) {
            // Pass the fetched company name to the view
            return view('user.create', compact('roles', 'customFields', 'companyName'));
        } else {
            return redirect()->back();
        }
    }

    //     public function store(Request $request)
    //     {

    //         if(\Auth::user()->can('create user'))
    //         {
    //             $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->where('created_by', '=', \Auth::user()->creatorId())->first();

    //             if(\Auth::user()->type == 'super admin')
    //             {
    //                 $validator = \Validator::make(
    //                     $request->all(), [
    //                                       'companyname' => 'required|max:120',
    //                                       'username' => 'required',
    //                                       'email' => 'required|email|unique:users',
    //                                       'password' => 'required|min:6',
    //                                   ]
    //                 );
    //                 if($validator->fails())
    //                 {
    //                     $messages = $validator->getMessageBag();

    //                     return redirect()->back()->with('error', $messages->first());
    //                 }
    //                 $user               = new User();
    //                 $user['companyname']       = $request->companyname;
    //                 $user['username']       = $request->username;
    //                 $user['email']      = $request->email;
    //                 $psw                = $request->password;
    //                 $user['password']   = Hash::make($request->password);
    //                 $user['type']       = 'company';
    //                 $user['default_pipeline'] = 1;
    //                 $user['plan'] = 1;
    //                 $user['lang']       = !empty($default_language) ? $default_language->value : 'en';
    //                 $user['created_by'] = \Auth::user()->id;
    //                 $user['plan']       = Plan::first()->id;
    //                 $user['email_verified_at'] = date('Y-m-d H:i:s');

    //                 $user->save();
    //                 $role_r = Role::findByName('company');
    //                 $user->assignRole($role_r);
    // //                $user->userDefaultData();
    //                 $user->userDefaultDataRegister($user->id);
    //                 $user->userWarehouseRegister($user->id);

    //                 //default bank account for new company
    //                 $user->userDefaultBankAccount($user->id);

    //                 Utility::chartOfAccountTypeData($user->id);
    //                 // Utility::chartOfAccountData($user);
    //                 // default chart of account for new company
    //                 Utility::chartOfAccountData1($user->id);

    //                 Utility::pipeline_lead_deal_Stage($user->id);
    //                 Utility::project_task_stages($user->id);
    //                 Utility::labels($user->id);
    //                 Utility::sources($user->id);
    //                 Utility::jobStage($user->id);
    //                 GenerateOfferLetter::defaultOfferLetterRegister($user->id);
    //                 ExperienceCertificate::defaultExpCertificatRegister($user->id);
    //                 JoiningLetter::defaultJoiningLetterRegister($user->id);
    //                 NOC::defaultNocCertificateRegister($user->id);
    //             }
    //             else
    //             {
    //                 $validator = \Validator::make(
    //                     $request->all(), [
    //                                       'companyname' => 'required|max:120',
    //                                       'username' => 'required',
    //                                       'email' => 'required|email|unique:users',
    //                                       'password' => 'required|min:6',
    //                                       'role' => 'required',
    //                                   ]
    //                 );
    //                 if($validator->fails())
    //                 {
    //                     $messages = $validator->getMessageBag();
    //                     return redirect()->back()->with('error', $messages->first());
    //                 }

    //                 $objUser    = \Auth::user()->creatorId();
    //                 $objUser =User::find($objUser);
    //                 $user = User::find(\Auth::user()->created_by);
    //                 $total_user = $objUser->countUsers();
    //                 $plan       = Plan::find($objUser->plan);
    //                 if($total_user < $plan->max_users || $plan->max_users == -1)
    //                 {
    //                     $role_r                = Role::findById($request->role);
    //                     $psw                   = $request->password;
    //                     $request['password']   = Hash::make($request->password);
    //                     $request['type']       = $role_r->name;
    //                     $request['lang']       = !empty($default_language) ? $default_language->value : 'en';
    //                     $request['created_by'] = \Auth::user()->id;
    //                     $request['email_verified_at'] = date('Y-m-d H:i:s');

    //                     $user = User::create($request->all());
    //                     $user->assignRole($role_r);
    //                     if($request['type'] != 'client')
    //                       \App\Models\Utility::employeeDetails($user->id,\Auth::user()->creatorId());
    //                 }
    //                 else
    //                 {
    //                     return redirect()->back()->with('error', __('Your user limit is over, Please upgrade plan.'));
    //                 }
    //             }
    //             // Send Email
    //             $setings = Utility::settings();
    //             if($setings['new_user'] == 1)
    //             {

    //                 $user->password = $psw;
    //                 $user->type = $role_r->name;
    //                 $user->userDefaultDataRegister($user->id);

    //                 $userArr = [
    //                     'email' => $user->email,
    //                     'password' => $user->password,
    //                 ];
    //                 $resp = Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $userArr);

    //                 return redirect()->route('users.index')->with('success', __('User successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
    //             }
    //             return redirect()->route('users.index')->with('success', __('User successfully created.'));

    //         }
    //         else
    //         {
    //             return redirect()->back();
    //         }

    //     }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create user')) {
            $default_language = DB::table('settings')
                ->select('value')
                ->where('name', 'default_language')
                ->where('created_by', '=', \Auth::user()->creatorId())
                ->first();

            if (\Auth::user()->type == 'super admin') {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'companyname' => 'required|max:120',
                        'depot_id' => 'required|array', // Accept array for multiple depots
                        'depot_id.*' => 'exists:depots,id', // Ensure depot IDs exist
                        'username' => 'required',
                        'email' => 'required|email|unique:users',
                        'password' => 'required|min:6',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $company = \App\Models\CompanyDetails::find($request->companyname);

                $user = new User();
                $user['companyname'] = $request->companyname;
                $user['depot_id'] = json_encode($request->depot_id);
                $user['username'] = $request->username;
                $user['email'] = $request->email;
                $psw = $request->password; // Store password in a variable
                $user['password'] = Hash::make($psw); // Hash the password
                $user['type'] = 'company';
                $user['default_pipeline'] = 1;
                $user['plan'] = 1;
                $user['lang'] = ! empty($default_language) ? $default_language->value : 'en';
                $user['created_by'] = \Auth::user()->id;
                $user['plan'] = Plan::first()->id;
                $user['email_verified_at'] = date('Y-m-d H:i:s');

                $user->save();
                $role_r = Role::findByName('company');
                $user->assignRole($role_r);

                // Send the email with the password
                Mail::to($user->email)->send(new \App\Mail\UserCreatedMail($user, $psw, $company->name));

                // Additional functions for default setup
                $user->userDefaultDataRegister($user->id);
                $user->userWarehouseRegister($user->id);

                //default bank account for new company
                $user->userDefaultBankAccount($user->id);

                Utility::chartOfAccountTypeData($user->id);
                // Utility::chartOfAccountData($user);
                // default chart of account for new company
                Utility::chartOfAccountData1($user->id);

                Utility::pipeline_lead_deal_Stage($user->id);
                Utility::project_task_stages($user->id);
                Utility::labels($user->id);
                Utility::sources($user->id);
                Utility::jobStage($user->id);
                GenerateOfferLetter::defaultOfferLetterRegister($user->id);
                ExperienceCertificate::defaultExpCertificatRegister($user->id);
                JoiningLetter::defaultJoiningLetterRegister($user->id);
                NOC::defaultNocCertificateRegister($user->id);
            } else {
                $validator = \Validator::make(
                    $request->all(), [
                        'companyname' => 'required|max:120',
                        'depot_id' => 'required|array', // Accept array for multiple depots
                        'depot_id.*' => 'exists:depots,id', // Ensure depot IDs exist
                        'username' => 'required',
                        'email' => 'required|email|unique:users',
                        'password' => 'required|min:6',
                        'role' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $objUser = \Auth::user()->creatorId();
                $objUser = User::find($objUser);
                $total_user = $objUser->countUsers();
                $plan = Plan::find($objUser->plan);

                if ($total_user < $plan->max_users || $plan->max_users == -1) {
                    $role_r = Role::findById($request->role);
                    $psw = $request->password; // Store password
                    $request['password'] = Hash::make($psw);
                    $request['type'] = $role_r->name;
                    $request['lang'] = ! empty($default_language) ? $default_language->value : 'en';
                    $request['created_by'] = \Auth::user()->id;
                    $request['email_verified_at'] = date('Y-m-d H:i:s');
                    $request['depot_id'] = json_encode($request->depot_id);
                      $request['vehicle_group_id'] = json_encode($request->vehicle_group_id);
                        $request['driver_group_id'] = json_encode($request->driver_group_id);

                    $user = User::create($request->all());
                    $user->assignRole($role_r);

                    if ($request['type'] != 'client') {
                        \App\Models\Utility::employeeDetails($user->id, \Auth::user()->creatorId());
                    }

                    // Fetch the companyname from the `CompanyDetails` model
                    $company = \App\Models\CompanyDetails::find($request->companyname);

                    // Send the email with the password
                    Mail::to($user->email)->send(new \App\Mail\UserCreatedMail($user, $psw, $company->name));
                } else {
                    return redirect()->back()->with('error', __('Your user limit is over, Please upgrade plan.'));
                }
            }

            return redirect()->route('users.index')->with('success', __('User successfully created and email sent.'));
        } else {
            return redirect()->back();
        }

    }

    public function show()
    {
        return redirect()->route('user.index');
    }

    //     public function edit($id)
    // {
    //     $user  = \Auth::user();
    //     $roles = Role::where('created_by', '=', $user->id)->where('name','!=','client')->get()->pluck('name', 'id');
    //     if(\Auth::user()->can('edit user'))
    //     {
    //         $user              = User::findOrFail($id);
    //         $user->customField = CustomField::getData($user, 'user');
    //         $customFields      = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'user')->get();
    //         $contractTypes     = CompanyDetails::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

    //         // Fetch company name based on user type
    //         if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
    //             // Fetch all company names
    //             $companyName = CompanyDetails::pluck('name', 'id');
    //         } else {
    //             // Fetch the company name for the logged-in user
    //             $companyName = CompanyDetails::where('created_by', '=', $user->creatorId())
    //                 ->where('id', '=', $user->companyname)
    //                 ->pluck('name', 'id');
    //         }

    //         return view('user.edit', compact('user', 'roles', 'customFields', 'contractTypes', 'companyName'));
    //     }
    //     else
    //     {
    //         return redirect()->back();
    //     }
    // }

    // public function edit($id)
    // {
    //     // Get the authenticated user
    //     $user = \Auth::user();

    //     // Fetch roles based on the logged-in user's ID and exclude the 'client' role
    //             $excludedRoles = ['super admin', 'customer', 'vender', 'company', 'accountant', 'client', 'ptc manager'];

    //     // Check if the user has the permission to edit user
    //     if ($user->can('edit user')) {

    //         // Find the user to edit
    //         $user = User::findOrFail($id);

    //         // Get custom fields data for the user
    //         $user->customField = CustomField::getData($user, 'user');

    //         // Fetch custom fields based on the module 'user' and created by the logged-in user
    //         $customFields = CustomField::where('created_by', '=', $user->creatorId())->where('module', '=', 'user')->get();

    //         // Check if the user is a super admin or PTC manager

    //               if ($user->hasRole('company') || $user->hasRole('PTC manager')) {
    //             // If the user is an admin or PTC manager, show all company names
    //             $companyName = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->pluck('name', 'id');
    //              $roles = Role::whereRaw("BINARY name NOT IN ('" . implode("','", $excludedRoles) . "')")
    //             ->pluck('name', 'id');
    //         } else {
    //             // If the user has another role, show only the company name associated with the logged-in user
    //             $companyName = CompanyDetails::where('id', $user->companyname)->where('company_status', 'Active')->pluck('name', 'id');
    //              $roles = Role::where('created_by', '=', $user->id)
    //                 ->whereRaw("BINARY name NOT IN ('" . implode("','", $excludedRoles) . "')")
    //                 ->pluck('name', 'id');

    //         }

    //         // Return the view with necessary data
    //         return view('user.edit', compact('user', 'roles', 'customFields', 'companyName'));
    //     } else {
    //         // If user doesn't have permission, redirect back with an error message
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    public function edit($id)
    {
        // Get the authenticated user
        $authUser = \Auth::user();

        // Define the exact lowercase roles to be excluded
        $excludedRoles = ['super admin', 'customer', 'vender', 'company', 'accountant', 'client', 'PTC manager'];

        // Fetch roles based on the logged-in user's permissions
        if ($authUser->hasRole('company') || $authUser->hasRole('PTC manager')) {
            // Super admins and PTC managers can see all roles except excluded ones
            $roles = Role::whereRaw("BINARY name NOT IN ('".implode("','", $excludedRoles)."')")
                ->pluck('name', 'id');
        } else {
            // Other users see only the roles they created, excluding specific ones
            $roles = Role::where('created_by', '=', $authUser->id)
                ->whereRaw("BINARY name NOT IN ('".implode("','", $excludedRoles)."')")
                ->pluck('name', 'id');
        }

        // Check if the user has permission to edit a user
        if ($authUser->can('edit user')) {

            // Find the user to edit
            $user = User::findOrFail($id);

            // Get custom fields data for the user
            $user->customField = CustomField::getData($user, 'user');

            // Fetch custom fields based on the module 'user' and created by the logged-in user
            $customFields = CustomField::where('created_by', '=', $authUser->creatorId())->where('module', '=', 'user')->get();

            // Check if the authenticated user is a super admin or PTC manager
            if ($authUser->hasRole('company') || $authUser->hasRole('PTC manager')) {
                // If the user is an admin or PTC manager, show all company names
                $companyName = CompanyDetails::orderBy('name', 'asc')->where('company_status', 'Active')->pluck('name', 'id');
            } else {
                // If the user has another role, show only the company name associated with the logged-in user
                $companyName = CompanyDetails::where('id', $authUser->companyname)->where('company_status', 'Active')->pluck('name', 'id');
            }

            // Return the view with necessary data
            return view('user.edit', compact('user', 'roles', 'customFields', 'companyName'));
        } else {
            // If the user doesn't have permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit user')) {
            $user = User::findOrFail($id);

            if (\Auth::user()->type == 'super admin') {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'companyname' => 'required|max:120',
                        'depot_id' => 'required|array', // Accept array for multiple depots
                        'depot_id.*' => 'exists:depots,id', // Ensure depot IDs exist
                        'username' => 'required',
                        'email' => 'required|email|unique:users,email,'.$id,
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                //                $role = Role::findById($request->role);
                $role = Role::findByName('company');
                $input = $request->all();
                $input['type'] = $role->name;
                $input['depot_id'] = json_encode($request->depot_id);
                $input['vehicle_group_id'] = json_encode($request->vehicle_group_id ?? []);
$input['driver_group_id'] = json_encode($request->driver_group_id ?? []);


                $user->fill($input)->save();
                CustomField::saveData($user, $request->customField);

                $roles[] = $role->id;
                $user->roles()->sync($roles);

                return redirect()->route('users.index')->with(
                    'success', 'User successfully updated.'
                );
            } else {
                $this->validate(
                    $request,
                    [
                        'companyname' => 'required|max:120',
                        'depot_id' => 'required|array', // Accept array for multiple depots
                        'depot_id.*' => 'exists:depots,id', // Ensure depot IDs exist
                        'username' => 'required',
                        'email' => 'required|email|unique:users,email,'.$id,
                        'role' => 'required',
                    ]
                );

                $role = Role::findById($request->role);
                $input = $request->all();
                $input['type'] = $role->name;
                $input['depot_id'] = json_encode($request->depot_id);
                $input['vehicle_group_id'] = json_encode($request->vehicle_group_id ?? []);
$input['driver_group_id'] = json_encode($request->driver_group_id ?? []);


                $user->fill($input)->save();
                Utility::employeeDetailsUpdate($user->id, \Auth::user()->creatorId());
                CustomField::saveData($user, $request->customField);

                $roles[] = $request->role;
                $user->roles()->sync($roles);

                return redirect()->back()->with(
                    'success', 'User successfully updated.'
                );
            }
        } else {
            return redirect()->back();
        }
    }

    public function destroy($id)
    {
        if (\Auth::user()->can('delete user')) {
            $user = User::find($id);
            if ($user) {
                if ($user->delete_status == 0) {
                    $user->delete_status = 1;
                } else {
                    $user->delete_status = 0;
                }
                $user->save();

                return redirect()->route('users.index')->with('success', __('User successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('User not found.'));
            }
        } else {
            return redirect()->back()->with('error', __('Unauthorized action.'));
        }
    }

    public function profile()
    {
        $userDetail = \Auth::user();
        $userDetail->customField = CustomField::getData($userDetail, 'user');
        $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'user')->get();

        return view('user.profile', compact('userDetail', 'customFields'));
    }

    public function editprofile(Request $request)
    {
        $userDetail = \Auth::user();
        $user = User::findOrFail($userDetail['id']);

        $validator = \Validator::make(
            $request->all(), [
                'name' => 'required|max:120',
                'email' => 'required|email|unique:users,email,'.$userDetail['id'],
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        if ($request->hasFile('profile')) {
            $filenameWithExt = $request->file('profile')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('profile')->getClientOriginalExtension();
            $fileNameToStore = $filename.'_'.time().'.'.$extension;

            $settings = Utility::getStorageSetting();
            if ($settings['storage_setting'] == 'local') {
                $dir = 'uploads/avatar/';
            } else {
                $dir = 'uploads/avatar';
            }

            $image_path = $dir.$userDetail['avatar'];

            if (File::exists($image_path)) {
                File::delete($image_path);
            }

            $url = '';
            $path = Utility::upload_file($request, 'profile', $fileNameToStore, $dir, []);
            if ($path['flag'] == 1) {
                $url = $path['url'];
            } else {
                return redirect()->route('profile', \Auth::user()->id)->with('error', __($path['msg']));
            }

            //            $dir        = storage_path('uploads/avatar/');
            //            $image_path = $dir . $userDetail['avatar'];
            //
            //            if(File::exists($image_path))
            //            {
            //                File::delete($image_path);
            //            }
            //
            //            if(!file_exists($dir))
            //            {
            //                mkdir($dir, 0777, true);
            //            }
            //            $path = $request->file('profile')->storeAs('uploads/avatar/', $fileNameToStore);

        }

        if (! empty($request->profile)) {
            $user['avatar'] = $fileNameToStore;
        }
        $user['username'] = $request['name'];
        $user['email'] = $request['email'];
        $user->save();
        CustomField::saveData($user, $request->customField);

        return redirect()->route('dashboard')->with(
            'success', 'Profile successfully updated.'
        );
    }

    public function updatePassword(Request $request)
    {

        if (Auth::Check()) {

            $validator = \Validator::make(
                $request->all(), [
                    'old_password' => 'required',
                    'password' => 'required|min:6',
                    'password_confirmation' => 'required|same:password',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $objUser = Auth::user();
            $request_data = $request->All();
            $current_password = $objUser->password;
            if (Hash::check($request_data['old_password'], $current_password)) {
                $user_id = Auth::User()->id;
                $obj_user = User::find($user_id);
                $obj_user->password = Hash::make($request_data['password']);
                $obj_user->save();

                return redirect()->route('profile', $objUser->id)->with('success', __('Password successfully updated.'));
            } else {
                return redirect()->route('profile', $objUser->id)->with('error', __('Please enter correct current password.'));
            }
        } else {
            return redirect()->route('profile', \Auth::user()->id)->with('error', __('Something is wrong.'));
        }
    }

    // User To do module
    public function todo_store(Request $request)
    {
        $request->validate(
            ['title' => 'required|max:120']
        );

        $post = $request->all();
        $post['user_id'] = Auth::user()->id;
        $todo = UserToDo::create($post);

        $todo->updateUrl = route(
            'todo.update', [
                $todo->id,
            ]
        );
        $todo->deleteUrl = route(
            'todo.destroy', [
                $todo->id,
            ]
        );

        return $todo->toJson();
    }

    public function todo_update($todo_id)
    {
        $user_todo = UserToDo::find($todo_id);
        if ($user_todo->is_complete == 0) {
            $user_todo->is_complete = 1;
        } else {
            $user_todo->is_complete = 0;
        }
        $user_todo->save();

        return $user_todo->toJson();
    }

    public function todo_destroy($id)
    {
        $todo = UserToDo::find($id);
        $todo->delete();

        return true;
    }

    // change mode 'dark or light'
    public function changeMode()
    {
        $usr = \Auth::user();
        if ($usr->mode == 'light') {
            $usr->mode = 'dark';
            $usr->dark_mode = 1;
        } else {
            $usr->mode = 'light';
            $usr->dark_mode = 0;
        }
        $usr->save();

        return redirect()->back();
    }

    public function upgradePlan($user_id)
    {
        $user = User::find($user_id);
        $plans = Plan::get();
        $admin_payment_setting = Utility::getAdminPaymentSetting();

        return view('user.plan', compact('user', 'plans', 'admin_payment_setting'));
    }

    public function activePlan($user_id, $plan_id)
    {

        $user = User::find($user_id);
        $assignPlan = $user->assignPlan($plan_id);
        $plan = Plan::find($plan_id);
        if ($assignPlan['is_success'] == true && ! empty($plan)) {
            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            Order::create(
                [
                    'order_id' => $orderID,
                    'name' => null,
                    'card_number' => null,
                    'card_exp_month' => null,
                    'card_exp_year' => null,
                    'plan_name' => $plan->name,
                    'plan_id' => $plan->id,
                    'price' => $plan->price,
                    'price_currency' => isset(\Auth::user()->planPrice()['currency']) ? \Auth::user()->planPrice()['currency'] : '',
                    'txn_id' => '',
                    'payment_status' => 'success',
                    'receipt' => null,
                    'user_id' => $user->id,
                ]
            );

            return redirect()->back()->with('success', 'Plan successfully upgraded.');
        } else {
            return redirect()->back()->with('error', 'Plan fail to upgrade.');
        }

    }

    public function userPassword($id)
    {
        $eId = \Crypt::decrypt($id);
        $user = User::find($eId);

        return view('user.reset', compact('user'));

    }

    public function userPasswordReset(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(), [
                'password' => 'required|confirmed|same:password_confirmation',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $user = User::where('id', $id)->first();
        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        return redirect()->route('users.index')->with(
            'success', 'User Password successfully updated.'
        );

    }

    //start for user login details
    public function userLog(Request $request)
    {
        $filteruser = User::where('created_by', \Auth::user()->creatorId())->get()->pluck('username', 'id');
        $filteruser->prepend('Select User', '');

        $query = DB::table('login_details')
            ->join('users', 'login_details.user_id', '=', 'users.id')
            ->select(DB::raw('login_details.*, users.id as user_id , users.username as user_name , users.email as user_email ,users.type as user_type'))
            ->where(['login_details.created_by' => \Auth::user()->id]);

        if (! empty($request->month)) {
            $query->whereMonth('date', date('m', strtotime($request->month)));
            $query->whereYear('date', date('Y', strtotime($request->month)));
        } else {
            $query->whereMonth('date', date('m'));
            $query->whereYear('date', date('Y'));
        }

        if (! empty($request->users)) {
            $query->where('user_id', '=', $request->users);
        }
        $userdetails = $query->get();
        $last_login_details = LoginDetail::where('created_by', \Auth::user()->creatorId())->get();

        return view('user.userlog', compact('userdetails', 'last_login_details', 'filteruser'));
    }

    public function userLogView($id)
    {
        $users = LoginDetail::find($id);

        return view('user.userlogview', compact('users'));
    }

    public function userLogDestroy($id)
    {
        $users = LoginDetail::where('user_id', $id)->delete();

        return redirect()->back()->with('success', 'User successfully deleted.');
    }

    //end for user login details
    // use Lab404\Impersonate\Impersonate;

    //     public function LoginWithCompany(Request $request, LoginDetail $user, $id)
    //     {

    //         $user = LoginDetail::find($id);
    //         if ($user && auth()->check()) {
    //             Impersonate::take($request->user(), $user);
    //             return redirect('/home');
    //         }
    //     }

}
