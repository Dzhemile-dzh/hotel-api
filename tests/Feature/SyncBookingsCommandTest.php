<?php

namespace Tests\Feature;

use App\Services\BookingSyncService;
use App\Services\PmsApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncBookingsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_bookings_command_successfully_syncs_data()
    {
        Http::fake([
            'https://api.pms.donatix.info/api/bookings' => Http::response([
                'data' => [1001]
            ], 200),
            'https://api.pms.donatix.info/api/bookings/1001' => Http::response([
                'id' => 1001,
                'external_id' => 'EXT-BKG-1001',
                'arrival_date' => '2024-09-01',
                'departure_date' => '2024-09-03',
                'room_id' => 201,
                'room_type_id' => 303,
                'guest_ids' => [401],
                'status' => 'confirmed',
                'notes' => 'VIP guest'
            ], 200),
            'https://api.pms.donatix.info/api/rooms/201' => Http::response([
                'id' => 201,
                'number' => '201',
                'floor' => 2
            ], 200),
            'https://api.pms.donatix.info/api/room-types/303' => Http::response([
                'id' => 303,
                'name' => 'Deluxe Suite',
                'description' => 'Luxurious suite with premium amenities'
            ], 200),
            'https://api.pms.donatix.info/api/guests/401' => Http::response([
                'id' => 401,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@email.com'
            ], 200),
        ]);

        $this->artisan('sync:bookings')
            ->expectsOutput('Starting PMS booking synchronization...')
            ->expectsOutput('Fetching booking IDs from PMS API...')
            ->expectsOutput('Found 1 bookings to sync.')
            ->expectsOutput('Synchronization completed successfully!')
            ->assertExitCode(0);

        // Assert data was created in database
        $this->assertDatabaseHas('room_types', [
            'external_id' => '303',
            'name' => 'Deluxe Suite'
        ]);

        $this->assertDatabaseHas('rooms', [
            'external_id' => '201',
            'number' => '201',
            'floor' => 2
        ]);

        $this->assertDatabaseHas('guests', [
            'external_id' => '401',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        $this->assertDatabaseHas('bookings', [
            'external_id' => 'EXT-BKG-1001',
            'arrival_date' => '2024-09-01 00:00:00',
            'departure_date' => '2024-09-03 00:00:00',
            'status' => 'confirmed'
        ]);
    }

    public function test_sync_bookings_command_with_since_parameter()
    {
        Http::fake([
            'https://api.pms.donatix.info/api/bookings?updated_at.gt=2025-07-20' => Http::response([
                'data' => []
            ], 200),
        ]);

        $this->artisan('sync:bookings', ['--since' => '2025-07-20'])
            ->expectsOutput('Starting PMS booking synchronization...')
            ->expectsOutput('Fetching booking IDs from PMS API...')
            ->expectsOutput('No bookings found to sync.')
            ->expectsOutput('Synchronization completed successfully!')
            ->assertExitCode(0);
    }

    public function test_sync_bookings_command_handles_api_errors()
    {
        Http::fake([
            'https://api.pms.donatix.info/api/bookings' => Http::response([], 500),
        ]);

        $this->artisan('sync:bookings')
            ->expectsOutput('Starting PMS booking synchronization...')
            ->expectsOutputToContain('Synchronization failed')
            ->assertExitCode(1);
    }

    public function test_sync_bookings_command_handles_partial_failures()
    {
        Http::fake([
            'https://api.pms.donatix.info/api/bookings' => Http::response([
                'data' => [1001, 1002]
            ], 200),
            'https://api.pms.donatix.info/api/bookings/1001' => Http::response([
                'id' => 1001,
                'external_id' => 'EXT-BKG-1001',
                'arrival_date' => '2024-09-01',
                'departure_date' => '2024-09-03',
                'room_id' => 201,
                'room_type_id' => 303,
                'guest_ids' => [401],
                'status' => 'confirmed',
                'notes' => 'VIP guest'
            ], 200),
            'https://api.pms.donatix.info/api/rooms/201' => Http::response([
                'id' => 201,
                'number' => '201',
                'floor' => 2
            ], 200),
            'https://api.pms.donatix.info/api/room-types/303' => Http::response([
                'id' => 303,
                'name' => 'Deluxe Suite',
                'description' => 'Luxurious suite with premium amenities'
            ], 200),
            'https://api.pms.donatix.info/api/guests/401' => Http::response([
                'id' => 401,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@email.com'
            ], 200),
            'https://api.pms.donatix.info/api/bookings/1002' => Http::response([], 404),
        ]);

        $this->artisan('sync:bookings')
            ->expectsOutput('Starting PMS booking synchronization...')
            ->expectsOutput('Fetching booking IDs from PMS API...')
            ->expectsOutput('Found 2 bookings to sync.')
            ->expectsOutput('Synchronization completed successfully!')
            ->assertExitCode(0);

        // Assert only the successful booking was created
        $this->assertDatabaseHas('bookings', [
            'external_id' => 'EXT-BKG-1001'
        ]);

        $this->assertDatabaseMissing('bookings', [
            'external_id' => 'EXT-BKG-1002'
        ]);
    }
} 