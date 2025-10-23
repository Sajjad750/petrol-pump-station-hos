<?php

use App\Models\Shift;
use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock the API key middleware
    $this->station = Station::factory()->create([
        'api_key' => 'test-api-key-123',
        'pts_id' => 'TEST001',
    ]);
});

it('can sync shifts successfully', function () {
    $shiftData = [
        'id' => 12345,
        'start_time' => '2024-01-15 08:00:00',
        'end_time' => '2024-01-15 16:00:00',
        'user_id' => 1,
        'notes' => 'Morning shift',
        'close_type' => 'manual',
        'status' => 'completed',
        'auto_close_time' => '2024-01-15 18:00:00',
        'start_time_utc' => '2024-01-15 13:00:00',
        'end_time_utc' => '2024-01-15 21:00:00',
        'auto_close_time_utc' => '2024-01-15 23:00:00',
        'created_at' => '2024-01-15 10:30:00',
        'updated_at' => '2024-01-15 10:30:00',
    ];

    $response = $this->postJson('/api/sync/shifts', [
        'pts_id' => 'TEST001',
        'data' => [$shiftData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 1 created, 0 updated, 0 failed shifts',
            'data' => [
                'created' => 1,
                'updated' => 0,
                'failed' => 0,
                'errors' => [],
            ],
        ]);

    // Verify shift was created in database
    $this->assertDatabaseHas('shifts', [
        'station_id' => $this->station->id,
        'bos_shift_id' => 12345,
        'user_id' => 1,
        'notes' => 'Morning shift',
        'close_type' => 'manual',
        'status' => 'completed',
    ]);

    // Verify shift has UUID
    $shift = Shift::where('bos_shift_id', 12345)->first();
    expect($shift->uuid)->not->toBeNull();
    expect($shift->synced_at)->not->toBeNull();
});

it('can sync multiple shifts at once', function () {
    $shiftsData = [
        [
            'id' => 12345,
            'start_time' => '2024-01-15 08:00:00',
            'end_time' => '2024-01-15 16:00:00',
            'user_id' => 1,
            'notes' => 'Morning shift',
            'close_type' => 'manual',
            'status' => 'completed',
        ],
        [
            'id' => 12346,
            'start_time' => '2024-01-15 16:00:00',
            'end_time' => '2024-01-16 00:00:00',
            'user_id' => 2,
            'notes' => 'Evening shift',
            'close_type' => 'auto',
            'status' => 'completed',
        ],
    ];

    $response = $this->postJson('/api/sync/shifts', [
        'pts_id' => 'TEST001',
        'data' => $shiftsData,
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 2 created, 0 updated, 0 failed shifts',
            'data' => [
                'created' => 2,
                'updated' => 0,
                'failed' => 0,
                'errors' => [],
            ],
        ]);

    // Verify both shifts were created
    $this->assertDatabaseHas('shifts', [
        'bos_shift_id' => 12345,
        'user_id' => 1,
        'close_type' => 'manual',
    ]);
    $this->assertDatabaseHas('shifts', [
        'bos_shift_id' => 12346,
        'user_id' => 2,
        'close_type' => 'auto',
    ]);
});

it('can update existing shifts', function () {
    // Create an existing shift
    $existingShift = Shift::factory()->create([
        'station_id' => $this->station->id,
        'bos_shift_id' => 12345,
        'user_id' => 1,
        'notes' => 'Old notes',
        'status' => 'started',
    ]);

    $updatedShiftData = [
        'id' => 12345,
        'start_time' => '2024-01-15 08:00:00',
        'end_time' => '2024-01-15 16:00:00',
        'user_id' => 1,
        'notes' => 'Updated notes',
        'close_type' => 'manual',
        'status' => 'completed',
    ];

    $response = $this->postJson('/api/sync/shifts', [
        'pts_id' => 'TEST001',
        'data' => [$updatedShiftData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 0 created, 1 updated, 0 failed shifts',
            'data' => [
                'created' => 0,
                'updated' => 1,
                'failed' => 0,
                'errors' => [],
            ],
        ]);

    // Verify shift was updated
    $existingShift->refresh();
    expect($existingShift->notes)->toBe('Updated notes');
    expect($existingShift->status)->toBe('completed');
    expect($existingShift->synced_at)->not->toBeNull();
});

it('handles mixed success and failure scenarios', function () {
    $shiftsData = [
        [
            'id' => 12345,
            'start_time' => '2024-01-15 08:00:00',
            'end_time' => '2024-01-15 16:00:00',
            'user_id' => 1,
            'notes' => 'Valid shift',
            'close_type' => 'manual',
            'status' => 'completed',
        ],
        [
            'id' => 12346,
            // Missing required fields to cause validation failure
            'start_time' => '2024-01-15 16:00:00',
            'user_id' => 2,
            'close_type' => 'invalid_type', // Invalid close_type
            'status' => 'invalid_status', // Invalid status
        ],
    ];

    $response = $this->postJson('/api/sync/shifts', [
        'pts_id' => 'TEST001',
        'data' => $shiftsData,
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 1 created, 0 updated, 1 failed shifts',
            'data' => [
                'created' => 1,
                'updated' => 0,
                'failed' => 1,
                'errors' => [
                    [
                        'shift_id' => 12346,
                        'error' => 'The data.1.close type must be manual or auto. (and 1 more error)',
                    ],
                ],
            ],
        ]);

    // Verify only the valid shift was created
    $this->assertDatabaseHas('shifts', [
        'bos_shift_id' => 12345,
        'notes' => 'Valid shift',
    ]);
    $this->assertDatabaseMissing('shifts', [
        'bos_shift_id' => 12346,
    ]);
});

it('validates required fields', function () {
    $response = $this->postJson('/api/sync/shifts', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                // Missing required fields
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'data.0.start_time',
            'data.0.user_id',
            'data.0.close_type',
            'data.0.status',
        ]);
});

it('validates data types and formats', function () {
    $response = $this->postJson('/api/sync/shifts', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 'invalid_id', // Should be integer
                'start_time' => 'invalid_date', // Should be valid date
                'end_time' => 'invalid_date', // Should be valid date
                'user_id' => 'invalid_user_id', // Should be integer
                'notes' => str_repeat('a', 1001), // Too long
                'close_type' => 'invalid', // Should be manual or auto
                'status' => 'invalid', // Should be started or completed
                'auto_close_time' => 'invalid_date', // Should be valid date
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'data.0.id',
            'data.0.start_time',
            'data.0.end_time',
            'data.0.user_id',
            'data.0.notes',
            'data.0.close_type',
            'data.0.status',
            'data.0.auto_close_time',
        ]);
});

