<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegisterMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $name;
    protected $verification_token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $verification_token)
    {
        $this->name = $name;
        $this->verification_token = $verification_token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');
        return $this->from($fromAddress, $fromName)
                   ->view('auth.register')
                   ->subject('Welcome to Worldwideadverts!')
                   ->with(
                    [
                        'name' => $this->name,
                        'verification_token' => $this->verification_token,
                    ]);
    }
}
