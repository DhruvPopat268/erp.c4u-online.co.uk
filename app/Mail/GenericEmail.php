<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// Import your Attachment model if not already imported

class GenericEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $text;
    public $name;
    public $attachmentPath;
    public $headerImage;
    public $headerImageUrl;
    public $buttonUrl;
    public $buttonText;
    public $logo;

    public function __construct($subject, $text, $name, $attachmentPath = null, $headerImage = null, $headerImageUrl = null, $buttonUrl = null, $buttonText = null, $logo = null)
    {
        $this->subject = $subject;
        $this->text = $text;
        $this->name = $name;
        $this->attachmentPath = $attachmentPath;
        $this->headerImage = $headerImage;
        $this->headerImageUrl = $headerImageUrl;
        $this->buttonUrl = $buttonUrl;
        $this->buttonText = $buttonText;
        $this->logo = $logo;
    }

    public function build()
    {
        $mail = $this->subject($this->subject)
        
            ->markdown('emails.generic')
            ->with([
                'text' => $this->text,
                'name' => $this->name,
                'headerImage' => $this->headerImage,
                'headerImageUrl' => $this->headerImageUrl,
                'buttonUrl' => $this->buttonUrl,
                'buttonText' => $this->buttonText,
                'logo' => $this->logo,
            ]);

        if ($this->attachmentPath) {
            $mail->attach($this->attachmentPath);
        }

        return $mail;
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.generic', // Correct view name for the email content
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
