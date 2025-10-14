<!-- 7fae6098-3b8e-4955-9ffb-3a367e66abb5 3c43bbb9-50b7-4201-bb71-eae5489922d5 -->
# HOS Implementation Plan

## Phase 1: Database Schema & Models (Priority: High)

### 1.1 Create Stations Table Migration

**File**: `database/migrations/YYYY_MM_DD_create_stations_table.php`

Create stations table to store information about all connected BOS sites:

- `id`, `pts_id` (unique from BOS), `site_name`, `type`, `dealer`
- Location fields: `country`, `region`, `city`, `district`, `address`
- Contact: `phone`, `email`, `notes`
- Sync tracking: `is_active`, `api_key` (encrypted), `last_sync_at`
- Timestamps and soft deletes

Add unique index on `pts_id` and index on `is_active`.

### 1.2 Create Pump Transactions Table Migration

**File**: `database/migrations/YYYY_MM_DD_create_pump_transactions_table.php`

Mirror BOS pump_transactions structure with HOS-specific additions:

- All BOS fields: `uuid`, `pts2_device_id`, `pts_id`, `request_id`, datetime fields, pump/nozzle/fuel/tank IDs, transaction details
- HOS additions: `station_id` (FK to stations), `bos_transaction_id` (original BOS ID), `bos_uuid`, `synced_at`, `created_at_bos`, `updated_at_bos`
- Indexes: `[station_id, date_time_start]`, `[pts_id]`, `[date_time_start]`
- Unique constraint: `[station_id, bos_transaction_id]` to prevent duplicates

### 1.3 Create Sync Logs Table Migration

**File**: `database/migrations/YYYY_MM_DD_create_sync_logs_table.php`

Track all sync operations from BOS:

- `id`, `station_id` (FK), `table_name`, `operation` (create/update/delete)
- `request_payload` (JSON), `response_data` (JSON), `status` (success/failed/pending)
- `error_message`, `synced_at`, `timestamps`
- Composite index: `[station_id, table_name, status]`

### 1.4 Create Models

**Files**:

- `app/Models/Station.php` - with relationships to all sync tables
- `app/Models/PumpTransaction.php` - with station relationship, scopes for reporting
- `app/Models/SyncLog.php` - for tracking sync operations

Add appropriate casts, fillable fields, and relationships.

## Phase 2: API Endpoints for BOS Sync (Priority: High)

### 2.1 Create Sync Controller

**File**: `app/Http/Controllers/Api/SyncController.php`

Implement primary endpoint: `syncPumpTransactions(SyncPumpTransactionRequest $request)`

**Logic flow**:

1. Validate API key and extract `pts_id` from bearer token
2. Find or create station by `pts_id`
3. Validate incoming data structure
4. Loop through transactions and use `updateOrCreate` with `[station_id, bos_transaction_id]`
5. Log each operation to sync_logs table
6. Return JSON response with success/error counts

**Response format**:

```json
{
  "success": true,
  "message": "Synced 15 transactions",
  "data": {
    "created": 10,
    "updated": 5,
    "failed": 0
  }
}
```

### 2.2 Create Form Request Validation

**File**: `app/Http/Requests/SyncPumpTransactionRequest.php`

Validate incoming payload structure:

- `pts_id` - required, string
- `data` - required, array
- `data.*.id` - required (BOS transaction ID)
- `data.*.uuid` - required, uuid
- All required transaction fields with appropriate rules

### 2.3 Create API Authentication Middleware

**File**: `app/Http/Middleware/ValidateBosApiKey.php`

Extract and validate API key from Authorization header:

- Parse `Bearer {token}` format
- Find station by `api_key` (encrypted in DB)
- Attach station to request: `$request->merge(['station' => $station])`
- Return 401 if invalid
- Implement rate limiting: 120 requests per minute per station

### 2.4 Define API Routes

**File**: `routes/api.php`

```php
Route::prefix('sync')->middleware(['bos.api.key', 'throttle:120,1'])->group(function () {
    Route::post('/pump-transactions', [SyncController::class, 'syncPumpTransactions']);
    // Future endpoints: tank-measurements, tank-deliveries, alert-records
});
```

## Phase 3: Dashboard Implementation (Priority: High)

### 3.1 Enhance Dashboard Controller

**File**: `app/Http/Controllers/DashboardController.php`

The dashboard view is already created. Add API methods for dynamic data:

- `getSiteStatusData()` - Return counts for status cards
- `getSalesSummaryData(Request $request)` - Aggregate sales for line chart
- `getProductSalesData(Request $request)` - Fuel grade distribution
- `getTopSitesData(Request $request)` - Top 5 sites by sales
- `getInventoryForecastData()` - Tank dry-out predictions (placeholder for Phase 5)

All methods return JSON for AJAX updates.

### 3.2 Create Dashboard Service

**File**: `app/Services/DashboardService.php`

Implement data aggregation with caching:

**Method**: `calculateSiteStatus()`

