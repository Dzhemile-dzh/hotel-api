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
            'external_id' => $this->faker->uuid(),
            'number' => $this->faker->numberBetween(100, 999),
            'room_type_id' => RoomType::factory(),
        ];
    }
}
