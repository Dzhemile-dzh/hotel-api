<?php
namespace Database\Factories;

use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomTypeFactory extends Factory
{
    protected $model = RoomType::class;

    public function definition()
    {
        $roomTypes = [
            'Standard Single' => 'Cozy room with single bed, perfect for solo travelers',
            'Standard Double' => 'Comfortable room with double bed for couples',
            'Deluxe Single' => 'Premium single room with luxury amenities',
            'Deluxe Double' => 'Spacious double room with premium features',
            'Suite' => 'Luxury suite with separate living area',
            'Executive Suite' => 'Top-tier suite with business amenities'
        ];
        
        $name = $this->faker->randomElement(array_keys($roomTypes));
        
        return [
            'external_id' => 'EXT-TYPE-' . $this->faker->unique()->numberBetween(300, 399),
            'name' => $name,
            'description' => $roomTypes[$name],
        ];
    }
}
