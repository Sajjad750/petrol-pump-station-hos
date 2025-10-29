<?php

use App\Models\PtsUser;
use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock the API key middleware
    $this->station = Station::factory()->create([
        'api_key' => 'test-api-key-123',
        'pts_id' => 'TEST001',
    ]);
});

it('can sync PTS users successfully', function () {
    $ptsUserData = [
        'id' => 12345,
        'pts_user_id' => 100,
        'login' => 'admin',
        'configuration_permission' => true,
        'control_permission' => true,
        'monitoring_permission' => true,
        'reports_permission' => true,
        'is_active' => true,
        'created_at' => '2024-01-15 10:30:00',
        'updated_at' => '2024-01-15 10:30:00',
    ];

    $response = $this->postJson('/api/sync/pts-users', [
        'pts_id' => 'TEST001',
        'data' => [$ptsUserData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 1 created, 0 updated, 0 failed PTS users',
            'data' => [
                'created' => 1,
                'updated' => 0,
                'failed' => 0,
                'errors' => [],
            ],
        ]);

    // Verify PTS user was created in database
    $this->assertDatabaseHas('pts_users', [
        'station_id' => $this->station->id,
        'bos_pts_user_id' => 12345,
        'pts_user_id' => 100,
        'login' => 'admin',
        'configuration_permission' => true,
        'control_permission' => true,
        'monitoring_permission' => true,
        'reports_permission' => true,
        'is_active' => true,
    ]);

    // Verify PTS user has UUID
    $ptsUser = PtsUser::where('bos_pts_user_id', 12345)->first();
    expect($ptsUser->uuid)->not->toBeNull();
    expect($ptsUser->synced_at)->not->toBeNull();
});

it('can sync multiple PTS users at once', function () {
    $ptsUsersData = [
        [
            'id' => 12345,
            'pts_user_id' => 100,
            'login' => 'admin',
            'configuration_permission' => true,
            'control_permission' => true,
            'monitoring_permission' => true,
            'reports_permission' => true,
            'is_active' => true,
        ],
        [
            'id' => 12346,
            'pts_user_id' => 101,
            'login' => 'operator',
            'configuration_permission' => false,
            'control_permission' => true,
            'monitoring_permission' => false,
            'reports_permission' => true,
            'is_active' => true,
        ],
    ];

    $response = $this->postJson('/api/sync/pts-users', [
        'pts_id' => 'TEST001',
        'data' => $ptsUsersData,
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 2 created, 0 updated, 0 failed PTS users',
            'data' => [
                'created' => 2,
                'updated' => 0,
                'failed' => 0,
                'errors' => [],
            ],
        ]);

    // Verify both PTS users were created
    $this->assertDatabaseHas('pts_users', [
        'bos_pts_user_id' => 12345,
        'login' => 'admin',
    ]);
    $this->assertDatabaseHas('pts_users', [
        'bos_pts_user_id' => 12346,
        'login' => 'operator',
    ]);
});

it('can update existing PTS users', function () {
    // Create an existing PTS user
    $existingPtsUser = PtsUser::factory()->create([
        'station_id' => $this->station->id,
        'bos_pts_user_id' => 12345,
        'login' => 'old_login',
        'is_active' => false,
    ]);

    $updatedPtsUserData = [
        'id' => 12345,
        'pts_user_id' => 100,
        'login' => 'new_login',
        'configuration_permission' => true,
        'control_permission' => true,
        'monitoring_permission' => true,
        'reports_permission' => true,
        'is_active' => true,
    ];

    $response = $this->postJson('/api/sync/pts-users', [
        'pts_id' => 'TEST001',
        'data' => [$updatedPtsUserData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Synced 0 created, 1 updated, 0 failed PTS users',
            'data' => [
                'created' => 0,
                'updated' => 1,
                'failed' => 0,
                'errors' => [],
            ],
        ]);

    // Verify PTS user was updated
    $existingPtsUser->refresh();
    expect($existingPtsUser->login)->toBe('new_login');
    expect($existingPtsUser->is_active)->toBe(true);
    expect($existingPtsUser->synced_at)->not->toBeNull();
});

it('handles PTS users with all permissions', function () {
    $ptsUserData = [
        'id' => 12345,
        'pts_user_id' => 100,
        'login' => 'super_admin',
        'configuration_permission' => true,
        'control_permission' => true,
        'monitoring_permission' => true,
        'reports_permission' => true,
        'is_active' => true,
    ];

    $response = $this->postJson('/api/sync/pts-users', [
        'pts_id' => 'TEST001',
        'data' => [$ptsUserData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful();

    // Verify all permissions were set
    $ptsUser = PtsUser::where('bos_pts_user_id', 12345)->first();
    expect($ptsUser->configuration_permission)->toBeTrue();
    expect($ptsUser->control_permission)->toBeTrue();
    expect($ptsUser->monitoring_permission)->toBeTrue();
    expect($ptsUser->reports_permission)->toBeTrue();
    expect($ptsUser->is_active)->toBeTrue();
});

it('handles PTS users with no permissions', function () {
    $ptsUserData = [
        'id' => 12345,
        'pts_user_id' => 100,
        'login' => 'viewer',
        'configuration_permission' => false,
        'control_permission' => false,
        'monitoring_permission' => false,
        'reports_permission' => false,
        'is_active' => true,
    ];

    $response = $this->postJson('/api/sync/pts-users', [
        'pts_id' => 'TEST001',
        'data' => [$ptsUserData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful();

    // Verify no permissions were set
    $ptsUser = PtsUser::where('bos_pts_user_id', 12345)->first();
    expect($ptsUser->configuration_permission)->toBeFalse();
    expect($ptsUser->control_permission)->toBeFalse();
    expect($ptsUser->monitoring_permission)->toBeFalse();
    expect($ptsUser->reports_permission)->toBeFalse();
});

it('handles PTS users with minimal required fields', function () {
    $ptsUserData = [
        'id' => 12345,
        'pts_user_id' => 100,
        'login' => 'minimal_user',
    ];

    $response = $this->postJson('/api/sync/pts-users', [
        'pts_id' => 'TEST001',
        'data' => [$ptsUserData],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $response->assertSuccessful();

    // Verify PTS user was created with defaults
    $this->assertDatabaseHas('pts_users', [
        'bos_pts_user_id' => 12345,
        'pts_user_id' => 100,
        'login' => 'minimal_user',
        'configuration_permission' => false,
        'control_permission' => false,
        'monitoring_permission' => false,
        'reports_permission' => false,
        'is_active' => true,
    ]);
});

it('updates station last sync time', function () {
    $originalLastSync = $this->station->last_sync_at;

    $this->postJson('/api/sync/pts-users', [
        'pts_id' => 'TEST001',
        'data' => [
            [
                'id' => 12345,
                'pts_user_id' => 100,
                'login' => 'admin',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-123',
    ]);

    $this->station->refresh();
    expect($this->station->last_sync_at)->not->toBe($originalLastSync);
    expect($this->station->last_sync_at)->toBeGreaterThan($originalLastSync);
});
