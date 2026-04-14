<?php

namespace App\Http\Controllers;

use App\Models\CompanyDetails;
use App\Models\Contract;
use Illuminate\Http\Request;

class ContractTypeController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('manage company')) {
            if (\Auth::user()->type == 'company' || \Auth::user()->type == 'PTC manager') {
                $types = CompanyDetails::with('creator')->where('created_by', '=', \Auth::user()->creatorId())->orderByRaw("CASE WHEN company_status = 'Active' THEN 1 ELSE 2 END")->get();

                // Check if $types is not null before returning the view
                if ($types !== null) {
                    return view('contractType.index', compact('types'));
                } else {
                    return redirect()->back()->with('error', __('No company details found.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        return view('contractType.create');
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create company')) {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email',
                'address' => 'required|string',
                'contact' => 'required',
                                'lc_check_status' => 'required',
                                'company_status' => 'nullable',
                'device.*' => 'required|string',
                'operator_name.*' => 'required|string',
                'operator_phone.*' => 'required|string',
                'operator_role.*' => 'required|in:Director,Manager,Transport Manager',
                'operator_dob.*' => 'required|date_format:d/m/Y',
                'status.*' => 'required',
                'compliance.*' => 'required|string',
                'operator_email.*' => 'required|email',
                'fors_browse_policy' => 'nullable|date',
            'fors_silver_policy' => 'nullable|date',
            'fors_gold_policy' => 'nullable|date',
            'promotional_email' => 'nullable|string',
            'ptc_library' => 'nullable|string',
            'public_liability' => 'nullable|date',
            'goods_in_transit' => 'nullable|date',
            'public_liability_insurance' => 'nullable|date',
             'payment_type' => 'required|in:Prepaid,Postpaid',  // ✅ new
    'coins' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // Retrieve last account number
            $lastCompany = \App\Models\CompanyDetails::orderBy('id', 'desc')->first();
            if ($lastCompany && preg_match('/-(\d+)$/', $lastCompany->account_no, $matches)) {
                $lastNumber = (int) $matches[1]; // Extract the number part and convert to integer
                if ($lastNumber >= 999) {
                    $nextNumber = 1; // Reset to 001 if last number is 999 or greater
                } else {
                    $nextNumber = $lastNumber + 1;
                }
            } else {
                $nextNumber = 1; // Default to 1 if no previous record exists
            }

            // Remove special characters and spaces from company name
            $cleanedName = preg_replace('/[^A-Za-z0-9]/', '', $request->name);

            // Generate account_no with cleaned company name and sequential number
            if (strlen($cleanedName) >= 3) {
                $accountNoPrefix = strtoupper(substr($cleanedName, 0, 3)); // Take first three characters
            } else {
                $accountNoPrefix = strtoupper($cleanedName).chr(rand(65, 90)); // Company name + random uppercase letter
            }

            $accountNo = $accountNoPrefix.'-'.str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $company = new CompanyDetails();
            $company->name = $request->name;
            $company->email = $request->email;
            $company->address = $request->address;
            $company->promotional_email = $request->promotional_email;
            $company->ptc_library = $request->ptc_library;
            
             // Handle payment type and coins
$company->payment_type = $request->payment_type;

if ($request->payment_type === 'Postpaid') {
    $company->coins = -1; // Unlimited coins
} else {
    $company->coins = $request->coins ?? 0; // Use entered coins or default 0
}

            $contactNumber = $request->contact;
            if ($contactNumber && $contactNumber !== '-') {
                $formattedContact = (strpos($contactNumber, '+44') !== 0) ? '+44 '.$contactNumber : $contactNumber;
            } else {
                $formattedContact = $contactNumber; // Keep null or "-" as is
            }
            $company->contact = $formattedContact;
            $company->lc_check_status = $request->lc_check_status;

            $company->account_no = $accountNo;
            $company->company_status = $request->company_status;
            $company->created_by = \Auth::user()->creatorId();
            $company->created_username = \Auth::user()->id;

            // Handle devices and other input
            $devices = $request->device;
            $deviceOthers = $request->device_other;
            $devicesFormatted = [];
            foreach ($devices as $key => $device) {
                if ($device === 'other' && isset($deviceOthers[$key])) {
                    $devicesFormatted[] = $deviceOthers[$key];
                } else {
                    $devicesFormatted[] = $device;
                }
            }
            $company->device = json_encode($devicesFormatted);

            $company->operator_name = json_encode($request->operator_name);

            $formattedOperatorPhones = [];
            foreach ($request->operator_phone as $operatorPhone) {
                if (strpos($operatorPhone, '+44') !== 0) {
                    // If +44 is not already present, prepend it
                    $formattedOperatorPhones[] = '+44 '.$operatorPhone;
                } else {
                    // If +44 is already present, keep it as is
                    $formattedOperatorPhones[] = $operatorPhone;
                }
            }
            $company->operator_phone = json_encode($formattedOperatorPhones);
            $company->status = json_encode($request->status);
            $company->compliance = json_encode($request->compliance);
            $company->operator_email = json_encode($request->operator_email);

            // Handle operator roles
            $company->operator_role = json_encode($request->operator_role);

            // Handle operator DOB
            $operatorDobs = [];
            // foreach ($request->operator_dob as $key => $operatorDob) {
            //     $date = \DateTime::createFromFormat('d/m/Y', $operatorDob);
            //     if ($date instanceof \DateTime) {
            //         $operatorDobs[$key] = $date->format('d/m/Y');
            //     } else {
            //         // Handle invalid dates gracefully (if necessary)
            //         $operatorDobs[$key] = null;
            //     }
            // }
            $company->operator_dob = json_encode($operatorDobs);
            
             $company->fors_browse_policy = $request->fors_browse_policy;
            $company->fors_silver_policy = $request->fors_silver_policy;
            $company->fors_gold_policy = $request->fors_gold_policy;

            $company->public_liability = $request->public_liability;
            $company->goods_in_transit = $request->goods_in_transit;
            $company->public_liability_insurance = $request->public_liability_insurance;

            $company->save();
            
              // Payment history
        \App\Models\CompanyPaymentHistory::create([
            'company_id' => $company->id,
            'old_payment_type' => null,
            'new_payment_type' => $company->payment_type,
            'old_coins' => null,
            'new_coins' => $company->coins,
            'changed_by' => \Auth::id(),
        ]);


            if ($request->has('insurance_type') && $request->has('insurance_date')) {
    foreach ($request->insurance_type as $key => $type) {
        if (!empty($type)) {
            \App\Models\CompanyInsurance::create([
                'company_id' => $company->id,
                'insurance_type' => $type,
                'insurance_date' => $request->insurance_date[$key] ?? null,
            ]);
        }
    }
}


            // Set the success message and trigger modal flag
            return redirect()->route('contractType.index')->with([
                'success' => __('Company successfully created.'),
                'showdepotModal' => true,
            ]);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // public function show($id)
    // {
    //     if (\Auth::user()->can('show company')) {
    //         $contractType = \App\Models\CompanyDetails::find($id);

    //         return view('contractType.show', compact('contractType'));

    //     } else {
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    public function show($id)
    {
        if (\Auth::user()->can('show company')) {
            // Fetch company details
            $contractType = \App\Models\CompanyDetails::find($id);

            // Fetch depots related to the company
            $depots = \App\Models\Depot::where('companyName', $id)->get();
            $totalVehicles = $depots->sum('vehicles');
            $totalTrailers = $depots->sum('trailers');

            // Fetch drivers associated with the company
            $driversCount = \App\Models\Driver::where('companyName', $id)->count();
            $vehiclesCount = \App\Models\vehicleDetails::where('companyName', $id)->count();

            return view('contractType.show', compact('contractType', 'depots', 'driversCount', 'vehiclesCount', 'totalVehicles', 'totalTrailers'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(CompanyDetails $contractType)
    {
        return view('contractType.edit', compact('contractType'));
    }

    public function update(Request $request, CompanyDetails $contractType)
    {
        if (\Auth::user()->can('edit company')) {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required|string',
                    'email' => 'nullable|string',
                    'address' => 'nullable|string',
                    'contact' => 'nullable',
                                    'lc_check_status' => 'required',
                                    'company_status' => 'nullable',
                    'device.*' => 'nullable|string|in:Convey,SJD,Geotab,DigiDL,other', // Updated validation for device
                    'operator_name.*' => 'nullable|string',
                    'operator_phone.*' => 'nullable|string',
                    'operator_role.*' => 'required|in:Director,Manager,Transport Manager',
                    // 'operator_dob.*' => 'nullable|date_format:d/m/Y',
                    'status.*' => 'nullable|string|in:ACTIVE,INACTIVE',
                    'compliance.*' => 'nullable|string|in:YES,NO', // Adjusted validation for compliance
                    'operator_email.*' => 'nullable|email',
                    'fors_browse_policy' => 'nullable|date',
            'fors_silver_policy' => 'nullable|date',
            'fors_gold_policy' => 'nullable|date',
            'promotional_email' => 'nullable|string',
            'ptc_library' => 'nullable|string',
            'public_liability' => 'nullable|date',
            'goods_in_transit' => 'nullable|date',
            'public_liability_insurance' => 'nullable|date',
            'payment_type' => 'required|in:Prepaid,Postpaid',
                'coins' => 'nullable|integer',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            
             $oldPaymentType = $contractType->payment_type;
    $oldCoins = (int) $contractType->coins; // Cast DB value to integer

    $newPaymentType = $request->payment_type;
    if ($newPaymentType === 'Prepaid') {
        $newCoins = (int) ($request->coins ?? 0);
    } elseif ($newPaymentType === 'Postpaid') {
        $newCoins = -1;
    } else {
        $newCoins = null;
    }

    // Only create history if actual change
    if ($oldPaymentType !== $newPaymentType || $oldCoins !== $newCoins) {
        \App\Models\CompanyPaymentHistory::create([
            'company_id' => $contractType->id,
            'old_payment_type' => $oldPaymentType,
            'new_payment_type' => $newPaymentType,
            'old_coins' => $oldCoins,
            'new_coins' => $newCoins,
            'changed_by' => \Auth::id(),
        ]);
    }

            $contractType->name = $request->name;
            $contractType->email = $request->email;
            $contractType->address = $request->address;
            $contractType->promotional_email = $request->promotional_email;
            $contractType->ptc_library = $request->ptc_library;
            $contractType->company_status = $request->company_status;
                        $contractType->payment_type = $newPaymentType;
    $contractType->coins = $newCoins;
            $contactNumber = $request->contact;
            if ($contactNumber && $contactNumber !== '-') {
                $formattedContact = '+44 '.$contactNumber;
            } else {
                $formattedContact = $contactNumber; // retain null or '-' as is
            }
            $contractType->contact = $formattedContact;
                        $contractType->lc_check_status = $request->lc_check_status;

            $contractType->created_by = \Auth::user()->creatorId();

            // // Serialize director names and associate them with numeric identifiers
            // $contractType->director_name = json_encode($request->director_name);
            // $contractType->director_dob = json_encode($request->director_dob);
            // Handle devices and other input
            $devices = $request->device;
            $deviceOthers = $request->device_other;
            $devicesFormatted = [];
            foreach ($devices as $key => $device) {
                if ($device === 'other' && isset($deviceOthers[$key])) {
                    $devicesFormatted[] = $deviceOthers[$key];
                } else {
                    $devicesFormatted[] = $device;
                }
            }
            $contractType->device = json_encode($devicesFormatted);

            $contractType->operator_name = json_encode($request->operator_name);

            $operatorPhone = $request->operator_phone;
            if (is_array($operatorPhone)) {
                foreach ($operatorPhone as $index => $phone) {
                    if ($phone && $phone !== '-') {
                        $operatorPhone[$index] = '+44 '.$phone;
                    }
                }
            }
            $contractType->operator_phone = json_encode($operatorPhone);
            $contractType->operator_role = json_encode($request->operator_role);
            // $contractType->operator_dob = json_encode($request->operator_dob);
            $contractType->status = json_encode($request->status);
            $contractType->compliance = json_encode($request->compliance);
            $contractType->operator_email = json_encode($request->operator_email);
            
             $contractType->fors_browse_policy = $request->fors_browse_policy;
            $contractType->fors_silver_policy = $request->fors_silver_policy;
            $contractType->fors_gold_policy = $request->fors_gold_policy;

                        $contractType->public_liability = $request->public_liability;
            $contractType->goods_in_transit = $request->goods_in_transit;
            $contractType->public_liability_insurance = $request->public_liability_insurance;

            $contractType->save();

            $contractType->insurances()->delete();

    // Then insert new ones
    if ($request->has('insurance_type') && $request->has('insurance_date')) {
        foreach ($request->insurance_type as $key => $type) {
            if (!empty($type)) {
                \App\Models\CompanyInsurance::create([
                    'company_id' => $contractType->id,
                    'insurance_type' => $type,
                    'insurance_date' => $request->insurance_date[$key] ?? null,
                ]);
            }
        }
    }

            return redirect()->route('contractType.index')->with('success', __('Company successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    private function storeCompanyDetails($data)
    {
        // Retrieve last account number
        $lastCompany = \App\Models\CompanyDetails::orderBy('id', 'desc')->first();
        if ($lastCompany && preg_match('/-(\d+)$/', $lastCompany->account_no, $matches)) {
            $lastNumber = (int) $matches[1]; // Extract the number part and convert to integer
            if ($lastNumber >= 999) {
                $nextNumber = 1; // Reset to 001 if last number is 999 or greater
            } else {
                $nextNumber = $lastNumber + 1;
            }
        } else {
            $nextNumber = 1; // Default to 1 if no previous record exists
        }

        // Generate account number
        $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $data['name'])); // Remove special characters
        $prefix = preg_replace('/^\d+/', '', $prefix); // Remove leading numbers
        $prefix = substr($prefix, 0, 3); // Get first 3 characters
        $prefix = str_pad($prefix, 3, chr(rand(65, 90))); // Pad if less than 3 characters (using 'A' as padding)

        $paddedNextNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT); // Pad with leading zeros if necessary
        $accountNo = $prefix.'-'.$paddedNextNumber; // Format: ABC-001 (where 001 is the next incremented number)

        // Create a new CompanyDetails instance
        $company = new \App\Models\CompanyDetails();
        $company->name = $data['name'];
        $company->email = $data['email'];
        $company->address = $data['address'];
        // Format contact number if not null or '-'
        $company->contact = isset($data['contact']) && $data['contact'] !== '-' ? $this->formatUKPhoneNumber($data['contact']) : '-';
        $company->account_no = $accountNo; // Set the generated account number
        $company->created_by = \Auth::user()->creatorId();
        $company->created_username = \Auth::user()->id;

        // Serialize director names and associate them with numeric identifiers
        $operatorDobs = [];

        foreach ($data['operator_dob'] as $key => $operatordob) {
            if ($operatordob && \DateTime::createFromFormat('d/m/Y', $operatordob) !== false) {
                $operatorDobs[] = \Carbon\Carbon::createFromFormat('d/m/Y', $operatordob)->format('d/m/Y');
            } else {
                $operatorDobs[] = '-';
            }
        }
        $company->operator_dob = json_encode($operatorDobs);

        // Serialize device and operator details
        $company->operator_role = json_encode($data['operator_role']);
        $company->device = json_encode($data['device']);
        $company->operator_name = json_encode($data['operator_name']);

        // Format operator phone numbers to prepend +44 if not already present
        $formattedOperatorPhones = [];
        foreach ($data['operator_phone'] as $operatorPhone) {
            if (! empty($operatorPhone) && strpos($operatorPhone, '+44') !== 0) {
                $formattedOperatorPhones[] = '+44 '.$operatorPhone;
            } else {
                $formattedOperatorPhones[] = $operatorPhone;
            }
        }
        $company->operator_phone = json_encode($formattedOperatorPhones);

        // Serialize status, compliance, and operator email
        $company->status = json_encode($data['status']);
        $company->compliance = json_encode($data['compliance']);
        $company->operator_email = json_encode($data['operator_email']);

        // Save the company details
        $company->save();
    }

    public function importFile()
    {
        if (\Auth::user()->can('create company')) {
            return view('contractType.import');
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

        $drivers = (new \App\Imports\CompanyImport)->toArray($request->file('file'))[0];
        $totalProduct = count($drivers) - 1;
        $errorArray = [];
        $successCount = 0;

        $companies = [];

        foreach ($drivers as $key => $items) {
            // Skip header row
            if ($key === 0) {
                continue;
            }

            $name = $items[0] ?? null;

            if (! isset($companies[$name])) {
                $companies[$name] = [
                    'name' => $name,
                    'email' => $items[1] ?? '-',
                    'address' => $items[2] ?? '-',
                    'contact' => $items[3] ?? null,
                    'operator_role' => [],
                    'device' => [],
                    'operator_name' => [],
                    'operator_phone' => [],
                    'operator_dob' => [],
                    'status' => [],
                    'compliance' => [],
                    'operator_email' => [],
                ];
            }

            $companies[$name]['operator_role'][] = $items[4] ?? '-';

            $dob = $items[8] ?? null;
            if ($dob && \DateTime::createFromFormat('d/m/Y', $dob) !== false) {
                $dob = \Carbon\Carbon::parse($dob)->format('d/m/Y');
            } else {
                $dob = '-'; // or handle the invalid date case appropriately
            }
            $companies[$name]['device'][] = $items[5] ?? null;
            $companies[$name]['operator_name'][] = $items[6] ?? null;
            $companies[$name]['operator_phone'][] = $items[7] ?? null;
            $companies[$name]['operator_dob'][] = $dob;
            $companies[$name]['status'][] = $items[9] ?? null;
            $companies[$name]['compliance'][] = $items[10] ?? null;
            $companies[$name]['operator_email'][] = $items[11] ?? null;
        }

        foreach ($companies as $name => $data) {
            try {
                // Check if company already exists
                $existingCompany = \App\Models\CompanyDetails::where('name', $name)->first();

                // Ensure the contact number has the +44 prefix if it's not null or '-'
                if (! empty($data['contact']) && $data['contact'] !== '-') {
                    $contactNumber = $data['contact'];
                    if (strpos($contactNumber, '+44') !== 0) {
                        $formattedContactNo = '+44 '.$contactNumber;
                    } else {
                        $formattedContactNo = $contactNumber;
                    }
                } else {
                    $formattedContactNo = null; // or empty string, depending on your database schema
                }
                $data['contact'] = $formattedContactNo;

                // Format operator phone numbers to prepend +44 if not already present
                $formattedOperatorPhones = [];
                foreach ($data['operator_phone'] as $operatorPhone) {
                    if (! empty($operatorPhone) && strpos($operatorPhone, '+44') !== 0) {
                        // If +44 is not already present and operatorPhone is not empty, prepend it
                        $formattedOperatorPhones[] = '+44 '.$operatorPhone;
                    } else {
                        // If +44 is already present or operatorPhone is empty, keep it as is
                        $formattedOperatorPhones[] = $operatorPhone;
                    }
                }
                $data['operator_phone'] = $formattedOperatorPhones;

                if ($existingCompany) {
                    // Merge new data with existing data while preserving existing values for specific fields
                    $this->updateCompanyDetails($existingCompany, $data);

                } else {
                    // Create new company if it doesn't exist
                    $this->storeCompanyDetails($data);
                }

                $successCount++;
            } catch (\Exception $e) {
                $errorArray[] = $name;
            }
        }

        if (empty($errorArray)) {
            $data['status'] = 'success';
            $data['msg'] = __('All records successfully imported');
        } else {
            $data['status'] = 'error';
            $data['msg'] = count($errorArray).' '.__('Record(s) failed to import out of').' '.$totalProduct.' '.__('record(s)');
            \Session::put('errorArray', $errorArray);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }

    private function updateCompanyDetails($existingCompany, $data)
    {
        // Merge new data with existing data while preserving unique values for specific fields
        $existingCompany->email = $data['email'];
        $existingCompany->address = $data['address'];
        $existingCompany->contact = $data['contact'];

        $existingOperatorRoles = json_decode($existingCompany->operator_role, true);
        $newOperatorRoles = array_merge($existingOperatorRoles, $data['operator_role']);
        $existingCompany->operator_role = json_encode($newOperatorRoles);

        $existingDevices = json_decode($existingCompany->device, true);
        $newDevices = array_merge($existingDevices, $data['device']);
        $existingCompany->device = json_encode($newDevices);

        $existingOperatorNames = json_decode($existingCompany->operator_name, true);
        $newOperatorNames = array_merge($existingOperatorNames, $data['operator_name']);
        $existingCompany->operator_name = json_encode($newOperatorNames);

        $existingOperatorPhones = json_decode($existingCompany->operator_phone, true);
        $newOperatorPhones = array_merge($existingOperatorPhones, $data['operator_phone']);
        $existingCompany->operator_phone = json_encode($newOperatorPhones);

        $existingOperatorDobs = json_decode($existingCompany->operator_dob, true);
        $newOperatorDobs = array_merge($existingOperatorDobs, $data['operator_dob']);
        $existingCompany->operator_dob = json_encode($newOperatorDobs);

        $existingStatuses = json_decode($existingCompany->status, true);
        $newStatuses = array_merge($existingStatuses, $data['status']);
        $existingCompany->status = json_encode($newStatuses);

        $existingCompliances = json_decode($existingCompany->compliance, true);
        $newCompliances = array_merge($existingCompliances, $data['compliance']);
        $existingCompany->compliance = json_encode($newCompliances);

        $existingOperatorEmails = json_decode($existingCompany->operator_email, true);
        $newOperatorEmails = array_merge($existingOperatorEmails, $data['operator_email']);
        $existingCompany->operator_email = json_encode($newOperatorEmails);

        $existingCompany->save();
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

    public function destroy(CompanyDetails $contractType)
    {
        if (\Auth::user()->can('delete company')) {
            $data = Contract::where('type', $contractType->id)->first();
            if (! empty($data)) {
                return redirect()->back()->with('error', __('this type is already use so please transfer or delete this type related data.'));
            }

            $contractType->delete();

            return redirect()->route('contractType.index')->with('success', __('Company successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function companyDataexport(Request $request)
    {
        // Fetch data from CompanyDetails model
        $companyDetails = \App\Models\CompanyDetails::all();

        // Fetch data from Depot model
        $depots = \App\Models\Depot::all()->keyBy('companyName');

        // Pass both sets of data to the export class
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\CompanyDataExport($companyDetails, $depots), 'CompanyDetails_Data_'.date('d-m-Y').'.xlsx');
    }
    
     public function creditcoinsindex(Request $request)
    {

        if (\Auth::user()->can('manage credit logs')) {
            $loggedInUser = \Auth::user();

            // Retrieve the company name of the user
            $companyName = $loggedInUser->companyname;

            // Retrieve the selected company ID from the request
             $selectedCompanyId = $request->input('company_id');

            // Retrieve contracts based on the user's role
            $paymentHistories = null;
            if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
            // If the user has the 'company' role, show all data with active company status
            $paymentHistories = \App\Models\CompanyPaymentHistory::with('company', 'creator')
                ->whereHas('company', function ($query) {
                    $query->where('company_status', 'Active');
                })
                ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('company_id', $selectedCompanyId);
                })
                ->get();
            } else {
                // If the user doesn't have the 'company' role, only show contracts associated with the user's company
                $paymentHistories = \App\Models\CompanyPaymentHistory::where('company_id', $companyName)
                ->whereHas('company', function ($query) {
                    $query->where('company_status', 'Active');
                })
                ->with(['company', 'creator'])
                ->get();
            }

        // Retrieve all companies with active status for the dropdown filter
        $companies = CompanyDetails::where('company_status', 'Active')
            ->orderBy('name', 'asc')
            ->get();

            // Return the view with the contracts
            return view('contractType.creaditcoinsindex', compact('paymentHistories','companies'));
        } else {
            // If the user doesn't have the permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }
}
