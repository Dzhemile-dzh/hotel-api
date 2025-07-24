<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\RoomType;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'teste@example.com',
        ]);

        RoomType::factory(3)->create();
        Room::factory(10)->create();
        Guest::factory(20)->create();

        Booking::factory(5)->create()->each(function ($booking) {
            $booking->guests()->attach(
                Guest::inRandomOrder()->take(2)->pluck('id')
            );
        });
    }
}
