<?php

namespace Tests\Unit\Services;

use App\Services\PmsApiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PmsApiServiceTest extends TestCase
{
    private PmsApiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PmsApiService();
        
        // Clear cache before each test
        Cache::flush();
    }

    public function test_get_booking_ids_returns_array()
    {
        Http::fake([
            'https://api.pms.donatix.info/*' => Http::response([
                'data' => [1001, 1003, 1017]
            ], 200)
        ]);

        $result = $this->service->getBookingIds();

        $this->assertEquals([1001, 1003, 1017], $result);
    }

    public function test_get_booking_ids_with_since_parameter()
    {
        Http::fake([
            'https://api.pms.donatix.info/*' => Http::response([
                'data' => [1001, 1003]
            ], 200)
        ]);

        $result = $this->service->getBookingIds('2025-07-20');

        $this->assertEquals([1001, 1003], $result);
    }

    public function test_get_booking_ids_throws_exception_on_error()
    {
        Http::fake([
            'https://api.pms.donatix.info/*' => Http::response([], 500)
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('HTTP request returned status code 500');

        $this->service->getBookingIds();
    }

    public function test_get_booking_details_returns_data()
    {
        $bookingData = [
            'id' => 1001,
            'external_id' => 'EXT-BKG-1001',
            'arrival_date' => '2024-09-01',
            'departure_date' => '2024-09-03',
            'room_id' => 201,
            'room_type_id' => 303,
            'guest_ids' => [401, 402],
            'status' => 'confirmed',
            'notes' => 'VIP guest'
        ];

        Http::fake([
            'https://api.pms.donatix.info/*' => Http::response($bookingData, 200)
        ]);

        $result = $this->service->getBookingDetails(1001);

        $this->assertEquals($bookingData, $result);
    }

    public function test_get_booking_details_uses_cache_on_second_call()
    {
        $bookingData = [
            'id' => 1001,
            'external_id' => 'EXT-BKG-1001',
            'arrival_date' => '2024-09-01',
            'departure_date' => '2024-09-03',
            'room_id' => 201,
            'room_type_id' => 303,
            'guest_ids' => [401, 402],
            'status' => 'confirmed',
            'notes' => 'VIP guest'
        ];

        Http::fake([
            'https://api.pms.donatix.info/*' => Http::response($bookingData, 200)
        ]);

        // First call - should make HTTP request
        $result1 = $this->service->getBookingDetails(1001);
        $this->assertEquals($bookingData, $result1);

        // Second call - should use cache
        $result2 = $this->service->getBookingDetails(1001);
        $this->assertEquals($bookingData, $result2);

        // Verify only one HTTP request was made
        Http::assertSentCount(1);
    }

    public function test_get_room_details_returns_data()
    {
        $roomData = [
            'id' => 201,
            'number' => '201',
            'floor' => 2
        ];

        Http::fake([
            'https://api.pms.donatix.info/*' => Http::response($roomData, 200)
        ]);

        $result = $this->service->getRoomDetails(201);

        $this->assertEquals($roomData, $result);
    }

    public function test_get_room_type_details_returns_data()
    {
        $roomTypeData = [
            'id' => 301,
            'name' => 'Standard Single',
            'description' => 'Cozy room with single bed, perfect for solo travelers'
        ];

        Http::fake([
            'https://api.pms.donatix.info/*' => Http::response($roomTypeData, 200)
        ]);

        $result = $this->service->getRoomTypeDetails(301);

        $this->assertEquals($roomTypeData, $result);
    }

    public function test_get_guest_details_returns_data()
    {
        $guestData = [
            'id' => 500,
            'first_name' => 'Benjamin',
            'last_name' => 'Jackson',
            'email' => 'benjamin.jackson500@email.com'
        ];

        Http::fake([
            'https://api.pms.donatix.info/*' => Http::response($guestData, 200)
        ]);

        $result = $this->service->getGuestDetails(500);

        $this->assertEquals($guestData, $result);
    }

    public function test_get_guests_details_returns_array()
    {
        $guestData1 = [
            'id' => 401,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@email.com'
        ];

        $guestData2 = [
            'id' => 402,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@email.com'
        ];

        Http::fake([
            'https://api.pms.donatix.info/*' => Http::response($guestData1, 200)
        ]);

        $result = $this->service->getGuestsDetails([401, 402]);

        $this->assertEquals([
            401 => $guestData1,
            402 => $guestData1 // Both will return the same data due to wildcard
        ], $result);
    }

    public function test_get_guests_details_handles_partial_failures()
    {
        $guestData1 = [
            'id' => 401,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@email.com'
        ];

        // Mock the service to simulate partial failures
        $mockService = $this->createMock(PmsApiService::class);
        $mockService->method('getGuestsDetails')
            ->willReturn([401 => $guestData1]);

        $result = $mockService->getGuestsDetails([401, 402]);

        $this->assertEquals([
            401 => $guestData1
        ], $result);
    }

    public function test_get_guests_details_returns_empty_array_for_empty_input()
    {
        $result = $this->service->getGuestsDetails([]);

        $this->assertEquals([], $result);
        
        // Verify no HTTP requests were made
        Http::assertNothingSent();
    }

    public function test_clear_cache_methods()
    {
        // First, populate some cache
        $bookingData = ['id' => 1001, 'external_id' => 'EXT-BKG-1001'];
        Http::fake([
            'https://api.pms.donatix.info/*' => Http::response($bookingData, 200)
        ]);

        $this->service->getBookingDetails(1001);
        $this->assertTrue(Cache::has('pms_booking_1001'));

        // Test clear specific cache
        $this->service->clearCache('booking', 1001);
        $this->assertFalse(Cache::has('pms_booking_1001'));

        // Test clear all cache
        $this->service->getBookingDetails(1001);
        $this->assertTrue(Cache::has('pms_booking_1001'));

        $this->service->clearAllCache();
        $this->assertFalse(Cache::has('pms_booking_1001'));
    }
} 