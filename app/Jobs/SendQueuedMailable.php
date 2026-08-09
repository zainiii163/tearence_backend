<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Queue outbound mail so signup/reset stay fast under load.
 */
class SendQueuedMailable implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $to,
        public object $mailable
    ) {}

    public function handle(): void
    {
        Mail::to($this->to)->send($this->mailable);
    }

    public function failed(Throwable $e): void
    {
        Log::error('Queued mail failed', [
            'to' => $this->to,
            'error' => $e->getMessage(),
        ]);
    }
}
