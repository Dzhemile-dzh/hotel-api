<?php

namespace Tests\Unit\Services;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\BookingSyncService;
use App\Services\PmsApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @var BookingSyncService */
    private $service;

    /** @var \PHPUnit\Framework\MockObject\MockObject|PmsApiService */
    private $apiService;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var \PHPUnit\Framework\MockObject\MockObject|PmsApiService $mock */
        $mock = $this->createMock(PmsApiService::class);
        $this->apiService = $mock;
        $this->service = new BookingSyncService($this->apiService);
    }

    public function test_sync_booking_creates_all_related_data()
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

        $roomData = [
            'id' => 201,
            'number' => '201',
            'floor' => 2
        ];

        $roomTypeData = [
            'id' => 303,
            'name' => 'Deluxe Suite',
            'description' => 'Luxurious suite with premium amenities'
        ];

        $guestsData = [
            401 => [
                'id' => 401,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com'
            ],
            402 => [
                'id' => 402,
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@example.com'
            ]
        ];

        $this->apiService->method('getBookingDetails')
            ->with(1001)
            ->willReturn($bookingData);

        $this->apiService->method('getRoomDetails')
            ->with(201)
            ->willReturn($roomData);

        $this->apiService->method('getRoomTypeDetails')
            ->with(303)
            ->willReturn($roomTypeData);

        $this->apiService->method('getGuestsDetails')
            ->with([401, 402])
            ->willReturn($guestsData);

        $this->service->syncBooking(1001);

        // Assert room type was created
        $this->assertDatabaseHas('room_types', [
            'external_id' => 303,
            'name' => 'Deluxe Suite',
            'description' => 'Luxurious suite with premium amenities'
        ]);

        // Assert room was created
        $this->assertDatabaseHas('rooms', [
            'external_id' => 201,
            'number' => '201',
            'floor' => 2
        ]);

        // Assert guests were created
        $this->assertDatabaseHas('guests', [
            'external_id' => 401,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com'
        ]);

        $this->assertDatabaseHas('guests', [
            'external_id' => 402,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com'
        ]);

        // Assert booking was created
        $this->assertDatabaseHas('bookings', [
            'external_id' => 'EXT-BKG-1001',
            'arrival_date' => '2024-09-01 00:00:00',
            'departure_date' => '2024-09-03 00:00:00',
            'status' => 'confirmed',
            'notes' => 'VIP guest'
        ]);

        // Assert booking-guest relationships were created
        $booking = Booking::where('external_id', 'EXT-BKG-1001')->first();
        $this->assertCount(2, $booking->guests);
    }

    public function test_sync_booking_updates_existing_data()
    {
        // Create existing data
        $roomType = RoomType::create([
            'external_id' => 303,
            'name' => 'Old Name',
            'description' => 'Old description'
        ]);

        $room = Room::create([
            'external_id' => 201,
            'number' => '201',
            'floor' => 1,
            'room_type_id' => $roomType->id
        ]);

        $guest = Guest::create([
            'external_id' => 401,
            'first_name' => 'Old',
            'last_name' => 'Name',
            'email' => 'old@example.com'
        ]);

        $booking = Booking::create([
            'external_id' => 'EXT-BKG-1001',
            'arrival_date' => '2024-08-01',
            'departure_date' => '2024-08-03',
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'status' => 'pending',
            'notes' => 'Old notes'
        ]);

        $booking->guests()->attach($guest->id);

        // Mock API responses with updated data
        $bookingData = [
            'id' => 1001,
            'external_id' => 'EXT-BKG-1001',
            'arrival_date' => '2024-09-01',
            'departure_date' => '2024-09-03',
            'room_id' => 201,
            'room_type_id' => 303,
            'guest_ids' => [401],
            'status' => 'confirmed',
            'notes' => 'Updated notes'
        ];

        $roomData = [
            'id' => 201,
            'number' => '201',
            'floor' => 2
        ];

        $roomTypeData = [
            'id' => 303,
            'name' => 'Updated Name',
            'description' => 'Updated description'
        ];

        $guestsData = [
            401 => [
                'id' => 401,
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'email' => 'updated@example.com'
            ]
        ];

        $this->apiService->method('getBookingDetails')
            ->with(1001)
            ->willReturn($bookingData);

        $this->apiService->method('getRoomDetails')
            ->with(201)
            ->willReturn($roomData);

        $this->apiService->method('getRoomTypeDetails')
            ->with(303)
            ->willReturn($roomTypeData);

        $this->apiService->method('getGuestsDetails')
            ->with([401])
            ->willReturn($guestsData);

        $this->service->syncBooking(1001);

        // Assert data was updated
        $this->assertDatabaseHas('room_types', [
            'external_id' => 303,
            'name' => 'Updated Name',
            'description' => 'Updated description'
        ]);

        $this->assertDatabaseHas('rooms', [
            'external_id' => 201,
            'floor' => 2
        ]);

        $this->assertDatabaseHas('guests', [
            'external_id' => 401,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com'
        ]);

        $this->assertDatabaseHas('bookings', [
            'external_id' => 'EXT-BKG-1001',
            'arrival_date' => '2024-09-01 00:00:00',
            'departure_date' => '2024-09-03 00:00:00',
            'status' => 'confirmed',
            'notes' => 'Updated notes'
        ]);
    }
} 