<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftPumpTotal extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'volume' => 'decimal:3',
        'amount' => 'decimal:2',
        'recorded_at' => 'datetime',
        'synced_at' => 'datetime',
        'created_at_bos' => 'datetime',
        'updated_at_bos' => 'datetime',
    ];

    /**
     * Get the station that owns this shift pump total
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Get the shift that owns this shift pump total
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Scope to get shift pump totals for a specific station
     */
    public function scopeForStation($query, int $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * Scope to get shift pump totals for a specific shift
     */
    public function scopeForShift($query, int $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    /**
     * Scope to get shift pump totals by pump
     */
    public function scopeByPump($query, int $pumpId)
    {
        return $query->where('pump_id', $pumpId);
    }

    /**
     * Scope to get shift pump totals by fuel grade
     */
    public function scopeByFuelGrade($query, int $fuelGradeId)
    {
        return $query->where('fuel_grade_id', $fuelGradeId);
    }

    /**
     * Scope to get shift pump totals within a date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('recorded_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get recent shift pump totals
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('recorded_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get shift pump totals with volume above threshold
     */
    public function scopeWithVolumeAbove($query, float $threshold)
    {
        return $query->where('volume', '>', $threshold);
    }

    /**
     * Scope to get shift pump totals with amount above threshold
     */
    public function scopeWithAmountAbove($query, float $threshold)
    {
        return $query->where('amount', '>', $threshold);
    }

    /**
     * Get average price per liter
     */
    public function getAveragePricePerLiter(): ?float
    {
        if ($this->volume <= 0) {
            return null;
        }

        return $this->amount / $this->volume;
    }

    /**
     * Get formatted volume
     */
    public function getFormattedVolume(): string
    {
        return number_format($this->volume, 3) . ' L';
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmount(): string
    {
        return '$' . number_format($this->amount, 2);
    }

    /**
     * Get formatted average price
     */
    public function getFormattedAveragePrice(): string
    {
        $price = $this->getAveragePricePerLiter();

        if ($price === null) {
            return 'N/A';
        }

        return '$' . number_format($price, 3) . '/L';
    }

    /**
     * Check if total has significant volume
     */
    public function hasSignificantVolume(float $threshold = 100.0): bool
    {
        return $this->volume >= $threshold;
    }

    /**
     * Check if total has significant amount
     */
    public function hasSignificantAmount(float $threshold = 1000.0): bool
    {
        return $this->amount >= $threshold;
    }

    /**
     * Get total status
     */
    public function getTotalStatus(): string
    {
        if ($this->volume <= 0) {
            return 'no_sales';
        }

        if ($this->hasSignificantVolume() && $this->hasSignificantAmount()) {
            return 'high_sales';
        }

        if ($this->hasSignificantVolume() || $this->hasSignificantAmount()) {
            return 'moderate_sales';
        }

        return 'low_sales';
    }

    /**
     * Get transaction rate (transactions per hour)
     */
    public function getTransactionRate(): ?float
    {
        if (!$this->recorded_at || $this->transaction_count <= 0) {
            return null;
        }

        $hoursElapsed = $this->recorded_at->diffInHours(now());

        if ($hoursElapsed <= 0) {
            return null;
        }

        return $this->transaction_count / $hoursElapsed;
    }

    /**
     * Get average transaction volume
     */
    public function getAverageTransactionVolume(): ?float
    {
        if ($this->transaction_count <= 0) {
            return null;
        }

        return $this->volume / $this->transaction_count;
    }

    /**
     * Get average transaction amount
     */
    public function getAverageTransactionAmount(): ?float
    {
        if ($this->transaction_count <= 0) {
            return null;
        }

        return $this->amount / $this->transaction_count;
    }
}
