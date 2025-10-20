<?php

use App\Models\Station;
use App\Models\TankDelivery;
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

it('can sync tank deliveries successfully', function () {
    $tankDeliveryData = [
        'id' => 12345,
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'request_id' => 1001,
        'pts_id' => 'TEST001',
        'pts_delivery_id' => 'DEL001',
        'tank' => 1,
        'fuel_grade_id' => 1,
        'fuel_grade_name' => 'Regular',
        'configuration_id' => 'CFG001',
        'start_datetime' => '2024-01-15 10:30:00',
        'start_product_height' => 12.5,
        'start_water_height' => 0.2,
        'start_temperature' => 25.5,
        'start_product_volume' => 15000.0,
        'start_product_tc_volume' => 15000.0,
        'start_product_density' => 0.85,
        'start_product_mass' => 12750.0,
        'end_datetime' => '2024-01-15 11:30:00',
        'end_product_height' => 15.0,
        'end_water_height' => 0.3,
        'end_temperature' => 26.0,
        'end_product_volume' => 18000.0,
        'end_product_tc_volume' => 18000.0,
        'end_product_density' => 0.85,
        'end_product_mass' => 15300.0,
        'received_product_volume' => 3000.0,
        'absolute_product_height' => 15.0,
        'absolute_water_height' => 0.3,
        'absolute_temperature' => 26.0,
        'absolute_product_volume' => 18000.0,
        'absolute_product_tc_volume' => 18000.0,
        'absolute_product_density' => 0.85,
        'absolute_product_mass' => 15300.0,
        'pumps_dispensed_volume' => 2500.0,
        'probe_data' => ['sensor1' => 'value1', 'sensor2' => 'value2'],
        'created_at' => '2024-01-15 10:30:00',
        'updated_at' => '2024-01-15 11:30:00',
    ];

    $response = $this->postJson('/api/sync/tank-deliveries', [
        'pts_id' => 'TEST001',
        'data' => [$tankDeliveryData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 1 created, 0 updated, 0 failed tank deliveries',
            'data' => [
                'created' => 1,
                'updated' => 0,
                'failed' => 0,
                'errors' => [],
            ],
        ]);

    // Verify tank delivery was created
    $this->assertDatabaseHas('tank_deliveries', [
        'station_id' => $this->station->id,
        'bos_tank_delivery_id' => 12345,
        'tank' => 1,
        'start_product_volume' => 15000.0,
        'end_product_volume' => 18000.0,
        'received_product_volume' => 3000.0,
        'pumps_dispensed_volume' => 2500.0,
    ]);

    // Verify probe_data was stored as JSON
    $tankDelivery = TankDelivery::where('bos_tank_delivery_id', 12345)->first();
    expect($tankDelivery->probe_data)->toBe(['sensor1' => 'value1', 'sensor2' => 'value2']);

    // Verify sync log was created
    $this->assertDatabaseHas('sync_logs', [
        'station_id' => $this->station->id,
        'entity_type' => 'tank_deliveries',
        'action' => 'create',
        'status' => 'success',
    ]);

    // Verify station last sync was updated
    $this->station->refresh();
    expect($this->station->last_sync_at)->not->toBeNull();
});

it('can handle duplicate tank deliveries by updating existing record', function () {
    // Create existing tank delivery
    $existingDelivery = TankDelivery::factory()->create([
        'station_id' => $this->station->id,
        'bos_tank_delivery_id' => 12345,
        'start_product_volume' => 10000.0,
        'end_product_volume' => 12000.0,
        'received_product_volume' => 2000.0,
    ]);

    $updatedTankDeliveryData = [
        'id' => 12345,
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'request_id' => 1002,
        'pts_id' => 'TEST001',
        'pts_delivery_id' => 'DEL001',
        'tank' => 1,
        'fuel_grade_id' => 1,
        'fuel_grade_name' => 'Regular',
        'configuration_id' => 'CFG001',
        'start_datetime' => '2024-01-15 10:30:00',
        'start_product_height' => 12.5,
        'start_water_height' => 0.2,
        'start_temperature' => 25.5,
        'start_product_volume' => 15000.0,
        'start_product_tc_volume' => 15000.0,
        'start_product_density' => 0.85,
        'start_product_mass' => 12750.0,
        'end_datetime' => '2024-01-15 11:30:00',
        'end_product_height' => 15.0,
        'end_water_height' => 0.3,
        'end_temperature' => 26.0,
        'end_product_volume' => 18000.0,
        'end_product_tc_volume' => 18000.0,
        'end_product_density' => 0.85,
        'end_product_mass' => 15300.0,
        'received_product_volume' => 3000.0,
        'absolute_product_height' => 15.0,
        'absolute_water_height' => 0.3,
        'absolute_temperature' => 26.0,
        'absolute_product_volume' => 18000.0,
        'absolute_product_tc_volume' => 18000.0,
        'absolute_product_density' => 0.85,
        'absolute_product_mass' => 15300.0,
        'pumps_dispensed_volume' => 2500.0,
        'probe_data' => ['updated' => 'data'],
        'created_at' => '2024-01-15 10:30:00',
        'updated_at' => '2024-01-15 11:30:00',
    ];

    $response = $this->postJson('/api/sync/tank-deliveries', [
        'pts_id' => 'TEST001',
        'data' => [$updatedTankDeliveryData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 0 created, 1 updated, 0 failed tank deliveries',
            'data' => [
                'created' => 0,
                'updated' => 1,
                'failed' => 0,
                'errors' => [],
            ],
        ]);

    // Verify tank delivery was updated
    $existingDelivery->refresh();
    expect($existingDelivery->start_product_volume)->toBe(15000.0);
    expect($existingDelivery->end_product_volume)->toBe(18000.0);
    expect($existingDelivery->received_product_volume)->toBe(3000.0);
    expect($existingDelivery->probe_data)->toBe(['updated' => 'data']);
});

it('can handle ongoing deliveries (no end_datetime)', function () {
    $ongoingDeliveryData = [
        'id' => 12345,
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'request_id' => 1001,
        'pts_id' => 'TEST001',
        'tank' => 1,
        'fuel_grade_id' => 1,
        'fuel_grade_name' => 'Regular',
        'start_datetime' => '2024-01-15 10:30:00',
        'start_product_height' => 12.5,
        'start_water_height' => 0.2,
        'start_temperature' => 25.5,
        'start_product_volume' => 15000.0,
        'start_product_tc_volume' => 15000.0,
        'start_product_density' => 0.85,
        'start_product_mass' => 12750.0,
        // No end_datetime or end_* fields for ongoing delivery
        'created_at' => '2024-01-15 10:30:00',
        'updated_at' => '2024-01-15 10:30:00',
    ];

    $response = $this->postJson('/api/sync/tank-deliveries', [
        'pts_id' => 'TEST001',
        'data' => [$ongoingDeliveryData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful();

    // Verify ongoing delivery was created
    $this->assertDatabaseHas('tank_deliveries', [
        'station_id' => $this->station->id,
        'bos_tank_delivery_id' => 12345,
        'tank' => 1,
        'start_datetime' => '2024-01-15 10:30:00',
        'end_datetime' => null,
    ]);

    // Verify delivery is ongoing
    $tankDelivery = TankDelivery::where('bos_tank_delivery_id', 12345)->first();
    expect($tankDelivery->isOngoing())->toBeTrue();
    expect($tankDelivery->isCompleted())->toBeFalse();
});

it('validates required fields', function () {
    $response = $this->postJson('/api/sync/tank-deliveries', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                // Missing required fields: uuid, tank
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'data.0.uuid',
            'data.0.tank',
        ]);
});

it('validates data types correctly', function () {
    $response = $this->postJson('/api/sync/tank-deliveries', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 'invalid-id', // Should be integer
                'uuid' => 'invalid-uuid', // Should be valid UUID
                'tank' => 'invalid-tank', // Should be integer
                'start_product_volume' => 'invalid-volume', // Should be numeric
                'probe_data' => 'invalid-array', // Should be array
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'data.0.id',
            'data.0.uuid',
            'data.0.tank',
            'data.0.start_product_volume',
            'data.0.probe_data',
        ]);
});

it('handles multiple tank deliveries in single request', function () {
    $tankDeliveriesData = [
        [
            'id' => 12345,
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'request_id' => 1001,
            'pts_id' => 'TEST001',
            'tank' => 1,
            'fuel_grade_id' => 1,
            'fuel_grade_name' => 'Regular',
            'start_datetime' => '2024-01-15 10:30:00',
            'start_product_volume' => 15000.0,
            'end_datetime' => '2024-01-15 11:30:00',
            'end_product_volume' => 18000.0,
            'received_product_volume' => 3000.0,
            'created_at' => '2024-01-15 10:30:00',
            'updated_at' => '2024-01-15 11:30:00',
        ],
        [
            'id' => 12346,
            'uuid' => '550e8400-e29b-41d4-a716-446655440001',
            'request_id' => 1002,
            'pts_id' => 'TEST001',
            'tank' => 2,
            'fuel_grade_id' => 2,
            'fuel_grade_name' => 'Premium',
            'start_datetime' => '2024-01-15 10:35:00',
            'start_product_volume' => 12000.0,
            'end_datetime' => '2024-01-15 11:35:00',
            'end_product_volume' => 15000.0,
            'received_product_volume' => 3000.0,
            'created_at' => '2024-01-15 10:35:00',
            'updated_at' => '2024-01-15 11:35:00',
        ],
    ];

    $response = $this->postJson('/api/sync/tank-deliveries', [
        'pts_id' => 'TEST001',
        'data' => $tankDeliveriesData,
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 2 created, 0 updated, 0 failed tank deliveries',
            'data' => [
                'created' => 2,
                'updated' => 0,
                'failed' => 0,
                'errors' => [],
            ],
        ]);

    // Verify both tank deliveries were created
    $this->assertDatabaseHas('tank_deliveries', [
        'station_id' => $this->station->id,
        'bos_tank_delivery_id' => 12345,
        'tank' => 1,
    ]);

    $this->assertDatabaseHas('tank_deliveries', [
        'station_id' => $this->station->id,
        'bos_tank_delivery_id' => 12346,
        'tank' => 2,
    ]);
});

it('handles partial failures gracefully', function () {
    $tankDeliveriesData = [
        [
            'id' => 12345,
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'tank' => 1,
            'start_product_volume' => 15000.0,
            'end_product_volume' => 18000.0,
        ],
        [
            'id' => 12346,
            'uuid' => 'invalid-uuid', // This will cause validation failure
            'tank' => 2,
            'start_product_volume' => 12000.0,
            'end_product_volume' => 15000.0,
        ],
    ];

    $response = $this->postJson('/api/sync/tank-deliveries', [
        'pts_id' => 'TEST001',
        'data' => $tankDeliveriesData,
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['data.1.uuid']);
});

it('requires valid API key', function () {
    $response = $this->postJson('/api/sync/tank-deliveries', [
        'pts_id' => 'TEST001',
        'data' => [],
    ], [
        'Authorization' => 'Bearer invalid-key',
    ]);

    $response->assertUnauthorized();
});

it('logs sync operations', function () {
    Log::shouldReceive('debug')->atLeast()->once();
    Log::shouldReceive('info')->atLeast()->once();

    $tankDeliveryData = [
        'id' => 12345,
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'tank' => 1,
        'start_product_volume' => 15000.0,
        'end_product_volume' => 18000.0,
    ];

    $this->postJson('/api/sync/tank-deliveries', [
        'pts_id' => 'TEST001',
        'data' => [$tankDeliveryData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);
});

it('validates nullable fields correctly', function () {
    $tankDeliveryData = [
        'id' => 12345,
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'tank' => 1,
        // All optional fields are null
        'request_id' => null,
        'pts_delivery_id' => null,
        'fuel_grade_id' => null,
        'fuel_grade_name' => null,
        'configuration_id' => null,
        'start_datetime' => null,
        'start_product_height' => null,
        'start_water_height' => null,
        'start_temperature' => null,
        'start_product_volume' => null,
        'start_product_tc_volume' => null,
        'start_product_density' => null,
        'start_product_mass' => null,
        'end_datetime' => null,
        'end_product_height' => null,
        'end_water_height' => null,
        'end_temperature' => null,
        'end_product_volume' => null,
        'end_product_tc_volume' => null,
        'end_product_density' => null,
        'end_product_mass' => null,
        'received_product_volume' => null,
        'absolute_product_height' => null,
        'absolute_water_height' => null,
        'absolute_temperature' => null,
        'absolute_product_volume' => null,
        'absolute_product_tc_volume' => null,
        'absolute_product_density' => null,
        'absolute_product_mass' => null,
        'pumps_dispensed_volume' => null,
        'probe_data' => null,
    ];

    $response = $this->postJson('/api/sync/tank-deliveries', [
        'pts_id' => 'TEST001',
        'data' => [$tankDeliveryData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful();

    // Verify tank delivery was created with null values
    $this->assertDatabaseHas('tank_deliveries', [
        'station_id' => $this->station->id,
        'bos_tank_delivery_id' => 12345,
        'tank' => 1,
        'request_id' => null,
        'pts_delivery_id' => null,
        'fuel_grade_id' => null,
    ]);
});
