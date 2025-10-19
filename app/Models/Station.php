<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Station extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pts_id',
        'site_name',
        'type',
        'dealer',
        'country',
        'region',
        'city',
        'district',
        'address',
        'phone',
        'email',
        'notes',
        'is_active',
        'api_key',
        'last_sync_at',
        'connectivity_status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    protected $hidden = [
        'api_key',
    ];

    /**
     * Encrypt the API key when storing
     */
    protected function apiKey(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $value ? decrypt($value) : null,
            set: fn (string $value) => encrypt($value),
        );
    }

    /**
     * Get all pump transactions for this station
     */
    public function pumpTransactions(): HasMany
    {
        return $this->hasMany(PumpTransaction::class);
    }

    /**
     * Get all pumps for this station
     */
    public function pumps(): HasMany
    {
        return $this->hasMany(Pump::class);
    }

    /**
     * Get all tank measurements for this station
     */
    public function tankMeasurements(): HasMany
    {
        return $this->hasMany(TankMeasurement::class);
    }

    /**
     * Get all sync logs for this station
     */
    public function syncLogs(): HasMany
    {
        return $this->hasMany(SyncLog::class);
    }

    /**
     * Scope to get only active stations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get stations by connectivity status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('connectivity_status', $status);
    }

    /**
     * Find station by PTS ID
     */
    public static function findByPtsId(string $ptsId): ?self
    {
        return static::where('pts_id', $ptsId)->first();
    }

    /**
     * Find station by API key
     */
    public static function findByApiKey(string $apiKey): ?self
    {
        // Since we can't search encrypted fields directly with Eloquent,
        // we need to get all stations and check their decrypted API keys
        // For better performance with many stations, consider using a hash-based approach
        $stations = static::all();

        foreach ($stations as $station) {
            if ($station->api_key === $apiKey) {
                return $station;
            }
        }

        return null;
    }

    /**
     * Update last sync timestamp
     */
    public function updateLastSync(): void
    {
        $this->update(['last_sync_at' => now()]);
    }

    /**
     * Check if station is online (synced within last 5 minutes)
     */
    public function isOnline(): bool
    {
        return $this->last_sync_at && $this->last_sync_at->diffInMinutes(now()) <= 5;
    }

    /**
     * Check if station has warning status (synced 5-30 minutes ago)
     */
    public function hasWarning(): bool
    {
        return $this->last_sync_at &&
               $this->last_sync_at->diffInMinutes(now()) > 5 &&
               $this->last_sync_at->diffInMinutes(now()) <= 30;
    }

    /**
     * Check if station is offline (no sync for more than 30 minutes)
     */
    public function isOffline(): bool
    {
        return !$this->last_sync_at || $this->last_sync_at->diffInMinutes(now()) > 30;
    }
}
