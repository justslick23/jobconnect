<?php

namespace App\Mail;

use App\Models\JobRequisition;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewJobPosted extends Mailable
{
    use Queueable, SerializesModels;

    public $job;

    public function __construct(JobRequisition $job)
    {
        $this->job = $job;
    }

    public function build()
    {
        return $this->subject('New Job Opportunity: ' . $this->job->title)
                    ->view('emails.jobs.new'); // <- use plain view
    }
}
