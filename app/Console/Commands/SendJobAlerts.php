<?php

namespace App\Console\Commands;

use App\Models\JobAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SendJobAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:send-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send job alert notifications to users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting job alert processing...');
        
        try {
            // Call the API endpoint to process alerts
            $response = \Http::post(url('/api/jobs/alerts/send'), [], [
                'Authorization' => 'Bearer ' . config('app.job_alert_token', 'cron-job-token')
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $this->info("Job alerts processed successfully!");
                $this->info("Alerts processed: {$data['data']['alerts_processed']}");
                $this->info("Alerts sent: {$data['data']['alerts_sent']}");
            } else {
                $this->error('Failed to process job alerts');
                $this->error($response->body());
            }
        } catch (\Exception $e) {
            $this->error('Error processing job alerts: ' . $e->getMessage());
            Log::error('Job alert command failed: ' . $e->getMessage());
        }
        
        return Command::SUCCESS;
    }
}
