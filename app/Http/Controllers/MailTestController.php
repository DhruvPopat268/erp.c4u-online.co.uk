<?php

namespace App\Http\Controllers;

use App\Mail\SendTestMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailTestController extends Controller
{
    /**
     * Show the test mail form.
     */
    public function index()
    {
        // Only super admin can access this page
        if (\Auth::user()->type !== 'super admin') {
            abort(404);
        }

        $smtpInfo = [
            'host'       => config('mail.mailers.smtp.host'),
            'port'       => config('mail.mailers.smtp.port'),
            'encryption' => config('mail.mailers.smtp.encryption'),
            'username'   => config('mail.mailers.smtp.username'),
            'from'       => config('mail.from.address'),
            'from_name'  => config('mail.from.name'),
        ];

        return view('mail.index', compact('smtpInfo'));
    }

    /**
     * Send the test email using current .env SMTP credentials.
     */
    public function send(Request $request)
    {
        // Only super admin can send test emails
        if (\Auth::user()->type !== 'super admin') {
            abort(404);
        }

        $request->validate([
            'to'      => ['required', 'email'],
            'subject' => ['required', 'string', 'max:255'],
            'body'    => ['required', 'string'],
        ]);

        try {
            Mail::to($request->to)->send(
                new SendTestMail($request->subject, $request->body)
            );

            return redirect()->route('mail.index')
                ->with('success', 'Email sent successfully to ' . $request->to);

        } catch (\Exception $e) {
            return redirect()->route('mail.index')
                ->withInput()
                ->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }
}
