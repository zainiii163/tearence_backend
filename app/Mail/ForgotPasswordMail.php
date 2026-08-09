<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected string $name,
        protected string $resetUrl
    ) {}

    public function build()
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        return $this->from($fromAddress, $fromName)
            ->subject('Reset your Worldwide Adverts password')
            ->view('emails.forgot-password-link')
            ->with([
                'name' => $this->name,
                'resetUrl' => $this->resetUrl,
            ]);
    }
}