- Count total stations
- Count stations with transactions in last 5 minutes (online)
- Count stations with no transactions for 5-30 min (warning)
- Count stations offline > 30 min
- Cache for 1 minute

**Method**: `aggregateSalesData($period, $groupBy = 'day')`

- Query pump_transactions by date range
- Group by day/hour and sum volume/amount
- Return formatted data for Chart.js
- Cache for 5 minutes

**Method**: `getProductDistribution($period)`

- Group by fuel_grade_id and sum volume
- Calculate percentages
- Cache for 5 minutes

**Method**: `rankSitesBySales($period, $limit = 5)`

- Group by station_id, order by total amount DESC
- Limit to top N sites
- Cache for 5 minutes

### 3.3 Add AJAX Routes for Dashboard

**File**: `routes/web.php`

```php
Route::middleware(['auth'])->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/site-status', [DashboardController::class, 'getSiteStatusData']);
    Route::get('/sales-summary', [DashboardController::class, 'getSalesSummaryData']);
    Route::get('/product-sales', [DashboardController::class, 'getProductSalesData']);
    Route::get('/top-sites', [DashboardController::class, 'getTopSitesData']);
});
```

### 3.4 Update Dashboard Blade to Use Real Data

**File**: `resources/views/dashboard.blade.php`

Replace mock data in JavaScript with AJAX calls:

- On page load, fetch all chart data
- Set up 30-second interval for auto-refresh
- Update charts using Chart.js update methods
- Show loading states during data fetch

Example for site status cards:

```javascript
function loadSiteStatus() {
    $.get('/dashboard/site-status', function(data) {
        $('#total-sites').text(data.total);
        $('#online-sites').text(data.online);
        $('#warning-sites').text(data.warning);
        $('#offline-sites').text(data.offline);
    });
}
```

## Phase 4: Operations Monitor (Priority: Medium)

### 4.1 Create Operations Monitor Page

**File**: `resources/views/operations/index.blade.php`

Similar to dashboard but focused on site-by-site monitoring:

- Summary cards (total sites, total pumps, total tanks)
- DataTable with all sites and their current status
- Real-time status indicators
- Action buttons (View Details, Close Shift - placeholder)

### 4.2 Create Operations Controller

**File**: `app/Http/Controllers/OperationsController.php`

Methods:

- `index()` - Operations monitor view
- `getSitesList()` - Return all stations with calculated status
- `getSiteDetails($stationId)` - Detailed site information
- `getSummaries()` - Calculate pump/tank summaries across all sites

### 4.3 Create Site Management Service

**File**: `app/Services/SiteManagementService.php`

Methods:

- `updateSiteStatus($stationId)` - Calculate and update site connectivity
- `getLastActivity($stationId)` - Get last transaction/sync time
- `getSiteHealthScore($stationId)` - Calculate health metric (0-100)

## Phase 5: Background Jobs & Automation (Priority: Medium)

### 5.1 Create Site Status Update Job

**File**: `app/Jobs/UpdateSiteStatusJob.php`

Run every minute to update all station statuses:

- Loop through all active stations
- Check last transaction time
- Update connectivity_status field
- Trigger alerts if site goes offline

### 5.2 Create Sync Monitoring Job

**File**: `app/Jobs/MonitorSyncHealthJob.php`

Run every 5 minutes:

- Check for failed syncs in sync_logs
- Count consecutive failures per station
- Send notifications if threshold exceeded

### 5.3 Register Scheduled Tasks

**File**: `routes/console.php`

```php
Schedule::job(new UpdateSiteStatusJob())->everyMinute();
Schedule::job(new MonitorSyncHealthJob())->everyFiveMinutes();
```

## Phase 6: Testing (Priority: High)

### 6.1 API Sync Tests

**File**: `tests/Feature/SyncApiTest.php`

Test cases:

- Valid pump transaction sync (single and batch)
- Invalid API key rejection
- Duplicate transaction handling (updateOrCreate)
- Malformed payload rejection
- Rate limiting enforcement

### 6.2 Dashboard Tests

**File**: `tests/Feature/DashboardTest.php`

Test cases:

- Dashboard loads successfully
- Site status calculations are correct
- Sales aggregations match expected values
- Caching works properly

### 6.3 Service Unit Tests

**File**: `tests/Unit/Services/DashboardServiceTest.php`

Test each service method with mock data.

## Phase 7: Configuration & Deployment (Priority: Medium)

### 7.1 Environment Configuration

Update `.env.example` with HOS-specific variables:

```env
# BOS API Configuration
BOS_API_KEY_ENCRYPTION_KEY=

# Dashboard Settings
DASHBOARD_CACHE_TTL=300
SITE_ONLINE_THRESHOLD=5
SITE_WARNING_THRESHOLD=30

# Sync Monitoring
SYNC_FAILURE_ALERT_THRESHOLD=3
```

### 7.2 Create HOS Config File

**File**: `config/hos.php`

Centralize all HOS-specific configuration:

