<?php

namespace App\Console\Commands;

use App\Services\BookingSyncService;
use App\Services\PmsApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncBookingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:bookings {--since= : Sync only bookings updated since this date (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync bookings from PMS API with rate limiting and progress feedback';

    private PmsApiService $apiService;
    private BookingSyncService $syncService;

    public function __construct(PmsApiService $apiService, BookingSyncService $syncService)
    {
        parent::__construct();
        $this->apiService = $apiService;
        $this->syncService = $syncService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting PMS booking synchronization...');

        try {
            $since = $this->option('since');
            $this->syncBookings($since);
            $this->info('Synchronization completed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Synchronization failed: ' . $e->getMessage());
            Log::error('Booking sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Sync all bookings from the PMS API
     */
    private function syncBookings(?string $since = null): void
    {
        $this->info('Fetching booking IDs from PMS API...');
        
        $bookingIds = $this->apiService->getBookingIds($since);
        $totalBookings = count($bookingIds);

        if ($totalBookings === 0) {
            $this->info('No bookings found to sync.');
            return;
        }

        $this->info("Found {$totalBookings} bookings to sync.");

        $progressBar = $this->output->createProgressBar($totalBookings);
        $progressBar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($bookingIds as $bookingId) {
            try {
                $this->syncService->syncBooking($bookingId);
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                Log::error("Failed to sync booking {$bookingId}", [
                    'error' => $e->getMessage(),
                    'booking_id' => $bookingId
                ]);
            }

            $progressBar->advance();
            $this->rateLimit();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Sync completed: {$successCount} successful, {$errorCount} failed");
    }

    /**
     * Apply rate limiting
     */
    private function rateLimit(): void
    {
        $delay = 1 / config('pms.api.rate_limit', 2);
        usleep($delay * 1000000);
    }
} 