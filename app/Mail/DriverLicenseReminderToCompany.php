<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DriverLicenseReminderToCompany extends Mailable
{
    use Queueable, SerializesModels;

    public $emailData;

public function __construct(array $emailData) // Accept an array
    {
        $this->emailData = $emailData;
    }

    public function build()
    {
                return $this->markdown('emails.driver_license_reminder_to_company', ['emailData' => $this->emailData]);

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Driver License Reminder To Company',
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
