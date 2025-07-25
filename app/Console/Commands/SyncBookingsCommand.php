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

    /**
     * Execute the console command.
     */
    public function handle(PmsApiService $apiService, BookingSyncService $syncService)
    {
        $this->info('Starting PMS booking synchronization...');

        try {
            $since = $this->option('since');
            $this->syncBookings($apiService, $syncService, $since);
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
    private function syncBookings(PmsApiService $apiService, BookingSyncService $syncService, ?string $since = null): void
    {
        $this->info('Fetching booking IDs from PMS API...');
        
        $bookingIds = $apiService->getBookingIds($since);
        $totalBookings = count($bookingIds);

        if ($totalBookings === 0) {
            $this->info('No bookings found to sync.');
            return;
        }

        $this->info("Found {$totalBookings} bookings to sync.");

        $progressBar = $this->output->createProgressBar($totalBookings);
        $progressBar->start();

        $processed = 0;
        $errors = 0;

        foreach ($bookingIds as $bookingId) {
            try {
                $syncService->syncBooking($bookingId, $apiService);
                $processed++;
            } catch (\Exception $e) {
                $errors++;
                Log::error("Failed to sync booking {$bookingId}", [
                    'error' => $e->getMessage(),
                    'booking_id' => $bookingId
                ]);
                $this->newLine();
                $this->warn("Failed to sync booking {$bookingId}: " . $e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Sync completed: {$processed} processed, {$errors} errors");
    }
} 