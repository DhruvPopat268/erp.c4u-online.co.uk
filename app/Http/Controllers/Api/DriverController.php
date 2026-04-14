<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Depot;
use App\Models\Driver;
use App\Models\ForsGold;
use App\Models\AppVersion;
use App\Models\DriverUser;
use App\Models\ForsBronze;
use App\Models\ForsSilver;
use App\Models\Endorsement;
use App\Models\Entitlement;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\CompanyDetails;
use App\Models\PolicyAssignment;
use App\Models\DriverAttachments;
use App\Models\Contract_attachment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;




class DriverController extends Controller
{
    public function changePassword(Request $request, $id)
    {
        $request->validate([
            'new_password' => 'required|min:5',
            'confirm_password' => 'required|same:new_password',
        ]);

        $driverUser = DriverUser::findOrFail($id);

        $driverUser->password = Hash::make($request->new_password);
        $driverUser->save();

        return redirect()->route('driver.index')->with('success', 'Password changed successfully.');
    }

    public function getDriverProfile()
    {
        // Get the authenticated user
        $user = Auth::user(); // This should be an instance of DriverUser

        // Ensure the authenticated user is a DriverUser
        if (! $user instanceof DriverUser) {
            return response()->json(['status' => 0, 'error' => 'User not found'], 404);
        }

        // Fetch the driver details for the authenticated user
        $driver = $user->driver; // Access the related Driver model

        // Check if driver data exists
        if (! $driver) {
            return response()->json(['status' => 0, 'error' => 'Driver profile not found'], 404);
        }

         // Fetch related company details
    $company = $driver->companyDetails; // Access the related CompanyDetails model

        // Fetch driver attachments
        $attachments = \App\Models\DriverAttachments::where('driver_id', $driver->id)->first();

        // Define base URL for image files
        $baseImageUrl = url('storage/');

        // Extract image URLs from the attachments
        $imageUrls = [
            'license_front' => $attachments?->license_front ? $baseImageUrl.'/'.$attachments->license_front : null,
            'license_back' => $attachments?->license_back ? $baseImageUrl.'/'.$attachments->license_back : null,
            'cpc_card_front' => $attachments?->cpc_card_front ? $baseImageUrl.'/'.$attachments->cpc_card_front : null,
            'cpc_card_back' => $attachments?->cpc_card_back ? $baseImageUrl.'/'.$attachments->cpc_card_back : null,
            'tacho_card_front' => $attachments?->tacho_card_front ? $baseImageUrl.'/'.$attachments->tacho_card_front : null,
            'tacho_card_back' => $attachments?->tacho_card_back ? $baseImageUrl.'/'.$attachments->tacho_card_back : null,
            'mpqc_card_front' => $attachments?->mpqc_card_front ? $baseImageUrl.'/'.$attachments->mpqc_card_front : null,
            'mpqc_card_back' => $attachments?->mpqc_card_back ? $baseImageUrl.'/'.$attachments->mpqc_card_back : null,
            'levelD_card_front' => $attachments?->levelD_card_front ? $baseImageUrl.'/'.$attachments->levelD_card_front : null,
            'levelD_card_back' => $attachments?->levelD_card_back ? $baseImageUrl.'/'.$attachments->levelD_card_back : null,
            'one_card_front' => $attachments?->one_card_front ? $baseImageUrl.'/'.$attachments->one_card_front : null,
            'one_card_back' => $attachments?->one_card_back ? $baseImageUrl.'/'.$attachments->one_card_back : null,
            'additional_cards' => $attachments && $attachments->additional_cards
                ? array_map(fn($path, $index) => ["additional_cards"  => $baseImageUrl.'/'.$path], json_decode($attachments->additional_cards, true), array_keys(json_decode($attachments->additional_cards, true)))
            : [], // Ensure additional_cards is an array of URLs
        ];

        // Encode the driver ID
        $encodedId = base64_encode($driver->id);

        // Define the base URL for driver details view
        $baseUrl = url('/driver/pdf/data/'.$encodedId);

        // Decode the JSON data from the endorsements column
        $endorsements = json_decode($driver->endorsements, true) ?? [];

        // Initialize variables for latest penalty points and offence code counts
        $latestPenaltyPoints = '0';
        $offenceCodes = [];

        // Get the latest penalty points value
        $latestPenaltyPoints = array_reduce($endorsements, function ($carry, $endorsement) {
            return isset($endorsement['penaltyPoints']) ? max($carry, $endorsement['penaltyPoints']) : $carry;
        }, 0);

        // Collect unique offence codes
        foreach ($endorsements as $endorsement) {
            if (isset($endorsement['offenceCode'])) {
                $offenceCodes[] = $endorsement['offenceCode'];
            }
        }

        // Count unique offence codes
        $uniqueOffenceCodeCount = count(array_unique($offenceCodes));

        // Return the driver profile details as JSON
        return response()->json([
            'status' => 1,
            'driver' => [
                'id' => $driver->id,
                'name' => $driver->name,
                'company_name' => $company ? $company->name : 'null',
                                'company_id' => $company ? $company->id : 'null',
                'company_account_id' => $company ? $company->account_no : 'null',
                'company_address' => $company ? $company->address : 'null',
                'driver_number' => $driver->ni_number,

                'licence_no' => $driver->driver_licence_no,
                'issue_no' => $driver->token_issue_number,
                'licence_valid_from' => $driver->token_valid_from_date,
                'licence_valid_to' => $driver->driver_licence_expiry,
                'gender' => $driver->gender,
                'dob' => $driver->driver_dob,
                'address' => $driver->driver_address.', '.$driver->post_code,
                'licence_status' => $driver->driver_licence_status,
                'licence_type' => $driver->licence_type,
                'tacho_card_no' => $driver->tacho_card_no,
                'tacho_card_valid_from' => $driver->tacho_card_valid_from,
                'tacho_card_valid_to' => $driver->tacho_card_valid_to,
                'cpc_valid_from' => $driver->dqc_issue_date,
                'cpc_valid_to' => $driver->cpc_validto,
                                'last_lc_date' => $driver->latest_lc_check,
                'pdf_url' => $baseUrl, // URL to view driver details with encoded ID
                'penalty_points' => $latestPenaltyPoints,
                'total_offence_code' => $uniqueOffenceCodeCount,
                'attachments' => $imageUrls, // Include the image URLs
            ],
        ]);
    }

       public function showContent($id)
    {
        // Fetch the policy assignment using the ID
        $policy = \App\Models\PolicyAssignment::find($id);

        if (!$policy) {
            return response()->json([
                'status' => 0,
                'error' => 'Policy not found',
            ], 404);
        }

        // Extract the policy description (HTML content)
        $policyDescription = $policy->description;

        // Add margins using inline CSS
        $contentWithMargins = "<div style='margin: 100px;'>{$policyDescription}</div>";

        // Return the content with margins as HTML
        return response()->make($contentWithMargins, 200, [
            'Content-Type' => 'text/html',
        ]);
    }



    public function getAssignPolicyList(Request $request)
    {
        // Retrieve the currently authenticated user
        $user = Auth::user(); // This should be an instance of DriverUser

        // Ensure the authenticated user is a DriverUser
        if (!$user instanceof \App\Models\DriverUser) {
            return response()->json([
                'status' => 0,
                'error' => 'User not found',
            ], 404);
        }

        // Fetch the driver details for the authenticated user
        $driver = $user->driver; // Access the related Driver model

        if (!$driver) {
            return response()->json([
                'status' => 0,
                'error' => 'Driver not found',
            ], 404);
        }

        // Fetch the latest policy assignments related to the driver's ID, grouped by policy_type
        $policyAssignments = \App\Models\PolicyAssignment::where('driver_id', $driver->id)
                                                         ->whereNotIn('status', ['Accept', 'Decline']) // Filter out 'Accept' and 'Decline'
                                                         ->select('id', 'policy_type', 'policy_id', 'driver_id', 'policy_version', 'description', 'company_id')
                                                         ->orderBy('policy_version', 'desc')
                                                         ->get()
                                                         ->unique('policy_id');

        // Prepare an array to hold the policy data with names and links
        $policiesWithName = [];

        // Generate a base URL for the content link
        $baseUrl = url('/api/policy-content');

        // Iterate over each policy assignment to fetch the corresponding policy name and description
        foreach ($policyAssignments as $policy) {
            $policyName = null;
            $policyDescription = null;
            $contentLink = null;

            // Fetch the company name using the company_id
            $companyName = \App\Models\CompanyDetails::where('id', $policy->company_id)->value('name');

            switch ($policy->policy_type) {
                case 'bronze':
                    $policyModel = \App\Models\ForsBronze::find($policy->policy_id);
                    $policyName = $policyModel ? $policyModel->bronze_policy_name : null;
                    break;

                case 'silver':
                    $policyModel = \App\Models\ForsBronze::find($policy->policy_id);
                    $policyName = $policyModel ? $policyModel->bronze_policy_name : null;
                    break;

                case 'gold':
                    $policyModel = \App\Models\ForsBronze::find($policy->policy_id);
                    $policyName = $policyModel ? $policyModel->bronze_policy_name : null;
                    break;
            }

            // Generate a random 5-character string
            $randomString = \Illuminate\Support\Str::random(5);

            // Generate a URL for the policy content with the random string appended
            $contentLink = $baseUrl . '/' . $policy->id . '?token=' . $randomString;

            $policy_type_display = $policy->policy_type === 'bronze' ? 'browse' : $policy->policy_type;


            // Add the policy assignment data with the name and content link to the array
            $policiesWithName[] = [
                'id' => $policy->id,
                'policy_type' => $policy_type_display,
                'policy_name' => $policyName,
                'driver_id' => $policy->driver_id,
                'version' => $policy->policy_version,
                'description' => $contentLink,
            ];
        }

        // Return the policy assignments with names and links as JSON
        return response()->json([
            'status' => 1,
            'data' => $policiesWithName,
        ]);
    }

public function getPolicyList(Request $request)
{
    // Retrieve the currently authenticated user
    $user = Auth::user(); // This should be an instance of DriverUser

    // Ensure the authenticated user is a DriverUser
    if (!$user instanceof \App\Models\DriverUser) {
        return response()->json([
            'status' => 0,
            'error' => 'User not found',
        ], 404);
    }

    // Fetch the driver details for the authenticated user
    $driver = $user->driver; // Access the related Driver model

    if (!$driver) {
        return response()->json([
            'status' => 0,
            'error' => 'Driver not found',
        ], 404);
    }

    // Fetch all policy assignments related to the driver's ID, including all statuses and versions
    $policyAssignments = \App\Models\PolicyAssignment::where('driver_id', $driver->id)
                                                     ->select('id','policy_type', 'policy_id')
                                                     ->get();

    // Prepare an array to hold the policy data with names and links
    $policiesWithName = [];

    // Iterate over each policy assignment to fetch the corresponding policy name and description
    foreach ($policyAssignments as $policy) {
        $policyName = null;

        // Fetch the company name using the company_id
        $companyName = \App\Models\CompanyDetails::where('id', $policy->company_id)->value('name');

        switch ($policy->policy_type) {
            case 'bronze':
                $policyModel = \App\Models\ForsBronze::find($policy->policy_id);
                $policyName = $policyModel ? $policyModel->bronze_policy_name : null;
                break;

            case 'silver':
                $policyModel = \App\Models\ForsBronze::find($policy->policy_id);
                $policyName = $policyModel ? $policyModel->bronze_policy_name : null;
                break;

            case 'gold':
                $policyModel = \App\Models\ForsBronze::find($policy->policy_id);
                $policyName = $policyModel ? $policyModel->bronze_policy_name : null;
                break;
        }
$policy_type_display = $policy->policy_type === 'bronze' ? 'browse' : $policy->policy_type;

        // Add the policy assignment data with the name and content link to the array
        $policiesWithName[] = [
            'id' => $policy->policy_id,
            'policy_name' => $policyName,
            'policy_type' =>  $policy_type_display,
        ];
    }

    // Remove duplicates based on the 'id' field
    $uniquePolicies = collect($policiesWithName)->unique('id')->values();

    // Return the policy assignments with names and links as JSON
    return response()->json([
        'status' => 1,
        'data' => $uniquePolicies,
    ]);
}



public function getSpecificPolicyDetails(Request $request)
{
    // Retrieve the currently authenticated user
    $user = Auth::user(); // This should be an instance of DriverUser

    // Ensure the authenticated user is a DriverUser
    if (!$user instanceof \App\Models\DriverUser) {
        return response()->json([
            'status' => 0,
            'error' => 'User not found',
        ], 404);
    }

    // Fetch the driver details for the authenticated user
    $driver = $user->driver; // Access the related Driver model

    if (!$driver) {
        return response()->json([
            'status' => 0,
            'error' => 'Driver not found',
        ], 404);
    }

    $policyId = $request->input('policy_id');



    // Fetch all policy assignments related to the driver's ID, including all statuses and versions
    $policyAssignments = \App\Models\PolicyAssignment::where('driver_id', $driver->id)->where('policy_id', $policyId)
                                                     ->select('id','policy_type', 'policy_id','policy_version','reviewed_on','description','status','next_review_date')
                                                     ->get();

    // Prepare an array to hold the policy data with names and links
    $policiesWithName = [];

        // Generate a base URL for the content link
        $baseUrl = url('/api/policy-content');



    // Iterate over each policy assignment to fetch the corresponding policy name and description
    foreach ($policyAssignments as $policy) {
        $policyName = null;
        $policyDescription = null;
        $contentLink = null;

        // Fetch the company name using the company_id
        $companyName = \App\Models\CompanyDetails::where('id', $policy->company_id)->value('name');

        switch ($policy->policy_type) {
            case 'bronze':
                $policyModel = \App\Models\ForsBronze::find($policy->policy_id);
                $policyName = $policyModel ? $policyModel->bronze_policy_name : null;
                break;

            case 'silver':
                $policyModel = \App\Models\ForsBronze::find($policy->policy_id);
                $policyName = $policyModel ? $policyModel->bronze_policy_name : null;
                break;

            case 'gold':
                $policyModel = \App\Models\ForsBronze::find($policy->policy_id);
                $policyName = $policyModel ? $policyModel->bronze_policy_name : null;
                break;
        }

            // Generate a random 5-character string
     $randomString = \Illuminate\Support\Str::random(5);

     // Generate a URL for the policy content with the random string appended
     $contentLink = $baseUrl . '/' . $policy->id . '?token=' . $randomString;


        // Add the policy assignment data with the name and content link to the array
        $policiesWithName[] = [
            'id' => $policy->id,
            'policy_name' => $policyName,
            'status' => $policy->status,
            'reviewed_on' => $policy->reviewed_on,
            'next_review_date' => $policy->next_review_date,
            'version' => $policy->policy_version,
            'description' => $contentLink,

        ];
    }

    // Return the policy assignments with names and links as JSON
    return response()->json([
        'status' => 1,
        'data' => $policiesWithName,
    ]);
}

