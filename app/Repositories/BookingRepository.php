<?php

namespace App\Repositories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingRepository
{
    /**
     * Get paginated bookings with filters
     */
    public function getPaginatedBookings(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Booking::with(['room', 'roomType', 'guests']);

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Get booking by ID with relationships
     */
    public function findById(int $id): ?Booking
    {
        return Booking::with(['room', 'roomType', 'guests'])->find($id);
    }

    /**
     * Get booking by external ID
     */
    public function findByExternalId(string $externalId): ?Booking
    {
        return Booking::where('external_id', $externalId)->first();
    }

    /**
     * Create a new booking
     */
    public function create(array $data): Booking
    {
        $booking = Booking::create($data);

        if (isset($data['guest_ids'])) {
            $booking->guests()->sync($data['guest_ids']);
        }

        return $booking->load(['room', 'roomType', 'guests']);
    }

    /**
     * Update an existing booking
     */
    public function update(Booking $booking, array $data): Booking
    {
        $booking->update($data);

        if (isset($data['guest_ids'])) {
            $booking->guests()->sync($data['guest_ids']);
        }

        return $booking->load(['room', 'roomType', 'guests']);
    }

    /**
     * Delete a booking
     */
    public function delete(Booking $booking): bool
    {
        return $booking->delete();
    }

    /**
     * Get bookings by status
     */
    public function getByStatus(string $status): Collection
    {
        return Booking::byStatus($status)->with(['room', 'roomType', 'guests'])->get();
    }

    /**
     * Get active bookings (confirmed or pending)
     */
    public function getActiveBookings(): Collection
    {
        return Booking::whereIn('status', ['confirmed', 'pending'])
            ->with(['room', 'roomType', 'guests'])
            ->get();
    }

    /**
     * Get bookings in date range
     */
    public function getBookingsInDateRange(string $startDate, string $endDate): Collection
    {
        return Booking::inDateRange($startDate, $endDate)
            ->with(['room', 'roomType', 'guests'])
            ->get();
    }

    /**
     * Get single room bookings for a specific guest
     */
    public function getSingleRoomBookingsForGuest(int $guestId): Collection
    {
        return Booking::singleRoomForGuest($guestId)
            ->with(['room', 'roomType', 'guests'])
            ->get();
    }

    /**
     * Apply filters to query
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (isset($filters['id'])) {
            $query->filterByIds($filters['id']);
        }

        if (isset($filters['single_guest_id'])) {
            $query->singleRoomForGuest($filters['single_guest_id']);
        }

        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['date_range'])) {
            $query->inDateRange($filters['date_range']['start'], $filters['date_range']['end']);
        }
    }
} 