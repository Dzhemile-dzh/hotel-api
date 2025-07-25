<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PmsApiService
{
    /**
     * Get all booking IDs from the PMS API
     */
    public function getBookingIds(?string $since = null): array
    {
        $url = config('pms.api.base_url') . '/bookings';
        if ($since) {
            $url .= "?updated_at.gt={$since}";
        }

        $response = Http::timeout(config('pms.api.timeout'))
            ->retry(config('pms.api.retry_attempts'), 100)
            ->get($url);
        
        if (!$response->successful()) {
            throw new \Exception("Failed to fetch bookings: " . $response->status());
        }

        $this->rateLimit();
        return $response->json('data', []);
    }

    /**
     * Get booking details by ID
     */
    public function getBookingDetails(int $bookingId): array
    {
        $response = Http::timeout(config('pms.api.timeout'))
            ->retry(config('pms.api.retry_attempts'), 100)
            ->get(config('pms.api.base_url') . "/bookings/{$bookingId}");
        
        if (!$response->successful()) {
            throw new \Exception("Failed to fetch booking {$bookingId}: " . $response->status());
        }

        $this->rateLimit();
        return $response->json();
    }

    /**
     * Get room details by ID
     */
    public function getRoomDetails(int $roomId): array
    {
        $response = Http::timeout(config('pms.api.timeout'))
            ->retry(config('pms.api.retry_attempts'), 100)
            ->get(config('pms.api.base_url') . "/rooms/{$roomId}");
        
        if (!$response->successful()) {
            throw new \Exception("Failed to fetch room {$roomId}: " . $response->status());
        }

        $this->rateLimit();
        return $response->json();
    }

    /**
     * Get room type details by ID
     */
    public function getRoomTypeDetails(int $roomTypeId): array
    {
        $response = Http::timeout(config('pms.api.timeout'))
            ->retry(config('pms.api.retry_attempts'), 100)
            ->get(config('pms.api.base_url') . "/room-types/{$roomTypeId}");
        
        if (!$response->successful()) {
            throw new \Exception("Failed to fetch room type {$roomTypeId}: " . $response->status());
        }

        $this->rateLimit();
        return $response->json();
    }

    /**
     * Get guest details by ID
     */
    public function getGuestDetails(int $guestId): array
    {
        $response = Http::timeout(config('pms.api.timeout'))
            ->retry(config('pms.api.retry_attempts'), 100)
            ->get(config('pms.api.base_url') . "/guests/{$guestId}");
        
        if (!$response->successful()) {
            throw new \Exception("Failed to fetch guest {$guestId}: " . $response->status());
        }

        $this->rateLimit();
        return $response->json();
    }

    /**
     * Get multiple guests details by IDs
     */
    public function getGuestsDetails(array $guestIds): array
    {
        $guestsData = [];
        
        foreach ($guestIds as $guestId) {
            try {
                $guestsData[] = $this->getGuestDetails($guestId);
            } catch (\Exception $e) {
                Log::warning("Failed to fetch guest {$guestId}: " . $e->getMessage());
            }
        }

        return $guestsData;
    }

    /**
     * Apply rate limiting delay
     */
    private function rateLimit(): void
    {
        $delay = 1 / config('pms.api.rate_limit'); // Convert requests per second to delay
        usleep($delay * 1000000);
    }
} 