
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
- **Automated Cron Jobs**: Scheduled synchronization with configurable intervals
- **Advanced Filtering**: Support for filtering by ID, status, and custom queries
- **Pagination**: Built-in pagination for all API endpoints
- **Caching**: Intelligent caching for API responses to improve performance
- **Parallel Processing**: Optimized guest data fetching with parallel requests

## API Documentation (Swagger / OpenAPI)

This project uses [Swagger](https://github.com/DarkaOnLine/L5-Swagger) to generate OpenAPI documentation from annotations in controller files.

**Access the documentation at**: http://localhost:8000/api/documentation

### Bookings Endpoint Filters

- `?single_guest_id=GUEST_ID` — Show only bookings for 'Single' rooms made by the specified guest.
- `?id=ID` or `?id[]=ID1&id[]=ID2` — Filter by booking id(s).
- `?status=confirmed` — Filter by booking status (confirmed, pending, cancelled, completed).
- `?page=1&per_page=10` — Pagination.

## API Endpoints

### Internal API
- `GET /api/bookings` - List all bookings (with filtering and pagination)
- `POST /api/bookings` - Create a new booking
- `GET /api/bookings/{id}` - Get booking details
- `PUT /api/bookings/{id}` - Update booking
- `DELETE /api/bookings/{id}` - Delete booking
- `GET /api/guests` - List all guests
- `POST /api/guests` - Create a new guest
- `GET /api/guests/{id}` - Get guest details
- `PUT /api/guests/{id}` - Update guest
- `DELETE /api/guests/{id}` - Delete guest
- `GET /api/rooms` - List all rooms
- `POST /api/rooms` - Create a new room
- `GET /api/rooms/{id}` - Get room details
- `PUT /api/rooms/{id}` - Update room
- `DELETE /api/rooms/{id}` - Delete room
- `GET /api/room-types` - List all room types
- `POST /api/room-types` - Create a new room type
- `GET /api/room-types/{id}` - Get room type details
- `PUT /api/room-types/{id}` - Update room type
- `DELETE /api/room-types/{id}` - Delete room type

### External PMS API (Integration)
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
5. Configure PMS API settings in `.env`:
   ```env
   PMS_API_BASE_URL=https://api.pms.donatix.info
   PMS_API_RATE_LIMIT=2
   PMS_API_TIMEOUT=30
   PMS_API_RETRIES=3
   ```
6. (Optional - internal API) Run migrations:
   ```bash
   php artisan migrate
   ```
7. (Optional - internal API) Seed with sample data:
   ```bash
   php artisan db:seed
   ```

## Configuration

### PMS API Configuration

Create `config/pms.php` or add to your `.env`:

```env
# PMS API Settings
PMS_API_BASE_URL=https://api.pms.donatix.info
PMS_API_RATE_LIMIT=2
PMS_API_TIMEOUT=30
PMS_API_RETRIES=3

# Cron Job Settings
PMS_CRON_ENABLED=true
PMS_FULL_SYNC_INTERVAL=everyFiveMinutes
PMS_INCREMENTAL_SYNC_INTERVAL=hourly
PMS_INCREMENTAL_SINCE="1 hour ago"
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

## Cron Job Setup

The system includes automated synchronization via cron jobs. Here's how to set it up:

### 1. Server Cron Configuration

Add this line to your server's crontab (`crontab -e`):

```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Environment Configuration

Add these variables to your `.env` file:

```env
# Cron Job Settings
PMS_CRON_ENABLED=true
PMS_FULL_SYNC_INTERVAL=everyFiveMinutes
PMS_INCREMENTAL_SYNC_INTERVAL=hourly
PMS_INCREMENTAL_SINCE="1 hour ago"
PMS_SYNC_MAX_EXECUTION_TIME=300
```

### 3. Available Cron Intervals

- **Full Sync**: Runs every 5 minutes by default (syncs all bookings)
- **Incremental Sync**: Runs every hour by default (syncs only recent changes)

### 4. Testing Cron Jobs

Test the cron setup:

```bash
php artisan cron:test
```

Check scheduled tasks:

```bash
php artisan schedule:list
```

## Manual Synchronization

You can still run synchronization manually:

```bash
# Full sync
php artisan sync:bookings

# Incremental sync (last hour)
php artisan sync:bookings --since="1 hour ago"

# Sync since specific date
php artisan sync:bookings --since="2024-01-01"
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

## Code Structure

### Architecture Patterns

- **Repository Pattern**: Data access logic centralized in repository classes
- **Service Layer**: Business logic separated into service classes
- **Form Requests**: Validation logic encapsulated in dedicated request classes
- **API Resources**: Response transformation handled by resource classes
- **Query Scopes**: Reusable query logic in model scopes

### Optimizations

- **Caching**: API responses cached for 5 minutes to reduce external calls
- **Parallel Processing**: Guest data fetched in parallel for better performance
- **Database Transactions**: Ensures data consistency during sync operations
- **Rate Limiting**: Built-in rate limiting to respect API constraints


### Testing Strategy

- **Unit Tests**: Individual component testing with mocked dependencies
- **Feature Tests**: End-to-end testing of complete workflows
- **Database Tests**: Data integrity and relationship testing
- **HTTP Faking**: External API calls mocked for reliable testing

### Security

- **CSRF Protection**: Enabled for API endpoints
- **Input Validation**: Comprehensive validation on all inputs
- **SQL Injection Prevention**: Eloquent ORM with parameterized queries
- **Rate Limiting**: Prevents abuse of internal API endpoints

### Directory Structure
```
app/
├── Console/Commands/          # Artisan commands
├── Http/
│   ├── Controllers/Api/      # API controllers with Swagger annotations
│   ├── Requests/             # Form request validation
│   └── Resources/            # API resource transformers
├── Models/                   # Eloquent models with relationships
├── Repositories/             # Data access layer (Repository Pattern)
├── Services/                 # Business logic layer
└── Providers/               # Service providers for DI registration
```

### Key Design Principles
1. **Dependency Injection**: All dependencies are injected via constructors
2. **Repository Pattern**: Data access logic is abstracted in repository classes
3. **Service Layer**: Business logic is separated into service classes
4. **Single Responsibility**: Each class has a single, well-defined purpose
5. **Interface Segregation**: Services and repositories follow interface contracts
6. **Database Transactions**: Critical operations are wrapped in transactions
7. **Error Handling**: Comprehensive exception handling with proper logging
8. **Caching**: Intelligent caching for API responses and database queries
9. **Rate Limiting**: Respect for external API rate limits
10. **Testing**: Comprehensive test coverage with proper mocking

### Performance Optimizations
- **Eager Loading**: Relationships are loaded efficiently with `with()`
- **Caching**: API responses are cached to reduce external calls
- **Parallel Processing**: Guest data is fetched in parallel
- **Database Indexing**: Proper indexes on frequently queried columns
- **Pagination**: Large datasets are paginated to prevent memory issues


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

### Running Specific Tests

```bash
# Run only unit tests
php artisan test --testsuite=Unit

# Run only feature tests
php artisan test --testsuite=Feature

# Run specific test class
php artisan test --filter=PmsApiServiceTest

# Run specific test method
php artisan test --filter=test_get_booking_details_returns_data
```

## Error Handling

The system includes comprehensive error handling:

- **API Errors**: Logs and reports API failures
- **Database Errors**: Uses transactions to ensure data consistency
- **Rate Limiting**: Automatically respects API rate limits
- **Partial Failures**: Continues processing even if individual records fail
- **Custom Exceptions**: Detailed error reporting with context information

## Rate Limiting

The system automatically implements rate limiting to respect the PMS API's 2 requests per second limit. This is handled internally by the `PmsApiService` class.

## Author

Dzhemile Ahmed