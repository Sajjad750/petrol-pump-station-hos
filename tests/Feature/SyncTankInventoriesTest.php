<?php

use App\Models\Station;
use App\Models\TankInventory;
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

it('can sync tank inventories successfully', function () {
    $inventoryData = [
        'id' => 12345,
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'request_id' => 'REQ001',
        'pts_id' => 'TEST001',
        'date_time' => '2024-01-15 10:30:00',
        'tank' => 1,
        'fuel_grade_id' => 1,
        'fuel_grade_name' => 'Regular',
        'status' => 'active',
        'alarms' => [],
        'product_height' => 150.5,
        'water_height' => 2.3,
        'temperature' => 20.5,
        'product_volume' => 5000.0,
        'water_volume' => 50.0,
        'product_ullage' => 1000.0,
        'product_tc_volume' => 4995.0,
        'product_density' => 0.75,
        'product_mass' => 3746.25,
        'tank_filling_percentage' => 83.33,
        'configuration_id' => 1,
        'created_at' => '2024-01-15 10:30:00',
        'updated_at' => '2024-01-15 10:30:00',
    ];

    $response = $this->postJson('/api/sync/tank-inventories', [
        'pts_id' => 'TEST001',
        'data' => [$inventoryData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 1 created, 0 updated, 0 failed tank inventories',
            'data' => [
                'created' => 1,
                'updated' => 0,
                'failed' => 0,
                'errors' => [],
            ],
        ]);

    // Verify inventory was created in database
    $this->assertDatabaseHas('tank_inventories', [
        'station_id' => $this->station->id,
        'bos_tank_inventory_id' => 12345,
        'tank' => 1,
        'fuel_grade_id' => 1,
        'fuel_grade_name' => 'Regular',
        'status' => 'active',
    ]);

    // Verify inventory has UUID
    $inventory = TankInventory::where('bos_tank_inventory_id', 12345)->first();
    expect($inventory->uuid)->not->toBeNull();
    expect($inventory->synced_at)->not->toBeNull();
});

it('can sync multiple tank inventories at once', function () {
    $inventoriesData = [
        [
            'id' => 12345,
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'pts_id' => 'TEST001',
            'date_time' => '2024-01-15 10:30:00',
            'tank' => 1,
            'fuel_grade_id' => 1,
            'fuel_grade_name' => 'Regular',
            'product_volume' => 5000.0,
            'product_height' => 150.5,
        ],
        [
            'id' => 12346,
            'uuid' => '550e8400-e29b-41d4-a716-446655440001',
            'pts_id' => 'TEST001',
            'date_time' => '2024-01-15 10:30:00',
            'tank' => 2,
            'fuel_grade_id' => 2,
            'fuel_grade_name' => 'Premium',
            'product_volume' => 3000.0,
            'product_height' => 90.2,
        ],
    ];

    $response = $this->postJson('/api/sync/tank-inventories', [
        'pts_id' => 'TEST001',
        'data' => $inventoriesData,
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 2 created, 0 updated, 0 failed tank inventories',
            'data' => [
                'created' => 2,
                'updated' => 0,
                'failed' => 0,
                'errors' => [],
            ],
        ]);

    // Verify both inventories were created
    $this->assertDatabaseHas('tank_inventories', [
        'bos_tank_inventory_id' => 12345,
        'tank' => 1,
        'fuel_grade_name' => 'Regular',
    ]);
    $this->assertDatabaseHas('tank_inventories', [
        'bos_tank_inventory_id' => 12346,
        'tank' => 2,
        'fuel_grade_name' => 'Premium',
    ]);
});

it('can update existing tank inventories', function () {
    // Create an existing inventory
    $existingInventory = TankInventory::factory()->create([
        'station_id' => $this->station->id,
        'bos_tank_inventory_id' => 12345,
        'tank' => 1,
        'fuel_grade_name' => 'Old Grade',
        'product_volume' => 1000.0,
    ]);

    $updatedInventoryData = [
        'id' => 12345,
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'pts_id' => 'TEST001',
        'date_time' => '2024-01-15 10:30:00',
        'tank' => 1,
        'fuel_grade_id' => 1,
        'fuel_grade_name' => 'Updated Grade',
        'product_volume' => 2000.0,
        'product_height' => 200.0,
    ];

    $response = $this->postJson('/api/sync/tank-inventories', [
        'pts_id' => 'TEST001',
        'data' => [$updatedInventoryData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 0 created, 1 updated, 0 failed tank inventories',
            'data' => [
                'created' => 0,
                'updated' => 1,
                'failed' => 0,
                'errors' => [],
            ],
        ]);

    // Verify inventory was updated
    $existingInventory->refresh();
    expect($existingInventory->fuel_grade_name)->toBe('Updated Grade');
    expect($existingInventory->product_volume)->toBe(2000.0);
    expect($existingInventory->synced_at)->not->toBeNull();
});

it('handles mixed success and failure scenarios', function () {
    $inventoriesData = [
        [
            'id' => 12345,
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'pts_id' => 'TEST001',
            'date_time' => '2024-01-15 10:30:00',
            'tank' => 1,
            'fuel_grade_id' => 1,
            'product_volume' => 5000.0,
        ],
        [
            'id' => 12346,
            // Missing required fields to cause validation failure
            'pts_id' => 'TEST001',
            'date_time' => 'invalid_date', // Invalid date
            'tank' => 'invalid_tank', // Invalid tank type
        ],
    ];

    $response = $this->postJson('/api/sync/tank-inventories', [
        'pts_id' => 'TEST001',
        'data' => $inventoriesData,
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 1 created, 0 updated, 1 failed tank inventories',
            'data' => [
                'created' => 1,
                'updated' => 0,
                'failed' => 1,
                'errors' => [
                    [
                        'tank_inventory_id' => 12346,
                        'error' => 'The data.1.date time must be a valid date. (and 1 more error)',
                    ],
                ],
            ],
        ]);

    // Verify only the valid inventory was created
    $this->assertDatabaseHas('tank_inventories', [
        'bos_tank_inventory_id' => 12345,
        'tank' => 1,
    ]);
    $this->assertDatabaseMissing('tank_inventories', [
        'bos_tank_inventory_id' => 12346,
    ]);
});

it('validates required fields', function () {
    $response = $this->postJson('/api/sync/tank-inventories', [
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
            'data.0.uuid',
            'data.0.pts_id',
            'data.0.date_time',
            'data.0.tank',
        ]);
});

it('validates data types and formats', function () {
    $response = $this->postJson('/api/sync/tank-inventories', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 'invalid_id', // Should be integer
                'uuid' => 'invalid_uuid', // Should be valid UUID
                'pts_id' => 'TEST001',
                'date_time' => 'invalid_date', // Should be valid date
                'tank' => 'invalid_tank', // Should be integer
                'fuel_grade_id' => 'invalid_id', // Should be integer
                'product_height' => 'invalid_height', // Should be numeric
                'product_volume' => 'invalid_volume', // Should be numeric
                'tank_filling_percentage' => 150, // Should be max 100
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'data.0.id',
            'data.0.uuid',
            'data.0.date_time',
            'data.0.tank',
            'data.0.fuel_grade_id',
            'data.0.product_height',
            'data.0.product_volume',
            'data.0.tank_filling_percentage',
        ]);
});

it('validates tank filling percentage range', function () {
    $response = $this->postJson('/api/sync/tank-inventories', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                'uuid' => '550e8400-e29b-41d4-a716-446655440000',
                'pts_id' => 'TEST001',
                'date_time' => '2024-01-15 10:30:00',
                'tank' => 1,
                'tank_filling_percentage' => 150, // Invalid: over 100%
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['data.0.tank_filling_percentage']);
});

it('handles empty data array', function () {
    $response = $this->postJson('/api/sync/tank-inventories', [
        'pts_id' => 'TEST001',
        'data' => [],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['data']);
});

it('handles missing pts_id', function () {
    $response = $this->postJson('/api/sync/tank-inventories', [
        'data' => [
            [
                'id' => 12345,
                'uuid' => '550e8400-e29b-41d4-a716-446655440000',
                'pts_id' => 'TEST001',
                'date_time' => '2024-01-15 10:30:00',
                'tank' => 1,
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['pts_id']);
});

it('handles invalid API key', function () {
    $response = $this->postJson('/api/sync/tank-inventories', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                'uuid' => '550e8400-e29b-41d4-a716-446655440000',
                'pts_id' => 'TEST001',
                'date_time' => '2024-01-15 10:30:00',
                'tank' => 1,
            ],
        ],
    ], [
        'Authorization' => 'Bearer invalid-api-key',
    ]);

    $response->assertUnauthorized();
});

it('handles missing authorization header', function () {
    $response = $this->postJson('/api/sync/tank-inventories', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                'uuid' => '550e8400-e29b-41d4-a716-446655440000',
                'pts_id' => 'TEST001',
                'date_time' => '2024-01-15 10:30:00',
                'tank' => 1,
            ],
        ],
    ]);

    $response->assertUnauthorized();
});

