<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Support\Facades\DB;

class BookingSyncService
{
    private PmsApiService $apiService;

    public function __construct(PmsApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Sync a single booking with all its related data
     */
    public function syncBooking(int $bookingId): void
    {
        // Fetch booking details
        $bookingData = $this->apiService->getBookingDetails($bookingId);
        
        // Fetch related data
        $roomData = $this->apiService->getRoomDetails($bookingData['room_id']);
        $roomTypeData = $this->apiService->getRoomTypeDetails($bookingData['room_type_id']);
        $guestsData = $this->apiService->getGuestsDetails($bookingData['guest_ids']);

        DB::transaction(function () use ($bookingData, $roomData, $roomTypeData, $guestsData) {
            // Sync room type first (room depends on it)
            $roomType = $this->syncRoomType($roomTypeData);
            
            // Sync room
            $room = $this->syncRoom($roomData, $roomType);
            
            // Sync guests
            $guests = $this->syncGuests($guestsData);
            
            // Sync booking
            $booking = $this->syncBookingData($bookingData, $room, $roomType);
            
            // Sync booking-guest relationships
            $this->syncBookingGuests($booking, $guests);
        });
    }

    /**
     * Sync room type data
     */
    private function syncRoomType(array $roomTypeData): RoomType
    {
        return RoomType::updateOrCreate(
            ['external_id' => $roomTypeData['id']],
            [
                'name' => $roomTypeData['name'],
                'description' => $roomTypeData['description'] ?? null,
            ]
        );
    }

    /**
     * Sync room data
     */
    private function syncRoom(array $roomData, RoomType $roomType): Room
    {
        return Room::updateOrCreate(
            ['external_id' => $roomData['id']],
            [
                'number' => $roomData['number'],
                'floor' => $roomData['floor'],
                'room_type_id' => $roomType->id,
            ]
        );
    }

    /**
     * Sync guests data
     */
    private function syncGuests(array $guestsData): array
    {
        $guests = [];
        
        foreach ($guestsData as $guestData) {
            $guests[] = Guest::updateOrCreate(
                ['external_id' => $guestData['id']],
                [
                    'first_name' => $guestData['first_name'],
                    'last_name' => $guestData['last_name'],
                    'email' => $guestData['email'] ?? null,
                ]
            );
        }

        return $guests;
    }

    /**
     * Sync booking data
     */
    private function syncBookingData(array $bookingData, Room $room, RoomType $roomType): Booking
    {
        return Booking::updateOrCreate(
            ['external_id' => $bookingData['external_id']],
            [
                'arrival_date' => $bookingData['arrival_date'],
                'departure_date' => $bookingData['departure_date'],
                'room_id' => $room->id,
                'room_type_id' => $roomType->id,
                'status' => $bookingData['status'],
                'notes' => $bookingData['notes'] ?? null,
            ]
        );
    }

    /**
     * Sync booking-guest relationships
     */
    private function syncBookingGuests(Booking $booking, array $guests): void
    {
        $guestIds = collect($guests)->pluck('id')->toArray();
        $booking->guests()->sync($guestIds);
    }
} 