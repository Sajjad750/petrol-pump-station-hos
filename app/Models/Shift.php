<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start_time' => 'datetime',
        'start_time_utc' => 'datetime',
        'end_time' => 'datetime',
        'end_time_utc' => 'datetime',
        'auto_close_time' => 'datetime',
        'auto_close_time_utc' => 'datetime',
        'synced_at' => 'datetime',
        'created_at_bos' => 'datetime',
        'updated_at_bos' => 'datetime',
    ];

    /**
     * Get the station that owns this shift
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Get all product wise summaries for this shift
     */
    public function productWiseSummaries(): HasMany
    {
        return $this->hasMany(ProductWiseSummary::class, 'bos_shift_id', 'bos_shift_id');
    }

    /**
     * Scope to get shifts for a specific station
     */
    public function scopeForStation($query, int $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * Scope to get shifts for a specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get shifts by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get active shifts (started but not completed)
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'started');
    }

    /**
     * Scope to get completed shifts
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get shifts by close type
     */
    public function scopeByCloseType($query, string $closeType)
    {
        return $query->where('close_type', $closeType);
    }

    /**
     * Scope to get manual close shifts
     */
    public function scopeManualClose($query)
    {
        return $query->where('close_type', 'manual');
    }

    /**
     * Scope to get auto close shifts
     */
    public function scopeAutoClose($query)
    {
        return $query->where('close_type', 'auto');
    }

    /**
     * Scope to get shifts within a date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_time', [$startDate, $endDate]);
    }

    /**
     * Scope to get recent shifts
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('start_time', '>=', now()->subDays($days));
    }

    /**
     * Scope to get shifts that need auto-close
     */
    public function scopeNeedsAutoClose($query)
    {
        return $query->where('status', 'started')
            ->where('close_type', 'auto')
            ->where('auto_close_time', '<=', now());
    }

    /**
     * Check if shift is active
     */
    public function isActive(): bool
    {
        return $this->status === 'started';
    }

    /**
     * Check if shift is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if shift is manual close
     */
    public function isManualClose(): bool
    {
        return $this->close_type === 'manual';
    }

    /**
     * Check if shift is auto close
     */
    public function isAutoClose(): bool
    {
        return $this->close_type === 'auto';
    }

    /**
     * Get shift duration in minutes
     */
    public function getDurationInMinutes(): ?int
    {
        if (!$this->end_time) {
            return null;
        }

        return $this->start_time->diffInMinutes($this->end_time);
    }

    /**
     * Get shift duration in hours
     */
    public function getDurationInHours(): ?float
    {
        if (!$this->end_time) {
            return null;
        }

        return $this->start_time->diffInHours($this->end_time);
    }

    /**
     * Get shift duration in human readable format
     */
    public function getDurationFormatted(): ?string
    {
        $duration = $this->getDurationInMinutes();

        if ($duration === null) {
            return null;
        }

        $hours = floor($duration / 60);
        $minutes = $duration % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    /**
     * Check if shift is overdue for auto-close
     */
    public function isOverdueForAutoClose(): bool
    {
        if (!$this->isAutoClose() || $this->isCompleted()) {
            return false;
        }

        return $this->auto_close_time && $this->auto_close_time <= now();
    }

    /**
     * Get shift status display
     */
    public function getStatusDisplay(): string
    {
        return match ($this->status) {
            'started' => 'Active',
            'completed' => 'Completed',
            default => 'Unknown',
        };
    }

    /**
     * Get close type display
     */
    public function getCloseTypeDisplay(): string
    {
        return match ($this->close_type) {
            'manual' => 'Manual',
            'auto' => 'Automatic',
            default => 'Unknown',
        };
    }

    /**
     * Get total sales amount for this shift
     */
    public function getTotalSalesAmount(): float
    {
        return $this->productWiseSummaries()->sum('amount');
    }

    /**
     * Get total sales volume for this shift
     */
    public function getTotalSalesVolume(): float
    {
        return $this->productWiseSummaries()->sum('volume');
    }

    /**
     * Get shift summary
     */
    public function getShiftSummary(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $this->getStatusDisplay(),
            'close_type' => $this->getCloseTypeDisplay(),
            'start_time' => $this->start_time?->format('Y-m-d H:i:s'),
            'end_time' => $this->end_time?->format('Y-m-d H:i:s'),
            'duration' => $this->getDurationFormatted(),
            'total_sales_amount' => $this->getTotalSalesAmount(),
            'total_sales_volume' => $this->getTotalSalesVolume(),
            'notes' => $this->notes,
        ];
    }
}
