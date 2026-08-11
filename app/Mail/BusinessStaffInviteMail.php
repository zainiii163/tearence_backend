<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BusinessStaffInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $inviteeEmail,
        public string $businessName,
        public string $inviterName,
        public string $role,
        public string $signupUrl,
        public string $acceptUrl
    ) {
    }

    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
            ->subject("You're invited to manage {$this->businessName} on Worldwide Adverts")
            ->view('emails.business-staff-invite')
            ->with([
                'inviteeEmail' => $this->inviteeEmail,
                'businessName' => $this->businessName,
                'inviterName' => $this->inviterName,
                'role' => $this->role,
                'signupUrl' => $this->signupUrl,
                'acceptUrl' => $this->acceptUrl,
            ]);
    }
}
