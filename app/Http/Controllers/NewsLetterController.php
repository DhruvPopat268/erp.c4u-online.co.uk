<?php

namespace App\Http\Controllers;

use App\Imports\OtherEmailImport;
use App\Mail\GenericEmail;
use App\Models\CompanyDetails;
use App\Models\Driver;
use App\Models\EmailLog;
use App\Models\NewsLetterEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class NewsLetterController extends Controller
{
    public function index()
    {
        // Check if the user has either the 'company' role or 'PTC manager' role
        if (\Auth::user()->hasRole('company') || \Auth::user()->hasRole('PTC manager')) {
            $emails = NewsLetterEmail::all();

            // Return the view with the newsletter emails
            return view('newsletter.index', compact('emails'));
        } else {
            // If the user doesn't have the permission, redirect back with an error message
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function emailLogShow()
    {
        $emailLogs = EmailLog::orderBy('sent_at', 'desc')->get();

        return view('newsletter.emaillogs', compact('emailLogs'));
    }
    
    public function deleteAll()
{
    EmailLog::truncate(); // This will delete all records from the table
    return redirect()->back()->with('success', 'All email logs have been deleted.');
}


    public function getDriverNames()
    {
        $drivers = \App\Models\Driver::pluck('name')->toArray();

        return response()->json($drivers);
    }

    // public function sendEmail(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'roles' => 'required|array',
    //             'subject' => 'required|string',
    //             'text' => 'required|string',
    //             'attachments.*' => 'nullable|file', // Validate each file
    //         ]);

    //         $subject = $request->input('subject');
    //         $text = $request->input('text'); // This should include the Summernote content with image URLs
    //         $roles = $request->input('roles');
    //         $attachments = $request->file('attachments'); // Get all uploaded files
    //         $userId = auth()->id(); // Assuming the user is authenticated

    //         $recipients = [];

    //         if (in_array('Driver', $roles)) {
    //             $drivers = Driver::select('contact_email as email', 'name')->get()->toArray();
    //             $recipients = array_merge($recipients, $drivers);
    //         }

    //         if (in_array('Operator', $roles)) {
    //             $operators = CompanyDetails::select('email', 'name')->get()->toArray();
    //             $recipients = array_merge($recipients, $operators);
    //         }

    //         if (in_array('NewsLetterEmail', $roles)) {
    //             $newsletterEmails = NewsLetterEmail::select('email', 'name')->get()->toArray();
    //             $recipients = array_merge($recipients, $newsletterEmails);
    //         }

    //         foreach ($recipients as $recipient) {
    //             // Skip if the email is NULL or empty
    //             if (empty($recipient['email'])) {
    //                 continue;
    //             }

    //             // Replace the {name} placeholder with the recipient's name
    //             $personalizedText = str_replace('{name}', $recipient['name'], $text);

    //             $mail = new GenericEmail($subject, $personalizedText, $recipient['name']);

    //             // Attach files
    //             if ($attachments) {
    //                 foreach ($attachments as $attachment) {
    //                     $attachmentPath = $attachment->store('attachments'); // Example storage path
    //                     $mail->attach(storage_path('app/'.$attachmentPath)); // Attach the file to the email
    //                 }
    //             }

    //             // Send the email
    //             \Mail::to($recipient['email'])->send($mail);

    //             // Log the email
    //             EmailLog::create([
    //                 'email' => $recipient['email'],
    //                 'subject' => $subject,
    //                 'status' => 'SEND',
    //                 'sent_at' => now(),
    //                 'created_by' => $userId,
    //             ]);
    //         }

    //         return redirect()->back()->with('success', 'Emails sent successfully!');
    //     } catch (\Exception $e) {
    //         // Log the error message
    //         \Log::error('Email sending failed: '.$e->getMessage());

    //         // Log failed emails
    //         foreach ($recipients as $recipient) {
    //             if (empty($recipient['email'])) {
    //                 continue;
    //             }

    //             EmailLog::create([
    //                 'email' => $recipient['email'],
    //                 'subject' => $subject,
    //                 'status' => 'FAILED',
    //                 'sent_at' => now(),
    //                 'created_by' => $userId,
    //             ]);
    //         }

    //         // Redirect back with error message
    //         return redirect()->back()->with('error', 'Failed to send emails. Please try again later.');
    //     }
    // }
    
    public function sendEmail(Request $request)
    {
        try {
            $request->validate([
                'roles' => 'required|array',
                'subject' => 'required|string',
                'text' => 'required|string',
                'attachments.*' => 'nullable|file',
                'header_image' => 'nullable|image',
                'header_image_url' => 'nullable|url',
                'button_url' => 'nullable|url', // Validate button URL
                'button_text' => 'nullable|string', // Validate button text
            ]);

            $subject = $request->input('subject');
            $text = $request->input('text');
            $roles = $request->input('roles');
            $attachments = $request->file('attachments');
            $headerImage = $request->file('header_image');
            $headerImageUrl = $request->input('header_image_url');
            $buttonUrl = $request->input('button_url');
            $buttonText = $request->input('button_text');
            $userId = auth()->id();

            $headerImagePath = $headerImage ? $headerImage->store('header_images') : null;

            $recipients = [];

            // if (in_array('Driver', $roles)) {
            //     $drivers = Driver::select('contact_email as email', 'name')->get()->toArray();
            //     $recipients = array_merge($recipients, $drivers);
            // }
            
            if (in_array('Driver', $roles)) {
    $drivers = Driver::whereHas('company', function ($query) {
            $query->where('promotional_email', 'Yes');
        })
        ->select('contact_email as email', 'name')
        ->get()
        ->toArray();

    $recipients = array_merge($recipients, $drivers);
}


             if (in_array('Operator', $roles)) {
                $operators = CompanyDetails::where('promotional_email', 'Yes')
                    ->select('email', 'name')
                    ->get()
                    ->toArray();
                $recipients = array_merge($recipients, $operators);
            }

            if (in_array('NewsLetterEmail', $roles)) {
                $newsletterEmails = NewsLetterEmail::select('email', 'name')->get()->toArray();
                $recipients = array_merge($recipients, $newsletterEmails);
            }

            foreach ($recipients as $recipient) {
               if (empty($recipient['email']) || !str_contains($recipient['email'], '@')) {
    continue; // skip invalid emails
}


                $personalizedText = str_replace('{name}', $recipient['name'], $text);

                $mail = new GenericEmail(
                    $subject,
                    $personalizedText,
                    $recipient['name'],
                    null,
                    $headerImagePath ? storage_path($headerImagePath) : null,
                    $headerImageUrl,
                    $buttonUrl,
                    $buttonText,
                                        storage_path('/uploads/logo/5-logo-dark.png')

                );

                $attachmentPaths = [];

                if ($attachments) {
                    foreach ($attachments as $attachment) {
                        $attachmentPath = $attachment->store('attachments');
                        $fullPath = storage_path($attachmentPath);

                        if (file_exists($fullPath)) {
                            $attachmentPaths[] = $attachmentPath;
                            $mail->attach($fullPath);
                        } else {
                            \Log::warning('Attachment not found: ' . $fullPath);
                        }
                    }
                }

                \Mail::to($recipient['email'])->send($mail);

                EmailLog::create([
                    'email' => $recipient['email'],
                    'name' => $recipient['name'],
                    'subject' => $subject,
                    'body' => $text,
                    'header_image' => $headerImagePath,
                    'header_image_url' => $headerImageUrl,
                    'button_url' => $buttonUrl,
                    'button_text' => $buttonText,
                    'attachments' => json_encode($attachmentPaths),
                    'status' => 'SEND',
                    'sent_at' => now(),
                    'created_by' => $userId,
                ]);
            }

            return redirect()->back()->with('success', 'Emails sent successfully!');
        } catch (\Exception $e) {
            \Log::error('Email sending failed: '.$e->getMessage());

            foreach ($recipients as $recipient) {
                if (empty($recipient['email'])) {
                    continue;
                }

                EmailLog::create([
                    'email' => $recipient['email'],
                    'name' => $recipient['name'],
                    'subject' => $subject,
                    'body' => $text,
                    'header_image' => $headerImagePath,
                    'header_image_url' => $headerImageUrl,
                    'button_url' => $buttonUrl,
                    'button_text' => $buttonText,
                    'attachments' => json_encode($attachmentPaths ?? []),
                    'status' => 'FAILED',
                    'sent_at' => now(),
                    'created_by' => $userId,
                ]);
            }

            return redirect()->back()->with('error', 'Failed to send emails. Please try again later.');
        }
    }


    // public function sendEmail(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'roles' => 'required|array',
    //             'subject' => 'required|string',
    //             'text' => 'required|string',
    //             'attachments.*' => 'nullable|file',
    //             'header_image' => 'nullable|image',
    //             'header_image_url' => 'nullable|url',
    //             'button_url' => 'nullable|url', // Validate button URL
    //             'button_text' => 'nullable|string', // Validate button text
    //         ]);
    
    //         $subject = $request->input('subject');
    //         $text = $request->input('text');
    //         $roles = $request->input('roles');
    //         $attachments = $request->file('attachments');
    //         $headerImage = $request->file('header_image');
    //         $headerImageUrl = $request->input('header_image_url');
    //         $buttonUrl = $request->input('button_url');
    //         $buttonText = $request->input('button_text');
    //         $userId = auth()->id();
    
    //         $headerImagePath = $headerImage ? $headerImage->store('header_images') : null;
    
    //         $recipients = [];
    
    //         if (in_array('Driver', $roles)) {
    //             // Get drivers whose associated company has 'Active' status
    //             $drivers = Driver::with('companyDetails')
    //                             ->whereHas('companyDetails', function ($query) {
    //                                 $query->where('company_status', 'Active');
    //                             })
    //                             ->select('contact_email as email', 'name', 'company_id')
    //                             ->get()
    //                             ->toArray();
    //             $recipients = array_merge($recipients, $drivers);
    //         }
    
    //         if (in_array('Operator', $roles)) {
    //             // Fetch operators whose company status is 'Active'
    //             $operators = CompanyDetails::where('company_status', 'Active')
    //                                         ->select('email', 'name')
    //                                         ->get()
    //                                         ->toArray();
            
    //             $recipients = array_merge($recipients, $operators);
    //         }
            
    
    //         if (in_array('NewsLetterEmail', $roles)) {
    //             $newsletterEmails = NewsLetterEmail::select('email', 'name')->get()->toArray();
    //             $recipients = array_merge($recipients, $newsletterEmails);
    //         }
    
    //         foreach ($recipients as $recipient) {
    //             if (empty($recipient['email'])) {
    //                 continue;
    //             }
    
    //             $personalizedText = str_replace('{name}', $recipient['name'], $text);
    
    //             // Check if it's a driver and its company has 'Active' status
    //             if (isset($recipient['company_id'])) {
    //                 $companyDetails = \App\Models\CompanyDetails::find($recipient['company_id']);
    //                 if (!$companyDetails || $companyDetails->company_status !== 'Active') {
    //                     continue; // Skip sending email to this driver if the company status is not 'Active'
    //                 }
    //             }
    
    //             $mail = new GenericEmail(
    //                 $subject,
    //                 $personalizedText,
    //                 $recipient['name'],
    //                 null,
    //                 $headerImagePath ? storage_path($headerImagePath) : null,
    //                 $headerImageUrl,
    //                 $buttonUrl,
    //                 $buttonText,
    //                 storage_path('/uploads/logo/5-logo-dark.png')
    //             );
    
    //             $attachmentPaths = [];
    
    //             if ($attachments) {
    //                 foreach ($attachments as $attachment) {
    //                     $attachmentPath = $attachment->store('attachments');
    //                     $fullPath = storage_path($attachmentPath);
    
    //                     if (file_exists($fullPath)) {
    //                         $attachmentPaths[] = $attachmentPath;
    //                         $mail->attach($fullPath);
    //                     } else {
    //                         \Log::warning('Attachment not found: ' . $fullPath);
    //                     }
    //                 }
    //             }
    
    //             \Mail::to($recipient['email'])->send($mail);
    
    //             EmailLog::create([
    //                 'email' => $recipient['email'],
    //                 'name' => $recipient['name'],
    //                 'subject' => $subject,
    //                 'body' => $text,
    //                 'header_image' => $headerImagePath,
    //                 'header_image_url' => $headerImageUrl,
    //                 'button_url' => $buttonUrl,
    //                 'button_text' => $buttonText,
    //                 'attachments' => json_encode($attachmentPaths),
    //                 'status' => 'SEND',
    //                 'sent_at' => now(),
    //                 'created_by' => $userId,
    //             ]);
    //         }
    
    //         return redirect()->back()->with('success', 'Emails sent successfully!');
    //     } catch (\Exception $e) {
    //         \Log::error('Email sending failed: '.$e->getMessage());
    
    //         foreach ($recipients as $recipient) {
    //             if (empty($recipient['email'])) {
    //                 continue;
    //             }
    
    //             EmailLog::create([
    //                 'email' => $recipient['email'],
    //                 'name' => $recipient['name'],
    //                 'subject' => $subject,
    //                 'body' => $text,
    //                 'header_image' => $headerImagePath,
    //                 'header_image_url' => $headerImageUrl,
    //                 'button_url' => $buttonUrl,
    //                 'button_text' => $buttonText,
    //                 'attachments' => json_encode($attachmentPaths ?? []),
    //                 'status' => 'FAILED',
    //                 'sent_at' => now(),
    //                 'created_by' => $userId,
    //             ]);
    //         }
    
    //         return redirect()->back()->with('error', 'Failed to send emails. Please try again later.');
    //     }
    // }


    public function resendEmail()
    {
        try {
            // Fetch all email logs with status 'FAILED'
            $failedLogs = EmailLog::where('status', 'FAILED')->get();

            foreach ($failedLogs as $emailLog) {
                try {
                    $email = $emailLog->email;
                    $subject = $emailLog->subject;
                    $text = $emailLog->body; // Original text with placeholder
                    $recipientName = $emailLog->name;

                    // Replace the {name} placeholder with the recipient's name
                    $personalizedText = str_replace('{name}', $recipientName, $text);

                    // Create a new email instance with additional parameters for header image and button
                    $mail = new GenericEmail(
                        $subject,
                        $personalizedText,
                        $recipientName,
                        null, // Pass null if no attachment path is needed
                        $emailLog->header_image ? storage_path($emailLog->header_image) : null,
                        $emailLog->header_image_url,
                        $emailLog->button_url,
                        $emailLog->button_text,
                                            storage_path('/uploads/logo/5-logo-dark.png')
                    );

                    // Attach files if they exist
                    $attachments = json_decode($emailLog->attachments, true);
                    if (is_array($attachments)) {
                        foreach ($attachments as $attachmentPath) {
                            $fullPath = storage_path($attachmentPath);

                            // Check if the file exists before attaching
                            if (file_exists($fullPath)) {
                                $mail->attach($fullPath); // Attach the file to the email
                            } else {
                                \Log::warning('Attachment not found for email resend: ' . $fullPath);
                            }
                        }
                    }

                    // Send the email
                    \Mail::to($email)->send($mail);

                    // Update the email log status
                    $emailLog->update([
                        'status' => 'SEND',
                        'sent_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Email resend failed for email '.$emailLog->email.': '.$e->getMessage());

                    // Update the log entry to reflect failure
                    $emailLog->update([
                        'status' => 'FAILED',
                        'sent_at' => now(),
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Resent emails successfully!');
        } catch (\Exception $e) {
            \Log::error('Failed emails resend failed: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to resend failed emails. Please try again later.');
        }
    }
    
        public function resendEmailCronJob()
{
    try {
        // Fetch all email logs with status 'FAILED'
        $failedLogs = EmailLog::where('status', 'FAILED')->get();

        $successCount = 0;
        $failedCount = 0;

        foreach ($failedLogs as $emailLog) {
            try {
                $email = $emailLog->email;
                $subject = $emailLog->subject;
                $text = $emailLog->body;
                $recipientName = $emailLog->name;

                $personalizedText = str_replace('{name}', $recipientName, $text);

                $mail = new GenericEmail(
                    $subject,
                    $personalizedText,
                    $recipientName,
                    null,
                    $emailLog->header_image ? storage_path($emailLog->header_image) : null,
                    $emailLog->header_image_url,
                    $emailLog->button_url,
                    $emailLog->button_text,
                    storage_path('/uploads/logo/5-logo-dark.png')
                );

                // Attachments
                $attachments = json_decode($emailLog->attachments, true);
                if (is_array($attachments)) {
                    foreach ($attachments as $attachmentPath) {
                        $fullPath = storage_path($attachmentPath);
                        if (file_exists($fullPath)) {
                            $mail->attach($fullPath);
                        } else {
                            \Log::warning('Attachment not found for email resend: ' . $fullPath);
                        }
                    }
                }

                \Mail::to($email)->send($mail);

                $emailLog->update([
                    'status' => 'SEND',
                    'sent_at' => now(),
                ]);

                $successCount++;
            } catch (\Exception $e) {
                \Log::error('Email resend failed for email ' . $emailLog->email . ': ' . $e->getMessage());

                $emailLog->update([
                    'status' => 'FAILED',
                    'sent_at' => now(),
                ]);

                $failedCount++;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Resent emails completed.',
            'success_count' => $successCount,
            'failed_count' => $failedCount,
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Failed emails resend failed: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to resend failed emails. Please try again later.',
        ], 500);
    }
}

    public function importFile()
    {
        return view('newsletter.import');

    }

    // public function import(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|file|mimes:xlsx,xls,csv',
    //     ]);

    //     Excel::import(new OtherEmailImport, $request->file('file'));

    //     return redirect()->back()->with('success', 'Emails imported successfully!');
    // }

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

        // Delete existing records
        NewsLetterEmail::truncate(); // This deletes all records from the table

        // Import new data
        $newsletter = (new OtherEmailImport)->toArray($request->file('file'))[0];
        $totalProduct = count($newsletter) - 1;
        $errorArray = [];
        $successCount = 0;

        foreach ($newsletter as $key => $items) {
            // Skip header row
            if ($key === 0) {
                continue;
            }

            // Check if email already exists
            $existingEmail = NewsLetterEmail::where('email', $items[1])->first();
            if ($existingEmail) {
                // Skip importing this record
                $errorArray[] = [
                    'name' => $items[0] ?? null,
                    'email' => $items[1] ?? null,
                    'reason' => 'Email already exists in the database.',
                ];

                continue;
            }

            // Create new record
            $newsletterService = new NewsLetterEmail();
            $newsletterService->name = $items[0] ?? null;
            $newsletterService->email = $items[1] ?? null;
            $newsletterService->created_by = \Auth::user()->id;

            $newsletterService->save();
            $successCount++;
        }

        if (empty($errorArray)) {
            $data['status'] = 'success';
            $data['msg'] = __('All Emails successfully imported');
        } else {
            $data['status'] = 'error';
            $data['msg'] = count($errorArray).' '.__('Record(s) failed to import out of').' '.$totalProduct.' '.__('record(s)');

            \Session::put('errorArray', $errorArray);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }

    public function show()
    {
        // Fetch all newsletter emails
        $emails = NewsLetterEmail::all();

        // Return the view with the email data
        return view('newsletter.show')->with('emails', $emails);
    }

    public function destroy($id)
    {
        $emails = NewsLetterEmail::findOrFail($id);
        $emails->delete();

        // Optionally, redirect back with a success message
        return redirect()->back()->with('success', 'Email Data deleted successfully');
    }
    
    public function emaillogsExport(Request $request)
    {
        // Fetch all data from the Driver model
        $data = \App\Models\EmailLog::get();

        // Adjust the export logic as per your requirement
        $name = 'NewsLetter Email Logs_'.date('d-m-Y');

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\EmailLogsExport($data), $name.'.xlsx');
    }
}
