<?php

namespace App\Repositories;

use App\Models\Guest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class GuestRepository
{
    private Guest $model;

    public function __construct(Guest $model)
    {
        $this->model = $model;
    }

    /**
     * Get all guests with pagination
     */
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['bookings'])
            ->paginate($perPage);
    }

    /**
     * Get guest by ID
     */
    public function findById(int $id): ?Guest
    {
        return $this->model
            ->with(['bookings'])
            ->find($id);
    }

    /**
     * Get guest by external ID
     */
    public function findByExternalId(string $externalId): ?Guest
    {
        return $this->model
            ->with(['bookings'])
            ->where('external_id', $externalId)
            ->first();
    }

    /**
     * Create a new guest
     */
    public function create(array $data): Guest
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing guest
     */
    public function update(Guest $guest, array $data): Guest
    {
        $guest->update($data);
        return $guest->load(['bookings']);
    }

    /**
     * Delete a guest
     */
    public function delete(Guest $guest): bool
    {
        return DB::transaction(function () use ($guest) {
            $guest->bookings()->detach();
            return $guest->delete();
        });
    }

    /**
     * Get guests by name (search)
     */
    public function searchByName(string $name): Collection
    {
        return $this->model
            ->with(['bookings'])
            ->where(function ($query) use ($name) {
                $query->where('first_name', 'like', "%{$name}%")
                      ->orWhere('last_name', 'like', "%{$name}%");
            })
            ->get();
    }

    /**
     * Get guests by email
     */
    public function findByEmail(string $email): ?Guest
    {
        return $this->model
            ->with(['bookings'])
            ->where('email', $email)
            ->first();
    }
} 