    public function saveSignature(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'policy_id' => 'required|exists:policy_assignments,id',
            'signature' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'status' => 'required|boolean',
            'comment' => 'nullable|string',
            'start_time' => 'nullable|date_format:d/m/Y H:i:s', // Validate as time (e.g., 14:30)
            'end_time' => 'nullable|date_format:d/m/Y H:i:s|after:start_time', // Ensure end_time is after start_time
        ]);

        // Retrieve the policy_id from the request
        $policyId = $request->input('policy_id');

        // Find the PolicyAssignment instance by ID
        $policy = \App\Models\PolicyAssignment::find($policyId);

        if (!$policy) {
            return response()->json([
                'status' => 0,
                'error' => 'PolicyAssignment not found',
            ], 404);
        }

       // Handle the signature image
       if ($request->hasFile('signature')) {
        $file = $request->file('signature');
        $fileName = time() . '.' . $file->getClientOriginalExtension();
        $filePath = 'signatures/' . $fileName; // Specify the path relative to the storage directory
        Storage::disk('local')->put($filePath, file_get_contents($file));

        // Update the PolicyAssignment with the signature path
        $policy->signature = $filePath;
    }

       // Update the PolicyAssignment with the status
       $status = $request->input('status') ? 'Accept' : 'Decline';
       $policy->comment = $request->input('comment');
       $policy->status = $status;
  // Parse start and end times using Carbon
    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $request->input('start_time'));
    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $request->input('end_time'));

    // Calculate the difference in seconds
    $durationInSeconds = $endTime->diffInSeconds($startTime);

    // Convert the difference to minutes and seconds
    $minutes = intdiv($durationInSeconds, 60);
    $seconds = $durationInSeconds % 60;

    // Build the duration string
    $duration = '';
    if ($minutes > 0) {
        $duration .= "{$minutes} min";
    }
    if ($seconds > 0) {
        $duration .= ($minutes > 0 ? ' ' : '') . "{$seconds} sec";
    }

     $policy->start_time = $startTime;
    $policy->end_time = $endTime;
    $policy->duration = $duration;

       // Save the PolicyAssignment instance
       $policy->save();



        return response()->json([
            'status' => 1,
            'message' => 'Signature and status saved successfully',
            'data' => [
                'signature' => $policy->signature,
                'status' => $policy->status,
            ],
        ]);
    }

    public function upload(Request $request)
    {
    $user = Auth::user();

    $driverId = $user->driver->id;

        $validator = Validator::make($request->all(), [
            'license_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'license_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cpc_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cpc_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tacho_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tacho_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'mpqc_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'mpqc_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'levelD_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'levelD_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'one_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'one_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_cards.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0,'error' => $validator->errors()], 400);
        }

        $driverAttachment = \App\Models\DriverAttachments::where('driver_id', $driverId)->first();

        if (! $driverAttachment) {
            $driverAttachment = new \App\Models\DriverAttachments();
            $driverAttachment->driver_id = $driverId; // Ensure this is just the driver ID, not a JSON object
        }

        // Handle file uploads
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

        $driverAttachment->save();

        return response()->json([
            'status' => 1,
            'message' => 'Driver File Upload successful',
        ]);
    }

// public function upload(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['errors' => $validator->errors()], 400);
//     }

//     $user = Auth::user();
//     $driver = $user->driver; // Access the related Driver model

//     if (!$driver) {
//         return response()->json(['error' => 'Unauthenticated or no associated driver.'], 401);
//     }

//     $images = $request->file('images');

//     foreach ($images as $image) {
//         $path = $image->store('images', 'public');

//         // Debugging: Log the path and driver ID
//         \Log::info('Image Path: '.$path);
//         \Log::info('Driver ID: '.$driver->id);

//         $attachment = \App\Models\DriverAttachments::create([
//             'driver_id' => $driver->id,
//             'multipleimagepath' => $path,
//         ]);

//         // Check if the attachment was created
//         if (!$attachment) {
//             \Log::error('Failed to create attachment.');
//         }
//     }

//     return response()->json(['message' => 'Images uploaded successfully.']);
// }

    public function getContactBook(Request $request)
    {
         // Get the logged-in driver user
         $driverUser = Auth::user();

         // Ensure the driver user is logged in
         if (!$driverUser) {
             return response()->json(['status'=> 0,'error' => 'Unauthorized'], 401);
         }

         // Fetch the corresponding driver record using the DriverUser's ID
         $driver = Driver::where('id', $driverUser->driver_id)->first();

         if (!$driver) {
             return response()->json(['status'=> 0,'error' => 'Driver not found'], 404);
         }

        // Fetch data from WorkAroundContact where company_id matches the Driver's companyName
        $contacts = \App\Models\WorkAroundContact::where('company_id', $driver->companyName)
            ->select('name', 'designation', 'mobile_no', 'company_id', 'address')
            ->get();

        // Add the +44 prefix to each mobile number and replace company_id with company name
        $contacts->transform(function ($contact) {
            // Add +44 prefix if not already present
            if (!str_starts_with($contact->mobile_no, '+44')) {
                $contact->mobile_no = '+44' . $contact->mobile_no;
            }

            // Fetch the company name from the CompanyDetails model using the correct column (assuming 'id')
            $company = \App\Models\CompanyDetails::where('id', $contact->company_id)->first();
            if ($company) {
                // Replace company_id with company_name
                $contact->company_name = $company->name;
            }

            // Remove the original company_id key from the response
            unset($contact->company_id);

            return $contact;
        });

         // Return the data as JSON
         return response()->json(['status'=> 1, 'contactbook' => $contacts], 200);
     }

public function getProfileData(Request $request)
{
    // Get the logged-in driver user
    $driverUser = Auth::user();

    // Ensure the driver user is logged in
    if (!$driverUser) {
        return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
    }

    // Fetch the corresponding driver record using the DriverUser's ID
    $driver = Driver::where('id', $driverUser->driver_id)->first();

    if (!$driver) {
        return response()->json(['status' => 0, 'error' => 'Driver not found'], 404);
    }

    // Fetch the profiles where company_id matches the Driver's companyName and mobile_app_enabled is 'Yes'
    $profiles = \App\Models\WorkAroundProfile::where('company_id', $driver->companyName)
        ->where('mobile_app_enabled', 'Yes')
        ->get();

    // Fetch the step values from the WorkAroundStore model for the logged-in driver's ID
    $steps = \App\Models\WorkAroundStore::where('driver_id', $driver->id)->orderBy('step', 'asc')->get();

    // Initialize default values for step status and profile ID
    $stepStatus = 'nofound';
    $profileId = 0;
    $storeId = 0;

    if ($steps->isNotEmpty()) {
        // Iterate through the steps to check if all are "done"
        $allStepsDone = true;
        $latestStep = null;

        foreach ($steps as $step) {
            if ($step->step != 'done') {
                $allStepsDone = false;
                $latestStep = $step->step; // Keep track of the latest unfinished step
                break;
            }
        }

        // If all steps are "done", show "done", otherwise show the latest unfinished step value
        $stepStatus = $allStepsDone ? 'done' : $latestStep;

        // Fetch the profile associated with the latest step
        $profile = \App\Models\WorkAroundProfile::where('id', $step->profile_id)->first();

        if ($profile) {
            $profileId = $step->profile_id;
                        $storeId = $step->id;


            // Assuming `WorkAroundProfileDetails` holds the connection between profile and questions
            $workAroundQuestionIds = \App\Models\WorkAroundProfileDetails::where('work_around_profile_id', $profile->id)
                ->pluck('work_around_question_id')
                ->toArray();

            // Now count questions based on those IDs
            $totalQuestions = \App\Models\WorkAroundQuestion::whereIn('id', $workAroundQuestionIds)->count();

            if ($totalQuestions > 0) {
                // Set the number of questions per page
                $questionsPerPage = 10;

                // Calculate the total number of pages
                $totalPages = (int) ceil($totalQuestions / $questionsPerPage);
            } else {
                $totalPages = 0;
            }
        } else {
            $totalPages = 0;
        }
    } else {
        $totalPages = 0;
    }

    // Initialize array to hold the profiles
    $profileData = [];

    // Iterate over profiles and prepare profile data
    foreach ($profiles as $profile) {
        $profileData[] = [
            'id' => $profile->id,
            'name' => $profile->name,
        ];
    }

    // Return the profile data as JSON
    return response()->json([
        'status' => 1,
        'step' => $stepStatus,
        'profile_id' => $profileId,
                'workaround_store_id' => $storeId,
        'total_pages' => $totalPages,
        'profiles' => $profileData,
    ], 200);
}


public function getProfileDetails(Request $request)
{
    // Validate that profile_id is present in the request
    $request->validate([
        'profile_id' => 'required|integer',
    ]);

    // Get the logged-in driver user
    $driverUser = Auth::user();

    // Ensure the driver user is logged in
    if (!$driverUser) {
        return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
    }

    // Fetch the corresponding driver record using the DriverUser's ID
    $driver = Driver::where('id', $driverUser->driver_id)->first();

    if (!$driver) {
        return response()->json(['status' => 0, 'error' => 'Driver not found'], 404);
    }

    // Fetch the profile by profile ID from the request and ensure it belongs to the same company
    $profile = \App\Models\WorkAroundProfile::where('company_id', $driver->companyName)
        ->where('id', $request->profile_id)
        ->first();

    if (!$profile) {
        return response()->json(['status' => 0, 'error' => 'Profile not found'], 404);
    }

    // Fetch the operating centers
    $operatingCentres = Depot::where('companyName', $driver->companyName)->where('id', $driver->depot_id)->get();

    // Fetch vehicles related to the profile
    $profileVehicles = \App\Models\WorkAroundProfileDetails::where('company_id', $driver->companyName)
        ->where('work_around_profile_id', $profile->id)
        ->get();

    // Allowed vehicle statuses
    $allowedStatuses = ['Owned', 'Rented', 'Leased', 'Contract Hire', 'Depot Transfer'];

    // Initialize arrays to hold vehicle and question IDs
    $uniqueVehicleIds = [];
    $workAroundQuestionIds = [];
    $workAroundProfileDetails = [];

    // Collect unique vehicle IDs and work_around_question_ids
    foreach ($profileVehicles as $vehicle) {
        if (!in_array($vehicle->vehicle_id, $uniqueVehicleIds)) {
            $uniqueVehicleIds[] = $vehicle->vehicle_id;
        }
        if (!in_array($vehicle->work_around_question_id, $workAroundQuestionIds)) {
            $workAroundQuestionIds[] = $vehicle->work_around_question_id;
        }
        // Collect WorkAroundProfileDetails IDs
        $workAroundProfileDetails[$vehicle->work_around_question_id] = $vehicle->id;
    }

    // Fetch vehicles only if they belong to the driver's `depot_id`
    $vehicles = \App\Models\Vehicles::whereIn('id', $uniqueVehicleIds)
        ->whereHas('vehicleDetail', function ($query) use ($allowedStatuses, $driver) {
            $query->whereIn('vehicle_status', $allowedStatuses)
                  ->where('depot_id', $driver->depot_id); // Filter by driver's depot_id
        })
        ->get();

    // Fetch vehicle details for make
    $vehicleDetails = \App\Models\vehicleDetails::whereIn('vehicle_id', $uniqueVehicleIds)
        ->whereIn('vehicle_status', $allowedStatuses)
        ->where('depot_id', $driver->depot_id) // Ensure vehicle is from the same depot as the driver
        ->get()
        ->keyBy('vehicle_id');

    // Fetch work around question details
    $questionsPerPage = 10;

    // Fetch all the questions without pagination
    $workAroundQuestions = \App\Models\WorkAroundQuestion::whereIn('id', $workAroundQuestionIds)->get();

    // Calculate the total number of pages
    $totalQuestions = $workAroundQuestions->count();
    $totalPages = (int) ceil($totalQuestions / $questionsPerPage);

    // Get the current page's data based on $questionsPerPage
    $currentPageQuestions = $workAroundQuestions->forPage(1, $questionsPerPage);

    // Return the data including total page count and the current questions on the first page
    return response()->json([
        'status' => 1,
        'total_questions' => $totalQuestions,
        'total_pages' => $totalPages,
        'operating_centres' => collect($operatingCentres)->map(function ($operatingCentre) {
            return [
                'id' => $operatingCentre->id,
                'name' => $operatingCentre->name,
            ];
        }),
        'vehicles' => $vehicles->map(function ($vehicle) use ($vehicleDetails) {
            // Get vehicle details by vehicle_id
            $details = $vehicleDetails->get($vehicle->id);

            return [
                'vehicle_id' => $vehicle->id,
                'id' => $vehicle->id,
                'vehicle' => ($vehicle->vehicle_type == 'Trailer'
                    ? ($details ? $details->vehicle_nick_name : 'No Vehicle ID')
                    : ($vehicle->registrations ?? 'No Registration')) . ' - ' . ($details ? $details->make : 'N/A'),
            ];
        }),
    ], 200);
}


