<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id',
        'device_type',
        'device_number',
        'state',
        'code',
        'datetime',
        'is_read',
        'meta',
        'description',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'datetime' => 'datetime',
        'meta' => 'array',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
