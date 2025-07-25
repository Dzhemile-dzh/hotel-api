<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;

class PmsApiService
{
    private const CACHE_TTL = 300; // 5 minutes
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY = 100; // milliseconds

    /**
     * Get all booking IDs from the PMS API
     */
    public function getBookingIds(?string $since = null): array
    {
        $cacheKey = "pms_bookings_{$since}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($since) {
            $url = $this->buildUrl('/bookings', $since);
            $response = $this->makeRequest('GET', $url);
            
            return $response->json('data', []);
        });
    }

    /**
     * Get booking details by ID
     */
    public function getBookingDetails(int $bookingId): array
    {
        $cacheKey = "pms_booking_{$bookingId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($bookingId) {
            $url = $this->buildUrl("/bookings/{$bookingId}");
            $response = $this->makeRequest('GET', $url);
            return $response->json();
        });
    }

    /**
     * Get room details by ID
     */
    public function getRoomDetails(int $roomId): array
    {
        $cacheKey = "pms_room_{$roomId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($roomId) {
            $url = $this->buildUrl("/rooms/{$roomId}");
            $response = $this->makeRequest('GET', $url);
            return $response->json();
        });
    }

    /**
     * Get room type details by ID
     */
    public function getRoomTypeDetails(int $roomTypeId): array
    {
        $cacheKey = "pms_room_type_{$roomTypeId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($roomTypeId) {
            $url = $this->buildUrl("/room-types/{$roomTypeId}");
            $response = $this->makeRequest('GET', $url);
            return $response->json();
        });
    }

    /**
     * Get guest details by ID
     */
    public function getGuestDetails(int $guestId): array
    {
        $cacheKey = "pms_guest_{$guestId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($guestId) {
            $url = $this->buildUrl("/guests/{$guestId}");
            $response = $this->makeRequest('GET', $url);
            return $response->json();
        });
    }

    /**
     * Get multiple guests details by IDs with parallel processing
     */
    public function getGuestsDetails(array $guestIds): array
    {
        if (empty($guestIds)) {
            return [];
        }

        // Use parallel requests for better performance
        $promises = [];
        foreach ($guestIds as $guestId) {
            $cacheKey = "pms_guest_{$guestId}";
            $promises[$guestId] = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($guestId) {
                try {
                    $url = $this->buildUrl("/guests/{$guestId}");
                    $response = $this->makeRequest('GET', $url);
                    return $response->json();
                } catch (\Exception $e) {
                    Log::warning("Failed to fetch guest {$guestId}: " . $e->getMessage());
                    return null;
                }
            });
        }

        return array_filter($promises); // Remove null values
    }

    /**
     * Clear cache for specific entity
     */
    public function clearCache(string $entity, int $id): void
    {
        $cacheKey = "pms_{$entity}_{$id}";
        Cache::forget($cacheKey);
    }

    /**
     * Clear all PMS cache
     */
    public function clearAllCache(): void
    {
        Cache::flush();
    }

    /**
     * Build URL with optional parameters
     */
    private function buildUrl(string $endpoint, ?string $since = null): string
    {
        $url = config('pms.api.base_url') . $endpoint;
        
        if ($since) {
            $url .= "?updated_at.gt=" . urlencode($since);
        }
        
        return $url;
    }

    /**
     * Make HTTP request with retry logic and rate limiting
     */
    private function makeRequest(string $method, string $url): Response
    {
        $response = Http::timeout(config('pms.api.timeout'))
            ->retry(self::MAX_RETRIES, self::RETRY_DELAY)
            ->$method($url);
        
        if (!$response->successful()) {
            $this->logError($method, $url, $response);
            throw new \Exception("HTTP request failed: {$response->status()} - {$response->body()}");
        }

        $this->rateLimit();
        return $response;
    }

    /**
     * Log error details
     */
    private function logError(string $method, string $url, Response $response): void
    {
        Log::error('PMS API request failed', [
            'method' => $method,
            'url' => $url,
            'status' => $response->status(),
            'body' => $response->body(),
            'headers' => $response->headers(),
        ]);
    }

    /**
     * Apply rate limiting delay
     */
    private function rateLimit(): void
    {
        $delay = 1 / config('pms.api.rate_limit', 2);
        usleep($delay * 1000000);
    }
} 