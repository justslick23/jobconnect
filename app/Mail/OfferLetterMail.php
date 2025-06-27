<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferLetterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $messageBody;
    public $filePath;

    public function __construct($user, $messageBody = '', $filePath)
    {
        $this->user = $user;
        $this->messageBody = $messageBody;
        $this->filePath = $filePath;
    }

    public function build()
    {
        return $this->view('emails.offer_letter')
            ->with([
                'user' => $this->user,
                'messageBody' => $this->messageBody,
            ])
            ->attach(storage_path('app/' . $this->filePath));
    }
}
