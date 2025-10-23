<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftTemplate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'end_time' => 'datetime:H:i:s',
        'synced_at' => 'datetime',
        'created_at_bos' => 'datetime',
        'updated_at_bos' => 'datetime',
    ];

    /**
     * Get the station that owns this shift template
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Scope to get shift templates for a specific station
     */
    public function scopeForStation($query, int $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * Scope to get shift templates by timezone
     */
    public function scopeByTimezone($query, string $timezone)
    {
        return $query->where('timezone', $timezone);
    }

    /**
     * Scope to get shift templates by end time
     */
    public function scopeByEndTime($query, string $endTime)
    {
        return $query->where('end_time', $endTime);
    }

    /**
     * Scope to get shift templates for a specific device
     */
    public function scopeForDevice($query, int $pts2DeviceId)
    {
        return $query->where('pts2_device_id', $pts2DeviceId);
    }

    /**
     * Scope to get active shift templates
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('end_time');
    }

    /**
     * Get formatted end time
     */
    public function getFormattedEndTime(): string
    {
        return $this->end_time->format('H:i:s');
    }

    /**
     * Get end time in 12-hour format
     */
    public function getEndTime12Hour(): string
    {
        return $this->end_time->format('g:i A');
    }

    /**
     * Get shift duration in hours (assuming 24-hour cycle)
     */
    public function getShiftDurationInHours(): float
    {
        // This assumes shifts are within a 24-hour period
        // You might need to adjust this based on your business logic
        $startTime = $this->end_time->copy()->subHours(8); // Assuming 8-hour shifts

        return $startTime->diffInHours($this->end_time);
    }

    /**
     * Check if shift template is for a specific timezone
     */
    public function isForTimezone(string $timezone): bool
    {
        return $this->timezone === $timezone;
    }

    /**
     * Get timezone display name
     */
    public function getTimezoneDisplay(): string
    {
        return match ($this->timezone) {
            'UTC' => 'UTC',
            'America/New_York' => 'Eastern Time',
            'America/Chicago' => 'Central Time',
            'America/Denver' => 'Mountain Time',
            'America/Los_Angeles' => 'Pacific Time',
            'Europe/London' => 'GMT',
            'Europe/Paris' => 'CET',
            'Asia/Tokyo' => 'JST',
            'Asia/Shanghai' => 'CST',
            default => $this->timezone,
        };
    }

    /**
     * Get shift template summary
     */
    public function getShiftTemplateSummary(): array
    {
        return [
            'id' => $this->id,
            'end_time' => $this->getFormattedEndTime(),
            'end_time_12h' => $this->getEndTime12Hour(),
            'timezone' => $this->timezone,
            'timezone_display' => $this->getTimezoneDisplay(),
            'duration_hours' => $this->getShiftDurationInHours(),
            'pts2_device_id' => $this->pts2_device_id,
        ];
    }

    /**
     * Check if shift template is valid
     */
    public function isValid(): bool
    {
        return $this->end_time !== null && !empty($this->timezone);
    }
}
