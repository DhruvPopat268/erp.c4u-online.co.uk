<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrainingAssigned extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $driver;
    public $training;
    public $fromDate;
    public $toDate;
    public $icsFilePath;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($driver, $training, $fromDate, $toDate, $icsFilePath = null)
    {
        $this->driver = $driver;
        $this->training = $training;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->icsFilePath = $icsFilePath;
    }

    public function build()
    {
        $subject = 'Upcoming Training Course Reminder: ' . $this->training->trainingCourse->name;

        $email = $this->markdown('emails.training_assigned')
            ->subject($subject)
            ->with([
                'driver' => $this->driver,
                'training' => $this->training,
                'fromDate' => $this->fromDate,
                'toDate' => $this->toDate,
            ]);

        if ($this->icsFilePath) {
            $email->attach($this->icsFilePath, [
                'as' => 'training_details.ics',
                'mime' => 'text/calendar',
            ]);
        }

        return $email;
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
