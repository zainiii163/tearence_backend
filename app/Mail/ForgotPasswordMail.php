<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $name;
    protected $password;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $password)
    {
        $this->name = $name;
        $this->password = $password;
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
                   ->view('auth.forgot-password')
                   ->subject('Reset Password')
                   ->with(
                    [
                        'name' => $this->name,
                        'password' => $this->password,
                    ]);
    }
}
