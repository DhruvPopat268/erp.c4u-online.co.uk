<?php

namespace App\Http\Controllers;

use App\Models\EmailSender;
use App\Models\MonthlyEmailSender;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('manage emailsend')) {
            $loggedInUser = \Auth::user();
            $companyName = $loggedInUser->companyname;
            $contracts = null;

            if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
                $contracts = EmailSender::with(['types', 'creator','companyDetails'])
                ->whereHas('types', function ($q) {
                    $q->where('company_status', 'Active'); // Only include assignments where the company is active
                })
                ->get();
            } else {
                $contracts = EmailSender::where('companyname', $companyName)
                ->whereHas('types', function ($q) {
                    $q->where('company_status', 'Active'); // Only include assignments where the company is active
                })
                    ->with(['types', 'creator','companyDetails'])
                    ->get();
            }

            return view('emailsender.index', compact('contracts'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function monthlyindex()
    {
        if (\Auth::user()->can('manage emailsend')) {
            $loggedInUser = \Auth::user();
            $companyName = $loggedInUser->companyname;
            $contracts = null;

            if ($loggedInUser->hasRole('company') || $loggedInUser->hasRole('PTC manager')) {
                $contracts = MonthlyEmailSender::with(['types', 'creator','companyDetails'])
                ->whereHas('types', function ($q) {
                    $q->where('company_status', 'Active'); // Only include assignments where the company is active
                })
                ->get();
            } else {
                $contracts = MonthlyEmailSender::where('companyname', $companyName)
                    ->with(['types', 'creator','companyDetails'])
                    ->whereHas('types', function ($q) {
                        $q->where('company_status', 'Active'); // Only include assignments where the company is active
                    })
                    ->get();
            }

            return view('monthlyemailsender.index', compact('contracts'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function importFile()
    {
        return view('emailsender.import');
    }

    public function import(Request $request)
    {
        $rules = [
            'files' => 'required',
            'files.*' => 'file|mimes:pdf',
        ];
    
        $validator = \Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
    
            return redirect()->back()->with('error', $messages->first());
        }
    
        $uploadedFiles = $request->file('files');
        $filesGroupedByCompany = [];
        $errorArray = [];
    
        foreach ($uploadedFiles as $file) {
            // Extract the first 7 characters from the file name
            $fileNameWithoutExtension = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $companyIdentifier = substr($fileNameWithoutExtension, 0, 7);
    
            // Fetch company details matching the first 7 characters of the file name
            $company = \App\Models\CompanyDetails::where('account_no', 'LIKE', $companyIdentifier.'%')->first();
    
            if ($company) {
                // Check if company status is 'Active'
                if ($company->company_status === 'Active') {
                    $fileName = $file->getClientOriginalName();
                    $filePath = $file->storeAs('weekly-email-report', $fileName); // Adjust storage path as needed
    
                    if ($filePath) {
                        // Use a sequential key for each file path
                        $filesGroupedByCompany[$company->id][] = $filePath;
                    } else {
                        $errorArray[] = 'Error saving file: '.$fileName;
                    }
                } else {
                    // Skip file if company status is not 'Active'
                    $errorArray[] = 'Company with account number ' . $company->account_no . ' is not active. File skipped: ' . $file->getClientOriginalName();
                }
            } else {
                $errorArray[] = 'No matching company found for file: '.$file->getClientOriginalName();
            }
        }
    
        foreach ($filesGroupedByCompany as $companyId => $filePaths) {
            // Prepare the JSON structure with keys 1, 2, etc.
            $filesJson = [];
            foreach ($filePaths as $index => $filePath) {
                // Remove the 'weekly-email-report/' prefix from the file path
                $cleanedFilePath = str_replace('weekly-email-report/', '', $filePath);
                $filesJson[$index + 1] = $cleanedFilePath; // +1 to make keys start from 1
            }
    
            $driverService = new \App\Models\EmailSender();
            $driverService->files = json_encode($filesJson);
    
            $driverService->companyName = $companyId; // Save the company ID
            $driverService->status = 'Not Send Mail';
            $driverService->created_by = \Auth::user()->id;
            $driverService->save();
        }
    
        if (empty($errorArray)) {
            $data['status'] = 'success';
            $data['msg'] = __('All PDFs successfully uploaded');
        } else {
            $data['status'] = 'error';
            $data['msg'] = implode('<br/>', $errorArray); // Combine error messages with line breaks
        }
    
        return redirect()->back()->with($data['status'], $data['msg']);
    }
    

    public function MonthlyimportFile()
    {
        return view('monthlyemailsender.import');
    }

    public function Monthlyimport(Request $request)
{
    $rules = [
        'files' => 'required|array|max:1000',
        'files.*' => 'file|mimes:pdf',
                    'email_type' => 'required|string' // Validate email_type as well

    ];

    $validator = \Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        $messages = $validator->getMessageBag();
        return redirect()->back()->with('error', $messages->first());
    }

    $uploadedFiles = $request->file('files');
            $emailType = $request->input('email_type'); // Get the selected email type

    $filesGroupedByCompany = [];
    $errorArray = [];

    foreach ($uploadedFiles as $file) {
        // Extract the first 7 characters from the file name
        $fileNameWithoutExtension = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $companyIdentifier = substr($fileNameWithoutExtension, 0, 7);

        // Fetch company details matching the first 7 characters of the file name
        $company = \App\Models\CompanyDetails::where('account_no', 'LIKE', $companyIdentifier.'%')->first();

        if ($company) {
            if ($company->company_status === 'Active') {
            $fileName = $file->getClientOriginalName();
            $filePath = $file->storeAs('monthly-email-report', $fileName); // Adjust storage path as needed

            if ($filePath) {
                // Use a sequential key for each file path
                $filesGroupedByCompany[$company->id][] = $filePath;
            } else {
                $errorArray[] = 'Error saving file: '.$fileName;
            }
        } else {
            // Skip file if company status is not 'Active'
            $errorArray[] = 'Company with account number ' . $company->account_no . ' is not active. File skipped: ' . $file->getClientOriginalName();
        }
    } else {
        $errorArray[] = 'No matching company found for file: '.$file->getClientOriginalName();
    }
}

    foreach ($filesGroupedByCompany as $companyId => $filePaths) {
        // Prepare the JSON structure with keys 1, 2, etc.
        $filesJson = [];
        foreach ($filePaths as $index => $filePath) {
            // Remove the 'monthly-email-report/' prefix from the file path
            $cleanedFilePath = str_replace('monthly-email-report/', '', $filePath);
            $filesJson[$index + 1] = $cleanedFilePath; // +1 to make keys start from 1
        }

        $driverService = new \App\Models\MonthlyEmailSender();
        $driverService->files = json_encode($filesJson);

        $driverService->companyName = $companyId; // Save the company ID
        $driverService->status = 'Not Send Mail';
                $driverService->email_type = $emailType; // Save the email type

        $driverService->created_by = \Auth::user()->id;
        $driverService->save();
    }

    if (empty($errorArray)) {
        $data['status'] = 'success';
        $data['msg'] = __('All PDFs successfully uploaded');
    } else {
        $data['status'] = 'error';
        $data['msg'] = implode('<br/>', $errorArray); // Combine error messages with line breaks
    }

    return redirect()->back()->with($data['status'], $data['msg']);
}


    // public function weeklysendReminders()
    // {
    //     $companies = \App\Models\CompanyDetails::where('id')
    //         ->get();

    //     // Iterate over each company and prepare data for emails
    //     foreach ($companies as $company) {
    //         // Prepare data for company email
    //         $companyData = [
    //             'companyName' => $company->name,
    //             'companyEmail' => $company->email,
    //         ];

    //         // Send summary email to company
    //         \Mail::to($companyData['companyEmail'])->send(new \App\Mail\WeeklySendReport($companyData));
    //     }

    //     // return redirect()->with('success', __('Reminder emails sent successfully.'));
    //     return response()->json(['message' => 'Success'], 200);
    // }

    public function weeklysendReminders()
    {
        $drivers = \App\Models\EmailSender::with('companyDetails')->where('status', '!=', 'DONE')->get();
        $driversByCompany = $drivers->groupBy('companyDetails.id');
    
        foreach ($driversByCompany as $companyId => $drivers) {
            $companyDetails = \App\Models\CompanyDetails::find($companyId);
    
            // Check if company status is 'Active'
            if ($companyDetails && $companyDetails->company_status === 'Active') {
                $companyData = [
                    'companyName' => $companyDetails->name,
                    'drivers' => $drivers->map(function ($driver) {
                        $files = json_decode($driver->files, true);
                        $formattedFiles = [];
                        foreach ($files as $key => $filename) {
                            $formattedFiles[] = [
                                'id' => $key,
                                'filename' => $filename,
                                'path' => storage_path('weekly-email-report/'.$filename),
                            ];
                        }
    
                        return [
                            'driver' => $driver,
                            'files' => $formattedFiles,
                        ];
                    }),
                ];
    
                // Get the email addresses as an array
                $emails = [];
                if ($companyDetails->email) {
                    $emails[] = $companyDetails->email;
                }
                if ($companyDetails->operator_email) {
                    $operatorEmails = json_decode($companyDetails->operator_email, true);
                    if (is_array($operatorEmails)) {
                        $emails = array_merge($emails, $operatorEmails);
                    }
                }
    
                // Update the status to "Processing" for all drivers in this company
                foreach ($drivers as $driver) {
                    $driver->status = 'SENDING';
                    $driver->save();
                }
    
                try {
                    \Mail::to($emails)
                        ->queue(new \App\Mail\WeeklySendReport($companyData, $drivers));
    
                    // Update the status to "Done" after the email is sent successfully
                    foreach ($drivers as $driver) {
                        $driver->status = 'DONE';
                        $driver->save();
                    }
                } catch (\Exception $e) {
                    // Update the status to "Failed" if there is an error
                    foreach ($drivers as $driver) {
                        $driver->status = 'FAILED';
                        $driver->save();
                    }
                }
            } else {
                // Skip the company if status is not 'Active'
                continue;
            }
        }
    
        return redirect()->route('weeklyemailsender')->with('success', __('Weekly Report emails sent successfully.'));
    }
    
    
    public function monthlysendReminders()
    {
        $drivers = \App\Models\MonthlyEmailSender::with('companyDetails')->where('status', '!=', 'DONE')->get();
        $driversByCompany = $drivers->groupBy('companyDetails.id');
    
        foreach ($driversByCompany as $companyId => $drivers) {
            $companyDetails = \App\Models\CompanyDetails::find($companyId);
    
            // Check if company status is 'Active'
            if ($companyDetails && $companyDetails->company_status === 'Active') {
                $companyData = [
                    'companyName' => $companyDetails->name,
                    'drivers' => $drivers->map(function ($driver) {
                        $files = json_decode($driver->files, true);
                        $formattedFiles = [];
                        foreach ($files as $key => $filename) {
                            $formattedFiles[] = [
                                'id' => $key,
                                'filename' => $filename,
                                'path' => storage_path('monthly-email-report/'.$filename),
                            ];
                        }
    
                        return [
                            'driver' => $driver,
                            'files' => $formattedFiles,
                            'email_type' => $driver->email_type, // Include email_type for each driver
                        ];
                    }),
                ];
    
                // Get the email addresses as an array
                $emails = [];
                if ($companyDetails->email) {
                    $emails[] = $companyDetails->email;
                }
                if ($companyDetails->operator_email) {
                    $operatorEmails = json_decode($companyDetails->operator_email, true);
                    if (is_array($operatorEmails)) {
                        $emails = array_merge($emails, $operatorEmails);
                    }
                }
    
                // Update the status to "Processing" for all drivers in this company
                foreach ($drivers as $driver) {
                    $driver->status = 'SENDING';
                    $driver->save();
                }
    
                try {
                    \Mail::to($emails)
                        ->queue(new \App\Mail\MonthlySendReport($companyData, $drivers));
    
                    // Update the status to "Done" after the email is sent successfully
                    foreach ($drivers as $driver) {
                        $driver->status = 'DONE';
                        $driver->save();
                    }
                } catch (\Exception $e) {
                    // Update the status to "Failed" if there is an error
                    foreach ($drivers as $driver) {
                        $driver->status = 'FAILED';
                        $driver->save();
                    }
                    \Log::error('Failed to send monthly report email for company ID ' . $companyId . ': ' . $e->getMessage());
                }
            } else {
                // Skip the company if status is not 'Active'
                continue;
            }
        }
    
        return redirect()->route('monthlyemailsender')->with('success', __('Monthly Report emails sent successfully.'));
    }
    
    
    public function deleteOldData()
    {
        // Delete all records from the EmailSender model
        $deletedRows = EmailSender::truncate();
        $deletedRows = MonthlyEmailSender::truncate();

          // return redirect()->route('weeklyemailsender')->with('success', __('Deleted Weekly and Monthly Data.'));
        return response()->json(['message' => 'Deleted Weekly and Monthly Data.']);

    }
    public function weeklyEmailDataexport(Request $request)
    {
        // Fetch all data from the Driver model
        $weeklyEmail = \App\Models\EmailSender::with('types','companyDetails')
        ->whereHas('types', function ($q) {
            $q->where('company_status', 'Active'); // Only include assignments where the company is active
        })
        ->get();

        // Adjust the export logic as per your requirement
        $name = 'Weekly Email Data_'.date('d-m-Y');

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\WeeklyEmailDataExport($weeklyEmail), $name.'.xlsx');
    }

    public function MonthlyEmailDataexport(Request $request)
    {
        // Fetch all data from the Driver model
        $monthlyEmail = \App\Models\MonthlyEmailSender::with('types','companyDetails')
        ->whereHas('types', function ($q) {
            $q->where('company_status', 'Active'); // Only include assignments where the company is active
        })
        ->get();

        // Adjust the export logic as per your requirement
        $name = 'Monthly Email Data_'.date('d-m-Y');

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MonthlyEmailDataExport($monthlyEmail), $name.'.xlsx');
    }
}
