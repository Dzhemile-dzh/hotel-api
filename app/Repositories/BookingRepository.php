<?php

namespace App\Repositories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BookingRepository
{
    private Booking $model;

    public function __construct(Booking $model)
    {
        $this->model = $model;
    }

    /**
     * Get all bookings with pagination
     */
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['room', 'roomType', 'guests'])
            ->paginate($perPage);
    }

    /**
     * Get booking by ID
     */
    public function findById(int $id): ?Booking
    {
        return $this->model
            ->with(['room', 'roomType', 'guests'])
            ->find($id);
    }

    /**
     * Get booking by external ID
     */
    public function findByExternalId(string $externalId): ?Booking
    {
        return $this->model
            ->with(['room', 'roomType', 'guests'])
            ->where('external_id', $externalId)
            ->first();
    }

    /**
     * Create a new booking
     */
    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $booking = $this->model->create($data);
            
            if (isset($data['guest_ids'])) {
                $booking->guests()->attach($data['guest_ids']);
            }
            
            return $booking->load(['room', 'roomType', 'guests']);
        });
    }

    /**
     * Update an existing booking
     */
    public function update(Booking $booking, array $data): Booking
    {
        return DB::transaction(function () use ($booking, $data) {
            $booking->update($data);
            
            if (isset($data['guest_ids'])) {
                $booking->guests()->sync($data['guest_ids']);
            }
            
            return $booking->load(['room', 'roomType', 'guests']);
        });
    }

    /**
     * Delete a booking
     */
    public function delete(Booking $booking): bool
    {
        return DB::transaction(function () use ($booking) {
            $booking->guests()->detach();
            return $booking->delete();
        });
    }

    /**
     * Get bookings by status
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model
            ->with(['room', 'roomType', 'guests'])
            ->where('status', $status)
            ->get();
    }

    /**
     * Get bookings for a specific guest
     */
    public function getByGuest(int $guestId): Collection
    {
        return $this->model
            ->with(['room', 'roomType', 'guests'])
            ->whereHas('guests', function ($query) use ($guestId) {
                $query->where('guests.id', $guestId);
            })
            ->get();
    }

    /**
     * Get bookings for a specific room
     */
    public function getByRoom(int $roomId): Collection
    {
        return $this->model
            ->with(['room', 'roomType', 'guests'])
            ->where('room_id', $roomId)
            ->get();
    }

    /**
     * Get bookings for a specific room type
     */
    public function getByRoomType(int $roomTypeId): Collection
    {
        return $this->model
            ->with(['room', 'roomType', 'guests'])
            ->where('room_type_id', $roomTypeId)
            ->get();
    }

    /**
     * Get active bookings (arrival_date >= today)
     */
    public function getActive(): Collection
    {
        return $this->model
            ->with(['room', 'roomType', 'guests'])
            ->where('arrival_date', '>=', now()->toDateString())
            ->get();
    }

    /**
     * Get bookings in date range
     */
    public function getInDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model
            ->with(['room', 'roomType', 'guests'])
            ->whereBetween('arrival_date', [$startDate, $endDate])
            ->get();
    }
} 