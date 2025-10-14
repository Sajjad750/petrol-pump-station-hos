<?php

use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can sync pump transactions with valid API key', function () {
    // Create a test station
    $station = Station::create([
        'pts_id' => 'TEST_STATION_001',
        'site_name' => 'Test Station',
        'is_active' => true,
        'api_key' => 'test_api_key_123',
        'connectivity_status' => 'unknown',
    ]);

    $transactionData = [
        'pts_id' => 'TEST_STATION_001',
        'data' => [
            [
                'id' => 'txn_001',
                'uuid' => '550e8400-e29b-41d4-a716-446655440000',
                'pts2_device_id' => 'device_001',
                'pts_id' => 'TEST_STATION_001',
                'request_id' => 'req_001',
                'date_time_start' => '2024-01-01 10:00:00',
                'date_time_end' => '2024-01-01 10:05:00',
                'date_time_paid' => '2024-01-01 10:05:30',
                'pump_id' => 'pump_001',
                'nozzle_id' => 'nozzle_001',
                'fuel_grade_id' => 'fuel_001',
                'tank_id' => 'tank_001',
                'volume' => 25.5,
                'amount' => 75.25,
                'unit_price' => 2.95,
                'transaction_type' => 'sale',
                'payment_method' => 'card',
                'customer_id' => null,
                'vehicle_id' => null,
                'notes' => 'Test transaction',
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-01 10:05:30',
            ],
        ],
    ];

    $response = $this->postJson('/api/sync/pump-transactions', $transactionData, [
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

    // Verify transaction was created
    $this->assertDatabaseHas('pump_transactions', [
        'station_id' => $station->id,
        'bos_transaction_id' => 'txn_001',
        'volume' => 25.5,
        'amount' => 75.25,
    ]);

    // Verify sync log was created
    $this->assertDatabaseHas('sync_logs', [
        'station_id' => $station->id,
        'table_name' => 'pump_transactions',
        'operation' => 'create',
        'status' => 'success',
    ]);
});

it('rejects requests with invalid API key', function () {
    $transactionData = [
        'pts_id' => 'TEST_STATION_001',
        'data' => [],
    ];

    $response = $this->postJson('/api/sync/pump-transactions', $transactionData, [
        'Authorization' => 'Bearer invalid_api_key',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid API key',
        ]);
});

it('rejects requests without authorization header', function () {
    $transactionData = [
        'pts_id' => 'TEST_STATION_001',
        'data' => [],
    ];

    $response = $this->postJson('/api/sync/pump-transactions', $transactionData);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Authorization header is required',
        ]);
});

it('validates required transaction fields', function () {
    // Create a test station
    $station = Station::create([
        'pts_id' => 'TEST_STATION_001',
        'site_name' => 'Test Station',
        'is_active' => true,
        'api_key' => 'test_api_key_123',
        'connectivity_status' => 'unknown',
    ]);

    $invalidTransactionData = [
        'pts_id' => 'TEST_STATION_001',
        'data' => [
            [
                'id' => 'txn_001',
                // Missing required fields
            ],
        ],
    ];

    $response = $this->postJson('/api/sync/pump-transactions', $invalidTransactionData, [
        'Authorization' => 'Bearer test_api_key_123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'data.0.uuid',
            'data.0.pts2_device_id',
            'data.0.date_time_start',
            'data.0.pump_id',
            'data.0.nozzle_id',
            'data.0.fuel_grade_id',
            'data.0.tank_id',
            'data.0.volume',
            'data.0.amount',
            'data.0.unit_price',
        ]);
});

it('handles duplicate transactions correctly', function () {
    // Create a test station
    $station = Station::create([
        'pts_id' => 'TEST_STATION_001',
        'site_name' => 'Test Station',
        'is_active' => true,
        'api_key' => 'test_api_key_123',
        'connectivity_status' => 'unknown',
    ]);

    $transactionData = [
        'pts_id' => 'TEST_STATION_001',
        'data' => [
            [
                'id' => 'txn_001',
                'uuid' => '550e8400-e29b-41d4-a716-446655440000',
                'pts2_device_id' => 'device_001',
                'pts_id' => 'TEST_STATION_001',
                'date_time_start' => '2024-01-01 10:00:00',
                'pump_id' => 'pump_001',
                'nozzle_id' => 'nozzle_001',
                'fuel_grade_id' => 'fuel_001',
                'tank_id' => 'tank_001',
                'volume' => 25.5,
                'amount' => 75.25,
                'unit_price' => 2.95,
            ],
        ],
    ];

    // First sync
    $response1 = $this->postJson('/api/sync/pump-transactions', $transactionData, [
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

    // Second sync with same transaction (should update)
    $transactionData['data'][0]['volume'] = 30.0; // Change volume
    $transactionData['data'][0]['amount'] = 88.50; // Change amount

    $response2 = $this->postJson('/api/sync/pump-transactions', $transactionData, [
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

    // Verify only one transaction exists with updated values
    $this->assertDatabaseCount('pump_transactions', 1);
    $this->assertDatabaseHas('pump_transactions', [
        'station_id' => $station->id,
        'bos_transaction_id' => 'txn_001',
        'volume' => 30.0,
        'amount' => 88.50,
    ]);
});
