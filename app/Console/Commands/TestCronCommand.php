<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestCronCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test cron job functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing cron job functionality...');
        
        // Test configuration
        $this->info('Cron enabled: ' . (config('pms.cron.enabled') ? 'Yes' : 'No'));
        $this->info('Full sync interval: ' . config('pms.cron.full_sync_interval', 'everyFiveMinutes'));
        $this->info('Incremental sync interval: ' . config('pms.cron.incremental_sync_interval', 'hourly'));
        $this->info('Incremental since: ' . config('pms.cron.incremental_since', '1 hour ago'));
        
        // Log test message
        Log::info('Cron test executed successfully', [
            'timestamp' => now(),
            'config' => config('pms.cron')
        ]);
        
        $this->info('Cron test completed successfully!');
        return 0;
    }
} 