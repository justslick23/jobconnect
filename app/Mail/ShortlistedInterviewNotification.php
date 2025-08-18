<?php

namespace App\Mail;

use App\Models\JobApplication;
use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ShortlistedInterviewNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $interview;

    public function __construct(JobApplication $application, Interview $interview)
    {
        $this->application = $application;
        $this->interview = $interview;
    }

    public function build()
    {
        return $this->subject('You have been Shortlisted for Interview')
                    ->view('emails.shortlisted_interview');
    }
}
