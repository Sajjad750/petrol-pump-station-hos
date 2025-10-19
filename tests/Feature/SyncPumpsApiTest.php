<?php

use App\Models\Pump;
use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can sync pumps with valid API key', function () {
    // Create a test station
    $station = Station::create([
        'pts_id' => 'TEST_STATION_001',
        'site_name' => 'Test Station',
        'is_active' => true,
        'api_key' => 'test_api_key_123',
        'connectivity_status' => 'unknown',
    ]);

    $pumpData = [
        'pts_id' => 'TEST_STATION_001',
        'data' => [
            [
                'id' => 1,
                'uuid' => '550e8400-e29b-41d4-a716-446655440000',
                'name' => 'Pump 1',
                'pump_id' => 'P001',
                'is_self_service' => true,
                'nozzles_count' => 4,
                'status' => 'active',
                'pts_pump_id' => 1,
                'pts_port_id' => 1,
                'pts_address_id' => 1,
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-01 10:00:00',
            ],
        ],
    ];

    $response = $this->postJson('/api/sync/pumps', $pumpData, [
        'Authorization' => 'Bearer test_api_key_123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'created' => 1,
                'updated' => 0,
                'failed' => 0,
            ],
        ]);

    // Verify pump was created
    $this->assertDatabaseHas('pumps', [
        'station_id' => $station->id,
        'bos_pump_id' => 1,
        'name' => 'Pump 1',
        'pump_id' => 'P001',
        'is_self_service' => true,
        'nozzles_count' => 4,
        'status' => 'active',
    ]);

    // Verify sync log was created
    $this->assertDatabaseHas('sync_logs', [
        'station_id' => $station->id,
        'table_name' => 'pumps',
        'operation' => 'create',
        'status' => 'success',
    ]);
});

it('rejects requests with invalid API key', function () {
    $pumpData = [
        'pts_id' => 'TEST_STATION_001',
        'data' => [],
    ];

    $response = $this->postJson('/api/sync/pumps', $pumpData, [
        'Authorization' => 'Bearer invalid_api_key',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid API key',
        ]);
});

it('rejects requests without authorization header', function () {
    $pumpData = [
        'pts_id' => 'TEST_STATION_001',
        'data' => [],
    ];

    $response = $this->postJson('/api/sync/pumps', $pumpData);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Authorization header is required',
        ]);
});

it('handles missing required pump fields gracefully', function () {
    // Create a test station
    $station = Station::create([
        'pts_id' => 'TEST_STATION_001',
        'site_name' => 'Test Station',
        'is_active' => true,
        'api_key' => 'test_api_key_123',
        'connectivity_status' => 'unknown',
    ]);

    $invalidPumpData = [
        'pts_id' => 'TEST_STATION_001',
        'data' => [
            [
                'id' => 1,
                // Missing required fields
            ],
        ],
    ];

    $response = $this->postJson('/api/sync/pumps', $invalidPumpData, [
        'Authorization' => 'Bearer test_api_key_123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'created' => 0,
                'updated' => 0,
                'failed' => 1,
            ],
        ]);

    // Verify no pump was created
    $this->assertDatabaseCount('pumps', 0);
});

it('handles duplicate pumps correctly', function () {
    // Create a test station
    $station = Station::create([
        'pts_id' => 'TEST_STATION_001',
        'site_name' => 'Test Station',
        'is_active' => true,
        'api_key' => 'test_api_key_123',
        'connectivity_status' => 'unknown',
    ]);

    $pumpData = [
        'pts_id' => 'TEST_STATION_001',
        'data' => [
            [
                'id' => 1,
                'uuid' => '550e8400-e29b-41d4-a716-446655440000',
                'name' => 'Pump 1',
                'pump_id' => 'P001',
                'is_self_service' => true,
                'nozzles_count' => 4,
                'status' => 'active',
                'pts_pump_id' => 1,
                'pts_port_id' => 1,
                'pts_address_id' => 1,
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-01 10:00:00',
            ],
        ],
    ];

    // First sync
    $response1 = $this->postJson('/api/sync/pumps', $pumpData, [
        'Authorization' => 'Bearer test_api_key_123',
    ]);

    $response1->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'created' => 1,
                'updated' => 0,
                'failed' => 0,
            ],
        ]);

    // Second sync with same pump (should update)
    $pumpData['data'][0]['name'] = 'Updated Pump 1';
    $pumpData['data'][0]['nozzles_count'] = 6;

    $response2 = $this->postJson('/api/sync/pumps', $pumpData, [
        'Authorization' => 'Bearer test_api_key_123',
    ]);

    $response2->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'created' => 0,
                'updated' => 1,
                'failed' => 0,
            ],
        ]);

    // Verify only one pump exists with updated values
    $this->assertDatabaseCount('pumps', 1);
    $this->assertDatabaseHas('pumps', [
        'station_id' => $station->id,
        'bos_pump_id' => 1,
        'name' => 'Updated Pump 1',
        'nozzles_count' => 6,
    ]);
});

it('can sync multiple pumps in batch', function () {
    // Create a test station
    $station = Station::create([
        'pts_id' => 'TEST_STATION_001',
        'site_name' => 'Test Station',
        'is_active' => true,
        'api_key' => 'test_api_key_123',
        'connectivity_status' => 'unknown',
    ]);

    $pumpData = [
        'pts_id' => 'TEST_STATION_001',
        'data' => [
            [
                'id' => 1,
                'uuid' => '550e8400-e29b-41d4-a716-446655440000',
                'name' => 'Pump 1',
                'pump_id' => 'P001',
                'is_self_service' => true,
                'nozzles_count' => 4,
                'status' => 'active',
                'pts_pump_id' => 1,
                'pts_port_id' => 1,
                'pts_address_id' => 1,
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-01 10:00:00',
            ],
            [
                'id' => 2,
                'uuid' => '550e8400-e29b-41d4-a716-446655440001',
                'name' => 'Pump 2',
                'pump_id' => 'P002',
                'is_self_service' => false,
                'nozzles_count' => 2,
                'status' => 'active',
                'pts_pump_id' => 2,
                'pts_port_id' => 2,
                'pts_address_id' => 2,
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-01 10:00:00',
            ],
        ],
    ];

    $response = $this->postJson('/api/sync/pumps', $pumpData, [
        'Authorization' => 'Bearer test_api_key_123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'created' => 2,
                'updated' => 0,
                'failed' => 0,
            ],
        ]);

    // Verify both pumps were created
    $this->assertDatabaseCount('pumps', 2);
    $this->assertDatabaseHas('pumps', [
        'station_id' => $station->id,
        'bos_pump_id' => 1,
        'name' => 'Pump 1',
        'is_self_service' => true,
    ]);
    $this->assertDatabaseHas('pumps', [
        'station_id' => $station->id,
        'bos_pump_id' => 2,
        'name' => 'Pump 2',
        'is_self_service' => false,
    ]);
});
