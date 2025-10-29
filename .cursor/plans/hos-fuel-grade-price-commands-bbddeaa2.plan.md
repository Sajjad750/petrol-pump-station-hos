<!-- bbddeaa2-6239-4cfc-9ab0-850fea8f3583 817c8003-0dc9-419b-956a-c2e8c630cd81 -->
# HOS Side Implementation: Fuel Grade Price Command Queue

## Overview

Implement a command queue system that allows HOS administrators to update fuel grade prices and schedules. Commands are stored in HOS and fetched by BOS during its regular sync operations.

## Architecture

- **Command Queue Pattern**: HOS stores commands → BOS polls → BOS executes → BOS acknowledges
- **Polling**: BOS fetches commands during existing sync operations
- **Status Tracking**: Commands tracked through pending → processing → completed/failed states

## Phase 1: Database Schema

### 1.1 Create hos_commands Migration

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_hos_commands_table.php`

```php
Schema::create('hos_commands', function (Blueprint $table) {
    $table->id();
    $table->foreignId('station_id')->constrained()->onDelete('cascade');
    $table->string('command_type', 100)->comment('update_fuel_grade_price, schedule_fuel_grade_price');
    $table->json('command_data')->comment('Command payload with fuel grade ID, price, scheduled_at, etc.');
    $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
    $table->text('error_message')->nullable();
    $table->timestamp('executed_at')->nullable();
    $table->timestamp('acknowledged_at')->nullable();
    $table->integer('retry_count')->default(0);
    $table->timestamps();
    
    $table->index(['station_id', 'status']);
    $table->index(['status', 'created_at']);
    $table->index(['command_type', 'status']);
});
```

### 1.2 Create HosCommand Model

**File**: `app/Models/HosCommand.php`

- Add fillable fields: `station_id`, `command_type`, `command_data`, `status`, `error_message`, `executed_at`, `acknowledged_at`, `retry_count`
- Cast `command_data` as array, `executed_at` and `acknowledged_at` as datetime
- Add relationship: `belongsTo(Station::class)`
- Add methods:
  - `markAsProcessing(): void`
  - `markAsCompleted(): void`
  - `markAsFailed(string $error): void`
  - `incrementRetry(): void`
- Add scopes:
  - `pending()` - status = 'pending'
  - `forStation(int $stationId)`
  - `byType(string $type)`

## Phase 2: API Endpoints for Command Queue

### 2.1 Add Command Endpoints to SyncController

**File**: `app/Http/Controllers/Api/SyncController.php`

Add two new methods:

**Method**: `getPendingCommands(Request $request): JsonResponse`

- Get station from request (via middleware)
- Query `HosCommand::where('station_id', $station->id)->where('status', 'pending')->orderBy('created_at')->limit(10)`
- Return command list with `id`, `command_type`, `command_data`
- Mark commands as 'processing' when fetched

**Method**: `acknowledgeCommand(Request $request): JsonResponse`

- Validate: `command_id` (required), `success` (boolean), `error_message` (nullable)
- Find command by ID and station_id
- If success: call `markAsCompleted()`
- If failed: call `markAsFailed($errorMessage)`
- Return success response

### 2.2 Add Routes

**File**: `routes/api.php`

Add to existing sync route group:

```php
Route::get('/pending-commands', [SyncController::class, 'getPendingCommands']);
Route::post('/acknowledge-command', [SyncController::class, 'acknowledgeCommand']);
```

## Phase 3: Fuel Grade Price Update Controller

### 3.1 Create or Update FuelGradeController

**File**: `app/Http/Controllers/FuelGradeController.php` (create if doesn't exist)

**Method**: `updatePrice(Request $request, FuelGrade $fuelGrade): JsonResponse`

- Validate: `price` (required|numeric|min:0), `scheduled_at` (nullable|date)
- Update fuel grade in HOS database
- Create HosCommand:
  - `station_id` = $fuelGrade->station_id
  - `command_type` = 'update_fuel_grade_price' (or 'schedule_fuel_grade_price' if scheduled_at provided)
  - `command_data` = [

'bos_fuel_grade_id' => $fuelGrade->bos_fuel_grade_id,

'price' => validated price,

'scheduled_at' => validated scheduled_at or null

]

  - `status` = 'pending'
- Return success response with command queued message

**Method**: `schedulePrice(Request $request, FuelGrade $fuelGrade): JsonResponse`

- Similar to updatePrice but for scheduled price changes
- Command type: 'schedule_fuel_grade_price'
- Requires both `scheduled_price` and `scheduled_at`

### 3.2 Add Routes

**File**: `routes/web.php`

```php
Route::middleware(['auth'])->group(function () {
    Route::put('/fuel-grades/{fuelGrade}/price', [FuelGradeController::class, 'updatePrice'])->name('fuel-grades.update-price');
    Route::put('/fuel-grades/{fuelGrade}/schedule-price', [FuelGradeController::class, 'schedulePrice'])->name('fuel-grades.schedule-price');
});
```

## Phase 4: UI Updates for Price Management

### 4.1 Update Fuel Grades Index View

**File**: `resources/views/fuel_grades/index.blade.php`

Add action buttons/forms:

- Edit price button (opens modal/form)
- Schedule price button (opens modal/form with date picker)
- Show current price and scheduled price if exists
- Display command status if pending commands exist

### 4.2 Create Price Update Form Partial

**File**: `resources/views/fuel_grades/partials/update-price-form.blade.php`

Form fields:

- Price input (decimal)
- Scheduled at datetime picker (optional)
- Submit button
- AJAX form submission handling

## Phase 5: Command Status Tracking

### 5.1 Add Command Status View

**File**: `app/Http/Controllers/HosCommandController.php` (create)

**Method**: `index(Request $request): View`

- Show all commands with filtering by station, status, command type
- Use DataTables for listing
- Show command details, status, error messages

**Method**: `show(HosCommand $hosCommand): JsonResponse`

- Return command details as JSON

### 5.2 Add Routes

**File**: `routes/web.php`

```php
Route::middleware(['auth'])->prefix('hos-commands')->group(function () {
    Route::get('/', [HosCommandController::class, 'index'])->name('hos-commands.index');
    Route::get('/{hosCommand}', [HosCommandController::class, 'show'])->name('hos-commands.show');
});
```

## Phase 6: Testing

### 6.1 Feature Tests

**File**: `tests/Feature/HosCommandTest.php`

Test cases:

- Create command when price is updated
- Get pending commands endpoint returns correct data
- Acknowledge command endpoint updates status correctly
- Commands are filtered by station
- Failed commands store error messages
- Commands are limited to 10 per request

### 6.2 Integration Tests

**File**: `tests/Feature/FuelGradePriceUpdateTest.php`

Test cases:

- Admin can update fuel grade price
- Admin can schedule fuel grade price
- Command is created in database
- Command appears in pending commands endpoint
- Command status updates after acknowledgment

## Implementation Checklist

- [ ] Create hos_commands migration
- [ ] Create HosCommand model with relationships and methods
- [ ] Add getPendingCommands method to SyncController
- [ ] Add acknowledgeCommand method to SyncController
- [ ] Add API routes for command endpoints
- [ ] Create/update FuelGradeController with updatePrice and schedulePrice methods
- [ ] Add web routes for price update endpoints
- [ ] Update fuel grades index view with price update UI
- [ ] Create price update form partial
- [ ] Create HosCommandController for status tracking
- [ ] Add command status routes
- [ ] Write feature tests for command queue
- [ ] Write integration tests for price updates
- [ ] Test with BOS polling simulation
- [ ] Update API documentation

## Notes

- Commands are processed in FIFO order (oldest first)
- Maximum 10 commands per polling request
- Commands remain in 'processing' state until acknowledged
- Failed commands store error messages for debugging
- Retry count can be used for future retry logic
- Command data is stored as JSON for flexibility

### To-dos

- [ ] Create hos_commands migration with all required fields and indexes
- [ ] Create HosCommand model with relationships, casts, and status management methods
- [ ] Add getPendingCommands and acknowledgeCommand methods to SyncController
- [ ] Add API routes for pending-commands and acknowledge-command endpoints
- [ ] Create/update FuelGradeController with updatePrice and schedulePrice methods
- [ ] Add web routes for fuel grade price update endpoints
- [ ] Update fuel grades index view with price update UI elements
- [ ] Create HosCommandController for viewing command status
- [ ] Write feature tests for command queue functionality
- [ ] Write integration tests for price update flow