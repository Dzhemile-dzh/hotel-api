<?php
namespace Database\Factories;

use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomTypeFactory extends Factory
{
    protected $model = RoomType::class;

    public function definition()
    {
        return [
            'external_id' => $this->faker->uuid(),
            'name' => $this->faker->randomElement(['Standard', 'Deluxe', 'Suite']),
        ];
    }
}
