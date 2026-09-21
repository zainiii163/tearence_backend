<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected ?string $name,
        protected string $code,
        protected int $expiresIn = 10
    ) {
    }

    public function build()
    {
        $frontendUrl = config('verification.frontend_url', 'https://worldwideadverts.info');
        $verifyUrl = $frontendUrl . '/verify-email?code=' . urlencode($this->code);

        return $this->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Your verification code — Worldwide Adverts')
            ->view('emails.verification-code')
            ->with([
                'name' => $this->name,
                'code' => $this->code,
                'expiresIn' => $this->expiresIn,
                'verifyUrl' => $verifyUrl,
            ]);
    }
}
