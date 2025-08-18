<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationNotShortlistedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $applicantName;
    public $jobTitle;

    /**
     * Create a new message instance.
     */
    public function __construct($applicantName, $jobTitle)
    {
        $this->applicantName = $applicantName;
        $this->jobTitle = $jobTitle;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Application Update - ' . $this->jobTitle)
                    ->view('emails.application-not-shortlisted');
    }
}
