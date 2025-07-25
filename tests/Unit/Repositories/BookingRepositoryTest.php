<?php

namespace Tests\Unit\Repositories;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\RoomType;
use App\Repositories\BookingRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private BookingRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new BookingRepository(new Booking());
    }

    public function test_get_all_paginated()
    {
        // Create test data
        $roomType = RoomType::create([
            'external_id' => 301,
            'name' => 'Standard Single',
            'description' => 'Cozy room with single bed'
        ]);
        
        $room = Room::create([
            'external_id' => 201,
            'number' => '201',
            'floor' => 2,
            'room_type_id' => $roomType->id
        ]);
        
        Booking::create([
            'external_id' => 'EXT-BKG-1001',
            'arrival_date' => '2024-09-01',
            'departure_date' => '2024-09-03',
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'status' => 'confirmed'
        ]);

        $result = $this->repository->getAllPaginated(3);

        $this->assertCount(1, $result->items());
        $this->assertEquals(1, $result->total());
    }

    public function test_find_by_id()
    {
        $roomType = RoomType::create([
            'external_id' => 301,
            'name' => 'Standard Single',
            'description' => 'Cozy room with single bed'
        ]);
        
        $room = Room::create([
            'external_id' => 201,
            'number' => '201',
            'floor' => 2,
            'room_type_id' => $roomType->id
        ]);
        
        $booking = Booking::create([
            'external_id' => 'EXT-BKG-1001',
            'arrival_date' => '2024-09-01',
            'departure_date' => '2024-09-03',
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'status' => 'confirmed'
        ]);

        $result = $this->repository->findById($booking->id);

        $this->assertNotNull($result);
        $this->assertEquals($booking->id, $result->id);
    }

    public function test_find_by_external_id()
    {
        $roomType = RoomType::create([
            'external_id' => 301,
            'name' => 'Standard Single',
            'description' => 'Cozy room with single bed'
        ]);
        
        $room = Room::create([
            'external_id' => 201,
            'number' => '201',
            'floor' => 2,
            'room_type_id' => $roomType->id
        ]);
        
        $booking = Booking::create([
            'external_id' => 'EXT-123',
            'arrival_date' => '2024-09-01',
            'departure_date' => '2024-09-03',
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'status' => 'confirmed'
        ]);

        $result = $this->repository->findByExternalId('EXT-123');

        $this->assertNotNull($result);
        $this->assertEquals('EXT-123', $result->external_id);
    }

    public function test_create_booking()
    {
        $roomType = RoomType::create([
            'external_id' => 301,
            'name' => 'Standard Single',
            'description' => 'Cozy room with single bed'
        ]);
        
        $room = Room::create([
            'external_id' => 201,
            'number' => '201',
            'floor' => 2,
            'room_type_id' => $roomType->id
        ]);
        
        $guest = Guest::create([
            'external_id' => 401,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com'
        ]);

        $data = [
            'external_id' => 'EXT-123',
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'arrival_date' => '2024-09-01',
            'departure_date' => '2024-09-03',
            'status' => 'confirmed',
            'notes' => 'Test booking',
            'guest_ids' => [$guest->id]
        ];

        $result = $this->repository->create($data);

        $this->assertNotNull($result);
        $this->assertEquals('EXT-123', $result->external_id);
        $this->assertCount(1, $result->guests);
    }

    public function test_update_booking()
    {
        $roomType = RoomType::create([
            'external_id' => 301,
            'name' => 'Standard Single',
            'description' => 'Cozy room with single bed'
        ]);
        
        $room = Room::create([
            'external_id' => 201,
            'number' => '201',
            'floor' => 2,
            'room_type_id' => $roomType->id
        ]);
        
        $booking = Booking::create([
            'external_id' => 'EXT-BKG-1001',
            'arrival_date' => '2024-09-01',
            'departure_date' => '2024-09-03',
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'status' => 'confirmed'
        ]);

        $data = ['status' => 'cancelled', 'notes' => 'Updated notes'];

        $result = $this->repository->update($booking, $data);

        $this->assertEquals('cancelled', $result->status);
        $this->assertEquals('Updated notes', $result->notes);
    }

    public function test_delete_booking()
    {
        $roomType = RoomType::create([
            'external_id' => 301,
            'name' => 'Standard Single',
            'description' => 'Cozy room with single bed'
        ]);
        
        $room = Room::create([
            'external_id' => 201,
            'number' => '201',
            'floor' => 2,
            'room_type_id' => $roomType->id
        ]);
        
        $booking = Booking::create([
            'external_id' => 'EXT-BKG-1001',
            'arrival_date' => '2024-09-01',
            'departure_date' => '2024-09-03',
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'status' => 'confirmed'
        ]);

        $result = $this->repository->delete($booking);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }

    public function test_get_by_status()
    {
        $roomType = RoomType::create([
            'external_id' => 301,
            'name' => 'Standard Single',
            'description' => 'Cozy room with single bed'
        ]);
        
        $room = Room::create([
            'external_id' => 201,
            'number' => '201',
            'floor' => 2,
            'room_type_id' => $roomType->id
        ]);
        
        Booking::create([
            'external_id' => 'EXT-BKG-1001',
            'arrival_date' => '2024-09-01',
            'departure_date' => '2024-09-03',
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'status' => 'confirmed'
        ]);
        
        Booking::create([
            'external_id' => 'EXT-BKG-1002',
            'arrival_date' => '2024-09-05',
            'departure_date' => '2024-09-07',
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'status' => 'pending'
        ]);

        $result = $this->repository->getByStatus('confirmed');

        $this->assertCount(1, $result);
        $this->assertEquals('confirmed', $result->first()->status);
    }

    public function test_get_by_guest()
    {
        $roomType = RoomType::create([
            'external_id' => 301,
            'name' => 'Standard Single',
            'description' => 'Cozy room with single bed'
        ]);
        
        $room = Room::create([
            'external_id' => 201,
            'number' => '201',
            'floor' => 2,
            'room_type_id' => $roomType->id
        ]);
        
        $guest = Guest::create([
            'external_id' => 401,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com'
        ]);
        
        $booking = Booking::create([
            'external_id' => 'EXT-BKG-1001',
            'arrival_date' => '2024-09-01',
            'departure_date' => '2024-09-03',
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'status' => 'confirmed'
        ]);
        
        $booking->guests()->attach($guest->id);

        $result = $this->repository->getByGuest($guest->id);

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->guests->contains($guest));
    }

    public function test_get_by_room()
    {
        $roomType = RoomType::create([
            'external_id' => 301,
            'name' => 'Standard Single',
            'description' => 'Cozy room with single bed'
        ]);
        
        $room = Room::create([
            'external_id' => 201,
            'number' => '201',
            'floor' => 2,
            'room_type_id' => $roomType->id
        ]);
        
        Booking::create([
            'external_id' => 'EXT-BKG-1001',
            'arrival_date' => '2024-09-01',
            'departure_date' => '2024-09-03',
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'status' => 'confirmed'
        ]);

        $result = $this->repository->getByRoom($room->id);

        $this->assertCount(1, $result);
        $this->assertEquals($room->id, $result->first()->room_id);
    }

    public function test_get_by_room_type()
    {
        $roomType = RoomType::create([
            'external_id' => 301,
            'name' => 'Standard Single',
            'description' => 'Cozy room with single bed'
        ]);
        
        $room = Room::create([
            'external_id' => 201,
            'number' => '201',
            'floor' => 2,
            'room_type_id' => $roomType->id
        ]);
        
        Booking::create([
            'external_id' => 'EXT-BKG-1001',
            'arrival_date' => '2024-09-01',
            'departure_date' => '2024-09-03',
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'status' => 'confirmed'
        ]);

        $result = $this->repository->getByRoomType($roomType->id);

        $this->assertCount(1, $result);
        $this->assertEquals($roomType->id, $result->first()->room_type_id);
    }

    public function test_get_active()
    {
        $roomType = RoomType::create([
            'external_id' => 301,
            'name' => 'Standard Single',
            'description' => 'Cozy room with single bed'
        ]);
        
        $room = Room::create([
            'external_id' => 201,
            'number' => '201',
            'floor' => 2,
            'room_type_id' => $roomType->id
        ]);
        
        // Create active booking (arrival_date >= today)
        Booking::create([
            'external_id' => 'EXT-BKG-1001',
            'arrival_date' => now()->addDays(1)->format('Y-m-d'),
            'departure_date' => now()->addDays(3)->format('Y-m-d'),
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'status' => 'confirmed'
        ]);
        
        // Create past booking
        Booking::create([
            'external_id' => 'EXT-BKG-1002',
            'arrival_date' => now()->subDays(5)->format('Y-m-d'),
            'departure_date' => now()->subDays(3)->format('Y-m-d'),
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'status' => 'confirmed'
        ]);

        $result = $this->repository->getActive();

        $this->assertCount(1, $result);
    }

    public function test_get_in_date_range()
    {
        $roomType = RoomType::create([
            'external_id' => 301,
            'name' => 'Standard Single',
            'description' => 'Cozy room with single bed'
        ]);
        
        $room = Room::create([
            'external_id' => 201,
            'number' => '201',
            'floor' => 2,
            'room_type_id' => $roomType->id
        ]);
        
        Booking::create([
            'external_id' => 'EXT-BKG-1001',
            'arrival_date' => '2024-09-01',
            'departure_date' => '2024-09-03',
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'status' => 'confirmed'
        ]);
        
        Booking::create([
            'external_id' => 'EXT-BKG-1002',
            'arrival_date' => '2024-10-01',
            'departure_date' => '2024-10-03',
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'status' => 'confirmed'
        ]);

        $result = $this->repository->getInDateRange('2024-09-01', '2024-09-30');

        $this->assertCount(1, $result);
    }
} 