public function getQuestionsByPage(Request $request)
{
    // Validate the required input for page number
    $request->validate([
        'profile_id' => 'required|integer',
        'page' => 'required|integer|min:1', // Ensure the page is at least 1
    ]);

    // Get the logged-in driver user
    $driverUser = Auth::user();

    // Ensure the driver user is logged in
    if (!$driverUser) {
        return response()->json(['status' => 0,'error' => 'Unauthorized'], 401);
    }

    // Fetch the corresponding driver record using the DriverUser's ID
    $driver = Driver::where('id', $driverUser->driver_id)->first();

    if (!$driver) {
        return response()->json(['status' => 0,'error' => 'Driver not found'], 404);
    }

    // Fetch the profile by profile ID and ensure it belongs to the same company
    $profile = \App\Models\WorkAroundProfile::where('company_id', $driver->companyName)
        ->where('id', $request->profile_id)
        ->first();

    if (!$profile) {
        return response()->json(['status' => 0,'error' => 'Profile not found'], 404);
    }

    // Fetch vehicles related to the profile
    $profileVehicles = \App\Models\WorkAroundProfileDetails::where('company_id', $driver->companyName)
        ->where('work_around_profile_id', $profile->id)
        ->get();

    // Initialize arrays to hold vehicle and question IDs
    $uniqueVehicleIds = [];
    $workAroundQuestionIds = [];
    $workAroundProfileDetails = [];

    // Collect unique vehicle IDs and work_around_question_ids
    foreach ($profileVehicles as $vehicle) {
        if (!in_array($vehicle->vehicle_id, $uniqueVehicleIds)) {
            $uniqueVehicleIds[] = $vehicle->vehicle_id;
        }
        if (!in_array($vehicle->work_around_question_id, $workAroundQuestionIds)) {
            $workAroundQuestionIds[] = $vehicle->work_around_question_id;
        }
        // Collect WorkAroundProfileDetails IDs
        $workAroundProfileDetails[$vehicle->work_around_question_id] = $vehicle->id;
    }

    // Set the number of questions per page
    $questionsPerPage = 10;

    // Fetch work around question details
    $workAroundQuestions = \App\Models\WorkAroundQuestion::whereIn('id', $workAroundQuestionIds)->get();

    // Calculate the total number of questions and pages
    $totalQuestions = $workAroundQuestions->count();
    $totalPages = (int) ceil($totalQuestions / $questionsPerPage);

    // Get the page number from request
    $currentPage = $request->page;

    // Ensure the page number is within valid bounds
    if ($currentPage > $totalPages) {
        return response()->json(['status' => 0,'error' => 'Page number exceeds total pages'], 400);
    }

    // Get the current page's data
    $currentPageQuestions = $workAroundQuestions->forPage($currentPage, $questionsPerPage);

    // Count the number of questions on the current page
    $currentPageQuestionCount = $currentPageQuestions->count();

    // **Set the starting index for the current page to 1**
    $startIndex = 1;

    // Map question_type to human-readable values and add an incremented index starting from 1 for each page
    $workAroundQuestionsMapped = $currentPageQuestions->map(function ($question, $index) use ($workAroundProfileDetails, $startIndex) {
        // Determine human-readable value for question_type
        $questionType = '';
        if ($question->question_type == 'Yes/No') {
            $questionType = 2;
        } elseif ($question->question_type == 'Yes/No/N-A') {
            $questionType = 3;
        }

        $reasonImage = '';
     if ($question->select_reasonimage == 'Yes' || $question->select_reasonimage == 'None') {
            $reasonImage = true;
        } elseif ($question->select_reasonimage == 'No') {
            $reasonImage = false;
        }

        return [
            'index' => $startIndex + $index, // **Add incremented index starting from 1**
            'question_id' => $question->id,
            'name' => $question->name,
            'description' => $question->description,
            'question_type' => $questionType,
            'reasonimage' => $reasonImage,
            'profile_details_id' => $workAroundProfileDetails[$question->id] ?? null,
        ];
    });

    // Return the data as JSON including pagination info
    return response()->json([
        'status' => 1,
        'current_page' => $currentPage,
        'total_questions' => $totalQuestions,
        'total_pages' => $totalPages,
                'current_page_total_questions' => $currentPageQuestionCount, // Add this line
        'questions' => $workAroundQuestionsMapped->values()->toArray(),
    ], 200);
}

    public function storeworkaroundstep1(Request $request)
    {
        $user = Auth::user();

        // Ensure the user is logged in and associated with a driver
        if (!$user || !$user->driver) {
            return response()->json(['status' => 0,'error' => 'Unauthorized or Driver not found'], 401);
        }

        $driverId = $user->driver->id;
        $companyId = $user->driver->companyName;

        // Validate the request input
        $validator = Validator::make($request->all(), [
            'profile_id' => 'nullable|integer|exists:work_around_profiles,id',
            'operating_centres' => 'nullable|integer|exists:depots,id',
            'vehicle_id' => 'nullable|integer|exists:vehicles,id',
            'speedo_odometer' => 'nullable|string',
            'fuel_level' => 'nullable|string',
            'adblue_level' => 'nullable|string',
            'step' => 'nullable|string',
            'start_date' => 'nullable|date_format:d/m/Y H:i:s',
                        'lat_lng' => 'nullable|string'

        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['status' => 0,'error' => $validator->errors()], 400);
        }


        // Prepare the data array only with the values provided in the request
        $dataToUpdate = [];

        if ($request->has('profile_id')) {
            $dataToUpdate['profile_id'] = $request->input('profile_id');
        }

        if ($request->has('operating_centres_id')) {
            $dataToUpdate['operating_centres'] = $request->input('operating_centres_id');
        }

        if ($request->has('vehicle_id')) {
            $dataToUpdate['vehicle_id'] = $request->input('vehicle_id');
        }

        if ($request->has('speedo_odometer')) {
            $dataToUpdate['speedo_odometer'] = $request->input('speedo_odometer');
        }

        if ($request->has('fuel_level')) {
            $dataToUpdate['fuel_level'] = $request->input('fuel_level');
        }

        if ($request->has('adblue_level')) {
            $dataToUpdate['adblue_level'] = $request->input('adblue_level');
        }

        if ($request->has('step')) {
            $dataToUpdate['step'] = $request->input('step');
        }

        if ($request->has('start_date')) {
            $dataToUpdate['start_date'] = $request->input('start_date');
        }

         // Check if lat_lng is provided
    if ($request->has('lat_lng')) {
        $latLng = $request->input('lat_lng');
        // Split the lat_lng string into latitude and longitude
        list($latitude, $longitude) = explode(',', $latLng);

        // // Make a request to the Google Maps API to get the address
        // $googleApiKey = 'AIzaSyCYJrI4qi8sgD5DsKn9lVlUtQtKr_y13t4'; // Replace with your actual API key
        // $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key={$googleApiKey}";

        // // Make the API call using file_get_contents or Guzzle (Guzzle is recommended)
        // $geocodeResponse = file_get_contents($url);
        // $geocodeData = json_decode($geocodeResponse, true);

        // // If the response contains a formatted address, save it in the 'location' field
        // if (isset($geocodeData['results'][0]['formatted_address'])) {
        //     $dataToUpdate['location'] = $geocodeData['results'][0]['formatted_address'];
        // }

        // Save latitude and longitude in a single column 'lat_lng'
        $dataToUpdate['lat_lng'] = $latLng;
    }



        // Always update driver_id and company_id since they are tied to the user
        $dataToUpdate['driver_id'] = $driverId;
        $dataToUpdate['company_id'] = $companyId;

        // Create the instance of WorkAroundStore with the selective data
        $workAroundStore = \App\Models\WorkAroundStore::create($dataToUpdate);

        // Return a success response
        return response()->json([
            'status' => 1,
            'message' => 'Data saved successfully',
            'data' => $workAroundStore,
        ], 200);
    }

    protected $notificationService;

    public function __construct(\App\Notifications\WorkAroundCompleteNotification $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    // protected function sendNotificationToOperators($title, $message, $companyId)
    // {
    //     $notification = new \App\Models\WorkAroundNotification();
    //     $notification->company_id = $companyId;
    //     $notification->title = $title;
    //     $notification->message = $message;
    //             $notification->key = 1;
    //     $notification->save();

    //     // Retrieve operator FCM tokens from the User model
    //     $operators = \App\Models\User::where('companyname', $companyId)->get();
    //     $operatorTokens = $operators->pluck('operator_tokens')->toArray();

    //     \Log::info("Attempting to send notification to operators for company ID: {$companyId}");

    //     if (!empty($operatorTokens)) {
    //         \Log::info("Found operator tokens: " . implode(', ', $operatorTokens));

    //         $this->notificationService->send([
    //             'title' => $title,
    //             'message' => $message,
    //             'tokens' => $operatorTokens,
    //             'target' => 'mobile_app',
    //         ]);
    //     } else {
    //         \Log::warning("No valid operator tokens found for company ID: {$companyId}");
    //     }
    // }



    // public function updatestoreworkaroundstep1(Request $request)
    // {
    //     // Ensure the user is logged in and associated with a driver
    //     $user = Auth::user();
    //     if (!$user || !$user->driver) {
    //         return response()->json(['status' => 0, 'error' => 'Unauthorized or Driver not found'], 401);
    //     }

    //     // Validate the request input
    //     $validator = Validator::make($request->all(), [
    //         'workaround_store_id' => 'required|integer|exists:work_around_stores,id',
    //         'end_date' => 'nullable|date_format:d/m/Y H:i:s',
    //         'uploaded_date' => 'nullable|date_format:d/m/Y H:i:s',
    //         'signature' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:15000',
    //         'step' => 'nullable|string',
    //     ]);

    //     // If validation fails, return error response
    //     if ($validator->fails()) {
    //         return response()->json(['status' => 0, 'error' => $validator->errors()], 400);
    //     }

    //     // Retrieve the ID from the request
    //     $id = $request->input('workaround_store_id');

    //     // Find the existing record
    //     $workAroundStore = \App\Models\WorkAroundStore::find($id);
    //     if (!$workAroundStore) {
    //         return response()->json(['status' => 0, 'error' => 'Record not found'], 404);
    //     }

    //     // Handle signature image upload if provided
    //     if ($request->hasFile('signature')) {
    //         $signatureImage = $request->file('signature');
    //         $signatureImagePath = $signatureImage->store('walkaround_signatures', 'local');
    //         $workAroundStore->signature = $signatureImagePath;
    //     }

    //     // Update end_date and calculate duration
    //     if ($request->has('uploaded_date')) {
    //         try {
    //     $startDate = Carbon::createFromFormat('d/m/Y H:i:s', $workAroundStore->start_date);
    //     $uploadedDate = Carbon::createFromFormat('d/m/Y H:i:s', $request->input('uploaded_date'));

    //     // Check if uploaded_date is after start_date
    //     if ($uploadedDate->lessThan($startDate)) {
    //         return response()->json(['status' => 0, 'error' => 'Uploaded date must be after start date'], 400);
    //     }

    //     // Calculate duration in minutes and seconds
    //     $durationInSeconds = $uploadedDate->diffInSeconds($startDate);
    //     $minutes = intdiv($durationInSeconds, 60);
    //     $seconds = $durationInSeconds % 60;

    //     $uploadedDuration = '';
    //     if ($minutes > 0) {
    //         $uploadedDuration .= "{$minutes} min";
    //     }
    //     if ($seconds > 0) {
    //         $uploadedDuration .= ($minutes > 0 ? ' ' : '') . "{$seconds} sec";
    //     }

    //     $workAroundStore->duration = $uploadedDuration; // Update the duration field
    //     $workAroundStore->uploaded_date = $uploadedDate->format('d/m/Y H:i:s');
    //          } catch (\Exception $e) {
    //           return response()->json(['status' => 0, 'error' => 'Invalid date format for uploaded_date'], 400);
    //          }
    //      }

    //     // Update uploaded_date if provided
    //     if ($request->has('end_date')) {
    //         $workAroundStore->end_date = $request->input('end_date');
    //     }

    //     if ($request->has('step')) {
    //         $workAroundStore->step = $request->input('step');
    //     }

    //     // Save the updated record
    //     $workAroundStore->save();

    //     // Get the company details associated with the workAroundStore
    //     $company = $workAroundStore->types; // Assuming WorkAroundStore belongs to CompanyDetails

    //     // Send email notifications to operator emails
    //     $operatorEmails = json_decode($company->operator_email, true);
    //     if (is_array($operatorEmails)) {
    //         foreach ($operatorEmails as $email) {
    //         // Log the time before sending the email
    //         // \Log::info('Sending email to: ' . $email . ' at ' . now());

    //             try {
    //             // Send email to each operator
    //                 \Mail::to($email)->send(new \App\Mail\WorkAroundComplete($workAroundStore));

    //             // Log success after sending the email
    //             // \Log::info('Email sent to: ' . $email . ' successfully at ' . now());
    //             } catch (\Exception $e) {
    //             // Log any failure during email sending
    //             // \Log::error('Failed to send email to: ' . $email . ' at ' . now() . '. Error: ' . $e->getMessage());
    //             }
    //         }
    //     }

    //     // Send mobile app notification to operators via OneSignal
    //     $this->sendNotificationToOperators(
    //         'Walkaround Completed',
    //         "The walkaround is Completed by {$workAroundStore->driver->name}.",
    //         $company->id // Assuming the company ID is needed to fetch operator tokens
    //     );

    //     // Return a success response
    //     return response()->json([
    //         'status' => 1,
    //         'message' => 'Data updated successfully',
    //         'data' => $workAroundStore,
    //     ], 200);
    // }

protected function sendNotificationToOperators($title, $message, $companyId, $depotId, $groupId)
{
  //  \Log::info("=== Notification Triggered ===");

    $operators = \App\Models\User::where('companyname', $companyId)
        ->whereIn('walkaround_preference', ['notification', 'both'])
        ->whereJsonContains('depot_id', (string) $depotId)
        ->whereJsonContains('driver_group_id', (string) $groupId)
        ->get();

   // \Log::info("Operators Count: " . $operators->count());

    $operatorTokens = $operators->pluck('operator_tokens')->filter()->toArray();

   // \Log::info("Tokens: " . json_encode($operatorTokens));

    if (!empty($operatorTokens)) {

        $notification = new \App\Models\WorkAroundNotification();
        $notification->company_id = $companyId;
        $notification->title = $title;
        $notification->message = $message;
        $notification->depot_id = $depotId;
        $notification->key = 1;
        $notification->save();

        $this->notificationService->send([
            'title' => $title,
            'message' => $message,
            'tokens' => $operatorTokens,
            'target' => 'mobile_app',
        ]);

       // \Log::info("Notification sent successfully");
    } else {
        \Log::warning("No valid operator tokens found");
    }
}




public function updatestoreworkaroundstep1(Request $request)
{
   // \Log::info("=== Walkaround Step 1 Update API Called ===");

    $driveruser = Auth::user();

    if (! $driveruser || ! $driveruser->driver) {
        return response()->json(['status' => 0, 'error' => 'Unauthorized or Driver not found'], 401);
    }

    $validator = Validator::make($request->all(), [
        'workaround_store_id' => 'required|integer|exists:work_around_stores,id',
        'end_date' => 'nullable|date_format:d/m/Y H:i:s',
        'uploaded_date' => 'nullable|date_format:d/m/Y H:i:s',
        'signature' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:15000',
        'step' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 0, 'error' => $validator->errors()], 400);
    }

    $workAroundStore = \App\Models\WorkAroundStore::find($request->workaround_store_id);

    if (! $workAroundStore) {
        return response()->json(['status' => 0, 'error' => 'Record not found'], 404);
    }

    // Upload signature
    if ($request->hasFile('signature')) {
        $path = $request->file('signature')->store('walkaround_signatures', 'local');
        $workAroundStore->signature = $path;
    }

    // Handle uploaded_date + duration
    if ($request->has('uploaded_date')) {
        try {
            $startDate = Carbon::createFromFormat('d/m/Y H:i:s', $workAroundStore->start_date);
            $uploadedDate = Carbon::createFromFormat('d/m/Y H:i:s', $request->uploaded_date);

            if ($uploadedDate->lessThan($startDate)) {
                return response()->json(['status' => 0, 'error' => 'Uploaded date must be after start date'], 400);
            }

            $seconds = $uploadedDate->diffInSeconds($startDate);
            $minutes = intdiv($seconds, 60);
            $remainingSeconds = $seconds % 60;

            $duration = '';
            if ($minutes > 0) $duration .= "{$minutes} min ";
            if ($remainingSeconds > 0) $duration .= "{$remainingSeconds} sec";

            $workAroundStore->duration = trim($duration);
            $workAroundStore->uploaded_date = $uploadedDate->format('d/m/Y H:i:s');

        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'error' => 'Invalid date format'], 400);
        }
    }

    if ($request->has('end_date')) {
        $workAroundStore->end_date = $request->end_date;
    }

    if ($request->has('step')) {
        $workAroundStore->step = $request->step;
    }

    $workAroundStore->save();

    // 🔥 Get related data
    $company = $workAroundStore->types;
    $driver = $workAroundStore->driver;

   // \Log::info("Driver Depot: " . $driver->depot_id);
    // \Log::info("Driver Group: " . $driver->group_id);

    $operatorEmails = json_decode($company->operator_email, true);

   // \Log::info("Operator Emails: " . json_encode($operatorEmails));

    // ✅ FIXED FILTER
    $emailusers = \App\Models\User::whereIn('email', $operatorEmails)
        ->whereJsonContains('depot_id', (string) $driver->depot_id)
        ->whereJsonContains('driver_group_id', (string) $driver->group_id)
        ->get();

   // \Log::info("Matched Email Users: " . $emailusers->count());

    $shouldSendNotification = false;

    foreach ($emailusers as $user) {

        $preference = strtolower(trim($user->walkaround_preference));

       // \Log::info("User: {$user->email}, Preference: {$preference}");

        // 📧 EMAIL
        if ($preference === 'both' || $preference === 'email') {
            try {
                \Mail::to($user->email)->send(new \App\Mail\WorkAroundComplete($workAroundStore));
               // \Log::info("Email sent to: {$user->email}");
            } catch (\Exception $e) {
                \Log::error("Email failed for {$user->email}: " . $e->getMessage());
            }
        }

        // 🔔 NOTIFICATION FLAG
        if ($preference === 'both' || $preference === 'notification') {
            $shouldSendNotification = true;
        }
    }

    // 🔔 SEND NOTIFICATION ONCE
    if ($shouldSendNotification) {
        $this->sendNotificationToOperators(
            'Walkaround Completed',
            "The walkaround is Completed by {$driver->name}.",
            $company->id,
            $driver->depot_id,
            $driver->group_id
        );
    } else {
        \Log::warning("No users eligible for notification");
    }

    return response()->json([
        'status' => 1,
        'message' => 'Data updated successfully',
        'data' => $workAroundStore,
    ]);
}

 public function storeworkaroundstep2(Request $request)
    {
        $user = Auth::user();

        // Ensure the user is logged in and associated with a driver
        if (!$user || !$user->driver) {
            return response()->json(['status' => 0,'error' => 'Unauthorized or Driver not found'], 401);
        }

        // Validate the request input
        $validator = Validator::make($request->all(), [
             'profile_details_id' => 'required|integer',
            'question_id' => 'required|integer',
            'status' => 'required|integer|in:1,2,3', // Assuming status is a string (e.g., 'completed', 'pending')
            'reason' => 'nullable|string',
            'workaround_store_id' => 'required|integer|exists:work_around_stores,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:15000',
                        'step' => 'required|string'

        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['status' => 0,'error' => $validator->errors()], 400);
        }

        // Map the status input to corresponding values
    $statusMapping = [
        1 => 'Yes',
        2 => 'No',
        3 => 'N-A',
    ];

    // Get the corresponding status value for the database
    $statusValue = $statusMapping[$request->input('status')];

        // Handle image upload
    $imagePath = null;
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imagePath = $image->store('question_answer_reason_image', 'local'); // Store image in 'question_answer_reason_image' directory within storage
    }

        $workAroundStoreId = $request->input('workaround_store_id');

        // Fetch the related question
        $workAroundQuestion = \App\Models\WorkAroundQuestion::find($request->input('question_id'));

        $reason = null;
        $otherReason = null;

        if ($workAroundQuestion && $workAroundQuestion->select_reasonimage === "None") {
            // If select_reasonimage is "None", save reason into other_reason
            $otherReason = $request->input('reason');
        } else {
            // Otherwise, save normally into reason
            $reason = $request->input('reason');
        }

        // Create or update the instance of WorkAroundQuestionAnswerStore
        $workAroundStep2 = \App\Models\WorkAroundQuestionAnswerStore::updateOrCreate(
            [
                'profile_details_id' => $request->input('profile_details_id'),
                'question_id' => $request->input('question_id'),
                                'workaround_store_id' => $workAroundStoreId,

            ],
            [
                'status' => $statusValue,
                'reason' => $reason,
                'other_reason' => $otherReason,
                'image' => $imagePath,
                'workaround_store_id' => $workAroundStoreId,

            ]
        );

         // Update the step value in the WorkAroundStore model
    \App\Models\WorkAroundStore::where('id', $workAroundStoreId)
        ->update(['step' => $request->input('step')]);

         // Increment defects_count if image or reason is provided
        $workAroundStore = \App\Models\WorkAroundStore::find($workAroundStoreId);
       if ($workAroundStore) {
    $incrementDefectCount = $workAroundStore->defects_count ?? 0;

    // Check if should increment
    $shouldIncrement = false;

    if ($request->hasFile('image')) {
        $shouldIncrement = true;
    } elseif ($workAroundQuestion && $workAroundQuestion->select_reasonimage !== "None" && !empty($request->input('reason'))) {
        // Only increment if reason exists and select_reasonimage is NOT 'None'
        $shouldIncrement = true;
    }

    if ($shouldIncrement) {
        $incrementDefectCount++;
    }

    $workAroundStore->update(['defects_count' => $incrementDefectCount]);
}


        // Return a success response
        return response()->json([
            'status' => 1,
            'message' => 'Data saved successfully',
            'data' => $workAroundStep2,  // Optionally return the saved data
        ], 200);
    }

        public function deleteoldwalkaround(Request $request)
    {
        // Validate the request to ensure 'id' is provided
        $request->validate([
            'id' => 'required|integer',
        ]);

        // Get the 'id' from the request
        $id = $request->input('id');

        // Find the WorkAroundStore record by ID
        $workAroundStore = \App\Models\WorkAroundStore::find($id);

        // Check if the record exists
        if (!$workAroundStore) {
            return response()->json(['status' => 0, 'error' => 'Record not found'], 404);
        }

        // Start database transaction to ensure data consistency
        \DB::beginTransaction();

        try {
            // Delete the related WorkAroundQuestionAnswerStore records
            \App\Models\WorkAroundQuestionAnswerStore::where('workaround_store_id', $id)->delete();

            // Delete the WorkAroundStore record
            $workAroundStore->delete();

            // Commit the transaction
            \DB::commit();

            return response()->json(['status' => 1, 'message' => 'Old WalkAround deleted successfully'], 200);

        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            \DB::rollBack();
            return response()->json(['status' => 0, 'error' => 'Failed to delete the record and related data'], 500);
        }
    }

    public function getpreviewwalkaround(Request $request)
    {
        // Validate the optional vehicle_id and page input
        $request->validate([
            'vehicle_id' => 'nullable|integer', // Make vehicle_id optional
            'page' => 'nullable|integer|min:1', // Validate the page number
        ]);

        // Get the logged-in driver user
        $driverUser = Auth::user();

        // Ensure the driver user is logged in
        if (!$driverUser) {
            return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
        }

        // Fetch the corresponding driver record using the DriverUser's ID
        $driver = Driver::where('id', $driverUser->driver_id)->first();

        if (!$driver) {
            return response()->json(['status' => 0, 'error' => 'Driver not found'], 404);
        }

        // Build the query for WorkAroundStore
        $query = \App\Models\WorkAroundStore::where('driver_id', $driver->id)->where('step', 'done'); // Add condition to filter by step value

        // Apply vehicle_id filter if provided
        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->input('vehicle_id'));
        }

        // Order by defects_count in descending order
        $query->orderBy('created_at', 'desc');

        // Check if the page parameter is provided
        $page = $request->input('page');

        if ($page) {
            // Paginate the data if page is provided
            $workAroundStores = $query->with(['vehicle', 'driver']) // Eager load related models
                ->paginate(30, [ // Paginate with 30 items per page
                'id',
                    'uploaded_date',
                    'vehicle_id',
                    'driver_id',
                    'defects_count',
                    'duration'
                ]);
        } else {
            // Fetch all data if page is not provided
            $workAroundStores = $query->with(['vehicle', 'driver'])
                ->get([ // Retrieve all data without pagination
                'id',
                    'uploaded_date',
                    'vehicle_id',
                    'driver_id',
                    'defects_count',
                    'duration'
                ]);
        }

        // Map the result to include vehicle registration number, driver name, and defect status
        $result = $workAroundStores->map(function($item) {
            $isShortDuration = false;
        if ($item->duration) {
            $durationString = $item->duration;
            $totalSeconds = 0;

            if (preg_match('/(\d+)\s*min/', $durationString, $minutesMatch)) {
                $totalSeconds += (int)$minutesMatch[1] * 60; // Convert minutes to seconds
            }

            if (preg_match('/(\d+)\s*sec/', $durationString, $secondsMatch)) {
                $totalSeconds += (int)$secondsMatch[1]; // Add seconds
            }

            // Check if the total duration is less than 10 minutes (600 seconds)
            $isShortDuration = $totalSeconds < 600;
        }

         // Determine the vehicle display value
            $vehicleDisplay = 'N/A';
            if ($item->vehicle) {
                if ($item->vehicle->vehicle_type == 'Trailer') {
                    $vehicleDisplay = $item->vehicle->vehicleDetail->vehicle_nick_name ?? 'No Vehicle ID';
                } else {
                    $vehicleDisplay = $item->vehicle->registrations ?? 'No Registration';
                }
            }

            return [
                                'id' => $item->id,
                'uploaded_date' => $item->uploaded_date,
                'defects_count' => $item->defects_count,
                'vehicle' => $vehicleDisplay, // Updated condition based on type
                'driver_name' => $item->driver ? $item->driver->name : 'N/A', // Ensure this field exists in Driver model
                'defect_status' => is_null($item->defects_count) || $item->defects_count == 0 ? 'Completed' : 'Not-completed',
                'duration' => $item->duration,
                 'is_short_duration' => $isShortDuration ? 1 : 0, // Add the new parameter
            ];
        });

        // Return the paginated or all data as JSON
        return response()->json([
            'status' => 1,
            'data' => $result,
        ], 200);
    }

    public function getpreviewwalkaroundDetails(Request $request)
    {
        // Validate the input to ensure 'id' is provided and is a valid integer
        $request->validate([
            'id' => 'required|integer|exists:work_around_stores,id', // Validate that 'id' exists in WorkAroundStore table
        ]);

        // Get the logged-in driver user
        $driverUser = Auth::user();

        // Ensure the driver user is logged in
        if (!$driverUser) {
            return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
        }

        // Fetch the corresponding driver record using the DriverUser's ID
        $driver = Driver::where('id', $driverUser->driver_id)->first();

        if (!$driver) {
            return response()->json(['status' => 0, 'error' => 'Driver not found'], 404);
        }

        // Get the ID from the request
        $id = $request->input('id');

        // Fetch the specific WorkAroundStore record
        $workAroundStore = \App\Models\WorkAroundStore::where('id', $id)
            ->where('driver_id', $driver->id) // Ensure the record belongs to the logged-in driver
            ->with(['vehicle.vehicleDetail', 'driver', 'types', 'workAroundQuestionAnswers']) // Eager load related models including workAroundQuestionAnswers
            ->first();

        // Check if the WorkAroundStore record exists
        if (!$workAroundStore) {
            return response()->json(['status' => 0, 'error' => 'WorkAroundStore record not found'], 404);
        }

$vehicleDetails = 'N/A';
    if ($workAroundStore->vehicle) {
        $make = $workAroundStore->vehicle->vehicleDetail->make ?? 'Unknown Make';

        if ($workAroundStore->vehicle->vehicle_type == 'Trailer') {
            $vehicleId = $workAroundStore->vehicle->vehicleDetail->vehicle_nick_name ?? 'No Vehicle ID';
            $vehicleDetails = "{$vehicleId} ({$make})";
        } else {
            $registration = $workAroundStore->vehicle->registrations ?? 'No Registration';
            $vehicleDetails = "{$registration} ({$make})";
        }
    }

        // Initialize workAroundQuestionAnswers if it's null
        $workAroundQuestionAnswers = $workAroundStore->workAroundQuestionAnswers ?? collect();

        // Separate question answers into two parts
        $questionsWithNullValues = $workAroundQuestionAnswers->filter(function ($answer) {
            return is_null($answer->image) && is_null($answer->reason);
        });

        $defectsWithValues = $workAroundQuestionAnswers->filter(function ($answer) {
            return !is_null($answer->image) || !is_null($answer->reason);
        });

        // Encode the driver ID
        $encodedId = base64_encode($workAroundStore->id);

        // Define the base URL for driver details view
        $baseUrl = url('/walkAround/pdf/'.$encodedId);


        // Format the details for response
        $details = [
            'walkaround_id' => $workAroundStore->id,
            'pdf' => $baseUrl,
            'company' => $workAroundStore->types->name ?? 'N/A', // Ensure 'name' field exists in Types model
            'vehicle' => $vehicleDetails,
            'driver_name' => $workAroundStore->driver ? $workAroundStore->driver->name : 'N/A', // Ensure this field exists in Driver model
            'profile' => $workAroundStore->profile->name ?? 'N/A', // Ensure 'name' field exists in Profile model
            'date' => $workAroundStore->uploaded_date,
            'fuel_type' => $workAroundStore->fuel_level,
            'odometer' => $workAroundStore->speedo_odometer,
            'adblue_level' => $workAroundStore->adblue_level,
            'rectified' => $workAroundStore->rectified ?? 0,
            'defects' => $defectsWithValues->count(), // Add count of defects with values

            'non_defects_question' => $questionsWithNullValues->values()->map(function ($item, $index) {
                return [
                    'index' => $index + 1, // Add index starting from 1
                    'question' => $item->question->name, // Assuming 'name' is the correct field
                ];
            })->toArray(), // Convert to array
           'defects_question' => $defectsWithValues->map(function ($item, $index) {
            // Determine vehicle details based on vehicle type
            if ($item->workAroundStore->vehicle->vehicle_type === 'Trailer') {
                $vehicleInfo = $item->workAroundStore->vehicle->vehicleDetail->vehicle_nick_name ?? 'No Vehicle ID';
            } else {
                $vehicleInfo = $item->workAroundStore->vehicle->registrations ?? 'No Registration';
            }

            return [
                'index' => $index + 1, // Add index starting from 1
                'question' => $item->question->name,
                'image' => url('storage/' . $item->image), // Generate full URL for the image
                'vehicle' => $vehicleInfo, // Display vehicle based on type
            ];
            })->values()->toArray(), // Convert to array and reset keys

        ];

        // Return the details as JSON
        return response()->json([
            'status' => 1,
            'data' => $details,
        ], 200);
    }

