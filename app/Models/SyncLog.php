<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id',
        'table_name',
        'operation',
        'request_payload',
        'response_data',
        'status',
        'error_message',
        'synced_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_data' => 'array',
        'synced_at' => 'datetime',
    ];

    /**
     * Get the station that owns this sync log
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Scope to get logs for a specific station
     */
    public function scopeForStation($query, int $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * Scope to get logs by table name
     */
    public function scopeForTable($query, string $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    /**
     * Scope to get logs by operation
     */
    public function scopeByOperation($query, string $operation)
    {
        return $query->where('operation', $operation);
    }

    /**
     * Scope to get logs by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get successful logs
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope to get failed logs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get recent logs
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Check if sync was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if sync failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if sync is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Mark sync as successful
     */
    public function markAsSuccessful(array $responseData = null): void
    {
        $this->update([
            'status' => 'success',
            'response_data' => $responseData,
            'synced_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark sync as failed
     */
    public function markAsFailed(string $errorMessage, array $responseData = null): void
    {
        $this->update([
            'status' => 'failed',
            'response_data' => $responseData,
            'error_message' => $errorMessage,
            'synced_at' => now(),
        ]);
    }

    /**
     * Create a new sync log entry
     */
    public static function createLog(
        int $stationId,
        string $tableName,
        string $operation,
        array $requestPayload,
        string $status = 'pending'
    ): self {
        return static::create([
            'station_id' => $stationId,
            'table_name' => $tableName,
            'operation' => $operation,
            'request_payload' => $requestPayload,
            'status' => $status,
        ]);
    }
}
