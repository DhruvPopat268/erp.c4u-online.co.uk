<?php

namespace App\Http\Controllers;

use App\Models\CompanyDetails;
use App\Models\DriverConsentForm;
use Illuminate\Http\Request;

class DriverConsentController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('manage driver')) {

            $loggedInUser = \Auth::user();
        $companyName = $loggedInUser->companyname;

        // request filters
            $selectedCompanyId = $request->input('company_id');
        $selectedDepotIds = (array)$request->input('depot_id');
        $selectedGroupId = $request->input('group_id');

        // login user depot ids
             $depotIds = is_array($loggedInUser->depot_id)
            ? $loggedInUser->depot_id
            : json_decode($loggedInUser->depot_id, true);

        if (!is_array($depotIds)) {
            $depotIds = [$loggedInUser->depot_id];
        }

        // CHANGE: driver group ids
        $driverGroupIds = is_array($loggedInUser->driver_group_id)
            ? $loggedInUser->driver_group_id
            : json_decode($loggedInUser->driver_group_id, true);

        if (!is_array($driverGroupIds)) {
            $driverGroupIds = [$loggedInUser->driver_group_id];
        }

            if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {

                $driverconsent = DriverConsentForm::when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('company_id', $selectedCompanyId);
                })

                ->when(!empty($selectedDepotIds), function ($query) use ($selectedDepotIds) {
                    $query->whereHas('driver', function ($q) use ($selectedDepotIds) {
                        $q->whereIn('depot_id', $selectedDepotIds);
                    });
                })

                ->when($selectedGroupId, function ($query) use ($selectedGroupId) {
                    $query->whereHas('driver', function ($q) use ($selectedGroupId) {
                        $q->where('group_id', $selectedGroupId);
                    });
                })

                    ->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                    })

                    ->orderBy('id', 'desc')->get();

            } else {

                $driverconsent = DriverConsentForm::where('company_id', $companyName)

                ->whereHas('driver', function ($query) use ($depotIds, $driverGroupIds, $companyName, $selectedDepotIds, $selectedGroupId) {

                    $query->where('companyName', $companyName)

              ->whereIn('depot_id', $depotIds)
                        ->whereIn('group_id', $driverGroupIds)

                        ->when(!empty($selectedDepotIds), function ($q) use ($selectedDepotIds) {
                            $q->whereIn('depot_id', $selectedDepotIds);
                        })

                        ->when($selectedGroupId, function ($q) use ($selectedGroupId) {
                            $q->where('group_id', $selectedGroupId);
                        });

    })

                    ->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                    })

                    ->orderBy('id', 'desc')->get();
            }

        $companies = CompanyDetails::orderBy('name', 'asc')
            ->where('company_status', 'Active')
            ->get();

        $depotsQuery = \App\Models\Depot::orderBy('name','asc');

$groupsQuery = \App\Models\Group::orderBy('name','asc');

if (!$loggedInUser->hasRole('company') && !$loggedInUser->hasRole('PTC manager')) {

    // only assigned depots
    $depotsQuery->whereIn('id', $depotIds);

    // only assigned groups
    $groupsQuery->whereIn('id', $driverGroupIds);
}

$depots = $depotsQuery->get();

