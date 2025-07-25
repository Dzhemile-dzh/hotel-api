<?php

namespace App\Repositories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RoomRepository
{
    private Room $model;

    public function __construct(Room $model)
    {
        $this->model = $model;
    }

    /**
     * Get all rooms with pagination
     */
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['roomType', 'bookings'])
            ->paginate($perPage);
    }

    /**
     * Get room by ID
     */
    public function findById(int $id): ?Room
    {
        return $this->model
            ->with(['roomType', 'bookings'])
            ->find($id);
    }

    /**
     * Get room by external ID
     */
    public function findByExternalId(string $externalId): ?Room
    {
        return $this->model
            ->with(['roomType', 'bookings'])
            ->where('external_id', $externalId)
            ->first();
    }

    /**
     * Create a new room
     */
    public function create(array $data): Room
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing room
     */
    public function update(Room $room, array $data): Room
    {
        $room->update($data);
        return $room->load(['roomType', 'bookings']);
    }

    /**
     * Delete a room
     */
    public function delete(Room $room): bool
    {
        return DB::transaction(function () use ($room) {
            // Check if room has active bookings
            $activeBookings = $room->bookings()
                ->where('departure_date', '>=', now()->toDateString())
                ->count();

            if ($activeBookings > 0) {
                throw new \Exception('Cannot delete room with active bookings');
            }

            return $room->delete();
        });
    }

    /**
     * Get rooms by floor
     */
    public function getByFloor(int $floor): Collection
    {
        return $this->model
            ->with(['roomType', 'bookings'])
            ->where('floor', $floor)
            ->get();
    }

    /**
     * Get rooms by room type
     */
    public function getByRoomType(int $roomTypeId): Collection
    {
        return $this->model
            ->with(['roomType', 'bookings'])
            ->where('room_type_id', $roomTypeId)
            ->get();
    }

    /**
     * Get available rooms (no active bookings)
     */
    public function getAvailable(): Collection
    {
        return $this->model
            ->with(['roomType'])
            ->whereDoesntHave('bookings', function ($query) {
                $query->where('departure_date', '>=', now()->toDateString())
                      ->whereIn('status', ['confirmed', 'pending']);
            })
            ->get();
    }

    /**
     * Get rooms by number
     */
    public function findByNumber(string $number): ?Room
    {
        return $this->model
            ->with(['roomType', 'bookings'])
            ->where('number', $number)
            ->first();
    }
} 