public function getvehiclelist(Request $request)
{
    // Get the logged-in driver user
    $driverUser = Auth::user();

    // Ensure the driver user is logged in
    if (!$driverUser) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Fetch the corresponding driver record using the DriverUser's ID
    $driver = Driver::where('id', $driverUser->driver_id)->first();

    if (!$driver) {
        return response()->json(['error' => 'Driver not found'], 404);
    }

    // Fetch the company associated with the driver
    $company = $driver->companyDetails;

    if (!$company) {
        return response()->json(['error' => 'Company not found'], 404);
    }

    // Fetch the vehicles associated with the company and filter by vehicleDetails.depot_id == driver.depot_id
$vehicles = \App\Models\Vehicles::where('companyName', $company->id)
    ->whereHas('vehicleDetail', function ($query) use ($driver) {
        $query->where('depot_id', $driver->depot_id)
              ->where(function ($q) {
                  $q->whereNull('vehicle_status')
                    ->orWhere('vehicle_status', '')
                    ->orWhere('vehicle_status', 'not like', 'Archive%');
              });
    })
    ->with(['vehicleDetail' => function ($query) use ($driver) {
        $query->where('depot_id', $driver->depot_id)
              ->where(function ($q) {
                  $q->whereNull('vehicle_status')
                    ->orWhere('vehicle_status', '')
                    ->orWhere('vehicle_status', 'not like', 'Archive%');
              });
    }])
    ->get(['id', 'registrations', 'vehicle_type', 'make']);


    // Filter out any vehicles whose vehicleDetail is null (i.e., no matching depot)
    $filteredVehicles = $vehicles->filter(function ($vehicle) {
        return $vehicle->vehicleDetail !== null;
    });

    // Transform the vehicles to include details
    $vehicleList = $filteredVehicles->map(function ($vehicle) {
        $vehicleId = $vehicle->vehicleDetail->vehicle_nick_name ?? 'No Vehicle ID';
        return [
            'id' => $vehicle->id,
            'registration' => ($vehicle->vehicle_type == 'Trailer')
                ? $vehicleId
                : ($vehicle->registrations ?? 'No Registration'),
            'make' => $vehicle->make ?? 'Unknown',
            'vehicle_registration_number' => $vehicle->registrations ?? 'No Registration',
            'vehicle_type' => $vehicle->vehicle_type ?? 'Null',
        ];
    });

    return response()->json([
        'status' => 1,
        'company_name' => $company->name,
        'vehicles' => $vehicleList->values(), // Reset keys in case of filtering
    ], 200);
}


    public function getDriverVehicleDetailsData(Request $request)
{
    // Validate the incoming request data
    $request->validate([
        'vehicleId' => 'required|integer|exists:vehicles,id',
    ]);

    // Get the vehicleId from the request
    $vehicleId = $request->input('vehicleId');

         // Get the logged-in driver user
         $driverUser = Auth::user();

         // Ensure the driver user is logged in
         if (!$driverUser) {
             return response()->json(['error' => 'Unauthorized'], 401);
         }

         // Fetch the corresponding driver record using the DriverUser's ID
         $driver = Driver::where('id', $driverUser->driver_id)->first();

         if (!$driver) {
             return response()->json(['error' => 'Driver not found'], 404);
         }

         // Fetch the company associated with the driver
         $company = $driver->companyDetails;


    if (! $company) {
        return response()->json(['status' => 0, 'error' => 'Company not found'], 404);
    }

    // Fetch a specific vehicle by its ID for the given company, including related vehicle details
    $vehicle = \App\Models\Vehicles::where('companyName', $company->id)
        ->where('id', $vehicleId)
        ->with('details') // Eager load the VehicleDetails
        ->first();

    if (! $vehicle) {
        return response()->json(['status' => 0, 'error' => 'Vehicle not found'], 404);
    }

    // Get the vehicleDetails ID
    $vehicleDetailsId = $vehicle->details->id;

    // Fetch the count of Contract_attachment records associated with the vehicleDetails ID
    $contractAttachmentCount = Contract_attachment::where('contract_id', $vehicleDetailsId)->count();

    // Fetch Contract_attachment records and generate URLs
    $attachments = Contract_attachment::where('contract_id', $vehicleDetailsId)
        ->get()
        ->map(function ($attachment) {
            // Assuming the file is stored in the 'public' disk
            return [
                'file_url' => Storage::url('image_attechment/' . $attachment->files),
            ];
        });

    // Helper function to handle `"-"` values
    $convertDashToNull = function ($value) {
        return $value === '-' ? null : $value;
    };

    // Fetch related VehiclesAnnualTest records
   $annualTests = \App\Models\VehiclesAnnualTest::where('vehicle_id', $vehicleId)
    ->with('defects') // Ensure the 'defects' relationship is eager loaded
     ->orderBy('completed_date', 'desc')
    ->get()
    ->map(function ($test) use ($convertDashToNull) {
        // Group defects by type
        $groupedDefects = $test->defects->groupBy(function ($defect) {
            return strtoupper($defect->type);
        });

        // Map defect types to display titles
        $typeTitleMap = [
            'MAJOR' => 'Repair immediately (major defects)',
            'FAIL' => 'Repair immediately (major defects)',
            'PRS' => 'Repair immediately (major defects)',
            'ADVISORY' => 'Monitor and repair if necessary (advisories)',
            'DANGEROUS' => 'Do not drive until repaired (dangerous defects)',
            'MINOR' => 'Repair as soon as possible (minor defects)',
        ];

        // Format grouped defects
        $defectsFormatted = collect($typeTitleMap)->map(function ($title, $type) use ($groupedDefects) {
            if (!isset($groupedDefects[$type])) {
                return null;
            }

            return [
                'defect_title' => $title,
                'defect_value' => $groupedDefects[$type]->pluck('text')->implode(' $ '),
            ];
        })->filter()->values();

        return [
            'test_date' => $convertDashToNull($test->completed_date) ? Carbon::parse($test->completed_date)->format('d/m/Y') : null,
            'test_result' => $test->test_result,

            'test_certificate_number' => $test->mot_test_number,
            'test_expiry_date' => $convertDashToNull($test->expiry_date) ? Carbon::parse($test->expiry_date)->format('d/m/Y') : null,
             'mileage' => $test->odometer_value . ' ' . ($test->odometer_unit ?? ''),
            'test_location' => $test->location,
            'defect' => $defectsFormatted,
        ];
    });


    // Format dates or keep as null
    $formattedDates = [
        'registration_date' => $convertDashToNull($vehicle->registration_date) ? Carbon::parse($vehicle->registration_date)->format('d/m/Y') : null,
        'annual_test_expiry_date' => $convertDashToNull($vehicle->annual_test_expiry_date) ? Carbon::parse($vehicle->annual_test_expiry_date)->format('d/m/Y') : null,
        'taxDueDate' => $convertDashToNull($vehicle->details->taxDueDate) ? Carbon::parse($vehicle->details->taxDueDate)->format('d/m/Y') : null,
        'motExpiryDate' => $convertDashToNull($vehicle->details->motExpiryDate) ? Carbon::parse($vehicle->details->motExpiryDate)->format('d/m/Y') : null,
        'dateOfLastV5CIssued' => $convertDashToNull($vehicle->details->dateOfLastV5CIssued) ? Carbon::parse($vehicle->details->dateOfLastV5CIssued)->format('d/m/Y') : null,
        'PMI_due' => $convertDashToNull($vehicle->details->PMI_due) ? Carbon::parse($vehicle->details->PMI_due)->format('d/m/Y') : null,
        'brake_test_due' => $convertDashToNull($vehicle->details->brake_test_due) ? Carbon::parse($vehicle->details->brake_test_due)->format('d/m/Y') : null,
        'tacho_calibration' => $convertDashToNull($vehicle->details->tacho_calibration) ? Carbon::parse($vehicle->details->tacho_calibration)->format('d/m/Y') : null,
        'date_of_inspection' => $convertDashToNull($vehicle->details->date_of_inspection) ? Carbon::parse($vehicle->details->date_of_inspection)->format('d/m/Y') : null,
        'dvs_pss_permit_expiry' => $convertDashToNull($vehicle->details->dvs_pss_permit_expiry) ? Carbon::parse($vehicle->details->dvs_pss_permit_expiry)->format('d/m/Y') : null,
    ];
$insuranceType = json_decode($vehicle->details->insurance_type, true);

    // Format VehicleDetails if available
    $vehicleDetails = $vehicle->details ? [
        'vehicle_attachment_count' => $contractAttachmentCount,
        'attachments' => $attachments, // Include attachments data
        'registration_number' => $vehicle->details->registrationNumber,
        'company_name' => $company->name,
        'make' => ($vehicle->make === "Details Not Provide By DVLA" || $vehicle->make === null) ? 'N/A' : $vehicle->make,
        'tax_status' => ($vehicle->details->taxStatus === "Details Not Provide By DVLA" || $vehicle->details->taxStatus === null) ? 'N/A' : $vehicle->details->taxStatus,
        'taxduedate' => $formattedDates['taxDueDate'] ?? 'N/A',
        'mot_status' => ($vehicle->details->motStatus === "Details Not Provide By DVLA" || $vehicle->details->motStatus === null) ? 'N/A' : $vehicle->details->motStatus,
        'yearofmanufacture' => ($vehicle->details->yearOfManufacture === "Details Not Provide By DVLA" || $vehicle->details->yearOfManufacture === null) ? 'N/A' : $vehicle->details->yearOfManufacture,
        'enginecapacity' => ($vehicle->details->engineCapacity === "Details Not Provide By DVLA" || $vehicle->details->engineCapacity === null) ? 'N/A' : $vehicle->details->engineCapacity,
        'co2emissions' => ($vehicle->details->co2Emissions === "Details Not Provide By DVLA" || $vehicle->details->co2Emissions === null) ? 'N/A' : $vehicle->details->co2Emissions,
'insurance_type' => is_array($insuranceType) ? implode(',', $insuranceType) : ($insuranceType ?? 'N/A'),
        'insurance' => $vehicle->details->insurance,
        'pmi_due' => $formattedDates['PMI_due'] ?? 'N/A',
        'brake_test_due' => $formattedDates['brake_test_due'] ?? 'N/A',
        'odometer_reading' => $vehicle->details->odometer_reading ?? 'N/A',
        'fueltype' => ($vehicle->details->fuelType === "Details Not Provide By DVLA" || $vehicle->details->fuelType === null) ? 'N/A' : $vehicle->details->fuelType,
        'markedforexport' => ($vehicle->details->markedForExport === "Details Not Provide By DVLA" || $vehicle->details->markedForExport === null) ? 'N/A' : $vehicle->details->markedForExport,
        'colour' => ($vehicle->details->colour === "Details Not Provide By DVLA" || $vehicle->details->colour === null) ? 'N/A' : $vehicle->details->colour,
        'typeapproval' => ($vehicle->details->typeApproval === "Details Not Provide By DVLA" || $vehicle->details->typeApproval === null) ? 'N/A' : $vehicle->details->typeApproval,
        'revenueweight' => ($vehicle->details->revenueWeight === "Details Not Provide By DVLA" || $vehicle->details->revenueWeight === null) ? 'N/A' : $vehicle->details->revenueWeight,
        'eurostatus' => ($vehicle->details->euroStatus === "Details Not Provide By DVLA" || $vehicle->details->euroStatus === null) ? 'N/A' : $vehicle->details->euroStatus,
        'dateoflastv5cissued' => $formattedDates['dateOfLastV5CIssued'] ?? 'N/A',
        'motexpirydate' => $formattedDates['motExpiryDate'] ?? 'N/A',
        'wheelplan' => ($vehicle->details->wheelplan === "Details Not Provide By DVLA" || $vehicle->details->wheelplan === null) ? 'N/A' : $vehicle->details->wheelplan,
        'monthoffirstregistration' => ($vehicle->details->monthOfFirstRegistration === "Details Not Provide By DVLA" || $vehicle->details->monthOfFirstRegistration === null) ? 'N/A' : $vehicle->details->monthOfFirstRegistration,
        'tacho_calibration' => $formattedDates['tacho_calibration'] ?? 'N/A',
        'dvs_pss_permit_expiry' => $formattedDates['dvs_pss_permit_expiry'] ?? 'N/A',
        'date_of_inspection' => $formattedDates['date_of_inspection'] ?? 'N/A',
    ] : null;

    // Return the data as JSON
    return response()->json([
        'status' => 1,
        'vehicle_enquiry' => $vehicleDetails, // Include VehicleDetails data separately
        'vehicle_annual' => [
            'id' => $vehicle->id,
            'registration_number' => $vehicle->registrations,
            'make' => ($vehicle->make === "Details Not Provide By DVLA" || $vehicle->make === null) ? 'N/A' : $vehicle->make,
            'model' => ($vehicle->model === "Details Not Provide By DVLA" || $vehicle->model === null) ? 'N/A' : $vehicle->model,
            'registration_date' => $formattedDates['registration_date'] ?? 'N/A',
            'annual_test_expiry_date' => $formattedDates['annual_test_expiry_date'] ?? 'N/A',
            'annual_tests' => $annualTests, // Include VehiclesAnnualTest data
        ],
    ]);
}

