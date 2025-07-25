<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function guests()
    {
        return $this->belongsToMany(Guest::class);
    }
}
