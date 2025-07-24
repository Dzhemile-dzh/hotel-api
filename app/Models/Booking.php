<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

public function room()
{
    return $this->belongsTo(Room::class);
}

public function guests()
{
    return $this->belongsToMany(Guest::class);
}

}
