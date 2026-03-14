<?php

namespace App\Mail;

use App\Models\JobAlert;
use App\Models\JobListing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class JobAlertNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public JobAlert $alert,
        public Collection $jobs
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Jobs Matching Your Alert: {$this->alert->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.job-alert-notification',
            with: [
                'alert' => $this->alert,
                'jobs' => $this->jobs,
                'totalJobs' => $this->jobs->count(),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