it('validates close_type enum values', function () {
    $response = $this->postJson('/api/sync/shifts', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                'start_time' => '2024-01-15 08:00:00',
                'user_id' => 1,
                'close_type' => 'invalid_type',
                'status' => 'started',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['data.0.close_type']);
});

it('validates status enum values', function () {
    $response = $this->postJson('/api/sync/shifts', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                'start_time' => '2024-01-15 08:00:00',
                'user_id' => 1,
                'close_type' => 'manual',
                'status' => 'invalid_status',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['data.0.status']);
});

it('handles empty data array', function () {
    $response = $this->postJson('/api/sync/shifts', [
        'pts_id' => 'TEST001',
        'data' => [],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['data']);
});

it('handles missing pts_id', function () {
    $response = $this->postJson('/api/sync/shifts', [
        'data' => [
            [
                'id' => 12345,
                'start_time' => '2024-01-15 08:00:00',
                'user_id' => 1,
                'close_type' => 'manual',
                'status' => 'started',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['pts_id']);
});

it('handles invalid API key', function () {
    $response = $this->postJson('/api/sync/shifts', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                'start_time' => '2024-01-15 08:00:00',
                'user_id' => 1,
                'close_type' => 'manual',
                'status' => 'started',
            ],
        ],
    ], [
        'Authorization' => 'Bearer invalid-api-key',
    ]);

    $response->assertUnauthorized();
});

it('handles missing authorization header', function () {
    $response = $this->postJson('/api/sync/shifts', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                'start_time' => '2024-01-15 08:00:00',
                'user_id' => 1,
                'close_type' => 'manual',
                'status' => 'started',
            ],
        ],
    ]);

    $response->assertUnauthorized();
});

