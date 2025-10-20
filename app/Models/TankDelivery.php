<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TankDelivery extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'synced_at' => 'datetime',
        'created_at_bos' => 'datetime',
        'updated_at_bos' => 'datetime',
        'start_product_height' => 'decimal:3',
        'start_water_height' => 'decimal:3',
        'start_temperature' => 'decimal:2',
        'start_product_volume' => 'decimal:3',
        'start_product_tc_volume' => 'decimal:3',
        'start_product_density' => 'decimal:3',
        'start_product_mass' => 'decimal:3',
        'end_product_height' => 'decimal:3',
        'end_water_height' => 'decimal:3',
        'end_temperature' => 'decimal:2',
        'end_product_volume' => 'decimal:3',
        'end_product_tc_volume' => 'decimal:3',
        'end_product_density' => 'decimal:3',
        'end_product_mass' => 'decimal:3',
        'received_product_volume' => 'decimal:3',
        'absolute_product_height' => 'decimal:3',
        'absolute_water_height' => 'decimal:3',
        'absolute_temperature' => 'decimal:2',
        'absolute_product_volume' => 'decimal:3',
        'absolute_product_tc_volume' => 'decimal:3',
        'absolute_product_density' => 'decimal:3',
        'absolute_product_mass' => 'decimal:3',
        'pumps_dispensed_volume' => 'decimal:3',
        'probe_data' => 'array',
    ];

    /**
     * Get the station that owns this tank delivery
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Scope to get tank deliveries for a specific station
     */
    public function scopeForStation($query, int $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * Scope to get tank deliveries within a date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_datetime', [$startDate, $endDate]);
    }

    /**
     * Scope to get tank deliveries by tank number
     */
    public function scopeByTank($query, int $tank)
    {
        return $query->where('tank', $tank);
    }

    /**
     * Scope to get tank deliveries by fuel grade
     */
    public function scopeByFuelGrade($query, int $fuelGradeId)
    {
        return $query->where('fuel_grade_id', $fuelGradeId);
    }

    /**
     * Scope to get recent tank deliveries
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('start_datetime', '>=', now()->subDays($days));
    }

    /**
     * Scope to get completed deliveries (have end_datetime)
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('end_datetime');
    }

    /**
     * Scope to get ongoing deliveries (no end_datetime)
     */
    public function scopeOngoing($query)
    {
        return $query->whereNull('end_datetime');
    }

    /**
     * Check if delivery is completed
     */
    public function isCompleted(): bool
    {
        return !is_null($this->end_datetime);
    }

    /**
     * Check if delivery is ongoing
     */
    public function isOngoing(): bool
    {
        return is_null($this->end_datetime);
    }

    /**
     * Get delivery duration in minutes
     */
    public function getDurationInMinutes(): ?int
    {
        if (!$this->end_datetime) {
            return null;
        }

        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    /**
     * Get delivery duration in hours
     */
    public function getDurationInHours(): ?float
    {
        if (!$this->end_datetime) {
            return null;
        }

        return $this->start_datetime->diffInHours($this->end_datetime);
    }

    /**
     * Get volume delivered
     */
    public function getVolumeDelivered(): ?float
    {
        if (!$this->start_product_volume || !$this->end_product_volume) {
            return null;
        }

        return $this->end_product_volume - $this->start_product_volume;
    }

    /**
     * Get mass delivered
     */
    public function getMassDelivered(): ?float
    {
        if (!$this->start_product_mass || !$this->end_product_mass) {
            return null;
        }

        return $this->end_product_mass - $this->start_product_mass;
    }

    /**
     * Get delivery status
     */
    public function getDeliveryStatus(): string
    {
        if ($this->isOngoing()) {
            return 'ongoing';
        }

        if ($this->isCompleted()) {
            return 'completed';
        }

        return 'unknown';
    }

    /**
     * Get delivery efficiency (volume per hour)
     */
    public function getDeliveryEfficiency(): ?float
    {
        $volumeDelivered = $this->getVolumeDelivered();
        $durationInHours = $this->getDurationInHours();

        if (!$volumeDelivered || !$durationInHours || $durationInHours <= 0) {
            return null;
        }

        return $volumeDelivered / $durationInHours;
    }
}
