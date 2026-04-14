<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PolicyRequestNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $operatorName;
    public $companyName;
    public $policies;

    public function __construct($operatorName, $companyName, $policies)
    {
        $this->operatorName = $operatorName;
        $this->companyName = $companyName;
        $this->policies = $policies;
    }

    public function build()
    {
        return $this->subject('Policy Access Request')
                    ->markdown('emails.policy_request_notification')
                    ->with([
                        'OperatorName' => $this->operatorName,
                        'CompanyName' => $this->companyName,
                        'PolicyList' => $this->policies,
                    ]);
    }
}