public function getdefectwalkaroundList(Request $request)
{
    // Get the logged-in driver user
    $driverUser = Auth::user();

    // Ensure the driver user is logged in
    if (!$driverUser) {
        return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
    }

    // Fetch the corresponding driver record using the DriverUser's ID
    $driver = Driver::where('id', $driverUser->driver_id)->first();

    if (!$driver) {
        return response()->json(['status' => 0, 'error' => 'Driver not found'], 404);
    }

    // Fetch WorkAroundStore records associated with the driver
    $workAroundStoreIds = \App\Models\WorkAroundStore::where('driver_id', $driver->id) ->where('step', 'done')->pluck('id');

    if ($workAroundStoreIds->isEmpty()) {
        return response()->json(['status' => 1, 'data' => []], 200);
    }

    // Fetch the WorkAroundStore IDs that have defects or rectifications
    $defectWorkAroundStoreIds = \App\Models\WorkAroundQuestionAnswerStore::whereIn('workaround_store_id', $workAroundStoreIds)
        ->where(function ($query) {
            $query->whereNotNull('image')
                  ->orWhereNotNull('reason');
        })
        ->pluck('workaround_store_id')
        ->unique(); // Get unique WorkAroundStore IDs

    if ($defectWorkAroundStoreIds->isEmpty()) {
        return response()->json(['status' => 1, 'data' => [] ], 200);
    }

    // Fetch WorkAroundStore records with the defect/rectified IDs and include vehicle_id
    $workAroundStores = \App\Models\WorkAroundStore::whereIn('id', $defectWorkAroundStoreIds)
        ->with('vehicle') // Eager load the vehicle relationship
         ->orderBy('id', 'desc') // Order by id in descending order
        ->get(['id', 'vehicle_id']); // Select relevant fields



    // Prepare results
    $result = $workAroundStores->map(function ($store) {

        // Fetch total questions for this WorkAroundStore
        $totalQuestions = \App\Models\WorkAroundQuestionAnswerStore::where('workaround_store_id', $store->id)->count();

        // Fetch defect questions for this WorkAroundStore
        $defectQuestionsCount = \App\Models\WorkAroundQuestionAnswerStore::where('workaround_store_id', $store->id)
            ->where(function ($query) {
                $query->whereNotNull('image')
                      ->orWhereNotNull('reason');
            })
            ->count();

        // Fetch rectified questions for this WorkAroundStore
        $rectifiedQuestionsCount = \App\Models\WorkAroundQuestionAnswerStore::where('workaround_store_id', $store->id)
            ->whereNotNull('rectified_date') // Check for non-null rectified_date
            ->count();

        $status = ($defectQuestionsCount === 0) ? 'Rectified' : 'Not-Rectified';


        // Fetch vehicle registration number
$vehicleRegistrationNumber = 'N/A';
        if ($store->vehicle) {
            if ($store->vehicle->vehicle_type == 'Trailer') {
                $vehicleRegistrationNumber = $store->vehicle->vehicleDetail->vehicle_nick_name ?? 'No Vehicle ID';
            } else {
                $vehicleRegistrationNumber = $store->vehicle->registrations ?? 'No Registration';
            }
        }

                $total = $defectQuestionsCount + $rectifiedQuestionsCount;


        return [
            'id' => $store->id,
            'vehicle_id' => $store->vehicle_id ?? 'N/A', // Default to 'N/A' if vehicle_id is null
            'registration_number' => $vehicleRegistrationNumber,
            'total_questions' => $totalQuestions,
            'defect_questions' => $defectQuestionsCount,
            'rectified_questions' => $rectifiedQuestionsCount, // Add rectified questions count
            'status' => $status, // Add the status field
                        'total' =>  $total


        ];
    })->values(); // Use values() to reset the keys and return a proper indexed array

    // Return the result with WorkAroundStore IDs, vehicle_id, registration_number, total questions, defect questions, and rectified questions
    return response()->json([
        'status' => 1,
        'data' => $result,
    ], 200);
}

