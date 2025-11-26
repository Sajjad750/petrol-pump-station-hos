<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelGrade extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:2',
        'scheduled_price' => 'decimal:3',
        'scheduled_at' => 'datetime',
        'expansion_coefficient' => 'decimal:5',
        'synced_at' => 'datetime',
        'created_at_bos' => 'datetime',
        'updated_at_bos' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the station that owns this fuel grade
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Get all product wise summaries for this fuel grade
     */
    public function productWiseSummaries(): HasMany
    {
        return $this->hasMany(ProductWiseSummary::class, 'fuel_grade_id', 'id');
    }

    /**
     * Scope to get fuel grades for a specific station
     */
    public function scopeForStation($query, int $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * Scope to get fuel grades by name
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', $name);
    }

    /**
     * Scope to get fuel grades by price range
     */
    public function scopeByPriceRange($query, float $minPrice, float $maxPrice)
    {
        return $query->whereBetween('price', [$minPrice, $maxPrice]);
    }

    /**
     * Scope to get fuel grades with scheduled price changes
     */
    public function scopeWithScheduledPrice($query)
    {
        return $query->whereNotNull('scheduled_price')
            ->whereNotNull('scheduled_at');
    }

    /**
     * Scope to get fuel grades with pending price changes
     */
    public function scopeWithPendingPriceChanges($query)
    {
        return $query->whereNotNull('scheduled_price')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now());
    }

    /**
     * Scope to get fuel grades with active price changes
     */
    public function scopeWithActivePriceChanges($query)
    {
        return $query->whereNotNull('scheduled_price')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now());
    }

    /**
     * Scope to get blended fuel grades
     */
    public function scopeBlended($query)
    {
        return $query->whereNotNull('blend_tank1_id')
            ->where('blend_tank1_id', '>', 0);
    }

    /**
     * Scope to get non-blended fuel grades
     */
    public function scopeNonBlended($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('blend_tank1_id')
              ->orWhere('blend_tank1_id', 0);
        });
    }

    /**
     * Check if fuel grade has scheduled price change
     */
    public function hasScheduledPriceChange(): bool
    {
        return $this->scheduled_price !== null && $this->scheduled_at !== null;
    }

    /**
     * Check if scheduled price change is pending
     */
    public function hasPendingPriceChange(): bool
    {
        return $this->hasScheduledPriceChange() && $this->scheduled_at > now();
    }

    /**
     * Check if scheduled price change is active
     */
    public function hasActivePriceChange(): bool
    {
        return $this->hasScheduledPriceChange() && $this->scheduled_at <= now();
    }

    /**
     * Check if fuel grade is blended
     */
    public function isBlended(): bool
    {
        return $this->blend_tank1_id !== null && $this->blend_tank1_id > 0;
    }

    /**
     * Get current effective price
     */
    public function getCurrentPrice(): float
    {
        if ($this->hasActivePriceChange()) {
            return $this->scheduled_price;
        }

        return $this->price;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPrice(): string
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get formatted scheduled price
     */
    public function getFormattedScheduledPrice(): ?string
    {
        if (!$this->scheduled_price) {
            return null;
        }

        return '$' . number_format($this->scheduled_price, 3);
    }

    /**
     * Get formatted current effective price
     */
    public function getFormattedCurrentPrice(): string
    {
        return '$' . number_format($this->getCurrentPrice(), 2);
    }

    /**
     * Get price change status
     */
    public function getPriceChangeStatus(): string
    {
        if (!$this->hasScheduledPriceChange()) {
            return 'no_change';
        }

        if ($this->hasPendingPriceChange()) {
            return 'pending';
        }

        if ($this->hasActivePriceChange()) {
            return 'active';
        }

        return 'unknown';
    }

    /**
     * Get blend information
     */
    public function getBlendInfo(): ?array
    {
        if (!$this->isBlended()) {
            return null;
        }

        return [
            'tank1_id' => $this->blend_tank1_id,
            'tank1_percentage' => $this->blend_tank1_percentage,
            'tank2_id' => $this->blend_tank2_id,
            'tank2_percentage' => $this->blend_tank2_percentage ? (100 - $this->blend_tank1_percentage) : null,
        ];
    }

    /**
     * Get fuel grade summary
     */
    public function getFuelGradeSummary(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'current_price' => $this->getCurrentPrice(),
            'scheduled_price' => $this->scheduled_price,
            'scheduled_at' => $this->scheduled_at?->format('Y-m-d H:i:s'),
            'price_change_status' => $this->getPriceChangeStatus(),
            'is_blended' => $this->isBlended(),
            'blend_info' => $this->getBlendInfo(),
            'expansion_coefficient' => $this->expansion_coefficient,
        ];
    }
}
