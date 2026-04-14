<?php

namespace App\Mail;

use App\Models\CompanyDetails;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MedicalInsuranceReminderToCompany extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $company;

    public $drivers;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(CompanyDetails $company, array $drivers)
    {
        $this->company = $company;
        $this->drivers = $drivers;
    }

    public function build()
    {
        return $this->markdown('emails.medical_insurance_reminder_to_company')
        ->subject('Medical Insurance Reminder To Company ');

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Medical Insurance Reminder To Company',
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
