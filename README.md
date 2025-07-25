
# Hotel Management System API

A Laravel-based hotel management system that syncs bookings with a Property Management System (PMS) via REST API.

## Features

- **PMS Integration**: Syncs bookings, rooms, room types, and guests from external PMS API
- **Rate Limiting**: Respects API rate limits (2 requests per second)
- **Incremental Sync**: Support for syncing only updated records since a specific date
- **Error Handling**: Comprehensive error handling and logging
- **Progress Feedback**: Real-time progress indication during sync operations
- **Idempotent Operations**: Safe to run multiple times without data duplication
- **Swagger (OpenAPI) Documentation**: Interactive API docs generated from controller annotations

## API Documentation (Swagger / OpenAPI)

This project uses [L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger) to generate OpenAPI documentation from annotations in controller files.

http://localhost:8000/api/documentation#/

## API Endpoints

The system integrates with the following PMS API endpoints:

- `GET /api/bookings` - Returns an array of booking IDs
- `GET /api/bookings/{id}` - Returns booking details including guest_ids array
- `GET /api/rooms/{id}` - Returns room details
- `GET /api/room-types/{id}` - Returns room type details
- `GET /api/guests/{id}` - Returns guest details

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy environment file:
   ```bash
   cp .env.example .env
   ```
4. Configure your database in `.env`
5. Run migrations:
   ```bash
   php artisan migrate
   ```

## Usage

### Sync All Bookings

To sync all bookings from the PMS API:

```bash
php artisan sync:bookings
```

### Sync Bookings Since a Specific Date

To sync only bookings updated since a specific date:

```bash
php artisan sync:bookings --since=2025-07-20
```

You can also use datetime format:

```bash
php artisan sync:bookings --since=2025-07-20T14:30:00
```

### Command Output

The command provides real-time feedback:

```
Starting PMS booking synchronization...
Fetching booking IDs from PMS API...
Found 1000 bookings to sync.
████████████████████████████████████████ 1000/1000 [100%]
Sync completed: 998 processed, 2 errors
Synchronization completed successfully!
```

## Database Schema

### Bookings Table
- `id` - Primary key
- `external_id` - PMS booking ID (unique)
- `arrival_date` - Guest arrival date
- `departure_date` - Guest departure date
- `room_id` - Foreign key to rooms table
- `room_type_id` - Foreign key to room_types table
- `status` - Booking status (confirmed, pending, cancelled, completed)
- `notes` - Additional booking notes
- `created_at`, `updated_at` - Timestamps

### Rooms Table
- `id` - Primary key
- `external_id` - PMS room ID (unique)
- `number` - Room number
- `floor` - Floor number
- `room_type_id` - Foreign key to room_types table
- `created_at`, `updated_at` - Timestamps

### Room Types Table
- `id` - Primary key
- `external_id` - PMS room type ID (unique)
- `name` - Room type name
- `description` - Room type description
- `created_at`, `updated_at` - Timestamps

### Guests Table
- `id` - Primary key
- `external_id` - PMS guest ID (unique)
- `first_name` - Guest first name
- `last_name` - Guest last name
- `email` - Guest email address
- `phone` - Guest phone number
- `created_at`, `updated_at` - Timestamps

### Booking Guest Pivot Table
- `booking_id` - Foreign key to bookings table
- `guest_id` - Foreign key to guests table

## API Response Examples

### Booking Details
```json
{
  "id": 1001,
  "external_id": "EXT-BKG-1001",
  "arrival_date": "2024-09-01",
  "departure_date": "2024-09-03",
  "room_id": 201,
  "room_type_id": 303,
  "guest_ids": [401, 402],
  "status": "confirmed",
  "notes": "VIP guest"
}
```

### Room Details
```json
{
  "id": 201,
  "number": "201",
  "floor": 2
}
```

### Room Type Details
```json
{
  "id": 301,
  "name": "Standard Single",
  "description": "Cozy room with single bed, perfect for solo travelers"
}
```

### Guest Details
```json
{
  "id": 500,
  "first_name": "Benjamin",
  "last_name": "Jackson",
  "email": "benjamin.jackson500@email.com"
}
```

## Testing

Run the test suite:

```bash
php artisan test
```

### Test Coverage

- **Unit Tests**: Test individual service methods and API interactions
- **Feature Tests**: Test complete command execution flow
- **Database Tests**: Test data synchronization and relationships

## Error Handling

The system includes comprehensive error handling:

- **API Errors**: Logs and reports API failures
- **Database Errors**: Uses transactions to ensure data consistency
- **Rate Limiting**: Automatically respects API rate limits
- **Partial Failures**: Continues processing even if individual records fail

## Rate Limiting

The system automatically implements rate limiting to respect the PMS API's 2 requests per second limit. This is handled internally by the `PmsApiService` class.

## Author

Dzhemile Ahmed