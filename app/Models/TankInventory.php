<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TankInventory extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'snapshot_datetime' => 'datetime',
        'synced_at' => 'datetime',
        'created_at_bos' => 'datetime',
        'updated_at_bos' => 'datetime',
        'probe_data' => 'array',
        'absolute_product_height' => 'decimal:3',
        'absolute_water_height' => 'decimal:3',
        'absolute_temperature' => 'decimal:2',
        'absolute_product_volume' => 'decimal:3',
        'absolute_product_tc_volume' => 'decimal:3',
        'absolute_product_density' => 'decimal:3',
        'absolute_product_mass' => 'decimal:3',
        'pumps_dispensed_volume' => 'decimal:3',
    ];

    /**
     * Get the station that owns this tank inventory
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Scope to get tank inventories for a specific station
     */
    public function scopeForStation($query, int $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * Scope to get tank inventories within a date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('snapshot_datetime', [$startDate, $endDate]);
    }

    /**
     * Scope to get tank inventories by tank number
     */
    public function scopeByTank($query, int $tank)
    {
        return $query->where('tank', $tank);
    }

    /**
     * Scope to get tank inventories by fuel grade
     */
    public function scopeByFuelGrade($query, int $fuelGradeId)
    {
        return $query->where('fuel_grade_id', $fuelGradeId);
    }

    /**
     * Scope to get recent tank inventories
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('snapshot_datetime', '>=', now()->subHours($hours));
    }

    /**
     * Scope to get latest inventory for each tank
     */
    public function scopeLatestForTanks($query)
    {
        return $query->whereIn('id', function ($subQuery) {
            $subQuery->selectRaw('MAX(id)')
                ->from('tank_inventories')
                ->groupBy('station_id', 'tank');
        });
    }

    /**
     * Scope to get inventories with low levels
     */
    public function scopeLowLevel($query, float $threshold = 20.0)
    {
        return $query->whereNotNull('absolute_product_volume')
            ->where('absolute_product_volume', '<', $threshold);
    }

    /**
     * Scope to get inventories with high water levels
     */
    public function scopeHighWaterLevel($query, float $threshold = 5.0)
    {
        return $query->whereNotNull('absolute_water_height')
            ->where('absolute_water_height', '>', $threshold);
    }

    /**
     * Check if tank has low inventory level
     */
    public function hasLowLevel(float $threshold = 20.0): bool
    {
        return $this->absolute_product_volume !== null && $this->absolute_product_volume < $threshold;
    }

    /**
     * Check if tank has high water level
     */
    public function hasHighWaterLevel(float $threshold = 5.0): bool
    {
        return $this->absolute_water_height !== null && $this->absolute_water_height > $threshold;
    }

    /**
     * Get tank filling percentage based on volume
     */
    public function getFillingPercentage(): ?float
    {
        if ($this->absolute_product_volume === null) {
            return null;
        }

        // This would need to be calculated based on tank capacity
        // For now, return null as we don't have tank capacity data
        return null;
    }

    /**
     * Get water percentage in tank
     */
    public function getWaterPercentage(): ?float
    {
        if ($this->absolute_product_height === null || $this->absolute_water_height === null) {
            return null;
        }

        if ($this->absolute_product_height <= 0) {
            return 0;
        }

        return ($this->absolute_water_height / $this->absolute_product_height) * 100;
    }

    /**
     * Get product height without water
     */
    public function getProductHeightWithoutWater(): ?float
    {
        if ($this->absolute_product_height === null || $this->absolute_water_height === null) {
            return $this->absolute_product_height;
        }

        return $this->absolute_product_height - $this->absolute_water_height;
    }

    /**
     * Check if inventory data is complete
     */
    public function isDataComplete(): bool
    {
        return $this->snapshot_datetime !== null
            && $this->absolute_product_volume !== null
            && $this->absolute_product_height !== null
            && $this->absolute_temperature !== null;
    }

    /**
     * Get inventory status
     */
    public function getInventoryStatus(): string
    {
        if (!$this->isDataComplete()) {
            return 'incomplete';
        }

        if ($this->hasLowLevel()) {
            return 'low';
        }

        if ($this->hasHighWaterLevel()) {
            return 'high_water';
        }

        return 'normal';
    }

    /**
     * Get formatted snapshot time
     */
    public function getFormattedSnapshotTime(): ?string
    {
        return $this->snapshot_datetime?->format('Y-m-d H:i:s');
    }

    /**
     * Get probe data as array
     */
    public function getProbeDataArray(): array
    {
        return $this->probe_data ?? [];
    }

    /**
     * Check if probe data exists
     */
    public function hasProbeData(): bool
    {
        return !empty($this->probe_data);
    }
}
