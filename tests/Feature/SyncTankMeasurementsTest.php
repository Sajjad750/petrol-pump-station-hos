<?php

use App\Models\Station;
use App\Models\TankMeasurement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock the API key middleware
    $this->station = Station::factory()->create([
        'api_key' => 'test-api-key-123',
        'pts_id' => 'TEST001',
    ]);
});

it('can sync tank measurements successfully', function () {
    $tankMeasurementData = [
        'id' => 12345,
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'request_id' => 1001,
        'pts_id' => 'TEST001',
        'date_time' => '2024-01-15 10:30:00',
        'tank' => 1,
        'fuel_grade_id' => 1,
        'fuel_grade_name' => 'Regular',
        'status' => 'active',
        'alarms' => null,
        'product_height' => 12.5,
        'water_height' => 0.2,
        'temperature' => 25.5,
        'product_volume' => 15000.0,
        'water_volume' => 200.0,
        'product_ullage' => 5000.0,
        'product_tc_volume' => 15000.0,
        'product_density' => 0.85,
        'product_mass' => 12750.0,
        'tank_filling_percentage' => 75.0,
        'configuration_id' => 1,
        'created_at' => '2024-01-15 10:30:00',
        'updated_at' => '2024-01-15 10:30:00',
    ];

    $response = $this->postJson('/api/sync/tank-measurements', [
        'pts_id' => 'TEST001',
        'data' => [$tankMeasurementData],
    ], [
        'X-API-Key' => 'test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 1 created, 0 updated, 0 failed tank measurements',
            'data' => [
                'created' => 1,
                'updated' => 0,
                'failed' => 0,
                'errors' => [],
            ],
        ]);

    // Verify tank measurement was created
    $this->assertDatabaseHas('tank_measurements', [
        'station_id' => $this->station->id,
        'bos_tank_measurement_id' => 12345,
        'tank' => 1,
        'product_volume' => 15000.0,
        'tank_filling_percentage' => 75.0,
    ]);

    // Verify sync log was created
    $this->assertDatabaseHas('sync_logs', [
        'station_id' => $this->station->id,
        'entity_type' => 'tank_measurements',
        'action' => 'create',
        'status' => 'success',
    ]);

    // Verify station last sync was updated
    $this->station->refresh();
    expect($this->station->last_sync_at)->not->toBeNull();
});

it('can handle duplicate tank measurements by updating existing record', function () {
    // Create existing tank measurement
    $existingMeasurement = TankMeasurement::factory()->create([
        'station_id' => $this->station->id,
        'bos_tank_measurement_id' => 12345,
        'product_volume' => 10000.0,
        'tank_filling_percentage' => 50.0,
    ]);

    $updatedTankMeasurementData = [
        'id' => 12345,
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'request_id' => 1002,
        'pts_id' => 'TEST001',
        'date_time' => '2024-01-15 11:00:00',
        'tank' => 1,
        'fuel_grade_id' => 1,
        'fuel_grade_name' => 'Regular',
        'status' => 'active',
        'alarms' => 'Low Level',
        'product_height' => 15.0,
        'water_height' => 0.5,
        'temperature' => 26.0,
        'product_volume' => 18000.0,
        'water_volume' => 500.0,
        'product_ullage' => 2000.0,
        'product_tc_volume' => 18000.0,
        'product_density' => 0.85,
        'product_mass' => 15300.0,
        'tank_filling_percentage' => 90.0,
        'configuration_id' => 1,
        'created_at' => '2024-01-15 11:00:00',
        'updated_at' => '2024-01-15 11:00:00',
    ];

    $response = $this->postJson('/api/sync/tank-measurements', [
        'pts_id' => 'TEST001',
        'data' => [$updatedTankMeasurementData],
    ], [
        'X-API-Key' => 'test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 0 created, 1 updated, 0 failed tank measurements',
            'data' => [
                'created' => 0,
                'updated' => 1,
                'failed' => 0,
                'errors' => [],
            ],
        ]);

    // Verify tank measurement was updated
    $existingMeasurement->refresh();
    expect($existingMeasurement->product_volume)->toBe(18000.0);
    expect($existingMeasurement->tank_filling_percentage)->toBe(90.0);
    expect($existingMeasurement->alarms)->toBe('Low Level');
});

it('validates required fields', function () {
    $response = $this->postJson('/api/sync/tank-measurements', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                // Missing required fields: uuid, date_time, tank
            ],
        ],
    ], [
        'X-API-Key' => 'test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'data.0.uuid',
            'data.0.date_time',
            'data.0.tank',
        ]);
});

