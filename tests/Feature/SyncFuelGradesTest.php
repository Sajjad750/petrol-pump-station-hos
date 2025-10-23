<?php

use App\Models\FuelGrade;
use App\Models\Station;
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

it('can sync fuel grades successfully', function () {
    $fuelGradeData = [
        'id' => 12345,
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'pts_fuel_grade_id' => 'FG001',
        'name' => 'Regular',
        'price' => 3.45,
        'scheduled_price' => 3.50,
        'scheduled_at' => '2024-01-20 10:00:00',
        'expansion_coefficient' => 0.00120,
        'blend_tank1_id' => 1,
        'blend_tank1_percentage' => 80,
        'blend_tank2_id' => 2,
        'created_at' => '2024-01-15 10:30:00',
        'updated_at' => '2024-01-15 10:30:00',
    ];

    $response = $this->postJson('/api/sync/fuel-grades', [
        'pts_id' => 'TEST001',
        'data' => [$fuelGradeData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 1 created, 0 updated, 0 failed fuel grades',
            'data' => [
                'created' => 1,
                'updated' => 0,
                'failed' => 0,
                'errors' => [],
            ],
        ]);

    // Verify fuel grade was created
    $this->assertDatabaseHas('fuel_grades', [
        'station_id' => $this->station->id,
        'bos_fuel_grade_id' => 12345,
        'name' => 'Regular',
        'price' => 3.45,
        'scheduled_price' => 3.50,
        'blend_tank1_id' => 1,
        'blend_tank1_percentage' => 80,
    ]);

    // Verify sync log was created
    $this->assertDatabaseHas('sync_logs', [
        'station_id' => $this->station->id,
        'entity_type' => 'fuel_grades',
        'action' => 'create',
        'status' => 'success',
    ]);

    // Verify station last sync was updated
    $this->station->refresh();
    expect($this->station->last_sync_at)->not->toBeNull();
});

it('can handle duplicate fuel grades by updating existing record', function () {
    // Create existing fuel grade
    $existingFuelGrade = FuelGrade::factory()->create([
        'station_id' => $this->station->id,
        'bos_fuel_grade_id' => 12345,
        'name' => 'Regular',
        'price' => 3.40,
        'scheduled_price' => null,
    ]);

    $updatedFuelGradeData = [
        'id' => 12345,
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'pts_fuel_grade_id' => 'FG001',
        'name' => 'Regular',
        'price' => 3.50,
        'scheduled_price' => 3.55,
        'scheduled_at' => '2024-01-20 10:00:00',
        'expansion_coefficient' => 0.00125,
        'blend_tank1_id' => 1,
        'blend_tank1_percentage' => 75,
        'blend_tank2_id' => 2,
        'created_at' => '2024-01-15 10:30:00',
        'updated_at' => '2024-01-15 11:00:00',
    ];

    $response = $this->postJson('/api/sync/fuel-grades', [
        'pts_id' => 'TEST001',
        'data' => [$updatedFuelGradeData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 0 created, 1 updated, 0 failed fuel grades',
            'data' => [
                'created' => 0,
                'updated' => 1,
                'failed' => 0,
                'errors' => [],
            ],
        ]);

    // Verify fuel grade was updated
    $existingFuelGrade->refresh();
    expect($existingFuelGrade->price)->toBe(3.50);
    expect($existingFuelGrade->scheduled_price)->toBe(3.55);
    expect($existingFuelGrade->blend_tank1_percentage)->toBe(75);
});

it('can handle blended fuel grades', function () {
    $blendedFuelGradeData = [
        'id' => 12345,
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'pts_fuel_grade_id' => 'FG001',
        'name' => 'Premium Blend',
        'price' => 3.75,
        'scheduled_price' => null,
        'scheduled_at' => null,
        'expansion_coefficient' => 0.00130,
        'blend_tank1_id' => 1,
        'blend_tank1_percentage' => 60,
        'blend_tank2_id' => 2,
        'created_at' => '2024-01-15 10:30:00',
        'updated_at' => '2024-01-15 10:30:00',
    ];

    $response = $this->postJson('/api/sync/fuel-grades', [
        'pts_id' => 'TEST001',
        'data' => [$blendedFuelGradeData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful();

    // Verify blended fuel grade was created
    $this->assertDatabaseHas('fuel_grades', [
        'station_id' => $this->station->id,
        'bos_fuel_grade_id' => 12345,
        'name' => 'Premium Blend',
        'blend_tank1_id' => 1,
        'blend_tank1_percentage' => 60,
        'blend_tank2_id' => 2,
    ]);

    // Verify fuel grade is blended
    $fuelGrade = FuelGrade::where('bos_fuel_grade_id', 12345)->first();
    expect($fuelGrade->isBlended())->toBeTrue();
    expect($fuelGrade->getBlendInfo())->toBe([
        'tank1_id' => 1,
        'tank1_percentage' => 60,
        'tank2_id' => 2,
        'tank2_percentage' => 40,
    ]);
});

it('can handle scheduled price changes', function () {
    $fuelGradeData = [
        'id' => 12345,
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'pts_fuel_grade_id' => 'FG001',
        'name' => 'Regular',
        'price' => 3.45,
        'scheduled_price' => 3.50,
        'scheduled_at' => '2024-01-20 10:00:00',
        'expansion_coefficient' => 0.00120,
        'created_at' => '2024-01-15 10:30:00',
        'updated_at' => '2024-01-15 10:30:00',
    ];

    $response = $this->postJson('/api/sync/fuel-grades', [
        'pts_id' => 'TEST001',
        'data' => [$fuelGradeData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful();

    // Verify scheduled price was set
    $fuelGrade = FuelGrade::where('bos_fuel_grade_id', 12345)->first();
    expect($fuelGrade->hasScheduledPriceChange())->toBeTrue();
    expect($fuelGrade->hasPendingPriceChange())->toBeTrue();
    expect($fuelGrade->getPriceChangeStatus())->toBe('pending');
});

it('validates required fields', function () {
    $response = $this->postJson('/api/sync/fuel-grades', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                // Missing required fields: uuid, name, price
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'data.0.uuid',
            'data.0.name',
            'data.0.price',
        ]);
});

it('validates data types correctly', function () {
    $response = $this->postJson('/api/sync/fuel-grades', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 'invalid-id', // Should be integer
                'uuid' => 'invalid-uuid', // Should be valid UUID
                'name' => 123, // Should be string
                'price' => 'invalid-price', // Should be numeric
                'blend_tank1_id' => 300, // Should be 0-255
                'blend_tank1_percentage' => 150, // Should be 1-99
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'data.0.id',
            'data.0.uuid',
            'data.0.name',
            'data.0.price',
            'data.0.blend_tank1_id',
            'data.0.blend_tank1_percentage',
        ]);
});

it('validates blend percentage range', function () {
    $response = $this->postJson('/api/sync/fuel-grades', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                'uuid' => '550e8400-e29b-41d4-a716-446655440000',
                'name' => 'Regular',
                'price' => 3.45,
                'blend_tank1_id' => 1,
                'blend_tank1_percentage' => 0, // Should be 1-99
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['data.0.blend_tank1_percentage']);
});

it('handles multiple fuel grades in single request', function () {
    $fuelGradesData = [
        [
            'id' => 12345,
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'pts_fuel_grade_id' => 'FG001',
            'name' => 'Regular',
            'price' => 3.45,
            'created_at' => '2024-01-15 10:30:00',
            'updated_at' => '2024-01-15 10:30:00',
        ],
        [
            'id' => 12346,
            'uuid' => '550e8400-e29b-41d4-a716-446655440001',
            'pts_fuel_grade_id' => 'FG002',
            'name' => 'Premium',
            'price' => 3.75,
            'created_at' => '2024-01-15 10:35:00',
            'updated_at' => '2024-01-15 10:35:00',
        ],
    ];

    $response = $this->postJson('/api/sync/fuel-grades', [
        'pts_id' => 'TEST001',
        'data' => $fuelGradesData,
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 2 created, 0 updated, 0 failed fuel grades',
            'data' => [
                'created' => 2,
                'updated' => 0,
                'failed' => 0,
                'errors' => [],
            ],
        ]);

    // Verify both fuel grades were created
    $this->assertDatabaseHas('fuel_grades', [
        'station_id' => $this->station->id,
        'bos_fuel_grade_id' => 12345,
        'name' => 'Regular',
    ]);

    $this->assertDatabaseHas('fuel_grades', [
        'station_id' => $this->station->id,
        'bos_fuel_grade_id' => 12346,
        'name' => 'Premium',
    ]);
});

it('handles partial failures gracefully', function () {
    $fuelGradesData = [
        [
            'id' => 12345,
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'Regular',
            'price' => 3.45,
        ],
        [
            'id' => 12346,
            'uuid' => 'invalid-uuid', // This will cause validation failure
            'name' => 'Premium',
            'price' => 3.75,
        ],
    ];

    $response = $this->postJson('/api/sync/fuel-grades', [
        'pts_id' => 'TEST001',
        'data' => $fuelGradesData,
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['data.1.uuid']);
});

it('requires valid API key', function () {
    $response = $this->postJson('/api/sync/fuel-grades', [
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

    $fuelGradeData = [
        'id' => 12345,
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'name' => 'Regular',
        'price' => 3.45,
    ];

    $this->postJson('/api/sync/fuel-grades', [
        'pts_id' => 'TEST001',
        'data' => [$fuelGradeData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);
});

it('validates nullable fields correctly', function () {
    $fuelGradeData = [
        'id' => 12345,
        'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'name' => 'Regular',
        'price' => 3.45,
        // All optional fields are null
        'pts_fuel_grade_id' => null,
        'scheduled_price' => null,
        'scheduled_at' => null,
        'expansion_coefficient' => null,
        'blend_tank1_id' => null,
        'blend_tank1_percentage' => null,
        'blend_tank2_id' => null,
    ];

    $response = $this->postJson('/api/sync/fuel-grades', [
        'pts_id' => 'TEST001',
        'data' => [$fuelGradeData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful();

    // Verify fuel grade was created with null values
    $this->assertDatabaseHas('fuel_grades', [
        'station_id' => $this->station->id,
        'bos_fuel_grade_id' => 12345,
        'name' => 'Regular',
        'price' => 3.45,
        'pts_fuel_grade_id' => null,
        'scheduled_price' => null,
        'blend_tank1_id' => null,
    ]);
});

it('validates tank ID range', function () {
    $response = $this->postJson('/api/sync/fuel-grades', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                'uuid' => '550e8400-e29b-41d4-a716-446655440000',
                'name' => 'Regular',
                'price' => 3.45,
                'blend_tank1_id' => 300, // Should be 0-255
                'blend_tank2_id' => -1, // Should be 0-255
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'data.0.blend_tank1_id',
            'data.0.blend_tank2_id',
        ]);
});