public function getdefectwalkaroundDetails(Request $request)
{
    // Get the logged-in driver user
    $driverUser = Auth::user();

    // Ensure the driver user is logged in
    if (!$driverUser) {
        return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
    }

    // Fetch the corresponding driver record using the DriverUser's ID
    $driver = Driver::where('id', $driverUser->driver_id)->first();

    if (!$driver) {
        return response()->json(['status' => 0, 'error' => 'Driver not found'], 404);
    }

    // Validate and get the WorkAroundStore ID from the request
    $workAroundStoreId = $request->input('workaround_store_id');

    if (!$workAroundStoreId) {
        return response()->json(['status' => 0, 'error' => 'WorkAroundStore ID is required'], 400);
    }

    // Fetch the WorkAroundStore record with the given ID
    $workAroundStore = \App\Models\WorkAroundStore::where('id', $workAroundStoreId)
        ->where('driver_id', $driver->id)
        ->first();

    if (!$workAroundStore) {
        return response()->json(['status' => 0, 'error' => 'WorkAroundStore not found or unauthorized'], 404);
    }

    // Fetch defect questions and their associated histories
    $questions = \App\Models\WorkAroundQuestionAnswerStore::where('workaround_store_id', $workAroundStoreId)
        ->where(function ($query) {
            $query->whereNotNull('image')
                  ->orWhereNotNull('reason')
                  ->orWhereNotNull('rectified_date'); // Include rectified questions
        })
        ->with(['question', 'defectHistory']) // Eager load defect history
        ->get(['id', 'reason', 'image', 'rectified_date', 'question_id']); // Fetch relevant columns

    // Fetch the uploaded date and vehicle registration number
    $uploadedDate = $workAroundStore->uploaded_date ?? 'N/A'; // Default to 'N/A' if uploaded_date is null
        // $registrationNumber = $workAroundStore->vehicle->registrations ?? 'N/A'; // Default to 'N/A' if registration_number is null
         $registrationNumber = 'N/A';
    if ($workAroundStore->vehicle) {
        if ($workAroundStore->vehicle->vehicle_type == 'Trailer') {
            $registrationNumber = $workAroundStore->vehicle->vehicleDetail->vehicle_nick_name ?? 'No Vehicle ID';
        } else {
            $registrationNumber = $workAroundStore->vehicle->registrations ?? 'No Registration';
        }
    }

    // Prepare result data
    $result = $questions->map(function ($item) use ($uploadedDate, $registrationNumber) {
        // Determine the reason to show
        $finalReason = $item->defectHistory->reason ?? $item->reason;

 // Determine the final image to show
        $finalImage = null; // Default to null

        if ($item->defectHistory) {
            $finalImage = $item->defectHistory->image ? url('storage/' . $item->defectHistory->image) : null;
        }

        if (!$finalImage && $item->image) {
            $finalImage = url('storage/' . $item->image);
        }

        // Determine status (rectified or not)
        $status = $item->rectified_date ? 'Rectified' : 'Not-Rectified';

        return [
            'id' => $item->id,
            'reason' => $finalReason, // Use the final reason from defect history or original
                        'question_id' => $item->question_id,
            'name' => $item->question->name ?? 'N/A', // Question name
            'uploaded_date' => $uploadedDate, // Add uploaded_date
              'registration_number' => $registrationNumber,
            'image' => $finalImage, // Handle image URL
            'status' => $status, // Status based on rectified_date
        ];
    })->values(); // Use values() to reset the keys and return a proper indexed array

    // Return the result with defect questions and rectified data
    return response()->json([
        'status' => 1,
        'defect_questions' => $result,
    ], 200);
}

public function getDefectOptions(Request $request)
{
    // Validate and get the WorkAroundQuestion ID from the request
    $questionId = $request->input('question_id');

    if (!$questionId) {
        return response()->json(['status' => 0, 'error' => 'Question ID is required'], 400);
    }

    // Fetch the WorkAroundQuestion record with the given ID
    $question = \App\Models\WorkAroundQuestion::where('id', $questionId)->first();

    if (!$question) {
        return response()->json(['status' => 0, 'error' => 'WorkAroundQuestion not found'], 404);
    }

    // Decode the defect options JSON string into an array
    $defectOptions = json_decode($question->defect_options, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return response()->json(['status' => 0, 'error' => 'Invalid JSON format in defect options'], 500);
    }

    // Transform the defect options into the desired format
    $defectOptions = collect($defectOptions)->map(function ($option) {
        return ['name' => $option];
    });

    // Return the defect options as an array of objects with name property
    return response()->json([
        'status' => 1,
        'defect_options' => $defectOptions,
    ], 200);
}


public function storedefectwalkaroundRectifield(Request $request)
{
    // Get the logged-in driver user
    $driverUser = Auth::user();

    // Ensure the driver user is logged in
    if (!$driverUser) {
        return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
    }

    // Fetch the corresponding driver record using the DriverUser's ID
    $driver = Driver::where('id', $driverUser->driver_id)->first();

    if (!$driver) {
        return response()->json(['status' => 0, 'error' => 'Driver not found'], 404);
    }

    // Validate the incoming request data
    $validated = $request->validate([
        'workaround_question_answer_id' => 'required|exists:work_around_question_answer_stores,id',
        'problem_type' => 'required|string',
        'problem_solution' => 'required|string',
        'third_party' => 'required|string',
        'defect_options' => 'required|string',
        'rectified_date' => 'required|date_format:d/m/Y H:i:s',
                'rectified_signature'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5000',
        'reason' => 'nullable|string', // Optional reason field
        'image' => 'nullable|string',  // Optional image field
    ]);

    // Fetch the WorkAroundQuestionAnswerStore record with the given ID
    $workAroundQAStore = \App\Models\WorkAroundQuestionAnswerStore::find($validated['workaround_question_answer_id']);

    if (!$workAroundQAStore) {
        return response()->json(['status' => 0, 'error' => 'WorkAroundQuestionAnswerStore record not found'], 404);
    }

     // Handle rectified_signature file upload if provided
  if ($request->hasFile('rectified_signature')) {
    // Store the signature in the desired directory
    $rectifiedSignaturePath = $request->file('rectified_signature')->store('walkaround_rectified_signatures', 'local');
} else {
    $rectifiedSignaturePath = null; // If not provided, keep null
}

    // Update the WorkAroundQuestionAnswerStore record with the provided data
    $workAroundQAStore->update([
        'problem_type' => $validated['problem_type'],
        'problem_solution' => $validated['problem_solution'],
        'third_party' => $validated['third_party'],
        'defect_options' => $validated['defect_options'],
                'rectified_signature' => $rectifiedSignaturePath,
                        'rectified_username' => $driver->name,
                        'rectified_date' => $validated['rectified_date'],

        // 'reason' and 'image' fields are not updated here, so they remain unchanged initially
    ]);

    // Fetch the corresponding WorkAroundStore using work_around_stores_id from WorkAroundQuestionAnswerStore
    $workAroundStore = \App\Models\WorkAroundStore::where('id', $workAroundQAStore->workaround_store_id)->first();

    if ($workAroundStore) {
        // Only update rectified if defect_count is greater than 0
        if ($workAroundStore->defects_count > 0) {
            $newDefectValue = max(0, $workAroundStore->defects_count - 1); // Ensure defect doesn't go below 0
            $newRectifiedValue = $workAroundStore->rectified + 1;

            // Update the WorkAroundStore record with the new values
            $workAroundStore->update([
                'defects_count' => $newDefectValue,
                'rectified' => $newRectifiedValue,
            ]);
        }
    } else {
        return response()->json(['status' => 0, 'error' => 'WorkAroundStore record not found'], 404);
    }

    // Insert the 'reason' and 'image' values into the WorkAroundHistory table
    \App\Models\WorkAroundDefectsHistories::create([
        'workaround_question_answer_id' => $workAroundQAStore->id,
        'reason' => $workAroundQAStore->reason, // Copy the original 'reason'
        'image' => $workAroundQAStore->image,   // Copy the original 'image'
    ]);

    // Update the WorkAroundQuestionAnswerStore record to set 'reason' and 'image' to null
    $workAroundQAStore->update([
        'reason' => null,
        'image' => null,
    ]);

    // Return a success response
    return response()->json([
        'status' => 1,
        'message' => 'Defect details updated, rectified, and history logged successfully',
    ], 200);
}

