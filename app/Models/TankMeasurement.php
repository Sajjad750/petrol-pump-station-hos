<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TankMeasurement extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date_time' => 'datetime',
        'synced_at' => 'datetime',
        'created_at_bos' => 'datetime',
        'updated_at_bos' => 'datetime',
        'alarms' => 'array',
        'product_height' => 'decimal:3',
        'water_height' => 'decimal:3',
        'temperature' => 'decimal:2',
        'product_volume' => 'decimal:3',
        'water_volume' => 'decimal:3',
        'product_ullage' => 'decimal:3',
        'product_tc_volume' => 'decimal:3',
        'product_density' => 'decimal:3',
        'product_mass' => 'decimal:3',
        'tank_filling_percentage' => 'decimal:2',
    ];

    /**
     * Get the station that owns this tank measurement
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Scope to get tank measurements for a specific station
     */
    public function scopeForStation($query, int $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * Scope to get tank measurements within a date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date_time', [$startDate, $endDate]);
    }

    /**
     * Scope to get tank measurements by tank number
     */
    public function scopeByTank($query, int $tank)
    {
        return $query->where('tank', $tank);
    }

    /**
     * Scope to get tank measurements by fuel grade
     */
    public function scopeByFuelGrade($query, int $fuelGradeId)
    {
        return $query->where('fuel_grade_id', $fuelGradeId);
    }

    /**
     * Scope to get recent tank measurements
     */
    public function scopeRecent($query, int $minutes = 60)
    {
        return $query->where('date_time', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope to get tank measurements by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if tank measurement has alarms
     */
    public function hasAlarms(): bool
    {
        return !empty($this->alarms);
    }

    /**
     * Get tank filling status
     */
    public function getFillingStatus(): string
    {
        if ($this->tank_filling_percentage === null) {
            return 'unknown';
        }

        if ($this->tank_filling_percentage >= 90) {
            return 'high';
        } elseif ($this->tank_filling_percentage >= 50) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Check if tank is critically low
     */
    public function isCriticallyLow(): bool
    {
        return $this->tank_filling_percentage !== null && $this->tank_filling_percentage < 20;
    }

    /**
     * Check if tank is near capacity
     */
    public function isNearCapacity(): bool
    {
        return $this->tank_filling_percentage !== null && $this->tank_filling_percentage >= 90;
    }
}
