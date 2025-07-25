<?php
namespace Database\Factories;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition()
    {
        $arrivalDate = $this->faker->dateTimeBetween('now', '+2 months');
        $departureDate = $this->faker->dateTimeBetween($arrivalDate, $arrivalDate->format('Y-m-d') . ' +1 month');
        
        return [
            'external_id' => 'EXT-BKG-' . $this->faker->unique()->numberBetween(1000, 9999),
            'arrival_date' => $arrivalDate->format('Y-m-d'),
            'departure_date' => $departureDate->format('Y-m-d'),
            'room_id' => Room::factory(),
            'room_type_id' => RoomType::factory(),
            'status' => $this->faker->randomElement(['confirmed', 'pending', 'cancelled', 'completed']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