public function getWalkaroundVehicleContactbookDefectsHandbookTrainingcount(Request $request)
{
    // Get the logged-in driver user
    $driverUser = Auth::user();

    // Ensure the driver user is logged in
    if (!$driverUser) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Fetch the corresponding driver record using the DriverUser's ID
    $driver = Driver::where('id', $driverUser->driver_id)->first();

    if (!$driver) {
        return response()->json(['error' => 'Driver not found'], 404);
    }

    // Fetch the company associated with the driver
    $company = $driver->companyDetails;

    if (!$company) {
        return response()->json(['error' => 'Company not found'], 404);
    }

    // Fetch the access level associated with the company
    $accessLevel = \App\Models\AppAccessLevel::where('company_id', $company->id)->first();

    // Define all possible driver access types
    $allDriverAccessTypes = [
        "Walkaround", "Vehicle", "Contact", "Handbook", "Training"
    ];

    // Prepare the driver access data as true/false
    $driverAccess = [];

    // If access level exists, populate the access data
    if ($accessLevel && is_array($accessLevel->driver_access)) {
        $driverAccessArray = $accessLevel->driver_access ?? [];

        foreach ($allDriverAccessTypes as $access) {
            $driverAccess[$access] = in_array($access, $driverAccessArray);
        }
        $allStatus = true; // All access types are based on driver_access
    } else {
        // If no access level exists or driver_access is null, set all values to false
        foreach ($allDriverAccessTypes as $access) {
            $driverAccess[$access] = false;
        }
        $allStatus = false; // All access types are false when no valid access
    }

    // Add 'all_status' within the 'driver_access' response
    $driverAccess['all_status'] = $allStatus;

    // Count the number of WorkAroundStore records related to the company and driver
    $workAroundStoreIds = \App\Models\WorkAroundStore::where('company_id', $company->id)
        ->where('driver_id', $driver->id)
        ->pluck('id'); // Get IDs of WorkAroundStore records

    $workAroundStoreCount = $workAroundStoreIds->count();

    // Count the number of vehicles associated with the company
$vehiclesCount = \App\Models\Vehicles::where('companyName', $company->id)
    ->whereHas('vehicleDetails', function ($query) use ($driver) {
        $query->where('depot_id', $driver->depot_id)
              ->where(function ($q) {
                  $q->whereNull('vehicle_status')
                    ->orWhere('vehicle_status', '')
                    ->orWhere('vehicle_status', 'not like', 'Archive%');
              });
    })
    ->count();


    // Count the number of contacts in the contact book associated with the company
    $contactbookCount = \App\Models\WorkAroundContact::where('company_id', $company->id)->count();

    // Fetch the WorkAroundStore IDs that have defects
    $defectWorkAroundStoreIds = \App\Models\WorkAroundQuestionAnswerStore::whereIn('workaround_store_id', $workAroundStoreIds)
        ->where(function ($query) {
            $query->whereNotNull('image')
                  ->orWhereNotNull('reason');
        })
        ->pluck('workaround_store_id')
        ->unique(); // Get unique WorkAroundStore IDs

    // Fetch WorkAroundStore records with the defect IDs and include vehicle_id
    $workAroundStores = \App\Models\WorkAroundStore::whereIn('id', $defectWorkAroundStoreIds)
        ->with('vehicle') // Eager load the vehicle relationship
        ->get(['id', 'vehicle_id']); // Select relevant fields

    $defectWorkAroundStore = $workAroundStores->count();

    // Fetch all policy assignments related to the driver's ID, including all statuses and versions
    $policyAssignments = \App\Models\PolicyAssignment::where('driver_id', $driver->id)
                                                     ->select('id','policy_type', 'policy_id')
                                                     ->get();

    // Prepare an array to hold the policy data with names and links
    $policiesWithName = [];

    // Iterate over each policy assignment to fetch the corresponding policy name and description
    foreach ($policyAssignments as $policy) {
        // Add the policy assignment data with the name and content link to the array
        $policiesWithName[] = [
            'id' => $policy->policy_id,
        ];
    }

    // Remove duplicates based on the 'id' field
    $uniquePolicies = collect($policiesWithName)->unique('id')->values();

    // Count policies for each type
    $policyCount = $uniquePolicies->count();

    // Count the number of training assignments related to the driver
    $trainingCount = \App\Models\TrainingDriverAssign::where('driver_id', $driver->id)->count();

    // Return the company name, vehicle registration numbers, and counts as JSON
    return response()->json([
        'status' => 1,
        'companyName' => $company->name, // Adjust field name based on your Company model
        'walkaround_count' => $workAroundStoreCount,
        'vehicles' => $vehiclesCount,
        'contactbook' => $contactbookCount,
        'defect_questions_count' => $defectWorkAroundStore,
        'handbook_count' => $policyCount, // Add policy count to the response
        'training_count' => $trainingCount,
        'consent_form' => $driver->consent_form_status,
        'lc_check' => $company->lc_check_status,
        'depot_name' => $driver->depot->name ?? null, // Added depot_id
        'depot_access_status' => $driver->depot_access_status === 'Yes',

        'driver_access' => $driverAccess, // Include driver access with 'all_status' as part of the response

    ], 200);
}


public function getLatestdefectwalkaroundDetails(Request $request)
{
    // Get the logged-in driver user
    $driverUser = Auth::user();

    // Ensure the driver user is logged in
    if (!$driverUser) {
        return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
    }

    // Fetch the corresponding driver record using the DriverUser's ID
    $driver = Driver::where('id', $driverUser->driver_id)->first();

    if (!$driver) {
        return response()->json(['status' => 0, 'error' => 'Driver not found'], 404);
    }

    // Validate and get the vehicle_id from the request
    $vehicleId = $request->input('vehicle_id');

    if (!$vehicleId) {
        return response()->json(['status' => 0, 'error' => 'Vehicle ID is required'], 400);
    }

    // Fetch the latest WorkAroundStore record for the driver with the given vehicle_id
    $workAroundStore = \App\Models\WorkAroundStore::where('driver_id', $driver->id)
        ->where('vehicle_id', $vehicleId) // Add vehicle_id filter
        ->orderBy('created_at', 'desc') // Assuming you want the latest based on creation date
        ->first();

    if (!$workAroundStore) {
        return response()->json(['status' => 0, 'error' => 'No WorkAroundStore found for this driver and vehicle'], 404);
    }

    // Fetch details from WorkAroundQuestionAnswerStore related to the WorkAroundStore ID
    $defectQuestions = \App\Models\WorkAroundQuestionAnswerStore::where('workaround_store_id', $workAroundStore->id)
        ->where(function ($query) {
            $query->whereNotNull('image')
                  ->orWhereNotNull('reason');
        })
        ->with('question') // Eager load the WorkAroundQuestion relationship
        ->get(['id', 'reason', 'image', 'question_id']); // Select relevant fields

    // Fetch the uploaded date from WorkAroundStore
    $uploadedDate = $workAroundStore->uploaded_date ?? 'N/A'; // Default to 'N/A' if uploaded_date is null
    $registrationNumber = $workAroundStore->vehicle->registrations ?? 'N/A'; // Default to 'N/A' if registration_number is null

    // Prepare result data with name and uploaded_date for each defect question
    $result = $defectQuestions->map(function ($item) use ($uploadedDate, $registrationNumber) {
        return [
            'id' => $item->id,
            'reason' => $item->reason,
            'question_id' => $item->question_id,
            'name' => $item->question->name ?? 'N/A', // Add name from WorkAroundQuestion
            'uploaded_date' => $uploadedDate, // Add uploaded_date to each defect question
            'registration_number' => $registrationNumber,
        'image' => $item->image ? url('storage/' . $item->image) : null, // Use a ternary to check for null
        ];
    })->values(); // Use values() to reset the keys and return a proper indexed array

    // Return the result with defect questions including name and uploaded_date
    return response()->json([
        'status' => 1,
        'defect_questions' => $result,
    ], 200);
}

public function getDriverNotification(Request $request)
{
   // Get the logged-in driver user
   $driverUser = Auth::user();

   // Ensure the driver user is logged in
   if (!$driverUser) {
       return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
   }

   // Fetch the corresponding driver record using the DriverUser's ID
   $driver = Driver::where('id', $driverUser->driver_id)->first();

   if (!$driver) {
       return response()->json(['status' => 0, 'error' => 'Driver not found'], 404);
   }

    // Fetch the vehicles associated with the company and their details using eager loading
    $notification = \App\Models\DriverNotification::where('driver_id', $driver->id)
    ->orderBy('created_at', 'desc') // or use 'id' if you don't have 'created_at' field
        ->take(30)
                ->get(['id', 'message','title','key']);

    // Transform the vehicles to include the make from vehicleDetails
    $notificationList = $notification->map(function ($notifications) {
        return [
            'id' => $notifications->id,
            'key' => $notifications->key,
            'title' => $notifications->title,
            'message ' => $notifications->message,
        ];
    });

    $notificationcount =  $notificationList->count();

    $driverApp = AppVersion::where('type', 'driver')->first();

    // Return the company name and vehicle registration numbers with details as JSON
    return response()->json([
        'status' => 1,
        // 'company_name' => $company->name,
         'app_version' => optional($driverApp)->version,
        'maintenance' => optional($driverApp)->maintenance_mode ? true : false,
        'total_notification' => $notificationcount,
        'notification' => $notificationList
    ], 200);
}

public function deleteDriverNotification(Request $request)
{
    // Validate the request to ensure the 'id' is provided
    $request->validate([
        'id' => 'required|integer|exists:driver_notifications,id',
    ]);

    $driverUser = Auth::user();

    // Ensure the driver user is logged in
    if (!$driverUser) {
        return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
    }

    // Find the notification by ID
    $notification = \App\Models\DriverNotification::find($request->id);

    if (!$notification) {
        return response()->json(['status' => 0, 'error' => 'Notification not found'], 404);
    }

    // Fetch the corresponding driver record using the DriverUser's ID
    $driver = Driver::where('id', $driverUser->driver_id)->first();

    if (!$driver || $notification->driver_id !== $driver->id) {
        return response()->json(['status' => 0, 'error' => 'Unauthorized action'], 403);
    }

    // Delete the notification
    $notification->delete();

    // Get the updated notification count for the driver
    $notificationCount = \App\Models\DriverNotification::where('driver_id', $driver->id)->count();

    // Return a success response with the updated notification count
    return response()->json([
        'status' => 1,
        'message' => 'Notification deleted successfully',
        'notification_count' => $notificationCount,
    ], 200);
}


// public function getDriverTrainingAssignments(Request $request)
// {
//     // Get the logged-in driver user
//     $driverUser = Auth::user();

//     // Ensure the driver user is logged in
//     if (!$driverUser) {
//         return response()->json(['error' => 'Unauthorized'], 401);
//     }

//     // Fetch the corresponding driver record using the DriverUser's ID
//     $driver = Driver::where('id', $driverUser->driver_id)->first();

//     if (!$driver) {
//         return response()->json(['status' => 0,'error' => 'Driver not found'], 404);
//     }

//     // Fetch all training assignments for the driver
//     $trainingAssignments = \App\Models\TrainingDriverAssign::where('driver_id', $driver->id)
//         ->with(['training', 'training.trainingType', 'training.trainingCourse']) // Eager load relationships
//         ->get();

//     // Prepare the response data
//     $trainingData = [];

//     foreach ($trainingAssignments as $assignment) {
//         // Check if the training exists before trying to access its properties
//         $training = $assignment->training;

//         if ($training) {
//             $trainingData[] = [
//                 'id' => $assignment->id,
//                 'training_id' => $assignment->training_id,
//                 'training_type' => $training->trainingType->name ?? null, // Assuming 'name' is the field for training type
//                 'training_course' => $training->trainingCourse->name ?? null, // Assuming 'name' is the field for training course
//                 'training_status' => $assignment->status, // Status of the training assignment
//                 'status' => $training->status ?? null, // Status of the training
//                 'from_date' => $training->from_date ? Carbon::parse($training->from_date)->format('d/m/Y') : null, // Convert if exists
//                 'to_date' => $training->to_date ? Carbon::parse($training->to_date)->format('d/m/Y') : null, // Convert if exists
//                 'from_time' => $training->from_time ? Carbon::parse($training->from_time)->format('g:i A') : null, // Convert to AM/PM format
//                 'to_time' => $training->to_time ? Carbon::parse($training->to_time)->format('g:i A') : null, // Convert to AM/PM format
//             ];
//         }
//     }

//     // Return the training assignments as JSON
//     return response()->json([
//         'status' => 1,
//         'training' => $trainingData,
//     ], 200);
// }

public function getDriverTrainingAssignments(Request $request)
{
    // Get the logged-in driver user
    $driverUser = Auth::user();

    // Ensure the driver user is logged in
    if (!$driverUser) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Fetch the corresponding driver record using the DriverUser's ID
    $driver = Driver::where('id', $driverUser->driver_id)->first();

    if (!$driver) {
        return response()->json(['status' => 0, 'error' => 'Driver not found'], 404);
    }

    // Retrieve filter parameters from the request
    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');

    // Convert input dates from dd/mm/yyyy to yyyy-mm-dd
    if ($fromDate) {
        $fromDate = Carbon::createFromFormat('d/m/Y', $fromDate)->format('Y-m-d');
    }
    if ($toDate) {
        $toDate = Carbon::createFromFormat('d/m/Y', $toDate)->format('Y-m-d');
    }

        // Validate if start_date is greater than end_date
    if ($fromDate && $toDate && Carbon::parse($fromDate)->gt(Carbon::parse($toDate))) {
        return response()->json(['status' => 0, 'error' => 'Start date cannot be greater than end date'], 400);
    }

    // Fetch all training assignments for the driver
    $query = \App\Models\TrainingDriverAssign::where('driver_id', $driver->id)
        ->with(['training', 'training.trainingType', 'training.trainingCourse']);

    // Apply date filters if provided
    if ($fromDate && $toDate) {
        $query->whereHas('training', function ($q) use ($fromDate, $toDate) {
            $q->whereDate('from_date', '>=', $fromDate)
              ->whereDate('to_date', '<=', $toDate);
        });
    }

    $trainingAssignments = $query->get();

    // Prepare the response data
    $trainingData = [];

    foreach ($trainingAssignments as $assignment) {
        $training = $assignment->training;

        if ($training) {
            $trainingData[] = [
                'id' => $assignment->id,
                'training_id' => $assignment->training_id,
                'training_type' => $training->trainingType->name ?? null,
                'training_course' => $training->trainingCourse->name ?? null,
                'training_status' => $assignment->status,
                'status' => $training->status ?? null,
                'from_date' => $training->from_date ? Carbon::parse($training->from_date)->format('d/m/Y') : null,
                'to_date' => $training->to_date ? Carbon::parse($training->to_date)->format('d/m/Y') : null,
                'from_time' => $training->from_time ? Carbon::parse($training->from_time)->format('g:i A') : null,
                'to_time' => $training->to_time ? Carbon::parse($training->to_time)->format('g:i A') : null,
            ];
        }
    }

    // Return the training assignments as JSON
    return response()->json([
        'status' => 1,
        'training' => $trainingData,
    ], 200);
}

public function updateTrainingAssignment(Request $request)
{
    // Validate incoming request
    $validator = Validator::make($request->all(), [
        'id' => 'required|integer|exists:training_driver_assigns,id', // Validate ID exists in the model
        'reason' => 'nullable|string|max:255',
        'file' => 'nullable|file|max:5000', // Handle file uploads (5MB max)
        'signature' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        'training_status' => 'required|integer|in:0,1', // Expect 0 or 1
    ]);

    // If validation fails, return the errors
    if ($validator->fails()) {
        return response()->json(['status' => 0,'error' => $validator->errors()], 400);
    }

    // Find the TrainingDriverAssign record by ID
    $trainingAssignment = \App\Models\TrainingDriverAssign::find($request->input('id'));

    if (!$trainingAssignment) {
        return response()->json(['status' => 0, 'error' => 'Training assignment not found'], 404);
    }

    // Handle file upload (if provided)
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $filePath = $file->store('training/files', 'local');
        $trainingAssignment->file = $filePath;
    }

    if ($request->hasFile('signature')) {
        $signatureImage = $request->file('signature');
        $signatureImagePath = $signatureImage->store('training/signatures', 'local');
        $trainingAssignment->signature = $signatureImagePath;
    }

    // Update the fields in the TrainingDriverAssign model
    $trainingAssignment->reason = $request->input('reason');

    // Map training status input value
    $trainingAssignment->status = $request->input('training_status') == 1 ? 'Complete' : 'Decline';

    // Save the changes
    $trainingAssignment->save();

    // Return a success response
    return response()->json([
        'status' => 1,
        'message' => 'Training assignment updated successfully',
        'data' => $trainingAssignment
    ], 200);
}
public function getCompanyAccountDetails(Request $request)
{
    // Validate the incoming request
    $request->validate([
        'account_no' => 'required|string', // Adjust validation rules as needed
    ]);

    $account_no = $request->input('account_no');
    $companyDetails = CompanyDetails::where('account_no', $account_no)->first();

    if ($companyDetails) {
        return response()->json([
            'status' => 1,
            'company_id' => $companyDetails->id,
            'companyName' => $companyDetails->name,
            'companyAddress' => $companyDetails->address,
            // Add other fields as needed
        ]);
    }

    return response()->json([
        'status' => 0,
        'message' => 'Company details not found',
    ]);
}


