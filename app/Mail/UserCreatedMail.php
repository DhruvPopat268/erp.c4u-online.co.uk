<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $user;
    public $password;
    public $companyname; // Add companyname property


    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string $password
     */
    public function __construct(\App\Models\User $user, $password,$companyname)
    {
        $this->user = $user;
        $this->password = $password;
        $this->companyname = $companyname; // Assign companyname

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Welcome to PTC ERP - Account Details and Access Information')
            ->markdown('emails.user_created')
            ->with([
                'user' => $this->user,
                'password' => $this->password,
                'companyname' => $this->companyname, // Pass companyname to the view
            ]);
    }
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to PTC ERP - Account Details and Access Information'
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
