<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductWiseSummary extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'volume' => 'decimal:2',
        'amount' => 'decimal:2',
        'synced_at' => 'datetime',
        'created_at_bos' => 'datetime',
        'updated_at_bos' => 'datetime',
    ];

    /**
     * Get the station that owns this product wise summary
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Get the fuel grade for this product wise summary
     */
    public function fuelGrade(): BelongsTo
    {
        return $this->belongsTo(FuelGrade::class, 'fuel_grade_id', 'id');
    }

    /**
     * Scope to get product wise summaries for a specific station
     */
    public function scopeForStation($query, int $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * Scope to get product wise summaries for a specific shift
     */
    public function scopeForShift($query, int $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    /**
     * Scope to get product wise summaries by BOS shift id and station
     */
    public function scopeForBosShiftAtStation($query, int $bosShiftId, int $stationId)
    {
        return $query->where('bos_shift_id', $bosShiftId)->where('station_id', $stationId);
    }

    /**
     * Scope to get product wise summaries by fuel grade
     */
    public function scopeByFuelGrade($query, int $fuelGradeId)
    {
        return $query->where('fuel_grade_id', $fuelGradeId);
    }

    /**
     * Scope to get product wise summaries within a date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get recent product wise summaries
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get summaries with volume above threshold
     */
    public function scopeWithVolumeAbove($query, float $threshold)
    {
        return $query->where('volume', '>', $threshold);
    }

    /**
     * Scope to get summaries with amount above threshold
     */
    public function scopeWithAmountAbove($query, float $threshold)
    {
        return $query->where('amount', '>', $threshold);
    }

    /**
     * Scope to get top selling fuel grades by volume
     */
    public function scopeTopByVolume($query, int $limit = 10)
    {
        return $query->orderBy('volume', 'desc')->limit($limit);
    }

    /**
     * Scope to get top selling fuel grades by amount
     */
    public function scopeTopByAmount($query, int $limit = 10)
    {
        return $query->orderBy('amount', 'desc')->limit($limit);
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
        return number_format($this->volume, 2) . ' L';
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
     * Check if summary has significant volume
     */
    public function hasSignificantVolume(float $threshold = 100.0): bool
    {
        return $this->volume >= $threshold;
    }

    /**
     * Check if summary has significant amount
     */
    public function hasSignificantAmount(float $threshold = 1000.0): bool
    {
        return $this->amount >= $threshold;
    }

    /**
     * Get summary status
     */
    public function getSummaryStatus(): string
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
     * Get fuel grade name (if available from related data)
     */
    public function getFuelGradeName(): ?string
    {
        // This would typically come from a fuel_grades table or similar
        // For now, return null as we don't have that relationship set up
        return null;
    }

    /**
     * Get shift information (if available from related data)
     */
    public function getShiftInfo(): ?array
    {
        // This would typically come from a shifts table or similar
        // For now, return null as we don't have that relationship set up
        return null;
    }
}
