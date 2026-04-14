<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DriverUserWithForm extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $username;
    public $password;
    public $contactEmail;
    public $driver;

    public function __construct($username, $password, $contactEmail, $driver)
    {
        $this->username = $username;
        $this->password = $password;
        $this->contactEmail = $contactEmail;
        $this->driver = $driver;
    }

    public function build()
    {
        return $this->markdown('emails.driver_user_with_form')
                    ->subject('Your Driver Account Created');
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