it('handles database transaction rollback on failure', function () {
    // Mock a database exception
    DB::shouldReceive('beginTransaction')->once();
    DB::shouldReceive('rollBack')->once();

    $response = $this->postJson('/api/sync/tank-inventories', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                'uuid' => '550e8400-e29b-41d4-a716-446655440000',
                'pts_id' => 'TEST001',
                'date_time' => '2024-01-15 10:30:00',
                'tank' => 1,
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    // The response should still be successful as the transaction rollback is handled
    $response->assertSuccessful();
});

it('logs sync operations', function () {
    Log::shouldReceive('debug')->with('syncTankInventories: ', \Mockery::type('array'))->once();
    Log::shouldReceive('debug')->with('tank_inventories: ', \Mockery::type('array'))->once();

    $this->postJson('/api/sync/tank-inventories', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                'uuid' => '550e8400-e29b-41d4-a716-446655440000',
                'pts_id' => 'TEST001',
                'date_time' => '2024-01-15 10:30:00',
                'tank' => 1,
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);
});

it('handles tank inventories with all optional fields', function () {
    $inventoryData = [
        'id' => 12345,
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'request_id' => 'REQ001',
        'pts_id' => 'TEST001',
        'date_time' => '2024-01-15 10:30:00',
        'tank' => 1,
        'fuel_grade_id' => 1,
        'fuel_grade_name' => 'Regular',
        'status' => 'active',
        'alarms' => ['low_level', 'high_temperature'],
        'product_height' => 150.5,
        'water_height' => 2.3,
        'temperature' => 20.5,
        'product_volume' => 5000.0,
        'water_volume' => 50.0,
        'product_ullage' => 1000.0,
        'product_tc_volume' => 4995.0,
        'product_density' => 0.75,
        'product_mass' => 3746.25,
        'tank_filling_percentage' => 83.33,
        'configuration_id' => 1,
        'created_at' => '2024-01-15 10:30:00',
        'updated_at' => '2024-01-15 10:30:00',
    ];

    $response = $this->postJson('/api/sync/tank-inventories', [
        'pts_id' => 'TEST001',
        'data' => [$inventoryData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful();

    // Verify all fields were stored correctly
    $inventory = TankInventory::where('bos_tank_inventory_id', 12345)->first();
    expect($inventory->request_id)->toBe('REQ001');
    expect($inventory->tank)->toBe(1);
    expect($inventory->fuel_grade_id)->toBe(1);
    expect($inventory->fuel_grade_name)->toBe('Regular');
    expect($inventory->status)->toBe('active');
    expect($inventory->alarms)->toBe(['low_level', 'high_temperature']);
    expect($inventory->product_height)->toBe(150.5);
    expect($inventory->water_height)->toBe(2.3);
    expect($inventory->temperature)->toBe(20.5);
    expect($inventory->product_volume)->toBe(5000.0);
    expect($inventory->water_volume)->toBe(50.0);
    expect($inventory->product_ullage)->toBe(1000.0);
    expect($inventory->product_tc_volume)->toBe(4995.0);
    expect($inventory->product_density)->toBe(0.75);
    expect($inventory->product_mass)->toBe(3746.25);
    expect($inventory->tank_filling_percentage)->toBe(83.33);
    expect($inventory->configuration_id)->toBe(1);
});

it('handles tank inventories with minimal required fields', function () {
    $inventoryData = [
        'id' => 12345,
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'pts_id' => 'TEST001',
        'date_time' => '2024-01-15 10:30:00',
        'tank' => 1,
    ];

    $response = $this->postJson('/api/sync/tank-inventories', [
        'pts_id' => 'TEST001',
        'data' => [$inventoryData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful();

    // Verify inventory was created with minimal data
    $this->assertDatabaseHas('tank_inventories', [
        'bos_tank_inventory_id' => 12345,
        'tank' => 1,
        'fuel_grade_id' => null,
        'fuel_grade_name' => null,
        'status' => null,
        'alarms' => null,
    ]);
});

it('updates station last sync time', function () {
    $originalLastSync = $this->station->last_sync_at;

    $this->postJson('/api/sync/tank-inventories', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                'uuid' => '550e8400-e29b-41d4-a716-446655440000',
                'pts_id' => 'TEST001',
                'date_time' => '2024-01-15 10:30:00',
                'tank' => 1,
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $this->station->refresh();
    expect($this->station->last_sync_at)->not->toBe($originalLastSync);
    expect($this->station->last_sync_at)->toBeGreaterThan($originalLastSync);
});
