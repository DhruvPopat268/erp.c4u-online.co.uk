<?php

namespace App\Mail;

use App\Models\Driver;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MedicalInsuranceReminder extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    public function build()
    {
        return $this->markdown('emails.medical_insurance_reminder')
        ->subject('Medical Insurance Renewal Reminder')
            ->with(['name' => $this->driver->name,
                'driver_dob' => $this->driver->driver_dob,
                'driver_age' => $this->driver->driver_age]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Medical Insurance Reminder',
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
