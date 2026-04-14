<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendVehicleReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
      public $email;
    public $data;

    /**
     * Create a new job instance.
     *
     * @param string $email
     * @param array $data
     */
    public function __construct(string $email, array $data)
    {
        $this->email = $email;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        \Mail::to($this->email)->send(new \App\Mail\VehicleReminder($this->data));
    }

}
