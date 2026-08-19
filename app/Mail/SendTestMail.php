<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $emailSubject;
    public string $emailBody;

    public function __construct(string $subject, string $body)
    {
        $this->emailSubject = $subject;
        $this->emailBody    = $body;
    }

    public function build(): static
    {
        return $this->subject($this->emailSubject)
                    ->view('emails.send_test_mail')
                    ->with([
                        'emailBody' => $this->emailBody,
                    ]);
    }
}
