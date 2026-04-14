<?php

namespace App\Mail;

use App\Models\WorkAroundStore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkAroundComplete extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $workAroundStore;

    public function __construct(WorkAroundStore $workAroundStore)
    {
        $this->workAroundStore = $workAroundStore;
    }

    public function build()
    {
       
        $driverName = $this->workAroundStore->driver->name ?? 'N/A';
        $vehicleRegistration = $this->workAroundStore->vehicle->registrations ?? 'N/A'; // Assuming you have this relationship

        return $this->subject("WalkAround Report #{$this->workAroundStore->id} - {$driverName} - {$vehicleRegistration}")
                    ->markdown('emails.walkaroundcomplete') // Create this Blade view for the email
                    ->with([
                        'id' => $this->workAroundStore->id,
                        'duration' => $this->workAroundStore->duration,
                        'defect_count' => $this->workAroundStore->defects_count ?? 0,
                        'driver_name' => $driverName,
                        'vehicle' => $vehicleRegistration
                    ]);
    }

    /**
     * Get the message envelope.
     */
    // public function envelope(): Envelope
    // {
    //     return new Envelope(
    //         subject: 'WalkAround Complete',
    //     );
    // }

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
