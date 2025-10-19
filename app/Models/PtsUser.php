<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PtsUser extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'configuration_permission' => 'boolean',
        'control_permission' => 'boolean',
        'monitoring_permission' => 'boolean',
        'reports_permission' => 'boolean',
        'is_active' => 'boolean',
        'synced_at' => 'datetime',
        'created_at_bos' => 'datetime',
        'updated_at_bos' => 'datetime',
    ];

    /**
     * Get the station that owns this PTS user
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Scope to get PTS users for a specific station
     */
    public function scopeForStation($query, int $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * Scope to get active PTS users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get inactive PTS users
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope to get PTS users by login
     */
    public function scopeByLogin($query, string $login)
    {
        return $query->where('login', $login);
    }

    /**
     * Scope to get PTS users with configuration permission
     */
    public function scopeWithConfigurationPermission($query)
    {
        return $query->where('configuration_permission', true);
    }

    /**
     * Scope to get PTS users with control permission
     */
    public function scopeWithControlPermission($query)
    {
        return $query->where('control_permission', true);
    }

    /**
     * Scope to get PTS users with monitoring permission
     */
    public function scopeWithMonitoringPermission($query)
    {
        return $query->where('monitoring_permission', true);
    }

    /**
     * Scope to get PTS users with reports permission
     */
    public function scopeWithReportsPermission($query)
    {
        return $query->where('reports_permission', true);
    }

    /**
     * Scope to get PTS users with admin permissions
     */
    public function scopeAdmins($query)
    {
        return $query->where('configuration_permission', true)
            ->where('control_permission', true)
            ->where('monitoring_permission', true)
            ->where('reports_permission', true);
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if user has configuration permission
     */
    public function hasConfigurationPermission(): bool
    {
        return $this->configuration_permission;
    }

    /**
     * Check if user has control permission
     */
    public function hasControlPermission(): bool
    {
        return $this->control_permission;
    }

    /**
     * Check if user has monitoring permission
     */
    public function hasMonitoringPermission(): bool
    {
        return $this->monitoring_permission;
    }

    /**
     * Check if user has reports permission
     */
    public function hasReportsPermission(): bool
    {
        return $this->reports_permission;
    }

    /**
     * Check if user is admin (has all permissions)
     */
    public function isAdmin(): bool
    {
        return $this->hasConfigurationPermission() &&
               $this->hasControlPermission() &&
               $this->hasMonitoringPermission() &&
               $this->hasReportsPermission();
    }

    /**
     * Get user permissions as array
     */
    public function getPermissions(): array
    {
        return [
            'configuration' => $this->configuration_permission,
            'control' => $this->control_permission,
            'monitoring' => $this->monitoring_permission,
            'reports' => $this->reports_permission,
        ];
    }

    /**
     * Get user permissions as string
     */
    public function getPermissionsString(): string
    {
        $permissions = [];

        if ($this->configuration_permission) {
            $permissions[] = 'Configuration';
        }

        if ($this->control_permission) {
            $permissions[] = 'Control';
        }

        if ($this->monitoring_permission) {
            $permissions[] = 'Monitoring';
        }

        if ($this->reports_permission) {
            $permissions[] = 'Reports';
        }

        return empty($permissions) ? 'None' : implode(', ', $permissions);
    }

    /**
     * Get user status
     */
    public function getStatus(): string
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Get user role
     */
    public function getRole(): string
    {
        if ($this->isAdmin()) {
            return 'Admin';
        }

        $permissionCount = array_sum($this->getPermissions());

        return match ($permissionCount) {
            0 => 'No Access',
            1 => 'Limited Access',
            2 => 'Standard User',
            3 => 'Power User',
            4 => 'Admin',
            default => 'Unknown',
        };
    }

    /**
     * Get user summary
     */
    public function getUserSummary(): array
    {
        return [
            'id' => $this->id,
            'pts_user_id' => $this->pts_user_id,
            'login' => $this->login,
            'status' => $this->getStatus(),
            'role' => $this->getRole(),
            'permissions' => $this->getPermissions(),
            'permissions_string' => $this->getPermissionsString(),
            'is_admin' => $this->isAdmin(),
        ];
    }
}
