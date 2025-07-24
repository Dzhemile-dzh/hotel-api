<?php
namespace Database\Factories;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition()
    {
        return [
            'external_id' => $this->faker->uuid(),
            'room_id' => Room::factory(),
        ];
    }
}
