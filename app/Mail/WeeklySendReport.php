<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklySendReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $companyData;

    public $drivers;

    public function __construct($companyData, $drivers)
    {
        $this->companyData = $companyData;
        $this->drivers = $drivers;
    }

    public function build()
    {
        $yesterday = Carbon::yesterday()->toFormattedDateString(); // Get yesterday's date in a readable format
        $companyName = $this->companyData['companyName']; // Assuming companyData is an array and 'name' contains the company name
        $subject = "{$companyName} Weekly Report {$yesterday}";

        // Attach files to the email
        foreach ($this->drivers as $driver) {
            $files = json_decode($driver->files, true); // Assuming files is JSON stored in DB

            foreach ($files as $filename) {
                $path = storage_path('weekly-email-report/'.$filename); // Adjust path as per your file storage logic

                if (file_exists($path)) {
                    $this->attach($path, ['as' => $filename]);
                }
            }
        }

        return $this->subject($subject)
            ->markdown('emails.weeklyreport')
            ->with('companyData', $this->companyData);
    }

    public function envelope(): Envelope
    {
        $companyName = $this->companyData['companyName']; // Ensure to get the company name here as well
        $yesterday = Carbon::yesterday()->toFormattedDateString(); // Get yesterday's date

        return new Envelope(
            subject: "{$companyName} Weekly Report {$yesterday}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