$groups = $groupsQuery->get();

        return view('driverconsent.index', compact(
            'driverconsent',
            'companies',
            'depots',
            'groups'
        ));

        } else {
            // If the user doesn't have the permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show()
    {

    }

    public function getCompanyDetails($account_no)
    {
        $companyDetails = CompanyDetails::where('account_no', $account_no)->first();

        if ($companyDetails) {
            return response()->json([
                'success' => true,
                'company_id' => $companyDetails->id,
                'companyName' => $companyDetails->name,
                'companyAddress' => $companyDetails->address,
                // Add other fields as needed
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Company details not found',
        ]);
    }

    public function submitted()
    {
        // Fetch all driver consent forms or adjust to your needs
        $driverConsentForms = \App\Models\DriverConsentForm::all();

        return view('driverconsent.submitted', compact('driverConsentForms'));
    }

    public function formcreate()
    {
        $settings = \App\Models\Utility::settings();
        $company_logo = \App\Models\Utility::getValByName('company_logo');
        $imagePath = storage_path('/uploads/logo/'.(isset($company_logo) && ! empty($company_logo) ? $company_logo : '5-logo-dark.png'));

        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $img = 'data:image/png;base64,'.$imageData;
        } else {
            // \Log::error('Image file does not exist: '.$imagePath);
            $img = ''; // Fallback or default image if necessary
        }

        return view('driverconsent.formcreate', compact('img'));
    }

    public function formstore(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
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
            // 'signature_image' => 'nullable|string', // Add this line
            'cpc_information' => 'required|string',
            'tacho_information' => 'required|string',

        ]);

        // Check for existing records with the same account_no and driver_licence_no
        $existingForm = \App\Models\DriverConsentForm::where('account_no', $validatedData['account_no'])
            ->where('driver_licence_no', strtoupper($validatedData['driver_licence_no']))
            ->first();

        if ($existingForm) {
            // Return back with an error message
            return redirect()->back()->withErrors(['form_submission' => 'The driving licence number you entered is already registered. Please provide a different driving licence number.']);
        }

        // Find the existing Driver
        $driver = \App\Models\Driver::where('companyName', $validatedData['company_id'])
            ->where('driver_licence_no', strtoupper($validatedData['driver_licence_no']))
            ->first();

        if (! $driver) {
            return redirect()->back()->withErrors(['form_submission' => 'Please note that the driver’s license number you entered is not registered in our system. Kindly check the number and re-enter it, or contact support for your Company.']);
        }

        // Check if the driver already has a consent form submitted
        if ($driver && $driver->consent_form_status === 'Yes') {
            return redirect()->back()->withErrors(['form_submission' => 'This driver has already submitted a consent form.']);
        }

        // Create a new DriverConsentForm record
        $driverConsentForm = new \App\Models\DriverConsentForm();
        $driverConsentForm->company_id = $validatedData['company_id'];
        $driverConsentForm->account_no = $validatedData['account_no'];
        $driverConsentForm->companyName = strtoupper($validatedData['companyName']);
        $driverConsentForm->company_address = strtoupper($validatedData['company_address']);
        $driverConsentForm->account_number = strtoupper($validatedData['account_number']);
        $driverConsentForm->reference_number = strtoupper($validatedData['reference_number']);
        $driverConsentForm->making_an_enquiry = strtoupper($validatedData['making_an_enquiry']);
        $driverConsentForm->making_an_enquiry_details = strtoupper($validatedData['making_an_enquiry_details']);
        $driverConsentForm->reason_for_processing_information = strtoupper($validatedData['reason_for_processing_information']);
        $driverConsentForm->surname = strtoupper($validatedData['surname']);
        $driverConsentForm->first_name = strtoupper($validatedData['first_name']);
        $driverConsentForm->middle_name = strtoupper($validatedData['middle_name']);
        $driverConsentForm->email = $validatedData['email'];
        $driverConsentForm->date_of_birth = $validatedData['date_of_birth']; // Keep date format as is
        $driverConsentForm->current_address_line1 = strtoupper($validatedData['current_address_line1']);
        $driverConsentForm->current_address_line2 = strtoupper($validatedData['current_address_line2']);
        $driverConsentForm->current_address_line3 = strtoupper($validatedData['current_address_line3']);
        $driverConsentForm->current_address_posttown = strtoupper($validatedData['current_address_posttown']);
        $driverConsentForm->current_address_postcode = strtoupper($validatedData['current_address_postcode']);
        $driverConsentForm->licence_address_line1 = strtoupper($validatedData['licence_address_line1']);
        $driverConsentForm->licence_address_line2 = strtoupper($validatedData['licence_address_line2']);
        $driverConsentForm->licence_address_line3 = strtoupper($validatedData['licence_address_line3']);
        $driverConsentForm->licence_address_posttown = strtoupper($validatedData['licence_address_posttown']);
        $driverConsentForm->licence_address_postcode = strtoupper($validatedData['licence_address_postcode']);
        $driverConsentForm->driver_licence_no = strtoupper($validatedData['driver_licence_no']);
        $driverConsentForm->cpc_information = strtoupper($validatedData['cpc_information']);
        $driverConsentForm->tacho_information = strtoupper($validatedData['tacho_information']);
        $driverConsentForm->submitted_date = \Carbon\Carbon::now();

        // $signatureData = $validatedData['signature_image'];

        // If the signature data is in Base64 format, process it
        // if (preg_match('/^data:image\/(\w+);base64,/', $signatureData, $type)) {
        //     $data = substr($signatureData, strpos($signatureData, ',') + 1);
        //     $type = strtolower($type[1]); // jpg, png, gif

        //     // Decode the Base64 data
        //     $data = base64_decode($data);

        //     // Create a unique filename
        //     $fileName = uniqid() . '.png'; // Change the extension based on your needs

        //     // Store the signature image
        //     $signaturePath = 'dvla/signature_image/' . $fileName;

        //     // Save the file to the local filesystem
        //     \Storage::disk('local')->put($signaturePath, $data);
        //     $driverConsentForm->signature_image = $signaturePath; // Save the signature image path

        // } else {
        //     return redirect()->back()->withErrors(['signature_image' => 'Invalid signature image data.']);
        // }

        // Save the form data into the database
        $driverConsentForm->save();

        $driver->consent_form_status = 'Yes';
        // Save the driver record
        $driver->save();

        // if ($driverConsentForm->email) { // Check if the email field is filled
        //     \Mail::to($driverConsentForm->email)->send(new \App\Mail\DriverConsentFormSubmitted($driverConsentForm));
        // }

        // Redirect back or to another page with a success message
        return redirect()->route('driverconsent.submitted')->with('success', 'Driver consent form submitted successfully and an email has been sent.');
    }

    public function downloadPdf($id)
    {
        // Find the Driver Consent Form by ID
        $driverConsentForm = DriverConsentForm::findOrFail($id);
        // // Render the Blade view to HTML
        // $view = view('driverconsent.template')->render(); // Ensure the path is correct

        // // Generate PDF
        // $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($view)
        //     ->setPaper('A4', 'portrait') // Set paper size to A4 and orientation to portrait
        //     ->setOptions(['defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);

        // $filename = 'driver_consent_form.pdf';

        // return $pdf->stream($filename);

        $driverName = $driverConsentForm->first_name; // Assuming 'driver_name' is a column in DriverConsentForm

        $filePath = storage_path('dvla/dvla-906.html'); // Path to the file in storage/dvla

        if (\Illuminate\Support\Facades\File::exists($filePath)) {
            $htmlContent = \Illuminate\Support\Facades\File::get($filePath); // Read the file content

            return view('driverconsent.template', ['content' => $htmlContent, 'driverConsentForm' => $driverConsentForm, 'fileName' => $driverName.' Consent Form']); // Pass it to a view
        } else {
            return 'File not found';
        }
    }
}
