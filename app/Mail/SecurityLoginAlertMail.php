<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SecurityLoginAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $alertMessage,
        public array $details = []
    ) {}

    public function build()
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        return $this->from($fromAddress, $fromName)
            ->subject('[WWA Security] Login activity alert')
            ->view('emails.security-login-alert')
            ->with([
                'alertMessage' => $this->alertMessage,
                'details' => $this->details,
            ]);
    }
}