- Site status thresholds
- Cache TTLs
- Sync monitoring settings
- Alert thresholds

### 7.3 Database Optimization

- Add composite indexes for common queries
- Set up query result caching with Redis
- Configure database connection pooling

### 7.4 Generate Station API Keys

Create artisan command to generate API keys for stations:

**File**: `app/Console/Commands/GenerateStationApiKey.php`

```bash
php artisan hos:generate-api-key {pts_id}
```

Output encrypted key to be configured in BOS `.env`

## Implementation Checklist

**Week 1: Core Sync Infrastructure**

- [ ] Create stations, pump_transactions, sync_logs migrations
- [ ] Create corresponding models with relationships
- [ ] Implement ValidateBosApiKey middleware
- [ ] Create SyncController with syncPumpTransactions method
- [ ] Create SyncPumpTransactionRequest validation
- [ ] Define API routes with rate limiting
- [ ] Test API endpoint with Postman/curl
- [ ] Write feature tests for sync API

**Week 2: Dashboard Backend**

- [ ] Create DashboardService with all aggregation methods
- [ ] Implement caching strategy with Redis
- [ ] Add dashboard API routes
- [ ] Test all dashboard data endpoints
- [ ] Write unit tests for DashboardService

**Week 3: Dashboard Frontend**

- [ ] Update dashboard.blade.php with AJAX data loading
- [ ] Implement auto-refresh functionality
- [ ] Add loading states and error handling
- [ ] Test with real data from BOS
- [ ] Optimize chart rendering performance

**Week 4: Operations Monitor**

- [ ] Create operations/index.blade.php view
- [ ] Implement OperationsController
- [ ] Create SiteManagementService
- [ ] Add operations routes
- [ ] Test with multiple stations

**Week 5: Background Jobs**

- [ ] Create UpdateSiteStatusJob
- [ ] Create MonitorSyncHealthJob
- [ ] Register scheduled tasks
- [ ] Test job execution
- [ ] Set up queue workers

**Week 6: Testing & Deployment**

- [ ] Run full test suite
- [ ] Fix any failing tests
- [ ] Run Laravel Pint for code formatting
- [ ] Update documentation
- [ ] Deploy to staging environment
- [ ] Configure BOS to point to HOS API
- [ ] Monitor first real sync
- [ ] Deploy to production

## Integration with BOS

BOS already has the sync infrastructure in place:

- `HosSyncService` - Handles HTTP requests to HOS
- `SyncToHosJob` - Queue-based sync with retries
- `PumpTransactionCreated` event - Triggers on new transactions
- `SyncPumpTransactionToHos` listener - Attempts immediate sync, queues on failure

**HOS must provide**:

1. `/api/sync/pump-transactions` endpoint
2. Accept `pts_id` and transaction data array
3. Return success/error response
4. Handle duplicate transactions gracefully
5. Provide API key per station

**BOS Configuration** (after HOS deployment):

Update BOS `.env`:

```env
HOS_API_URL=https://hos.example.com
HOS_API_KEY={generated_station_api_key}
HOS_SYNC_ENABLED=true
```

## Key Technical Decisions

1. **Station Identification**: Use `pts_id` from BOS as unique identifier
2. **Deduplication Strategy**: Unique constraint on `[station_id, bos_transaction_id]`
3. **API Authentication**: Bearer token (station API key) with rate limiting
4. **Sync Method**: BOS pushes to HOS (real-time) + HOS can query status
5. **Caching**: Redis with 1-5 minute TTL for dashboard data
6. **Queue**: Database queue driver for background jobs
7. **Real-time Updates**: 30-second AJAX polling (WebSocket in Phase 8)
8. **Testing**: Pest framework with feature and unit tests

## Future Enhancements (Post-MVP)

- Phase 8: Add tank_measurements, tank_deliveries, alert_records sync
- Phase 9: Implement comprehensive reporting module
- Phase 10: Add WebSocket support via Laravel Reverb
- Phase 11: Implement inventory forecasting
- Phase 12: Add user roles and permissions
- Phase 13: Create mobile-responsive views
- Phase 14: Add export functionality (Excel/PDF)

### To-dos

- [ ] Create database migrations for stations, pump_transactions, and sync_logs tables
- [ ] Create Eloquent models (Station, PumpTransaction, SyncLog) with relationships
- [ ] Implement ValidateBosApiKey middleware for API authentication
- [ ] Create SyncController with syncPumpTransactions endpoint
- [ ] Create SyncPumpTransactionRequest for payload validation
- [ ] Define API routes with middleware and rate limiting
- [ ] Create DashboardService with data aggregation methods and caching
- [ ] Add dashboard API endpoints for AJAX data loading
- [ ] Update dashboard.blade.php to load real data via AJAX
- [ ] Create operations monitor view and controller
- [ ] Create and schedule background jobs for site status updates
- [ ] Write feature and unit tests for sync API and services
- [ ] Create artisan command to generate station API keys
- [ ] Deploy HOS and configure BOS to sync data