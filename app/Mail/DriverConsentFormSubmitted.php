<?php

namespace App\Mail;

use App\Models\DriverConsentForm;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DriverConsentFormSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $driverConsentForm;

    public function __construct(DriverConsentForm $driverConsentForm)
    {
        $this->driverConsentForm = $driverConsentForm;
    }

    public function build()
    {
        $downloadUrl = route('driverconsent.pdf.download', ['id' => $this->driverConsentForm->id]);


        return $this->subject('Driver Consent Form Submitted')
        ->markdown('emails.driver_consent_form', ['downloadUrl' => $downloadUrl]);

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
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
