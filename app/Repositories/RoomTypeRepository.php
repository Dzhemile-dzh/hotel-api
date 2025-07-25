<?php

namespace App\Repositories;

use App\Models\RoomType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RoomTypeRepository
{
    private RoomType $model;

    public function __construct(RoomType $model)
    {
        $this->model = $model;
    }

    /**
     * Get all room types with pagination
     */
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['rooms', 'bookings'])
            ->paginate($perPage);
    }

    /**
     * Get room type by ID
     */
    public function findById(int $id): ?RoomType
    {
        return $this->model
            ->with(['rooms', 'bookings'])
            ->find($id);
    }

    /**
     * Get room type by external ID
     */
    public function findByExternalId(string $externalId): ?RoomType
    {
        return $this->model
            ->with(['rooms', 'bookings'])
            ->where('external_id', $externalId)
            ->first();
    }

    /**
     * Create a new room type
     */
    public function create(array $data): RoomType
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing room type
     */
    public function update(RoomType $roomType, array $data): RoomType
    {
        $roomType->update($data);
        return $roomType->load(['rooms', 'bookings']);
    }

    /**
     * Delete a room type
     */
    public function delete(RoomType $roomType): bool
    {
        return DB::transaction(function () use ($roomType) {
            // Check if room type has rooms
            if ($roomType->rooms()->count() > 0) {
                throw new \Exception('Cannot delete room type with existing rooms');
            }

            // Check if room type has bookings
            if ($roomType->bookings()->count() > 0) {
                throw new \Exception('Cannot delete room type with existing bookings');
            }

            return $roomType->delete();
        });
    }

    /**
     * Get room types by name
     */
    public function findByName(string $name): ?RoomType
    {
        return $this->model
            ->with(['rooms', 'bookings'])
            ->where('name', $name)
            ->first();
    }

    /**
     * Search room types by name
     */
    public function searchByName(string $name): Collection
    {
        return $this->model
            ->with(['rooms', 'bookings'])
            ->where('name', 'like', "%{$name}%")
            ->get();
    }

    /**
     * Get room types with room count
     */
    public function getWithRoomCount(): Collection
    {
        return $this->model
            ->withCount('rooms')
            ->with(['rooms', 'bookings'])
            ->get();
    }

    /**
     * Get room types with booking count
     */
    public function getWithBookingCount(): Collection
    {
        return $this->model
            ->withCount('bookings')
            ->with(['rooms', 'bookings'])
            ->get();
    }
} 