it('validates data types correctly', function () {
    $response = $this->postJson('/api/sync/tank-measurements', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 'invalid-id', // Should be integer
                'uuid' => 'invalid-uuid', // Should be valid UUID
                'date_time' => 'invalid-date', // Should be valid date
                'tank' => 'invalid-tank', // Should be integer
                'product_volume' => 'invalid-volume', // Should be numeric
                'tank_filling_percentage' => 150, // Should be max 100
            ],
        ],
    ], [
        'X-API-Key' => 'test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'data.0.id',
            'data.0.uuid',
            'data.0.date_time',
            'data.0.tank',
            'data.0.product_volume',
            'data.0.tank_filling_percentage',
        ]);
});

it('handles multiple tank measurements in single request', function () {
    $tankMeasurementsData = [
        [
            'id' => 12345,
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'request_id' => 1001,
            'pts_id' => 'TEST001',
            'date_time' => '2024-01-15 10:30:00',
            'tank' => 1,
            'fuel_grade_id' => 1,
            'fuel_grade_name' => 'Regular',
            'status' => 'active',
            'product_volume' => 15000.0,
            'tank_filling_percentage' => 75.0,
            'created_at' => '2024-01-15 10:30:00',
            'updated_at' => '2024-01-15 10:30:00',
        ],
        [
            'id' => 12346,
            'uuid' => '550e8400-e29b-41d4-a716-446655440001',
            'request_id' => 1002,
            'pts_id' => 'TEST001',
            'date_time' => '2024-01-15 10:35:00',
            'tank' => 2,
            'fuel_grade_id' => 2,
            'fuel_grade_name' => 'Premium',
            'status' => 'active',
            'product_volume' => 12000.0,
            'tank_filling_percentage' => 60.0,
            'created_at' => '2024-01-15 10:35:00',
            'updated_at' => '2024-01-15 10:35:00',
        ],
    ];

    $response = $this->postJson('/api/sync/tank-measurements', [
        'pts_id' => 'TEST001',
        'data' => $tankMeasurementsData,
    ], [
        'X-API-Key' => 'test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 2 created, 0 updated, 0 failed tank measurements',
            'data' => [
                'created' => 2,
                'updated' => 0,
                'failed' => 0,
                'errors' => [],
            ],
        ]);

    // Verify both tank measurements were created
    $this->assertDatabaseHas('tank_measurements', [
        'station_id' => $this->station->id,
        'bos_tank_measurement_id' => 12345,
        'tank' => 1,
    ]);

    $this->assertDatabaseHas('tank_measurements', [
        'station_id' => $this->station->id,
        'bos_tank_measurement_id' => 12346,
        'tank' => 2,
    ]);
});

it('handles partial failures gracefully', function () {
    $tankMeasurementsData = [
        [
            'id' => 12345,
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'date_time' => '2024-01-15 10:30:00',
            'tank' => 1,
            'product_volume' => 15000.0,
            'tank_filling_percentage' => 75.0,
        ],
        [
            'id' => 12346,
            'uuid' => 'invalid-uuid', // This will cause validation failure
            'date_time' => '2024-01-15 10:35:00',
            'tank' => 2,
            'product_volume' => 12000.0,
            'tank_filling_percentage' => 60.0,
        ],
    ];

    $response = $this->postJson('/api/sync/tank-measurements', [
        'pts_id' => 'TEST001',
        'data' => $tankMeasurementsData,
    ], [
        'X-API-Key' => 'test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['data.1.uuid']);
});

it('requires valid API key', function () {
    $response = $this->postJson('/api/sync/tank-measurements', [
        'pts_id' => 'TEST001',
        'data' => [],
    ], [
        'X-API-Key' => 'invalid-key',
    ]);

    $response->assertUnauthorized();
});

it('logs sync operations', function () {
    Log::shouldReceive('debug')->atLeast()->once();
    Log::shouldReceive('info')->atLeast()->once();

    $tankMeasurementData = [
        'id' => 12345,
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'date_time' => '2024-01-15 10:30:00',
        'tank' => 1,
        'product_volume' => 15000.0,
        'tank_filling_percentage' => 75.0,
    ];

    $this->postJson('/api/sync/tank-measurements', [
        'pts_id' => 'TEST001',
        'data' => [$tankMeasurementData],
    ], [
        'X-API-Key' => 'test-api-key-123',
    ]);
});
