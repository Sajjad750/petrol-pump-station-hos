<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelGradePriceHistory extends Model
{
    protected $table = 'fuel_grade_price_history';

    protected $guarded = [];

    protected $casts = [
        'old_price' => 'decimal:3',
        'new_price' => 'decimal:3',
        'effective_at' => 'datetime',
        'synced_at' => 'datetime',
        'created_at_bos' => 'datetime',
        'updated_at_bos' => 'datetime',
    ];

    /**
     * Get the fuel grade for this price history entry
     */
    public function fuelGrade(): BelongsTo
    {
        return $this->belongsTo(FuelGrade::class, 'fuel_grade_id', 'bos_fuel_grade_id');
    }
}
