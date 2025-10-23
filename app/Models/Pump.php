<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pump extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_self_service' => 'boolean',
        'synced_at' => 'datetime',
        'created_at_bos' => 'datetime',
        'updated_at_bos' => 'datetime',
    ];

    /**
     * Get the station that owns this pump
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Scope to get pumps for a specific station
     */
    public function scopeForStation($query, int $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * Scope to get active pumps
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get pumps by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get self-service pumps
     */
    public function scopeSelfService($query)
    {
        return $query->where('is_self_service', true);
    }

    /**
     * Scope to get full-service pumps
     */
    public function scopeFullService($query)
    {
        return $query->where('is_self_service', false);
    }

    /**
     * Check if pump is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if pump is self-service
     */
    public function isSelfService(): bool
    {
        return $this->is_self_service === true;
    }

    /**
     * Check if pump is full-service
     */
    public function isFullService(): bool
    {
        return $this->is_self_service === false;
    }

    /**
     * Get pump display name
     */
    public function getDisplayName(): string
    {
        return $this->name ?: "Pump {$this->pump_id}";
    }
}
