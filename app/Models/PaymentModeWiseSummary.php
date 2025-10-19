<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentModeWiseSummary extends Model
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
     * Get the station that owns this payment mode wise summary
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Get the shift that owns this payment mode wise summary
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Scope to get payment mode wise summaries for a specific station
     */
    public function scopeForStation($query, int $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * Scope to get payment mode wise summaries for a specific shift
     */
    public function scopeForShift($query, int $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    /**
     * Scope to get payment mode wise summaries by payment mode
     */
    public function scopeByPaymentMode($query, string $mop)
    {
        return $query->where('mop', $mop);
    }

    /**
     * Scope to get payment mode wise summaries within a date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get recent payment mode wise summaries
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
     * Scope to get top payment modes by volume
     */
    public function scopeTopByVolume($query, int $limit = 10)
    {
        return $query->orderBy('volume', 'desc')->limit($limit);
    }

    /**
     * Scope to get top payment modes by amount
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
     * Get payment mode display name
     */
    public function getPaymentModeDisplay(): string
    {
        return match (strtolower($this->mop)) {
            'cash' => 'Cash',
            'card' => 'Card',
            'credit' => 'Credit',
            'debit' => 'Debit',
            'mobile' => 'Mobile Payment',
            'voucher' => 'Voucher',
            'loyalty' => 'Loyalty Points',
            default => ucfirst($this->mop),
        };
    }
}
