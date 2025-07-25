<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'arrival_date',
        'departure_date',
        'room_id',
        'room_type_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'departure_date' => 'date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Scope to filter by booking IDs
     */
    public function scopeFilterByIds(Builder $query, $ids): Builder
    {
        if (is_array($ids)) {
            return $query->whereIn('id', $ids);
        }
        
        return $query->where('id', $ids);
    }

    /**
     * Scope to filter by single room bookings for a specific guest
     */
    public function scopeSingleRoomForGuest(Builder $query, int $guestId): Builder
    {
        return $query->whereHas('roomType', function ($q) {
            $q->where('name', 'like', '%Single%');
        })->whereHas('guests', function ($q) use ($guestId) {
            $q->where('guests.id', $guestId);
        });
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeInDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('arrival_date', [$startDate, $endDate]);
    }

    /**
     * Get the room that owns the booking.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the room type that owns the booking.
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Get the guests for the booking.
     */
    public function guests(): BelongsToMany
    {
        return $this->belongsToMany(Guest::class);
    }

    /**
     * Check if booking is active (confirmed or pending)
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['confirmed', 'pending']);
    }

    /**
     * Get booking duration in days
     */
    public function getDurationInDays(): int
    {
        return $this->arrival_date->diffInDays($this->departure_date);
    }
}