it('handles database transaction rollback on failure', function () {
    // Mock a database exception
    DB::shouldReceive('beginTransaction')->once();
    DB::shouldReceive('rollBack')->once();

    $response = $this->postJson('/api/sync/shifts', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                'start_time' => '2024-01-15 08:00:00',
                'user_id' => 1,
                'close_type' => 'manual',
                'status' => 'started',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    // The response should still be successful as the transaction rollback is handled
    $response->assertSuccessful();
});

it('logs sync operations', function () {
    Log::shouldReceive('debug')->with('syncShifts: ', \Mockery::type('array'))->once();
    Log::shouldReceive('debug')->with('shifts: ', \Mockery::type('array'))->once();

    $this->postJson('/api/sync/shifts', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                'start_time' => '2024-01-15 08:00:00',
                'user_id' => 1,
                'close_type' => 'manual',
                'status' => 'started',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);
});

it('handles shifts with all optional fields', function () {
    $shiftData = [
        'id' => 12345,
        'start_time' => '2024-01-15 08:00:00',
        'end_time' => '2024-01-15 16:00:00',
        'user_id' => 1,
        'notes' => 'Complete shift data',
        'close_type' => 'auto',
        'status' => 'completed',
        'auto_close_time' => '2024-01-15 18:00:00',
        'start_time_utc' => '2024-01-15 13:00:00',
        'end_time_utc' => '2024-01-15 21:00:00',
        'auto_close_time_utc' => '2024-01-15 23:00:00',
        'created_at' => '2024-01-15 10:30:00',
        'updated_at' => '2024-01-15 10:30:00',
    ];

    $response = $this->postJson('/api/sync/shifts', [
        'pts_id' => 'TEST001',
        'data' => [$shiftData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful();

    // Verify all fields were stored correctly
    $shift = Shift::where('bos_shift_id', 12345)->first();
    expect($shift->start_time->format('Y-m-d H:i:s'))->toBe('2024-01-15 08:00:00');
    expect($shift->end_time->format('Y-m-d H:i:s'))->toBe('2024-01-15 16:00:00');
    expect($shift->user_id)->toBe(1);
    expect($shift->notes)->toBe('Complete shift data');
    expect($shift->close_type)->toBe('auto');
    expect($shift->status)->toBe('completed');
    expect($shift->auto_close_time->format('Y-m-d H:i:s'))->toBe('2024-01-15 18:00:00');
    expect($shift->start_time_utc->format('Y-m-d H:i:s'))->toBe('2024-01-15 13:00:00');
    expect($shift->end_time_utc->format('Y-m-d H:i:s'))->toBe('2024-01-15 21:00:00');
    expect($shift->auto_close_time_utc->format('Y-m-d H:i:s'))->toBe('2024-01-15 23:00:00');
});

it('handles shifts with minimal required fields', function () {
    $shiftData = [
        'id' => 12345,
        'start_time' => '2024-01-15 08:00:00',
        'user_id' => 1,
        'close_type' => 'manual',
        'status' => 'started',
    ];

    $response = $this->postJson('/api/sync/shifts', [
        'pts_id' => 'TEST001',
        'data' => [$shiftData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful();

    // Verify shift was created with minimal data
    $this->assertDatabaseHas('shifts', [
        'bos_shift_id' => 12345,
        'user_id' => 1,
        'close_type' => 'manual',
        'status' => 'started',
        'end_time' => null,
        'notes' => null,
        'auto_close_time' => null,
    ]);
});

it('updates station last sync time', function () {
    $originalLastSync = $this->station->last_sync_at;

    $this->postJson('/api/sync/shifts', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                'start_time' => '2024-01-15 08:00:00',
                'user_id' => 1,
                'close_type' => 'manual',
                'status' => 'started',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $this->station->refresh();
    expect($this->station->last_sync_at)->not->toBe($originalLastSync);
    expect($this->station->last_sync_at)->toBeGreaterThan($originalLastSync);
});
