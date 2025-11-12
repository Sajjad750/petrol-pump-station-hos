<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id',
        'bos_alert_id',
        'bos_uuid',
        'device_type',
        'device_number',
        'state',
        'code',
        'datetime',
        'is_read',
        'meta',
        'description',
        'raw_payload',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'datetime' => 'datetime',
        'meta' => 'array',
        'raw_payload' => 'array',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