public function consentforms(Request $request)
{
    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'account_no' => 'required|string|max:255',
        'company_id' => 'required|string|max:255',
        'companyName' => 'required|string|max:255',
        'company_address' => 'required|string',
        'account_number' => 'required|string|max:255',
        'reference_number' => 'required|string|max:255',
        'making_an_enquiry' => 'required|string|max:255',
        'making_an_enquiry_details' => 'nullable|string|max:255',
        'reason_for_processing_information' => 'required|string',
        'surname' => 'required|string|max:255',
        'first_name' => 'required|string|max:255',
        'middle_name' => 'nullable|string|max:255',
        'email' => 'required|email|max:255',
        'date_of_birth' => 'required|date',
        'current_address_line1' => 'required|string',
        'current_address_line2' => 'nullable|string|max:255',
        'current_address_line3' => 'nullable|string|max:255',
        'current_address_posttown' => 'nullable|string|max:255',
        'current_address_postcode' => 'required|string',
        'licence_address_line1' => 'required|string|max:255',
        'licence_address_line2' => 'nullable|string|max:255',
        'licence_address_line3' => 'nullable|string|max:255',
        'licence_address_posttown' => 'required|string',
        'licence_address_postcode' => 'required|string|max:255',
        'driver_licence_no' => 'required|string|max:255',
        'signature_image' => 'nullable|image',
        'cpc_information' => 'required|string',
        'tacho_information' => 'required|string',
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        return response()->json(['status' => 0, 'error' => $validator->errors()], 400);
    }

    // // Check for existing records with the same account_no and driver_licence_no
    // $existingForm = \App\Models\DriverConsentForm::where('account_no', $request->input('account_no'))
    //     ->where('driver_licence_no', strtoupper($request->input('driver_licence_no')))
    //     ->first();

    // if ($existingForm) {
    //     return response()->json(['status' => 0, 'error' => 'The driving licence number you entered is already registered. Please provide a different driving licence number.'], 400);
    // }

    // Find the existing Driver
    $driver = \App\Models\Driver::where('companyName', $request->input('company_id'))
        ->where('driver_licence_no', strtoupper($request->input('driver_licence_no')))
        ->first();

    if (!$driver) {
        return response()->json(['status' => 0, 'error' => 'Please note that the driver’s license number you entered is not registered in our system. Kindly check the number and re-enter it, or contact support for your Company.'], 400);
    }

     $companyDetails = \App\Models\CompanyDetails::find($driver->companyName);
    if (!$companyDetails) {
        return response()->json(['status' => 0, 'error' => 'CompanyDetails record not found.'], 400);
    }


// ✅ Coins & payment_type validation logic
if (is_null($companyDetails->payment_type) || is_null($companyDetails->coins)) {
    return response()->json([
        'status' => 0,
        'error' => 'Company payment configuration is incomplete. Please update payment type or coins before proceeding.'
    ], 400);
}

// ✅ Prepaid coins check (only if payment_type & coins are valid)
if ($companyDetails->payment_type === 'Prepaid' && $companyDetails->coins <= 0) {
    return response()->json([
        'status' => 0,
        'error' => 'No API calls available for this company. Please recharge or contact support.'
    ], 400);
}




    // Check if the driver already has a consent form submitted
    if ($driver->consent_form_status === 'Yes') {
        return response()->json(['status' => 0, 'error' => 'This driver has already submitted a consent form.'], 400);
    }

    // Create a new DriverConsentForm record
    $driverConsentForm = new \App\Models\DriverConsentForm();

    // Assign the validated data to the model's attributes
    $driverConsentForm->company_id = $request->input('company_id');
    $driverConsentForm->account_no = $request->input('account_no');
    $driverConsentForm->companyName = strtoupper($request->input('companyName'));
    $driverConsentForm->company_address = strtoupper($request->input('company_address'));
    $driverConsentForm->account_number = strtoupper($request->input('account_number'));
    $driverConsentForm->reference_number = strtoupper($request->input('reference_number'));
    $driverConsentForm->making_an_enquiry = strtoupper($request->input('making_an_enquiry'));
    $driverConsentForm->making_an_enquiry_details = strtoupper($request->input('making_an_enquiry_details'));
    $driverConsentForm->reason_for_processing_information = strtoupper($request->input('reason_for_processing_information'));
    $driverConsentForm->surname = strtoupper($request->input('surname'));
    $driverConsentForm->first_name = strtoupper($request->input('first_name'));
    $driverConsentForm->middle_name = strtoupper($request->input('middle_name'));
    $driverConsentForm->email = $request->input('email');
    $driverConsentForm->date_of_birth = $request->input('date_of_birth');
    $driverConsentForm->current_address_line1 = strtoupper($request->input('current_address_line1'));
    $driverConsentForm->current_address_line2 = strtoupper($request->input('current_address_line2'));
    $driverConsentForm->current_address_line3 = strtoupper($request->input('current_address_line3'));
    $driverConsentForm->current_address_posttown = strtoupper($request->input('current_address_posttown'));
    $driverConsentForm->current_address_postcode = strtoupper($request->input('current_address_postcode'));
    $driverConsentForm->licence_address_line1 = strtoupper($request->input('licence_address_line1'));
    $driverConsentForm->licence_address_line2 = strtoupper($request->input('licence_address_line2'));
    $driverConsentForm->licence_address_line3 = strtoupper($request->input('licence_address_line3'));
    $driverConsentForm->licence_address_posttown = strtoupper($request->input('licence_address_posttown'));
    $driverConsentForm->licence_address_postcode = strtoupper($request->input('licence_address_postcode'));
    $driverConsentForm->driver_licence_no = strtoupper($request->input('driver_licence_no'));
    $driverConsentForm->cpc_information = strtoupper($request->input('cpc_information'));
    $driverConsentForm->tacho_information = strtoupper($request->input('tacho_information'));
    $driverConsentForm->submitted_date = \Carbon\Carbon::now();

    // Handle the signature image file if present
    if ($request->hasFile('signature_image')) {
        $file = $request->file('signature_image');
        $fileName = time() . '.' . $file->getClientOriginalExtension();
        $filePath = 'dvla/signature_image/' . $fileName; // Specify the path relative to the storage directory
        Storage::disk('local')->put($filePath, file_get_contents($file));

        // Update the signature path in the model
        $driverConsentForm->signature_image = $filePath;
    }

    // Save the form data into the database
    $driverConsentForm->save();

    // Update the driver record
    $driver->consent_form_status = 'Yes';

    $driver->save();

    // Log the action
    // \Log::info('Consent form submitted for driver ID: ' . $driver->id);

    // Send confirmation email to the driver
    if ($driverConsentForm->email) { // Check if the email field is filled
        \Mail::to($driverConsentForm->email)->send(new \App\Mail\DriverConsentFormSubmitted($driverConsentForm));
    }

    // Additional code for checking driver status and further processing
    try {
        $driver = Driver::findOrFail($driver->id);

        if ($driver->driver_status !== 'Active') {
             return response()->json(['status' => 0, 'error' => 'Driver is not Active']);
        }

        // Get the driver’s information from the external API
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

            $fullName = trim(($data['driver']['firstNames'] ?? '') . ' ' . ($data['driver']['lastName'] ?? ''));
            $lastName = $data['driver']['lastName'] ?? '';
            $addressLine1 = $data['driver']['address']['unstructuredAddress']['line1'] ?? '';
            $addressLine2 = $data['driver']['address']['unstructuredAddress']['line2'] ?? '';
            $addressLine3 = $data['driver']['address']['unstructuredAddress']['line3'] ?? '';
            $addressLine4 = $data['driver']['address']['unstructuredAddress']['line4'] ?? '';
            $addressLine5 = $data['driver']['address']['unstructuredAddress']['line5'] ?? '';
            $fullAddress = trim($addressLine1 . ' ' . $addressLine2 . ' ' . $addressLine3 . ' ' . $addressLine4 . ' ' . $addressLine5);

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
                        $contentValidUntil = Carbon::now('Europe/London')->addYears(3)->format('d/m/Y');


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
                'consent_valid' => $contentValidUntil,
            ]);

            $dupliacatdriver = \App\Models\DuplicateDriver::create([
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
                    'consent_valid' => $contentValidUntil,
                'created_by' => 'Auto Generator',
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

        // Only generate username and password if they are empty
        if (empty($driverUser->username) || empty($driverUser->password)) {
            $name = preg_replace('/\s+/', '', $fullName); // Remove spaces from name

            // Extract the first 3 characters of the name
            $namePart = strtolower(substr($name, 0, 3));
            $lastNamePart = strtolower(substr($lastName, 0, 3));
            $companyPart = strtolower(substr($companyName, 0, 3));

            list($day, $month) = explode('/', $formattedDriverDob);

            // Create the username
            $username = $lastNamePart . $companyPart . $day . $month;

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
            $driverUser->created_by = 'Auto Generator';

                            \Mail::to($driver->contact_email)->send(new \App\Mail\DriverUser($username, $password, $driver->contact_email));

        }

        $driverUser->save(); // Save DriverUser



            // Find the CompanyDetails record and increment api_call_count
            $companyDetails = CompanyDetails::where('id', $driver->companyName)->first();

            if (!$companyDetails) {
                return redirect()->back()->with('error', 'CompanyDetails record not found.');
            }

             // Decrement coins only if Prepaid, not unlimited (-1), and coins > 0
if (
    $companyDetails->payment_type === 'Prepaid' &&
    $companyDetails->coins !== -1 &&
    $companyDetails->coins > 0
) {
    $companyDetails->coins -= 1;
    $companyDetails->save();
}



            // Increment api_call_count
            $companyDetails->increment('api_call_count');

             // Log the data
                \App\Models\DriverAPILog::create([
                    'companyName' => $driver->companyName,
                    'created' => 'Auto Generator',
                    'last_lc_check' => $latestLcCheck,
                    'licence_no' => $driver->driver_licence_no,
                    'driver_id' => $driver->id,
                ]);


        }
    } catch (\Exception $e) {
        \Log::error('Error retrieving driver information: ' . $e->getMessage());
    }

    return response()->json(['status' => 1, 'message' => 'Consent form submitted successfully.']);
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

    private function calculateAgeDriver($dob)
    {
        $dob = \Carbon\Carbon::parse($dob);

        return $dob->age;
    }

    private function formatDateToDDMMYYYY($date)
    {
        return $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : null;
    }

    private function calculateCheckInterval($penaltyPoints)
    {
        if ($penaltyPoints > 5) {
            return '1 months';
        } else {
            return '3 months';
        }
    }

    public function updateConsentValid()
{
    // Update consent_valid where consent_form_status is 'Yes' and consent_valid is NULL
    $updatedRows = \App\Models\Driver::where('consent_form_status', 'Yes')
        ->whereNull('consent_valid')
        ->update(['consent_valid' => '28/11/2027']);

    if ($updatedRows) {
        return response()->json(['message' => 'Consent valid dates updated successfully.'], 200);
    } else {
        return response()->json(['message' => 'No records updated.'], 200);
    }
}

    public function getDriverCompanyDepots(Request $request)
    {
        // Get the logged-in driver user
        $driverUser = Auth::user();

        // Ensure the driver user is logged in
        if (!$driverUser) {
            return response()->json(['status' => 0,'error' => 'Unauthorized'], 401);
        }

        // Fetch the corresponding driver record using the DriverUser's ID
        $driver = Driver::where('id', $driverUser->driver_id)->first();

        if (!$driver) {
            return response()->json(['status' => 0,'error' => 'Driver not found'], 404);
        }

        // Fetch the company associated with the driver
        $company = $driver->companyDetails;

        if (!$company) {
            return response()->json(['status' => 0,'error' => 'Company not found'], 404);
        }

        // Fetch all depots related to the company with only id and name
        $depots = \App\Models\Depot::where('companyName', $company->id)->get(['id', 'name']);

        return response()->json([
            'status' => 1,
            'companyName' => $company->name,
            'depots' => $depots
        ], 200);
    }
    public function updateDriverDepot(Request $request)
    {
        // Validate the request input
        $validator = Validator::make($request->all(), [
            'depot_id' => 'required|exists:depots,id'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'error' => $validator->errors()
            ], 422);
        }

        // Get the logged-in driver user
        $driverUser = Auth::user();

        // Ensure the driver user is logged in
        if (!$driverUser) {
            return response()->json(['status' => 0, 'error' => 'Unauthorized'], 401);
        }

        // Fetch the corresponding driver record using the DriverUser's ID
        $driver = Driver::where('id', $driverUser->driver_id)->first();

        if (!$driver) {
            return response()->json(['status' => 0, 'error' => 'Driver not found'], 404);
        }

        // Check depot access status
        if ($driver->depot_access_status !== 'Yes') {
            return response()->json(['status' => 0, 'error' => 'Driver does not have permission to update depot'], 403);
        }

        // Fetch the depot details
        $depot = Depot::where('id', $request->depot_id)->first();

        if (!$depot) {
            return response()->json(['status' => 0, 'error' => 'Depot not found'], 404);
        }

        // Fetch the company associated with the driver
        $company = $driver->companyDetails;

        if (!$company) {
            return response()->json(['status' => 0, 'error' => 'Company not found'], 404);
        }

        // Check if the depot belongs to the driver's company
        if ($depot->companyName != $company->id) {
            return response()->json(['status' => 0, 'error' => 'Depot does not belong to the drivers company'], 403);
        }

        // Update the depot_id in the Driver model
        $driver->depot_id = $request->depot_id;
        $driver->save();

        return response()->json([
            'status' => 1,
            'message' => 'Depot updated successfully',
            'depot_id' => $driver->depot_id,
        ], 200);
    }
}
