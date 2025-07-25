<?php
namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition()
    {
        return [
            'external_id' => 'EXT-ROOM-' . $this->faker->unique()->numberBetween(200, 999),
            'number' => (string) $this->faker->numberBetween(100, 999),
            'floor' => $this->faker->numberBetween(1, 20),
            'room_type_id' => RoomType::factory(),
        ];
    }
}
