<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PumpTransaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date_time_start' => 'datetime',
        'date_time_end' => 'datetime',
        'date_time_paid' => 'datetime',
        'volume' => 'decimal:3',
        'amount' => 'decimal:2',
        'synced_at' => 'datetime',
        'created_at_bos' => 'datetime',
        'updated_at_bos' => 'datetime',
    ];

    /**
     * Get the station that owns this transaction
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Get the fuel grade for this transaction
     */
    public function fuelGrade(): BelongsTo
    {
        return $this->belongsTo(FuelGrade::class, 'pts_fuel_grade_id', 'id');
    }

    /**
     * Scope to get transactions for a specific station
     */
    public function scopeForStation($query, int $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * Scope to get transactions within a date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date_time_start', [$startDate, $endDate]);
    }

    /**
     * Scope to get transactions by fuel grade
     */
    public function scopeByFuelGrade($query, string $fuelGradeId)
    {
        return $query->where('fuel_grade_id', $fuelGradeId);
    }

    /**
     * Scope to get transactions by payment method
     */
    public function scopeByPaymentMethod($query, string $paymentMethod)
    {
        return $query->where('payment_method', $paymentMethod);
    }

    /**
     * Scope to get recent transactions
     */
    public function scopeRecent($query, int $minutes = 60)
    {
        return $query->where('date_time_start', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope to get completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('date_time_end');
    }

    /**
     * Scope to get paid transactions
     */
    public function scopePaid($query)
    {
        return $query->whereNotNull('date_time_paid');
    }

    /**
     * Check if transaction is completed
     */
    public function isCompleted(): bool
    {
        return !is_null($this->date_time_end);
    }

    /**
     * Check if transaction is paid
     */
    public function isPaid(): bool
    {
        return !is_null($this->date_time_paid);
    }

    /**
     * Get transaction duration in minutes
     */
    public function getDurationInMinutes(): ?int
    {
        if (!$this->date_time_end) {
            return null;
        }

        return $this->date_time_start->diffInMinutes($this->date_time_end);
    }

    /**
     * Get transaction duration in seconds
     */
    public function getDurationInSeconds(): ?int
    {
        if (!$this->date_time_end) {
            return null;
        }

        return $this->date_time_start->diffInSeconds($this->date_time_end);
    }
}
