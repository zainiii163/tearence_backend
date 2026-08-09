<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerContactEnquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $sellerName,
        public string $listingTitle,
        public string $buyerName,
        public string $buyerEmail,
        public ?string $buyerPhone,
        public string $contactMethod,
        public string $enquiryMessage,
        public ?string $listingUrl = null,
    ) {}

    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
            ->replyTo($this->buyerEmail, $this->buyerName)
            ->subject('Buyer enquiry: '.$this->listingTitle)
            ->view('emails.seller-contact-enquiry');
    }
}
