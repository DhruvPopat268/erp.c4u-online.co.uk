<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\ForsBronze;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ForsBronzeController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('manage fors')) {
        $loggedInUser = \Auth::user();
        $companyName = $loggedInUser->companyname; 
         if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
            // Fetch all data from the ForsBronze model
            $bronzePolicies = ForsBronze::with('company')->get();
         } else {
                     $bronzePolicies = ForsBronze::with('company')
                ->where('companyName', $companyName)->get();
        }

            // Pass the data to the view
            return view('fors.bronze.index', compact('bronzePolicies'));
        } else {
            // If user doesn't have permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        $user = \Auth::user();
        if ($user->can('manage fors')) {

            return view('fors.bronze.create');
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
                    'bronze_policy_name' => 'required',
                                        

                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

                    $loggedInUser = \Auth::user();


            $bronze = new ForsBronze();
            $bronze->bronze_policy_name = $request->bronze_policy_name;
                        $bronze->policy_type = 'Bronze'; // Save the policy type
            $bronze->companyName = $loggedInUser->companyname;
             $bronze->created_by = $loggedInUser->id;
            $bronze->save();

            return redirect()->route('fors.bronze.index')->with('success', __('Policy successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(ForsBronze $forsBronze)
    {
        return view('fors.bronze.edit', compact('forsBronze'));
    }

    public function update(Request $request, ForsBronze $forsBronze)
    {
        if (\Auth::user()->can('manage fors')) {
            $validator = \Validator::make(
                $request->all(), [
                    'bronze_policy_name' => 'required',
                                       

                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $forsBronze->bronze_policy_name = $request->bronze_policy_name;
                        $forsBronze->policy_type = 'Bronze'; // Save the policy type

            $forsBronze->save();

            return redirect()->route('fors.bronze.index')->with('success', __('Policy successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // public function BronzePolicy_descriptionStore($id, Request $request)
    // {
    //     if (\Auth::user()->type == 'company') {
    //         $forsBronze = ForsBronze::find($id);

    //         $forsBronze->bronze_policy_description = $request->bronze_policy_description;
    //         $forsBronze->save();

    //         return response()->json(
    //             [
    //                 'is_success' => true,
    //                 'success' => __('Bronze Policy description successfully saved!'),
    //             ], 200
    //         );
    //     } else {
    //         return response()->json(
    //             [
    //                 'is_success' => false,
    //                 'error' => __('Permission Denied.'),
    //             ], 401
    //         );
    //     }
    // }
    
     public function BronzePolicy_descriptionStore($id, Request $request)
    {
         $user = \Auth::user();
    $forsBronze = ForsBronze::find($id);

    if (!$forsBronze) {
        return response()->json([
            'is_success' => false,
            'error' => __('Policy not found.'),
        ], 404);
    }

    
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'PTC manager') {
            $forsBronze = ForsBronze::find($id);

            // Get the bronze_policy_description from the request
            $description = $request->bronze_policy_description;

            // Clean up HTML content
            $description = preg_replace('/<div[^>]*style\s*:\s*page-break[^>]*>/', '', $description);
            $description = preg_replace('/<p[^>]*style\s*:\s*page-break[^>]*>/', '', $description);
            $description = preg_replace('/<p\s*style\s*:\s*page-break[^>]*>/', '', $description);

            // Load HTML into DOMDocument for manipulation
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true); // Suppress errors
            $dom->loadHTML(mb_convert_encoding($description, 'HTML-ENTITIES', 'UTF-8'));

            // Create a new DOMXPath instance
            $xpath = new \DOMXPath($dom);

            // Remove specific <span> tags
            foreach ($xpath->query('//span[@style="font-size:12.0pt;line-height:107%;font-family:&quot;Times New Roman&quot;,serif;mso-fareast-font-family:&quot;Times New Roman&quot;"]') as $span) {
                $span->parentNode->removeChild($span);
            }

            // Remove empty elements
            foreach ($xpath->query('//p[not(node())]') as $emptyP) {
                $emptyP->parentNode->removeChild($emptyP);
            }
            foreach ($xpath->query('//div[not(node())]') as $emptyDiv) {
                $emptyDiv->parentNode->removeChild($emptyDiv);
            }

            // Save the cleaned HTML content
            $forsBronze->bronze_policy_description = $dom->saveHTML();
            $forsBronze->save();

            return response()->json(
                [
                    'is_success' => true,
                    'success' => __('Policy description successfully saved!'),
                ], 200
            );
        } else {
        // For other users, check if company matches
        if ($user->companyname != $forsBronze->companyName) {
            return response()->json([
                    'is_success' => false,
                'error' => __('You are not allowed to edit this company\'s policy.'),
            ], 403);
        }
    }

    // Get the bronze_policy_description from the request
    $description = $request->bronze_policy_description;

    // Clean up HTML content
    $description = preg_replace('/<div[^>]*style\s*:\s*page-break[^>]*>/', '', $description);
    $description = preg_replace('/<p[^>]*style\s*:\s*page-break[^>]*>/', '', $description);
    $description = preg_replace('/<p\s*style\s*:\s*page-break[^>]*>/', '', $description);

    // Load HTML into DOMDocument for manipulation
    $dom = new \DOMDocument();
    libxml_use_internal_errors(true); // Suppress errors
    $dom->loadHTML(mb_convert_encoding($description, 'HTML-ENTITIES', 'UTF-8'));

    // Create a new DOMXPath instance
    $xpath = new \DOMXPath($dom);

    // Remove specific <span> tags
    foreach ($xpath->query('//span[@style="font-size:12.0pt;line-height:107%;font-family:&quot;Times New Roman&quot;,serif;mso-fareast-font-family:&quot;Times New Roman&quot;"]') as $span) {
        $span->parentNode->removeChild($span);
        }

    // Remove empty elements
    foreach ($xpath->query('//p[not(node())]') as $emptyP) {
        $emptyP->parentNode->removeChild($emptyP);
    }
    foreach ($xpath->query('//div[not(node())]') as $emptyDiv) {
        $emptyDiv->parentNode->removeChild($emptyDiv);
    }

    // Save the cleaned HTML content
    $forsBronze->bronze_policy_description = $dom->saveHTML();
    $forsBronze->save();

    return response()->json([
        'is_success' => true,
        'success' => __('Policy description successfully saved!'),
    ], 200);
    }

    public function show($id)
    {
        if (\Auth::user()->can('manage fors')) {
            $forsBronze = ForsBronze::find($id);

            // Check if $forsBronze is null
            if (! $forsBronze) {
                return redirect()->back()->with('error', __('ForsBronze not found.'));
            }

            $acceptedDrivers = \Illuminate\Support\Facades\DB::table('driver_bronze_policy')
                ->join('drivers', 'driver_bronze_policy.driver_id', '=', 'drivers.id')
                ->join('company_details', 'drivers.companyName', '=', 'company_details.id') // Adjust based on actual relationship
                ->where('fors_bronze_id', $id)
                ->where('driver_bronze_policy.status', 'Accept')
                ->get([
                    'driver_bronze_policy.driver_id',
                    'driver_bronze_policy.driver_signature',
                    'drivers.name',
                    'company_details.name as companyName', // Fetch the company name
                ]);

            $declinedDrivers = \Illuminate\Support\Facades\DB::table('driver_bronze_policy')
                ->join('drivers', 'driver_bronze_policy.driver_id', '=', 'drivers.id')
                ->join('company_details', 'drivers.companyName', '=', 'company_details.id') // Adjust based on actual relationship
                ->where('fors_bronze_id', $id)
                ->where('driver_bronze_policy.status', 'Decline')
                ->get(['drivers.name as declinedDriverName', 'company_details.name as companyName']);

            return view('fors.bronze.show', compact('forsBronze', 'acceptedDrivers', 'declinedDrivers'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(ForsBronze $forsBronze)
    {
        if (\Auth::user()->can('manage fors')) {
             // Delete related rows in PolicyAssignment first
        $forsBronze->policyAssignments()->delete(); // Assuming the relation is named policyAssignments

        // Delete the ForsBronze record
        $forsBronze->delete();

            return redirect()->route('fors.bronze.index')->with('success', __('Policy successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function Bronzeassign($id)
    {
        $bronzePolicy = ForsBronze::findOrFail($id);
        $drivers = \App\Models\Driver::all(); // Fetch the list of drivers

        // Fetch assigned drivers (assuming there's a relationship defined in the BronzePolicy model)
        $assignedDrivers = $bronzePolicy->drivers; // Adjust this line based on your actual relationships
        $assignedDriverIds = $assignedDrivers->pluck('id')->toArray();

        return view('fors.bronze.assign', compact('bronzePolicy', 'drivers', 'assignedDriverIds'));
    }

    public function BronzeassignPolicy(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'driver_ids.*' => 'required|exists:drivers,id',
        ]);

        // Retrieve the policy and driver IDs
        $bronzePolicy = ForsBronze::findOrFail($id);
        $driverIds = $request->input('driver_ids');

        // Attach multiple drivers to the policy
        $bronzePolicy->drivers()->sync($driverIds);

        // Redirect back with a success message
        return redirect()->route('fors.bronze.index')->with('success', 'Policy assigned successfully.');
    }

    public function Bronzesignature($id)
    {
        $driverSignature = ForsBronze::find($id);

        return view('fors.bronze.signature', compact('driverSignature'));

    }

    public function BronzesignatureStore(Request $request)
    {
        $driverSignature = ForsBronze::find($request->id);

        if ($driverSignature) {
            if (\Auth::user()->type == 'company') {
                $driverSignature->driver_signature = $request->driver_signature;
            }
            // Uncomment if client signatures are needed
            // else if(\Auth::user()->type == 'client'){
            //     $driverSignature->client_signature = $request->client_signature;
            // }

            $driverSignature->save();

            return response()->json([
                'Success' => true,
                'message' => __('Policy Signed successfully'),
            ], 200);
        } else {
            return response()->json([
                'Success' => false,
                'message' => __('Driver Signature not found'),
            ], 404);
        }
    }

    public function BronzeprintPolicy($driver_id)
    {
        // Fetch the driver
        $driver = DB::table('drivers')->where('id', $driver_id)->first();

        if (! $driver) {
            return redirect()->back()->with('error', 'Driver not found.');
        }

        // Fetch the driver_bronze_policy data, including driver_signature
        $driverPolicy = DB::table('driver_bronze_policy')->where('driver_id', $driver_id)->first();

        if (! $driverPolicy) {
            return redirect()->back()->with('error', 'Driver policy not found.');
        }

        // Fetch the ForsBronze data
        $forsBronze = DB::table('fors_bronzes')->where('id', $driverPolicy->fors_bronze_id)->first();

        if (! $forsBronze) {
            return redirect()->back()->with('error', 'Fors policy not found.');
        }

        $settings = Utility::settings();
        $logo = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo');
        $img = asset($logo.'/'.(isset($company_logo) && ! empty($company_logo) ? $company_logo : 'logo-dark.png'));

        return view('fors.bronze.preview', compact('driver', 'driverPolicy', 'forsBronze', 'img', 'settings'));
    }

    public function BronzepdffromDriverPolicy($driver_id)
    {
        // Fetch the driver
        $driver = DB::table('drivers')->where('id', $driver_id)->first();

        if (! $driver) {
            return redirect()->back()->with('error', 'Driver not found.');
        }

        // Fetch the driver_bronze_policy data, including driver_signature
        $driverPolicy = DB::table('driver_bronze_policy')->where('driver_id', $driver_id)->first();

        if (! $driverPolicy) {
            return redirect()->back()->with('error', 'Driver policy not found.');
        }

        // Fetch the ForsBronze data
        $forsBronze = DB::table('fors_bronzes')->where('id', $driverPolicy->fors_bronze_id)->first();

        if (! $forsBronze) {
            return redirect()->back()->with('error', 'Fors policy not found.');
        }

        $settings = Utility::settings();
        $company_logo = Utility::getValByName('company_logo');
        $imagePath = storage_path('/uploads/logo/'.(isset($company_logo) && ! empty($company_logo) ? $company_logo : 'logo-dark.png'));

        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $img = 'data:image/png;base64,'.$imageData;
        } else {
            \Log::error('Image file does not exist: '.$imagePath);
            $img = ''; // Fallback or default image if necessary
        }

        //return view('fors.bronze.template', compact('driver', 'driverPolicy', 'forsBronze', 'img', 'settings'));

        $view = view('fors.bronze.template', compact('driver', 'driverPolicy', 'forsBronze', 'img', 'settings'))->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($view)
            ->setOptions(['defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);

        $filename = 'Policy Accept ('.$driver->name.').pdf';

        return $pdf->download($filename);

    }

    public function BronzeacceptdownloadPdf($forsBronzeId)
    {

        $forsBronze = ForsBronze::find($forsBronzeId);

        if (! $forsBronze) {
            return redirect()->back()->with('error', __('ForsBronze not found.'));
        }
        // Fetch all accepted drivers for the given ForsBronze ID
        $acceptedDrivers = \Illuminate\Support\Facades\DB::table('driver_bronze_policy')
            ->join('drivers', 'driver_bronze_policy.driver_id', '=', 'drivers.id')
            ->join('company_details', 'drivers.companyName', '=', 'company_details.id')
            ->where('fors_bronze_id', $forsBronzeId)
            ->where('driver_bronze_policy.status', 'Accept')
            ->get([
                'driver_bronze_policy.driver_id',
                'driver_bronze_policy.driver_signature',
                'drivers.name',
                'company_details.name as companyName',
                'driver_bronze_policy.status',
            ]);

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('fors.bronze.acceptpolicytemplate', ['acceptedDrivers' => $acceptedDrivers, 'policyName' => $forsBronze->bronze_policy_name]);

        // Define the file name with policy name
        $fileName = 'Accepted_Driver_List_For-'.str_replace(' ', '_', $forsBronze->bronze_policy_name).'.pdf';

        // Download PDF
        return $pdf->download($fileName);
    }

    public function BronzeDeclinedownloadPdf($forsBronzeId)
    {

        $forsBronze = ForsBronze::find($forsBronzeId);

        if (! $forsBronze) {
            return redirect()->back()->with('error', __('policy not found.'));
        }
        // Fetch all accepted drivers for the given ForsBronze ID
        $declinedDrivers = \Illuminate\Support\Facades\DB::table('driver_bronze_policy')
            ->join('drivers', 'driver_bronze_policy.driver_id', '=', 'drivers.id')
            ->join('company_details', 'drivers.companyName', '=', 'company_details.id')
            ->where('fors_bronze_id', $forsBronzeId)
            ->where('driver_bronze_policy.status', 'Decline')
            ->get([
                'drivers.name',
                'company_details.name as companyName',
                'driver_bronze_policy.status',
            ]);

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('fors.bronze.declinepolicytemplate', ['declinedDrivers' => $declinedDrivers, 'policyName' => $forsBronze->bronze_policy_name]);

        // Define the file name with policy name
        $fileName = 'Decline_Driver_List_For-'.str_replace(' ', '_', $forsBronze->bronze_policy_name).'.pdf';

        // Download PDF
        return $pdf->download($fileName);
    }
}
