<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HosCommand extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id',
        'command_type',
        'command_data',
        'status',
        'error_message',
        'executed_at',
        'acknowledged_at',
        'retry_count',
    ];

    protected function casts(): array
    {
        return [
            'command_data' => 'array',
            'executed_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }

    /**
     * Get the station that owns this command
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Scope to get pending commands
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get commands for a specific station
     */
    public function scopeForStation($query, int $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * Scope to get commands by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('command_type', $type);
    }

    /**
     * Mark command as processing
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
        ]);
    }

    /**
     * Mark command as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'acknowledged_at' => now(),
        ]);
    }

    /**
     * Mark command as failed
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'acknowledged_at' => now(),
        ]);
    }

    /**
     * Increment retry count
     */
    public function incrementRetry(): void
    {
        $this->increment('retry_count');
    }
}
